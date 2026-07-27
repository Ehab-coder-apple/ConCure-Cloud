/**
 * ConCure realtime message listener.
 *
 * Subscribes to private-clinic.{clinicId}.user.{userId} and reacts to
 * `message.received` broadcasts:
 *   - always refresh the sidebar unread badge (cheap)
 *   - if the message carries an *urgent* transfer, show a Bootstrap toast
 *     with quick actions (Open chart / New tab / Dismiss).
 *
 * Degrades gracefully: if Pusher isn't configured or fails to connect, the
 * existing 20s polling on the badge keeps everything working — just without
 * realtime toasts.
 */
(function () {
    'use strict';

    var cfg = window.ConCureRealtime || null;
    if (!cfg || !cfg.pusherKey || !cfg.clinicId || !cfg.userId) {
        return; // silently disabled
    }
    if (typeof Pusher === 'undefined') {
        console.warn('[ConCureRealtime] pusher-js not loaded; realtime disabled.');
        return;
    }

    Pusher.logToConsole = !!cfg.debug;

    var pusher;
    try {
        pusher = new Pusher(cfg.pusherKey, {
            cluster: cfg.pusherCluster || 'mt1',
            forceTLS: true,
            authEndpoint: cfg.authEndpoint || '/broadcasting/auth',
            auth: {
                headers: {
                    'X-CSRF-TOKEN': cfg.csrfToken || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            },
        });
    } catch (e) {
        console.warn('[ConCureRealtime] Pusher init failed', e);
        return;
    }

    var channelName = 'private-clinic.' + cfg.clinicId + '.user.' + cfg.userId;
    var channel = pusher.subscribe(channelName);

    channel.bind('pusher:subscription_error', function (status) {
        console.warn('[ConCureRealtime] subscription error', status);
    });

    channel.bind('message.received', function (payload) {
        try { if (typeof window.refreshSidebarUnread === 'function') window.refreshSidebarUnread(); } catch (e) {}

        var t = payload && payload.transfer;
        if (t && t.priority === 'urgent') {
            renderUrgentToast(payload);
        }
    });

    function escapeHtml(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    function ensureToastContainer() {
        var c = document.getElementById('concure-toast-container');
        if (c) return c;
        c = document.createElement('div');
        c.id = 'concure-toast-container';
        c.className = 'toast-container position-fixed top-0 end-0 p-3';
        c.style.zIndex = '1090';
        document.body.appendChild(c);
        return c;
    }

    function renderUrgentToast(p) {
        var container = ensureToastContainer();
        var sender = escapeHtml(p.sender_name || 'A colleague');
        var patient = escapeHtml(p.patient_name || 'a patient');
        var note = escapeHtml((p.transfer && p.transfer.note) || '');
        var url = p.patient_url || '#';
        var transferId = p.transfer && p.transfer.id;

        var el = document.createElement('div');
        el.className = 'toast border-danger';
        el.setAttribute('role', 'alert');
        el.setAttribute('aria-live', 'assertive');
        el.setAttribute('data-bs-autohide', 'false');
        el.innerHTML =
            '<div class="toast-header bg-danger text-white">' +
                '<i class="fas fa-user-md me-2"></i>' +
                '<strong class="me-auto">Urgent: Patient handoff</strong>' +
                '<small>now</small>' +
                '<button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="toast" aria-label="Close"></button>' +
            '</div>' +
            '<div class="toast-body">' +
                '<div class="mb-2"><strong>' + sender + '</strong> sent you <strong>' + patient + '</strong>.</div>' +
                (note ? '<div class="small text-muted mb-2">"' + note + '"</div>' : '') +
                '<div class="d-flex gap-2 flex-wrap">' +
                    '<a class="btn btn-sm btn-danger" href="' + escapeHtml(url) + '" data-act="open">Open chart</a>' +
                    '<a class="btn btn-sm btn-outline-secondary" href="' + escapeHtml(url) + '" target="_blank" rel="noopener" data-act="newtab">Open in new tab</a>' +
                    '<button type="button" class="btn btn-sm btn-outline-secondary" data-act="dismiss">Dismiss</button>' +
                '</div>' +
            '</div>';
        container.appendChild(el);

        var toast;
        try { toast = new bootstrap.Toast(el, { autohide: false }); toast.show(); } catch (e) {}

        var dismissBtn = el.querySelector('[data-act="dismiss"]');
        var openBtn = el.querySelector('[data-act="open"]');
        var ackOnce = false;
        var ack = function () {
            if (ackOnce || !transferId) return; ackOnce = true;
            fetch('/messages/transfers/' + transferId + '/action', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': cfg.csrfToken || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ action: 'acknowledge' }),
            }).then(function () {
                try { if (typeof window.refreshSidebarUnread === 'function') window.refreshSidebarUnread(); } catch (e) {}
            }).catch(function () {});
        };
        if (dismissBtn) dismissBtn.addEventListener('click', function () { ack(); try { toast && toast.hide(); } catch (e) {} });
        if (openBtn) openBtn.addEventListener('click', ack);
        el.addEventListener('hidden.bs.toast', function () { try { el.remove(); } catch (e) {} });

        try {
            var beep = new Audio('data:audio/wav;base64,UklGRl9vT19XQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQAAAAA=');
            beep.volume = 0.3; beep.play().catch(function () {});
        } catch (e) {}
    }
})();
