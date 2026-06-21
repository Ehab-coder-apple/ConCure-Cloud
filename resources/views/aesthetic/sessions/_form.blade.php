@php
    $initialSessionMode = old('session_mode', isset($aestheticSession)
        ? ($aestheticSession->isPackageSession ? 'package' : 'direct')
        : ($defaultSessionMode ?? 'package'));
    $isPackage = $initialSessionMode === 'package';
@endphp

<!-- Session Mode Toggle -->
<div class="col-12 mb-3">
    <label class="form-label d-block">{{ __('Session Type') }}</label>
    <div class="btn-group" role="group">
        <input type="radio" class="btn-check" name="session_mode" id="mode_package" value="package" {{ $isPackage ? 'checked' : '' }}>
        <label class="btn btn-outline-primary" for="mode_package">
            <i class="fas fa-box me-1"></i>{{ __('Package Session') }}
        </label>
        <input type="radio" class="btn-check" name="session_mode" id="mode_direct" value="direct" {{ !$isPackage ? 'checked' : '' }}>
        <label class="btn btn-outline-primary" for="mode_direct">
            <i class="fas fa-stethoscope me-1"></i>{{ __('Direct Treatment') }}
        </label>
    </div>
</div>

<div class="row g-3" id="package-fields" style="display: {{ $isPackage ? 'flex' : 'none' }};">
    <!-- Patient Package -->
    <div class="col-md-6">
        <div class="d-flex justify-content-between align-items-center gap-2 mb-1">
            <label for="patient_package_id" class="form-label mb-0">{{ __('Patient Package') }} <span class="text-danger">*</span></label>
            <a href="{{ route('patients.create', ['return_to' => route('aesthetic.sessions.create', ['session_mode' => 'package'], false)]) }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-user-plus me-1"></i>{{ __('Create New Patient') }}
            </a>
        </div>
        <select class="form-select @error('patient_package_id') is-invalid @enderror"
                id="patient_package_id" name="patient_package_id"
                onchange="window.location.href='?patient_package_id='+this.value">
            <option value="">{{ __('Select Patient Package') }}</option>
            @foreach($patientPackages as $pp)
                <option value="{{ $pp->id }}"
                    {{ old('patient_package_id', $aestheticSession->patient_package_id ?? ($selectedPackageId ?? '')) == $pp->id ? 'selected' : '' }}>
                    {{ $pp->patient->first_name }} {{ $pp->patient->last_name }}
                    - {{ $pp->package->name }}
                </option>
            @endforeach
        </select>
        @error('patient_package_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="form-text text-muted">
            {{ __('After creating the patient, assign an aesthetic package, then return here to select it.') }}
        </small>
    </div>
</div>

<div class="row g-3" id="direct-fields" style="display: {{ !$isPackage ? 'flex' : 'none' }};">
    <!-- Patient (Direct) -->
    <div class="col-md-6">
        <div class="d-flex justify-content-between align-items-center gap-2 mb-1">
            <label for="patient_id" class="form-label mb-0">{{ __('Patient') }} <span class="text-danger">*</span></label>
            <a href="{{ route('patients.create', ['return_to' => route('aesthetic.sessions.create', ['session_mode' => 'direct'], false)]) }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-user-plus me-1"></i>{{ __('Create New Patient') }}
            </a>
        </div>
        <select class="form-select @error('patient_id') is-invalid @enderror" id="patient_id" name="patient_id">
            <option value="">{{ __('Select Patient') }}</option>
            @foreach($patients as $patient)
                <option value="{{ $patient->id }}"
                    {{ old('patient_id', $aestheticSession->patient_id ?? ($selectedPatientId ?? '')) == $patient->id ? 'selected' : '' }}>
                    {{ $patient->first_name }} {{ $patient->last_name }}
                    @if($patient->phone)<small class="text-muted">({{ $patient->phone }})</small>@endif
                </option>
            @endforeach
        </select>
        @error('patient_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Treatment (Direct) -->
    <div class="col-md-6">
        <label for="treatment_id" class="form-label">{{ __('Treatment') }}</label>
        <select class="form-select @error('treatment_id') is-invalid @enderror" id="treatment_id" name="treatment_id">
            <option value="">{{ __('Select Treatment (Optional)') }}</option>
            @foreach($treatments as $treatment)
                <option value="{{ $treatment->id }}"
                    {{ old('treatment_id', $aestheticSession->treatment_id ?? '') == $treatment->id ? 'selected' : '' }}>
                    {{ $treatment->name }}
                    <small class="text-muted">({{ $treatment->category_display }})</small>
                </option>
            @endforeach
        </select>
        @error('treatment_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="form-text text-muted">
            {{ __('Choose a treatment if you want the Create Invoice shortcut to auto-fill the invoice item and price.') }}
        </small>
    </div>
</div>

<div class="row g-3 mt-2">

    <!-- Session Number -->
    <div class="col-md-3">
        <label for="session_number" class="form-label">{{ __('Session Number') }} <span class="text-danger">*</span></label>
        <input type="number" min="1" step="1"
               class="form-control @error('session_number') is-invalid @enderror"
               id="session_number" name="session_number"
               value="{{ old('session_number', $aestheticSession->session_number ?? ($nextSessionNumber ?? 1)) }}"
               required>
        @error('session_number')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Session Date -->
    <div class="col-md-3">
        <label for="session_date" class="form-label">{{ __('Session Date') }} <span class="text-danger">*</span></label>
        <input type="date" class="form-control @error('session_date') is-invalid @enderror"
               id="session_date" name="session_date"
               value="{{ old('session_date', isset($aestheticSession) && $aestheticSession->session_date ? $aestheticSession->session_date->format('Y-m-d') : now()->format('Y-m-d')) }}"
               required>
        @error('session_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Status -->
    <div class="col-md-3">
        <label for="status" class="form-label">{{ __('Status') }} <span class="text-danger">*</span></label>
        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
            @foreach(\App\Models\AestheticSession::STATUSES as $key => $label)
                <option value="{{ $key }}" {{ old('status', $aestheticSession->status ?? 'scheduled') == $key ? 'selected' : '' }}>
                    {{ __($label) }}
                </option>
            @endforeach
        </select>
        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="form-text text-muted">
            {{ __('Sessions should remain Scheduled until patient consent has been signed on the session screen. Started and Completed statuses require a consent record.') }}
        </small>
    </div>

    <!-- Assigned Person -->
    <div class="col-md-3">
        <label for="assigned_user_id" class="form-label">{{ __('Assigned Person') }}</label>
        <select class="form-select @error('assigned_user_id') is-invalid @enderror" id="assigned_user_id" name="assigned_user_id">
            <option value="">{{ __('Select Assigned Person') }}</option>
            @foreach($assignableUsers as $assignableUser)
                <option value="{{ $assignableUser->id }}" {{ old('assigned_user_id', $aestheticSession->assigned_user_id ?? '') == $assignableUser->id ? 'selected' : '' }}>
                    {{ $assignableUser->full_name }}
                </option>
            @endforeach
        </select>
        @error('assigned_user_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="form-text text-muted">
            {{ __('Choose the person responsible for running this session. If you select someone here, any external practitioner name will be cleared.') }}
        </small>
    </div>

    <!-- External Practitioner Name -->
    <div class="col-md-3">
        <label for="external_practitioner_name" class="form-label">{{ __('External Practitioner Name') }}</label>
        <input type="text"
               class="form-control @error('external_practitioner_name') is-invalid @enderror"
               id="external_practitioner_name" name="external_practitioner_name"
               value="{{ old('external_practitioner_name', $aestheticSession->external_practitioner_name ?? '') }}">
        @error('external_practitioner_name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="form-text text-muted">
            {{ __('Use this when the practitioner is external and not listed in the Assigned Person dropdown. Typing a name here will clear any selected Assigned Person.') }}
        </small>
    </div>

    <!-- Notes -->
    <div class="col-12">
        <label for="notes" class="form-label">{{ __('Notes') }}</label>
        <textarea class="form-control @error('notes') is-invalid @enderror"
                  id="notes" name="notes" rows="3"
                  placeholder="{{ __('Session notes, observations, treatment details...') }}">{{ old('notes', $aestheticSession->notes ?? '') }}</textarea>
        @error('notes')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Inventory Usage -->
    @if(isset($inventoryItems) && $inventoryItems->count() > 0)
    <div class="col-12" id="inventory_section">
        <hr class="my-3">
        <h6 class="mb-3">
            <i class="fas fa-boxes me-2 text-primary"></i>
            {{ __('Inventory Used') }}
            <small class="text-muted">{{ __('(Stock is deducted immediately when saved)') }}</small>
        </h6>
        <div class="table-responsive">
            <table class="table table-sm table-bordered" id="inventory_table">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Product') }}</th>
                        <th>{{ __('Available') }}</th>
                        <th>{{ __('Quantity Used') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="inventory_rows">
                    @php
                        $selectedItems = old('inventory_items', []);
                        if (isset($aestheticSession) && $aestheticSession->inventoryUsages) {
                            $selectedItems = [];
                            foreach ($aestheticSession->inventoryUsages as $usage) {
                                $selectedItems[] = ['product_id' => $usage->product_id, 'quantity_used' => $usage->quantity_used];
                            }
                        }
                    @endphp
                    @if(count($selectedItems) > 0)
                        @foreach($selectedItems as $i => $item)
                        <tr class="inventory-row">
                            <td>
                                <select class="form-select form-select-sm" name="inventory_items[{{ $i }}][product_id]" required>
                                    <option value="">{{ __('Select Product') }}</option>
                                    @foreach($inventoryItems as $product)
                                        <option value="{{ $product->id }}" {{ ($item['product_id'] ?? '') == $product->id ? 'selected' : '' }}>
                                            {{ $product->product_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="text-muted">{{ $inventoryItems->firstWhere('id', $item['product_id'] ?? 0)?->quantity ?? '-' }}</td>
                            <td>
                                <input type="number" min="1" class="form-control form-control-sm" name="inventory_items[{{ $i }}][quantity_used]" value="{{ $item['quantity_used'] ?? '' }}" required>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.inventory-row').remove()">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    @else
                    <tr class="inventory-row">
                        <td>
                            <select class="form-select form-select-sm" name="inventory_items[0][product_id]">
                                <option value="">{{ __('Select Product') }}</option>
                                @foreach($inventoryItems as $product)
                                    <option value="{{ $product->id }}">{{ $product->product_name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="text-muted">-</td>
                        <td>
                            <input type="number" min="1" class="form-control form-control-sm" name="inventory_items[0][quantity_used]" placeholder="0">
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.inventory-row').remove()">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary" id="add_inventory_row">
            <i class="fas fa-plus me-1"></i>{{ __('Add Item') }}
        </button>
    </div>
    @endif
</div>

@if(isset($inventoryItems) && $inventoryItems->count() > 0)
<script>
(function () {
    let rowCount = document.querySelectorAll('.inventory-row').length;
    const products = @json($inventoryItems->map(fn($p) => ['id' => $p->id, 'name' => $p->product_name, 'quantity' => $p->quantity]));
    const tbody = document.getElementById('inventory_rows');
    const addBtn = document.getElementById('add_inventory_row');

    function buildRow(index) {
        const opts = products.map(p => `<option value="${p.id}">${p.name}</option>`).join('');
        return `
        <tr class="inventory-row">
            <td>
                <select class="form-select form-select-sm" name="inventory_items[${index}][product_id]">
                    <option value="">{{ __('Select Product') }}</option>
                    ${opts}
                </select>
            </td>
            <td class="text-muted">-</td>
            <td>
                <input type="number" min="1" class="form-control form-select-sm" name="inventory_items[${index}][quantity_used]" placeholder="0">
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.inventory-row').remove()">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>`;
    }

    if (addBtn) {
        addBtn.addEventListener('click', function () {
            tbody.insertAdjacentHTML('beforeend', buildRow(rowCount++));
        });
    }

    // Update available stock display on product change
    tbody.addEventListener('change', function (e) {
        if (e.target.tagName === 'SELECT') {
            const row = e.target.closest('.inventory-row');
            const productId = e.target.value;
            const product = products.find(p => String(p.id) === String(productId));
            const availCell = row.querySelectorAll('td')[1];
            availCell.textContent = product ? product.quantity : '-';
        }
    });
})();

// Toggle between package and direct treatment modes
(function () {
    const modePackage = document.getElementById('mode_package');
    const modeDirect = document.getElementById('mode_direct');
    const packageFields = document.getElementById('package-fields');
    const directFields = document.getElementById('direct-fields');

    function toggleMode() {
        const isPackage = modePackage && modePackage.checked;
        if (packageFields) {
            packageFields.style.display = isPackage ? 'flex' : 'none';
            const selects = packageFields.querySelectorAll('select');
            selects.forEach(s => s.required = isPackage);
        }
        if (directFields) {
            directFields.style.display = !isPackage ? 'flex' : 'none';
            const patientSelect = directFields.querySelector('#patient_id');
            if (patientSelect) patientSelect.required = !isPackage;
        }
    }

    if (modePackage) modePackage.addEventListener('change', toggleMode);
    if (modeDirect) modeDirect.addEventListener('change', toggleMode);
    toggleMode();
})();

	// Keep internal Assigned Person and External Practitioner mutually exclusive
	(function () {
	    const assignedSelect = document.getElementById('assigned_user_id');
	    const externalInput = document.getElementById('external_practitioner_name');

	    if (!assignedSelect || !externalInput) return;

	    function handleExternalChange() {
	        if (externalInput.value.trim() !== '') {
	            if (assignedSelect.value !== '') {
	                assignedSelect.value = '';
	            }
	        }
	    }

	    function handleAssignedChange() {
	        if (assignedSelect.value !== '') {
	            if (externalInput.value.trim() !== '') {
	                externalInput.value = '';
	            }
	        }
	    }

	    externalInput.addEventListener('input', handleExternalChange);
	    assignedSelect.addEventListener('change', handleAssignedChange);

	    // Normalize state on load if both somehow have values (e.g. after back/forward navigation)
	    handleExternalChange();
	    handleAssignedChange();
	})();
</script>
@endif
