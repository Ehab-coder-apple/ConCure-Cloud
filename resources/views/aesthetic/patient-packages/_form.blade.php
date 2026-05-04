<div class="row g-3">
    <!-- Patient -->
    <div class="col-md-6">
        <label for="patient_id" class="form-label">{{ __('Patient') }} <span class="text-danger">*</span></label>
        <select class="form-select @error('patient_id') is-invalid @enderror" id="patient_id" name="patient_id" required>
            <option value="">{{ __('Select Patient') }}</option>
            @foreach($patients as $patient)
                <option value="{{ $patient->id }}" {{ old('patient_id', $patientPackage->patient_id ?? '') == $patient->id ? 'selected' : '' }}>
                    {{ $patient->first_name }} {{ $patient->last_name }}
                    @if($patient->phone) ({{ $patient->phone }}) @endif
                </option>
            @endforeach
        </select>
        @error('patient_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Package -->
    <div class="col-md-6">
        <label for="package_id" class="form-label">{{ __('Package') }} <span class="text-danger">*</span></label>
        <select class="form-select @error('package_id') is-invalid @enderror" id="package_id" name="package_id" required>
            <option value="">{{ __('Select Package') }}</option>
            @foreach($aestheticPackages as $pkg)
                <option value="{{ $pkg->id }}" data-sessions="{{ $pkg->total_sessions }}"
                    {{ old('package_id', $patientPackage->package_id ?? '') == $pkg->id ? 'selected' : '' }}>
                    {{ $pkg->name }} ({{ $pkg->total_sessions }} {{ __('sessions') }} - {{ number_format($pkg->final_price, 2) }})
                </option>
            @endforeach
        </select>
        @error('package_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Purchase Date -->
    <div class="col-md-6">
        <label for="purchase_date" class="form-label">{{ __('Purchase Date') }} <span class="text-danger">*</span></label>
        <input type="date" class="form-control @error('purchase_date') is-invalid @enderror"
               id="purchase_date" name="purchase_date"
               value="{{ old('purchase_date', isset($patientPackage) && $patientPackage->purchase_date ? $patientPackage->purchase_date->format('Y-m-d') : now()->format('Y-m-d')) }}"
               required>
        @error('purchase_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Sessions Used (edit only) -->
    @if(isset($patientPackage))
    <div class="col-md-3">
        <label for="sessions_used" class="form-label">{{ __('Sessions Used') }}</label>
        <input type="number" min="0" step="1"
               class="form-control @error('sessions_used') is-invalid @enderror"
               id="sessions_used" name="sessions_used"
               value="{{ old('sessions_used', $patientPackage->sessions_used) }}">
        @error('sessions_used')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3">
        <label for="sessions_remaining" class="form-label">{{ __('Sessions Remaining') }}</label>
        <input type="number" min="0" step="1"
               class="form-control @error('sessions_remaining') is-invalid @enderror"
               id="sessions_remaining" name="sessions_remaining"
               value="{{ old('sessions_remaining', $patientPackage->sessions_remaining) }}">
        @error('sessions_remaining')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    @endif
</div>
