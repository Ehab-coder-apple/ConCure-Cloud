@extends('layouts.app')

@push('styles')
<style>
.table td, .table th {
    color: #333 !important;
}
.table .badge {
    color: #fff !important;
}
.table .text-dark {
    color: #333 !important;
}
.table .text-secondary {
    color: #6c757d !important;
}
.table .text-muted {
    color: #6c757d !important;
}
</style>
@endpush

@section('styles')
<style>
    .foods-table {
        font-size: 0.9rem;
    }

    .foods-table td {
        vertical-align: middle;
        padding: 0.75rem 0.5rem;
    }

    .foods-table th {
        font-weight: 600;
        font-size: 0.85rem;
        padding: 0.75rem 0.5rem;
        border-bottom: 2px solid #dee2e6;
    }

    .nutrition-value {
        font-weight: 600;
        font-size: 0.95rem;
    }

    .nutrition-unit {
        font-size: 0.75rem;
        color: #6c757d;
    }

    .food-name {
        font-weight: 600;
        color: #495057;
    }

    .food-description {
        font-size: 0.8rem;
        color: #6c757d;
        margin-top: 2px;
    }

    .category-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        min-width: 60px;
        text-align: center;
        border: 1px solid rgba(0,0,0,0.1);
        font-weight: 500;
    }

    /* Fallback for category badges without proper colors */
    .category-badge:not([style*="background-color"]) {
        background-color: #6c757d !important;
        color: white !important;
    }

    /* Ensure text is always visible for problematic colors */
    .category-badge[style*="background-color: #ffffff"],
    .category-badge[style*="background-color: white"],
    .category-badge[style*="background-color: #000000"],
    .category-badge[style*="background-color: black"],
    .category-badge[style*="background-color: transparent"] {
        background-color: #6c757d !important;
        color: white !important;
    }

    .btn-group-sm .btn {
        padding: 0.25rem 0.4rem;
        font-size: 0.8rem;
    }

    @media (max-width: 768px) {
        .foods-table {
            font-size: 0.8rem;
        }

        .foods-table td, .foods-table th {
            padding: 0.5rem 0.25rem;
        }
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-apple-alt text-primary"></i>
                    Food Composition Database
                </h1>
                @if(auth()->user()->hasPermission('food_database_view'))
                <div>
                    @if(auth()->user()->hasPermission('food_database_groups'))
                    <a href="{{ route('food-groups.index') }}" class="btn btn-outline-primary me-2">
                        <i class="fas fa-layer-group"></i> Food Groups
                    </a>
                    @endif
                    @if(auth()->user()->hasPermission('food_database_import'))
                    <a href="{{ route('foods.import') }}" class="btn btn-outline-success me-2">
                        <i class="fas fa-upload"></i> Import Foods
                    </a>
                    @endif
                    @if($foods->count() > 0 && auth()->user()->hasPermission('food_database_clear'))
                    <button type="button" class="btn btn-outline-danger me-2" onclick="confirmClearAll()">
                        <i class="fas fa-trash-alt"></i> Clear All Foods
                    </button>
                    @endif
                    @if(auth()->user()->hasPermission('food_database_create'))
                    <a href="{{ route('foods.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Food
                    </a>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('foods.index') }}">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="search" class="form-label">Search Foods</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="{{ request('search') }}" placeholder="Search by name...">
                            </div>
                            <div class="col-md-3">
                                <label for="food_group_id" class="form-label">Food Group</label>
                                <select class="form-select" id="food_group_id" name="food_group_id">
                                    <option value="">All Groups</option>
                                    @foreach($foodGroups as $group)
                                        <option value="{{ $group->id }}" 
                                                {{ request('food_group_id') == $group->id ? 'selected' : '' }}>
                                            {{ $group->translated_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="type" class="form-label">Type</label>
                                <select class="form-select" id="type" name="type">
                                    <option value="">All Types</option>
                                    <option value="standard" {{ request('type') === 'standard' ? 'selected' : '' }}>
                                        Standard
                                    </option>
                                    <option value="custom" {{ request('type') === 'custom' ? 'selected' : '' }}>
                                        Custom
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="nutrition_filter" class="form-label">Nutrition</label>
                                <select class="form-select" id="nutrition_filter" name="nutrition_filter">
                                    <option value="">All Foods</option>
                                    <option value="high_protein" {{ request('nutrition_filter') === 'high_protein' ? 'selected' : '' }}>
                                        High Protein
                                    </option>
                                    <option value="low_calorie" {{ request('nutrition_filter') === 'low_calorie' ? 'selected' : '' }}>
                                        Low Calorie
                                    </option>
                                    <option value="high_fiber" {{ request('nutrition_filter') === 'high_fiber' ? 'selected' : '' }}>
                                        High Fiber
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Filter
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Foods Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body p-0">
                    @if($foods->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 foods-table">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 25%;">Food Name</th>
                                    <th style="width: 15%;">Category</th>
                                    <th style="width: 8%;" class="text-center">Calories</th>
                                    <th style="width: 8%;" class="text-center">Protein</th>
                                    <th style="width: 8%;" class="text-center">Carbs</th>
                                    <th style="width: 8%;" class="text-center">Fat</th>
                                    <th style="width: 8%;" class="text-center">Fiber</th>
                                    <th style="width: 10%;" class="text-center">Serving</th>
                                    <th style="width: 8%;" class="text-center">Type</th>
                                    <th style="width: 12%;" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($foods as $food)
                                <tr>
                                    <td>
                                        <div class="food-name">{{ $food->translated_name }}</div>
                                        @if($food->translated_description)
                                        <div class="food-description">{{ Str::limit($food->translated_description, 60) }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $bgColor = $food->foodGroup->color ?? '#6c757d';
                                            // Ensure we have a valid color
                                            if (empty($bgColor) || $bgColor === '#ffffff' || $bgColor === 'white') {
                                                $bgColor = '#6c757d'; // Default gray
                                            }
                                            // Determine text color based on background
                                            $textColor = 'white';
                                            if ($bgColor === '#FFEB3B' || $bgColor === '#FFC107' || $bgColor === '#FFFF00') {
                                                $textColor = '#333'; // Dark text for yellow backgrounds
                                            }
                                        @endphp
                                        <span class="badge rounded-pill category-badge" style="background-color: {{ $bgColor }}; color: {{ $textColor }};">
                                            {{ $food->foodGroup->translated_name }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="nutrition-value text-primary">{{ $food->calories }}</span>
                                        <br><span class="nutrition-unit">kcal</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="nutrition-value text-success">{{ $food->protein }}</span>
                                        <br><span class="nutrition-unit">g</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="nutrition-value text-warning">{{ $food->carbohydrates }}</span>
                                        <br><span class="nutrition-unit">g</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="nutrition-value text-danger">{{ $food->fat }}</span>
                                        <br><span class="nutrition-unit">g</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="nutrition-value text-info">{{ $food->fiber ?? 0 }}</span>
                                        <br><span class="nutrition-unit">g</span>
                                    </td>
                                    <td class="text-center">
                                        @if($food->serving_size)
                                        <small class="text-muted">{{ $food->serving_size }}</small>
                                        @else
                                        <small class="text-muted">100g</small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($food->is_custom)
                                            <span class="badge bg-info">Custom</span>
                                        @else
                                            <span class="badge bg-secondary">Standard</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('foods.show', $food) }}" class="btn btn-outline-primary btn-sm" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if(auth()->user()->hasPermission('food_database_edit') && ($food->is_custom || auth()->user()->role === 'program_owner'))
                                            <a href="{{ route('foods.edit', $food) }}" class="btn btn-outline-secondary btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endif
                                            @if(auth()->user()->hasPermission('food_database_delete') && ($food->is_custom || auth()->user()->role === 'program_owner'))
                                            <button type="button" class="btn btn-outline-danger btn-sm"
                                                    onclick="confirmDelete('{{ $food->id }}', '{{ $food->translated_name }}')" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="fas fa-apple-alt fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No foods found</h5>
                        <p class="text-muted">Try adjusting your search criteria or add a new food item.</p>
                        @if(auth()->user()->hasPermission('food_database_create'))
                        <a href="{{ route('foods.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add First Food
                        </a>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    @if($foods->hasPages())
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-center">
                {{ $foods->withQueryString()->links() }}
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="foodName"></strong>?</p>
                <p class="text-muted small">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Clear All Foods Confirmation Modal -->
<div class="modal fade" id="clearAllModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle"></i> Clear All Foods
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-warning"></i>
                    <strong>Warning:</strong> This action will permanently delete ALL food items from your database.
                </div>
                <p><strong>Are you sure you want to clear all {{ $foods->total() }} food items?</strong></p>
                <p class="text-muted">This will:</p>
                <ul class="text-muted">
                    <li>Delete all custom and imported food items</li>
                    <li>Remove all food data from your clinic's database</li>
                    <li>Allow you to start fresh with a new food list</li>
                </ul>
                <p class="text-danger"><strong>This action cannot be undone!</strong></p>

                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" id="confirmClearAll">
                    <label class="form-check-label" for="confirmClearAll">
                        I understand this will permanently delete all food items
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="clearAllForm" action="{{ route('foods.clear-all') }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" id="clearAllButton" disabled>
                        <i class="fas fa-trash-alt"></i> Clear All Foods
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function confirmDelete(foodId, foodName) {
    document.getElementById('foodName').textContent = foodName;
    document.getElementById('deleteForm').action = `/foods/${foodId}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function confirmClearAll() {
    new bootstrap.Modal(document.getElementById('clearAllModal')).show();
}

// Enable/disable clear all button based on checkbox
document.addEventListener('DOMContentLoaded', function() {
    const checkbox = document.getElementById('confirmClearAll');
    const button = document.getElementById('clearAllButton');

    if (checkbox && button) {
        checkbox.addEventListener('change', function() {
            button.disabled = !this.checked;
        });
    }
});
</script>
@endpush
@endsection
