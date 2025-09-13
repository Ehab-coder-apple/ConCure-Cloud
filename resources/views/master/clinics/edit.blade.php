@extends('master.layouts.app')

@section('title', 'Edit Clinic')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-edit me-2"></i>
                        Edit Clinic
                    </h1>
                    <p class="text-muted mb-0">Update clinic details and limits</p>
                </div>
                <div>
                    <a href="{{ route('master.clinics.show', $clinic) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Back to Clinic
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Clinic Information -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-hospital me-2"></i>
                        Clinic Information
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('master.clinics.update', $clinic) }}">
                        @csrf
                        @method('PATCH')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Clinic Name *</label>
                                <input type="text"
                                       class="form-control @error('name') is-invalid @enderror"
                                       id="name"
                                       name="name"
                                       value="{{ old('name', $clinic->name) }}"
                                       required
                                       placeholder="Enter clinic name">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Clinic Email *</label>
                                <input type="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       id="email"
                                       name="email"
                                       value="{{ old('email', $clinic->email) }}"
                                       required
                                       placeholder="clinic@example.com">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="text"
                                       class="form-control @error('phone') is-invalid @enderror"
                                       id="phone"
                                       name="phone"
                                       value="{{ old('phone', $clinic->phone) }}"
                                       placeholder="+1 (555) 123-4567">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="max_users" class="form-label">Maximum Users *</label>
                                <input type="number"
                                       class="form-control @error('max_users') is-invalid @enderror"
                                       id="max_users"
                                       name="max_users"
                                       value="{{ old('max_users', $clinic->max_users) }}"
                                       min="1"
                                       max="1000"
                                       required>
                                @error('max_users')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Maximum number of users allowed for this clinic</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control @error('address') is-invalid @enderror"
                                      id="address"
                                      name="address"
                                      rows="3"
                                      placeholder="Enter clinic address">{{ old('address', $clinic->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> Save Changes
                            </button>
                            <a href="{{ route('master.clinics.show', $clinic) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Admin Tools -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user-shield me-2"></i>
                        Admin Tools
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3 text-muted small">
                        Reset the clinic admin's password.
                    </div>
                    <form method="POST" action="{{ route('master.clinics.reset-admin-password', $clinic) }}">
                        @csrf
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password *</label>
                            <input type="password"
                                   class="form-control @error('new_password') is-invalid @enderror"
                                   id="new_password" name="new_password" required minlength="8">
                            @error('new_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="new_password_confirmation" class="form-label">Confirm Password *</label>
                            <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required minlength="8">
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-key me-2"></i>
                                Reset Admin Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

