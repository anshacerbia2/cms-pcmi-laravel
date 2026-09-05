document.addEventListener("DOMContentLoaded", () => {
  const EMPLOYEE_LIST_SEARCH_FORM = document.querySelector("#c_employee_list_search_form");
  const EMPLOYEE_LIST_SEARCH_INPUT = document.querySelector("#c_employee_list_search_input");

  if (EMPLOYEE_LIST_SEARCH_FORM && EMPLOYEE_LIST_SEARCH_INPUT) {
    EMPLOYEE_LIST_SEARCH_FORM.addEventListener("submit", (e) => {
      e.preventDefault();
      $('#employee_list').DataTable().ajax.reload();
    });
  }

  if ($('#employee_list').length > 0) {
    $('#employee_list').DataTable({
      "serverSide": true,
      "bFilter": false,
      "bInfo": false,
      "ordering": true,
      "autoWidth": true,
      "order": [[0, "desc"]],
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
        const $wrapper = $(settings.nTableWrapper);
        $wrapper.find('.dataTables_paginate').appendTo('.datatable-paginate');
        $wrapper.find('.dataTables_length').appendTo('.datatable-length');
      },
      ajax: {
        url: $('#employee_list').data('url'),
        type: "GET",
        data: function (d) {
          d.search = EMPLOYEE_LIST_SEARCH_INPUT?.value || "";
        },
        dataSrc: function (json) {
          return json.data;
        }
      },
      columns: [
        { data: 'code' },
        { data: 'name' },
        { data: 'position' },
        { data: 'phone' },
        {
          data: 'actions',
          orderable: false,
          searchable: false
        }
      ],
    });
  }
});
