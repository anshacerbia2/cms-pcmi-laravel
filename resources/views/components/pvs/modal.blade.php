<div class="offcanvas offcanvas-end offcanvas-large" tabindex="-1" id="c_pv_canvas" style="width: 700px !important;">
    <div class="offcanvas-header border-bottom">
        <h4 id="c_pv_canvas_title">Create Payment Voucher</h4>
        <button type="button" class="btn-close custom-btn-close border p-1 me-0 d-flex align-items-center justify-content-center rounded-circle" data-bs-dismiss="offcanvas" aria-label="Close">
            <i class="ti ti-x"></i>
        </button>
    </div>
    <div class="offcanvas-body">
        <form id="c_pv_canvas_form">
            @csrf
            <input type="hidden" name="id" id="input_pv_id">
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="col-form-label">PV Number <span class="text-danger">*</span></label>
                        <input type="text" name="pv_number" id="input_pv_pv_number" class="form-control" placeholder="PV-XXX/...">
                        <small class="text-danger" id="error_pv_pv_number"></small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="col-form-label">Issuing Date <span class="text-danger">*</span></label>
                        <input type="date" name="issuing_date" id="input_pv_issuing_date" class="form-control" value="{{ date('Y-m-d') }}">
                        <small class="text-danger" id="error_pv_issuing_date"></small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="col-form-label">Due Date</label>
                        <input type="date" name="due_date" id="input_pv_due_date" class="form-control">
                        <small class="text-danger" id="error_pv_due_date"></small>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="col-form-label">Currency <span class="text-danger">*</span></label>
                        <select name="currency" id="input_pv_currency" class="form-control select2">
                            <option value="IDR" selected>IDR</option>
                            <option value="USD">USD</option>
                        </select>
                        <small class="text-danger" id="error_pv_currency"></small>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="mb-3">
                        <label class="col-form-label">Amount <span class="text-danger">*</span></label>
                        <input type="text" name="amount" id="input_pv_amount" class="form-control number-input" placeholder="0">
                        <small class="text-danger" id="error_pv_amount"></small>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="col-form-label">Category <span class="text-danger">*</span></label>
                        <select name="category" id="input_pv_category" class="form-control select2">
                            <option value="Expense">Expense</option>
                            <option value="COGS">COGS (Project Cost)</option>
                            <option value="Staff Loan">Staff Loan</option>
                        </select>
                        <small class="text-danger" id="error_pv_category"></small>
                    </div>
                </div>
                <div class="col-md-6" id="pv_category_detail_container">
                    <!-- Dynamic: PO Select or Expense Type Input -->
                    <div class="mb-3" id="pv_category_expense_type">
                        <label class="col-form-label">Expense Type <span class="text-danger">*</span></label>
                        <input type="text" name="expense_type" id="input_pv_expense_type" class="form-control" placeholder="e.g. Office Rent">
                        <small class="text-danger" id="error_pv_expense_type"></small>
                    </div>
                    <div class="mb-3" id="pv_category_po" style="display: none;">
                        <label class="col-form-label">Purchase Order <span class="text-danger">*</span></label>
                        <select name="purchase_order_id" id="input_pv_purchase_order_id" class="form-control select2">
                            <option value="">-- Select PO --</option>
                            <!-- Lookups will be handled by JS -->
                        </select>
                        <small class="text-danger" id="error_pv_purchase_order_id"></small>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="col-form-label">Payable To <span class="text-danger">*</span></label>
                        <select name="payable_type" id="input_pv_payable_type" class="form-control select2">
                            <option value="Manual">Manual Entry</option>
                            <option value="Supplier">Supplier</option>
                            <option value="Staff">Staff</option>
                        </select>
                        <small class="text-danger" id="error_pv_payable_type"></small>
                    </div>
                </div>
                <div class="col-md-6" id="pv_payable_lookup_container" style="display: none;">
                    <div class="mb-3">
                        <label class="col-form-label">Select Recipient <span class="text-danger">*</span></label>
                        <select name="payable_id" id="input_pv_payable_id" class="form-control select2">
                            <option value="">-- Select --</option>
                        </select>
                        <small class="text-danger" id="error_pv_payable_id"></small>
                    </div>
                </div>
                <div class="col-md-6" id="pv_payable_manual_container">
                    <div class="mb-3">
                        <label class="col-form-label">Recipient Name <span class="text-danger">*</span></label>
                        <input type="text" name="payable_name_manual" id="input_pv_payable_name_manual" class="form-control" placeholder="Enter name">
                        <small class="text-danger" id="error_pv_payable_name_manual"></small>
                    </div>
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="col-form-label">Payment Form <span class="text-danger">*</span></label>
                        <select name="source_payment_form" id="input_pv_source_payment_form" class="form-control select2">
                            <option value="Transfer">Transfer</option>
                            <option value="Cash">Cash</option>
                        </select>
                        <small class="text-danger" id="error_pv_source_payment_form"></small>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="mb-3">
                        <label class="col-form-label">Source Bank</label>
                        <select name="pcmi_bank_id" id="input_pv_pcmi_bank_id" class="form-control select2">
                            <option value="">-- Select Bank --</option>
                            @foreach(\App\Models\PcmiBank::with('bank')->get() as $bank)
                                <option value="{{ $bank->id }}">{{ $bank->bank->name }} - {{ $bank->account_number }}</option>
                            @endforeach
                        </select>
                        <small class="text-danger" id="error_pv_pcmi_bank_id"></small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="col-form-label">Payment Date</label>
                        <input type="date" name="payment_date" id="input_pv_payment_date" class="form-control">
                        <small class="text-danger" id="error_pv_payment_date"></small>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="col-form-label">Description</label>
                <textarea name="description" id="input_pv_description" class="form-control" rows="3"></textarea>
                <small class="text-danger" id="error_pv_description"></small>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button type="button" class="btn btn-light me-2" data-bs-dismiss="offcanvas">Cancel</button>
                <button type="submit" class="btn btn-primary" id="btn_pv_submit">Save Payment Voucher</button>
            </div>
        </form>
    </div>
</div>
