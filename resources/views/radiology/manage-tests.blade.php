@extends('layouts.fullscreen')

@section('page-title', __('Manage Radiology Tests'))
@section('page-subtitle', __('Create custom tests and manage your radiology test database'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-end align-items-center mb-4">
        <div>
            <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#createTestModal">
                <i class="fas fa-plus me-2"></i> {{ __('Create Custom Test') }}
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Quick Help Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ __('How to Add Tests & Categories') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h6 class="text-primary">
                                <i class="fas fa-plus-circle me-1"></i>
                                {{ __('Add New Test') }}
                            </h6>
                            <p class="small mb-2">
                                1. Click <strong>"Create Custom Test"</strong> button above<br>
                                2. Fill in test name (required)<br>
                                3. Select existing category or create new one<br>
                                4. Add description and preparation instructions<br>
                                5. Click "Create Test"
                            </p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-success">
                                <i class="fas fa-tags me-1"></i>
                                {{ __('Create New Category') }}
                            </h6>
                            <p class="small mb-2">
                                1. In the "New Category" field, type your category name<br>
                                2. Examples: <code>cardiac_imaging</code>, <code>pediatric_radiology</code><br>
                                3. Use underscores instead of spaces<br>
                                4. Category will be created automatically
                            </p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-warning">
                                <i class="fas fa-lightbulb me-1"></i>
                                {{ __('Quick Tips') }}
                            </h6>
                            <p class="small mb-2">
                                • <strong>System tests</strong> cannot be deleted<br>
                                • <strong>Custom tests</strong> can be deleted by your clinic<br>
                                • Tests are immediately available in request forms<br>
                                • Categories help organize your tests
                            </p>
                        </div>
                    </div>

                    <!-- Example Tests Section -->
                    <div class="mt-3">
                        <button class="btn btn-sm btn-outline-info" type="button" data-bs-toggle="collapse" data-bs-target="#exampleTests">
                            <i class="fas fa-eye me-1"></i>
                            {{ __('View Example Tests & Categories') }}
                        </button>

                        <div class="collapse mt-2" id="exampleTests">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-primary">{{ __('Example Custom Tests') }}</h6>
                                    <ul class="list-unstyled small">
                                        <li><strong>Echocardiogram</strong> - Category: <code>cardiac_imaging</code></li>
                                        <li><strong>Pediatric Chest X-ray</strong> - Category: <code>pediatric_radiology</code></li>
                                        <li><strong>Dental Panoramic X-ray</strong> - Category: <code>dental_imaging</code></li>
                                        <li><strong>Sports Injury MRI</strong> - Category: <code>sports_medicine</code></li>
                                        <li><strong>Oncology PET Scan</strong> - Category: <code>oncology_imaging</code></li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-success">{{ __('Example Categories') }}</h6>
                                    <ul class="list-unstyled small">
                                        <li><code>cardiac_imaging</code> - Heart and cardiovascular</li>
                                        <li><code>pediatric_radiology</code> - Children's imaging</li>
                                        <li><code>dental_imaging</code> - Dental and oral</li>
                                        <li><code>sports_medicine</code> - Athletic injuries</li>
                                        <li><code>oncology_imaging</code> - Cancer-related scans</li>
                                        <li><code>emergency_radiology</code> - Emergency imaging</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Test Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $tests->total() }}</h4>
                            <p class="mb-0">{{ __('Total Tests') }}</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-vials fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $tests->where('clinic_id', auth()->user()->clinic_id)->count() }}</h4>
                            <p class="mb-0">{{ __('Custom Tests') }}</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-md fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ count($categories) }}</h4>
                            <p class="mb-0">{{ __('Categories') }}</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-tags fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $tests->where('is_frequent', true)->count() }}</h4>
                            <p class="mb-0">{{ __('Frequent Tests') }}</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-star fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tests Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ __('Radiology Tests') }}</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Code') }}</th>
                            <th>{{ __('Category') }}</th>
                            <th>{{ __('Body Part') }}</th>
                            <th>{{ __('Duration') }}</th>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tests as $test)
                        <tr>
                            <td>
                                <strong>{{ $test->name }}</strong>
                                @if($test->description)
                                <br><small class="text-muted">{{ Str::limit($test->description, 50) }}</small>
                                @endif
                            </td>
                            <td>
                                @if($test->code)
                                <span class="badge bg-secondary">{{ $test->code }}</span>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ ucwords(str_replace('_', ' ', $test->category)) }}</span>
                            </td>
                            <td>
                                @if($test->body_part)
                                {{ ucwords(str_replace('_', ' ', $test->body_part)) }}
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($test->estimated_duration_minutes)
                                {{ $test->estimated_duration_minutes }}min
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($test->clinic_id)
                                <span class="badge bg-success">{{ __('Custom') }}</span>
                                @else
                                <span class="badge bg-info">{{ __('System') }}</span>
                                @endif
                                @if($test->is_frequent)
                                <span class="badge bg-warning">{{ __('Frequent') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($test->clinic_id === auth()->user()->clinic_id)
                                <form method="POST" action="{{ route('recommendations.radiology.tests.delete', $test) }}" 
                                      class="d-inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this test?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @else
                                <span class="text-muted">{{ __('System test') }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-vials fa-3x text-muted mb-3"></i>
                                <p class="text-muted">{{ __('No tests found') }}</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($tests->hasPages())
            <div class="d-flex justify-content-center">
                {{ $tests->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Create Test Modal -->
<div class="modal fade" id="createTestModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Create Custom Radiology Test') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createTestForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">{{ __('Test Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">{{ __('Test Code') }}</label>
                            <input type="text" class="form-control" name="code" placeholder="e.g., CXR">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Category') }} <span class="text-danger">*</span></label>
                            <select class="form-select" name="category" required>
                                <option value="">{{ __('Select existing category') }}</option>
                                @foreach($categories as $category)
                                <option value="{{ $category }}">{{ ucwords(str_replace('_', ' ', $category)) }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">{{ __('Or type a new category name below') }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('New Category') }}</label>
                            <input type="text" class="form-control" name="new_category" placeholder="{{ __('e.g., cardiac_imaging, pediatric_radiology') }}">
                            <div class="form-text">
                                {{ __('Leave empty to use existing category') }}<br>
                                <small class="text-muted">{{ __('Quick add') }}:</small>
                                <div class="mt-1">
                                    <button type="button" class="btn btn-sm btn-outline-secondary me-1" onclick="setCategory('cardiac_imaging')">Cardiac</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary me-1" onclick="setCategory('pediatric_radiology')">Pediatric</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary me-1" onclick="setCategory('dental_imaging')">Dental</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setCategory('sports_medicine')">Sports</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Body Part') }}</label>
                            <select class="form-select" name="body_part">
                                <option value="">{{ __('Select body part') }}</option>
                                <option value="head">{{ __('Head') }}</option>
                                <option value="neck">{{ __('Neck') }}</option>
                                <option value="chest">{{ __('Chest') }}</option>
                                <option value="abdomen">{{ __('Abdomen') }}</option>
                                <option value="pelvis">{{ __('Pelvis') }}</option>
                                <option value="spine">{{ __('Spine') }}</option>
                                <option value="upper_extremity">{{ __('Upper Extremity') }}</option>
                                <option value="lower_extremity">{{ __('Lower Extremity') }}</option>
                                <option value="whole_body">{{ __('Whole Body') }}</option>
                                <option value="other">{{ __('Other') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Estimated Duration (minutes)') }}</label>
                            <input type="number" class="form-control" name="estimated_duration_minutes" min="1" max="480">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Description') }}</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="{{ __('Brief description of the test') }}"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Preparation Instructions') }}</label>
                        <textarea class="form-control" name="preparation_instructions" rows="3" placeholder="{{ __('Patient preparation instructions') }}"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="requires_contrast" id="requires_contrast">
                                <label class="form-check-label" for="requires_contrast">
                                    {{ __('Requires Contrast') }}
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="requires_fasting" id="requires_fasting">
                                <label class="form-check-label" for="requires_fasting">
                                    {{ __('Requires Fasting') }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> {{ __('Create Test') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('createTestForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    // Handle new category
    const newCategory = formData.get('new_category');
    if (newCategory) {
        formData.set('category', newCategory.toLowerCase().replace(/\s+/g, '_'));
    }

    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> {{ __("Creating...") }}';
    submitBtn.disabled = true;

    fetch('{{ route("recommendations.radiology.tests.create-custom") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal and reload page
            const modal = bootstrap.Modal.getInstance(document.getElementById('createTestModal'));
            modal.hide();
            location.reload();
        } else {
            alert(data.message || '{{ __("Failed to create test") }}');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('{{ __("An error occurred while creating the test") }}');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

// Quick category selection
function setCategory(categoryName) {
    document.querySelector('input[name="new_category"]').value = categoryName;
    // Clear the existing category selection
    document.querySelector('select[name="category"]').value = '';
}
</script>
@endpush
