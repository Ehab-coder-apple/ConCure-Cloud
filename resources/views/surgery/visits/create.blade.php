@extends('layouts.app')

@php
    $isEdit = isset($visit);
@endphp

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">
                    <i class="fas fa-stethoscope text-primary me-2"></i>
                    {{ $isEdit ? 'Edit Follow-up Visit #' . $visitNumber : 'Add Follow-up Visit #' . $visitNumber }} for {{ $surgicalCase->patient->first_name }} {{ $surgicalCase->patient->last_name }}
                </h1>
                <p class="text-muted mb-0">
                    <strong>Case ID:</strong> {{ $surgicalCase->id }} |
                    <strong>Patient ID:</strong> {{ $surgicalCase->patient->patient_id }}
                </p>
            </div>
            <div>
                <a href="{{ route('surgery.show', $surgicalCase) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>
                    Back to Case
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-clipboard-check me-2"></i>
                        Follow-up Visit Information
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ $isEdit ? route('surgery.visit.update', [$surgicalCase->id, $visit->id]) : route('surgery.visit.store', $surgicalCase->id) }}" class="needs-validation">
                        @csrf
                        @if($isEdit)
                            @method('PUT')
                        @endif

                        <!-- Visit Date -->
                        <div class="mb-3">
                            <label for="visit_date" class="form-label">
                                {{ __('Visit Date') }} <span class="text-danger">*</span>
                            </label>
                            <input type="date" 
                                   id="visit_date" 
                                   name="visit_date" 
                                   class="form-control @error('visit_date') is-invalid @enderror" 
                                   value="{{ old('visit_date', now()->format('Y-m-d')) }}"
                                   required>
                            @error('visit_date')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Wound Status -->
                        <div class="mb-3">
                            <label for="wound_status" class="form-label">
                                {{ __('Wound Status') }}
                            </label>
                            <select id="wound_status" 
                                    name="wound_status" 
                                    class="form-select @error('wound_status') is-invalid @enderror">
                                <option value="">-- Select Status --</option>
                                <option value="healing_well" {{ old('wound_status') === 'healing_well' ? 'selected' : '' }}>
                                    Healing Well
                                </option>
                                <option value="delayed" {{ old('wound_status') === 'delayed' ? 'selected' : '' }}>
                                    Delayed Healing
                                </option>
                                <option value="infected" {{ old('wound_status') === 'infected' ? 'selected' : '' }}>
                                    Infected
                                </option>
                                <option value="other" {{ old('wound_status') === 'other' ? 'selected' : '' }}>
                                    Other
                                </option>
                            </select>
                            @error('wound_status')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Clinical Observations -->
                        <div class="mb-3">
                            <label for="clinical_observations" class="form-label">
                                {{ __('Clinical Observations') }}
                            </label>
                            <textarea id="clinical_observations" 
                                      name="clinical_observations" 
                                      rows="4" 
                                      class="form-control @error('clinical_observations') is-invalid @enderror"
                                      placeholder="Record any clinical findings, symptoms, and observations during the visit...">{{ old('clinical_observations') }}</textarea>
                            @error('clinical_observations')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Medications Prescribed -->
                        <div class="mb-3">
                            <label for="medications_prescribed" class="form-label">
                                {{ __('Medications Prescribed') }}
                            </label>
                            <textarea id="medications_prescribed" 
                                      name="medications_prescribed" 
                                      rows="3" 
                                      class="form-control @error('medications_prescribed') is-invalid @enderror"
                                      placeholder="List medications, dosages, and frequencies...">{{ old('medications_prescribed') }}</textarea>
                            @error('medications_prescribed')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        @include('surgery.partials.wound-assessment-fields')

                        <!-- Form Actions -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                {{ $isEdit ? __('Update Visit') : __('Record Visit') }}
                            </button>
                            <a href="{{ route('surgery.show', $surgicalCase) }}" class="btn btn-secondary">
                                {{ __('Cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@include('surgery.partials.wifi-calculator-script')
@endsection
