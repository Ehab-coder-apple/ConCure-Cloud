@extends('layouts.app')

@section('title', __('Add Inventory Product'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-plus text-primary me-2"></i>
                        {{ __('Add Inventory Product') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Add a new product to your aesthetic inventory') }}</p>
                </div>
                <a href="{{ route('aesthetic.inventory.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>
                    {{ __('Back to Inventory') }}
                </a>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                {{ __('Product Information') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('aesthetic.inventory.store') }}">
                                @csrf
                                @include('aesthetic.inventory._form')

                                <div class="d-flex justify-content-end gap-2 mt-4">
                                    <a href="{{ route('aesthetic.inventory.index') }}" class="btn btn-outline-secondary">
                                        {{ __('Cancel') }}
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>
                                        {{ __('Save Product') }}
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
                                <h6 class="alert-heading">{{ __('Inventory Management Tips') }}</h6>
                                <ul class="mb-0 small">
                                    <li>{{ __('Set a low-stock threshold for automatic alerts') }}</li>
                                    <li>{{ __('Track expiry dates for consumables') }}</li>
                                    <li>{{ __('Stock is only deducted when sessions are completed') }}</li>
                                    <li>{{ __('Use the adjust button for manual corrections') }}</li>
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
