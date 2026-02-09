@extends('layouts.app')

@section('title', __('Form Templates'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-file-alt text-primary me-2"></i>
                        {{ __('Form Templates') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Upload and manage your clinic\'s form templates (Word/Excel)') }}</p>
                </div>
                @if(auth()->user()->canManageFormTemplates())
                <div class="d-flex gap-2">
                    <a href="{{ route('forms.templates.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>
                        {{ __('Upload Template') }}
                    </a>
                </div>
                @endif
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('forms.templates.index') }}">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('Search') }}</label>
                                <input type="text" class="form-control" name="search" value="{{ request('search') }}"
                                       placeholder="{{ __('Search by name, description, or category...') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ __('Status') }}</label>
                                <select class="form-select" name="status">
                                    <option value="">{{ __('All Status') }}</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
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

            <!-- Templates Table -->
            <div class="card">
                <div class="card-body">
                    @if($templates->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('Template') }}</th>
                                        <th>{{ __('Category') }}</th>
                                        <th>{{ __('File') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Created') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($templates as $template)
                                    <tr>
                                        <td>
                                            <strong>{{ $template->name }}</strong>
                                            @if($template->description)
                                                <small class="text-muted d-block">{{ Str::limit($template->description, 80) }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $template->category ?: '-' }}</td>
                                        <td>
                                            <span class="badge bg-secondary text-uppercase">{{ $template->extension }}</span>
                                            <small class="text-muted d-block">{{ number_format(($template->file_size ?? 0) / 1024, 1) }} KB</small>
                                        </td>
                                        <td>
                                            @if($template->is_active)
                                                <span class="badge bg-success">{{ __('Active') }}</span>
                                            @else
                                                <span class="badge bg-danger">{{ __('Inactive') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $template->created_at->format('M d, Y') }}<br>
                                                {{ __('by') }} {{ $template->createdBy->name ?? 'System' }}
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('forms.templates.download', $template) }}" class="btn btn-sm btn-outline-success" title="{{ __('Download') }}">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                @if(auth()->user()->canManageFormTemplates())
                                                <a href="{{ route('forms.templates.edit', $template) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('Edit') }}">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" action="{{ route('forms.templates.destroy', $template) }}" class="d-inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this template?') }}');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('Delete') }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center mt-4">
                            {{ $templates->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('No templates found') }}</h5>
                            <p class="text-muted">{{ __('Start by uploading your first form template.') }}</p>
                            @if(auth()->user()->canManageFormTemplates())
                            <a href="{{ route('forms.templates.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>
                                {{ __('Upload Template') }}
                            </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

