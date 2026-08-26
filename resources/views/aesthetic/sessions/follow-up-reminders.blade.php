@extends('layouts.app')

@section('title', __('Follow-up Reminders'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-bell text-warning me-2"></i>
                        {{ __('Follow-up Reminders') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('All outstanding treatment follow-ups, sorted by the most urgent due date.') }}</p>
                </div>
                <a href="{{ route('aesthetic.sessions.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i>
                    {{ __('Back') }}
                </a>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">{{ __('Outstanding Reminders') }}</h6>
                    <span class="badge bg-warning text-dark">{{ $followUpReminders->total() }}</span>
                </div>
                <div class="card-body">
                    @if($followUpReminders->isEmpty())
                        <p class="text-muted mb-0">{{ __('No open follow-up reminders yet.') }}</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ __('Patient') }}</th>
                                        <th>{{ __('Package / Treatment') }}</th>
                                        <th>{{ __('Last Completed Session') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Next Due') }}</th>
                                        <th class="text-end">{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($followUpReminders as $reminder)
                                        @php($reminderPhone = $reminder->resolvedPatient?->whatsapp_phone ?: $reminder->resolvedPatient?->phone)
                                        <tr>
                                            <td>{{ $reminder->patient_display }}</td>
                                            <td>
                                                @if($reminder->isPackageSession)
                                                    <span class="badge bg-info">{{ $reminder->patientPackage?->package?->name ?? __('Package') }}</span>
                                                @else
                                                    <span class="badge bg-warning">{{ $reminder->effective_treatments->pluck('name')->implode(', ') ?: __('Direct Treatment') }}</span>
                                                @endif
                                            </td>
                                            <td>{{ __('Session :number', ['number' => $reminder->session_number]) }}</td>
                                            <td>
                                                <span class="badge bg-{{ \App\Models\AestheticSession::STATUS_COLORS[$reminder->status] ?? 'secondary' }}">
                                                    {{ \App\Models\AestheticSession::STATUSES[$reminder->status] ?? ucfirst($reminder->status) }}
                                                </span>
                                            </td>
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

                        <div class="mt-3">
                            {{ $followUpReminders->appends(request()->query())->links() }}
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
