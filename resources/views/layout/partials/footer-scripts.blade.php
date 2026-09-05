<!-- jQuery -->
<script src="{{ URL::asset('build/js/jquery-3.7.1.min.js') }}"></script>

<!-- Bootstrap Core JS -->
<script src="{{ URL::asset('build/js/bootstrap.bundle.min.js') }}"></script>

<!-- Feather Icon JS -->
<script src="{{ URL::asset('build/js/feather.min.js') }}"></script>

<!-- Slimscroll JS -->
<script src="{{ URL::asset('build/js/jquery.slimscroll.js') }}"></script>
<script src="{{ URL::asset('build/js/jquery.slimscroll.min.js') }}"></script>

<!-- Datatable JS -->
<script src="{{ URL::asset('build/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ URL::asset('build/js/dataTables.bootstrap5.min.js') }}"></script>

<!-- Daterangepicker JS -->
<script src="{{ URL::asset('build/js/moment.min.js') }}"></script>
<script src="{{ URL::asset('build/plugins/daterangepicker/daterangepicker.js') }}"></script>

<script src="{{ URL::asset('build/plugins/toastr/toastr.min.js') }}"></script>
<script src="{{ URL::asset('build/plugins/toastr/toastr.js') }}"></script>

 @if (Route::is(['calendar']))
     <!-- Full Calendar JS -->
     <script src="{{ URL::asset('build/js/jquery-ui.min.js') }}"></script>
     <script src="{{ URL::asset('build/plugins/fullcalendar/fullcalendar.min.js') }}"></script>
     <script src="{{ URL::asset('build/plugins/fullcalendar/jquery.fullcalendar.js') }}"></script>
 @endif
 
 @if (Route::is(['chat']))
     <!-- Mobile Input -->
     <script src="{{ URL::asset('build/plugins/intltelinput/js/intlTelInput.js') }}"></script>
 @endif

 @if (Route::is(['ui-sortable']))
 <!-- Sortable JS -->
 <script src="{{ URL::asset('build/plugins/sortablejs/Sortable.min.js') }}"></script>

 <!-- Internal Sortable JS -->
 <script src="{{ URL::asset('build/js/sortable.js') }}"></script>
 @endif

 @if (Route::is(['ui-swiperjs']))
 <!-- Swiper JS -->
 <script src="{{ URL::asset('build/plugins/swiper/swiper-bundle.min.js') }}"></script>
	
 <!-- Internal Swiper JS -->
 <script src="{{ URL::asset('build/js/swiper.js') }}"></script>
 @endif
 
 @if (Route::is(['form-pickers']))
<script src="{{ URL::asset('build/plugins/flatpickr/flatpickr.js') }}"></script>
<script src="{{ URL::asset('build/plugins/bootstrap-datepicker/bootstrap-datepicker.js') }}"></script>
<script src="{{ URL::asset('build/plugins/daterangepicker/daterangepicker.js') }}"></script>
<script src="{{ URL::asset('build/plugins/jquery-timepicker/jquery-timepicker.js') }}"></script>
<script src="{{ URL::asset('build/plugins/pickr/pickr.js') }}"></script>
<script src="{{ URL::asset('build/plugins/@simonwep/pickr/pickr.min.js') }}"></script>
<script src="{{ URL::asset('build/js/forms-pickers.js') }}"></script>
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

@if (Route::is(['maps-leaflet']))
<!-- Leaflet Maps JS -->
<script src="{{ URL::asset('build/plugins/leaflet/leaflet.js') }}"></script>
<script src="{{ URL::asset('build/js/leaflet.js') }}"></script>
@endif

@if (Route::is(['maps-leaflet']))
<!-- Leaflet Maps CSS -->
<link rel="stylesheet" href="{{ url('build/plugins/leaflet/leaflet.css') }}">
@endif

 @if (Route::is(['chart-c3']))
     <!-- Chart JS -->
     <script src="{{ URL::asset('build/plugins/c3-chart/d3.v5.min.js') }}"></script>
     <script src="{{ URL::asset('build/plugins/c3-chart/c3.min.js') }}"></script>
     <script src="{{ URL::asset('build/plugins/c3-chart/chart-data.js') }}"></script>
 @endif

 @if (Route::is(['chart-js']))
     <!-- Chart JS -->
     <script src="{{ URL::asset('build/plugins/chartjs/chart.min.js') }}"></script>
     <script src="{{ URL::asset('build/plugins/chartjs/chart-data.js') }}"></script>
 @endif

 @if (Route::is(['chart-flot']))
     <!-- Chart JS -->
     <script src="{{ URL::asset('build/plugins/flot/jquery.flot.js') }}"></script>
     <script src="{{ URL::asset('build/plugins/flot/jquery.flot.fillbetween.js') }}"></script>
     <script src="{{ URL::asset('build/plugins/flot/jquery.flot.pie.js') }}"></script>
     <script src="{{ URL::asset('build/plugins/flot/chart-data.js') }}"></script>
 @endif

 @if (Route::is(['chart-morris']))
     <!-- Chart JS -->
     <script src="{{ URL::asset('build/plugins/morris/raphael-min.js') }}"></script>
     <script src="{{ URL::asset('build/plugins/morris/morris.min.js') }}"></script>
     <script src="{{ URL::asset('build/plugins/morris/chart-data.js') }}"></script>
 @endif

 @if (Route::is(['chart-peity', 'dashboard', 'subscription', 'domain', 'purchase-transaction']))
     <!-- Chart JS -->
     <script src="{{ URL::asset('build/plugins/peity/jquery.peity.min.js') }}"></script>
     <script src="{{ URL::asset('build/plugins/peity/chart-data.js') }}"></script>
 @endif

 @if (Route::is(['chat']))
     <!-- FancyBox JS -->
     <script src="{{ URL::asset('build/plugins/fancybox/jquery.fancybox.min.js') }}"></script>
 @endif

 @if (Route::is(['calendar']))
     <!-- Datetimepicker JS -->
     <script src="{{ URL::asset('build/plugins/moment/moment.min.js') }}"></script>
 @endif

 @if (Route::is([
         'ban-ip-address',
         'bank-accounts',
         'companies-grid',
         'companies',
         'company-settings',
         'connected-apps',
         'contact-grid',
         'contacts',
         'tax-rates',
         'storage',
         'sms-gateways',
         'security',
         'profile',
         'payment-gateways',
         'notifications',
         'proposals-grid',
         'proposals',
         'proposals.index',
         'contracts-grid',
         'contracts',
         'estimations',
         'estimations-grid',
         'invoices',
         'invoices.index',
         'invoice-grid',
         'boqs',
         'boqs.index',
         'company',
         'subscription',
         'domain',
         'purchase-transaction'
     ]))
     <!-- Profile Upload JS -->
     <script src="{{ URL::asset('build/js/profile-upload.js') }}"></script>
 @endif

 <!-- Bootstrap Tagsinput JS -->
 @if (Route::is([
         'activities',
         'activity-calls',
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
         'products.index',
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
         'contracts-grid',
         'contracts',
         'estimations-kanban',
         'estimations',
         'invoices',
         'invoices.index',
         'invoice-grid',
         'invoice',
         'boqs',
         'boqs.index',
         'dashboard',
         'company',
         'subscription',
         'packages',
         'packages-grid',
         'domain',
         'purchase-transaction'
     ]))
     <script src="{{ URL::asset('build/plugins/bootstrap-tagsinput/bootstrap-tagsinput.js') }}"></script>
 @endif

 @if (Route::is([
         'activities',
         'activity-calls',
         'activity-mail',
         'activity-meeting',
         'activity-task',
         'analytics',
         'campagin',
         'campaign-archieve',
         'campaign-complete',
         'cities',
         'chat',
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
         'boqs',
         'boqs.index',
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
     <!-- Datetimepicker JS -->
     <script src="{{ URL::asset('build/js/bootstrap-datetimepicker.min.js') }}"></script>
 @endif

 @if (Route::is([
         'calendar',
         'task-reports',
         'project-reports',
         'project-dashboard',
         'notes',
         'leads-dashboard',
         'lead-reports',
         'deals-dashboard',
         'analytics',
         'deal-reports',
         'contact-reports',
         'company-reports',
         'chart-apex',
         'dashboard',
         'layout-mini',
         'layout-horizontal-single',
         'layout-without-header',
         'layout-rtl',
         'layout-detached',
         'layout-dark'
     ]))
     <!-- Chart JS -->
     <script src="{{ URL::asset('build/plugins/apexchart/apexcharts.min.js') }}"></script>
     <script src="{{ URL::asset('build/plugins/apexchart/chart-data.js') }}"></script>
 @endif

 @if (Route::is([
        'activities',
        'activity-calls',
        'activity-mail',
        'activity-meeting',
        'activity-task',
        'analytics',
        'call-history',
        'campaign',
        'campaign-archieve',
        'campaign-complete',
        'chat',
        'cities',
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
        'products',
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
        'permissions.index',
        'pages',
        'pages-list',
        'notes',
        'menus.index',
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
        'video-call',
        'leads',
        'lead-reports',
        'language-web',
        'language',
        'companies',
        'companies-grid',
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
        'appearance',
        'form-select2',
        'customers',
        'suppliers',
        'suppliers.index',
        'form-vertical',
        'form-horizontal',
        'proposals-grid',
        'proposals',
        'contracts-grid',
        'contracts',
        'estimations-kanban',
        'estimations',
        'employees',
        'employees.index',
        'invoices',
        'invoices.index',
        'invoice-grid',
        'rvs',
        'rvs.index',
        'kanban-view',
        'invoice',
        'proposals',
        'proposals.index',
        'proposals.read',
        'boqs',
        'boqs.index',
        'customers.index',
        'pdf-templates.index',
        'dashboard',
        'company',
        'subscription',
        'packages',
        'packages-grid',
        'domain',
        'purchase-transaction',
    ]))
    <!-- Select2 JS -->
    <script src="{{ URL::asset('build/plugins/select2/js/select2.min.js') }}"></script>
 @endif

 @if (Route::is(['form-select2', 'customers', 'suppliers']))
    <script src="{{ URL::asset('build/js/custom-select2.js') }}"></script>
@endif

 @if (Route::is([
         'activities',
         'activity-calls',
         'activity-mail',
         'activity-meeting',
         'activity-task',
         'analytics',
         'campaign',
         'campaign-archieve',
         'campaign-complete',
         'companies-grid',
         'companies',
         'company-details',
         'contact-grid',
         'contact-messages',
         'contacts',
         'chat',
         'cities',
         'deals-details',
         'deals-kanban',
         'deals',
         'contact-details',
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
         'products.index',
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
         'leads-details',
         'file-manager',
         'proposals-grid',
         'proposals',
         'proposals.index',
         'contracts-grid',
         'contracts',
         'estimations-kanban',
         'estimations',
         'invoices',
         'invoices.index',
         'invoice-grid',
         'invoice',
         'boqs',
         'boqs.index',
         'company',
         'subscription',
         'domain',
         'purchase-transaction'
     ]))
     <!-- Summernote JS -->
     <script src="{{ URL::asset('build/plugins/summernote/summernote-lite.min.js') }}"></script>
 @endif

 @if (Route::is(['form-fileupload']))
     <!-- Fileupload JS -->
     <script src="{{ URL::asset('build/plugins/fileupload/fileupload.min.js') }}"></script>
 @endif

 @if (Route::is(['form-mask']))
     <!-- Mask JS -->
     <script src="{{ URL::asset('build/js/jquery.maskedinput.min.js') }}"></script>
     <script src="{{ URL::asset('build/js/mask.js') }}"></script>
 @endif

 @if (Route::is(['ui-clipboard']))
     <!-- Clipboard JS -->
     <script src="{{ URL::asset('build/plugins/clipboard/clipboard.min.js') }}"></script>
 @endif

 @if (Route::is(['ui-drag-drop']))
     <!-- Dragula JS -->
     <script src="{{ URL::asset('build/plugins/dragula/js/dragula.min.js') }}"></script>
     <script src="{{ URL::asset('build/plugins/dragula/js/drag-drop.min.js') }}"></script>
     <script src="{{ URL::asset('build/plugins/dragula/js/draggable-cards.js') }}"></script>
 @endif

 @if (Route::is(['ui-rating']))
     <!-- Rater JS -->
     <script src="{{ URL::asset('build/plugins/rater-js/index.js') }}"></script>

     <!-- Internal Ratings JS -->
     <script src="{{ URL::asset('build/js/ratings.js') }}"></script>
 @endif

 @if (Route::is(['ui-counter']))
     <!-- Stickynote JS -->
     <script src="{{ URL::asset('build/plugins/countup/jquery.counterup.min.js') }}"></script>
     <script src="{{ URL::asset('build/plugins/countup/jquery.waypoints.min.js') }}"></script>
     <script src="{{ URL::asset('build/plugins/countup/jquery.missofis-countdown.js') }}"></script>
 @endif

 @if (Route::is(['ui-text-editor', 'chat']))
     <!-- Summernote JS -->
     <script src="{{ URL::asset('build/plugins/summernote/summernote-bs4.min.js') }}"></script>
 @endif

 @if (Route::is(['ui-rangeslider']))
     <!-- Rangeslider JS -->
     <script src="{{ URL::asset('build/plugins/ion-rangeslider/js/ion.rangeSlider.min.js') }}"></script>
     <script src="{{ URL::asset('build/plugins/ion-rangeslider/js/custom-rangeslider.js') }}"></script>
 @endif

 @if (Route::is(['ui-sweetalerts', 'audio-call', 'call-history', 'ui-ribbon', 'chat']))
     <!-- Sweetalert 2 -->
     <script src="{{ URL::asset('build/plugins/sweetalert/sweetalert2.all.min.js') }}"></script>
     <script src="{{ URL::asset('build/plugins/sweetalert/sweetalerts.min.js') }}"></script>
 @else
     <script src="{{ URL::asset('build/plugins/sweetalert/sweetalert2.all.min.js') }}"></script>
     <script src="{{ URL::asset('build/plugins/sweetalert/sweetalerts.min.js') }}"></script>
 @endif
     
 @if (Route::is(['ui-scrollbar']))
     <!-- Plyr JS -->
     <script src="{{ URL::asset('build/plugins/scrollbar/scrollbar.min.js') }}"></script>
     <script src="{{ URL::asset('build/plugins/scrollbar/custom-scroll.js') }}"></script>
 @endif

 @if (Route::is(['ui-stickynote']))
     <!-- Stickynote JS -->
     <script src="{{ URL::asset('build/js/jquery-ui.min.js') }}"></script>
     <script src="{{ URL::asset('build/plugins/stickynote/sticky.js') }}"></script>
 @endif

 @if (Route::is(['ui-toasts', 'categories', 'categories.index', 'products', 'products.index', 'roles.index', 'permission.index']))
     <!-- Mask JS -->
     <script src="{{ URL::asset('build/plugins/toastr/toastr.min.js') }}"></script>
     <script src="{{ URL::asset('build/plugins/toastr/toastr.js') }}"></script>
 @endif
 
 @if (Route::is(['ui-lightbox']))
     <!-- lightbox JS -->
     <script src="{{ URL::asset('build/plugins/lightbox/glightbox.min.js') }}"></script>
     <script src="{{ URL::asset('build/plugins/lightbox/lightbox.js') }}"></script>
 @endif

 @if (Route::is(['video-call', 'audio-call', 'notes', 'file-manager']))
     <!-- Owl JS -->
     <script src="{{ URL::asset('build/plugins/owlcarousel/owl.carousel.min.js') }}"></script>
 @endif

 @if (Route::is(['file-manager']))
     <script src="{{ URL::asset('build/js/plyr-js.js') }}"></script>
 @endif

 @if (Route::is(['form-wizard']))
     <!-- Wizard JS -->
     <script src="{{ URL::asset('build/plugins/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js') }}"></script>
     <script src="{{ URL::asset('build/plugins/twitter-bootstrap-wizard/prettify.js') }}"></script>
     <script src="{{ URL::asset('build/plugins/twitter-bootstrap-wizard/form-wizard.js') }}"></script>
 @endif
 @if (Route::is([
         'audio-call',
         'ban-ip-address',
         'bank-accounts',
         'video-call',
         'todo',
         'tax-rates',
         'storage',
         'sms-gateways',
         'security',
         'project-details',
         'profile',
         'printers',
         'prefixes',
         'preference',
         'payment-gateways',
         'notifications',
         'notes',
         'localization',
         'language-web',
         'language',
         'contact-details',
         'company-details',
         'connected-apps',
         'currencies',
         'company-settings',
         'custom-fields',
         'deals-details',
         'email-settings',
         'file-manager',
         'gdpr-cookies',
         'invoice-settings',
         'appearance',
         'leads-details',
         'social-feed'
     ]))
     <!-- Sticky Sidebar JS -->
     <script src="{{ URL::asset('build/plugins/theia-sticky-sidebar/ResizeSensor.js') }}"></script>
     <script src="{{ URL::asset('build/plugins/theia-sticky-sidebar/theia-sticky-sidebar.js') }}"></script>
 @endif

 @if (Route::is(['video-call', 'chat']))
     <!-- Swiper JS -->
     <script src="{{ URL::asset('build/plugins/swiper/swiper.min.js') }}"></script>
 @endif

 @if (Route::is(['leads-kanban', 'deals-kanban', 'estimations-kanban', 'kanban-view']))
     <!-- Drag Card -->
     <script src="{{ URL::asset('build/js/jquery-ui.min.js') }}"></script>
     <script src="{{ URL::asset('build/js/jquery.ui.touch-punch.min.js') }}"></script>
 @endif

 <!-- Custom Json Js -->
 <script src="{{ URL::asset('build/js/jsonscript.js') }}"></script>

 @if (
     !Route::is([
         'under-maintenance',
         'two-step-verification-3',
         'two-step-verification-2',
         'two-step-verification',
         'success-3',
         'success-2',
         'success',
         'reset-password-3',
         'reset-password-2',
         'reset-password',
         'register-3',
         'register-2',
         'register',
         'login-3',
         'login-2',
         'lock-screen',
         'coming-soon',
         'index',
         'email-verification',
         'email-verification-2',
         'email-verification-3',
         'error-404',
         'error-500',
         'forgot-password',
         'forgot-password-1',
         'forgot-password-2',
         'forgot-password-3',
         'layout-mini',
         'layout-horizontal-single',
         'layout-without-header',
         'layout-rtl',
         'layout-detached',
         'layout-dark',
     ]))
     <script src="{{ URL::asset('build/js/theme-script.js') }}"></script>
 @endif

 <!-- Color Picker JS -->
 <script src="{{ URL::asset('build/plugins/@simonwep/pickr/pickr.es5.min.js') }}"></script>

 <!-- Custom JS -->
 <script src="{{ URL::asset('build/js/theme-colorpicker.js') }}"></script>
 <script src="{{ URL::asset('build/js/script.js') }}"></script>
