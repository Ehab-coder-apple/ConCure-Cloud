/**
 * ConCure "Send to Doctor" helper.
 *
 * Exposed as window.ConCureSendToDoctor.send({ patientId, patientName }).
 * Opens a Bootstrap modal, lets the sender pick a doctor in the same clinic
 * and add an optional note, then:
 *   1) POSTs /messages/conversations to create (or join) a direct conversation
 *      with the chosen doctor.
 *   2) POSTs /messages/send with priority=urgent + transfer_type=patient_file.
 * The server-side broadcast then makes the doctor's browser show the toast.
 */
(function () {
    'use strict';

    var cfg = window.ConCureRealtime || {};
    var csrf = function () {
        return cfg.csrfToken || (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
    };

    function postJson(url, body) {
        return fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(body || {}),
        }).then(function (r) { return r.json().then(function (j) { return { ok: r.ok, body: j }; }); });
    }

    function getJson(url) {
        return fetch(url, {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        }).then(function (r) { return r.json(); });
    }

    function escapeHtml(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    function buildModal() {
        var existing = document.getElementById('concureSendToDoctorModal');
        if (existing) existing.remove();
        var html =
            '<div class="modal fade" id="concureSendToDoctorModal" tabindex="-1" aria-hidden="true">' +
              '<div class="modal-dialog modal-dialog-centered">' +
                '<div class="modal-content">' +
                  '<div class="modal-header bg-primary text-white">' +
                    '<h5 class="modal-title"><i class="fas fa-user-md me-2"></i>Send to Doctor</h5>' +
                    '<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>' +
                  '</div>' +
                  '<div class="modal-body">' +
                    '<div class="mb-2 small text-muted" data-role="patient-summary"></div>' +
                    '<div class="mb-3"><label class="form-label">Doctor</label>' +
                      '<select class="form-select" data-role="doctor-select"><option value="">Loading…</option></select>' +
                    '</div>' +
                    '<div class="mb-3"><label class="form-label">Note (optional)</label>' +
                      '<textarea class="form-control" rows="3" maxlength="500" data-role="note" placeholder="Why are you sending this now?"></textarea>' +
                    '</div>' +
                    '<div class="alert alert-danger small mb-0 d-none" data-role="error"></div>' +
                  '</div>' +
                  '<div class="modal-footer">' +
                    '<button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>' +
                    '<button type="button" class="btn btn-primary" data-role="submit">' +
                      '<i class="fas fa-paper-plane me-1"></i>Send urgent</button>' +
                  '</div>' +
                '</div>' +
              '</div>' +
            '</div>';
        var wrap = document.createElement('div');
        wrap.innerHTML = html;
        document.body.appendChild(wrap.firstChild);
        return document.getElementById('concureSendToDoctorModal');
    }

    function showError(modalEl, msg) {
        var box = modalEl.querySelector('[data-role="error"]');
        if (!box) return;
        box.textContent = msg || 'Something went wrong.';
        box.classList.remove('d-none');
    }

    // Event delegation: any element with [data-concure-send-to-doctor] triggers the flow.
    function bindDelegation() {
        document.addEventListener('click', function (e) {
            var btn = e.target.closest && e.target.closest('[data-concure-send-to-doctor]');
            if (!btn) return;
            e.preventDefault();
            window.ConCureSendToDoctor.send({
                patientId: parseInt(btn.getAttribute('data-patient-id'), 10),
                patientName: btn.getAttribute('data-patient-name') || '',
            });
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindDelegation);
    } else {
        bindDelegation();
    }

    window.ConCureSendToDoctor = {
        send: function (opts) {
            opts = opts || {};
            var patientId = parseInt(opts.patientId, 10);
            if (!patientId) { alert('Missing patient id'); return; }

            var modalEl = buildModal();
            modalEl.querySelector('[data-role="patient-summary"]').textContent =
                'Patient: ' + (opts.patientName || ('#' + patientId));
            var sel = modalEl.querySelector('[data-role="doctor-select"]');
            var modal = new bootstrap.Modal(modalEl);
            modal.show();

            getJson('/messages/recipients?role=doctor').then(function (data) {
                var list = (data && data.recipients) || [];
                if (!list.length) {
                    sel.innerHTML = '<option value="">No doctors found in your clinic</option>';
                    return;
                }
                sel.innerHTML = '<option value="">— Select a doctor —</option>' +
                    list.map(function (r) { return '<option value="' + r.id + '">' + escapeHtml(r.name) + '</option>'; }).join('');
            }).catch(function () {
                sel.innerHTML = '<option value="">Failed to load doctors</option>';
            });

            modalEl.querySelector('[data-role="submit"]').addEventListener('click', function () {
                var doctorId = parseInt(sel.value, 10);
                if (!doctorId) { showError(modalEl, 'Please select a doctor.'); return; }
                var note = (modalEl.querySelector('[data-role="note"]').value || '').trim();
                var btn = modalEl.querySelector('[data-role="submit"]');
                btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Sending…';

                postJson('/messages/conversations', {
                    title: 'Patient handoff',
                    participant_ids: [doctorId],
                }).then(function (res) {
                    if (!res.ok || !res.body || !res.body.conversation_id) {
                        throw new Error((res.body && (res.body.message || res.body.error)) || 'Could not create conversation');
                    }
                    return postJson('/messages/send', {
                        conversation_id: res.body.conversation_id,
                        patient_id: patientId,
                        is_transfer: true,
                        transfer_type: 'patient_file',
                        source_type: 'App\\Models\\Patient',
                        source_id: patientId,
                        priority: 'urgent',
                        metadata: { note: note },
                    });
                }).then(function (res) {
                    if (!res.ok) throw new Error((res.body && (res.body.message || res.body.error)) || 'Send failed');
                    modal.hide();
                    if (typeof window.refreshSidebarUnread === 'function') window.refreshSidebarUnread();
                }).catch(function (err) {
                    showError(modalEl, err && err.message ? err.message : 'Send failed.');
                    btn.disabled = false; btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Send urgent';
                });
            });
        },
    };
})();
