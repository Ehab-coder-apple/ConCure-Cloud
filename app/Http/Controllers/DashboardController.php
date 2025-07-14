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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index()
    {
        $user = auth()->user();
        
        // Get dashboard data based on user role
        $dashboardData = $this->getDashboardData($user);
        
        return view('dashboard', $dashboardData);
    }

    /**
     * Get dashboard data based on user role.
     */
    private function getDashboardData($user): array
    {
        $data = [];
        
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
            $data['newPatientsThisMonth'] = $patientsQuery->active()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();
        }

        // Prescription statistics
        if ($user->canPrescribe() || $user->canManagePatients() ) {
            $prescriptionsQuery = Prescription::query();
            $prescriptionsQuery->whereHas('patient', function ($q) use ($user) {
                $q->where('clinic_id', $user->clinic_id);
            });
            
            $data['activePrescriptions'] = $prescriptionsQuery->active()->count();
            $data['prescriptionsThisMonth'] = $prescriptionsQuery
                ->whereMonth('prescribed_date', now()->month)
                ->whereYear('prescribed_date', now()->year)
                ->count();
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

            $data['totalRevenue'] = $invoicesQuery
                ->whereMonth('invoice_date', now()->month)
                ->whereYear('invoice_date', now()->year)
                ->sum('total_amount');
                
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
            $data['newUsersThisMonth'] = $usersQuery->active()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();
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
            $data['thisMonthNutritionPlans'] = $nutritionQuery
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();
        }

        // Upcoming appointments (detailed)
        $data['upcomingAppointmentsList'] = $this->getUpcomingAppointments($user);

        // Appointments by date (for the next 7 days)
        $data['appointmentsByDate'] = $this->getAppointmentsByDate($user);

        // Quick stats for charts
        $data['monthlyStats'] = $this->getMonthlyStats($user);

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
     * Get monthly statistics for charts.
     */
    private function getMonthlyStats($user): array
    {
        $stats = [];
        
        // Get last 6 months data
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthKey = $month->format('M Y');
            
            $stats[$monthKey] = [
                'patients' => 0,
                'prescriptions' => 0,
                'revenue' => 0,
            ];
            
            // Patient count
            if ($user->canManagePatients() ) {
                $patientsQuery = Patient::query();
                $patientsQuery->where('clinic_id', $user->clinic_id);

                $stats[$monthKey]['patients'] = $patientsQuery
                    ->whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->count();
            }
            
            // Prescription count
            if ($user->canPrescribe() || $user->canManagePatients()) {
                $prescriptionsQuery = Prescription::query();
                $prescriptionsQuery->whereHas('patient', function ($q) use ($user) {
                    $q->where('clinic_id', $user->clinic_id);
                });

                $stats[$monthKey]['prescriptions'] = $prescriptionsQuery
                    ->whereMonth('prescribed_date', $month->month)
                    ->whereYear('prescribed_date', $month->year)
                    ->count();
            }
            
            // Revenue
            if ($user->canAccessFinance()) {
                $invoicesQuery = Invoice::query();
                $invoicesQuery->where('clinic_id', $user->clinic_id);

                $stats[$monthKey]['revenue'] = $invoicesQuery
                    ->whereMonth('invoice_date', $month->month)
                    ->whereYear('invoice_date', $month->year)
                    ->sum('total_amount');
            }
        }
        
        return $stats;
    }
}
