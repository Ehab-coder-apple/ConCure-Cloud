@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-3">Record Operation for Case #{{ $surgicalCase->id }}</h1>

    <p><strong>Patient:</strong> {{ $surgicalCase->patient->first_name }} {{ $surgicalCase->patient->last_name }}</p>
    <p><strong>Planned Procedure:</strong> {{ $surgicalCase->planned_procedure }}</p>

    <form method="POST" action="{{ route('surgery.operations.store', $surgicalCase) }}">
        @csrf

        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="operation_date" class="form-label">Operation Date & Time</label>
                <input type="datetime-local" name="operation_date" id="operation_date" class="form-control" value="{{ old('operation_date') }}">
            </div>
            <div class="col-md-4 mb-3">
                <label for="theatre" class="form-label">Theatre</label>
                <input type="text" name="theatre" id="theatre" class="form-control" value="{{ old('theatre') }}">
            </div>
            <div class="col-md-2 mb-3">
                <label for="asa_class" class="form-label">ASA Class</label>
                <input type="text" name="asa_class" id="asa_class" class="form-control" value="{{ old('asa_class') }}">
            </div>
            <div class="col-md-2 mb-3">
                <label for="anesthesia_type" class="form-label">Anesthesia</label>
                <input type="text" name="anesthesia_type" id="anesthesia_type" class="form-control" value="{{ old('anesthesia_type') }}">
            </div>
        </div>

        <hr>
        <h4>Pre‑operation Assessment</h4>
        <div class="mb-3">
            <label for="preop_vitals" class="form-label">Vitals / Risk Factors</label>
            <textarea name="preop_vitals" id="preop_vitals" rows="3" class="form-control">{{ old('preop_vitals') }}</textarea>
        </div>
        <div class="mb-3">
            <label for="preop_notes" class="form-label">Additional Pre‑op Notes</label>
            <textarea name="preop_notes" id="preop_notes" rows="3" class="form-control">{{ old('preop_notes') }}</textarea>
        </div>

        <hr>
        <h4>Operative Note</h4>
        <div class="mb-3">
            <label for="operative_note" class="form-label">Operative Note</label>
            <textarea name="operative_note" id="operative_note" rows="6" class="form-control">{{ old('operative_note') }}</textarea>
        </div>

        <hr>
        <h4>Post‑operation Assessment</h4>
        <div class="mb-3">
            <label for="postop_status" class="form-label">Immediate Post‑op Status</label>
            <textarea name="postop_status" id="postop_status" rows="3" class="form-control">{{ old('postop_status') }}</textarea>
        </div>
        <div class="mb-3">
            <label for="postop_plan" class="form-label">Post‑op Orders / Plan</label>
            <textarea name="postop_plan" id="postop_plan" rows="3" class="form-control">{{ old('postop_plan') }}</textarea>
        </div>

	        @include('surgery.partials.wound-assessment-fields')

	        <div class="row mt-4">
	            <div class="col-md-4 mb-3">
	                <label for="estimated_blood_loss_ml" class="form-label">Estimated Blood Loss (ml)</label>
	                <input type="number" name="estimated_blood_loss_ml" id="estimated_blood_loss_ml" class="form-control" value="{{ old('estimated_blood_loss_ml') }}" min="0">
	            </div>
	            <div class="col-md-8 mb-3">
	                <label for="complications" class="form-label">Complications</label>
	                <textarea name="complications" id="complications" rows="2" class="form-control">{{ old('complications') }}</textarea>
	            </div>
	        </div>

        <button type="submit" class="btn btn-primary">Save Operation</button>
        <a href="{{ route('surgery.show', $surgicalCase) }}" class="btn btn-link">Cancel</a>
    </form>
</div>

@include('surgery.partials.wifi-calculator-script')
@endsection
