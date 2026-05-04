@extends('layouts.app')

@section('title', __('Aesthetic Treatments'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-spa text-primary me-2"></i>
                        {{ __('Aesthetic Treatments') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Manage your clinic\'s aesthetic treatment catalog') }}</p>
                </div>
                <a href="{{ route('aesthetic.treatments.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>
                    {{ __('Add Treatment') }}
                </a>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-lg-4 col-md-4 mb-3">
                    <div class="card border-primary">
                        <div class="card-body text-center">
                            <i class="fas fa-spa fa-2x text-primary mb-2"></i>
                            <h4 class="mb-1">{{ $stats['total'] }}</h4>
                            <small class="text-muted">{{ __('Total Treatments') }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 mb-3">
                    <div class="card border-success">
                        <div class="card-body text-center">
                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                            <h4 class="mb-1">{{ $stats['active'] }}</h4>
                            <small class="text-muted">{{ __('Active Treatments') }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 mb-3">
                    <div class="card border-secondary">
                        <div class="card-body text-center">
                            <i class="fas fa-pause-circle fa-2x text-secondary mb-2"></i>
                            <h4 class="mb-1">{{ $stats['inactive'] }}</h4>
                            <small class="text-muted">{{ __('Inactive Treatments') }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('aesthetic.treatments.index') }}">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">{{ __('Search') }}</label>
                                <input type="text" class="form-control" name="search" value="{{ request('search') }}"
                                       placeholder="{{ __('Search by name or description...') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ __('Category') }}</label>
                                <select class="form-select" name="category">
                                    <option value="">{{ __('All Categories') }}</option>
                                    @foreach($existingCategories ?? \App\Models\AestheticTreatment::CATEGORIES as $key => $label)
                                        <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>
                                            {{ __($label) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ __('Status') }}</label>
                                <select class="form-select" name="status">
                                    <option value="">{{ __('All Status') }}</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
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

            <!-- Treatments Table -->
            <div class="card">
                <div class="card-body">
                    @if($treatments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Category') }}</th>
                                        <th>{{ __('Price') }}</th>
                                        <th>{{ __('Sessions') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($treatments as $treatment)
                                    <tr>
                                        <td>
                                            <strong>{{ $treatment->name }}</strong>
                                            @if($treatment->description)
                                                <small class="text-muted d-block">{{ Str::limit($treatment->description, 60) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $treatment->category_color }}">
                                                {{ $treatment->category_display }}
                                            </span>
                                        </td>
                                        <td>
                                            @php($currency = \DB::table('settings')->where('clinic_id', auth()->user()->clinic_id)->where('key', 'currency')->value('value') ?? 'USD')
                                            <strong>{{ $currency }} {{ number_format($treatment->default_price, 2) }}</strong>
                                        </td>
                                        <td>
                                            @if($treatment->session_required)
                                                <span class="badge bg-info">{{ $treatment->sessions_count }} {{ __('session(s)') }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($treatment->is_active)
                                                <span class="badge bg-success">{{ __('Active') }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('aesthetic.treatments.edit', $treatment) }}"
                                                   class="btn btn-sm btn-outline-primary" title="{{ __('Edit') }}">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" action="{{ route('aesthetic.treatments.destroy', $treatment) }}"
                                                      onsubmit="return confirm('{{ __('Are you sure you want to delete this treatment?') }}')">
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
                            {{ $treatments->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-spa fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('No treatments found') }}</h5>
                            <p class="text-muted">{{ __('Start building your aesthetic treatment catalog.') }}</p>
                            <a href="{{ route('aesthetic.treatments.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>
                                {{ __('Add First Treatment') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
