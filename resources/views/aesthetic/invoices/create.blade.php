@extends('layouts.app')

@section('title', __('New Aesthetic Invoice'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-plus text-primary me-2"></i>
                        {{ __('New Aesthetic Invoice') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Create a new invoice for a patient') }}</p>
                </div>
                <a href="{{ route('aesthetic.invoices.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>{{ __('Back to Invoices') }}
                </a>
            </div>

            <div class="row">
                <div class="col-lg-10">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-file-invoice-dollar me-2"></i>
                                {{ __('Invoice Details') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('aesthetic.invoices.store') }}">
                                @csrf
                                @include('aesthetic.invoices._form')

                                <div class="d-flex justify-content-end gap-2 mt-4">
                                    <a href="{{ route('aesthetic.invoices.index') }}" class="btn btn-outline-secondary">
                                        {{ __('Cancel') }}
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>{{ __('Create Invoice') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
