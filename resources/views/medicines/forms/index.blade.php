@extends('layouts.app')

@section('title', __('Manage Medicine Forms'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-layer-group text-primary me-2"></i>
                        {{ __('Manage Medicine Forms') }}
                    </h1>
                    <p class="text-muted mb-0">
                        {{ __('Rename or remove the custom forms your clinic has added. Built-in forms (Tablet, Capsule, etc.) cannot be edited.') }}
                    </p>
                </div>
                <a href="{{ route('medicines.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>
                    {{ __('Back to Inventory') }}
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-body p-0">
                    @if($forms->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-layer-group fa-3x mb-3 opacity-50"></i>
                            <p class="mb-1">{{ __('No custom forms yet.') }}</p>
                            <p class="small mb-0">
                                {{ __('When you add a medicine and pick "Other", the new form name is saved here for reuse.') }}
                            </p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('Label') }}</th>
                                        <th class="text-muted small">{{ __('Slug') }}</th>
                                        <th class="text-center">{{ __('In Use By') }}</th>
                                        <th class="text-end">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($forms as $form)
                                        @php $count = (int) ($usage[$form->key] ?? 0); @endphp
                                        <tr>
                                            <td style="min-width: 280px;">
                                                <form method="POST" action="{{ route('medicines.forms.update', $form) }}" class="d-flex gap-2">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="text" class="form-control form-control-sm"
                                                           name="label" value="{{ $form->label }}"
                                                           maxlength="80" required>
                                                    <button type="submit" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-save"></i>
                                                        <span class="d-none d-md-inline ms-1">{{ __('Save') }}</span>
                                                    </button>
                                                </form>
                                            </td>
                                            <td>
                                                <code class="small text-muted">{{ $form->key }}</code>
                                            </td>
                                            <td class="text-center">
                                                @if($count > 0)
                                                    <span class="badge bg-info">{{ $count }}</span>
                                                @else
                                                    <span class="text-muted small">{{ __('Unused') }}</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <form method="POST" action="{{ route('medicines.forms.destroy', $form) }}"
                                                      class="d-inline"
                                                      onsubmit="return confirm('{{ __('Delete this form?') }}');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="btn btn-sm btn-outline-danger"
                                                            @if($count > 0) disabled title="{{ __('Cannot delete: still in use.') }}" @endif>
                                                        <i class="fas fa-trash"></i>
                                                        <span class="d-none d-md-inline ms-1">{{ __('Delete') }}</span>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <div class="alert alert-light border mt-3 small text-muted mb-0">
                <i class="fas fa-info-circle me-1"></i>
                {{ __('Tip: to add a new form, open Add Medicine, select "Other" in the Form dropdown, and type the new name.') }}
            </div>
        </div>
    </div>
</div>
@endsection
