@extends('layouts.app')

@section('title', __('Edit Treatment Session'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-edit text-primary me-2"></i>
                        {{ __('Edit Treatment Session') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Update session details') }}</p>
                </div>
                <a href="{{ route('aesthetic.sessions.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>
                    {{ __('Back to Sessions') }}
                </a>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                {{ __('Session Information') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('aesthetic.sessions.update', $aestheticSession) }}">
                                @csrf
                                @method('PUT')
                                @include('aesthetic.sessions._form')

                                <div class="d-flex justify-content-end gap-2 mt-4">
                                    <a href="{{ route('aesthetic.sessions.index') }}" class="btn btn-outline-secondary">
                                        {{ __('Cancel') }}
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>
                                        {{ __('Update Session') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-lightbulb me-2"></i>
                                {{ __('Tips') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <h6 class="alert-heading">{{ __('Editing a Session') }}</h6>
                                <ul class="mb-0 small">
                                    <li>{{ __('You can change the session date and status') }}</li>
                                    <li>{{ __('Moving a session to another package is possible') }}</li>
                                    <li>{{ __('Images are preserved when editing') }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
