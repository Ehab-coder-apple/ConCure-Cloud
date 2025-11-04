@extends('layouts.app')

@section('page-title', __('Form Assignment') . ' - ' . $patient->full_name)

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0">
                <i class="fas fa-file-alt text-secondary"></i>
                {{ $assignment->template?->name ?? __('Form') }}
            </h1>
            <div>
                <a href="{{ route('patients.forms.index', $patient) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> {{ __('Back') }}
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <strong>{{ __('Status') }}:</strong><br>
                            @php
                                $badge = match($assignment->status){
                                  'completed' => 'success',
                                  'in_progress' => 'info',
                                  default => 'secondary'
                                };
                            @endphp
                            <span class="badge bg-{{ $badge }}">{{ __(Str::title(str_replace('_',' ', $assignment->status))) }}</span>
                        </div>
                        <div class="col-md-4 mb-3">
                            <strong>{{ __('Assigned At') }}:</strong><br>
                            <span class="text-muted">{{ $assignment->assigned_at?->format('Y-m-d H:i') ?? '-' }}</span>
                        </div>
                        <div class="col-md-4 mb-3">
                            <strong>{{ __('Completed At') }}:</strong><br>
                            <span class="text-muted">{{ $assignment->completed_at?->format('Y-m-d H:i') ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <strong>{{ __('Assigned By') }}:</strong><br>
                            <span class="text-muted">{{ $assignment->assignedBy?->name ?? '-' }}</span>
                        </div>
                        <div class="col-md-4 mb-3">
                            <strong>{{ __('Filled By') }}:</strong><br>
                            <span class="text-muted">{{ $assignment->filledBy?->name ?? '-' }}</span>
                        </div>
                        <div class="col-md-4 mb-3">
                            <strong>{{ __('Template File') }}:</strong><br>
                            @if($assignment->template)
                                <a href="{{ route('forms.templates.download', $assignment->template) }}" class="text-decoration-none">
                                    <i class="fas fa-download me-1"></i> {{ __('Download Original') }}
                                </a>
                            @else
                                <span class="text-muted">{{ __('N/A') }}</span>
                            @endif
                        </div>
                    </div>
                    @if($assignment->notes)
                    <div class="row">
                        <div class="col-12">
                            <strong>{{ __('Notes') }}:</strong>
                            <p class="mb-0">{{ $assignment->notes }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle me-1"></i>
                {{ __('Form filling will be implemented in Step 6. For now, you can manage assignments and download templates.') }}
            </div>
        </div>
    </div>
</div>
@endsection

