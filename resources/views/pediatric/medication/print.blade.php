<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Prescription') }} — {{ $patient->first_name }} {{ $patient->last_name }}</title>
    <style>
        /* Screen + print base */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 14px; color: #1a1a1a; background: #f0f0f0; }
        .page { max-width: 800px; margin: 20px auto; background: #fff; padding: 40px 48px; box-shadow: 0 2px 12px rgba(0,0,0,.12); }

        /* Clinic header */
        .clinic-header { text-align: center; border-bottom: 2px solid #2563eb; padding-bottom: 16px; margin-bottom: 20px; }
        .clinic-header .logo-area { margin-bottom: 6px; }
        .clinic-header .logo-area img { max-height: 60px; }
        .clinic-name { font-size: 22px; font-weight: 700; color: #1e40af; }
        .clinic-detail { font-size: 12px; color: #555; }

        /* Patient info bar */
        .patient-bar { display: flex; justify-content: space-between; flex-wrap: wrap; gap: 8px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 16px; margin-bottom: 20px; }
        .patient-bar .col { flex: 1; min-width: 140px; }
        .patient-bar .label { font-size: 11px; color: #64748b; text-transform: uppercase; letter-spacing: .5px; }
        .patient-bar .value { font-size: 14px; font-weight: 600; }

        /* Rx heading */
        .rx-heading { font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; }
        .rx-heading .rx-symbol { font-size: 20px; color: #2563eb; }

        /* Table */
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 8px 10px; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 13px; }
        th { background: #f1f5f9; font-weight: 600; color: #334155; font-size: 12px; text-transform: uppercase; letter-spacing: .3px; }
        tr:last-child td { border-bottom: none; }
        .extra-row td { background: #fffbeb; font-style: italic; }

        /* Signature */
        .signature-area { margin-top: 40px; display: flex; justify-content: space-between; align-items: flex-end; }
        .signature-block { text-align: center; }
        .signature-line { width: 220px; border-top: 1px solid #333; margin-top: 50px; padding-top: 6px; font-size: 13px; }
        .doctor-name { font-weight: 600; }

        /* Toolbar (screen only) */
        .toolbar { max-width: 800px; margin: 0 auto 10px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; }
        .toolbar .btn { padding: 8px 18px; border: none; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer; }
        .btn-print { background: #2563eb; color: #fff; }
        .btn-print:hover { background: #1d4ed8; }
        .btn-back { background: #e2e8f0; color: #334155; }
        .btn-back:hover { background: #cbd5e1; }
        .btn-add { background: #16a34a; color: #fff; }
        .btn-add:hover { background: #15803d; }

        /* Print styles */
        @media print {
            body { background: #fff; }
            .page { max-width: 100%; margin: 0; padding: 20px 24px; box-shadow: none; }
            .toolbar { display: none !important; }
            .no-print { display: none !important; }
            .extra-row td { background: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            th { background: #f1f5f9 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

<!-- Toolbar (hidden in print) -->
<div class="toolbar">
    <div>
        <a href="{{ route('pediatric.medication.history', ['patient_id' => $patient->id]) }}" class="btn btn-back">← {{ __('Back to History') }}</a>
    </div>
    <div style="display:flex; gap:8px;">
        <button type="button" class="btn btn-add" onclick="addMedicineRow()">+ {{ __('Add Medicine') }}</button>
        <button type="button" class="btn btn-print" onclick="window.print()">🖨️ {{ __('Print Prescription') }}</button>
    </div>
</div>

<div class="page">
    <!-- Clinic Header -->
    <div class="clinic-header">
        <div class="logo-area">
            @if($clinic->logo)
                <img src="{{ asset('storage/' . $clinic->logo) }}" alt="Logo">
            @endif
        </div>
        <div class="clinic-name">{{ $clinic->name }}</div>
        <div class="clinic-detail">
            {{ $clinic->formatted_address }}
            @if($clinic->phone) &nbsp;|&nbsp; {{ __('Tel') }}: {{ $clinic->phone }} @endif
            @if($clinic->email) &nbsp;|&nbsp; {{ $clinic->email }} @endif
        </div>
        @if($clinic->speciality)
        <div class="clinic-detail" style="margin-top:2px;">{{ $clinic->speciality }}</div>
        @endif
    </div>

    <!-- Patient Info -->
    <div class="patient-bar">
        <div class="col"><div class="label">{{ __('Patient') }}</div><div class="value">{{ $patient->first_name }} {{ $patient->last_name }}</div></div>
        <div class="col"><div class="label">{{ __('ID') }}</div><div class="value">{{ $patient->patient_id }}</div></div>
        <div class="col"><div class="label">{{ __('Age') }}</div><div class="value">{{ $patient->age_formatted ?? ($patient->age_months . ' ' . __('months')) }}</div></div>
        <div class="col"><div class="label">{{ __('Gender') }}</div><div class="value">{{ ucfirst($patient->gender ?? '--') }}</div></div>
        <div class="col"><div class="label">{{ __('Weight') }}</div><div class="value">{{ $patient->latest_weight_kg ?? $patient->weight ?? '--' }} kg</div></div>
        <div class="col"><div class="label">{{ __('Height') }}</div><div class="value">{{ $patient->height ?? '--' }} cm</div></div>
        <div class="col"><div class="label">{{ __('Date') }}</div><div class="value">{{ now()->format('d M Y') }}</div></div>
    </div>

    <!-- Prescription Table -->
    <div class="rx-heading"><span class="rx-symbol">℞</span> {{ __('Medications') }}</div>
    <table id="rxTable">
        <thead>
            <tr>
                <th style="width:5%">#</th>
                <th style="width:25%">{{ __('Medication') }}</th>
                <th style="width:20%">{{ __('Dose') }}</th>
                <th style="width:20%">{{ __('Frequency') }}</th>
                <th style="width:12%">{{ __('Duration') }}</th>
                <th style="width:18%">{{ __('Notes') }}</th>
            </tr>
        </thead>
        <tbody id="rxBody">
            @foreach($prescriptions as $i => $rx)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td><strong>{{ $rx->drug->generic_name }}</strong>@if($rx->drug->brand_name) <span style="color:#64748b">({{ $rx->drug->brand_name }})</span>@endif<br><span style="font-size:12px;color:#64748b">{{ $rx->form->display_label }}</span></td>
                <td>{{ $rx->dose_mg }} mg @if($rx->dose_ml)<br><span style="color:#2563eb">{{ $rx->dose_ml }} ml</span>@endif</td>
                <td>{{ $rx->frequency_per_day }}x/day @if($rx->frequency_per_day > 1)<br><span style="font-size:12px;color:#64748b">(Every {{ round(24 / $rx->frequency_per_day) }} hours)</span>@endif</td>
                <td>{{ $rx->duration_days ? $rx->duration_days . ' days' : '--' }}</td>
                <td style="font-size:12px">{{ $rx->notes ?? '--' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Signature -->
    <div class="signature-area">
        <div></div>
        <div class="signature-block">
            <div class="doctor-name">Dr. {{ $doctor->name }}</div>
            <div class="signature-line">{{ __('Signature & Stamp') }}</div>
        </div>
    </div>
</div>

<script>
let extraCount = 0;

function addMedicineRow() {
    extraCount++;
    const tbody = document.getElementById('rxBody');
    const rowNum = tbody.querySelectorAll('tr').length + 1;
    const tr = document.createElement('tr');
    tr.className = 'extra-row';
    tr.id = 'extra-' + extraCount;
    tr.innerHTML = `
        <td>${rowNum}</td>
        <td><input type="text" placeholder="Medicine name" style="width:100%;border:1px solid #cbd5e1;border-radius:4px;padding:4px 6px;font-size:13px" class="no-print-input extra-name"></td>
        <td><input type="text" placeholder="Dose / instructions" style="width:100%;border:1px solid #cbd5e1;border-radius:4px;padding:4px 6px;font-size:13px" class="no-print-input extra-dose"></td>
        <td><input type="text" placeholder="Frequency" style="width:100%;border:1px solid #cbd5e1;border-radius:4px;padding:4px 6px;font-size:13px" class="no-print-input extra-freq"></td>
        <td><input type="text" placeholder="Duration" style="width:100%;border:1px solid #cbd5e1;border-radius:4px;padding:4px 6px;font-size:13px" class="no-print-input extra-dur"></td>
        <td style="display:flex;gap:4px;align-items:center">
            <input type="text" placeholder="Notes" style="flex:1;border:1px solid #cbd5e1;border-radius:4px;padding:4px 6px;font-size:13px" class="no-print-input extra-notes">
            <button onclick="removeExtra(${extraCount})" class="no-print" style="background:#ef4444;color:#fff;border:none;border-radius:4px;padding:2px 8px;cursor:pointer;font-size:12px" title="Remove">✕</button>
        </td>
    `;
    tbody.appendChild(tr);
    tr.querySelector('.extra-name').focus();
}

function removeExtra(id) {
    const row = document.getElementById('extra-' + id);
    if (row) row.remove();
    renumberRows();
}

function renumberRows() {
    const rows = document.querySelectorAll('#rxBody tr');
    rows.forEach((tr, i) => { tr.querySelector('td').textContent = i + 1; });
}

// Before printing, convert inputs to plain text for clean output
window.addEventListener('beforeprint', function() {
    document.querySelectorAll('.extra-row').forEach(tr => {
        tr.querySelectorAll('.no-print-input').forEach(input => {
            const span = document.createElement('span');
            span.textContent = input.value || '--';
            span.className = 'print-text';
            span.dataset.forInput = '1';
            input.style.display = 'none';
            input.parentNode.insertBefore(span, input.nextSibling);
        });
        // Hide remove buttons
        const rmBtn = tr.querySelector('.no-print');
        if (rmBtn) rmBtn.style.display = 'none';
    });
});

// After printing, restore inputs
window.addEventListener('afterprint', function() {
    document.querySelectorAll('.print-text[data-for-input]').forEach(span => span.remove());
    document.querySelectorAll('.no-print-input').forEach(input => input.style.display = '');
    document.querySelectorAll('.extra-row .no-print').forEach(btn => btn.style.display = '');
});
</script>
</body>
</html>

