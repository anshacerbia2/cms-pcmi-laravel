class ReceiveVoucherForm {
    isInit = true;
    selectedInvoiceIds = [];
    customers = [];
    employees = [];
    suppliers = [];
    paymentVouchers = [];
    isSubmitting = false;
    mode = "create";
    pcmibanks = [];
    unpaidInvoices = [];
    data = {};
    isFetching = false;
    loadingEl = null;
    errors = {};

    currencies = [
        "IDR",
        "USD",
        "EUR",
        "GBP",
        "JPY",
        "KRW",
        "MYR",
        "HKD",
        "Others"
    ];
    creditCards = [
        "UOB Master - David Harsamto",
        "Mandiri Visa - David Harsamto",
        "DBS Visa - David Harsamto",
        "BRI Corporate Cards - RD Hidianitje",
        "BRI Corporate Cards - Frida Achmadi",
        "BRI Corporate Cards - Yogi Widodo"
    ];
    paymentForms = [
        { value: 'Bank', label: 'Bank' },
        { value: 'Credit Card', label: 'Credit Card' },
        { value: 'Cash', label: 'Cash (Disabled - Must deposit to Bank)', disabled: true }
    ];
    payerTypes = ["Customer", "Employee", "Supplier", "Others", "Unknown"];
    paymentPurposes = [
        "Invoice",
        "Return/Refund",
        "Returning Deposit",
        "Returning Cash Advance",
        "Staff Loan",
        "Others",
        "Unknown"
    ];

    constructor (formId) {
        this.form = document.getElementById(formId);
        this.closeForm = document.getElementById("c_rv_canvas_close_btn");

        this.handleSubmit = this.handleSubmit.bind(this);
        this.handleDocumentChange = this.handleDocumentChange.bind(this);
        this.handleDocumentInput = this.handleDocumentInput.bind(this);
        this.handleDocumentKeyDown = this.handleDocumentKeyDown.bind(this);

        this.form.addEventListener("submit", (e) => {
            e.preventDefault();
            this.handleSubmit();
        });

        document.addEventListener("change", this.handleDocumentChange);
        document.addEventListener("input", this.handleDocumentInput);
        document.addEventListener("keydown", this.handleDocumentKeyDown);
    }

    async fetchUnpaidInvoices(customerId = null) {
        let url = '/invoices/unpaid';
        if (customerId) url += `?customer_id=${customerId}`;
        return this.fetchResource(url).then(res => {
            this.unpaidInvoices = res;

            // Only update UI if we are already in the DOM
            if ($('#rv_canvas_unpaid_invoice_list_table').length) {
                this.initDataTable();
            }
        });
    }

    async fetchPcmiBanks() { return this.fetchResource('/pcmibanks/all').then(res => this.pcmibanks = res); }
    async fetchCustomers() { return this.fetchResource('/customers/all').then(res => this.customers = res); }
    async fetchEmployees() { return this.fetchResource('/users/all').then(res => this.employees = res); }
    async fetchSuppliers() { return this.fetchResource('/suppliers/all').then(res => this.suppliers = res); }

    async fetchResource(url) {
        this.isFetching = true;
        this.showLoading();
        return fetch(url, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
            },
        })
            .then(response => {
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                return response.json();
            })
            .then(data => {
                return data.data || [];
            })
            .catch(err => {
                console.error("Fetch error for " + url + ":", err);
                return [];
            })
            .finally(() => {
                this.isFetching = false;
                if (!this.isInit) this.hideLoading();
            });
    }

    async init({ mode = "create", data = null }) {
        this.resetForm();
        this.showLoading();
        this.mode = mode;
        this.data = data;

        const fetches = [];

        if (mode === 'create') {
            fetches.push(this.fetchPcmiBanks());
            fetches.push(this.fetchCustomers());
            fetches.push(this.fetchUnpaidInvoices());
        } else if (mode === 'edit' && data) {
            if (data.payment_form === 'Bank') fetches.push(this.fetchPcmiBanks());

            // Pre-fetch payer list based on saved type
            if (data.payer_type === 'Customer') fetches.push(this.fetchCustomers());
            if (data.payer_type === 'Employee') fetches.push(this.fetchEmployees());
            if (data.payer_type === 'Supplier') fetches.push(this.fetchSuppliers());

            // Pre-fetch related data based on purpose
            if (data.payer_type === 'Customer' && data.purpose === 'Invoice') {
                fetches.push(this.fetchUnpaidInvoices(data.payer_id));
            }
            if (['Return/Refund', 'Returning Deposit', 'Returning Cash Advance', 'Staff Loan'].includes(data.purpose)) {
                fetches.push(this.fetchPaymentVouchers());
            }
        }

        await Promise.all(fetches);

        const formWrapper = document.createElement("div");
        formWrapper.innerHTML = this.generateForm();
        this.form.appendChild(formWrapper);

        await this.renderInvoiceTable();
        // --- Explicit Render after DOM is ready ---
        this.initPlugins();

        this.isInit = false;
        this.hideLoading();
    }

    generateForm() {
        let value = {
            id: "",
            rv_number: "",
            rv_date: moment().format('DD/MM/YY'),
            currency: "IDR",
            currency_manual: "",
            amount: "0",
            payment_form: "Bank",
            pcmi_bank_id: "",
            payment_form_value: "",
            payer_type: "Customer",
            payer_id: "",
            payer_name_manual: "",
            purpose: "Invoice",
            payment_voucher_id: "",
            description: ""
        };

        if (this.mode === "edit" && this.data) {
            this.selectedInvoiceIds = (this.data.invoices || []).map(i => i.id.toString());
            value.id = this.data.id || "";
            value.rv_number = this.data.rv_number || "";
            value.rv_date = this.data.rv_date ? moment(this.data.rv_date).format('DD/MM/YY') : "";
            value.currency = this.data.currency || "";
            value.currency_manual = this.data.currency_manual || "";
            value.amount = this.data.amount || "0";
            value.payment_form = this.data.payment_form || "";
            value.pcmi_bank_id = this.data.pcmi_bank_id || "";
            value.payment_form_value = this.data.payment_form_value || "";
            value.payer_type = this.data.payer_type || "";
            value.payer_id = this.data.payer_id || "";
            value.payer_name_manual = this.data.payer_name_manual || "";
            value.purpose = this.data.purpose || "Invoice";
            value.payment_voucher_id = this.data.payment_voucher_id || "";
            value.description = this.data.description || "";
        }

        let bankOptions = '';
        if (value.payment_form === 'Bank') {
            bankOptions = this.pcmibanks.filter(b => b.type === 'Bank').map(b => `
                <option value="${b.id}" ${value.pcmi_bank_id == b.id ? 'selected' : ''}>
                    ${b.bank?.bank_name || 'N/A'} - ${b.account_no}
                </option>
            `).join('');
        } else if (value.payment_form === 'Credit Card') {
            bankOptions = this.creditCards.map(cc => `
                <option value="${cc}" ${cc === value.payment_form_value ? 'selected' : ''}>${cc}</option>
            `).join('');
        }

        let currentPayerList = [];
        if (value.payer_type === 'Customer') currentPayerList = this.customers;
        else if (value.payer_type === 'Employee') currentPayerList = this.employees;
        else if (value.payer_type === 'Supplier') currentPayerList = this.suppliers;

        let payerOptions = currentPayerList.map(p => `
            <option value="${p.id}" ${p.id == value.payer_id ? 'selected' : ''}>${p.name}</option>
        `).join('');

        let pvOptions = (this.paymentVouchers || []).map(pv => `
            <option value="${pv.id}" ${value.payment_voucher_id == pv.id ? 'selected' : ''}>${pv.pv_number} - ${pv.description || ''}</option>
        `).join('');

        return `
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="col-form-label">RV Number <span class="text-danger">*</span></label>
                        <input type="text" name="rv_number" id="input_rv_rv_number" class="form-control" 
                            placeholder="e.g. RV-001/PCMI/III/2026" value="${value.rv_number}">
                        <small class="text-danger" id="input_rv_rv_number_error" style="display: none;"></small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="col-form-label">RV Date <span class="text-danger">*</span></label>
                        <div class="icon-form">
                            <span class="form-icon"><i class="ti ti-calendar-event"></i></span>
                            <input type="text" name="rv_date" id="input_rv_rv_date" class="form-control datetimepicker" 
                                placeholder="DD/MM/YY" value="${value.rv_date}">
                        </div>
                        <small class="text-danger" id="input_rv_rv_date_error" style="display: none;"></small>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="col-form-label">Currency <span class="text-danger">*</span></label>
                        <select name="currency" id="input_rv_currency" class="form-control select2">
                            <option value="">-- SELECT CURRENCY --</option>
                            ${this.currencies.map(c => `
                                <option value="${c}" ${value.currency === c ? 'selected' : ''}>${c}</option>
                            `).join('')}
                        </select>
                        <small class="text-danger" id="input_rv_currency_error" style="display: none;"></small>
                    </div>
                </div>
                <div class="col-md-8" id="currency_manual_container" style="display: ${value.currency === 'Others' ? 'block' : 'none'};">
                    <div class="mb-3">
                        <label class="col-form-label">Other Currency <span class="text-danger">*</span></label>
                        <input type="text" name="currency_manual" id="input_rv_currency_manual" class="form-control" 
                            placeholder="e.g. AUD" value="${value.currency_manual}">
                        <small class="text-danger" id="input_rv_currency_manual_error" style="display: none;"></small>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="col-form-label">Amount <span class="text-danger">*</span></label>
                        <input type="text" name="amount" id="input_rv_amount" class="form-control number-input" 
                            placeholder="0" value="${value.amount ? formatRupiahDisplay(value.amount.toString().replace('.', ',')) : 0}">
                        <small class="text-danger" id="input_rv_amount_error" style="display: none;"></small>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="col-form-label">Form of Payment <span class="text-danger">*</span></label>
                        <select name="payment_form" id="input_rv_payment_form" class="form-control select2">
                            <option value="">-- SELECT FORM OF PAYMENT --</option>
                            ${this.paymentForms.map(pf => `
                                <option value="${pf.value}" ${value.payment_form === pf.value ? 'selected' : ''} ${pf.disabled ? 'disabled' : ''}>${pf.label}</option>
                            `).join('')}
                        </select>
                        <small class="text-danger" id="input_rv_payment_form_error" style="display: none;"></small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="col-form-label" id="label_rv_pcmi_bank_id">Bank / CC Account <span class="text-danger">*</span></label>
                        <select name="pcmi_bank_id" id="input_rv_pcmi_bank_id" class="form-control select2">
                            <option value="">-- SELECT ${value.payment_form.toUpperCase()} --</option>
                            ${bankOptions}
                        </select>
                        <small class="text-danger" id="input_rv_pcmi_bank_id_error" style="display: none;"></small>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="col-form-label">Payer Type <span class="text-danger">*</span></label>
                        <select name="payer_type" id="input_rv_payer_type" class="form-control select2">
                            <option value="">-- SELECT PAYER TYPE --</option>
                            ${this.payerTypes.map(pt => `
                                <option value="${pt}" ${value.payer_type === pt ? 'selected' : ''}>${pt}</option>
                            `).join('')}
                        </select>
                        <small class="text-danger" id="input_rv_payer_type_error" style="display: none;"></small>
                    </div>
                </div>
                <div class="col-md-6" id="rv_payer_lookup_container" style="display: ${['Others', 'Unknown'].includes(value.payer_type) ? 'none' : 'block'};">
                    <div class="mb-3">
                        <label class="col-form-label" id="label_rv_payer_id">Select Payer <span class="text-danger">*</span></label>
                        <select name="payer_id" id="input_rv_payer_id" class="form-control select2">
                            <option value="">-- SELECT ${value.payer_type.toUpperCase()} --</option>
                            ${payerOptions}
                        </select>
                        <small class="text-danger" id="input_rv_payer_id_error" style="display: none;"></small>
                    </div>
                </div>
                <div class="col-md-6" id="rv_payer_manual_container" style="display: ${value.payer_type === 'Others' ? 'block' : 'none'};">
                    <div class="mb-3">
                        <label class="col-form-label">Payer Name <span class="text-danger">*</span></label>
                        <input type="text" name="payer_name_manual" id="input_rv_payer_name_manual" class="form-control" 
                            placeholder="Enter name" value="${value.payer_name_manual}">
                        <small class="text-danger" id="input_rv_payer_name_manual_error" style="display: none;"></small>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="col-form-label">Purpose of Payment <span class="text-danger">*</span></label>
                        <select name="purpose" id="input_rv_purpose" class="form-control select2">
                            <option value="">-- SELECT PURPOSE --</option>
                            ${this.paymentPurposes.map(p => `
                                <option value="${p}" ${value.purpose === p ? 'selected' : ''}>${p}</option>
                            `).join('')}
                        </select>
                        <small class="text-danger" id="input_rv_purpose_error" style="display: none;"></small>
                    </div>
                </div>
            </div>

            <div id="rv_purpose_link_container" style="display: ${['Return/Refund', 'Returning Deposit', 'Returning Cash Advance', 'Staff Loan'].includes(value.purpose) ? 'block' : 'none'};">
                <div class="mb-3">
                    <label class="col-form-label" id="label_rv_purpose_link">Related Document (PV)</label>
                    <select name="payment_voucher_id" id="input_rv_payment_voucher_id" class="form-control select2">
                        <option value="">-- Choose PV Number --</option>
                    </select>
                    <small class="text-danger" id="input_rv_payment_voucher_id_error" style="display: none;"></small>
                </div>
            </div>

            <div id="rv_invoice_datatable_container"></div>

            <div class="mb-3">
                <label class="col-form-label">Description</label>
                <textarea name="description" id="input_rv_description" class="form-control" rows="3">${value.description}</textarea>
                <small class="text-danger" id="input_rv_description_error" style="display: none;"></small>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button type="button" class="btn btn-light me-2" data-bs-dismiss="offcanvas">Cancel</button>
                <button type="submit" class="btn btn-primary" id="btn_rv_submit">Save Receive Voucher</button>
            </div>
        `;
    }

    getUnpaidInvoiceTableHTML() {
        return `
            <div class="mb-3">
                <label class="col-form-label">Select Related Invoices</label>
                <div style="border: 1px solid #e8e8e8; border-radius: 8px;">
                    <div class="table-responsive custom-table">
                        <table class="table mb-0" id="rv_canvas_unpaid_invoice_list_table">
                            <thead class="thead-light">
                                <tr>
                                    <th class="no-sort" style="width: 40px;">
                                        <div class="form-check custom-checkbox">
                                            <input type="checkbox" class="form-check-input" id="rv_select_all_invoices">
                                        </div>
                                    </th>
                                    <th>Invoice No</th>
                                    <th>Due Date</th>
                                    <th class="text-end">Total Amount</th>
                                    <th class="text-end">Balance Due</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Loaded via JS -->
                            </tbody>
                        </table>
                    </div>
                    <div class="row align-items-center" style="row-gap: 1em; padding: 10px 15px;">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center justify-content-center justify-content-md-start">
                                <div class="rv-datatable-info"></div>
                                <div class="rv-canvas-table-unpaid-invoice-length ms-2"></div>
                            </div>
                        </div>
                        <div class="col-md-6 flex-grow-1">
                            <div class="rv-canvas-table-unpaid-invoice-paginate"></div>
                        </div>
                    </div>
                </div>
                
                <!-- 🚀 New Allocation Details Section (Clean & Separated) -->
                <div id="rv_allocation_container" class="mt-4 pt-2" style="display: none; border-top: 2px dashed #eee; pt-3">
                    <h5 class="mb-3 text-dark fw-bold"><i class="ti ti-layout-list me-2"></i>Payment Allocation Details</h5>
                    <div id="rv_allocation_list" class="d-flex flex-column gap-3">
                        <!-- Dynamic Allocation Rows will appear here -->
                    </div>
                    
                    <div class="row mt-4 pt-3" style="border-top: 1px solid #eee;">
                        <div class="col-12 text-end">
                            <div class="d-flex flex-column">
                                <span class="text-muted" style="font-size: 0.85rem;">Total Allocated: <b id="rv_total_allocated_display" class="text-dark">IDR 0,00</b></span>
                                <span class="text-dark" style="font-size: 1rem;">Unallocated: <b id="rv_unallocated_balance_display" class="text-primary fw-bold">IDR 0,00</b></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <small class="text-danger" id="input_rv_invoice_ids_error" style="display: none;"></small>
            </div>
        `;
    }

    async renderInvoiceTable() {
        const container = $('#rv_invoice_datatable_container');
        if (!container.length) return;

        const payerType = $('#input_rv_payer_type').val();
        const customerId = $('#input_rv_payer_id').val();
        const purpose = $('#input_rv_purpose').val();

        if (purpose === 'Invoice' && payerType === 'Customer' && customerId) {
            // 🚀 Reset selection on manual change (not during initialisation)
            if (!this.isInit) {
                this.selectedInvoiceIds = [];
            }

            // 🚀 SMART RENDER: Only append if table doesn't exist
            if ($('#rv_canvas_unpaid_invoice_list_table').length === 0) {
                container.empty().append(this.getUnpaidInvoiceTableHTML());
            }

            await this.fetchUnpaidInvoices(customerId);
            this.initDataTable();
        } else {
            this.clearInvoiceTable();
        }
    }

    // ---------------------------------------- GLOBAL HANDLER ----------------------------------------
    handleDocumentKeyDown(e) {
        const target = e.target;
        if (target.matches(`.number-input`)) {
            if (e.ctrlKey || e.metaKey || e.altKey) return;

            const k = e.key;

            if (
                k === "Backspace" ||
                k === "Delete" ||
                k === "ArrowLeft" ||
                k === "ArrowRight" ||
                k === "Home" ||
                k === "End" ||
                k === "Tab"
            ) return;

            if (!/[\d,]/.test(k)) e.preventDefault();
        }
    }

    handleDocumentInput(e) {
        const target = e.target;
        if (target.matches(`.number-input`)) {

            if (target.classList.contains("no-decimal")) {
                let val = target.value;
                val = val.replace(/\D/g, "").replace(/^0+(?=\d)/, "");
                if (val === "") val = "0";
                target.value = val;
            } else {
                const before = target.value;
                const caret = target.selectionStart;

                // Remove non-digits and leading zeros before formatting
                let clean = before.replace(/[^\d,]/g, "").replace(/^0+(?=\d)/, "");
                if (clean.startsWith(",")) clean = "0" + clean;
                if (clean === "") clean = "0";

                const formatted = formatRupiahDisplay(clean);

                target.value = formatted;

                const delta = formatted.length - before.length;
                const newCaret = caret + delta > 0 ? caret + delta : 0;
                target.setSelectionRange(newCaret, newCaret);
            }

            // 🚀 Real-time sync if triggered on allocation fields
            if (target.matches(".rv-allocation-input") ||
                target.matches(".rv-pph23-input") ||
                target.matches(".rv-bankcharge-input") ||
                target.matches(".rv-wapu-input") ||
                target.matches(".rv-adjustment-input") ||
                target.id === 'input_rv_amount') {
                this.updateAllocatedTotals();
            }
        }
    }

    async handleDocumentChange(e) {
        const target = e.target;

        if (target.id === 'input_rv_payer_type') {
            const type = target.value;
            const lookupContainer = $('#rv_payer_lookup_container');
            const manualContainer = $('#rv_payer_manual_container');

            // 🚀 Atomic Reset: Clear table and ID as soon as type changes
            this.clearInvoiceTable();
            $('#input_rv_payer_id').val('').trigger('change.select2');

            lookupContainer.hide();
            manualContainer.hide();

            if (type === 'Others') {
                manualContainer.show();
            } else if (type !== 'Unknown' && type !== '') {
                lookupContainer.show();
                let list = [];
                if (type === 'Customer') {
                    if (!this.customers.length) await this.fetchCustomers();
                    list = this.customers;
                } else if (type === 'Employee') {
                    if (!this.employees.length) await this.fetchEmployees();
                    list = this.employees;
                } else if (type === 'Supplier') {
                    if (!this.suppliers.length) await this.fetchSuppliers();
                    list = this.suppliers;
                }
                this.populatePayerDropdown(type, list);
            }
        }

        if (target.id === 'input_rv_payer_id') {
            this.renderInvoiceTable();
        }

        if (target.id === 'input_rv_purpose') {
            const purpose = target.value;
            const pvContainer = $('#rv_purpose_link_container');
            pvContainer.hide();

            this.renderInvoiceTable();

            if (this.paymentPurposes.filter(p => p !== 'Invoice' && p !== 'Others' && p !== 'Unknown').includes(purpose)) {
                pvContainer.show();
                this.fetchPaymentVouchers();
            }
        }

        if (target.id === 'input_rv_currency') {
            const val = target.value;
            if (val === 'Others') {
                $('#currency_manual_container').show();
            } else {
                $('#currency_manual_container').hide();
                $('#input_rv_currency_manual').val('');
            }
        }

        if (target.id === 'input_rv_payment_form') {
            const type = target.value;
            const inputBank = $('#input_rv_pcmi_bank_id');
            if (inputBank.length) {
                inputBank.empty().append(new Option('-- SELECT BANK --', ''));
                if (type === 'Bank') {
                    await this.fetchPcmiBanks();
                    this.pcmibanks.filter(b => b.type === 'Bank').forEach(b => {
                        const label = `${b.bank?.bank_name || 'N/A'} - ${b.account_no}`;
                        inputBank.append(new Option(label, b.id));
                    });
                } else if (type === 'Credit Card') {
                    this.creditCards.forEach(cc => inputBank.append(new Option(cc, cc)));
                }
                inputBank.val('').trigger('change');
            }
        }

        if (target.matches("#rv_select_all_invoices")) {
            const checked = target.checked;

            if (!checked) {
                this.selectedInvoiceIds = [];
            }

            document.querySelectorAll('#rv_canvas_unpaid_invoice_list_table input.invoice-check').forEach(el => {
                el.checked = checked;
                if (checked) {
                    if (!this.selectedInvoiceIds.includes(el.value)) {
                        this.selectedInvoiceIds.push(el.value);
                    }
                }
            });
            this.updateSelectAllCheckbox();
            this.renderAllocationSection(); // 🚀 Render section after changes
        } else if (target.matches(".invoice-check")) {
            const checked = target.checked;
            const id = target.value;

            if (!checked) {
                const selectAll = document.querySelector("#rv_select_all_invoices");
                if (selectAll) selectAll.checked = false;
                this.selectedInvoiceIds = this.selectedInvoiceIds.filter(i => i !== id);
            } else {
                if (!this.selectedInvoiceIds.includes(id)) {
                    this.selectedInvoiceIds.push(id);
                }
            }
            this.updateSelectAllCheckbox();
            this.renderAllocationSection(); // 🚀 Render section after changes
        }
    }

    resetForm() {
        this.isInit = true;
        this.selectedInvoiceIds = [];
        this.isSubmitting = false;
        this.mode = "create";
        this.data = null;
        this.errors = {};
        this.form.innerHTML = "";
        this.loadingEl = null;
    }

    resetErrorFields() {
        const errKeys = Object.keys(this.errors);
        if (errKeys.length) {
            errKeys.forEach(v => {
                const el = this.form.querySelector("#" + v);
                if (el) {
                    el.textContent = "";
                    el.style.display = "none";
                }
            });
        }
        this.errors = {};
    }

    validateFields() {
        this.resetErrorFields();
        const payload = {
            id: this.form.querySelector("#input_rv_id")?.value || null
        };

        const getValue = (id) => this.form.querySelector("#" + id)?.value?.trim() || "";

        const inputs = [
            { field: "input_rv_rv_number", required: true, message: "RV Number is required." },
            { field: "input_rv_rv_date", required: true, date: true, message: "RV Date is required." },
            { field: "input_rv_currency", required: true, message: "Currency is required." },
            { field: "input_rv_amount", required: true, message: "Valid amount is required." },
            { field: "input_rv_payment_form", required: true, message: "Payment form is required." },
            { field: "input_rv_pcmi_bank_id", required: true, message: "Bank / CC Account is required." },
            { field: "input_rv_payer_type", required: true, message: "Payer type is required." },
            { field: "input_rv_purpose", required: true, message: "Purpose is required." },
        ];

        inputs.forEach(id => {
            const el = this.form.querySelector("#" + id.field);
            let value = el ? el.value.trim() : "";

            if (value && id.date) {
                value = moment(value, 'DD/MM/YY').format('YYYY-MM-DD');
            }

            // Normalisasi field name untuk payload (buang prefix input_rv_)
            const key = id.field.replace("input_rv_", "");
            payload[key] = value;

            if (id.required && (!value || value === "0" || value === "")) {
                this.errors[id.field + "_error"] = id.message;
            }
        });

        // --- Custom Clean & Correct Mapping ---

        // 1. Amount Normalization (Numeric standard)
        if (payload.amount) {
            payload.amount = parseFloat(normalizeFormatRupiah(payload.amount).replace(",", "."));
        }

        // 2. Bank / CC Mapping logic
        if (payload.payment_form === 'Bank') {
            payload.pcmi_bank_id = getValue("input_rv_pcmi_bank_id");
            payload.payment_form_value = null;
        } else {
            payload.pcmi_bank_id = null;
            payload.payment_form_value = getValue("input_rv_pcmi_bank_id");
        }

        // 3. Conditional: Currency Others
        if (payload.currency === 'Others') {
            payload.currency_manual = getValue("input_rv_currency_manual");
            if (!payload.currency_manual) this.errors.input_rv_currency_manual_error = "Currency code is required.";
        }

        // 4. Conditional: Payer
        payload.payer_id = getValue("input_rv_payer_id");
        payload.payer_name_manual = getValue("input_rv_payer_name_manual");
        if (['Customer', 'Employee', 'Supplier'].includes(payload.payer_type)) {
            if (!payload.payer_id) this.errors.input_rv_payer_id_error = "Please select a Payer.";
        } else if (payload.payer_type === 'Others') {
            if (!payload.payer_name_manual) this.errors.input_rv_payer_name_manual_error = "Payer name is required.";
        }

        // 5. Conditional: Purpose (PV Link)
        payload.payment_voucher_id = getValue("input_rv_payment_voucher_id");
        if (['Return/Refund', 'Returning Deposit', 'Returning Cash Advance', 'Staff Loan'].includes(payload.purpose)) {
            if (!payload.payment_voucher_id) this.errors.input_rv_payment_voucher_id_error = "Related PV is required.";
        }

        // 6. Conditional: Purpose (Invoice List with Manual Allocation)
        if (payload.purpose === 'Invoice') {
            const allocations = [];
            const rows = document.querySelectorAll('.rv_allocation_row');

            rows.forEach(row => {
                const id = row.dataset.id;
                const cash = normalizeFormatRupiah(row.querySelector('.rv-allocation-input')?.value || "0");
                const pph = normalizeFormatRupiah(row.querySelector('.rv-pph23-input')?.value || "0");
                const bank = normalizeFormatRupiah(row.querySelector('.rv-bankcharge-input')?.value || "0");
                const wapu = normalizeFormatRupiah(row.querySelector('.rv-wapu-input')?.value || "0");
                const adj = normalizeFormatRupiah(row.querySelector('.rv-adjustment-input')?.value || "0");

                if (cash > 0 || pph !== 0 || bank !== 0 || wapu !== 0 || adj !== 0) {
                    allocations.push({
                        invoice_id: id,
                        amount_applied: cash,
                        pph23_deduction: pph,
                        bank_charge: bank,
                        ppn_wapu_deduction: wapu,
                        others_adjustment: adj
                    });
                }
            });

            payload.invoice_allocations = allocations;
            payload.invoice_ids = allocations.map(a => a.invoice_id);

            if (allocations.length === 0) {
                this.errors.invoice_selection_error = "Please select and allocate at least one invoice.";
            }
        }

        // 7. Others
        payload.description = getValue("input_rv_description");

        return payload;
    }

    showLoading() {
        if (!this.loadingEl) {
            this.loadingEl = document.createElement("div");
            this.loadingEl.className = "c-form-loading-overlay";
            this.loadingEl.innerHTML = `
      <div class="c-form-spinner"></div>
    `;
            Object.assign(this.loadingEl.style, {
                position: "absolute",
                top: 0,
                left: 0,
                width: "100%",
                height: "100%",
                background: "rgba(255,255,255,0.7)",
                display: "flex",
                justifyContent: "center",
                alignItems: "center",
                zIndex: 9999
            });
            this.form.appendChild(this.loadingEl);
        }
        this.loadingEl.style.display = "flex";
    }

    hideLoading() {
        if (this.loadingEl) {
            this.loadingEl.style.display = "none";
        }
    }

    initPlugins() {
        if (window.$ && $.fn.select2) {
            $(this.form).find('.select2').each(function () {
                $(this).select2({ dropdownParent: $(this).parent() });

                // bridge event agar change bisa dideteksi oleh document.addEventListener('change')
                $(this).on('select2:select select2:unselect', function () {
                    this.dispatchEvent(new Event('change', { bubbles: true }));
                });
            });
        }

        if ($('.datetimepicker').length && $.fn.datetimepicker) {
            $('.datetimepicker').each(function () {
                const el = $(this);
                const rawValue = el.val();
                const isIso = rawValue && moment(rawValue, moment.ISO_8601, true).isValid();
                const parsedDate = isIso ? moment(rawValue) : null;

                el.datetimepicker({
                    format: 'DD/MM/YY',
                    date: parsedDate || null,
                    icons: {
                        previous: 'ti ti-chevron-left',
                        next: 'ti ti-chevron-right',
                        up: 'ti ti-chevron-up',
                        down: 'ti ti-chevron-down',
                        close: 'ti ti-x'
                    }
                });

                if (isIso) {
                    el.val(parsedDate.format('DD/MM/YY'));
                }
            });
        }
    }

    populatePayerDropdown(type, list) {
        const inputPayer = $('#input_rv_payer_id');
        if (!inputPayer.length) return; // Safety check if called before render
        inputPayer.empty().append(new Option('-- SELECT ' + type.toUpperCase() + ' --', ''));
        list.forEach(item => inputPayer.append(new Option(item.name, item.id)));
    }


    async fetchPaymentVouchers() {
        this.isFetching = true;
        this.showLoading();
        return fetch('/payment-vouchers/all', {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
            },
        })
            .then(response => {
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                return response.json();
            })
            .then(data => {
                const items = data.data || [];
                const sel = $('#input_rv_payment_voucher_id');
                if (sel.length) {
                    sel.empty().append(new Option('-- Choose PV Number --', ''));
                    items.forEach(item => sel.append(new Option(item.pv_number + ' - ' + item.category, item.id)));
                }
                return items;
            })
            .catch(err => {
                console.error("Fetch PV error:", err);
                return [];
            })
            .finally(() => {
                this.isFetching = false;
                if (!this.isInit) this.hideLoading();
            });
    }

    clearInvoiceTable() {
        this.selectedInvoiceIds = [];
        this.updateSelectAllCheckbox();

        const container = $('#rv_invoice_datatable_container');
        const $table = $('#rv_canvas_unpaid_invoice_list_table');

        // 🚀 Proper Cleanup: Destroy DataTable instance before emptying DOM
        if ($.fn.DataTable.isDataTable($table)) {
            $table.DataTable().destroy();
        }

        container.empty();
    }

    initDataTable() {
        const self = this;
        const $table = $('#rv_canvas_unpaid_invoice_list_table');
        if (!$table.length) return; // Safety check
        let data = [];

        // Prepare data: merge pre-fetched unpaid with currently linked invoices if in EDIT mode
        if (this.mode === 'create') {
            data = this.unpaidInvoices;
        } else if (this.mode === 'edit' && this.data) {
            const linkedIds = (this.data.invoices || []).map(inv => inv.id);
            const combined = [...(this.data.invoices || [])];
            this.unpaidInvoices.forEach(inv => {
                if (!linkedIds.includes(inv.id)) combined.push(inv);
            });
            data = combined;
        }

        // 🚀 SMART REUSE: If DataTable already exists, just update its data
        if ($.fn.DataTable.isDataTable($table)) {
            const dt = $table.DataTable();
            const currentPage = dt.page();

            dt.clear();
            dt.rows.add(data);
            dt.draw(false);
            dt.page(currentPage).draw('page');
            this.updateSelectAllCheckbox(); // 🚀 Force refresh Select All status
            return;
        }

        // 🚀 Fresh Initialisation (Matches Invoice standard)
        $table.DataTable({
            bFilter: false,
            bInfo: false,
            ordering: true,
            order: [[1, "asc"]],
            language: {
                search: ' ',
                sLengthMenu: '_MENU_',
                searchPlaceholder: "Search",
                info: "_START_ - _END_ of _TOTAL_ items",
                lengthMenu: "Show _MENU_ entries",
                emptyTable: "No unpaid invoices found.",
                paginate: {
                    next: 'Next <i class="fa fa-angle-right"></i>',
                    previous: '<i class="fa fa-angle-left"></i> Prev'
                },
            },
            initComplete: function (settings, json) {
                const $wrapper = $(settings.nTable).closest('.dataTables_wrapper');
                $wrapper.find('.dataTables_paginate').appendTo('.rv-canvas-table-unpaid-invoice-paginate');
                $wrapper.find('.dataTables_length').appendTo('.rv-canvas-table-unpaid-invoice-length');
                $wrapper.find('.dataTables_info').appendTo('.rv-datatable-info');
            },
            drawCallback: function () {
                self.updateSelectAllCheckbox();
            },
            data,
            columns: [
                {
                    data: 'id',
                    orderable: false,
                    render: (id, type, row) => {
                        const isChecked = self.selectedInvoiceIds.includes(id.toString());
                        return `
                        <div class="form-check custom-checkbox">
                            <input type="checkbox" class="form-check-input invoice-check" value="${id}" ${isChecked ? 'checked' : ''}>
                        </div>`;
                    }
                },
                {
                    data: 'invoice_number',
                    render: (val, type, row) => `
                        <div class="d-flex flex-column">
                            <span class="fw-medium text-dark">${val || row.code}</span>
                            <div class="d-flex gap-1 mt-1">
                                <span class="badge ${row.payment_status === 'PARTLY PAID' ? 'bg-info-transparent' : 'bg-warning-transparent'}" style="font-size: 0.65rem;">${row.payment_status}</span>
                                <small class="text-muted" style="font-size: 0.75rem;">ID: ${row.id}</small>
                            </div>
                        </div>`
                },
                {
                    data: 'due_date',
                    render: val => val ? new Date(val).toLocaleDateString('id-ID') : '-'
                },
                {
                    data: 'total_amount',
                    className: 'text-end',
                    render: (val, type, row) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: row.currency || 'IDR' }).format(val)
                },
                {
                    data: 'balance_due',
                    className: 'text-end fw-bold text-danger',
                    render: (val, type, row) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: row.currency || 'IDR' }).format(val)
                }
            ]
        });
    }

    updateSelectAllCheckbox() {
        const total = $('.invoice-check').length;
        const checked = $('.invoice-check:checked').length;
        $('#rv_select_all_invoices').prop('checked', total > 0 && total === checked);

        // --- SYNC NEW SEPARATION SECTION ---
        this.renderAllocationSection();
    }

    renderAllocationSection() {
        const container = $('#rv_allocation_container');
        const list = $('#rv_allocation_list');
        const ids = this.selectedInvoiceIds;

        if (ids.length === 0) {
            container.hide();
            list.empty();
            return;
        }

        container.show();

        const currentInDom = [];
        list.find('.rv_allocation_row').each(function () {
            currentInDom.push(String($(this).data('id')));
        });

        list.find('.rv_allocation_row').each(function () {
            const id = String($(this).data('id'));
            if (!ids.includes(id)) $(this).remove();
        });

        ids.forEach(id => {
            if (currentInDom.includes(String(id))) return;

            let inv = this.unpaidInvoices.find(v => String(v.id) === String(id));
            if (!inv && this.data && this.data.invoices) {
                inv = this.data.invoices.find(v => String(v.id) === String(id));
            }

            if (!inv) return;

            const balance = parseFloat(inv.balance_due || 0);
            let cashVal = balance;
            let pphVal = 0, bankVal = 0, wapuVal = 0, adjVal = 0;

            if (this.mode === 'edit' && inv.pivot) {
                cashVal = inv.pivot.amount_applied || 0;
                pphVal = inv.pivot.pph23_deduction || 0;
                bankVal = inv.pivot.bank_charge || 0;
                wapuVal = inv.pivot.ppn_wapu_deduction || 0;
                adjVal = inv.pivot.others_adjustment || 0;
            }

            // --- WAPU FIELD LOGIC ---
            let wapuHtml = '';
            if (inv.tax_type === 'Tax - WAPU') {
                wapuHtml = `
                    <div class="col-md-3">
                        <label class="form-label small mb-1 text-danger fw-bold">VAT Deduction (WAPU)</label>
                        <input type="text" class="form-control form-control-sm number-input rv-wapu-input bg-light-danger" 
                               data-id="${id}" value="${formatRupiahDisplay(wapuVal.toString())}">
                    </div>
                `;
            }

            const html = `
                <div class="card mb-3 border-dashed rv_allocation_row" data-id="${id}">
                    <div class="card-body p-3">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <div class="d-flex flex-column">
                                    <span class="fw-bold text-primary">${inv.invoice_number || inv.code}</span>
                                    <span class="badge bg-light text-dark align-self-start mt-1" style="font-size: 10px;">${inv.tax_type}</span>
                                    <small class="text-muted mt-1">Balance: <b class="text-danger">${formatRupiah(balance)}</b></small>
                                </div>
                            </div>
                            <div class="col-md-9 border-start">
                                <div class="row g-2">
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1 fw-semibold">To Apply (Cash)</label>
                                        <input type="text" class="form-control form-control-sm number-input rv-allocation-input" 
                                               data-id="${id}" value="${formatRupiahDisplay(cashVal.toString())}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small mb-1">PPh-23</label>
                                        <input type="text" class="form-control form-control-sm number-input rv-pph23-input" 
                                               data-id="${id}" value="${formatRupiahDisplay(pphVal.toString())}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small mb-1">Bank Chg</label>
                                        <input type="text" class="form-control form-control-sm number-input rv-bankcharge-input" 
                                               data-id="${id}" value="${formatRupiahDisplay(bankVal.toString())}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small mb-1">Others</label>
                                        <input type="text" class="form-control form-control-sm number-input rv-adjustment-input" 
                                               data-id="${id}" value="${formatRupiahDisplay(adjVal.toString())}">
                                    </div>
                                    ${wapuHtml}
                                </div>
                                <div class="mt-2 pt-2 border-top d-flex justify-content-between">
                                    <span class="small text-muted italic">* Ensure total settlement matches balance for full paid.</span>
                                    <span class="fw-bold text-dark">Total: <span class="rv-row-settlement-total" data-id="${id}">${formatRupiah(0)}</span></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            list.append(html);
        });

        this.updateAllocatedTotals();
    }

    updateAllocatedTotals() {
        let totalCashApplied = 0;
        const rvAmount = normalizeFormatRupiah($('#input_rv_amount').val() || "0");

        $('.rv_allocation_row').each((index, element) => {
            const cash = normalizeFormatRupiah($(element).find('.rv-allocation-input').val() || "0");
            const pph = normalizeFormatRupiah($(element).find('.rv-pph23-input').val() || "0");
            const bank = normalizeFormatRupiah($(element).find('.rv-bankcharge-input').val() || "0");
            const wapu = normalizeFormatRupiah($(element).find('.rv-wapu-input').val() || "0");
            const adj = normalizeFormatRupiah($(element).find('.rv-adjustment-input').val() || "0");

            const rowSum = cash + pph + bank + wapu + adj;
            $(element).find('.rv-row-settlement-total').text(formatRupiah(rowSum));

            totalCashApplied += cash;
        });

        $('#rv_total_allocated_display').text(formatRupiah(totalCashApplied));

        const unallocated = rvAmount - totalCashApplied;
        const display = $('#rv_unallocated_balance_display');
        display.text(formatRupiah(unallocated));

        if (unallocated < 0) {
            display.removeClass('text-success text-primary').addClass('text-danger');
        } else if (unallocated === 0) {
            display.removeClass('text-danger text-success').addClass('text-dark');
        } else {
            display.removeClass('text-danger text-dark').addClass('text-primary');
        }
    }


    async handleSubmit() {
        if (this.isSubmitting) return;
        this.isSubmitting = true;
        this.showLoading();

        const payload = this.validateFields();
        const errKeys = Object.keys(this.errors);

        if (errKeys.length) {
            errKeys.forEach(v => {
                const el = this.form.querySelector("#" + v);
                if (el) {
                    el.textContent = this.errors[v];
                    el.style.display = "block";
                }
            });
            this.hideLoading();
            this.initDataTable();
            this.isSubmitting = false;

            return;
        }

        if (this.mode === "create") {
            try {
                const response = await fetch("/rvs", {
                    method: 'POST',
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                    },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    $('#rv_list').DataTable().ajax.reload();
                    showToast("success", result.message || 'Receive voucher created successfully!');
                    if (this.closeForm) this.closeForm.click();
                    this.resetForm();
                } else {
                    showToast("error", result.message || "Failed to create receive voucher.");
                }
            } catch (err) {
                console.error(err);
                showToast("error", 'An error occurred while creating Receive Voucher.');
            } finally {
                this.isSubmitting = false;
                this.hideLoading();
            }
        } else {
            const id = payload.id;
            try {
                const response = await fetch(`/rvs/${id}`, {
                    method: 'PUT',
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                    },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    $('#rv_list').DataTable().ajax.reload(null, false);
                    showToast("success", result.message || 'Receive voucher updated successfully!');
                    if (this.closeForm) this.closeForm.click();
                    this.resetForm();
                } else {
                    showToast("error", result.message || "Failed to update receive voucher.");
                }
            } catch (err) {
                console.error(err);
                showToast("error", 'An error occurred while updating Receive Voucher.');
            } finally {
                this.isSubmitting = false;
                this.hideLoading();
            }
        }
    }
}

// ---------------------------------------- TRIGGER ----------------------------------------
document.addEventListener("DOMContentLoaded", () => {
    const RV_CANVAS = document.querySelector("#c_rv_canvas");
    const RV_MODAL = document.querySelector("#c_rv_modal");
    const RV_FORM = RV_CANVAS?.querySelector("form#c_rv_canvas_form")
        ? new ReceiveVoucherForm("c_rv_canvas_form")
        : null;
    const RV_CANVAS_BS = RV_CANVAS ? new bootstrap.Offcanvas(RV_CANVAS) : null;
    const RV_MODAL_BS = RV_MODAL ? new bootstrap.Modal(RV_MODAL) : null;

    let IS_FETCHING = false;

    document.addEventListener("click", async e => {
        let target = e.target;

        // CREATE
        if (target.matches("#c_rv_create_btn")) {
            e.preventDefault();
            if (RV_CANVAS_BS && RV_FORM && !IS_FETCHING) {
                IS_FETCHING = true;
                try {
                    RV_CANVAS_BS.show();
                    RV_FORM.resetForm();
                    await RV_FORM.init({ mode: "create" });
                } catch (error) {
                    console.error(error);
                    showToast("error", "An error occurred during initialization.");
                } finally {
                    IS_FETCHING = false;
                }
            }
        }

        // EDIT
        else if (target.closest(".c_rv_edit_btn")) {
            target = target.closest(".c_rv_edit_btn");
            e.preventDefault();
            if (RV_CANVAS_BS && RV_FORM && !IS_FETCHING) {
                IS_FETCHING = true;
                try {
                    const url = target.dataset.url;
                    const response = await fetch(url, { headers: { "Accept": "application/json" } });
                    const resJson = await response.json();
                    if (response.ok && resJson.success) {
                        RV_FORM.resetForm();
                        await RV_FORM.init({ mode: "edit", data: resJson.data });
                        RV_CANVAS_BS.show();
                    } else {
                        showToast("error", resJson.message || "Failed to fetch receive voucher data.");
                    }
                } catch (error) {
                    console.error(error);
                    showToast("error", "An error occurred while fetching receive voucher data.");
                } finally {
                    IS_FETCHING = false;
                }
            }
        }

        // DELETE
        else if (target.closest(".c_rv_delete_btn")) {
            target = target.closest(".c_rv_delete_btn");
            e.preventDefault();
            if (RV_MODAL_BS && RV_MODAL_BS) {
                const url = target.dataset.url;
                const confirmBtn = RV_MODAL.querySelector("#c_rv_modal_confirm_btn");
                if (confirmBtn) confirmBtn.dataset.url = url;
                RV_MODAL_BS.show();
            }
        }

        // CONFIRM DELETE
        else if (target.matches("#c_rv_modal_confirm_btn")) {
            e.preventDefault();
            if (RV_MODAL_BS && !IS_FETCHING) {
                IS_FETCHING = true;
                try {
                    const url = target.dataset.url;
                    const response = await fetch(url, {
                        method: "DELETE",
                        headers: {
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                            "Accept": "application/json"
                        }
                    });
                    const resJson = await response.json();
                    if (response.ok && resJson.success) {
                        $('#rv_list').DataTable().ajax.reload(null, false);
                        showToast("success", resJson.message || "Receive voucher deleted successfully.");
                        RV_MODAL_BS.hide();
                    } else {
                        showToast("error", resJson.message || "Failed to delete receive voucher.");
                    }
                } catch (error) {
                    console.error(error);
                    showToast("error", "An error occurred while deleting the receive voucher.");
                } finally {
                    IS_FETCHING = false;
                }
            }
        }
    });

});

// --- RUPIAH HELPERS ---
function formatRupiah(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(amount);
}

function formatRupiahDisplay(value) {
    if (!value) return "0";
    let val = value.toString().replace(/[^0-9]/g, "");
    if (!val) return "0";
    return new Intl.NumberFormat('id-ID').format(parseInt(val));
}

function normalizeFormatRupiah(value) {
    if (!value) return 0;
    // Remove Rp, Dots, and handle comma as decimal if any (though usually we keep it simple for IDR)
    let clean = value.toString().replace(/[^0-9]/g, "");
    return parseFloat(clean) || 0;
}
