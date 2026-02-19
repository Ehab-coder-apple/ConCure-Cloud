<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\User;
use App\Models\Receipt;
use App\Notifications\NewAppointmentNotification;

class AppointmentController extends Controller
{
    /**
     * Display a listing of appointments.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = DB::table('appointments')
            ->leftJoin('patients', 'appointments.patient_id', '=', 'patients.id')
            ->leftJoin('users as doctors', 'appointments.doctor_id', '=', 'doctors.id')
            ->select(
                'appointments.*',
                'patients.first_name as patient_first_name',
                'patients.last_name as patient_last_name',
                'patients.patient_id',
                'patients.phone as patient_phone',
                'doctors.first_name as doctor_first_name',
                'doctors.last_name as doctor_last_name'
            )
            ->where('appointments.clinic_id', $user->clinic_id);

        // Filter by doctor for non-admin users
        // Only Super Admins and Clinic Admins can see all appointments
        // Regular doctors and other users can only see their own appointments
        if (!$user->isSuperAdmin() && !$user->isClinicAdmin()) {
            $query->where('appointments.doctor_id', $user->id);
        }

        // Apply filters
        if ($request->filled('status')) {
            $query->where('appointments.status', $request->status);
        }

        $legacy = $this->isLegacyAppointments();

        // Date filter - only apply if explicitly provided
        // Don't default to today to show all appointments
        if ($request->filled('date')) {
            if ($legacy) {
                $query->whereDate('appointments.appointment_date', $request->date);
            } else {
                $query->whereDate('appointments.appointment_datetime', $request->date);
            }
        }

        if ($request->filled('doctor_id')) {
            $query->where('appointments.doctor_id', $request->doctor_id);
        }

        if ($legacy) {
            $query->orderBy('appointments.appointment_date')
                  ->orderBy('appointments.appointment_time');
        } else {
            $query->orderBy('appointments.appointment_datetime');
        }
        $appointments = $query->paginate(20);

        // Get doctors and patients for modal/filter
        $doctors = DB::table('users')
            ->where('clinic_id', Auth::user()->clinic_id)
            ->whereIn('role', ['doctor', 'admin'])
            ->where(function($q){ $q->where('is_active', true)->orWhereNull('is_active'); })
            ->select('id', 'first_name', 'last_name', 'role')
            ->orderBy('first_name')
            ->get();

        $patients = DB::table('patients')
            ->where('clinic_id', Auth::user()->clinic_id)
            ->where(function($q){ $q->where('is_active', true)->orWhereNull('is_active'); })
            ->select('id', 'first_name', 'last_name', 'patient_id')
            ->orderBy('first_name')
            ->get();

        // Check if calendar view is requested
        $viewType = $request->get('view', 'list');

        // Get calendar data if calendar view is requested
        $calendarEvents = [];
        if ($viewType === 'calendar') {
            try {
                $calendarQuery = DB::table('appointments')
                    ->leftJoin('patients', 'appointments.patient_id', '=', 'patients.id')
                    ->leftJoin('users as doctors', 'appointments.doctor_id', '=', 'doctors.id')
                    ->select(
                        'appointments.id',
                        'appointments.appointment_datetime',
                        'appointments.duration_minutes',
                        'appointments.appointment_date',
                        'appointments.appointment_time',
                        'appointments.duration',
                        'appointments.type',
                        'appointments.status',
                        'appointments.notes',
                        'patients.first_name as patient_first_name',
                        'patients.last_name as patient_last_name',
                        'doctors.first_name as doctor_first_name',
                        'doctors.last_name as doctor_last_name'
                    )
                    ->where('appointments.clinic_id', $user->clinic_id);

                // Apply same role-based filtering for calendar view
                if (!$user->isSuperAdmin() && !$user->isClinicAdmin()) {
                    $calendarQuery->where('appointments.doctor_id', $user->id);
                }

                if ($legacy) {
                    $calendarQuery = $calendarQuery
                        ->whereDate('appointments.appointment_date', '>=', Carbon::now()->subDays(30))
                        ->whereDate('appointments.appointment_date', '<=', Carbon::now()->addDays(90))
                        ->get();
                } else {
                    $calendarQuery = $calendarQuery
                        ->whereDate('appointments.appointment_datetime', '>=', Carbon::now()->subDays(30))
                        ->whereDate('appointments.appointment_datetime', '<=', Carbon::now()->addDays(90))
                        ->get();
                }

                foreach ($calendarQuery as $appointment) {
                    if ($legacy) {
                        $dateStr = $appointment->appointment_date ?: Carbon::today()->toDateString();
                        $timeStr = $appointment->appointment_time ?: '00:00:00';
                        $startDateTime = Carbon::parse(trim($dateStr . ' ' . $timeStr));
                        $durationMinutes = is_numeric($appointment->duration) ? intval($appointment->duration) : 30;
                    } else {
                        $startDateTime = Carbon::parse($appointment->appointment_datetime);
                        $durationMinutes = is_numeric($appointment->duration_minutes) ? intval($appointment->duration_minutes) : 30;
                    }
                    $endDateTime = $startDateTime->copy()->addMinutes($durationMinutes);

                    $calendarEvents[] = [
                        'id' => $appointment->id,
                        'title' => trim(($appointment->patient_first_name ?? 'Unknown') . ' ' . ($appointment->patient_last_name ?? 'Patient')),
                        'start' => $startDateTime->toIso8601String(),
                        'end' => $endDateTime->toIso8601String(),
                        'backgroundColor' => $this->getStatusColor($appointment->status),
                        'borderColor' => $this->getStatusColor($appointment->status),
                        'extendedProps' => [
                            'patient' => trim(($appointment->patient_first_name ?? '') . ' ' . ($appointment->patient_last_name ?? '')),
                            'doctor' => 'Dr. ' . trim(($appointment->doctor_first_name ?? '') . ' ' . ($appointment->doctor_last_name ?? '')),
                            'type' => $appointment->type,
                            'status' => $appointment->status,
                            'notes' => $appointment->notes,
                            'duration' => ($durationMinutes) . ' min'
                        ]
                    ];
                }
            } catch (\Throwable $e) {
                Log::error('Appointments calendar build failed', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
                $calendarEvents = [];
            }
        }

        return view('appointments.index', compact('appointments', 'doctors', 'viewType', 'calendarEvents', 'patients'));
    }

    /**
     * Show the form for creating a new appointment.
     */
    public function create()
    {
        $patients = DB::table('patients')
            ->where('clinic_id', Auth::user()->clinic_id)
            ->where(function($q){ $q->where('is_active', true)->orWhereNull('is_active'); })
            ->select('id', 'first_name', 'last_name', 'patient_id')
            ->get();

        $doctors = DB::table('users')
            ->where('clinic_id', Auth::user()->clinic_id)
            ->whereIn('role', ['doctor', 'admin'])
            ->where(function($q){ $q->where('is_active', true)->orWhereNull('is_active'); })
            ->select('id', 'first_name', 'last_name', 'role')
            ->get();

        return view('appointments.create', compact('patients', 'doctors'));
    }

    /**
     * Store a newly created appointment.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:users,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required',
            'appointment_type' => 'nullable|string|max:100',
            'duration' => 'nullable|integer|min:15|max:240',
            'notes' => 'nullable|string|max:1000',

            // Payment collection (optional)
            'fees_collected' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|in:cash,card,bank_transfer,check,other',
            'payment_notes' => 'nullable|string|max:1000',
        ]);

        $validator->sometimes('payment_method', 'required', function ($input) {
            return isset($input->fees_collected) && (float) $input->fees_collected > 0;
        });

        $validated = $validator->validate();

        // Check for conflicts (support legacy schema)
        $appointmentDateTime = Carbon::parse($request->appointment_date . ' ' . $request->appointment_time);
        $duration = $request->duration ?? 30;
        $endTime = $appointmentDateTime->copy()->addMinutes($duration);

        $legacy = $this->isLegacyAppointments();
        if ($legacy) {
            $conflict = DB::table('appointments')
                ->where('doctor_id', $request->doctor_id)
                ->where('appointment_date', $request->appointment_date)
                ->where('status', '!=', 'cancelled')
                ->where(function ($query) use ($appointmentDateTime, $endTime) {
                    $query->whereBetween('appointment_time', [
                        $appointmentDateTime->format('H:i:s'),
                        $endTime->format('H:i:s')
                    ]);
                })
                ->exists();
        } else {
            // Overlap if start < existing_end AND end > existing_start
            $conflict = DB::table('appointments')
                ->where('doctor_id', $request->doctor_id)
                ->whereDate('appointment_datetime', $appointmentDateTime->toDateString())
                ->where('status', '!=', 'cancelled')
                ->where('appointment_datetime', '<', $endTime->toDateTimeString())
                ->where(DB::raw("DATE_ADD(appointment_datetime, INTERVAL duration_minutes MINUTE)"), '>', $appointmentDateTime->toDateTimeString())
                ->exists();
        }

        if ($conflict) {
            return back()->withInput()
                ->with('error', __('The selected time slot conflicts with an existing appointment.'));
        }

        // Combine date and time into datetime
        $appointmentDateTime = Carbon::parse($request->appointment_date . ' ' . $request->appointment_time);

        // Generate appointment number
        $appointmentNumber = 'APT-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $user = Auth::user();
        $feesCollected = (float) ($validated['fees_collected'] ?? 0);

        try {
            DB::beginTransaction();

            if ($legacy) {
                $appointmentId = DB::table('appointments')->insertGetId([
                    'appointment_number' => $appointmentNumber,
                    'patient_id' => $request->patient_id,
                    'doctor_id' => $request->doctor_id,
                    'clinic_id' => $user->clinic_id,
                    'appointment_date' => $appointmentDateTime->toDateString(),
                    'appointment_time' => $appointmentDateTime->format('H:i:s'),
                    'duration' => $duration,
                    'type' => $request->appointment_type ?? 'consultation',
                    'status' => 'scheduled',
                    'notes' => $request->notes,
                    'created_by' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $appointmentId = DB::table('appointments')->insertGetId([
                    'appointment_number' => $appointmentNumber,
                    'patient_id' => $request->patient_id,
                    'doctor_id' => $request->doctor_id,
                    'clinic_id' => $user->clinic_id,
                    'appointment_datetime' => $appointmentDateTime,
                    'duration_minutes' => $duration,
                    'type' => $request->appointment_type ?? 'consultation',
                    'status' => 'scheduled',
                    'notes' => $request->notes,
                    'created_by' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Create finance receipt if payment collected now
            if ($feesCollected > 0) {
                $patient = DB::table('patients')
                    ->where('id', $request->patient_id)
                    ->select('first_name', 'last_name')
                    ->first();

                $payerName = $patient ? trim(($patient->first_name ?? '') . ' ' . ($patient->last_name ?? '')) : null;

                Receipt::create([
                    'clinic_id' => $user->clinic_id,
					'description' => 'Consultation Fee - ' . $appointmentNumber,
                    'amount' => $feesCollected,
                    'category' => 'consultation_fee',
                    'receipt_date' => now()->toDateString(),
                    'payment_method' => $validated['payment_method'],
                    'payer_name' => $payerName,
                    // Reference the appointment ID (string field)
                    'reference_number' => (string) $appointmentId,
                    'notes' => $validated['payment_notes'] ?? null,
                    'created_by' => $user->id,
                    'status' => 'approved',
                    'approved_by' => $user->id,
                    'approved_at' => now(),
                ]);
            }

            DB::commit();

            // Notify the assigned doctor about the new appointment (includes appointment number)
            try {
                $doctor = User::where('id', $request->doctor_id)
                    ->where('clinic_id', $user->clinic_id)
                    ->first();

                if ($doctor) {
                    $patient = DB::table('patients')
                        ->where('id', $request->patient_id)
                        ->select('first_name', 'last_name', 'patient_id')
                        ->first();

                    $patientName = $patient ? trim(($patient->first_name ?? '') . ' ' . ($patient->last_name ?? '')) : null;
                    $patientCode = $patient->patient_id ?? null;
                    $scheduledAt = $appointmentDateTime->format('Y-m-d H:i');

                    $doctor->notify(new NewAppointmentNotification(
                        (int) $appointmentId,
                        (string) $appointmentNumber,
                        $patientName ?: null,
                        $patientCode ?: null,
                        $scheduledAt,
                        $user->clinic_id
                    ));
                }
            } catch (\Throwable $e) {
                \Log::warning('Failed to notify doctor of new appointment', [
                    'error' => $e->getMessage(),
                ]);
            }

            return redirect()->route('appointments.index')
                ->with('success', __('Appointment scheduled successfully.'));

        } catch (\Throwable $e) {
            try { DB::rollBack(); } catch (\Throwable $ignored) {}
            return back()->withInput()
                ->with('error', __('Error scheduling appointment: ') . $e->getMessage());
        }
    }

    /**
     * Display the specified appointment.
     */
    public function show($id)
    {
        $user = Auth::user();

        $query = DB::table('appointments')
            ->leftJoin('patients', 'appointments.patient_id', '=', 'patients.id')
            ->leftJoin('users as doctors', 'appointments.doctor_id', '=', 'doctors.id')
            ->leftJoin('users as creators', 'appointments.created_by', '=', 'creators.id')
            ->select(
                'appointments.*',
                'patients.first_name as patient_first_name',
                'patients.last_name as patient_last_name',
                'patients.patient_id',
                'patients.phone as patient_phone',
                'patients.email as patient_email',
                'patients.date_of_birth',
                'patients.gender',
                'doctors.first_name as doctor_first_name',
                'doctors.last_name as doctor_last_name',
                'doctors.phone as doctor_phone',
                'creators.first_name as creator_first_name',
                'creators.last_name as creator_last_name'
            )
            ->where('appointments.id', $id)
            ->where('appointments.clinic_id', $user->clinic_id);

        // Apply role-based filtering
        if (!$user->isSuperAdmin() && !$user->isClinicAdmin()) {
            $query->where('appointments.doctor_id', $user->id);
        }

        $appointment = $query->first();

        if (!$appointment) {
            abort(404, 'Appointment not found');
        }

        $receipt = Receipt::where('clinic_id', $user->clinic_id)
            ->where('reference_number', (string) $appointment->id)
            ->first();

        $currency = DB::table('settings')
            ->where('clinic_id', $user->clinic_id)
            ->where('key', 'currency')
            ->value('value') ?? 'USD';

        $currencySymbol = $this->getCurrencySymbol($currency);

        return view('appointments.show', compact('appointment', 'receipt', 'currency', 'currencySymbol'));
    }

    /**
     * Generate a PDF receipt for an appointment payment (if collected).
     */
    public function generateReceiptPDF($id)
    {
        $user = Auth::user();

        $query = DB::table('appointments')
            ->leftJoin('patients', 'appointments.patient_id', '=', 'patients.id')
            ->leftJoin('users as doctors', 'appointments.doctor_id', '=', 'doctors.id')
            ->select(
                'appointments.*',
                'patients.first_name as patient_first_name',
                'patients.last_name as patient_last_name',
                'patients.patient_id',
                'patients.phone as patient_phone',
                'patients.email as patient_email',
                'doctors.first_name as doctor_first_name',
                'doctors.last_name as doctor_last_name'
            )
            ->where('appointments.id', $id)
            ->where('appointments.clinic_id', $user->clinic_id);

        // Apply role-based filtering
        if (!$user->isSuperAdmin() && !$user->isClinicAdmin()) {
            $query->where('appointments.doctor_id', $user->id);
        }

        $appointment = $query->first();
        if (!$appointment) {
            abort(404, 'Appointment not found');
        }

        $receipt = Receipt::where('clinic_id', $user->clinic_id)
            ->where('reference_number', (string) $appointment->id)
            ->first();

        if (!$receipt) {
            abort(404, 'Receipt not found for this appointment');
        }

        $clinic = DB::table('clinics')
            ->where('id', $user->clinic_id)
            ->first();

        $currency = DB::table('settings')
            ->where('clinic_id', $user->clinic_id)
            ->where('key', 'currency')
            ->value('value') ?? 'USD';

        $currencySymbol = $this->getCurrencySymbol($currency);

        $appointmentDateTime = null;
        if (!empty($appointment->appointment_datetime)) {
            $appointmentDateTime = Carbon::parse($appointment->appointment_datetime);
        } elseif (!empty($appointment->appointment_date)) {
            $appointmentDateTime = Carbon::parse($appointment->appointment_date . ' ' . ($appointment->appointment_time ?? '00:00:00'));
        }

        $serviceDescription = ucfirst(str_replace('_', ' ', $appointment->type ?? 'consultation'));

        $pdf = Pdf::loadView('appointments.receipt-pdf', compact(
            'appointment',
            'receipt',
            'clinic',
            'currency',
            'currencySymbol',
            'appointmentDateTime',
            'serviceDescription'
        ));

        return $pdf->download("receipt-{$receipt->receipt_number}.pdf");
    }

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

    /**
     * Show the form for editing the specified appointment.
     */
    public function edit($id)
    {
        $user = Auth::user();

        $query = DB::table('appointments')
            ->where('id', $id)
            ->where('clinic_id', $user->clinic_id);

        // Apply role-based filtering
        if (!$user->isSuperAdmin() && !$user->isClinicAdmin()) {
            $query->where('doctor_id', $user->id);
        }

        $appointment = $query->first();

        if (!$appointment) {
            abort(404, 'Appointment not found');
        }

        $patients = DB::table('patients')
            ->where('clinic_id', Auth::user()->clinic_id)
            ->where(function($q){ $q->where('is_active', true)->orWhereNull('is_active'); })
            ->select('id', 'first_name', 'last_name', 'patient_id')
            ->get();

        $doctors = DB::table('users')
            ->where('clinic_id', Auth::user()->clinic_id)
            ->whereIn('role', ['doctor', 'admin'])
            ->where(function($q){ $q->where('is_active', true)->orWhereNull('is_active'); })
            ->select('id', 'first_name', 'last_name', 'role')
            ->get();

        // Load receipt if exists
        $receipt = Receipt::where('clinic_id', $user->clinic_id)
            ->where('reference_number', (string) $appointment->id)
            ->first();

        $currency = DB::table('settings')
            ->where('clinic_id', $user->clinic_id)
            ->where('key', 'currency')
            ->value('value') ?? 'USD';

        $currencySymbol = $this->getCurrencySymbol($currency);

        return view('appointments.edit', compact('appointment', 'patients', 'doctors', 'receipt', 'currency', 'currencySymbol'));
    }

    /**
     * Update the specified appointment.
     */
    public function update(Request $request, $id)
    {
        // Use Validator for conditional validation
        $validator = Validator::make($request->all(), [
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:users,id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'appointment_type' => 'nullable|string|max:100',
            'duration' => 'nullable|integer|min:15|max:240',
            'status' => 'required|in:scheduled,confirmed,completed,cancelled',
            'notes' => 'nullable|string|max:1000',
            'fees_collected' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|in:cash,card,bank_transfer,check,other',
            'payment_notes' => 'nullable|string|max:500',
        ]);

        // Add conditional validation for payment_method
        $validator->sometimes('payment_method', 'required', function ($input) {
            return (float) ($input->fees_collected ?? 0) > 0;
        });

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Combine date and time into datetime
        $appointmentDateTime = Carbon::parse($request->appointment_date . ' ' . $request->appointment_time);

        $legacy = $this->isLegacyAppointments();
        $data = [
            'patient_id' => $request->patient_id,
            'doctor_id' => $request->doctor_id,
            'type' => $request->appointment_type ?? 'consultation',
            'status' => $request->status,
            'notes' => $request->notes,
            'updated_at' => now(),
        ];
        if ($legacy) {
            $data['appointment_date'] = $appointmentDateTime->toDateString();
            $data['appointment_time'] = $appointmentDateTime->format('H:i:s');
            $data['duration'] = $request->duration ?? 30;
        } else {
            $data['appointment_datetime'] = $appointmentDateTime;
            $data['duration_minutes'] = $request->duration ?? 30;
        }

        $user = Auth::user();

        $query = DB::table('appointments')
            ->where('id', $id)
            ->where('clinic_id', $user->clinic_id);

        // Apply role-based filtering
        if (!$user->isSuperAdmin() && !$user->isClinicAdmin()) {
            $query->where('doctor_id', $user->id);
        }

        // Use transaction for atomic operations
        DB::beginTransaction();
        try {
            $updated = $query->update($data);

            if ($updated) {
                // Handle receipt creation/update
                $feesCollected = (float) ($request->fees_collected ?? 0);

                if ($feesCollected > 0) {
                    // Check if receipt already exists
                    $receipt = Receipt::where('clinic_id', $user->clinic_id)
                        ->where('reference_number', (string) $id)
                        ->first();

                    if ($receipt) {
                        // Update existing receipt
                        $receipt->update([
                            'amount' => $feesCollected,
                            'payment_method' => $request->payment_method,
                            'notes' => $request->payment_notes,
                        ]);
                    } else {
                        // Create new receipt
                        Receipt::create([
                            'clinic_id' => $user->clinic_id,
                            'description' => 'Appointment Payment',
                            'amount' => $feesCollected,
                            'category' => 'consultation_fee',
                            'receipt_date' => now()->toDateString(),
                            'payment_method' => $request->payment_method,
                            'reference_number' => (string) $id,
                            'notes' => $request->payment_notes,
                            'created_by' => $user->id,
                            'status' => 'approved',
                            'approved_by' => $user->id,
                            'approved_at' => now(),
                        ]);
                    }
                } else {
                    // If fees are 0, delete any existing receipt
                    Receipt::where('clinic_id', $user->clinic_id)
                        ->where('reference_number', (string) $id)
                        ->delete();
                }

                DB::commit();
                return redirect()->route('appointments.index')
                    ->with('success', __('Appointment updated successfully.'));
            }

            DB::rollBack();
            return back()->with('error', __('Appointment not found or access denied.'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', __('Error updating appointment: ') . $e->getMessage());
        }
    }

    /**
     * Remove the specified appointment.
     */
    public function destroy($id)
    {
        $user = Auth::user();

        $query = DB::table('appointments')
            ->where('id', $id)
            ->where('clinic_id', $user->clinic_id);

        // Apply role-based filtering
        if (!$user->isSuperAdmin() && !$user->isClinicAdmin()) {
            $query->where('doctor_id', $user->id);
        }

        $deleted = $query->delete();

        if ($deleted) {
            return redirect()->route('appointments.index')
                ->with('success', __('Appointment deleted successfully.'));
        }

        return back()->with('error', __('Appointment not found or access denied.'));
    }

    /**
     * Update appointment status.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:scheduled,confirmed,completed,cancelled'
        ]);

        $user = Auth::user();

        $query = DB::table('appointments')
            ->where('id', $id)
            ->where('clinic_id', $user->clinic_id);

        // Apply role-based filtering
        if (!$user->isSuperAdmin() && !$user->isClinicAdmin()) {
            $query->where('doctor_id', $user->id);
        }

        $updated = $query->update([
            'status' => $request->status,
            'updated_at' => now(),
        ]);

        if ($updated) {
            return response()->json([
                'success' => true,
                'message' => __('Appointment status updated successfully.')
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('Appointment not found or access denied.')
        ], 404);
    }

    /**
     * Get color for appointment status.
     */
    private function getStatusColor($status)
    {
        switch ($status) {
            case 'scheduled':
                return '#007bff'; // Blue
            case 'confirmed':
                return '#28a745'; // Green
            case 'completed':
                return '#6c757d'; // Gray
            case 'cancelled':
                return '#dc3545'; // Red
            case 'no_show':
                return '#fd7e14'; // Orange
            default:
                return '#6f42c1'; // Purple
        }
    }

    /**
     * Determine if the server uses the legacy appointments schema
     * (appointment_date + appointment_time + duration) instead of
     * appointment_datetime + duration_minutes.
     */
    private function isLegacyAppointments(): bool
    {
        try {
            if (Schema::hasColumn('appointments', 'appointment_datetime')) {
                return false;
            }
            if (Schema::hasColumn('appointments', 'appointment_date')) {
                return true;
            }
            // Probe column existence by selecting it; if it fails, assume legacy
            try {
                DB::table('appointments')->limit(1)->select('appointment_datetime')->first();
                return false;
            } catch (QueryException $qe) {
                return true;
            }
        } catch (\Throwable $e) {
            // If detection fails for any reason, default to new schema to be safer
            return false;
        }
    }

    /**
     * Pending appointments count for current doctor (today, upcoming from now).
     * Returns JSON: { count: number }
     */
    public function pendingCount(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['count' => 0]);
        }


        $now = Carbon::now();
        $legacy = $this->isLegacyAppointments();

        $query = DB::table('appointments')
            ->where('clinic_id', $user->clinic_id)
            ->where('doctor_id', $user->id)
            ->whereIn('status', ['scheduled', 'confirmed']);

        if ($legacy) {
            $query->whereRaw("STR_TO_DATE(CONCAT(appointment_date,' ', appointment_time), '%Y-%m-%d %H:%i:%s') >= ?", [$now->format('Y-m-d H:i:s')]);
        } else {
            $query->where('appointment_datetime', '>=', $now);
        }

        $count = (int) $query->count();
        return response()->json(['count' => $count]);
    }
    /**
     * Upcoming summary for bell dropdown: personal (doctor) and clinic totals.
     * Returns JSON with my_count, clinic_count, my (list of up to 10), clinic (list of up to 10).
     */
    public function upcomingSummary(Request $request)
    {
        $user = $request->user();
        if (!$user) return response()->json(['my_count' => 0, 'clinic_count' => 0, 'my' => [], 'clinic' => []]);

        $now = Carbon::now();
        $legacy = $this->isLegacyAppointments();
        $statuses = ['scheduled', 'confirmed'];

        $buildBase = function() use ($legacy, $now) {
            $q = DB::table('appointments')
                ->leftJoin('patients', 'appointments.patient_id', '=', 'patients.id')
                ->leftJoin('users as doctors', 'appointments.doctor_id', '=', 'doctors.id')
                ->select(
                    'appointments.id',
                    'appointments.appointment_number',
                    'appointments.status',
                    'appointments.type',
                    'appointments.appointment_datetime',
                    'appointments.appointment_date',
                    'appointments.appointment_time',
                    'patients.first_name as patient_first_name',
                    'patients.last_name as patient_last_name',
                    'doctors.first_name as doctor_first_name',
                    'doctors.last_name as doctor_last_name'
                );
            if ($legacy) {
                $q->whereRaw("STR_TO_DATE(CONCAT(appointment_date,' ', appointment_time), '%Y-%m-%d %H:%i:%s') >= ?", [$now->format('Y-m-d H:i:s')])
                  ->orderBy('appointment_date')->orderBy('appointment_time');
            } else {
                $q->where('appointment_datetime', '>=', $now)
                  ->orderBy('appointment_datetime');
            }
            return $q;
        };

        // Personal (doctor) upcoming
        $myQuery = $buildBase()
            ->where('appointments.clinic_id', $user->clinic_id)
            ->where('appointments.doctor_id', $user->id)
            ->whereIn('appointments.status', $statuses);
        $myCount = (int) $myQuery->count();
        $myList = $myQuery->limit(10)->get()->map(function($r) use ($legacy) {
            $patient = trim(($r->patient_first_name ?? '') . ' ' . ($r->patient_last_name ?? ''));
            $doctor = trim(($r->doctor_first_name ?? '') . ' ' . ($r->doctor_last_name ?? ''));
            $when = $legacy ? (trim(($r->appointment_date ?? '') . ' ' . ($r->appointment_time ?? ''))) : ($r->appointment_datetime);
            return [
                'id' => $r->id,
                'appointment_number' => $r->appointment_number,
                'patient' => $patient,
                'doctor' => $doctor,
                'status' => $r->status,
                'when' => is_string($when) ? $when : optional($when)->format('Y-m-d H:i'),
            ];
        })->toArray();

        // Clinic upcoming (admins)
        $clinicCount = 0; $clinicList = [];
        $isAdmin = in_array($user->role, ['admin', 'program_owner']);
        if ($isAdmin) {
            $clinicQuery = $buildBase()
                ->where('appointments.clinic_id', $user->clinic_id)
                ->whereIn('appointments.status', $statuses);
            $clinicCount = (int) $clinicQuery->count();
            $clinicList = $clinicQuery->limit(10)->get()->map(function($r) use ($legacy) {
                $patient = trim(($r->patient_first_name ?? '') . ' ' . ($r->patient_last_name ?? ''));
                $doctor = trim(($r->doctor_first_name ?? '') . ' ' . ($r->doctor_last_name ?? ''));
                $when = $legacy ? (trim(($r->appointment_date ?? '') . ' ' . ($r->appointment_time ?? ''))) : ($r->appointment_datetime);
                return [
                    'id' => $r->id,
                    'appointment_number' => $r->appointment_number,
                    'patient' => $patient,
                    'doctor' => $doctor,
                    'status' => $r->status,
                    'when' => is_string($when) ? $when : optional($when)->format('Y-m-d H:i'),
                ];
            })->toArray();
        }

        return response()->json([
            'my_count' => $myCount,
            'clinic_count' => $clinicCount,
            'my' => $myList,
            'clinic' => $clinicList,
        ]);
    }


}
