<div class="row g-3">
    <!-- Product Name -->
    <div class="col-md-6">
        <label for="product_name" class="form-label">{{ __('Product Name') }} <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('product_name') is-invalid @enderror"
               id="product_name" name="product_name" value="{{ old('product_name', $aestheticInventory->product_name ?? '') }}"
               placeholder="{{ __('e.g., Hyaluronic Acid Filler') }}" required>
        @error('product_name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Type -->
    <div class="col-md-6">
        <label for="type_select" class="form-label">{{ __('Type') }} <span class="text-danger">*</span></label>
        <select class="form-select @error('type') is-invalid @enderror" id="type_select" required>
            <option value="">{{ __('Select Type') }}</option>
            @foreach($existingTypes ?? \App\Models\AestheticInventory::TYPES as $key => $label)
                <option value="{{ $key }}" {{ old('type', $aestheticInventory->type ?? 'consumable') == $key ? 'selected' : '' }}>
                    {{ __($label) }}
                </option>
            @endforeach
            <option value="__other__">{{ __('+ Add New Type') }}</option>
        </select>
        <input type="text" class="form-control mt-2 @error('type') is-invalid @enderror"
               id="type_text" name="type"
               value="{{ old('type', $aestheticInventory->type ?? '') }}"
               placeholder="{{ __('Enter new type name') }}" style="display:none;">
        @error('type')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Purchased Quantity -->
    <div class="col-md-4">
        <label for="purchased_quantity" class="form-label">{{ __('Initial Purchased Stock') }} <span class="text-danger">*</span></label>
        <input type="number" min="0" step="1"
               class="form-control @error('purchased_quantity') is-invalid @enderror"
               id="purchased_quantity" name="purchased_quantity"
               value="{{ old('purchased_quantity', $aestheticInventory->purchased_quantity ?? 0) }}"
               required>
        @error('purchased_quantity')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Bonus Quantity -->
    <div class="col-md-4">
        <label for="bonus_quantity" class="form-label">{{ __('Initial Bonus Stock') }} <span class="text-danger">*</span></label>
        <input type="number" min="0" step="1"
               class="form-control @error('bonus_quantity') is-invalid @enderror"
               id="bonus_quantity" name="bonus_quantity"
               value="{{ old('bonus_quantity', $aestheticInventory->bonus_quantity ?? 0) }}"
               required>
        @error('bonus_quantity')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="form-text text-muted">{{ __('Free/bonus goods from supplier, tracked separately from purchased stock.') }}</small>
    </div>

    <!-- Low Stock Threshold -->
    <div class="col-md-4">
        <label for="low_stock_threshold" class="form-label">{{ __('Low Stock Alert') }} <span class="text-danger">*</span></label>
        <input type="number" min="1" step="1"
               class="form-control @error('low_stock_threshold') is-invalid @enderror"
               id="low_stock_threshold" name="low_stock_threshold"
               value="{{ old('low_stock_threshold', $aestheticInventory->low_stock_threshold ?? 10) }}"
               required>
        @error('low_stock_threshold')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="form-text text-muted">{{ __('Alert when stock falls below this number') }}</small>
    </div>

    <!-- Expiry Date -->
    <div class="col-md-4">
        <label for="expiry_date" class="form-label">{{ __('Expiry Date') }}</label>
        <input type="date" class="form-control @error('expiry_date') is-invalid @enderror"
               id="expiry_date" name="expiry_date"
               value="{{ old('expiry_date', isset($aestheticInventory) && $aestheticInventory->expiry_date ? $aestheticInventory->expiry_date->format('Y-m-d') : '') }}">
        @error('expiry_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="form-text text-muted">{{ __('Optional. Leave blank for items with no expiry.') }}</small>
    </div>

    <!-- Purchase Price -->
    <div class="col-md-6">
        <label for="purchase_price" class="form-label">{{ __('Purchase Price') }} <span class="text-danger">*</span></label>
        <input type="number" min="0" step="0.01"
               class="form-control @error('purchase_price') is-invalid @enderror"
               id="purchase_price" name="purchase_price"
               value="{{ old('purchase_price', $aestheticInventory->purchase_price ?? 0) }}"
               required>
        @error('purchase_price')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="form-text text-muted">{{ __('Cost price paid to acquire this item.') }}</small>
    </div>

    <!-- Selling Price -->
    <div class="col-md-6">
        <label for="selling_price" class="form-label">{{ __('Selling Price') }} <span class="text-danger">*</span></label>
        <input type="number" min="0" step="0.01"
               class="form-control @error('selling_price') is-invalid @enderror"
               id="selling_price" name="selling_price"
               value="{{ old('selling_price', $aestheticInventory->selling_price ?? 0) }}"
               required>
        @error('selling_price')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="form-text text-muted">{{ __('Price charged to patients when this item is used/sold.') }}</small>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const typeSelect = document.getElementById('type_select');
    const typeText = document.getElementById('type_text');

    if (typeSelect && typeText) {
        function toggleTypeInput() {
            const isOther = typeSelect.value === '__other__';
            typeText.style.display = isOther ? 'block' : 'none';
            typeText.required = isOther;
            if (!isOther) {
                typeText.value = typeSelect.value;
            }
        }

        typeSelect.addEventListener('change', toggleTypeInput);

        // On load: if stored type is not in options, switch to Other
        const currentType = typeText.value;
        const optionExists = Array.from(typeSelect.options).some(opt => opt.value === currentType && opt.value !== '' && opt.value !== '__other__');
        if (currentType && !optionExists) {
            typeSelect.value = '__other__';
        }
        toggleTypeInput();
    }
})();
</script>
@endpush
