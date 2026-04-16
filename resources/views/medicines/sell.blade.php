@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-shopping-cart me-2"></i>
                            {{ __('Sell Medicine') }}
                        </h5>
                        <a href="{{ route('medicines.index') }}" class="btn btn-sm btn-light">
                            <i class="fas fa-arrow-left me-1"></i>{{ __('Back') }}
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Medicine Info -->
                    <div class="alert alert-info">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="mb-1"><strong>{{ $medicine->name }}</strong></h6>
                                <p class="mb-0">
                                    <small class="text-muted">
                                        {{ $medicine->form_display }} @if($medicine->dosage)- {{ $medicine->dosage }}@endif
                                    </small>
                                </p>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block">{{ __('Available Stock') }}</small>
                                <h5 class="mb-0">
                                    <span class="badge bg-{{ $medicine->stock_quantity > 10 ? 'success' : ($medicine->stock_quantity > 0 ? 'warning' : 'danger') }}">
                                        {{ $medicine->stock_quantity }} {{ __('units') }}
                                    </span>
                                </h5>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block">{{ __('Selling Price') }}</small>
                                <h5 class="mb-0">{{ number_format($medicine->selling_price ?? 0, 2) }}</h5>
                            </div>
                        </div>
                    </div>

                    @if($medicine->stock_quantity <= 0)
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            {{ __('This medicine is out of stock. Please purchase new stock before selling.') }}
                        </div>
                    @endif

                    <!-- Sale Form -->
                    <form method="POST" action="{{ route('medicines.sell.process', $medicine) }}">
                        @csrf

                        <div class="row">
                            <!-- Patient Selection -->
                            <div class="col-md-6 mb-3">
                                <label for="patient_id" class="form-label">{{ __('Patient') }} <small class="text-muted">({{ __('Optional') }})</small></label>
                                <select class="form-select @error('patient_id') is-invalid @enderror" id="patient_id" name="patient_id">
                                    <option value="">{{ __('Select patient (walk-in if none)') }}</option>
                                    @foreach($patients as $patient)
                                        <option value="{{ $patient->id }}" {{ old('patient_id') == $patient->id ? 'selected' : '' }}>
                                            {{ $patient->first_name }} {{ $patient->last_name }} ({{ $patient->patient_id }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('patient_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Quantity -->
                            <div class="col-md-6 mb-3">
                                <label for="quantity" class="form-label">{{ __('Quantity') }} <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('quantity') is-invalid @enderror"
                                       id="quantity" name="quantity" value="{{ old('quantity', 1) }}"
                                       min="0.01" max="{{ $medicine->stock_quantity }}" step="0.01" required
                                       {{ $medicine->stock_quantity <= 0 ? 'disabled' : '' }}>
                                <small class="text-muted">{{ __('Max') }}: {{ $medicine->stock_quantity }} {{ __('units') }} | {{ __('Supports decimals (e.g., 0.5, 1.5)') }}</small>
                                @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Unit Price -->
                            <div class="col-md-6 mb-3">
                                <label for="unit_price" class="form-label">{{ __('Unit Price') }} <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('unit_price') is-invalid @enderror" 
                                       id="unit_price" name="unit_price" 
                                       value="{{ old('unit_price', $medicine->selling_price ?? 0) }}" 
                                       min="0" step="0.01" required
                                       {{ $medicine->stock_quantity <= 0 ? 'disabled' : '' }}>
                                @error('unit_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Payment Method -->
                            <div class="col-md-6 mb-3">
                                <label for="payment_method" class="form-label">{{ __('Payment Method') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('payment_method') is-invalid @enderror" 
                                        id="payment_method" name="payment_method" required
                                        {{ $medicine->stock_quantity <= 0 ? 'disabled' : '' }}>
                                    <option value="">{{ __('Select payment method') }}</option>
                                    <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>{{ __('Cash') }}</option>
                                    <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>{{ __('Card') }}</option>
                                    <option value="credit" {{ old('payment_method') == 'credit' ? 'selected' : '' }}>{{ __('Credit') }}</option>
                                    <option value="insurance" {{ old('payment_method') == 'insurance' ? 'selected' : '' }}>{{ __('Insurance') }}</option>
                                    <option value="other" {{ old('payment_method') == 'other' ? 'selected' : '' }}>{{ __('Other') }}</option>
                                </select>
                                @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Total Amount (Auto-calculated) -->
                            <div class="col-12 mb-3">
                                <div class="alert alert-success">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <h6 class="mb-0">{{ __('Total Amount') }}:</h6>
                                        </div>
                                        <div class="col-md-6 text-end">
                                            <h4 class="mb-0" id="total_amount">0.00</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="col-12 mb-3">
                                <label for="notes" class="form-label">{{ __('Notes') }}</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" name="notes" rows="2" 
                                          placeholder="{{ __('Additional notes (optional)...') }}"
                                          {{ $medicine->stock_quantity <= 0 ? 'disabled' : '' }}>{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('medicines.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>{{ __('Cancel') }}
                            </a>
                            @if($medicine->stock_quantity > 0)
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-check me-1"></i>{{ __('Complete Sale') }}
                            </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const quantityInput = document.getElementById('quantity');
    const unitPriceInput = document.getElementById('unit_price');
    const totalAmountDisplay = document.getElementById('total_amount');

    function calculateTotal() {
        const quantity = parseFloat(quantityInput.value) || 0;
        const unitPrice = parseFloat(unitPriceInput.value) || 0;
        const total = quantity * unitPrice;
        totalAmountDisplay.textContent = total.toFixed(2);
    }

    quantityInput.addEventListener('input', calculateTotal);
    unitPriceInput.addEventListener('input', calculateTotal);

    // Calculate initial total
    calculateTotal();
});
</script>
@endpush
@endsection
