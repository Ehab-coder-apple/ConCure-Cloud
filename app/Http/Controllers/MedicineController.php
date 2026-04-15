<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Imports\MedicinesImport;
use App\Exports\MedicinesTemplateExport;
use App\Exports\MedicinesExport;
use App\Http\Traits\SmartSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class MedicineController extends Controller
{
    use SmartSearch;
    /**
     * Display a listing of medicines.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Medicine::with(['creator'])
            ->visibleToUser($user);

        // Apply smart search filter
        $searchTerm = $this->getValidatedSearchTerm($request);
        if ($searchTerm !== null) {
            $query->search($searchTerm);
        }

        if ($request->filled('form')) {
            $query->where('form', $request->form);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('frequent')) {
            $query->where('is_frequent', true);
        }

        $medicines = $query->latest()->paginate(15);

        // Get statistics - apply same visibility filtering
        $statsQuery = Medicine::visibleToUser($user);
        $stats = [
            'total' => $statsQuery->count(),
            'active' => (clone $statsQuery)->where('is_active', true)->count(),
            'frequent' => (clone $statsQuery)->where('is_frequent', true)->count(),
            'forms' => (clone $statsQuery)->distinct('form')->count('form'),
        ];

        return view('medicines.index', compact('medicines', 'stats'));
    }

    /**
     * Show the form for creating a new medicine.
     */
    public function create()
    {
        return view('medicines.create');
    }

    /**
     * Store a newly created medicine.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'brand_name' => 'nullable|string|max:255',
            'dosage' => 'nullable|string|max:100',
            'form' => 'required|string|in:' . implode(',', array_keys(Medicine::FORMS)),
            'description' => 'nullable|string|max:1000',
            'side_effects' => 'nullable|string|max:1000',
            'contraindications' => 'nullable|string|max:1000',
            'is_frequent' => 'boolean',
            'is_active' => 'boolean',
            'stock_quantity' => 'nullable|integer|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'expiry_date' => 'nullable|date',
            'batch_number' => 'nullable|string|max:100',
        ]);

        // Check for duplicate medicine in the same clinic
        $exists = Medicine::where('clinic_id', $user->clinic_id)
            ->where('name', $request->name)
            ->where('dosage', $request->dosage)
            ->where('form', $request->form)
            ->exists();

        if ($exists) {
            return back()->withInput()
                ->with('error', __('A medicine with the same name, dosage, and form already exists in your inventory.'));
        }

        Medicine::create([
            'name' => $request->name,
            'generic_name' => $request->generic_name,
            'brand_name' => $request->brand_name,
            'dosage' => $request->dosage,
            'form' => $request->form,
            'description' => $request->description,
            'side_effects' => $request->side_effects,
            'contraindications' => $request->contraindications,
            'is_frequent' => $request->boolean('is_frequent'),
            'is_active' => $request->boolean('is_active', true),
            'stock_quantity' => $request->stock_quantity ?? 0,
            'purchase_price' => $request->purchase_price,
            'selling_price' => $request->selling_price,
            'expiry_date' => $request->expiry_date,
            'batch_number' => $request->batch_number,
            'clinic_id' => $user->clinic_id,
            'created_by' => $user->id,
        ]);

        return redirect()->route('medicines.index')
            ->with('success', __('Medicine added to inventory successfully.'));
    }

    /**
     * Display the specified medicine.
     */
    public function show(Medicine $medicine)
    {
        $this->authorize('view', $medicine);
        
        $medicine->load(['creator', 'prescriptionMedicines.prescription.patient']);
        
        // Get usage statistics
        $usageStats = [
            'total_prescriptions' => $medicine->prescriptionMedicines()->count(),
            'recent_prescriptions' => $medicine->prescriptionMedicines()
                ->with(['prescription.patient'])
                ->latest()
                ->limit(5)
                ->get(),
        ];

        return view('medicines.show', compact('medicine', 'usageStats'));
    }

    /**
     * Show the form for editing the specified medicine.
     */
    public function edit(Medicine $medicine)
    {
        $this->authorize('update', $medicine);
        
        return view('medicines.edit', compact('medicine'));
    }

    /**
     * Update the specified medicine.
     */
    public function update(Request $request, Medicine $medicine)
    {
        $this->authorize('update', $medicine);

        $request->validate([
            'name' => 'required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'brand_name' => 'nullable|string|max:255',
            'dosage' => 'nullable|string|max:100',
            'form' => 'required|string|in:' . implode(',', array_keys(Medicine::FORMS)),
            'description' => 'nullable|string|max:1000',
            'side_effects' => 'nullable|string|max:1000',
            'contraindications' => 'nullable|string|max:1000',
            'is_frequent' => 'boolean',
            'is_active' => 'boolean',
            'stock_quantity' => 'nullable|integer|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'expiry_date' => 'nullable|date',
            'batch_number' => 'nullable|string|max:100',
        ]);

        // Check for duplicate medicine in the same clinic (excluding current)
        $exists = Medicine::where('clinic_id', $medicine->clinic_id)
            ->where('name', $request->name)
            ->where('dosage', $request->dosage)
            ->where('form', $request->form)
            ->where('id', '!=', $medicine->id)
            ->exists();

        if ($exists) {
            return back()->withInput()
                ->with('error', __('A medicine with the same name, dosage, and form already exists in your inventory.'));
        }

        $medicine->update([
            'name' => $request->name,
            'generic_name' => $request->generic_name,
            'brand_name' => $request->brand_name,
            'dosage' => $request->dosage,
            'form' => $request->form,
            'description' => $request->description,
            'side_effects' => $request->side_effects,
            'contraindications' => $request->contraindications,
            'is_frequent' => $request->boolean('is_frequent'),
            'is_active' => $request->boolean('is_active'),
            'stock_quantity' => $request->stock_quantity ?? 0,
            'purchase_price' => $request->purchase_price,
            'selling_price' => $request->selling_price,
            'expiry_date' => $request->expiry_date,
            'batch_number' => $request->batch_number,
        ]);

        return redirect()->route('medicines.show', $medicine)
            ->with('success', __('Medicine updated successfully.'));
    }

    /**
     * Remove the specified medicine from storage.
     */
    public function destroy(Medicine $medicine)
    {
        $this->authorize('delete', $medicine);

        // Check if medicine is used in any prescriptions
        if ($medicine->prescriptionMedicines()->exists()) {
            return back()->with('error', __('Cannot delete medicine that has been used in prescriptions. You can deactivate it instead.'));
        }

        $medicine->delete();

        return redirect()->route('medicines.index')
            ->with('success', __('Medicine deleted successfully.'));
    }

    /**
     * Toggle medicine active status.
     */
    public function toggleStatus(Medicine $medicine)
    {
        $this->authorize('update', $medicine);

        $medicine->update([
            'is_active' => !$medicine->is_active
        ]);

        $status = $medicine->is_active ? 'activated' : 'deactivated';
        
        return back()->with('success', __("Medicine {$status} successfully."));
    }

    /**
     * Toggle medicine frequent status.
     */
    public function toggleFrequent(Medicine $medicine)
    {
        $this->authorize('update', $medicine);

        $medicine->update([
            'is_frequent' => !$medicine->is_frequent
        ]);

        $status = $medicine->is_frequent ? 'marked as frequent' : 'removed from frequent';
        
        return back()->with('success', __("Medicine {$status} successfully."));
    }

    /**
     * Get medicines for AJAX requests (for prescription forms).
     */
    public function search(Request $request)
    {
        $user = Auth::user();

        // Validate and get search term (supports both 'q' and 'search' parameters)
        $searchTerm = $this->getValidatedSearchTerm($request, 'q');

        // Build query with role-based filtering
        $query = Medicine::visibleToUser($user)
            ->where('is_active', true);

        // Apply search if valid term provided
        if ($searchTerm !== null) {
            $query->search($searchTerm);
        }

        $medicines = $query
            ->select('id', 'name', 'generic_name', 'brand_name', 'dosage', 'form')
            ->limit(20)
            ->get()
            ->map(function ($medicine) {
                return [
                    'id' => $medicine->id,
                    'text' => $medicine->full_name,
                    'name' => $medicine->name,
                    'generic_name' => $medicine->generic_name,
                    'brand_name' => $medicine->brand_name,
                    'dosage' => $medicine->dosage,
                    'form' => $medicine->form,
                ];
            });

        return response()->json($medicines);
    }

    /**
     * Show the import form.
     */
    public function showImport()
    {
        return view('medicines.import');
    }

    /**
     * Download the import template.
     */
    public function downloadTemplate(Request $request)
    {
        $includeSampleData = $request->boolean('sample', true);
        $format = $request->get('format', 'xlsx'); // Default to Excel

        // Only use CSV if explicitly requested
        if ($format === 'csv') {
            return $this->downloadCsvTemplate($includeSampleData);
        }

        // Excel generation with enhanced error handling
        try {
            // Clear any output buffers that might interfere
            while (ob_get_level()) {
                ob_end_clean();
            }

            $filename = 'medicines_import_template_' . date('Y-m-d') . '.xlsx';

            // Create and validate the export instance
            $export = new MedicinesTemplateExport($includeSampleData);

            // Pre-validate the export data
            $headers = $export->headings();
            $data = $export->array();

            if (empty($headers)) {
                throw new \Exception('Template headers are missing');
            }

            if (!$includeSampleData && empty($data)) {
                throw new \Exception('Empty template data is invalid');
            }

            // Generate and return the Excel file
            return Excel::download($export, $filename, \Maatwebsite\Excel\Excel::XLSX, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);

        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Excel template generation failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            // Only fallback to CSV if Excel completely fails
            \Log::info('Falling back to CSV template due to Excel generation failure');

            return response()->json([
                'error' => 'Excel template generation failed. Please try the CSV format or contact support.',
                'fallback_url' => route('medicines.import.template', ['sample' => $includeSampleData, 'format' => 'csv'])
            ], 500);
        }
    }

    /**
     * Download CSV template as fallback.
     */
    private function downloadCsvTemplate(bool $includeSampleData): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = 'medicines_import_template_' . date('Y-m-d') . '.csv';
        $headers = MedicinesImport::getExpectedHeaders();
        $sampleData = $includeSampleData ? MedicinesImport::getSampleData() : [];

        $callback = function() use ($headers, $sampleData) {
            $file = fopen('php://output', 'w');

            // Write headers
            fputcsv($file, array_keys($headers));

            // Write sample data if requested
            if (!empty($sampleData)) {
                foreach ($sampleData as $row) {
                    fputcsv($file, $row);
                }
            } else {
                // Write a few empty rows for template structure
                for ($i = 0; $i < 5; $i++) {
                    $emptyRow = array_fill(0, count($headers), '');
                    fputcsv($file, $emptyRow);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Import medicines from uploaded file.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
        ]);

        try {
            $import = new MedicinesImport();

            Excel::import($import, $request->file('file'));

            $message = "Import completed successfully! ";
            $message .= "Imported: {$import->getImportedCount()} medicines. ";

            if ($import->getSkippedCount() > 0) {
                $message .= "Skipped: {$import->getSkippedCount()} medicines (duplicates or errors).";
            }

            if ($import->hasErrors()) {
                $errorMessage = "Some medicines could not be imported:\n" . implode("\n", array_slice($import->getErrors(), 0, 10));
                if (count($import->getErrors()) > 10) {
                    $errorMessage .= "\n... and " . (count($import->getErrors()) - 10) . " more errors.";
                }

                return redirect()->route('medicines.import')
                    ->with('warning', $message)
                    ->with('import_errors', $errorMessage);
            }

            return redirect()->route('medicines.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            \Log::error('Medicine import failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return redirect()->route('medicines.import')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Export medicines to Excel
     */
    public function export()
    {
        $user = Auth::user();

        try {
            // Clear any output buffers
            while (ob_get_level()) {
                ob_end_clean();
            }

            $filename = 'medicines_export_' . date('Y-m-d_His') . '.xlsx';
            $clinicId = $user->clinic_id;

            return Excel::download(
                new MedicinesExport($clinicId, $user),
                $filename,
                \Maatwebsite\Excel\Excel::XLSX,
                [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ]
            );
        } catch (\Exception $e) {
            return redirect()->route('medicines.index')
                ->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    /**
     * Bulk delete medicines
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'medicine_ids' => 'required|array',
            'medicine_ids.*' => 'exists:medicines,id',
        ]);

        $user = Auth::user();

        try {
            DB::beginTransaction();

            $query = Medicine::whereIn('id', $request->medicine_ids)
                ->visibleToUser($user);

            // For regular users, only allow deleting their own medicines
            if (!$user->isSuperAdmin() && !$user->isClinicAdmin()) {
                $query->where('created_by', $user->id);
            }

            $count = $query->count();

            // Delete medicines
            $query->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully deleted {$count} medicine(s).",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Bulk delete failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear all medicines for the current clinic
     */
    public function clearAll(Request $request)
    {
        $user = Auth::user();

        if (!$user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'You must be assigned to a clinic to perform this action.',
            ], 403);
        }

        try {
            DB::beginTransaction();

            $query = Medicine::visibleToUser($user);

            // For regular users, only allow clearing their own medicines
            if (!$user->isSuperAdmin() && !$user->isClinicAdmin()) {
                $query->where('created_by', $user->id);
            }

            $count = $query->count();

            // Delete all medicines
            $query->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully cleared all {$count} medicine(s).",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Clear all failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
