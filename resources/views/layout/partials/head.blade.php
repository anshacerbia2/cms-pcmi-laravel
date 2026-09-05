<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Apple Touch Icon -->
<link rel="apple-touch-icon" sizes="180x180" href="{{ url('//img/apple-touch-icon.png') }}">
<link rel="stylesheet" href="{{ url('build/plugins/select2/css/select2.min.css') }}">


<!-- Favicon -->
<link rel="icon" href="{{ url('build/img/favicon.png')}}" type="image/x-icon">
<link rel="shortcut icon" href="{{ url('build/img/favicon.png')}}" type="image/x-icon">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="{{ url('build/plugins/toastr/toastr.css') }}">
 
@if (Route::is(['layout-rtl']))
<!-- Bootstrap CSS -->
<link rel="stylesheet" href="{{ url('build/css/bootstrap.rtl.min.css') }}">
@endif


@if (!Route::is(['layout-rtl']))
 <!-- Bootstrap CSS -->
 <link rel="stylesheet" href="{{ url('build/css/bootstrap.min.css') }}">
 @endif

 <!-- Tabler Icon CSS -->
 <link rel="stylesheet" href="{{ url('build/plugins/tabler-icons/tabler-icons.css') }}">

 @if (Route::is(['audio-call', 'bank-accounts', 'blog', 'ui-nav-tabs', 'video-call']))
 <!-- Feathericon CSS -->
 <link rel="stylesheet" href="{{ url('build/css/feather.css') }}">
 @endif

 @if (Route::is(['chat', 'invoice']))
 <!-- Summernote CSS -->
 <link rel="stylesheet" href="{{ url('build/plugins/summernote/summernote-bs4.min.css') }}">
 @endif

 @if (Route::is(['chat']))
 <!-- Mobile CSS-->
 <link rel="stylesheet" href="{{ url('build/plugins/intltelinput/css/intlTelInput.css') }}">
 <link rel="stylesheet" href="{{ url('build/plugins/intltelinput/css/demo.css') }}">
 @endif

 @if (Route::is(['calendar']))
 <!-- Full Calander CSS -->
 <link rel="stylesheet" href="{{ url('build/plugins/fullcalendar/fullcalendar.min.css') }}">
 @endif

 @if (Route::is(['audio-call', 'call-history', 'video-call', 'chat']))
 <!-- Boxicons CSS -->
 <link rel="stylesheet" href="{{ url('build/plugins/boxicons/css/boxicons.min.css') }}">
 @endif

 @if (Route::is(['audio-call', 'video-call', 'notes', 'file-manager']))
 <!-- Owl Carousel CSS -->
 <link rel="stylesheet" href="{{ url('build/plugins/owlcarousel/owl.carousel.min.css') }}">
 <link rel="stylesheet" href="{{ url('build/plugins/owlcarousel/owl.theme.default.min.css') }}">
 @endif

 @if (Route::is(['file-manager']))
 <link rel="stylesheet" href="{{ url('build/css/plyr.css') }}">
 @endif

 <!-- Datatable CSS -->
 <link rel="stylesheet" href="{{ url('build/css/dataTables.bootstrap5.min.css') }}">

 @if (Route::is([
         'audio-call',
         'calendar',
         'call-history',
         'video-call',
         'chart-apex',
         'chart-c3',
         'chart-flot',
         'chart-js',
         'chart-morris',
         'chart-peity',
         'chat',
         'contacts',
         'ui-nav-tabs',
         'ui-sortable',
         'ui-swiperjs',
         'icon-tabler',
         'icon-bootstrap'
     ]))
 <!-- animation CSS -->
 <link rel="stylesheet" href="{{ url('build/css/animate.css') }}">
 @endif

 @if (Route::is(['icon-bootstrap']))
 <!-- Bootstrap Icon CSS -->
 <link rel="stylesheet" href="{{ url('build/plugins/icons/bootstrap/bootstrap-icons.min.css') }}">
 @endif

 @if (Route::is(['form-pickers']))
 <!-- Form Date PIckers CSS -->
 <link rel="stylesheet" href="{{ url('build/plugins/flatpickr/flatpickr.css') }}">
 <link rel="stylesheet" href="{{ url('build/plugins/bootstrap-datepicker/bootstrap-datepicker.css') }}">
 <link rel="stylesheet" href="{{ url('build/plugins/jquery-timepicker/jquery-timepicker.css') }}">
 <link rel="stylesheet" href="{{ url('build/plugins/pickr/pickr-themes.css') }}">
 @endif

 @if (Route::is(['maps-vector']))
<!-- JSVector Maps MapsJS -->
<script src="{{ URL::asset('build/plugins/jsvectormap/js/jsvectormap.min.js') }}"></script>
<script src="{{ URL::asset('build/plugins/jsvectormap/maps/world-merc.js') }}"></script>
<script src="{{ URL::asset('build/js/us-merc-en.js') }}"></script>
<script src="{{ URL::asset('build/js/russia.js') }}"></script>
<script src="{{ URL::asset('build/js/spain.js') }}"></script>
<script src="{{ URL::asset('build/js/canada.js') }}"></script>
<script src="{{ URL::asset('build/js/jsvectormap.js') }}"></script>
@endif

 @if (Route::is(['maps-vector']))
 <!-- Jsvector Maps -->
 <link rel="stylesheet" href="{{ url('build/plugins/jsvectormap/css/jsvectormap.min.css') }}">
 @endif

 @if (Route::is(['maps-leaflet']))
 <!-- Leaflet Maps CSS -->
 <link rel="stylesheet" href="{{ url('build/plugins/leaflet/leaflet.css') }}">
 @endif

 <!-- Color Picker CSS -->
 <link rel="stylesheet" href="{{ url('build/plugins/flatpickr/flatpickr.min.css') }}">
 <link rel="stylesheet" href="{{ url('build/plugins/@simonwep/pickr/themes/nano.min.css') }}">

 @if (Route::is(['ui-swiperjs']))
 <!-- Swiper CSS -->
 <link rel="stylesheet" href="{{ url('build/plugins/swiper/swiper-bundle.min.css') }}">
 @endif

 @if (Route::is(['icon-remix']))
 <!-- Remix Icon CSS -->
 <link rel="stylesheet" href="{{ url('build/plugins/icons/remix/fonts/remixicon.css') }}">
 @endif

 @if (Route::is(['chat']))
 <!-- Fancybox -->
 <link rel="stylesheet" href="{{ url('build/plugins/fancybox/jquery.fancybox.min.css') }}">
 @endif

 <!-- Fontawesome CSS -->
 <link rel="stylesheet" href="{{ url('build/plugins/fontawesome/css/fontawesome.min.css') }}">
 <link rel="stylesheet" href="{{ url('build/plugins/fontawesome/css/all.min.css') }}">

 <!-- Datetimepicker CSS -->
 @if (Route::is([
         'activities',
         'activity-calls',
         'activity-mail',
         'activity-meeting',
         'activity-task',
         'analytics',
         'campaign',
         'todo',
         'tickets',
         'campaign-archieve',
         'campaign-complete',
         'chat',
         'cities',
         'companies-grid',
         'companies',
         'company-details',
         'company-reports',
         'contact-details',
         'contact-grid',
         'contact-messages',
         'contact-reports',
         'contacts',
         'countries',
         'deals-reports',
         'deals-details',
         'deals-kanban',
         'deals',
         'delete-request',
         'faq',
         'file-manager',
         'testimonials',
         'tasks',
         'tasks-important',
         'tasks-completed',
         'task-reports',
         'states',
         'sources',
         'roles',
         'roles.index',
         'categories.index',
         'products.index',
         'projects',
         'projects.index',
         'projects.read',
         'project-reports',
         'project-grid',
         'project-details',
         'project-dashboard',
         'pipeline',
         'permissions',
         'pages',
         'pages-list',
         'notes',
         'membership-transactions',
         'membership-addons',
         'membership-plans',
         'manage-users',
         'users.index',
         'leads-kanban',
         'leads-details',
         'leads-reports',
         'leads-dashboard',
         'leads',
         'lead-reports',
         'language-web',
         'language',
         'proposals-grid',
         'proposals',
         'proposals.index',
         'proposals.read',
         'boqs',
         'boqs.index',
         'contracts-grid',
         'contracts',
         'estimations-kanban',
         'estimations',
         'employees',
         'employees.index',
         'invoices',
         'invoices.index',
         'invoice-grid',
         'kanban-view',
         'invoice',
         'dashboard',
         'company',
         'subscription',
         'packages',
         'packages-grid',
         'domain',
         'purchase-transaction',
         'rvs',
         'rvs.index'
     ]))
 <link rel="stylesheet" href="{{ url('build/css/bootstrap-datetimepicker.min.css') }}">
 @endif

 @if (Route::is([
         'activities',
         'activity-calls',
         'activity-mail',
         'activity-meeting',
         'activity-task',
         'analytics',
         'audio-call',
         'call-history',
         'video-call',
         'campaign',
         'campaign-archieve',
         'campaign-complete',
         'chat',
         'cities',
         'companies-grid',
         'companies',
         'company-details',
         'company-reports',
         'company-settings',
         'contact-details',
         'contact-grid',
         'contact-messages',
         'contact-reports',
         'contacts',
         'countries',
         'custom-fields',
         'deal-reports',
         'deals-details',
         'deals-kanban',
         'deals',
         'delete-request',
         'faq',
         'file-manager',
         'gdpr-cookies',
         'invoice-settings',
         'todo',
         'tickets',
         'testimonials',
         'tasks',
         'tasks-important',
         'tasks-completed',
         'task-reports',
         'states',
         'sources',
         'roles',
         'roles.index',
         'categories.index',
         'products.index',
         'projects',
         'projects.index',
         'projects.read',
         'project-reports',
         'project-grid',
         'project-details',
         'project-dashboard',
         'printers',
         'prefixes',
         'preference',
         'pipeline',
         'permissions',
         'pages',
         'pages-list',
         'notes',
         'membership-transactions',
         'membership-addons',
         'membership-plans',
         'manage-users',
         'users.index',
         'localization',
         'leads-kanban',
         'leads-details',
         'leads-reports',
         'leads-dashboard',
         'leads',
         'lead-reports',
         'language-web',
         'language',
         'appearance',
         'form-select2',
         'customers',
         'suppliers',
         'suppliers.index',
         'form-vertical',
         'form-horizontal',
         'proposals-grid',
         'proposals',
         'proposals.index',
         'proposals.read',
         'boqs',
         'boqs.index',
         'customers.index',
         'contracts-grid',
         'contracts',
         'estimations-kanban',
         'estimations',
         'invoices',
         'invoices.index',
         'invoice-grid',
         'kanban-view',
         'invoice',
         'dashboard',
         'company',
         'subscription',
         'packages',
         'packages-grid',
         'domain',
         'purchase-transaction',
         'menus.index'
     ]))
 <!-- Select2 CSS -->
 <link rel="stylesheet" href="{{ url('build/plugins/select2/css/select2.min.css') }}">
 @endif

 @if (Route::is([
         'activities',
         'activity-calls',
         'activity-mail',
         'activity-meeting',
         'activity-task',
         'analytics',
         'ui-text-editor',
         'campaign',
         'campaign-archieve',
         'campaign-complete',
         'companies-grid',
         'companies',
         'company-details',
         'contact-details',
         'contact-grid',
         'contact-messages',
         'contacts',
         'deals-details',
         'deals-kanban',
         'deals',
         'contact-details',
         'deals',
         'delete-request',
         'gdpr-cookies',
         'invoice-settings',
         'leads-details',
         'membership-transactions',
         'membership-addons',
         'membership-plans',
         'manage-users',
         'users.index',
         'pipeline',
         'permissions',
         'roles',
         'roles.index',
         'projects',
         'project-grid',
         'project-details',
         'tickets',
         'tasks',
         'tasks-important',
         'tasks-completed',
         'todo',
         'notes',
         'file-manager',
         'proposals-grid',
         'proposals',
         'proposals.index',
         'boqs',
         'boqs.index',
         'contracts-grid',
         'contracts',
         'estimations-kanban',
         'estimations',
         'invoices',
         'invoices.index',
         'invoice-grid',
         'company',
         'subscription',
         'domain',
         'purchase-transaction'
     ]))
 <!-- Summernote CSS -->
 <link rel="stylesheet" href="{{ url('build/plugins/summernote/summernote-lite.min.css') }}">
 @endif

 @if (Route::is([
         'activities',
         'activity-cally',
         'activity-mail',
         'activity-meeting',
         'activity-task',
         'analytics',
         'campaign',
         'campaign-archieve',
         'campaign-complete',
         'cities',
         'companies-grid',
         'companies',
         'company-details',
         'company-reports',
         'contact-details',
         'contact-grid',
         'contact-messages',
         'contact-reports',
         'contacts',
         'countries',
         'deal-reports',
         'deals-details',
         'deals-kanban',
         'deals',
         'delete-request',
         'faq',
         'tickets',
         'testimonials',
         'tasks',
         'tasks-important',
         'tasks-completed',
         'task-reports',
         'states',
         'sources',
         'roles',
         'roles.index',
         'projects',
         'project-reports',
         'project-grid',
         'project-details',
         'project-dashboard',
         'pipeline',
         'permissions',
         'pages',
         'pages-list',
         'membership-transactions',
         'membership-addons',
         'membership-plans',
         'manage-users',
         'users.index',
         'leads-kanban',
         'leads-details',
         'leads-reports',
         'leads-dashboard',
         'proposals-grid',
         'proposals',
         'proposals.index',
         'boqs',
         'boqs.index',
         'contracts-grid',
         'contracts',
         'estimations-kanban',
         'estimations',
         'invoices',
         'invoices.index',
         'invoice-grid',
         'invoice',
         'dashboard',
         'company',
         'subscription',
         'packages',
         'packages-grid',
         'domain',
         'purchase-transaction'
     ]))
 <!-- Bootstrap Tagsinput CSS -->
 <link rel="stylesheet" href="{{ url('build/plugins/bootstrap-tagsinput/bootstrap-tagsinput.css') }}">
 @endif

 @if (Route::is(['video-call', 'chat']))
 <!-- Swiper CSS -->
 <link rel="stylesheet" href="{{ url('build/plugins/swiper/swiper.min.css') }}">
 @endif

 @if (Route::is(['ui-rangeslider', 'ui-rating']))
 <!-- Rangeslider CSS -->
 <link rel="stylesheet" href="{{ url('build/plugins/ion-rangeslider/css/ion.rangeSlider.min.css') }}">
 @endif

 @if (Route::is(['ui-lightbox']))
 <!-- Lightbox CSS -->
 <link rel="stylesheet" href="{{ url('build/plugins/lightbox/glightbox.min.css') }}">
 @endif

 @if (Route::is(['ui-clipboard', 'ui-drag-drop', 'ui-sortable']))
 <!-- Dragula CSS -->
 <link rel="stylesheet" href="{{ url('build/plugins/dragula/css/dragula.min.css') }}">
 @endif

 @if (Route::is(['ui-stickynote']))
 <!-- Sticky CSS -->
 <link rel="stylesheet" href="{{ url('build/plugins/stickynote/sticky.css') }}">
 @endif

 @if (Route::is(['ui-scrollbar']))
 <link rel="stylesheet" href="{{ url('build/plugins/scrollbar/scroll.min.css') }}">
 @endif

 @if (Route::is(['form-wizard']))
 <!-- Wizard CSS -->
 <link rel="stylesheet" href="{{ URL::asset('build/plugins/twitter-bootstrap-wizard/form-wizard.css') }}">
 @endif

 @if (Route::is(['icon-flag']))
 <!-- Pe7 CSS -->
 <link rel="stylesheet" href="{{ URL::asset('build/plugins/icons/flags/flags.css') }}">
 @endif

 @if (Route::is(['icon-feather']))
 <!-- Feather CSS -->
 <link rel="stylesheet" href="{{ URL::asset('build/plugins/icons/feather/feather.css') }}">
 @endif

 @if (Route::is(['icon-ionic']))
 <!-- Ionic CSS -->
 <link rel="stylesheet" href="{{ URL::asset('build/plugins/icons/ionic/ionicons.css') }}">
 @endif

 @if (Route::is(['icon-material']))
 <!-- Material CSS -->
 <link rel="stylesheet" href="{{ URL::asset('build/plugins/material/materialdesignicons.css') }}">
 @endif

 @if (Route::is(['icon-pe7']))
 <!-- Pe7 CSS -->
 <link rel="stylesheet" href="{{ URL::asset('build/plugins/icons/pe7/pe-icon-7.css') }}">
 @endif

 @if (Route::is(['icon-simpleline']))
 <!-- Material CSS -->
 <link rel="stylesheet" href="{{ URL::asset('build/plugins/simpleline/simple-line-icons.css') }}">
 @endif

 @if (Route::is(['icon-themify']))
 <!-- Pe7 CSS -->
 <link rel="stylesheet" href="{{ URL::asset('build/plugins/icons/themify/themify.css') }}">
 @endif

 @if (Route::is(['icon-typicon']))
 <!-- Pe7 CSS -->
 <link rel="stylesheet" href="{{ URL::asset('build/plugins/icons/typicons/typicons.css') }}">
 @endif

 @if (Route::is(['icon-weather']))
 <!-- Pe7 CSS -->
 <link rel="stylesheet" href="{{ URL::asset('build/plugins/icons/weather/weathericons.css') }}">
 @endif

 <!-- Daterangepicker CSS -->
 <link rel="stylesheet" href="{{ url('build/plugins/daterangepicker/daterangepicker.css') }}">

 <!-- Main CSS -->
 <link rel="stylesheet" href="{{ url('build/css/style.css') }}">

 <style>
    .swal2-container {
        max-width: 400px;
        justify-content: flex-start;
     }

    .swal2-container .swal2-show {
        flex-wrap: wrap;
        gap: 6px;
    }

    .swal-toast-success {
        background-color:rgb(250, 255, 250) !important; 
    }
    .swal-toast-error {
        background-color:rgb(255, 250, 250) !important; 
    }
    
    #invoice_list td {
        vertical-align: baseline;
    }
 </style>