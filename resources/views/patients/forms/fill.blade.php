@extends('layouts.app')

@section('page-title', __('Fill Form') . ' - ' . $patient->full_name)

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0">
                <i class="fas fa-pen text-primary"></i>
                {{ __('Fill Form') }} — {{ $assignment->template?->name ?? __('Form') }}
            </h1>
            <div class="d-flex gap-2">
                <a href="{{ route('patients.forms.show', [$patient, $assignment]) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> {{ __('Cancel') }}
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('patients.forms.fill.submit', [$patient, $assignment]) }}">
                        @csrf

                        <div class="mb-3">
                            <label for="content" class="form-label fw-bold">{{ __('Form Content / Notes') }}</label>
                            <textarea name="content" id="content" rows="14" class="form-control" placeholder="{{ __('Enter form notes or content here...') }}">{{ old('content', data_get($assignment->form_data, 'content', '')) }}</textarea>
                            @error('content')
                            <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" name="action" value="save" class="btn btn-info">
                                <i class="fas fa-save me-1"></i> {{ __('Save Progress') }}
                            </button>
                            <button type="submit" name="action" value="complete" class="btn btn-success">
                                <i class="fas fa-check me-1"></i> {{ __('Mark as Completed') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header fw-bold">
                    <i class="fas fa-info-circle me-1"></i> {{ __('Template & Tips') }}
                </div>
                <div class="card-body">
                    @if($assignment->template)
                        <p class="mb-2"><strong>{{ __('Template') }}:</strong> {{ $assignment->template->name }}</p>
                        <p class="mb-3">
                            <a href="{{ route('forms.templates.download', $assignment->template) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-download me-1"></i> {{ __('Download Original Template') }}
                            </a>
                        </p>
                    @endif
                    <ul class="mb-0 small text-muted">
                        <li>{{ __('Use Save Progress to keep working later.') }}</li>
                        <li>{{ __('Use Mark as Completed when the form is done and reviewed.') }}</li>
                        <li>{{ __('Completed forms become read-only.') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

