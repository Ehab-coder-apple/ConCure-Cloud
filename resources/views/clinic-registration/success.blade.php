<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful - ConCure</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .success-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 600px;
            width: 100%;
            text-align: center;
        }
        .success-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 3rem 2rem;
        }
        .success-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }
        .step-indicator {
            display: flex;
            justify-content: center;
            margin: 2rem 0;
        }
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #28a745;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 10px;
            font-weight: bold;
            color: white;
        }
        .step.current {
            background: #667eea;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        .info-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
            margin: 1rem 0;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            padding: 12px 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-container">
            <div class="success-header">
                <div class="success-icon">
                    <i class="fas fa-check fa-3x"></i>
                </div>
                <h2 class="mb-0">Registration Successful!</h2>
                <p class="mb-0 mt-2">Your clinic registration has been submitted</p>
            </div>
            
            <div class="p-4">
                <!-- Step Indicator -->
                <div class="step-indicator">
                    <div class="step">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="step current">2</div>
                    <div class="step">3</div>
                </div>

                <div class="mb-4">
                    <h4 class="text-success mb-3">
                        <i class="fas fa-paper-plane me-2"></i>
                        What Happens Next?
                    </h4>
                </div>

                <!-- Next Steps -->
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="info-card">
                            <div class="text-primary mb-2">
                                <i class="fas fa-envelope fa-2x"></i>
                            </div>
                            <h6>Email Confirmation</h6>
                            <p class="small text-muted mb-0">
                                Check your email for a confirmation message with your registration details.
                            </p>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <div class="info-card">
                            <div class="text-warning mb-2">
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                            <h6>Review Process</h6>
                            <p class="small text-muted mb-0">
                                Our team will review your application within 24-48 hours.
                            </p>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <div class="info-card">
                            <div class="text-success mb-2">
                                <i class="fas fa-key fa-2x"></i>
                            </div>
                            <h6>Account Activation</h6>
                            <p class="small text-muted mb-0">
                                Once approved, you'll receive login credentials to access your clinic.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Important Information -->
                <div class="alert alert-info">
                    <h6 class="alert-heading">
                        <i class="fas fa-info-circle me-2"></i>
                        Important Information
                    </h6>
                    <ul class="mb-0 text-start">
                        <li>Keep your registration email safe - it contains your activation code</li>
                        <li>You can check your registration status anytime using the link below</li>
                        <li>If you don't hear from us within 48 hours, please contact support</li>
                        <li>Your account will remain inactive until approved by our team</li>
                    </ul>
                </div>

                <!-- What's Included -->
                <div class="info-card text-start">
                    <h6 class="text-primary mb-3">
                        <i class="fas fa-gift me-2"></i>
                        What's Included in Your ConCure Account
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>Patient Management</li>
                                <li><i class="fas fa-check text-success me-2"></i>Prescription System</li>
                                <li><i class="fas fa-check text-success me-2"></i>Appointment Scheduling</li>
                                <li><i class="fas fa-check text-success me-2"></i>Lab Request Management</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>Financial Management</li>
                                <li><i class="fas fa-check text-success me-2"></i>User Guide (4 Languages)</li>
                                <li><i class="fas fa-check text-success me-2"></i>Up to 10 Users</li>
                                <li><i class="fas fa-check text-success me-2"></i>24/7 Support</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-grid gap-2 d-md-flex justify-content-md-center mt-4">
                    <a href="{{ route('clinic-registration.check-status') }}" class="btn btn-primary me-md-2">
                        <i class="fas fa-search me-2"></i>
                        Check Registration Status
                    </a>
                    <a href="{{ url('/') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-home me-2"></i>
                        Back to Home
                    </a>
                </div>

                <!-- Contact Information -->
                <div class="mt-4 pt-3 border-top">
                    <p class="text-muted small mb-0">
                        <strong>Need Help?</strong><br>
                        Email: support@concure.com | Phone: +1 (555) 123-4567<br>
                        Our support team is available 24/7 to assist you.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
