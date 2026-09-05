@php
use App\Http\Services\MenuService;
@endphp

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
	<div class="modern-profile p-3 pb-0">
		<div class="sidebar-nav mb-3">
			<ul 
				class="nav nav-tabs nav-tabs-solid nav-tabs-rounded nav-justified bg-transparent"
				role="tablist"
			>
				<li class="nav-item"><a class="nav-link active border-0" href="#">Menu</a></li>
				<li class="nav-item"><a class="nav-link border-0" href="{{url('chat')}}">Chats</a></li>
				<li class="nav-item"><a class="nav-link border-0" href="{{url('email')}}">Inbox</a></li>
			</ul>
		</div>
	</div>
	<div class="sidebar-header p-3 pb-0 pt-2">
		<div class="d-flex align-items-center justify-content-between menu-item mb-3">
			<div class="me-3">
				<a href="{{url('calendar')}}" class="btn btn-icon border btn-menubar">
					<i class="ti ti-layout-grid-remove"></i>
				</a>
			</div>
			<div class="me-3">
				<a href="{{url('chat')}}" class="btn btn-icon border btn-menubar position-relative">
					<i class="ti ti-brand-hipchat"></i>
				</a>
			</div>
			<div class="me-3 notification-item">
				<a href="{{url('activities')}}" class="btn btn-icon border btn-menubar position-relative me-1">
					<i class="ti ti-bell"></i>
					<span class="notification-status-dot"></span>
				</a>
			</div>
			<div class="me-0">
				<a href="{{url('email')}}" class="btn btn-icon border btn-menubar">
					<i class="ti ti-message"></i>
				</a>
			</div>
		</div>
	</div>
	<div class="sidebar-inner slimscroll">
		<div id="sidebar-menu" class="sidebar-menu">
			<ul>
				<li class="clinicdropdown">
					<a href="{{ url('profile') }}">
						<img src="{{ URL::asset('/build/img/profiles/Ryan-Circle.png') }}" class="img-fluid" alt="Profile">
						<div class="user-names">
							<h5>Ryan Fadilla</h5>
							<h6>Project Manager</h6>
						</div>
					</a>
				</li>
			</ul>
			<ul>
				<li>
					<h6 class="submenu-hdr">Main Menu</h6>
					<ul>
						<li class="submenu">
							<a 
								href="javascript:void(0);"
								class="{{ Request::is('deals-dashboard', 'leads-dashboard', 'project-dashboard') ? 'subdrop active' : '' }}"
							>
								<i class="ti ti-layout-2"></i>
								<span>Dashboard</span>
								<span class="menu-arrow"></span>
							</a>
							<ul>
								<!-- <li><a class="{{ Request::is('deals-dashboard') ? 'active' : '' }}"
												href="{{ url('deals-dashboard') }}">Deals Dashboard</a></li>
								<li><a class="{{ Request::is('leads-dashboard') ? 'active' : '' }}"
												href="{{ url('leads-dashboard') }}">Leads Dashboard</a></li> -->
								<li>
									<a 
										class="{{ Request::is('project-dashboard') ? 'active' : '' }}"
										href="{{ url('project-dashboard') }}"
									>
										Project Dashboard
									</a>
								</li>
							</ul>
						</li>
					</ul>
				</li>
				@php
					$groups = [
						'Products' => [
							'categories.index',
							'products.index',
						],
						'Finance' => [
							'rvs.index',
							'pvs.index',
						],
						'Main' => [
							'contacts.index',
							'companies.index',
							'customers.index',
							'employees.index',
							'suppliers.index',
							'projects.index',
							'proposals.index',
							'invoices.index',
							'boqs.index',
							'payments.index',
							'banks.index',
						],
						'Others' => [
							'pdf-templates.index',
						],
						'User Management' => [
							'users.index', 
							'roles.index', 
							'menus.index', 
							'permissions.index'
						]
					];
				@endphp
				@foreach ($groups as $groupTitle => $routes)
					@php
						$menusInGroup = $userMenus->filter(function ($menu) use ($routes) {
							return in_array($menu->permission->route, $routes);
						});
					@endphp
					@if ($menusInGroup->isNotEmpty())
						<li>
							<h6 class="submenu-hdr">{{ $groupTitle }}</h6>
							<ul>
								@foreach ($menusInGroup as $menu)
									<li>
										<a 
											href="{{ route($menu->permission->route) }}"
											class="{{ Route::is(explode('.', $menu->permission->route)[0] . '.*') ? 'active' : '' }}"
										>
											@if ($menu->icon)
													<i class="{{ $menu->icon }}"></i>
											@endif
											<span>{{ $menu->name }}</span>
										</a>
									</li>
								@endforeach
							</ul>
						<li>
					@endif
				@endforeach
			</ul>
		</div>
	</div>
</div>
<!-- /Sidebar -->
