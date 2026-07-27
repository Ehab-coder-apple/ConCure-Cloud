@extends('layouts.app')

@section('title', __('Add Aesthetic Package'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-plus text-primary me-2"></i>
                        {{ __('Add Aesthetic Package') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Create a treatment package for your clinic') }}</p>
                </div>
                <a href="{{ route('aesthetic.packages.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>
                    {{ __('Back to Packages') }}
                </a>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                {{ __('Package Information') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('aesthetic.packages.store') }}">
                                @csrf
                                @include('aesthetic.packages._form')

                                <div class="d-flex justify-content-end gap-2 mt-4">
                                    <a href="{{ route('aesthetic.packages.index') }}" class="btn btn-outline-secondary">
                                        {{ __('Cancel') }}
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>
                                        {{ __('Save Package') }}
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
                                <h6 class="alert-heading">{{ __('Package Setup Tips') }}</h6>
                                <ul class="mb-0 small">
                                    <li>{{ __('Choose a treatment that supports multiple sessions') }}</li>
                                    <li>{{ __('Set the total number of sessions in the package') }}</li>
                                    <li>{{ __('Use discount to offer bundled pricing') }}</li>
                                    <li>{{ __('Set an expiry date for time-limited offers') }}</li>
                                    <li>{{ __('Final price is calculated automatically') }}</li>
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
