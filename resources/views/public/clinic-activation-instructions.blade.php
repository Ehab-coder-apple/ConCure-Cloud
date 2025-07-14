<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ConCure Clinic Activation Instructions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .step-number {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
        }
    </style>
</head>
<body class="bg-light">
    <div class="gradient-bg text-white py-5">
        <div class="container text-center">
            <h1 class="display-4 mb-3">
                <i class="fas fa-hospital me-3"></i>
                ConCure Clinic Activation
            </h1>
            <p class="lead">Welcome to ConCure Professional Clinic Management System</p>
        </div>
    </div>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-key me-2"></i>
                            How to Activate Your Clinic
                        </h4>
                    </div>
                    <div class="card-body p-4">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>You've received an activation code!</strong> Follow these simple steps to set up your clinic in ConCure.
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="d-flex align-items-start">
                                    <div class="step-number bg-primary text-white me-3">1</div>
                                    <div>
                                        <h5>Visit the Activation Page</h5>
                                        <p class="text-muted">Click the button below or visit the activation URL provided by your ConCure administrator.</p>
                                        <a href="{{ url('/activate-clinic') }}" class="btn btn-primary">
                                            <i class="fas fa-external-link-alt me-1"></i>
                                            Go to Activation Page
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <div class="d-flex align-items-start">
                                    <div class="step-number bg-warning text-dark me-3">2</div>
                                    <div>
                                        <h5>Enter Your Activation Code</h5>
                                        <p class="text-muted">Input the 15-character activation code you received (format: CLINIC-XXXXXXXX).</p>
                                        <div class="bg-light p-2 rounded font-monospace text-center">
                                            CLINIC-XXXXXXXX
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <div class="d-flex align-items-start">
                                    <div class="step-number bg-info text-white me-3">3</div>
                                    <div>
                                        <h5>Create Your Admin Account</h5>
                                        <p class="text-muted">Set up your administrator username and password. This will be your login for managing the clinic.</p>
                                        <ul class="small text-muted">
                                            <li>Choose a unique username</li>
                                            <li>Create a strong password (min. 8 characters)</li>
                                            <li>Add clinic contact information</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <div class="d-flex align-items-start">
                                    <div class="step-number bg-success text-white me-3">4</div>
                                    <div>
                                        <h5>Start Using ConCure</h5>
                                        <p class="text-muted">Once activated, you'll be automatically logged in and can start managing your clinic.</p>
                                        <ul class="small text-muted">
                                            <li>Add patients and staff</li>
                                            <li>Create prescriptions</li>
                                            <li>Schedule appointments</li>
                                            <li>Manage nutrition plans</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card border-success">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0">
                                            <i class="fas fa-star me-2"></i>
                                            What You Get with ConCure
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <ul class="list-unstyled">
                                                    <li><i class="fas fa-check text-success me-2"></i>Complete patient management</li>
                                                    <li><i class="fas fa-check text-success me-2"></i>Digital prescription system</li>
                                                    <li><i class="fas fa-check text-success me-2"></i>Appointment scheduling</li>
                                                    <li><i class="fas fa-check text-success me-2"></i>Nutrition planning tools</li>
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <ul class="list-unstyled">
                                                    <li><i class="fas fa-check text-success me-2"></i>Laboratory request management</li>
                                                    <li><i class="fas fa-check text-success me-2"></i>Multi-language support</li>
                                                    <li><i class="fas fa-check text-success me-2"></i>User role management</li>
                                                    <li><i class="fas fa-check text-success me-2"></i>Comprehensive reporting</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card border-warning">
                                    <div class="card-header bg-warning text-dark">
                                        <h6 class="mb-0">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            Important Notes
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <ul class="small mb-0">
                                            <li>Activation codes expire in 30 days</li>
                                            <li>Each code can only be used once</li>
                                            <li>Keep your admin credentials secure</li>
                                            <li>Contact support if you encounter issues</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-info">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0">
                                            <i class="fas fa-question-circle me-2"></i>
                                            Need Help?
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="small mb-2">If you need assistance:</p>
                                        <ul class="small mb-0">
                                            <li>Contact your ConCure administrator</li>
                                            <li>Check the activation code format</li>
                                            <li>Ensure the code hasn't expired</li>
                                            <li>Try refreshing the page</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <a href="{{ url('/activate-clinic') }}" class="btn btn-primary btn-lg">
                                <i class="fas fa-rocket me-2"></i>
                                Start Clinic Activation
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">
                <i class="fas fa-heart text-danger me-1"></i>
                ConCure - Professional Clinic Management System
            </p>
            <small class="text-muted">Empowering healthcare professionals worldwide</small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
