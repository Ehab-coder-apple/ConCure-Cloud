<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ConCure Cloud - Complete Features List</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
            padding: 15mm 20mm;
            margin: 0;
        }
        
        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 3px solid #0d6efd;
            margin-bottom: 20px;
        }
        
        .header h1 {
            font-size: 24px;
            color: #0d6efd;
            margin-bottom: 5px;
        }
        
        .header p {
            font-size: 12px;
            color: #666;
        }
        
        .section {
            margin-bottom: 20px;
            page-break-inside: avoid;
            padding: 0 5px;
        }
        
        .section-header {
            background-color: #f8f9fa;
            padding: 8px 15px;
            border-left: 4px solid #0d6efd;
            margin-bottom: 10px;
            font-size: 14px;
            font-weight: bold;
        }
        
        .section-header.success {
            border-left-color: #198754;
            background-color: #d1e7dd;
        }
        
        .section-header.info {
            border-left-color: #0dcaf0;
            background-color: #cff4fc;
        }
        
        .section-header.warning {
            border-left-color: #ffc107;
            background-color: #fff3cd;
        }
        
        .section-header.danger {
            border-left-color: #dc3545;
            background-color: #f8d7da;
        }
        
        .section-header.dark {
            border-left-color: #212529;
            background-color: #e2e3e5;
        }
        
        .section-header.secondary {
            border-left-color: #6c757d;
            background-color: #e9ecef;
        }
        
        .overview-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        
        .overview-item {
            display: table-cell;
            width: 33.33%;
            padding: 10px;
            vertical-align: top;
        }
        
        .overview-item h3 {
            font-size: 11px;
            color: #0d6efd;
            margin-bottom: 5px;
        }
        
        .feature-grid {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }

        .feature-column {
            display: table-cell;
            width: 50%;
            padding: 0 10px;
            vertical-align: top;
        }

        .feature-column:first-child {
            padding-left: 0;
        }

        .feature-column:last-child {
            padding-right: 0;
        }
        
        .feature-module {
            margin-bottom: 10px;
        }
        
        .feature-module h3 {
            font-size: 12px;
            color: #198754;
            margin-bottom: 5px;
        }
        
        .feature-list {
            list-style: none;
            padding-left: 0;
        }
        
        .feature-list li {
            padding-left: 18px;
            margin-bottom: 4px;
            position: relative;
            line-height: 1.5;
        }
        
        .feature-list li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #198754;
            font-weight: bold;
        }
        
        .subsection {
            margin-bottom: 12px;
            padding: 0 5px;
        }

        .subsection h4 {
            font-size: 11px;
            margin-bottom: 6px;
            color: #495057;
            font-weight: bold;
        }
        
        .role-list {
            list-style: none;
            padding-left: 0;
        }
        
        .role-list li {
            padding-left: 15px;
            margin-bottom: 3px;
            position: relative;
        }
        
        .role-list li:before {
            content: "•";
            position: absolute;
            left: 0;
            color: #dc3545;
            font-weight: bold;
        }
        
        .role-list strong {
            color: #dc3545;
        }
        
        .footer {
            text-align: center;
            padding: 15px 0;
            border-top: 2px solid #dee2e6;
            margin-top: 20px;
            font-size: 9px;
            color: #666;
        }
        
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>ConCure Cloud - Complete Features List</h1>
        <p>Comprehensive Clinic Management System</p>
        <p style="font-size: 10px; margin-top: 5px;">Generated on {{ date('F d, Y') }}</p>
    </div>

    <!-- Application Overview -->
    <div class="section">
        <div class="section-header">Application Overview</div>
        <p style="margin-bottom: 10px; font-size: 11px;">ConCure Cloud is a comprehensive, multi-tenant SaaS clinic management system designed to streamline healthcare operations.</p>
        <div class="overview-grid">
            <div class="overview-item">
                <h3>Multi-Language Support</h3>
                <p>English, Arabic (العربية), Kurdish (کوردی)</p>
            </div>
            <div class="overview-item">
                <h3>Role-Based Access</h3>
                <p>15 user roles with granular permissions</p>
            </div>
            <div class="overview-item">
                <h3>Multi-Clinic</h3>
                <p>Complete data isolation per clinic</p>
            </div>
        </div>
    </div>

    <!-- Core Modules -->
    <div class="section">
        <div class="section-header success">Core Modules</div>
        
        <div class="feature-grid">
            <div class="feature-column">
                <div class="feature-module">
                    <h3>Patient Management</h3>
                    <ul class="feature-list">
                        <li>Complete patient profiles with demographics</li>
                        <li>Medical history tracking</li>
                        <li>Vital signs recording (BP, HR, Temp, Weight, Height, BMI)</li>
                        <li>Chronic conditions management</li>
                        <li>Allergies and medication tracking</li>
                        <li>Family history documentation</li>
                        <li>File attachments (medical reports, images)</li>
                        <li>Patient search and filtering</li>
                        <li>Export patient data (Excel)</li>
                        <li>Bulk operations (delete, clear all)</li>
                        <li>Patient timeline view</li>
                        <li>Smart search with Select2 integration</li>
                    </ul>
                </div>
            </div>
            
            <div class="feature-column">
                <div class="feature-module">
                    <h3>Prescription System</h3>
                    <ul class="feature-list">
                        <li>Digital prescription creation</li>
                        <li>Medicine database integration</li>
                        <li>Dosage and frequency management</li>
                        <li>Duration tracking</li>
                        <li>Instructions and notes</li>
                        <li>Simple prescription mode (quick entry)</li>
                        <li>PDF generation with clinic branding</li>
                        <li>Custom Rx template support (upload clinic-branded template)</li>
                        <li>Print functionality</li>
                        <li>Prescription history</li>
                        <li>Drug interaction warnings</li>
                        <li>Prescription templates</li>
                        <li>Multi-language prescription printing</li>
                        <li>Auto-populated Height/Weight from latest checkup or growth measurement</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="page-break"></div>

    <!-- Appointments & Lab -->
    <div class="section">
        <div class="section-header success">Appointment & Laboratory Management</div>
        
        <div class="feature-grid">
            <div class="feature-column">
                <div class="feature-module">
                    <h3>Appointment Management</h3>
                    <ul class="feature-list">
                        <li>Calendar view (daily, weekly, monthly)</li>
                        <li>Appointment scheduling</li>
                        <li>Doctor assignment</li>
                        <li>Status tracking (scheduled, confirmed, completed, cancelled)</li>
                        <li>Appointment types and reasons</li>
                        <li>Notes and comments</li>
                        <li>Quick appointment creation modal</li>
                        <li>Patient search integration</li>
                        <li>Conflict detection</li>
                        <li>Appointment reminders</li>
                        <li>Color-coded status indicators</li>
                    </ul>
                </div>
            </div>
            
            <div class="feature-column">
                <div class="feature-module">
                    <h3>Laboratory Management</h3>
                    <ul class="feature-list">
                        <li>Lab test catalog</li>
                        <li>Lab request creation</li>
                        <li>Multiple tests per request</li>
                        <li>Test results entry</li>
                        <li>Normal range indicators</li>
                        <li>Status tracking (pending, in-progress, completed)</li>
                        <li>PDF report generation</li>
                        <li>Lab technician assignment</li>
                        <li>Test history tracking</li>
                        <li>Urgent test flagging</li>
                        <li>Lab test management (add/edit/delete)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Radiology & Nutrition -->
    <div class="section">
        <div class="section-header success">Radiology & Nutrition Management</div>
        
        <div class="feature-grid">
            <div class="feature-column">
                <div class="feature-module">
                    <h3>Radiology Management</h3>
                    <ul class="feature-list">
                        <li>Radiology test catalog (X-Ray, CT, MRI, Ultrasound, etc.)</li>
                        <li>Radiology request creation</li>
                        <li>Multiple tests per request</li>
                        <li>Clinical indications</li>
                        <li>Findings and impressions</li>
                        <li>Status tracking</li>
                        <li>PDF report generation</li>
                        <li>Radiologist assignment</li>
                        <li>Image attachments</li>
                        <li>Urgent request flagging</li>
                        <li>Radiology test management</li>
                    </ul>
                </div>
            </div>

            <div class="feature-column">
                <div class="feature-module">
                    <h3>Nutrition & Diet Planning</h3>
                    <ul class="feature-list">
                        <li>Comprehensive food database</li>
                        <li>Nutritional information (calories, protein, carbs, fats)</li>
                        <li>Food groups categorization</li>
                        <li>Diet plan creation</li>
                        <li>Meal planning (breakfast, lunch, dinner, snacks)</li>
                        <li>Portion size management</li>
                        <li>Calorie tracking</li>
                        <li>Dietary restrictions</li>
                        <li>Food search and filtering</li>
                        <li>Import/Export food data (Excel)</li>
                        <li>Custom food items</li>
                        <li>Nutrition recommendations</li>
                        <li>PDF diet plan generation</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Dental & Pediatric -->
    <div class="section">
        <div class="section-header success">Dental & Pediatric Modules</div>

        <div class="feature-grid">
            <div class="feature-column">
                <div class="feature-module">
                    <h3>Dental Module</h3>
                    <ul class="feature-list">
                        <li>Interactive dental chart (adult & pediatric)</li>
                        <li>Tooth-by-tooth condition tracking</li>
                        <li>Visual tooth status indicators (color-coded)</li>
                        <li>Dental conditions library (30+ conditions)</li>
                        <li>Treatment planning and tracking</li>
                        <li>Treatment history per tooth</li>
                        <li>Canal treatment (endodontic) worksheet with FDI canal library</li>
                        <li>Dental imaging with tooth-level linkage</li>
                        <li>Simple and detailed chart views</li>
                        <li>Dental chart PDF export</li>
                        <li>Multi-tooth selection and bulk updates</li>
                        <li>Searchable condition legend</li>
                        <li>Tooth numbering system (FDI notation)</li>
                        <li>Dental lab requests with external lab integration</li>
                        <li>External dental labs directory and management</li>
                        <li>Lab request assignment to Dental Technician or CAD/CAM Designer</li>
                        <li>Dedicated workflow for technicians/designers (restricted to assigned requests)</li>
                        <li>Lab request result upload and completion tracking</li>
                    </ul>
                </div>
            </div>

            <div class="feature-column">
                <div class="feature-module">
                    <h3>Pediatric Growth Chart</h3>
                    <ul class="feature-list">
                        <li>WHO & CDC growth reference data</li>
                        <li>Weight-for-age charts (0–5 years)</li>
                        <li>Length/Height-for-age charts</li>
                        <li>Head circumference-for-age charts</li>
                        <li>BMI-for-age charts</li>
                        <li>Weight-for-length/height charts</li>
                        <li>Percentile curves (3rd–97th)</li>
                        <li>Growth measurement recording & history</li>
                        <li>Preterm/LBW corrected age support</li>
                        <li>Birth weight & gestational age tracking</li>
                        <li>Growth chart PDF export with formatted layout</li>
                        <li>Pediatric patient list with age filtering</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="page-break"></div>

    <!-- Financial Management -->
    <div class="section">
        <div class="section-header info">Financial Management</div>

        <div class="feature-grid">
            <div class="feature-column">
                <div class="subsection">
                    <h4>Invoicing</h4>
                    <ul class="feature-list">
                        <li>Invoice creation and management</li>
                        <li>Multiple line items</li>
                        <li>Tax calculation</li>
                        <li>Discount support</li>
                        <li>Payment tracking</li>
                        <li>Status management (draft, sent, paid, overdue)</li>
                        <li>PDF generation</li>
                        <li>Email invoices</li>
                        <li>Invoice history</li>
                        <li>Partial payments</li>
                    </ul>
                </div>
            </div>

            <div class="feature-column">
                <div class="subsection">
                    <h4>Expense Management</h4>
                    <ul class="feature-list">
                        <li>Expense recording</li>
                        <li>Category management</li>
                        <li>Vendor tracking</li>
                        <li>Approval workflow</li>
                        <li>Receipt attachments</li>
                        <li>Status tracking (pending, approved, rejected)</li>
                        <li>Expense reports</li>
                        <li>Budget tracking</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="subsection" style="margin-top: 10px;">
            <h4>Financial Reports</h4>
            <ul class="feature-list">
                <li>Revenue tracking</li>
                <li>Expense analysis</li>
                <li>Profit/Loss statements</li>
                <li>Cash flow reports</li>
                <li>Outstanding invoices</li>
                <li>Monthly/Yearly summaries</li>
                <li>Profit margin calculation</li>
            </ul>
        </div>
    </div>

    <!-- Advanced Features -->
    <div class="section">
        <div class="section-header warning">Advanced Features</div>

        <div class="feature-grid">
            <div class="feature-column">
                <div class="subsection">
                    <h4>Custom Checkup Templates</h4>
                    <ul class="feature-list">
                        <li>Create custom checkup forms</li>
                        <li>Dynamic form builder</li>
                        <li>Multiple field types (text, number, select, checkbox, etc.)</li>
                        <li>Section organization</li>
                        <li>Template assignment to patients</li>
                        <li>Medical condition specific templates</li>
                        <li>Specialty-based templates</li>
                        <li>Template activation/deactivation</li>
                        <li>Usage statistics</li>
                    </ul>
                </div>

                <div class="subsection">
                    <h4>Advertisement System</h4>
                    <ul class="feature-list">
                        <li>Create and manage ads</li>
                        <li>Image upload support</li>
                        <li>Title and description</li>
                        <li>Link/URL management</li>
                        <li>Active/Inactive status</li>
                        <li>Display on dashboard</li>
                        <li>Carousel/Slider support</li>
                    </ul>
                </div>
            </div>

            <div class="feature-column">
                <div class="subsection">
                    <h4>Medicine Database</h4>
                    <ul class="feature-list">
                        <li>Comprehensive medicine catalog</li>
                        <li>Generic and brand names</li>
                        <li>Dosage forms (tablet, capsule, syrup, injection, etc.)</li>
                        <li>Strength/concentration</li>
                        <li>Category classification</li>
                        <li>Manufacturer information</li>
                        <li>Search and filtering</li>
                        <li>Import/Export (Excel)</li>
                        <li>Bulk operations</li>
                    </ul>
                </div>

                <div class="subsection">
                    <h4>Internal Messaging</h4>
                    <ul class="feature-list">
                        <li>Send messages between users</li>
                        <li>Inbox management</li>
                        <li>Read/Unread status</li>
                        <li>Message notifications</li>
                        <li>Reply functionality</li>
                        <li>Message deletion</li>
                        <li>Unread count badge</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="page-break"></div>

    <!-- User Management & Security -->
    <div class="section">
        <div class="section-header danger">User Management & Security</div>

        <div class="feature-grid">
            <div class="feature-column">
                <div class="subsection">
                    <h4>User Roles</h4>
                    <ul class="role-list">
                        <li><strong>Super Admin:</strong> System-wide access</li>
                        <li><strong>Master Admin:</strong> Multi-clinic management</li>
                        <li><strong>Admin:</strong> Clinic administrator</li>
                        <li><strong>Doctor:</strong> Medical professional</li>
                        <li><strong>Nutritionist:</strong> Diet and nutrition specialist</li>
                        <li><strong>Pharmacist:</strong> Pharmacy operations</li>
                        <li><strong>Lab Dept:</strong> Laboratory technician</li>
                        <li><strong>Radiology Dept:</strong> Radiology technician</li>
                        <li><strong>Dental Dept:</strong> Dentist</li>
                        <li><strong>Dental Technician:</strong> Dental lab technician (assigned lab requests only)</li>
                        <li><strong>CAD/CAM Designer:</strong> Dental CAD/CAM designer (assigned lab requests only)</li>
                        <li><strong>Assistant:</strong> Administrative support</li>
                        <li><strong>Nurse:</strong> Nursing staff</li>
                        <li><strong>Accountant:</strong> Financial operations</li>
                        <li><strong>Patient:</strong> Patient self-service access</li>
                    </ul>
                </div>
            </div>

            <div class="feature-column">
                <div class="subsection">
                    <h4>Permissions & Access Control</h4>
                    <ul class="feature-list">
                        <li>Granular permission system (60+ permissions)</li>
                        <li>Role-based access control (RBAC)</li>
                        <li>Custom permission assignment</li>
                        <li>Module-level permissions</li>
                        <li>Action-level permissions (view, create, edit, delete)</li>
                        <li>User activation/deactivation</li>
                        <li>Account expiry management</li>
                        <li>Password reset functionality</li>
                        <li>Last login tracking</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="subsection" style="margin-top: 10px;">
            <h4>Audit & Security</h4>
            <ul class="feature-list">
                <li>Comprehensive audit logging</li>
                <li>User activity tracking</li>
                <li>Login/Logout logging</li>
                <li>Failed login attempts tracking</li>
                <li>IP address logging</li>
                <li>User agent tracking</li>
                <li>Data change history</li>
                <li>Audit log filtering and search</li>
            </ul>
        </div>
    </div>

    <!-- Master/SaaS Features -->
    <div class="section">
        <div class="section-header dark">Master Dashboard (SaaS Management)</div>

        <div class="feature-grid">
            <div class="feature-column">
                <div class="subsection">
                    <h4>Clinic Management</h4>
                    <ul class="feature-list">
                        <li>Multi-tenant architecture</li>
                        <li>Clinic registration and approval</li>
                        <li>Clinic activation/deactivation</li>
                        <li>Complete data isolation per clinic</li>
                        <li>Clinic profile management</li>
                        <li>Admin password reset</li>
                        <li>Clinic statistics</li>
                    </ul>
                </div>
            </div>

            <div class="feature-column">
                <div class="subsection">
                    <h4>System Reports</h4>
                    <ul class="feature-list">
                        <li>System-wide statistics</li>
                        <li>Clinic growth tracking</li>
                        <li>User distribution by role</li>
                        <li>Patient statistics across clinics</li>
                        <li>Prescription analytics</li>
                        <li>Financial overview</li>
                        <li>Subscription tracking</li>
                        <li>Payment management</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="subsection" style="margin-top: 10px;">
            <h4>System Maintenance</h4>
            <ul class="feature-list">
                <li>System health monitoring</li>
                <li>Database health checks</li>
                <li>Storage monitoring</li>
                <li>Cache management</li>
                <li>Server updates</li>
            </ul>
        </div>
    </div>

    <!-- Technical Features -->
    <div class="section">
        <div class="section-header secondary">Technical Features & Integrations</div>

        <div class="feature-grid">
            <div class="feature-column">
                <div class="subsection">
                    <h4>PDF Generation</h4>
                    <ul class="feature-list">
                        <li>Prescriptions</li>
                        <li>Lab reports</li>
                        <li>Radiology reports</li>
                        <li>Invoices</li>
                        <li>Diet plans</li>
                        <li>Custom branding</li>
                    </ul>
                </div>

                <div class="subsection">
                    <h4>Import/Export</h4>
                    <ul class="feature-list">
                        <li>Patient data export</li>
                        <li>Medicine import/export</li>
                        <li>Food database import/export</li>
                        <li>Excel format support</li>
                        <li>Bulk data operations</li>
                    </ul>
                </div>
            </div>

            <div class="feature-column">
                <div class="subsection">
                    <h4>Search & Filtering</h4>
                    <ul class="feature-list">
                        <li>Smart search with Select2</li>
                        <li>Advanced filtering</li>
                        <li>Date range filters</li>
                        <li>Status filters</li>
                        <li>Category filters</li>
                        <li>Real-time search</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="page-break"></div>

    <!-- Summary of Key Features -->
    <div class="section">
        <div class="section-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <i class="fas fa-star"></i> Summary of Key Features
        </div>

        <div class="feature-grid">
            <!-- Core Modules -->
            <div class="feature-column">
                <div class="feature-module" style="border: 2px solid #007bff; border-radius: 8px; padding: 15px;">
                    <h3 style="color: #007bff; border-bottom: 2px solid #007bff; padding-bottom: 8px;">
                        <i class="fas fa-hospital"></i> Core Clinical Modules
                    </h3>
                    <ul class="feature-list">
                        <li><strong>Patient Management:</strong> Complete profiles, medical history, vital signs, chronic conditions</li>
                        <li><strong>Prescription System:</strong> Digital prescriptions with drug database, PDF generation, multi-language support</li>
                        <li><strong>Laboratory:</strong> Lab requests, test results, reference ranges, PDF reports</li>
                        <li><strong>Radiology:</strong> Imaging requests, DICOM integration, report generation</li>
                        <li><strong>Appointments:</strong> Calendar scheduling, conflict detection, status tracking</li>
                        <li><strong>Nutrition Planning:</strong> Food database, meal planning, calorie tracking, diet plans</li>
                        <li><strong>Dental Module:</strong> Interactive dental charts, condition tracking, treatment planning, PDF export</li>
                        <li><strong>Pediatric Growth:</strong> WHO/CDC growth charts, percentile tracking, corrected age support</li>
                    </ul>
                </div>
            </div>

            <!-- Business Features -->
            <div class="feature-column">
                <div class="feature-module" style="border: 2px solid #28a745; border-radius: 8px; padding: 15px;">
                    <h3 style="color: #28a745; border-bottom: 2px solid #28a745; padding-bottom: 8px;">
                        <i class="fas fa-chart-line"></i> Business & Operations
                    </h3>
                    <ul class="feature-list">
                        <li><strong>Financial Management:</strong> Invoicing, payment tracking, expense management, profit/loss reports</li>
                        <li><strong>Marketing:</strong> Advertisement management, campaign tracking, analytics</li>
                        <li><strong>Reports & Analytics:</strong> Patient statistics, revenue reports, activity tracking</li>
                        <li><strong>User Management:</strong> Role-based access, permissions, activity logs</li>
                        <li><strong>System Settings:</strong> Clinic configuration, branding, customization</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="feature-grid" style="margin-top: 15px;">
            <!-- Technical Capabilities -->
            <div class="feature-column">
                <div class="feature-module" style="border: 2px solid #17a2b8; border-radius: 8px; padding: 15px;">
                    <h3 style="color: #17a2b8; border-bottom: 2px solid #17a2b8; padding-bottom: 8px;">
                        <i class="fas fa-laptop-code"></i> Technical Capabilities
                    </h3>
                    <ul class="feature-list">
                        <li><strong>Multi-language:</strong> English, Arabic, Kurdish (Bahdini & Sorani)</li>
                        <li><strong>PDF Generation:</strong> Prescriptions, lab reports, invoices, diet plans</li>
                        <li><strong>Import/Export:</strong> Excel support for patients, medicines, food database</li>
                        <li><strong>Smart Search:</strong> Real-time search, advanced filtering, Select2 integration</li>
                        <li><strong>Responsive Design:</strong> Works on desktop, tablet, and mobile devices</li>
                        <li><strong>Security:</strong> Role-based access, audit logging, data encryption</li>
                    </ul>
                </div>
            </div>

            <!-- Key Statistics -->
            <div class="feature-column">
                <div class="feature-module" style="border: 2px solid #ffc107; border-radius: 8px; padding: 15px;">
                    <h3 style="color: #856404; border-bottom: 2px solid #ffc107; padding-bottom: 8px;">
                        <i class="fas fa-trophy"></i> System Highlights
                    </h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px;">
                        <div style="background: #f8f9fa; padding: 10px; text-align: center; border-radius: 5px;">
                            <h2 style="color: #007bff; margin: 0;">200+</h2>
                            <small>Total Features</small>
                        </div>
                        <div style="background: #f8f9fa; padding: 10px; text-align: center; border-radius: 5px;">
                            <h2 style="color: #28a745; margin: 0;">10</h2>
                            <small>Core Modules</small>
                        </div>
                        <div style="background: #f8f9fa; padding: 10px; text-align: center; border-radius: 5px;">
                            <h2 style="color: #17a2b8; margin: 0;">4</h2>
                            <small>Languages</small>
                        </div>
                        <div style="background: #f8f9fa; padding: 10px; text-align: center; border-radius: 5px;">
                            <h2 style="color: #ffc107; margin: 0;">100%</h2>
                            <small>Cloud-Based</small>
                        </div>
                    </div>
                    <ul class="feature-list" style="margin-top: 10px;">
                        <li>✓ HIPAA Compliant Ready</li>
                        <li>✓ Real-time Data Sync</li>
                        <li>✓ Automated Backups</li>
                        <li>✓ 24/7 Availability</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Benefits Section -->
        <div style="background: #e7f3ff; border: 2px solid #007bff; border-radius: 8px; padding: 15px; margin-top: 15px;">
            <h3 style="color: #007bff; margin-top: 0;">
                <i class="fas fa-lightbulb"></i> Why Choose ConCure Cloud?
            </h3>
            <div class="feature-grid">
                <div class="feature-column">
                    <ul class="feature-list">
                        <li><strong>⚡ Efficiency:</strong> Streamline clinic operations and reduce paperwork</li>
                        <li><strong>❤️ Patient Care:</strong> Better patient management and treatment tracking</li>
                        <li><strong>💰 Cost-Effective:</strong> Reduce operational costs and increase revenue</li>
                    </ul>
                </div>
                <div class="feature-column">
                    <ul class="feature-list">
                        <li><strong>🔒 Secure:</strong> Enterprise-grade security and data protection</li>
                        <li><strong>📈 Scalable:</strong> Grows with your clinic from small to large practices</li>
                        <li><strong>🎧 Support:</strong> Dedicated customer support and training</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p><strong>ConCure Cloud</strong> - Comprehensive Clinic Management System</p>
        <p>Version 1.0 | © {{ date('Y') }} ConCure. All rights reserved.</p>
        <p style="margin-top: 5px;">Total Features: 200+ | Generated: {{ date('F d, Y \a\t h:i A') }}</p>
    </div>
</body>
</html>
