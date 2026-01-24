@extends('layouts.app')

@section('title', __('Dental Chart') . ' - ' . $patient->full_name)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-tooth me-2 text-primary"></i>
                        {{ __('Dental Chart') }}
                    </h1>
                    <p class="text-muted mb-0">
                        {{ __('Patient') }}: <strong>{{ $patient->full_name }}</strong> | 
                        {{ __('Date') }}: {{ $dentalChart->created_at->format('M d, Y') }}
                    </p>
                </div>
                <div>
                    <a href="{{ url("/dental/patients/{$patient->id}/charts") }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to Charts') }}
                    </a>
                    @if(in_array(auth()->user()->role, ['doctor', 'assistant', 'admin', 'program_owner']))
                        <a href="{{ url("/dental/patients/{$patient->id}/charts/{$dentalChart->id}/edit") }}" class="btn btn-primary">
                            <i class="fas fa-edit me-1"></i>
                            {{ __('Edit Chart') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Type Badge -->
    <div class="row mb-3">
        <div class="col-12">
            <span class="badge bg-{{ $dentalChart->chart_type === 'adult' ? 'primary' : 'info' }} fs-6">
                <i class="fas fa-info-circle me-1"></i>
                {{ ucfirst($dentalChart->chart_type) }} {{ __('Dentition') }}
            </span>
        </div>
    </div>

    <!-- Dental Chart Visualization -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-teeth me-2"></i>
                        {{ __('Dental Chart Visualization') }}
                    </h6>
                </div>
                <div class="card-body">
                    <!-- Legend -->
                    <div class="mb-4 p-3 bg-light rounded">
                        <h6 class="mb-3">{{ __('Condition Legend') }}</h6>
                        <div class="row">
                            @foreach(\App\Models\DentalToothRecord::CONDITIONS as $key => $condition)
                                <div class="col-md-3 col-sm-4 col-6 mb-2">
                                    <span class="badge" style="background-color: {{ $condition['color'] }}; color: {{ in_array($key, ['healthy', 'filling', 'crown', 'implant', 'bridge', 'periodontal', 'other']) ? '#000' : '#fff' }};">
                                        <i class="{{ $condition['icon'] }} me-1"></i>
                                        {{ $condition['name'] }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Simple Dental Chart Display -->
                    <div class="dental-chart-container">
                        @php
                            $toothNumbers = $dentalChart->tooth_numbers;
                            $toothRecords = $dentalChart->toothRecords->keyBy('tooth_number');
                        @endphp

                        <!-- Upper Jaw -->
                        <div class="mb-4">
                            <h6 class="text-center mb-3">{{ __('Upper Jaw') }}</h6>
                            <div class="row">
                                <!-- Upper Right -->
                                <div class="col-6">
                                    <p class="text-center text-muted small">{{ __('Right') }}</p>
                                    <div class="d-flex justify-content-center flex-wrap">
                                        @foreach($toothNumbers['upper_right'] as $toothNum)
                                            @php
                                                $record = $toothRecords->get($toothNum);
                                                $color = $record ? $record->primary_condition_color : '#FFFFFF';
                                                $condition = $record ? $record->primary_condition : 'healthy';
                                            @endphp
                                            <div class="tooth-box m-1 text-center" style="background-color: {{ $color }}; border: 2px solid #333; border-radius: 8px; padding: 10px; min-width: 50px;">
                                                <div class="tooth-number" style="font-weight: bold; font-size: 14px; color: {{ in_array($condition, ['healthy', 'filling', 'crown', 'implant', 'bridge', 'periodontal', 'other']) ? '#000' : '#fff' }};">
                                                    {{ $toothNum }}
                                                </div>
                                                @if($record)
                                                    <div class="tooth-condition" style="font-size: 10px; color: {{ in_array($condition, ['healthy', 'filling', 'crown', 'implant', 'bridge', 'periodontal', 'other']) ? '#000' : '#fff' }};">
                                                        {{ \App\Models\DentalToothRecord::CONDITIONS[$record->primary_condition]['name'] ?? '' }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Upper Left -->
                                <div class="col-6">
                                    <p class="text-center text-muted small">{{ __('Left') }}</p>
                                    <div class="d-flex justify-content-center flex-wrap">
                                        @foreach($toothNumbers['upper_left'] as $toothNum)
                                            @php
                                                $record = $toothRecords->get($toothNum);
                                                $color = $record ? $record->primary_condition_color : '#FFFFFF';
                                                $condition = $record ? $record->primary_condition : 'healthy';
                                            @endphp
                                            <div class="tooth-box m-1 text-center" style="background-color: {{ $color }}; border: 2px solid #333; border-radius: 8px; padding: 10px; min-width: 50px;">
                                                <div class="tooth-number" style="font-weight: bold; font-size: 14px; color: {{ in_array($condition, ['healthy', 'filling', 'crown', 'implant', 'bridge', 'periodontal', 'other']) ? '#000' : '#fff' }};">
                                                    {{ $toothNum }}
                                                </div>
                                                @if($record)
                                                    <div class="tooth-condition" style="font-size: 10px; color: {{ in_array($condition, ['healthy', 'filling', 'crown', 'implant', 'bridge', 'periodontal', 'other']) ? '#000' : '#fff' }};">
                                                        {{ \App\Models\DentalToothRecord::CONDITIONS[$record->primary_condition]['name'] ?? '' }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Lower Jaw -->
                        <div class="mb-4">
                            <h6 class="text-center mb-3">{{ __('Lower Jaw') }}</h6>
                            <div class="row">
                                <!-- Lower Right -->
                                <div class="col-6">
                                    <p class="text-center text-muted small">{{ __('Right') }}</p>
                                    <div class="d-flex justify-content-center flex-wrap">
                                        @foreach($toothNumbers['lower_right'] as $toothNum)
                                            @php
                                                $record = $toothRecords->get($toothNum);
                                                $color = $record ? $record->primary_condition_color : '#FFFFFF';
                                                $condition = $record ? $record->primary_condition : 'healthy';
                                            @endphp
                                            <div class="tooth-box m-1 text-center" style="background-color: {{ $color }}; border: 2px solid #333; border-radius: 8px; padding: 10px; min-width: 50px;">
                                                <div class="tooth-number" style="font-weight: bold; font-size: 14px; color: {{ in_array($condition, ['healthy', 'filling', 'crown', 'implant', 'bridge', 'periodontal', 'other']) ? '#000' : '#fff' }};">
                                                    {{ $toothNum }}
                                                </div>
                                                @if($record)
                                                    <div class="tooth-condition" style="font-size: 10px; color: {{ in_array($condition, ['healthy', 'filling', 'crown', 'implant', 'bridge', 'periodontal', 'other']) ? '#000' : '#fff' }};">
                                                        {{ \App\Models\DentalToothRecord::CONDITIONS[$record->primary_condition]['name'] ?? '' }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Lower Left -->
                                <div class="col-6">
                                    <p class="text-center text-muted small">{{ __('Left') }}</p>
                                    <div class="d-flex justify-content-center flex-wrap">
                                        @foreach($toothNumbers['lower_left'] as $toothNum)
                                            @php
                                                $record = $toothRecords->get($toothNum);
                                                $color = $record ? $record->primary_condition_color : '#FFFFFF';
                                                $condition = $record ? $record->primary_condition : 'healthy';
                                            @endphp
                                            <div class="tooth-box m-1 text-center" style="background-color: {{ $color }}; border: 2px solid #333; border-radius: 8px; padding: 10px; min-width: 50px;">
                                                <div class="tooth-number" style="font-weight: bold; font-size: 14px; color: {{ in_array($condition, ['healthy', 'filling', 'crown', 'implant', 'bridge', 'periodontal', 'other']) ? '#000' : '#fff' }};">
                                                    {{ $toothNum }}
                                                </div>
                                                @if($record)
                                                    <div class="tooth-condition" style="font-size: 10px; color: {{ in_array($condition, ['healthy', 'filling', 'crown', 'implant', 'bridge', 'periodontal', 'other']) ? '#000' : '#fff' }};">
                                                        {{ \App\Models\DentalToothRecord::CONDITIONS[$record->primary_condition]['name'] ?? '' }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tooth Records Details -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        {{ __('Tooth Records Details') }}
                    </h6>
                </div>
                <div class="card-body">
                    @if($dentalChart->toothRecords->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('Tooth #') }}</th>
                                        <th>{{ __('Primary Condition') }}</th>
                                        <th>{{ __('All Conditions') }}</th>
                                        <th>{{ __('Surfaces') }}</th>
                                        <th>{{ __('Severity') }}</th>
                                        <th>{{ __('Notes') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dentalChart->toothRecords->sortBy('tooth_number') as $record)
                                        <tr>
                                            <td><strong>{{ $record->tooth_number }}</strong></td>
                                            <td>
                                                <span class="badge" style="background-color: {{ $record->primary_condition_color }}; color: {{ in_array($record->primary_condition, ['healthy', 'filling', 'crown', 'implant', 'bridge', 'periodontal', 'other']) ? '#000' : '#fff' }};">
                                                    {{ \App\Models\DentalToothRecord::CONDITIONS[$record->primary_condition]['name'] ?? $record->primary_condition }}
                                                </span>
                                            </td>
                                            <td>
                                                @foreach($record->conditions ?? [] as $cond)
                                                    <span class="badge bg-secondary me-1">{{ \App\Models\DentalToothRecord::CONDITIONS[$cond]['name'] ?? $cond }}</span>
                                                @endforeach
                                            </td>
                                            <td>{{ $record->surfaces_display }}</td>
                                            <td>
                                                @if($record->severity)
                                                    <span class="badge bg-{{ $record->severity === 'mild' ? 'success' : ($record->severity === 'moderate' ? 'warning' : 'danger') }}">
                                                        {{ ucfirst($record->severity) }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td><small>{{ $record->notes ?? '-' }}</small></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-info-circle fa-2x mb-2"></i>
                            <p class="mb-0">{{ __('No tooth records in this chart') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- General Notes -->
    @if($dentalChart->general_notes)
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-sticky-note me-2"></i>
                            {{ __('General Notes') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $dentalChart->general_notes }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

