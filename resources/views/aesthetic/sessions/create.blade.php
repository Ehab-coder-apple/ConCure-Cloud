@extends('layouts.app')

@section('title', __('New Treatment Session'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-plus text-primary me-2"></i>
                        {{ __('New Treatment Session') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Schedule a new treatment session for a patient') }}</p>
                </div>
                <a href="{{ route('aesthetic.sessions.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>
                    {{ __('Back to Sessions') }}
                </a>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                {{ __('Session Information') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('aesthetic.sessions.store') }}">
                                @csrf
                                @include('aesthetic.sessions._form')

                                <div class="d-flex justify-content-end gap-2 mt-4">
                                    <a href="{{ route('aesthetic.sessions.index') }}" class="btn btn-outline-secondary">
                                        {{ __('Cancel') }}
                                    </a>
                                    <button type="submit" name="next_action" value="create_invoice" class="btn btn-outline-success">
                                        <i class="fas fa-file-invoice-dollar me-1"></i>
                                        {{ __('Save Session & Create Invoice') }}
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>
                                        {{ __('Save Session') }}
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
                                <h6 class="alert-heading">{{ __('Session Tips') }}</h6>
                                <ul class="mb-0 small">
                                    <li>{{ __('Select the patient package first') }}</li>
                                    <li>{{ __('Session number auto-increments') }}</li>
                                    <li>{{ __('Upload before images before the session starts') }}</li>
                                    <li>{{ __('Upload after images when the session is complete') }}</li>
                                    <li>{{ __('Mark the session as completed when done') }}</li>
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
