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
                                                $textColor = in_array($condition, ['healthy', 'filling', 'crown', 'implant', 'bridge', 'periodontal', 'other']) ? '#000' : '#fff';
                                                // Determine tooth type based on position (incisors, canines, premolars, molars)
                                                $toothType = in_array($toothNum, ['11', '12', '21', '22', '31', '32', '41', '42']) ? 'incisor' :
                                                            (in_array($toothNum, ['13', '23', '33', '43']) ? 'canine' :
                                                            (in_array($toothNum, ['14', '15', '24', '25', '34', '35', '44', '45']) ? 'premolar' : 'molar'));
                                            @endphp
                                            <div class="tooth-container m-1 text-center" style="position: relative; width: 60px;">
                                                <svg viewBox="0 0 60 80" width="60" height="80" style="display: block;">
                                                    @if($toothType === 'incisor')
                                                        <!-- Incisor shape (flat, chisel-like) -->
                                                        <path d="M 20 10 L 40 10 L 42 25 L 40 60 Q 30 75 30 75 Q 30 75 20 60 L 18 25 Z"
                                                              fill="{{ $color }}" stroke="#333" stroke-width="2"/>
                                                    @elseif($toothType === 'canine')
                                                        <!-- Canine shape (pointed) -->
                                                        <path d="M 25 5 L 35 5 L 38 20 L 40 60 Q 30 75 30 75 Q 30 75 20 60 L 22 20 Z"
                                                              fill="{{ $color }}" stroke="#333" stroke-width="2"/>
                                                    @elseif($toothType === 'premolar')
                                                        <!-- Premolar shape (two cusps) -->
                                                        <path d="M 18 15 Q 20 8 25 10 Q 30 5 35 10 Q 40 8 42 15 L 42 60 Q 30 75 30 75 Q 30 75 18 60 Z"
                                                              fill="{{ $color }}" stroke="#333" stroke-width="2"/>
                                                    @else
                                                        <!-- Molar shape (multiple cusps, wider) -->
                                                        <path d="M 15 15 Q 17 8 22 10 Q 27 5 30 8 Q 33 5 38 10 Q 43 8 45 15 L 45 60 Q 30 75 30 75 Q 30 75 15 60 Z"
                                                              fill="{{ $color }}" stroke="#333" stroke-width="2"/>
                                                    @endif
                                                    <!-- Tooth number -->
                                                    <text x="30" y="45" text-anchor="middle" font-size="14" font-weight="bold" fill="{{ $textColor }}">
                                                        {{ $toothNum }}
                                                    </text>
                                                </svg>
                                                @if($record)
                                                    <div class="tooth-condition" style="font-size: 9px; margin-top: 2px; color: #666;">
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
                                                $textColor = in_array($condition, ['healthy', 'filling', 'crown', 'implant', 'bridge', 'periodontal', 'other']) ? '#000' : '#fff';
                                                // Determine tooth type based on position
                                                $toothType = in_array($toothNum, ['11', '12', '21', '22', '31', '32', '41', '42']) ? 'incisor' :
                                                            (in_array($toothNum, ['13', '23', '33', '43']) ? 'canine' :
                                                            (in_array($toothNum, ['14', '15', '24', '25', '34', '35', '44', '45']) ? 'premolar' : 'molar'));
                                            @endphp
                                            <div class="tooth-container m-1 text-center" style="position: relative; width: 60px;">
                                                <svg viewBox="0 0 60 80" width="60" height="80" style="display: block;">
                                                    @if($toothType === 'incisor')
                                                        <!-- Incisor shape -->
                                                        <path d="M 20 10 L 40 10 L 42 25 L 40 60 Q 30 75 30 75 Q 30 75 20 60 L 18 25 Z"
                                                              fill="{{ $color }}" stroke="#333" stroke-width="2"/>
                                                    @elseif($toothType === 'canine')
                                                        <!-- Canine shape -->
                                                        <path d="M 25 5 L 35 5 L 38 20 L 40 60 Q 30 75 30 75 Q 30 75 20 60 L 22 20 Z"
                                                              fill="{{ $color }}" stroke="#333" stroke-width="2"/>
                                                    @elseif($toothType === 'premolar')
                                                        <!-- Premolar shape -->
                                                        <path d="M 18 15 Q 20 8 25 10 Q 30 5 35 10 Q 40 8 42 15 L 42 60 Q 30 75 30 75 Q 30 75 18 60 Z"
                                                              fill="{{ $color }}" stroke="#333" stroke-width="2"/>
                                                    @else
                                                        <!-- Molar shape -->
                                                        <path d="M 15 15 Q 17 8 22 10 Q 27 5 30 8 Q 33 5 38 10 Q 43 8 45 15 L 45 60 Q 30 75 30 75 Q 30 75 15 60 Z"
                                                              fill="{{ $color }}" stroke="#333" stroke-width="2"/>
                                                    @endif
                                                    <!-- Tooth number -->
                                                    <text x="30" y="45" text-anchor="middle" font-size="14" font-weight="bold" fill="{{ $textColor }}">
                                                        {{ $toothNum }}
                                                    </text>
                                                </svg>
                                                @if($record)
                                                    <div class="tooth-condition" style="font-size: 9px; margin-top: 2px; color: #666;">
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
                                                $textColor = in_array($condition, ['healthy', 'filling', 'crown', 'implant', 'bridge', 'periodontal', 'other']) ? '#000' : '#fff';
                                                // Determine tooth type
                                                $toothType = in_array($toothNum, ['11', '12', '21', '22', '31', '32', '41', '42']) ? 'incisor' :
                                                            (in_array($toothNum, ['13', '23', '33', '43']) ? 'canine' :
                                                            (in_array($toothNum, ['14', '15', '24', '25', '34', '35', '44', '45']) ? 'premolar' : 'molar'));
                                            @endphp
                                            <div class="tooth-container m-1 text-center" style="position: relative; width: 60px;">
                                                <svg viewBox="0 0 60 80" width="60" height="80" style="display: block; transform: scaleY(-1);">
                                                    @if($toothType === 'incisor')
                                                        <!-- Incisor shape (flipped for lower jaw) -->
                                                        <path d="M 20 10 L 40 10 L 42 25 L 40 60 Q 30 75 30 75 Q 30 75 20 60 L 18 25 Z"
                                                              fill="{{ $color }}" stroke="#333" stroke-width="2"/>
                                                    @elseif($toothType === 'canine')
                                                        <!-- Canine shape -->
                                                        <path d="M 25 5 L 35 5 L 38 20 L 40 60 Q 30 75 30 75 Q 30 75 20 60 L 22 20 Z"
                                                              fill="{{ $color }}" stroke="#333" stroke-width="2"/>
                                                    @elseif($toothType === 'premolar')
                                                        <!-- Premolar shape -->
                                                        <path d="M 18 15 Q 20 8 25 10 Q 30 5 35 10 Q 40 8 42 15 L 42 60 Q 30 75 30 75 Q 30 75 18 60 Z"
                                                              fill="{{ $color }}" stroke="#333" stroke-width="2"/>
                                                    @else
                                                        <!-- Molar shape -->
                                                        <path d="M 15 15 Q 17 8 22 10 Q 27 5 30 8 Q 33 5 38 10 Q 43 8 45 15 L 45 60 Q 30 75 30 75 Q 30 75 15 60 Z"
                                                              fill="{{ $color }}" stroke="#333" stroke-width="2"/>
                                                    @endif
                                                    <!-- Tooth number (flipped back) -->
                                                    <text x="30" y="45" text-anchor="middle" font-size="14" font-weight="bold" fill="{{ $textColor }}" transform="scale(1, -1) translate(0, -90)">
                                                        {{ $toothNum }}
                                                    </text>
                                                </svg>
                                                @if($record)
                                                    <div class="tooth-condition" style="font-size: 9px; margin-top: 2px; color: #666;">
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
                                                $textColor = in_array($condition, ['healthy', 'filling', 'crown', 'implant', 'bridge', 'periodontal', 'other']) ? '#000' : '#fff';
                                                // Determine tooth type
                                                $toothType = in_array($toothNum, ['11', '12', '21', '22', '31', '32', '41', '42']) ? 'incisor' :
                                                            (in_array($toothNum, ['13', '23', '33', '43']) ? 'canine' :
                                                            (in_array($toothNum, ['14', '15', '24', '25', '34', '35', '44', '45']) ? 'premolar' : 'molar'));
                                            @endphp
                                            <div class="tooth-container m-1 text-center" style="position: relative; width: 60px;">
                                                <svg viewBox="0 0 60 80" width="60" height="80" style="display: block; transform: scaleY(-1);">
                                                    @if($toothType === 'incisor')
                                                        <!-- Incisor shape (flipped for lower jaw) -->
                                                        <path d="M 20 10 L 40 10 L 42 25 L 40 60 Q 30 75 30 75 Q 30 75 20 60 L 18 25 Z"
                                                              fill="{{ $color }}" stroke="#333" stroke-width="2"/>
                                                    @elseif($toothType === 'canine')
                                                        <!-- Canine shape -->
                                                        <path d="M 25 5 L 35 5 L 38 20 L 40 60 Q 30 75 30 75 Q 30 75 20 60 L 22 20 Z"
                                                              fill="{{ $color }}" stroke="#333" stroke-width="2"/>
                                                    @elseif($toothType === 'premolar')
                                                        <!-- Premolar shape -->
                                                        <path d="M 18 15 Q 20 8 25 10 Q 30 5 35 10 Q 40 8 42 15 L 42 60 Q 30 75 30 75 Q 30 75 18 60 Z"
                                                              fill="{{ $color }}" stroke="#333" stroke-width="2"/>
                                                    @else
                                                        <!-- Molar shape -->
                                                        <path d="M 15 15 Q 17 8 22 10 Q 27 5 30 8 Q 33 5 38 10 Q 43 8 45 15 L 45 60 Q 30 75 30 75 Q 30 75 15 60 Z"
                                                              fill="{{ $color }}" stroke="#333" stroke-width="2"/>
                                                    @endif
                                                    <!-- Tooth number (flipped back) -->
                                                    <text x="30" y="45" text-anchor="middle" font-size="14" font-weight="bold" fill="{{ $textColor }}" transform="scale(1, -1) translate(0, -90)">
                                                        {{ $toothNum }}
                                                    </text>
                                                </svg>
                                                @if($record)
                                                    <div class="tooth-condition" style="font-size: 9px; margin-top: 2px; color: #666;">
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

