<div class="row g-3">
    <!-- Patient -->
    <div class="col-md-6">
        <label for="patient_id" class="form-label">{{ __('Patient') }} <span class="text-danger">*</span></label>
        <select class="form-select @error('patient_id') is-invalid @enderror" id="patient_id" name="patient_id" required>
            <option value="">{{ __('Select Patient') }}</option>
            @foreach($patients as $patient)
                <option value="{{ $patient->id }}" {{ old('patient_id', $aestheticInvoice->patient_id ?? ($preselectedPatient->id ?? '')) == $patient->id ? 'selected' : '' }}>
                    {{ $patient->first_name }} {{ $patient->last_name }}
                    @if($patient->phone) ({{ $patient->phone }}) @endif
                </option>
            @endforeach
        </select>
        @error('patient_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Session (optional) -->
    <div class="col-md-6">
        <label for="session_id" class="form-label">{{ __('Session (Optional)') }}</label>
        <select class="form-select @error('session_id') is-invalid @enderror" id="session_id" name="session_id">
            <option value="">{{ __('No Session') }}</option>
            @foreach($sessions as $session)
                <option value="{{ $session->id }}" {{ old('session_id', $aestheticInvoice->session_id ?? ($preselectedSession->id ?? '')) == $session->id ? 'selected' : '' }}>
                    {{ $session->patientPackage->patient->first_name }} {{ $session->patientPackage->patient->last_name }}
                    - {{ $session->patientPackage->package->name ?? '-' }} (Session #{{ $session->session_number }})
                </option>
            @endforeach
        </select>
        @error('session_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Invoice Date -->
    <div class="col-md-4">
        <label for="invoice_date" class="form-label">{{ __('Invoice Date') }} <span class="text-danger">*</span></label>
        <input type="date" class="form-control @error('invoice_date') is-invalid @enderror"
               id="invoice_date" name="invoice_date"
               value="{{ old('invoice_date', isset($aestheticInvoice) && $aestheticInvoice->invoice_date ? $aestheticInvoice->invoice_date->format('Y-m-d') : now()->format('Y-m-d')) }}"
               required>
        @error('invoice_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Due Date -->
    <div class="col-md-4">
        <label for="due_date" class="form-label">{{ __('Due Date') }}</label>
        <input type="date" class="form-control @error('due_date') is-invalid @enderror"
               id="due_date" name="due_date"
               value="{{ old('due_date', isset($aestheticInvoice) && $aestheticInvoice->due_date ? $aestheticInvoice->due_date->format('Y-m-d') : '') }}">
        @error('due_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Tax Rate -->
    <div class="col-md-4">
        <label for="tax_rate" class="form-label">{{ __('Tax Rate (%)') }}</label>
        <input type="number" step="0.01" min="0" max="100"
               class="form-control @error('tax_rate') is-invalid @enderror"
               id="tax_rate" name="tax_rate"
               value="{{ old('tax_rate', $aestheticInvoice->tax_rate ?? 0) }}">
        @error('tax_rate')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Notes -->
    <div class="col-12">
        <label for="notes" class="form-label">{{ __('Notes') }}</label>
        <textarea class="form-control @error('notes') is-invalid @enderror"
                  id="notes" name="notes" rows="2"
                  placeholder="{{ __('Invoice notes...') }}">{{ old('notes', $aestheticInvoice->notes ?? '') }}</textarea>
        @error('notes')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Line Items -->
    <div class="col-12">
        <hr class="my-2">
        <h6 class="mb-3">
            <i class="fas fa-list me-2 text-primary"></i>
            {{ __('Line Items') }}
        </h6>
        <div class="table-responsive">
            <table class="table table-sm table-bordered" id="items_table">
                <thead class="table-light">
                    <tr>
                        <th style="width: 35%">{{ __('Description') }}</th>
                        <th style="width: 20%">{{ __('Treatment') }}</th>
                        <th style="width: 10%">{{ __('Qty') }}</th>
                        <th style="width: 15%">{{ __('Unit Price') }}</th>
                        <th style="width: 10%">{{ __('Discount') }}</th>
                        <th style="width: 15%">{{ __('Total') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="items_body">
                    @php
                        $items = old('items', []);
                        if (empty($items) && isset($aestheticInvoice)) {
                            foreach ($aestheticInvoice->items as $item) {
                                $items[] = [
                                    'id' => $item->id,
                                    'description' => $item->description,
                                    'treatment_id' => $item->treatment_id,
                                    'quantity' => $item->quantity,
                                    'unit_price' => $item->unit_price,
                                    'discount' => $item->discount,
                                ];
                            }
                        }
                        if (empty($items) && isset($lineItems) && count($lineItems) > 0) {
                            foreach ($lineItems as $lineItem) {
                                $items[] = $lineItem;
                            }
                        }
                        if (empty($items)) {
                            $items = [[]];
                        }
                    @endphp
                    @foreach($items as $i => $item)
                    <tr class="item-row">
                        <td>
                            <input type="hidden" name="items[{{ $i }}][id]" value="{{ $item['id'] ?? '' }}">
                            <input type="text" class="form-control form-control-sm" name="items[{{ $i }}][description]" value="{{ $item['description'] ?? '' }}" placeholder="{{ __('Description') }}" required>
                        </td>
                        <td>
                            <select class="form-select form-select-sm" name="items[{{ $i }}][treatment_id]">
                                <option value="">{{ __('None') }}</option>
                                @foreach($treatments as $treatment)
                                    <option value="{{ $treatment->id }}" data-price="{{ $treatment->default_price }}"
                                        {{ ($item['treatment_id'] ?? '') == $treatment->id ? 'selected' : '' }}>
                                        {{ $treatment->name }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="number" min="1" class="form-control form-control-sm qty" name="items[{{ $i }}][quantity]" value="{{ $item['quantity'] ?? 1 }}" required>
                        </td>
                        <td>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" min="0" class="form-control price" name="items[{{ $i }}][unit_price]" value="{{ $item['unit_price'] ?? '' }}" required>
                            </div>
                        </td>
                        <td>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" min="0" class="form-control discount" name="items[{{ $i }}][discount]" value="{{ $item['discount'] ?? 0 }}">
                            </div>
                        </td>
                        <td>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">$</span>
                                <input type="text" class="form-control line-total" readonly value="0.00">
                            </div>
                        </td>
                        <td class="align-middle text-center">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-row">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary" id="add_item_row">
            <i class="fas fa-plus me-1"></i>{{ __('Add Line Item') }}
        </button>
    </div>

    <!-- Totals -->
    <div class="col-12">
        <div class="row justify-content-end">
            <div class="col-md-5 col-lg-4">
                <table class="table table-sm">
                    <tr>
                        <td>{{ __('Subtotal') }}</td>
                        <td class="text-end"><strong id="subtotal_display">0.00</strong></td>
                    </tr>
                    <tr>
                        <td>{{ __('Tax') }} (<span id="tax_rate_display">0</span>%)</td>
                        <td class="text-end"><strong id="tax_display">0.00</strong></td>
                    </tr>
                    <tr>
                        <td>{{ __('Discount') }}</td>
                        <td class="text-end">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" min="0" class="form-control" id="discount_amount" name="discount_amount" value="{{ old('discount_amount', $aestheticInvoice->discount_amount ?? 0) }}">
                            </div>
                        </td>
                    </tr>
                    <tr class="table-primary">
                        <td><strong>{{ __('Total') }}</strong></td>
                        <td class="text-end"><strong id="total_display" class="fs-5">0.00</strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const tbody = document.getElementById('items_body');
    const addBtn = document.getElementById('add_item_row');
    const taxRateInput = document.getElementById('tax_rate');
    const discountInput = document.getElementById('discount_amount');

    const treatments = @json($treatments->map(fn($t) => ['id' => $t->id, 'name' => $t->name, 'price' => (float) $t->default_price]));

    function buildOptions() {
        return treatments.map(t => `<option value="${t.id}" data-price="${t.price}">${t.name}</option>`).join('');
    }

    let rowCount = tbody.querySelectorAll('.item-row').length;

    function addRow() {
        const idx = rowCount++;
        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.innerHTML = `
            <td>
                <input type="text" class="form-control form-control-sm" name="items[${idx}][description]" placeholder="{{ __('Description') }}" required>
            </td>
            <td>
                <select class="form-select form-select-sm treatment-select" name="items[${idx}][treatment_id]">
                    <option value="">{{ __('None') }}</option>
                    ${buildOptions()}
                </select>
            </td>
            <td>
                <input type="number" min="1" class="form-control form-control-sm qty" name="items[${idx}][quantity]" value="1" required>
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" min="0" class="form-control price" name="items[${idx}][unit_price]" value="0" required>
                </div>
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" min="0" class="form-control discount" name="items[${idx}][discount]" value="0">
                </div>
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <span class="input-group-text">$</span>
                    <input type="text" class="form-control line-total" readonly value="0.00">
                </div>
            </td>
            <td class="align-middle text-center">
                <button type="button" class="btn btn-sm btn-outline-danger remove-row">
                    <i class="fas fa-trash"></i>
                </button>
            </td>`;
        tbody.appendChild(tr);
        bindRow(tr);
        recalc();
    }

    function bindRow(row) {
        row.querySelector('.remove-row').addEventListener('click', function () {
            if (tbody.querySelectorAll('.item-row').length > 1) {
                row.remove();
                recalc();
            }
        });

        const treatmentSelect = row.querySelector('.treatment-select, select[name*="[treatment_id]"]');
        if (treatmentSelect) {
            treatmentSelect.addEventListener('change', function () {
                const opt = this.options[this.selectedIndex];
                const price = parseFloat(opt.dataset.price || 0);
                if (price > 0) {
                    row.querySelector('.price').value = price.toFixed(2);
                }
                recalc();
            });
        }

        ['input', 'change'].forEach(evt => {
            row.querySelector('.qty')?.addEventListener(evt, recalc);
            row.querySelector('.price')?.addEventListener(evt, recalc);
            row.querySelector('.discount')?.addEventListener(evt, recalc);
        });
    }

    function recalc() {
        let subtotal = 0;
        tbody.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.qty').value) || 0;
            const price = parseFloat(row.querySelector('.price').value) || 0;
            const disc = parseFloat(row.querySelector('.discount').value) || 0;
            const total = Math.max(0, (qty * price) - disc);
            row.querySelector('.line-total').value = total.toFixed(2);
            subtotal += total;
        });

        const taxRate = parseFloat(taxRateInput?.value) || 0;
        const tax = (subtotal * taxRate) / 100;
        const discount = parseFloat(discountInput?.value) || 0;
        const grandTotal = Math.max(0, subtotal + tax - discount);

        document.getElementById('subtotal_display').textContent = subtotal.toFixed(2);
        document.getElementById('tax_rate_display').textContent = taxRate.toFixed(2);
        document.getElementById('tax_display').textContent = tax.toFixed(2);
        document.getElementById('total_display').textContent = grandTotal.toFixed(2);
    }

    addBtn?.addEventListener('click', addRow);
    taxRateInput?.addEventListener('input', recalc);
    discountInput?.addEventListener('input', recalc);

    tbody.querySelectorAll('.item-row').forEach(bindRow);
    recalc();
})();
</script>
