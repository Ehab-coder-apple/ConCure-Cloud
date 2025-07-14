<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        if ($request->filled('date')) {
            $query->whereDate('appointments.appointment_datetime', $request->date);
        } else {
            // Default to today's appointments
            $query->whereDate('appointments.appointment_datetime', Carbon::today());
        }

        if ($request->filled('doctor_id')) {
            $query->where('appointments.doctor_id', $request->doctor_id);
        }

        $appointments = $query->orderBy('appointments.appointment_datetime')
            ->paginate(20);

        // Get doctors for filter
        $doctors = DB::table('users')
            ->where('clinic_id', Auth::user()->clinic_id)
            ->where('role', 'doctor')
            ->where('is_active', true)
            ->select('id', 'first_name', 'last_name')
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
                    'appointments.appointment_datetime',
                    'appointments.duration_minutes',
                    'appointments.type',
                    'appointments.status',
                    'appointments.notes',
                    'patients.first_name as patient_first_name',
                    'patients.last_name as patient_last_name',
                    'doctors.first_name as doctor_first_name',
                    'doctors.last_name as doctor_last_name'
                )
                ->where('appointments.clinic_id', Auth::user()->clinic_id)
                ->whereDate('appointments.appointment_datetime', '>=', Carbon::now()->subDays(30))
                ->whereDate('appointments.appointment_datetime', '<=', Carbon::now()->addDays(90))
                ->get();

            foreach ($calendarQuery as $appointment) {
                $startDateTime = Carbon::parse($appointment->appointment_datetime);
                $endDateTime = $startDateTime->copy()->addMinutes($appointment->duration_minutes ?? 30);

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
                        'duration' => $appointment->duration_minutes . ' min'
                    ]
                ];
            }
        }

        return view('appointments.index', compact('appointments', 'doctors', 'viewType', 'calendarEvents'));
    }

    /**
     * Show the form for creating a new appointment.
     */
    public function create()
    {
        $patients = DB::table('patients')
            ->where('clinic_id', Auth::user()->clinic_id)
            ->where('is_active', true)
            ->select('id', 'first_name', 'last_name', 'patient_id')
            ->get();

        $doctors = DB::table('users')
            ->where('clinic_id', Auth::user()->clinic_id)
            ->where('role', 'doctor')
            ->where('is_active', true)
            ->select('id', 'first_name', 'last_name')
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

        // Check for conflicts
        $appointmentDateTime = Carbon::parse($request->appointment_date . ' ' . $request->appointment_time);
        $duration = $request->duration ?? 30;
        $endTime = $appointmentDateTime->copy()->addMinutes($duration);

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

        if ($conflict) {
            return back()->withInput()
                ->with('error', __('The selected time slot conflicts with an existing appointment.'));
        }

        // Combine date and time into datetime
        $appointmentDateTime = Carbon::parse($request->appointment_date . ' ' . $request->appointment_time);

        // Generate appointment number
        $appointmentNumber = 'APT-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        try {
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
            ->where('is_active', true)
            ->select('id', 'first_name', 'last_name', 'patient_id')
            ->get();

        $doctors = DB::table('users')
            ->where('clinic_id', Auth::user()->clinic_id)
            ->where('role', 'doctor')
            ->where('is_active', true)
            ->select('id', 'first_name', 'last_name')
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

        $updated = DB::table('appointments')
            ->where('id', $id)
            ->where('clinic_id', Auth::user()->clinic_id)
            ->update([
                'patient_id' => $request->patient_id,
                'doctor_id' => $request->doctor_id,
                'appointment_datetime' => $appointmentDateTime,
                'duration_minutes' => $request->duration ?? 30,
                'type' => $request->appointment_type ?? 'consultation',
                'status' => $request->status,
                'notes' => $request->notes,
                'updated_at' => now(),
            ]);

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
}
