<?php

namespace App\Http\Controllers;

use App\Models\SimplePrescription;
use App\Models\SimplePrescriptionMedicine;
use App\Models\Patient;
use App\Models\Medicine;
use App\Models\MedicineSaleInvoice;
use App\Models\MedicineTransaction;
use App\Models\Clinic;
use App\Models\User;
use App\Services\PdfKurdishFontService;
use App\Services\StorageQuotaService;
use App\Services\ThermalReceiptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class SimplePrescriptionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get tenant clinic IDs for cross-clinic access
        $tenantClinicIds = $user->clinic ? $user->clinic->getTenantClinicIds() : [$user->clinic_id];

        $query = SimplePrescription::with(['patient', 'doctor', 'clinic']);

        // Filter prescriptions based on user role
        if ($user->isSuperAdmin()) {
            // Super Admin sees all prescriptions from all clinics
            // No filter needed
        } elseif ($user->role === 'pharmacist') {
            // Pharmacists see all prescriptions from all clinics in their tenant
            $query->whereIn('clinic_id', $tenantClinicIds);
        } elseif ($user->isClinicAdmin()) {
            // Clinic Admins see all prescriptions in their clinic only
            $query->where('clinic_id', $user->clinic_id);
        } else {
            // Regular doctors can only see their own prescriptions
            $query->where('clinic_id', $user->clinic_id)
                  ->where('doctor_id', $user->id);
        }

        // Filter by patient name
        if ($request->filled('patient_name')) {
            $patientName = $request->patient_name;
            $query->whereHas('patient', function ($q) use ($patientName) {
                $q->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'LIKE', "%{$patientName}%")
                  ->orWhere('first_name', 'LIKE', "%{$patientName}%")
                  ->orWhere('last_name', 'LIKE', "%{$patientName}%")
                  ->orWhere('patient_id', 'LIKE', "%{$patientName}%");
            });
        }

        // Filter by doctor name (new filter for pharmacists)
        if ($request->filled('doctor_name')) {
            $doctorName = $request->doctor_name;
            $query->whereHas('doctor', function ($q) use ($doctorName) {
                $q->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'LIKE', "%{$doctorName}%")
                  ->orWhere('first_name', 'LIKE', "%{$doctorName}%")
                  ->orWhere('last_name', 'LIKE', "%{$doctorName}%");
            });
        }

        // Filter by patient ID (for direct patient filtering)
        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('prescribed_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('prescribed_date', '<=', $request->date_to);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $prescriptions = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get patients for dropdown filter (from tenant clinics)
        $patients = Patient::whereIn('clinic_id', $tenantClinicIds)
            ->where('is_active', true)
            ->orderBy('first_name')
            ->get();

        // Get list of doctors for filter dropdown (for pharmacists)
        $doctors = collect();
        if ($user->role === 'pharmacist' || $user->isSuperAdmin()) {
            $doctors = User::whereIn('clinic_id', $tenantClinicIds)
                ->where('role', 'doctor')
                ->where('is_active', true)
                ->select('id', 'first_name', 'last_name', 'title_prefix')
                ->orderBy('first_name')
                ->get();
        }

        return view('simple-prescriptions.index', compact('prescriptions', 'patients', 'doctors'));
    }

    public function create(Request $request)
    {
        $patients = Patient::where('clinic_id', Auth::user()->clinic_id)
            ->where('is_active', true)
            ->orderBy('first_name')
            ->get();

        $medicines = Medicine::where('clinic_id', Auth::user()->clinic_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $selectedPatientId = $request->get('patient_id');

        return view('simple-prescriptions.create', compact('patients', 'medicines', 'selectedPatientId'));
    }

    public function store(Request $request)
    {
        // Simple validation
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'prescribed_date' => 'required|date',
            'diagnosis' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'medicines' => 'nullable|array',
            'medicines.*.name' => 'nullable|string|max:255',
            'medicines.*.dosage' => 'nullable|string|max:100',
            'medicines.*.frequency' => 'nullable|string|max:100',
            'medicines.*.duration' => 'nullable|string|max:100',
            'medicines.*.instructions' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // Create prescription
            $prescription = SimplePrescription::create([
                'patient_id' => $request->patient_id,
                'doctor_id' => Auth::id(),
                'clinic_id' => Auth::user()->clinic_id,
                'prescription_number' => SimplePrescription::generatePrescriptionNumber(),
                'diagnosis' => $request->diagnosis,
                'notes' => $request->notes,
                'prescribed_date' => $request->prescribed_date,
                'status' => 'active'
            ]);

            // Add medicines if provided
            if ($request->medicines) {
                foreach ($request->medicines as $medicine) {
                    if (!empty($medicine['name'])) {
                        // Check if it's a new medicine (starts with 'new:')
                        $medicineName = $medicine['name'];
                        if (strpos($medicineName, 'new:') === 0) {
                            // Create new medicine
                            $newMedicineName = substr($medicineName, 4); // Remove 'new:' prefix
                            $newMedicine = Medicine::create([
                                'name' => $newMedicineName,
                                'generic_name' => $newMedicineName,
                                'dosage' => $medicine['strength'] ?? null,
                                'form' => 'other',
                                'is_frequent' => false,
                                'clinic_id' => Auth::user()->clinic_id,
                                'created_by' => Auth::id(),
                                'is_active' => true,
                            ]);
                            $medicineName = $newMedicineName;
                        }

                        SimplePrescriptionMedicine::create([
                            'prescription_id' => $prescription->id,
                            'medicine_name' => $medicineName,
                            'dosage' => $medicine['dosage'] ?? null,
                            'frequency' => $medicine['frequency'] ?? null,
                            'duration' => $medicine['duration'] ?? null,
                            'instructions' => $medicine['instructions'] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('simple-prescriptions.show', $prescription->id)
                ->with('success', 'Prescription created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating prescription: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $user = Auth::user();

        // Get tenant clinic IDs for cross-clinic access
        $tenantClinicIds = $user->clinic ? $user->clinic->getTenantClinicIds() : [$user->clinic_id];

        // Pharmacists and super admins can view prescriptions from all tenant clinics
        if ($user->role === 'pharmacist' || $user->isSuperAdmin()) {
            $prescription = SimplePrescription::with(['patient', 'doctor', 'medicines', 'clinic'])
                ->whereIn('clinic_id', $tenantClinicIds)
                ->findOrFail($id);
        } else {
            // Others are restricted to their clinic
            $prescription = SimplePrescription::with(['patient', 'doctor', 'medicines', 'clinic'])
                ->forClinic($user->clinic_id)
                ->findOrFail($id);

            // Authorization: Only allow access to own prescriptions for regular doctors
            if (!$user->isClinicAdmin() && $prescription->doctor_id !== $user->id) {
                abort(403, 'You can only view your own prescriptions.');
            }
        }

        return view('simple-prescriptions.show', compact('prescription'));
    }

    public function edit($id)
    {
        $user = Auth::user();

        // Pharmacists cannot edit prescriptions
        if ($user->role === 'pharmacist') {
            abort(403, 'Pharmacists can view but not edit prescriptions.');
        }

        $prescription = SimplePrescription::with('medicines')
            ->forClinic($user->clinic_id)
            ->findOrFail($id);

        // Authorization: Only allow editing own prescriptions for regular doctors
        if (!$user->isSuperAdmin() && !$user->isClinicAdmin() && $prescription->doctor_id !== $user->id) {
            abort(403, 'You can only edit your own prescriptions.');
        }

        $patients = Patient::where('clinic_id', $user->clinic_id)
            ->where('is_active', true)
            ->orderBy('first_name')
            ->get();

        $medicines = Medicine::where('clinic_id', $user->clinic_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('simple-prescriptions.edit', compact('prescription', 'patients', 'medicines'));
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $prescription = SimplePrescription::forClinic($user->clinic_id)->findOrFail($id);

        // Authorization: Only allow updating own prescriptions for regular doctors
        if (!$user->isSuperAdmin() && !$user->isClinicAdmin() && $prescription->doctor_id !== $user->id) {
            abort(403, 'You can only update your own prescriptions.');
        }

        // Simple validation
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'prescribed_date' => 'required|date',
            'diagnosis' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'medicines' => 'nullable|array',
            'medicines.*.name' => 'nullable|string|max:255',
            'medicines.*.dosage' => 'nullable|string|max:100',
            'medicines.*.frequency' => 'nullable|string|max:100',
            'medicines.*.duration' => 'nullable|string|max:100',
            'medicines.*.instructions' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // Update prescription
            $prescription->update([
                'patient_id' => $request->patient_id,
                'diagnosis' => $request->diagnosis,
                'notes' => $request->notes,
                'prescribed_date' => $request->prescribed_date,
            ]);

            // Delete existing medicines and add new ones
            $prescription->medicines()->delete();

            if ($request->medicines) {
                foreach ($request->medicines as $medicine) {
                    if (!empty($medicine['name'])) {
                        // Check if it's a new medicine (starts with 'new:')
                        $medicineName = $medicine['name'];
                        if (strpos($medicineName, 'new:') === 0) {
                            // Create new medicine
                            $newMedicineName = substr($medicineName, 4); // Remove 'new:' prefix
                            $newMedicine = Medicine::create([
                                'name' => $newMedicineName,
                                'generic_name' => $newMedicineName,
                                'dosage' => $medicine['strength'] ?? null,
                                'form' => 'other',
                                'is_frequent' => false,
                                'clinic_id' => Auth::user()->clinic_id,
                                'created_by' => Auth::id(),
                                'is_active' => true,
                            ]);
                            $medicineName = $newMedicineName;
                        }

                        SimplePrescriptionMedicine::create([
                            'prescription_id' => $prescription->id,
                            'medicine_name' => $medicineName,
                            'dosage' => $medicine['dosage'] ?? null,
                            'frequency' => $medicine['frequency'] ?? null,
                            'duration' => $medicine['duration'] ?? null,
                            'instructions' => $medicine['instructions'] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('simple-prescriptions.show', $prescription->id)
                ->with('success', 'Prescription updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating prescription: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $prescription = SimplePrescription::forClinic($user->clinic_id)->findOrFail($id);

        // Authorization: Only allow deleting own prescriptions for regular doctors
        if (!$user->isSuperAdmin() && !$user->isClinicAdmin() && $prescription->doctor_id !== $user->id) {
            abort(403, 'You can only delete your own prescriptions.');
        }

        $prescription->delete();

        return redirect()->route('simple-prescriptions.index')
            ->with('success', 'Prescription deleted successfully!');
    }

    public function pdf($id, Request $request)
    {
        $user = Auth::user();

        // Get tenant clinic IDs for cross-clinic access
        $tenantClinicIds = $user->clinic ? $user->clinic->getTenantClinicIds() : [$user->clinic_id];

        // Pharmacists and super admins can view prescriptions from all tenant clinics
        if ($user->role === 'pharmacist' || $user->isSuperAdmin()) {
            $prescription = SimplePrescription::with(['patient', 'doctor', 'medicines', 'clinic'])
                ->whereIn('clinic_id', $tenantClinicIds)
                ->findOrFail($id);
        } else {
            $prescription = SimplePrescription::with(['patient', 'doctor', 'medicines', 'clinic'])
                ->forClinic($user->clinic_id)
                ->findOrFail($id);

            // Authorization: Only allow PDF generation for own prescriptions for regular doctors
            if (!$user->isClinicAdmin() && $prescription->doctor_id !== $user->id) {
                abort(403, 'You can only generate PDF for your own prescriptions.');
            }
        }

        // Check if user explicitly requested custom template via query param
        $clinic = Clinic::find($user->clinic_id);
        $useCustomTemplate = false;
        $templateLocalPath = null;
        $templateIsPdf = false;
        $rxSettings = [];

        if ($clinic) {
            // Use custom template if ?template=custom is passed, OR if the setting is enabled
            $requestedCustom = $request->query('template') === 'custom';
            $enabledRaw = $clinic->getSetting('rx_template_enabled', false);
            $settingEnabled = filter_var($enabledRaw, FILTER_VALIDATE_BOOLEAN);
            $useCustomTemplate = $requestedCustom || $settingEnabled;
            $templatePath = $clinic->getSetting('rx_template_path');

            \Log::info('RX PDF: template check', [
                'clinic_id' => $clinic->id,
                'requestedCustom' => $requestedCustom,
                'settingEnabled' => $settingEnabled,
                'useCustomTemplate' => $useCustomTemplate,
                'templatePath' => $templatePath,
            ]);

            if ($useCustomTemplate && $templatePath) {
                try {
                    $ext = strtolower(pathinfo($templatePath, PATHINFO_EXTENSION));
                    $templateIsPdf = ($ext === 'pdf');
                    $tempFile = storage_path('app/temp_rx_template_' . $clinic->id . '.' . $ext);

                    // Check if file exists on Spaces first
                    $existsOnSpaces = Storage::disk(StorageQuotaService::SPACES_DISK)->exists($templatePath);
                    \Log::info('RX PDF: template file check', [
                        'ext' => $ext,
                        'isPdf' => $templateIsPdf,
                        'existsOnSpaces' => $existsOnSpaces,
                        'spacesPath' => $templatePath,
                    ]);

                    if (!$existsOnSpaces) {
                        \Log::warning('RX PDF: template file not found on Spaces', ['path' => $templatePath]);
                        $useCustomTemplate = false;
                    } else {
                        $contents = Storage::disk(StorageQuotaService::SPACES_DISK)->get($templatePath);
                        if ($contents && strlen($contents) > 0) {
                            file_put_contents($tempFile, $contents);
                            $templateLocalPath = $tempFile;
                            \Log::info('RX PDF: template downloaded OK', [
                                'size' => strlen($contents),
                                'localPath' => $tempFile,
                            ]);
                        } else {
                            \Log::warning('RX PDF: template file empty from Spaces');
                            $useCustomTemplate = false;
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error('RX Template download failed', [
                        'error' => $e->getMessage(),
                        'path' => $templatePath,
                        'trace' => $e->getTraceAsString(),
                    ]);
                    $useCustomTemplate = false;
                }
            } else {
                if ($useCustomTemplate && !$templatePath) {
                    \Log::warning('RX PDF: custom template requested but no template path stored in clinic settings');
                }
                $useCustomTemplate = false;
            }

            $rxSettings = [
                'medicine_x' => (int) $clinic->getSetting('rx_medicine_x', 40),
                'medicine_y' => (int) $clinic->getSetting('rx_medicine_y', 200),
                'font_size' => (int) $clinic->getSetting('rx_font_size', 11),
                'line_spacing' => (int) $clinic->getSetting('rx_line_spacing', 22),
                'max_medicines' => (int) $clinic->getSetting('rx_max_medicines', 12),
                'notes_y_bottom' => (int) $clinic->getSetting('rx_notes_y_bottom', 60),
                'notes_x_right' => (int) $clinic->getSetting('rx_notes_x_right', 40),
            ];
        }

        // Create mPDF instance with Arabic support
        $tempDir = storage_path('mpdf/temp');
        if (!is_dir($tempDir)) {
            @mkdir($tempDir, 0755, true);
        }

        $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];
        $fontDirs[] = storage_path('fonts');

        $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        // Get paper size from clinic settings (default to A4)
        $paperSize = $clinic ? $clinic->getSetting('rx_paper_size', 'A4') : 'A4';
        $allowedSizes = ['A4', 'A5', 'A6', 'Letter', 'Legal', 'B5'];
        if (!in_array($paperSize, $allowedSizes)) {
            $paperSize = 'A4';
        }

        $mpdfConfig = [
            'mode' => 'utf-8',
            'format' => $paperSize,
            'tempDir' => $tempDir,
            'fontDir' => $fontDirs,
            'fontdata' => $fontData,
            'default_font' => 'dejavusans',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
        ];

        // For custom templates, remove default margins so the background fills the page
        if ($useCustomTemplate && $templateLocalPath) {
            $mpdfConfig['margin_top'] = 0;
            $mpdfConfig['margin_bottom'] = 0;
            $mpdfConfig['margin_left'] = 0;
            $mpdfConfig['margin_right'] = 0;
        }

        $mpdf = new \Mpdf\Mpdf($mpdfConfig);

        // Apply custom template
        $pdfConvertedImagePath = null; // track converted image for cleanup
        try {
            if ($useCustomTemplate && $templateLocalPath) {
                \Log::info('RX PDF: rendering custom template', [
                    'isPdf' => $templateIsPdf,
                    'localPath' => $templateLocalPath,
                    'fileExists' => file_exists($templateLocalPath),
                    'fileSize' => file_exists($templateLocalPath) ? filesize($templateLocalPath) : 0,
                ]);

                // If template is PDF, convert to image first to avoid FPDI parser issues
                if ($templateIsPdf) {
                    $convertedImagePath = storage_path('app/temp_rx_template_' . $clinic->id . '_converted.png');
                    $pdfConverted = false;

                    // Try Imagick first
                    if (class_exists('Imagick')) {
                        try {
                            $imagick = new \Imagick();
                            $imagick->setResolution(150, 150);
                            $imagick->readImage($templateLocalPath . '[0]'); // first page only
                            $imagick->setImageFormat('png');
                            $imagick->setImageCompressionQuality(95);
                            $imagick->writeImage($convertedImagePath);
                            $imagick->clear();
                            $imagick->destroy();
                            if (file_exists($convertedImagePath) && filesize($convertedImagePath) > 0) {
                                $pdfConverted = true;
                                $pdfConvertedImagePath = $convertedImagePath;
                                \Log::info('RX PDF: converted PDF template to image via Imagick', [
                                    'size' => filesize($convertedImagePath),
                                ]);
                            }
                        } catch (\Exception $imgE) {
                            \Log::warning('RX PDF: Imagick conversion failed', ['error' => $imgE->getMessage()]);
                        }
                    }

                    // Fallback: try Ghostscript via shell
                    if (!$pdfConverted) {
                        $gsCmd = 'gs -sDEVICE=png16m -dNOPAUSE -dBATCH -dFirstPage=1 -dLastPage=1 -r150 -sOutputFile=' .
                            escapeshellarg($convertedImagePath) . ' ' . escapeshellarg($templateLocalPath) . ' 2>&1';
                        @exec($gsCmd, $gsOutput, $gsReturn);
                        if ($gsReturn === 0 && file_exists($convertedImagePath) && filesize($convertedImagePath) > 0) {
                            $pdfConverted = true;
                            $pdfConvertedImagePath = $convertedImagePath;
                            \Log::info('RX PDF: converted PDF template to image via Ghostscript');
                        } else {
                            \Log::warning('RX PDF: Ghostscript conversion failed', [
                                'returnCode' => $gsReturn,
                                'output' => implode("\n", $gsOutput ?? []),
                            ]);
                        }
                    }

                    if ($pdfConverted) {
                        // Use converted image as background instead of SetDocTemplate
                        $templateIsPdf = false;
                        $templateLocalPath = $convertedImagePath;
                    } else {
                        // Last resort: try SetDocTemplate (may fail with compressed PDFs)
                        \Log::info('RX PDF: attempting SetDocTemplate as last resort');
                        $mpdf->SetDocTemplate($templateLocalPath, true);
                    }
                }

                $maxMedicines = $rxSettings['max_medicines'] ?? 12;
                $medicines = $prescription->medicines->take($maxMedicines);
                $templateImagePath = $templateIsPdf ? null : $templateLocalPath;
                $html = view('simple-prescriptions.pdf-custom-template', compact('prescription', 'templateImagePath', 'rxSettings', 'medicines'))->render();
            } else {
                \Log::info('RX PDF: rendering default template');
                $html = view('simple-prescriptions.pdf', compact('prescription'))->render();
            }

            $mpdf->WriteHTML($html);
        } catch (\Exception $e) {
            \Log::error('RX PDF rendering failed', [
                'prescription_id' => $id,
                'clinic_id' => $user->clinic_id,
                'custom_template' => $useCustomTemplate,
                'error' => $e->getMessage(),
            ]);

            // Clean up temp files before fallback
            if ($templateLocalPath && file_exists($templateLocalPath)) {
                @unlink($templateLocalPath);
            }
            if ($pdfConvertedImagePath && file_exists($pdfConvertedImagePath)) {
                @unlink($pdfConvertedImagePath);
            }

            // Fall back to default template if custom template failed
            if ($useCustomTemplate) {
                $mpdfConfig['margin_top'] = 15;
                $mpdfConfig['margin_bottom'] = 15;
                $mpdfConfig['margin_left'] = 15;
                $mpdfConfig['margin_right'] = 15;
                $mpdf = new \Mpdf\Mpdf($mpdfConfig);
                $html = view('simple-prescriptions.pdf', compact('prescription'))->render();
                $mpdf->WriteHTML($html);
            } else {
                throw $e;
            }
        }

        // Clean up temp template files
        if ($templateLocalPath && file_exists($templateLocalPath)) {
            @unlink($templateLocalPath);
        }
        if ($pdfConvertedImagePath && file_exists($pdfConvertedImagePath)) {
            @unlink($pdfConvertedImagePath);
        }
        // Also clean original PDF temp file if we converted it
        $origPdfTemp = storage_path('app/temp_rx_template_' . ($clinic->id ?? 0) . '.pdf');
        if (file_exists($origPdfTemp)) {
            @unlink($origPdfTemp);
        }

        $filename = 'prescription-' . $prescription->prescription_number . '.pdf';

        return response()->streamDownload(function() use ($mpdf) {
            echo $mpdf->Output('', 'S');
        }, $filename, ['Content-Type' => 'application/pdf']);
    }

    public function print($id)
    {
        $user = Auth::user();

        // Get tenant clinic IDs for cross-clinic access
        $tenantClinicIds = $user->clinic ? $user->clinic->getTenantClinicIds() : [$user->clinic_id];

        // Pharmacists and super admins can view prescriptions from all tenant clinics
        if ($user->role === 'pharmacist' || $user->isSuperAdmin()) {
            $prescription = SimplePrescription::with(['patient', 'doctor', 'medicines', 'clinic'])
                ->whereIn('clinic_id', $tenantClinicIds)
                ->findOrFail($id);
        } else {
            $prescription = SimplePrescription::with(['patient', 'doctor', 'medicines', 'clinic'])
                ->forClinic($user->clinic_id)
                ->findOrFail($id);

            // Authorization: Only allow printing own prescriptions for regular doctors
            if (!$user->isClinicAdmin() && $prescription->doctor_id !== $user->id) {
                abort(403, 'You can only print your own prescriptions.');
            }
        }

        return view('simple-prescriptions.print', compact('prescription'));
    }

    /**
     * Render the prescription on a thermal-printer-friendly page (58mm / 80mm).
     * The QR code encodes a signed public URL so patients can re-open the
     * prescription details on their phone.
     */
    public function thermal(Request $request, $id, ThermalReceiptService $thermal)
    {
        $user = Auth::user();
        $tenantClinicIds = $user->clinic ? $user->clinic->getTenantClinicIds() : [$user->clinic_id];

        if ($user->role === 'pharmacist' || $user->isSuperAdmin()) {
            $prescription = SimplePrescription::with(['patient', 'doctor', 'medicines', 'clinic'])
                ->whereIn('clinic_id', $tenantClinicIds)
                ->findOrFail($id);
        } else {
            $prescription = SimplePrescription::with(['patient', 'doctor', 'medicines', 'clinic'])
                ->forClinic($user->clinic_id)
                ->findOrFail($id);

            if (!$user->isClinicAdmin() && $prescription->doctor_id !== $user->id) {
                abort(403, 'You can only print your own prescriptions.');
            }
        }

        $widthMm = (int) $request->query('width', 0);
        if (!in_array($widthMm, ThermalReceiptService::ALLOWED_WIDTHS, true)) {
            $widthMm = ThermalReceiptService::DEFAULT_WIDTH;
        }

        $payload = $thermal->buildForPrescription($prescription, $widthMm);
        $payload['auto_print'] = $request->boolean('auto', true);

        return view('receipts.thermal', $payload);
    }

    /**
     * Convert prescription to sale (Dispense medicines and update inventory).
     */
    public function convertToSale(Request $request, $id)
    {
        $user = Auth::user();

        // Only pharmacists and admins can dispense
        if (!in_array($user->role, ['pharmacist', 'admin', 'super_admin'])) {
            abort(403, 'Only pharmacists and admins can dispense prescriptions.');
        }

        // Get tenant clinic IDs for cross-clinic access
        $tenantClinicIds = $user->clinic ? $user->clinic->getTenantClinicIds() : [$user->clinic_id];

        // Load prescription with medicines
        if ($user->role === 'pharmacist' || $user->isSuperAdmin()) {
            $prescription = SimplePrescription::with(['patient', 'doctor', 'medicines'])
                ->whereIn('clinic_id', $tenantClinicIds)
                ->findOrFail($id);
        } else {
            $prescription = SimplePrescription::with(['patient', 'doctor', 'medicines'])
                ->where('clinic_id', $user->clinic_id)
                ->findOrFail($id);
        }

        // Check if already dispensed
        if ($prescription->is_dispensed) {
            return back()->with('error', 'This prescription has already been dispensed on ' .
                $prescription->dispensed_at->format('M d, Y \a\t H:i') . ' by ' .
                ($prescription->dispenser->name ?? 'Unknown'));
        }

        // Check if prescription is active
        if ($prescription->status !== 'active') {
            return back()->with('error', 'Only active prescriptions can be dispensed. Current status: ' . $prescription->status);
        }

        $request->validate([
            'payment_method' => 'required|in:cash,card,credit,insurance,other',
            'print_after_dispense' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $unavailableMedicines = [];
            $insufficientStock = [];
            $matchedItems = [];
            $subtotal = 0.0;

            // Single resolution pass — locks each matched stock row so a parallel
            // sale on the same clinic cannot oversell while we dispense.
            foreach ($prescription->medicines as $prescribedMedicine) {
                $medicine = Medicine::where('clinic_id', $prescription->clinic_id)
                    ->where(function ($query) use ($prescribedMedicine) {
                        $query->where('name', 'LIKE', '%' . $prescribedMedicine->medicine_name . '%')
                            ->orWhere('generic_name', 'LIKE', '%' . $prescribedMedicine->medicine_name . '%');
                    })
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->first();

                if (!$medicine) {
                    $unavailableMedicines[] = $prescribedMedicine->medicine_name;
                    continue;
                }

                $quantityNeeded = (float) ($prescribedMedicine->quantity ?? 1);
                if ((float) $medicine->stock_quantity < $quantityNeeded) {
                    $insufficientStock[] = [
                        'name' => $medicine->name,
                        'needed' => $quantityNeeded,
                        'available' => (float) $medicine->stock_quantity,
                    ];
                    continue;
                }

                $unitPrice = (float) ($medicine->selling_price ?? 0);
                $lineTotal = round($quantityNeeded * $unitPrice, 2);
                $subtotal += $lineTotal;

                $matchedItems[] = [
                    'medicine'   => $medicine,
                    'qty'        => $quantityNeeded,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ];
            }

            if (!empty($unavailableMedicines)) {
                DB::rollBack();
                return back()->with('error', 'The following medicines are not found in inventory: ' .
                    implode(', ', $unavailableMedicines) . '. Please add them to inventory first.');
            }

            if (!empty($insufficientStock)) {
                DB::rollBack();
                $errorMessage = 'Insufficient stock for the following medicines:<br>';
                foreach ($insufficientStock as $item) {
                    $errorMessage .= "• {$item['name']}: Need {$item['needed']} units, only {$item['available']} available<br>";
                }
                return back()->with('error', $errorMessage);
            }

            $subtotal = round($subtotal, 2);

            // Create the sale invoice that ties all dispensed items together.
            // clinic_id mirrors the prescription's clinic so stock and revenue
            // attribution land on the same tenant.
            $invoice = MedicineSaleInvoice::create([
                'clinic_id'      => $prescription->clinic_id,
                'user_id'        => $user->id,
                'patient_id'     => $prescription->patient_id,
                'invoice_number' => MedicineSaleInvoice::generateInvoiceNumber($prescription->clinic_id),
                'payment_method' => $request->payment_method,
                'subtotal'       => $subtotal,
                'discount'       => 0,
                'tax'            => 0,
                'total'          => $subtotal,
                'paid_amount'    => $subtotal,
                'notes'          => 'Dispensed from prescription #' . $prescription->prescription_number,
                'sold_at'        => now(),
            ]);

            foreach ($matchedItems as $item) {
                $stockBefore = (float) $item['medicine']->stock_quantity;

                MedicineTransaction::create([
                    'medicine_id'              => $item['medicine']->id,
                    'clinic_id'                => $prescription->clinic_id,
                    'user_id'                  => $user->id,
                    'medicine_sale_invoice_id' => $invoice->id,
                    'type'                     => 'sale',
                    'quantity'                 => $item['qty'],
                    'unit_price'               => $item['unit_price'],
                    'total_amount'             => $item['line_total'],
                    'reference_number'         => $invoice->invoice_number,
                    'patient_id'               => $prescription->patient_id,
                    'payment_method'           => $request->payment_method,
                    'stock_before'             => $stockBefore,
                    'stock_after'              => $stockBefore - $item['qty'],
                    'notes'                    => 'Dispensed from prescription #' . $prescription->prescription_number,
                    'transaction_date'         => now(),
                ]);

                $item['medicine']->decrement('stock_quantity', $item['qty']);
            }

            $prescription->update([
                'is_dispensed'       => true,
                'dispensed_at'       => now(),
                'dispensed_by'       => $user->id,
                'dispense_reference' => $invoice->invoice_number,
                'status'             => 'completed',
            ]);

            DB::commit();

            $redirect = redirect()
                ->route('simple-prescriptions.show', $prescription)
                ->with('success', 'Prescription dispensed successfully! Invoice ' . $invoice->invoice_number .
                    ' · Total ' . number_format($subtotal, 2));

            // When the cashier ticked "Print thermal receipt after dispensing"
            // (default ON), flash a URL the show page will pop open in a new tab.
            if ($request->boolean('print_after_dispense', true)) {
                $redirect->with('auto_print_url', route('medicines.sales.thermal', [
                    'invoice' => $invoice->id,
                    'width'   => 80,
                ]));
            }

            return $redirect;

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Prescription dispense failed', [
                'prescription_id' => $prescription->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Failed to dispense prescription: ' . $e->getMessage());
        }
    }

    /**
     * Generate a demo prescription PDF using the clinic's custom template settings
     * for preview purposes on the prescription template settings page.
     */
    public function templatePreview(Request $request)
    {
        $user = Auth::user();
        $clinic = $user->clinic ? Clinic::find($user->clinic_id) : null;

        if (!$clinic) {
            return response()->json(['error' => __('No clinic found.')], 404);
        }

        $templatePath = $clinic->getSetting('rx_template_path', '');
        $useCustomTemplate = $clinic->getSetting('rx_template_enabled', false) && $templatePath;
        $paperSize = $clinic->getSetting('rx_paper_size', 'A4');

        // Create demo prescription data
        $demoPatient = new \stdClass();
        $demoPatient->first_name = 'Ali';
        $demoPatient->last_name = 'Adnan';
        $demoPatient->date_of_birth = now()->subYears(25);
        $demoPatient->latest_weight_kg = 72;
        $demoPatient->latest_height = 175;
        $demoPatient->age_formatted = '25 Years';
        $demoPatient->gender = 'male';
        $demoPatient->weight = 72;
        $demoPatient->height = 175;

        $demoDoctor = new \stdClass();
        $demoDoctor->first_name = $user->first_name ?? 'Dr.';
        $demoDoctor->last_name = $user->last_name ?? 'Demo';
        $demoDoctor->email = $user->email ?? 'doctor@concure.app';
        $demoDoctor->specialization = 'General Medicine';
        $demoDoctor->specialization_font_size = null;
        $demoDoctor->scientific_degree = null;
        $demoDoctor->medical_degrees = null;
        $demoDoctor->medical_degrees_font_size = null;
        $demoDoctor->educational_institution = null;
        $demoDoctor->professional_credentials = null;
        $demoDoctor->professional_credentials_font_size = null;
        $demoDoctor->phone = null;

        $demoClinic = new \stdClass();
        $demoClinic->name = $clinic->name ?? 'ConCure Clinic';

        $demoPrescription = new \stdClass();
        $demoPrescription->prescription_number = 'RX-DEMO-001';
        $demoPrescription->diagnosis = 'Upper Respiratory Tract Infection';
        $demoPrescription->notes = 'Take with food. Rest well and drink plenty of fluids.';
        $demoPrescription->prescribed_date = now();
        $demoPrescription->patient = $demoPatient;
        $demoPrescription->doctor = $demoDoctor;
        $demoPrescription->clinic = $demoClinic;
        $demoPrescription->clinic_id = $clinic->id;
        $demoPrescription->medicines = $demoMedicines;

        // Demo medicines
        $demoMedicines = collect([
            (object) ['medicine_name' => 'Amoxicillin 500mg Capsule', 'dosage' => '1 capsule', 'frequency' => '3 times daily', 'duration' => '7 days', 'instructions' => 'Take after meals'],
            (object) ['medicine_name' => 'Paracetamol 500mg Tablet', 'dosage' => '2 tablets', 'frequency' => 'Every 6 hours as needed', 'duration' => '5 days', 'instructions' => 'For fever or pain. Max 8 per day.'],
            (object) ['medicine_name' => 'Vitamin C 1000mg', 'dosage' => '1 tablet', 'frequency' => 'Once daily', 'duration' => '14 days', 'instructions' => 'Take in the morning'],
            (object) ['medicine_name' => 'Saline Nasal Spray', 'dosage' => '2 sprays', 'frequency' => '3 times daily', 'duration' => '7 days', 'instructions' => 'Each nostril'],
        ]);

        $rxSettings = [
            'medicine_x' => (int) ($request->rx_medicine_x ?? $clinic->getSetting('rx_medicine_x', 40)),
            'medicine_y' => (int) ($request->rx_medicine_y ?? $clinic->getSetting('rx_medicine_y', 200)),
            'font_size' => (int) ($request->rx_font_size ?? $clinic->getSetting('rx_font_size', 11)),
            'line_spacing' => (int) ($request->rx_line_spacing ?? $clinic->getSetting('rx_line_spacing', 22)),
            'max_medicines' => (int) ($request->rx_max_medicines ?? $clinic->getSetting('rx_max_medicines', 12)),
            'notes_y_bottom' => (int) ($request->rx_notes_y_bottom ?? $clinic->getSetting('rx_notes_y_bottom', 60)),
            'notes_x_right' => (int) ($request->rx_notes_x_right ?? $clinic->getSetting('rx_notes_x_right', 40)),
        ];

        $mpdfConfig = ['default_font' => 'dejavusans', 'tempDir' => storage_path('mpdf/temp')];

        if ($paperSize === 'A5') {
            $mpdfConfig['format'] = [148, 210];
        } elseif ($paperSize === 'B5') {
            $mpdfConfig['format'] = [176, 250];
        } else {
            $mpdfConfig['format'] = 'A4';
        }

        $mpdf = new \Mpdf\Mpdf($mpdfConfig);

        // Configure for Kurdish/Arabic support
        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont = true;

        $templateLocalPath = null;
        $templateIsPdf = false;
        $pdfConvertedImagePath = null;

        if ($useCustomTemplate && $templatePath) {
            try {
                $disk = config('filesystems.default');
                $templateFullPath = Storage::disk($disk)->path($templatePath);
                if (file_exists($templateFullPath)) {
                    $templateLocalPath = storage_path('app/temp_rx_preview_' . $clinic->id . '.' . pathinfo($templateFullPath, PATHINFO_EXTENSION));
                    copy($templateFullPath, $templateLocalPath);
                    $templateIsPdf = strtolower(pathinfo($templateFullPath, PATHINFO_EXTENSION)) === 'pdf';

                    if ($templateIsPdf) {
                        $convertedImagePath = storage_path('app/temp_rx_preview_' . $clinic->id . '_converted.png');
                        if (class_exists('\Imagick')) {
                            $imagick = new \Imagick();
                            $imagick->setResolution(150, 150);
                            $imagick->readImage($templateLocalPath . '[0]');
                            $imagick->setImageFormat('png');
                            $imagick->writeImage($convertedImagePath);
                            $imagick->clear();
                            $imagick->destroy();
                            $templateLocalPath = $convertedImagePath;
                        }
                    }
                }
            } catch (\Exception $e) {
                $useCustomTemplate = false;
            }
        }

        if ($useCustomTemplate && $templateLocalPath) {
            $medicines = $demoMedicines->take($rxSettings['max_medicines']);
            $templateImagePath = $templateIsPdf ? null : $templateLocalPath;
            $html = view('simple-prescriptions.pdf-custom-template', [
                'prescription' => $demoPrescription,
                'templateImagePath' => $templateImagePath,
                'rxSettings' => $rxSettings,
                'medicines' => $medicines,
            ])->render();
        } else {
            $html = view('simple-prescriptions.pdf', ['prescription' => $demoPrescription])->render();
        }

        $mpdf->WriteHTML($html);

        // Cleanup
        if ($templateLocalPath && file_exists($templateLocalPath)) {
            @unlink($templateLocalPath);
        }
        if ($pdfConvertedImagePath && file_exists($pdfConvertedImagePath)) {
            @unlink($pdfConvertedImagePath);
        }
        $origPdfTemp = storage_path('app/temp_rx_preview_' . ($clinic->id ?? 0) . '.pdf');
        if (file_exists($origPdfTemp)) {
            @unlink($origPdfTemp);
        }

        return response($mpdf->Output('', 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="prescription-template-preview.pdf"',
        ]);
    }
}
