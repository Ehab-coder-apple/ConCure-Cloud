<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Status - {{ $clinic->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
        .status-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 700px;
            margin: 0 auto;
        }
        .status-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            display: inline-block;
            margin: 1rem 0;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
            border: 2px solid #ffeaa7;
        }
        .status-approved {
            background: #d1edff;
            color: #0c5460;
            border: 2px solid #bee5eb;
        }
        .status-rejected {
            background: #f8d7da;
            color: #721c24;
            border: 2px solid #f5c6cb;
        }
        .info-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
            margin: 1rem 0;
        }
        .timeline {
            position: relative;
            padding-left: 2rem;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 0.5rem;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #dee2e6;
        }
        .timeline-item {
            position: relative;
            margin-bottom: 2rem;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -1.5rem;
            top: 0.25rem;
            width: 1rem;
            height: 1rem;
            border-radius: 50%;
            background: #28a745;
            border: 2px solid white;
            box-shadow: 0 0 0 2px #28a745;
        }
        .timeline-item.pending::before {
            background: #ffc107;
            box-shadow: 0 0 0 2px #ffc107;
        }
        .timeline-item.future::before {
            background: #dee2e6;
            box-shadow: 0 0 0 2px #dee2e6;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="status-container">
            <div class="status-header">
                <i class="fas fa-hospital fa-3x mb-3"></i>
                <h2 class="mb-0">{{ $clinic->name }}</h2>
                <p class="mb-0 mt-2">Registration Status</p>
            </div>
            
            <div class="p-4">
                <!-- Current Status -->
                <div class="text-center mb-4">
                    @if($clinic->is_active)
                        <div class="status-badge status-approved">
                            <i class="fas fa-check-circle me-2"></i>
                            Approved & Active
                        </div>
                        <p class="text-success mb-0">
                            <i class="fas fa-thumbs-up me-1"></i>
                            Your clinic has been approved and is now active!
                        </p>
                    @else
                        <div class="status-badge status-pending">
                            <i class="fas fa-clock me-2"></i>
                            Pending Review
                        </div>
                        <p class="text-warning mb-0">
                            <i class="fas fa-hourglass-half me-1"></i>
                            Your registration is being reviewed by our team
                        </p>
                    @endif
                </div>

                <!-- Clinic Information -->
                <div class="info-card">
                    <h6 class="text-primary mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        Registration Details
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td class="text-muted">Clinic Name:</td>
                                    <td class="fw-bold">{{ $clinic->name }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Email:</td>
                                    <td>{{ $clinic->email }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Phone:</td>
                                    <td>{{ $clinic->phone ?? 'Not provided' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td class="text-muted">Registration Date:</td>
                                    <td>{{ $clinic->created_at->format('M d, Y') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Activation Code:</td>
                                    <td class="fw-bold">{{ $clinic->activation_code }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Max Users:</td>
                                    <td>{{ $clinic->max_users }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Registration Timeline -->
                <div class="info-card">
                    <h6 class="text-primary mb-3">
                        <i class="fas fa-list-ol me-2"></i>
                        Registration Progress
                    </h6>
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="fw-bold">Registration Submitted</div>
                            <div class="text-muted small">{{ $clinic->created_at->format('M d, Y \a\t H:i') }}</div>
                            <div class="text-muted">Your clinic registration was successfully submitted.</div>
                        </div>
                        
                        <div class="timeline-item {{ $clinic->is_active ? '' : 'pending' }}">
                            <div class="fw-bold">Under Review</div>
                            <div class="text-muted small">
                                @if($clinic->is_active)
                                    Completed
                                @else
                                    In Progress
                                @endif
                            </div>
                            <div class="text-muted">Our team is reviewing your application and verifying the information.</div>
                        </div>
                        
                        <div class="timeline-item {{ $clinic->is_active ? '' : 'future' }}">
                            <div class="fw-bold">Account Activation</div>
                            <div class="text-muted small">
                                @if($clinic->is_active && $clinic->activated_at)
                                    {{ $clinic->activated_at->format('M d, Y \a\t H:i') }}
                                @else
                                    Pending
                                @endif
                            </div>
                            <div class="text-muted">Your clinic account will be activated and login credentials will be sent.</div>
                        </div>
                    </div>
                </div>

                @if($clinic->is_active)
                    <!-- Success Message -->
                    <div class="alert alert-success">
                        <h6 class="alert-heading">
                            <i class="fas fa-party-horn me-2"></i>
                            Congratulations!
                        </h6>
                        <p class="mb-2">Your clinic has been approved and is now active. You can now:</p>
                        <ul class="mb-2">
                            <li>Log in to your clinic dashboard</li>
                            <li>Add staff members and manage users</li>
                            <li>Start managing patients and appointments</li>
                            <li>Access all ConCure features</li>
                        </ul>
                        <hr>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                            <a href="{{ url('/') }}" class="btn btn-success me-md-2">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Login to Your Clinic
                            </a>
                        </div>
                    </div>
                @else
                    <!-- Pending Message -->
                    <div class="alert alert-info">
                        <h6 class="alert-heading">
                            <i class="fas fa-clock me-2"></i>
                            What's Next?
                        </h6>
                        <p class="mb-2">Your registration is currently being reviewed. Here's what you can expect:</p>
                        <ul class="mb-2">
                            <li><strong>Review Time:</strong> 24-48 hours during business days</li>
                            <li><strong>Email Updates:</strong> You'll receive notifications about status changes</li>
                            <li><strong>Approval:</strong> Once approved, you'll get login credentials</li>
                            <li><strong>Support:</strong> Contact us if you have any questions</li>
                        </ul>
                        <hr>
                        <p class="mb-0">
                            <strong>Estimated completion:</strong> 
                            {{ $clinic->created_at->addBusinessDays(2)->format('M d, Y') }}
                        </p>
                    </div>
                @endif

                <!-- Action Buttons -->
                <div class="d-grid gap-2 d-md-flex justify-content-md-center mt-4">
                    <button onclick="window.location.reload()" class="btn btn-outline-primary me-md-2">
                        <i class="fas fa-sync-alt me-2"></i>
                        Refresh Status
                    </button>
                    <a href="{{ route('clinic-registration.check-status') }}" class="btn btn-outline-secondary me-md-2">
                        <i class="fas fa-search me-2"></i>
                        Check Another Registration
                    </a>
                    <a href="{{ url('/') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-home me-2"></i>
                        Back to Home
                    </a>
                </div>

                <!-- Contact Information -->
                <div class="mt-4 pt-3 border-top text-center">
                    <p class="text-muted small mb-0">
                        <strong>Need Help?</strong><br>
                        Email: support@concure.com | Phone: +1 (555) 123-4567<br>
                        Reference your activation code: <strong>{{ $clinic->activation_code }}</strong>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
