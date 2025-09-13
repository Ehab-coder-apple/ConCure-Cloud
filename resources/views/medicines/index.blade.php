@extends('layouts.app')

@section('title', __('Medicine Inventory'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-pills text-primary me-2"></i>
                        {{ __('Medicine Inventory') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Manage your clinic\'s medicine inventory') }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('medicines.import') }}" class="btn btn-success">
                        <i class="fas fa-file-import me-1"></i>
                        {{ __('Import from Excel') }}
                    </a>
                    <a href="{{ route('medicines.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>
                        {{ __('Add Medicine') }}
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-primary">
                        <div class="card-body text-center">
                            <i class="fas fa-pills fa-2x text-primary mb-2"></i>
                            <h4 class="mb-1">{{ $stats['total'] }}</h4>
                            <small class="text-muted">{{ __('Total Medicines') }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-success">
                        <div class="card-body text-center">
                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                            <h4 class="mb-1">{{ $stats['active'] }}</h4>
                            <small class="text-muted">{{ __('Active Medicines') }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-warning">
                        <div class="card-body text-center">
                            <i class="fas fa-star fa-2x text-warning mb-2"></i>
                            <h4 class="mb-1">{{ $stats['frequent'] }}</h4>
                            <small class="text-muted">{{ __('Frequent Medicines') }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-info">
                        <div class="card-body text-center">
                            <i class="fas fa-layer-group fa-2x text-info mb-2"></i>
                            <h4 class="mb-1">{{ $stats['forms'] }}</h4>
                            <small class="text-muted">{{ __('Medicine Forms') }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('medicines.index') }}">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">{{ __('Search') }}</label>
                                <input type="text" class="form-control" name="search" value="{{ request('search') }}" 
                                       placeholder="{{ __('Search by name, generic name, or brand...') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">{{ __('Form') }}</label>
                                <select class="form-select" name="form">
                                    <option value="">{{ __('All Forms') }}</option>
                                    @foreach(\App\Models\Medicine::FORMS as $key => $label)
                                        <option value="{{ $key }}" {{ request('form') == $key ? 'selected' : '' }}>
                                            {{ __($label) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">{{ __('Status') }}</label>
                                <select class="form-select" name="status">
                                    <option value="">{{ __('All Status') }}</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">{{ __('Frequent') }}</label>
                                <select class="form-select" name="frequent">
                                    <option value="">{{ __('All') }}</option>
                                    <option value="1" {{ request('frequent') == '1' ? 'selected' : '' }}>{{ __('Frequent Only') }}</option>
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

            <!-- Medicines Table -->
            <div class="card">
                <div class="card-body">
                    @if($medicines->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('Medicine') }}</th>
                                        <th>{{ __('Form') }}</th>
                                        <th>{{ __('Dosage') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Frequent') }}</th>
                                        <th>{{ __('Created') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($medicines as $medicine)
                                    <tr>
                                        <td>
                                            <div>
                                                <strong>{{ $medicine->name }}</strong>
                                                @if($medicine->brand_name)
                                                    <small class="text-muted d-block">{{ __('Brand') }}: {{ $medicine->brand_name }}</small>
                                                @endif
                                                @if($medicine->generic_name)
                                                    <small class="text-muted d-block">{{ __('Generic') }}: {{ $medicine->generic_name }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $medicine->form_display }}</span>
                                        </td>
                                        <td>{{ $medicine->dosage ?? '-' }}</td>
                                        <td>
                                            @if($medicine->is_active)
                                                <span class="badge bg-success">{{ __('Active') }}</span>
                                            @else
                                                <span class="badge bg-danger">{{ __('Inactive') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($medicine->is_frequent)
                                                <i class="fas fa-star text-warning" title="{{ __('Frequent Medicine') }}"></i>
                                            @else
                                                <i class="far fa-star text-muted" title="{{ __('Regular Medicine') }}"></i>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $medicine->created_at->format('M d, Y') }}<br>
                                                {{ __('by') }} {{ $medicine->creator->name ?? 'System' }}
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('medicines.show', $medicine) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @can('update', $medicine)
                                                <a href="{{ route('medicines.edit', $medicine) }}" class="btn btn-sm btn-outline-secondary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @endcan
                                                @can('update', $medicine)
                                                <form method="POST" action="{{ route('medicines.toggle-status', $medicine) }}" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm {{ $medicine->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}" 
                                                            title="{{ $medicine->is_active ? __('Deactivate') : __('Activate') }}">
                                                        <i class="fas {{ $medicine->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                                    </button>
                                                </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $medicines->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-pills fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('No medicines found') }}</h5>
                            <p class="text-muted">{{ __('Start building your medicine inventory by adding medicines.') }}</p>
                            <a href="{{ route('medicines.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>
                                {{ __('Add First Medicine') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
