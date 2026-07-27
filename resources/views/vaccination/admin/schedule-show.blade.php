@extends('layouts.app')

@section('title', __('Schedule') . ': ' . $schedule->name)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-calendar-alt me-2 text-primary"></i>
                        {{ $schedule->name }}
                    </h1>
                    <p class="text-muted mb-0">
                        {{ $schedule->country->name ?? '' }} &mdash; v{{ $schedule->version ?? '1.0' }}
                        @if($schedule->is_default) <span class="badge bg-success ms-1">{{ __('Default') }}</span> @endif
                    </p>
                </div>
                <a href="{{ route('vaccination.admin.index') }}" class="btn btn-outline-dark">
                    <i class="fas fa-arrow-left me-1"></i>{{ __('Back') }}
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="row g-4">
        <!-- Schedule Items Table -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0"><i class="fas fa-list me-2"></i>{{ __('Schedule Items') }} ({{ $schedule->items->count() }})</h6>
                </div>
                <div class="card-body p-0">
                    @if($schedule->items->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Vaccine') }}</th>
                                    <th>{{ __('Code') }}</th>
                                    <th>{{ __('Dose') }}</th>
                                    <th>{{ __('Recommended Age') }}</th>
                                    <th>{{ __('Min Age') }}</th>
                                    <th>{{ __('Max Age') }}</th>
                                    <th>{{ __('Grace (days)') }}</th>
                                    <th>{{ __('Mandatory') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($schedule->items->sortBy('sort_order') as $i => $item)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td><strong>{{ $item->vaccine->global_name ?? '?' }}</strong></td>
                                    <td><span class="badge bg-light text-dark">{{ $item->vaccine->code ?? '' }}</span></td>
                                    <td><span class="badge bg-secondary">{{ __('Dose') }} {{ $item->dose_number }}</span></td>
                                    <td>{{ $item->recommended_age_value }} {{ __($item->recommended_age_unit) }}</td>
                                    <td>{{ $item->min_age_value ? $item->min_age_value . ' ' . __($item->recommended_age_unit) : '—' }}</td>
                                    <td>{{ $item->max_age_value ? $item->max_age_value . ' ' . __($item->recommended_age_unit) : '—' }}</td>
                                    <td>{{ $item->grace_period_days ?? 7 }}</td>
                                    <td>
                                        @if($item->is_mandatory)
                                            <span class="badge bg-danger">{{ __('Yes') }}</span>
                                        @else
                                            <span class="text-muted">{{ __('No') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">{{ __('No items. Add vaccines to this schedule.') }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Add Item Form -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0"><i class="fas fa-plus-circle me-2 text-success"></i>{{ __('Add Schedule Item') }}</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('vaccination.admin.item.store') }}">
                        @csrf
                        <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">
                        <div class="mb-2">
                            <label class="form-label small">{{ __('Vaccine') }}</label>
                            <select name="vaccine_id" class="form-select form-select-sm" required>
                                <option value="">{{ __('Select vaccine') }}</option>
                                @php $vaccines = \App\Models\Vaccine::orderBy('code')->get(); @endphp
                                @foreach($vaccines as $v)
                                    <option value="{{ $v->id }}">{{ $v->code }} — {{ $v->global_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small">{{ __('Dose #') }}</label>
                                <input type="number" name="dose_number" class="form-control form-control-sm" min="1" value="1" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small">{{ __('Grace (days)') }}</label>
                                <input type="number" name="grace_period_days" class="form-control form-control-sm" min="0" value="7">
                            </div>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small">{{ __('Recommended Age') }}</label>
                                <input type="number" name="recommended_age_value" class="form-control form-control-sm" min="0" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small">{{ __('Unit') }}</label>
                                <select name="recommended_age_unit" class="form-select form-select-sm">
                                    <option value="days">{{ __('Days') }}</option>
                                    <option value="weeks">{{ __('Weeks') }}</option>
                                    <option value="months" selected>{{ __('Months') }}</option>
                                    <option value="years">{{ __('Years') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small">{{ __('Min Age') }}</label>
                                <input type="number" name="min_age_value" class="form-control form-control-sm" min="0">
                            </div>
                            <div class="col-6">
                                <label class="form-label small">{{ __('Max Age') }}</label>
                                <input type="number" name="max_age_value" class="form-control form-control-sm" min="0">
                            </div>
                        </div>
                        <div class="form-check mb-3">
                            <input type="checkbox" name="is_mandatory" value="1" class="form-check-input" id="isMandatory" checked>
                            <label class="form-check-label small" for="isMandatory">{{ __('Mandatory') }}</label>
                        </div>
                        <button type="submit" class="btn btn-success btn-sm w-100">
                            <i class="fas fa-plus me-1"></i>{{ __('Add Item') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

