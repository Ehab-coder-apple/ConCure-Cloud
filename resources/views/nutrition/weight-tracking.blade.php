@extends('layouts.app')

@section('title', __('Weight Tracking') . ' - ' . $dietPlan->title)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-weight text-primary me-2"></i>
                        {{ __('Weight Tracking') }}
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('nutrition.index') }}">{{ __('Nutrition Plans') }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('nutrition.show', $dietPlan) }}">{{ $dietPlan->title }}</a></li>
                            <li class="breadcrumb-item active">{{ __('Weight Tracking') }}</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addWeightRecordModal">
                        <i class="fas fa-plus me-1"></i>
                        {{ __('Add Weight Record') }}
                    </button>
                    <a href="{{ route('nutrition.show', $dietPlan) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to Plan') }}
                    </a>
                </div>
            </div>

            <!-- Patient & Plan Info -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="fas fa-user me-2"></i>
                                {{ __('Patient Information') }}
                            </h6>
                            <p class="mb-1"><strong>{{ __('Name') }}:</strong> {{ $dietPlan->patient->first_name }} {{ $dietPlan->patient->last_name }}</p>
                            <p class="mb-1"><strong>{{ __('Patient ID') }}:</strong> {{ $dietPlan->patient->patient_id }}</p>
                            <p class="mb-0"><strong>{{ __('Plan') }}:</strong> {{ $dietPlan->title }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="fas fa-bullseye me-2"></i>
                                {{ __('Weight Goals') }}
                            </h6>
                            <div class="row">
                                <div class="col-6">
                                    <p class="mb-1"><strong>{{ __('Initial') }}:</strong> {{ $dietPlan->initial_weight ? $dietPlan->initial_weight . ' kg' : '--' }}</p>
                                    <p class="mb-0"><strong>{{ __('Current') }}:</strong> {{ $dietPlan->current_weight ? $dietPlan->current_weight . ' kg' : '--' }}</p>
                                </div>
                                <div class="col-6">
                                    <p class="mb-1"><strong>{{ __('Target') }}:</strong> {{ $dietPlan->target_weight ? $dietPlan->target_weight . ' kg' : '--' }}</p>
                                    <p class="mb-0"><strong>{{ __('Weekly Goal') }}:</strong> {{ $dietPlan->weekly_weight_goal ? ($dietPlan->weekly_weight_goal > 0 ? '+' : '') . $dietPlan->weekly_weight_goal . ' kg' : '--' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Progress Statistics -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-chart-line fa-2x text-primary mb-2"></i>
                            <h4 class="mb-1">{{ $stats['total_records'] }}</h4>
                            <small class="text-muted">{{ __('Total Records') }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-balance-scale fa-2x {{ $stats['total_weight_change'] > 0 ? 'text-success' : ($stats['total_weight_change'] < 0 ? 'text-warning' : 'text-secondary') }} mb-2"></i>
                            <h4 class="mb-1">{{ $stats['total_weight_change'] ? ($stats['total_weight_change'] > 0 ? '+' : '') . number_format($stats['total_weight_change'], 1) . ' kg' : '--' }}</h4>
                            <small class="text-muted">{{ __('Total Change') }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-percentage fa-2x text-info mb-2"></i>
                            <h4 class="mb-1">{{ $stats['progress_percentage'] ? number_format($stats['progress_percentage'], 1) . '%' : '--' }}</h4>
                            <small class="text-muted">{{ __('Progress to Goal') }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-calendar-week fa-2x text-success mb-2"></i>
                            <h4 class="mb-1">{{ $stats['average_weekly_change'] ? ($stats['average_weekly_change'] > 0 ? '+' : '') . number_format($stats['average_weekly_change'], 2) . ' kg' : '--' }}</h4>
                            <small class="text-muted">{{ __('Avg Weekly Change') }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BMI Progress -->
            @if($dietPlan->initial_bmi || $dietPlan->current_bmi || $dietPlan->target_bmi)
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-heartbeat me-2"></i>
                        {{ __('BMI Progress') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-lg-4 col-md-6 mb-3 mb-lg-0">
                            <div class="border-end border-lg-block d-none d-lg-block h-100"></div>
                            <h5 class="mb-2">{{ __('Initial BMI') }}</h5>
                            @if($dietPlan->initial_bmi)
                                <h3 class="mb-1">
                                    <span class="badge bg-secondary fs-5">{{ number_format($dietPlan->initial_bmi, 1) }}</span>
                                </h3>
                                <small class="text-muted">
                                    @if($dietPlan->initial_bmi < 18.5)
                                        <span class="badge bg-info">{{ __('Underweight') }}</span>
                                    @elseif($dietPlan->initial_bmi < 25)
                                        <span class="badge bg-success">{{ __('Normal weight') }}</span>
                                    @elseif($dietPlan->initial_bmi < 30)
                                        <span class="badge bg-warning">{{ __('Overweight') }}</span>
                                    @else
                                        <span class="badge bg-danger">{{ __('Obese') }}</span>
                                    @endif
                                </small>
                            @else
                                <h3 class="text-muted">--</h3>
                                <small class="text-muted">{{ __('Not set') }}</small>
                            @endif
                        </div>

                        <div class="col-lg-4 col-md-6 mb-3 mb-lg-0">
                            <div class="border-end border-lg-block d-none d-lg-block h-100"></div>
                            <h5 class="mb-2">{{ __('Current BMI') }}</h5>
                            @if($dietPlan->current_bmi)
                                <h3 class="mb-1">
                                    <span class="badge bg-primary fs-5">{{ number_format($dietPlan->current_bmi, 1) }}</span>
                                </h3>
                                <small class="text-muted">
                                    @if($dietPlan->current_bmi < 18.5)
                                        <span class="badge bg-info">{{ __('Underweight') }}</span>
                                    @elseif($dietPlan->current_bmi < 25)
                                        <span class="badge bg-success">{{ __('Normal weight') }}</span>
                                    @elseif($dietPlan->current_bmi < 30)
                                        <span class="badge bg-warning">{{ __('Overweight') }}</span>
                                    @else
                                        <span class="badge bg-danger">{{ __('Obese') }}</span>
                                    @endif
                                </small>
                            @else
                                <h3 class="text-muted">--</h3>
                                <small class="text-muted">{{ __('Not recorded') }}</small>
                            @endif
                        </div>

                        <div class="col-lg-4 col-md-12">
                            <h5 class="mb-2">{{ __('Target BMI') }}</h5>
                            @if($dietPlan->target_bmi)
                                <h3 class="mb-1">
                                    <span class="badge bg-warning fs-5">{{ number_format($dietPlan->target_bmi, 1) }}</span>
                                </h3>
                                <small class="text-muted">
                                    @if($dietPlan->target_bmi < 18.5)
                                        <span class="badge bg-info">{{ __('Underweight') }}</span>
                                    @elseif($dietPlan->target_bmi < 25)
                                        <span class="badge bg-success">{{ __('Normal weight') }}</span>
                                    @elseif($dietPlan->target_bmi < 30)
                                        <span class="badge bg-warning">{{ __('Overweight') }}</span>
                                    @else
                                        <span class="badge bg-danger">{{ __('Obese') }}</span>
                                    @endif
                                </small>
                            @else
                                <h3 class="text-muted">--</h3>
                                <small class="text-muted">{{ __('Not set') }}</small>
                            @endif
                        </div>
                    </div>
                    @if($stats['bmi_change'])
                    <div class="text-center mt-3">
                        <span class="badge {{ $stats['bmi_change'] < 0 ? 'bg-success' : 'bg-info' }} fs-6">
                            {{ __('BMI Change') }}: {{ $stats['bmi_change'] > 0 ? '+' : '' }}{{ number_format($stats['bmi_change'], 1) }}
                        </span>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Goal Achievement Status -->
            @if($stats['goal_achieved'])
            <div class="alert alert-success">
                <i class="fas fa-trophy me-2"></i>
                <strong>{{ __('Congratulations!') }}</strong> {{ __('Weight goal has been achieved!') }}
            </div>
            @elseif($stats['projected_completion'])
            <div class="alert alert-info">
                <i class="fas fa-calendar-alt me-2"></i>
                <strong>{{ __('Projected Goal Achievement') }}:</strong> {{ $stats['projected_completion']->format('M d, Y') }}
                ({{ __('in about') }} {{ $stats['projected_completion']->diffInWeeks(now()) }} {{ __('weeks') }})
            </div>
            @endif

            <!-- Weight Records Table -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        {{ __('Weight Records') }}
                        <span class="badge bg-secondary ms-2">{{ $weightRecords->count() }}</span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    @if($weightRecords->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Weight') }}</th>
                                        <th>{{ __('Height') }}</th>
                                        <th>{{ __('BMI') }}</th>
                                        <th>{{ __('Change') }}</th>
                                        <th>{{ __('Progress') }}</th>
                                        <th>{{ __('Notes') }}</th>
                                        <th>{{ __('Recorded By') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($weightRecords as $record)
                                    <tr>
                                        <td>
                                            <strong>{{ $record->record_date->format('M d, Y') }}</strong>
                                            <br><small class="text-muted">{{ $record->record_date->diffForHumans() }}</small>
                                        </td>
                                        <td>
                                            <strong>{{ number_format($record->weight, 1) }} kg</strong>
                                        </td>
                                        <td>{{ $record->height ? number_format($record->height, 1) . ' cm' : '--' }}</td>
                                        <td>
                                            @if($record->bmi)
                                                <strong>{{ number_format($record->bmi, 1) }}</strong>
                                                <br><small class="text-muted">{{ $record->bmi_category }}</small>
                                            @else
                                                --
                                            @endif
                                        </td>
                                        <td>
                                            @if($record->weight_change)
                                                <span class="{{ $record->weight_change_badge_class }}">
                                                    {{ $record->weight_change > 0 ? '+' : '' }}{{ number_format($record->weight_change, 1) }} kg
                                                </span>
                                                @if($record->weight_change_percentage)
                                                    <br><small class="text-muted">({{ $record->weight_change_percentage > 0 ? '+' : '' }}{{ number_format($record->weight_change_percentage, 1) }}%)</small>
                                                @endif
                                            @else
                                                <span class="badge bg-secondary">{{ __('Initial') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($record->progress_towards_goal)
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-success" role="progressbar" 
                                                         style="width: {{ min($record->progress_towards_goal, 100) }}%">
                                                        {{ number_format($record->progress_towards_goal, 1) }}%
                                                    </div>
                                                </div>
                                            @else
                                                --
                                            @endif
                                        </td>
                                        <td>
                                            @if($record->notes)
                                                <span data-bs-toggle="tooltip" title="{{ $record->notes }}">
                                                    {{ Str::limit($record->notes, 30) }}
                                                </span>
                                            @else
                                                --
                                            @endif
                                        </td>
                                        <td>
                                            @if($record->recorder)
                                                {{ $record->recorder->first_name }} {{ $record->recorder->last_name }}
                                            @else
                                                <span class="text-muted">{{ __('Unknown') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                        data-bs-toggle="modal" data-bs-target="#editWeightRecordModal"
                                                        data-record-id="{{ $record->id }}"
                                                        data-weight="{{ $record->weight }}"
                                                        data-target-weight="{{ $record->dietPlan->target_weight }}"
                                                        data-target-bmi="{{ $record->dietPlan->target_bmi }}"
                                                        data-height="{{ $record->height }}"
                                                        data-date="{{ $record->record_date->format('Y-m-d') }}"
                                                        data-notes="{{ $record->notes }}"
                                                        title="{{ __('Edit') }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                
                                                @if(!$loop->last || $weightRecords->count() > 1)
                                                <form method="POST" action="{{ route('nutrition.weight-records.delete', [$dietPlan, $record]) }}" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                            title="{{ __('Delete') }}"
                                                            onclick="return confirm('{{ __('Are you sure you want to delete this weight record?') }}')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-weight fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('No weight records found') }}</h5>
                            <p class="text-muted">{{ __('Start tracking progress by adding the first weight record.') }}</p>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addWeightRecordModal">
                                <i class="fas fa-plus me-1"></i>
                                {{ __('Add First Record') }}
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Weight Record Modal -->
<div class="modal fade" id="addWeightRecordModal" tabindex="-1" aria-labelledby="addWeightRecordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addWeightRecordModalLabel">
                    <i class="fas fa-plus me-2"></i>
                    {{ __('Add Weight Record') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('nutrition.weight-records.store', $dietPlan) }}">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="weight" class="form-label">{{ __('Current Weight (kg)') }} <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('weight') is-invalid @enderror"
                                   id="weight" name="weight" value="{{ old('weight') }}"
                                   step="0.1" min="20" max="500" required>
                            @error('weight')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="target_weight" class="form-label">{{ __('Target Weight (kg)') }}</label>
                            <input type="number" class="form-control @error('target_weight') is-invalid @enderror"
                                   id="target_weight" name="target_weight" value="{{ old('target_weight', $dietPlan->target_weight) }}"
                                   step="0.1" min="20" max="500">
                            @error('target_weight')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">{{ __('Leave empty to keep current target weight') }}</div>
                        </div>

                        <div class="col-md-6">
                            <label for="target_bmi" class="form-label">{{ __('Target BMI') }}</label>
                            <input type="number" class="form-control @error('target_bmi') is-invalid @enderror"
                                   id="target_bmi" name="target_bmi" value="{{ old('target_bmi', $dietPlan->target_bmi) }}"
                                   step="0.1" min="10" max="50">
                            @error('target_bmi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">{{ __('Calculated automatically or enter manually') }}</div>
                        </div>

                        <div class="col-md-6">
                            <label for="height" class="form-label">{{ __('Height (cm)') }}</label>
                            <input type="number" class="form-control @error('height') is-invalid @enderror"
                                   id="height" name="height" value="{{ old('height', $dietPlan->initial_height) }}"
                                   step="0.1" min="100" max="250">
                            @error('height')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="record_date" class="form-label">{{ __('Record Date') }} <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('record_date') is-invalid @enderror"
                                   id="record_date" name="record_date" value="{{ old('record_date', date('Y-m-d')) }}"
                                   max="{{ date('Y-m-d') }}" required>
                            @error('record_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ __('BMI') }}</label>
                            <div class="form-control-plaintext" id="calculated-bmi">
                                <span class="text-muted">{{ __('Enter weight and height to calculate') }}</span>
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="notes" class="form-label">{{ __('Notes') }}</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror"
                                      id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Additional Measurements -->
                        <div class="col-12">
                            <h6 class="border-bottom pb-2">{{ __('Additional Measurements (Optional)') }}</h6>
                        </div>

                        <div class="col-md-6">
                            <label for="measurements_waist" class="form-label">{{ __('Waist (cm)') }}</label>
                            <input type="number" class="form-control"
                                   id="measurements_waist" name="measurements[waist]"
                                   step="0.1" min="30" max="200">
                        </div>

                        <div class="col-md-6">
                            <label for="measurements_chest" class="form-label">{{ __('Chest (cm)') }}</label>
                            <input type="number" class="form-control"
                                   id="measurements_chest" name="measurements[chest]"
                                   step="0.1" min="50" max="200">
                        </div>

                        <div class="col-md-6">
                            <label for="measurements_hips" class="form-label">{{ __('Hips (cm)') }}</label>
                            <input type="number" class="form-control"
                                   id="measurements_hips" name="measurements[hips]"
                                   step="0.1" min="50" max="200">
                        </div>

                        <div class="col-md-6">
                            <label for="measurements_arm" class="form-label">{{ __('Arm (cm)') }}</label>
                            <input type="number" class="form-control"
                                   id="measurements_arm" name="measurements[arm]"
                                   step="0.1" min="15" max="100">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>
                        {{ __('Add Record') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Weight Record Modal -->
<div class="modal fade" id="editWeightRecordModal" tabindex="-1" aria-labelledby="editWeightRecordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editWeightRecordModalLabel">
                    <i class="fas fa-edit me-2"></i>
                    {{ __('Edit Weight Record') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="editWeightRecordForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="edit_weight" class="form-label">{{ __('Current Weight (kg)') }} <span class="text-danger">*</span></label>
                            <input type="number" class="form-control"
                                   id="edit_weight" name="weight"
                                   step="0.1" min="20" max="500" required>
                        </div>

                        <div class="col-md-6">
                            <label for="edit_target_weight" class="form-label">{{ __('Target Weight (kg)') }}</label>
                            <input type="number" class="form-control"
                                   id="edit_target_weight" name="target_weight"
                                   step="0.1" min="20" max="500">
                            <div class="form-text">{{ __('Leave empty to keep current target weight') }}</div>
                        </div>

                        <div class="col-md-6">
                            <label for="edit_target_bmi" class="form-label">{{ __('Target BMI') }}</label>
                            <input type="number" class="form-control"
                                   id="edit_target_bmi" name="target_bmi"
                                   step="0.1" min="10" max="50">
                            <div class="form-text">{{ __('Calculated automatically or enter manually') }}</div>
                        </div>

                        <div class="col-md-6">
                            <label for="edit_height" class="form-label">{{ __('Height (cm)') }}</label>
                            <input type="number" class="form-control"
                                   id="edit_height" name="height"
                                   step="0.1" min="100" max="250">
                        </div>

                        <div class="col-md-6">
                            <label for="edit_record_date" class="form-label">{{ __('Record Date') }} <span class="text-danger">*</span></label>
                            <input type="date" class="form-control"
                                   id="edit_record_date" name="record_date"
                                   max="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ __('BMI') }}</label>
                            <div class="form-control-plaintext" id="edit-calculated-bmi">
                                <span class="text-muted">{{ __('Enter weight and height to calculate') }}</span>
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="edit_notes" class="form-label">{{ __('Notes') }}</label>
                            <textarea class="form-control"
                                      id="edit_notes" name="notes" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>
                        {{ __('Update Record') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // BMI calculation function
    function calculateBMI(weight, height) {
        if (!weight || !height || weight <= 0 || height <= 0) {
            return null;
        }
        const heightInMeters = height / 100;
        return weight / (heightInMeters * heightInMeters);
    }

    function getBMICategory(bmi) {
        if (!bmi) return '';
        if (bmi < 18.5) return '{{ __("Underweight") }}';
        if (bmi < 25) return '{{ __("Normal weight") }}';
        if (bmi < 30) return '{{ __("Overweight") }}';
        return '{{ __("Obese") }}';
    }

    function getBMIClass(bmi) {
        if (!bmi) return 'text-muted';
        if (bmi < 18.5) return 'text-info';
        if (bmi < 25) return 'text-success';
        if (bmi < 30) return 'text-warning';
        return 'text-danger';
    }

    // Add weight record BMI calculation
    function updateAddBMI() {
        const weight = parseFloat(document.getElementById('weight').value);
        const height = parseFloat(document.getElementById('height').value);
        const bmiDisplay = document.getElementById('calculated-bmi');

        if (weight && height) {
            const bmi = calculateBMI(weight, height);
            const category = getBMICategory(bmi);
            const bmiClass = getBMIClass(bmi);

            bmiDisplay.innerHTML = `<strong class="${bmiClass}">${bmi.toFixed(1)}</strong> <small class="text-muted">(${category})</small>`;
        } else {
            bmiDisplay.innerHTML = '<span class="text-muted">{{ __("Enter weight and height to calculate") }}</span>';
        }
    }

    // Edit weight record BMI calculation
    function updateEditBMI() {
        const weight = parseFloat(document.getElementById('edit_weight').value);
        const height = parseFloat(document.getElementById('edit_height').value);
        const bmiDisplay = document.getElementById('edit-calculated-bmi');

        if (weight && height) {
            const bmi = calculateBMI(weight, height);
            const category = getBMICategory(bmi);
            const bmiClass = getBMIClass(bmi);

            bmiDisplay.innerHTML = `<strong class="${bmiClass}">${bmi.toFixed(1)}</strong> <small class="text-muted">(${category})</small>`;
        } else {
            bmiDisplay.innerHTML = '<span class="text-muted">{{ __("Enter weight and height to calculate") }}</span>';
        }
    }

    // Target BMI calculation for add form
    function updateAddTargetBMI() {
        const targetWeight = parseFloat(document.getElementById('target_weight').value);
        const height = parseFloat(document.getElementById('height').value);
        const targetBmiInput = document.getElementById('target_bmi');

        if (targetWeight && height) {
            const targetBmi = calculateBMI(targetWeight, height);
            targetBmiInput.value = targetBmi.toFixed(1);
        }
    }

    // Target BMI calculation for edit form
    function updateEditTargetBMI() {
        const targetWeight = parseFloat(document.getElementById('edit_target_weight').value);
        const height = parseFloat(document.getElementById('edit_height').value);
        const targetBmiInput = document.getElementById('edit_target_bmi');

        if (targetWeight && height) {
            const targetBmi = calculateBMI(targetWeight, height);
            targetBmiInput.value = targetBmi.toFixed(1);
        }
    }

    // Add event listeners for BMI calculation
    document.getElementById('weight').addEventListener('input', updateAddBMI);
    document.getElementById('height').addEventListener('input', function() {
        updateAddBMI();
        updateAddTargetBMI();
    });
    document.getElementById('target_weight').addEventListener('input', updateAddTargetBMI);

    document.getElementById('edit_weight').addEventListener('input', updateEditBMI);
    document.getElementById('edit_height').addEventListener('input', function() {
        updateEditBMI();
        updateEditTargetBMI();
    });
    document.getElementById('edit_target_weight').addEventListener('input', updateEditTargetBMI);

    // Handle edit modal
    const editModal = document.getElementById('editWeightRecordModal');
    editModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const recordId = button.getAttribute('data-record-id');
        const weight = button.getAttribute('data-weight');
        const targetWeight = button.getAttribute('data-target-weight');
        const targetBmi = button.getAttribute('data-target-bmi');
        const height = button.getAttribute('data-height');
        const date = button.getAttribute('data-date');
        const notes = button.getAttribute('data-notes');

        // Update form action
        const form = document.getElementById('editWeightRecordForm');
        form.action = `{{ route('nutrition.weight-records.update', [$dietPlan, ':id']) }}`.replace(':id', recordId);

        // Populate form fields
        document.getElementById('edit_weight').value = weight;
        document.getElementById('edit_target_weight').value = targetWeight || '';
        document.getElementById('edit_target_bmi').value = targetBmi || '';
        document.getElementById('edit_height').value = height || '';
        document.getElementById('edit_record_date').value = date;
        document.getElementById('edit_notes').value = notes || '';

        // Calculate BMI
        updateEditBMI();
        updateEditTargetBMI();
    });

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initial BMI calculation for add modal
    updateAddBMI();
});
</script>
@endpush
