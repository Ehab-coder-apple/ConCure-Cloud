<div class="row g-3">
    <!-- Package Name -->
    <div class="col-md-6">
        <label for="name" class="form-label">{{ __('Package Name') }} <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('name') is-invalid @enderror"
               id="name" name="name" value="{{ old('name', $aestheticPackage->name ?? '') }}"
               placeholder="{{ __('e.g., Laser Hair Removal - 6 Sessions') }}" required>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Treatments -->
    <div class="col-md-6">
        <label class="form-label">{{ __('Treatments') }} <span class="text-danger">*</span></label>
        @php
            $selected = old('treatment_ids', $selectedTreatmentIds ?? [($aestheticPackage->treatment_id ?? '')]);
            if (!is_array($selected)) { $selected = [$selected]; }
            $selected = array_filter($selected);
        @endphp

        <div class="dropdown w-100" id="treatmentDropdown">
            <button class="btn btn-outline-secondary dropdown-toggle w-100 text-start" type="button"
                    data-bs-toggle="dropdown" aria-expanded="false" id="treatmentDropdownBtn">
                <span id="treatmentCount">{{ count($selected) }}</span> {{ __('treatment(s) selected') }}
            </button>
            <div class="dropdown-menu w-100 p-2" style="max-height: 320px; overflow-y: auto;">
                <input type="text" class="form-control form-control-sm mb-2" id="treatmentSearch"
                       placeholder="{{ __('Search treatments...') }}" autocomplete="off">
                <div id="treatmentList">
                    @foreach($treatments as $treatment)
                        <div class="form-check treatment-item" data-name="{{ strtolower($treatment->name) }} {{ strtolower($treatment->category_display) }}">
                            <input class="form-check-input treatment-checkbox" type="checkbox"
                                   name="treatment_ids[]"
                                   id="treatment_{{ $treatment->id }}"
                                   value="{{ $treatment->id }}"
                                   {{ in_array($treatment->id, $selected) ? 'checked' : '' }}>
                            <label class="form-check-label" for="treatment_{{ $treatment->id }}">
                                {{ $treatment->name }}
                                <small class="text-muted">({{ $treatment->category_display }})</small>
                            </label>
                        </div>
                    @endforeach
                </div>
                @if($treatments->count() === 0)
                    <div class="text-muted small text-center py-2">{{ __('No treatments available') }}</div>
                @endif
            </div>
        </div>

        <!-- Selected badges (live preview) -->
        <div id="selectedTreatmentsPreview" class="mt-2 d-flex flex-wrap gap-1">
            @foreach($treatments as $treatment)
                @if(in_array($treatment->id, $selected))
                    <span class="badge bg-primary treatment-badge-{{ $treatment->id }}">
                        {{ $treatment->name }}
                    </span>
                @endif
            @endforeach
        </div>

        @error('treatment_ids')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
        @error('treatment_ids.*')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <!-- Total Sessions -->
    <div class="col-md-4">
        <label for="total_sessions" class="form-label">{{ __('Total Sessions') }} <span class="text-danger">*</span></label>
        <input type="number" min="1" step="1"
               class="form-control @error('total_sessions') is-invalid @enderror"
               id="total_sessions" name="total_sessions"
               value="{{ old('total_sessions', $aestheticPackage->total_sessions ?? '') }}"
               placeholder="{{ __('e.g., 6') }}" required>
        @error('total_sessions')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Price -->
    <div class="col-md-4">
        <label for="price" class="form-label">{{ __('Package Price') }} <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text">$</span>
            <input type="number" step="0.01" min="0"
                   class="form-control @error('price') is-invalid @enderror"
                   id="price" name="price"
                   value="{{ old('price', $aestheticPackage->price ?? '') }}"
                   placeholder="0.00" required>
        </div>
        @error('price')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Discount -->
    <div class="col-md-4">
        <label for="discount" class="form-label">{{ __('Discount') }}</label>
        <div class="input-group">
            <span class="input-group-text">$</span>
            <input type="number" step="0.01" min="0"
                   class="form-control @error('discount') is-invalid @enderror"
                   id="discount" name="discount"
                   value="{{ old('discount', $aestheticPackage->discount ?? '') }}"
                   placeholder="0.00">
        </div>
        @error('discount')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="form-text text-muted">{{ __('Optional discount applied to the package price.') }}</small>
    </div>

    <!-- Expiry Date -->
    <div class="col-md-6">
        <label for="expiry_date" class="form-label">{{ __('Expiry Date') }}</label>
        <input type="date" class="form-control @error('expiry_date') is-invalid @enderror"
               id="expiry_date" name="expiry_date"
               value="{{ old('expiry_date', isset($aestheticPackage) && $aestheticPackage->expiry_date ? $aestheticPackage->expiry_date->format('Y-m-d') : '') }}">
        @error('expiry_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="form-text text-muted">{{ __('Leave blank for packages with no expiration.') }}</small>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const searchInput = document.getElementById('treatmentSearch');
    const items = document.querySelectorAll('.treatment-item');
    const checkboxes = document.querySelectorAll('.treatment-checkbox');
    const countSpan = document.getElementById('treatmentCount');
    const preview = document.getElementById('selectedTreatmentsPreview');

    function updatePreview() {
        const checked = Array.from(checkboxes).filter(cb => cb.checked);
        countSpan.textContent = checked.length;

        preview.innerHTML = '';
        checked.forEach(cb => {
            const label = cb.closest('.treatment-item').querySelector('label');
            const name = label.childNodes[0].textContent.trim();
            const badge = document.createElement('span');
            badge.className = 'badge bg-primary';
            badge.textContent = name;
            preview.appendChild(badge);
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const term = this.value.toLowerCase().trim();
            items.forEach(item => {
                const name = item.dataset.name;
                item.style.display = name.includes(term) ? '' : 'none';
            });
        });

        // Prevent dropdown from closing when clicking inside it
        document.getElementById('treatmentDropdown')?.querySelector('.dropdown-menu')
            ?.addEventListener('click', function (e) {
                e.stopPropagation();
            });
    }

    checkboxes.forEach(cb => cb.addEventListener('change', updatePreview));
    updatePreview();
})();
</script>
@endpush
