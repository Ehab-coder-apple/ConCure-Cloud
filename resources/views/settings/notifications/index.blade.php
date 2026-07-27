@extends('layouts.app')

@section('title', __('Notification Settings'))

@section('content')
<div style="margin-left: 250px; padding: 20px; width: calc(100% - 250px); box-sizing: border-box;">
<div class="container-fluid" style="max-width: 960px;">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-semibold">{{ __('Notification Settings') }}</h1>
            <p class="text-muted mb-0 small">{{ __('Configure automated WhatsApp reminders for your clinic.') }}</p>
        </div>
        <a href="{{ route('settings.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> {{ __('Back') }}
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Stats Row --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:44px;height:44px;background:#d1fae5;">
                        <i class="fas fa-paper-plane text-success"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold lh-1">{{ $stats['total_sent'] }}</div>
                        <small class="text-muted">{{ __('Sent') }}</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:44px;height:44px;background:#fee2e2;">
                        <i class="fas fa-exclamation-triangle text-danger"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold lh-1">{{ $stats['total_failed'] }}</div>
                        <small class="text-muted">{{ __('Failed') }}</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:44px;height:44px;background:#dbeafe;">
                        <i class="fas fa-chart-line text-primary"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold lh-1">{{ $stats['last_7_days'] }}</div>
                        <small class="text-muted">{{ __('Last 7 days') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('notifications.settings.update') }}" method="POST" id="notificationSettingsForm">
        @csrf

        {{-- Master Toggle --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;background:#dcfce7;">
                            <i class="fab fa-whatsapp fs-4" style="color:#25d366;"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-semibold">{{ __('WhatsApp Notifications') }}</h6>
                            <small class="text-muted">{{ __('Master switch — all automated reminders require this.') }}</small>
                        </div>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" role="switch" id="whatsapp_enabled" name="whatsapp_enabled" value="1" {{ $settings->whatsapp_enabled ? 'checked' : '' }} style="width:3em;height:1.5em;cursor:pointer;">
                    </div>
                </div>
            </div>
        </div>

        {{-- Dependent sections wrapper --}}
        <div id="dependentSections" style="{{ $settings->whatsapp_enabled ? '' : 'opacity:0.5;pointer-events:none;' }}">

            {{-- Appointment Reminders --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center justify-content-between mb-0" data-bs-toggle="collapse" data-bs-target="#apptSection" style="cursor:pointer;" aria-expanded="{{ $settings->appointment_reminder_enabled ? 'true' : 'false' }}">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;background:#dbeafe;">
                                <i class="fas fa-calendar-check text-primary"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-semibold">{{ __('Appointment Reminders') }}</h6>
                                <small class="text-muted">{{ __('Notify patients before their scheduled visit.') }}</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="form-check form-switch mb-0" onclick="event.stopPropagation();">
                                <input class="form-check-input" type="checkbox" role="switch" id="appointment_reminder_enabled" name="appointment_reminder_enabled" value="1" {{ $settings->appointment_reminder_enabled ? 'checked' : '' }} style="width:2.5em;height:1.25em;cursor:pointer;">
                            </div>
                            <i class="fas fa-chevron-down text-muted small collapse-chevron"></i>
                        </div>
                    </div>
                </div>
                <div class="collapse {{ $settings->appointment_reminder_enabled ? 'show' : '' }}" id="apptSection">
                    <div class="card-body pt-0 border-top">
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-medium text-muted">{{ __('Hours before appointment') }}</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" class="form-control" name="appointment_reminder_hours" value="{{ old('appointment_reminder_hours', $settings->appointment_reminder_hours) }}" min="1" max="168">
                                    <span class="input-group-text">{{ __('hrs') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label small fw-medium text-muted">{{ __('Message Template') }}</label>
                            <small class="d-block text-muted mb-1"><i class="fas fa-language me-1"></i>{{ __('You can write templates in any language (Arabic, Kurdish, etc.). Placeholders will be replaced with actual patient data.') }}</small>
                            <textarea class="form-control form-control-sm" name="appointment_reminder_template" rows="4" dir="auto" placeholder="{{ \App\Models\NotificationSetting::DEFAULT_TEMPLATES['appointment_reminder'] }}" style="font-size:.85rem;line-height:1.6;">{{ old('appointment_reminder_template', $settings->appointment_reminder_template) }}</textarea>
                            <div class="mt-2 d-flex flex-wrap gap-1">
                                @foreach(['{patient_name}','{patient_first_name}','{patient_last_name}','{patient_phone}','{patient_age}','{patient_gender}','{appointment_date}','{appointment_time}','{doctor_name}','{clinic_name}','{clinic_phone}','{appointment_type}'] as $ph)
                                    <span class="badge bg-light text-dark border" style="font-size:.72rem;cursor:pointer;" onclick="insertPlaceholder(this, 'appointment_reminder_template')">{{ $ph }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Vaccination Reminders --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center justify-content-between mb-0" data-bs-toggle="collapse" data-bs-target="#vaccSection" style="cursor:pointer;" aria-expanded="{{ $settings->vaccination_reminder_enabled ? 'true' : 'false' }}">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;background:#e0f2fe;">
                                <i class="fas fa-syringe text-info"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-semibold">{{ __('Vaccination Reminders') }}</h6>
                                <small class="text-muted">{{ __('Remind parents before their child\'s vaccination is due.') }}</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="form-check form-switch mb-0" onclick="event.stopPropagation();">
                                <input class="form-check-input" type="checkbox" role="switch" id="vaccination_reminder_enabled" name="vaccination_reminder_enabled" value="1" {{ $settings->vaccination_reminder_enabled ? 'checked' : '' }} style="width:2.5em;height:1.25em;cursor:pointer;">
                            </div>
                            <i class="fas fa-chevron-down text-muted small collapse-chevron"></i>
                        </div>
                    </div>
                </div>
                <div class="collapse {{ $settings->vaccination_reminder_enabled ? 'show' : '' }}" id="vaccSection">
                    <div class="card-body pt-0 border-top">
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-medium text-muted">{{ __('Days before due date') }}</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" class="form-control" name="vaccination_reminder_days" value="{{ old('vaccination_reminder_days', $settings->vaccination_reminder_days) }}" min="1" max="30">
                                    <span class="input-group-text">{{ __('days') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label small fw-medium text-muted">{{ __('Message Template') }}</label>
                            <small class="d-block text-muted mb-1"><i class="fas fa-language me-1"></i>{{ __('You can write templates in any language (Arabic, Kurdish, etc.). Placeholders will be replaced with actual patient data.') }}</small>
                            <textarea class="form-control form-control-sm" name="vaccination_reminder_template" rows="4" dir="auto" placeholder="{{ \App\Models\NotificationSetting::DEFAULT_TEMPLATES['vaccination_reminder'] }}" style="font-size:.85rem;line-height:1.6;">{{ old('vaccination_reminder_template', $settings->vaccination_reminder_template) }}</textarea>
                            <div class="mt-2 d-flex flex-wrap gap-1">
                                @foreach(['{patient_name}','{patient_first_name}','{patient_last_name}','{patient_phone}','{patient_age}','{vaccine_name}','{due_date}','{clinic_name}','{clinic_phone}'] as $ph)
                                    <span class="badge bg-light text-dark border" style="font-size:.72rem;cursor:pointer;" onclick="insertPlaceholder(this, 'vaccination_reminder_template')">{{ $ph }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Follow-up Reminders --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center justify-content-between mb-0" data-bs-toggle="collapse" data-bs-target="#followSection" style="cursor:pointer;" aria-expanded="{{ $settings->follow_up_reminder_enabled ? 'true' : 'false' }}">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;background:#fef3c7;">
                                <i class="fas fa-redo text-warning"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-semibold">{{ __('Follow-up Reminders') }}</h6>
                                <small class="text-muted">{{ __('Remind patients about upcoming follow-up visits.') }}</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="form-check form-switch mb-0" onclick="event.stopPropagation();">
                                <input class="form-check-input" type="checkbox" role="switch" id="follow_up_reminder_enabled" name="follow_up_reminder_enabled" value="1" {{ $settings->follow_up_reminder_enabled ? 'checked' : '' }} style="width:2.5em;height:1.25em;cursor:pointer;">
                            </div>
                            <i class="fas fa-chevron-down text-muted small collapse-chevron"></i>
                        </div>
                    </div>
                </div>
                <div class="collapse {{ $settings->follow_up_reminder_enabled ? 'show' : '' }}" id="followSection">
                    <div class="card-body pt-0 border-top">
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-medium text-muted">{{ __('Days before follow-up') }}</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" class="form-control" name="follow_up_reminder_days" value="{{ old('follow_up_reminder_days', $settings->follow_up_reminder_days) }}" min="1" max="30">
                                    <span class="input-group-text">{{ __('days') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label small fw-medium text-muted">{{ __('Message Template') }}</label>
                            <small class="d-block text-muted mb-1"><i class="fas fa-language me-1"></i>{{ __('You can write templates in any language (Arabic, Kurdish, etc.). Placeholders will be replaced with actual patient data.') }}</small>
                            <textarea class="form-control form-control-sm" name="follow_up_reminder_template" rows="4" dir="auto" placeholder="{{ \App\Models\NotificationSetting::DEFAULT_TEMPLATES['follow_up_reminder'] }}" style="font-size:.85rem;line-height:1.6;">{{ old('follow_up_reminder_template', $settings->follow_up_reminder_template) }}</textarea>
                            <div class="mt-2 d-flex flex-wrap gap-1">
                                @foreach(['{patient_name}','{patient_first_name}','{patient_last_name}','{patient_phone}','{patient_age}','{appointment_date}','{appointment_time}','{doctor_name}','{clinic_name}','{clinic_phone}'] as $ph)
                                    <span class="badge bg-light text-dark border" style="font-size:.72rem;cursor:pointer;" onclick="insertPlaceholder(this, 'follow_up_reminder_template')">{{ $ph }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>{{-- /dependentSections --}}

        {{-- Save --}}
        <div class="d-flex justify-content-end my-4">
            <button type="submit" class="btn btn-dark px-4">
                <i class="fas fa-save me-2"></i>{{ __('Save Settings') }}
            </button>
        </div>
    </form>

    {{-- Recent Activity --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-semibold"><i class="fas fa-history me-2 text-muted"></i>{{ __('Recent Activity') }}</h6>
            <span class="badge bg-light text-dark border">{{ $recentLogs->count() }} {{ __('entries') }}</span>
        </div>
        <div class="card-body p-0">
            @if($recentLogs->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-bell-slash fa-2x text-muted mb-2 d-block"></i>
                    <p class="text-muted mb-0">{{ __('No notifications sent yet.') }}</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size:.875rem;">
                        <thead>
                            <tr class="text-muted" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">
                                <th class="fw-medium border-0 ps-3">{{ __('Date') }}</th>
                                <th class="fw-medium border-0">{{ __('Patient') }}</th>
                                <th class="fw-medium border-0">{{ __('Type') }}</th>
                                <th class="fw-medium border-0">{{ __('Status') }}</th>
                                <th class="fw-medium border-0">{{ __('Recipient') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentLogs as $log)
                            <tr>
                                <td class="ps-3 text-nowrap">{{ $log->created_at->format('M d, Y H:i') }}</td>
                                <td>{{ $log->patient?->full_name ?? '—' }}</td>
                                <td>
                                    @switch($log->type)
                                        @case('appointment_reminder')
                                            <span class="badge rounded-pill" style="background:#dbeafe;color:#1e40af;">{{ __('Appointment') }}</span>
                                            @break
                                        @case('vaccination_reminder')
                                            <span class="badge rounded-pill" style="background:#e0f2fe;color:#0369a1;">{{ __('Vaccination') }}</span>
                                            @break
                                        @case('follow_up_reminder')
                                            <span class="badge rounded-pill" style="background:#fef3c7;color:#92400e;">{{ __('Follow-up') }}</span>
                                            @break
                                        @default
                                            <span class="badge rounded-pill bg-secondary">{{ $log->type }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    @if($log->status === 'sent' || $log->status === 'delivered')
                                        <span class="d-inline-flex align-items-center gap-1"><i class="fas fa-circle text-success" style="font-size:.5rem;"></i> {{ ucfirst($log->status) }}</span>
                                    @elseif($log->status === 'failed')
                                        <span class="d-inline-flex align-items-center gap-1" title="{{ $log->error_message }}"><i class="fas fa-circle text-danger" style="font-size:.5rem;"></i> {{ __('Failed') }}</span>
                                    @else
                                        <span class="d-inline-flex align-items-center gap-1"><i class="fas fa-circle text-secondary" style="font-size:.5rem;"></i> {{ ucfirst($log->status) }}</span>
                                    @endif
                                </td>
                                <td class="text-muted">{{ $log->recipient ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

</div>
</div>

<style>
    .collapse-chevron { transition: transform .2s ease; }
    [aria-expanded="true"] .collapse-chevron { transform: rotate(180deg); }
    .form-check-input:checked { background-color: #25d366; border-color: #25d366; }
    #notificationSettingsForm .card { transition: all .15s ease; }
    #notificationSettingsForm .card:hover { box-shadow: 0 .25rem .75rem rgba(0,0,0,.08) !important; }
</style>

<script>
// Master toggle → enable/disable dependent sections
document.getElementById('whatsapp_enabled').addEventListener('change', function() {
    const dep = document.getElementById('dependentSections');
    dep.style.opacity = this.checked ? '1' : '0.5';
    dep.style.pointerEvents = this.checked ? '' : 'none';
});

// Insert placeholder tag into textarea
function insertPlaceholder(badge, textareaName) {
    const ta = document.querySelector('textarea[name="' + textareaName + '"]');
    if (!ta) return;
    const start = ta.selectionStart;
    const end = ta.selectionEnd;
    const text = badge.textContent.trim();
    ta.value = ta.value.substring(0, start) + text + ta.value.substring(end);
    ta.focus();
    ta.selectionStart = ta.selectionEnd = start + text.length;
}
</script>
@endsection