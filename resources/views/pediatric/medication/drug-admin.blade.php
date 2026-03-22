@extends('layouts.app')

@section('title', __('Pediatric Drug Database'))

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-database me-2 text-primary"></i>
                        {{ __('Pediatric Drug Database') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Manage drugs, formulations, and dosage rules') }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('pediatric.medication.calculator') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-calculator me-1"></i> {{ __('Calculator') }}
                    </a>
                    <a href="{{ route('pediatric.medication.import') }}" class="btn btn-outline-success btn-sm">
                        <i class="fas fa-file-import me-1"></i> {{ __('Import') }}
                    </a>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addDrugModal">
                        <i class="fas fa-plus me-1"></i> {{ __('Add Drug') }}
                    </button>
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
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @forelse($drugs as $drug)
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">
                    <i class="fas fa-capsules me-2 text-primary"></i>
                    {{ $drug->generic_name }}
                    @if($drug->brand_name) <span class="text-muted fw-normal">({{ $drug->brand_name }})</span> @endif
                </h5>
                @if($drug->category) <span class="badge bg-secondary">{{ $drug->category }}</span> @endif
            </div>
            <form method="POST" action="{{ route('pediatric.medication.drug.destroy', $drug) }}" class="d-inline"
                  onsubmit="return confirm('Delete this drug and all its forms/rules?')">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger btn-sm"><i class="fas fa-trash"></i></button>
            </form>
        </div>
        <div class="card-body">
            @if($drug->description) <p class="text-muted small mb-3">{{ $drug->description }}</p> @endif

            <div class="row">
                <!-- Forms -->
                <div class="col-md-5">
                    <h6 class="fw-bold"><i class="fas fa-flask me-1"></i> {{ __('Formulations') }}</h6>
                    @if($drug->forms->count())
                    <ul class="list-group list-group-flush">
                        @foreach($drug->forms as $form)
                        <li class="list-group-item px-0 py-1 small">
                            <strong>{{ ucfirst($form->form) }}</strong> — {{ $form->concentration }}
                            <span class="text-muted">({{ $form->concentration_mg }}mg{{ $form->concentration_per_ml ? '/'.$form->concentration_per_ml.'ml' : '' }})</span>
                        </li>
                        @endforeach
                    </ul>
                    @else
                    <p class="text-muted small">{{ __('No forms yet.') }}</p>
                    @endif
                    <button class="btn btn-outline-primary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#addFormModal" onclick="document.getElementById('formDrugId').value={{ $drug->id }}">
                        <i class="fas fa-plus me-1"></i> {{ __('Add Form') }}
                    </button>
                </div>

                <!-- Rules -->
                <div class="col-md-7">
                    <h6 class="fw-bold"><i class="fas fa-ruler me-1"></i> {{ __('Dosage Rules') }}</h6>
                    @if($drug->dosageRules->count())
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0" style="font-size:0.8rem;">
                            <thead class="table-light">
                                <tr>
                                    <th>mg/kg min</th><th>mg/kg max</th><th>Max Daily</th>
                                    <th>Freq</th><th>Age Range</th><th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($drug->dosageRules as $rule)
                                <tr>
                                    <td>{{ $rule->mg_per_kg_min }}</td>
                                    <td>{{ $rule->mg_per_kg_max }}</td>
                                    <td>{{ $rule->max_daily_mg ?? '--' }}</td>
                                    <td>{{ $rule->frequency_per_day }}x/day</td>
                                    <td>
                                        @if($rule->min_age_months || $rule->max_age_months)
                                        {{ $rule->min_age_months ?? 0 }}–{{ $rule->max_age_months ?? '∞' }} mo
                                        @else -- @endif
                                    </td>
                                    <td>{{ $rule->notes ?? '--' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted small">{{ __('No rules yet.') }}</p>
                    @endif
                    <button class="btn btn-outline-primary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#addRuleModal" onclick="document.getElementById('ruleDrugId').value={{ $drug->id }}">
                        <i class="fas fa-plus me-1"></i> {{ __('Add Rule') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        {{ __('No drugs in the database yet.') }}
        <a href="{{ route('pediatric.medication.import') }}">{{ __('Import drugs') }}</a> {{ __('or add them manually.') }}
    </div>
    @endforelse
</div>

<!-- Add Drug Modal -->
<div class="modal fade" id="addDrugModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title"><i class="fas fa-plus me-2"></i>{{ __('Add Drug') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST" action="{{ route('pediatric.medication.drug.store') }}">@csrf
    <div class="modal-body">
        <div class="mb-3"><label class="form-label">{{ __('Generic Name') }} *</label><input type="text" name="generic_name" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">{{ __('Brand Name') }}</label><input type="text" name="brand_name" class="form-control"></div>
        <div class="mb-3"><label class="form-label">{{ __('Category') }}</label><input type="text" name="category" class="form-control" placeholder="e.g. Analgesic, Antibiotic"></div>
        <div class="mb-3"><label class="form-label">{{ __('Description') }}</label><textarea name="description" class="form-control" rows="2"></textarea></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button><button type="submit" class="btn btn-primary">{{ __('Save Drug') }}</button></div>
    </form>
</div></div></div>

<!-- Add Form Modal -->
<div class="modal fade" id="addFormModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title"><i class="fas fa-flask me-2"></i>{{ __('Add Formulation') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST" action="{{ route('pediatric.medication.drug-form.store') }}">@csrf
    <input type="hidden" name="drug_id" id="formDrugId">
    <div class="modal-body">
        <div class="mb-3"><label class="form-label">{{ __('Form') }} *</label>
            <select name="form" class="form-select" required>
                <option value="syrup">Syrup</option><option value="suspension">Suspension</option>
                <option value="tablet">Tablet</option><option value="drops">Drops</option>
                <option value="injection">Injection</option><option value="suppository">Suppository</option>
            </select>
        </div>
        <div class="mb-3"><label class="form-label">{{ __('Concentration Label') }} *</label><input type="text" name="concentration" class="form-control" required placeholder="e.g. 120mg/5ml"></div>
        <div class="mb-3"><label class="form-label">{{ __('Concentration (mg)') }} *</label><input type="number" step="0.01" name="concentration_mg" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">{{ __('Per ml') }}</label><input type="number" step="0.01" name="concentration_per_ml" class="form-control" placeholder="e.g. 5 for 120mg/5ml"></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button><button type="submit" class="btn btn-primary">{{ __('Save Form') }}</button></div>
    </form>
</div></div></div>

<!-- Add Rule Modal -->
<div class="modal fade" id="addRuleModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title"><i class="fas fa-ruler me-2"></i>{{ __('Add Dosage Rule') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST" action="{{ route('pediatric.medication.dosage-rule.store') }}">@csrf
    <input type="hidden" name="drug_id" id="ruleDrugId">
    <div class="modal-body">
        <div class="row g-3">
            <div class="col-md-4"><label class="form-label">{{ __('mg/kg min') }} *</label><input type="number" step="0.01" name="mg_per_kg_min" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label">{{ __('mg/kg max') }} *</label><input type="number" step="0.01" name="mg_per_kg_max" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label">{{ __('Max daily (mg)') }}</label><input type="number" step="0.01" name="max_daily_mg" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">{{ __('Frequency/day') }} *</label><input type="number" name="frequency_per_day" class="form-control" required value="3"></div>
            <div class="col-md-4"><label class="form-label">{{ __('Every N hours') }}</label><input type="number" name="frequency_hours" class="form-control" placeholder="e.g. 8"></div>
            <div class="col-md-4"></div>
            <div class="col-md-3"><label class="form-label">{{ __('Min age (mo)') }}</label><input type="number" name="min_age_months" class="form-control"></div>
            <div class="col-md-3"><label class="form-label">{{ __('Max age (mo)') }}</label><input type="number" name="max_age_months" class="form-control"></div>
            <div class="col-md-3"><label class="form-label">{{ __('Min weight (kg)') }}</label><input type="number" step="0.1" name="min_weight_kg" class="form-control"></div>
            <div class="col-md-3"><label class="form-label">{{ __('Max weight (kg)') }}</label><input type="number" step="0.1" name="max_weight_kg" class="form-control"></div>
            <div class="col-12"><label class="form-label">{{ __('Notes') }}</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
        </div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button><button type="submit" class="btn btn-primary">{{ __('Save Rule') }}</button></div>
    </form>
</div></div></div>
@endsection

