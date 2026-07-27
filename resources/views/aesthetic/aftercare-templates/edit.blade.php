@extends('layouts.app')

@section('title', __('Edit Aftercare Template'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0"><i class="fas fa-edit text-primary me-2"></i>{{ __('Edit Aftercare Template') }}</h1>
                    <p class="text-muted mb-0">{{ __('Adjust the standard instructions used when issuing aftercare PDFs') }}</p>
                </div>
                <a href="{{ route('aesthetic.aftercare-templates.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>{{ __('Back to Templates') }}
                </a>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header"><h6 class="mb-0"><i class="fas fa-notes-medical me-2"></i>{{ __('Template Details') }}</h6></div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('aesthetic.aftercare-templates.update', $aestheticAftercareTemplate) }}">
                                @csrf
                                @method('PUT')
                                @include('aesthetic.aftercare-templates._form')
                                <div class="d-flex justify-content-end gap-2 mt-4">
                                    <a href="{{ route('aesthetic.aftercare-templates.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>{{ __('Update Template') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header"><h6 class="mb-0"><i class="fas fa-lightbulb me-2"></i>{{ __('Tips') }}</h6></div>
                        <div class="card-body">
                            <div class="alert alert-info mb-0">
                                <h6 class="alert-heading">{{ __('Editing Notes') }}</h6>
                                <ul class="mb-0 small">
                                    <li>{{ __('Changes apply only to newly issued aftercare PDFs.') }}</li>
                                    <li>{{ __('Disable templates instead of deleting when you want to preserve history.') }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection