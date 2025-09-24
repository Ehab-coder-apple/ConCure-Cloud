<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Prescription;
use App\Models\LabRequest;
use App\Models\DietPlan;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\User;
use App\Models\AuditLog;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Determine selected period: precedence = explicit param (admin saves) -> clinic default -> 'month'
        $requested = $request->get('period');
        $isValid = in_array($requested, ['day','month','year'], true);

        $clinicDefault = null;
        $clinic = null;
        if ($user && $user->clinic_id) {
            $clinic = Clinic::find($user->clinic_id);
            // Prefer settings table value if present
            $clinicDefault = DB::table('settings')
                ->where('clinic_id', $user->clinic_id)
                ->where('key', 'dashboard_default_period')
                ->value('value');
            // Fallback to Clinic JSON settings column
            if (!$clinicDefault && $clinic) {
                $clinicDefault = $clinic->getSetting('dashboard_default_period', null);
            }
        }

        $period = $isValid ? $requested : ($clinicDefault ?? 'month');

        // If an admin explicitly chooses a period, persist it as the clinic default for everyone
        if ($isValid && $clinic && method_exists($user, 'canManageUsers') && $user->canManageUsers()) {
            // Save in the settings table (authoritative)
            DB::table('settings')->updateOrInsert(
                ['clinic_id' => $user->clinic_id, 'key' => 'dashboard_default_period'],
                ['value' => $period, 'type' => 'string', 'updated_at' => now()]
            );
            // Keep Clinic JSON settings in sync as a fallback
            $clinic->setSetting('dashboard_default_period', $period);
        }

        // Get dashboard data based on user role and selected period
        $dashboardData = $this->getDashboardData($user, $period);
        $dashboardData['selectedPeriod'] = $period;
        $dashboardData['periodPhrase'] = $period === 'day' ? 'today' : ($period === 'year' ? 'this year' : 'this month');

        return view('dashboard', $dashboardData);
    }

    /**
     * Get dashboard data based on user role.
     */
    private function getDashboardData($user, string $period = 'month'): array
    {
        $data = [];

        // Helper to apply selected period to a given date column
        $applyPeriod = function ($query, string $column) use ($period) {
            if ($period === 'day') {
                return $query->whereDate($column, now()->toDateString());
            } elseif ($period === 'year') {
                return $query->whereYear($column, now()->year);
            }
            // default month
            return $query->whereMonth($column, now()->month)
                         ->whereYear($column, now()->year);
        };

        // Common filters for clinic-based data
        $clinicFilter = function ($query) use ($user) {
            if ($user->role === 'patient') {
                // Patients see only their own data
                $query->where('patient_id', $user->patient_id ?? 0);
            } else {
                // Other roles see clinic data
                $query->whereHas('patient', function ($q) use ($user) {
                    $q->where('clinic_id', $user->clinic_id);
                });
            }
        };

        // Patient statistics
        if ($user->canManagePatients() ) {
            $patientsQuery = Patient::query();
            $patientsQuery->where('clinic_id', $user->clinic_id);

            $data['totalPatients'] = $patientsQuery->active()->count();
            $data['newPatientsThisMonth'] = (clone $patientsQuery)->active();
            $data['newPatientsThisMonth'] = $applyPeriod($data['newPatientsThisMonth'], 'created_at')->count();
        }

        // Prescription statistics
        if ($user->canPrescribe() || $user->canManagePatients() ) {
            $prescriptionsQuery = Prescription::query();
            $prescriptionsQuery->whereHas('patient', function ($q) use ($user) {
                $q->where('clinic_id', $user->clinic_id);
            });
            
            $data['activePrescriptions'] = $prescriptionsQuery->active()->count();
            $data['prescriptionsThisMonth'] = $applyPeriod((clone $prescriptionsQuery), 'prescribed_date')->count();
        }

        // Lab request statistics
        if ($user->canPrescribe() || $user->canManagePatients() ) {
            $labRequestsQuery = LabRequest::query();
            $labRequestsQuery->whereHas('patient', function ($q) use ($user) {
                $q->where('clinic_id', $user->clinic_id);
            });
            
            $data['pendingLabRequests'] = $labRequestsQuery->pending()->count();
            $data['urgentLabRequests'] = $labRequestsQuery->pending()
                ->where('priority', 'urgent')
                ->count();
        }

        // Diet plan statistics
        if ($user->canPrescribe() || $user->canManagePatients() ) {
            $dietPlansQuery = DietPlan::query();
            $dietPlansQuery->whereHas('patient', function ($q) use ($user) {
                $q->where('clinic_id', $user->clinic_id);
            });
            
            $data['activeDietPlans'] = $dietPlansQuery->active()->count();
            $data['expiredDietPlans'] = $dietPlansQuery->expired()->count();
        }

        // Financial statistics
        if ($user->canAccessFinance() ) {
            $invoicesQuery = Invoice::query();
            $invoicesQuery->where('clinic_id', $user->clinic_id);

            $data['totalRevenue'] = $applyPeriod((clone $invoicesQuery), 'invoice_date')->sum('total_amount');
                
            $data['pendingInvoices'] = $invoicesQuery
                ->whereIn('status', ['draft', 'sent'])
                ->count();
                
            $data['overdueInvoices'] = $invoicesQuery
                ->where('status', 'sent')
                ->where('due_date', '<', now())
                ->count();
        }

        // User statistics (for admins and program owners)
        if ($user->canManageUsers()) {
            $usersQuery = User::query();
            $usersQuery->where('clinic_id', $user->clinic_id);

            $data['totalUsers'] = $usersQuery->active()->count();
            $data['newUsersThisMonth'] = $applyPeriod((clone $usersQuery)->active(), 'created_at')->count();
        }

        // Recent activity
        $data['recentActivity'] = $this->getRecentActivity($user);
        
        // Appointment statistics
        if (class_exists('App\Models\Appointment')) {
            $appointmentsQuery = Appointment::query();
            $appointmentsQuery->where('clinic_id', $user->clinic_id);
            if ($user->role === 'doctor') {
                $appointmentsQuery->where('doctor_id', $user->id);
            }

            $data['totalAppointments'] = $appointmentsQuery->count();
            $data['todayAppointments'] = $appointmentsQuery
                ->whereDate('appointment_datetime', now()->toDateString())
                ->count();
            $data['upcomingAppointments'] = $appointmentsQuery
                ->where('appointment_datetime', '>', now())
                ->where('status', 'scheduled')
                ->count();
        }

        // Nutrition plan statistics
        if (class_exists('App\Models\DietPlan')) {
            $nutritionQuery = \App\Models\DietPlan::query();
            $nutritionQuery->whereHas('patient', function ($q) use ($user) {
                $q->where('clinic_id', $user->clinic_id);
            });
            if ($user->role === 'doctor') {
                $nutritionQuery->where('doctor_id', $user->id);
            }

            $data['totalNutritionPlans'] = $nutritionQuery->count();
            $data['activeNutritionPlans'] = $nutritionQuery->where('status', 'active')->count();
            $data['thisMonthNutritionPlans'] = $applyPeriod((clone $nutritionQuery), 'created_at')->count();
        }

        // Upcoming appointments (detailed)
        $data['upcomingAppointmentsList'] = $this->getUpcomingAppointments($user);

        // Appointments by date (for the next 7 days)
        $data['appointmentsByDate'] = $this->getAppointmentsByDate($user);

        // Quick stats for charts (period-aware)
        $data['monthlyStats'] = $this->getMonthlyStats($user, $period);

        return $data;
    }

    /**
     * Get recent activity for the user.
     */
    private function getRecentActivity($user): array
    {
        $query = AuditLog::with('user');
        $query->where('clinic_id', $user->clinic_id);

        return $query->latest('performed_at')
                    ->limit(10)
                    ->get()
                    ->toArray();
    }

    /**
     * Get upcoming appointments.
     */
    private function getUpcomingAppointments($user): array
    {
        if (!class_exists('App\Models\Appointment')) {
            return [];
        }

        $query = Appointment::with(['patient', 'doctor']);

        if ($user->role === 'patient') {
            $query->where('patient_id', $user->patient_id ?? 0);
        } else {
            $query->where('clinic_id', $user->clinic_id);
            if ($user->role === 'doctor') {
                $query->where('doctor_id', $user->id);
            }
        }

        return $query->where('appointment_datetime', '>=', now())
                    ->where('status', 'scheduled')
                    ->orderBy('appointment_datetime')
                    ->limit(5)
                    ->get()
                    ->toArray();
    }

    /**
     * Get appointments organized by date for the next 7 days.
     */
    private function getAppointmentsByDate($user): array
    {
        if (!class_exists('App\Models\Appointment')) {
            return [];
        }

        $appointments = [];

        // Get appointments for the next 7 days
        for ($i = 0; $i < 7; $i++) {
            $date = now()->addDays($i);
            $dateKey = $date->toDateString();
            $dateLabel = $date->format('l, M j'); // e.g., "Monday, Jan 15"

            $query = Appointment::with(['patient', 'doctor']);

            if ($user->role === 'patient') {
                $query->where('patient_id', $user->patient_id ?? 0);
            } else {
                $query->where('clinic_id', $user->clinic_id);
                if ($user->role === 'doctor') {
                    $query->where('doctor_id', $user->id);
                }
            }

            $dayAppointments = $query->whereDate('appointment_datetime', $dateKey)
                                   ->orderBy('appointment_datetime')
                                   ->get()
                                   ->toArray();

            if (!empty($dayAppointments) || $i === 0) { // Always include today even if empty
                $appointments[] = [
                    'date' => $dateKey,
                    'date_label' => $dateLabel,
                    'is_today' => $i === 0,
                    'appointments' => $dayAppointments,
                    'count' => count($dayAppointments)
                ];
            }
        }

        return $appointments;
    }

    /**
     * Get period-aware statistics for charts.
     */
    private function getMonthlyStats($user, string $period = 'month'): array
    {
        $stats = [];

        if ($period === 'day') {
            // Last 7 days including today
            for ($i = 6; $i >= 0; $i--) {
                $day = now()->subDays($i);
                $key = $day->format('M j');
                $stats[$key] = ['patients' => 0, 'prescriptions' => 0, 'revenue' => 0];

                if ($user->canManagePatients()) {
                    $stats[$key]['patients'] = Patient::where('clinic_id', $user->clinic_id)
                        ->whereDate('created_at', $day->toDateString())
                        ->count();
                }
                if ($user->canPrescribe() || $user->canManagePatients()) {
                    $stats[$key]['prescriptions'] = Prescription::whereHas('patient', function ($q) use ($user) {
                            $q->where('clinic_id', $user->clinic_id);
                        })
                        ->whereDate('prescribed_date', $day->toDateString())
                        ->count();
                }
                if ($user->canAccessFinance()) {
                    $stats[$key]['revenue'] = Invoice::where('clinic_id', $user->clinic_id)
                        ->whereDate('invoice_date', $day->toDateString())
                        ->sum('total_amount');
                }
            }
        } elseif ($period === 'year') {
            // Last 5 years including current year
            for ($i = 4; $i >= 0; $i--) {
                $year = now()->subYears($i)->year;
                $key = (string) $year;
                $stats[$key] = ['patients' => 0, 'prescriptions' => 0, 'revenue' => 0];

                if ($user->canManagePatients()) {
                    $stats[$key]['patients'] = Patient::where('clinic_id', $user->clinic_id)
                        ->whereYear('created_at', $year)
                        ->count();
                }
                if ($user->canPrescribe() || $user->canManagePatients()) {
                    $stats[$key]['prescriptions'] = Prescription::whereHas('patient', function ($q) use ($user) {
                            $q->where('clinic_id', $user->clinic_id);
                        })
                        ->whereYear('prescribed_date', $year)
                        ->count();
                }
                if ($user->canAccessFinance()) {
                    $stats[$key]['revenue'] = Invoice::where('clinic_id', $user->clinic_id)
                        ->whereYear('invoice_date', $year)
                        ->sum('total_amount');
                }
            }
        } else {
            // Default: last 6 months
            for ($i = 5; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $key = $month->format('M Y');
                $stats[$key] = ['patients' => 0, 'prescriptions' => 0, 'revenue' => 0];

                if ($user->canManagePatients()) {
                    $stats[$key]['patients'] = Patient::where('clinic_id', $user->clinic_id)
                        ->whereMonth('created_at', $month->month)
                        ->whereYear('created_at', $month->year)
                        ->count();
                }
                if ($user->canPrescribe() || $user->canManagePatients()) {
                    $stats[$key]['prescriptions'] = Prescription::whereHas('patient', function ($q) use ($user) {
                            $q->where('clinic_id', $user->clinic_id);
                        })
                        ->whereMonth('prescribed_date', $month->month)
                        ->whereYear('prescribed_date', $month->year)
                        ->count();
                }
                if ($user->canAccessFinance()) {
                    $stats[$key]['revenue'] = Invoice::where('clinic_id', $user->clinic_id)
                        ->whereMonth('invoice_date', $month->month)
                        ->whereYear('invoice_date', $month->year)
                        ->sum('total_amount');
                }
            }
        }

        return $stats;
    }
}
