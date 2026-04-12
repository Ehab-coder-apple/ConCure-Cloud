@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-volume-high me-2"></i>{{ __('Create Audiometry Test') }}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    @if($entRecord)
                    <li class="breadcrumb-item"><a href="{{ route('ent.index') }}">{{ __('ENT Records') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('ent.show', $entRecord) }}">{{ __('View Record') }}</a></li>
                    @elseif($patient)
                    <li class="breadcrumb-item"><a href="{{ route('patients.show', $patient) }}">{{ __('Patient') }}</a></li>
                    @endif
                    <li class="breadcrumb-item active">{{ __('New Audiometry Test') }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <form action="{{ route('ent.audiometry.store') }}" method="POST" id="audiometryForm">
        @csrf

        @if($entRecord)
            <input type="hidden" name="ent_record_id" value="{{ $entRecord->id }}">
            <input type="hidden" name="patient_id" value="{{ $entRecord->patient_id }}">
        @elseif($patient)
            <input type="hidden" name="patient_id" value="{{ $patient->id }}">
        @endif

        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Test Information') }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @if($patient)
                    <div class="col-md-12 mb-3">
                        <p><strong>{{ __('Patient') }}:</strong> {{ $patient->full_name }} ({{ $patient->patient_id }})</p>
                    </div>
                    @endif

                    <div class="col-md-4 mb-3">
                        <label for="test_date" class="form-label">{{ __('Test Date') }} <span class="text-danger">*</span></label>
                        <input type="date" name="test_date" id="test_date" class="form-control @error('test_date') is-invalid @enderror"
                               value="{{ old('test_date', date('Y-m-d')) }}" required>
                        @error('test_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="test_type" class="form-label">{{ __('Test Type') }} <span class="text-danger">*</span></label>
                        <select name="test_type" id="test_type" class="form-select @error('test_type') is-invalid @enderror" required>
                            <option value="pure_tone" {{ old('test_type') == 'pure_tone' ? 'selected' : '' }}>{{ __('Pure Tone Audiometry') }}</option>
                            <option value="speech" {{ old('test_type') == 'speech' ? 'selected' : '' }}>{{ __('Speech Audiometry') }}</option>
                            <option value="tympanometry" {{ old('test_type') == 'tympanometry' ? 'selected' : '' }}>{{ __('Tympanometry') }}</option>
                            <option value="other" {{ old('test_type') == 'other' ? 'selected' : '' }}>{{ __('Other') }}</option>
                        </select>
                        @error('test_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Pure Tone Audiometry Section -->
        <div class="card mb-3" id="pure_tone_section">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Pure Tone Audiometry (Hearing Thresholds in dB)') }}</h5>
                <small class="text-muted">{{ __('Enter hearing threshold values for each frequency. Leave blank if not tested.') }}</small>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Right Ear -->
                    <div class="col-md-6 mb-3">
                        <h6 class="text-danger"><i class="fas fa-ear-listen me-2"></i>{{ __('Right Ear (Air Conduction)') }}</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>{{ __('Frequency (Hz)') }}</th>
                                        <th>{{ __('Threshold (dB)') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach([250, 500, 1000, 2000, 3000, 4000, 6000, 8000] as $freq)
                                    <tr>
                                        <td><strong>{{ $freq }} Hz</strong></td>
                                        <td>
                                            <input type="number" name="right_ear_data[{{ $freq }}]"
                                                   class="form-control form-control-sm"
                                                   placeholder="dB"
                                                   min="-10" max="120" step="5"
                                                   value="{{ old('right_ear_data.'.$freq) }}">
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Left Ear -->
                    <div class="col-md-6 mb-3">
                        <h6 class="text-primary"><i class="fas fa-ear-listen me-2"></i>{{ __('Left Ear (Air Conduction)') }}</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>{{ __('Frequency (Hz)') }}</th>
                                        <th>{{ __('Threshold (dB)') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach([250, 500, 1000, 2000, 3000, 4000, 6000, 8000] as $freq)
                                    <tr>
                                        <td><strong>{{ $freq }} Hz</strong></td>
                                        <td>
                                            <input type="number" name="left_ear_data[{{ $freq }}]"
                                                   class="form-control form-control-sm"
                                                   placeholder="dB"
                                                   min="-10" max="120" step="5"
                                                   value="{{ old('left_ear_data.'.$freq) }}">
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



        <!-- Speech Audiometry & Interpretation -->
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Additional Tests & Interpretation') }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Speech Audiometry -->
                    <div class="col-md-12 mb-3">
                        <h6>{{ __('Speech Audiometry (Optional)') }}</h6>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="right_srt" class="form-label">{{ __('Right SRT (dB)') }}</label>
                        <input type="number" name="right_srt" id="right_srt" class="form-control"
                               min="0" max="120" step="5" value="{{ old('right_srt') }}"
                               placeholder="Speech Reception Threshold">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="right_wrs" class="form-label">{{ __('Right WRS (%)') }}</label>
                        <input type="number" name="right_wrs" id="right_wrs" class="form-control"
                               min="0" max="100" step="5" value="{{ old('right_wrs') }}"
                               placeholder="Word Recognition Score">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="left_srt" class="form-label">{{ __('Left SRT (dB)') }}</label>
                        <input type="number" name="left_srt" id="left_srt" class="form-control"
                               min="0" max="120" step="5" value="{{ old('left_srt') }}"
                               placeholder="Speech Reception Threshold">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="left_wrs" class="form-label">{{ __('Left WRS (%)') }}</label>
                        <input type="number" name="left_wrs" id="left_wrs" class="form-control"
                               min="0" max="100" step="5" value="{{ old('left_wrs') }}"
                               placeholder="Word Recognition Score">
                    </div>

                    <!-- Tympanometry -->
                    <div class="col-md-12 mb-3 mt-3">
                        <h6>{{ __('Tympanometry (Optional)') }}</h6>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="right_tympanometry" class="form-label">{{ __('Right Ear Result') }}</label>
                        <input type="text" name="right_tympanometry" id="right_tympanometry" class="form-control"
                               value="{{ old('right_tympanometry') }}" placeholder="e.g., Type A, Type B, Type C">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="left_tympanometry" class="form-label">{{ __('Left Ear Result') }}</label>
                        <input type="text" name="left_tympanometry" id="left_tympanometry" class="form-control"
                               value="{{ old('left_tympanometry') }}" placeholder="e.g., Type A, Type B, Type C">
                    </div>

                    <!-- Interpretation -->
                    <div class="col-md-12 mb-3 mt-3">
                        <h6>{{ __('Clinical Interpretation') }}</h6>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="right_interpretation" class="form-label">{{ __('Right Ear Interpretation') }}</label>
                        <select name="right_interpretation" id="right_interpretation" class="form-select">
                            <option value="">{{ __('Select interpretation') }}</option>
                            <option value="normal" {{ old('right_interpretation') == 'normal' ? 'selected' : '' }}>{{ __('Normal Hearing') }}</option>
                            <option value="conductive_loss" {{ old('right_interpretation') == 'conductive_loss' ? 'selected' : '' }}>{{ __('Conductive Hearing Loss') }}</option>
                            <option value="sensorineural_loss" {{ old('right_interpretation') == 'sensorineural_loss' ? 'selected' : '' }}>{{ __('Sensorineural Hearing Loss') }}</option>
                            <option value="mixed_loss" {{ old('right_interpretation') == 'mixed_loss' ? 'selected' : '' }}>{{ __('Mixed Hearing Loss') }}</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="left_interpretation" class="form-label">{{ __('Left Ear Interpretation') }}</label>
                        <select name="left_interpretation" id="left_interpretation" class="form-select">
                            <option value="">{{ __('Select interpretation') }}</option>
                            <option value="normal" {{ old('left_interpretation') == 'normal' ? 'selected' : '' }}>{{ __('Normal Hearing') }}</option>
                            <option value="conductive_loss" {{ old('left_interpretation') == 'conductive_loss' ? 'selected' : '' }}>{{ __('Conductive Hearing Loss') }}</option>
                            <option value="sensorineural_loss" {{ old('left_interpretation') == 'sensorineural_loss' ? 'selected' : '' }}>{{ __('Sensorineural Hearing Loss') }}</option>
                            <option value="mixed_loss" {{ old('left_interpretation') == 'mixed_loss' ? 'selected' : '' }}>{{ __('Mixed Hearing Loss') }}</option>
                        </select>
                    </div>

                    <!-- Notes & Recommendations -->
                    <div class="col-md-12 mb-3">
                        <label for="notes" class="form-label">{{ __('Test Notes') }}</label>
                        <textarea name="notes" id="notes" rows="3" class="form-control"
                                  placeholder="{{ __('Additional observations, patient cooperation, environmental factors...') }}">{{ old('notes') }}</textarea>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="recommendations" class="form-label">{{ __('Recommendations') }}</label>
                        <textarea name="recommendations" id="recommendations" rows="3" class="form-control"
                                  placeholder="{{ __('Follow-up recommendations, referrals, hearing aid recommendations...') }}">{{ old('recommendations') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i>{{ __('Save Audiometry Test') }}
            </button>
            @if($entRecord)
            <a href="{{ route('ent.show', $entRecord) }}" class="btn btn-secondary">
                <i class="fas fa-times me-1"></i>{{ __('Cancel') }}
            </a>
            @elseif($patient)
            <a href="{{ route('patients.show', $patient) }}" class="btn btn-secondary">
                <i class="fas fa-times me-1"></i>{{ __('Cancel') }}
            </a>
            @endif
        </div>
    </form>
</div>
@endsection
