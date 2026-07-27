@extends('layouts.app')

@section('title', __('Dental Images') . ' - ' . $patient->full_name)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-images text-info me-2"></i>
                        {{ __('Dental Images') }}
                    </h1>
                    <p class="text-muted mb-0">
                        {{ __('Patient') }}: <strong>{{ $patient->full_name }}</strong> ({{ $patient->patient_id }})
                    </p>
                </div>
                <div>
                    <a href="{{ url("/dental/patients/{$patient->id}/images/upload") }}" class="btn btn-success me-2">
                        <i class="fas fa-upload me-1"></i>
                        {{ __('Upload Images') }}
                    </a>
                    <a href="{{ url("/patients/{$patient->id}") }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to Patient') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ url("/dental/patients/{$patient->id}/images") }}" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Image Type') }}</label>
                            <select name="image_type" class="form-select">
                                <option value="">{{ __('All Types') }}</option>
                                <option value="xray" {{ request('image_type') === 'xray' ? 'selected' : '' }}>{{ __('X-Ray') }}</option>
                                <option value="intraoral" {{ request('image_type') === 'intraoral' ? 'selected' : '' }}>{{ __('Intraoral') }}</option>
                                <option value="extraoral" {{ request('image_type') === 'extraoral' ? 'selected' : '' }}>{{ __('Extraoral') }}</option>
                                <option value="panoramic" {{ request('image_type') === 'panoramic' ? 'selected' : '' }}>{{ __('Panoramic') }}</option>
                                <option value="cbct" {{ request('image_type') === 'cbct' ? 'selected' : '' }}>{{ __('CBCT') }}</option>
                                <option value="other" {{ request('image_type') === 'other' ? 'selected' : '' }}>{{ __('Other') }}</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">{{ __('Sort By') }}</label>
                            <select name="sort_by" class="form-select">
                                <option value="created_at" {{ request('sort_by', 'created_at') === 'created_at' ? 'selected' : '' }}>{{ __('Upload Date') }}</option>
                                <option value="image_type" {{ request('sort_by') === 'image_type' ? 'selected' : '' }}>{{ __('Image Type') }}</option>
                                <option value="tooth_number" {{ request('sort_by') === 'tooth_number' ? 'selected' : '' }}>{{ __('Tooth Number') }}</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">{{ __('Order') }}</label>
                            <select name="sort_order" class="form-select">
                                <option value="desc" {{ request('sort_order', 'desc') === 'desc' ? 'selected' : '' }}>{{ __('Newest First') }}</option>
                                <option value="asc" {{ request('sort_order') === 'asc' ? 'selected' : '' }}>{{ __('Oldest First') }}</option>
                            </select>
                        </div>

                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-filter me-1"></i>
                                {{ __('Apply Filters') }}
                            </button>
                            <a href="{{ url("/dental/patients/{$patient->id}/images") }}" class="btn btn-outline-secondary">
                                <i class="fas fa-redo me-1"></i>
                                {{ __('Reset') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Images Gallery -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-image me-2"></i>
                        {{ __('Image Gallery') }} ({{ $images->total() }})
                    </h6>
                </div>
                <div class="card-body">
                    @if($images->count() > 0)
                        <div class="row g-3">
                            @foreach($images as $image)
                                <div class="col-md-3 col-sm-6">
                                    <div class="card h-100 shadow-sm">
                                        <!-- Image Thumbnail -->
                                        <a href="{{ url("/dental/patients/{$patient->id}/images/{$image->id}") }}">
                                            <img src="{{ \App\Services\StorageQuotaService::getSecureUrl($image->file_path) }}"
                                                 class="card-img-top" 
                                                 alt="{{ $image->title }}"
                                                 style="height: 200px; object-fit: cover; cursor: pointer;">
                                        </a>
                                        
                                        <!-- Image Info -->
                                        <div class="card-body p-3">
                                            <h6 class="card-title mb-2">
                                                <a href="{{ url("/dental/patients/{$patient->id}/images/{$image->id}") }}" class="text-decoration-none">
                                                    {{ Str::limit($image->title ?? 'Untitled', 30) }}
                                                </a>
                                            </h6>
                                            
                                            <div class="mb-2">
                                                <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $image->image_type)) }}</span>
                                                @if($image->tooth_number)
                                                    <span class="badge bg-secondary">{{ __('Tooth') }} {{ $image->tooth_number }}</span>
                                                @endif
                                            </div>
                                            
                                            <p class="card-text small text-muted mb-2">
                                                <i class="fas fa-calendar me-1"></i>
                                                {{ $image->created_at->format('M d, Y') }}
                                            </p>
                                            
                                            @if($image->uploader)
                                                <p class="card-text small text-muted mb-0">
                                                    <i class="fas fa-user me-1"></i>
                                                    {{ $image->uploader->name }}
                                                </p>
                                            @endif
                                        </div>
                                        
                                        <!-- Actions -->
                                        <div class="card-footer bg-transparent border-top p-2">
                                            <div class="d-flex justify-content-between">
                                                <a href="{{ url("/dental/patients/{$patient->id}/images/{$image->id}") }}" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ \App\Services\StorageQuotaService::getSecureUrl($image->file_path) }}"
                                                   download 
                                                   class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4">
                            {{ $images->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-images fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('No Dental Images Found') }}</h5>
                            <p class="text-muted">{{ __('Upload dental images to get started.') }}</p>
                            <a href="{{ url("/dental/patients/{$patient->id}/images/upload") }}" class="btn btn-success">
                                <i class="fas fa-upload me-1"></i>
                                {{ __('Upload Images') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

