@extends('layouts.app')

@section('title', __('Edit Dental Chart') . ' - ' . $patient->full_name)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-edit text-primary me-2"></i>
                        {{ __('Edit Dental Chart') }}
                    </h1>
                    <p class="text-muted mb-0">
                        {{ __('Patient') }}: <strong>{{ $patient->full_name }}</strong> ({{ $patient->patient_id }})
                    </p>
                </div>
                <div>
                    <a href="{{ url("/dental/patients/{$patient->id}/charts/{$dentalChart->id}") }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to Chart') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ url("/dental/patients/{$patient->id}/charts/{$dentalChart->id}") }}" method="POST" id="dental-chart-form">
        @csrf
        @method('PUT')

        <!-- Chart Type Selection -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-cog me-2"></i>
                            {{ __('Chart Configuration') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Chart Type') }} <span class="text-danger">*</span></label>
                                <select name="chart_type" id="chart_type" class="form-select" required>
                                    <option value="adult" {{ old('chart_type', $dentalChart->chart_type) === 'adult' ? 'selected' : '' }}>
                                        {{ __('Adult (Permanent Dentition)') }}
                                    </option>
                                    <option value="pediatric" {{ old('chart_type', $dentalChart->chart_type) === 'pediatric' ? 'selected' : '' }}>
                                        {{ __('Pediatric (Primary Dentition)') }}
                                    </option>
                                </select>
                                @error('chart_type')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Created') }}</label>
                                <input type="text" class="form-control" value="{{ $dentalChart->created_at->format('M d, Y H:i') }}" disabled>
                            </div>

                            <div class="col-12">
                                <label class="form-label">{{ __('General Notes') }}</label>
                                <textarea name="general_notes" class="form-control" rows="4" placeholder="{{ __('Enter general notes about this dental chart...') }}">{{ old('general_notes', $dentalChart->general_notes) }}</textarea>
                                @error('general_notes')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Existing Tooth Records -->
        @if($dentalChart->toothRecords && $dentalChart->toothRecords->count() > 0)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-tooth me-2"></i>
                                {{ __('Recorded Teeth') }} ({{ $dentalChart->toothRecords->count() }})
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                {{ __('To edit individual tooth records, please use the tooth record management feature on the chart detail page.') }}
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Tooth #') }}</th>
                                            <th>{{ __('Surfaces') }}</th>
                                            <th>{{ __('Condition') }}</th>
                                            <th>{{ __('Notes') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($dentalChart->toothRecords->sortBy('tooth_number') as $record)
                                            <tr>
                                                <td><strong>{{ $record->tooth_number }}</strong></td>
                                                <td>
                                                    @if($record->surfaces)
                                                        <span class="badge bg-secondary">{{ implode(', ', $record->surfaces) }}</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($record->condition)
                                                        <span class="badge bg-{{ $record->condition === 'healthy' ? 'success' : ($record->condition === 'cavity' ? 'danger' : 'warning') }}">
                                                            {{ ucfirst($record->condition) }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ Str::limit($record->notes, 50) ?? '-' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Form Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <a href="{{ url("/dental/patients/{$patient->id}/charts/{$dentalChart->id}") }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>
                                {{ __('Cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                {{ __('Update Dental Chart') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

