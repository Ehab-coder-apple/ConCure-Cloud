@extends('layouts.app')

@section('title', $dentalImage->title ?? __('Dental Image'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-image text-info me-2"></i>
                        {{ $dentalImage->title ?? __('Dental Image') }}
                    </h1>
                    <p class="text-muted mb-0">
                        {{ __('Patient') }}: <strong>{{ $patient->full_name }}</strong> ({{ $patient->patient_id }})
                    </p>
                </div>
                <div>
                    <a href="{{ url("/dental/patients/{$patient->id}/images") }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to Images') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Image Display -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-image me-2"></i>
                        {{ __('Image') }}
                    </h6>
                </div>
                <div class="card-body text-center bg-dark">
                    <img src="{{ \App\Services\StorageQuotaService::getSecureUrl($dentalImage->file_path) }}"
                         alt="{{ $dentalImage->title }}" 
                         class="img-fluid rounded"
                         style="max-height: 600px; cursor: zoom-in;"
                         onclick="openImageModal(this.src)">
                </div>
            </div>

            <!-- Image Details -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ __('Image Details') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <p class="mb-2">
                                <strong>{{ __('Image Type') }}:</strong><br>
                                <span class="badge bg-info fs-6">{{ ucfirst(str_replace('_', ' ', $dentalImage->image_type)) }}</span>
                            </p>
                        </div>

                        @if($dentalImage->tooth_number)
                            <div class="col-md-6 mb-3">
                                <p class="mb-2">
                                    <strong>{{ __('Tooth Number') }}:</strong><br>
                                    <span class="badge bg-secondary fs-6">{{ $dentalImage->tooth_number }}</span>
                                </p>
                            </div>
                        @endif

                        @if($dentalImage->image_date)
                            <div class="col-md-6 mb-3">
                                <p class="mb-2">
                                    <strong>{{ __('Image Date') }}:</strong><br>
                                    {{ \Carbon\Carbon::parse($dentalImage->image_date)->format('M d, Y') }}
                                </p>
                            </div>
                        @endif

                        <div class="col-md-6 mb-3">
                            <p class="mb-2">
                                <strong>{{ __('Uploaded') }}:</strong><br>
                                {{ $dentalImage->created_at->format('M d, Y H:i') }}
                            </p>
                        </div>

                        @if($dentalImage->uploader)
                            <div class="col-md-6 mb-3">
                                <p class="mb-2">
                                    <strong>{{ __('Uploaded By') }}:</strong><br>
                                    {{ $dentalImage->uploader->name }}
                                </p>
                            </div>
                        @endif

                        <div class="col-md-6 mb-3">
                            <p class="mb-2">
                                <strong>{{ __('File Size') }}:</strong><br>
                                {{ number_format($dentalImage->file_size / 1024 / 1024, 2) }} MB
                            </p>
                        </div>

                        @if($dentalImage->description)
                            <div class="col-12 mb-3">
                                <p class="mb-2">
                                    <strong>{{ __('Description') }}:</strong><br>
                                    {{ $dentalImage->description }}
                                </p>
                            </div>
                        @endif

                        @if($dentalImage->notes)
                            <div class="col-12">
                                <p class="mb-0">
                                    <strong>{{ __('Clinical Notes') }}:</strong><br>
                                    {{ $dentalImage->notes }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Linked Records -->
            @if($dentalImage->dentalChart || $dentalImage->dentalTreatment)
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-link me-2"></i>
                            {{ __('Linked Records') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($dentalImage->dentalChart)
                            <div class="mb-3">
                                <strong>{{ __('Dental Chart') }}:</strong><br>
                                <a href="{{ url("/dental/patients/{$patient->id}/charts/{$dentalImage->dental_chart_id}") }}" class="btn btn-sm btn-outline-primary mt-2">
                                    <i class="fas fa-chart-line me-1"></i>
                                    {{ ucfirst($dentalImage->dentalChart->chart_type) }} - {{ $dentalImage->dentalChart->created_at->format('M d, Y') }}
                                </a>
                            </div>
                        @endif

                        @if($dentalImage->dentalTreatment)
                            <div>
                                <strong>{{ __('Treatment Plan') }}:</strong><br>
                                <a href="{{ url("/dental/treatments/{$dentalImage->dental_treatment_id}") }}" class="btn btn-sm btn-outline-primary mt-2">
                                    <i class="fas fa-procedures me-1"></i>
                                    {{ $dentalImage->dentalTreatment->treatment_number }} - {{ $dentalImage->dentalTreatment->procedure_name }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-cog me-2"></i>
                        {{ __('Actions') }}
                    </h6>
                </div>
                <div class="card-body">
                    <a href="{{ \App\Services\StorageQuotaService::getSecureUrl($dentalImage->file_path) }}" download class="btn btn-success w-100 mb-2">
                        <i class="fas fa-download me-1"></i>
                        {{ __('Download Image') }}
                    </a>

                    <a href="{{ \App\Services\StorageQuotaService::getSecureUrl($dentalImage->file_path) }}" target="_blank" class="btn btn-outline-primary w-100 mb-2">
                        <i class="fas fa-external-link-alt me-1"></i>
                        {{ __('Open in New Tab') }}
                    </a>

                    <button type="button" class="btn btn-outline-info w-100 mb-2" onclick="printImage()">
                        <i class="fas fa-print me-1"></i>
                        {{ __('Print Image') }}
                    </button>

                    @if(in_array(auth()->user()->role, ['admin', 'program_owner']))
                        <hr>
                        <form method="POST" action="{{ url("/dental/patients/{$patient->id}/images/{$dentalImage->id}") }}" onsubmit="return confirm('{{ __('Are you sure you want to delete this image?') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="fas fa-trash me-1"></i>
                                {{ __('Delete Image') }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Image Metadata -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-file-alt me-2"></i>
                        {{ __('File Information') }}
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-2 small">
                        <strong>{{ __('File Name') }}:</strong><br>
                        <span class="text-muted">{{ basename($dentalImage->file_path) }}</span>
                    </p>
                    <p class="mb-2 small">
                        <strong>{{ __('File Type') }}:</strong><br>
                        <span class="text-muted">{{ strtoupper(pathinfo($dentalImage->file_path, PATHINFO_EXTENSION)) }}</span>
                    </p>
                    <p class="mb-2 small">
                        <strong>{{ __('File Size') }}:</strong><br>
                        <span class="text-muted">{{ number_format($dentalImage->file_size / 1024 / 1024, 2) }} MB</span>
                    </p>
                    <p class="mb-0 small">
                        <strong>{{ __('Storage Path') }}:</strong><br>
                        <span class="text-muted text-break">{{ $dentalImage->file_path }}</span>
                    </p>
                </div>
            </div>

            <!-- Patient Info -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-user me-2"></i>
                        {{ __('Patient Information') }}
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>{{ __('Name') }}:</strong><br>
                        {{ $patient->full_name }}
                    </p>
                    <p class="mb-2">
                        <strong>{{ __('Patient ID') }}:</strong><br>
                        {{ $patient->patient_id }}
                    </p>
                    <p class="mb-3">
                        <strong>{{ __('Age') }}:</strong><br>
                        {{ $patient->age ?? 'N/A' }}
                    </p>
                    <a href="{{ url("/patients/{$patient->id}") }}" class="btn btn-sm btn-outline-primary w-100">
                        <i class="fas fa-user me-1"></i>
                        {{ __('View Patient Profile') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content bg-dark">
            <div class="modal-header border-0">
                <h5 class="modal-title text-white">{{ $dentalImage->title ?? __('Dental Image') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-0">
                <img id="modalImage" src="" alt="{{ $dentalImage->title }}" class="img-fluid">
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openImageModal(src) {
    document.getElementById('modalImage').src = src;
    const modal = new bootstrap.Modal(document.getElementById('imageModal'));
    modal.show();
}

function printImage() {
    const imageUrl = '{{ \App\Services\StorageQuotaService::getSecureUrl($dentalImage->file_path) }}';
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>{{ $dentalImage->title ?? __('Dental Image') }}</title>
                <style>
                    body { margin: 0; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
                    img { max-width: 100%; height: auto; }
                </style>
            </head>
            <body>
                <img src="${imageUrl}" onload="window.print(); window.close();">
            </body>
        </html>
    `);
    printWindow.document.close();
}
</script>
@endpush
@endsection

