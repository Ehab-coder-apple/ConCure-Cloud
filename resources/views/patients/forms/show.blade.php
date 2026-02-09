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
            <div class="d-flex gap-2">
                @if(Auth::user()->canFillForms() && $assignment->status !== 'completed')
                <a href="{{ route('patients.forms.fill', [$patient, $assignment]) }}" class="btn btn-primary">
                    <i class="fas fa-pen me-1"></i> {{ __('Fill / Continue') }}
                </a>
                @endif
                @if($assignment->status === 'completed')
                    @if($assignment->hasSnapshotPdf())
                    <a href="{{ route('patients.forms.pdf-snapshot', [$patient, $assignment]) }}" target="_blank" class="btn btn-success">
                        <i class="fas fa-file-pdf me-1"></i> {{ __('Open Stored PDF') }}
                    </a>
                    @else
                    <a href="{{ route('patients.forms.pdf', [$patient, $assignment]) }}?open=1" target="_blank" class="btn btn-success">
                        <i class="fas fa-file-pdf me-1"></i> {{ __('Open PDF') }}
                    </a>
                    @endif
                @endif
                @if($assignment->hasAttachment())
                <a href="{{ route('patients.forms.attachment', [$patient, $assignment]) }}" class="btn btn-outline-primary">
                    <i class="fas fa-paperclip me-1"></i> {{ __('Download Attachment') }}
                </a>
                @endif
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
                        <div class="col-md-4 mb-3">
                            <strong>{{ __('Attachment') }}:</strong><br>
                            @if($assignment->hasAttachment())
                                <a href="{{ route('patients.forms.attachment', [$patient, $assignment]) }}" class="text-decoration-none">
                                    <i class="fas fa-paperclip me-1"></i> {{ $assignment->attachment_name ?? __('Download Attachment') }}
                                </a>
                            @else
                                <span class="text-muted">{{ __('None') }}</span>
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
            @php($content = data_get($assignment->form_data, 'content'))
            @if(!empty($content))
            <div class="card">
                <div class="card-header fw-bold">
                    <i class="fas fa-align-left me-1"></i> {{ __('Form Data') }}
                </div>
                <div class="card-body">
                    <div class="rich-content mb-0">{!! $content !!}</div>
                </div>
            </div>
            @else
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-1"></i>
                {{ __('No data has been entered yet. Click Fill to start.') }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.rich-content table { width: 100%; border-collapse: collapse !important; }
.rich-content table, .rich-content th, .rich-content td { border: 1px solid #ced4da !important; }
.rich-content th, .rich-content td { padding: 6px 8px; }
.rich-content thead th { background: #f1f3f5; }
</style>
@endpush


