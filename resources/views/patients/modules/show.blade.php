@extends('layouts.app')

@section('title', $moduleDefinition['label'] . ' - ' . $patient->full_name)

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h3 class="mb-1"><i class="{{ $moduleDefinition['icon'] }} me-2 text-primary"></i>{{ $moduleDefinition['label'] }}</h3>
            <p class="text-muted mb-0">{{ $moduleDefinition['description'] }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('patients.show', ['patient' => $patient->id]) }}" class="btn btn-outline-secondary">{{ __('Back to Patient Profile') }}</a>
            <form method="POST" action="{{ route('patients.modules.destroy', ['patient' => $patient->id, 'module' => $module]) }}">
                @csrf
                @method('DELETE')
                <button class="btn btn-outline-danger" type="submit">{{ __('Hide Module Tab') }}</button>
            </form>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">{{ __('Module Summary') }}</h5>
                    <div class="row g-3">
                        @foreach($moduleStats as $stat)
                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100 bg-light">
                                    <small class="text-muted d-block">{{ $stat['label'] }}</small>
                                    <strong>{{ $stat['value'] }}</strong>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="mb-3">{{ __('Patient') }}</h5>
                    <div class="mb-2"><small class="text-muted d-block">{{ __('Name') }}</small><strong>{{ $patient->full_name }}</strong></div>
                    <div class="mb-2"><small class="text-muted d-block">{{ __('Patient ID') }}</small><strong>{{ $patient->patient_id }}</strong></div>
                    <div><small class="text-muted d-block">{{ __('Age') }}</small><strong>{{ $patient->age_formatted }}</strong></div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">{{ __('Module Links') }}</h5>
                    @forelse($moduleLinks as $link)
                        <a href="{{ $link['url'] }}" class="btn btn-outline-primary w-100 mb-2">{{ $link['label'] }}</a>
                    @empty
                        <p class="text-muted mb-0">{{ __('This module page is scaffolded and ready for a dedicated implementation.') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection