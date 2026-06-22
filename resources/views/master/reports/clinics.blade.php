@extends('master.layouts.app')

@section('title', 'Clinics Report | ConCure Master')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <div class="d-flex align-items-center">
        <i class="fas fa-hospital fa-2x text-primary me-3"></i>
        <div>
            <h5 class="mb-0">Clinics Report</h5>
            <small class="text-muted">Overview of clinic activity and performance</small>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('master.reports') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> System Reports
        </a>
        <a href="{{ route('master.reports.login-activity') }}" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-sign-in-alt me-1"></i> Login Activity
        </a>
    </div>
</div>

<div class="card mb-3">
    <form method="GET" class="card-body row g-2 align-items-end">
        <div class="col-md-4 col-lg-3">
            <label class="form-label">Search by Clinic Name</label>
            <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Enter clinic name...">
        </div>
        <input type="hidden" name="sort" value="{{ $sort }}">
        <input type="hidden" name="direction" value="{{ $direction }}">
        <div class="col-md-3 col-lg-2">
            <button type="submit" class="btn btn-primary w-100 mt-3 mt-md-0">
                <i class="fas fa-search me-1"></i> Apply
            </button>
        </div>
        @if($search)
            <div class="col-md-2 col-lg-2">
                <a href="{{ route('master.reports.clinics') }}" class="btn btn-outline-secondary w-100 mt-3 mt-md-0">Reset</a>
            </div>
        @endif
    </form>
</div>

<div class="card">
    <div class="card-body table-responsive">
        <table class="table table-sm align-middle">
            <thead>
                <tr>
                    @php
                        $cols = [
                            'name' => 'Clinic',
                            'patients' => 'Total Patients',
                            'images' => 'Total Images',
                            'prescriptions' => 'Total Prescriptions',
                            'revenue' => 'Total Revenue',
                            'last_login' => 'Last Login',
                        ];
                    @endphp
                    @foreach($cols as $key => $label)
                        @php
                            $isActive = $sort === $key;
                            $nextDir = $isActive && $direction === 'asc' ? 'desc' : 'asc';
                            $icon = $isActive ? ($direction === 'asc' ? 'fa-sort-up' : 'fa-sort-down') : 'fa-sort';
                        @endphp
                        <th scope="col">
                            <a href="{{ route('master.reports.clinics', array_filter(['search' => $search, 'sort' => $key, 'direction' => $nextDir])) }}"
                               class="text-decoration-none text-nowrap">
                                <span>{{ $label }}</span>
                                <i class="fas {{ $icon }} ms-1 small {{ $isActive ? '' : 'text-muted' }}"></i>
                            </a>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($clinics as $clinic)
                    @php
                        $lastLogin = $clinic->last_login_at ? \Illuminate\Support\Carbon::parse($clinic->last_login_at) : null;
                    @endphp
                    <tr>
                        <td>{{ $clinic->name }}</td>
                        <td>{{ number_format($clinic->total_patients) }}</td>
                        <td>{{ number_format($clinic->total_images ?? 0) }}</td>
                        <td>{{ number_format($clinic->total_prescriptions) }}</td>
                        <td>{{ config('concure.currency_symbol', '$') }}{{ number_format($clinic->total_revenue ?? 0, 2) }}</td>
                        <td>
                            @if($lastLogin)
                                {{ $lastLogin->format('Y-m-d H:i') }}
                            @else
                                <span class="text-muted">Never</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No clinics found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-2">
            {{ $clinics->links() }}
        </div>
    </div>
</div>
@endsection
