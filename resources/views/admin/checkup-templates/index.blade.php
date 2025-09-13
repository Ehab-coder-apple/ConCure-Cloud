@extends('layouts.app')

@section('page-title', __('Custom Checkup Templates'))

@section('content')
<div class="container">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-clipboard-list text-primary"></i>
                        {{ __('Custom Checkup Templates') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Create and manage specialized checkup forms for different medical conditions') }}</p>
                </div>
                <div>
                    <a href="{{ route('admin.checkup-templates.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>
                        {{ __('Create Template') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">{{ __('Total Templates') }}</h6>
                            <h3 class="mb-0">{{ $templates->count() }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clipboard-list fa-2x opacity-75"></i>
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
                            <h6 class="card-title">{{ __('Active Templates') }}</h6>
                            <h3 class="mb-0">{{ $templates->where('is_active', true)->count() }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x opacity-75"></i>
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
                            <h6 class="card-title">{{ __('Specialties') }}</h6>
                            <h3 class="mb-0">{{ $specialties->count() }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-md fa-2x opacity-75"></i>
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
                            <h6 class="card-title">{{ __('Conditions') }}</h6>
                            <h3 class="mb-0">{{ $conditions->count() }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-heartbeat fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Guide -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info">
                <h6 class="alert-heading">
                    <i class="fas fa-info-circle me-2"></i>
                    {{ __('How to Use Custom Checkup Templates') }}
                </h6>
                <p class="mb-2">{{ __('Custom checkup templates allow you to create specialized forms for different medical conditions:') }}</p>
                <ul class="mb-2">
                    <li><strong>{{ __('Pre-Surgery Assessment') }}:</strong> {{ __('Comprehensive pre-operative evaluation forms') }}</li>
                    <li><strong>{{ __('Diabetes Follow-up') }}:</strong> {{ __('Specialized diabetes management tracking') }}</li>
                    <li><strong>{{ __('Cardiac Assessment') }}:</strong> {{ __('Heart condition monitoring forms') }}</li>
                    <li><strong>{{ __('Mental Health') }}:</strong> {{ __('Psychological evaluation and screening') }}</li>
                </ul>
                <div class="row mt-3">
                    <div class="col-md-4">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="createFromTemplate('pre_surgery')">
                            <i class="fas fa-scalpel me-1"></i>
                            {{ __('Pre-Surgery Template') }}
                        </button>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-outline-success btn-sm" onclick="createFromTemplate('diabetes')">
                            <i class="fas fa-tint me-1"></i>
                            {{ __('Diabetes Template') }}
                        </button>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="createFromTemplate('cardiac')">
                            <i class="fas fa-heartbeat me-1"></i>
                            {{ __('Cardiac Template') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Templates List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        {{ __('Checkup Templates') }}
                    </h5>
                </div>
                <div class="card-body">
                    @if($templates->count() > 0)
                        <!-- Bulk Actions -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                    <label class="form-check-label" for="selectAll">
                                        {{ __('Select All') }}
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6 text-end">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-outline-success btn-sm" onclick="bulkAction('activate')" id="bulkActivateBtn" disabled>
                                        <i class="fas fa-check me-1"></i>
                                        {{ __('Activate Selected') }}
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="bulkAction('deactivate')" id="bulkDeactivateBtn" disabled>
                                        <i class="fas fa-times me-1"></i>
                                        {{ __('Deactivate Selected') }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="40">
                                            <input type="checkbox" id="selectAllHeader" onchange="toggleSelectAll()">
                                        </th>
                                        <th>{{ __('Template Name') }}</th>
                                        <th>{{ __('Medical Condition') }}</th>
                                        <th>{{ __('Specialty') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Fields') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Usage') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($templates as $template)
                                    <tr class="{{ !$template->is_active ? 'table-secondary' : '' }}">
                                        <td>
                                            <input type="checkbox" class="form-check-input template-checkbox" value="{{ $template->id }}" onchange="updateBulkButtons()">
                                        </td>
                                        <td>
                                            <strong>{{ $template->name }}</strong>
                                            @if($template->is_default)
                                                <span class="badge bg-primary ms-1">{{ __('Default') }}</span>
                                            @endif
                                            @if($template->description)
                                                <br><small class="text-muted">{{ Str::limit($template->description, 60) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $template->medical_condition ?: '-' }}
                                        </td>
                                        <td>
                                            {{ $template->specialty ?: '-' }}
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                {{ $checkupTypes[$template->checkup_type] ?? $template->checkup_type }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                {{ $template->fields_count }} {{ __('fields') }}
                                            </span>
                                            <br><small class="text-muted">{{ $template->sections_count }} {{ __('sections') }}</small>
                                        </td>
                                        <td>
                                            <span class="badge {{ $template->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $template->is_active ? __('Active') : __('Inactive') }}
                                            </span>
                                        </td>
                                        <td>
                                            @php $stats = $template->usage_stats; @endphp
                                            <small class="text-muted">
                                                {{ $stats['total_assignments'] }} {{ __('assignments') }}<br>
                                                {{ $stats['total_checkups'] }} {{ __('checkups') }}
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('admin.checkup-templates.show', $template) }}" 
                                                   class="btn btn-outline-info" title="{{ __('View') }}">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.checkup-templates.edit', $template) }}" 
                                                   class="btn btn-outline-primary" title="{{ __('Edit') }}">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.checkup-templates.toggle-status', $template) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-outline-{{ $template->is_active ? 'warning' : 'success' }}" 
                                                            title="{{ $template->is_active ? __('Deactivate') : __('Activate') }}">
                                                        <i class="fas fa-{{ $template->is_active ? 'pause' : 'play' }}"></i>
                                                    </button>
                                                </form>
                                                <button type="button" class="btn btn-outline-secondary" 
                                                        onclick="cloneTemplate({{ $template->id }}, '{{ $template->name }}')" 
                                                        title="{{ __('Clone') }}">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger" 
                                                        onclick="confirmDelete({{ $template->id }}, '{{ $template->name }}')" 
                                                        title="{{ __('Delete') }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('No Checkup Templates Created') }}</h5>
                            <p class="text-muted">{{ __('Create your first custom checkup template to get started.') }}</p>
                            <a href="{{ route('admin.checkup-templates.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>
                                {{ __('Create First Template') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Clone Template Modal -->
<div class="modal fade" id="cloneModal" tabindex="-1" aria-labelledby="cloneModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cloneModalLabel">
                    <i class="fas fa-copy me-2"></i>
                    {{ __('Clone Template') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cloneForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="clone_name" class="form-label">{{ __('New Template Name') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="clone_name" name="name" required>
                    </div>
                    <div class="alert alert-info">
                        <small>{{ __('This will create a copy of the template that you can customize.') }}</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-copy me-1"></i>
                        {{ __('Clone Template') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function createFromTemplate(templateType) {
    window.location.href = '{{ route("admin.checkup-templates.create") }}?template=' + templateType;
}

function cloneTemplate(templateId, templateName) {
    document.getElementById('clone_name').value = templateName + ' (Copy)';
    document.getElementById('cloneForm').action = `/admin/checkup-templates/${templateId}/clone`;
    
    const modal = new bootstrap.Modal(document.getElementById('cloneModal'));
    modal.show();
}

function confirmDelete(templateId, templateName) {
    if (confirm(`Are you sure you want to delete the template "${templateName}"? This action cannot be undone.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/checkup-templates/${templateId}`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const selectAllHeader = document.getElementById('selectAllHeader');
    const checkboxes = document.querySelectorAll('.template-checkbox');
    
    selectAll.checked = selectAllHeader.checked;
    selectAllHeader.checked = selectAll.checked;
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateBulkButtons();
}

function updateBulkButtons() {
    const checkboxes = document.querySelectorAll('.template-checkbox:checked');
    const bulkActivateBtn = document.getElementById('bulkActivateBtn');
    const bulkDeactivateBtn = document.getElementById('bulkDeactivateBtn');
    
    const hasSelected = checkboxes.length > 0;
    bulkActivateBtn.disabled = !hasSelected;
    bulkDeactivateBtn.disabled = !hasSelected;
}

function bulkAction(action) {
    const checkboxes = document.querySelectorAll('.template-checkbox:checked');
    const templateIds = Array.from(checkboxes).map(cb => cb.value);
    
    if (templateIds.length === 0) {
        alert('{{ __("Please select at least one template.") }}');
        return;
    }
    
    const actionText = action === 'activate' ? '{{ __("activate") }}' : '{{ __("deactivate") }}';
    const confirmMessage = `{{ __("Are you sure you want to") }} ${actionText} ${templateIds.length} {{ __("template(s)?") }}`;
    
    if (confirm(confirmMessage)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.checkup-templates.index") }}';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'bulk_action';
        actionInput.value = action;
        form.appendChild(actionInput);
        
        templateIds.forEach(id => {
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'template_ids[]';
            idInput.value = id;
            form.appendChild(idInput);
        });
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection
