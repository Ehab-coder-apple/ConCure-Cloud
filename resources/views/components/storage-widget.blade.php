{{-- Storage Quota Widget --}}
@php
    $storageInfo = $storageInfo ?? null;
    if (!$storageInfo && auth()->check() && auth()->user()->clinic_id) {
        $storageInfo = app(\App\Services\StorageQuotaService::class)->getStorageInfo(auth()->user()->clinic_id);
    }
@endphp

@if($storageInfo)
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-hdd me-2"></i>{{ __('Storage Usage') }}
        </h6>
        <span class="badge {{ $storageInfo['critical'] ? 'bg-danger' : ($storageInfo['warning'] ? 'bg-warning text-dark' : 'bg-success') }}">
            {{ $storageInfo['percentage_used'] }}%
        </span>
    </div>
    <div class="card-body">
        {{-- Warning/Critical Alerts --}}
        @if($storageInfo['critical'])
        <div class="alert alert-danger py-2 mb-3">
            <i class="fas fa-exclamation-triangle me-1"></i>
            <strong>{{ __('Critical') }}:</strong> {{ __('Storage is almost full! Please contact your administrator.') }}
        </div>
        @elseif($storageInfo['warning'])
        <div class="alert alert-warning py-2 mb-3">
            <i class="fas fa-exclamation-circle me-1"></i>
            <strong>{{ __('Warning') }}:</strong> {{ __('Storage usage is above 80%. Consider freeing up space.') }}
        </div>
        @endif

        {{-- Progress Bar --}}
        @php
            $barColor = $storageInfo['critical'] ? 'bg-danger' : ($storageInfo['warning'] ? 'bg-warning' : 'bg-success');
            $pct = min($storageInfo['percentage_used'], 100);
        @endphp
        <div class="progress mb-3" style="height: 20px;">
            <div class="progress-bar {{ $barColor }} progress-bar-striped {{ $storageInfo['critical'] ? 'progress-bar-animated' : '' }}"
                 role="progressbar"
                 style="width: {{ $pct }}%"
                 aria-valuenow="{{ $pct }}"
                 aria-valuemin="0"
                 aria-valuemax="100">
                {{ $storageInfo['percentage_used'] }}%
            </div>
        </div>

        {{-- Storage Details --}}
        <div class="row text-center">
            <div class="col-4">
                <div class="text-muted small">{{ __('Used') }}</div>
                <div class="font-weight-bold">{{ $storageInfo['used_gb'] }} GB</div>
            </div>
            <div class="col-4">
                <div class="text-muted small">{{ __('Remaining') }}</div>
                <div class="font-weight-bold text-success">{{ $storageInfo['remaining_gb'] }} GB</div>
            </div>
            <div class="col-4">
                <div class="text-muted small">{{ __('Total') }}</div>
                <div class="font-weight-bold">{{ $storageInfo['limit_gb'] }} GB</div>
            </div>
        </div>
    </div>
</div>
@endif

