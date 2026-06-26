@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-3">Create Surgical Case (Scaffold)</h1>
    <p>This is a minimal form just to prove the Surgical module is wired correctly. Customize fields as needed.</p>

    <form method="POST" action="{{ route('surgery.store') }}">
        @csrf

        <div class="mb-3">
            <label for="patient_id" class="form-label">Patient</label>
            <select name="patient_id" id="patient_id" class="form-select" required>
                <option value="">-- Select Patient --</option>
                @foreach ($patients as $patient)
                    <option value="{{ $patient->id }}" {{ (old('patient_id', $preselectedPatientId) == $patient->id) ? 'selected' : '' }}>
                        {{ $patient->first_name }} {{ $patient->last_name }} (ID: {{ $patient->patient_id }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="primary_surgeon_id" class="form-label">Primary Surgeon</label>
            <select name="primary_surgeon_id" id="primary_surgeon_id" class="form-select" required>
                <option value="">-- Select Surgeon --</option>
                @foreach ($doctors as $doctor)
                    <option value="{{ $doctor->id }}" {{ old('primary_surgeon_id') == $doctor->id ? 'selected' : '' }}>
                        {{ $doctor->first_name }} {{ $doctor->last_name }} ({{ $doctor->role }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="diagnosis" class="form-label">Diagnosis</label>
            <textarea name="diagnosis" id="diagnosis" rows="2" class="form-control">{{ old('diagnosis') }}</textarea>
        </div>

        <div class="mb-3">
            <label for="planned_procedure" class="form-label">Planned Procedure</label>
            <textarea name="planned_procedure" id="planned_procedure" rows="2" class="form-control">{{ old('planned_procedure') }}</textarea>
        </div>

        <div class="mb-3">
            <label for="scheduled_at" class="form-label">Scheduled At</label>
            <input type="datetime-local" name="scheduled_at" id="scheduled_at" class="form-control" value="{{ old('scheduled_at') }}">
        </div>

        <button type="submit" class="btn btn-primary">Save Case</button>
    </form>
</div>
@endsection
