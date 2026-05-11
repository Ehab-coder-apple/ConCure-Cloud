@extends('layouts.guest')

@section('title', 'Contract Review Required')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Warning Alert -->
            <div class="alert alert-warning border-warning shadow-sm mb-4">
                <div class="d-flex align-items-start">
                    <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                    <div>
                        <h5 class="alert-heading mb-2">Contract Review Required</h5>
                        <p class="mb-0">
                            Before you can access <strong>{{ $clinic->name }}</strong> on ConCure Cloud, 
                            you must review and accept the service agreement below.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Contract Card -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-file-contract me-2"></i>
                        {{ $contract->contract_title ?? 'Service Agreement' }}
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Contract Content -->
                    <div class="contract-content p-4 bg-light border rounded" style="max-height: 500px; overflow-y: auto;">
                        {!! nl2br(e($contract->contract_content)) !!}
                    </div>

                    <!-- Contract Details -->
                    <div class="row mt-4">
                        @if($contract->monthly_price)
                        <div class="col-md-4 mb-3">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block">Monthly Price</small>
                                <strong class="text-success">{{ number_format($contract->monthly_price, 2) }} IQD</strong>
                            </div>
                        </div>
                        @endif

                        @if($contract->contract_duration_months)
                        <div class="col-md-4 mb-3">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block">Contract Duration</small>
                                <strong>{{ $contract->contract_duration_months }} Months</strong>
                            </div>
                        </div>
                        @endif

                        @if($contract->end_date)
                        <div class="col-md-4 mb-3">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block">Valid Until</small>
                                <strong>{{ $contract->end_date->format('Y-m-d') }}</strong>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Acceptance Form -->
                    <form action="{{ route('contract.accept') }}" method="POST" class="mt-4">
                        @csrf

                        <!-- Agreement Checkbox -->
                        <div class="form-check mb-3 p-3 bg-light rounded">
                            <input class="form-check-input" type="checkbox" name="agree" id="agree" value="1" required>
                            <label class="form-check-label fw-bold" for="agree">
                                I have read and agree to the terms and conditions outlined in this service agreement.
                            </label>
                            @error('agree')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Signature Input -->
                        <div class="mb-4">
                            <label for="signature_name" class="form-label fw-bold">
                                <i class="fas fa-signature me-2"></i>Digital Signature
                            </label>
                            <input 
                                type="text" 
                                class="form-control form-control-lg @error('signature_name') is-invalid @enderror" 
                                id="signature_name" 
                                name="signature_name" 
                                placeholder="Type your full name to sign" 
                                required
                                autocomplete="name"
                            >
                            <small class="text-muted">
                                By typing your name, you agree that this constitutes a legally binding signature.
                            </small>
                            @error('signature_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-check-circle me-2"></i>
                                Accept Contract and Activate Clinic
                            </button>
                        </div>

                        <!-- Footer Note -->
                        <div class="alert alert-info mt-3 mb-0">
                            <small>
                                <i class="fas fa-info-circle me-2"></i>
                                Your IP address and acceptance timestamp will be recorded for verification purposes.
                            </small>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Logout Option -->
            <div class="text-center mt-4">
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-link text-muted">
                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@if ($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function() {
        alert('Please fill in all required fields correctly.');
    });
</script>
@endif
@endsection
