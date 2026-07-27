@extends('layouts.app')

@section('title', __('Treatment Sessions'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-calendar-check text-primary me-2"></i>
                        {{ __('Treatment Sessions') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Manage patient treatment sessions and before/after images') }}</p>
                </div>
                <a href="{{ route('aesthetic.sessions.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>
                    {{ __('New Session') }}
                </a>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-primary">
                        <div class="card-body text-center">
                            <i class="fas fa-calendar-check fa-2x text-primary mb-2"></i>
                            <h4 class="mb-1">{{ $stats['total'] }}</h4>
                            <small class="text-muted">{{ __('Total Sessions') }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-warning">
                        <div class="card-body text-center">
                            <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                            <h4 class="mb-1">{{ $stats['scheduled'] }}</h4>
                            <small class="text-muted">{{ __('Scheduled') }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-success">
                        <div class="card-body text-center">
                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                            <h4 class="mb-1">{{ $stats['completed'] }}</h4>
                            <small class="text-muted">{{ __('Completed') }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-info">
                        <div class="card-body text-center">
                            <i class="fas fa-bell fa-2x text-info mb-2"></i>
                            <h4 class="mb-1">{{ $stats['follow_up_due'] }}</h4>
                            <small class="text-muted">{{ __('Open Follow-ups') }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-bell me-2 text-warning"></i>{{ __('Package Follow-up Reminders') }}</h6>
                    <span class="badge bg-warning text-dark">{{ $followUpReminders->count() }}</span>
                </div>
                <div class="card-body">
                    @if($followUpReminders->isEmpty())
                        <p class="text-muted mb-0">{{ __('No open package follow-up reminders yet.') }}</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ __('Patient') }}</th>
                                        <th>{{ __('Package') }}</th>
                                        <th>{{ __('Last Completed Session') }}</th>
                                        <th>{{ __('Next Due') }}</th>
                                        <th class="text-end">{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($followUpReminders as $reminder)
                                        @php($reminderPhone = $reminder->resolvedPatient?->whatsapp_phone ?: $reminder->resolvedPatient?->phone)
                                        <tr>
                                            <td>{{ $reminder->patient_display }}</td>
                                            <td>{{ $reminder->patientPackage?->package?->name ?? __('Package') }}</td>
                                            <td>{{ __('Session :number', ['number' => $reminder->session_number]) }}</td>
                                            <td>
                                                <span class="fw-semibold">{{ $reminder->next_due_date?->format('M d, Y') }}</span>
                                                @if($reminder->next_due_date?->isPast())
                                                    <span class="badge bg-danger ms-1">{{ __('Overdue') }}</span>
                                                @elseif($reminder->next_due_date?->isToday())
                                                    <span class="badge bg-warning text-dark ms-1">{{ __('Today') }}</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <div class="d-inline-flex gap-2">
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-success js-send-followup-whatsapp"
                                                        data-url="{{ route('aesthetic.sessions.send-whatsapp-reminder', $reminder) }}"
                                                        {{ $reminderPhone ? '' : 'disabled' }}
                                                        title="{{ $reminderPhone ? __('Send WhatsApp reminder') : __('Patient has no WhatsApp number') }}"
                                                    >
                                                        <i class="fab fa-whatsapp me-1"></i>{{ __('WhatsApp') }}
                                                    </button>

                                                    <a href="{{ route('aesthetic.sessions.show', $reminder) }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye me-1"></i>{{ __('Open') }}
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('aesthetic.sessions.index') }}">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">{{ __('Search') }}</label>
                                <input type="text" class="form-control" name="search" value="{{ request('search') }}"
                                       placeholder="{{ __('Search by patient or treatment...') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ __('Type') }}</label>
                                <select class="form-select" name="session_type">
                                    <option value="">{{ __('All Types') }}</option>
                                    <option value="package" {{ request('session_type') == 'package' ? 'selected' : '' }}>{{ __('Package Session') }}</option>
                                    <option value="direct" {{ request('session_type') == 'direct' ? 'selected' : '' }}>{{ __('Direct Treatment') }}</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ __('Status') }}</label>
                                <select class="form-select" name="status">
                                    <option value="">{{ __('All Status') }}</option>
                                    @foreach(\App\Models\AestheticSession::STATUSES as $key => $label)
                                        <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                                            {{ __($label) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-1"></i>
                                        {{ __('Filter') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sessions Table -->
            <div class="card">
                <div class="card-body">
                    @if($sessions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>{{ __('Patient') }}</th>
                                        <th>{{ __('Type / Context') }}</th>
                                        <th>{{ __('Session') }}</th>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Assigned') }}</th>
                                        <th>{{ __('Next Due') }}</th>
                                        <th>{{ __('Images') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sessions as $session)
                                    <tr>
                                        <td>
                                            @if($session->isPackageSession)
                                                <strong>{{ $session->patientPackage?->patient?->first_name }} {{ $session->patientPackage?->patient?->last_name }}</strong>
                                            @else
                                                <strong>{{ $session->patient?->first_name }} {{ $session->patient?->last_name }}</strong>
                                            @endif
                                        </td>
                                        <td>
                                            @if($session->isPackageSession)
                                                <span class="badge bg-info">{{ $session->patientPackage?->package?->name ?? __('Package') }}</span>
                                            @else
                                                @if($session->effective_treatments->isNotEmpty())
                                                    @foreach($session->effective_treatments as $et)
                                                        <span class="badge bg-warning">{{ $et->name }}</span>
                                                    @endforeach
                                                @else
                                                    <span class="badge bg-warning">{{ __('Direct Treatment') }}</span>
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ __('Session :number', ['number' => $session->session_number]) }}</strong>
                                        </td>
                                        <td>
                                            {{ $session->session_date->format('M d, Y') }}
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $session->status_color }}">
                                                {{ $session->status_display }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($session->assigned_user_id)
                                                <span class="fw-semibold">{{ $session->assigned_person_display }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($session->next_due_date)
                                                <div>{{ $session->next_due_date->format('M d, Y') }}</div>
                                                @if($session->has_open_reminder)
                                                    <span class="badge bg-warning text-dark">{{ __('Follow-up due') }}</span>
                                                @endif
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($session->has_comparison)
                                                <span class="badge bg-success">
                                                    <i class="fas fa-images me-1"></i>
                                                    {{ __('Before/After') }}
                                                </span>
                                            @elseif($session->images->count() > 0)
                                                <span class="badge bg-primary">
                                                    {{ $session->images->count() }} {{ __('image(s)') }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('aesthetic.sessions.show', $session) }}"
                                                   class="btn btn-sm btn-outline-primary" title="{{ __('View') }}">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="{{ __('Change Status') }}">
                                                        <i class="fas fa-check-double"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        @foreach(\App\Models\AestheticSession::STATUSES as $key => $label)
                                                            @if($key !== $session->status)
                                                            <li>
                                                                <form method="POST" action="{{ route('aesthetic.sessions.update', $session) }}" class="d-inline">
                                                                    @csrf
                                                                    @method('PUT')
                                                                    <input type="hidden" name="session_mode" value="{{ $session->isPackageSession ? 'package' : 'direct' }}">
                                                                    <input type="hidden" name="patient_package_id" value="{{ $session->patient_package_id ?? '' }}">
                                                                    <input type="hidden" name="patient_id" value="{{ $session->patient_id ?? '' }}">
                                                                    <input type="hidden" name="treatment_id" value="{{ $session->treatment_id ?? '' }}">
                                                                    <input type="hidden" name="session_number" value="{{ $session->session_number }}">
                                                                    <input type="hidden" name="session_date" value="{{ $session->session_date->format('Y-m-d') }}">
                                                                    <input type="hidden" name="notes" value="{{ $session->notes ?? '' }}">
                                                                    <input type="hidden" name="status" value="{{ $key }}">
                                                                    <button type="submit" class="dropdown-item">{{ __($label) }}</button>
                                                                </form>
                                                            </li>
                                                            @endif
                                                        @endforeach
                                                    </ul>
                                                </div>
                                                <a href="{{ route('aesthetic.sessions.edit', $session) }}"
                                                   class="btn btn-sm btn-outline-info" title="{{ __('Edit') }}">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" action="{{ route('aesthetic.sessions.destroy', $session) }}"
                                                      onsubmit="return confirm('{{ __('Are you sure you want to delete this session?') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('Delete') }}">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center mt-4">
                            {{ $sessions->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-check fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('No sessions found') }}</h5>
                            <p class="text-muted">{{ __('Create a new session to start tracking treatment progress.') }}</p>
                            <a href="{{ route('aesthetic.sessions.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>
                                {{ __('Create First Session') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    document.querySelectorAll('.js-send-followup-whatsapp').forEach((button) => {
        button.addEventListener('click', async () => {
            const originalHtml = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>{{ __('Sending...') }}';

            try {
                const response = await fetch(button.dataset.url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || '{{ __('Failed to send reminder.') }}');
                }

                if (data.whatsapp_url) {
                    window.open(data.whatsapp_url, '_blank', 'noopener');
                }

                button.classList.remove('btn-outline-success', 'btn-outline-danger');
                button.classList.add('btn-success');
                button.innerHTML = '<i class="fas fa-check me-1"></i>{{ __('Ready') }}';
            } catch (error) {
                button.classList.remove('btn-outline-success', 'btn-success');
                button.classList.add('btn-outline-danger');
                button.innerHTML = '<i class="fas fa-times me-1"></i>{{ __('Failed') }}';
                button.title = error.message;
            }

            setTimeout(() => {
                button.disabled = false;
                button.classList.remove('btn-success', 'btn-outline-danger');
                button.classList.add('btn-outline-success');
                button.innerHTML = originalHtml;
            }, 2500);
        });
    });
});
</script>
@endpush
