{{-- JS for the standalone payments page modals. Kept separate so the markup
     partial stays readable. Guard prevents double-binding if pulled in twice. --}}
<script>
if (typeof window.__masterPaymentHandlersLoaded === 'undefined') {
    window.__masterPaymentHandlersLoaded = true;

    const clinicCityMap = @json($clinicCityMap ?? []);

    function bindClinicCityAutofill(clinicSelectId, cityInputId) {
        const clinicSel = document.getElementById(clinicSelectId);
        const cityInput = document.getElementById(cityInputId);
        if (!clinicSel || !cityInput) return;
        clinicSel.addEventListener('change', function () {
            const mapped = clinicCityMap[this.value];
            if (mapped && cityInput.value.trim() === '') {
                cityInput.value = mapped;
            }
        });
    }
    bindClinicCityAutofill('payment_clinic_id', 'payment_city');
    bindClinicCityAutofill('edit_payment_clinic_id', 'edit_payment_city');

    document.getElementById('recordPaymentForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch('{{ route("master.finance.payment.store") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': formData.get('_token') },
            body: formData,
        })
            .then(r => r.json())
            .then(j => {
                if (j.success) { alert('Payment recorded successfully'); location.reload(); }
                else { alert('Error: ' + (j.message || 'Failed to record payment')); }
            })
            .catch(err => { alert('Error recording payment: ' + err.message); console.error(err); });
    });

    window.editPaymentRow = function (payment) {
        document.getElementById('edit_payment_id').value = payment.id;
        document.getElementById('edit_payment_clinic_id').value = payment.clinic_id || '';
        document.getElementById('edit_payment_currency').value = payment.currency || 'USD';
        document.getElementById('edit_payment_amount').value = payment.amount;
        document.getElementById('edit_payment_method').value = payment.method || '';
        document.getElementById('edit_payment_paid_at').value = payment.paid_at
            ? String(payment.paid_at).split('T')[0].split(' ')[0]
            : '';
        document.getElementById('edit_payment_city').value = payment.city || '';
        document.getElementById('edit_payment_note').value = payment.notes || '';
        new bootstrap.Modal(document.getElementById('editPaymentModal')).show();
    };

    document.getElementById('editPaymentForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const id = document.getElementById('edit_payment_id').value;
        const formData = new FormData(this);
        fetch(`/master/finance/payment/${id}/update`, {
            method: 'PUT',
            headers: { 'X-CSRF-TOKEN': formData.get('_token'), 'Content-Type': 'application/json' },
            body: JSON.stringify({
                clinic_id: formData.get('clinic_id'),
                currency: formData.get('currency'),
                amount: formData.get('amount'),
                payment_method: formData.get('payment_method'),
                paid_at: formData.get('paid_at'),
                city: formData.get('city') || null,
                note: formData.get('note'),
            }),
        })
            .then(r => r.json())
            .then(j => {
                if (j.success) { alert('Payment updated successfully'); location.reload(); }
                else { alert('Error: ' + (j.message || 'Failed to update payment')); }
            })
            .catch(err => { alert('Error updating payment: ' + err.message); console.error(err); });
    });

    window.deletePayment = function (id) {
        if (!confirm('Delete this payment? This action cannot be undone.')) return;
        fetch(`/master/finance/payment/${id}/delete`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
            },
        })
            .then(r => r.json())
            .then(j => {
                if (j.success) { alert(j.message || 'Payment deleted'); location.reload(); }
                else { alert('Error: ' + (j.message || 'Failed to delete payment')); }
            })
            .catch(err => { alert('Error deleting payment: ' + err.message); console.error(err); });
    };
}
</script>
