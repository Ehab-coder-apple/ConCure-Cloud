{{-- Payment modals (Record + Edit + Delete) for the standalone payments page.
     Mirrors the inline modals on the finance dashboard but doesn't depend on
     $recentReceipts; payment rows are passed straight into editPaymentRow(). --}}

<datalist id="masterCityOptions">
    @foreach(($cityOptions ?? []) as $city)
        <option value="{{ $city }}"></option>
    @endforeach
</datalist>

<!-- Record Payment Modal -->
<div class="modal fade" id="createReceiptModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="recordPaymentForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-receipt me-2"></i>Record Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="payment_clinic_id" class="form-label">Clinic <span class="text-danger">*</span></label>
                        <select class="form-select" id="payment_clinic_id" name="clinic_id" required>
                            <option value="">Select Clinic</option>
                            @foreach($clinics as $clinic)
                                <option value="{{ $clinic->id }}">{{ $clinic->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="payment_currency" class="form-label">Currency <span class="text-danger">*</span></label>
                        <select class="form-select" id="payment_currency" name="currency" required>
                            <option value="USD">US Dollar ($)</option>
                            <option value="IQD">Iraqi Dinar (IQD)</option>
                            <option value="JOD">Jordanian Dinar (JD)</option>
                            <option value="EGP">Egyptian Pound (EGP)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="payment_amount" class="form-label">Amount <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="payment_amount" name="amount" min="0.01" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
                        <select class="form-select" id="payment_method" name="payment_method" required>
                            <option value="cash" selected>Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="check">Check</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="payment_paid_at" class="form-label">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="payment_paid_at" name="paid_at" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="payment_city" class="form-label">City</label>
                        <input type="text" class="form-control" id="payment_city" name="city"
                               maxlength="80" list="masterCityOptions" placeholder="e.g. Erbil">
                        <small class="text-muted">Auto-filled from the clinic when blank.</small>
                    </div>
                    <div class="mb-3">
                        <label for="payment_note" class="form-label">Note</label>
                        <textarea class="form-control" id="payment_note" name="note" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-check me-1"></i> Record Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Payment Modal -->
<div class="modal fade" id="editPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editPaymentForm">
                @csrf
                <input type="hidden" id="edit_payment_id" name="payment_id">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_payment_clinic_id" class="form-label">Clinic <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_payment_clinic_id" name="clinic_id" required>
                            <option value="">Select Clinic</option>
                            @foreach($clinics as $clinic)
                                <option value="{{ $clinic->id }}">{{ $clinic->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_payment_currency" class="form-label">Currency <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_payment_currency" name="currency" required>
                            <option value="USD">US Dollar ($)</option>
                            <option value="IQD">Iraqi Dinar (IQD)</option>
                            <option value="JOD">Jordanian Dinar (JD)</option>
                            <option value="EGP">Egyptian Pound (EGP)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_payment_amount" class="form-label">Amount <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="edit_payment_amount" name="amount" min="0.01" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_payment_method" name="payment_method" required>
                            <option value="">Select Method</option>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="check">Check</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_payment_paid_at" class="form-label">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="edit_payment_paid_at" name="paid_at" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_payment_city" class="form-label">City</label>
                        <input type="text" class="form-control" id="edit_payment_city" name="city"
                               maxlength="80" list="masterCityOptions" placeholder="e.g. Erbil">
                    </div>
                    <div class="mb-3">
                        <label for="edit_payment_note" class="form-label">Note</label>
                        <textarea class="form-control" id="edit_payment_note" name="note" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Update Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('master.finance._payment-modals-script')
