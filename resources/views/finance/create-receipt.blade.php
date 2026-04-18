@extends('layouts.app')

@section('title', __('Record Receipt'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="fas fa-hand-holding-usd me-2 text-success"></i>
            {{ __('Record Receipt') }}
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
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('finance.receipts.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="description" class="form-label">{{ __('Description') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="description" name="description" value="{{ old('description') }}" required>
                    </div>

                    <div class="col-md-6">
                        <label for="amount" class="form-label">{{ __('Amount') }} ({{ $currencySymbol }}) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="amount" name="amount" value="{{ old('amount') }}" step="0.01" min="0" required>
                    </div>

                    <div class="col-md-6">
                        <label for="category" class="form-label">{{ __('Category') }} <span class="text-danger">*</span></label>
                        <select class="form-select" id="category" name="category" required>
                            <option value="">{{ __('Select Category') }}</option>
                            @foreach(\App\Models\Receipt::CATEGORIES as $key => $value)
                                <option value="{{ $key }}" {{ old('category') == $key ? 'selected' : '' }}>{{ __($value) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="receipt_date" class="form-label">{{ __('Receipt Date') }} <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="receipt_date" name="receipt_date" value="{{ old('receipt_date', date('Y-m-d')) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label for="payment_method" class="form-label">{{ __('Payment Method') }} <span class="text-danger">*</span></label>
                        <select class="form-select" id="payment_method" name="payment_method" required>
                            <option value="">{{ __('Select Payment Method') }}</option>
                            @foreach(\App\Models\Receipt::PAYMENT_METHODS as $key => $value)
                                <option value="{{ $key }}" {{ old('payment_method') == $key ? 'selected' : '' }}>{{ __($value) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="payer_name" class="form-label">{{ __('Payer Name') }}</label>
                        <input type="text" class="form-control" id="payer_name" name="payer_name" value="{{ old('payer_name') }}">
                    </div>

                    <div class="col-md-6">
                        <label for="reference_number" class="form-label">{{ __('Reference Number') }}</label>
                        <input type="text" class="form-control" id="reference_number" name="reference_number" value="{{ old('reference_number') }}" placeholder="{{ __('Invoice #, Check #, etc.') }}">
                    </div>

                    <div class="col-md-6">
                        <label for="receipt_file" class="form-label">{{ __('Receipt File') }}</label>
                        <input type="file" class="form-control" id="receipt_file" name="receipt_file" accept=".pdf,.jpg,.jpeg,.png">
                        <div class="form-text">{{ __('PDF, JPG, PNG, max 5MB') }}</div>
                    </div>

                    <div class="col-md-12">
                        <label for="notes" class="form-label">{{ __('Notes') }}</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> {{ __('Submit for Approval') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
