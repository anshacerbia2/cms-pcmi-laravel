document.addEventListener("DOMContentLoaded", () => {
    const pvForm = document.getElementById("c_pv_canvas_form");
    const pvCanvas = new bootstrap.Offcanvas(document.getElementById("c_pv_canvas"));
    const pvTitle = document.getElementById("c_pv_canvas_title");
    
    const payableTypeSelect = document.getElementById("input_pv_payable_type");
    const payableLookupContainer = document.getElementById("pv_payable_lookup_container");
    const payableManualContainer = document.getElementById("pv_payable_manual_container");
    const payableLookupSelect = document.getElementById("input_pv_payable_id");

    const categorySelect = document.getElementById("input_pv_category");
    const categoryExpenseType = document.getElementById("pv_category_expense_type");
    const categoryPo = document.getElementById("pv_category_po");
    const poSelect = document.getElementById("input_pv_purchase_order_id");

    // Handle Category Change
    $(categorySelect).on('change', function() {
        const cat = $(this).val();
        if (cat === 'COGS') {
            $(categoryExpenseType).hide();
            $(categoryPo).show();
            // Fetch POs - Placeholder for now as table might not exist
            fetchPOData();
        } else if (cat === 'Expense') {
            $(categoryPo).hide();
            $(categoryExpenseType).show();
        } else {
            $(categoryPo).hide();
            $(categoryExpenseType).hide();
        }
    });

    function fetchPOData() {
        // Placeholder lookup for Purchase Orders
        $(poSelect).empty().append(new Option('-- Select PO --', ''));
        fetch('/purchase-orders/all')
            .then(res => res.ok ? res.json() : { data: [] })
            .then(data => {
                (data.data || []).forEach(po => {
                    $(poSelect).append(new Option(po.po_number, po.id));
                });
            })
            .catch(() => console.log('PO table not yet available'));
    }

    // Handle Payable Type Change
    $(payableTypeSelect).on('change', function() {
        const type = $(this).val();
        if (type === 'Manual') {
            $(payableLookupContainer).hide();
            $(payableManualContainer).show();
        } else {
            $(payableManualContainer).hide();
            $(payableLookupContainer).show();
            fetchPayableData(type);
        }
    });

    function fetchPayableData(type) {
        let url = '';
        if (type === 'Supplier') url = '/suppliers/all';
        else if (type === 'Staff') url = '/users/all'; // Assuming users are staff
        else if (type === 'Customer') url = '/customers/all';

        $(payableLookupSelect).empty().append(new Option('-- Select ' + type + ' --', ''));
        
        if (!url) return;

        fetch(url)
            .then(res => res.json())
            .then(data => {
                const items = data.data || [];
                items.forEach(item => {
                    $(payableLookupSelect).append(new Option(item.name, item.id));
                });
            });
    }

    // Open Create Modal
    $(document).on('click', '#c_pv_create_btn', function() {
        pvTitle.innerText = "Create Payment Voucher";
        pvForm.reset();
        document.getElementById("input_pv_id").value = "";
        $('.text-danger').text("");
        $(categorySelect).val('Expense').trigger('change');
        $(payableTypeSelect).val('Manual').trigger('change');
        pvCanvas.show();
    });

    // Open Edit Modal
    $(document).on('click', '.c_pv_edit_btn', function() {
        const url = $(this).data('url');
        $('.text-danger').text("");
        
        fetch(url)
            .then(res => res.json())
            .then(res => {
                const data = res.data;
                pvTitle.innerText = "Edit Payment Voucher";
                document.getElementById("input_pv_id").value = data.id;
                document.getElementById("input_pv_pv_number").value = data.pv_number;
                document.getElementById("input_pv_issuing_date").value = data.issuing_date;
                document.getElementById("input_pv_due_date").value = data.due_date || "";
                document.getElementById("input_pv_amount").value = data.amount;
                document.getElementById("input_pv_expense_type").value = data.expense_type || "";
                document.getElementById("input_pv_payable_name_manual").value = data.payable_name_manual || "";
                document.getElementById("input_pv_description").value = data.description || "";
                document.getElementById("input_pv_payment_date").value = data.payment_date || "";
                
                $(document.getElementById("input_pv_currency")).val(data.currency).trigger('change');
                $(document.getElementById("input_pv_category")).val(data.category).trigger('change');
                $(document.getElementById("input_pv_source_payment_form")).val(data.source_payment_form).trigger('change');
                $(document.getElementById("input_pv_pcmi_bank_id")).val(data.pcmi_bank_id).trigger('change');
                
                $(payableTypeSelect).val(data.payable_type).trigger('change');
                
                setTimeout(() => {
                    if (data.payable_id) $(payableLookupSelect).val(data.payable_id).trigger('change');
                    if (data.purchase_order_id) $(poSelect).val(data.purchase_order_id).trigger('change');
                }, 500);

                pvCanvas.show();
            });
    });

    // Submit Form
    $(pvForm).on('submit', function(e) {
        e.preventDefault();
        $('.text-danger').text("");
        
        const id = document.getElementById("input_pv_id").value;
        const url = id ? '/pvs/' + id : '/pvs';
        const method = id ? 'PUT' : 'POST';
        
        const data = $(this).serializeArray();
        const payload = {};
        data.forEach(item => payload[item.name] = item.value);

        $.ajax({
            url: url,
            type: method,
            data: payload,
            success: function(res) {
                if (res.success) {
                    pvCanvas.hide();
                    $('#pv_list').DataTable().ajax.reload();
                    Toastify({ text: res.message, backgroundColor: "green" }).showToast();
                }
            },
            error: function(xhr) {
                if (xhr.status === 400) {
                    const errors = xhr.responseJSON.errors;
                    for (const key in errors) {
                        $('#error_pv_' + key).text(errors[key][0]);
                    }
                } else {
                    alert('An error occurred. Please try again.');
                }
            }
        });
    });

    // Delete PV
    $(document).on('click', '.c_pv_delete_btn', function() {
        if(confirm('Are you sure you want to delete this PV?')) {
            const url = $(this).data('url');
            $.ajax({
                url: url,
                type: 'DELETE',
                data: { _token: $('meta[name="csrf-token"]').attr('content') },
                success: function(res) {
                    if(res.success) {
                        $('#pv_list').DataTable().ajax.reload();
                        Toastify({ text: res.message, backgroundColor: "green" }).showToast();
                    }
                }
            });
        }
    });

    // Initialize select2
    $('.select2').each(function() {
        $(this).select2({
            dropdownParent: $(this).parent()
        });
    });
});
