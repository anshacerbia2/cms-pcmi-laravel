class EmployeeForm {
  isInit = true;
  mode = "create";
  data = {};
  errors = {};
  banks = [];

  constructor (formId) {
    this.form = document.getElementById(formId);
    this.closeForm = document.getElementById("close_employee_form");

    this.handleDocumentInput = this.handleDocumentInput.bind(this);
    this.handleSubmit = this.handleSubmit.bind(this)

    this.form.addEventListener("submit", (e) => {
      e.preventDefault();
      this.handleSubmit()
    });

    document.addEventListener("input", this.handleDocumentInput);
  }

  async handleDocumentInput(e) {
    const target = e.target;
  }

  // ---------------------------------------- INIT ----------------------------------------
  async init(mode = "create", data = {}) {
    this.resetForm();
    this.showLoading();
    this.data = data;
    this.mode = mode;

    // Fetch banks if not loaded
    if (this.banks.length === 0) {
      try {
        const response = await fetch('/banks/all', {
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          }
        });
        const resJson = await response.json();
        if (resJson.success) {
          this.banks = resJson.data;
        }
      } catch (err) {
        console.error("Failed to fetch banks", err);
      }
    }

    const formWrapper = document.createElement("div");
    formWrapper.id = "employee_form_wrapper";
    formWrapper.innerHTML = this.createForm();
    this.form.appendChild(formWrapper);
    this.initPlugins();
    this.isInit = false;
    this.hideLoading();
  }

  createForm() {
    const isEdit = this.mode === "edit";

    const value = {
      code: "",
      name: "",
      ktp_number: "",
      sim_number: "",
      passport_number: "",
      npwp_number: "",
      bpjs_number: "",
      marital_status: "TK",
      phone: "",
      personal_email: "",
      ktp_address: "",
      ktp_province: "",
      ktp_city: "",
      ktp_district: "",
      ktp_village: "",
      current_address: "",
      current_province: "",
      current_city: "",
      current_district: "",
      current_village: "",
      join_date: "",
      position: "",
      bank_id: "",
      bank_account_number: "",
      bank_account_name: "",
      username: "",
      role: "",
    }

    if (isEdit && this.data) {
      Object.keys(value).forEach(key => {
        value[key] = this.data[key] || "";
      });
      if (this.data.join_date) {
        value.join_date = moment(this.data.join_date).format("YYYY-MM-DD");
      }
    }

    const maritalOptions = ['TK', 'K1', 'K2', 'K3'].map(s => {
      return `<option value="${s}" ${value.marital_status === s ? "selected" : ""}>${s}</option>`;
    }).join("");

    const bankOptions = this.banks.map(b => {
      return `<option value="${b.id}" ${+value.bank_id === +b.id ? "selected" : ""}>${b.bank_name}</option>`;
    }).join("");

    const codeHtml = isEdit ?
      `
        <div class="col-md-6">
          <div class="mb-3">
            <label class="col-form-label">Employee Code</label>
              <input type="text" class="form-control btn-disabled" value="${value.code}" disabled>
          </div>
        </div>
      ` : ""
      ;

    return `
      <div>
        <h6 class="mb-3 text-primary">Personal Information</h6>
        <div class="row">
          ${codeHtml}
          <div class="col-md-6">
            <div class="mb-3">
              <label class="col-form-label">Full Name<span class="text-danger">*</span></label>
              <input type="text" id="input_employee_name" class="form-control" value="${value.name}">
              <small id="input_employee_name_error" class="text-danger mt-1" style="display: none;"></small>
            </div>
          </div>
          <div class="col-md-4">
            <div class="mb-3">
              <label class="col-form-label">Marital Status</label>
              <select id="input_employee_marital_status" class="select form-control">
                ${maritalOptions}
              </select>
            </div>
          </div>
          <div class="col-md-4">
            <div class="mb-3">
              <label class="col-form-label">Phone</label>
              <input type="text" id="input_employee_phone" class="form-control" value="${value.phone}">
            </div>
          </div>
          <div class="col-md-4">
            <div class="mb-3">
              <label class="col-form-label">Personal Email</label>
              <input type="email" id="input_employee_personal_email" class="form-control" value="${value.personal_email}">
            </div>
          </div>
        </div>

        <h6 class="mb-3 mt-3 text-primary">Identity</h6>
        <div class="row">
          <div class="col-md-6">
            <div class="mb-3">
              <label class="col-form-label">KTP Number</label>
              <input type="text" id="input_employee_ktp_number" class="form-control" value="${value.ktp_number}">
              <small id="input_employee_ktp_number_error" class="text-danger mt-1" style="display: none;"></small>
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="col-form-label">SIM Number</label>
              <input type="text" id="input_employee_sim_number" class="form-control" value="${value.sim_number}">
            </div>
          </div>
          <div class="col-md-4">
            <div class="mb-3">
              <label class="col-form-label">Passport</label>
              <input type="text" id="input_employee_passport_number" class="form-control" value="${value.passport_number}">
            </div>
          </div>
          <div class="col-md-4">
            <div class="mb-3">
              <label class="col-form-label">NPWP</label>
              <input type="text" id="input_employee_npwp_number" class="form-control" value="${value.npwp_number}">
            </div>
          </div>
          <div class="col-md-4">
            <div class="mb-3">
              <label class="col-form-label">BPJS</label>
              <input type="text" id="input_employee_bpjs_number" class="form-control" value="${value.bpjs_number}">
            </div>
          </div>
        </div>

        <h6 class="mb-3 mt-3 text-primary">Address (KTP)</h6>
        <div class="row">
          <div class="col-md-12">
            <div class="mb-3">
              <label class="col-form-label">KTP Address</label>
              <textarea id="input_employee_ktp_address" class="form-control">${value.ktp_address}</textarea>
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="col-form-label">Province</label>
              <input type="text" id="input_employee_ktp_province" class="form-control" value="${value.ktp_province}">
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="col-form-label">City</label>
              <input type="text" id="input_employee_ktp_city" class="form-control" value="${value.ktp_city}">
            </div>
          </div>
        </div>

        <h6 class="mb-3 mt-3 text-primary">Residence Address</h6>
        <div class="row">
          <div class="col-md-12">
            <div class="mb-3">
              <label class="col-form-label">Current Address</label>
              <textarea id="input_employee_current_address" class="form-control">${value.current_address}</textarea>
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="col-form-label">Province</label>
              <input type="text" id="input_employee_current_province" class="form-control" value="${value.current_province}">
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="col-form-label">City</label>
              <input type="text" id="input_employee_current_city" class="form-control" value="${value.current_city}">
            </div>
          </div>
        </div>

        <h6 class="mb-3 mt-3 text-primary">Employment & Financial</h6>
        <div class="row">
          <div class="col-md-6">
            <div class="mb-3">
              <label class="col-form-label">Join Date</label>
              <div class="icon-form">
                <span class="form-icon"><i class="ti ti-calendar-event"></i></span>
                <input id="input_employee_join_date" type="text" class="form-control datetimepicker" placeholder="DD/MM/YY" value="${value.join_date}">
              </div>
              <small id="input_employee_join_date_error" class="text-danger mt-1" style="display: none;"></small>
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="col-form-label">Position</label>
              <input type="text" id="input_employee_position" class="form-control" value="${value.position}">
            </div>
          </div>
          <div class="col-md-4">
            <div class="mb-3">
              <label class="col-form-label">Bank</label>
              <select id="input_employee_bank_id" class="select form-control">
                <option value="">-- Select Bank --</option>
                ${bankOptions}
              </select>
            </div>
          </div>
          <div class="col-md-4">
            <div class="mb-3">
              <label class="col-form-label">Acc. Number</label>
              <input type="text" id="input_employee_bank_account_number" class="form-control" value="${value.bank_account_number}">
            </div>
          </div>
          <div class="col-md-4">
            <div class="mb-3">
              <label class="col-form-label">Acc. Name</label>
              <input type="text" id="input_employee_bank_account_name" class="form-control" value="${value.bank_account_name}">
            </div>
          </div>
        </div>

        <h6 class="mb-3 mt-3 text-primary">System Auth</h6>
        <div class="row">
          <div class="col-md-4">
            <div class="mb-3">
              <label class="col-form-label">Username</label>
              <input type="text" id="input_employee_username" class="form-control" value="${value.username}">
              <small id="input_employee_username_error" class="text-danger mt-1" style="display: none;"></small>
            </div>
          </div>
          <div class="col-md-4">
            <div class="mb-3">
              <label class="col-form-label">Password ${isEdit ? "(Leave blank to keep current)" : ""}</label>
              <input type="password" id="input_employee_password" class="form-control">
              <small id="input_employee_password_error" class="text-danger mt-1" style="display: none;"></small>
            </div>
          </div>
          <div class="col-md-4">
            <div class="mb-3">
              <label class="col-form-label">Role</label>
              <input type="text" id="input_employee_role" class="form-control" value="${value.role}">
            </div>
          </div>
        </div>

        <div class="d-flex align-items-center justify-content-end mt-4">
          <a href="javascript:void(0)" class="btn btn-light me-2" data-bs-dismiss="offcanvas">Cancel</a>
          <button type="submit" class="btn btn-primary">Save</button>
        </div>
      </div>
    `;
  }

  initPlugins() {
    if (window.$ && $.fn.select2) {
      $('.select').select2({
        width: '100%',
        dropdownParent: $('#c_employee_canvas')
      });
    }

    if ($('.datetimepicker').length && $.fn.datetimepicker) {
      console.log("asu");

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

  showLoading() {
    if (!this.loadingEl) {
      this.loadingEl = document.createElement("div");
      this.loadingEl.className = "c-form-loading-overlay";
      this.loadingEl.innerHTML = `<div class="c-form-spinner"></div>`;
      Object.assign(this.loadingEl.style, {
        position: "absolute",
        top: 0, left: 0, width: "100%", height: "100%",
        background: "rgba(255,255,255,0.7)",
        display: "flex", justifyContent: "center", alignItems: "center",
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
    this.mode = "create";
    this.data = {};
    this.form.innerHTML = "";
    this.errors = {};
    this.loadingEl = null;
  }

  resetErrorFields() {
    const errKeys = Object.keys(this.errors);
    if (errKeys.length) {
      errKeys.forEach(v => {
        const el = this.form.querySelector(`#${v}`);
        if (el) {
          el.innerText = "";
          el.style.display = "none";
        }
      });
    }
    this.errors = {};
  }

  validateFields() {
    this.resetErrorFields();
    const payload = {};

    // Mapping simple fields
    const fields = [
      "name", "ktp_number", "sim_number", "passport_number", "npwp_number", "bpjs_number",
      "marital_status", "phone", "personal_email", "ktp_address", "ktp_province", "ktp_city",
      "ktp_district", "ktp_village", "current_address", "current_province", "current_city",
      "current_district", "current_village", "join_date", "position", "bank_id",
      "bank_account_number", "bank_account_name", "username", "password", "role"
    ];

    fields.forEach(f => {
      const el = this.form.querySelector("#input_employee_" + f);
      let value = el ? el.value.trim() : "";

      if (value && f === 'join_date') {
        value = moment(value, 'DD/MM/YY').format('YYYY-MM-DD')
      }

      payload[f] = value;

      if (f === "name" && !value) {
        this.errors["input_employee_name_error"] = "Name is required.";
      }
    });

    return payload;
  }

  async handleSubmit() {
    if (IS_FETCHING) return;
    IS_FETCHING = true;
    this.showLoading();

    const payload = this.validateFields();
    const errKeys = Object.keys(this.errors);
    if (errKeys.length) {
      errKeys.forEach(v => {
        const el = document.getElementById(v);
        if (el) {
          el.innerText = this.errors[v];
          el.style.display = "block";
        }
      });
      IS_FETCHING = false;
      this.hideLoading()
      return;
    }

    const url = this.mode === "create" ? '/employees' : `/employees/${this.data.id}`;
    const method = this.mode === "create" ? 'POST' : 'PUT';

    try {
      const response = await fetch(url, {
        method: method,
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json",
          "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
        },
        body: JSON.stringify(payload)
      });

      const result = await response.json();

      if (response.ok && result.success) {
        showToast("success", result.message || `Employee ${this.mode === 'create' ? 'created' : 'updated'} successfully!`);
        $('#employee_list').DataTable().ajax.reload(null, this.mode === "create" ? true : false);
        if (this.closeForm) this.closeForm.click();
        this.resetForm()
      } else {
        // Handle Laravel validation errors
        if (result.errors) {
          Object.keys(result.errors).forEach(key => {
            const errEl = document.getElementById(`input_employee_${key}_error`);
            if (errEl) {
              errEl.innerText = result.errors[key][0];
              errEl.style.display = "block";
            }
          });
          showToast("error", "Please check the form for errors.");
        } else {
          showToast("error", result.message || "Failed to save employee.");
        }
      }
    } catch (err) {
      showToast("error", 'An error occurred while saving Employee.');
    } finally {
      IS_FETCHING = false;
      this.hideLoading()
    }
  }
}

// ----------------------------------------------- TRIGGER -----------------------------------------------

document.addEventListener("DOMContentLoaded", () => {
  const EMPLOYEE_CANVAS = document.querySelector("#c_employee_canvas");
  const EMPLOYEE_MODAL = document.querySelector("#c_employee_modal");
  const EMPLOYEE_FORM = EMPLOYEE_CANVAS?.querySelector("form#c_employee_canvas_form")
    ? new EmployeeForm("c_employee_canvas_form")
    : null;
  const EMPLOYEE_CANVAS_BS = EMPLOYEE_CANVAS ? new bootstrap.Offcanvas(EMPLOYEE_CANVAS) : null;
  const EMPLOYEE_MODAL_BS = EMPLOYEE_MODAL ? new bootstrap.Modal(EMPLOYEE_MODAL) : null;

  document.addEventListener("click", async e => {
    let target = e.target;

    // CREATE
    if (target.matches("#c_employee_create_btn")) {
      e.preventDefault();
      if (EMPLOYEE_CANVAS_BS && EMPLOYEE_FORM && !IS_FETCHING) {
        const title = EMPLOYEE_CANVAS.querySelector("#c_employee_canvas_title");
        title.textContent = "Create Employee";
        EMPLOYEE_CANVAS_BS.show();
        await EMPLOYEE_FORM.init("create");
      }
    }

    // EDIT
    if (target.closest(".c_employee_edit_btn")) {
      target = target.closest(".c_employee_edit_btn");
      e.preventDefault();
      if (!EMPLOYEE_CANVAS_BS || !EMPLOYEE_FORM || IS_FETCHING) return;
      IS_FETCHING = true;

      const url = target.dataset.url;

      try {
        const response = await fetch(url, {
          method: "GET",
          headers: {
            "Content-Type": "application/json",
            "Accept": "application/json",
          },
        })

        const resJson = await response.json();

        if (response.ok && resJson.success) {
          const title = EMPLOYEE_CANVAS.querySelector("#c_employee_canvas_title");
          title.textContent = "Edit Employee";
          EMPLOYEE_CANVAS_BS.show();
          await EMPLOYEE_FORM.init("edit", resJson.data);
        } else {
          showToast("error", resJson.message || "Failed to fetch employee data.");
        }
      } catch (error) {
        showToast("error", 'An error occurred while fetching employee data.');
      } finally {
        IS_FETCHING = false;
      }
    }

    // DELETE
    else if (target.closest(".c_employee_delete_btn")) {
      target = target.closest(".c_employee_delete_btn");
      e.preventDefault();
      if (!EMPLOYEE_MODAL_BS || IS_FETCHING) return;
      const url = target.dataset.url;
      const confirmBtn = EMPLOYEE_MODAL.querySelector("#c_employee_modal_confirm_btn");
      confirmBtn.dataset.url = url;
      EMPLOYEE_MODAL_BS.show();
    }

    // CONFIRM DELETE 
    else if (target.matches("#c_employee_modal_confirm_btn")) {
      e.preventDefault();
      if (IS_FETCHING) return;
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
          $('#employee_list').DataTable().ajax.reload(null, false);
          showToast("success", resJson.message || "Employee deleted successfully.");
          EMPLOYEE_MODAL_BS.hide();
        } else {
          showToast("error", resJson.message || "Failed to delete employee.");
        }
      } catch (error) {
        showToast("error", "An error occurred while deleting the employee.");
      } finally {
        IS_FETCHING = false;
      }
    }
  })
});
