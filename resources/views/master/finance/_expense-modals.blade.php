<!-- Shared city suggestions datalist (used by expense + payment modals) -->
@if(!empty($cityOptions ?? []))
<datalist id="masterCityOptions">
    @foreach($cityOptions as $city)
        <option value="{{ $city }}"></option>
    @endforeach
</datalist>
@else
<datalist id="masterCityOptions"></datalist>
@endif

<!-- Record Expense Modal -->
<div class="modal fade" id="createExpenseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="recordExpenseForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-money-bill-wave me-2 text-danger"></i>
                        Record Expense
                        <span class="badge bg-secondary ms-2">IQD</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="expense_category" class="form-label">Category <span class="text-danger">*</span></label>
                        <select class="form-select" id="expense_category" name="category" required>
                            <option value="">Select Category</option>
                            @foreach($expenseCategories as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3 d-none" id="expense_new_category_wrapper">
                        <label for="expense_new_category_label" class="form-label">New category name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="expense_new_category_label" name="new_category_label" maxlength="60" placeholder="e.g. Legal fees">
                        <small class="text-muted">Saved as a reusable category for future expenses.</small>
                    </div>
                    <div class="mb-3">
                        <label for="expense_description" class="form-label">Description <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="expense_description" name="description" maxlength="255" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="expense_amount" class="form-label">Amount (IQD) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">IQD</span>
                                <input type="number" class="form-control" id="expense_amount" name="amount" min="0.01" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="expense_date" class="form-label">Expense Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="expense_date" name="expense_date" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="expense_payment_method" class="form-label">Payment Method</label>
                            <select class="form-select" id="expense_payment_method" name="payment_method">
                                <option value="">-</option>
                                @foreach($expensePaymentMethods as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="expense_city" class="form-label">City</label>
                            <input type="text" class="form-control" id="expense_city" name="city"
                                   maxlength="80" list="masterCityOptions" placeholder="e.g. Erbil">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="expense_notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="expense_notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-save me-1"></i> Save Expense
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Expense Modal -->
<div class="modal fade" id="editExpenseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editExpenseForm">
                @csrf
                <input type="hidden" id="edit_expense_id" name="expense_id">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>
                        Edit Expense
                        <span class="badge bg-secondary ms-2">IQD</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_expense_category" class="form-label">Category <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_expense_category" name="category" required>
                            @foreach($expenseCategories as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3 d-none" id="edit_expense_new_category_wrapper">
                        <label for="edit_expense_new_category_label" class="form-label">New category name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_expense_new_category_label" name="new_category_label" maxlength="60" placeholder="e.g. Legal fees">
                        <small class="text-muted">Saved as a reusable category for future expenses.</small>
                    </div>
                    <div class="mb-3">
                        <label for="edit_expense_description" class="form-label">Description <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_expense_description" name="description" maxlength="255" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_expense_amount" class="form-label">Amount (IQD) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">IQD</span>
                                <input type="number" class="form-control" id="edit_expense_amount" name="amount" min="0.01" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_expense_date" class="form-label">Expense Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="edit_expense_date" name="expense_date" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_expense_payment_method" class="form-label">Payment Method</label>
                            <select class="form-select" id="edit_expense_payment_method" name="payment_method">
                                <option value="">-</option>
                                @foreach($expensePaymentMethods as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_expense_city" class="form-label">City</label>
                            <input type="text" class="form-control" id="edit_expense_city" name="city"
                                   maxlength="80" list="masterCityOptions" placeholder="e.g. Erbil">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_expense_notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="edit_expense_notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Update Expense
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Master Expenses inline handlers (used by both finance dashboard and the
// dedicated expenses list page when no other script defines them yet).
if (typeof window.__masterExpenseHandlersLoaded === 'undefined') {
    window.__masterExpenseHandlersLoaded = true;

    // Reveal the "New category name" input only when "Other" is selected.
    function bindMasterExpenseCategoryToggle(selectId, wrapperId, inputId) {
        const sel = document.getElementById(selectId);
        const wrap = document.getElementById(wrapperId);
        const input = document.getElementById(inputId);
        if (!sel || !wrap || !input) return;
        const sync = () => {
            const isOther = sel.value === 'other';
            wrap.classList.toggle('d-none', !isOther);
            input.required = isOther;
            if (!isOther) input.value = '';
        };
        sel.addEventListener('change', sync);
        sync();
    }
    bindMasterExpenseCategoryToggle('expense_category', 'expense_new_category_wrapper', 'expense_new_category_label');
    bindMasterExpenseCategoryToggle('edit_expense_category', 'edit_expense_new_category_wrapper', 'edit_expense_new_category_label');

    document.getElementById('recordExpenseForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const data = {
            category: formData.get('category'),
            new_category_label: formData.get('new_category_label') || null,
            description: formData.get('description'),
            amount: formData.get('amount'),
            expense_date: formData.get('expense_date'),
            payment_method: formData.get('payment_method') || null,
            city: formData.get('city') || null,
            notes: formData.get('notes') || null,
        };

        fetch('{{ route("master.finance.expense.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': formData.get('_token')
            },
            body: JSON.stringify(data)
        })
        .then(r => r.json().then(j => ({ ok: r.ok, json: j })))
        .then(({ ok, json }) => {
            if (ok && json.success) {
                alert('Expense recorded successfully');
                location.reload();
            } else {
                alert('Error: ' + (json.message || 'Failed to record expense'));
            }
        })
        .catch(err => { alert('Error recording expense: ' + err.message); console.error(err); });
    });

    window.editExpense = function(expense) {
        document.getElementById('edit_expense_id').value = expense.id;
        const sel = document.getElementById('edit_expense_category');
        sel.value = expense.category;
        // Hide the new-category input when editing — the row already has a key.
        sel.dispatchEvent(new Event('change'));
        document.getElementById('edit_expense_description').value = expense.description;
        document.getElementById('edit_expense_amount').value = expense.amount;
        document.getElementById('edit_expense_date').value = expense.expense_date
            ? String(expense.expense_date).split('T')[0].split(' ')[0]
            : '';
        document.getElementById('edit_expense_payment_method').value = expense.payment_method || '';
        document.getElementById('edit_expense_city').value = expense.city || '';
        document.getElementById('edit_expense_notes').value = expense.notes || '';
        new bootstrap.Modal(document.getElementById('editExpenseModal')).show();
    };

    document.getElementById('editExpenseForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const id = document.getElementById('edit_expense_id').value;
        const formData = new FormData(this);
        const data = {
            category: formData.get('category'),
            new_category_label: formData.get('new_category_label') || null,
            description: formData.get('description'),
            amount: formData.get('amount'),
            expense_date: formData.get('expense_date'),
            payment_method: formData.get('payment_method') || null,
            city: formData.get('city') || null,
            notes: formData.get('notes') || null,
        };

        fetch(`/master/finance/expense/${id}/update`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': formData.get('_token')
            },
            body: JSON.stringify(data)
        })
        .then(r => r.json().then(j => ({ ok: r.ok, json: j })))
        .then(({ ok, json }) => {
            if (ok && json.success) {
                alert('Expense updated successfully');
                location.reload();
            } else {
                alert('Error: ' + (json.message || 'Failed to update expense'));
            }
        })
        .catch(err => { alert('Error updating expense: ' + err.message); console.error(err); });
    });

    window.deleteExpense = function(id) {
        if (!confirm('Delete this expense? This cannot be undone.')) return;
        fetch(`/master/finance/expense/${id}/delete`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(r => r.json().then(j => ({ ok: r.ok, json: j })))
        .then(({ ok, json }) => {
            if (ok && json.success) {
                alert(json.message);
                location.reload();
            } else {
                alert('Error: ' + (json.message || 'Failed to delete expense'));
            }
        })
        .catch(err => { alert('Error deleting expense: ' + err.message); console.error(err); });
    };
}
</script>
