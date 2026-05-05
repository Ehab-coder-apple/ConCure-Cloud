@extends('layouts.app')

@php
    $patientName = $aestheticSession->isPackageSession
        ? ($aestheticSession->patientPackage?->patient?->first_name . ' ' . $aestheticSession->patientPackage?->patient?->last_name)
        : ($aestheticSession->patient?->first_name . ' ' . $aestheticSession->patient?->last_name);
@endphp

@section('title', __('Session :number - :patient', ['number' => $aestheticSession->session_number, 'patient' => $patientName ?? '']))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-calendar-check text-primary me-2"></i>
                        {{ __('Session :number', ['number' => $aestheticSession->session_number]) }}
                        @if($aestheticSession->isDirectSession)
                            <span class="badge bg-warning ms-2">{{ __('Direct Treatment') }}</span>
                        @endif
                    </h1>
                    <p class="text-muted mb-0">
                        @if($aestheticSession->isPackageSession)
                            {{ $aestheticSession->patientPackage?->patient?->first_name }} {{ $aestheticSession->patientPackage?->patient?->last_name }}
                            - {{ $aestheticSession->patientPackage?->package?->name ?? __('Package') }}
                        @else
                            {{ $aestheticSession->patient?->first_name }} {{ $aestheticSession->patient?->last_name }}
                            @if($aestheticSession->treatment)- {{ $aestheticSession->treatment->name }}@endif
                        @endif
                    </p>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <form method="POST" action="{{ route('aesthetic.sessions.update', $aestheticSession) }}" class="d-flex gap-2 align-items-center">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="session_mode" value="{{ $aestheticSession->isPackageSession ? 'package' : 'direct' }}">
                        <input type="hidden" name="patient_package_id" value="{{ $aestheticSession->patient_package_id ?? '' }}">
                        <input type="hidden" name="patient_id" value="{{ $aestheticSession->patient_id ?? '' }}">
                        <input type="hidden" name="treatment_id" value="{{ $aestheticSession->treatment_id ?? '' }}">
                        <input type="hidden" name="session_number" value="{{ $aestheticSession->session_number }}">
                        <input type="hidden" name="session_date" value="{{ $aestheticSession->session_date->format('Y-m-d') }}">
                        <input type="hidden" name="notes" value="{{ $aestheticSession->notes ?? '' }}">

                        <select name="status" class="form-select form-select-sm" style="width: 140px;" onchange="this.form.submit()">
                            @foreach(\App\Models\AestheticSession::STATUSES as $key => $label)
                                <option value="{{ $key }}" {{ $aestheticSession->status === $key ? 'selected' : '' }}>{{ __($label) }}</option>
                            @endforeach
                        </select>
                    </form>

                    <a href="{{ route('aesthetic.sessions.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back') }}
                    </a>
                    <a href="{{ route('aesthetic.sessions.edit', $aestheticSession) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-edit me-1"></i>
                        {{ __('Edit') }}
                    </a>
                </div>
            </div>

            <!-- Session Info Card -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-calendar fa-2x text-primary mb-2"></i>
                            <h5 class="mb-1">{{ $aestheticSession->session_date->format('M d, Y') }}</h5>
                            <small class="text-muted">{{ __('Session Date') }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-info-circle fa-2x text-{{ $aestheticSession->status_color }} mb-2"></i>
                            <h5 class="mb-1">{{ $aestheticSession->status_display }}</h5>
                            <small class="text-muted">{{ __('Status') }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-images fa-2x text-info mb-2"></i>
                            <h5 class="mb-1">{{ $aestheticSession->images->count() }}</h5>
                            <small class="text-muted">{{ __('Images') }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-spa fa-2x text-success mb-2"></i>
                            @if($aestheticSession->isPackageSession)
                                @if($aestheticSession->patientPackage?->package?->treatments?->count() > 0)
                                    <h5 class="mb-1">
                                        @foreach($aestheticSession->patientPackage->package->treatments as $pt)
                                            {{ $pt->name }}{{ !$loop->last ? ', ' : '' }}
                                        @endforeach
                                    </h5>
                                @else
                                    <h5 class="mb-1">{{ $aestheticSession->patientPackage?->package?->treatment?->name ?? '-' }}</h5>
                                @endif
                                <small class="text-muted">{{ __('Package Treatments') }}</small>
                            @else
                                <h5 class="mb-1">{{ $aestheticSession->treatment?->name ?? '-' }}</h5>
                                <small class="text-muted">{{ __('Direct Treatment') }}</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if($aestheticSession->notes)
            <div class="alert alert-light border mb-4">
                <h6 class="alert-heading"><i class="fas fa-sticky-note me-2"></i>{{ __('Notes') }}</h6>
                <p class="mb-0">{{ $aestheticSession->notes }}</p>
            </div>
            @endif

            <!-- Before/After Comparison -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-images me-2"></i>
                        {{ __('Before / After Comparison') }}
                    </h6>
                    @if($aestheticSession->has_comparison)
                        <span class="badge bg-success">{{ __('Comparison Ready') }}</span>
                    @else
                        <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#demoComparisonModal">
                            <i class="fas fa-eye me-1"></i>{{ __('View Demo') }}
                        </button>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Before Images -->
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-camera me-2 text-warning"></i>
                                {{ __('Before') }}
                                <span class="badge bg-warning ms-2">{{ $aestheticSession->beforeImages->count() }}</span>
                            </h6>
                            @if($aestheticSession->beforeImages->count() > 0)
                                <div class="row g-2">
                                    @foreach($aestheticSession->beforeImages as $image)
                                    <div class="col-6 col-lg-4">
                                        <div class="position-relative">
                                            <a href="{{ $image->image_url }}" target="_blank">
                                                <img src="{{ $image->image_url }}" class="img-fluid rounded border" alt="Before"
                                                     style="width: 100%; height: 150px; object-fit: cover;">
                                            </a>
                                            <form method="POST" action="{{ route('aesthetic.sessions.images.destroy', [$aestheticSession, $image]) }}"
                                                  class="position-absolute top-0 end-0 m-1">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger"
                                                        onclick="return confirm('{{ __('Delete this image?') }}')"
                                                        style="padding: 2px 6px; font-size: 10px;">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-camera fa-2x mb-2"></i>
                                    <p class="mb-0 small">{{ __('No before images yet') }}</p>
                                </div>
                            @endif
                        </div>

                        <!-- After Images -->
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-camera me-2 text-success"></i>
                                {{ __('After') }}
                                <span class="badge bg-success ms-2">{{ $aestheticSession->afterImages->count() }}</span>
                            </h6>
                            @if($aestheticSession->afterImages->count() > 0)
                                <div class="row g-2">
                                    @foreach($aestheticSession->afterImages as $image)
                                    <div class="col-6 col-lg-4">
                                        <div class="position-relative">
                                            <a href="{{ $image->image_url }}" target="_blank">
                                                <img src="{{ $image->image_url }}" class="img-fluid rounded border" alt="After"
                                                     style="width: 100%; height: 150px; object-fit: cover;">
                                            </a>
                                            <form method="POST" action="{{ route('aesthetic.sessions.images.destroy', [$aestheticSession, $image]) }}"
                                                  class="position-absolute top-0 end-0 m-1">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger"
                                                        onclick="return confirm('{{ __('Delete this image?') }}')"
                                                        style="padding: 2px 6px; font-size: 10px;">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-camera fa-2x mb-2"></i>
                                    <p class="mb-0 small">{{ __('No after images yet') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Demo Comparison Modal -->
            <div class="modal fade" id="demoComparisonModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-images me-2"></i>{{ __('Demo: Before / After Comparison') }}
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-0">
                            <div class="alert alert-info m-3 mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                {{ __('This is a demo showing how before and after images will appear once uploaded to a session.') }}
                            </div>
                            <div class="p-3">
                                <div class="row g-3">
                                    <!-- Demo Before -->
                                    <div class="col-md-6">
                                        <div class="card border-warning h-100">
                                            <div class="card-header bg-warning text-dark">
                                                <i class="fas fa-calendar-day me-2"></i>{{ __('Before Treatment') }}
                                                <span class="badge bg-dark ms-2">{{ __('Demo') }}</span>
                                            </div>
                                            <div class="card-body text-center p-0">
                                                <div class="demo-image-placeholder bg-secondary bg-opacity-10 d-flex flex-column align-items-center justify-content-center"
                                                     style="height: 280px;">
                                                    <i class="fas fa-user-circle fa-5x text-secondary mb-3"></i>
                                                    <p class="text-muted fw-bold mb-1">{{ __('BEFORE') }}</p>
                                                    <p class="text-muted small">{{ __('Patient photo before treatment session') }}</p>
                                                    <span class="badge bg-warning mt-2">{{ __('Day 0') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Demo After -->
                                    <div class="col-md-6">
                                        <div class="card border-success h-100">
                                            <div class="card-header bg-success text-white">
                                                <i class="fas fa-calendar-check me-2"></i>{{ __('After Treatment') }}
                                                <span class="badge bg-light text-success ms-2">{{ __('Demo') }}</span>
                                            </div>
                                            <div class="card-body text-center p-0">
                                                <div class="demo-image-placeholder bg-success bg-opacity-10 d-flex flex-column align-items-center justify-content-center"
                                                     style="height: 280px;">
                                                    <i class="fas fa-user-check fa-5x text-success mb-3"></i>
                                                    <p class="text-success fw-bold mb-1">{{ __('AFTER') }}</p>
                                                    <p class="text-muted small">{{ __('Patient photo after treatment session') }}</p>
                                                    <span class="badge bg-success mt-2">{{ __('Day 30') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Demo Comparison Slider -->
                                <div class="mt-4">
                                    <h6 class="text-center mb-3">
                                        <i class="fas fa-exchange-alt me-2 text-primary"></i>{{ __('Side-by-Side Comparison View') }}
                                    </h6>
                                    <div class="row g-0">
                                        <div class="col-6 position-relative">
                                            <div class="bg-dark text-white text-center py-5" style="border-radius: 8px 0 0 8px;">
                                                <i class="fas fa-user fa-4x text-secondary mb-2"></i>
                                                <p class="mb-0 fw-bold">{{ __('BEFORE') }}</p>
                                                <small class="text-muted">{{ __('Baseline') }}</small>
                                            </div>
                                            <span class="position-absolute top-0 start-0 m-2 badge bg-warning">{{ __('Before') }}</span>
                                        </div>
                                        <div class="col-6 position-relative">
                                            <div class="bg-primary text-white text-center py-5" style="border-radius: 0 8px 8px 0;">
                                                <i class="fas fa-user-check fa-4x text-white mb-2"></i>
                                                <p class="mb-0 fw-bold">{{ __('AFTER') }}</p>
                                                <small class="text-white-50">{{ __('Post Session') }}</small>
                                            </div>
                                            <span class="position-absolute top-0 end-0 m-2 badge bg-success">{{ __('After') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                            <a href="#upload-section" class="btn btn-primary" onclick="document.getElementById('upload-section').scrollIntoView({behavior:'smooth'});" data-bs-dismiss="modal">
                                <i class="fas fa-upload me-1"></i>{{ __('Upload Real Images') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inventory Usage -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-boxes me-2 text-primary"></i>
                        {{ __('Inventory Used') }}
                    </h6>
                    @if($aestheticSession->inventoryUsages->count() > 0)
                        <span class="badge bg-success">{{ __('Stock Deducted') }}</span>
                    @else
                        <span class="badge bg-secondary">{{ __('No Items Recorded') }}</span>
                    @endif
                </div>
                <div class="card-body">
                    @if($aestheticSession->inventoryUsages->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('Product') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Quantity Used') }}</th>
                                        <th>{{ __('Date') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($aestheticSession->inventoryUsages as $usage)
                                    <tr>
                                        <td>{{ $usage->product->product_name }}</td>
                                        <td><span class="badge bg-secondary">{{ \App\Models\AestheticInventory::TYPES[$usage->product->type] ?? $usage->product->type }}</span></td>
                                        <td><strong>{{ $usage->quantity_used }}</strong></td>
                                        <td>{{ $usage->created_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-boxes fa-2x mb-2"></i>
                            <p class="mb-0">{{ __('No inventory items recorded for this session.') }}</p>
                            <p class="mb-0 small">{{ __('Add inventory items when creating or editing the session.') }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Upload Images -->
            <div class="row" id="upload-section">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-upload me-2 text-warning"></i>
                                {{ __('Upload Before Images') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('aesthetic.sessions.images.store', $aestheticSession) }}"
                                  enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="type" value="before">
                                <div class="mb-3">
                                    <input type="file" class="form-control @error('images') is-invalid @enderror"
                                           name="images[]" accept="image/*" multiple required>
                                    <small class="form-text text-muted">{{ __('You can select multiple images') }}</small>
                                    @error('images')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @error('images.*')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button type="submit" class="btn btn-warning w-100">
                                    <i class="fas fa-upload me-1"></i>
                                    {{ __('Upload Before Images') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-upload me-2 text-success"></i>
                                {{ __('Upload After Images') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('aesthetic.sessions.images.store', $aestheticSession) }}"
                                  enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="type" value="after">
                                <div class="mb-3">
                                    <input type="file" class="form-control @error('images') is-invalid @enderror"
                                           name="images[]" accept="image/*" multiple required>
                                    <small class="form-text text-muted">{{ __('You can select multiple images') }}</small>
                                    @error('images')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @error('images.*')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fas fa-upload me-1"></i>
                                    {{ __('Upload After Images') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
