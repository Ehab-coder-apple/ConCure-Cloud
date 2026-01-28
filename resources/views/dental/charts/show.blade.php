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

                    <!-- Occlusal View Dental Chart Display -->
                    <div class="dental-chart-container" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px; border-radius: 15px;">
                        @php
                            $toothNumbers = $dentalChart->tooth_numbers;
                            $toothRecords = $dentalChart->toothRecords->keyBy('tooth_number');
                        @endphp

                        <!-- Upper Jaw -->
                        <div class="mb-5">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="badge bg-light text-dark px-3 py-2" style="font-size: 14px; font-weight: 600;">{{ __('Right') }}</span>
                                <h6 class="text-white mb-0" style="font-weight: 600; letter-spacing: 1px;">{{ __('UPPER JAW') }}</h6>
                                <span class="badge bg-light text-dark px-3 py-2" style="font-size: 14px; font-weight: 600;">{{ __('Left') }}</span>
                            </div>
                            <div class="row">
                                <!-- Upper Right -->
                                <div class="col-6">
                                    <div class="d-flex justify-content-end flex-wrap">
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
                                            <div class="tooth-container m-1 text-center" style="position: relative; width: 50px;">
                                                <svg viewBox="0 0 50 60" width="50" height="60" style="display: block; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));">
                                                    @if($toothType === 'incisor')
                                                        <!-- Incisor: Thin chisel shape from top (narrow cutting edge) -->
                                                        <rect x="18" y="10" width="14" height="40" rx="3" ry="3"
                                                              fill="{{ $color }}" stroke="#2c3e50" stroke-width="2"/>
                                                        <!-- Cutting edge detail -->
                                                        <line x1="20" y1="12" x2="30" y2="12" stroke="#2c3e50" stroke-width="1.5" opacity="0.6"/>
                                                    @elseif($toothType === 'canine')
                                                        <!-- Canine: Pointed/triangular from top (sharp cusp) -->
                                                        <path d="M 25 8 L 35 18 L 35 48 Q 35 52 30 52 L 20 52 Q 15 52 15 48 L 15 18 Z"
                                                              fill="{{ $color }}" stroke="#2c3e50" stroke-width="2"/>
                                                        <!-- Cusp ridge -->
                                                        <line x1="25" y1="10" x2="25" y2="25" stroke="#2c3e50" stroke-width="1.5" opacity="0.5"/>
                                                    @elseif($toothType === 'premolar')
                                                        <!-- Premolar: Oval/rounded from top (smaller grinding surface) -->
                                                        <ellipse cx="25" cy="30" rx="12" ry="18"
                                                                 fill="{{ $color }}" stroke="#2c3e50" stroke-width="2"/>
                                                        <!-- Two cusps visible as indentations -->
                                                        <path d="M 20 20 Q 25 23 30 20" fill="none" stroke="#2c3e50" stroke-width="1.5" opacity="0.5"/>
                                                        <circle cx="22" cy="25" r="1.5" fill="#2c3e50" opacity="0.3"/>
                                                        <circle cx="28" cy="25" r="1.5" fill="#2c3e50" opacity="0.3"/>
                                                    @else
                                                        <!-- Molar: Wide rectangular with rounded corners (broad grinding surface) -->
                                                        <rect x="10" y="12" width="30" height="36" rx="5" ry="5"
                                                              fill="{{ $color }}" stroke="#2c3e50" stroke-width="2"/>
                                                        <!-- Four cusps pattern (fissure cross) -->
                                                        <path d="M 25 15 L 25 45" stroke="#2c3e50" stroke-width="1.5" opacity="0.4"/>
                                                        <path d="M 13 30 L 37 30" stroke="#2c3e50" stroke-width="1.5" opacity="0.4"/>
                                                        <!-- Cusp points -->
                                                        <circle cx="17" cy="20" r="2" fill="#2c3e50" opacity="0.3"/>
                                                        <circle cx="33" cy="20" r="2" fill="#2c3e50" opacity="0.3"/>
                                                        <circle cx="17" cy="40" r="2" fill="#2c3e50" opacity="0.3"/>
                                                        <circle cx="33" cy="40" r="2" fill="#2c3e50" opacity="0.3"/>
                                                    @endif
                                                    <!-- Tooth number -->
                                                    <text x="25" y="33" text-anchor="middle" font-size="11" font-weight="bold" fill="{{ $textColor }}" style="text-shadow: 0 1px 2px rgba(0,0,0,0.3);">
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
                                    <div class="d-flex justify-content-start flex-wrap">
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
                                            <div class="tooth-container m-1 text-center" style="position: relative; width: 50px;">
                                                <svg viewBox="0 0 50 60" width="50" height="60" style="display: block; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));">
                                                    @if($toothType === 'incisor')
                                                        <!-- Incisor: Thin chisel shape from top (narrow cutting edge) -->
                                                        <rect x="18" y="10" width="14" height="40" rx="3" ry="3"
                                                              fill="{{ $color }}" stroke="#2c3e50" stroke-width="2"/>
                                                        <!-- Cutting edge detail -->
                                                        <line x1="20" y1="12" x2="30" y2="12" stroke="#2c3e50" stroke-width="1.5" opacity="0.6"/>
                                                    @elseif($toothType === 'canine')
                                                        <!-- Canine: Pointed/triangular from top (sharp cusp) -->
                                                        <path d="M 25 8 L 35 18 L 35 48 Q 35 52 30 52 L 20 52 Q 15 52 15 48 L 15 18 Z"
                                                              fill="{{ $color }}" stroke="#2c3e50" stroke-width="2"/>
                                                        <!-- Cusp ridge -->
                                                        <line x1="25" y1="10" x2="25" y2="25" stroke="#2c3e50" stroke-width="1.5" opacity="0.5"/>
                                                    @elseif($toothType === 'premolar')
                                                        <!-- Premolar: Oval/rounded from top (smaller grinding surface) -->
                                                        <ellipse cx="25" cy="30" rx="12" ry="18"
                                                                 fill="{{ $color }}" stroke="#2c3e50" stroke-width="2"/>
                                                        <!-- Two cusps visible as indentations -->
                                                        <path d="M 20 20 Q 25 23 30 20" fill="none" stroke="#2c3e50" stroke-width="1.5" opacity="0.5"/>
                                                        <circle cx="22" cy="25" r="1.5" fill="#2c3e50" opacity="0.3"/>
                                                        <circle cx="28" cy="25" r="1.5" fill="#2c3e50" opacity="0.3"/>
                                                    @else
                                                        <!-- Molar: Wide rectangular with rounded corners (broad grinding surface) -->
                                                        <rect x="10" y="12" width="30" height="36" rx="5" ry="5"
                                                              fill="{{ $color }}" stroke="#2c3e50" stroke-width="2"/>
                                                        <!-- Four cusps pattern (fissure cross) -->
                                                        <path d="M 25 15 L 25 45" stroke="#2c3e50" stroke-width="1.5" opacity="0.4"/>
                                                        <path d="M 13 30 L 37 30" stroke="#2c3e50" stroke-width="1.5" opacity="0.4"/>
                                                        <!-- Cusp points -->
                                                        <circle cx="17" cy="20" r="2" fill="#2c3e50" opacity="0.3"/>
                                                        <circle cx="33" cy="20" r="2" fill="#2c3e50" opacity="0.3"/>
                                                        <circle cx="17" cy="40" r="2" fill="#2c3e50" opacity="0.3"/>
                                                        <circle cx="33" cy="40" r="2" fill="#2c3e50" opacity="0.3"/>
                                                    @endif
                                                    <!-- Tooth number -->
                                                    <text x="25" y="33" text-anchor="middle" font-size="11" font-weight="bold" fill="{{ $textColor }}" style="text-shadow: 0 1px 2px rgba(0,0,0,0.3);">
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
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="badge bg-light text-dark px-3 py-2" style="font-size: 14px; font-weight: 600;">{{ __('Right') }}</span>
                                <h6 class="text-white mb-0" style="font-weight: 600; letter-spacing: 1px;">{{ __('LOWER JAW') }}</h6>
                                <span class="badge bg-light text-dark px-3 py-2" style="font-size: 14px; font-weight: 600;">{{ __('Left') }}</span>
                            </div>
                            <div class="row">
                                <!-- Lower Right -->
                                <div class="col-6">
                                    <div class="d-flex justify-content-end flex-wrap">
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
                                            <div class="tooth-container m-1 text-center" style="position: relative; width: 50px;">
                                                <svg viewBox="0 0 50 60" width="50" height="60" style="display: block; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));">
                                                    @if($toothType === 'incisor')
                                                        <!-- Incisor: Thin chisel shape from top (narrow cutting edge) -->
                                                        <rect x="18" y="10" width="14" height="40" rx="3" ry="3"
                                                              fill="{{ $color }}" stroke="#2c3e50" stroke-width="2"/>
                                                        <!-- Cutting edge detail -->
                                                        <line x1="20" y1="12" x2="30" y2="12" stroke="#2c3e50" stroke-width="1.5" opacity="0.6"/>
                                                    @elseif($toothType === 'canine')
                                                        <!-- Canine: Pointed/triangular from top (sharp cusp) -->
                                                        <path d="M 25 8 L 35 18 L 35 48 Q 35 52 30 52 L 20 52 Q 15 52 15 48 L 15 18 Z"
                                                              fill="{{ $color }}" stroke="#2c3e50" stroke-width="2"/>
                                                        <!-- Cusp ridge -->
                                                        <line x1="25" y1="10" x2="25" y2="25" stroke="#2c3e50" stroke-width="1.5" opacity="0.5"/>
                                                    @elseif($toothType === 'premolar')
                                                        <!-- Premolar: Oval/rounded from top (smaller grinding surface) -->
                                                        <ellipse cx="25" cy="30" rx="12" ry="18"
                                                                 fill="{{ $color }}" stroke="#2c3e50" stroke-width="2"/>
                                                        <!-- Two cusps visible as indentations -->
                                                        <path d="M 20 20 Q 25 23 30 20" fill="none" stroke="#2c3e50" stroke-width="1.5" opacity="0.5"/>
                                                        <circle cx="22" cy="25" r="1.5" fill="#2c3e50" opacity="0.3"/>
                                                        <circle cx="28" cy="25" r="1.5" fill="#2c3e50" opacity="0.3"/>
                                                    @else
                                                        <!-- Molar: Wide rectangular with rounded corners (broad grinding surface) -->
                                                        <rect x="10" y="12" width="30" height="36" rx="5" ry="5"
                                                              fill="{{ $color }}" stroke="#2c3e50" stroke-width="2"/>
                                                        <!-- Four cusps pattern (fissure cross) -->
                                                        <path d="M 25 15 L 25 45" stroke="#2c3e50" stroke-width="1.5" opacity="0.4"/>
                                                        <path d="M 13 30 L 37 30" stroke="#2c3e50" stroke-width="1.5" opacity="0.4"/>
                                                        <!-- Cusp points -->
                                                        <circle cx="17" cy="20" r="2" fill="#2c3e50" opacity="0.3"/>
                                                        <circle cx="33" cy="20" r="2" fill="#2c3e50" opacity="0.3"/>
                                                        <circle cx="17" cy="40" r="2" fill="#2c3e50" opacity="0.3"/>
                                                        <circle cx="33" cy="40" r="2" fill="#2c3e50" opacity="0.3"/>
                                                    @endif
                                                    <!-- Tooth number -->
                                                    <text x="25" y="33" text-anchor="middle" font-size="11" font-weight="bold" fill="{{ $textColor }}" style="text-shadow: 0 1px 2px rgba(0,0,0,0.3);">
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
                                    <div class="d-flex justify-content-start flex-wrap">
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
                                            <div class="tooth-container m-1 text-center" style="position: relative; width: 50px;">
                                                <svg viewBox="0 0 50 60" width="50" height="60" style="display: block; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));">
                                                    @if($toothType === 'incisor')
                                                        <!-- Incisor: Thin chisel shape from top (narrow cutting edge) -->
                                                        <rect x="18" y="10" width="14" height="40" rx="3" ry="3"
                                                              fill="{{ $color }}" stroke="#2c3e50" stroke-width="2"/>
                                                        <!-- Cutting edge detail -->
                                                        <line x1="20" y1="12" x2="30" y2="12" stroke="#2c3e50" stroke-width="1.5" opacity="0.6"/>
                                                    @elseif($toothType === 'canine')
                                                        <!-- Canine: Pointed/triangular from top (sharp cusp) -->
                                                        <path d="M 25 8 L 35 18 L 35 48 Q 35 52 30 52 L 20 52 Q 15 52 15 48 L 15 18 Z"
                                                              fill="{{ $color }}" stroke="#2c3e50" stroke-width="2"/>
                                                        <!-- Cusp ridge -->
                                                        <line x1="25" y1="10" x2="25" y2="25" stroke="#2c3e50" stroke-width="1.5" opacity="0.5"/>
                                                    @elseif($toothType === 'premolar')
                                                        <!-- Premolar: Oval/rounded from top (smaller grinding surface) -->
                                                        <ellipse cx="25" cy="30" rx="12" ry="18"
                                                                 fill="{{ $color }}" stroke="#2c3e50" stroke-width="2"/>
                                                        <!-- Two cusps visible as indentations -->
                                                        <path d="M 20 20 Q 25 23 30 20" fill="none" stroke="#2c3e50" stroke-width="1.5" opacity="0.5"/>
                                                        <circle cx="22" cy="25" r="1.5" fill="#2c3e50" opacity="0.3"/>
                                                        <circle cx="28" cy="25" r="1.5" fill="#2c3e50" opacity="0.3"/>
                                                    @else
                                                        <!-- Molar: Wide rectangular with rounded corners (broad grinding surface) -->
                                                        <rect x="10" y="12" width="30" height="36" rx="5" ry="5"
                                                              fill="{{ $color }}" stroke="#2c3e50" stroke-width="2"/>
                                                        <!-- Four cusps pattern (fissure cross) -->
                                                        <path d="M 25 15 L 25 45" stroke="#2c3e50" stroke-width="1.5" opacity="0.4"/>
                                                        <path d="M 13 30 L 37 30" stroke="#2c3e50" stroke-width="1.5" opacity="0.4"/>
                                                        <!-- Cusp points -->
                                                        <circle cx="17" cy="20" r="2" fill="#2c3e50" opacity="0.3"/>
                                                        <circle cx="33" cy="20" r="2" fill="#2c3e50" opacity="0.3"/>
                                                        <circle cx="17" cy="40" r="2" fill="#2c3e50" opacity="0.3"/>
                                                        <circle cx="33" cy="40" r="2" fill="#2c3e50" opacity="0.3"/>
                                                    @endif
                                                    <!-- Tooth number -->
                                                    <text x="25" y="33" text-anchor="middle" font-size="11" font-weight="bold" fill="{{ $textColor }}" style="text-shadow: 0 1px 2px rgba(0,0,0,0.3);">
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

