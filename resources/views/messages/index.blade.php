@extends('layouts.app')

@section('content')
<div class="container-fluid">
  <div class="d-flex align-items-center mb-3">
    <h4 class="me-3">Messages</h4>
    <span class="badge bg-primary" id="unreadBadge">0</span>
  </div>

  <div class="row" style="height: calc(100vh - 200px);">
    <div class="col-md-4 border-end overflow-auto" id="conversationsPane">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <strong>Conversations</strong>
        <button class="btn btn-sm btn-outline-primary" id="newConvBtn">New</button>
      </div>
      <ul class="list-group" id="conversationsList"></ul>
    </div>
    <div class="col-md-8 d-flex flex-column" id="threadPane">
      <div class="border-bottom pb-2 mb-2 d-flex justify-content-between align-items-center">
        <div>
          <strong id="threadTitle">Select a conversation</strong>
        </div>
        <button class="btn btn-sm btn-outline-secondary d-none" id="markReadBtn">Mark as read</button>
      </div>
      <div class="flex-grow-1 overflow-auto" id="messagesList" style="background: #fafafa;"></div>
      <div class="mt-2 d-flex gap-2">
        <input class="form-control" id="composerInput" placeholder="Type a message..." />
        <button class="btn btn-primary" id="sendBtn" disabled>Send</button>
      </div>
    </div>
  </div>
</div>

<!-- New Conversation Modal -->
<div class="modal fade" id="newConvModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Start a conversation</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Recipient</label>
          <input type="text" class="form-control" id="recipientSearch" placeholder="Search by name/email/username" autocomplete="off">
          <div class="list-group mt-2" id="recipientResults" style="max-height: 220px; overflow:auto;"></div>
          <input type="hidden" id="selectedRecipientId">
        </div>
        <div class="mb-3">
          <label class="form-label">Title (optional)</label>
          <input type="text" class="form-control" id="convTitle" maxlength="255">
        </div>
        <div class="mb-3">
          <label class="form-label">Message (optional)</label>
          <textarea class="form-control" id="initialMessage" rows="2" placeholder="Write a note..."></textarea>
        </div>
        <div id="transferPresetAlert" class="alert alert-info d-none small mb-0">
          This conversation will be created with a transfer attached.
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="createConvBtn" disabled>Create</button>
      </div>
    </div>
  </div>
</div>


@push('scripts')
<script>
(function() {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
  const headers = csrf ? { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'Content-Type': 'application/json' } : { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'Content-Type': 'application/json' };

  let state = {
    conversations: [],
    selectedConversationId: null,
    participantsByConv: {},
  };

  const el = {
    unreadBadge: document.getElementById('unreadBadge'),
    conversationsList: document.getElementById('conversationsList'),
    messagesList: document.getElementById('messagesList'),
    threadTitle: document.getElementById('threadTitle'),
    markReadBtn: document.getElementById('markReadBtn'),
    composerInput: document.getElementById('composerInput'),
    sendBtn: document.getElementById('sendBtn'),
    newConvBtn: document.getElementById('newConvBtn'),
  };

  async function getJSON(url) {
    const res = await fetch(url, { headers, credentials: 'same-origin' });
    if (!res.ok) throw new Error('Request failed: ' + res.status);
    return res.json();
  }
  async function postJSON(url, body) {
    const res = await fetch(url, { method: 'POST', headers, credentials: 'same-origin', body: JSON.stringify(body || {}) });
    if (!res.ok) {
      const txt = await res.text();
      throw new Error('Request failed: ' + res.status + ' ' + txt);
    }
    return res.json();
  }

  function renderConversations() {
    el.conversationsList.innerHTML = '';
    state.conversations.forEach(c => {
      const li = document.createElement('li');
      li.className = 'list-group-item d-flex justify-content-between align-items-center pointer';
      li.style.cursor = 'pointer';
      li.onclick = () => selectConversation(c.id);
      const title = c.title || (c.participants?.map(p => p.name).join(', ') || 'Conversation');
      li.innerHTML = `
        <div>
          <div class="fw-semibold">${title}</div>
          <div class="text-muted small">${c.last_message?.body || (c.last_message?.message_type === 'transfer' ? '[Transfer]' : '') || ''}</div>
        </div>
        ${c.unread > 0 ? `<span class="badge bg-danger">${c.unread}</span>` : ''}
      `;
      el.conversationsList.appendChild(li);
    });
  }

  function renderMessages(messages) {
    el.messagesList.innerHTML = '';
    messages.forEach(m => {
      const wrap = document.createElement('div');
      wrap.className = 'p-2';
      const name = m.sender?.name || 'Unknown';
      const when = new Date(m.created_at).toLocaleString();
      let inner = '';
      if (m.type === 'transfer' && m.transfer) {
        const t = m.transfer;
        const p = t.patient;
        const pname = t?.metadata?.patient_name || p?.full_name || (p ? ('Patient #' + p.id) : (t.patient_id ? ('Patient #' + t.patient_id) : ''));
        const patientLink = t.patient_id ? `/patients/${t.patient_id}` : null;
        const reportLink = t.patient_id ? `/patients/${t.patient_id}/report` : null;
        const note = (t?.metadata?.note || '').trim();
        inner = `
          <div class="card">
            <div class="card-body">
              <div class="d-flex justify-content-between">
                <div>
                  <div class="fw-semibold">Transfer: ${t.transfer_type.replace('_',' ')}</div>
                  <div class="text-muted small">From ${name} • ${when}</div>
                </div>
                <span class="badge bg-secondary text-uppercase">${t.status}</span>
              </div>
              <div class="mt-2">
                ${pname ? `<div><span class="text-muted small">Patient:</span> <a href="${patientLink}" target="_blank">${pname}</a></div>` : ''}
                ${note ? `<div class="small mt-1">Note: ${note.replace(/</g,'&lt;')}</div>` : ''}
                ${reportLink ? `<div class="mt-2"><a class="btn btn-sm btn-outline-primary" href="${reportLink}" target="_blank">Open Patient Report</a></div>` : ''}
              </div>
              <div class="mt-3 d-flex gap-2">
                <button class="btn btn-sm btn-success" data-action="accept">Accept</button>
                <button class="btn btn-sm btn-outline-danger" data-action="reject">Reject</button>
                <button class="btn btn-sm btn-outline-secondary" data-action="acknowledge">Acknowledge</button>
              </div>
            </div>
          </div>`;
      } else {
        inner = `
          <div class="border rounded p-2 bg-white">
            <div class="small text-muted">${name} • ${when}</div>
            <div>${(m.body || '').replace(/</g,'&lt;')}</div>
          </div>`;
      }
      wrap.innerHTML = inner;
      if (m.transfer) {
        wrap.querySelectorAll('button[data-action]').forEach(btn => {
          btn.addEventListener('click', () => actOnTransfer(m.transfer.id, btn.getAttribute('data-action')));
        });
      }
      el.messagesList.appendChild(wrap);
    });
    el.messagesList.scrollTop = el.messagesList.scrollHeight;
  }

  async function refreshUnread() {
    try {
      const data = await getJSON('/messages/unread-count');
      const count = data.unread ?? 0;
      el.unreadBadge.textContent = count;
      const sidebarBadge = document.getElementById('sidebarUnread');
      if (sidebarBadge) sidebarBadge.textContent = count;
      if (typeof window.__lastUnread === 'undefined') window.__lastUnread = 0;
      if (count > window.__lastUnread && window.__lastUnread !== 0) {
        if ('Notification' in window && Notification.permission === 'granted') {
          const diff = count - window.__lastUnread;
          new Notification('New message' + (diff > 1 ? 's' : ''), { body: diff + ' unread' });
        }
      }
      window.__lastUnread = count;
    } catch (e) { /* noop */ }
  }

  async function refreshConversations() {
    try {
      const data = await getJSON('/messages/conversations');
      state.conversations = data.conversations || [];
      renderConversations();
    } catch (e) { /* noop */ }
  }

  async function loadMessages(convId) {
    const data = await getJSON(`/messages/conversations/${convId}/messages`);
    renderMessages(data.messages || []);
  }

  async function selectConversation(convId) {
    state.selectedConversationId = convId;
    const conv = state.conversations.find(c => c.id === convId);
    el.threadTitle.textContent = conv?.title || (conv?.participants?.map(p => p.name).join(', ') || 'Conversation');
    el.markReadBtn.classList.remove('d-none');
    await loadMessages(convId);
  }

  async function sendMessage() {
    const convId = state.selectedConversationId;
    const body = (el.composerInput.value || '').trim();
    if (!convId || !body) return;
    el.sendBtn.disabled = true;
    try {
      await postJSON('/messages/send', { conversation_id: convId, body });
      el.composerInput.value = '';
      await Promise.all([loadMessages(convId), refreshConversations(), refreshUnread()]);
    } catch (e) {
      alert('Send failed');
    } finally {
      el.sendBtn.disabled = false;
    }
  }

  async function markRead() {
    const convId = state.selectedConversationId;
    if (!convId) return;
    try {
      await postJSON(`/messages/${convId}/read`);
      await Promise.all([refreshConversations(), refreshUnread()]);
    } catch (e) { /* noop */ }
  }

  async function actOnTransfer(transferId, action) {
    try {
      await postJSON(`/messages/transfers/${transferId}/action`, { action });
      if (state.selectedConversationId) await loadMessages(state.selectedConversationId);
      await refreshConversations();

    } catch (e) {
      alert('Action failed');
    }
  }


  // New Conversation Modal logic
  const newConvModalEl = document.getElementById('newConvModal');
  const newConvModal = new bootstrap.Modal(newConvModalEl);
  const recipientSearch = document.getElementById('recipientSearch');
  const recipientResults = document.getElementById('recipientResults');
  const selectedRecipientId = document.getElementById('selectedRecipientId');
  const convTitle = document.getElementById('convTitle');
  const initialMessage = document.getElementById('initialMessage');
  const transferPresetAlert = document.getElementById('transferPresetAlert');
  const createConvBtn = document.getElementById('createConvBtn');
  let presetTransfer = null;
  let searchTimer = null;

  function openNewConvModal(preset = null) {
    presetTransfer = preset;
    transferPresetAlert.classList.toggle('d-none', !presetTransfer);
    selectedRecipientId.value = '';
    recipientSearch.value = '';
    recipientResults.innerHTML = '';
    convTitle.value = '';
    initialMessage.value = presetTransfer?.note || '';
    createConvBtn.disabled = true;
    newConvModal.show();
    if ('Notification' in window && Notification.permission === 'default') {
      Notification.requestPermission().catch(()=>{});
    }
  }

  function renderRecipientResults(items) {
    recipientResults.innerHTML = '';
    items.forEach(u => {
      const a = document.createElement('a');
      a.href = '#';
      a.className = 'list-group-item list-group-item-action';
      a.textContent = `${u.name} (${u.role})`;
      a.addEventListener('click', (e) => {
        e.preventDefault();
        selectedRecipientId.value = u.id;
        recipientSearch.value = u.name;
        createConvBtn.disabled = false;
        recipientResults.innerHTML = '';
      });
      recipientResults.appendChild(a);
    });
  }

  async function searchRecipients(q) {
    const url = '/messages/recipients?query=' + encodeURIComponent(q || '');
    try {
      const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
      if (!res.ok) return;
      const data = await res.json();
      renderRecipientResults(data.recipients || []);
    } catch (e) { /* noop */ }
  }

  recipientSearch.addEventListener('input', () => {
    const q = recipientSearch.value.trim();
    selectedRecipientId.value = '';
    createConvBtn.disabled = true;
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => searchRecipients(q), 250);
  });

  createConvBtn.addEventListener('click', async () => {
    const rid = Number(selectedRecipientId.value || '');
    if (!rid) return;
    createConvBtn.disabled = true;
    try {
      const createRes = await postJSON('/messages/conversations', { participant_ids: [rid], title: convTitle.value || null });
      const convId = createRes.conversation_id;
      if (presetTransfer) {
        // Send as transfer
        await postJSON('/messages/send', {
          conversation_id: convId,
          is_transfer: true,
          transfer_type: presetTransfer.transfer_type,
          patient_id: presetTransfer.patient_id,
          source_type: presetTransfer.source_type || null,
          source_id: presetTransfer.source_id || null,
          metadata: presetTransfer.metadata || { note: initialMessage.value || null }
        });
      } else if ((initialMessage.value || '').trim()) {
        await postJSON('/messages/send', { conversation_id: convId, body: initialMessage.value.trim() });
      }
      newConvModal.hide();
      await refreshConversations();
      await selectConversation(convId);
      await refreshUnread();
    } catch (e) {
      console.error(e);
      alert('Failed to create conversation: ' + (e.message || ''));
    } finally {
      createConvBtn.disabled = false;
    }
  });

  // Prefill flow from other pages
  try {
    const prefill = localStorage.getItem('prefill_transfer');
    if (prefill) {
      localStorage.removeItem('prefill_transfer');
      const preset = JSON.parse(prefill);
      openNewConvModal(preset);
    }
  } catch (_) {}


  // Wire events
  el.sendBtn.addEventListener('click', sendMessage);
  el.composerInput.addEventListener('input', () => { el.sendBtn.disabled = !(el.composerInput.value || '').trim(); });
  el.composerInput.addEventListener('keydown', (ev) => { if (ev.key === 'Enter') sendMessage(); });
  el.markReadBtn.addEventListener('click', markRead);
  el.newConvBtn.addEventListener('click', () => openNewConvModal());

  // Initial load + polling
  Promise.all([refreshConversations(), refreshUnread()]);
  setInterval(refreshUnread, 12000);
  setInterval(refreshConversations, 15000);
})();
</script>
@endpush
@endsection

