<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ConCure Cloud - Complete Features List</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        @page { size: A4 portrait; margin: 22mm 16mm 24mm 16mm; }
        @page :first { margin: 0; }

        body {
            font-family: 'Helvetica', 'DejaVu Sans', sans-serif;
            font-size: 11.5px;
            line-height: 1.65;
            color: #1f2937;
        }
        strong { color: #0b1220; font-weight: 700; }
        h1, h2, h3, h4 { color: #0b1220; }
        p { margin-bottom: 6px; }

        /* ============ COVER PAGE ============ */
        .cover {
            page-break-after: always;
            height: 297mm;
            width: 210mm;
            position: relative;
            background: #0b3a8c;
            color: #fff;
            padding: 36mm 22mm 0 22mm;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .cover .brand-mark {
            font-size: 11px; letter-spacing: 6px; text-transform: uppercase;
            opacity: .9; margin-bottom: 26mm; font-weight: 700;
        }
        .cover .cover-logo {
            display: block;
            width: 36mm; height: auto;
            margin-bottom: 10mm;
            background: #ffffff;
            padding: 4mm;
            border-radius: 3mm;
        }
        .cover h1 {
            color: #fff;
            font-size: 46px; line-height: 1.05; font-weight: 700;
            margin-bottom: 8mm; max-width: 160mm; letter-spacing: -0.5px;
        }
        .cover .subtitle {
            font-size: 14.5px; line-height: 1.6; max-width: 155mm;
            opacity: .94; font-weight: 300;
        }
        .cover .accent-bar {
            position: absolute; left: 22mm; bottom: 62mm;
            width: 38mm; height: 1.1mm; background: #ffffff;
        }
        .cover .meta {
            position: absolute; left: 22mm; bottom: 26mm; right: 22mm;
            font-size: 11px; opacity: .95;
            border-top: 0.5pt solid rgba(255,255,255,.5); padding-top: 7mm;
        }
        .cover .meta-row { display: table; width: 100%; }
        .cover .meta-cell { display: table-cell; }
        .cover .meta-cell.right { text-align: right; }
        .cover .meta strong { color: #fff; font-weight: 700; font-size: 12px; }

        /* ============ DOCUMENT HEADER (after cover) ============ */
        .doc-header {
            border-bottom: 1pt solid #0b3a8c;
            padding-bottom: 8px; margin-bottom: 18px;
        }
        .doc-header .row { display: table; width: 100%; }
        .doc-header .left, .doc-header .right {
            display: table-cell; vertical-align: middle;
            font-size: 9.5px; color: #4b5563;
        }
        .doc-header .right { text-align: right; }
        .doc-header .brand {
            color: #0b3a8c; font-weight: 700; letter-spacing: 1.5px;
            font-size: 10.5px; text-transform: uppercase;
        }

        /* ============ SECTIONS ============ */
        .section { margin-bottom: 20px; page-break-inside: avoid; }
        .section-header {
            background: #0b3a8c;
            color: #fff;
            padding: 9px 16px;
            margin-bottom: 14px;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: .3px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .section-header.success,
        .section-header.info,
        .section-header.warning,
        .section-header.danger,
        .section-header.dark,
        .section-header.secondary {
            background: #0b3a8c; color: #fff;
        }

        .overview-grid {
            display: table; width: 100%; margin-bottom: 12px;
            background: #f7f9fc; border: 0.5pt solid #d8e2f1;
            -webkit-print-color-adjust: exact; print-color-adjust: exact;
        }
        .overview-item {
            display: table-cell; width: 33.33%; padding: 12px 14px;
            vertical-align: top; border-right: 0.5pt solid #d8e2f1;
        }
        .overview-item:last-child { border-right: 0; }
        .overview-item h3 {
            font-size: 11px; color: #0b3a8c; margin-bottom: 4px; font-weight: 700;
            text-transform: uppercase; letter-spacing: .8px;
        }
        .overview-item p { font-size: 11px; color: #374151; line-height: 1.55; }

        .feature-grid { display: table; width: 100%; margin-bottom: 8px; }
        .feature-column { display: table-cell; width: 50%; padding: 0 8px; vertical-align: top; }
        .feature-column:first-child { padding-left: 0; }
        .feature-column:last-child { padding-right: 0; }

        .feature-module {
            margin-bottom: 14px;
            background: #fafbfd;
            border: 0.5pt solid #e1e6ef;
            border-top: 2pt solid #0b3a8c;
            padding: 10px 13px 11px 13px;
            -webkit-print-color-adjust: exact; print-color-adjust: exact;
        }
        .feature-module h3 {
            font-size: 13.5px; color: #0b3a8c;
            margin-bottom: 7px; font-weight: 700;
            border-bottom: 0.5pt solid #d8e2f1; padding-bottom: 5px;
        }

        .feature-list { list-style: none; padding-left: 0; }
        .feature-list li {
            padding-left: 14px; margin-bottom: 4px; position: relative;
            line-height: 1.55; color: #1f2937; font-size: 11px;
        }
        .feature-list li:before {
            content: ""; position: absolute;
            left: 2px; top: 7px;
            width: 4px; height: 4px;
            background: #0b3a8c;
        }

        .subsection { margin-bottom: 14px; padding: 0 2px; }
        .subsection h4 {
            font-size: 12px; margin-bottom: 7px; color: #0b3a8c; font-weight: 700;
            text-transform: uppercase; letter-spacing: .8px;
            border-bottom: 0.5pt solid #d8e2f1; padding-bottom: 4px;
        }

        .role-list { list-style: none; padding-left: 0; }
        .role-list li {
            padding-left: 14px; margin-bottom: 4px; position: relative;
            font-size: 11px; line-height: 1.55;
        }
        .role-list li:before {
            content: ""; position: absolute; left: 2px; top: 7px;
            width: 4px; height: 4px; background: #0b3a8c;
        }
        .role-list strong { color: #0b1220; }

        /* Stat cards (replaces broken CSS grid in summary). */
        .stat-grid { display: table; width: 100%; margin-bottom: 12px; border-collapse: collapse; }
        .stat-cell {
            display: table-cell; width: 25%; padding: 12px 6px;
            border: 0.5pt solid #d8e2f1; text-align: center; vertical-align: middle;
            background: #f7f9fc;
            -webkit-print-color-adjust: exact; print-color-adjust: exact;
        }
        .stat-cell .num {
            font-size: 22px; color: #0b3a8c; font-weight: 700;
            display: block; margin-bottom: 3px; line-height: 1;
        }
        .stat-cell .label {
            font-size: 9.5px; color: #4b5563;
            text-transform: uppercase; letter-spacing: .8px;
        }

        .summary-card {
            border: 0.5pt solid #d8e2f1;
            border-top: 2.5pt solid #0b3a8c;
            padding: 12px 14px; margin-bottom: 10px;
            background: #fafbfd;
            -webkit-print-color-adjust: exact; print-color-adjust: exact;
        }
        .summary-card h3 {
            font-size: 13.5px; color: #0b3a8c; font-weight: 700;
            border-bottom: 0.5pt solid #d8e2f1; padding-bottom: 5px; margin-bottom: 8px;
        }

        .callout {
            background: #eef3fb; border-left: 4pt solid #0b3a8c;
            padding: 14px 16px; margin-top: 14px;
            -webkit-print-color-adjust: exact; print-color-adjust: exact;
        }
        .callout h3 {
            font-size: 14px; color: #0b3a8c;
            margin-bottom: 8px; font-weight: 700;
        }

        .footer-end {
            text-align: center; padding-top: 14px; margin-top: 22px;
            border-top: 1pt solid #0b3a8c; font-size: 10px; color: #4b5563;
        }
        .footer-end p { margin-bottom: 3px; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    @php
        // Embed the cover logo as a base64 data URI so DomPDF's HTML <img>
        // parser (which is restricted by the chroot security setting) doesn't
        // need to resolve an absolute filesystem path. The per-page header
        // uses $canvas->image() in the controller and bypasses chroot.
        $logoRel = \App\Http\Controllers\Master\SettingsController::getMasterBrandingLogoForPdfRelPath();
        $logoPath = $logoRel ? public_path($logoRel) : null;
        $hasLogo = $logoPath && file_exists($logoPath);
        $logoSrc = null;
        if ($hasLogo) {
            $bytes = @file_get_contents($logoPath);
            if ($bytes !== false) {
                $info = @getimagesize($logoPath);
                $mime = $info['mime'] ?? 'image/png';
                $logoSrc = 'data:' . $mime . ';base64,' . base64_encode($bytes);
            }
        }
        $hasLogo = (bool) $logoSrc;
    @endphp

    {{-- ============== COVER PAGE ============== --}}
    <div class="cover">
        @if($hasLogo)
            <img src="{{ $logoSrc }}" alt="ConCure" class="cover-logo">
        @endif
        <div class="brand-mark">CONCURE&nbsp;&nbsp;CLOUD</div>
        <h1>Complete<br>Feature&nbsp;List</h1>
        <div class="subtitle">
            A comprehensive multi-tenant SaaS clinic management system covering
            clinical, operational, financial and administrative workflows.
        </div>
        <div class="accent-bar"></div>
        <div class="meta">
            <div class="meta-row">
                <div class="meta-cell">
                    Document reference<br>
                    <strong>FEATURES&middot;{{ date('Ymd') }}</strong>
                </div>
                <div class="meta-cell right">
                    Generated<br>
                    <strong>{{ date('F d, Y') }}</strong>
                </div>
            </div>
        </div>
    </div>

    {{-- The per-page header (logo + brand + date) is drawn on every page after the cover --}}
    {{-- via $canvas->page_script() in DashboardController::featuresPdf(). --}}

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

    <!-- ENT Module -->
    <div class="section">
        <div class="section-header success">ENT Module (Ear, Nose & Throat)</div>

        <div class="feature-grid">
            <div class="feature-column">
                <div class="feature-module">
                    <h3>ENT Clinical Records</h3>
                    <ul class="feature-list">
                        <li>Per-visit ENT encounter records (CRUD)</li>
                        <li>Chief complaint documentation</li>
                        <li>Ear examination (otoscopy findings)</li>
                        <li>Nose examination (anterior/posterior rhinoscopy)</li>
                        <li>Throat examination (oropharynx, tonsils, larynx)</li>
                        <li>Neck examination (lymph nodes, thyroid, masses)</li>
                        <li>Cranial nerves assessment</li>
                        <li>Diagnosis with ICD-10 coding</li>
                        <li>Treatment plan and prescribed medications</li>
                        <li>Follow-up date scheduling</li>
                        <li>Patient-level ENT profile (hearing, nasal, throat issues, dizziness)</li>
                        <li>ENT-related file uploads (audiograms, scans, images)</li>
                        <li>Linked visit context for continuity of care</li>
                        <li>Per-clinic module toggle (enable/disable)</li>
                    </ul>
                </div>
            </div>

            <div class="feature-column">
                <div class="feature-module">
                    <h3>Audiometry &amp; Hearing Tests</h3>
                    <ul class="feature-list">
                        <li>Pure-tone audiometry (air & bone conduction)</li>
                        <li>Speech audiometry with SRT (Speech Recognition Threshold)</li>
                        <li>Word Recognition Score (WRS) tracking</li>
                        <li>Tympanometry results recording</li>
                        <li>Bilateral ear data capture (left & right)</li>
                        <li>Frequency-by-frequency threshold entry (250 Hz–8 kHz)</li>
                        <li>Hearing-loss interpretation per ear:
                            normal, conductive, sensorineural, mixed</li>
                        <li>Tests linked to ENT records or standalone per patient</li>
                        <li>Test history timeline per patient</li>
                        <li>Date-stamped, performer-tracked test records</li>
                        <li>Multiple test types: pure tone, speech, tympanometry, other</li>
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
        <div class="section-header">Summary of Key Features</div>

        <div class="feature-grid">
            <div class="feature-column">
                <div class="summary-card">
                    <h3>Core Clinical Modules</h3>
                    <ul class="feature-list">
                        <li><strong>Patient Management:</strong> Complete profiles, medical history, vital signs, chronic conditions</li>
                        <li><strong>Prescription System:</strong> Digital prescriptions with drug database, PDF generation, multi-language support</li>
                        <li><strong>Laboratory:</strong> Lab requests, test results, reference ranges, PDF reports</li>
                        <li><strong>Radiology:</strong> Imaging requests, DICOM integration, report generation</li>
                        <li><strong>Appointments:</strong> Calendar scheduling, conflict detection, status tracking</li>
                        <li><strong>Nutrition Planning:</strong> Food database, meal planning, calorie tracking, diet plans</li>
                        <li><strong>Dental Module:</strong> Interactive dental charts, condition tracking, treatment planning, PDF export</li>
                        <li><strong>Pediatric Growth:</strong> WHO/CDC growth charts, percentile tracking, corrected age support</li>
                        <li><strong>ENT Module:</strong> Ear/nose/throat examinations, ICD-10 diagnoses, audiometry &amp; tympanometry tests</li>
                    </ul>
                </div>
            </div>

            <div class="feature-column">
                <div class="summary-card">
                    <h3>Business &amp; Operations</h3>
                    <ul class="feature-list">
                        <li><strong>Financial Management:</strong> Invoicing, payment tracking, expense management, profit/loss reports</li>
                        <li><strong>Marketing:</strong> Advertisement management, campaign tracking, analytics</li>
                        <li><strong>Reports &amp; Analytics:</strong> Patient statistics, revenue reports, activity tracking</li>
                        <li><strong>User Management:</strong> Role-based access, permissions, activity logs</li>
                        <li><strong>System Settings:</strong> Clinic configuration, branding, customization</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="feature-grid">
            <div class="feature-column">
                <div class="summary-card">
                    <h3>Technical Capabilities</h3>
                    <ul class="feature-list">
                        <li><strong>Multi-language:</strong> English, Arabic, Kurdish (Bahdini &amp; Sorani)</li>
                        <li><strong>PDF Generation:</strong> Prescriptions, lab reports, invoices, diet plans</li>
                        <li><strong>Import/Export:</strong> Excel support for patients, medicines, food database</li>
                        <li><strong>Smart Search:</strong> Real-time search, advanced filtering, Select2 integration</li>
                        <li><strong>Responsive Design:</strong> Works on desktop, tablet, and mobile devices</li>
                        <li><strong>Security:</strong> Role-based access, audit logging, data encryption</li>
                    </ul>
                </div>
            </div>

            <div class="feature-column">
                <div class="summary-card">
                    <h3>System Highlights</h3>
                    <table class="stat-grid" cellspacing="0" cellpadding="0">
                        <tr>
                            <td class="stat-cell"><span class="num">200+</span><span class="label">Features</span></td>
                            <td class="stat-cell"><span class="num">10</span><span class="label">Modules</span></td>
                            <td class="stat-cell"><span class="num">4</span><span class="label">Languages</span></td>
                            <td class="stat-cell"><span class="num">100%</span><span class="label">Cloud</span></td>
                        </tr>
                    </table>
                    <ul class="feature-list">
                        <li>HIPAA Compliant Ready</li>
                        <li>Real-time Data Sync</li>
                        <li>Automated Backups</li>
                        <li>24/7 Availability</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Benefits Section -->
        <div class="callout">
            <h3>Why Choose ConCure Cloud?</h3>
            <div class="feature-grid">
                <div class="feature-column">
                    <ul class="feature-list">
                        <li><strong>Efficiency:</strong> Streamline clinic operations and reduce paperwork</li>
                        <li><strong>Patient Care:</strong> Better patient management and treatment tracking</li>
                        <li><strong>Cost-Effective:</strong> Reduce operational costs and increase revenue</li>
                    </ul>
                </div>
                <div class="feature-column">
                    <ul class="feature-list">
                        <li><strong>Secure:</strong> Enterprise-grade security and data protection</li>
                        <li><strong>Scalable:</strong> Grows with your clinic from small to large practices</li>
                        <li><strong>Support:</strong> Dedicated customer support and training</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer-end">
        <p><strong>ConCure Cloud</strong> &middot; Comprehensive Clinic Management System</p>
        <p>Version 1.0 &nbsp;|&nbsp; &copy; {{ date('Y') }} ConCure. All rights reserved.</p>
        <p>Total Features: 200+ &nbsp;|&nbsp; Generated {{ date('F d, Y \a\t h:i A') }}</p>
    </div>
</body>
</html>
