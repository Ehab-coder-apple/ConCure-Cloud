@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-cash-register me-2"></i>
                        {{ __('Create Sell — Multi-Item Invoice') }}
                    </h5>
                    <a href="{{ route('medicines.index') }}" class="btn btn-sm btn-light">
                        <i class="fas fa-arrow-left me-1"></i>{{ __('Back') }}
                    </a>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
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

                    <form method="POST" action="{{ route('medicines.sales.store') }}" id="multiSellForm">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="patient_id" class="form-label">{{ __('Patient') }}</label>
                                <select id="patient_id" name="patient_id" class="form-select">
                                    <option value="">{{ __('— Walk-in / no patient —') }}</option>
                                    @foreach($patients as $p)
                                        <option value="{{ $p->id }}" @selected(old('patient_id') == $p->id)>
                                            {{ trim(($p->first_name ?? '') . ' ' . ($p->last_name ?? '')) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="payment_method" class="form-label">{{ __('Payment Method') }} *</label>
                                <select id="payment_method" name="payment_method" class="form-select" required>
                                    @foreach(['cash','card','credit','insurance','other'] as $pm)
                                        <option value="{{ $pm }}" @selected(old('payment_method', 'cash') === $pm)>
                                            {{ __(ucfirst($pm)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="paid_amount" class="form-label">{{ __('Paid Amount') }}</label>
                                <input type="number" step="0.01" min="0" id="paid_amount" name="paid_amount"
                                       class="form-control" value="{{ old('paid_amount') }}"
                                       placeholder="{{ __('Defaults to total') }}">
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle" id="itemsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="min-width:280px;">{{ __('Medicine') }}</th>
                                        <th style="width:110px;">{{ __('Stock') }}</th>
                                        <th style="width:120px;">{{ __('Quantity') }}</th>
                                        <th style="width:140px;">{{ __('Unit Price') }}</th>
                                        <th style="width:140px;">{{ __('Line Total') }}</th>
                                        <th style="width:50px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody"></tbody>
                            </table>
                        </div>

                        <button type="button" class="btn btn-outline-primary btn-sm mb-3" id="addRowBtn">
                            <i class="fas fa-plus me-1"></i>{{ __('Add Row') }}
                        </button>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label for="notes" class="form-label">{{ __('Notes') }}</label>
                                <textarea id="notes" name="notes" rows="3" class="form-control"
                                          maxlength="1000">{{ old('notes') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm">
                                    <tr>
                                        <th>{{ __('Subtotal') }}</th>
                                        <td class="text-end" id="subtotalCell">0.00</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Discount') }}</th>
                                        <td>
                                            <input type="number" step="0.01" min="0" id="discount" name="discount"
                                                   class="form-control form-control-sm text-end"
                                                   value="{{ old('discount', 0) }}">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Tax') }}</th>
                                        <td>
                                            <input type="number" step="0.01" min="0" id="tax" name="tax"
                                                   class="form-control form-control-sm text-end"
                                                   value="{{ old('tax', 0) }}">
                                        </td>
                                    </tr>
                                    <tr class="table-active">
                                        <th>{{ __('Grand Total') }}</th>
                                        <td class="text-end fw-bold" id="totalCell">0.00</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('medicines.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                            <button type="submit" class="btn btn-danger" id="submitBtn">
                                <i class="fas fa-check me-1"></i>{{ __('Confirm Sale') }}
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
(function () {
    const medicines = @json($medicines);
    const medById = {};
    medicines.forEach(m => { medById[m.id] = m; });

    const tbody = document.getElementById('itemsBody');
    const subtotalCell = document.getElementById('subtotalCell');
    const totalCell = document.getElementById('totalCell');
    const discountInput = document.getElementById('discount');
    const taxInput = document.getElementById('tax');
    let rowIndex = 0;

    function buildOptions(selectedId) {
        let html = '<option value="">— {{ __('Select medicine') }} —</option>';
        medicines.forEach(m => {
            const label = `${m.name}${m.dosage ? ' ' + m.dosage : ''}${m.form ? ' (' + m.form + ')' : ''}`;
            html += `<option value="${m.id}" data-stock="${m.stock_quantity}" data-price="${m.selling_price ?? 0}" ${selectedId == m.id ? 'selected' : ''}>${label}</option>`;
        });
        return html;
    }

    function addRow(presetId) {
        const idx = rowIndex++;
        const tr = document.createElement('tr');
        tr.dataset.idx = idx;
        tr.innerHTML = `
            <td>
                <select name="items[${idx}][medicine_id]" class="form-select form-select-sm med-select" required>
                    ${buildOptions(presetId)}
                </select>
            </td>
            <td class="stock-cell text-muted">—</td>
            <td>
                <input type="number" step="0.01" min="0.01" name="items[${idx}][quantity]"
                       class="form-control form-control-sm qty-input" value="1" required>
            </td>
            <td>
                <input type="number" step="0.01" min="0" name="items[${idx}][unit_price]"
                       class="form-control form-control-sm price-input" value="0" required>
            </td>
            <td class="text-end line-total">0.00</td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-danger remove-row" title="{{ __('Remove') }}">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        bindRow(tr);
        if (presetId) {
            tr.querySelector('.med-select').dispatchEvent(new Event('change'));
        }
    }

    function bindRow(tr) {
        const sel = tr.querySelector('.med-select');
        const qty = tr.querySelector('.qty-input');
        const price = tr.querySelector('.price-input');
        const stockCell = tr.querySelector('.stock-cell');
        const lineTotal = tr.querySelector('.line-total');

        sel.addEventListener('change', function () {
            const opt = this.selectedOptions[0];
            if (opt && opt.value) {
                stockCell.textContent = opt.dataset.stock;
                stockCell.classList.toggle('text-danger', parseFloat(opt.dataset.stock) <= 0);
                if (parseFloat(price.value) === 0) {
                    price.value = parseFloat(opt.dataset.price || 0).toFixed(2);
                }
            } else {
                stockCell.textContent = '—';
                stockCell.classList.remove('text-danger');
            }
            recalcRow(tr);
        });

        qty.addEventListener('input', () => recalcRow(tr));
        price.addEventListener('input', () => recalcRow(tr));

        tr.querySelector('.remove-row').addEventListener('click', () => {
            tr.remove();
            recalcAll();
        });
    }

    function recalcRow(tr) {
        const qty = parseFloat(tr.querySelector('.qty-input').value) || 0;
        const price = parseFloat(tr.querySelector('.price-input').value) || 0;
        tr.querySelector('.line-total').textContent = (qty * price).toFixed(2);
        recalcAll();
    }

    function recalcAll() {
        let subtotal = 0;
        document.querySelectorAll('#itemsBody tr').forEach(tr => {
            subtotal += parseFloat(tr.querySelector('.line-total').textContent) || 0;
        });
        const discount = parseFloat(discountInput.value) || 0;
        const tax = parseFloat(taxInput.value) || 0;
        const total = Math.max(0, subtotal - discount + tax);
        subtotalCell.textContent = subtotal.toFixed(2);
        totalCell.textContent = total.toFixed(2);
    }

    document.getElementById('addRowBtn').addEventListener('click', () => addRow());
    discountInput.addEventListener('input', recalcAll);
    taxInput.addEventListener('input', recalcAll);

    document.getElementById('multiSellForm').addEventListener('submit', function (e) {
        if (document.querySelectorAll('#itemsBody tr').length === 0) {
            e.preventDefault();
            alert('{{ __('Add at least one medicine.') }}');
        }
    });

    addRow();
})();
</script>
@endpush
@endsection
