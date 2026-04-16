@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-cart-plus me-2"></i>
                            {{ __('Purchase Medicine') }}
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
                                <small class="text-muted d-block">{{ __('Current Stock') }}</small>
                                <h5 class="mb-0">
                                    <span class="badge bg-{{ $medicine->stock_quantity > 10 ? 'success' : ($medicine->stock_quantity > 0 ? 'warning' : 'danger') }}">
                                        {{ $medicine->stock_quantity }} {{ __('units') }}
                                    </span>
                                </h5>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block">{{ __('Last Purchase Price') }}</small>
                                <h5 class="mb-0">{{ number_format($medicine->purchase_price ?? 0, 2) }}</h5>
                            </div>
                        </div>
                    </div>

                    <!-- Purchase Form -->
                    <form method="POST" action="{{ route('medicines.purchase.process', $medicine) }}">
                        @csrf

                        <div class="row">
                            <!-- Supplier Name -->
                            <div class="col-md-6 mb-3">
                                <label for="supplier_name" class="form-label">{{ __('Supplier Name') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('supplier_name') is-invalid @enderror" 
                                       id="supplier_name" name="supplier_name" value="{{ old('supplier_name') }}" 
                                       placeholder="{{ __('Enter supplier name') }}" required>
                                @error('supplier_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Quantity -->
                            <div class="col-md-6 mb-3">
                                <label for="quantity" class="form-label">{{ __('Quantity') }} <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('quantity') is-invalid @enderror" 
                                       id="quantity" name="quantity" value="{{ old('quantity', 1) }}" 
                                       min="1" step="1" required>
                                @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Unit Price -->
                            <div class="col-md-6 mb-3">
                                <label for="unit_price" class="form-label">{{ __('Purchase Price (per unit)') }} <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('unit_price') is-invalid @enderror" 
                                       id="unit_price" name="unit_price" 
                                       value="{{ old('unit_price', $medicine->purchase_price ?? 0) }}" 
                                       min="0" step="0.01" required>
                                @error('unit_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Payment Method -->
                            <div class="col-md-6 mb-3">
                                <label for="payment_method" class="form-label">{{ __('Payment Method') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('payment_method') is-invalid @enderror" 
                                        id="payment_method" name="payment_method" required>
                                    <option value="">{{ __('Select payment method') }}</option>
                                    <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>{{ __('Cash') }}</option>
                                    <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>{{ __('Card') }}</option>
                                    <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>{{ __('Bank Transfer') }}</option>
                                    <option value="credit" {{ old('payment_method') == 'credit' ? 'selected' : '' }}>{{ __('Credit') }}</option>
                                    <option value="check" {{ old('payment_method') == 'check' ? 'selected' : '' }}>{{ __('Check') }}</option>
                                    <option value="other" {{ old('payment_method') == 'other' ? 'selected' : '' }}>{{ __('Other') }}</option>
                                </select>
                                @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Expiry Date -->
                            <div class="col-md-6 mb-3">
                                <label for="expiry_date" class="form-label">{{ __('Expiry Date') }}</label>
                                <input type="date" class="form-control @error('expiry_date') is-invalid @enderror" 
                                       id="expiry_date" name="expiry_date" 
                                       value="{{ old('expiry_date', $medicine->expiry_date ? $medicine->expiry_date->format('Y-m-d') : '') }}">
                                @error('expiry_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Batch Number -->
                            <div class="col-md-6 mb-3">
                                <label for="batch_number" class="form-label">{{ __('Batch/Lot Number') }}</label>
                                <input type="text" class="form-control @error('batch_number') is-invalid @enderror" 
                                       id="batch_number" name="batch_number" value="{{ old('batch_number') }}" 
                                       placeholder="{{ __('e.g., BATCH-2024-001') }}">
                                @error('batch_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Total Amount (Auto-calculated) -->
                            <div class="col-12 mb-3">
                                <div class="alert alert-warning">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <h6 class="mb-0">{{ __('Total Purchase Amount') }}:</h6>
                                        </div>
                                        <div class="col-md-6 text-end">
                                            <h4 class="mb-0" id="total_amount">0.00</h4>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-6">
                                            <small class="text-muted">{{ __('Stock after purchase') }}:</small>
                                        </div>
                                        <div class="col-md-6 text-end">
                                            <strong id="stock_after">{{ $medicine->stock_quantity }}</strong> {{ __('units') }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="col-12 mb-3">
                                <label for="notes" class="form-label">{{ __('Notes') }}</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" name="notes" rows="2" 
                                          placeholder="{{ __('Additional notes (optional)...') }}">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('medicines.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>{{ __('Cancel') }}
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check me-1"></i>{{ __('Complete Purchase') }}
                            </button>
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
    const stockAfterDisplay = document.getElementById('stock_after');
    const currentStock = {{ $medicine->stock_quantity }};

    function calculateTotals() {
        const quantity = parseFloat(quantityInput.value) || 0;
        const unitPrice = parseFloat(unitPriceInput.value) || 0;
        const total = quantity * unitPrice;
        const stockAfter = currentStock + quantity;
        
        totalAmountDisplay.textContent = total.toFixed(2);
        stockAfterDisplay.textContent = stockAfter;
    }

    quantityInput.addEventListener('input', calculateTotals);
    unitPriceInput.addEventListener('input', calculateTotals);

    // Calculate initial totals
    calculateTotals();
});
</script>
@endpush
@endsection
