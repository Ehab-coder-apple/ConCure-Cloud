@extends('layouts.app')

@section('title', __('Aesthetic Inventory'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-boxes text-primary me-2"></i>
                        {{ __('Aesthetic Inventory') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Manage products, consumables, and stock levels') }}</p>
                </div>
                <a href="{{ route('aesthetic.inventory.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>
                    {{ __('Add Product') }}
                </a>
            </div>

            <!-- Alert Cards -->
            @if($stats['low_stock'] > 0 || $stats['out_of_stock'] > 0 || $stats['expired'] > 0)
            <div class="row mb-4">
                @if($stats['low_stock'] > 0)
                <div class="col-md-4 mb-3">
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>{{ $stats['low_stock'] }}</strong> {{ __('item(s) are low on stock') }}
                    </div>
                </div>
                @endif
                @if($stats['out_of_stock'] > 0)
                <div class="col-md-4 mb-3">
                    <div class="alert alert-danger mb-0">
                        <i class="fas fa-times-circle me-2"></i>
                        <strong>{{ $stats['out_of_stock'] }}</strong> {{ __('item(s) are out of stock') }}
                    </div>
                </div>
                @endif
                @if($stats['expired'] > 0)
                <div class="col-md-4 mb-3">
                    <div class="alert alert-danger mb-0">
                        <i class="fas fa-calendar-times me-2"></i>
                        <strong>{{ $stats['expired'] }}</strong> {{ __('item(s) are expired') }}
                    </div>
                </div>
                @endif
            </div>
            @endif

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('aesthetic.inventory.index') }}">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">{{ __('Search') }}</label>
                                <input type="text" class="form-control" name="search" value="{{ request('search') }}"
                                       placeholder="{{ __('Search by product name...') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ __('Type') }}</label>
                                <select class="form-select" name="type">
                                    <option value="">{{ __('All Types') }}</option>
                                    @foreach($existingTypes ?? \App\Models\AestheticInventory::TYPES as $key => $label)
                                        <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>
                                            {{ __($label) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ __('Stock Status') }}</label>
                                <select class="form-select" name="stock_status">
                                    <option value="">{{ __('All') }}</option>
                                    <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>{{ __('Low Stock') }}</option>
                                    <option value="out" {{ request('stock_status') == 'out' ? 'selected' : '' }}>{{ __('Out of Stock') }}</option>
                                </select>
                            </div>
                            <div class="col-md-3">
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

            <!-- Inventory Table -->
            <div class="card">
                <div class="card-body">
                    @if($items->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>{{ __('Product') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Stock') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Expiry') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $item)
                                    <tr>
                                        <td>
                                            <strong>{{ $item->product_name }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ \App\Models\AestheticInventory::TYPES[$item->type] ?? $item->type }}</span>
                                        </td>
                                        <td>
                                            <strong>{{ $item->quantity }}</strong>
                                            <small class="text-muted">{{ __('(alert at :threshold)', ['threshold' => $item->low_stock_threshold]) }}</small>
                                        </td>
                                        <td>
                                            @if($item->quantity === 0)
                                                <span class="badge bg-danger">{{ __('Out of Stock') }}</span>
                                            @elseif($item->is_low_stock)
                                                <span class="badge bg-warning">{{ __('Low Stock') }}</span>
                                            @else
                                                <span class="badge bg-success">{{ __('OK') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($item->is_expired)
                                                <span class="badge bg-danger">{{ $item->expiry_date->format('M d, Y') }} - {{ __('Expired') }}</span>
                                            @elseif($item->is_near_expiry)
                                                <span class="badge bg-warning">{{ $item->expiry_date->format('M d, Y') }} - {{ __('Near Expiry') }}</span>
                                            @elseif($item->expiry_date)
                                                <span class="text-muted">{{ $item->expiry_date->format('M d, Y') }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#adjustModal{{ $item->id }}">
                                                    <i class="fas fa-sliders-h"></i>
                                                </button>
                                                <a href="{{ route('aesthetic.inventory.edit', $item) }}"
                                                   class="btn btn-sm btn-outline-primary" title="{{ __('Edit') }}">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" action="{{ route('aesthetic.inventory.destroy', $item) }}"
                                                      onsubmit="return confirm('{{ __('Are you sure you want to delete this product?') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('Delete') }}">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </div>

                                            <!-- Adjust Stock Modal -->
                                            <div class="modal fade" id="adjustModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form method="POST" action="{{ route('aesthetic.inventory.adjust-stock', $item) }}">
                                                            @csrf
                                                            <div class="modal-header">
                                                                <h6 class="modal-title">{{ __('Adjust Stock: :product', ['product' => $item->product_name]) }}</h6>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>{{ __('Current stock: :quantity', ['quantity' => $item->quantity]) }}</p>
                                                                <div class="mb-3">
                                                                    <label class="form-label">{{ __('Adjustment (+ add / - deduct)') }}</label>
                                                                    <input type="number" class="form-control" name="adjustment" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">{{ __('Reason') }}</label>
                                                                    <input type="text" class="form-control" name="reason" placeholder="{{ __('e.g., New shipment, Damaged items...') }}">
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                                                <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center mt-4">
                            {{ $items->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-boxes fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('No inventory items found') }}</h5>
                            <p class="text-muted">{{ __('Add your first product to start tracking stock.') }}</p>
                            <a href="{{ route('aesthetic.inventory.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>
                                {{ __('Add First Product') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
