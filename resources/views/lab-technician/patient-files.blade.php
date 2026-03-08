@extends('layouts.app')

@section('title', __('Upload Lab Result'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-file-medical me-2"></i>
                        {{ __('Upload Lab Result') }}
                    </h1>
                    <p class="text-muted mb-0">
                        {{ __('Patient') }}: <strong>{{ $patient->full_name }}</strong> ({{ $patient->patient_id }})
                    </p>
                </div>
                <div>
                    <a href="{{ route('recommendations.lab-technician.patients') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to Patients') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Form -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-upload me-2"></i>
                        {{ __('Upload New Lab Result') }}
                    </h6>
                </div>
                <div class="card-body">
                    <form id="uploadForm" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="test_name" class="form-label">
                                {{ __('Test Name') }}
                                <small class="text-muted">({{ __('Optional') }})</small>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="test_name" 
                                   name="test_name"
                                   placeholder="{{ __('e.g., Complete Blood Count, Lipid Profile, etc.') }}">
                        </div>

                        <div class="mb-3">
                            <label for="result_file" class="form-label">
                                {{ __('Lab Result File') }}
                                <span class="text-danger">*</span>
                            </label>
                            <input type="file" 
                                   class="form-control" 
                                   id="result_file" 
                                   name="result_file"
                                   accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                   required>
                            <small class="text-muted">
                                {{ __('Accepted formats: PDF, JPG, JPEG, PNG, DOC, DOCX (Max: 10MB)') }}
                            </small>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">
                                {{ __('Description/Notes') }}
                                <small class="text-muted">({{ __('Optional') }})</small>
                            </label>
                            <textarea class="form-control" 
                                      id="description" 
                                      name="description"
                                      rows="3"
                                      placeholder="{{ __('Add any additional notes or comments...') }}"></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-upload me-2"></i>
                                {{ __('Upload Lab Result') }}
                            </button>
                        </div>
                    </form>

                    <!-- Alert Container -->
                    <div id="alertContainer" class="mt-3"></div>
                </div>
            </div>
        </div>

        <!-- Patient Info Card -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user me-2"></i>
                        {{ __('Patient Information') }}
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th>{{ __('Name') }}:</th>
                            <td>{{ $patient->full_name }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('ID') }}:</th>
                            <td>{{ $patient->patient_id }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Phone') }}:</th>
                            <td>{{ $patient->phone ?? __('N/A') }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Gender') }}:</th>
                            <td>{{ ucfirst($patient->gender ?? 'N/A') }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Age') }}:</th>
                            <td>
                                @if($patient->date_of_birth)
                                    {{ \Carbon\Carbon::parse($patient->date_of_birth)->age }} {{ __('years') }}
                                @else
                                    {{ __('N/A') }}
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Previous Lab Results -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history me-2"></i>
                        {{ __('Previous Lab Results') }}
                    </h6>
                </div>
                <div class="card-body">
                    @if($labResults->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('File Name') }}</th>
                                        <th>{{ __('Description') }}</th>
                                        <th>{{ __('Uploaded By') }}</th>
                                        <th>{{ __('Upload Date') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($labResults as $result)
                                    <tr>
                                        <td>
                                            <i class="fas fa-file-pdf text-danger me-1"></i>
                                            {{ $result->original_name }}
                                        </td>
                                        <td>{{ $result->description ?? __('N/A') }}</td>
                                        <td>
                                            @if($result->uploader)
                                                {{ $result->uploader->full_name }}
                                                <br>
                                                <small class="text-muted">{{ ucfirst($result->uploader->role) }}</small>
                                            @else
                                                {{ __('Unknown') }}
                                            @endif
                                        </td>
                                        <td>
                                            {{ $result->created_at->format('M d, Y') }}
                                            <br>
                                            <small class="text-muted">{{ $result->created_at->format('h:i A') }}</small>
                                        </td>
                                        <td>
                                            <a href="{{ \App\Services\StorageQuotaService::getSecureUrl($result->file_path) }}"
                                               target="_blank"
                                               class="btn btn-sm btn-outline-primary"
                                               title="{{ __('View File') }}">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('No previous lab results') }}</h5>
                            <p class="text-muted">{{ __('This patient has no lab results uploaded yet') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const uploadForm = document.getElementById('uploadForm');
    const alertContainer = document.getElementById('alertContainer');

    if (uploadForm) {
        uploadForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;

            // Disable button and show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>{{ __("Uploading...") }}';

            // Clear previous alerts
            alertContainer.innerHTML = '';

            fetch('{{ route("recommendations.lab-technician.patients.upload", $patient->id) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    alertContainer.innerHTML = `
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            ${data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `;

                    // Reset form
                    uploadForm.reset();

                    // Reload page after 1.5 seconds to show the new file
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    // Show error message
                    alertContainer.innerHTML = `
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            ${data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `;

                    // Re-enable button
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alertContainer.innerHTML = `
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        {{ __("An error occurred while uploading the file. Please try again.") }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;

                // Re-enable button
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            });
        });
    }
});
</script>
@endpush


