<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\PatientCheckup;
use App\Models\Prescription;
use App\Models\Appointment;
use App\Models\PatientImage;
use App\Models\ReportTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\CustomTemplateService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class PatientReportController extends Controller
{
    /**
     * Generate comprehensive patient report
     */
    public function generateReport(Request $request, Patient $patient)
    {
        $this->authorizePatientAccess($patient);

        // Get date range from request or default to last 6 months
        $dateFrom = $request->get('date_from', Carbon::now()->subMonths(6)->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));

        // Collect all patient data
        $reportData = $this->collectPatientData($patient, $dateFrom, $dateTo);

        // Determine output format
        $format = $request->get('format', 'html');

        if ($format === 'pdf') {
            return $this->generatePdfReport($patient, $reportData, $dateFrom, $dateTo);
        }

        return $this->generateHtmlReport($patient, $reportData, $dateFrom, $dateTo);
    }

    /**
     * Show blank report form
     */
    public function showBlankReportForm(Request $request, Patient $patient)
    {
        $this->authorizePatientAccess($patient);

        $user = Auth::user();

        // Load custom templates for this clinic
        $customTemplates = ReportTemplate::byClinic($user->clinic_id)
            ->active()
            ->orderBy('name')
            ->get();

        return view('reports.blank-report-form', [
            'patient' => $patient,
            'doctor' => $user,
            'clinic' => $user->clinic,
            'customTemplates' => $customTemplates,
            'templateIcons' => ReportTemplate::ICONS,
        ]);
    }

    /**
     * Preview blank report before saving
     */
    public function previewBlankReport(Request $request, Patient $patient)
    {
        $this->authorizePatientAccess($patient);

        $request->validate([
            'report_title' => 'nullable|string|max:255',
            'notes' => 'required|string',
        ]);

        $user = Auth::user();

        $reportData = [
            'patient' => $patient,
            'doctor' => $user,
            'clinic' => $user->clinic,
            'report_title' => $request->input('report_title', 'Medical Report'),
            'notes' => $request->input('notes'),
            'generated_date' => Carbon::now(),
        ];

        return view('reports.blank-report-preview', $reportData);
    }

    /**
     * Generate and save blank report with notes
     */
    public function generateBlankReport(Request $request, Patient $patient)
    {
        $this->authorizePatientAccess($patient);

        $request->validate([
            'report_title' => 'nullable|string|max:255',
            'notes' => 'required|string',
        ]);

        $user = Auth::user();

        $reportData = [
            'patient' => $patient,
            'doctor' => $user,
            'clinic' => $user->clinic,
            'report_title' => $request->input('report_title', 'Medical Report'),
            'notes' => $request->input('notes'),
            'generated_date' => Carbon::now(),
        ];

        $filename = 'blank-report-' . $patient->patient_id . '-' . Carbon::now()->format('Y-m-d-His') . '.pdf';

        // Check for custom template
        $forceCustom = $request->query('template') === 'custom';
        $clinic = $user->clinic;
        $templateData = $clinic ? CustomTemplateService::prepareTemplate($clinic, 'blank_report', $forceCustom) : null;

        if ($templateData) {
            // Use mPDF with custom template
            $mpdf = CustomTemplateService::createMpdf($templateData);
            $reportData['templateImagePath'] = $templateData['imagePath'];
            $reportData['tplSettings'] = $templateData['settings'];
            $html = view('reports.blank-report-custom-template', $reportData)->render();
            $mpdf->WriteHTML($html);
            $pdfContent = $mpdf->Output('', 'S');
            CustomTemplateService::cleanup($templateData);
        } else {
            // Use DomPDF (default)
            $pdf = Pdf::loadView('reports.patient-blank-report-pdf', $reportData);
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => true,
                'defaultFont' => 'Arial',
                'margin_top' => 10,
                'margin_right' => 10,
                'margin_bottom' => 10,
                'margin_left' => 10,
            ]);
            $pdfContent = $pdf->output();
        }

        // Save PDF to patient files
        $path = 'patients/' . $patient->id . '/files/' . $filename;
        \Storage::disk('public')->put($path, $pdfContent);

        // Create patient file record
        \App\Models\PatientFile::create([
            'patient_id' => $patient->id,
            'original_name' => $filename,
            'file_name' => $filename,
            'file_path' => $path,
            'file_type' => 'application/pdf',
            'file_size' => \Storage::disk('public')->size($path),
            'category' => 'medical_report',
            'description' => $request->input('report_title', 'Blank Medical Report'),
            'uploaded_by' => $user->id,
        ]);

        // Return PDF for download
        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
    
    /**
     * Generate HTML report view
     */
    private function generateHtmlReport(Patient $patient, array $reportData, string $dateFrom, string $dateTo)
    {
        return view('reports.patient-report', compact('patient', 'reportData', 'dateFrom', 'dateTo'));
    }
    
    /**
     * Generate PDF report
     */
    private function generatePdfReport(Patient $patient, array $reportData, string $dateFrom, string $dateTo)
    {
        $pdf = Pdf::loadView('reports.patient-report-pdf', compact('patient', 'reportData', 'dateFrom', 'dateTo'));
        
        // Configure PDF settings
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => true,
            'defaultFont' => 'Arial',
            'margin_top' => 10,
            'margin_right' => 10,
            'margin_bottom' => 10,
            'margin_left' => 10,
        ]);
        
        $filename = 'patient-report-' . $patient->patient_id . '-' . Carbon::now()->format('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }
    
    /**
     * Collect comprehensive patient data for report
     */
    private function collectPatientData(Patient $patient, string $dateFrom, string $dateTo): array
    {
        $dateFromCarbon = Carbon::parse($dateFrom)->startOfDay();
        $dateToCarbon = Carbon::parse($dateTo)->endOfDay();
        
        // Get checkups in date range
        $checkups = PatientCheckup::where('patient_id', $patient->id)
            ->whereBetween('checkup_date', [$dateFromCarbon, $dateToCarbon])
            ->with('recorder')
            ->orderBy('checkup_date', 'desc')
            ->get();
        
        // Get prescriptions in date range
        $prescriptions = Prescription::where('patient_id', $patient->id)
            ->whereBetween('created_at', [$dateFromCarbon, $dateToCarbon])
            ->with(['medicines', 'prescriber'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get appointments in date range
        $appointments = Appointment::where('patient_id', $patient->id)
            ->whereBetween('appointment_datetime', [$dateFromCarbon, $dateToCarbon])
            ->with('doctor')
            ->orderBy('appointment_datetime', 'desc')
            ->get();
        
        // Calculate vital signs trends
        $vitalTrends = $this->calculateVitalTrends($checkups);
        
        // Get latest checkup for current status
        $latestCheckup = $checkups->first();
        
        // Calculate BMI history
        $bmiHistory = $this->calculateBmiHistory($checkups);

        // Get images in date range
        $images = PatientImage::where('patient_id', $patient->id)
            ->whereBetween('created_at', [$dateFromCarbon, $dateToCarbon])
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'checkups' => $checkups,
            'prescriptions' => $prescriptions,
            'appointments' => $appointments,
            'vital_trends' => $vitalTrends,
            'latest_checkup' => $latestCheckup,
            'bmi_history' => $bmiHistory,
            'images' => $images,
            'summary' => [
                'total_checkups' => $checkups->count(),
                'total_prescriptions' => $prescriptions->count(),
                'total_appointments' => $appointments->count(),
                'date_range' => [
                    'from' => $dateFromCarbon,
                    'to' => $dateToCarbon,
                ],
            ],
        ];
    }
    
    /**
     * Calculate vital signs trends
     */
    private function calculateVitalTrends($checkups): array
    {
        $trends = [
            'weight' => [],
            'blood_pressure_systolic' => [],
            'blood_pressure_diastolic' => [],
            'heart_rate' => [],
            'temperature' => [],
            'blood_sugar' => [],
        ];
        
        foreach ($checkups as $checkup) {
            $date = $checkup->checkup_date->format('Y-m-d');
            
            if ($checkup->weight) {
                $trends['weight'][] = ['date' => $date, 'value' => $checkup->weight];
            }
            
            if ($checkup->blood_pressure) {
                $bp = explode('/', $checkup->blood_pressure);
                if (count($bp) === 2) {
                    $trends['blood_pressure_systolic'][] = ['date' => $date, 'value' => (int)$bp[0]];
                    $trends['blood_pressure_diastolic'][] = ['date' => $date, 'value' => (int)$bp[1]];
                }
            }
            
            if ($checkup->heart_rate) {
                $trends['heart_rate'][] = ['date' => $date, 'value' => $checkup->heart_rate];
            }
            
            if ($checkup->temperature) {
                $trends['temperature'][] = ['date' => $date, 'value' => $checkup->temperature];
            }
            
            if ($checkup->blood_sugar) {
                $trends['blood_sugar'][] = ['date' => $date, 'value' => $checkup->blood_sugar];
            }
        }
        
        return $trends;
    }
    
    /**
     * Calculate BMI history
     */
    private function calculateBmiHistory($checkups): array
    {
        $bmiHistory = [];
        
        foreach ($checkups as $checkup) {
            if ($checkup->weight && $checkup->height) {
                $heightInMeters = $checkup->height / 100;
                $bmi = round($checkup->weight / ($heightInMeters * $heightInMeters), 1);
                
                $bmiHistory[] = [
                    'date' => $checkup->checkup_date->format('Y-m-d'),
                    'bmi' => $bmi,
                    'weight' => $checkup->weight,
                    'height' => $checkup->height,
                ];
            }
        }
        
        return $bmiHistory;
    }
    
    /**
     * Store a new report template (AJAX).
     */
    public function storeTemplate(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'content' => 'required|string',
            'icon' => 'nullable|string|max:100',
        ]);

        $user = Auth::user();

        $template = ReportTemplate::create([
            'clinic_id' => $user->clinic_id,
            'created_by' => $user->id,
            'name' => $request->input('name'),
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'icon' => $request->input('icon', 'fas fa-file-alt'),
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Template saved successfully.',
            'template' => $template,
        ]);
    }

    /**
     * Update a report template (AJAX).
     */
    public function updateTemplate(Request $request, ReportTemplate $reportTemplate)
    {
        $user = Auth::user();

        // Ensure same clinic
        if ($reportTemplate->clinic_id !== $user->clinic_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'content' => 'required|string',
            'icon' => 'nullable|string|max:100',
        ]);

        $reportTemplate->update([
            'name' => $request->input('name'),
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'icon' => $request->input('icon', $reportTemplate->icon),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Template updated successfully.',
            'template' => $reportTemplate,
        ]);
    }

    /**
     * Delete a report template (AJAX).
     */
    public function destroyTemplate(ReportTemplate $reportTemplate)
    {
        $user = Auth::user();

        // Ensure same clinic
        if ($reportTemplate->clinic_id !== $user->clinic_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $reportTemplate->delete();

        return response()->json([
            'success' => true,
            'message' => 'Template deleted successfully.',
        ]);
    }

    /**
     * Authorize access to patient
     */
    private function authorizePatientAccess(Patient $patient): void
    {
        // DEVELOPMENT MODE: Completely disable patient access authorization
        if (config('app.debug') || env('DISABLE_PERMISSIONS', true)) {
            return; // Allow all access during development
        }

        $user = Auth::user();

        // Users can only access patients in their clinic
        if ($patient->clinic_id !== $user->clinic_id) {
            abort(403, 'Unauthorized access to patient.');
        }

        // Permission-based access only
        if (!$user->hasPermission('patients_view') &&
            !$user->canManagePatients()) {
            abort(403, 'Insufficient permissions to view patients.');
        }
    }
}
