<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    /**
     * Display a listing of appointments.
     */
    public function index(Request $request)
    {
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
            ->where('appointments.clinic_id', Auth::user()->clinic_id);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('appointments.status', $request->status);
        }

        $legacy = $this->isLegacyAppointments();

        if ($request->filled('date')) {
            if ($legacy) {
                $query->whereDate('appointments.appointment_date', $request->date);
            } else {
                $query->whereDate('appointments.appointment_datetime', $request->date);
            }
        } else {
            // Default to today's appointments
            if ($legacy) {
                $query->whereDate('appointments.appointment_date', Carbon::today());
            } else {
                $query->whereDate('appointments.appointment_datetime', Carbon::today());
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
            $calendarQuery = DB::table('appointments')
                ->leftJoin('patients', 'appointments.patient_id', '=', 'patients.id')
                ->leftJoin('users as doctors', 'appointments.doctor_id', '=', 'doctors.id')
                ->select(
                    'appointments.id',
                    // For legacy DBs, appointment_datetime may not exist
                    // We will compute start/end in PHP below
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
                ->where('appointments.clinic_id', Auth::user()->clinic_id);

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
                    $startDateTime = Carbon::parse(($appointment->appointment_date ?? Carbon::today()->toDateString()) . ' ' . ($appointment->appointment_time ?? '00:00:00'));
                    $durationMinutes = $appointment->duration ?? 30;
                } else {
                    $startDateTime = Carbon::parse($appointment->appointment_datetime);
                    $durationMinutes = $appointment->duration_minutes ?? 30;
                }
                $endDateTime = $startDateTime->copy()->addMinutes($durationMinutes);

                $calendarEvents[] = [
                    'id' => $appointment->id,
                    'title' => ($appointment->patient_first_name ?? 'Unknown') . ' ' . ($appointment->patient_last_name ?? 'Patient'),
                    'start' => $startDateTime->toISOString(),
                    'end' => $endDateTime->toISOString(),
                    'backgroundColor' => $this->getStatusColor($appointment->status),
                    'borderColor' => $this->getStatusColor($appointment->status),
                    'extendedProps' => [
                        'patient' => $appointment->patient_first_name . ' ' . $appointment->patient_last_name,
                        'doctor' => 'Dr. ' . $appointment->doctor_first_name . ' ' . $appointment->doctor_last_name,
                        'type' => $appointment->type,
                        'status' => $appointment->status,
                        'notes' => $appointment->notes,
                        'duration' => ($durationMinutes) . ' min'
                    ]
                ];
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
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:users,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required',
            'appointment_type' => 'nullable|string|max:100',
            'duration' => 'nullable|integer|min:15|max:240',
            'notes' => 'nullable|string|max:1000',
        ]);

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

        try {
            if ($legacy) {
                $appointmentId = DB::table('appointments')->insertGetId([
                    'appointment_number' => $appointmentNumber,
                    'patient_id' => $request->patient_id,
                    'doctor_id' => $request->doctor_id,
                    'clinic_id' => Auth::user()->clinic_id,
                    'appointment_date' => $appointmentDateTime->toDateString(),
                    'appointment_time' => $appointmentDateTime->format('H:i:s'),
                    'duration' => $duration,
                    'type' => $request->appointment_type ?? 'consultation',
                    'status' => 'scheduled',
                    'notes' => $request->notes,
                    'created_by' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $appointmentId = DB::table('appointments')->insertGetId([
                    'appointment_number' => $appointmentNumber,
                    'patient_id' => $request->patient_id,
                    'doctor_id' => $request->doctor_id,
                    'clinic_id' => Auth::user()->clinic_id,
                    'appointment_datetime' => $appointmentDateTime,
                    'duration_minutes' => $duration,
                    'type' => $request->appointment_type ?? 'consultation',
                    'status' => 'scheduled',
                    'notes' => $request->notes,
                    'created_by' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return redirect()->route('appointments.show', $appointmentId)
                ->with('success', __('Appointment scheduled successfully.'));

        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', __('Error scheduling appointment: ') . $e->getMessage());
        }
    }

    /**
     * Display the specified appointment.
     */
    public function show($id)
    {
        $appointment = DB::table('appointments')
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
            ->where('appointments.clinic_id', Auth::user()->clinic_id)
            ->first();

        if (!$appointment) {
            abort(404, 'Appointment not found');
        }

        return view('appointments.show', compact('appointment'));
    }

    /**
     * Show the form for editing the specified appointment.
     */
    public function edit($id)
    {
        $appointment = DB::table('appointments')
            ->where('id', $id)
            ->where('clinic_id', Auth::user()->clinic_id)
            ->first();

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

        return view('appointments.edit', compact('appointment', 'patients', 'doctors'));
    }

    /**
     * Update the specified appointment.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:users,id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'appointment_type' => 'nullable|string|max:100',
            'duration' => 'nullable|integer|min:15|max:240',
            'status' => 'required|in:scheduled,confirmed,completed,cancelled',
            'notes' => 'nullable|string|max:1000',
        ]);

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

        $updated = DB::table('appointments')
            ->where('id', $id)
            ->where('clinic_id', Auth::user()->clinic_id)
            ->update($data);

        if ($updated) {
            return redirect()->route('appointments.show', $id)
                ->with('success', __('Appointment updated successfully.'));
        }

        return back()->with('error', __('Appointment not found or access denied.'));
    }

    /**
     * Remove the specified appointment.
     */
    public function destroy($id)
    {
        $deleted = DB::table('appointments')
            ->where('id', $id)
            ->where('clinic_id', Auth::user()->clinic_id)
            ->delete();

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

        $updated = DB::table('appointments')
            ->where('id', $id)
            ->where('clinic_id', Auth::user()->clinic_id)
            ->update([
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

}
