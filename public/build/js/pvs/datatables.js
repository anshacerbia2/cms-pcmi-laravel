document.addEventListener("DOMContentLoaded", () => {
  const PV_LIST_SEARCH_FORM = document.querySelector("#c_pv_list_search_form");
  const PV_LIST_SEARCH_INPUT = document.querySelector("#c_pv_list_search_input");

  if (PV_LIST_SEARCH_FORM && PV_LIST_SEARCH_INPUT) {
    PV_LIST_SEARCH_FORM.addEventListener("submit", (e) => {
      e.preventDefault();
      $('#pv_list').DataTable().ajax.reload();
    });
  }

  if ($('#pv_list').length > 0) {
    $('#pv_list').DataTable({
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
        $('.dataTables_paginate').appendTo('.pv_list_datatable_paginate');
        $('.dataTables_length').appendTo('.pv_list_datatable_length');
      },
      ajax: {
        url: $('#pv_list').data('url'),
        type: "GET",
        data: function (d) {
          d.search = PV_LIST_SEARCH_INPUT?.value || "";
        },
        dataSrc: function (json) {
          return json.data;
        }
      },
      columns: [
        { data: 'pv_number' },
        {
          data: 'issuing_date',
          render: function (data) {
            return moment(data).format('DD-MMM-YYYY');
          }
        },
        { 
            data: 'payable_type',
            render: function(data, type, row) {
                return row.payable_name_manual || data;
            }
        },
        { data: 'category' },
        { data: 'bank_name' },
        { data: 'amount_formatted', className: 'text-end' },
        { data: 'actions', orderable: false, searchable: false }
      ]
    });
  }
});
