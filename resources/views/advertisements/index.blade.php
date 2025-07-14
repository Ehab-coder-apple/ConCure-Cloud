@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-bullhorn text-primary"></i>
                    Advertisement Management
                </h1>
                @can('manage-advertisements')
                <a href="{{ route('advertisements.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Advertisement
                </a>
                @endcan
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('advertisements.index') }}">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="{{ request('search') }}" placeholder="Search advertisements...">
                            </div>
                            <div class="col-md-2">
                                <label for="type" class="form-label">Type</label>
                                <select class="form-select" id="type" name="type">
                                    <option value="">All Types</option>
                                    @foreach(\App\Models\Advertisement::TYPES as $key => $value)
                                        <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="position" class="form-label">Position</label>
                                <select class="form-select" id="position" name="position">
                                    <option value="">All Positions</option>
                                    @foreach(\App\Models\Advertisement::POSITIONS as $key => $value)
                                        <option value="{{ $key }}" {{ request('position') === $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">All Status</option>
                                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>
                                        Active
                                    </option>
                                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>
                                        Inactive
                                    </option>
                                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>
                                        Expired
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-3">
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

    <!-- Advertisements List -->
    <div class="row">
        @forelse($advertisements as $advertisement)
        <div class="col-lg-6 col-xl-4 mb-4">
            <div class="card h-100">
                @if($advertisement->hasImage())
                <div class="position-relative">
                    <img src="{{ $advertisement->image_url }}" class="card-img-top" alt="{{ $advertisement->translated_title }}" 
                         style="height: 200px; object-fit: cover;">
                    <div class="position-absolute top-0 end-0 m-2">
                        @if($advertisement->isCurrentlyActive())
                            <span class="badge bg-success">Active</span>
                        @elseif($advertisement->isExpired())
                            <span class="badge bg-danger">Expired</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </div>
                </div>
                @endif
                
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">{{ $advertisement->translated_title }}</h6>
                    <div>
                        <span class="badge bg-primary">{{ $advertisement->type_display }}</span>
                        <span class="badge bg-secondary">{{ $advertisement->position_display }}</span>
                    </div>
                </div>
                
                <div class="card-body">
                    @if($advertisement->translated_description)
                    <p class="text-muted small mb-3">{{ Str::limit($advertisement->translated_description, 100) }}</p>
                    @endif

                    <!-- Target Audience -->
                    <div class="mb-2">
                        <small class="text-muted">Target Audience:</small>
                        <div>
                            @if($advertisement->target_audience)
                                @foreach($advertisement->target_audience as $audience)
                                    <span class="badge bg-info me-1">
                                        {{ \App\Models\Advertisement::TARGET_AUDIENCES[$audience] ?? $audience }}
                                    </span>
                                @endforeach
                            @else
                                <span class="badge bg-info">All Users</span>
                            @endif
                        </div>
                    </div>

                    <!-- Date Range -->
                    <div class="mb-2">
                        <small class="text-muted">
                            <i class="fas fa-calendar"></i>
                            @if($advertisement->start_date)
                                {{ $advertisement->start_date->format('M d, Y') }}
                            @else
                                No start date
                            @endif
                            -
                            @if($advertisement->end_date)
                                {{ $advertisement->end_date->format('M d, Y') }}
                            @else
                                No end date
                            @endif
                        </small>
                    </div>

                    <!-- Statistics -->
                    <div class="row text-center mb-3">
                        <div class="col-4">
                            <div class="border rounded p-2">
                                <div class="fw-bold text-primary">{{ number_format($advertisement->view_count) }}</div>
                                <small class="text-muted">Views</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-2">
                                <div class="fw-bold text-success">{{ number_format($advertisement->click_count) }}</div>
                                <small class="text-muted">Clicks</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-2">
                                <div class="fw-bold text-warning">{{ $advertisement->click_through_rate }}%</div>
                                <small class="text-muted">CTR</small>
                            </div>
                        </div>
                    </div>

                    @if($advertisement->link_url)
                    <p class="small text-muted mb-2">
                        <i class="fas fa-link"></i>
                        <a href="{{ $advertisement->link_url }}" target="_blank" class="text-decoration-none">
                            {{ Str::limit($advertisement->link_url, 40) }}
                        </a>
                    </p>
                    @endif
                </div>
                
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="btn-group">
                            <a href="{{ route('advertisements.show', $advertisement) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                            @can('manage-advertisements')
                            <a href="{{ route('advertisements.edit', $advertisement) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endcan
                        </div>
                        
                        @can('manage-advertisements')
                        <div class="btn-group">
                            <form method="POST" action="{{ route('advertisements.toggle-status', $advertisement) }}" style="display: inline;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-outline-{{ $advertisement->is_active ? 'warning' : 'success' }}">
                                    <i class="fas fa-{{ $advertisement->is_active ? 'pause' : 'play' }}"></i>
                                    {{ $advertisement->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                            
                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                    onclick="confirmDelete('{{ $advertisement->id }}', '{{ $advertisement->translated_title }}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        @endcan
                    </div>
                    
                    <div class="mt-2">
                        <small class="text-muted">
                            Priority: {{ $advertisement->priority }} | 
                            Created: {{ $advertisement->created_at->format('M d, Y') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="text-center py-5">
                <i class="fas fa-bullhorn fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No advertisements found</h5>
                <p class="text-muted">Create your first advertisement to start promoting your services.</p>
                @can('manage-advertisements')
                <a href="{{ route('advertisements.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create First Advertisement
                </a>
                @endcan
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($advertisements->hasPages())
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-center">
                {{ $advertisements->withQueryString()->links() }}
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
                <p>Are you sure you want to delete <strong id="advertisementTitle"></strong>?</p>
                <p class="text-muted small">This action cannot be undone and will also delete the associated image.</p>
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

@push('scripts')
<script>
function confirmDelete(advertisementId, advertisementTitle) {
    document.getElementById('advertisementTitle').textContent = advertisementTitle;
    document.getElementById('deleteForm').action = `/advertisements/${advertisementId}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush
@endsection
