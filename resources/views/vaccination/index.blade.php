@extends('layouts.app')

@section('title', __('Vaccination Management'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-syringe me-2 text-primary"></i>
                        {{ __('Vaccination Management') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Track and manage patient vaccination schedules') }}</p>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#enrollModal">
                        <i class="fas fa-user-plus me-1"></i>
                        {{ __('Add Patient') }}
                    </button>
                    @if(auth()->user()->isSuperAdmin() || auth()->user()->isClinicAdmin())
                    <a href="{{ route('vaccination.admin.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-cogs me-1"></i>
                        {{ __('Admin Panel') }}
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="row mb-3">
        <div class="col-md-5 col-lg-4">
            <form method="GET" action="{{ route('vaccination.index') }}" class="d-flex gap-2">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="{{ __('Search by name, ID...') }}" value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                    @if(request('search'))
                        <a href="{{ route('vaccination.index') }}" class="btn btn-outline-danger"><i class="fas fa-times"></i></a>
                    @endif
                </div>
            </form>
        </div>
        <div class="col-md-3 col-lg-2">
            <form method="GET" action="{{ route('vaccination.index') }}">
                @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">{{ __('All Statuses') }}</option>
                    <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>{{ __('Upcoming') }}</option>
                    <option value="missed" {{ request('status') == 'missed' ? 'selected' : '' }}>{{ __('Missed') }}</option>
                    <option value="delayed" {{ request('status') == 'delayed' ? 'selected' : '' }}>{{ __('Delayed') }}</option>
                    <option value="on_time" {{ request('status') == 'on_time' ? 'selected' : '' }}>{{ __('On Time') }}</option>
                </select>
            </form>
        </div>
    </div>

    <!-- Patients Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0">
                        <i class="fas fa-users me-2"></i>
                        {{ __('Patients with Vaccination Schedules') }} ({{ $patients->total() }})
                    </h6>
                </div>
                <div class="card-body p-0">
                    @if($patients->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('Patient ID') }}</th>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Age') }}</th>
                                        <th>{{ __('Schedule') }}</th>
                                        <th>{{ __('Progress') }}</th>
                                        <th>{{ __('Next Due') }}</th>
                                        <th class="text-end">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($patients as $patient)
                                    @php
                                        $total = $patient->vaccinations->count();
                                        $given = $patient->vaccinations->whereNotNull('given_date')->count();
                                        $missed = $patient->vaccinations->where('status', 'missed')->count();
                                        $pct = $total > 0 ? round(($given / $total) * 100) : 0;
                                        $nextDue = $patient->vaccinations->where('status', 'upcoming')->sortBy('scheduled_date')->first();
                                    @endphp
                                    <tr>
                                        <td><strong class="text-primary">{{ $patient->patient_id }}</strong></td>
                                        <td>
                                            <a href="{{ route('patients.show', $patient) }}" class="text-decoration-none fw-bold">{{ $patient->full_name }}</a>
                                        </td>
                                        <td><span class="badge bg-light text-dark">{{ $patient->age ?? '-' }}</span></td>
                                        <td>
                                            <span class="badge bg-info text-dark">{{ $patient->vaccinationSchedule->name ?? __('N/A') }}</span>
                                        </td>
                                        <td style="min-width: 160px;">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="progress flex-grow-1" style="height: 8px;">
                                                    <div class="progress-bar bg-success" style="width: {{ $pct }}%"></div>
                                                </div>
                                                <small class="text-muted">{{ $given }}/{{ $total }}</small>
                                            </div>
                                            @if($missed > 0)
                                                <small class="text-danger"><i class="fas fa-exclamation-triangle me-1"></i>{{ $missed }} {{ __('missed') }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($nextDue)
                                                <span class="badge bg-{{ $nextDue->scheduled_date->isPast() ? 'danger' : 'warning' }} text-dark">
                                                    {{ $nextDue->scheduled_date->format('M d, Y') }}
                                                </span>
                                            @else
                                                <span class="text-muted">{{ __('Complete') }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('vaccination.show', $patient) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-syringe me-1"></i>{{ __('View Card') }}
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer">{{ $patients->links() }}</div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-syringe fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('No Patients with Vaccination Schedules') }}</h5>
                            <p class="text-muted">{{ __('Click "Add Patient" to enroll a patient into a vaccination schedule.') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Enroll Patient Modal --}}
<div class="modal fade" id="enrollModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('vaccination.enroll') }}">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>{{ __('Add Patient to Vaccination') }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    {{-- Patient Smart Search --}}
                    <div class="mb-3 position-relative" id="enrollSearchWrapper">
                        <label class="form-label fw-bold">{{ __('Search Patient') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" id="enrollPatientSearch" class="form-control" placeholder="{{ __('Type name or patient ID...') }}" autocomplete="off">
                            <span class="input-group-text d-none" id="enrollSearchSpinner">
                                <span class="spinner-border spinner-border-sm text-primary" role="status"></span>
                            </span>
                        </div>
                        <input type="hidden" name="patient_id" id="enrollPatientId" required>
                        {{-- Dropdown Results --}}
                        <div id="enrollSearchResults" class="list-group position-absolute w-100 shadow border rounded-bottom" style="max-height:250px;overflow-y:auto;z-index:1055;display:none;top:100%;"></div>
                        {{-- Selected Patient Card --}}
                        <div id="enrollSelectedPatient" class="alert alert-success d-flex align-items-center mt-2 py-2 mb-0" style="display:none;">
                            <i class="fas fa-user-check me-2 text-success"></i>
                            <div class="flex-grow-1" id="enrollSelectedInfo"></div>
                            <button type="button" class="btn btn-sm btn-outline-danger ms-2" id="enrollClearBtn">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Schedule Selection --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ __('Vaccination Schedule') }}</label>
                        <select name="schedule_id" id="enrollScheduleId" class="form-select">
                            <option value="">{{ __('Auto-detect (based on clinic/country)') }}</option>
                            @foreach($schedules as $schedule)
                                <option value="{{ $schedule->id }}">
                                    {{ $schedule->name }} ({{ $schedule->country->name ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">{{ __('Leave as "Auto-detect" to use the clinic\'s default schedule.') }}</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-success" id="enrollSubmitBtn" disabled>
                        <i class="fas fa-check me-1"></i>{{ __('Enroll & Generate Schedule') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    var searchInput, patientIdInput, resultsDiv, selectedDiv, selectedInfo, submitBtn, spinner, clearBtn;
    var debounceTimer = null;

    document.getElementById('enrollModal').addEventListener('shown.bs.modal', function() {
        searchInput   = document.getElementById('enrollPatientSearch');
        patientIdInput = document.getElementById('enrollPatientId');
        resultsDiv    = document.getElementById('enrollSearchResults');
        selectedDiv   = document.getElementById('enrollSelectedPatient');
        selectedInfo  = document.getElementById('enrollSelectedInfo');
        submitBtn     = document.getElementById('enrollSubmitBtn');
        spinner       = document.getElementById('enrollSearchSpinner');
        clearBtn      = document.getElementById('enrollClearBtn');

        searchInput.focus();

        // Bind only once
        if (searchInput.dataset.bound) return;
        searchInput.dataset.bound = '1';

        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            var q = this.value.trim();
            if (q.length < 1) {
                resultsDiv.style.display = 'none';
                resultsDiv.innerHTML = '';
                return;
            }
            spinner.classList.remove('d-none');
            debounceTimer = setTimeout(function() { doSearch(q); }, 300);
        });

        searchInput.addEventListener('focus', function() {
            if (resultsDiv.innerHTML && this.value.trim().length >= 1) {
                resultsDiv.style.display = 'block';
            }
        });

        clearBtn.addEventListener('click', clearSelection);

        document.addEventListener('click', function(e) {
            if (resultsDiv && !resultsDiv.contains(e.target) && e.target !== searchInput) {
                resultsDiv.style.display = 'none';
            }
        });
    });

    document.getElementById('enrollModal').addEventListener('hidden.bs.modal', function() {
        clearSelection();
        var scheduleSelect = document.getElementById('enrollScheduleId');
        if (scheduleSelect) scheduleSelect.value = '';
    });

    function doSearch(q) {
        var url = '{{ route("vaccination.search-unenrolled") }}?search=' + encodeURIComponent(q);
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            spinner.classList.add('d-none');
            resultsDiv.innerHTML = '';

            if (!data.has_results) {
                resultsDiv.innerHTML =
                    '<div class="list-group-item text-muted text-center py-3">' +
                    '<i class="fas fa-user-slash me-2"></i>' +
                    (data.message || '{{ __("No unenrolled patients found") }}') +
                    '</div>';
                resultsDiv.style.display = 'block';
                return;
            }

            data.data.forEach(function(p) {
                var age = p.date_of_birth ? calcAge(p.date_of_birth) : '';
                var gIcon = p.gender === 'male' ? 'fa-mars text-primary' : (p.gender === 'female' ? 'fa-venus text-danger' : 'fa-user text-secondary');
                var el = document.createElement('a');
                el.href = '#';
                el.className = 'list-group-item list-group-item-action d-flex align-items-center py-2';
                el.innerHTML =
                    '<span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light border me-3" style="width:38px;height:38px;min-width:38px;">' +
                        '<i class="fas ' + gIcon + '"></i>' +
                    '</span>' +
                    '<div class="flex-grow-1 overflow-hidden">' +
                        '<div class="fw-semibold text-truncate">' + p.first_name + ' ' + p.last_name + '</div>' +
                        '<small class="text-muted">' +
                            '<i class="fas fa-id-badge me-1"></i>' + p.patient_id +
                            (age ? ' &nbsp;<i class="fas fa-birthday-cake me-1"></i>' + age : '') +
                            (p.date_of_birth ? ' &nbsp;<i class="fas fa-calendar-alt me-1"></i>' + p.date_of_birth : '') +
                        '</small>' +
                    '</div>' +
                    '<i class="fas fa-plus-circle text-success ms-2"></i>';
                el.addEventListener('click', function(e) {
                    e.preventDefault();
                    pickPatient(p);
                });
                resultsDiv.appendChild(el);
            });

            // Count footer
            var footer = document.createElement('div');
            footer.className = 'list-group-item text-center py-1 bg-light';
            footer.innerHTML = '<small class="text-muted">' + data.count + ' {{ __("patient(s) found") }}</small>';
            resultsDiv.appendChild(footer);

            resultsDiv.style.display = 'block';
        })
        .catch(function(err) {
            spinner.classList.add('d-none');
            console.error('Enroll search error:', err);
        });
    }

    function pickPatient(p) {
        var age = p.date_of_birth ? calcAge(p.date_of_birth) : '';
        patientIdInput.value = p.id;
        searchInput.value = '';
        searchInput.style.display = 'none';
        resultsDiv.style.display = 'none';
        resultsDiv.innerHTML = '';
        selectedInfo.innerHTML =
            '<strong>' + p.first_name + ' ' + p.last_name + '</strong> ' +
            '<span class="badge bg-primary ms-1">' + p.patient_id + '</span> ' +
            (age ? '<span class="badge bg-light text-dark ms-1">' + age + '</span> ' : '') +
            (p.date_of_birth ? '<small class="text-muted ms-2">DOB: ' + p.date_of_birth + '</small>' : '');
        selectedDiv.style.display = 'flex';
        submitBtn.disabled = false;
    }

    function clearSelection() {
        if (!patientIdInput) return;
        patientIdInput.value = '';
        if (selectedDiv) selectedDiv.style.display = 'none';
        if (selectedInfo) selectedInfo.innerHTML = '';
        if (submitBtn) submitBtn.disabled = true;
        if (searchInput) {
            searchInput.style.display = '';
            searchInput.value = '';
        }
        if (resultsDiv) {
            resultsDiv.style.display = 'none';
            resultsDiv.innerHTML = '';
        }
    }

    function calcAge(dob) {
        var b = new Date(dob), n = new Date();
        var y = n.getFullYear() - b.getFullYear();
        var m = n.getMonth() - b.getMonth();
        if (m < 0) { y--; m += 12; }
        if (y > 0) return y + 'y ' + m + 'm';
        return m + 'm';
    }
})();
</script>
@endpush
