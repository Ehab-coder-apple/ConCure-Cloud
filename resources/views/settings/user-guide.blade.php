@extends('layouts.fullscreen', [
    'backUrl' => route('settings.index'),
    'backText' => __('Back to Settings')
])

@section('page-title', __('ConCure User Guide'))
@section('page-subtitle', __('Comprehensive guide for using ConCure Clinic Management System'))

@section('content')
<div class="container-fluid">
    <!-- Language Selection and Export -->
    <div class="d-flex justify-content-end align-items-center mb-4">
        <div class="me-3">
            <div class="dropdown">
                <button class="btn btn-outline-primary dropdown-toggle" type="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-globe me-2"></i>
                    <span id="currentLanguageText">English</span>
                </button>
                <ul class="dropdown-menu" aria-labelledby="languageDropdown">
                    <li><a class="dropdown-item" href="#" onclick="setGuideLanguage('en'); return false;">
                        <i class="fas fa-flag-usa me-2"></i>English
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="setGuideLanguage('ar'); return false;">
                        <i class="fas fa-flag me-2"></i>العربية (Arabic)
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="setGuideLanguage('ku-bahdeni'); return false;">
                        <i class="fas fa-flag me-2"></i>کوردی بادینی (Kurdish Bahdeni)
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="setGuideLanguage('ku-sorani'); return false;">
                        <i class="fas fa-flag me-2"></i>کوردی سۆرانی (Kurdish Sorani)
                    </a></li>
                </ul>
            </div>
        </div>
        <div>
            <button type="button" class="btn btn-primary" onclick="exportUserGuide()">
                <i class="fas fa-file-pdf me-1"></i>
                {{ __('Export PDF') }}
            </button>
        </div>
    </div>

    <!-- Language Selection Info -->
    <div class="alert alert-info mb-4">
        <i class="fas fa-info-circle me-2"></i>
        <strong>{{ __('Current Language') }}:</strong>
        <span id="currentGuideLanguage">English</span>
        <br>
        <small>{{ __('Select a language from the dropdown above to view the guide in different languages. You can export any version to PDF.') }}</small>
    </div>





    <!-- User Guide Content -->
    <div id="userGuideContent">
        <!-- English Content (Default) -->
        <div class="guide-content d-block" data-lang="en">
            <div class="row">
                <div class="col-12">
                    <div class="mb-4">
                        <h5 class="text-secondary">Table of Contents</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-unstyled ms-3">
                                    <li><a href="#getting-started" class="text-decoration-none">1. Getting Started</a></li>
                                    <li><a href="#dashboard" class="text-decoration-none">2. Dashboard Overview</a></li>
                                    <li><a href="#patients" class="text-decoration-none">3. Patient Management</a></li>
                                    <li><a href="#prescriptions" class="text-decoration-none">4. Prescriptions</a></li>
                                    <li><a href="#appointments" class="text-decoration-none">5. Appointments</a></li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled ms-3">
                                    <li><a href="#radiology" class="text-decoration-none">6. Radiology Requests</a></li>
                                    <li><a href="#lab-requests" class="text-decoration-none">7. Lab Requests</a></li>
                                    <li><a href="#nutrition" class="text-decoration-none">8. Nutrition Plans</a></li>
                                    <li><a href="#finance" class="text-decoration-none">9. Financial Management</a></li>
                                    <li><a href="#settings" class="text-decoration-none">10. Settings & Configuration</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Getting Started -->
                    <div class="card mb-4" id="getting-started">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-play-circle me-2"></i>
                                1. Getting Started
                            </h6>
                        </div>
                        <div class="card-body">
                            <h6 class="text-primary">System Requirements</h6>
                            <ul>
                                <li>Modern web browser (Chrome, Firefox, Safari, Edge)</li>
                                <li>Internet connection</li>
                                <li>Screen resolution: 1024x768 or higher</li>
                            </ul>

                            <h6 class="text-primary mt-3">First Login</h6>
                            <ol>
                                <li>Open your web browser</li>
                                <li>Navigate to your ConCure URL</li>
                                <li>Enter your username and password</li>
                                <li>Click "Login" to access the system</li>
                            </ol>

                            <h6 class="text-primary mt-3">Navigation Basics</h6>
                            <ul>
                                <li><strong>Sidebar:</strong> Main navigation menu on the left</li>
                                <li><strong>Dashboard:</strong> Overview of clinic activities</li>
                                <li><strong>Quick Actions:</strong> Fast access to common tasks</li>
                                <li><strong>User Menu:</strong> Profile and logout options (top right)</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Dashboard -->
                    <div class="card mb-4" id="dashboard">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                2. Dashboard Overview
                            </h6>
                        </div>
                        <div class="card-body">
                            <h6 class="text-primary">Statistics Cards</h6>
                            <ul>
                                <li><strong>Total Patients:</strong> Number of registered patients</li>
                                <li><strong>Active Prescriptions:</strong> Current prescriptions</li>
                                <li><strong>Today's Appointments:</strong> Scheduled appointments</li>
                                <li><strong>Nutrition Plans:</strong> Active nutrition plans</li>
                            </ul>

                            <h6 class="text-primary mt-3">Quick Actions</h6>
                            <p>Use Quick Actions for common tasks:</p>
                            <ul>
                                <li><strong>Add Patient:</strong> Register new patients</li>
                                <li><strong>New Prescription:</strong> Create prescriptions</li>
                                <li><strong>New Appointment:</strong> Schedule appointments</li>
                                <li><strong>Radiology Request:</strong> Order imaging studies</li>
                                <li><strong>Lab Request:</strong> Order laboratory tests</li>
                                <li><strong>Nutrition Plan:</strong> Create diet plans</li>
                            </ul>

                            <h6 class="text-primary mt-3">Recent Activity</h6>
                            <p>Monitor recent system activities and changes made by users.</p>
                        </div>
                    </div>

                    <!-- Patient Management -->
                    <div class="card mb-4" id="patients">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-users me-2"></i>
                                3. Patient Management
                            </h6>
                        </div>
                        <div class="card-body">
                            <h6 class="text-primary">Adding New Patients</h6>
                            <ol>
                                <li>Click "Add Patient" from Quick Actions or sidebar</li>
                                <li>Fill in required information:
                                    <ul>
                                        <li>Personal details (name, age, gender)</li>
                                        <li>Contact information (phone, email)</li>
                                        <li>Medical information (allergies, conditions)</li>
                                    </ul>
                                </li>
                                <li>Click "Save Patient" to register</li>
                            </ol>

                            <h6 class="text-primary mt-3">Patient Search</h6>
                            <ul>
                                <li>Use the search bar to find patients by name or ID</li>
                                <li>Filter by age, gender, or medical conditions</li>
                                <li>Sort by registration date or last visit</li>
                            </ul>

                            <h6 class="text-primary mt-3">Patient Records</h6>
                            <ul>
                                <li>View complete medical history</li>
                                <li>Access previous prescriptions and treatments</li>
                                <li>Review appointment history</li>
                                <li>Update patient information as needed</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Prescriptions -->
                    <div class="card mb-4" id="prescriptions">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0">
                                <i class="fas fa-prescription me-2"></i>
                                4. Prescriptions
                            </h6>
                        </div>
                        <div class="card-body">
                            <h6 class="text-primary">Creating Prescriptions</h6>
                            <ol>
                                <li>Select patient from the patient list</li>
                                <li>Click "New Prescription" button</li>
                                <li>Add medications:
                                    <ul>
                                        <li>Search and select medicines</li>
                                        <li>Set dosage and frequency</li>
                                        <li>Add special instructions</li>
                                    </ul>
                                </li>
                                <li>Review and save prescription</li>
                                <li>Print or send electronically</li>
                            </ol>

                            <h6 class="text-primary mt-3">Prescription Templates</h6>
                            <ul>
                                <li>Create templates for common prescriptions</li>
                                <li>Save time with frequently used combinations</li>
                                <li>Customize templates for different conditions</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Appointments -->
                    <div class="card mb-4" id="appointments">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-calendar me-2"></i>
                                5. Appointments
                            </h6>
                        </div>
                        <div class="card-body">
                            <h6 class="text-primary">Scheduling Appointments</h6>
                            <ol>
                                <li>Click "New Appointment" from Quick Actions</li>
                                <li>Select patient (or create new patient)</li>
                                <li>Choose date and time</li>
                                <li>Select appointment type</li>
                                <li>Add notes if needed</li>
                                <li>Save appointment</li>
                            </ol>

                            <h6 class="text-primary mt-3">Calendar View</h6>
                            <ul>
                                <li>View appointments in daily, weekly, or monthly format</li>
                                <li>Drag and drop to reschedule</li>
                                <li>Color-coded by appointment type</li>
                                <li>Quick access to patient information</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Radiology -->
                    <div class="card mb-4" id="radiology">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-x-ray me-2"></i>
                                6. Radiology Requests
                            </h6>
                        </div>
                        <div class="card-body">
                            <h6 class="text-primary">Creating Radiology Requests</h6>
                            <ol>
                                <li>Select patient</li>
                                <li>Choose imaging type (X-ray, CT, MRI, etc.)</li>
                                <li>Select body part/region</li>
                                <li>Add clinical indication</li>
                                <li>Set priority level</li>
                                <li>Submit request</li>
                            </ol>

                            <h6 class="text-primary mt-3">Managing Custom Tests</h6>
                            <ul>
                                <li>Create custom radiology tests</li>
                                <li>Organize tests by categories</li>
                                <li>Set preparation instructions</li>
                                <li>Define duration and requirements</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Lab Requests -->
                    <div class="card mb-4" id="lab-requests">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-vial me-2"></i>
                                7. Lab Requests
                            </h6>
                        </div>
                        <div class="card-body">
                            <h6 class="text-primary">Creating Lab Requests</h6>
                            <ol>
                                <li>Navigate to "Lab Requests" from the sidebar</li>
                                <li>Click "New Lab Request" button</li>
                                <li>Select patient from the dropdown</li>
                                <li>Choose laboratory tests:
                                    <ul>
                                        <li>Blood tests (CBC, Chemistry panel, etc.)</li>
                                        <li>Urine tests</li>
                                        <li>Microbiology tests</li>
                                        <li>Hormone tests</li>
                                        <li>Custom tests</li>
                                    </ul>
                                </li>
                                <li>Set priority level (Routine, Urgent, STAT)</li>
                                <li>Add clinical notes and indications</li>
                                <li>Submit the request</li>
                            </ol>

                            <h6 class="text-primary mt-3">Managing Lab Results</h6>
                            <ul>
                                <li>View pending lab requests</li>
                                <li>Track request status (Pending, In Progress, Completed)</li>
                                <li>Upload and attach result files</li>
                                <li>Add interpretation notes</li>
                                <li>Flag abnormal results for review</li>
                                <li>Print lab reports</li>
                            </ul>

                            <h6 class="text-primary mt-3">Lab Request Templates</h6>
                            <ul>
                                <li>Create templates for common test combinations</li>
                                <li>Save frequently ordered test panels</li>
                                <li>Customize templates by specialty</li>
                                <li>Share templates with team members</li>
                            </ul>

                            <h6 class="text-primary mt-3">Integration Features</h6>
                            <ul>
                                <li>Connect with external laboratory systems</li>
                                <li>Automatic result import (when available)</li>
                                <li>Barcode generation for specimen tracking</li>
                                <li>Electronic result delivery</li>
                            </ul>
                        </div>
                    </div>

                    <div class="card mb-4" id="nutrition">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-apple-alt me-2"></i>
                                8. Nutrition Plans
                            </h6>
                        </div>
                        <div class="card-body">
                            <h6 class="text-primary">Creating Nutrition Plans</h6>
                            <ol>
                                <li>Go to "Nutrition Plans" from the sidebar</li>
                                <li>Click "New Nutrition Plan" button</li>
                                <li>Select patient and assess their needs</li>
                                <li>Set plan parameters:
                                    <ul>
                                        <li>Duration (days, weeks, months)</li>
                                        <li>Caloric requirements</li>
                                        <li>Dietary restrictions and allergies</li>
                                        <li>Health goals (weight loss, gain, maintenance)</li>
                                        <li>Medical conditions (diabetes, hypertension, etc.)</li>
                                    </ul>
                                </li>
                                <li>Design meal plans:
                                    <ul>
                                        <li>Breakfast, lunch, dinner options</li>
                                        <li>Snack recommendations</li>
                                        <li>Portion sizes and servings</li>
                                        <li>Alternative food choices</li>
                                    </ul>
                                </li>
                                <li>Add nutritional guidelines and tips</li>
                                <li>Save and assign to patient</li>
                            </ol>

                            <h6 class="text-primary mt-3">Nutrition Plan Templates</h6>
                            <ul>
                                <li>Pre-designed plans for common conditions:
                                    <ul>
                                        <li>Diabetic diet plans</li>
                                        <li>Heart-healthy diets</li>
                                        <li>Weight management plans</li>
                                        <li>Post-surgery nutrition</li>
                                        <li>Pediatric nutrition plans</li>
                                    </ul>
                                </li>
                                <li>Customizable meal templates</li>
                                <li>Seasonal menu variations</li>
                                <li>Cultural and religious dietary considerations</li>
                            </ul>

                            <h6 class="text-primary mt-3">Monitoring and Follow-up</h6>
                            <ul>
                                <li>Track patient progress and compliance</li>
                                <li>Schedule nutrition consultations</li>
                                <li>Monitor weight and health indicators</li>
                                <li>Adjust plans based on results</li>
                                <li>Generate progress reports</li>
                                <li>Set reminders for plan reviews</li>
                            </ul>

                            <h6 class="text-primary mt-3">Educational Resources</h6>
                            <ul>
                                <li>Nutritional information database</li>
                                <li>Food exchange lists</li>
                                <li>Cooking tips and healthy recipes</li>
                                <li>Exercise recommendations</li>
                                <li>Patient education materials</li>
                            </ul>
                        </div>
                    </div>

                    <div class="card mb-4" id="finance">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0">
                                <i class="fas fa-dollar-sign me-2"></i>
                                9. Financial Management
                            </h6>
                        </div>
                        <div class="card-body">
                            <h6 class="text-primary">Billing and Invoicing</h6>
                            <ol>
                                <li>Navigate to "Finance" from the sidebar</li>
                                <li>Create invoices for services:
                                    <ul>
                                        <li>Consultation fees</li>
                                        <li>Procedure charges</li>
                                        <li>Medication costs</li>
                                        <li>Laboratory test fees</li>
                                        <li>Radiology service charges</li>
                                    </ul>
                                </li>
                                <li>Set payment terms and due dates</li>
                                <li>Apply discounts or insurance coverage</li>
                                <li>Generate and send invoices to patients</li>
                                <li>Track payment status and follow up</li>
                            </ol>

                            <h6 class="text-primary mt-3">Payment Processing</h6>
                            <ul>
                                <li>Accept multiple payment methods:
                                    <ul>
                                        <li>Cash payments</li>
                                        <li>Credit/debit cards</li>
                                        <li>Bank transfers</li>
                                        <li>Insurance claims</li>
                                        <li>Installment plans</li>
                                    </ul>
                                </li>
                                <li>Record partial payments</li>
                                <li>Generate payment receipts</li>
                                <li>Handle refunds and adjustments</li>
                                <li>Manage outstanding balances</li>
                            </ul>

                            <h6 class="text-primary mt-3">Financial Reporting</h6>
                            <ul>
                                <li>Daily, weekly, monthly revenue reports</li>
                                <li>Outstanding payments summary</li>
                                <li>Service-wise income analysis</li>
                                <li>Patient payment history</li>
                                <li>Insurance claim tracking</li>
                                <li>Tax reporting and documentation</li>
                                <li>Profit and loss statements</li>
                            </ul>

                            <h6 class="text-primary mt-3">Insurance Management</h6>
                            <ul>
                                <li>Maintain insurance provider database</li>
                                <li>Verify patient insurance coverage</li>
                                <li>Submit electronic claims</li>
                                <li>Track claim status and approvals</li>
                                <li>Handle claim rejections and appeals</li>
                                <li>Manage co-payments and deductibles</li>
                            </ul>

                            <h6 class="text-primary mt-3">Expense Tracking</h6>
                            <ul>
                                <li>Record clinic operational expenses</li>
                                <li>Track inventory and supply costs</li>
                                <li>Monitor staff salaries and benefits</li>
                                <li>Manage equipment and maintenance costs</li>
                                <li>Categorize expenses for tax purposes</li>
                            </ul>
                        </div>
                    </div>

                    <div class="card mb-4" id="settings">
                        <div class="card-header bg-dark text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-cog me-2"></i>
                                10. Settings & Configuration
                            </h6>
                        </div>
                        <div class="card-body">
                            <h6 class="text-primary">User Profile Management</h6>
                            <ul>
                                <li>Update personal information and contact details</li>
                                <li>Change password and security settings</li>
                                <li>Set profile picture and display preferences</li>
                                <li>Configure notification preferences</li>
                                <li>Manage two-factor authentication</li>
                            </ul>

                            <h6 class="text-primary mt-3">General System Settings</h6>
                            <ul>
                                <li>System language and localization</li>
                                <li>Date and time format preferences</li>
                                <li>Currency settings for billing</li>
                                <li>Time zone configuration</li>
                                <li>Default page sizes and display options</li>
                                <li>System backup and maintenance schedules</li>
                            </ul>

                            <h6 class="text-primary mt-3">Clinic Information</h6>
                            <ul>
                                <li>Clinic name, address, and contact information</li>
                                <li>Operating hours and holiday schedules</li>
                                <li>Clinic logo and branding settings</li>
                                <li>License numbers and certifications</li>
                                <li>Emergency contact information</li>
                                <li>Clinic specialties and services offered</li>
                            </ul>

                            <h6 class="text-primary mt-3">User Management</h6>
                            <ul>
                                <li>Add, edit, and deactivate user accounts</li>
                                <li>Assign roles and permissions:
                                    <ul>
                                        <li>Administrator - Full system access</li>
                                        <li>Doctor - Patient care and medical records</li>
                                        <li>Nurse - Patient care and basic records</li>
                                        <li>Receptionist - Appointments and basic patient info</li>
                                        <li>Accountant - Financial records and billing</li>
                                    </ul>
                                </li>
                                <li>Monitor user activity and login history</li>
                                <li>Set password policies and security requirements</li>
                                <li>Manage user groups and departments</li>
                            </ul>

                            <h6 class="text-primary mt-3">System Settings</h6>
                            <ul>
                                <li>Database backup and restore options</li>
                                <li>System performance monitoring</li>
                                <li>Error logging and troubleshooting</li>
                                <li>Integration settings for external systems</li>
                                <li>API configuration and access tokens</li>
                                <li>Security settings and access controls</li>
                                <li>Audit trail configuration</li>
                            </ul>

                            <h6 class="text-primary mt-3">Customization Options</h6>
                            <ul>
                                <li>Custom fields for patient records</li>
                                <li>Prescription templates and formats</li>
                                <li>Report templates and layouts</li>
                                <li>Email templates for notifications</li>
                                <li>Custom forms and questionnaires</li>
                                <li>Workflow automation rules</li>
                            </ul>

                            <h6 class="text-primary mt-3">Data Management</h6>
                            <ul>
                                <li>Data import and export utilities</li>
                                <li>Patient data migration tools</li>
                                <li>Data archiving and retention policies</li>
                                <li>GDPR compliance and privacy settings</li>
                                <li>Data encryption and security measures</li>
                                <li>Regular data cleanup and optimization</li>
                            </ul>

                            <h6 class="text-primary mt-3">Help and Support</h6>
                            <ul>
                                <li>Access to user documentation and guides</li>
                                <li>Contact information for technical support</li>
                                <li>System update notifications</li>
                                <li>Feature request and feedback submission</li>
                                <li>Training resources and video tutorials</li>
                                <li>Community forums and user groups</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Arabic Content -->
        <div class="guide-content d-none" data-lang="ar" dir="rtl">
            <div class="row">
                <div class="col-12">
                    <div class="mb-4">
                        <h5 class="text-secondary">جدول المحتويات</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-unstyled ms-3">
                                    <li><a href="#getting-started-ar" class="text-decoration-none">١. البدء</a></li>
                                    <li><a href="#dashboard-ar" class="text-decoration-none">٢. نظرة عامة على لوحة التحكم</a></li>
                                    <li><a href="#patients-ar" class="text-decoration-none">٣. إدارة المرضى</a></li>
                                    <li><a href="#prescriptions-ar" class="text-decoration-none">٤. الوصفات الطبية</a></li>
                                    <li><a href="#appointments-ar" class="text-decoration-none">٥. المواعيد</a></li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled ms-3">
                                    <li><a href="#radiology-ar" class="text-decoration-none">٦. طلبات الأشعة</a></li>
                                    <li><a href="#lab-requests-ar" class="text-decoration-none">٧. طلبات المختبر</a></li>
                                    <li><a href="#nutrition-ar" class="text-decoration-none">٨. خطط التغذية</a></li>
                                    <li><a href="#finance-ar" class="text-decoration-none">٩. الإدارة المالية</a></li>
                                    <li><a href="#settings-ar" class="text-decoration-none">١٠. الإعدادات والتكوين</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Arabic Getting Started -->
                    <div class="card mb-4" id="getting-started-ar">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-play-circle me-2"></i>
                                ١. البدء
                            </h6>
                        </div>
                        <div class="card-body">
                            <h6 class="text-primary">متطلبات النظام</h6>
                            <ul>
                                <li>متصفح ويب حديث (Chrome، Firefox، Safari، Edge)</li>
                                <li>اتصال بالإنترنت</li>
                                <li>دقة الشاشة: ١٠٢٤×٧٦٨ أو أعلى</li>
                            </ul>

                            <h6 class="text-primary mt-3">تسجيل الدخول الأول</h6>
                            <ol>
                                <li>افتح متصفح الويب</li>
                                <li>انتقل إلى رابط ConCure الخاص بك</li>
                                <li>أدخل اسم المستخدم وكلمة المرور</li>
                                <li>انقر على "تسجيل الدخول" للوصول إلى النظام</li>
                            </ol>

                            <h6 class="text-primary mt-3">أساسيات التنقل</h6>
                            <ul>
                                <li><strong>الشريط الجانبي:</strong> قائمة التنقل الرئيسية على اليسار</li>
                                <li><strong>لوحة التحكم:</strong> نظرة عامة على أنشطة العيادة</li>
                                <li><strong>الإجراءات السريعة:</strong> وصول سريع للمهام الشائعة</li>
                                <li><strong>قائمة المستخدم:</strong> خيارات الملف الشخصي وتسجيل الخروج (أعلى اليمين)</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Arabic Dashboard -->
                    <div class="card mb-4" id="dashboard-ar">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                ٢. نظرة عامة على لوحة التحكم
                            </h6>
                        </div>
                        <div class="card-body">
                            <h6 class="text-primary">بطاقات الإحصائيات</h6>
                            <ul>
                                <li><strong>إجمالي المرضى:</strong> عدد المرضى المسجلين</li>
                                <li><strong>الوصفات النشطة:</strong> الوصفات الحالية</li>
                                <li><strong>مواعيد اليوم:</strong> المواعيد المجدولة</li>
                                <li><strong>خطط التغذية:</strong> خطط التغذية النشطة</li>
                            </ul>

                            <h6 class="text-primary mt-3">الإجراءات السريعة</h6>
                            <p>استخدم الإجراءات السريعة للمهام الشائعة:</p>
                            <ul>
                                <li><strong>إضافة مريض:</strong> تسجيل مرضى جدد</li>
                                <li><strong>وصفة جديدة:</strong> إنشاء وصفات طبية</li>
                                <li><strong>موعد جديد:</strong> جدولة المواعيد</li>
                                <li><strong>طلب أشعة:</strong> طلب دراسات التصوير</li>
                                <li><strong>طلب مختبر:</strong> طلب فحوصات مخبرية</li>
                                <li><strong>خطة تغذية:</strong> إنشاء خطط غذائية</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Arabic Prescriptions -->
                    <div class="card mb-4" id="prescriptions-ar">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0">
                                <i class="fas fa-prescription me-2"></i>
                                ٤. الوصفات الطبية
                            </h6>
                        </div>
                        <div class="card-body">
                            <h6 class="text-primary">إنشاء الوصفات</h6>
                            <ol>
                                <li>اختر المريض من قائمة المرضى</li>
                                <li>انقر على زر "وصفة جديدة"</li>
                                <li>أضف الأدوية:
                                    <ul>
                                        <li>ابحث واختر الأدوية</li>
                                        <li>حدد الجرعة والتكرار</li>
                                        <li>أضف تعليمات خاصة</li>
                                    </ul>
                                </li>
                                <li>راجع واحفظ الوصفة</li>
                                <li>اطبع أو أرسل إلكترونياً</li>
                            </ol>

                            <h6 class="text-primary mt-3">قوالب الوصفات</h6>
                            <ul>
                                <li>إنشاء قوالب للوصفات الشائعة</li>
                                <li>توفير الوقت مع التركيبات المستخدمة بكثرة</li>
                                <li>تخصيص القوالب لحالات مختلفة</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Arabic Patient Management -->
                    <div class="card mb-4" id="patients-ar">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-users me-2"></i>
                                ٣. إدارة المرضى
                            </h6>
                        </div>
                        <div class="card-body">
                            <h6 class="text-primary">إضافة مريض جديد</h6>
                            <ol>
                                <li>انقر على "المرضى" في الشريط الجانبي</li>
                                <li>اختر "إضافة مريض جديد"</li>
                                <li>املأ المعلومات الأساسية (الاسم، العمر، الجنس)</li>
                                <li>أضف معلومات الاتصال والعنوان</li>
                                <li>احفظ بيانات المريض</li>
                            </ol>

                            <h6 class="text-primary mt-3">البحث عن المرضى</h6>
                            <ul>
                                <li>استخدم شريط البحث للعثور على المرضى</li>
                                <li>ابحث بالاسم أو رقم الهاتف أو رقم المريض</li>
                                <li>استخدم الفلاتر لتضييق النتائج</li>
                            </ul>

                            <h6 class="text-primary mt-3">تحديث بيانات المريض</h6>
                            <ul>
                                <li>افتح ملف المريض</li>
                                <li>انقر على "تعديل المعلومات"</li>
                                <li>قم بالتحديثات المطلوبة</li>
                                <li>احفظ التغييرات</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Arabic Appointments -->
                    <div class="card mb-4" id="appointments-ar">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-calendar-alt me-2"></i>
                                ٥. المواعيد
                            </h6>
                        </div>
                        <div class="card-body">
                            <h6 class="text-primary">حجز موعد جديد</h6>
                            <ol>
                                <li>انتقل إلى قسم "المواعيد"</li>
                                <li>انقر على "موعد جديد"</li>
                                <li>اختر المريض من القائمة</li>
                                <li>حدد التاريخ والوقت المناسب</li>
                                <li>أضف ملاحظات إذا لزم الأمر</li>
                                <li>احفظ الموعد</li>
                            </ol>

                            <h6 class="text-primary mt-3">إدارة المواعيد</h6>
                            <ul>
                                <li>عرض المواعيد اليومية والأسبوعية</li>
                                <li>تعديل أو إلغاء المواعيد الموجودة</li>
                                <li>إرسال تذكيرات للمرضى</li>
                                <li>تتبع حالة المواعيد (مؤكد، ملغى، مكتمل)</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Arabic Radiology -->
                    <div class="card mb-4" id="radiology-ar">
                        <div class="card-header bg-dark text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-x-ray me-2"></i>
                                ٦. طلبات الأشعة
                            </h6>
                        </div>
                        <div class="card-body">
                            <h6 class="text-primary">إنشاء طلب أشعة</h6>
                            <ol>
                                <li>افتح ملف المريض</li>
                                <li>انتقل إلى قسم "الأشعة"</li>
                                <li>انقر على "طلب جديد"</li>
                                <li>اختر نوع الأشعة المطلوبة</li>
                                <li>أضف التفاصيل والملاحظات</li>
                                <li>احفظ الطلب</li>
                            </ol>

                            <h6 class="text-primary mt-3">متابعة النتائج</h6>
                            <ul>
                                <li>تتبع حالة طلبات الأشعة</li>
                                <li>استلام النتائج والتقارير</li>
                                <li>ربط النتائج بملف المريض</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Arabic Lab Requests -->
                    <div class="card mb-4" id="lab-requests-ar">
                        <div class="card-header bg-danger text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-flask me-2"></i>
                                ٧. طلبات المختبر
                            </h6>
                        </div>
                        <div class="card-body">
                            <h6 class="text-primary">إنشاء طلب مختبر</h6>
                            <ol>
                                <li>افتح ملف المريض</li>
                                <li>انتقل إلى قسم "المختبر"</li>
                                <li>اختر "طلب جديد"</li>
                                <li>حدد الفحوصات المطلوبة</li>
                                <li>أضف تعليمات خاصة</li>
                                <li>احفظ الطلب</li>
                            </ol>

                            <h6 class="text-primary mt-3">إدارة النتائج</h6>
                            <ul>
                                <li>استلام نتائج الفحوصات</li>
                                <li>مراجعة القيم والمؤشرات</li>
                                <li>إضافة تفسيرات طبية</li>
                                <li>مشاركة النتائج مع المريض</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Arabic Nutrition -->
                    <div class="card mb-4" id="nutrition-ar">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-apple-alt me-2"></i>
                                ٨. خطط التغذية
                            </h6>
                        </div>
                        <div class="card-body">
                            <h6 class="text-primary">إنشاء خطة تغذية</h6>
                            <ol>
                                <li>افتح ملف المريض</li>
                                <li>انتقل إلى قسم "التغذية"</li>
                                <li>انقر على "خطة جديدة"</li>
                                <li>أدخل الوزن والطول والعمر</li>
                                <li>حدد الأهداف الغذائية</li>
                                <li>اختر الأطعمة المناسبة</li>
                                <li>احفظ الخطة</li>
                            </ol>

                            <h6 class="text-primary mt-3">متابعة التقدم</h6>
                            <ul>
                                <li>تسجيل الوزن بانتظام</li>
                                <li>مراقبة الالتزام بالخطة</li>
                                <li>تعديل الخطة حسب الحاجة</li>
                                <li>تقييم النتائج</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Arabic Finance -->
                    <div class="card mb-4" id="finance-ar">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0">
                                <i class="fas fa-dollar-sign me-2"></i>
                                ٩. الإدارة المالية
                            </h6>
                        </div>
                        <div class="card-body">
                            <h6 class="text-primary">إدارة الفواتير</h6>
                            <ol>
                                <li>إنشاء فاتورة جديدة للمريض</li>
                                <li>إضافة الخدمات والأسعار</li>
                                <li>تطبيق الخصومات إذا لزم الأمر</li>
                                <li>إرسال الفاتورة للمريض</li>
                                <li>تتبع حالة الدفع</li>
                            </ol>

                            <h6 class="text-primary mt-3">التقارير المالية</h6>
                            <ul>
                                <li>تقارير الإيرادات اليومية والشهرية</li>
                                <li>تحليل الخدمات الأكثر ربحية</li>
                                <li>متابعة المدفوعات المعلقة</li>
                                <li>إحصائيات الأداء المالي</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Arabic Settings -->
                    <div class="card mb-4" id="settings-ar">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-cog me-2"></i>
                                ١٠. الإعدادات والتكوين
                            </h6>
                        </div>
                        <div class="card-body">
                            <h6 class="text-primary">إعدادات العيادة</h6>
                            <ul>
                                <li>تحديث معلومات العيادة</li>
                                <li>إدارة المستخدمين والصلاحيات</li>
                                <li>تخصيص واجهة النظام</li>
                                <li>إعداد النسخ الاحتياطي</li>
                            </ul>

                            <h6 class="text-primary mt-3">إعدادات النظام</h6>
                            <ul>
                                <li>تكوين الطابعات</li>
                                <li>إعدادات الأمان</li>
                                <li>تخصيص التقارير</li>
                                <li>إدارة قواعد البيانات</li>
                            </ul>

                            <h6 class="text-primary mt-3">الدعم والمساعدة</h6>
                            <ul>
                                <li>الاتصال بالدعم الفني</li>
                                <li>تحديثات النظام</li>
                                <li>دليل المستخدم</li>
                                <li>التدريب والموارد</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kurdish Bahdeni Content -->
        <div class="guide-content d-none" data-lang="ku-bahdeni" dir="rtl">
            <div class="row">
                <div class="col-12">
                    <div class="mb-4">
                        <h5 class="text-secondary">نێوەڕۆکەکان</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-unstyled ms-3">
                                    <li><a href="#getting-started-kb" class="text-decoration-none">١. دەستپێکردن</a></li>
                                    <li><a href="#dashboard-kb" class="text-decoration-none">٢. سەرەکی</a></li>
                                    <li><a href="#patients-kb" class="text-decoration-none">٣. بەڕێوەبردنی نەخۆش</a></li>
                                    <li><a href="#prescriptions-kb" class="text-decoration-none">٤. ڕەچەتەکان</a></li>
                                    <li><a href="#appointments-kb" class="text-decoration-none">٥. کاتی چاوپێکەوتن</a></li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled ms-3">
                                    <li><a href="#radiology-kb" class="text-decoration-none">٦. داواکاری تیشک</a></li>
                                    <li><a href="#lab-requests-kb" class="text-decoration-none">٧. داواکاری تاقیگە</a></li>
                                    <li><a href="#nutrition-kb" class="text-decoration-none">٨. پلانی خۆراک</a></li>
                                    <li><a href="#finance-kb" class="text-decoration-none">٩. بەڕێوەبردنی دارایی</a></li>
                                    <li><a href="#settings-kb" class="text-decoration-none">١٠. ڕێکخستنەکان</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Kurdish Bahdeni Getting Started -->
                    <div class="card mb-4" id="getting-started-kb">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-play-circle me-2"></i>
                                ١. دەستپێکردن
                            </h6>
                        </div>
                        <div class="card-body">
                            <h6 class="text-primary">پێداویستیەکانی سیستەم</h6>
                            <ul>
                                <li>وێبگەڕی نوێ (Chrome، Firefox، Safari، Edge)</li>
                                <li>پەیوەندی ئینتەرنێت</li>
                                <li>ڕوونی شاشە: ١٠٢٤×٧٦٨ یان زیاتر</li>
                            </ul>

                            <h6 class="text-primary mt-3">یەکەم جار چوونە ژوورەوە</h6>
                            <ol>
                                <li>وێبگەڕ بکەرەوە</li>
                                <li>بڕۆ بۆ لینکی ConCure</li>
                                <li>ناوی بەکارهێنەر و وشەی نهێنی بنووسە</li>
                                <li>کلیک لە "چوونە ژوورەوە" بکە</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Kurdish Bahdeni Dashboard -->
                    <div class="card mb-4" id="dashboard-kb">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                ٢. سەرەکی
                            </h6>
                        </div>
                        <div class="card-body">
                            <h6 class="text-primary">بینینی گشتی</h6>
                            <ul>
                                <li>ئاماری ڕۆژانە</li>
                                <li>نەخۆشە نوێکان</li>
                                <li>کاتی چاوپێکەوتنی ئەمڕۆ</li>
                                <li>ڕەچەتە نوێکان</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Kurdish Bahdeni Patients -->
                    <div class="card mb-4" id="patients-kb">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-users me-2"></i>
                                ٣. بەڕێوەبردنی نەخۆش
                            </h6>
                        </div>
                        <div class="card-body">
                            <h6 class="text-primary">زیادکردنی نەخۆشی نوێ</h6>
                            <ol>
                                <li>کلیک لە "نەخۆشان" بکە</li>
                                <li>"نەخۆشی نوێ زیاد بکە" هەڵبژێرە</li>
                                <li>زانیاری بنەڕەتی پڕ بکەرەوە</li>
                                <li>زانیاری پەیوەندی زیاد بکە</li>
                                <li>زانیارییەکان پاشەکەوت بکە</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Kurdish Bahdeni Prescriptions -->
                    <div class="card mb-4" id="prescriptions-kb">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0">
                                <i class="fas fa-prescription me-2"></i>
                                ٤. ڕەچەتەکان
                            </h6>
                        </div>
                        <div class="card-body">
                            <h6 class="text-primary">دروستکردنی ڕەچەتەی نوێ</h6>
                            <ol>
                                <li>فایلی نەخۆش بکەرەوە</li>
                                <li>بڕۆ بۆ بەشی "ڕەچەتەکان"</li>
                                <li>کلیک لە "ڕەچەتەی نوێ" بکە</li>
                                <li>دەرمانەکان هەڵبژێرە</li>
                                <li>ڕێنمایی و بڕ زیاد بکە</li>
                                <li>ڕەچەتە پاشەکەوت بکە</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Kurdish Bahdeni Appointments -->
                    <div class="card mb-4" id="appointments-kb">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-calendar-alt me-2"></i>
                                ٥. کاتی چاوپێکەوتن
                            </h6>
                        </div>
                        <div class="card-body">
                            <h6 class="text-primary">کاتی چاوپێکەوتنی نوێ</h6>
                            <ol>
                                <li>بڕۆ بۆ "کاتی چاوپێکەوتن"</li>
                                <li>"کاتی نوێ" هەڵبژێرە</li>
                                <li>نەخۆش هەڵبژێرە</li>
                                <li>ڕێکەوت و کات دیاری بکە</li>
                                <li>تێبینی زیاد بکە</li>
                                <li>پاشەکەوت بکە</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Kurdish Bahdeni Radiology -->
                    <div class="card mb-4" id="radiology-kb">
                        <div class="card-header bg-dark text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-x-ray me-2"></i>
                                ٦. داواکاری تیشک
                            </h6>
                        </div>
                        <div class="card-body">
                            <h6 class="text-primary">داواکاری تیشکی نوێ</h6>
                            <ol>
                                <li>فایلی نەخۆش بکەرەوە</li>
                                <li>بڕۆ بۆ بەشی "تیشک"</li>
                                <li>"داواکاری نوێ" هەڵبژێرە</li>
                                <li>جۆری تیشک دیاری بکە</li>
                                <li>وردەکاری زیاد بکە</li>
                                <li>پاشەکەوت بکە</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Kurdish Bahdeni Lab Requests -->
                    <div class="card mb-4" id="lab-requests-kb">
                        <div class="card-header bg-danger text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-flask me-2"></i>
                                ٧. داواکاری تاقیگە
                            </h6>
                        </div>
                        <div class="card-body">
                            <h6 class="text-primary">داواکاری تاقیگەی نوێ</h6>
                            <ol>
                                <li>فایلی نەخۆش بکەرەوە</li>
                                <li>بڕۆ بۆ بەشی "تاقیگە"</li>
                                <li>"داواکاری نوێ" هەڵبژێرە</li>
                                <li>شیکردنەوەکان هەڵبژێرە</li>
                                <li>ڕێنمایی زیاد بکە</li>
                                <li>پاشەکەوت بکە</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Kurdish Bahdeni Nutrition -->
                    <div class="card mb-4" id="nutrition-kb">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-apple-alt me-2"></i>
                                ٨. پلانی خۆراک
                            </h6>
                        </div>
                        <div class="card-body">
                            <h6 class="text-primary">پلانی خۆراکی نوێ</h6>
                            <ol>
                                <li>فایلی نەخۆش بکەرەوە</li>
                                <li>بڕۆ بۆ بەشی "خۆراک"</li>
                                <li>"پلانی نوێ" هەڵبژێرە</li>
                                <li>کێش و باڵا تۆمار بکە</li>
                                <li>ئامانجەکان دیاری بکە</li>
                                <li>پاشەکەوت بکە</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Kurdish Bahdeni Finance -->
                    <div class="card mb-4" id="finance-kb">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0">
                                <i class="fas fa-dollar-sign me-2"></i>
                                ٩. بەڕێوەبردنی دارایی
                            </h6>
                        </div>
                        <div class="card-body">
                            <h6 class="text-primary">بەڕێوەبردنی پسوولە</h6>
                            <ol>
                                <li>پسوولەی نوێ دروست بکە</li>
                                <li>خزمەتگوزاری زیاد بکە</li>
                                <li>نرخەکان دیاری بکە</li>
                                <li>پسوولە بنێرە</li>
                                <li>پارەدان بەدوایدا بکە</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Kurdish Bahdeni Settings -->
                    <div class="card mb-4" id="settings-kb">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-cog me-2"></i>
                                ١٠. ڕێکخستنەکان
                            </h6>
                        </div>
                        <div class="card-body">
                            <h6 class="text-primary">ڕێکخستنی نەخۆشخانە</h6>
                            <ul>
                                <li>زانیاری نەخۆشخانە نوێ بکەرەوە</li>
                                <li>بەکارهێنەران بەڕێوە ببە</li>
                                <li>ڕووکاری سیستەم گۆڕ بدە</li>
                                <li>پاشەکەوتکردن ڕێک بخە</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kurdish Sorani Content -->
        <div class="guide-content d-none" data-lang="ku-sorani" dir="rtl">
            <div class="row">
                <div class="col-12">
                    <div class="mb-4">
                        <h5 class="text-secondary">نێوەڕۆکەکان</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-unstyled ms-3">
                                    <li><a href="#getting-started-ks" class="text-decoration-none">١. دەستپێکردن</a></li>
                                    <li><a href="#dashboard-ks" class="text-decoration-none">٢. سەرەکی</a></li>
                                    <li><a href="#patients-ks" class="text-decoration-none">٣. بەڕێوەبردنی نەخۆش</a></li>
                                    <li><a href="#prescriptions-ks" class="text-decoration-none">٤. ڕەچەتەکان</a></li>
                                    <li><a href="#appointments-ks" class="text-decoration-none">٥. کاتی چاوپێکەوتن</a></li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled ms-3">
                                    <li><a href="#radiology-ks" class="text-decoration-none">٦. داواکاری تیشک</a></li>
                                    <li><a href="#lab-requests-ks" class="text-decoration-none">٧. داواکاری تاقیگە</a></li>
                                    <li><a href="#nutrition-ks" class="text-decoration-none">٨. پلانی خۆراک</a></li>
                                    <li><a href="#finance-ks" class="text-decoration-none">٩. بەڕێوەبردنی دارایی</a></li>
                                    <li><a href="#settings-ks" class="text-decoration-none">١٠. ڕێکخستنەکان</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Kurdish Sorani Getting Started -->
                    <div class="card mb-4" id="getting-started-ks">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-play-circle me-2"></i>
                                ١. دەستپێکردن
                            </h6>
                        </div>
                        <div class="card-body">
                            <h6 class="text-primary">پێویستیەکانی سیستەم</h6>
                            <ul>
                                <li>وێبگەڕی نوێ (Chrome، Firefox، Safari، Edge)</li>
                                <li>پەیوەندی ئینتەرنێت</li>
                                <li>ڕوونی شاشە: ١٠٢٤×٧٦٨ یان زیاتر</li>
                            </ul>

                            <h6 class="text-primary mt-3">یەکەم جار چوونە ژوورەوە</h6>
                            <ol>
                                <li>وێبگەڕ بکەرەوە</li>
                                <li>بڕۆ بۆ لینکی ConCure</li>
                                <li>ناوی بەکارهێنەر و وشەی نهێنی بنووسە</li>
                                <li>کلیک لە "چوونە ژوورەوە" بکە</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Kurdish Sorani Dashboard -->
                    <div class="card mb-4" id="dashboard-ks">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                ٢. سەرەکی
                            </h6>
                        </div>
                        <div class="card-body">
                            <h6 class="text-primary">بینینی گشتی</h6>
                            <ul>
                                <li>ئاماری ڕۆژانە</li>
                                <li>نەخۆشە نوێکان</li>
                                <li>کاتی چاوپێکەوتنی ئەمڕۆ</li>
                                <li>ڕەچەتە نوێکان</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Kurdish Sorani Patients -->
                    <div class="card mb-4" id="patients-ks">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-users me-2"></i>
                                ٣. بەڕێوەبردنی نەخۆش
                            </h6>
                        </div>
                        <div class="card-body">
                            <h6 class="text-primary">زیادکردنی نەخۆشی نوێ</h6>
                            <ol>
                                <li>کلیک لە "نەخۆشان" بکە</li>
                                <li>"نەخۆشی نوێ زیاد بکە" هەڵبژێرە</li>
                                <li>زانیاری بنەڕەتی پڕ بکەرەوە</li>
                                <li>زانیاری پەیوەندی زیاد بکە</li>
                                <li>زانیارییەکان پاشەکەوت بکە</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Kurdish Sorani Prescriptions -->
                    <div class="card mb-4" id="prescriptions-ks">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0">
                                <i class="fas fa-prescription me-2"></i>
                                ٤. ڕەچەتەکان
                            </h6>
                        </div>
                        <div class="card-body">
                            <h6 class="text-primary">دروستکردنی ڕەچەتەی نوێ</h6>
                            <ol>
                                <li>فایلی نەخۆش بکەرەوە</li>
                                <li>بڕۆ بۆ بەشی "ڕەچەتەکان"</li>
                                <li>کلیک لە "ڕەچەتەی نوێ" بکە</li>
                                <li>دەرمانەکان هەڵبژێرە</li>
                                <li>ڕێنمایی و بڕ زیاد بکە</li>
                                <li>ڕەچەتە پاشەکەوت بکە</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Kurdish Sorani Appointments -->
                    <div class="card mb-4" id="appointments-ks">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-calendar-alt me-2"></i>
                                ٥. کاتی چاوپێکەوتن
                            </h6>
                        </div>
                        <div class="card-body">
                            <h6 class="text-primary">کاتی چاوپێکەوتنی نوێ</h6>
                            <ol>
                                <li>بڕۆ بۆ "کاتی چاوپێکەوتن"</li>
                                <li>"کاتی نوێ" هەڵبژێرە</li>
                                <li>نەخۆش هەڵبژێرە</li>
                                <li>ڕێکەوت و کات دیاری بکە</li>
                                <li>تێبینی زیاد بکە</li>
                                <li>پاشەکەوت بکە</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Kurdish Sorani Radiology -->
                    <div class="card mb-4" id="radiology-ks">
                        <div class="card-header bg-dark text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-x-ray me-2"></i>
                                ٦. داواکاری تیشک
                            </h6>
                        </div>
                        <div class="card-body">
                            <h6 class="text-primary">داواکاری تیشکی نوێ</h6>
                            <ol>
                                <li>فایلی نەخۆش بکەرەوە</li>
                                <li>بڕۆ بۆ بەشی "تیشک"</li>
                                <li>"داواکاری نوێ" هەڵبژێرە</li>
                                <li>جۆری تیشک دیاری بکە</li>
                                <li>وردەکاری زیاد بکە</li>
                                <li>پاشەکەوت بکە</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Kurdish Sorani Lab Requests -->
                    <div class="card mb-4" id="lab-requests-ks">
                        <div class="card-header bg-danger text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-flask me-2"></i>
                                ٧. داواکاری تاقیگە
                            </h6>
                        </div>
                        <div class="card-body">
                            <h6 class="text-primary">داواکاری تاقیگەی نوێ</h6>
                            <ol>
                                <li>فایلی نەخۆش بکەرەوە</li>
                                <li>بڕۆ بۆ بەشی "تاقیگە"</li>
                                <li>"داواکاری نوێ" هەڵبژێرە</li>
                                <li>شیکردنەوەکان هەڵبژێرە</li>
                                <li>ڕێنمایی زیاد بکە</li>
                                <li>پاشەکەوت بکە</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Kurdish Sorani Nutrition -->
                    <div class="card mb-4" id="nutrition-ks">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-apple-alt me-2"></i>
                                ٨. پلانی خۆراک
                            </h6>
                        </div>
                        <div class="card-body">
                            <h6 class="text-primary">پلانی خۆراکی نوێ</h6>
                            <ol>
                                <li>فایلی نەخۆش بکەرەوە</li>
                                <li>بڕۆ بۆ بەشی "خۆراک"</li>
                                <li>"پلانی نوێ" هەڵبژێرە</li>
                                <li>کێش و باڵا تۆمار بکە</li>
                                <li>ئامانجەکان دیاری بکە</li>
                                <li>پاشەکەوت بکە</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Kurdish Sorani Finance -->
                    <div class="card mb-4" id="finance-ks">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0">
                                <i class="fas fa-dollar-sign me-2"></i>
                                ٩. بەڕێوەبردنی دارایی
                            </h6>
                        </div>
                        <div class="card-body">
                            <h6 class="text-primary">بەڕێوەبردنی پسوولە</h6>
                            <ol>
                                <li>پسوولەی نوێ دروست بکە</li>
                                <li>خزمەتگوزاری زیاد بکە</li>
                                <li>نرخەکان دیاری بکە</li>
                                <li>پسوولە بنێرە</li>
                                <li>پارەدان بەدوایدا بکە</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Kurdish Sorani Settings -->
                    <div class="card mb-4" id="settings-ks">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-cog me-2"></i>
                                ١٠. ڕێکخستنەکان
                            </h6>
                        </div>
                        <div class="card-body">
                            <h6 class="text-primary">ڕێکخستنی نەخۆشخانە</h6>
                            <ul>
                                <li>زانیاری نەخۆشخانە نوێ بکەرەوە</li>
                                <li>بەکارهێنەران بەڕێوە ببە</li>
                                <li>ڕووکاری سیستەم گۆڕ بدە</li>
                                <li>پاشەکەوتکردن ڕێک بخە</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- User Guide JavaScript -->
<script src="{{ asset('js/user-guide.js') }}"></script>

@endsection
