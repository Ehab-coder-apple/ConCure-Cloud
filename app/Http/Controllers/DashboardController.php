<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Prescription;
use App\Models\LabRequest;
use App\Models\DentalLabRequest;
use App\Models\DietPlan;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Receipt;
use App\Models\User;
use App\Models\AuditLog;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Redirect lab technicians to their dedicated dashboard
        if ($user->role === 'lab_dept') {
            return redirect()->route('recommendations.lab-technician.dashboard');
        }

        // Redirect radiology technicians to their dedicated dashboard
        if ($user->role === 'radiology_dept') {
            return redirect()->route('recommendations.radiology-technician.dashboard');
        }

        // DEBUG: Log assistant doctor assignments
        if ($user->role === 'assistant') {
            \Log::info('Assistant Dashboard Debug', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'doctors_count' => $user->doctors()->count(),
                'doctor_ids' => $user->doctors()->pluck('users.id')->toArray(),
                'allowed_doctor_ids' => $user->allowedDoctorIds(),
            ]);
        }

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

        // Get clinic currency setting
        $currency = DB::table('settings')
            ->where('clinic_id', $user->clinic_id)
            ->where('key', 'currency')
            ->value('value') ?? 'USD';

        $dashboardData['currencySymbol'] = $this->getCurrencySymbol($currency);
        $dashboardData['currency'] = $currency;

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

            // Filter for doctors: show only their own patients
            if ($user->role === 'doctor') {
                $patientsQuery->where(function($q) use ($user) {
                    $q->where('created_by', $user->id)
                    ->orWhereHas('appointments', function($subQ) use ($user) {
                        $subQ->where('doctor_id', $user->id);
                    })
                    ->orWhereHas('prescriptions', function($subQ) use ($user) {
                        $subQ->where('doctor_id', $user->id);
                    })
                    ->orWhereHas('labRequests', function($subQ) use ($user) {
                        $subQ->where('doctor_id', $user->id);
                    })
                    ->orWhereHas('dietPlans', function($subQ) use ($user) {
                        $subQ->where('doctor_id', $user->id);
                    });
                });
            }
            // Filter for dentists: show patients they created or have dental records for
            elseif ($user->role === 'dental_dept') {
                $patientsQuery->where(function($q) use ($user) {
                    $q->where('created_by', $user->id)
                    ->orWhereHas('dentalCharts', function($subQ) use ($user) {
                        $subQ->where('created_by', $user->id);
                    })
                    ->orWhereHas('dentalTreatments', function($subQ) use ($user) {
                        $subQ->where('created_by', $user->id);
                    });
                });
            }
            // Filter for assistants: show patients who have any interaction with their assigned doctors
            // (appointments, prescriptions, lab requests, or diet plans)
            elseif ($user->role === 'assistant') {
                $doctorIds = $user->allowedDoctorIds();
                if (!empty($doctorIds)) {
                    $patientsQuery->where(function($q) use ($doctorIds) {
                        $q->whereHas('appointments', function($subQ) use ($doctorIds) {
                            $subQ->whereIn('doctor_id', $doctorIds);
                        })
                        ->orWhereHas('prescriptions', function($subQ) use ($doctorIds) {
                            $subQ->whereIn('doctor_id', $doctorIds);
                        })
                        ->orWhereHas('labRequests', function($subQ) use ($doctorIds) {
                            $subQ->whereIn('doctor_id', $doctorIds);
                        })
                        ->orWhereHas('dietPlans', function($subQ) use ($doctorIds) {
                            $subQ->whereIn('doctor_id', $doctorIds);
                        });
                    });
                } else {
                    $patientsQuery->whereRaw('1 = 0');
                }
            }

            $data['totalPatients'] = (clone $patientsQuery)->active()->count();
            $data['newPatientsThisMonth'] = (clone $patientsQuery)->active();
            $data['newPatientsThisMonth'] = $applyPeriod($data['newPatientsThisMonth'], 'created_at')->count();

            // New patients added today
            $data['newPatientsToday'] = (clone $patientsQuery)
                ->active()
                ->whereDate('created_at', now()->toDateString())
                ->count();
        }

        // Prescription statistics (include Simple Prescriptions if available)
        if ($user->canPrescribe() || $user->canManagePatients() ) {
            $prescriptionsQuery = Prescription::query();
            $prescriptionsQuery->whereHas('patient', function ($q) use ($user) {
                $q->where('clinic_id', $user->clinic_id);
            });

            // Filter for doctors: only show their own prescriptions
            if ($user->role === 'doctor') {
                $prescriptionsQuery->where('doctor_id', $user->id);
            }
            // Filter for dentists: only show their own prescriptions
            elseif ($user->role === 'dental_dept') {
                $prescriptionsQuery->where('doctor_id', $user->id);
            }
            // Filter for assistants: only show prescriptions from their assigned doctors
            elseif ($user->role === 'assistant') {
                $doctorIds = $user->allowedDoctorIds();
                if (!empty($doctorIds)) {
                    $prescriptionsQuery->whereIn('doctor_id', $doctorIds);
                } else {
                    $prescriptionsQuery->whereRaw('1 = 0');
                }
            }

            $activeCount = (clone $prescriptionsQuery)->active()->count();
            $thisPeriodCount = $applyPeriod((clone $prescriptionsQuery), 'prescribed_date')->count();

            // Also count simple_prescriptions for this clinic if the model/table exists
            if (class_exists(\App\Models\SimplePrescription::class) && \Illuminate\Support\Facades\Schema::hasTable('simple_prescriptions')) {
                $spBase = \App\Models\SimplePrescription::query()->where('clinic_id', $user->clinic_id);

                // Filter for doctors
                if ($user->role === 'doctor') {
                    $spBase->where('doctor_id', $user->id);
                }
                // Filter for dentists
                elseif ($user->role === 'dental_dept') {
                    $spBase->where('doctor_id', $user->id);
                }
                // Filter for assistants
                elseif ($user->role === 'assistant') {
                    $doctorIds = $user->allowedDoctorIds();
                    if (!empty($doctorIds)) {
                        $spBase->whereIn('doctor_id', $doctorIds);
                    } else {
                        $spBase->whereRaw('1 = 0');
                    }
                }

                $activeCount += (clone $spBase)->where('status', 'active')->count();
                $thisPeriodCount += $applyPeriod((clone $spBase), 'prescribed_date')->count();
            }

            $data['activePrescriptions'] = $activeCount;
            $data['prescriptionsThisMonth'] = $thisPeriodCount;
        }

        // Lab request statistics
        if ($user->canPrescribe() || $user->canManagePatients() ) {
            $labRequestsQuery = LabRequest::query();
            $labRequestsQuery->whereHas('patient', function ($q) use ($user) {
                $q->where('clinic_id', $user->clinic_id);
            });

            // Filter for doctors: only show their own lab requests
            if ($user->role === 'doctor') {
                $labRequestsQuery->where('doctor_id', $user->id);
            }
            // Filter for assistants: only show lab requests from their assigned doctors
            elseif ($user->role === 'assistant') {
                $doctorIds = $user->allowedDoctorIds();
                if (!empty($doctorIds)) {
                    $labRequestsQuery->whereIn('doctor_id', $doctorIds);
                } else {
                    $labRequestsQuery->whereRaw('1 = 0');
                }
            }

            $data['pendingLabRequests'] = (clone $labRequestsQuery)->pending()->count();
            $data['urgentLabRequests'] = (clone $labRequestsQuery)->pending()
                ->where('priority', 'urgent')
                ->count();
        }

        // Dental lab request statistics (pending only)
        if ($user->canPrescribe() || $user->canManagePatients() ) {
            $dentalLabRequestsQuery = DentalLabRequest::query();
            $dentalLabRequestsQuery->where('clinic_id', $user->clinic_id);

            // Filter for doctors: only show their own dental lab requests
            if ($user->role === 'doctor') {
                $dentalLabRequestsQuery->where('doctor_id', $user->id);
            }
            // Filter for dentists: only show their own dental lab requests
            elseif ($user->role === 'dental_dept') {
                $dentalLabRequestsQuery->where('doctor_id', $user->id);
            }
            // Filter for assistants: only show dental lab requests from their assigned doctors
            elseif ($user->role === 'assistant') {
                $doctorIds = $user->allowedDoctorIds();
                if (!empty($doctorIds)) {
                    $dentalLabRequestsQuery->whereIn('doctor_id', $doctorIds);
                } else {
                    $dentalLabRequestsQuery->whereRaw('1 = 0');
                }
            }

            $data['pendingDentalLabRequests'] = (clone $dentalLabRequestsQuery)
                ->where('status', 'pending')
                ->count();
            $data['urgentDentalLabRequests'] = (clone $dentalLabRequestsQuery)
                ->where('status', 'pending')
                ->where('priority', 'urgent')
                ->count();
        }

        // Lab request statistics (completed with results uploaded) - includes both dental and regular lab requests
        if ($user->canPrescribe() || $user->canManagePatients() ) {
            $completedLabCount = 0;

            // Count completed dental lab requests with results uploaded
            $dentalLabRequestsQuery = DentalLabRequest::query();
            $dentalLabRequestsQuery->where('clinic_id', $user->clinic_id);

            // Filter for doctors: only show their own dental lab requests
            if ($user->role === 'doctor') {
                $dentalLabRequestsQuery->where('doctor_id', $user->id);
            }
            // Filter for dentists: only show their own dental lab requests
            elseif ($user->role === 'dental_dept') {
                $dentalLabRequestsQuery->where('doctor_id', $user->id);
            }
            // Filter for assistants: only show dental lab requests from their assigned doctors
            elseif ($user->role === 'assistant') {
                $doctorIds = $user->allowedDoctorIds();
                if (!empty($doctorIds)) {
                    $dentalLabRequestsQuery->whereIn('doctor_id', $doctorIds);
                } else {
                    $dentalLabRequestsQuery->whereRaw('1 = 0');
                }
            }

            $completedDentalLab = (clone $dentalLabRequestsQuery)
                ->where('status', 'completed')
                ->whereNotNull('result_file_path')
                ->count();

            // Count completed regular lab requests with results uploaded
            $labRequestsQuery = LabRequest::query();
            $labRequestsQuery->whereHas('patient', function ($q) use ($user) {
                $q->where('clinic_id', $user->clinic_id);
            });

            // Filter for doctors: only show their own lab requests
            if ($user->role === 'doctor') {
                $labRequestsQuery->where('doctor_id', $user->id);
            }
            // Filter for assistants: only show lab requests from their assigned doctors
            elseif ($user->role === 'assistant') {
                $doctorIds = $user->allowedDoctorIds();
                if (!empty($doctorIds)) {
                    $labRequestsQuery->whereIn('doctor_id', $doctorIds);
                } else {
                    $labRequestsQuery->whereRaw('1 = 0');
                }
            }

            $completedRegularLab = (clone $labRequestsQuery)
                ->where('status', 'completed')
                ->whereNotNull('result_file_path')
                ->count();

            // Total completed lab requests (both dental and regular)
            $data['completedLabRequests'] = $completedDentalLab + $completedRegularLab;
        }

        // Diet plan statistics
        if ($user->canPrescribe() || $user->canManagePatients() ) {
            $dietPlansQuery = DietPlan::query();
            $dietPlansQuery->whereHas('patient', function ($q) use ($user) {
                $q->where('clinic_id', $user->clinic_id);
            });

            // Filter for doctors: only show their own diet plans
            if ($user->role === 'doctor') {
                $dietPlansQuery->where('doctor_id', $user->id);
            }
            // Filter for assistants: only show diet plans from their assigned doctors
            elseif ($user->role === 'assistant') {
                $doctorIds = $user->allowedDoctorIds();
                if (!empty($doctorIds)) {
                    $dietPlansQuery->whereIn('doctor_id', $doctorIds);
                } else {
                    $dietPlansQuery->whereRaw('1 = 0');
                }
            }

            $data['activeDietPlans'] = (clone $dietPlansQuery)->active()->count();
            $data['expiredDietPlans'] = (clone $dietPlansQuery)->expired()->count();
        }

        // Financial statistics
        if ($user->canAccessFinance() ) {
            $invoicesQuery = Invoice::query();
            $invoicesQuery->where('clinic_id', $user->clinic_id);

            $receiptsQuery = Receipt::query();
            $receiptsQuery->where('clinic_id', $user->clinic_id);

            // Total revenue includes both invoices and approved receipts
            $invoiceRevenue = $applyPeriod((clone $invoicesQuery), 'invoice_date')->sum('total_amount');
            $receiptRevenue = $applyPeriod((clone $receiptsQuery)->where('status', 'approved'), 'receipt_date')->sum('amount');
            $data['totalRevenue'] = $invoiceRevenue + $receiptRevenue;

            $data['pendingInvoices'] = (clone $invoicesQuery)
                ->whereIn('status', ['draft', 'sent'])
                ->count();

            $data['overdueInvoices'] = (clone $invoicesQuery)
                ->where('status', 'sent')
                ->where('due_date', '<', now())
                ->count();
        }

        // User statistics (for admins and program owners)
        if ($user->canManageUsers()) {
            $usersQuery = User::query();
            $usersQuery->where('clinic_id', $user->clinic_id);

            $data['totalUsers'] = (clone $usersQuery)->active()->count();
            $data['newUsersThisMonth'] = $applyPeriod((clone $usersQuery)->active(), 'created_at')->count();
        }

        // Recent activity
        $data['recentActivity'] = $this->getRecentActivity($user);

        // Appointment statistics (schema-aware)
        if (class_exists('App\\Models\\Appointment')) {
            $legacy = $this->isLegacyAppointments();
            if ($legacy) {
                $base = DB::table('appointments')->where('clinic_id', $user->clinic_id);
                if ($user->role === 'doctor') {
                    $base->where('doctor_id', $user->id);
                } elseif ($user->role === 'assistant') {
                    $doctorIds = $user->allowedDoctorIds();
                    if (!empty($doctorIds)) {
                        $base->whereIn('doctor_id', $doctorIds);
                    } else {
                        $base->whereRaw('1 = 0');
                    }
                }
                $now = Carbon::now();
                $data['totalAppointments'] = (clone $base)->count();
                $data['todayAppointments'] = (clone $base)
                    ->whereDate('appointment_date', now()->toDateString())
                    ->count();
                $data['upcomingAppointments'] = (clone $base)
                    ->whereIn('status', ['scheduled'])
                    ->whereRaw("STR_TO_DATE(CONCAT(appointment_date,' ', appointment_time), '%Y-%m-%d %H:%i:%s') > ?", [$now->format('Y-m-d H:i:s')])
                    ->count();
            } else {
                $appointmentsQuery = Appointment::query();
                $appointmentsQuery->where('clinic_id', $user->clinic_id);
                if ($user->role === 'doctor') {
                    $appointmentsQuery->where('doctor_id', $user->id);
                } elseif ($user->role === 'assistant') {
                    $doctorIds = $user->allowedDoctorIds();
                    if (!empty($doctorIds)) {
                        $appointmentsQuery->whereIn('doctor_id', $doctorIds);
                    } else {
                        $appointmentsQuery->whereRaw('1 = 0');
                    }
                }
                $data['totalAppointments'] = (clone $appointmentsQuery)->count();
                $data['todayAppointments'] = (clone $appointmentsQuery)
                    ->whereDate('appointment_datetime', now()->toDateString())
                    ->count();
                $data['upcomingAppointments'] = (clone $appointmentsQuery)
                    ->where('appointment_datetime', '>', now())
                    ->where('status', 'scheduled')
                    ->count();
            }
        }

        // Nutrition plan statistics (exclude dental_dept role)
        if (class_exists('App\Models\DietPlan') && $user->role !== 'dental_dept') {
            $nutritionQuery = \App\Models\DietPlan::query();
            $nutritionQuery->whereHas('patient', function ($q) use ($user) {
                $q->where('clinic_id', $user->clinic_id);

                // Filter for assistants: only show patients of their assigned doctors
                if ($user->role === 'assistant') {
                    $doctorIds = $user->allowedDoctorIds();
                    if (!empty($doctorIds)) {
                        $q->whereIn('doctor_id', $doctorIds);
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                }
            });
            if ($user->role === 'doctor') {
                $nutritionQuery->where('doctor_id', $user->id);
            } elseif ($user->role === 'assistant') {
                $doctorIds = $user->allowedDoctorIds();
                if (!empty($doctorIds)) {
                    $nutritionQuery->whereIn('doctor_id', $doctorIds);
                } else {
                    $nutritionQuery->whereRaw('1 = 0');
                }
            }

            $data['totalNutritionPlans'] = (clone $nutritionQuery)->count();
            $data['activeNutritionPlans'] = (clone $nutritionQuery)->where('status', 'active')->count();
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
     * Detect legacy appointment schema (separate date/time columns) vs unified datetime.
     */
    private function isLegacyAppointments(): bool
    {
        // Legacy if no appointment_datetime or if appointment_date/time exist
        $hasDatetime = Schema::hasColumn('appointments', 'appointment_datetime');
        $hasDate = Schema::hasColumn('appointments', 'appointment_date');
        $hasTime = Schema::hasColumn('appointments', 'appointment_time');
        return !$hasDatetime && ($hasDate || $hasTime);
    }



    /**
     * Get upcoming appointments.
     */
    private function getUpcomingAppointments($user): array
    {
        if (!class_exists('App\Models\Appointment')) {
            return [];
        }

        $legacy = $this->isLegacyAppointments();
        if ($legacy) {
            $now = Carbon::now();
            $q = DB::table('appointments')
                ->leftJoin('patients', 'appointments.patient_id', '=', 'patients.id')
                ->leftJoin('users as doctors', 'appointments.doctor_id', '=', 'doctors.id')
                ->where('appointments.clinic_id', $user->clinic_id)
                ->where('appointments.status', 'scheduled');
            if ($user->role === 'patient') {
                $q->where('appointments.patient_id', $user->patient_id ?? 0);
            } elseif ($user->role === 'doctor') {
                $q->where('appointments.doctor_id', $user->id);
            } elseif ($user->role === 'assistant') {
                $doctorIds = $user->allowedDoctorIds();
                if (!empty($doctorIds)) {
                    $q->whereIn('appointments.doctor_id', $doctorIds);
                } else {
                    $q->whereRaw('1 = 0');
                }
            }
            $rows = $q->whereRaw("STR_TO_DATE(CONCAT(appointment_date,' ', appointment_time), '%Y-%m-%d %H:%i:%s') >= ?", [$now->format('Y-m-d H:i:s')])
                ->orderBy('appointment_date')->orderBy('appointment_time')
                ->limit(5)
                ->get([
                    'appointments.id','appointments.status','appointments.appointment_date','appointments.appointment_time',
                    'patients.first_name as patient_first_name','patients.last_name as patient_last_name',
                    'doctors.first_name as doctor_first_name','doctors.last_name as doctor_last_name'
                ]);
            return $rows->map(function($r){
                return [
                    'id' => $r->id,
                    'status' => $r->status,
                    'appointment_datetime' => trim(($r->appointment_date ?? '').' '.($r->appointment_time ?? '')),
                    'patient' => ['first_name' => $r->patient_first_name, 'last_name' => $r->patient_last_name],
                    'doctor' => ['first_name' => $r->doctor_first_name, 'last_name' => $r->doctor_last_name],
                ];
            })->toArray();
        }

        $query = Appointment::with(['patient', 'doctor']);
        if ($user->role === 'patient') {
            $query->where('patient_id', $user->patient_id ?? 0);
        } else {
            $query->where('clinic_id', $user->clinic_id);
            if ($user->role === 'doctor') {
                $query->where('doctor_id', $user->id);
            } elseif ($user->role === 'assistant') {
                $doctorIds = $user->allowedDoctorIds();
                if (!empty($doctorIds)) {
                    $query->whereIn('doctor_id', $doctorIds);
                } else {
                    $query->whereRaw('1 = 0');
                }
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
        $legacy = $this->isLegacyAppointments();

        // Get appointments for the next 7 days
        for ($i = 0; $i < 7; $i++) {
            $date = now()->addDays($i);
            $dateKey = $date->toDateString();
            $dateLabel = $date->format('l, M j');

            if ($legacy) {
                $q = DB::table('appointments')
                    ->leftJoin('patients', 'appointments.patient_id', '=', 'patients.id')
                    ->leftJoin('users as doctors', 'appointments.doctor_id', '=', 'doctors.id')
                    ->where('appointments.clinic_id', $user->clinic_id)
                    ->whereDate('appointments.appointment_date', $dateKey);
                if ($user->role === 'patient') {
                    $q->where('appointments.patient_id', $user->patient_id ?? 0);
                } elseif ($user->role === 'doctor') {
                    $q->where('appointments.doctor_id', $user->id);
                } elseif ($user->role === 'assistant') {
                    $doctorIds = $user->allowedDoctorIds();
                    if (!empty($doctorIds)) {
                        $q->whereIn('appointments.doctor_id', $doctorIds);
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                }
                $rows = $q->orderBy('appointments.appointment_time')
                    ->get([
                        'appointments.id','appointments.status','appointments.appointment_date','appointments.appointment_time',
                        'patients.first_name as patient_first_name','patients.last_name as patient_last_name',
                        'doctors.first_name as doctor_first_name','doctors.last_name as doctor_last_name'
                    ]);
                $dayAppointments = $rows->map(function($r){
                    return [
                        'id' => $r->id,
                        'status' => $r->status,
                        'appointment_datetime' => trim(($r->appointment_date ?? '').' '.($r->appointment_time ?? '')),
                        'patient' => ['first_name' => $r->patient_first_name, 'last_name' => $r->patient_last_name],
                        'doctor' => ['first_name' => $r->doctor_first_name, 'last_name' => $r->doctor_last_name],
                    ];
                })->toArray();
            } else {
                $query = Appointment::with(['patient', 'doctor']);
                if ($user->role === 'patient') {
                    $query->where('patient_id', $user->patient_id ?? 0);
                } else {
                    $query->where('clinic_id', $user->clinic_id);
                    if ($user->role === 'doctor') {
                        $query->where('doctor_id', $user->id);
                    } elseif ($user->role === 'assistant') {
                        $doctorIds = $user->allowedDoctorIds();
                        if (!empty($doctorIds)) {
                            $query->whereIn('doctor_id', $doctorIds);
                        } else {
                            $query->whereRaw('1 = 0');
                        }
                    }
                }
                $dayAppointments = $query->whereDate('appointment_datetime', $dateKey)
                                       ->orderBy('appointment_datetime')
                                       ->get()
                                       ->toArray();
            }

            if (!empty($dayAppointments) || $i === 0) {
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
                    $patientsQuery = Patient::where('clinic_id', $user->clinic_id)
                        ->whereDate('created_at', $day->toDateString());

                    // Filter for assistants

                    $stats[$key]['patients'] = $patientsQuery->count();
                }
                if ($user->canPrescribe() || $user->canManagePatients()) {
                    $rxCount = Prescription::whereHas('patient', function ($q) use ($user) {
                            $q->where('clinic_id', $user->clinic_id);

                            // Filter for assistants
                            if ($user->role === 'assistant') {
                                $doctorIds = $user->allowedDoctorIds();
                                if (!empty($doctorIds)) {
                                    $q->whereIn('doctor_id', $doctorIds);
                                } else {
                                    $q->whereRaw('1 = 0');
                                }
                            }
                        })
                        ->whereDate('prescribed_date', $day->toDateString())
                        ->count();
                    if (class_exists(\App\Models\SimplePrescription::class) && \Illuminate\Support\Facades\Schema::hasTable('simple_prescriptions')) {
                        $spQuery = \App\Models\SimplePrescription::where('clinic_id', $user->clinic_id)
                            ->whereDate('prescribed_date', $day->toDateString());

                        // Filter for assistants
                        if ($user->role === 'assistant') {
                            $doctorIds = $user->allowedDoctorIds();
                            if (!empty($doctorIds)) {
                                $spQuery->whereIn('doctor_id', $doctorIds);
                            } else {
                                $spQuery->whereRaw('1 = 0');
                            }
                        }

                        $rxCount += $spQuery->count();
                    }
                    $stats[$key]['prescriptions'] = $rxCount;
                }
                if ($user->canAccessFinance()) {
                    $invoiceRevenue = Invoice::where('clinic_id', $user->clinic_id)
                        ->whereDate('invoice_date', $day->toDateString())
                        ->sum('total_amount');
                    $receiptRevenue = Receipt::where('clinic_id', $user->clinic_id)
                        ->where('status', 'approved')
                        ->whereDate('receipt_date', $day->toDateString())
                        ->sum('amount');
                    $stats[$key]['revenue'] = $invoiceRevenue + $receiptRevenue;
                }
            }
        } elseif ($period === 'year') {
            // Last 5 years including current year
            for ($i = 4; $i >= 0; $i--) {
                $year = now()->subYears($i)->year;
                $key = (string) $year;
                $stats[$key] = ['patients' => 0, 'prescriptions' => 0, 'revenue' => 0];

                if ($user->canManagePatients()) {
                    $patientsQuery = Patient::where('clinic_id', $user->clinic_id)
                        ->whereYear('created_at', $year);

                    // Filter for assistants

                    $stats[$key]['patients'] = $patientsQuery->count();
                }
                if ($user->canPrescribe() || $user->canManagePatients()) {
                    $rxCount = Prescription::whereHas('patient', function ($q) use ($user) {
                            $q->where('clinic_id', $user->clinic_id);

                            // Filter for assistants
                            if ($user->role === 'assistant') {
                                $doctorIds = $user->allowedDoctorIds();
                                if (!empty($doctorIds)) {
                                    $q->whereIn('doctor_id', $doctorIds);
                                } else {
                                    $q->whereRaw('1 = 0');
                                }
                            }
                        })
                        ->whereYear('prescribed_date', $year)
                        ->count();
                    if (class_exists(\App\Models\SimplePrescription::class) && \Illuminate\Support\Facades\Schema::hasTable('simple_prescriptions')) {
                        $spQuery = \App\Models\SimplePrescription::where('clinic_id', $user->clinic_id)
                            ->whereYear('prescribed_date', $year);

                        // Filter for assistants
                        if ($user->role === 'assistant') {
                            $doctorIds = $user->allowedDoctorIds();
                            if (!empty($doctorIds)) {
                                $spQuery->whereIn('doctor_id', $doctorIds);
                            } else {
                                $spQuery->whereRaw('1 = 0');
                            }
                        }

                        $rxCount += $spQuery->count();
                    }
                    $stats[$key]['prescriptions'] = $rxCount;
                }
                if ($user->canAccessFinance()) {
                    $invoiceRevenue = Invoice::where('clinic_id', $user->clinic_id)
                        ->whereYear('invoice_date', $year)
                        ->sum('total_amount');
                    $receiptRevenue = Receipt::where('clinic_id', $user->clinic_id)
                        ->where('status', 'approved')
                        ->whereYear('receipt_date', $year)
                        ->sum('amount');
                    $stats[$key]['revenue'] = $invoiceRevenue + $receiptRevenue;
                }
            }
        } else {
            // Default: last 6 months
            for ($i = 5; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $key = $month->format('M Y');
                $stats[$key] = ['patients' => 0, 'prescriptions' => 0, 'revenue' => 0];

                if ($user->canManagePatients()) {
                    $patientsQuery = Patient::where('clinic_id', $user->clinic_id)
                        ->whereMonth('created_at', $month->month)
                        ->whereYear('created_at', $month->year);

                    // Filter for assistants

                    $stats[$key]['patients'] = $patientsQuery->count();
                }
                if ($user->canPrescribe() || $user->canManagePatients()) {
                    $rxCount = Prescription::whereHas('patient', function ($q) use ($user) {
                            $q->where('clinic_id', $user->clinic_id);

                            // Filter for assistants
                            if ($user->role === 'assistant') {
                                $doctorIds = $user->allowedDoctorIds();
                                if (!empty($doctorIds)) {
                                    $q->whereIn('doctor_id', $doctorIds);
                                } else {
                                    $q->whereRaw('1 = 0');
                                }
                            }
                        })
                        ->whereMonth('prescribed_date', $month->month)
                        ->whereYear('prescribed_date', $month->year)
                        ->count();
                    if (class_exists(\App\Models\SimplePrescription::class) && \Illuminate\Support\Facades\Schema::hasTable('simple_prescriptions')) {
                        $spQuery = \App\Models\SimplePrescription::where('clinic_id', $user->clinic_id)
                            ->whereMonth('prescribed_date', $month->month)
                            ->whereYear('prescribed_date', $month->year);

                        // Filter for assistants
                        if ($user->role === 'assistant') {
                            $doctorIds = $user->allowedDoctorIds();
                            if (!empty($doctorIds)) {
                                $spQuery->whereIn('doctor_id', $doctorIds);
                            } else {
                                $spQuery->whereRaw('1 = 0');
                            }
                        }

                        $rxCount += $spQuery->count();
                    }
                    $stats[$key]['prescriptions'] = $rxCount;
                }
                if ($user->canAccessFinance()) {
                    $invoiceRevenue = Invoice::where('clinic_id', $user->clinic_id)
                        ->whereMonth('invoice_date', $month->month)
                        ->whereYear('invoice_date', $month->year)
                        ->sum('total_amount');
                    $receiptRevenue = Receipt::where('clinic_id', $user->clinic_id)
                        ->where('status', 'approved')
                        ->whereMonth('receipt_date', $month->month)
                        ->whereYear('receipt_date', $month->year)
                        ->sum('amount');
                    $stats[$key]['revenue'] = $invoiceRevenue + $receiptRevenue;
                }
            }
        }

        return $stats;
    }

    /**
     * Get currency symbol for a given currency code
     */
    private function getCurrencySymbol($currencyCode): string
    {
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'IQD' => 'د.ع',
            'JOD' => 'د.أ',
            'EGP' => 'ج.م',
        ];

        return $symbols[$currencyCode] ?? '$';
    }
}
