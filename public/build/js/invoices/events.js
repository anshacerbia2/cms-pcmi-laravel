class InvoiceForm {
  isInit = true;
  selectedItems = [];
  isSubmitting = false;
  mode = "create";
  project = null;
  projects = [];
  proposal = null;
  proposals = [];
  pcmibanks = [];
  data = {};
  billingOptions = [];
  isFetching = false;
  errors = {};

  constructor (formId) {
    this.form = document.getElementById(formId);
    this.closeForm = document.getElementById("c_invoice_canvas_close_btn");
    this.handleSubmit = this.handleSubmit.bind(this);

    this.handleDocumentChange = this.handleDocumentChange.bind(this);
    this.handleDocumentInput = this.handleDocumentInput.bind(this);
    this.handleDocumentKeyDown = this.handleDocumentKeyDown.bind(this);

    this.form.addEventListener("submit", (e) => {
      e.preventDefault();
      this.handleSubmit()
    });

    document.addEventListener("change", this.handleDocumentChange);
    document.addEventListener("input", this.handleDocumentInput);
    document.addEventListener("keydown", this.handleDocumentKeyDown);
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

        const norm = normalizeFormatRupiah(before);
        const formatted = formatRupiahDisplay(norm);

        target.value = formatted;

        const delta = formatted.length - before.length;
        const newCaret = caret + delta > 0 ? caret + delta : 0;
        target.setSelectionRange(newCaret, newCaret);
      }
    }

    if (target.matches("#input_invoice_total_amount") || target.matches("#input_invoice_management_fee")) {
      this.calculateFitAmount();
    }

    if (target.matches("#input_invoice_invoice_number")) {
      let val = target.value;

      // Enforce P- prefix
      if (!val.startsWith("P-")) {
        // If they deleted it or are starting to type
        val = "P-" + val.replace(/^P-?/, "");
      }

      // Allow only P- followed by digits, then a / and then free text
      // But the requirement says "Mandatory 3 digits" after P-
      // Let's try to format it as P-XXX/

      let parts = val.split("/");
      let prefixPart = parts[0]; // Should be P-000
      let restPart = parts.slice(1).join("/");

      let digits = prefixPart.replace("P-", "").replace(/\D/g, "");

      // Limit digits to 3 for the prefix part? 
      // "Mandatory 3 digits" could mean exactly 3 or at least 3.
      // Usually it's exactly 3 for sequencing.
      if (digits.length > 3) {
        digits = digits.substring(0, 3);
      }

      let newPrefixPart = "P-" + digits;

      // Only auto-add / if we have 3 digits and no / yet
      if (digits.length === 3 && parts.length === 1 && !val.endsWith("/")) {
        target.value = newPrefixPart + "/";
      } else {
        target.value = newPrefixPart + (parts.length > 1 || val.endsWith("/") ? "/" : "") + restPart;
      }
    }
  }
  async handleDocumentChange(e) {
    const target = e.target;

    // Watch invoice type change
    if (target.matches("#input_invoice_billing_type")) {
      this.handleInvoiceTypeChange(target.value);
    }

    if (target.matches("#select_invoice_project_id")) {
      const val = target.value;
      const $proposal = $("#select_invoice_proposal_id");
      const $proposalLabelStar = $proposal.closest('.mb-3').find('label span.text-danger');
      const $projectLabelStar = $(target).closest('.mb-3').find('label span.text-danger');

      if (val) {
        $proposal.val("").trigger("change");
        $proposal.prop("disabled", true);
        $proposalLabelStar.hide();
        $projectLabelStar.show();

        const project = this.projects.find(p => +p.id === +val);
        this.project = project;
        this.proposal = null;
        this.updateInvoiceTypeOptions(project, null);

        // Update Customer
        $("#input_invoice_customer").val(project?.customer?.name || "");

        // Reset Billing Options
        this.billingOptions = [];
        const $billingSelect = $("#input_invoice_billing_option_id");
        if ($billingSelect.length) {
          $billingSelect.val("").trigger("change.select2").prop("disabled", true);
        }

        // Fetch Billing Options
        const customerId = project?.customer?.id;
        if (customerId) {
          this.fetchBillingOptions(customerId);
        }

        // Inject FIT Calculation Fields
        const fitHtml = this.getFitCalculationHTML();
        $("#invoice_fit_calculation_container").html(fitHtml);
        this.initPlugins();

      } else {
        this.project = null;
        $proposal.prop("disabled", false);
        if (!$proposal.val()) {
          $proposalLabelStar.show();
          $projectLabelStar.show();
        }

        // Reset type options if both empty
        if (!$proposal.val() && !val) {
          this.updateInvoiceTypeOptions(null, null);
          $("#input_invoice_customer").val("");
        }

        // Remove FIT Calculation Fields
        $("#invoice_fit_calculation_container").empty();

        // Reset Billing Options
        this.billingOptions = [];
        const $billingSelect = $("#input_invoice_billing_option_id");
        if ($billingSelect.length) {
          $billingSelect.val("").trigger("change.select2").prop("disabled", true);
        }
      }
    }

    if (target.matches("#select_invoice_proposal_id")) {
      const val = target.value;
      const $project = $("#select_invoice_project_id");
      const $projectLabelStar = $project.closest('.mb-3').find('label span.text-danger');
      const $proposalLabelStar = $(target).closest('.mb-3').find('label span.text-danger');

      if (val) {
        $projectLabelStar.hide();
        $proposalLabelStar.show();

        const proposal = this.proposals.find(p => +p.id === +val);
        this.proposal = proposal;
        this.project = null;
        this.updateInvoiceTypeOptions(null, proposal);

        // Update Customer
        $("#input_invoice_customer").val(proposal?.project?.customer?.name || "");

        // Reset Billing Options
        this.billingOptions = [];
        const $billingSelect = $("#input_invoice_billing_option_id");
        if ($billingSelect.length) {
          $billingSelect.val("").trigger("change.select2").prop("disabled", true);
        }

        // Fetch Billing Options
        const customerId = proposal?.project?.customer?.id;
        if (customerId) {
          this.fetchBillingOptions(customerId);
        }

        // --- Dynamic Field Swap: Project Select -> Project Name Input ---
        let $projectContainer = $("#select_invoice_project_id").closest('.mb-3').parent();
        if ($projectContainer.length) {
          $projectContainer.html(`
            <div class="mb-3">
              <label class="col-form-label">Project Name<span class="text-danger">*</span></label>
              <input type="text" id="input_invoice_project_name" class="form-control btn-disabled" value="${proposal?.project?.name || ''}" disabled>
              <small id="input_invoice_project_name_error" class="text-danger mt-1" style="display: none;"></small>
            </div>
        `);
        }

        // Inject Proposal Items Table (Manual Mode)
        // Only if Type is NOT Full Amount
        const currentType = $("#input_invoice_billing_type").val();

        if (currentType !== 'Full Amount') {
          const tableHtml = this.getProposalItemTableHTML();
          $("#invoice_canvas_proposal_item_section").html(tableHtml);
          this.initDataTable();
        } else {
          $("#invoice_canvas_proposal_item_section").empty();
        }

      } else {
        this.proposal = null;

        // --- Restore Project Select ---
        this.restoreProjectSelect();

        // Reset Billing Options
        this.billingOptions = [];
        const $billingSelect = $("#input_invoice_billing_option_id");
        if ($billingSelect.length) {
          $billingSelect.val("").trigger("change.select2").prop("disabled", true);
        }

        // Reset type options if both empty
        // Since we restored project select, it has no value.
        // We pass null, null.
        this.updateInvoiceTypeOptions(null, null);
        $("#input_invoice_customer").val("");


        // Clear Proposal Section
        $("#invoice_canvas_proposal_item_section").empty();
      }
    }

    if (target.matches("#invoice_canvas_proposal_item_list #select_all_invoice_proposal_item")) {
      const checked = target.checked;

      if (!checked) {
        this.selectedItems = [];
      }

      document.querySelectorAll('#invoice_canvas_proposal_item_list input.row-check').forEach(el => {
        el.checked = checked;

        if (checked) {
          this.selectedItems.push({
            id: el.value,
            description: el.dataset.description
          });
        }
      });

      const unique = new Map(this.selectedItems.map(item => [item.id, item]));
      this.selectedItems = Array.from(unique.values());
      this.updateSelectedEl();
      this.checkAndAutoChangeType();
    } else if (target.matches("#invoice_canvas_proposal_item_list input.row-check")) {
      const checked = target.checked;

      if (!checked) {
        document.querySelector("#invoice_canvas_proposal_item_list #select_all_invoice_proposal_item").checked = false;
        this.selectedItems = this.selectedItems.filter(obj => obj.id !== target.value)
      } else {
        this.selectedItems.push({
          id: target.value,
          description: target.dataset.description
        });
      }

      const unique = new Map(this.selectedItems.map(item => [item.id, item]));
      this.selectedItems = Array.from(unique.values());
      this.updateSelectedEl();
      this.checkAndAutoChangeType();
    } else if (target.matches("#input_invoice_vat_rate") || target.matches("#input_invoice_management_fee_type") || target.matches("#input_invoice_tax_type")) {
      this.calculateFitAmount();
    }
  }

  updateInvoiceTypeOptions(project, proposal) {
    let typeOptions = [];
    const $typeSelect = $("#input_invoice_billing_type");
    let currentVal = $typeSelect.val();

    if (proposal) {
      // --- Proposal Logic ---
      let isFullAllowed = true;
      const allInvoices = proposal.invoices || [];
      // If in Edit mode, exclude current invoice ID from the check
      const currentInvoiceId = (this.mode === 'edit' && this.data) ? this.data.id : null;

      const otherActiveInvoices = allInvoices.filter(inv => {
        if (currentInvoiceId && +inv.id === +currentInvoiceId) return false;
        return true;
      });

      if (otherActiveInvoices.length > 0) {
        isFullAllowed = false;
      }

      // Pricing Model A -> Only Full Amount
      if (proposal.pricing_model === 'A') {
        typeOptions = ["Full Amount"];
      } else {
        // If not A, check the other invoices rule
        if (!isFullAllowed) {
          typeOptions = ["Partly Payment"];
        } else {
          typeOptions = ["Full Amount", "Partly Payment"];
        }
      }
    } else if (project) {
      // If Project (Fit) -> Defaults to ["Full Amount", "Partly Payment"]
      typeOptions = ["Full Amount", "Partly Payment"];
    }

    // Rebuild Options
    $typeSelect.empty();

    if (typeOptions.length === 0) {
      $typeSelect.prop('disabled', true);
      // Optional: Add placeholder or keep empty
    } else {
      $typeSelect.prop('disabled', false);
      typeOptions.forEach(opt => {
        $typeSelect.append(new Option(opt, opt));
      });

      // Attempt to preserve selection, or default to first
      if (typeOptions.includes(currentVal)) {
        $typeSelect.val(currentVal);
      } else {
        $typeSelect.val(typeOptions[0]);
      }
    }

    // Trigger change to update UI alerts etc.
    $typeSelect.trigger('change');
    this.updatePaymentStatusOptions($typeSelect.val());
  }

  updatePaymentStatusOptions(type) {
    const $paymentStatusSelect = $("#input_invoice_payment_status");
    let currentVal = $paymentStatusSelect.val();
    let options = ["UNPAID"];

    if (type === "Full Amount") {
      options.push("FULLY PAID");
    } else if (type === "Partly Payment") {
      options.push("PARTLY PAID");
    }

    $paymentStatusSelect.empty();
    options.forEach(opt => {
      $paymentStatusSelect.append(new Option(opt, opt));
    });

    if (options.includes(currentVal)) {
      $paymentStatusSelect.val(currentVal).trigger('change.select2');
    } else {
      $paymentStatusSelect.val(options[0]).trigger('change.select2');
    }
  }

  // ---------------------------------------- FETCHER ----------------------------------------
  async fetchProjects() {
    this.isFetching = true;
    this.showLoading();
    return fetch(`/projects/all`, {
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
      .then(res => {
        this.projects = (res.data || []).filter(p => p.type.toLowerCase() === "fit");
      })
      .catch(err => {
        console.error("Fetch projects error:", err);
      })
      .finally(() => {
        this.isFetching = false
        if (!this.isInit) this.hideLoading();
      });
  }

  async fetchProposals() {
    this.isFetching = true;
    this.showLoading();
    return fetch(`/proposals/all`, {
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
      .then(res => {
        this.proposals = (res.data || []).filter(p => {
          return p.status.toLowerCase() === "win" &&
            p.project.type.toLowerCase() === "regular" &&
            Array.isArray(p.items) &&
            p.items.some(item => {
              // const isCancelled = p.invoices?.find(inv => inv.id === item.invoice_id)?.status.toLowerCase() === "cancelled";
              // return !item.invoice_id || isCancelled;
              return !item.invoice_id;
            });
        });
      })
      .catch(err => {
        console.error("Fetch proposals error:", err);
      })
      .finally(() => {
        this.isFetching = false
        if (!this.isInit) this.hideLoading();
      });
  }

  async fetchPcmiBanks() {
    this.isFetching = true;
    this.showLoading();
    return fetch(`/pcmibanks/all`, {
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
      .then(res => {
        this.pcmibanks = res.data || [];
      })
      .catch(err => {
        console.error("Fetch pcmibanks error:", err);
      })
      .finally(() => {
        this.isFetching = false
        if (!this.isInit) this.hideLoading();
      });
  }

  async fetchBillingOptions(customerId) {
    if (!customerId) return;
    this.isFetching = true;
    this.showLoading();

    return fetch(`/billing-options?customer_id=${customerId}`, {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        "Accept": "application/json",
        "X-Requested-With": "XMLHttpRequest",
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
      },
    })
      .then(response => {
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        return response.json();
      })
      .then(res => {
        this.billingOptions = res.data || [];
        this.populateBillingOptions();
      })
      .catch(err => {
        console.error("Fetch billing options error:", err);
      })
      .finally(() => {
        this.isFetching = false
        if (!this.isInit) this.hideLoading();
      });
  }

  populateBillingOptions() {
    const $select = $("#input_invoice_billing_option_id");
    if (!$select.length) return;

    $select.empty();
    $select.append(new Option("-- Select Billing To --", ""));

    this.billingOptions.forEach(opt => {
      const label = `${opt.cp_name} (${opt.address})`;
      const option = new Option(label, opt.id);
      $select.append(option);
    });

    if (this.data && this.data.billing_option_id) {
      $select.val(this.data.billing_option_id).trigger('change.select2');
    }

    $select.prop('disabled', this.billingOptions.length === 0);
  }

  calculateFitAmount() {
    const basicPrice = parseFloat(normalizeFormatRupiah(this.form.querySelector("#input_invoice_total_amount")?.value || "0").replace(",", ".")) || 0;
    const vatRate = parseFloat(this.form.querySelector("#input_invoice_vat_rate")?.value || 0);
    const mgmtFeeRaw = this.form.querySelector("#input_invoice_management_fee")?.value || "0";
    const mgmtFee = parseFloat(normalizeFormatRupiah(mgmtFeeRaw).replace(",", ".")) || 0;
    const mgmtType = this.form.querySelector("#input_invoice_management_fee_type")?.value || "percent";
    const taxType = this.form.querySelector("#input_invoice_tax_type")?.value || "Tax - Non WAPU";

    let mgmtAmount = 0;
    if (mgmtType === "percent") {
      mgmtAmount = (basicPrice * mgmtFee) / 100;
    } else {
      mgmtAmount = mgmtFee;
    }

    const salesAmount = basicPrice + mgmtAmount;
    let vatAmount = 0;

    if (taxType !== "No Tax") {
      vatAmount = (salesAmount * vatRate) / 100;
    }
    const totalAmount = salesAmount + vatAmount;

    const calcField = this.form.querySelector("#input_invoice_calculated_amount");
    if (calcField) {
      calcField.value = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(totalAmount);
    }
  }

  checkAndAutoChangeType() {
    let isAllSelected = true;

    if (this.project) return; // Skip auto-type for FIT projects

    if (this.proposal?.items?.length) {
      const currentItems = this.selectedItems.map(v => +v.id)
      const proposalItems = this.proposal.items.map(v => v.id)
      proposalItems.forEach(b => {
        if (!currentItems.includes(b)) {
          isAllSelected = false
        }
      })
    }

    const typeSelect = this.form.querySelector("#input_invoice_billing_type");

    if (typeSelect && isAllSelected) {
      $(typeSelect).val("Full Amount").trigger('change.select2');
      this.handleInvoiceTypeChange("Full Amount");
    }
  }

  getProposalItemTableHTML() {
    if (this.data && this.mode === "edit" && this.data.items?.length) {
      this.selectedItems = this.data.items.map(item => ({
        id: item.id.toString(),
        description: item.description
      }));
    }

    let selectedProposalEl = "<li class='no-selected-tag'>No Selected Items</li>";

    if (this.selectedItems.length) {
      selectedProposalEl = this.selectedItems
        .map(obj => `<li class="selected-tag">${obj.description}</li>`)
        .join("");
    }

    return `
      <div>
        <label class="col-form-label">Sales Item(s)</label>
        <ul id="selected_invoice_canvas_proposal_item" class="mt-2 mb-2">${selectedProposalEl}</ul>
      </div>
      <div style="border: 1px solid #e8e8e8; border-radius: 6px;">
        <div class="table-responsive custom-table">
          <table class="table" id="invoice_canvas_proposal_item_list">
            <thead class="thead-light">
              <tr>
                <th class="td-break no-sort" style="position: sticky; z-index: 1;">
                  <label class="checkboxs">
                    <input type="checkbox" id="select_all_invoice_proposal_item">
                    <span class="checkmarks"></span>
                  </label>
                </th>
                <th class="td-break" style="width: 100%;">Item Description</th>
                <th class="td-break">Total Price</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
        <div class="row align-items-center" style="row-gap: 1em; padding: 10px 15px;">
          <div class="col-md-6">
            <div class="d-flex align-items-center justify-content-center justify-content-md-start">
              <div class="datatable-info"></div>
              <div class="invoice-canvas-table-proposal-item-length"></div>
            </div>
          </div>
          <div class="col-md-6 flex-grow-1">
            <div class="invoice-canvas-table-proposal-item-paginate"></div>
          </div>
        </div>
      </div>
      <small id="invoice_proposal_item_error" class="text-danger mt-1" style="display: none;"></small>
    `;
  }

  handleInvoiceTypeChange(type) {
    const proposalItemSection = document.getElementById("invoice_canvas_proposal_item_section");
    const alertInfo = document.getElementById("invoice_type_alert_container");

    // UI Updates (Relevant for both, though alerts are mainly for proposals)
    if (alertInfo) {
      if (type === "Full Amount") {
        alertInfo.innerHTML = `
          <div class="alert alert-info">
            <i class="ti ti-info-circle me-2"></i>
            Full Amount Invoice: All available Items will be automatically included.
          </div>`;
      } else {
        alertInfo.innerHTML = `
          <div class="alert alert-info">
            <i class="ti ti-info-circle me-2"></i>
            Partly Payment Invoice: Please select specific Items to include in this invoice.
          </div>`;
      }
    }

    // Proposal-specific Item Selection logic
    if (this.proposal && proposalItemSection) {
      $(proposalItemSection).empty();

      if (type === "Full Amount") {
        this.selectedItems = [];
      } else {
        $(proposalItemSection).append(this.getProposalItemTableHTML());
        // Initialize DataTable for Partial
        this.initDataTable();
      }
    }

    // Always update payment status options based on type
    this.updatePaymentStatusOptions(type);
  }

  // ---------------------------------------- INIT ----------------------------------------
  async init({ mode = "create", project = null, proposal = null, data = null }) {
    this.resetForm();
    this.showLoading();
    this.mode = mode;
    this.project = project;
    this.proposal = proposal;
    this.data = data;

    if (!project && !proposal) {
      await Promise.all([this.fetchProjects(), this.fetchProposals(), this.fetchPcmiBanks()]);
    } else {
      await this.fetchPcmiBanks();
    }

    if (this.mode === "edit" && this.data?.customer_id) {
      this.fetchBillingOptions(this.data.customer_id);
    } else if (this.project?.customer?.id) {
      this.fetchBillingOptions(this.project.customer.id);
    } else if (this.proposal?.project?.customer?.id) {
      this.fetchBillingOptions(this.proposal.project.customer.id);
    }

    const formWrapper = document.createElement("div");
    formWrapper.innerHTML = this.generateForm();
    this.form.appendChild(formWrapper);
    if (this.project) {
      this.calculateFitAmount();
    }
    this.initPlugins();
    this.isInit = false;
    this.hideLoading();

    // Trigger initial invoice type change to set correct visibility
    const typeSelect = this.form.querySelector("#input_invoice_billing_type");
    if (typeSelect) {
      this.handleInvoiceTypeChange(typeSelect.value);
    }
  }

  // ---------------------------------------- DOM ----------------------------------------
  generateForm() {
    let value = {
      project_name: "",
      customer_name: "",
      proposal_code: "",
      description: "",
      invoice_number: "P-",
      billing_type: "Full Amount",
      status: "PREPARED",
      tax_type: "Tax - Non WAPU",
      pcmi_bank_id: "",
      invoice_date: moment().format('DD/MM/YY'),
      due_date: moment().add(30, 'days').format('DD/MM/YY'),
      payment_status: "UNPAID",
      total_amount: "0",
      vat_rate: "11",
      management_fee: "0",
      management_fee_type: "percent",
    }

    // Create With Proposal Data 
    if (this.proposal && this.mode === "create") { // Project Regular
      value.project_name = this.proposal.project?.name || "";
      value.customer_name = this.proposal.project?.customer?.name || "";
      value.proposal_code = this.proposal.code || "";
    } else if (this.project && this.mode === "create") { // Project Fit
      value.project_name = this.project.name || "";
      value.customer_name = this.project.customer?.name || "";
    }

    if (this.data && this.mode === "edit") {
      if (this.data && this.mode === "edit" && this.proposal) { // Project Regular
        value.project_name = this.proposal?.project?.name || "";
        value.proposal_code = this.data.proposal?.code || "";
      } else if (this.data && this.mode === "edit" && this.project) { // Project Fit
        value.project_name = this.project?.name || "";
        value.description = this.data.description || "";
        value.total_amount = formatRupiahDisplay(this.data.total_amount?.toString().replace(".", ",") || "0");
        value.vat_rate = this.data.vat_rate || "11";
        value.management_fee = formatRupiahDisplay(this.data.management_fee?.toString().replace(".", ",") || "0");
        value.management_fee_type = this.data.management_fee_type || "percent";
      }

      value.customer_name = this.data.customer?.name || "";
      value.billing_type = this.data?.billing_type || "Full Amount";
      value.status = this.data?.status || "PREPARED";
      value.payment_status = this.data?.payment_status || "UNPAID";
      value.invoice_number = this.data?.invoice_number || "";
      value.tax_type = this.data?.tax_type || "Tax - Non WAPU";
      value.pcmi_bank_id = this.data?.pcmi_bank_id || "";
      value.invoice_date = this.data?.invoice_date
        ? moment(this.data.invoice_date).format("YYYY-MM-DD")
        : "";
      value.due_date = this.data?.due_date
        ? moment(this.data.due_date).format("YYYY-MM-DD")
        : "";
    }

    // Check if 'Full' type allowed
    // Full is allowed only if there are NO other active invoices.
    let typeOptions = [];

    if (this.proposal) { // Project Regular
      let isFullAllowed = true;
      const allInvoices = this.proposal?.invoices || [];
      const currentInvoiceId = this.mode === 'edit' ? this.data?.id : null;

      const otherActiveInvoices = allInvoices.filter(inv => {
        // Ignore current invoice
        if (currentInvoiceId && +inv.id === +currentInvoiceId) return false;
        // Ignore cancelled invoices (optional, but typical)
        // if (inv.status === 'Cancelled') return false;
        return true;
      });

      if (otherActiveInvoices.length > 0) {
        isFullAllowed = false;
        // Force type to Partly Payment if Full Amount was selected/defaulted but not allowed
        if (value.billing_type === 'Full Amount') {
          value.billing_type = 'Partly Payment';
        }
      }
      typeOptions = isFullAllowed ? ["Full Amount", "Partly Payment"] : ["Partly Payment"];
      // Type A: Only "Full Amount" allowed
      if (this.proposal?.pricing_model === 'A' || this.data?.proposal?.pricing_model === 'A') {
        typeOptions = ["Full Amount"];
        value.billing_type = "Full Amount";
      }
    } else if (this.project) { // Project Fit
      typeOptions = ["Full Amount", "Partly Payment"];
    }

    const selectTypeOptions = typeOptions.map(t => {
      return `<option value="${t}" ${t === value.billing_type ? "selected" : ""}>${t}</option>`;
    });

    const selectStatusOptions = ["VOID", "REVISED", "PREPARED", "SENT"].map(t => {
      return `<option value="${t}" ${t === value.status ? "selected" : ""}>${t}</option>`;
    });

    const selectTaxOptions = ["No Tax", "Tax - Non WAPU", "Tax - WAPU"].map(t => {
      return `<option value="${t}" ${t === value.tax_type ? "selected" : ""}>${t}</option>`;
    });

    const selectBankOptions = this.pcmibanks.map(b => {
      return `<option value="${b.id}" ${b.id == value.pcmi_bank_id ? "selected" : ""}>${b.bank?.bank_name || 'Bank'} - ${b.account_no}</option>`;
    });

    const selectProjectOptions = this.projects.map(t => {
      return `<option value="${t.id}">${t.name}</option>`;
    });

    const selectProposalOptions = this.proposals.map(t => {
      return `<option value="${t.id}">${t.code}</option>`;
    });

    return `
      <div class="row">
        ${!this.project && !this.proposal ? `
          <div class="col-md-6">
            <div class="mb-3">
              <label class="col-form-label">Project<span class="text-danger">*</span></label>
              <select id="select_invoice_project_id" class="select form-select" ${this.projects.length ? "" : "disabled"}>
                <option value="">-- Select Project --</option>
                ${selectProjectOptions.join("")}
              </select>
              <small id="select_invoice_project_id_error" class="text-danger mt-1" style="display: none;"></small>
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="col-form-label">Proposal <span class="text-danger">*</span></label>
              <select id="select_invoice_proposal_id" class="select form-select" ${this.proposals.length ? "" : "disabled"}>
                <option value="">-- Select Proposal --</option>
                ${selectProposalOptions.join("")}
              </select>
              <small id="select_invoice_proposal_id_error" class="text-danger mt-1" style="display: none;"></small>
            </div>
          </div>
          ` : `
          <div class="col-md-6">
            <div class="mb-3">
              <label class="col-form-label">Project Name<span class="text-danger">*</span></label>
              <input type="text" id="input_invoice_project_name" class="form-control btn-disabled" value="${value.project_name}" disabled>
              <small id="input_invoice_project_name_error" class="text-danger mt-1" style="display: none;"></small>
            </div>
          </div>
        `}
        <div class="col-md-6">
          <div class="mb-3">
            <label class="col-form-label">Customer<span class="text-danger">*</span></label>
            <input type="text" id="input_invoice_customer" class="form-control btn-disabled" value="${value.customer_name}" disabled>
            <small id="input_invoice_customer_error" class="text-danger mt-1" style="display: none;"></small>
          </div>
        </div>
        <div class="col-md-6">
          <div class="mb-3">
            <label class="col-form-label">Billing To<span class="text-danger">*</span></label>
            <select id="input_invoice_billing_option_id" class="select form-control" disabled>
              <option value="">-- Select Billing To --</option>
            </select>
            <small id="input_invoice_billing_option_id_error" class="text-danger mt-1" style="display: none;"></small>
          </div>
        </div>
        ${this.proposal ? `
          <div class="col-md-6">
            <div class="mb-3">
              <label class="col-form-label">Proposal Code<span class="text-danger">*</span></label>
              <input type="text" id="input_invoice_proposal_code" class="form-control btn-disabled" value="${value.proposal_code}" disabled>
              <small id="input_invoice_proposal_code_error" class="text-danger mt-1" style="display: none;"></small>
            </div>
          </div>
        ` : ""}
        <div class="col-md-6">
          <div class="mb-3">
            <label class="col-form-label">Type<span class="text-danger">*</span></label>
            <select id="input_invoice_billing_type" class="select form-control" ${typeOptions.length ? "" : "disabled"}>
              ${selectTypeOptions}
            </select>
            <small id="input_invoice_billing_type_error" class="text-danger mt-1" style="display: none;"></small>
          </div>
        </div>
        <div class="col-md-6">
          <div class="mb-3">
            <label class="col-form-label">Invoice Number<span class="text-danger">*</span></label>
            <input type="text" id="input_invoice_invoice_number" class="form-control" value="${value.invoice_number}">
            <small id="input_invoice_invoice_number_error" class="text-danger mt-1" style="display: none;"></small>
          </div>
        </div>
        <div class="col-md-6">
          <div class="mb-3">
            <label class="col-form-label">Status<span class="text-danger">*</span></label>
            <select id="input_invoice_status" class="select form-control">
              ${selectStatusOptions}
            </select>
            <small id="input_invoice_status_error" class="text-danger mt-1" style="display: none;"></small>
          </div>
        </div>
        <div class="col-md-6">
          <div class="mb-3">
            <label class="col-form-label">Payment Status<span class="text-danger">*</span></label>
            <select id="input_invoice_payment_status" class="select form-control">
              <option value="UNPAID" ${value.payment_status === "UNPAID" ? "selected" : ""}>UNPAID</option>
              <option value="PARTLY PAID" ${value.payment_status === "PARTLY PAID" ? "selected" : ""}>PARTLY PAID</option>
              <option value="FULLY PAID" ${value.payment_status === "FULLY PAID" ? "selected" : ""}>FULLY PAID</option>
            </select>
            <small id="input_invoice_payment_status_error" class="text-danger mt-1" style="display: none;"></small>
          </div>
        </div>
        <div class="col-md-6">
          <div class="mb-3">
            <label class="col-form-label">Taxation<span class="text-danger">*</span></label>
            <select id="input_invoice_tax_type" class="select form-control">
              ${selectTaxOptions}
            </select>
            <small id="input_invoice_tax_type_error" class="text-danger mt-1" style="display: none;"></small>
          </div>
        </div>
        <div class="col-md-6">
          <div class="mb-3">
            <label class="col-form-label">Bank Account<span class="text-danger">*</span></label>
            <select id="input_invoice_pcmi_bank_id" class="select form-control">
              <option value="">-- Select Bank --</option>
              ${selectBankOptions.join("")}
            </select>
            <small id="input_invoice_pcmi_bank_id_error" class="text-danger mt-1" style="display: none;"></small>
          </div>
        </div>
        <div class="col-md-6">
          <div class="mb-3">
            <label class="col-form-label">Invoice Date</label>
            <div class="icon-form">
              <span class="form-icon"><i class="ti ti-calendar-event"></i></span>
              <input id="input_invoice_invoice_date" type="text" class="form-control btn-disabled" placeholder="DD/MM/YY" value="${value.invoice_date}" disabled>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="mb-3">
            <label class="col-form-label">Due Date<span class="text-danger">*</span></label>
            <div class="icon-form">
              <span class="form-icon"><i class="ti ti-calendar-event"></i></span>
              <input id="input_invoice_due_date" type="text" class="form-control datetimepicker" placeholder="DD/MM/YY" value="${value.due_date}">
            </div>
            <small id="input_invoice_due_date_error" class="text-danger mt-1" style="display: none;"></small>
          </div>
        </div>
        <div class="col-md-12 mb-3" id="invoice_type_alert_container"></div>
        <div class="col-md-12 mb-3" id="invoice_canvas_proposal_item_section">
          ${this.proposal && value.type !== "Full" ? this.getProposalItemTableHTML() : ""}
        </div>
        </div>
      </div>
      
      <!-- FIT Calculator Fields -->
      <div id="invoice_fit_calculation_container">
        ${this.project ? this.getFitCalculationHTML(value) : ''}
      </div>

      <div class="d-flex align-items-center justify-content-end mt-4">
        <a href="javascript:void(0)" class="btn btn-light me-2" data-bs-dismiss="offcanvas">Cancel</a>
        <button type="submit" class="btn btn-primary">Save</button>
      </div>
    `;
  }

  getFitCalculationHTML(value = {}) {
    // Defaults for values if not provided
    value.description = value.description || "";
    value.total_amount = value.total_amount || "0";
    value.management_fee_type = value.management_fee_type || "percent";
    value.management_fee = value.management_fee || "0";
    value.vat_rate = value.vat_rate || "11";

    return `
      <div class="row border p-2 mb-3 rounded bg-light">
          <div class="col-12"><h6 class="text-primary"><i class="ti ti-calculator me-1"></i>Amount Calculation</h6></div>
          <div class="col-md-12">
            <div class="mb-3">
              <label class="col-form-label">Description (Item/Service)<span class="text-danger">*</span></label>
              <textarea id="input_invoice_description" class="form-control" rows="2">${value.description}</textarea>
              <small id="input_invoice_description_error" class="text-danger mt-1" style="display: none;"></small>
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
                <label class="col-form-label">Basic Price (IDR)<span class="text-danger">*</span></label>
                <input type="text" id="input_invoice_total_amount" class="form-control number-input" value="${value.total_amount}">
                <small id="input_invoice_total_amount_error" class="text-danger mt-1" style="display: none;"></small>
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
                <label class="col-form-label">Management Fee Type<span class="text-danger">*</span></label>
                <select id="input_invoice_management_fee_type" class="select form-control">
                    <option value="percent" ${value.management_fee_type == 'percent' ? 'selected' : ''}>Percent (%)</option>
                    <option value="nominal" ${value.management_fee_type == 'nominal' ? 'selected' : ''}>Nominal (IDR)</option>
                </select>
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
                <label class="col-form-label">Management Fee Value</label>
                <input type="text" id="input_invoice_management_fee" class="form-control number-input" value="${value.management_fee}">
                <small id="input_invoice_management_fee_error" class="text-danger mt-1" style="display: none;"></small>
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
                <label class="col-form-label">VAT Rate (%)<span class="text-danger">*</span></label>
                <select id="input_invoice_vat_rate" class="select form-select">
                    <option value="11" ${value.vat_rate == 11 ? 'selected' : ''}>11%</option>
                    <option value="1" ${value.vat_rate == 1 ? 'selected' : ''}>1%</option>
                </select>
                <small id="input_invoice_vat_rate_error" class="text-danger mt-1" style="display: none;"></small>
            </div>
          </div>
          <div class="col-12">
            <div class="mb-3">
                <label class="col-form-label fw-bold">Calculated Invoice Amount</label>
                <input type="text" id="input_invoice_calculated_amount" class="form-control fw-bold text-success" readonly>
                <small class="text-muted">Basic + Management Fee + VAT</small>
            </div>
          </div>
      </div>
     `;
  }

  restoreProjectSelect() {
    const $nameInput = $("#input_invoice_project_name");

    // Only restore if we are currently showing the name input (meaning it was swapped)
    // OR if the select is missing (safety).
    if ($nameInput.length) {
      let $container = $nameInput.closest('.mb-3').parent();

      const selectProjectOptions = this.projects.map(t => {
        return `<option value="${t.id}">${t.name}</option>`;
      });

      $container.html(`
        <div class="mb-3">
          <label class="col-form-label">Project<span class="text-danger">*</span></label>
          <select id="select_invoice_project_id" class="select form-select">
            <option value="">-- Select Project --</option>
            ${selectProjectOptions.join("")}
          </select>
          <small id="select_invoice_project_id_error" class="text-danger mt-1" style="display: none;"></small>
        </div>
      `);

      // Re-init plugins (Select2)
      // We only need to init the new select.
      this.initPlugins();
    }
  }

  updateSelectedEl() {
    const el = this.form.querySelector("#selected_invoice_canvas_proposal_item");
    if (el) {
      if (this.selectedItems.length) {
        el.innerHTML = this.selectedItems
          .map(obj => `<li class="selected-tag">${obj.description}</li>`)
          .join("");
      } else {
        el.innerHTML = `<li class="no-selected-tag">No Selected Item</li>`;
      }
    }
  }

  initDataTable() {
    const self = this;
    const $table = $('#invoice_canvas_proposal_item_list');
    let data = [];

    if (this.proposal && this.proposal.items && this.mode === "create") {
      data = this.proposal.items.filter(item => {
        // const isCancelled = this.proposal.invoices?.find(inv => inv.id === item.invoice_id)?.status.toLowerCase() === "cancelled";
        // return !item.invoice_id || isCancelled;
        return !item.invoice_id;
      });
    } else if (this.data?.proposal?.items && this.mode === "edit") {
      console.log("ASU", this.data);

      data = this.data.proposal.items.filter(item => {
        const isSelected = this.data.items.some(b => b.id === item.id);
        // const isCancelled = this.proposal.invoices?.find(inv => inv.id === item.invoice_id)?.status.toLowerCase() === "cancelled";
        // return !item.invoice_id || isSelected || isCancelled;
        return !item.invoice_id || isSelected;
      });
    }

    // 🔹 Kalau DataTable sudah ada → cuma update data-nya
    if ($.fn.DataTable.isDataTable($table)) {
      const dt = $table.DataTable();
      const currentPage = dt.page(); // simpan halaman sekarang

      dt.clear();
      dt.rows.add(data);
      dt.draw(false); // false = jangan reset pagination
      dt.page(currentPage).draw('page'); // balik ke halaman yang sama
      return; // selesai, gak usah reinit
    }

    // 🚀 Kalau belum ada → inisialisasi pertama kali
    $table.DataTable({
      bFilter: false,
      bInfo: false,
      ordering: true,
      order: [[0, "desc"]],
      language: {
        search: ' ',
        sLengthMenu: '_MENU_',
        searchPlaceholder: "Search",
        info: "_START_ - _END_ of _TOTAL_ items",
        lengthMenu: "Show _MENU_ entries",
        emptyTable: "No Items available for billing.",
        paginate: {
          next: 'Next <i class="fa fa-angle-right"></i>',
          previous: '<i class="fa fa-angle-left"></i> Prev'
        },
      },
      initComplete: function (settings, json) {
        const $wrapper = $(settings.nTable).closest('.dataTables_wrapper');
        $wrapper.find('.dataTables_paginate').appendTo('.invoice-canvas-table-proposal-item-paginate');
        $wrapper.find('.dataTables_length').appendTo('.invoice-canvas-table-proposal-item-length');
      },
      data,
      columns: [
        {
          data: 'id',
          render: function (data, type, row) {
            const checked = self.selectedItems.some(obj => +obj.id === data);
            return `
            <label class="checkboxs">
              <input type="checkbox" class="row-check" ${checked ? "checked" : ""} value="${data}" data-description="${row.description}">
              <span class="checkmarks"></span>
            </label>
          `;
          }
        },
        { data: 'description' },
        {
          data: 'total_price',
          render: data => formatRupiahDisplay(data.toString().replace(".", ","))
        },
      ]
    });
  }

  initPlugins() {
    if (window.$ && $.fn.select2) {
      $('.select').select2({
        width: '100%',
        dropdownParent: $('#c_invoice_canvas_form')
      });

      // bridge event agar change bisa dideteksi
      $('.select').on('select2:select', function () {
        this.dispatchEvent(new Event('change', { bubbles: true }));
      });
    }

    if ($('.datetimepicker').length && $.fn.datetimepicker) {
      $('.datetimepicker').each(function () {
        const el = $(this);
        const rawValue = el.val();
        const isIso = rawValue && moment(rawValue, moment.ISO_8601, true).isValid();
        const isFormat = rawValue && moment(rawValue, 'DD/MM/YY', true).isValid();
        const parsedDate = isIso ? moment(rawValue) : (isFormat ? moment(rawValue, 'DD/MM/YY') : null);

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

  resetForm() {
    this.isInit = true;
    this.selectedItems = [];
    this.isSubmitting = false;
    this.mode = "create";
    this.project = null;
    this.proposal = null;
    this.data = null;
    this.billingOptions = [];
    this.errors = {};
    this.form.innerHTML = "";
    this.loadingEl = null;
  }

  // ---------------------------------------- DATA & SUBMISSION ----------------------------------------
  resetErrorFields() {
    const errKeys = Object.keys(this.errors);
    if (errKeys.length) {
      errKeys.forEach(v => {
        const el = this.form.querySelector(`#${v}`);
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
    const payload = {};

    // 1. Try to get from class properties (Pre-filled context)
    payload.project_id = this.project?.id || null;
    payload.proposal_id = this.proposal?.id || null;

    // 2. If valid manual selection exists (Create Mode with no pre-fill), override
    if (!payload.project_id && !payload.proposal_id && this.mode === 'create') {
      const selectedProject = $("#select_invoice_project_id").val();
      const selectedProposal = $("#select_invoice_proposal_id").val();

      if (selectedProject) payload.project_id = selectedProject;
      if (selectedProposal) payload.proposal_id = selectedProposal;
    }

    if (this.mode === "edit") {
      payload.proposal_id = this.data?.proposal_id || null;
      // If editing FIT, project_id is in data
      if (this.data?.project_id) {
        payload.project_id = this.data.project_id;
      }
    }

    // Attempt to get customer_id
    if (this.mode === 'edit') {
      payload.customer_id = this.data?.customer_id || null;
    } else {
      // Create Mode
      if (this.proposal) {
        payload.customer_id = this.proposal.project?.customer?.id;
      } else if (this.project) {
        payload.customer_id = this.project.customer?.id;
      } else {
        // Manual Mode: Find in loaded arrays
        if (payload.proposal_id) {
          const p = this.proposals.find(x => x.id == payload.proposal_id);
          payload.customer_id = p?.project?.customer?.id;
        } else if (payload.project_id) {
          const p = this.projects.find(x => x.id == payload.project_id);
          payload.customer_id = p?.customer?.id;
        }
      }
    }

    // Validation: Proposal ID required ONLY if Project ID is missing
    if (!payload.project_id && !payload.proposal_id) {
      const msg = "Proposal or Project is required.";
      this.errors["input_invoice_proposal_code_error"] = msg;
      this.errors["input_invoice_project_name_error"] = msg;
      this.errors["select_invoice_proposal_id_error"] = msg;
      this.errors["select_invoice_project_id_error"] = msg;
    }

    if (!payload.customer_id) {
      this.errors["input_invoice_customer_error"] = "Customer ID is required.";
    }

    const inputs = [
      {
        field: "input_invoice_billing_type",
        required: true,
        message: "Invoice Type is required."
      },
      {
        field: "input_invoice_status",
        required: true,
        message: "Status is required."
      },
      {
        field: "input_invoice_invoice_number",
        required: true,
        message: "Invoice Number is required."
      },
      {
        field: "input_invoice_tax_type",
        required: true,
        message: "Taxation is required."
      },
      {
        field: "input_invoice_pcmi_bank_id",
        required: true,
        message: "Bank Account is required."
      },
      {
        field: "input_invoice_due_date",
        date: true,
        required: true,
        message: "Due Date is required."
      },
      {
        field: "input_invoice_payment_status",
        required: true,
        message: "Payment status is required."
      },
      {
        field: "input_invoice_billing_option_id",
        required: true,
        message: "Billing address is required."
      },
    ];

    // Add FIT fields validation
    if (this.project) {
      inputs.push(
        { field: "input_invoice_description", required: true, message: "Description is required." },
        { field: "input_invoice_total_amount", required: true, message: "Basic Price is required." },
        { field: "input_invoice_vat_rate", required: true, message: "VAT Rate is required." },
        { field: "input_invoice_management_fee", required: true, message: "Management Fee is required." }, // Assuming required, or false if optional
        { field: "input_invoice_management_fee_type", required: true, message: "Mgmt Fee Type is required." }
      );
    }

    inputs.forEach(id => {
      const el = this.form.querySelector("#" + id.field);
      let value = el ? el.value.trim() : "";

      if (value && id.date) {
        value = moment(value, 'DD/MM/YY').format('YYYY-MM-DD')
      }

      if (id.field !== "input_invoice_invoice_date") {
        payload[id.field.replace("input_invoice_", "")] = value;
      }

      if (!value && id.required) {
        this.errors[id.field + "_error"] = id.message || "This field is required.";
      }

      // Special validation for Invoice Number
      if (id.field === "input_invoice_invoice_number" && value) {
        const regex = /^P-\d{3}\//;
        if (!regex.test(value)) {
          this.errors[id.field + "_error"] = "Invoice Number must follow the format: P-000/ (Mandatory 3 digits).";
        }
      }
    });

    if (payload.due_date) {
      const today = moment().startOf('day');
      const dueDate = moment(payload.due_date);
      if (!dueDate.isAfter(today)) {
        this.errors["input_invoice_due_date_error"] = "Due date must be after invoice date.";
      }
    }

    if (this.project) {
      if (payload.total_amount) payload.total_amount = parseFloat(normalizeFormatRupiah(payload.total_amount).replace(",", "."));
      if (payload.management_fee) payload.management_fee = parseFloat(normalizeFormatRupiah(payload.management_fee).replace(",", "."));
    }

    // Only send item_ids for Partly Payment invoice type
    if (payload.billing_type === "Partly Payment" && payload.proposal_id) {
      payload.item_ids = this.selectedItems.map(obj => obj.id);

      // Validate Item selection for Partly Payment type
      if (!payload.item_ids.length) {
        this.errors["invoice_proposal_item_error"] = "Please select at least one Item for partial invoice.";
      }
    }

    return payload;
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
          el.innerText = this.errors[v];
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
        const response = await fetch("/invoices", {
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
          try {
            // On proposal.detail page
            loadProjectData(PROJECT_ID);
          } catch (error) { }
          try {
            // On proposal.detail page
            loadProposalData(PROPOSAL_ID);
          } catch (error) { }
          $('#invoice_list').DataTable().ajax.reload();
          $('#proposal_list').DataTable().ajax.reload(); // On proposal index page 
          showToast("success", result.message || 'Invoice created successfully!');
          if (this.closeForm) this.closeForm.click();
          this.resetForm();
        } else {
          showToast("error", `${result.errors?.item_ids || result.message}`);
        }
      } catch (err) {
        showToast("error", 'An error occurred while creating Invoice.');
      } finally {
        this.isSubmitting = false;
        this.hideLoading()
      }
    } else if (this.mode === "edit") {
      try {
        const response = await fetch(`/invoices/${this.data.id}`, {
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
          try {
            // On proposal.detail page
            loadProjectData(PROJECT_ID);
          } catch (error) { }
          try {
            // On proposal.detail page
            loadProposalData(PROPOSAL_ID);
          } catch (error) { }
          $('#invoice_list').DataTable().ajax.reload(null, false);
          $('#proposal_list').DataTable().ajax.reload(); // On proposal index page 
          showToast("success", result.message || 'Invoice created successfully!');
          if (this.closeForm) this.closeForm.click();
          this.resetForm();
        } else {
          showToast("error", `${result.errors?.item_ids || result.message}`);
        }
      } catch (err) {
        showToast("error", 'An error occurred while creating Invoice.');
      } finally {
        this.isSubmitting = false;
        this.hideLoading()
      }
    }
  }
}

// ----------------------------------------------- TRIGER -----------------------------------------------
document.addEventListener("DOMContentLoaded", () => {
  const INVOICE_CANVAS = document.querySelector("#c_invoice_canvas");
  const INVOICE_MODAL = document.querySelector("#c_invoice_modal");
  const INVOICE_FORM = INVOICE_CANVAS?.querySelector("form#c_invoice_canvas_form")
    ? new InvoiceForm("c_invoice_canvas_form")
    : null;
  const INVOICE_CANVAS_BS = INVOICE_CANVAS ? new bootstrap.Offcanvas(INVOICE_CANVAS) : null;
  const INVOICE_MODAL_BS = INVOICE_MODAL ? new bootstrap.Modal(INVOICE_MODAL) : null;

  document.addEventListener("click", async e => {
    let target = e.target;

    // CREATE
    if (target.matches("#c_invoice_create_btn")) {
      e.preventDefault();
      if (INVOICE_CANVAS_BS && INVOICE_FORM && !IS_FETCHING) {
        IS_FETCHING = true;

        try {
          let url = target.dataset.url;
          let type = target.dataset.type;

          // --- Intelligent Context Detection ---
          // If button is "naked" (no url/type), check for global context anchors
          if (!url && !type) {
            try {
              if (typeof PROPOSAL_ID !== 'undefined' && PROPOSAL_ID) {
                url = `/proposals/${PROPOSAL_ID}`;
                type = "proposal";
              } else if (typeof PROJECT_ID !== 'undefined' && PROJECT_ID) {
                url = `/projects/${PROJECT_ID}`;
                type = "fit";
              }
            } catch (e) { }
          }

          if (!url && !type) {
            // General Create Mode (Invoice list page)
            INVOICE_CANVAS_BS.show();
            INVOICE_FORM.resetForm();
            await INVOICE_FORM.init({ mode: "create" });
          } else {
            // Contextual Create Mode (Detail pages or specific buttons)
            const response = await fetch(url, {
              headers: { "Accept": "application/json" },
            });
            const resJson = await response.json();

            if (response.ok && resJson.success) {
              const data = resJson.data;

              INVOICE_CANVAS_BS.show();
              INVOICE_FORM.resetForm();
              if (type == "fit") {
                await INVOICE_FORM.init({ mode: "create", project: data });
              } else {
                await INVOICE_FORM.init({ mode: "create", proposal: data });
              }
            } else {
              showToast("error", resJson.message || "Failed to retrieve context data for invoice creation.");
            }
          }
        } catch (error) {
          console.error(error);
          showToast("error", "An error occurred while setting up invoice creation.");
        } finally {
          IS_FETCHING = false;
        }
      }
    }

    // EDIT
    else if (target.closest(".c_invoice_edit_btn")) {
      target = target.closest(".c_invoice_edit_btn");
      e.preventDefault();
      if (INVOICE_CANVAS_BS && INVOICE_FORM && !IS_FETCHING) {
        IS_FETCHING = true;

        try {
          let proposal_id = target.dataset.proposal_id;
          let project_id = target.dataset.project_id;
          const invoiceUrl = target.dataset.url;

          // Attempt to get global PROPOSAL_ID / PROJECT_ID if applicable
          try { if (!proposal_id && typeof PROPOSAL_ID !== 'undefined') proposal_id = PROPOSAL_ID; } catch (e) { }
          try { if (!project_id && typeof PROJECT_ID !== 'undefined') project_id = PROJECT_ID; } catch (e) { }

          // --- Step 1: Fetch Invoice Data First ---
          const invoiceRes = await fetch(invoiceUrl, {
            headers: { "Content-Type": "application/json", "Accept": "application/json" }
          });
          const invoiceJson = await invoiceRes.json();

          if (!invoiceRes.ok || !invoiceJson.success) {
            showToast("error", invoiceJson?.message || "Failed to fetch invoice data.");
            IS_FETCHING = false;
            return;
          }

          const invoice = invoiceJson.data;

          // Re-verify IDs from fetched invoice data if they were missing from dataset
          if (!proposal_id) proposal_id = invoice.proposal_id;
          if (!project_id) project_id = invoice.project_id;

          // --- Step 2: Fetch Context (Proposal or Project) ---
          let contextRes = null;
          let isFit = false;

          if (proposal_id) {
            contextRes = await fetch(`/proposals/${proposal_id}`, {
              headers: { "Content-Type": "application/json", "Accept": "application/json" }
            });
          } else if (project_id) {
            isFit = true;
            contextRes = await fetch(`/projects/${project_id}`, {
              headers: { "Content-Type": "application/json", "Accept": "application/json" }
            });
          }

          if (!contextRes) {
            showToast("error", "Missing Proposal or Project reference in this invoice.");
            IS_FETCHING = false;
            return;
          }

          const contextJson = await contextRes.json();

          if (contextRes.ok && contextJson.success) {
            const contextData = contextJson.data;
            INVOICE_CANVAS_BS.show();
            INVOICE_FORM.resetForm();

            if (isFit) {
              await INVOICE_FORM.init({ mode: "edit", project: contextData, data: invoice });
            } else {
              await INVOICE_FORM.init({ mode: "edit", proposal: contextData, data: invoice });
            }
          } else {
            showToast("error", contextJson?.message || "Failed to fetch related project/proposal data.");
          }
        } catch (error) {
          console.error(error);
          showToast("error", "An error occurred while retrieving invoice context.");
        } finally {
          IS_FETCHING = false;
        }
      }
    }

    // DELETE
    else if (target.closest(".c_invoice_delete_btn")) {
      target = target.closest(".c_invoice_delete_btn");
      e.preventDefault();
      if (INVOICE_MODAL && INVOICE_MODAL_BS) {
        const url = target.dataset.url;
        const confirmBtn = INVOICE_MODAL.querySelector("#c_invoice_modal_confirm_btn");
        confirmBtn.dataset.url = url;
        INVOICE_MODAL_BS.show();
      }
    }

    // CONFIRM DELETE 
    else if (target.matches("#c_invoice_modal_confirm_btn")) {
      e.preventDefault();
      if (INVOICE_MODAL_BS && !IS_FETCHING) {
        IS_FETCHING = true;

        try {
          const url = target.dataset.url;
          const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute("content");

          const resopnse = await fetch(url, {
            method: "DELETE",
            headers: {
              "X-CSRF-TOKEN": csrfToken,
              "Accept": "application/json"
            }
          });
          const resJson = await resopnse.json();

          if (resopnse.ok && resJson.success) {
            try {
              // On proposal.detail page
              loadProjectData(PROJECT_ID);
            } catch (error) { }
            try {
              // On proposal.detail page
              loadProposalData(PROPOSAL_ID);
            } catch (error) { }

            $('#invoice_list').DataTable().ajax.reload(null, false);
            showToast("success", resJson.message || "Invoice deleted successfully.");
            INVOICE_MODAL_BS.hide();
          } else {
            showToast("error", resJson.message || "Failed to delete invoice.");
          }
        } catch (error) {
          showToast("error", "An error occurred while deleting the invoice.");
        } finally {
          IS_FETCHING = false;
        }
      }
    }
  })
});