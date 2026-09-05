document.addEventListener("DOMContentLoaded", () => {
    const RV_LIST_SEARCH_FORM = document.querySelector("#c_rv_list_search_form");
    const RV_LIST_SEARCH_INPUT = document.querySelector("#c_rv_list_search_input");

    if (RV_LIST_SEARCH_FORM && RV_LIST_SEARCH_INPUT) {
        RV_LIST_SEARCH_FORM.addEventListener("submit", (e) => {
            e.preventDefault();
            $('#rv_list').DataTable().ajax.reload();
        });
    }

    if ($('#rv_list').length > 0) {
        $('#rv_list').DataTable({
            "serverSide": true,
            "bFilter": false,
            "bInfo": false,
            "ordering": true,
            "autoWidth": true,
            "order": [[1, "desc"]],
            "language": {
                search: '',
                sLengthMenu: '_MENU_',
                searchPlaceholder: "Search",
                info: "_START_ - _END_ of _TOTAL_ items",
                "lengthMenu": "Show _MENU_ entries",
                paginate: {
                    next: 'Next <i class="fa fa-angle-right"></i>',
                    previous: '<i class="fa fa-angle-left"></i> Prev'
                },
            },
            initComplete: (settings, json) => {
                $('.dataTables_paginate').appendTo('.rv_list_datatable_paginate');
                $('.dataTables_length').appendTo('.rv_list_datatable_length');
            },
            ajax: {
                url: $('#rv_list').data('url'),
                type: "GET",
                data: function (d) {
                    d.search = RV_LIST_SEARCH_INPUT?.value || "";
                },
                dataSrc: function (json) {
                    return json.data;
                }
            },
            columns: [
                { data: 'rv_number', orderable: true },
                { 
                    data: 'rv_date',
                    render: function (data, type, row) {
                        return type === 'display' ? moment(data).format('DD-MMM-YYYY') : data;
                    }
                },
                { data: 'payer_display', orderable: false },
                { data: 'purpose', orderable: true },
                { data: 'bank_name', orderable: false },
                { data: 'amount_formatted', className: 'text-end', orderable: false },
                {
                    data: 'actions',
                    orderable: false,
                    searchable: false
                }
            ]
        });
    }
});
