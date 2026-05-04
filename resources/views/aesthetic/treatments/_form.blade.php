<div class="row g-3">
    <!-- Treatment Name -->
    <div class="col-md-6">
        <label for="name" class="form-label">{{ __('Treatment Name') }} <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('name') is-invalid @enderror"
               id="name" name="name" value="{{ old('name', $aestheticTreatment->name ?? '') }}"
               placeholder="{{ __('e.g., CO2 Laser Resurfacing') }}" required>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Category -->
    <div class="col-md-6">
        <label for="category" class="form-label">{{ __('Category') }} <span class="text-danger">*</span></label>
        <select class="form-select @error('category') is-invalid @enderror" id="category_select" required>
            <option value="">{{ __('Select Category') }}</option>
            @foreach($existingCategories ?? \App\Models\AestheticTreatment::CATEGORIES as $key => $label)
                <option value="{{ $key }}" {{ old('category', $aestheticTreatment->category ?? '') == $key ? 'selected' : '' }}>
                    {{ __($label) }}
                </option>
            @endforeach
            <option value="__other__">{{ __('+ Add New Category') }}</option>
        </select>
        <input type="text" class="form-control mt-2 @error('category') is-invalid @enderror"
               id="category_text" name="category"
               value="{{ old('category', $aestheticTreatment->category ?? '') }}"
               placeholder="{{ __('Enter new category name') }}" style="display:none;">
        @error('category')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Default Price -->
    <div class="col-md-6">
        <label for="default_price" class="form-label">{{ __('Default Price') }} <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text">$</span>
            <input type="number" step="0.01" min="0"
                   class="form-control @error('default_price') is-invalid @enderror"
                   id="default_price" name="default_price"
                   value="{{ old('default_price', $aestheticTreatment->default_price ?? '') }}"
                   placeholder="0.00" required>
        </div>
        @error('default_price')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Session Required -->
    <div class="col-md-6 d-flex align-items-center">
        <div class="form-check mt-4">
            <input class="form-check-input @error('session_required') is-invalid @enderror"
                   type="checkbox" id="session_required" name="session_required" value="1"
                   {{ old('session_required', $aestheticTreatment->session_required ?? false) ? 'checked' : '' }}>
            <label class="form-check-label" for="session_required">
                {{ __('Session Required') }}
            </label>
            @error('session_required')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <!-- Sessions Count (conditional) -->
    <div class="col-md-6 {{ old('session_required', $aestheticTreatment->session_required ?? false) ? '' : 'd-none' }}" id="sessions_count_wrap">
        <label for="sessions_count" class="form-label">{{ __('Sessions Count') }} <span class="text-danger" id="sessions_required_mark">*</span></label>
        <input type="number" min="1"
               class="form-control @error('sessions_count') is-invalid @enderror"
               id="sessions_count" name="sessions_count"
               value="{{ old('sessions_count', $aestheticTreatment->sessions_count ?? '') }}"
               placeholder="{{ __('e.g., 3') }}">
        @error('sessions_count')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Description -->
    <div class="col-12">
        <label for="description" class="form-label">{{ __('Description') }}</label>
        <textarea class="form-control @error('description') is-invalid @enderror"
                  id="description" name="description" rows="3"
                  placeholder="{{ __('Brief description of the treatment...') }}">{{ old('description', $aestheticTreatment->description ?? '') }}</textarea>
        @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Active Status -->
    <div class="col-12">
        <div class="form-check">
            <input class="form-check-input @error('is_active') is-invalid @enderror"
                   type="checkbox" id="is_active" name="is_active" value="1"
                   {{ old('is_active', $aestheticTreatment->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">
                {{ __('Active') }}
            </label>
            <small class="form-text text-muted d-block">
                {{ __('Only active treatments are available for selection.') }}
            </small>
            @error('is_active')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    // Session count toggle
    const sessionCheckbox = document.getElementById('session_required');
    const sessionsWrap = document.getElementById('sessions_count_wrap');
    const sessionsInput = document.getElementById('sessions_count');
    const sessionsMark = document.getElementById('sessions_required_mark');

    if (sessionCheckbox && sessionsWrap) {
        function toggleSessionsCount() {
            const isChecked = sessionCheckbox.checked;
            sessionsWrap.classList.toggle('d-none', !isChecked);
            if (sessionsMark) {
                sessionsMark.style.display = isChecked ? 'inline' : 'none';
            }
            if (!isChecked && sessionsInput) {
                sessionsInput.value = '';
            }
        }

        sessionCheckbox.addEventListener('change', toggleSessionsCount);
        toggleSessionsCount();
    }

    // Category "Other" toggle
    const categorySelect = document.getElementById('category_select');
    const categoryText = document.getElementById('category_text');

    if (categorySelect && categoryText) {
        function toggleCategoryInput() {
            const isOther = categorySelect.value === '__other__';
            categoryText.style.display = isOther ? 'block' : 'none';
            categoryText.required = isOther;
            if (!isOther) {
                categoryText.value = categorySelect.value;
            }
        }

        categorySelect.addEventListener('change', toggleCategoryInput);

        // On load: if the stored category is not in the select options, switch to Other
        const currentCategory = categoryText.value;
        const optionExists = Array.from(categorySelect.options).some(opt => opt.value === currentCategory && opt.value !== '' && opt.value !== '__other__');
        if (currentCategory && !optionExists) {
            categorySelect.value = '__other__';
        }
        toggleCategoryInput();
    }
})();
</script>
@endpush
