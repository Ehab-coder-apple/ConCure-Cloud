@extends('layouts.app')

@section('title', __('Edit Orthodontic Case'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-teeth me-2 text-primary"></i>
                        {{ __('Edit Orthodontic Case') }} #{{ $orthodonticCase->case_number }}
                    </h1>
                    <p class="text-muted mb-0">{{ $orthodonticCase->patient->full_name }}</p>
                </div>
                <div>
                    <a href="{{ route('orthodontics.show', $orthodonticCase) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Form -->
    <form action="{{ route('orthodontics.update', $orthodonticCase) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="row">
            <!-- Doctor & Status Information -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-user-md me-2"></i>{{ __('Doctor & Status') }}</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="doctor_id" class="form-label">{{ __('Assigned Doctor') }} <span class="text-danger">*</span></label>
                            <select name="doctor_id" id="doctor_id" class="form-select @error('doctor_id') is-invalid @enderror" required>
                                @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" {{ old('doctor_id', $orthodonticCase->doctor_id) == $doctor->id ? 'selected' : '' }}>
                                        {{ $doctor->full_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('doctor_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">{{ __('Status') }} <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                @foreach(\App\Models\OrthodonticCase::STATUSES as $key => $value)
                                    <option value="{{ $key }}" {{ old('status', $orthodonticCase->status) == $key ? 'selected' : '' }}>
                                        {{ __($value) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="current_phase" class="form-label">{{ __('Current Phase') }}</label>
                            <select name="current_phase" id="current_phase" class="form-select @error('current_phase') is-invalid @enderror">
                                @foreach(\App\Models\OrthodonticCase::PHASES as $key => $value)
                                    <option value="{{ $key }}" {{ old('current_phase', $orthodonticCase->current_phase) == $key ? 'selected' : '' }}>
                                        {{ __($value) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('current_phase')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Treatment Details -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-tooth me-2"></i>{{ __('Treatment Details') }}</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="treatment_type" class="form-label">{{ __('Treatment Type') }} <span class="text-danger">*</span></label>
                            <select name="treatment_type" id="treatment_type" class="form-select @error('treatment_type') is-invalid @enderror" required>
                                @foreach(\App\Models\OrthodonticCase::TREATMENT_TYPES as $key => $value)
                                    <option value="{{ $key }}" {{ old('treatment_type', $orthodonticCase->treatment_type) == $key ? 'selected' : '' }}>
                                        {{ __($value) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('treatment_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="malocclusion_class" class="form-label">{{ __('Malocclusion Class') }}</label>
                            <select name="malocclusion_class" id="malocclusion_class" class="form-select @error('malocclusion_class') is-invalid @enderror">
                                <option value="">{{ __('Select Class') }}</option>
                                @foreach(\App\Models\OrthodonticCase::MALOCCLUSION_CLASSES as $key => $value)
                                    <option value="{{ $key }}" {{ old('malocclusion_class', $orthodonticCase->malocclusion_class) == $key ? 'selected' : '' }}>
                                        {{ __($value) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('malocclusion_class')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="estimated_duration_months" class="form-label">{{ __('Estimated Duration (Months)') }} <span class="text-danger">*</span></label>
                            <input type="number" name="estimated_duration_months" id="estimated_duration_months" 
                                   class="form-control @error('estimated_duration_months') is-invalid @enderror" 
                                   value="{{ old('estimated_duration_months', $orthodonticCase->estimated_duration_months) }}" min="1" max="60" required>
                            @error('estimated_duration_months')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Clinical Information -->
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>{{ __('Clinical Information') }}</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="diagnosis" class="form-label">{{ __('Diagnosis') }}</label>
                            <textarea name="diagnosis" id="diagnosis" rows="3" class="form-control @error('diagnosis') is-invalid @enderror">{{ old('diagnosis', $orthodonticCase->diagnosis) }}</textarea>
                            @error('diagnosis')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="treatment_objectives" class="form-label">{{ __('Treatment Objectives') }}</label>
                            <textarea name="treatment_objectives" id="treatment_objectives" rows="3" class="form-control @error('treatment_objectives') is-invalid @enderror">{{ old('treatment_objectives', $orthodonticCase->treatment_objectives) }}</textarea>
                            @error('treatment_objectives')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">{{ __('Additional Notes') }}</label>
                            <textarea name="notes" id="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $orthodonticCase->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Clinical Assessment -->
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-stethoscope me-2"></i>{{ __('Clinical Assessment') }}</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="skeletal_class" class="form-label">{{ __('Skeletal Class') }}</label>
                                <select name="skeletal_class" id="skeletal_class" class="form-select @error('skeletal_class') is-invalid @enderror">
                                    <option value="">{{ __('Select Class') }}</option>
                                    @foreach(\App\Models\OrthodonticCase::SKELETAL_CLASSES as $key => $value)
                                        <option value="{{ $key }}" {{ old('skeletal_class', $orthodonticCase->skeletal_class) == $key ? 'selected' : '' }}>
                                            {{ __($value) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('skeletal_class')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="overjet" class="form-label">{{ __('Overjet (mm)') }}</label>
                                <input type="number" name="overjet" id="overjet" step="0.01" min="0" max="99.99"
                                       class="form-control @error('overjet') is-invalid @enderror"
                                       value="{{ old('overjet', $orthodonticCase->overjet) }}" placeholder="e.g., 3.5">
                                @error('overjet')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="overbite" class="form-label">{{ __('Overbite (mm)') }}</label>
                                <input type="number" name="overbite" id="overbite" step="0.01" min="0" max="99.99"
                                       class="form-control @error('overbite') is-invalid @enderror"
                                       value="{{ old('overbite', $orthodonticCase->overbite) }}" placeholder="e.g., 2.5">
                                @error('overbite')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="midline" class="form-label">{{ __('Midline') }}</label>
                                <select name="midline" id="midline" class="form-select @error('midline') is-invalid @enderror">
                                    <option value="">{{ __('Select Alignment') }}</option>
                                    @foreach(\App\Models\OrthodonticCase::MIDLINE_OPTIONS as $key => $value)
                                        <option value="{{ $key }}" {{ old('midline', $orthodonticCase->midline) == $key ? 'selected' : '' }}>
                                            {{ __($value) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('midline')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="crowding" class="form-label">{{ __('Crowding') }}</label>
                                <select name="crowding" id="crowding" class="form-select @error('crowding') is-invalid @enderror">
                                    <option value="">{{ __('Select Level') }}</option>
                                    @foreach(\App\Models\OrthodonticCase::CROWDING_LEVELS as $key => $value)
                                        <option value="{{ $key }}" {{ old('crowding', $orthodonticCase->crowding) == $key ? 'selected' : '' }}>
                                            {{ __($value) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('crowding')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="crossbite" class="form-label">{{ __('Crossbite') }}</label>
                                <select name="crossbite" id="crossbite" class="form-select @error('crossbite') is-invalid @enderror">
                                    <option value="">{{ __('Select Type') }}</option>
                                    @foreach(\App\Models\OrthodonticCase::CROSSBITE_OPTIONS as $key => $value)
                                        <option value="{{ $key }}" {{ old('crossbite', $orthodonticCase->crossbite) == $key ? 'selected' : '' }}>
                                            {{ __($value) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('crossbite')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="open_bite" class="form-label">{{ __('Open Bite (mm)') }}</label>
                                <input type="number" name="open_bite" id="open_bite" step="0.01" min="0" max="99.99"
                                       class="form-control @error('open_bite') is-invalid @enderror"
                                       value="{{ old('open_bite', $orthodonticCase->open_bite) }}" placeholder="e.g., 1.5">
                                @error('open_bite')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">{{ __('Leave blank if no open bite present') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Financial Information -->
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-dollar-sign me-2"></i>{{ __('Financial Information') }}</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="total_cost" class="form-label">{{ __('Total Cost') }} <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" name="total_cost" id="total_cost" step="0.01"
                                               class="form-control @error('total_cost') is-invalid @enderror"
                                               value="{{ old('total_cost', $orthodonticCase->total_cost) }}" min="0" required>
                                        <span class="input-group-text">{{ $orthodonticCase->currency }}</span>
                                    </div>
                                    @error('total_cost')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="payment_plan" class="form-label">{{ __('Payment Plan') }} <span class="text-danger">*</span></label>
                                    <select name="payment_plan" id="payment_plan" class="form-select @error('payment_plan') is-invalid @enderror" required>
                                        @foreach(\App\Models\OrthodonticCase::PAYMENT_PLANS as $key => $value)
                                            <option value="{{ $key }}" {{ old('payment_plan', $orthodonticCase->payment_plan) == $key ? 'selected' : '' }}>
                                                {{ __($value) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('payment_plan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="alert alert-info mb-0">
                                    <small>
                                        <strong>{{ __('Paid Amount') }}:</strong> {{ number_format($orthodonticCase->paid_amount, 2) }} {{ $orthodonticCase->currency }}<br>
                                        <strong>{{ __('Balance') }}:</strong> {{ number_format($orthodonticCase->balance, 2) }} {{ $orthodonticCase->currency }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('orthodontics.show', $orthodonticCase) }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>
                                {{ __('Cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                {{ __('Update Case') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
