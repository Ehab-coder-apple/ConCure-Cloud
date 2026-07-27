{{--
    Bridge partial for ConCure realtime messaging.

    Loaded from layouts/app.blade.php for every authenticated user. Exposes
    the Pusher key, clinic_id and current user_id to the front-end JS so the
    listener can subscribe to private-clinic.{cid}.user.{uid}. Also loads the
    "Send to Doctor" sender helper used from the patient show page.

    Driver-aware: only emits the pusher-js loader and Echo config when a
    Pusher key is actually configured. With BROADCAST_DRIVER=log (the default
    in .env.example), this partial degrades to a no-op and the existing 20s
    unread-badge polling continues to work unchanged.
--}}
@auth
    @php
        $pusherKey = config('broadcasting.connections.pusher.key') ?: env('PUSHER_APP_KEY');
        $pusherCluster = config('broadcasting.connections.pusher.options.cluster') ?: env('PUSHER_APP_CLUSTER', 'mt1');
        $broadcastDriver = config('broadcasting.default');
        $realtimeEnabled = $broadcastDriver === 'pusher' && !empty($pusherKey);
    @endphp

    <script>
        window.ConCureRealtime = {
            enabled: {!! $realtimeEnabled ? 'true' : 'false' !!},
            pusherKey: @json($pusherKey),
            pusherCluster: @json($pusherCluster),
            authEndpoint: '/broadcasting/auth',
            csrfToken: @json(csrf_token()),
            clinicId: @json((int) (auth()->user()->clinic_id ?? 0)),
            userId: @json((int) (auth()->id() ?? 0)),
            debug: {!! config('app.debug') ? 'true' : 'false' !!}
        };
    </script>

    {{-- Sender helper: tiny, no Pusher dependency, useful regardless of driver. --}}
    <script src="{{ asset('js/send-to-doctor.js') }}?v={{ filemtime(public_path('js/send-to-doctor.js')) }}" defer></script>

    @if ($realtimeEnabled)
        <script src="https://js.pusher.com/8.4/pusher.min.js" defer></script>
        <script src="{{ asset('js/messages-realtime.js') }}?v={{ filemtime(public_path('js/messages-realtime.js')) }}" defer></script>
    @endif
@endauth
