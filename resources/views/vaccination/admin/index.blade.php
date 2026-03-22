@extends('layouts.app')

@section('title', __('Vaccination Admin'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-cogs me-2 text-primary"></i>
                        {{ __('Vaccination Admin Panel') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Manage countries, schedules, and vaccines') }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('vaccination.admin.vaccines') }}" class="btn btn-outline-primary">
                        <i class="fas fa-vials me-1"></i>{{ __('Manage Vaccines') }}
                    </a>
                    <a href="{{ route('vaccination.index') }}" class="btn btn-outline-dark">
                        <i class="fas fa-arrow-left me-1"></i>{{ __('Back') }}
                    </a>
                </div>
            </div>
        </div>
    </div>



    <div class="row g-4">
        <!-- Left: Countries & Schedules -->
        <div class="col-lg-8">
            @foreach($countries as $country)
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <span class="me-2" style="font-size: 1.4em;">{{ $country->flag_emoji }}</span>
                        {{ $country->name }} <span class="badge bg-secondary ms-1">{{ $country->iso_code }}</span>
                    </h6>
                    <form method="POST" action="{{ route('vaccination.admin.country.destroy', $country) }}" class="d-inline"
                          onsubmit="return confirm('{{ __('Are you sure you want to delete :name? This cannot be undone.', ['name' => $country->name]) }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('Delete Country') }}">
                            <i class="fas fa-trash-alt me-1"></i>{{ __('Delete') }}
                        </button>
                    </form>
                </div>
                <div class="card-body">
                    @if($country->vaccinationSchedules->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('Schedule Name') }}</th>
                                        <th>{{ __('Version') }}</th>
                                        <th>{{ __('Items') }}</th>
                                        <th>{{ __('Default') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th class="text-end">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($country->vaccinationSchedules as $schedule)
                                    <tr>
                                        <td><strong>{{ $schedule->name }}</strong></td>
                                        <td><span class="badge bg-light text-dark">v{{ $schedule->version ?? '1.0' }}</span></td>
                                        <td><span class="badge bg-info text-dark">{{ $schedule->items_count }} {{ __('vaccines') }}</span></td>
                                        <td>
                                            @if($schedule->is_default)
                                                <span class="badge bg-success"><i class="fas fa-check"></i> {{ __('Default') }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $schedule->is_active ? 'success' : 'secondary' }}">
                                                {{ $schedule->is_active ? __('Active') : __('Inactive') }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('vaccination.admin.schedule.show', $schedule) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye me-1"></i>{{ __('Manage') }}
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0"><i class="fas fa-info-circle me-1"></i>{{ __('No schedules yet. Create one or import from JSON.') }}</p>
                    @endif
                </div>
            </div>
            @endforeach

            @if($countries->isEmpty())
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fas fa-globe fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">{{ __('No Countries Configured') }}</h5>
                    <p class="text-muted">{{ __('Add a country to get started.') }}</p>
                </div>
            </div>
            @endif
        </div>

        <!-- Right: Quick Actions -->
        <div class="col-lg-4">
            <!-- Add Country -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0"><i class="fas fa-plus-circle me-2 text-success"></i>{{ __('Add Country') }}</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('vaccination.admin.country.store') }}">
                        @csrf
                        <div class="mb-2">
                            <input type="text" name="name" class="form-control form-control-sm" placeholder="{{ __('Country name') }}" required>
                        </div>
                        <div class="mb-2">
                            <input type="text" name="iso_code" class="form-control form-control-sm" placeholder="{{ __('ISO code (e.g. IQ, EG)') }}" maxlength="5" required>
                        </div>
                        <div class="mb-2">
                            <input type="text" name="default_language" class="form-control form-control-sm" placeholder="{{ __('Default language (e.g. ar)') }}">
                        </div>
                        <button type="submit" class="btn btn-success btn-sm w-100"><i class="fas fa-plus me-1"></i>{{ __('Add') }}</button>
                    </form>
                </div>
            </div>

            <!-- Add Schedule -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0"><i class="fas fa-calendar-plus me-2 text-primary"></i>{{ __('Create Schedule') }}</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('vaccination.admin.schedule.store') }}">
                        @csrf
                        <div class="mb-2">
                            <select name="country_id" class="form-select form-select-sm" required>
                                <option value="">{{ __('Select Country') }}</option>
                                @foreach($countries as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <input type="text" name="name" class="form-control form-control-sm" placeholder="{{ __('Schedule name') }}" required>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <input type="text" name="version" class="form-control form-control-sm" placeholder="{{ __('Version') }}" value="1.0">
                            </div>
                            <div class="col-6">
                                <div class="form-check mt-2">
                                    <input type="checkbox" name="is_default" value="1" class="form-check-input" id="schedIsDefault">
                                    <label class="form-check-label small" for="schedIsDefault">{{ __('Default') }}</label>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-plus me-1"></i>{{ __('Create') }}</button>
                    </form>
                </div>
            </div>

            <!-- Import JSON -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0"><i class="fas fa-file-import me-2 text-warning"></i>{{ __('Import Schedule (JSON)') }}</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('vaccination.admin.import') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-2">
                            <input type="file" name="json_file" class="form-control form-control-sm" accept=".json,.txt" required>
                        </div>
                        <small class="text-muted d-block mb-2">{{ __('Upload a JSON file with country schedule data.') }}</small>
                        <button type="submit" class="btn btn-warning btn-sm w-100"><i class="fas fa-upload me-1"></i>{{ __('Import') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

