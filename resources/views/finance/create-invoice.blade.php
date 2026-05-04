@extends('layouts.app')

@section('title', __('Add Invoice'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="fas fa-file-invoice-dollar me-2 text-primary"></i>
            {{ __('Add Invoice') }}
        </h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <form id="createInvoiceForm" method="POST" action="{{ route('finance.invoices.store') }}">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="patient_id" class="form-label">{{ __('Patient') }} <span class="text-danger">*</span></label>
                        <select name="patient_id" id="patient_id" class="form-select" required>
                            <option value="">{{ __('Select Patient') }}</option>
                            @foreach($patients as $patient)
                                <option value="{{ $patient->id }}">
                                    {{ $patient->first_name }} {{ $patient->last_name }}
                                    @if($patient->patient_id) (ID: {{ $patient->patient_id }}) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="due_date" class="form-label">{{ __('Due Date') }}</label>
                        <input type="date" name="due_date" id="due_date" class="form-control">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">{{ __('Notes') }}</label>
                    <textarea name="notes" id="notes" class="form-control" rows="2"></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="payment_amount" class="form-label">{{ __('Payment Amount') }} ({{ $currencySymbol ?? '$' }})</label>
                        <input type="number" name="payment_amount" id="payment_amount" class="form-control"
                               step="0.01" min="0" value="0" placeholder="{{ __('Amount paid now') }}">
                        <div class="form-text text-muted">{{ __('Leave 0 if no payment at creation') }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="payment_method" class="form-label">{{ __('Payment Method') }}</label>
                        <select name="payment_method" id="payment_method" class="form-select">
                            <option value="cash" selected>{{ __('Cash') }}</option>
                            <option value="card">{{ __('Credit/Debit Card') }}</option>
                            <option value="bank_transfer">{{ __('Bank Transfer') }}</option>
                            <option value="check">{{ __('Check') }}</option>
                            <option value="other">{{ __('Other') }}</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ __('Invoice Items') }}</label>
                    <div id="invoice-items">
                        <div class="invoice-item border p-3 mb-2">
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <input type="text" name="items[0][description]" class="form-control" placeholder="{{ __('Description') }}" required>
                                </div>
                                <div class="col-md-2">
                                    <input type="number" name="items[0][quantity]" class="form-control" placeholder="{{ __('Qty') }}" min="1" value="1" required>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" name="items[0][unit_price]" class="form-control" placeholder="{{ __('Unit Price') }} ({{ $currencySymbol }})" step="0.01" min="0" required>
                                </div>
                                <div class="col-md-2">
                                    <select name="items[0][item_type]" class="form-select" required>
                                        <option value="consultation">{{ __('Consultation') }}</option>
                                        <option value="procedure">{{ __('Procedure') }}</option>
                                        <option value="medication">{{ __('Medication') }}</option>
                                        <option value="lab_test">{{ __('Lab Test') }}</option>
                                        <option value="other">{{ __('Other') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-outline-danger btn-sm remove-item">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" id="add-item" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-plus"></i> {{ __('Add Item') }}
                    </button>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> {{ __('Create Invoice') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    let itemIndex = 1;
    const wrap = document.getElementById('invoice-items');
    document.getElementById('add-item').addEventListener('click', function () {
        const row = document.createElement('div');
        row.className = 'invoice-item border p-3 mb-2';
        row.innerHTML = `
            <div class="row g-2">
                <div class="col-md-4"><input type="text" name="items[${itemIndex}][description]" class="form-control" placeholder="{{ __('Description') }}" required></div>
                <div class="col-md-2"><input type="number" name="items[${itemIndex}][quantity]" class="form-control" placeholder="{{ __('Qty') }}" min="1" value="1" required></div>
                <div class="col-md-3"><input type="number" name="items[${itemIndex}][unit_price]" class="form-control" placeholder="{{ __('Unit Price') }}" step="0.01" min="0" required></div>
                <div class="col-md-2">
                    <select name="items[${itemIndex}][item_type]" class="form-select" required>
                        <option value="consultation">{{ __('Consultation') }}</option>
                        <option value="procedure">{{ __('Procedure') }}</option>
                        <option value="medication">{{ __('Medication') }}</option>
                        <option value="lab_test">{{ __('Lab Test') }}</option>
                        <option value="other">{{ __('Other') }}</option>
                    </select>
                </div>
                <div class="col-md-1"><button type="button" class="btn btn-outline-danger btn-sm remove-item"><i class="fas fa-trash"></i></button></div>
            </div>`;
        wrap.appendChild(row);
        itemIndex++;
    });
    wrap.addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-item');
        if (!btn) return;
        const rows = wrap.querySelectorAll('.invoice-item');
        if (rows.length > 1) btn.closest('.invoice-item').remove();
    });
});
</script>
@endpush
