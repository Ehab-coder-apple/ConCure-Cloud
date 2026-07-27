@extends('layouts.app')

@section('title', __('Aftercare Templates'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="fas fa-file-medical text-primary me-2"></i>{{ __('Aftercare Templates') }}</h1>
            <p class="text-muted mb-0">{{ __('Manage standardized aftercare instructions for the Aesthetic module') }}</p>
        </div>
        <a href="{{ route('aesthetic.aftercare-templates.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>{{ __('Add Template') }}
        </a>
    </div>

    <div class="row mb-4">
        <div class="col-md-4 mb-3"><div class="card"><div class="card-body text-center"><h3 class="text-primary mb-1">{{ $stats['total'] }}</h3><small class="text-muted">{{ __('Total Templates') }}</small></div></div></div>
        <div class="col-md-4 mb-3"><div class="card"><div class="card-body text-center"><h3 class="text-success mb-1">{{ $stats['active'] }}</h3><small class="text-muted">{{ __('Active') }}</small></div></div></div>
        <div class="col-md-4 mb-3"><div class="card"><div class="card-body text-center"><h3 class="text-secondary mb-1">{{ $stats['inactive'] }}</h3><small class="text-muted">{{ __('Inactive') }}</small></div></div></div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-5"><input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="{{ __('Search templates...') }}"></div>
                <div class="col-md-3">
                    <select name="category" class="form-select">
                        <option value="">{{ __('All Categories') }}</option>
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}" {{ request('category') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">{{ __('All Statuses') }}</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-outline-primary flex-fill"><i class="fas fa-search me-1"></i>{{ __('Filter') }}</button>
                    <a href="{{ route('aesthetic.aftercare-templates.index') }}" class="btn btn-outline-secondary">{{ __('Reset') }}</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('Template') }}</th>
                            <th>{{ __('Category') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Updated') }}</th>
                            <th class="text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templates as $template)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $template->name }}</div>
                                    <div class="small text-muted">{{ $template->title }}</div>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $template->category_display }}</span></td>
                                <td><span class="badge bg-{{ $template->is_active ? 'success' : 'secondary' }}">{{ $template->is_active ? __('Active') : __('Inactive') }}</span></td>
                                <td>{{ $template->updated_at->format('M d, Y') }}</td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('aesthetic.aftercare-templates.edit', $template) }}" class="btn btn-outline-primary"><i class="fas fa-edit"></i></a>
                                        <form method="POST" action="{{ route('aesthetic.aftercare-templates.destroy', $template) }}" onsubmit="return confirm('{{ __('Delete this aftercare template?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-5 text-muted"><i class="fas fa-file-medical fa-2x mb-2"></i><p class="mb-0">{{ __('No aftercare templates found.') }}</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($templates->hasPages())
            <div class="card-footer">{{ $templates->links() }}</div>
        @endif
    </div>
</div>
@endsection