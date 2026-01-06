<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Clinic;
use App\Models\User;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Medicine;
use App\Models\LabTest;
use App\Models\LabRequest;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\PatientCheckup;
use App\Models\SubscriptionPayment;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    /**
     * Master Reports index with filters and summary data
     */
    public function index(Request $request)
    {
        $from = $this->parseDate($request->query('from'));
        $to   = $this->parseDate($request->query('to'));
        $clinicId = $request->query('clinic_id');

        // Clinics summary
        $clinicsBase = Clinic::query();
        if ($from) { $clinicsBase->whereDate('created_at', '>=', $from->toDateString()); }
        if ($to)   { $clinicsBase->whereDate('created_at', '<=', $to->toDateString()); }

        $clinicsTotal    = (clone $clinicsBase)->count();
        $clinicsActive   = (clone $clinicsBase)->where('is_active', true)->count();
        $clinicsInactive = (clone $clinicsBase)->where('is_active', false)->count();

        // Users by role (exclude super_admin and master_admin)
        $usersBase = User::whereNotIn('role', ['super_admin', 'master_admin']);
        if ($from) { $usersBase->whereDate('created_at', '>=', $from->toDateString()); }
        if ($to)   { $usersBase->whereDate('created_at', '<=', $to->toDateString()); }
        if ($clinicId) { $usersBase->where('clinic_id', $clinicId); }

        $usersByRole = $usersBase
            ->select('role', DB::raw('COUNT(*) as count'))
            ->groupBy('role')
            ->orderBy('role')
            ->pluck('count', 'role')
            ->toArray();

        // Patient Analytics
        try {
            $patientStats = $this->getPatientStats($from, $to, $clinicId);
        } catch (\Exception $e) {
            $patientStats = ['total' => 0, 'active' => 0, 'new' => 0, 'gender' => [], 'age_groups' => []];
        }

        // Prescription Analytics
        try {
            $prescriptionStats = $this->getPrescriptionStats($from, $to, $clinicId);
        } catch (\Exception $e) {
            $prescriptionStats = ['total' => 0, 'active' => 0, 'completed' => 0, 'top_medicines' => collect()];
        }

        // Lab Test Analytics
        try {
            $labStats = $this->getLabStats($from, $to, $clinicId);
        } catch (\Exception $e) {
            $labStats = ['total' => 0, 'pending' => 0, 'completed' => 0, 'top_tests' => collect()];
        }

        // Appointment Analytics
        try {
            $appointmentStats = $this->getAppointmentStats($from, $to, $clinicId);
        } catch (\Exception $e) {
            $appointmentStats = ['total' => 0, 'scheduled' => 0, 'completed' => 0, 'cancelled' => 0, 'types' => [], 'type_labels' => [], 'type_values' => []];
        }

        // Financial Analytics
        try {
            $financialStats = $this->getFinancialStats($from, $to, $clinicId);
        } catch (\Exception $e) {
            $financialStats = ['total_invoices' => 0, 'total_revenue' => 0, 'paid_amount' => 0, 'outstanding' => 0, 'status' => []];
        }

        // Financials (master subscriptions)
        $currencySymbol = config('concure.currency_symbol', '$');
        $activeSubscribersQuery = Clinic::where('is_active', true);
        if (Schema::hasColumn('clinics', 'is_demo')) { $activeSubscribersQuery->where('is_demo', false); }
        $activeSubscribers = $activeSubscribersQuery->count();

        // Expected monthly fees: per-clinic billable user count × price/user (fallback to flat fee)
        $expectedMonthlyFees = 0.0;
        if (Schema::hasColumn('clinics', 'billing_user_price') && Schema::hasColumn('clinics', 'billing_user_count')) {
            $feesQuery = Clinic::query()->where('is_active', true);
            if (Schema::hasColumn('clinics', 'is_demo')) { $feesQuery->where('is_demo', false); }
            $expectedMonthlyFees = (float) $feesQuery->select(DB::raw('SUM(COALESCE(billing_user_price, 0) * COALESCE(billing_user_count, max_users)) as total'))
                                                  ->value('total');
            $expectedFormulaNote = 'Sum of per-clinic (users × price/user)';
        } else {
            $monthlyFee = (float) config('concure.subscription.monthly_fee', 29);
            $expectedMonthlyFees = $activeSubscribers * $monthlyFee;
            $expectedFormulaNote = $activeSubscribers.' subscribers × '.$currencySymbol.number_format($monthlyFee, 2);
        }

        // One-time service charges within range (default current month)
        $serviceCharges = 0.0;
        if (Schema::hasColumn('clinics', 'service_charge_amount') && Schema::hasColumn('clinics', 'service_charge_date')) {
            $scQuery = Clinic::query()->where('is_active', true)
                ->whereNotNull('service_charge_amount')
                ->where('service_charge_amount', '>', 0);
            if (Schema::hasColumn('clinics', 'is_demo')) { $scQuery->where('is_demo', false); }
            if ($from) { $scQuery->whereDate('service_charge_date', '>=', $from->toDateString()); }
            if ($to)   { $scQuery->whereDate('service_charge_date', '<=', $to->toDateString()); }
            if (!$from && !$to) {
                $scQuery->whereBetween('service_charge_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()]);
            }
            $serviceCharges = (float) $scQuery->sum('service_charge_amount');
        }

        // Collected amount for selected period (defaults to current month)
        $collectedAmount = 0.0;
        if (Schema::hasTable('subscription_payments')) {
            $payments = SubscriptionPayment::query();
            if ($from) { $payments->whereDate('paid_at', '>=', $from->toDateString()); }
            if ($to)   { $payments->whereDate('paid_at', '<=', $to->toDateString()); }
            if (!$from && !$to) {
                $payments->whereBetween('paid_at', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()]);
            }
            $collectedAmount = (float) $payments->sum('amount');
        }

        $filters = [
            'from' => $from?->toDateString(),
            'to'   => $to?->toDateString(),
            'clinic_id' => $clinicId,
        ];

        $clinics = Clinic::orderBy('name')->get(['id','name']);

        return view('master.reports.index', compact(
            'filters',
            'clinicsTotal', 'clinicsActive', 'clinicsInactive',
            'usersByRole',
            'patientStats', 'prescriptionStats', 'labStats', 'appointmentStats', 'financialStats',
            'currencySymbol', 'activeSubscribers', 'expectedMonthlyFees', 'collectedAmount', 'serviceCharges', 'expectedFormulaNote',
            'clinics'
        ));
    }

    /**
     * Export service charges within filters as CSV
     */
    public function exportServiceCharges(Request $request)
    {
        $from = $this->parseDate($request->query('from'));
        $to   = $this->parseDate($request->query('to'));
        $clinicId = $request->query('clinic_id');

        if (!(Schema::hasColumn('clinics', 'service_charge_amount') && Schema::hasColumn('clinics', 'service_charge_date'))) {
            return redirect()->route('master.reports')->with('error', 'Service charge fields are not available. Please run migrations.');
        }

        $query = Clinic::query()
            ->where('is_active', true)
            ->whereNotNull('service_charge_amount')
            ->where('service_charge_amount', '>', 0);

        if (Schema::hasColumn('clinics', 'is_demo')) { $query->where('is_demo', false); }
        if ($from) { $query->whereDate('service_charge_date', '>=', $from->toDateString()); }
        if ($to)   { $query->whereDate('service_charge_date', '<=', $to->toDateString()); }
        if (!$from && !$to) {
            $query->whereBetween('service_charge_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()]);
        }
        if ($clinicId) { $query->where('id', $clinicId); }

        $filename = 'service-charges-' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            // CSV Header
            fputcsv($out, ['clinic_id', 'clinic_name', 'service_charge_amount', 'service_charge_date', 'service_charge_note']);
            // Rows
            $query->orderBy('service_charge_date')
                ->get(['id', 'name', 'service_charge_amount', 'service_charge_date', 'service_charge_note'])
                ->each(function ($c) use ($out) {
                    fputcsv($out, [
                        $c->id,
                        $c->name,
                        number_format((float) $c->service_charge_amount, 2, '.', ''),
                        optional($c->service_charge_date)->toDateString(),
                        $c->service_charge_note,
                    ]);
                });
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }


    /**
     * Get patient statistics
     */
    private function getPatientStats($from, $to, $clinicId): array
    {
        $patientsBase = Patient::query();
        if ($from) { $patientsBase->whereDate('created_at', '>=', $from->toDateString()); }
        if ($to) { $patientsBase->whereDate('created_at', '<=', $to->toDateString()); }
        if ($clinicId) { $patientsBase->where('clinic_id', $clinicId); }

        $totalPatients = (clone $patientsBase)->count();
        $activePatients = (clone $patientsBase)->where('is_active', true)->count();
        $newPatients = (clone $patientsBase)->where('created_at', '>=', now()->subDays(30))->count();

        // Gender distribution
        $genderStats = (clone $patientsBase)
            ->select('gender', DB::raw('COUNT(*) as count'))
            ->groupBy('gender')
            ->pluck('count', 'gender')
            ->toArray();

        // Age groups
        $ageGroups = [
            '0-18' => 0,
            '19-35' => 0,
            '36-50' => 0,
            '51-65' => 0,
            '65+' => 0,
        ];

        $patients = (clone $patientsBase)->whereNotNull('date_of_birth')->get(['date_of_birth']);
        foreach ($patients as $patient) {
            $age = $patient->date_of_birth ? $patient->date_of_birth->age : 0;
            if ($age <= 18) $ageGroups['0-18']++;
            elseif ($age <= 35) $ageGroups['19-35']++;
            elseif ($age <= 50) $ageGroups['36-50']++;
            elseif ($age <= 65) $ageGroups['51-65']++;
            else $ageGroups['65+']++;
        }

        return [
            'total' => $totalPatients,
            'active' => $activePatients,
            'new' => $newPatients,
            'gender' => $genderStats,
            'age_groups' => $ageGroups,
        ];
    }

    /**
     * Get prescription statistics
     */
    private function getPrescriptionStats($from, $to, $clinicId): array
    {
        $prescriptionsBase = Prescription::query();
        if ($from) { $prescriptionsBase->whereDate('created_at', '>=', $from->toDateString()); }
        if ($to) { $prescriptionsBase->whereDate('created_at', '<=', $to->toDateString()); }
        if ($clinicId) { $prescriptionsBase->where('clinic_id', $clinicId); }

        $totalPrescriptions = (clone $prescriptionsBase)->count();
        $activePrescriptions = (clone $prescriptionsBase)->where('status', 'active')->count();
        $completedPrescriptions = (clone $prescriptionsBase)->where('status', 'completed')->count();

        // Most prescribed medicines
        $topMedicines = DB::table('prescription_medicines')
            ->join('prescriptions', 'prescription_medicines.prescription_id', '=', 'prescriptions.id')
            ->join('medicines', 'prescription_medicines.medicine_id', '=', 'medicines.id')
            ->when($from, function($q) use ($from) {
                return $q->whereDate('prescriptions.created_at', '>=', $from->toDateString());
            })
            ->when($to, function($q) use ($to) {
                return $q->whereDate('prescriptions.created_at', '<=', $to->toDateString());
            })
            ->when($clinicId, function($q) use ($clinicId) {
                return $q->where('prescriptions.clinic_id', $clinicId);
            })
            ->select('medicines.name', DB::raw('COUNT(*) as count'))
            ->groupBy('medicines.id', 'medicines.name')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return [
            'total' => $totalPrescriptions,
            'active' => $activePrescriptions,
            'completed' => $completedPrescriptions,
            'top_medicines' => $topMedicines,
        ];
    }

    /**
     * Get lab test statistics
     */
    private function getLabStats($from, $to, $clinicId): array
    {
        $labRequestsBase = LabRequest::query();
        if ($from) { $labRequestsBase->whereDate('lab_requests.created_at', '>=', $from->toDateString()); }
        if ($to) { $labRequestsBase->whereDate('lab_requests.created_at', '<=', $to->toDateString()); }
        if ($clinicId) {
            $labRequestsBase->whereHas('patient', function($q) use ($clinicId) {
                $q->where('clinic_id', $clinicId);
            });
        }

        $totalRequests = (clone $labRequestsBase)->count();
        $pendingRequests = (clone $labRequestsBase)->where('status', 'pending')->count();
        $completedRequests = (clone $labRequestsBase)->where('status', 'completed')->count();

        // Most requested tests - handle case where tables might not exist
        $topTests = collect();
        try {
            if (Schema::hasTable('lab_request_tests') && Schema::hasTable('lab_tests')) {
                $topTests = DB::table('lab_request_tests')
                    ->join('lab_requests', 'lab_request_tests.lab_request_id', '=', 'lab_requests.id')
                    ->join('lab_tests', 'lab_request_tests.lab_test_id', '=', 'lab_tests.id')
                    ->when($from, function($q) use ($from) {
                        return $q->whereDate('lab_requests.created_at', '>=', $from->toDateString());
                    })
                    ->when($to, function($q) use ($to) {
                        return $q->whereDate('lab_requests.created_at', '<=', $to->toDateString());
                    })
                    ->when($clinicId, function($q) use ($clinicId) {
                        return $q->join('patients', 'lab_requests.patient_id', '=', 'patients.id')
                                 ->where('patients.clinic_id', $clinicId);
                    })
                    ->select('lab_tests.name', DB::raw('COUNT(*) as count'))
                    ->groupBy('lab_tests.id', 'lab_tests.name')
                    ->orderByDesc('count')
                    ->limit(10)
                    ->get();
            }
        } catch (\Exception $e) {
            // If there's an error with the complex query, return empty collection
            $topTests = collect();
        }

        return [
            'total' => $totalRequests,
            'pending' => $pendingRequests,
            'completed' => $completedRequests,
            'top_tests' => $topTests,
        ];
    }

    /**
     * Get appointment statistics
     */
    private function getAppointmentStats($from, $to, $clinicId): array
    {
        $appointmentsBase = Appointment::query();
        if ($from) { $appointmentsBase->whereDate('appointments.created_at', '>=', $from->toDateString()); }
        if ($to) { $appointmentsBase->whereDate('appointments.created_at', '<=', $to->toDateString()); }
        if ($clinicId) { $appointmentsBase->where('clinic_id', $clinicId); }

        $totalAppointments = (clone $appointmentsBase)->count();
        $scheduledAppointments = (clone $appointmentsBase)->where('status', 'scheduled')->count();
        $completedAppointments = (clone $appointmentsBase)->where('status', 'completed')->count();
        $cancelledAppointments = (clone $appointmentsBase)->where('status', 'cancelled')->count();

        // Appointment types - handle gracefully if no data
        $typeStats = [];
        try {
            $typeStats = (clone $appointmentsBase)
                ->select('type', DB::raw('COUNT(*) as count'))
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray();
        } catch (\Exception $e) {
            $typeStats = [];
        }

        // Format type labels for display
        $typeLabels = [];
        $typeValues = [];
        foreach ($typeStats as $type => $count) {
            $typeLabels[] = ucfirst(str_replace('_', ' ', $type));
            $typeValues[] = $count;
        }

        return [
            'total' => $totalAppointments,
            'scheduled' => $scheduledAppointments,
            'completed' => $completedAppointments,
            'cancelled' => $cancelledAppointments,
            'types' => $typeStats,
            'type_labels' => $typeLabels,
            'type_values' => $typeValues,
        ];
    }

    /**
     * Get financial statistics
     */
    private function getFinancialStats($from, $to, $clinicId): array
    {
        $invoicesBase = Invoice::query();
        if ($from) { $invoicesBase->whereDate('created_at', '>=', $from->toDateString()); }
        if ($to) { $invoicesBase->whereDate('created_at', '<=', $to->toDateString()); }
        if ($clinicId) { $invoicesBase->where('clinic_id', $clinicId); }
        // Exclude demo clinics from financial stats (guard if column exists)
        if (Schema::hasColumn('clinics', 'is_demo')) {
            $invoicesBase->whereHas('clinic', function ($q) {
                $q->where('is_demo', false);
            });
        }

        $totalInvoices = (clone $invoicesBase)->count();
        $totalRevenue = (clone $invoicesBase)->sum('total_amount');
        $paidAmount = (clone $invoicesBase)->sum('paid_amount');
        $outstandingAmount = (clone $invoicesBase)->sum('balance');

        // Payment status
        $statusStats = (clone $invoicesBase)
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'total_invoices' => $totalInvoices,
            'total_revenue' => $totalRevenue,
            'paid_amount' => $paidAmount,
            'outstanding' => $outstandingAmount,
            'status' => $statusStats,
        ];
    }

    private function parseDate($value): ?Carbon
    {
        if (!$value) return null;
        try {
            return Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Show Login/Logout Activity Report
     */
    public function loginActivity(Request $request)
    {
        // Log access to this report
        AuditLog::create([
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->full_name,
            'user_role' => Auth::user()->role,
            'clinic_id' => Auth::user()->clinic_id,
            'action' => 'view_login_activity_report',
            'description' => 'Accessed Login/Logout Activity Report',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'performed_at' => now(),
        ]);

        // Get filter parameters
        $filters = [
            'from' => $request->query('from', now()->subDays(30)->format('Y-m-d')),
            'to' => $request->query('to', now()->format('Y-m-d')),
            'clinic_id' => $request->query('clinic_id'),
            'user_id' => $request->query('user_id'),
            'role' => $request->query('role'),
        ];

        $from = $this->parseDate($filters['from']);
        $to = $this->parseDate($filters['to']);

        // Get all login events
        $loginQuery = AuditLog::where('action', 'login')
            ->when($from, fn($q) => $q->whereDate('performed_at', '>=', $from->toDateString()))
            ->when($to, fn($q) => $q->whereDate('performed_at', '<=', $to->toDateString()))
            ->when($filters['clinic_id'], fn($q) => $q->where('clinic_id', $filters['clinic_id']))
            ->when($filters['user_id'], fn($q) => $q->where('user_id', $filters['user_id']))
            ->when($filters['role'], fn($q) => $q->where('user_role', $filters['role']))
            ->with(['user', 'clinic'])
            ->orderBy('performed_at', 'desc');

        // Paginate results
        $sessions = $loginQuery->paginate(50)->through(function ($login) {
            // Find the corresponding logout
            $logout = AuditLog::where('action', 'logout')
                ->where('user_id', $login->user_id)
                ->where('performed_at', '>', $login->performed_at)
                ->orderBy('performed_at', 'asc')
                ->first();

            $duration = null;
            $status = 'Active Session';

            if ($logout) {
                $duration = $login->performed_at->diffInMinutes($logout->performed_at);
                $status = 'Completed';
            }

            return (object) [
                'user_id' => $login->user_id,
                'user_name' => $login->user_name,
                'user_role' => $login->user_role,
                'clinic_id' => $login->clinic_id,
                'clinic_name' => $login->clinic?->name ?? 'N/A',
                'login_at' => $login->performed_at,
                'logout_at' => $logout?->performed_at,
                'duration_minutes' => $duration,
                'duration_formatted' => $duration ? $this->formatDuration($duration) : null,
                'ip_address' => $login->ip_address,
                'status' => $status,
            ];
        });

        // Get summary statistics
        $stats = $this->getLoginActivityStats($from, $to, $filters);

        // Get filter options
        $clinics = Clinic::orderBy('name')->get(['id', 'name']);
        $users = User::whereNotIn('role', ['super_admin', 'master_admin'])
            ->when($filters['clinic_id'], fn($q) => $q->where('clinic_id', $filters['clinic_id']))
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'role']);

        $roles = User::ROLES;
        unset($roles['super_admin'], $roles['master_admin']);

        return view('master.reports.login-activity', compact(
            'sessions',
            'filters',
            'stats',
            'clinics',
            'users',
            'roles'
        ));
    }

    /**
     * Export Login/Logout Activity Report to CSV
     */
    public function exportLoginActivity(Request $request)
    {
        // Log export action
        AuditLog::create([
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->full_name,
            'user_role' => Auth::user()->role,
            'clinic_id' => Auth::user()->clinic_id,
            'action' => 'export_login_activity_report',
            'description' => 'Exported Login/Logout Activity Report to CSV',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'performed_at' => now(),
        ]);

        $filters = [
            'from' => $request->query('from', now()->subDays(30)->format('Y-m-d')),
            'to' => $request->query('to', now()->format('Y-m-d')),
            'clinic_id' => $request->query('clinic_id'),
            'user_id' => $request->query('user_id'),
            'role' => $request->query('role'),
        ];

        $from = $this->parseDate($filters['from']);
        $to = $this->parseDate($filters['to']);

        // Get all login events
        $logins = AuditLog::where('action', 'login')
            ->when($from, fn($q) => $q->whereDate('performed_at', '>=', $from->toDateString()))
            ->when($to, fn($q) => $q->whereDate('performed_at', '<=', $to->toDateString()))
            ->when($filters['clinic_id'], fn($q) => $q->where('clinic_id', $filters['clinic_id']))
            ->when($filters['user_id'], fn($q) => $q->where('user_id', $filters['user_id']))
            ->when($filters['role'], fn($q) => $q->where('user_role', $filters['role']))
            ->with(['user', 'clinic'])
            ->orderBy('performed_at', 'desc')
            ->get();

        $filename = 'login_activity_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($logins) {
            $file = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($file, [
                'User Name',
                'Role',
                'Clinic',
                'Login Date',
                'Login Time',
                'Logout Date',
                'Logout Time',
                'Duration',
                'IP Address',
                'Status'
            ]);

            foreach ($logins as $login) {
                // Find corresponding logout
                $logout = AuditLog::where('action', 'logout')
                    ->where('user_id', $login->user_id)
                    ->where('performed_at', '>', $login->performed_at)
                    ->orderBy('performed_at', 'asc')
                    ->first();

                $duration = null;
                $status = 'Active Session';

                if ($logout) {
                    $durationMinutes = $login->performed_at->diffInMinutes($logout->performed_at);
                    $duration = $this->formatDuration($durationMinutes);
                    $status = 'Completed';
                }

                fputcsv($file, [
                    $login->user_name,
                    ucfirst($login->user_role ?? 'N/A'),
                    $login->clinic?->name ?? 'N/A',
                    $login->performed_at->format('Y-m-d'),
                    $login->performed_at->format('H:i:s'),
                    $logout?->performed_at->format('Y-m-d') ?? '',
                    $logout?->performed_at->format('H:i:s') ?? '',
                    $duration ?? '',
                    $login->ip_address ?? '',
                    $status
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get login activity statistics
     */
    private function getLoginActivityStats($from, $to, $filters)
    {
        $baseQuery = AuditLog::where('action', 'login')
            ->when($from, fn($q) => $q->whereDate('performed_at', '>=', $from->toDateString()))
            ->when($to, fn($q) => $q->whereDate('performed_at', '<=', $to->toDateString()))
            ->when($filters['clinic_id'], fn($q) => $q->where('clinic_id', $filters['clinic_id']))
            ->when($filters['user_id'], fn($q) => $q->where('user_id', $filters['user_id']))
            ->when($filters['role'], fn($q) => $q->where('user_role', $filters['role']));

        $totalSessions = (clone $baseQuery)->count();
        $uniqueUsers = (clone $baseQuery)->distinct('user_id')->count('user_id');

        // Calculate average session duration
        $logins = (clone $baseQuery)->get();
        $totalDuration = 0;
        $completedSessions = 0;

        foreach ($logins as $login) {
            $logout = AuditLog::where('action', 'logout')
                ->where('user_id', $login->user_id)
                ->where('performed_at', '>', $login->performed_at)
                ->orderBy('performed_at', 'asc')
                ->first();

            if ($logout) {
                $totalDuration += $login->performed_at->diffInMinutes($logout->performed_at);
                $completedSessions++;
            }
        }

        $avgDuration = $completedSessions > 0 ? round($totalDuration / $completedSessions) : 0;
        $activeSessions = $totalSessions - $completedSessions;

        // Sessions by role
        $sessionsByRole = (clone $baseQuery)
            ->selectRaw('user_role, COUNT(*) as count')
            ->groupBy('user_role')
            ->pluck('count', 'user_role')
            ->toArray();

        // Sessions by clinic
        $sessionsByClinic = (clone $baseQuery)
            ->join('clinics', 'audit_logs.clinic_id', '=', 'clinics.id')
            ->selectRaw('clinics.name, COUNT(*) as count')
            ->groupBy('clinics.id', 'clinics.name')
            ->orderByDesc('count')
            ->limit(10)
            ->pluck('count', 'name')
            ->toArray();

        return [
            'total_sessions' => $totalSessions,
            'unique_users' => $uniqueUsers,
            'completed_sessions' => $completedSessions,
            'active_sessions' => $activeSessions,
            'avg_duration' => $avgDuration,
            'avg_duration_formatted' => $this->formatDuration($avgDuration),
            'sessions_by_role' => $sessionsByRole,
            'sessions_by_clinic' => $sessionsByClinic,
        ];
    }

    /**
     * Format duration in minutes to human-readable format
     */
    private function formatDuration($minutes)
    {
        if ($minutes < 60) {
            return $minutes . ' min';
        }

        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        if ($hours < 24) {
            return $hours . 'h ' . $mins . 'm';
        }

        $days = floor($hours / 24);
        $hours = $hours % 24;

        return $days . 'd ' . $hours . 'h ' . $mins . 'm';
    }
}
