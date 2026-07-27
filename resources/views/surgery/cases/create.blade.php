@extends('layouts.app')

@php
    $isEdit = isset($surgicalCase);
@endphp

@section('content')
<div class="container">
    <h1 class="mb-3">{{ $isEdit ? 'Edit Surgical Case #' . $surgicalCase->id : 'Create Surgical Case (Scaffold)' }}</h1>
    @unless($isEdit)
        <p>This is a minimal form just to prove the Surgical module is wired correctly. Customize fields as needed.</p>
    @endunless

    <form method="POST" action="{{ $isEdit ? route('surgery.update', $surgicalCase) : route('surgery.store') }}">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div class="mb-3">
            <label for="patient_id" class="form-label">Patient</label>
            <select name="patient_id" id="patient_id" class="form-select" required>
                <option value="">-- Select Patient --</option>
                @foreach ($patients as $patient)
                    <option value="{{ $patient->id }}" {{ (old('patient_id', $isEdit ? $surgicalCase->patient_id : $preselectedPatientId) == $patient->id) ? 'selected' : '' }}>
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
                    <option value="{{ $doctor->id }}" {{ old('primary_surgeon_id', $isEdit ? $surgicalCase->primary_surgeon_id : null) == $doctor->id ? 'selected' : '' }}>
                        {{ $doctor->first_name }} {{ $doctor->last_name }} ({{ $doctor->role }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="diagnosis" class="form-label">Diagnosis</label>
            <textarea name="diagnosis" id="diagnosis" rows="2" class="form-control">{{ old('diagnosis', $isEdit ? $surgicalCase->diagnosis : null) }}</textarea>
        </div>

        <div class="mb-3">
            <label for="planned_procedure" class="form-label">Planned Procedure</label>
            <textarea name="planned_procedure" id="planned_procedure" rows="2" class="form-control">{{ old('planned_procedure', $isEdit ? $surgicalCase->planned_procedure : null) }}</textarea>
        </div>

        <div class="mb-3">
            <label for="scheduled_at" class="form-label">Scheduled At</label>
            <input type="datetime-local" name="scheduled_at" id="scheduled_at" class="form-control" value="{{ old('scheduled_at', $isEdit && $surgicalCase->scheduled_at ? $surgicalCase->scheduled_at->format('Y-m-d\TH:i') : null) }}">
        </div>

        @if($isEdit)
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    @foreach (['planned', 'in_progress', 'completed', 'cancelled'] as $statusOption)
                        <option value="{{ $statusOption }}" {{ old('status', $surgicalCase->status) === $statusOption ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $statusOption)) }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="mb-3">
            <label for="notes" class="form-label">Notes</label>
            <textarea name="notes" id="notes" rows="2" class="form-control">{{ old('notes', $isEdit ? $surgicalCase->notes : null) }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update Case' : 'Save Case' }}</button>
        @if($isEdit)
            <a href="{{ route('surgery.show', $surgicalCase) }}" class="btn btn-link">Cancel</a>
        @endif
    </form>
</div>
@endsection
