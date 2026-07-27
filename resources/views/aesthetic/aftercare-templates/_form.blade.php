<div class="row g-3">
    <div class="col-md-6">
        <label for="name" class="form-label">{{ __('Template Name') }} <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
               value="{{ old('name', $aestheticAftercareTemplate->name ?? '') }}" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="title" class="form-label">{{ __('PDF Title') }} <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title"
               value="{{ old('title', $aestheticAftercareTemplate->title ?? '') }}" required>
        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="category" class="form-label">{{ __('Treatment Category') }} <span class="text-danger">*</span></label>
        <select class="form-select @error('category') is-invalid @enderror" id="category" name="category" required>
            <option value="">{{ __('Select Category') }}</option>
            @foreach($categories as $key => $label)
                <option value="{{ $key }}" {{ old('category', $aestheticAftercareTemplate->category ?? '') === $key ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6 d-flex align-items-end">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                   {{ old('is_active', $aestheticAftercareTemplate->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">{{ __('Active and available for issuing') }}</label>
        </div>
    </div>

    <div class="col-12">
        <label for="instructions" class="form-label">{{ __('Instructions') }} <span class="text-danger">*</span></label>
        <textarea class="form-control @error('instructions') is-invalid @enderror" id="instructions" name="instructions" rows="10" required>{{ old('instructions', $aestheticAftercareTemplate->instructions ?? '') }}</textarea>
        <small class="form-text text-muted">{{ __('These instructions are snapshotted into the issued PDF at the time of issuing.') }}</small>
        @error('instructions')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>