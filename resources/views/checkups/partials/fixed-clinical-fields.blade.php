@php
    $checkup = $checkup ?? null;
    $idPrefix = $idPrefix ?? 'checkup_';
@endphp

<div class="row mt-4">
    <div class="col-12">
        <h6 class="text-primary mb-3">
            <i class="fas fa-notes-medical me-1"></i>
            {{ __('Clinical Summary') }}
        </h6>

        <div class="mb-3">
            <label for="{{ $idPrefix }}chief_complaint" class="form-label">{{ __('Chief Complaint') }}</label>
            <textarea class="form-control @error('chief_complaint') is-invalid @enderror" id="{{ $idPrefix }}chief_complaint" name="chief_complaint" rows="3" placeholder="{{ __('Main reason for the visit...') }}">{{ old('chief_complaint', $checkup?->chief_complaint) }}</textarea>
            @error('chief_complaint')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="{{ $idPrefix }}diagnosis" class="form-label">{{ __('Diagnosis') }}</label>
            <textarea class="form-control @error('diagnosis') is-invalid @enderror" id="{{ $idPrefix }}diagnosis" name="diagnosis" rows="3" placeholder="{{ __('Working diagnosis or clinical impression...') }}">{{ old('diagnosis', $checkup?->diagnosis) }}</textarea>
            @error('diagnosis')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="{{ $idPrefix }}clinical_examination" class="form-label">{{ __('Clinical Examination') }}</label>
            <textarea class="form-control @error('clinical_examination') is-invalid @enderror" id="{{ $idPrefix }}clinical_examination" name="clinical_examination" rows="3" placeholder="{{ __('Relevant examination findings...') }}">{{ old('clinical_examination', $checkup?->examination) }}</textarea>
            @error('clinical_examination')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>