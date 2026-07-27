@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-notes-medical me-2"></i>{{ __('Create ENT Record') }}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('ent.index') }}">{{ __('ENT Records') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('Create') }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <form action="{{ route('ent.store') }}" method="POST">
        @csrf

        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Basic Information') }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="patient_id" class="form-label">{{ __('Patient') }} <span class="text-danger">*</span></label>
                        <select name="patient_id" id="patient_id" class="form-select @error('patient_id') is-invalid @enderror" required>
                            <option value="">{{ __('Select Patient') }}</option>
                            @foreach($patients as $patient)
                            <option value="{{ $patient->id }}" {{ old('patient_id', $patient?->id) == $patient->id ? 'selected' : '' }}>
                                {{ $patient->full_name }} ({{ $patient->patient_id }})
                            </option>
                            @endforeach
                        </select>
                        @error('patient_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="doctor_id" class="form-label">{{ __('Doctor') }} <span class="text-danger">*</span></label>
                        <select name="doctor_id" id="doctor_id" class="form-select @error('doctor_id') is-invalid @enderror" required>
                            @foreach($doctors as $doctor)
                            <option value="{{ $doctor->id }}" {{ old('doctor_id', Auth::id()) == $doctor->id ? 'selected' : '' }}>
                                {{ $doctor->full_name }}
                            </option>
                            @endforeach
                        </select>
                        @error('doctor_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="visit_date" class="form-label">{{ __('Visit Date') }} <span class="text-danger">*</span></label>
                        <input type="date" name="visit_date" id="visit_date" class="form-control @error('visit_date') is-invalid @enderror"
                               value="{{ old('visit_date', date('Y-m-d')) }}" required>
                        @error('visit_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Chief Complaint & Examination') }}</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="chief_complaint" class="form-label">{{ __('Chief Complaint') }}</label>
                    <textarea name="chief_complaint" id="chief_complaint" rows="3" class="form-control @error('chief_complaint') is-invalid @enderror">{{ old('chief_complaint') }}</textarea>
                    @error('chief_complaint')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="ear_examination" class="form-label">{{ __('Ear Examination') }}</label>
                        <textarea name="ear_examination" id="ear_examination" rows="4" class="form-control @error('ear_examination') is-invalid @enderror"
                                  placeholder="Otoscopy findings, tympanic membrane, external auditory canal...">{{ old('ear_examination') }}</textarea>
                        @error('ear_examination')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="nose_examination" class="form-label">{{ __('Nose Examination') }}</label>
                        <textarea name="nose_examination" id="nose_examination" rows="4" class="form-control @error('nose_examination') is-invalid @enderror"
                                  placeholder="Nasal mucosa, septum, turbinates, discharge...">{{ old('nose_examination') }}</textarea>
                        @error('nose_examination')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="throat_examination" class="form-label">{{ __('Throat Examination') }}</label>
                        <textarea name="throat_examination" id="throat_examination" rows="4" class="form-control @error('throat_examination') is-invalid @enderror"
                                  placeholder="Oropharynx, tonsils, pharyngeal wall...">{{ old('throat_examination') }}</textarea>
                        @error('throat_examination')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="neck_examination" class="form-label">{{ __('Neck Examination') }}</label>
                        <textarea name="neck_examination" id="neck_examination" rows="4" class="form-control @error('neck_examination') is-invalid @enderror"
                                  placeholder="Lymph nodes, thyroid, masses...">{{ old('neck_examination') }}</textarea>
                        @error('neck_examination')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="cranial_nerves" class="form-label">{{ __('Cranial Nerves Assessment') }}</label>
                        <textarea name="cranial_nerves" id="cranial_nerves" rows="3" class="form-control @error('cranial_nerves') is-invalid @enderror">{{ old('cranial_nerves') }}</textarea>
                        @error('cranial_nerves')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>



        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Diagnosis & Treatment') }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label for="diagnosis" class="form-label">{{ __('Diagnosis') }}</label>
                        <textarea name="diagnosis" id="diagnosis" rows="3" class="form-control @error('diagnosis') is-invalid @enderror">{{ old('diagnosis') }}</textarea>
                        @error('diagnosis')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="icd10_code" class="form-label">{{ __('ICD-10 Code') }}</label>
                        <input type="text" name="icd10_code" id="icd10_code" class="form-control @error('icd10_code') is-invalid @enderror"
                               value="{{ old('icd10_code') }}" placeholder="e.g., H66.9">
                        @error('icd10_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="treatment_plan" class="form-label">{{ __('Treatment Plan') }}</label>
                        <textarea name="treatment_plan" id="treatment_plan" rows="4" class="form-control @error('treatment_plan') is-invalid @enderror">{{ old('treatment_plan') }}</textarea>
                        @error('treatment_plan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="medications" class="form-label">{{ __('Medications Prescribed') }}</label>
                        <textarea name="medications" id="medications" rows="3" class="form-control @error('medications') is-invalid @enderror"
                                  placeholder="List medications, dosage, duration...">{{ old('medications') }}</textarea>
                        @error('medications')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="followup_date" class="form-label">{{ __('Follow-up Date') }}</label>
                        <input type="date" name="followup_date" id="followup_date" class="form-control @error('followup_date') is-invalid @enderror"
                               value="{{ old('followup_date') }}">
                        @error('followup_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="notes" class="form-label">{{ __('Additional Notes') }}</label>
                        <textarea name="notes" id="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i>{{ __('Save ENT Record') }}
            </button>
            <a href="{{ route('ent.index') }}" class="btn btn-secondary">
                <i class="fas fa-times me-1"></i>{{ __('Cancel') }}
            </a>
        </div>
    </form>
</div>
@endsection
