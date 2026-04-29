<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>ConCure Cloud - قائمة الميزات الكاملة</title>
    <style>
        /* mPDF respects @page directives. The first page (cover) gets full
           bleed (no margins) and no footer; subsequent pages use the named
           "brandFooter" defined below. */
        @page { sheet-size: A4; margin: 22mm 16mm 24mm 16mm; }
        @page :first { margin: 0; }

        body {
            font-family: amiri, dejavusans, sans-serif;
            font-size: 11pt;
            line-height: 1.7;
            color: #1f2937;
            direction: rtl;
        }
        strong { color: #0b1220; font-weight: 700; }
        h1, h2, h3, h4 { color: #0b1220; font-family: amiri, sans-serif; }
        p { margin-bottom: 6px; }

        /* ============ COVER PAGE ============ */
        .cover {
            page-break-after: always;
            background: #0b3a8c;
            color: #fff;
            padding: 36mm 22mm 26mm 22mm;
            text-align: right;
        }
        .cover .brand-mark {
            font-size: 9pt; letter-spacing: 6px; text-transform: uppercase;
            opacity: .9; margin-bottom: 26mm; font-weight: 700;
            font-family: dejavusans, sans-serif;
        }
        .cover .cover-logo {
            display: block;
            width: 36mm;
            margin-bottom: 10mm;
            background: #ffffff;
            padding: 4mm;
        }
        .cover h1 {
            color: #fff;
            font-size: 36pt; line-height: 1.1; font-weight: 700;
            margin-bottom: 8mm;
        }
        .cover .subtitle {
            font-size: 13pt; line-height: 1.7;
            opacity: .94; font-weight: 400;
        }
        .cover .accent-bar {
            width: 38mm; height: 1.1mm; background: #ffffff;
            margin-top: 26mm; margin-bottom: 7mm;
        }
        .cover .meta {
            border-top: 0.5pt solid rgba(255,255,255,.5);
            padding-top: 6mm;
            font-size: 9pt; opacity: .95;
        }
        .cover .meta strong { color: #fff; font-weight: 700; font-size: 10pt; }

        /* ============ SECTIONS ============ */
        .section { margin-bottom: 14px; }
        .section-header {
            background: #0b3a8c;
            color: #fff;
            padding: 8px 14px;
            margin-bottom: 12px;
            font-size: 12pt;
            font-weight: 700;
            page-break-after: avoid;
        }

        .overview-grid {
            width: 100%; margin-bottom: 12px;
            background: #f7f9fc; border: 0.5pt solid #d8e2f1;
        }
        .overview-grid td {
            width: 33.33%; padding: 12px 14px; vertical-align: top;
            border-left: 0.5pt solid #d8e2f1;
        }
        .overview-grid td:last-child { border-left: 0; }
        .overview-grid h3 {
            font-size: 10pt; color: #0b3a8c; margin-bottom: 4px; font-weight: 700;
        }
        .overview-grid p { font-size: 10pt; color: #374151; }

        .feature-grid { width: 100%; margin-bottom: 8px; }
        .feature-grid td.feature-column {
            width: 50%; padding: 0 6px; vertical-align: top;
        }

        .feature-module {
            margin-bottom: 12px;
            background: #fafbfd;
            border: 0.5pt solid #e1e6ef;
            border-top: 2pt solid #0b3a8c;
            padding: 9px 12px 10px 12px;
            page-break-inside: avoid;
        }
        .feature-module h3 {
            font-size: 11pt; color: #0b3a8c;
            margin-bottom: 6px; font-weight: 700;
            border-bottom: 0.5pt solid #d8e2f1; padding-bottom: 4px;
        }

        .feature-list { list-style: none; padding: 0; margin: 0; }
        .feature-list li {
            padding-right: 12px; margin-bottom: 3px; position: relative;
            line-height: 1.7; color: #1f2937; font-size: 10.5pt;
        }
        .feature-list li:before {
            content: "•"; position: absolute;
            right: 2px; top: 0; color: #0b3a8c; font-weight: bold;
        }

        .subsection { margin-bottom: 10px; }
        .subsection h4 {
            font-size: 10.5pt; margin-bottom: 6px; color: #0b3a8c; font-weight: 700;
            border-bottom: 0.5pt solid #d8e2f1; padding-bottom: 3px;
        }

        .role-list { list-style: none; padding: 0; margin: 0; }
        .role-list li {
            padding-right: 12px; margin-bottom: 3px; position: relative;
            font-size: 10.5pt; line-height: 1.7;
        }
        .role-list li:before {
            content: "•"; position: absolute; right: 2px; top: 0;
            color: #0b3a8c; font-weight: bold;
        }
        .role-list strong { color: #0b1220; }

        .stat-grid { width: 100%; margin-bottom: 12px; border-collapse: collapse; }
        .stat-cell {
            width: 25%; padding: 10px 4px;
            border: 0.5pt solid #d8e2f1; text-align: center; vertical-align: middle;
            background: #f7f9fc;
        }
        .stat-cell .num {
            font-size: 18pt; color: #0b3a8c; font-weight: 700;
            display: block; margin-bottom: 2px;
            font-family: dejavusans, sans-serif;
        }
        .stat-cell .label {
            font-size: 8pt; color: #4b5563;
        }

        .summary-card {
            border: 0.5pt solid #d8e2f1;
            border-top: 2.5pt solid #0b3a8c;
            padding: 10px 12px; margin-bottom: 10px;
            background: #fafbfd;
        }

        .summary-card h3 {
            font-size: 11pt; color: #0b3a8c; font-weight: 700;
            border-bottom: 0.5pt solid #d8e2f1; padding-bottom: 4px; margin-bottom: 7px;
        }

        .callout {
            background: #eef3fb; border-right: 4pt solid #0b3a8c;
            padding: 12px 14px; margin-top: 12px;
        }
        .callout h3 {
            font-size: 11pt; color: #0b3a8c;
            margin-bottom: 7px; font-weight: 700;
        }

        .footer-end {
            text-align: center; padding-top: 12px; margin-top: 18px;
            border-top: 1pt solid #0b3a8c; font-size: 9pt; color: #4b5563;
        }
        .footer-end p { margin-bottom: 3px; }
    </style>
</head>
<body dir="rtl">

@php
    // Build a base64 logo for the cover (mPDF respects the data URI fine,
    // matches the English DomPDF behaviour and avoids any path lookup).
    $logoRel = \App\Http\Controllers\Master\SettingsController::getMasterBrandingLogoForPdfRelPath();
    $logoPath = $logoRel ? public_path($logoRel) : null;
    $logoSrc = null;
    if ($logoPath && file_exists($logoPath)) {
        $bytes = @file_get_contents($logoPath);
        if ($bytes !== false) {
            $info = @getimagesize($logoPath);
            $mime = $info['mime'] ?? 'image/png';
            $logoSrc = 'data:' . $mime . ';base64,' . base64_encode($bytes);
        }
    }
    $hasLogo = (bool) $logoSrc;
    $footerLogoSrc = $footerLogoSrc ?? $logoSrc;
@endphp

{{-- Define the named footer used on body pages. The cover (page 1) keeps
     it OFF; we re-enable it right after the cover's <pagebreak />. --}}
<htmlpagefooter name="brandFooter">
    <table dir="rtl" width="100%" style="border-top:0.5pt solid #0b3a8c;padding-top:3mm;font-family:amiri;font-size:9pt;color:#4b5563;">
        <tr>
            <td width="55%" align="right" style="vertical-align:middle;">
                @if($footerLogoSrc)
                    <img src="{{ $footerLogoSrc }}" style="width:11mm;height:11mm;vertical-align:middle;" />
                @endif
                &nbsp;<span style="color:#0b3a8c;font-weight:bold;">CONCURE CLOUD</span>
                &nbsp;·&nbsp;قائمة الميزات الكاملة
            </td>
            <td width="45%" align="left" style="vertical-align:middle;">
                صفحة {PAGENO} من {nbpg}
            </td>
        </tr>
    </table>
</htmlpagefooter>

{{-- Disable the footer for the cover. --}}
<sethtmlpagefooter name="brandFooter" page="O" value="off" />

{{-- ============== COVER PAGE ============== --}}
<div class="cover">
    @if($hasLogo)
        <img src="{{ $logoSrc }}" alt="ConCure" class="cover-logo">
    @endif
    <div class="brand-mark">CONCURE&nbsp;&nbsp;CLOUD</div>
    <h1>القائمة الكاملة<br>للميزات</h1>
    <div class="subtitle">
        نظامٌ شاملٌ متعدد المستأجرين لإدارة العيادات الطبية يُقدَّم كخدمة سحابية،
        ويغطي العمليات السريرية والتشغيلية والمالية والإدارية.
    </div>
    <div class="accent-bar"></div>
    <div class="meta">
        <table width="100%" dir="rtl"><tr>
            <td align="right">
                مرجع المستند<br>
                <strong>FEATURES&middot;{{ date('Ymd') }}</strong>
            </td>
            <td align="left">
                تاريخ الإنشاء<br>
                <strong>{{ date('Y/m/d') }}</strong>
            </td>
        </tr></table>
    </div>
</div>

{{-- Re-enable the footer for everything from the next page on. --}}
<sethtmlpagefooter name="brandFooter" page="ALL" value="on" />

<!-- نظرة عامة على التطبيق -->
<div class="section">
    <div class="section-header">نظرة عامة على التطبيق</div>
    <p style="margin-bottom: 10px;">ConCure Cloud هو نظامٌ شاملٌ متعدد المستأجرين لإدارة العيادات الطبية كخدمة سحابية، مصمَّمٌ لتبسيط العمليات الرعائية الصحية.</p>
    <table class="overview-grid" dir="rtl"><tr>
        <td>
            <h3>دعم متعدد اللغات</h3>
            <p>الإنجليزية، العربية، الكردية (کوردی)</p>
        </td>
        <td>
            <h3>صلاحيات حسب الدور</h3>
            <p>15 دوراً للمستخدمين بصلاحيات دقيقة</p>
        </td>
        <td>
            <h3>متعدد العيادات</h3>
            <p>عزل كامل للبيانات لكل عيادة</p>
        </td>
    </tr></table>
</div>


<!-- الوحدات الأساسية -->
<div class="section">
    <div class="section-header">الوحدات الأساسية</div>
    <table class="feature-grid" dir="rtl"><tr>
        <td class="feature-column">
            <div class="feature-module">
                <h3>إدارة المرضى</h3>
                <ul class="feature-list">
                    <li>ملفات مرضى كاملة مع البيانات الديموغرافية</li>
                    <li>تتبّع التاريخ المرضي</li>
                    <li>تسجيل العلامات الحيوية (ضغط الدم، النبض، الحرارة، الوزن، الطول، مؤشر كتلة الجسم)</li>
                    <li>إدارة الأمراض المزمنة</li>
                    <li>تتبّع الحساسية والأدوية</li>
                    <li>توثيق التاريخ العائلي</li>
                    <li>إرفاق الملفات (التقارير الطبية، الصور)</li>
                    <li>البحث عن المرضى وتصفيتهم</li>
                    <li>تصدير بيانات المرضى (Excel)</li>
                    <li>العمليات الجماعية (الحذف، التفريغ الكامل)</li>
                    <li>عرض الجدول الزمني للمريض</li>
                    <li>بحث ذكي مع تكامل Select2</li>
                </ul>
            </div>
        </td>
        <td class="feature-column">
            <div class="feature-module">
                <h3>نظام الوصفات الطبية</h3>
                <ul class="feature-list">
                    <li>إنشاء وصفات طبية رقمية</li>
                    <li>تكامل قاعدة بيانات الأدوية</li>
                    <li>إدارة الجرعات والتكرار</li>
                    <li>تتبّع مدة العلاج</li>
                    <li>التعليمات والملاحظات</li>
                    <li>وضع الوصفة المبسَّطة (إدخال سريع)</li>
                    <li>إنشاء PDF بهوية العيادة</li>
                    <li>دعم قوالب وصفات مخصَّصة (رفع قالب يحمل هوية العيادة)</li>
                    <li>إمكانية الطباعة</li>
                    <li>سجلّ الوصفات الطبية</li>
                    <li>تنبيهات تداخل الأدوية</li>
                    <li>قوالب الوصفات الجاهزة</li>
                    <li>طباعة الوصفات بلغات متعددة</li>
                    <li>تعبئة تلقائية للطول والوزن من آخر فحص أو قياس نمو</li>
                </ul>
            </div>
        </td>
    </tr></table>
</div>

<pagebreak />

<!-- إدارة المواعيد والمختبر -->
<div class="section">
    <div class="section-header">إدارة المواعيد والمختبر</div>
    <table class="feature-grid" dir="rtl"><tr>
        <td class="feature-column">
            <div class="feature-module">
                <h3>إدارة المواعيد</h3>
                <ul class="feature-list">
                    <li>عرض التقويم (يومي، أسبوعي، شهري)</li>
                    <li>جدولة المواعيد</li>
                    <li>إسناد المواعيد للأطباء</li>
                    <li>تتبّع الحالة (مجدول، مؤكد، مكتمل، ملغى)</li>
                    <li>أنواع المواعيد وأسبابها</li>
                    <li>الملاحظات والتعليقات</li>
                    <li>نافذة سريعة لإنشاء موعد</li>
                    <li>تكامل البحث عن المرضى</li>
                    <li>كشف التعارض في المواعيد</li>
                    <li>تذكيرات المواعيد</li>
                    <li>مؤشرات الحالة المُلوَّنة</li>
                </ul>
            </div>
        </td>
        <td class="feature-column">
            <div class="feature-module">
                <h3>إدارة المختبر</h3>
                <ul class="feature-list">
                    <li>كتالوج فحوصات المختبر</li>
                    <li>إنشاء طلبات الفحوصات</li>
                    <li>عدة فحوصات في الطلب الواحد</li>
                    <li>إدخال نتائج الفحوصات</li>
                    <li>مؤشرات النطاق الطبيعي</li>
                    <li>تتبّع الحالة (قيد الانتظار، قيد التنفيذ، مكتمل)</li>
                    <li>إنشاء تقارير PDF</li>
                    <li>إسناد الفنيين المختصين</li>
                    <li>تتبّع سجلّ الفحوصات</li>
                    <li>تمييز الفحوصات العاجلة</li>
                    <li>إدارة الفحوصات (إضافة/تعديل/حذف)</li>
                </ul>
            </div>
        </td>
    </tr></table>
</div>

<!-- إدارة الأشعة والتغذية -->
<div class="section">
    <div class="section-header">إدارة الأشعة والتغذية</div>
    <table class="feature-grid" dir="rtl"><tr>
        <td class="feature-column">
            <div class="feature-module">
                <h3>إدارة الأشعة</h3>
                <ul class="feature-list">
                    <li>كتالوج فحوصات الأشعة (X-Ray، CT، MRI، الموجات فوق الصوتية، إلخ)</li>
                    <li>إنشاء طلبات الأشعة</li>
                    <li>عدة فحوصات في الطلب الواحد</li>
                    <li>المؤشرات السريرية</li>
                    <li>النتائج والانطباعات</li>
                    <li>تتبّع الحالة</li>
                    <li>إنشاء تقارير PDF</li>
                    <li>إسناد طبيب الأشعة</li>
                    <li>إرفاق الصور</li>
                    <li>تمييز الطلبات العاجلة</li>
                    <li>إدارة فحوصات الأشعة</li>
                </ul>
            </div>
        </td>
        <td class="feature-column">
            <div class="feature-module">
                <h3>التغذية وتخطيط الحمية</h3>
                <ul class="feature-list">
                    <li>قاعدة بيانات شاملة للأطعمة</li>
                    <li>المعلومات الغذائية (السعرات، البروتين، الكربوهيدرات، الدهون)</li>
                    <li>تصنيف المجموعات الغذائية</li>
                    <li>إنشاء خطط الحمية</li>
                    <li>تخطيط الوجبات (الإفطار، الغداء، العشاء، الوجبات الخفيفة)</li>
                    <li>إدارة أحجام الحصص</li>
                    <li>تتبّع السعرات الحرارية</li>
                    <li>القيود الغذائية</li>
                    <li>البحث في الأطعمة وتصفيتها</li>
                    <li>استيراد/تصدير بيانات الأطعمة (Excel)</li>
                    <li>إضافة أصناف غذائية مخصَّصة</li>
                    <li>توصيات غذائية</li>
                    <li>إنشاء PDF لخطة الحمية</li>
                </ul>
            </div>
        </td>
    </tr></table>
</div>

<pagebreak />

<!-- وحدات الأسنان والأطفال -->
<div class="section">
    <div class="section-header">وحدات الأسنان والأطفال</div>
    <table class="feature-grid" dir="rtl"><tr>
        <td class="feature-column">
            <div class="feature-module">
                <h3>وحدة الأسنان</h3>
                <ul class="feature-list">
                    <li>مخطّط أسنان تفاعلي (للبالغين والأطفال)</li>
                    <li>تتبّع حالة كل سنّ على حدة</li>
                    <li>مؤشرات حالة الأسنان البصرية المُلوَّنة</li>
                    <li>مكتبة حالات الأسنان (أكثر من 30 حالة)</li>
                    <li>تخطيط العلاج وتتبّعه</li>
                    <li>سجلّ العلاجات لكل سنّ</li>
                    <li>ورقة عمل علاج الجذور (الإندودنتي) مع مكتبة قنوات FDI</li>
                    <li>تصوير أسنان مرتبط بمستوى السنّ</li>
                    <li>عروض المخطط البسيطة والتفصيلية</li>
                    <li>تصدير مخطط الأسنان كـ PDF</li>
                    <li>اختيار عدة أسنان وتحديثات جماعية</li>
                    <li>دليل حالات قابل للبحث</li>
                    <li>نظام ترقيم الأسنان (FDI)</li>
                    <li>طلبات مختبرات الأسنان مع تكامل المختبرات الخارجية</li>
                    <li>دليل وإدارة مختبرات الأسنان الخارجية</li>
                    <li>إسناد طلب المختبر إلى فني الأسنان أو مصمّم CAD/CAM</li>
                    <li>سير عمل مخصَّص للفنيين/المصمّمين (مقتصر على الطلبات المُسنَدة)</li>
                    <li>رفع نتائج طلب المختبر وتتبّع إكماله</li>
                </ul>
            </div>
        </td>
        <td class="feature-column">
            <div class="feature-module">
                <h3>مخطّط نمو الأطفال</h3>
                <ul class="feature-list">
                    <li>بيانات النمو المرجعية لمنظمة الصحة العالمية WHO ومراكز CDC</li>
                    <li>مخططات الوزن حسب العمر (0–5 سنوات)</li>
                    <li>مخططات الطول/القامة حسب العمر</li>
                    <li>مخططات محيط الرأس حسب العمر</li>
                    <li>مخططات مؤشر كتلة الجسم حسب العمر</li>
                    <li>مخططات الوزن حسب الطول/القامة</li>
                    <li>منحنيات المئينات (3 إلى 97)</li>
                    <li>تسجيل قياسات النمو وسجلّها</li>
                    <li>دعم العمر المُصحَّح للخدّج/منخفضي الوزن عند الولادة</li>
                    <li>تتبّع وزن الولادة وعمر الحمل</li>
                    <li>تصدير مخطط النمو كـ PDF بتنسيق منسَّق</li>
                    <li>قائمة مرضى الأطفال مع تصفية حسب العمر</li>
                </ul>
            </div>
        </td>
    </tr></table>
</div>

<!-- وحدة الأنف والأذن والحنجرة -->
<div class="section">
    <div class="section-header">وحدة الأنف والأذن والحنجرة</div>
    <table class="feature-grid" dir="rtl"><tr>
        <td class="feature-column">
            <div class="feature-module">
                <h3>السجلات السريرية للأنف والأذن والحنجرة</h3>
                <ul class="feature-list">
                    <li>سجلات لكل زيارة (إضافة/تعديل/حذف)</li>
                    <li>توثيق الشكوى الرئيسية</li>
                    <li>فحص الأذن (نتائج تنظير الأذن)</li>
                    <li>فحص الأنف (التنظير الأنفي الأمامي/الخلفي)</li>
                    <li>فحص الحنجرة (الفم البلعومي، اللوزتان، الحنجرة)</li>
                    <li>فحص الرقبة (العقد الليمفاوية، الغدة الدرقية، الكتل)</li>
                    <li>تقييم الأعصاب القحفية</li>
                    <li>التشخيص بترميز ICD-10</li>
                    <li>خطة العلاج والأدوية الموصوفة</li>
                    <li>جدولة موعد المتابعة</li>
                    <li>ملف ENT على مستوى المريض (السمع، المشاكل الأنفية، البلعوم، الدوار)</li>
                    <li>رفع ملفات متعلقة بالـ ENT (مخططات السمع، الفحوصات، الصور)</li>
                    <li>سياق الزيارة المرتبط لاستمرارية الرعاية</li>
                    <li>تفعيل/تعطيل الوحدة لكل عيادة</li>
                </ul>
            </div>
        </td>
        <td class="feature-column">
            <div class="feature-module">
                <h3>قياس السمع واختباراته</h3>
                <ul class="feature-list">
                    <li>قياس السمع بالنغمة النقية (التوصيل الهوائي والعظمي)</li>
                    <li>قياس السمع الكلامي مع SRT (عتبة استقبال الكلام)</li>
                    <li>تتبّع درجة التعرّف على الكلمات (WRS)</li>
                    <li>تسجيل نتائج قياس الطبلة</li>
                    <li>التقاط بيانات للأذنين (اليمنى واليسرى)</li>
                    <li>إدخال العتبات حسب التردد (250 هرتز إلى 8 كيلوهرتز)</li>
                    <li>تفسير ضعف السمع لكل أذن: طبيعي، توصيلي، حسّي عصبي، مختلط</li>
                    <li>ربط الاختبارات بسجلّ ENT أو مستقلة لكل مريض</li>
                    <li>الجدول الزمني لتاريخ الاختبارات للمريض</li>
                    <li>سجلات اختبارات بتاريخ ومُجريها</li>
                    <li>أنواع متعددة من الاختبارات: نغمة نقية، كلامي، طبلي، أخرى</li>
                </ul>
            </div>
        </td>
    </tr></table>
</div>

<pagebreak />

<!-- الإدارة المالية -->
<div class="section">
    <div class="section-header">الإدارة المالية</div>
    <table class="feature-grid" dir="rtl"><tr>
        <td class="feature-column">
            <div class="subsection">
                <h4>الفوترة</h4>
                <ul class="feature-list">
                    <li>إنشاء الفواتير وإدارتها</li>
                    <li>عدة بنود في الفاتورة</li>
                    <li>احتساب الضرائب</li>
                    <li>دعم الخصومات</li>
                    <li>تتبّع الدفعات</li>
                    <li>إدارة الحالة (مسودة، مُرسَلة، مدفوعة، متأخرة)</li>
                    <li>إنشاء PDF</li>
                    <li>إرسال الفواتير عبر البريد</li>
                    <li>سجلّ الفواتير</li>
                    <li>الدفعات الجزئية</li>
                </ul>
            </div>
        </td>
        <td class="feature-column">
            <div class="subsection">
                <h4>إدارة المصاريف</h4>
                <ul class="feature-list">
                    <li>تسجيل المصاريف</li>
                    <li>إدارة الفئات</li>
                    <li>تتبّع الموردين</li>
                    <li>سير عمل الموافقات</li>
                    <li>إرفاق الإيصالات</li>
                    <li>تتبّع الحالة (قيد الانتظار، موافَق عليها، مرفوضة)</li>
                    <li>تقارير المصاريف</li>
                    <li>تتبّع الميزانية</li>
                </ul>
            </div>
        </td>
    </tr></table>
    <div class="subsection" style="margin-top: 8px;">
        <h4>التقارير المالية</h4>
        <ul class="feature-list">
            <li>تتبّع الإيرادات</li>
            <li>تحليل المصاريف</li>
            <li>قوائم الأرباح/الخسائر</li>
            <li>تقارير التدفق النقدي</li>
            <li>الفواتير المستحقة</li>
            <li>الملخصات الشهرية/السنوية</li>
            <li>احتساب هامش الربح</li>
        </ul>
    </div>
</div>

<!-- الميزات المتقدمة -->
<div class="section">
    <div class="section-header">الميزات المتقدمة</div>
    <table class="feature-grid" dir="rtl"><tr>
        <td class="feature-column">
            <div class="subsection">
                <h4>قوالب الفحوصات المخصَّصة</h4>
                <ul class="feature-list">
                    <li>إنشاء نماذج فحص مخصَّصة</li>
                    <li>منشئ نماذج ديناميكي</li>
                    <li>أنواع متعددة من الحقول (نص، رقم، قائمة، خانة اختيار، إلخ)</li>
                    <li>تنظيم الأقسام</li>
                    <li>إسناد القوالب للمرضى</li>
                    <li>قوالب خاصة بحالات مرضية</li>
                    <li>قوالب حسب التخصص</li>
                    <li>تفعيل/تعطيل القوالب</li>
                    <li>إحصائيات الاستخدام</li>
                </ul>
            </div>
            <div class="subsection">
                <h4>نظام الإعلانات</h4>
                <ul class="feature-list">
                    <li>إنشاء وإدارة الإعلانات</li>
                    <li>دعم رفع الصور</li>
                    <li>العنوان والوصف</li>
                    <li>إدارة الروابط (URL)</li>
                    <li>حالة التفعيل/التعطيل</li>
                    <li>عرض على لوحة التحكم</li>
                    <li>دعم العرض المتتالي (Carousel)</li>
                </ul>
            </div>
        </td>
        <td class="feature-column">
            <div class="subsection">
                <h4>قاعدة بيانات الأدوية</h4>
                <ul class="feature-list">
                    <li>كتالوج شامل للأدوية</li>
                    <li>الأسماء العامة والتجارية</li>
                    <li>أشكال الجرعات (أقراص، كبسولات، شراب، حقن، إلخ)</li>
                    <li>التركيز/الكميات الفعالة</li>
                    <li>التصنيف</li>
                    <li>معلومات الشركة المُصنِّعة</li>
                    <li>البحث والتصفية</li>
                    <li>الاستيراد/التصدير (Excel)</li>
                    <li>العمليات الجماعية</li>
                </ul>
            </div>
            <div class="subsection">
                <h4>المراسلة الداخلية</h4>
                <ul class="feature-list">
                    <li>إرسال الرسائل بين المستخدمين</li>
                    <li>إدارة صندوق الوارد</li>
                    <li>حالة المقروء/غير المقروء</li>
                    <li>إشعارات الرسائل</li>
                    <li>إمكانية الردّ</li>
                    <li>حذف الرسائل</li>
                    <li>شارة عدد الرسائل غير المقروءة</li>
                </ul>
            </div>
        </td>
    </tr></table>
</div>

<pagebreak />

<!-- إدارة المستخدمين والأمان -->
<div class="section">
    <div class="section-header">إدارة المستخدمين والأمان</div>
    <table class="feature-grid" dir="rtl"><tr>
        <td class="feature-column">
            <div class="subsection">
                <h4>أدوار المستخدمين</h4>
                <ul class="role-list">
                    <li><strong>المسؤول الأعلى (Super Admin):</strong> صلاحيات على مستوى النظام بالكامل</li>
                    <li><strong>المسؤول الرئيسي (Master Admin):</strong> إدارة عيادات متعددة</li>
                    <li><strong>المسؤول (Admin):</strong> مدير العيادة</li>
                    <li><strong>الطبيب:</strong> اختصاصي طبي</li>
                    <li><strong>اختصاصي التغذية:</strong> الحمية والتغذية</li>
                    <li><strong>الصيدلي:</strong> عمليات الصيدلية</li>
                    <li><strong>قسم المختبر:</strong> فني مختبر</li>
                    <li><strong>قسم الأشعة:</strong> فني أشعة</li>
                    <li><strong>قسم الأسنان:</strong> طبيب أسنان</li>
                    <li><strong>فني الأسنان:</strong> فني مختبر الأسنان (طلبات مُسنَدة فقط)</li>
                    <li><strong>مصمّم CAD/CAM:</strong> مصمّم CAD/CAM للأسنان (طلبات مُسنَدة فقط)</li>
                    <li><strong>مساعد:</strong> دعم إداري</li>
                    <li><strong>الممرّض/ة:</strong> الكادر التمريضي</li>
                    <li><strong>المحاسب:</strong> العمليات المالية</li>
                    <li><strong>المريض:</strong> خدمة ذاتية للمريض</li>
                </ul>
            </div>
        </td>
        <td class="feature-column">
            <div class="subsection">
                <h4>الصلاحيات والتحكم في الوصول</h4>
                <ul class="feature-list">
                    <li>نظام صلاحيات دقيق (أكثر من 60 صلاحية)</li>
                    <li>التحكم في الوصول حسب الدور (RBAC)</li>
                    <li>إسناد صلاحيات مخصَّصة</li>
                    <li>صلاحيات على مستوى الوحدة</li>
                    <li>صلاحيات على مستوى الإجراء (عرض، إنشاء، تعديل، حذف)</li>
                    <li>تفعيل/تعطيل المستخدم</li>
                    <li>إدارة انتهاء صلاحية الحساب</li>
                    <li>وظيفة إعادة تعيين كلمة المرور</li>
                    <li>تتبّع آخر تسجيل دخول</li>
                </ul>
            </div>
        </td>
    </tr></table>
    <div class="subsection" style="margin-top: 8px;">
        <h4>التدقيق والأمان</h4>
        <ul class="feature-list">
            <li>تسجيل تدقيق شامل</li>
            <li>تتبّع نشاط المستخدم</li>
            <li>تسجيل الدخول والخروج</li>
            <li>تتبّع محاولات الدخول الفاشلة</li>
            <li>تسجيل عناوين IP</li>
            <li>تتبّع وكيل المستخدم (User Agent)</li>
            <li>سجلّ تغييرات البيانات</li>
            <li>تصفية وبحث في سجلّ التدقيق</li>
        </ul>
    </div>
</div>

<!-- لوحة التحكم الرئيسية (إدارة SaaS) -->
<div class="section">
    <div class="section-header">لوحة التحكم الرئيسية (إدارة SaaS)</div>
    <table class="feature-grid" dir="rtl"><tr>
        <td class="feature-column">
            <div class="subsection">
                <h4>إدارة العيادات</h4>
                <ul class="feature-list">
                    <li>بنية متعددة المستأجرين</li>
                    <li>تسجيل العيادات والموافقة عليها</li>
                    <li>تفعيل/تعطيل العيادات</li>
                    <li>عزل كامل للبيانات لكل عيادة</li>
                    <li>إدارة ملف العيادة</li>
                    <li>إعادة تعيين كلمة مرور المسؤول</li>
                    <li>إحصائيات العيادة</li>
                </ul>
            </div>
        </td>
        <td class="feature-column">
            <div class="subsection">
                <h4>تقارير النظام</h4>
                <ul class="feature-list">
                    <li>إحصائيات على مستوى النظام</li>
                    <li>تتبّع نمو العيادات</li>
                    <li>توزيع المستخدمين حسب الدور</li>
                    <li>إحصائيات المرضى عبر العيادات</li>
                    <li>تحليلات الوصفات الطبية</li>
                    <li>نظرة عامة مالية</li>
                    <li>تتبّع الاشتراكات</li>
                    <li>إدارة الدفعات</li>
                </ul>
            </div>
        </td>
    </tr></table>
    <div class="subsection" style="margin-top: 10px;">
        <h4>صيانة النظام</h4>
        <ul class="feature-list">
            <li>مراقبة صحة النظام</li>
            <li>فحوصات صحة قاعدة البيانات</li>
            <li>مراقبة التخزين</li>
            <li>إدارة الذاكرة المؤقتة (Cache)</li>
            <li>تحديثات الخادم</li>
        </ul>
    </div>
</div>

<!-- الميزات التقنية والتكاملات -->
<div class="section">
    <div class="section-header">الميزات التقنية والتكاملات</div>
    <table class="feature-grid" dir="rtl"><tr>
        <td class="feature-column">
            <div class="subsection">
                <h4>إنشاء PDF</h4>
                <ul class="feature-list">
                    <li>الوصفات الطبية</li>
                    <li>تقارير المختبر</li>
                    <li>تقارير الأشعة</li>
                    <li>الفواتير</li>
                    <li>خطط الحمية</li>
                    <li>هوية بصرية مخصَّصة</li>
                </ul>
            </div>
            <div class="subsection">
                <h4>الاستيراد/التصدير</h4>
                <ul class="feature-list">
                    <li>تصدير بيانات المرضى</li>
                    <li>استيراد/تصدير الأدوية</li>
                    <li>استيراد/تصدير قاعدة بيانات الأطعمة</li>
                    <li>دعم تنسيق Excel</li>
                    <li>عمليات بيانات جماعية</li>
                </ul>
            </div>
        </td>
        <td class="feature-column">
            <div class="subsection">
                <h4>البحث والتصفية</h4>
                <ul class="feature-list">
                    <li>بحث ذكي مع Select2</li>
                    <li>تصفية متقدمة</li>
                    <li>تصفية حسب نطاق التاريخ</li>
                    <li>تصفية حسب الحالة</li>
                    <li>تصفية حسب الفئة</li>
                    <li>بحث في الوقت الفعلي</li>
                </ul>
            </div>
        </td>
    </tr></table>
</div>

<pagebreak />

<!-- ملخص الميزات الرئيسية -->
<div class="section">
    <div class="section-header">ملخص الميزات الرئيسية</div>
    <table class="feature-grid" dir="rtl"><tr>
        <td class="feature-column">
            <div class="summary-card">
                <h3>الوحدات السريرية الأساسية</h3>
                <ul class="feature-list">
                    <li><strong>إدارة المرضى:</strong> ملفات كاملة، تاريخ مرضي، علامات حيوية، أمراض مزمنة</li>
                    <li><strong>نظام الوصفات:</strong> وصفات طبية رقمية مع قاعدة بيانات الأدوية، إنشاء PDF، دعم متعدد اللغات</li>
                    <li><strong>المختبر:</strong> طلبات فحوصات، نتائج، نطاقات مرجعية، تقارير PDF</li>
                    <li><strong>الأشعة:</strong> طلبات تصوير، تكامل DICOM، إنشاء التقارير</li>
                    <li><strong>المواعيد:</strong> جدولة بالتقويم، كشف التعارض، تتبّع الحالة</li>
                    <li><strong>تخطيط التغذية:</strong> قاعدة بيانات الأطعمة، تخطيط الوجبات، تتبّع السعرات، خطط الحمية</li>
                    <li><strong>وحدة الأسنان:</strong> مخططات أسنان تفاعلية، تتبّع الحالات، تخطيط العلاج، تصدير PDF</li>
                    <li><strong>نمو الأطفال:</strong> مخططات WHO/CDC، تتبّع المئينات، دعم العمر المُصحَّح</li>
                    <li><strong>وحدة الأنف والأذن والحنجرة:</strong> فحوصات ENT، تشخيصات ICD-10، اختبارات قياس السمع والطبلة</li>
                </ul>
            </div>
        </td>
        <td class="feature-column">
            <div class="summary-card">
                <h3>الأعمال والعمليات</h3>
                <ul class="feature-list">
                    <li><strong>الإدارة المالية:</strong> الفوترة، تتبّع الدفعات، إدارة المصاريف، تقارير الأرباح/الخسائر</li>
                    <li><strong>التسويق:</strong> إدارة الإعلانات، تتبّع الحملات، التحليلات</li>
                    <li><strong>التقارير والتحليلات:</strong> إحصائيات المرضى، تقارير الإيرادات، تتبّع النشاط</li>
                    <li><strong>إدارة المستخدمين:</strong> صلاحيات حسب الدور، تحكم بالوصول، سجلات النشاط</li>
                    <li><strong>إعدادات النظام:</strong> تهيئة العيادة، الهوية البصرية، التخصيص</li>
                </ul>
            </div>
        </td>
    </tr></table>
</div>



<div class="section">
    <table class="feature-grid" dir="rtl"><tr>
        <td class="feature-column">
            <div class="summary-card">
                <h3>القدرات التقنية</h3>
                <ul class="feature-list">
                    <li><strong>تعدد اللغات:</strong> الإنجليزية، العربية، الكردية (بهديني وسوراني)</li>
                    <li><strong>إنشاء PDF:</strong> الوصفات، تقارير المختبر، الفواتير، خطط الحمية</li>
                    <li><strong>الاستيراد/التصدير:</strong> دعم Excel للمرضى والأدوية وقاعدة بيانات الأطعمة</li>
                    <li><strong>البحث الذكي:</strong> بحث في الوقت الفعلي، تصفية متقدمة، تكامل Select2</li>
                    <li><strong>تصميم متجاوب:</strong> يعمل على الحاسوب والجهاز اللوحي والهاتف المحمول</li>
                    <li><strong>الأمان:</strong> صلاحيات حسب الدور، تسجيل التدقيق، تشفير البيانات</li>
                </ul>
            </div>
        </td>
        <td class="feature-column">
            <div class="summary-card">
                <h3>أبرز ما يميّز النظام</h3>
                <table class="stat-grid" cellspacing="0" cellpadding="0">
                    <tr>
                        <td class="stat-cell"><span class="num">+200</span><span class="label">ميزة</span></td>
                        <td class="stat-cell"><span class="num">10</span><span class="label">وحدات</span></td>
                        <td class="stat-cell"><span class="num">4</span><span class="label">لغات</span></td>
                        <td class="stat-cell"><span class="num">100%</span><span class="label">سحابي</span></td>
                    </tr>
                </table>
                <ul class="feature-list">
                    <li>جاهز للتوافق مع HIPAA</li>
                    <li>مزامنة بيانات في الوقت الفعلي</li>
                    <li>نسخ احتياطي تلقائي</li>
                    <li>توفّر على مدار الساعة طوال أيام الأسبوع</li>
                </ul>
            </div>
        </td>
    </tr></table>

    <!-- لماذا تختار ConCure Cloud؟ -->
    <div class="callout">
        <h3>لماذا تختار ConCure Cloud؟</h3>
        <table class="feature-grid" dir="rtl"><tr>
            <td class="feature-column">
                <ul class="feature-list">
                    <li><strong>الكفاءة:</strong> تبسيط عمليات العيادة وتقليل الأعمال الورقية</li>
                    <li><strong>رعاية المرضى:</strong> إدارة أفضل للمرضى وتتبّع العلاج</li>
                    <li><strong>اقتصادي:</strong> خفض التكاليف التشغيلية وزيادة الإيرادات</li>
                </ul>
            </td>
            <td class="feature-column">
                <ul class="feature-list">
                    <li><strong>آمن:</strong> أمان وحماية بيانات بمستوى المؤسسات</li>
                    <li><strong>قابل للتوسّع:</strong> ينمو مع عيادتك من الممارسات الصغيرة إلى الكبيرة</li>
                    <li><strong>الدعم:</strong> دعم عملاء مخصَّص وتدريب</li>
                </ul>
            </td>
        </tr></table>
    </div>
</div>

<!-- التذييل -->
<div class="footer-end">
    <p><strong>ConCure Cloud</strong> &middot; نظام شامل لإدارة العيادات</p>
    <p>الإصدار 1.0 &nbsp;|&nbsp; &copy; {{ date('Y') }} ConCure. جميع الحقوق محفوظة.</p>
    <p>إجمالي الميزات: +200 &nbsp;|&nbsp; تم الإنشاء في {{ date('Y/m/d - H:i') }}</p>
</div>

</body>
</html>
