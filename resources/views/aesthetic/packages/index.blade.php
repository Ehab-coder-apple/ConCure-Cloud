@extends('layouts.app')

@section('title', __('Aesthetic Packages'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-box-open text-primary me-2"></i>
                        {{ __('Aesthetic Packages') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Manage treatment packages and bundles') }}</p>
                </div>
                <a href="{{ route('aesthetic.packages.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>
                    {{ __('Add Package') }}
                </a>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-lg-4 col-md-4 mb-3">
                    <div class="card border-primary">
                        <div class="card-body text-center">
                            <i class="fas fa-box-open fa-2x text-primary mb-2"></i>
                            <h4 class="mb-1">{{ $stats['total'] }}</h4>
                            <small class="text-muted">{{ __('Total Packages') }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 mb-3">
                    <div class="card border-success">
                        <div class="card-body text-center">
                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                            <h4 class="mb-1">{{ $stats['active'] }}</h4>
                            <small class="text-muted">{{ __('Active Packages') }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 mb-3">
                    <div class="card border-danger">
                        <div class="card-body text-center">
                            <i class="fas fa-hourglass-end fa-2x text-danger mb-2"></i>
                            <h4 class="mb-1">{{ $stats['expired'] }}</h4>
                            <small class="text-muted">{{ __('Expired Packages') }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('aesthetic.packages.index') }}">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">{{ __('Search') }}</label>
                                <input type="text" class="form-control" name="search" value="{{ request('search') }}"
                                       placeholder="{{ __('Search by package or treatment name...') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ __('Treatment') }}</label>
                                <select class="form-select" name="treatment_id">
                                    <option value="">{{ __('All Treatments') }}</option>
                                    @foreach($treatments as $treatment)
                                        <option value="{{ $treatment->id }}" {{ request('treatment_id') == $treatment->id ? 'selected' : '' }}>
                                            {{ $treatment->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ __('Status') }}</label>
                                <select class="form-select" name="status">
                                    <option value="">{{ __('All Status') }}</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>{{ __('Expired') }}</option>
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

            <!-- Packages Table -->
            <div class="card">
                <div class="card-body">
                    @if($packages->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>{{ __('Package') }}</th>
                                        <th>{{ __('Treatment') }}</th>
                                        <th>{{ __('Sessions') }}</th>
                                        <th>{{ __('Price') }}</th>
                                        <th>{{ __('Final Price') }}</th>
                                        <th>{{ __('Expiry') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($packages as $package)
                                    <tr>
                                        <td>
                                            <strong>{{ $package->name }}</strong>
                                        </td>
                                        <td>
                                            @if($package->treatments->count() > 0)
                                                @foreach($package->treatments as $pt)
                                                    <span class="badge bg-{{ $pt->category_color }} me-1">
                                                        {{ $pt->name }}
                                                    </span>
                                                @endforeach
                                            @elseif($package->treatment)
                                                <span class="badge bg-{{ $package->treatment->category_color }}">
                                                    {{ $package->treatment->name }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $package->total_sessions }} {{ __('sessions') }}</span>
                                        </td>
                                        <td>
                                            <strong>{{ number_format($package->price, 2) }}</strong>
                                        </td>
                                        <td>
                                            <strong class="text-success">{{ number_format($package->final_price, 2) }}</strong>
                                            @if($package->discount > 0)
                                                <br><small class="text-muted">-{{ number_format($package->discount, 2) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($package->expiry_date)
                                                {{ $package->expiry_date->format('M d, Y') }}
                                            @else
                                                <span class="text-muted">{{ __('No expiry') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($package->is_expired)
                                                <span class="badge bg-danger">{{ __('Expired') }}</span>
                                            @else
                                                <span class="badge bg-success">{{ __('Active') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('aesthetic.packages.edit', $package) }}"
                                                   class="btn btn-sm btn-outline-primary" title="{{ __('Edit') }}">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" action="{{ route('aesthetic.packages.destroy', $package) }}"
                                                      onsubmit="return confirm('{{ __('Are you sure you want to delete this package?') }}')">
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

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $packages->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('No packages found') }}</h5>
                            <p class="text-muted">{{ __('Create your first treatment package to offer bundled sessions.') }}</p>
                            <a href="{{ route('aesthetic.packages.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>
                                {{ __('Add First Package') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
