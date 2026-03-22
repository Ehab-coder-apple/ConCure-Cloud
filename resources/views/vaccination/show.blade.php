@extends('layouts.app')

@section('title', __('Vaccination Card') . ' - ' . $patient->full_name)

@push('styles')
<style>
.vacc-status-on_time { background-color: #d1fae5; color: #065f46; }
.vacc-status-delayed { background-color: #fef3c7; color: #92400e; }
.vacc-status-missed { background-color: #fee2e2; color: #991b1b; }
.vacc-status-upcoming { background-color: #e0f2fe; color: #075985; }
.vacc-status-skipped { background-color: #f3f4f6; color: #374151; }
.vacc-row:hover { background-color: #f8fafc; }
.stat-card { border-radius: 12px; padding: 1rem; text-align: center; }
.stat-card .stat-number { font-size: 1.8rem; font-weight: 700; line-height: 1.2; }
.stat-card .stat-label { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.8; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-syringe me-2 text-primary"></i>
                        {{ __('Vaccination Card') }}
                    </h1>
                    <p class="text-muted mb-0">
                        <a href="{{ route('patients.show', $patient) }}" class="text-decoration-none">{{ $patient->full_name }}</a>
                        &mdash; {{ $patient->vaccinationSchedule->name ?? __('No Schedule') }}
                        @if($patient->vaccinationSchedule?->country)
                            ({{ $patient->vaccinationSchedule->country->name }})
                        @endif
                    </p>
                </div>
                <div class="d-flex gap-2">
                    @if(!$patient->vaccination_schedule_id)
                    <form method="POST" action="{{ route('vaccination.generate', $patient) }}">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-magic me-1"></i>{{ __('Generate Schedule') }}
                        </button>
                    </form>
                    @endif
                    @if($patient->vaccinations->count() > 0)
                    <a href="{{ route('vaccination.print', $patient) }}" class="btn btn-outline-secondary" target="_blank">
                        <i class="fas fa-print me-1"></i>{{ __('Print Card') }}
                    </a>
                    @endif
                    <a href="{{ route('vaccination.index') }}" class="btn btn-outline-dark">
                        <i class="fas fa-arrow-left me-1"></i>{{ __('Back') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    @include('components.back-button')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show"><i class="fas fa-exclamation-triangle me-1"></i>{{ session('warning') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <!-- Stats Row -->
    <div class="row mb-4 g-3">
        <div class="col-6 col-md-3 col-lg-2">
            <div class="stat-card bg-primary text-white">
                <div class="stat-number">{{ $stats['total'] }}</div>
                <div class="stat-label">{{ __('Total Doses') }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-lg-2">
            <div class="stat-card bg-success text-white">
                <div class="stat-number">{{ $stats['given'] }}</div>
                <div class="stat-label">{{ __('Given') }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-lg-2">
            <div class="stat-card bg-danger text-white">
                <div class="stat-number">{{ $stats['missed'] }}</div>
                <div class="stat-label">{{ __('Missed') }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-lg-2">
            <div class="stat-card bg-warning text-dark">
                <div class="stat-number">{{ $stats['upcoming'] }}</div>
                <div class="stat-label">{{ __('Upcoming') }}</div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="stat-card bg-white border shadow-sm">
                <div class="stat-number text-primary">{{ $stats['completion_percentage'] }}%</div>
                <div class="stat-label text-muted">{{ __('Completion') }}</div>
                <div class="progress mt-2" style="height: 6px;">
                    <div class="progress-bar bg-success" style="width: {{ $stats['completion_percentage'] }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Vaccination Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-list-alt me-2"></i>{{ __('Vaccination Timeline') }}</h6>
                </div>
                <div class="card-body p-0">
                    @if($patient->vaccinations->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Vaccine') }}</th>
                                    <th>{{ __('Dose') }}</th>
                                    <th>{{ __('Scheduled Date') }}</th>
                                    <th>{{ __('Given Date') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Batch #') }}</th>
                                    <th class="text-end">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($patient->vaccinations as $i => $vacc)
                                <tr class="vacc-row">
                                    <td>{{ $i + 1 }}</td>
                                    <td>
                                        <strong>{{ $vacc->vaccine->getLocalizedName($language) }}</strong>
                                        <br><small class="text-muted">{{ $vacc->vaccine->code }}</small>
                                    </td>
                                    <td><span class="badge bg-secondary">{{ __('Dose') }} {{ $vacc->dose_number }}</span></td>
                                    <td>{{ $vacc->scheduled_date->format('M d, Y') }}</td>
                                    <td>
                                        @if($vacc->given_date)
                                            <span class="text-success fw-bold">{{ $vacc->given_date->format('M d, Y') }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge vacc-status-{{ $vacc->status }} px-2 py-1">
                                            {{ __(ucfirst(str_replace('_', ' ', $vacc->status))) }}
                                        </span>
                                    </td>
                                    <td>{{ $vacc->batch_number ?? '—' }}</td>
                                    <td class="text-end">
                                        @if(!$vacc->given_date && $vacc->status !== 'skipped')
                                        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#recordModal{{ $vacc->id }}">
                                            <i class="fas fa-check me-1"></i>{{ __('Record') }}
                                        </button>
                                        <button class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#skipModal{{ $vacc->id }}">
                                            <i class="fas fa-forward"></i>
                                        </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="fas fa-syringe fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">{{ __('No Vaccination Records') }}</h5>
                        <p class="text-muted">{{ __('Click "Generate Schedule" to create vaccination records based on the assigned schedule.') }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Record Vaccination Modals --}}
@foreach($patient->vaccinations as $vacc)
@if(!$vacc->given_date && $vacc->status !== 'skipped')
{{-- Record Modal --}}
<div class="modal fade" id="recordModal{{ $vacc->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('vaccination.record', $vacc) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-syringe me-2"></i>{{ __('Record Vaccination') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><strong>{{ $vacc->vaccine->getLocalizedName($language) }}</strong> — {{ __('Dose') }} {{ $vacc->dose_number }}</p>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Date Given') }} <span class="text-danger">*</span></label>
                        <input type="date" name="given_date" class="form-control" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Batch Number') }}</label>
                        <input type="text" name="batch_number" class="form-control" placeholder="{{ __('e.g. LOT-2025-ABC') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Administered By') }}</label>
                        <input type="text" name="administered_by" class="form-control" placeholder="{{ __('Nurse/Doctor name') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Notes') }}</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-check me-1"></i>{{ __('Record') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
{{-- Skip Modal --}}
<div class="modal fade" id="skipModal{{ $vacc->id }}" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <form method="POST" action="{{ route('vaccination.skip', $vacc) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-forward me-2"></i>{{ __('Skip Vaccination') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('Skip') }} <strong>{{ $vacc->vaccine->getLocalizedName($language) }}</strong> {{ __('Dose') }} {{ $vacc->dose_number }}?</p>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Reason') }}</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="{{ __('Optional reason...') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-dark"><i class="fas fa-forward me-1"></i>{{ __('Skip') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endforeach
@endsection

