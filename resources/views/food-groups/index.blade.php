@extends('layouts.app')

@section('title', __('Food Groups'))

@push('styles')
<link href="{{ asset('css/table-fix.css') }}" rel="stylesheet">
<style>
/* Force table text colors */
.table td, .table th,
.table td *, .table th *,
.table tbody tr td,
.table tbody tr td strong,
.table tbody tr td div,
.table tbody tr td span:not(.badge),
.table tbody tr td small {
    color: #333 !important;
}

/* Badge colors */
.table .badge {
    color: #fff !important;
}

.badge-primary {
    background-color: #007bff !important;
    color: #fff !important;
}

.badge-secondary {
    background-color: #6c757d !important;
    color: #fff !important;
}

.badge-success {
    background-color: #28a745 !important;
    color: #fff !important;
}

.badge-warning {
    background-color: #ffc107 !important;
    color: #212529 !important;
}

/* Specific overrides */
.table .text-dark {
    color: #333 !important;
}

.table .text-secondary {
    color: #6c757d !important;
}

.table .text-muted {
    color: #6c757d !important;
}

/* Force all table content to be dark */
.card .table-responsive .table tbody tr td {
    color: #333 !important;
}

.card .table-responsive .table tbody tr td strong {
    color: #333 !important;
}

.card .table-responsive .table tbody tr td div {
    color: #333 !important;
}
</style>
@endpush

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-layer-group text-primary"></i>
                        {{ __('Food Groups') }}
                    </h1>
                    <p class="text-muted">{{ __('Manage food categories and groups') }}</p>
                </div>
                <div>
                    <a href="{{ route('foods.index') }}" class="btn btn-outline-primary me-2">
                        <i class="fas fa-apple-alt"></i> {{ __('Foods') }}
                    </a>
                    @can('manage-food-composition')
                        <a href="{{ route('food-groups.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> {{ __('Add Food Group') }}
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('food-groups.index') }}">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="search" class="form-label">{{ __('Search Food Groups') }}</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="search" 
                                       name="search" 
                                       value="{{ request('search') }}" 
                                       placeholder="{{ __('Search by name or description...') }}">
                            </div>
                            <div class="col-md-3">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-search"></i> {{ __('Search') }}
                                </button>
                            </div>
                            <div class="col-md-3">
                                <label>&nbsp;</label>
                                <a href="{{ route('food-groups.index') }}" class="btn btn-outline-secondary btn-block">
                                    <i class="fas fa-times"></i> {{ __('Clear') }}
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Food Groups List -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list"></i> {{ __('Food Groups List') }}
                        <span class="badge badge-info ml-2">{{ $foodGroups->total() }}</span>
                    </h6>
                </div>
                <div class="card-body">
                    @if($foodGroups->count() > 0)
                        <div class="table-responsive">
                            <table id="food-groups-table" class="table table-bordered table-hover" style="color: #333 !important;">
                                <thead class="thead-light">
                                    <tr>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Description') }}</th>
                                        <th>{{ __('Foods Count') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Created') }}</th>
                                        <th width="150">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($foodGroups as $group)
                                        <tr data-group-id="{{ $group->id }}" data-group-name="{{ $group->name }}">
                                            <td style="color: #333 !important;">
                                                <strong style="color: #333 !important;">{{ $group->translated_name }}</strong>
                                                @if($group->name_translations)
                                                    <div class="small text-muted mt-1">
                                                        @if($group->getNameTranslation('en'))
                                                            <span class="badge badge-secondary">EN: {{ $group->getNameTranslation('en') }}</span>
                                                        @endif
                                                        @if($group->getNameTranslation('ar'))
                                                            <span class="badge badge-secondary">AR: {{ $group->getNameTranslation('ar') }}</span>
                                                        @endif
                                                        @if($group->getNameTranslation('ku'))
                                                            <span class="badge badge-secondary">KU: {{ $group->getNameTranslation('ku') }}</span>
                                                        @endif
                                                    </div>
                                                @endif
                                            </td>
                                            <td style="color: #333 !important;">
                                                <div class="text-truncate" style="max-width: 200px; color: #333 !important;">
                                                    {{ $group->translated_description ?? __('No description') }}
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-primary">
                                                    {{ $group->foods_count ?? 0 }} {{ __('foods') }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($group->is_active)
                                                    <span class="badge badge-success">{{ __('Active') }}</span>
                                                @else
                                                    <span class="badge badge-warning">{{ __('Inactive') }}</span>
                                                @endif
                                            </td>
                                            <td style="color: #333 !important;">
                                                <small style="color: #6c757d !important;">
                                                    {{ $group->created_at->format('M d, Y') }}
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('food-groups.show', $group) }}" 
                                                       class="btn btn-sm btn-outline-primary" 
                                                       title="{{ __('View Details') }}">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @can('manage-food-composition')
                                                        <a href="{{ route('food-groups.edit', $group) }}" 
                                                           class="btn btn-sm btn-outline-warning" 
                                                           title="{{ __('Edit') }}">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        @if($group->foods_count == 0)
                                                            <button type="button" 
                                                                    class="btn btn-sm btn-outline-danger" 
                                                                    title="{{ __('Delete') }}"
                                                                    onclick="confirmDelete('{{ $group->id }}', '{{ $group->translated_name }}')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        @endif
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $foodGroups->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-layer-group fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('No Food Groups Found') }}</h5>
                            <p class="text-muted">
                                @if(request('search'))
                                    {{ __('No food groups match your search criteria.') }}
                                    <a href="{{ route('food-groups.index') }}">{{ __('View all food groups') }}</a>
                                @else
                                    {{ __('Start by creating your first food group.') }}
                                @endif
                            </p>
                            @can('manage-food-composition')
                                @if(!request('search'))
                                    <a href="{{ route('food-groups.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> {{ __('Add First Food Group') }}
                                    </a>
                                @endif
                            @endcan
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @can('manage-food-composition')
        <!-- Delete Forms (Hidden) -->
        @foreach($foodGroups as $group)
            <form id="deleteForm{{ $group->id }}" action="{{ route('food-groups.destroy', $group) }}" method="POST" style="display: none;">
                @csrf
                @method('DELETE')
            </form>
        @endforeach
    @endcan
</div>
@endsection

@push('scripts')
<script>
function confirmDelete(groupId, groupName) {
    if (confirm(`{{ __('Are you sure you want to delete the food group') }} "${groupName}"? {{ __('This action cannot be undone.') }}`)) {
        document.getElementById('deleteForm' + groupId).submit();
    }
}

// Force text colors after page load and check for duplicates
document.addEventListener('DOMContentLoaded', function() {
    // Check for duplicate rows
    const rows = document.querySelectorAll('#food-groups-table tbody tr');
    const groupIds = [];
    let duplicatesFound = false;

    rows.forEach(function(row) {
        const groupId = row.getAttribute('data-group-id');
        if (groupIds.includes(groupId)) {
            console.warn('Duplicate food group found:', groupId);
            row.style.backgroundColor = '#ffcccc'; // Highlight duplicates in red
            duplicatesFound = true;
        } else {
            groupIds.push(groupId);
        }
    });

    if (duplicatesFound) {
        console.warn('Duplicates detected in food groups table');
    } else {
        console.log('No duplicates found. Total unique groups:', groupIds.length);
    }
    // Force all table cells to have dark text
    const tableCells = document.querySelectorAll('.table td, .table th');
    tableCells.forEach(function(cell) {
        cell.style.color = '#333';
        cell.style.setProperty('color', '#333', 'important');
    });

    // Force all text elements in table cells
    const textElements = document.querySelectorAll('.table td *, .table th *');
    textElements.forEach(function(element) {
        if (!element.classList.contains('badge')) {
            element.style.color = '#333';
            element.style.setProperty('color', '#333', 'important');
        }
    });

    // Ensure badges keep their colors
    const badges = document.querySelectorAll('.badge');
    badges.forEach(function(badge) {
        if (badge.classList.contains('badge-primary')) {
            badge.style.backgroundColor = '#007bff';
            badge.style.color = '#fff';
        } else if (badge.classList.contains('badge-secondary')) {
            badge.style.backgroundColor = '#6c757d';
            badge.style.color = '#fff';
        } else if (badge.classList.contains('badge-success')) {
            badge.style.backgroundColor = '#28a745';
            badge.style.color = '#fff';
        } else if (badge.classList.contains('badge-warning')) {
            badge.style.backgroundColor = '#ffc107';
            badge.style.color = '#212529';
        }
    });
});
</script>
@endpush
