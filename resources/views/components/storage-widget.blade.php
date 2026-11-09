{{-- Storage Quota Widget (Tenant/clinic-facing) --}}
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
        @include('components.storage-status-alert', ['storageInfo' => $storageInfo])

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

@if($storageInfo['critical'])
    @push('scripts')
    <script>
        // Storage capacity is at/over 100% for this clinic: disable upload
        // affordances on this page so staff can't start an upload that the
        // CheckStorageQuota middleware will reject anyway (413). Targets:
        //   - any <input type="file">
        //   - any element explicitly opted in via [data-requires-storage]
        //     (e.g. "Upload" buttons that open a modal/file picker)
        // Native HTML5 "disabled" also stops the file picker dialog itself
        // from opening, not just form submission.
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('input[type="file"], [data-requires-storage]').forEach(function (el) {
                el.disabled = true;
                el.classList.add('disabled');
                el.setAttribute('title', @json(__('Storage limit exceeded - contact your administrator to upgrade or free up space.')));
            });
        });
    </script>
    @endpush
@endif
@endif
