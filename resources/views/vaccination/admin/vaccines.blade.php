@extends('layouts.app')

@section('title', __('Manage Vaccines'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-vials me-2 text-primary"></i>
                        {{ __('Global Vaccine Registry') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Manage vaccines and their translations') }}</p>
                </div>
                <a href="{{ route('vaccination.admin.index') }}" class="btn btn-outline-dark">
                    <i class="fas fa-arrow-left me-1"></i>{{ __('Back') }}
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="row g-4">
        <!-- Vaccines Table -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0"><i class="fas fa-list me-2"></i>{{ __('Vaccines') }} ({{ $vaccines->count() }})</h6>
                </div>
                <div class="card-body p-0">
                    @if($vaccines->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('Code') }}</th>
                                    <th>{{ __('Global Name') }}</th>
                                    <th>{{ __('Arabic') }}</th>
                                    <th>{{ __('Kurdish') }}</th>
                                    <th>{{ __('Description') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($vaccines as $vaccine)
                                <tr>
                                    <td><span class="badge bg-primary">{{ $vaccine->code }}</span></td>
                                    <td><strong>{{ $vaccine->global_name }}</strong></td>
                                    <td>{{ $vaccine->translations->where('language_code', 'ar')->first()->name ?? '—' }}</td>
                                    <td>{{ $vaccine->translations->where('language_code', 'ku')->first()->name ?? '—' }}</td>
                                    <td><small class="text-muted">{{ Str::limit($vaccine->description, 50) }}</small></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="fas fa-vials fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">{{ __('No vaccines registered. Add your first vaccine.') }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Add/Edit Vaccine Form -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0"><i class="fas fa-plus-circle me-2 text-success"></i>{{ __('Add / Edit Vaccine') }}</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('vaccination.admin.vaccines.store') }}">
                        @csrf
                        <div class="row g-2 mb-2">
                            <div class="col-4">
                                <label class="form-label small">{{ __('Code') }} <span class="text-danger">*</span></label>
                                <input type="text" name="code" class="form-control form-control-sm" placeholder="BCG" maxlength="20" required>
                            </div>
                            <div class="col-8">
                                <label class="form-label small">{{ __('Global Name (English)') }} <span class="text-danger">*</span></label>
                                <input type="text" name="global_name" class="form-control form-control-sm" placeholder="{{ __('Bacillus Calmette-Guérin') }}" required>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">{{ __('Description') }}</label>
                            <textarea name="description" class="form-control form-control-sm" rows="2" placeholder="{{ __('Optional description...') }}"></textarea>
                        </div>

                        <hr class="my-2">
                        <p class="small text-muted mb-2"><i class="fas fa-language me-1"></i>{{ __('Translations') }}</p>

                        <div class="mb-2">
                            <label class="form-label small">{{ __('Arabic Name') }}</label>
                            <input type="text" name="translations[ar][name]" class="form-control form-control-sm" dir="rtl" placeholder="لقاح السل">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">{{ __('Arabic Description') }}</label>
                            <input type="text" name="translations[ar][description]" class="form-control form-control-sm" dir="rtl">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">{{ __('Kurdish Name') }}</label>
                            <input type="text" name="translations[ku][name]" class="form-control form-control-sm" dir="rtl" placeholder="ڤاکسینی BCG">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">{{ __('Kurdish Description') }}</label>
                            <input type="text" name="translations[ku][description]" class="form-control form-control-sm" dir="rtl">
                        </div>

                        <button type="submit" class="btn btn-success btn-sm w-100 mt-2">
                            <i class="fas fa-save me-1"></i>{{ __('Save Vaccine') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

