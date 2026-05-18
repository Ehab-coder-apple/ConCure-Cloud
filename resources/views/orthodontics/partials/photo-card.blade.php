<div class="col-md-4 col-sm-6 mb-4">
    <div class="card photo-card" data-photo-id="{{ $photo->id }}">
        <!-- Selection Checkbox -->
        <input type="checkbox" class="form-check-input photo-select-checkbox" 
               data-photo-id="{{ $photo->id }}"
               data-photo-path="{{ asset('storage/' . $photo->file_path) }}"
               data-photo-date="{{ $photo->photo_date->format('M d, Y') }}"
               data-photo-type="{{ $photo->photo_type }}"
               data-view-type="{{ $photo->view_type }}"
               data-stage="{{ $photo->stage }}">
        
        <!-- Photo Image -->
        <img src="{{ asset('storage/' . $photo->file_path) }}" 
             class="card-img-top photo-image" 
             alt="{{ $photo->photo_type }}" 
             style="height: 220px; object-fit: cover;"
             data-bs-toggle="modal"
             data-bs-target="#photoViewModal"
             data-photo-src="{{ asset('storage/' . $photo->file_path) }}"
             data-photo-info="{{ $photo->photo_type }} - {{ $photo->view_type }}">
        
        <!-- Action Buttons -->
        <div class="photo-actions">
            <button type="button" class="btn btn-sm btn-light" 
                    data-bs-toggle="modal" 
                    data-bs-target="#photoViewModal"
                    onclick="viewSinglePhoto('{{ asset('storage/' . $photo->file_path) }}', '{{ $photo->photo_type }} - {{ $photo->view_type }}', '{{ $photo->photo_date->format('M d, Y') }}')">
                <i class="fas fa-search-plus"></i>
            </button>
            <form action="{{ route('orthodontics.photos.destroy', [$orthodonticCase, $photo]) }}" method="POST" style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('{{ __('Are you sure you want to delete this photo?') }}')">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        </div>
        
        <!-- Photo Info -->
        <div class="card-body p-3">
            <div class="d-flex flex-wrap gap-1 mb-2">
                <span class="badge photo-badge photo-type-badge">
                    <i class="fas fa-camera me-1"></i>{{ ucfirst($photo->photo_type) }}
                </span>
                <span class="badge photo-badge view-type-badge">
                    <i class="fas fa-eye me-1"></i>{{ ucfirst($photo->view_type) }}
                </span>
                <span class="badge photo-badge stage-badge">
                    <i class="fas fa-flag me-1"></i>{{ ucfirst($photo->stage) }}
                </span>
            </div>
            
            <div class="d-flex align-items-center text-muted small mb-2">
                <i class="fas fa-calendar-day me-2"></i>
                {{ $photo->photo_date->format('M d, Y') }}
            </div>
            
            @if($photo->notes)
                <p class="text-muted small mb-0">
                    <i class="fas fa-sticky-note me-1"></i>
                    {{ Str::limit($photo->notes, 60) }}
                </p>
            @endif
        </div>
    </div>
</div>
