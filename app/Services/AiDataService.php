<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AiDataService
{
    /**
     * Get patient medical summary for AI analysis
     */
    public static function getPatientSummary(int $patientId): array
    {
        $user = Auth::user();
        $patient = Patient::with(['medicalOverview', 'appointments', 'prescriptions'])
            ->where('clinic_id', $user->clinic_id)
            ->findOrFail($patientId);

        return [
            'patient_id' => $patient->patient_id,
            'name' => $patient->full_name,
            'age' => $patient->age,
            'gender' => $patient->gender,
            'phone' => $patient->phone,
            'medical_history' => $patient->medicalOverview?->medical_history,
            'chronic_diseases' => $patient->medicalOverview?->chronic_diseases,
            'allergies' => $patient->medicalOverview?->allergies,
            'surgeries' => $patient->medicalOverview?->surgeries,
            'current_medications' => $patient->medicalOverview?->current_medications_summary,
            'recent_appointments' => $patient->appointments()
                ->orderByDesc('appointment_date')
                ->limit(5)
                ->get(['appointment_date', 'notes', 'status'])
                ->toArray(),
            'prescriptions_count' => $patient->prescriptions()->count(),
        ];
    }

    /**
     * Get clinic statistics for analytics
     */
    public static function getClinicStats(): array
    {
        $user = Auth::user();
        $clinicId = $user->clinic_id;

        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();

        return [
            'total_patients' => Patient::where('clinic_id', $clinicId)->count(),
            'active_patients_this_month' => Appointment::where('clinic_id', $clinicId)
                ->whereDate('appointment_date', '>=', $thisMonth)
                ->distinct('patient_id')
                ->count('patient_id'),
            'appointments_today' => Appointment::where('clinic_id', $clinicId)
                ->whereDate('appointment_date', $today)
                ->count(),
            'appointments_this_month' => Appointment::where('clinic_id', $clinicId)
                ->whereDate('appointment_date', '>=', $thisMonth)
                ->count(),
            'total_doctors' => User::where('clinic_id', $clinicId)
                ->whereIn('role', ['doctor', 'admin'])
                ->count(),
            'pending_appointments' => Appointment::where('clinic_id', $clinicId)
                ->where('status', 'scheduled')
                ->count(),
        ];
    }

    /**
     * Get top diagnoses in clinic
     */
    public static function getTopDiagnoses(int $limit = 10): array
    {
        $user = Auth::user();

        $results = DB::table('ent_records')
            ->where('clinic_id', $user->clinic_id)
            ->where('diagnosis', '!=', null)
            ->select('diagnosis', DB::raw('COUNT(*) as count'))
            ->groupBy('diagnosis')
            ->orderByDesc('count')
            ->limit($limit)
            ->get();

        return $results->map(fn($item) => ['diagnosis' => $item->diagnosis, 'count' => $item->count])->toArray();
    }

    /**
     * Get appointment statistics
     */
    public static function getAppointmentStats(): array
    {
        $user = Auth::user();
        $clinicId = $user->clinic_id;
        $thisMonth = now()->startOfMonth();

        $stats = DB::table('appointments')
            ->where('clinic_id', $clinicId)
            ->whereDate('appointment_date', '>=', $thisMonth)
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        return $stats->pluck('count', 'status')->toArray();
    }

    /**
     * Get medicine inventory status
     */
    public static function getMedicineInventory(): array
    {
        $user = Auth::user();

        return DB::table('medicines')
            ->where('clinic_id', $user->clinic_id)
            ->select('name', 'quantity', 'unit_price')
            ->where('quantity', '<=', 10)
            ->orderBy('quantity')
            ->get()
            ->toArray();
    }

    /**
     * Prepare data context for AI
     */
    public static function prepareContextData(array $options = []): string
    {
        try {
            $user = Auth::user();
            $context = "### Clinic Context Data\n";
            $context .= "User: {$user->full_name} ({$user->role})\n";
            $context .= "Clinic: {$user->clinic->name}\n\n";

            if ($options['include_stats'] ?? true) {
                try {
                    $stats = self::getClinicStats();
                    $context .= "**Clinic Statistics:**\n";
                    $context .= "- Total Patients: {$stats['total_patients']}\n";
                    $context .= "- Active Patients This Month: {$stats['active_patients_this_month']}\n";
                    $context .= "- Appointments Today: {$stats['appointments_today']}\n";
                    $context .= "- Pending Appointments: {$stats['pending_appointments']}\n\n";
                } catch (\Exception $e) {
                    \Log::warning('Failed to get clinic stats: ' . $e->getMessage());
                }
            }

            if ($options['include_diagnoses'] ?? false) {
                try {
                    $diagnoses = self::getTopDiagnoses(5);
                    if (!empty($diagnoses)) {
                        $context .= "**Top Diagnoses This Month:**\n";
                        foreach ($diagnoses as $d) {
                            $diagnosis = $d['diagnosis'] ?? 'Unknown';
                            $count = $d['count'] ?? 0;
                            $context .= "- {$diagnosis}: {$count} cases\n";
                        }
                        $context .= "\n";
                    }
                } catch (\Exception $e) {
                    \Log::warning('Failed to get diagnoses: ' . $e->getMessage());
                }
            }

            return $context;
        } catch (\Exception $e) {
            \Log::error('Error in prepareContextData: ' . $e->getMessage());
            return "### Clinic Context Data\nUnable to load clinic context at this time.\n\n";
        }
    }
}
