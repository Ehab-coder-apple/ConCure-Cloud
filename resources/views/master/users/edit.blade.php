@extends('master.layouts.app')

@section('title', 'Edit Super Admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="fas fa-user-edit text-primary me-2"></i>
            Edit Super Admin: {{ $user->first_name }} {{ $user->last_name }}
        </h1>
        <div class="d-flex gap-2">
            <a href="{{ route('master.users.show', $user) }}" class="btn btn-outline-info">
                <i class="fas fa-eye me-1"></i>
                View User
            </a>
            <a href="{{ route('master.users.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Back to Users
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-user-circle me-2"></i>
                        Super Admin Information
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('master.users.update', $user) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Personal Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2 mb-3">
                                    <i class="fas fa-user me-2"></i>
                                    Personal Information
                                </h6>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                           id="first_name" name="first_name" value="{{ old('first_name', $user->first_name) }}" required>
                                    @error('first_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('last_name') is-invalid @enderror" 
                                           id="last_name" name="last_name" value="{{ old('last_name', $user->last_name) }}" required>
                                    @error('last_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('username') is-invalid @enderror" 
                                           id="username" name="username" value="{{ old('username', $user->username) }}" required>
                                    @error('username')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="language" class="form-label">Language <span class="text-danger">*</span></label>
                                    <select class="form-select @error('language') is-invalid @enderror" id="language" name="language" required>
                                        <option value="en" {{ old('language', $user->language) == 'en' ? 'selected' : '' }}>English</option>
                                        <option value="ar" {{ old('language', $user->language) == 'ar' ? 'selected' : '' }}>العربية</option>
                                        <option value="ku" {{ old('language', $user->language) == 'ku' ? 'selected' : '' }}>کوردی</option>
                                    </select>
                                    @error('language')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="scientific_degree" class="form-label">{{ __('Scientific Degree / Educational Level') }}</label>
                                    <input type="text"
                                           class="form-control @error('scientific_degree') is-invalid @enderror"
                                           id="scientific_degree"
                                           name="scientific_degree"
                                           list="degree_options_edit"
                                           value="{{ old('scientific_degree', $user->scientific_degree ?? '') }}"
                                           placeholder="{{ __('Select or type degree (Optional)') }}">
                                    <datalist id="degree_options_edit">
                                        @foreach(\App\Models\User::getAvailableScientificDegrees() as $degree)
                                            <option value="{{ $degree }}">
                                        @endforeach
                                    </datalist>
                                    <div class="form-text">{{ __('Select from the list or type your own educational qualification (e.g., Consultant, Master, PhD, etc.)') }}</div>
                                    @error('scientific_degree')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="educational_institution" class="form-label">{{ __('Educational Institution') }}</label>
                                    <input type="text"
                                           class="form-control @error('educational_institution') is-invalid @enderror"
                                           id="educational_institution"
                                           name="educational_institution"
                                           value="{{ old('educational_institution', $user->educational_institution ?? '') }}"
                                           placeholder="{{ __('e.g., University of Baghdad, Harvard Medical School, etc.') }}">
                                    <div class="form-text">{{ __('The institution where the degree was obtained (optional)') }}</div>
                                    @error('educational_institution')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Account Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2 mb-3">
                                    <i class="fas fa-key me-2"></i>
                                    Account Information
                                </h6>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">New Password <small class="text-muted">(leave blank to keep current)</small></label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                           id="password" name="password">
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" 
                                           id="password_confirmation" name="password_confirmation">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                                    <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                                        <option value="">Select Role</option>
                                        @foreach($availableRoles as $role)
                                            <option value="{{ $role }}" {{ old('role', $user->role) == $role ? 'selected' : '' }}>
                                                {{ \App\Models\User::ROLES[$role] ?? __(ucfirst(str_replace('_', ' ', $role))) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('role')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                           {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Active User
                                    </label>
                                </div>
                            </div>

                            <div class="col-12 mt-3">
                                <div class="mb-3">
                                    <label for="clinic_ids" class="form-label">Allocated Clinics <span class="text-danger">*</span></label>
                                    @php($selectedClinicIds = old('clinic_ids', $user->superAdminClinics->pluck('id')->all()))
                                    <select class="form-select @error('clinic_ids') is-invalid @enderror @error('clinic_ids.*') is-invalid @enderror"
                                            id="clinic_ids"
                                            name="clinic_ids[]"
                                            multiple
                                            size="8"
                                            required>
                                        @foreach($clinics as $clinic)
                                            <option value="{{ $clinic->id }}" {{ in_array($clinic->id, $selectedClinicIds) ? 'selected' : '' }}>
                                                {{ $clinic->name }}@if($clinic->city) — {{ $clinic->city }}@endif{{ !$clinic->is_active ? ' (Inactive)' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text">Hold Ctrl/Cmd to select multiple clinics assigned to this Super Admin.</div>
                                    @error('clinic_ids')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    @error('clinic_ids.*')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Permissions -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2 mb-3">
                                    <i class="fas fa-shield-alt me-2"></i>
                                    Reference Permissions
                                </h6>
                                <p class="text-muted small mb-3">Scoped Super Admins use master-layer authority, but their data visibility is limited to the clinics selected above.</p>
                            </div>

                            <div class="col-12">
                                <div class="row">
                                    @foreach($masterPermissions as $permission => $label)
                                        <div class="col-md-6 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                       id="permission_{{ $permission }}"
                                                       name="permissions[]"
                                                       value="{{ $permission }}"
                                                       {{ in_array($permission, old('permissions', $user->permissions ?? [])) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="permission_{{ $permission }}">
                                                    {{ $label }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('master.users.show', $user) }}" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i>
                                        Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>
                                        Update Super Admin
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
