@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><i class="fas fa-notes-medical me-2"></i>{{ __('ENT Record Details') }}</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('ent.index') }}">{{ __('ENT Records') }}</a></li>
                            <li class="breadcrumb-item active">{{ __('View') }}</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('ent.print', $entRecord) }}" class="btn btn-info" target="_blank">
                        <i class="fas fa-print me-1"></i>{{ __('Print') }}
                    </a>
                    <a href="{{ route('ent.edit', $entRecord) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-1"></i>{{ __('Edit') }}
                    </a>
                    <a href="{{ route('ent.audiometry.create', ['ent_record_id' => $entRecord->id]) }}" class="btn btn-success">
                        <i class="fas fa-volume-high me-1"></i>{{ __('Add Audiometry Test') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Basic Information -->
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">{{ __('Basic Information') }}</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <p><strong>{{ __('Patient') }}:</strong><br>
                        <a href="{{ route('patients.show', $entRecord->patient) }}">
                            {{ $entRecord->patient->full_name }}
                        </a>
                        <br><small class="text-muted">{{ $entRecord->patient->patient_id }}</small>
                    </p>
                </div>
                <div class="col-md-4">
                    <p><strong>{{ __('Doctor') }}:</strong><br>{{ $entRecord->doctor->full_name }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>{{ __('Visit Date') }}:</strong><br>{{ $entRecord->visit_date->format('Y-m-d') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Chief Complaint -->
    @if($entRecord->chief_complaint)
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">{{ __('Chief Complaint') }}</h5>
        </div>
        <div class="card-body">
            <p class="mb-0">{{ $entRecord->chief_complaint }}</p>
        </div>
    </div>
    @endif

    <!-- Examination Findings -->
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">{{ __('Examination Findings') }}</h5>
        </div>
        <div class="card-body">
            <div class="row">
                @if($entRecord->ear_examination)
                <div class="col-md-6 mb-3">
                    <h6><i class="fas fa-ear-listen me-2"></i>{{ __('Ear Examination') }}</h6>
                    <p class="ms-4">{{ $entRecord->ear_examination }}</p>
                </div>
                @endif

                @if($entRecord->nose_examination)
                <div class="col-md-6 mb-3">
                    <h6><i class="fas fa-head-side-virus me-2"></i>{{ __('Nose Examination') }}</h6>
                    <p class="ms-4">{{ $entRecord->nose_examination }}</p>
                </div>
                @endif

                @if($entRecord->throat_examination)
                <div class="col-md-6 mb-3">
                    <h6><i class="fas fa-lungs me-2"></i>{{ __('Throat Examination') }}</h6>
                    <p class="ms-4">{{ $entRecord->throat_examination }}</p>
                </div>
                @endif

                @if($entRecord->neck_examination)
                <div class="col-md-6 mb-3">
                    <h6><i class="fas fa-user-injured me-2"></i>{{ __('Neck Examination') }}</h6>
                    <p class="ms-4">{{ $entRecord->neck_examination }}</p>
                </div>
                @endif

                @if($entRecord->cranial_nerves)
                <div class="col-md-12 mb-3">
                    <h6><i class="fas fa-brain me-2"></i>{{ __('Cranial Nerves') }}</h6>
                    <p class="ms-4">{{ $entRecord->cranial_nerves }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Diagnosis & Treatment -->
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">{{ __('Diagnosis & Treatment') }}</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8 mb-3">
                    <h6>{{ __('Diagnosis') }}</h6>
                    <p class="ms-3">{{ $entRecord->diagnosis ?? __('Not specified') }}</p>
                </div>
                @if($entRecord->icd10_code)
                <div class="col-md-4 mb-3">
                    <h6>{{ __('ICD-10 Code') }}</h6>
                    <p class="ms-3"><code>{{ $entRecord->icd10_code }}</code></p>
                </div>
                @endif

                @if($entRecord->treatment_plan)
                <div class="col-md-12 mb-3">
                    <h6>{{ __('Treatment Plan') }}</h6>
                    <p class="ms-3">{{ $entRecord->treatment_plan }}</p>
                </div>
                @endif

                @if($entRecord->medications)
                <div class="col-md-12 mb-3">
                    <h6>{{ __('Medications') }}</h6>
                    <p class="ms-3">{{ $entRecord->medications }}</p>
                </div>
                @endif
            </div>
        </div>


    <!-- Audiometry Tests -->
    @if($entRecord->audiometryTests->count() > 0)
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-volume-high me-2"></i>{{ __('Audiometry Tests') }}</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Test Type') }}</th>
                            <th>{{ __('Right Ear') }}</th>
                            <th>{{ __('Left Ear') }}</th>
                            <th>{{ __('Performed By') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($entRecord->audiometryTests as $test)
                        <tr>
                            <td>{{ $test->test_date->format('Y-m-d') }}</td>
                            <td>{{ $test->test_type_display }}</td>
                            <td>
                                @if($test->right_interpretation)
                                    <span class="badge {{ $test->right_interpretation === 'normal' ? 'bg-success' : 'bg-warning' }}">
                                        {{ $test->right_interpretation_display }}
                                    </span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($test->left_interpretation)
                                    <span class="badge {{ $test->left_interpretation === 'normal' ? 'bg-success' : 'bg-warning' }}">
                                        {{ $test->left_interpretation_display }}
                                    </span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>{{ $test->performer->full_name }}</td>
                            <td>
                                <a href="{{ route('ent.audiometry.show', $test) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-chart-line me-1"></i>{{ __('View Audiogram') }}
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Additional Information -->
    @if($entRecord->followup_date || $entRecord->notes)
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">{{ __('Additional Information') }}</h5>
        </div>
        <div class="card-body">
            @if($entRecord->followup_date)
            <p><strong>{{ __('Follow-up Date') }}:</strong> {{ $entRecord->followup_date->format('Y-m-d') }}</p>
            @endif

            @if($entRecord->notes)
            <p><strong>{{ __('Notes') }}:</strong><br>{{ $entRecord->notes }}</p>
            @endif
        </div>
    </div>
    @endif

    <!-- Action Buttons -->
    <div class="mb-3">
        <a href="{{ route('ent.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>{{ __('Back to List') }}
        </a>
        <form action="{{ route('ent.destroy', $entRecord) }}" method="POST" class="d-inline"
              onsubmit="return confirm('{{ __('Are you sure you want to delete this ENT record?') }}');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-trash me-1"></i>{{ __('Delete') }}
            </button>
        </form>
    </div>
</div>
@endsection
