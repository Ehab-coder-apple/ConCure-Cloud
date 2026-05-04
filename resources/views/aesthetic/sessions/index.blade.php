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
                <div class="col-lg-4 col-md-4 mb-3">
                    <div class="card border-primary">
                        <div class="card-body text-center">
                            <i class="fas fa-calendar-check fa-2x text-primary mb-2"></i>
                            <h4 class="mb-1">{{ $stats['total'] }}</h4>
                            <small class="text-muted">{{ __('Total Sessions') }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 mb-3">
                    <div class="card border-warning">
                        <div class="card-body text-center">
                            <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                            <h4 class="mb-1">{{ $stats['scheduled'] }}</h4>
                            <small class="text-muted">{{ __('Scheduled') }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 mb-3">
                    <div class="card border-success">
                        <div class="card-body text-center">
                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                            <h4 class="mb-1">{{ $stats['completed'] }}</h4>
                            <small class="text-muted">{{ __('Completed') }}</small>
                        </div>
                    </div>
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
                                                <span class="badge bg-warning">{{ $session->treatment?->name ?? __('Direct Treatment') }}</span>
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
