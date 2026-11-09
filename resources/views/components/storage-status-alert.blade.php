{{--
    Shared storage status alert + progress bar.
    Single source of truth for the 3-state visual treatment so the Master
    (admin-facing) and Tenant (clinic-facing) dashboards stay in sync:
      - < 90%         : clean/default (success) styling
      - 90% - 99.99%  : amber warning banner/border
      - >= 100%       : urgent red critical banner (flashing)

    Expects: $storageInfo (array from StorageQuotaService::getStorageInfo())
--}}
@php
    $pct = min($storageInfo['percentage_used'], 100);

    if ($storageInfo['critical']) {
        $state = 'critical';
        $barColor = 'bg-danger';
        $badgeClass = 'bg-danger';
    } elseif ($storageInfo['warning']) {
        $state = 'warning';
        $barColor = 'bg-warning';
        $badgeClass = 'bg-warning text-dark';
    } else {
        $state = 'ok';
        $barColor = 'bg-success';
        $badgeClass = 'bg-success';
    }
@endphp

<div class="storage-status-alert storage-status-alert--{{ $state }}" data-storage-state="{{ $state }}" data-storage-percentage="{{ $storageInfo['percentage_used'] }}">
    @if($state === 'critical')
        {{-- Urgent red critical banner. Flashes briefly on load to draw the
             eye, then settles into a solid high-contrast alert (persistent
             flashing is an accessibility/annoyance problem, so the CSS
             animation below runs a handful of times and stops). --}}
        <div class="alert alert-danger border border-danger border-2 storage-alert-flash py-3 mb-3" role="alert">
            <div class="d-flex align-items-start">
                <i class="fas fa-exclamation-triangle fa-lg me-2 mt-1"></i>
                <div>
                    <strong>{{ __('⚠️ Storage Limit Exceeded') }}:</strong>
                    {{ __('This clinic has reached its maximum storage capacity. To continue uploading, please contact the platform Administrator to upgrade your plan or delete unnecessary files.') }}
                </div>
            </div>
        </div>
    @elseif($state === 'warning')
        {{-- Amber warning banner for the 90-99% approach-to-limit zone. --}}
        <div class="alert alert-warning border border-warning py-2 mb-3" role="alert">
            <i class="fas fa-exclamation-circle me-1"></i>
            <strong>{{ __('Warning') }}:</strong>
            {{ __('Storage usage is at :pct%. You are nearing your storage capacity - uploads will be blocked once you reach 100%.', ['pct' => $storageInfo['percentage_used']]) }}
        </div>
    @endif

    <div class="progress mb-2" style="height: 22px;">
        <div class="progress-bar {{ $barColor }} progress-bar-striped {{ $state === 'critical' ? 'progress-bar-animated' : '' }}"
             role="progressbar"
             style="width: {{ $pct }}%"
             aria-valuenow="{{ $pct }}"
             aria-valuemin="0"
             aria-valuemax="100">
            {{ $storageInfo['used_gb'] }} GB / {{ $storageInfo['limit_gb'] }} GB ({{ $storageInfo['percentage_used'] }}%)
        </div>
    </div>
</div>

@once
    @push('styles')
    <style>
        /* A few quick flashes to grab attention without looping forever. */
        @keyframes storageAlertFlash {
            0%, 100% { background-color: inherit; }
            50% { background-color: #f8d7da; }
        }
        .storage-alert-flash {
            animation: storageAlertFlash 0.9s ease-in-out 3;
        }
    </style>
    @endpush
@endonce
