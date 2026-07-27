@extends('layouts.app')

@section('page-title', __('Assign Form') . ' - ' . $patient->full_name)

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0">
                <i class="fas fa-plus text-primary"></i>
                {{ __('Assign Form to') }} {{ $patient->full_name }}
            </h1>
            <div>
                <a href="{{ route('patients.forms.index', $patient) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> {{ __('Back') }}
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('patients.forms.store', $patient) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="template_id" class="form-label">{{ __('Form Template') }} <span class="text-danger">*</span></label>
                            <select id="template_id" name="template_id" class="form-select @error('template_id') is-invalid @enderror" required>
                                <option value="">{{ __('Select a template...') }}</option>
                                @foreach($templates as $t)
                                    <option value="{{ $t->id }}" {{ old('template_id') == $t->id ? 'selected' : '' }}>
                                        {{ $t->name }}
                                        @if($t->category) - {{ $t->category }} @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('template_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if($templates->isEmpty())
                                <div class="form-text mt-1">
                                    {{ __('No templates found. You can create one from Forms > Templates.') }}
                                </div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">{{ __('Notes (optional)') }}</label>
                            <textarea id="notes" name="notes" rows="4" class="form-control @error('notes') is-invalid @enderror" placeholder="{{ __('Any extra instructions or context for this assignment...') }}">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> {{ __('Assign Form') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><strong>{{ __('Tips') }}</strong></div>
                <div class="card-body small text-muted">
                    <ul class="mb-0">
                        <li>{{ __('Only active templates for this clinic are listed.') }}</li>
                        <li>{{ __('After assignment, you can fill the form in Step 6.') }}</li>
                        <li>{{ __('Completed forms will be available for PDF export in Step 8.') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

