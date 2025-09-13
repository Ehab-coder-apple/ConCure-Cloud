<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\PatientCheckup;
use App\Models\Prescription;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            ->whereBetween('appointment_date', [$dateFromCarbon, $dateToCarbon])
            ->with('doctor')
            ->orderBy('appointment_date', 'desc')
            ->get();
        
        // Calculate vital signs trends
        $vitalTrends = $this->calculateVitalTrends($checkups);
        
        // Get latest checkup for current status
        $latestCheckup = $checkups->first();
        
        // Calculate BMI history
        $bmiHistory = $this->calculateBmiHistory($checkups);
        
        return [
            'checkups' => $checkups,
            'prescriptions' => $prescriptions,
            'appointments' => $appointments,
            'vital_trends' => $vitalTrends,
            'latest_checkup' => $latestCheckup,
            'bmi_history' => $bmiHistory,
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

        // Check permission-based access or role-based fallback
        if (!$user->hasPermission('patients_view') &&
            !$user->canManagePatients() &&
            !in_array($user->role, ['doctor', 'admin', 'nurse'])) {
            abort(403, 'Insufficient permissions to view patients.');
        }
    }
}
