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
                    @if(in_array(auth()->user()->role, ['doctor', 'assistant', 'admin', 'program_owner', 'dental_dept']))
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
                                                        <!-- Incisor: Flat, chisel-shaped with thin rectangular crown -->
                                                        <path d="M 22 8 L 38 8 L 39 12 L 40 22 L 40 55 Q 38 62 35 67 Q 32 72 30 74 Q 28 72 25 67 Q 22 62 20 55 L 20 22 L 21 12 Z"
                                                              fill="{{ $color }}" stroke="#333" stroke-width="1.8"/>
                                                        <!-- Incisal edge (cutting edge) -->
                                                        <line x1="22" y1="8" x2="38" y2="8" stroke="#333" stroke-width="2.5" stroke-linecap="round"/>
                                                    @elseif($toothType === 'canine')
                                                        <!-- Canine: Pointed/triangular cusp for tearing -->
                                                        <path d="M 30 5 L 36 12 L 38 18 L 39 25 L 39 55 Q 37 62 34 67 Q 31 72 30 74 Q 29 72 26 67 Q 23 62 21 55 L 21 25 L 22 18 L 24 12 Z"
                                                              fill="{{ $color }}" stroke="#333" stroke-width="1.8"/>
                                                        <!-- Prominent cusp point -->
                                                        <path d="M 26 12 Q 28 6 30 5 Q 32 6 34 12" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round"/>
                                                    @elseif($toothType === 'premolar')
                                                        <!-- Premolar: Two distinct cusps (bicuspid) -->
                                                        <path d="M 20 18 Q 22 14 24 12 Q 26 10 28 11 L 30 13 L 32 11 Q 34 10 36 12 Q 38 14 40 18 L 40 55 Q 38 62 35 67 Q 32 72 30 74 Q 28 72 25 67 Q 22 62 20 55 Z"
                                                              fill="{{ $color }}" stroke="#333" stroke-width="1.8"/>
                                                        <!-- Two visible cusps -->
                                                        <path d="M 24 12 Q 26 9 28 11" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round"/>
                                                        <path d="M 32 11 Q 34 9 36 12" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round"/>
                                                        <!-- Central groove between cusps -->
                                                        <line x1="30" y1="13" x2="30" y2="20" stroke="#333" stroke-width="1" opacity="0.5"/>
                                                    @else
                                                        <!-- Molar: Wider with 4 cusps for grinding -->
                                                        <path d="M 16 20 Q 18 16 20 14 Q 22 12 24 13 L 26 15 L 28 13 Q 30 12 32 13 L 34 15 L 36 13 Q 38 12 40 14 Q 42 16 44 20 L 44 55 Q 42 62 38 67 Q 34 72 30 74 Q 26 72 22 67 Q 18 62 16 55 Z"
                                                              fill="{{ $color }}" stroke="#333" stroke-width="1.8"/>
                                                        <!-- Four visible cusps -->
                                                        <path d="M 20 14 Q 22 11 24 13" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round"/>
                                                        <path d="M 26 15 Q 28 12 28 13" fill="none" stroke="#333" stroke-width="1.5" stroke-linecap="round"/>
                                                        <path d="M 32 13 Q 32 12 34 15" fill="none" stroke="#333" stroke-width="1.5" stroke-linecap="round"/>
                                                        <path d="M 36 13 Q 38 11 40 14" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round"/>
                                                        <!-- Central fissure pattern -->
                                                        <path d="M 24 15 Q 28 18 30 17 Q 32 18 36 15" fill="none" stroke="#333" stroke-width="1" opacity="0.4"/>
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
                                                        <!-- Incisor: Flat, chisel-shaped with thin rectangular crown -->
                                                        <path d="M 22 8 L 38 8 L 39 12 L 40 22 L 40 55 Q 38 62 35 67 Q 32 72 30 74 Q 28 72 25 67 Q 22 62 20 55 L 20 22 L 21 12 Z"
                                                              fill="{{ $color }}" stroke="#333" stroke-width="1.8"/>
                                                        <!-- Incisal edge (cutting edge) -->
                                                        <line x1="22" y1="8" x2="38" y2="8" stroke="#333" stroke-width="2.5" stroke-linecap="round"/>
                                                    @elseif($toothType === 'canine')
                                                        <!-- Canine: Pointed/triangular cusp for tearing -->
                                                        <path d="M 30 5 L 36 12 L 38 18 L 39 25 L 39 55 Q 37 62 34 67 Q 31 72 30 74 Q 29 72 26 67 Q 23 62 21 55 L 21 25 L 22 18 L 24 12 Z"
                                                              fill="{{ $color }}" stroke="#333" stroke-width="1.8"/>
                                                        <!-- Prominent cusp point -->
                                                        <path d="M 26 12 Q 28 6 30 5 Q 32 6 34 12" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round"/>
                                                    @elseif($toothType === 'premolar')
                                                        <!-- Premolar: Two distinct cusps (bicuspid) -->
                                                        <path d="M 20 18 Q 22 14 24 12 Q 26 10 28 11 L 30 13 L 32 11 Q 34 10 36 12 Q 38 14 40 18 L 40 55 Q 38 62 35 67 Q 32 72 30 74 Q 28 72 25 67 Q 22 62 20 55 Z"
                                                              fill="{{ $color }}" stroke="#333" stroke-width="1.8"/>
                                                        <!-- Two visible cusps -->
                                                        <path d="M 24 12 Q 26 9 28 11" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round"/>
                                                        <path d="M 32 11 Q 34 9 36 12" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round"/>
                                                        <!-- Central groove between cusps -->
                                                        <line x1="30" y1="13" x2="30" y2="20" stroke="#333" stroke-width="1" opacity="0.5"/>
                                                    @else
                                                        <!-- Molar: Wider with 4 cusps for grinding -->
                                                        <path d="M 16 20 Q 18 16 20 14 Q 22 12 24 13 L 26 15 L 28 13 Q 30 12 32 13 L 34 15 L 36 13 Q 38 12 40 14 Q 42 16 44 20 L 44 55 Q 42 62 38 67 Q 34 72 30 74 Q 26 72 22 67 Q 18 62 16 55 Z"
                                                              fill="{{ $color }}" stroke="#333" stroke-width="1.8"/>
                                                        <!-- Four visible cusps -->
                                                        <path d="M 20 14 Q 22 11 24 13" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round"/>
                                                        <path d="M 26 15 Q 28 12 28 13" fill="none" stroke="#333" stroke-width="1.5" stroke-linecap="round"/>
                                                        <path d="M 32 13 Q 32 12 34 15" fill="none" stroke="#333" stroke-width="1.5" stroke-linecap="round"/>
                                                        <path d="M 36 13 Q 38 11 40 14" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round"/>
                                                        <!-- Central fissure pattern -->
                                                        <path d="M 24 15 Q 28 18 30 17 Q 32 18 36 15" fill="none" stroke="#333" stroke-width="1" opacity="0.4"/>
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
                                                        <!-- Incisor: Flat, chisel-shaped with thin rectangular crown -->
                                                        <path d="M 22 8 L 38 8 L 39 12 L 40 22 L 40 55 Q 38 62 35 67 Q 32 72 30 74 Q 28 72 25 67 Q 22 62 20 55 L 20 22 L 21 12 Z"
                                                              fill="{{ $color }}" stroke="#333" stroke-width="1.8"/>
                                                        <!-- Incisal edge (cutting edge) -->
                                                        <line x1="22" y1="8" x2="38" y2="8" stroke="#333" stroke-width="2.5" stroke-linecap="round"/>
                                                    @elseif($toothType === 'canine')
                                                        <!-- Canine: Pointed/triangular cusp for tearing -->
                                                        <path d="M 30 5 L 36 12 L 38 18 L 39 25 L 39 55 Q 37 62 34 67 Q 31 72 30 74 Q 29 72 26 67 Q 23 62 21 55 L 21 25 L 22 18 L 24 12 Z"
                                                              fill="{{ $color }}" stroke="#333" stroke-width="1.8"/>
                                                        <!-- Prominent cusp point -->
                                                        <path d="M 26 12 Q 28 6 30 5 Q 32 6 34 12" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round"/>
                                                    @elseif($toothType === 'premolar')
                                                        <!-- Premolar: Two distinct cusps (bicuspid) -->
                                                        <path d="M 20 18 Q 22 14 24 12 Q 26 10 28 11 L 30 13 L 32 11 Q 34 10 36 12 Q 38 14 40 18 L 40 55 Q 38 62 35 67 Q 32 72 30 74 Q 28 72 25 67 Q 22 62 20 55 Z"
                                                              fill="{{ $color }}" stroke="#333" stroke-width="1.8"/>
                                                        <!-- Two visible cusps -->
                                                        <path d="M 24 12 Q 26 9 28 11" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round"/>
                                                        <path d="M 32 11 Q 34 9 36 12" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round"/>
                                                        <!-- Central groove between cusps -->
                                                        <line x1="30" y1="13" x2="30" y2="20" stroke="#333" stroke-width="1" opacity="0.5"/>
                                                    @else
                                                        <!-- Molar: Wider with 4 cusps for grinding -->
                                                        <path d="M 16 20 Q 18 16 20 14 Q 22 12 24 13 L 26 15 L 28 13 Q 30 12 32 13 L 34 15 L 36 13 Q 38 12 40 14 Q 42 16 44 20 L 44 55 Q 42 62 38 67 Q 34 72 30 74 Q 26 72 22 67 Q 18 62 16 55 Z"
                                                              fill="{{ $color }}" stroke="#333" stroke-width="1.8"/>
                                                        <!-- Four visible cusps -->
                                                        <path d="M 20 14 Q 22 11 24 13" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round"/>
                                                        <path d="M 26 15 Q 28 12 28 13" fill="none" stroke="#333" stroke-width="1.5" stroke-linecap="round"/>
                                                        <path d="M 32 13 Q 32 12 34 15" fill="none" stroke="#333" stroke-width="1.5" stroke-linecap="round"/>
                                                        <path d="M 36 13 Q 38 11 40 14" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round"/>
                                                        <!-- Central fissure pattern -->
                                                        <path d="M 24 15 Q 28 18 30 17 Q 32 18 36 15" fill="none" stroke="#333" stroke-width="1" opacity="0.4"/>
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
                                                        <!-- Incisor: Flat, chisel-shaped with thin rectangular crown -->
                                                        <path d="M 22 8 L 38 8 L 39 12 L 40 22 L 40 55 Q 38 62 35 67 Q 32 72 30 74 Q 28 72 25 67 Q 22 62 20 55 L 20 22 L 21 12 Z"
                                                              fill="{{ $color }}" stroke="#333" stroke-width="1.8"/>
                                                        <!-- Incisal edge (cutting edge) -->
                                                        <line x1="22" y1="8" x2="38" y2="8" stroke="#333" stroke-width="2.5" stroke-linecap="round"/>
                                                    @elseif($toothType === 'canine')
                                                        <!-- Canine: Pointed/triangular cusp for tearing -->
                                                        <path d="M 30 5 L 36 12 L 38 18 L 39 25 L 39 55 Q 37 62 34 67 Q 31 72 30 74 Q 29 72 26 67 Q 23 62 21 55 L 21 25 L 22 18 L 24 12 Z"
                                                              fill="{{ $color }}" stroke="#333" stroke-width="1.8"/>
                                                        <!-- Prominent cusp point -->
                                                        <path d="M 26 12 Q 28 6 30 5 Q 32 6 34 12" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round"/>
                                                    @elseif($toothType === 'premolar')
                                                        <!-- Premolar: Two distinct cusps (bicuspid) -->
                                                        <path d="M 20 18 Q 22 14 24 12 Q 26 10 28 11 L 30 13 L 32 11 Q 34 10 36 12 Q 38 14 40 18 L 40 55 Q 38 62 35 67 Q 32 72 30 74 Q 28 72 25 67 Q 22 62 20 55 Z"
                                                              fill="{{ $color }}" stroke="#333" stroke-width="1.8"/>
                                                        <!-- Two visible cusps -->
                                                        <path d="M 24 12 Q 26 9 28 11" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round"/>
                                                        <path d="M 32 11 Q 34 9 36 12" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round"/>
                                                        <!-- Central groove between cusps -->
                                                        <line x1="30" y1="13" x2="30" y2="20" stroke="#333" stroke-width="1" opacity="0.5"/>
                                                    @else
                                                        <!-- Molar: Wider with 4 cusps for grinding -->
                                                        <path d="M 16 20 Q 18 16 20 14 Q 22 12 24 13 L 26 15 L 28 13 Q 30 12 32 13 L 34 15 L 36 13 Q 38 12 40 14 Q 42 16 44 20 L 44 55 Q 42 62 38 67 Q 34 72 30 74 Q 26 72 22 67 Q 18 62 16 55 Z"
                                                              fill="{{ $color }}" stroke="#333" stroke-width="1.8"/>
                                                        <!-- Four visible cusps -->
                                                        <path d="M 20 14 Q 22 11 24 13" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round"/>
                                                        <path d="M 26 15 Q 28 12 28 13" fill="none" stroke="#333" stroke-width="1.5" stroke-linecap="round"/>
                                                        <path d="M 32 13 Q 32 12 34 15" fill="none" stroke="#333" stroke-width="1.5" stroke-linecap="round"/>
                                                        <path d="M 36 13 Q 38 11 40 14" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round"/>
                                                        <!-- Central fissure pattern -->
                                                        <path d="M 24 15 Q 28 18 30 17 Q 32 18 36 15" fill="none" stroke="#333" stroke-width="1" opacity="0.4"/>
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

