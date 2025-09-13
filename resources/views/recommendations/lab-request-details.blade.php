@extends('layouts.app')

@section('title', __('Lab Request Details'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">{{ __('Lab Request Details') }}</h1>
                    <p class="text-muted mb-0">{{ $labRequest->request_number }}</p>
                </div>
                <div>
                    <a href="{{ route('recommendations.lab-requests') }}" class="btn btn-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to Lab Requests') }}
                    </a>
                    <a href="{{ route('recommendations.lab-requests.print', $labRequest->id) }}"
                       class="btn btn-outline-primary me-2" target="_blank">
                        <i class="fas fa-print me-1"></i>
                        {{ __('Print') }}
                    </a>
                    @if(
                        ($labRequest->status === 'pending' && !$labRequest->isSent()) ||
                        ($labRequest->status === 'cancelled')
                    )
                    <form action="{{ route('recommendations.lab-requests.destroy', $labRequest->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('{{ __("Are you sure you want to delete this lab request? This action cannot be undone.") }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="fas fa-trash me-1"></i>
                            {{ __('Delete') }}
                        </button>
                    </form>
                    @endif
                </div>
            </div>

            <!-- Lab Request Details Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Request Information') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>{{ __('Patient Information') }}</h6>
                            <p><strong>{{ __('Name') }}:</strong> {{ $labRequest->patient->full_name }}</p>
                            <p><strong>{{ __('Phone') }}:</strong> {{ $labRequest->patient->phone ?? 'Not provided' }}</p>
                            <p><strong>{{ __('Email') }}:</strong> {{ $labRequest->patient->email ?? 'Not provided' }}</p>
                            <p><strong>{{ __('Date of Birth') }}:</strong> {{ $labRequest->patient->date_of_birth ?? 'Not provided' }}</p>
                            <p><strong>{{ __('Gender') }}:</strong> {{ $labRequest->patient->gender ?? 'Not provided' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>{{ __('Request Details') }}</h6>
                            <p><strong>{{ __('Request Number') }}:</strong> {{ $labRequest->request_number }}</p>
                            <p><strong>{{ __('Status') }}:</strong>
                                <span class="badge {{ $labRequest->status === 'completed' ? 'bg-success' : ($labRequest->status === 'pending' ? 'bg-warning' : 'bg-secondary') }}">
                                    {{ strtoupper($labRequest->status) }}
                                </span>
                            </p>
                            <p><strong>{{ __('Priority') }}:</strong>
                                <span class="badge {{ $labRequest->priority === 'urgent' ? 'bg-warning' : ($labRequest->priority === 'stat' ? 'bg-danger' : 'bg-secondary') }}">
                                    {{ strtoupper($labRequest->priority ?? 'normal') }}
                                </span>
                            </p>
                            <p><strong>{{ __('Requested Date') }}:</strong> {{ $labRequest->requested_date ? $labRequest->requested_date->format('M d, Y') : 'Not set' }}</p>
                            <p><strong>{{ __('Due Date') }}:</strong> {{ $labRequest->due_date ? $labRequest->due_date->format('M d, Y') : 'Not set' }}</p>
                            @if($labRequest->doctor)
                            <p><strong>{{ __('Doctor') }}:</strong> {{ $labRequest->doctor->first_name }} {{ $labRequest->doctor->last_name }}</p>
                            @endif
                        </div>
                    </div>

                    @if($labRequest->clinical_notes)
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6>{{ __('Clinical Notes') }}</h6>
                            <p class="text-muted">{{ $labRequest->clinical_notes }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Tests Requested -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Tests Requested') }}</h5>
                </div>
                <div class="card-body">
                    @if($labRequest->tests && $labRequest->tests->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($labRequest->tests as $test)
                            <li class="list-group-item px-0">
                                <strong>{{ $test->test_name }}</strong>
                                @if($test->instructions)
                                    <br><small class="text-muted">{{ $test->instructions }}</small>
                                @endif
                            </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">{{ __('No tests specified') }}</p>
                    @endif
                </div>
            </div>

            <!-- Communication & Results Section -->
            <div class="card border-primary mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-comments me-2"></i>
                        {{ __('Communication & Results') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <button type="button" class="btn btn-success w-100" onclick="sendViaWhatsApp({{ $labRequest->id }})">
                                <i class="fab fa-whatsapp me-1"></i>
                                {{ __('Send via WhatsApp') }}
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-primary w-100" onclick="sendViaEmail({{ $labRequest->id }})">
                                <i class="fas fa-envelope me-1"></i>
                                {{ __('Send via Email') }}
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-info w-100" onclick="uploadResult({{ $labRequest->id }})">
                                <i class="fas fa-upload me-1"></i>
                                {{ __('Upload Result') }}
                            </button>
                        </div>
                    </div>
                    
                    <!-- Laboratory Information -->
                    @if($labRequest->lab_name || $labRequest->lab_email || $labRequest->lab_phone || $labRequest->lab_whatsapp)
                    <div class="mt-4">
                        <h6>{{ __('Laboratory Information') }}</h6>
                        <div class="row">
                            <div class="col-md-6">
                                @if($labRequest->lab_name)
                                <p><strong>{{ __('Lab Name') }}:</strong> {{ $labRequest->lab_name }}</p>
                                @endif
                                @if($labRequest->lab_phone)
                                <p><strong>{{ __('Phone') }}:</strong>
                                    <a href="tel:{{ $labRequest->lab_phone }}" class="text-decoration-none">
                                        {{ $labRequest->lab_phone }}
                                    </a>
                                </p>
                                @endif
                            </div>
                            <div class="col-md-6">
                                @if($labRequest->lab_email)
                                <p><strong>{{ __('Email') }}:</strong>
                                    <a href="mailto:{{ $labRequest->lab_email }}" class="text-decoration-none">
                                        {{ $labRequest->lab_email }}
                                    </a>
                                </p>
                                @endif
                                @if($labRequest->lab_whatsapp)
                                <p><strong>{{ __('WhatsApp') }}:</strong>
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $labRequest->lab_whatsapp) }}"
                                       target="_blank" class="text-decoration-none text-success">
                                        {{ $labRequest->lab_whatsapp }}
                                    </a>
                                </p>
                                @endif
                            </div>
                        </div>

                        <!-- Preferred Communication Method -->
                        @if($labRequest->communication_method)
                        <div class="mt-3">
                            <p><strong>{{ __('Preferred Communication') }}:</strong>
                                @if($labRequest->communication_method === 'whatsapp')
                                    <span class="badge bg-success">
                                        <i class="fab fa-whatsapp me-1"></i>
                                        WhatsApp
                                    </span>
                                @elseif($labRequest->communication_method === 'email')
                                    <span class="badge bg-primary">
                                        <i class="fas fa-envelope me-1"></i>
                                        Email
                                    </span>
                                @endif
                            </p>
                        </div>
                        @endif
                    </div>
                    @endif

                    <!-- Communication Status -->
                    <div class="mt-4 p-3 bg-light rounded">
                        <h6 class="text-muted">{{ __('Communication Status') }}</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <small class="text-muted">
                                    <strong>📱 WhatsApp:</strong><br>
                                    @if(config('services.whatsapp.api_url') && config('services.whatsapp.api_token'))
                                        <span class="badge bg-success">API Configured</span><br>
                                        <small>Messages sent automatically</small>
                                    @else
                                        <span class="badge bg-warning">Web Only</span><br>
                                        <small>Opens WhatsApp Web for manual sending</small>
                                    @endif
                                </small>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">
                                    <strong>📧 Email:</strong><br>
                                    @if(config('mail.default') && config('mail.mailers.'.config('mail.default').'.host'))
                                        <span class="badge bg-success">Configured</span><br>
                                        <small>Emails sent automatically</small>
                                    @else
                                        <span class="badge bg-danger">Not Configured</span><br>
                                        <small>Email sending not available</small>
                                    @endif
                                </small>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">
                                    <strong>📁 File Upload:</strong><br>
                                    <span class="badge bg-success">Available</span><br>
                                    <small>Local file storage ready</small>
                                </small>
                            </div>
                        </div>
                        <hr>
                        <small class="text-muted">
                            <strong>Lab Request ID:</strong> {{ $labRequest->id }} |
                            <strong>Generated:</strong> {{ now()->format('Y-m-d H:i:s') }}
                        </small>
                    </div>
                </div>
            </div>

            <!-- Results Section -->
            @if($labRequest->result_file_path)
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Results') }}</h5>
                </div>
                <div class="card-body">
                    <p><strong>{{ __('Result File') }}:</strong> 
                        <a href="{{ Storage::url($labRequest->result_file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-download me-1"></i>
                            {{ __('Download Result') }}
                        </a>
                    </p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Communication Functions
function sendViaWhatsApp(labRequestId) {
    // Check if lab request has WhatsApp number
    const labWhatsApp = '{{ $labRequest->lab_whatsapp ?? "" }}';
    let phone = labWhatsApp;

    if (!phone) {
        // No lab WhatsApp number registered, ask user to enter one
        phone = prompt('No WhatsApp number registered for this lab.\n\nEnter WhatsApp phone number:');
        if (!phone) return;
    }

    // If lab has WhatsApp number, use it automatically (user can still change it in the message prompt)

    const message = prompt(`Sending to WhatsApp: ${phone}\n\nEnter custom message (optional):`, '');

    const formData = new FormData();
    formData.append('phone_number', phone);
    if (message) formData.append('message', message);

    fetch(`/recommendations/lab-requests/${labRequestId}/send-whatsapp`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.whatsapp_url) {
                // WhatsApp API not configured - open web WhatsApp
                const userConfirmed = confirm(
                    'WhatsApp API is not configured. This will open WhatsApp Web where you can manually send the message.\n\n' +
                    'Click OK to open WhatsApp Web, or Cancel to abort.'
                );

                if (userConfirmed) {
                    window.open(data.whatsapp_url, '_blank');
                    alert('WhatsApp Web opened. Please send the message manually.\n\nPDF attachment: ' + (data.pdf_url || 'Generated'));
                }
            } else {
                // Actual API send
                alert('WhatsApp message sent successfully via API!');
            }
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error sending WhatsApp message');
    });
}

function sendViaEmail(labRequestId) {
    // Check if lab request has email address
    const labEmail = '{{ $labRequest->lab_email ?? "" }}';
    let email = labEmail;

    if (!email) {
        email = prompt('Enter email address:');
        if (!email) return;
    } else {
        const useLabEmail = confirm(`Send to lab's email: ${labEmail}?\n\nClick OK to use this email, or Cancel to enter a different email.`);
        if (!useLabEmail) {
            email = prompt('Enter email address:', labEmail);
            if (!email) return;
        }
    }

    const subject = prompt('Enter email subject (optional):', '');
    const message = prompt('Enter custom message (optional):', '');

    const formData = new FormData();
    formData.append('email', email);
    if (subject) formData.append('subject', subject);
    if (message) formData.append('message', message);

    fetch(`/recommendations/lab-requests/${labRequestId}/send-email`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Email sent successfully!');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error sending email');
    });
}

function uploadResult(labRequestId) {
    const fileInput = document.createElement('input');
    fileInput.type = 'file';
    fileInput.accept = '.pdf,.jpg,.jpeg,.png,.doc,.docx';
    
    fileInput.onchange = function() {
        const file = fileInput.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('result_file', file);

        // Show loading alert
        const loadingAlert = document.createElement('div');
        loadingAlert.className = 'alert alert-info position-fixed';
        loadingAlert.style.top = '20px';
        loadingAlert.style.right = '20px';
        loadingAlert.style.zIndex = '9999';
        loadingAlert.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Uploading result file...';
        document.body.appendChild(loadingAlert);

        fetch(`/recommendations/lab-requests/${labRequestId}/upload-result`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            document.body.removeChild(loadingAlert);

            if (data.success) {
                alert('Result file uploaded successfully!');
                location.reload(); // Refresh to show the uploaded file
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            document.body.removeChild(loadingAlert);
            console.error('Error:', error);
            alert('Error uploading result file');
        });
    };

    // Trigger file selection
    document.body.appendChild(fileInput);
    fileInput.click();
    document.body.removeChild(fileInput);
}

function deleteLabRequest(id) {
    if (confirm('{{ __("Are you sure you want to delete this lab request? This action cannot be undone.") }}')) {
        // Create a form to submit the DELETE request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url('recommendations/lab-requests') }}/${id}`;
        form.style.display = 'none';

        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(csrfToken);

        // Add method override for DELETE
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);

        // Submit the form
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush
