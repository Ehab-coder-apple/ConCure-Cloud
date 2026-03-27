<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\PediatricDrug;
use App\Models\PediatricDrugForm;
use App\Models\PediatricDosageRule;
use App\Models\PediatricPrescription;
use App\Services\PediatricDoseCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PediatricMedicationController extends Controller
{
    protected PediatricDoseCalculatorService $calculator;

    public function __construct(PediatricDoseCalculatorService $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * Dose Calculator page.
     */
    public function calculator(Request $request)
    {
        $clinicId = Auth::user()->clinic_id;
        $minDate = now()->subYears(20)->startOfDay();
        $patients = Patient::where('clinic_id', $clinicId)
            ->where('is_active', true)
            ->whereNotNull('date_of_birth')
            ->where('date_of_birth', '>=', $minDate)
            ->where('date_of_birth', '!=', '0000-00-00')
            ->orderBy('first_name')
            ->get();
        $drugs = PediatricDrug::active()->with('forms')->orderBy('generic_name')->get();

        return view('pediatric.medication.calculator', compact('patients', 'drugs'));
    }

    /**
     * AJAX: Calculate dose (single).
     */
    public function calculateDose(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'drug_id' => 'required|exists:pediatric_drugs,id',
            'form_id' => 'required|exists:pediatric_drug_forms,id',
        ]);

        $patient = Patient::findOrFail($request->patient_id);
        $drug = PediatricDrug::with('forms', 'dosageRules')->findOrFail($request->drug_id);
        $form = PediatricDrugForm::findOrFail($request->form_id);

        $result = $this->calculator->calculate($patient, $drug, $form);

        return response()->json($result);
    }

    /**
     * AJAX: Bulk calculate doses for multiple drugs.
     */
    public function bulkCalculate(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'items' => 'required|array|min:1',
            'items.*.drug_id' => 'required|exists:pediatric_drugs,id',
            'items.*.form_id' => 'required|exists:pediatric_drug_forms,id',
        ]);

        $patient = Patient::findOrFail($request->patient_id);
        $results = [];

        foreach ($request->items as $i => $item) {
            $drug = PediatricDrug::with('forms', 'dosageRules')->findOrFail($item['drug_id']);
            $form = PediatricDrugForm::findOrFail($item['form_id']);

            $calc = $this->calculator->calculate($patient, $drug, $form);
            $calc['drug_name'] = $drug->generic_name . ($drug->brand_name ? ' (' . $drug->brand_name . ')' : '');
            $calc['form_label'] = $form->display_label;
            $calc['drug_id'] = $drug->id;
            $calc['form_id'] = $form->id;
            $calc['index'] = $i;

            $results[] = $calc;
        }

        return response()->json([
            'patient_id' => $patient->id,
            'weight_kg' => $patient->latest_weight_kg,
            'age_months' => $patient->age_months,
            'results' => $results,
        ]);
    }

    /**
     * AJAX: Validate a custom dose.
     */
    public function validateDose(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'drug_id' => 'required|exists:pediatric_drugs,id',
            'form_id' => 'required|exists:pediatric_drug_forms,id',
            'dose_mg' => 'required|numeric|min:0',
            'frequency_per_day' => 'required|integer|min:1',
        ]);

        $patient = Patient::findOrFail($request->patient_id);
        $drug = PediatricDrug::findOrFail($request->drug_id);
        $form = PediatricDrugForm::findOrFail($request->form_id);

        $weightKg = $patient->latest_weight_kg;
        $safety = $this->calculator->validateCustomDose(
            (float) $request->dose_mg, $patient, $drug, $form, (int) $request->frequency_per_day
        );

        // Also calculate ml equivalent and mg/kg
        $doseMg = (float) $request->dose_mg;
        $doseMl = $form->convertMgToMl($doseMg);
        $mgPerKg = $weightKg ? round($doseMg / $weightKg, 2) : null;
        $freq = (int) $request->frequency_per_day;
        $dailyDoseMg = round($doseMg * $freq, 2);
        $dailyMgPerKg = $weightKg ? round($dailyDoseMg / $weightKg, 2) : null;

        // Get rule limits for UI display
        $rule = $drug->findDosageRule($patient->age_months, $weightKg);
        $maxDailyMg = $rule?->max_daily_mg;
        $maxDailyMgPerKg = ($maxDailyMg && $weightKg) ? round($maxDailyMg / $weightKg, 2) : null;

        return response()->json([
            'safety' => $safety,
            'dose_ml' => $doseMl,
            'mg_per_kg' => $mgPerKg,
            'daily_dose_mg' => $dailyDoseMg,
            'daily_mg_per_kg' => $dailyMgPerKg,
            'max_daily_mg' => $maxDailyMg,
            'max_daily_mg_per_kg' => $maxDailyMgPerKg,
            'frequency_per_day' => $freq,
        ]);
    }

    /**
     * Store a pediatric prescription.
     */
    public function storePrescription(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'drug_id' => 'required|exists:pediatric_drugs,id',
            'form_id' => 'required|exists:pediatric_drug_forms,id',
            'dose_mg' => 'required|numeric|min:0.01',
            'frequency_per_day' => 'required|integer|min:1',
            'duration_days' => 'nullable|integer|min:1',
            'override_reason' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        $patient = Patient::findOrFail($request->patient_id);
        $drug = PediatricDrug::findOrFail($request->drug_id);
        $form = PediatricDrugForm::findOrFail($request->form_id);

        $weightKg = $patient->latest_weight_kg;
        $ageMonths = $patient->age_months;
        $rule = $drug->findDosageRule($ageMonths, $weightKg);

        // Calculate safety
        $safety = $this->calculator->validateCustomDose(
            (float) $request->dose_mg, $patient, $drug, $form, (int) $request->frequency_per_day
        );

        // Require override reason for danger
        if ($safety['status'] === 'danger' && empty($request->override_reason)) {
            return back()->withInput()->with('error', 'Override reason is required when prescribing a dangerous dose.');
        }

        $minMg = $rule ? round($rule->mg_per_kg_min * $weightKg, 2) : null;
        $maxMg = $rule ? round($rule->mg_per_kg_max * $weightKg, 2) : null;

        $prescription = PediatricPrescription::create([
            'patient_id' => $patient->id,
            'drug_id' => $drug->id,
            'form_id' => $form->id,
            'rule_id' => $rule?->id,
            'clinic_id' => Auth::user()->clinic_id,
            'created_by' => Auth::id(),
            'patient_weight_kg' => $weightKg,
            'patient_age_months' => $ageMonths,
            'dose_mg' => $request->dose_mg,
            'dose_ml' => $form->convertMgToMl((float) $request->dose_mg),
            'recommended_dose_min_mg' => $minMg,
            'recommended_dose_max_mg' => $maxMg,
            'frequency_per_day' => $request->frequency_per_day,
            'duration_days' => $request->duration_days,
            'safety_status' => $safety['status'],
            'safety_message' => $safety['message'],
            'override_reason' => $request->override_reason,
            'notes' => $request->notes,
        ]);

        return redirect()->route('pediatric.medication.history', ['patient_id' => $patient->id])
            ->with('success', 'Prescription saved successfully.');
    }

    /**
     * Bulk store prescriptions for multiple drugs.
     */
    public function bulkPrescribe(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'items' => 'required|array|min:1',
            'items.*.drug_id' => 'required|exists:pediatric_drugs,id',
            'items.*.form_id' => 'required|exists:pediatric_drug_forms,id',
            'items.*.dose_mg' => 'required|numeric|min:0.01',
            'items.*.frequency_per_day' => 'required|integer|min:1',
            'items.*.duration_days' => 'nullable|integer|min:1',
            'items.*.override_reason' => 'nullable|string|max:500',
            'items.*.notes' => 'nullable|string|max:1000',
        ]);

        $patient = Patient::findOrFail($request->patient_id);
        $weightKg = $patient->latest_weight_kg;
        $ageMonths = $patient->age_months;
        $saved = 0;

        DB::beginTransaction();
        try {
            foreach ($request->items as $item) {
                $drug = PediatricDrug::findOrFail($item['drug_id']);
                $form = PediatricDrugForm::findOrFail($item['form_id']);
                $rule = $drug->findDosageRule($ageMonths, $weightKg);

                $safety = $this->calculator->validateCustomDose(
                    (float) $item['dose_mg'], $patient, $drug, $form, (int) $item['frequency_per_day']
                );

                if ($safety['status'] === 'danger' && empty($item['override_reason'])) {
                    continue; // Skip danger items without override
                }

                $minMg = $rule ? round($rule->mg_per_kg_min * $weightKg, 2) : null;
                $maxMg = $rule ? round($rule->mg_per_kg_max * $weightKg, 2) : null;

                PediatricPrescription::create([
                    'patient_id' => $patient->id,
                    'drug_id' => $drug->id,
                    'form_id' => $form->id,
                    'rule_id' => $rule?->id,
                    'clinic_id' => Auth::user()->clinic_id,
                    'created_by' => Auth::id(),
                    'patient_weight_kg' => $weightKg,
                    'patient_age_months' => $ageMonths,
                    'dose_mg' => $item['dose_mg'],
                    'dose_ml' => $form->convertMgToMl((float) $item['dose_mg']),
                    'recommended_dose_min_mg' => $minMg,
                    'recommended_dose_max_mg' => $maxMg,
                    'frequency_per_day' => $item['frequency_per_day'],
                    'duration_days' => $item['duration_days'] ?? null,
                    'safety_status' => $safety['status'],
                    'safety_message' => $safety['message'],
                    'override_reason' => $item['override_reason'] ?? null,
                    'notes' => $item['notes'] ?? null,
                ]);
                $saved++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to save prescriptions: ' . $e->getMessage());
        }

        return redirect()->route('pediatric.medication.history', ['patient_id' => $patient->id])
            ->with('success', "{$saved} prescription(s) saved successfully.");
    }

    /**
     * Prescription history.
     */
    public function history(Request $request)
    {
        $clinicId = Auth::user()->clinic_id;
        $minDate = now()->subYears(20)->startOfDay();
        $patients = Patient::where('clinic_id', $clinicId)
            ->where('is_active', true)
            ->whereNotNull('date_of_birth')
            ->where('date_of_birth', '>=', $minDate)
            ->where('date_of_birth', '!=', '0000-00-00')
            ->orderBy('first_name')
            ->get();

        $prescriptions = collect();
        $selectedPatient = null;

        if ($request->has('patient_id')) {
            $selectedPatient = Patient::find($request->patient_id);
            if ($selectedPatient) {
                $prescriptions = PediatricPrescription::where('patient_id', $selectedPatient->id)
                    ->where('clinic_id', $clinicId)
                    ->with(['drug', 'form', 'creator'])
                    ->orderByDesc('created_at')
                    ->get();
            }
        }

        return view('pediatric.medication.history', compact('patients', 'prescriptions', 'selectedPatient'));
    }

    /**
     * Printable prescription view.
     * Accepts ?ids=1,2,3 or ?patient_id=X (prints all for that patient from today).
     */
    public function printPrescription(Request $request)
    {
        $clinicId = Auth::user()->clinic_id;
        $clinic = \App\Models\Clinic::findOrFail($clinicId);
        $doctor = Auth::user();

        if ($request->has('ids')) {
            $ids = array_filter(explode(',', $request->ids));
            $prescriptions = PediatricPrescription::whereIn('id', $ids)
                ->where('clinic_id', $clinicId)
                ->with(['drug', 'form', 'patient', 'creator'])
                ->orderByDesc('created_at')
                ->get();
        } elseif ($request->has('patient_id')) {
            $prescriptions = PediatricPrescription::where('patient_id', $request->patient_id)
                ->where('clinic_id', $clinicId)
                ->whereDate('created_at', now()->toDateString())
                ->with(['drug', 'form', 'patient', 'creator'])
                ->orderByDesc('created_at')
                ->get();
        } else {
            return redirect()->route('pediatric.medication.history')->with('error', 'No prescriptions specified.');
        }

        if ($prescriptions->isEmpty()) {
            return redirect()->back()->with('error', 'No prescriptions found.');
        }

        $patient = $prescriptions->first()->patient;

        return view('pediatric.medication.print', compact('clinic', 'doctor', 'patient', 'prescriptions'));
    }

    /**
     * Drug Admin panel.
     */
    public function drugAdmin()
    {
        $drugs = PediatricDrug::with(['forms', 'dosageRules'])->orderBy('generic_name')->get();

        // Group drugs by category, with uncategorized at end
        $grouped = $drugs->groupBy(fn($d) => filled($d->category) ? $d->category : '__uncategorized__')
            ->sortKeys();

        // Move uncategorized to end
        if ($grouped->has('__uncategorized__')) {
            $uncategorized = $grouped->pull('__uncategorized__');
            $grouped->put('__uncategorized__', $uncategorized);
        }

        // Get distinct existing categories for the Add Drug modal
        $existingCategories = PediatricDrug::whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('pediatric.medication.drug-admin', compact('drugs', 'grouped', 'existingCategories'));
    }

    /**
     * Store a new drug.
     */
    public function storeDrug(Request $request)
    {
        $request->validate([
            'generic_name' => 'required|string|max:255',
            'brand_name' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        PediatricDrug::create(array_merge(
            $request->only('generic_name', 'brand_name', 'category', 'description'),
            ['is_system' => false, 'clinic_id' => Auth::user()->clinic_id]
        ));
        return back()->with('success', 'Drug added successfully.');
    }

    /**
     * Store a drug form.
     */
    public function storeDrugForm(Request $request)
    {
        $request->validate([
            'drug_id' => 'required|exists:pediatric_drugs,id',
            'form' => 'required|string|max:50',
            'concentration' => 'required|string|max:100',
            'concentration_mg' => 'required|numeric|min:0.01',
            'concentration_per_ml' => 'nullable|numeric|min:0.01',
        ]);

        PediatricDrugForm::create($request->only('drug_id', 'form', 'concentration', 'concentration_mg', 'concentration_per_ml'));
        return back()->with('success', 'Drug form added successfully.');
    }

    /**
     * Store a dosage rule.
     */
    public function storeDosageRule(Request $request)
    {
        $request->validate([
            'drug_id' => 'required|exists:pediatric_drugs,id',
            'mg_per_kg_min' => 'required|numeric|min:0',
            'mg_per_kg_max' => 'required|numeric|min:0',
            'max_daily_mg' => 'nullable|numeric|min:0',
            'frequency_per_day' => 'required|integer|min:1',
            'frequency_hours' => 'nullable|integer|min:1',
            'min_age_months' => 'nullable|integer|min:0',
            'max_age_months' => 'nullable|integer|min:0',
            'min_weight_kg' => 'nullable|numeric|min:0',
            'max_weight_kg' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        PediatricDosageRule::create($request->only([
            'drug_id', 'mg_per_kg_min', 'mg_per_kg_max', 'max_daily_mg',
            'frequency_per_day', 'frequency_hours',
            'min_age_months', 'max_age_months', 'min_weight_kg', 'max_weight_kg', 'notes'
        ]));
        return back()->with('success', 'Dosage rule added successfully.');
    }

    /**
     * Delete a drug (and cascade forms/rules).
     * System drugs can only be deleted by superadmin.
     * Tenant drugs can be deleted by users from the same clinic.
     */
    public function destroyDrug(PediatricDrug $drug)
    {
        $user = Auth::user();

        if (!$drug->canBeDeletedBy($user)) {
            return back()->with('error', 'You do not have permission to delete this system drug.');
        }

        $drug->delete();
        return back()->with('success', 'Drug deleted successfully.');
    }

    /**
     * Delete all tenant (non-system) drugs for the current clinic.
     * Superadmin can delete all non-system drugs.
     */
    public function destroyTenantDrugs()
    {
        $user = Auth::user();

        $query = PediatricDrug::where('is_system', false);

        if (!$user->isSuperAdmin()) {
            $query->where('clinic_id', $user->clinic_id);
        }

        $count = $query->count();
        $query->delete();

        return back()->with('success', "$count imported medicine(s) deleted successfully.");
    }

    /**
     * Import page.
     */
    public function importPage()
    {
        return view('pediatric.medication.import');
    }

    /**
     * Download import template (Excel).
     */
    public function downloadTemplate(Request $request)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Drug Import Template');

        $headers = [
            'generic_name', 'brand_name', 'category', 'description',
            'form', 'concentration', 'concentration_mg', 'concentration_per_ml',
            'mg_per_kg_min', 'mg_per_kg_max', 'max_daily_mg',
            'frequency_per_day', 'frequency_hours',
            'min_age_months', 'max_age_months', 'min_weight_kg', 'max_weight_kg', 'notes',
        ];

        // Header row
        $sheet->fromArray($headers, null, 'A1');

        // Style header
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
        $headerRange = "A1:{$lastCol}1";
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF4472C4');
        $sheet->getStyle($headerRange)->getFont()->getColor()->setARGB('FFFFFFFF');

        // Sample data rows
        $samples = [
            ['Paracetamol', 'Calpol', 'Analgesic', 'Pain/fever relief', 'syrup', '120mg/5ml', 120, 5, 10, 15, 4000, 4, 6, 3, 216, null, null, 'Common pediatric analgesic'],
            ['Ibuprofen', 'Brufen', 'NSAID', 'Anti-inflammatory', 'syrup', '100mg/5ml', 100, 5, 5, 10, 1200, 3, 8, 6, 144, null, null, 'Not for <6 months'],
            ['Amoxicillin', 'Amoxil', 'Antibiotic', 'Broad spectrum antibiotic', 'syrup', '250mg/5ml', 250, 5, 25, 50, 3000, 3, 8, 3, 216, null, null, 'First-line antibiotic'],
        ];
        $sheet->fromArray($samples, null, 'A2');

        // Auto-size columns
        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Instructions sheet
        $instrSheet = $spreadsheet->createSheet();
        $instrSheet->setTitle('Instructions');
        $instructions = [
            ['Column', 'Required', 'Description'],
            ['generic_name', 'YES', 'Drug generic name (e.g. Paracetamol)'],
            ['brand_name', 'No', 'Brand/trade name (e.g. Calpol)'],
            ['category', 'No', 'Drug category (e.g. Analgesic, Antibiotic)'],
            ['description', 'No', 'Brief description'],
            ['form', 'No', 'Form type: syrup, tablet, drops, injection, suppository'],
            ['concentration', 'No', 'Human-readable (e.g. 120mg/5ml)'],
            ['concentration_mg', 'No', 'Numeric mg value of concentration'],
            ['concentration_per_ml', 'No', 'Numeric ml value of concentration'],
            ['mg_per_kg_min', 'YES', 'Minimum dose in mg per kg body weight'],
            ['mg_per_kg_max', 'YES', 'Maximum dose in mg per kg body weight'],
            ['max_daily_mg', 'No', 'Maximum total daily dose in mg'],
            ['frequency_per_day', 'No', 'Times per day (e.g. 3)'],
            ['frequency_hours', 'No', 'Hours between doses (e.g. 8)'],
            ['min_age_months', 'No', 'Minimum age in months'],
            ['max_age_months', 'No', 'Maximum age in months'],
            ['min_weight_kg', 'No', 'Minimum weight in kg'],
            ['max_weight_kg', 'No', 'Maximum weight in kg'],
            ['notes', 'No', 'Additional notes'],
        ];
        $instrSheet->fromArray($instructions, null, 'A1');
        $instrSheet->getStyle('A1:C1')->getFont()->setBold(true);
        foreach (['A', 'B', 'C'] as $col) {
            $instrSheet->getColumnDimension($col)->setAutoSize(true);
        }

        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'pediatric_drug_import_template_' . date('Y-m-d') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        return response()->stream(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Preview import from CSV/JSON/Excel.
     */
    public function importPreview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,json,xlsx,xls|max:2048',
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $data = [];
        $errors = [];

        try {
            if ($extension === 'json') {
                $raw = json_decode(file_get_contents($file->getRealPath()), true);
                if (!$raw || !is_array($raw)) {
                    return back()->with('error', 'Invalid JSON format.');
                }
                $data = $raw;
            } elseif (in_array($extension, ['xlsx', 'xls'])) {
                // Excel file
                $spreadsheet = IOFactory::load($file->getRealPath());
                $worksheet = $spreadsheet->getActiveSheet();
                $rows = $worksheet->toArray(null, true, true, false);

                if (count($rows) < 2) {
                    return back()->with('error', 'Excel file is empty or has no data rows.');
                }

                $header = array_map('trim', array_map('strtolower', $rows[0]));
                for ($i = 1; $i < count($rows); $i++) {
                    $row = $rows[$i];
                    // Skip completely empty rows
                    if (empty(array_filter($row, fn($v) => $v !== null && $v !== ''))) {
                        continue;
                    }
                    $item = array_combine($header, array_pad($row, count($header), ''));
                    $data[] = $item;
                }
            } else {
                // CSV
                $handle = fopen($file->getRealPath(), 'r');
                $header = fgetcsv($handle);
                if (!$header) {
                    return back()->with('error', 'Empty CSV file.');
                }
                $header = array_map('trim', array_map('strtolower', $header));
                while (($row = fgetcsv($handle)) !== false) {
                    $item = array_combine($header, array_pad($row, count($header), ''));
                    $data[] = $item;
                }
                fclose($handle);
            }

            // Validate each entry
            foreach ($data as $i => &$item) {
                $item['_row'] = $i + 1;
                $item['_errors'] = [];

                if (empty($item['generic_name'] ?? '')) {
                    $item['_errors'][] = 'Missing generic_name';
                }
                if (empty($item['mg_per_kg_min'] ?? '') || !is_numeric($item['mg_per_kg_min'])) {
                    $item['_errors'][] = 'Invalid mg_per_kg_min';
                }
                if (empty($item['mg_per_kg_max'] ?? '') || !is_numeric($item['mg_per_kg_max'])) {
                    $item['_errors'][] = 'Invalid mg_per_kg_max';
                }

                if (!empty($item['_errors'])) {
                    $errors[] = "Row {$item['_row']}: " . implode(', ', $item['_errors']);
                }
            }
            unset($item);

        } catch (\Exception $e) {
            return back()->with('error', 'Error parsing file: ' . $e->getMessage());
        }

        // Sort by category for easier review, then by generic_name
        usort($data, function ($a, $b) {
            $catA = strtolower(trim($a['category'] ?? 'zzz'));
            $catB = strtolower(trim($b['category'] ?? 'zzz'));
            if ($catA !== $catB) return strcmp($catA, $catB);
            return strcmp(strtolower($a['generic_name'] ?? ''), strtolower($b['generic_name'] ?? ''));
        });

        // Store in session for confirmation (original order preserved in session)
        session(['pediatric_import_data' => $data]);

        $importErrors = $errors;
        return view('pediatric.medication.import-preview', compact('data', 'importErrors'));
    }

    /**
     * Confirm and process import.
     */
    public function importConfirm(Request $request)
    {
        $data = session('pediatric_import_data');
        if (!$data) {
            return redirect()->route('pediatric.medication.import')->with('error', 'No import data found. Please upload again.');
        }

        $imported = 0;
        $skipped = 0;

        DB::beginTransaction();
        try {
            foreach ($data as $item) {
                if (!empty($item['_errors'])) {
                    $skipped++;
                    continue;
                }

                // Find or create drug (imported drugs are tenant-owned, not system)
                $drug = PediatricDrug::firstOrCreate(
                    ['generic_name' => trim($item['generic_name'])],
                    [
                        'brand_name' => trim($item['brand_name'] ?? ''),
                        'category' => trim($item['category'] ?? ''),
                        'description' => trim($item['description'] ?? ''),
                        'is_active' => true,
                        'is_system' => false,
                        'clinic_id' => Auth::user()->clinic_id,
                    ]
                );

                // Create form if provided
                if (!empty($item['form']) && !empty($item['concentration'])) {
                    PediatricDrugForm::firstOrCreate(
                        ['drug_id' => $drug->id, 'form' => trim($item['form']), 'concentration' => trim($item['concentration'])],
                        [
                            'concentration_mg' => (float) ($item['concentration_mg'] ?? 0),
                            'concentration_per_ml' => !empty($item['concentration_per_ml']) ? (float) $item['concentration_per_ml'] : null,
                        ]
                    );
                }

                // Create dosage rule (avoid duplicates)
                PediatricDosageRule::firstOrCreate(
                    [
                        'drug_id' => $drug->id,
                        'mg_per_kg_min' => (float) $item['mg_per_kg_min'],
                        'mg_per_kg_max' => (float) $item['mg_per_kg_max'],
                        'frequency_per_day' => (int) ($item['frequency_per_day'] ?? 3),
                        'min_age_months' => !empty($item['min_age_months']) ? (int) $item['min_age_months'] : null,
                        'max_age_months' => !empty($item['max_age_months']) ? (int) $item['max_age_months'] : null,
                    ],
                    [
                        'max_daily_mg' => !empty($item['max_daily_mg']) ? (float) $item['max_daily_mg'] : null,
                        'frequency_hours' => !empty($item['frequency_hours']) ? (int) $item['frequency_hours'] : null,
                        'notes' => $item['notes'] ?? null,
                    ]
                );

                $imported++;
            }

            DB::commit();
            session()->forget('pediatric_import_data');

            return redirect()->route('pediatric.medication.drug-admin')
                ->with('success', "Import complete: {$imported} drugs imported, {$skipped} skipped.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * AJAX: Get forms for a drug.
     */
    public function getDrugForms(PediatricDrug $drug)
    {
        return response()->json($drug->forms);
    }

    /**
     * AJAX: Get patient info (weight, age).
     */
    public function getPatientInfo(Patient $patient)
    {
        return response()->json([
            'id' => $patient->id,
            'name' => $patient->first_name . ' ' . $patient->last_name,
            'age' => $patient->age,
            'age_months' => $patient->age_months,
            'weight_kg' => $patient->latest_weight_kg,
            'gender' => $patient->gender,
            'is_pediatric' => $patient->is_pediatric,
        ]);
    }
}

