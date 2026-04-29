@extends('master.layouts.app')

@section('title', 'Application Features')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-list-check me-2"></i>
                        ConCure Cloud - Complete Features List
                    </h1>
                    <p class="text-muted mb-0">Comprehensive Clinic Management System</p>
                </div>
                <div>
                    <a href="{{ route('master.features.pdf') }}" class="btn btn-danger me-2">
                        <i class="fas fa-file-pdf me-2"></i>
                        Download PDF
                    </a>
                    <button onclick="window.print()" class="btn btn-primary">
                        <i class="fas fa-print me-2"></i>
                        Print Features
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Application Overview -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Application Overview</h5>
                </div>
                <div class="card-body">
                    <p class="lead">ConCure Cloud is a comprehensive, multi-tenant SaaS clinic management system designed to streamline healthcare operations.</p>
                    <div class="row">
                        <div class="col-md-4">
                            <h6 class="text-primary"><i class="fas fa-globe me-2"></i>Multi-Language Support</h6>
                            <p>English, Arabic (العربية), Kurdish (کوردی)</p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-primary"><i class="fas fa-users-cog me-2"></i>Role-Based Access</h6>
                            <p>15 user roles with granular permissions</p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-primary"><i class="fas fa-building me-2"></i>Multi-Clinic</h6>
                            <p>Complete data isolation per clinic</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Core Modules -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-th-large me-2"></i>Core Modules</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Patient Management -->
                        <div class="col-md-6 mb-4">
                            <div class="feature-module">
                                <h5 class="text-success"><i class="fas fa-user-injured me-2"></i>Patient Management</h5>
                                <ul class="list-unstyled ms-3">
                                    <li><i class="fas fa-check text-success me-2"></i>Complete patient profiles with demographics</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Medical history tracking</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Vital signs recording (BP, HR, Temp, Weight, Height, BMI)</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Chronic conditions management</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Allergies and medication tracking</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Family history documentation</li>
                                    <li><i class="fas fa-check text-success me-2"></i>File attachments (medical reports, images)</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Patient search and filtering</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Export patient data (Excel)</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Bulk operations (delete, clear all)</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Patient timeline view</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Smart search with Select2 integration</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Prescription System -->
                        <div class="col-md-6 mb-4">
                            <div class="feature-module">
                                <h5 class="text-success"><i class="fas fa-prescription me-2"></i>Prescription System</h5>
                                <ul class="list-unstyled ms-3">
                                    <li><i class="fas fa-check text-success me-2"></i>Digital prescription creation</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Medicine database integration</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Dosage and frequency management</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Duration tracking</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Instructions and notes</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Simple prescription mode (quick entry)</li>
                                    <li><i class="fas fa-check text-success me-2"></i>PDF generation with clinic branding</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Custom Rx template support (upload clinic-branded template)</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Print functionality</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Prescription history</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Drug interaction warnings</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Prescription templates</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Multi-language prescription printing</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Auto-populated Height/Weight from latest checkup or growth measurement</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Appointments -->
                        <div class="col-md-6 mb-4">
                            <div class="feature-module">
                                <h5 class="text-success"><i class="fas fa-calendar-check me-2"></i>Appointment Management</h5>
                                <ul class="list-unstyled ms-3">
                                    <li><i class="fas fa-check text-success me-2"></i>Calendar view (daily, weekly, monthly)</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Appointment scheduling</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Doctor assignment</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Status tracking (scheduled, confirmed, completed, cancelled)</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Appointment types and reasons</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Notes and comments</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Quick appointment creation modal</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Patient search integration</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Conflict detection</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Appointment reminders</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Color-coded status indicators</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Laboratory -->
                        <div class="col-md-6 mb-4">
                            <div class="feature-module">
                                <h5 class="text-success"><i class="fas fa-flask me-2"></i>Laboratory Management</h5>
                                <ul class="list-unstyled ms-3">
                                    <li><i class="fas fa-check text-success me-2"></i>Lab test catalog</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Lab request creation</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Multiple tests per request</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Test results entry</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Normal range indicators</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Status tracking (pending, in-progress, completed)</li>
                                    <li><i class="fas fa-check text-success me-2"></i>PDF report generation</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Lab technician assignment</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Test history tracking</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Urgent test flagging</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Lab test management (add/edit/delete)</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Radiology -->
                        <div class="col-md-6 mb-4">
                            <div class="feature-module">
                                <h5 class="text-success"><i class="fas fa-x-ray me-2"></i>Radiology Management</h5>
                                <ul class="list-unstyled ms-3">
                                    <li><i class="fas fa-check text-success me-2"></i>Radiology test catalog (X-Ray, CT, MRI, Ultrasound, etc.)</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Radiology request creation</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Multiple tests per request</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Clinical indications</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Findings and impressions</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Status tracking</li>
                                    <li><i class="fas fa-check text-success me-2"></i>PDF report generation</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Radiologist assignment</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Image attachments</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Urgent request flagging</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Radiology test management</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Nutrition & Diet -->
                        <div class="col-md-6 mb-4">
                            <div class="feature-module">
                                <h5 class="text-success"><i class="fas fa-apple-alt me-2"></i>Nutrition & Diet Planning</h5>
                                <ul class="list-unstyled ms-3">
                                    <li><i class="fas fa-check text-success me-2"></i>Comprehensive food database</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Nutritional information (calories, protein, carbs, fats)</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Food groups categorization</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Diet plan creation</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Meal planning (breakfast, lunch, dinner, snacks)</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Portion size management</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Calorie tracking</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Dietary restrictions</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Food search and filtering</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Import/Export food data (Excel)</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Custom food items</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Nutrition recommendations</li>
                                    <li><i class="fas fa-check text-success me-2"></i>PDF diet plan generation</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Dental Module -->
                        <div class="col-md-6 mb-4">
                            <div class="feature-module">
                                <h5 class="text-success"><i class="fas fa-tooth me-2"></i>Dental Module</h5>
                                <ul class="list-unstyled ms-3">
                                    <li><i class="fas fa-check text-success me-2"></i>Interactive dental chart (adult & pediatric)</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Tooth-by-tooth condition tracking</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Visual tooth status indicators (color-coded)</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Dental conditions library (30+ conditions)</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Treatment planning and tracking</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Treatment history per tooth</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Canal treatment (endodontic) worksheet with FDI canal library</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Dental imaging with tooth-level linkage</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Simple and detailed chart views</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Dental chart PDF export</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Multi-tooth selection and bulk updates</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Searchable condition legend</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Tooth numbering system (FDI notation)</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Dental lab requests with external lab integration</li>
                                    <li><i class="fas fa-check text-success me-2"></i>External dental labs directory and management</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Lab request assignment to Dental Technician or CAD/CAM Designer</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Dedicated workflow for technicians/designers (restricted to assigned requests)</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Lab request result upload and completion tracking</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Pediatric Growth Chart -->
                        <div class="col-md-6 mb-4">
                            <div class="feature-module">
                                <h5 class="text-success"><i class="fas fa-child me-2"></i>Pediatric Growth Chart</h5>
                                <ul class="list-unstyled ms-3">
                                    <li><i class="fas fa-check text-success me-2"></i>WHO & CDC growth reference data</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Weight-for-age charts (0–5 years)</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Length/Height-for-age charts</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Head circumference-for-age charts</li>
                                    <li><i class="fas fa-check text-success me-2"></i>BMI-for-age charts</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Weight-for-length/height charts</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Percentile curves (3rd–97th)</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Growth measurement recording & history</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Preterm/LBW corrected age support</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Birth weight & gestational age tracking</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Growth chart PDF export with formatted layout</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Pediatric patient list with age filtering</li>
                                </ul>
                            </div>
                        </div>

                        <!-- ENT Module -->
                        <div class="col-md-6 mb-4">
                            <div class="feature-module">
                                <h5 class="text-success"><i class="fas fa-head-side-cough me-2"></i>ENT Module (Ear, Nose &amp; Throat)</h5>
                                <ul class="list-unstyled ms-3">
                                    <li><i class="fas fa-check text-success me-2"></i>Per-visit ENT encounter records (CRUD)</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Chief complaint documentation</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Ear examination (otoscopy findings)</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Nose examination (anterior/posterior rhinoscopy)</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Throat examination (oropharynx, tonsils, larynx)</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Neck examination (lymph nodes, thyroid, masses)</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Cranial nerves assessment</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Diagnosis with ICD-10 coding</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Treatment plan &amp; prescribed medications</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Follow-up date scheduling</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Patient-level ENT profile (hearing, nasal, throat issues, dizziness)</li>
                                    <li><i class="fas fa-check text-success me-2"></i>ENT-related file uploads (audiograms, scans, images)</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Per-clinic module toggle</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Audiometry & Hearing Tests -->
                        <div class="col-md-6 mb-4">
                            <div class="feature-module">
                                <h5 class="text-success"><i class="fas fa-volume-high me-2"></i>Audiometry &amp; Hearing Tests</h5>
                                <ul class="list-unstyled ms-3">
                                    <li><i class="fas fa-check text-success me-2"></i>Pure-tone audiometry (air &amp; bone conduction)</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Speech audiometry with SRT (Speech Recognition Threshold)</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Word Recognition Score (WRS) tracking</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Tympanometry results recording</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Bilateral ear data capture (left &amp; right)</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Frequency-by-frequency threshold entry (250 Hz–8 kHz)</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Hearing-loss interpretation: normal, conductive, sensorineural, mixed</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Tests linked to ENT records or standalone per patient</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Test history timeline per patient</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Date-stamped, performer-tracked test records</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Multiple test types: pure tone, speech, tympanometry, other</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Management -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-dollar-sign me-2"></i>Financial Management</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h6 class="text-info"><i class="fas fa-file-invoice me-2"></i>Invoicing</h6>
                            <ul class="list-unstyled ms-3">
                                <li><i class="fas fa-check text-info me-2"></i>Invoice creation and management</li>
                                <li><i class="fas fa-check text-info me-2"></i>Multiple line items</li>
                                <li><i class="fas fa-check text-info me-2"></i>Tax calculation</li>
                                <li><i class="fas fa-check text-info me-2"></i>Discount support</li>
                                <li><i class="fas fa-check text-info me-2"></i>Payment tracking</li>
                                <li><i class="fas fa-check text-info me-2"></i>Status management (draft, sent, paid, overdue)</li>
                                <li><i class="fas fa-check text-info me-2"></i>PDF generation</li>
                                <li><i class="fas fa-check text-info me-2"></i>Email invoices</li>
                                <li><i class="fas fa-check text-info me-2"></i>Invoice history</li>
                                <li><i class="fas fa-check text-info me-2"></i>Partial payments</li>
                            </ul>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="text-info"><i class="fas fa-receipt me-2"></i>Expense Management</h6>
                            <ul class="list-unstyled ms-3">
                                <li><i class="fas fa-check text-info me-2"></i>Expense recording</li>
                                <li><i class="fas fa-check text-info me-2"></i>Category management</li>
                                <li><i class="fas fa-check text-info me-2"></i>Vendor tracking</li>
                                <li><i class="fas fa-check text-info me-2"></i>Approval workflow</li>
                                <li><i class="fas fa-check text-info me-2"></i>Receipt attachments</li>
                                <li><i class="fas fa-check text-info me-2"></i>Status tracking (pending, approved, rejected)</li>
                                <li><i class="fas fa-check text-info me-2"></i>Expense reports</li>
                                <li><i class="fas fa-check text-info me-2"></i>Budget tracking</li>
                            </ul>
                        </div>
                        <div class="col-12">
                            <h6 class="text-info"><i class="fas fa-chart-line me-2"></i>Financial Reports</h6>
                            <ul class="list-unstyled ms-3">
                                <li><i class="fas fa-check text-info me-2"></i>Revenue tracking</li>
                                <li><i class="fas fa-check text-info me-2"></i>Expense analysis</li>
                                <li><i class="fas fa-check text-info me-2"></i>Profit/Loss statements</li>
                                <li><i class="fas fa-check text-info me-2"></i>Cash flow reports</li>
                                <li><i class="fas fa-check text-info me-2"></i>Outstanding invoices</li>
                                <li><i class="fas fa-check text-info me-2"></i>Monthly/Yearly summaries</li>
                                <li><i class="fas fa-check text-info me-2"></i>Profit margin calculation</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Features -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-star me-2"></i>Advanced Features</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Checkup Templates -->
                        <div class="col-md-6 mb-3">
                            <h6 class="text-warning"><i class="fas fa-clipboard-list me-2"></i>Custom Checkup Templates</h6>
                            <ul class="list-unstyled ms-3">
                                <li><i class="fas fa-check text-warning me-2"></i>Create custom checkup forms</li>
                                <li><i class="fas fa-check text-warning me-2"></i>Dynamic form builder</li>
                                <li><i class="fas fa-check text-warning me-2"></i>Multiple field types (text, number, select, checkbox, etc.)</li>
                                <li><i class="fas fa-check text-warning me-2"></i>Section organization</li>
                                <li><i class="fas fa-check text-warning me-2"></i>Template assignment to patients</li>
                                <li><i class="fas fa-check text-warning me-2"></i>Medical condition specific templates</li>
                                <li><i class="fas fa-check text-warning me-2"></i>Specialty-based templates</li>
                                <li><i class="fas fa-check text-warning me-2"></i>Template activation/deactivation</li>
                                <li><i class="fas fa-check text-warning me-2"></i>Usage statistics</li>
                            </ul>
                        </div>

                        <!-- Medicine Database -->
                        <div class="col-md-6 mb-3">
                            <h6 class="text-warning"><i class="fas fa-pills me-2"></i>Medicine Database</h6>
                            <ul class="list-unstyled ms-3">
                                <li><i class="fas fa-check text-warning me-2"></i>Comprehensive medicine catalog</li>
                                <li><i class="fas fa-check text-warning me-2"></i>Generic and brand names</li>
                                <li><i class="fas fa-check text-warning me-2"></i>Dosage forms (tablet, capsule, syrup, injection, etc.)</li>
                                <li><i class="fas fa-check text-warning me-2"></i>Strength/concentration</li>
                                <li><i class="fas fa-check text-warning me-2"></i>Category classification</li>
                                <li><i class="fas fa-check text-warning me-2"></i>Manufacturer information</li>
                                <li><i class="fas fa-check text-warning me-2"></i>Search and filtering</li>
                                <li><i class="fas fa-check text-warning me-2"></i>Import/Export (Excel)</li>
                                <li><i class="fas fa-check text-warning me-2"></i>Bulk operations</li>
                            </ul>
                        </div>

                        <!-- Advertisements -->
                        <div class="col-md-6 mb-3">
                            <h6 class="text-warning"><i class="fas fa-bullhorn me-2"></i>Advertisement System</h6>
                            <ul class="list-unstyled ms-3">
                                <li><i class="fas fa-check text-warning me-2"></i>Create and manage ads</li>
                                <li><i class="fas fa-check text-warning me-2"></i>Image upload support</li>
                                <li><i class="fas fa-check text-warning me-2"></i>Title and description</li>
                                <li><i class="fas fa-check text-warning me-2"></i>Link/URL management</li>
                                <li><i class="fas fa-check text-warning me-2"></i>Active/Inactive status</li>
                                <li><i class="fas fa-check text-warning me-2"></i>Display on dashboard</li>
                                <li><i class="fas fa-check text-warning me-2"></i>Carousel/Slider support</li>
                            </ul>
                        </div>

                        <!-- Messages -->
                        <div class="col-md-6 mb-3">
                            <h6 class="text-warning"><i class="fas fa-envelope me-2"></i>Internal Messaging</h6>
                            <ul class="list-unstyled ms-3">
                                <li><i class="fas fa-check text-warning me-2"></i>Send messages between users</li>
                                <li><i class="fas fa-check text-warning me-2"></i>Inbox management</li>
                                <li><i class="fas fa-check text-warning me-2"></i>Read/Unread status</li>
                                <li><i class="fas fa-check text-warning me-2"></i>Message notifications</li>
                                <li><i class="fas fa-check text-warning me-2"></i>Reply functionality</li>
                                <li><i class="fas fa-check text-warning me-2"></i>Message deletion</li>
                                <li><i class="fas fa-check text-warning me-2"></i>Unread count badge</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Management & Security -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>User Management & Security</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h6 class="text-danger"><i class="fas fa-users-cog me-2"></i>User Roles</h6>
                            <ul class="list-unstyled ms-3">
                                <li><i class="fas fa-check text-danger me-2"></i><strong>Super Admin:</strong> System-wide access</li>
                                <li><i class="fas fa-check text-danger me-2"></i><strong>Master Admin:</strong> Multi-clinic management</li>
                                <li><i class="fas fa-check text-danger me-2"></i><strong>Admin:</strong> Clinic administrator</li>
                                <li><i class="fas fa-check text-danger me-2"></i><strong>Doctor:</strong> Medical professional</li>
                                <li><i class="fas fa-check text-danger me-2"></i><strong>Nutritionist:</strong> Diet and nutrition specialist</li>
                                <li><i class="fas fa-check text-danger me-2"></i><strong>Pharmacist:</strong> Pharmacy operations</li>
                                <li><i class="fas fa-check text-danger me-2"></i><strong>Lab Dept:</strong> Laboratory technician</li>
                                <li><i class="fas fa-check text-danger me-2"></i><strong>Radiology Dept:</strong> Radiology technician</li>
                                <li><i class="fas fa-check text-danger me-2"></i><strong>Dental Dept:</strong> Dentist</li>
                                <li><i class="fas fa-check text-danger me-2"></i><strong>Dental Technician:</strong> Dental lab technician (assigned lab requests only)</li>
                                <li><i class="fas fa-check text-danger me-2"></i><strong>CAD/CAM Designer:</strong> Dental CAD/CAM designer (assigned lab requests only)</li>
                                <li><i class="fas fa-check text-danger me-2"></i><strong>Assistant:</strong> Administrative support</li>
                                <li><i class="fas fa-check text-danger me-2"></i><strong>Nurse:</strong> Nursing staff</li>
                                <li><i class="fas fa-check text-danger me-2"></i><strong>Accountant:</strong> Financial operations</li>
                                <li><i class="fas fa-check text-danger me-2"></i><strong>Patient:</strong> Patient self-service access</li>
                            </ul>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="text-danger"><i class="fas fa-key me-2"></i>Permissions & Access Control</h6>
                            <ul class="list-unstyled ms-3">
                                <li><i class="fas fa-check text-danger me-2"></i>Granular permission system (60+ permissions)</li>
                                <li><i class="fas fa-check text-danger me-2"></i>Role-based access control (RBAC)</li>
                                <li><i class="fas fa-check text-danger me-2"></i>Custom permission assignment</li>
                                <li><i class="fas fa-check text-danger me-2"></i>Module-level permissions</li>
                                <li><i class="fas fa-check text-danger me-2"></i>Action-level permissions (view, create, edit, delete)</li>
                                <li><i class="fas fa-check text-danger me-2"></i>User activation/deactivation</li>
                                <li><i class="fas fa-check text-danger me-2"></i>Account expiry management</li>
                                <li><i class="fas fa-check text-danger me-2"></i>Password reset functionality</li>
                                <li><i class="fas fa-check text-danger me-2"></i>Last login tracking</li>
                            </ul>
                        </div>
                        <div class="col-12">
                            <h6 class="text-danger"><i class="fas fa-history me-2"></i>Audit & Security</h6>
                            <ul class="list-unstyled ms-3">
                                <li><i class="fas fa-check text-danger me-2"></i>Comprehensive audit logging</li>
                                <li><i class="fas fa-check text-danger me-2"></i>User activity tracking</li>
                                <li><i class="fas fa-check text-danger me-2"></i>Login/Logout logging</li>
                                <li><i class="fas fa-check text-danger me-2"></i>Failed login attempts tracking</li>
                                <li><i class="fas fa-check text-danger me-2"></i>IP address logging</li>
                                <li><i class="fas fa-check text-danger me-2"></i>User agent tracking</li>
                                <li><i class="fas fa-check text-danger me-2"></i>Data change history</li>
                                <li><i class="fas fa-check text-danger me-2"></i>Audit log filtering and search</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Master/SaaS Features -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-crown me-2"></i>Master Dashboard (SaaS Management)</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h6 class="text-dark"><i class="fas fa-hospital me-2"></i>Clinic Management</h6>
                            <ul class="list-unstyled ms-3">
                                <li><i class="fas fa-check text-dark me-2"></i>Multi-tenant architecture</li>
                                <li><i class="fas fa-check text-dark me-2"></i>Clinic registration and approval</li>
                                <li><i class="fas fa-check text-dark me-2"></i>Clinic activation/deactivation</li>
                                <li><i class="fas fa-check text-dark me-2"></i>Complete data isolation per clinic</li>
                                <li><i class="fas fa-check text-dark me-2"></i>Clinic profile management</li>
                                <li><i class="fas fa-check text-dark me-2"></i>Admin password reset</li>
                                <li><i class="fas fa-check text-dark me-2"></i>Clinic statistics</li>
                            </ul>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="text-dark"><i class="fas fa-chart-bar me-2"></i>System Reports</h6>
                            <ul class="list-unstyled ms-3">
                                <li><i class="fas fa-check text-dark me-2"></i>System-wide statistics</li>
                                <li><i class="fas fa-check text-dark me-2"></i>Clinic growth tracking</li>
                                <li><i class="fas fa-check text-dark me-2"></i>User distribution by role</li>
                                <li><i class="fas fa-check text-dark me-2"></i>Patient statistics across clinics</li>
                                <li><i class="fas fa-check text-dark me-2"></i>Prescription analytics</li>
                                <li><i class="fas fa-check text-dark me-2"></i>Financial overview</li>
                                <li><i class="fas fa-check text-dark me-2"></i>Subscription tracking</li>
                                <li><i class="fas fa-check text-dark me-2"></i>Payment management</li>
                            </ul>
                        </div>
                        <div class="col-12">
                            <h6 class="text-dark"><i class="fas fa-tools me-2"></i>System Maintenance</h6>
                            <ul class="list-unstyled ms-3">
                                <li><i class="fas fa-check text-dark me-2"></i>System health monitoring</li>
                                <li><i class="fas fa-check text-dark me-2"></i>Database health checks</li>
                                <li><i class="fas fa-check text-dark me-2"></i>Storage monitoring</li>
                                <li><i class="fas fa-check text-dark me-2"></i>Cache management</li>
                                <li><i class="fas fa-check text-dark me-2"></i>Server updates</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Technical Features -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Technical Features & Integrations</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <h6 class="text-secondary"><i class="fas fa-file-pdf me-2"></i>PDF Generation</h6>
                            <ul class="list-unstyled ms-3">
                                <li><i class="fas fa-check text-secondary me-2"></i>Prescriptions</li>
                                <li><i class="fas fa-check text-secondary me-2"></i>Lab reports</li>
                                <li><i class="fas fa-check text-secondary me-2"></i>Radiology reports</li>
                                <li><i class="fas fa-check text-secondary me-2"></i>Invoices</li>
                                <li><i class="fas fa-check text-secondary me-2"></i>Diet plans</li>
                                <li><i class="fas fa-check text-secondary me-2"></i>Custom branding</li>
                            </ul>
                        </div>
                        <div class="col-md-4 mb-3">
                            <h6 class="text-secondary"><i class="fas fa-file-excel me-2"></i>Import/Export</h6>
                            <ul class="list-unstyled ms-3">
                                <li><i class="fas fa-check text-secondary me-2"></i>Patient data export</li>
                                <li><i class="fas fa-check text-secondary me-2"></i>Medicine import/export</li>
                                <li><i class="fas fa-check text-secondary me-2"></i>Food database import/export</li>
                                <li><i class="fas fa-check text-secondary me-2"></i>Excel format support</li>
                                <li><i class="fas fa-check text-secondary me-2"></i>Bulk data operations</li>
                            </ul>
                        </div>
                        <div class="col-md-4 mb-3">
                            <h6 class="text-secondary"><i class="fas fa-search me-2"></i>Search & Filtering</h6>
                            <ul class="list-unstyled ms-3">
                                <li><i class="fas fa-check text-secondary me-2"></i>Smart search with Select2</li>
                                <li><i class="fas fa-check text-secondary me-2"></i>Advanced filtering</li>
                                <li><i class="fas fa-check text-secondary me-2"></i>Date range filters</li>
                                <li><i class="fas fa-check text-secondary me-2"></i>Status filters</li>
                                <li><i class="fas fa-check text-secondary me-2"></i>Category filters</li>
                                <li><i class="fas fa-check text-secondary me-2"></i>Real-time search</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary of Key Features -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-primary">
                <div class="card-header bg-gradient text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="mb-0"><i class="fas fa-star me-2"></i>Summary of Key Features</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Core Modules -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100 border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="fas fa-hospital me-2"></i>Core Clinical Modules</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2"><i class="fas fa-user-injured text-primary me-2"></i><strong>Patient Management:</strong> Complete profiles, medical history, vital signs, chronic conditions</li>
                                        <li class="mb-2"><i class="fas fa-prescription text-primary me-2"></i><strong>Prescription System:</strong> Digital prescriptions with drug database, PDF generation, multi-language support</li>
                                        <li class="mb-2"><i class="fas fa-flask text-primary me-2"></i><strong>Laboratory:</strong> Lab requests, test results, reference ranges, PDF reports</li>
                                        <li class="mb-2"><i class="fas fa-x-ray text-primary me-2"></i><strong>Radiology:</strong> Imaging requests, DICOM integration, report generation</li>
                                        <li class="mb-2"><i class="fas fa-calendar-check text-primary me-2"></i><strong>Appointments:</strong> Calendar scheduling, conflict detection, status tracking</li>
                                        <li class="mb-2"><i class="fas fa-apple-alt text-primary me-2"></i><strong>Nutrition Planning:</strong> Food database, meal planning, calorie tracking, diet plans</li>
                                        <li class="mb-2"><i class="fas fa-tooth text-primary me-2"></i><strong>Dental Module:</strong> Interactive dental charts, condition tracking, treatment planning, PDF export</li>
                                        <li class="mb-2"><i class="fas fa-child text-primary me-2"></i><strong>Pediatric Growth:</strong> WHO/CDC growth charts, percentile tracking, corrected age support</li>
                                        <li class="mb-2"><i class="fas fa-head-side-cough text-primary me-2"></i><strong>ENT Module:</strong> Ear/nose/throat examinations, ICD-10 diagnoses, audiometry &amp; tympanometry tests</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Business Features -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100 border-success">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0"><i class="fas fa-chart-line me-2"></i>Business & Operations</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2"><i class="fas fa-file-invoice-dollar text-success me-2"></i><strong>Financial Management:</strong> Invoicing, payment tracking, expense management, profit/loss reports</li>
                                        <li class="mb-2"><i class="fas fa-bullhorn text-success me-2"></i><strong>Marketing:</strong> Advertisement management, campaign tracking, analytics</li>
                                        <li class="mb-2"><i class="fas fa-chart-bar text-success me-2"></i><strong>Reports & Analytics:</strong> Patient statistics, revenue reports, activity tracking</li>
                                        <li class="mb-2"><i class="fas fa-users-cog text-success me-2"></i><strong>User Management:</strong> Role-based access, permissions, activity logs</li>
                                        <li class="mb-2"><i class="fas fa-cog text-success me-2"></i><strong>System Settings:</strong> Clinic configuration, branding, customization</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Technical Capabilities -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100 border-info">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="fas fa-laptop-code me-2"></i>Technical Capabilities</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2"><i class="fas fa-language text-info me-2"></i><strong>Multi-language:</strong> English, Arabic, Kurdish (Bahdini & Sorani)</li>
                                        <li class="mb-2"><i class="fas fa-file-pdf text-info me-2"></i><strong>PDF Generation:</strong> Prescriptions, lab reports, invoices, diet plans</li>
                                        <li class="mb-2"><i class="fas fa-file-excel text-info me-2"></i><strong>Import/Export:</strong> Excel support for patients, medicines, food database</li>
                                        <li class="mb-2"><i class="fas fa-search text-info me-2"></i><strong>Smart Search:</strong> Real-time search, advanced filtering, Select2 integration</li>
                                        <li class="mb-2"><i class="fas fa-mobile-alt text-info me-2"></i><strong>Responsive Design:</strong> Works on desktop, tablet, and mobile devices</li>
                                        <li class="mb-2"><i class="fas fa-shield-alt text-info me-2"></i><strong>Security:</strong> Role-based access, audit logging, data encryption</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Key Statistics -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100 border-warning">
                                <div class="card-header bg-warning text-dark">
                                    <h6 class="mb-0"><i class="fas fa-trophy me-2"></i>System Highlights</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-6 mb-3">
                                            <div class="p-3 bg-light rounded">
                                                <h3 class="text-primary mb-0">200+</h3>
                                                <small class="text-muted">Total Features</small>
                                            </div>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <div class="p-3 bg-light rounded">
                                                <h3 class="text-success mb-0">10</h3>
                                                <small class="text-muted">Core Modules</small>
                                            </div>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <div class="p-3 bg-light rounded">
                                                <h3 class="text-info mb-0">4</h3>
                                                <small class="text-muted">Languages</small>
                                            </div>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <div class="p-3 bg-light rounded">
                                                <h3 class="text-warning mb-0">100%</h3>
                                                <small class="text-muted">Cloud-Based</small>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>HIPAA Compliant Ready</li>
                                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Real-time Data Sync</li>
                                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Automated Backups</li>
                                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>24/7 Availability</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Benefits Section -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="alert alert-primary mb-0">
                                <h6 class="alert-heading"><i class="fas fa-lightbulb me-2"></i>Why Choose ConCure Cloud?</h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <p class="mb-2"><strong><i class="fas fa-bolt text-warning me-2"></i>Efficiency:</strong> Streamline clinic operations and reduce paperwork</p>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="mb-2"><strong><i class="fas fa-heart text-danger me-2"></i>Patient Care:</strong> Better patient management and treatment tracking</p>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="mb-2"><strong><i class="fas fa-dollar-sign text-success me-2"></i>Cost-Effective:</strong> Reduce operational costs and increase revenue</p>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="mb-2"><strong><i class="fas fa-lock text-info me-2"></i>Secure:</strong> Enterprise-grade security and data protection</p>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="mb-2"><strong><i class="fas fa-expand-arrows-alt text-primary me-2"></i>Scalable:</strong> Grows with your clinic from small to large practices</p>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="mb-0"><strong><i class="fas fa-headset text-secondary me-2"></i>Support:</strong> Dedicated customer support and training</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm bg-light">
                <div class="card-body text-center">
                    <p class="mb-2"><strong>ConCure Cloud</strong> - Comprehensive Clinic Management System</p>
                    <p class="text-muted mb-0">Version 1.0 | © {{ date('Y') }} ConCure. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    @media print {
        .btn, .sidebar, .navbar, .no-print {
            display: none !important;
        }
        .card {
            page-break-inside: avoid;
            border: 1px solid #dee2e6 !important;
        }
        .card-header {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        body {
            font-size: 12px;
        }
        h5 {
            font-size: 14px;
        }
        h6 {
            font-size: 13px;
        }
    }
    .feature-module {
        height: 100%;
    }
</style>
@endpush
@endsection
