@extends('layouts.app')

@section('title', __('Add Aesthetic Treatment'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-plus text-primary me-2"></i>
                        {{ __('Add Aesthetic Treatment') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Create a new aesthetic treatment for your clinic catalog') }}</p>
                </div>
                <a href="{{ route('aesthetic.treatments.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>
                    {{ __('Back to Treatments') }}
                </a>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                {{ __('Treatment Information') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('aesthetic.treatments.store') }}">
                                @csrf
                                @include('aesthetic.treatments._form')

                                <div class="d-flex justify-content-end gap-2 mt-4">
                                    <a href="{{ route('aesthetic.treatments.index') }}" class="btn btn-outline-secondary">
                                        {{ __('Cancel') }}
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>
                                        {{ __('Save Treatment') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-lightbulb me-2"></i>
                                {{ __('Tips') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <h6 class="alert-heading">{{ __('Treatment Setup Tips') }}</h6>
                                <ul class="mb-0 small">
                                    <li>{{ __('Use clear, descriptive treatment names') }}</li>
                                    <li>{{ __('Choose the correct category for accurate reporting') }}</li>
                                    <li>{{ __('Set a default price to speed up invoicing') }}</li>
                                    <li>{{ __('Indicate session requirements when applicable') }}</li>
                                    <li>{{ __('Keep treatments active for selection in visits') }}</li>
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
