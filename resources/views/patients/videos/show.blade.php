@extends('layouts.app')

@section('title', $video->title ?? $video->filename)

@section('styles')
<style>
    .video-container {
        background: #000;
        border-radius: 0.5rem;
        overflow: hidden;
    }
    .video-container video {
        width: 100%;
        max-height: 80vh;
        display: block;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-video text-primary me-2"></i>
                        {{ $video->title ?? $video->filename }}
                    </h1>
                    <p class="text-muted mb-0">
                        {{ __('Patient') }}: <strong>{{ $patient->full_name }}</strong> ({{ $patient->patient_id }})
                    </p>
                </div>
                <div>
                    <a href="{{ route('patients.show', $patient) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to Patient') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Video Player -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="video-container">
                        <video controls autoplay preload="auto">
                            <source src="{{ $video->url }}" type="{{ $video->mime }}">
                            {{ __('Your browser does not support the video tag.') }}
                        </video>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Video Details -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ __('Video Details') }}
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th style="width: 150px;">{{ __('Filename') }}</th>
                            <td>{{ $video->filename }}</td>
                        </tr>
                        @if($video->title)
                        <tr>
                            <th>{{ __('Title') }}</th>
                            <td>{{ $video->title }}</td>
                        </tr>
                        @endif
                        <tr>
                            <th>{{ __('Size') }}</th>
                            <td>{{ $video->file_size_human }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Type') }}</th>
                            <td>{{ $video->mime }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Uploaded') }}</th>
                            <td>{{ $video->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                        @if(is_array($video->condition_tags) && count($video->condition_tags))
                        <tr>
                            <th>{{ __('Tags') }}</th>
                            <td>
                                @foreach($video->condition_tags as $t)
                                    <span class="badge bg-light text-dark border me-1">#{{ $t }}</span>
                                @endforeach
                            </td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        @php $canEditPatients = auth()->check() && (auth()->user()->canManagePatients() || auth()->user()->hasPermission('patients_edit')); @endphp
        @if($canEditPatients)
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-cog me-2"></i>
                        {{ __('Actions') }}
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('patients.videos.update', [$patient, $video]) }}" method="POST" class="mb-3">
                        @csrf
                        @method('PATCH')
                        <div class="mb-2">
                            <label class="form-label small">{{ __('Title') }}</label>
                            <input type="text" name="title" class="form-control form-control-sm" value="{{ $video->title }}">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">{{ __('Tags') }}</label>
                            <input type="text" name="condition_tags" class="form-control form-control-sm" value="{{ is_array($video->condition_tags) ? implode(', ', $video->condition_tags) : '' }}" placeholder="{{ __('Comma-separated') }}">
                        </div>
                        <button class="btn btn-sm btn-primary w-100" type="submit">
                            <i class="fas fa-save me-1"></i>{{ __('Save Changes') }}
                        </button>
                    </form>
                    <form action="{{ route('patients.videos.destroy', [$patient, $video]) }}" method="POST" onsubmit="return confirm('{{ __('Delete this video?') }}')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger w-100" type="submit">
                            <i class="fas fa-trash me-1"></i>{{ __('Delete Video') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
