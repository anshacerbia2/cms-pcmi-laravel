<?php $page = 'employees'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <div class="row">
                <div class="col-md-12">
                    <div class="card ">
                        <div class="card-header">
                            <!-- Search -->
							<div class="row align-items-center">
								<div class="col-sm-4">
									<form class="icon-form mb-3 mb-sm-0" id="c_employee_list_search_form">
										<span class="form-icon"><i class="ti ti-search"></i></span>
										<input type="text" class="form-control" placeholder="Search Employee" id="c_employee_list_search_input">
									</form>							
								</div>		
                                <div class="col-sm-8">                    
                                    <div class="d-flex align-items-center flex-wrap row-gap-2 justify-content-sm-end">
                                        <div class="dropdown me-2">
                                            <a href="javascript:void(0);" class="dropdown-toggle"  data-bs-toggle="dropdown"><i class="ti ti-package-export me-2"></i>Export</a>
                                            <div class="dropdown-menu  dropdown-menu-end">
                                                <ul>
                                                    <li>
                                                        <a href="javascript:void(0);" class="dropdown-item"><i class="ti ti-file-type-pdf text-danger me-1"></i>Export as PDF</a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>  
                                        <a href="javascript:void(0);" id="c_employee_create_btn" class="btn btn-primary"><i class="ti ti-square-rounded-plus me-2"></i>Add New Employee</a>
                                    </div>
                                </div>
                            </div>
                            <!-- /Search -->
                        </div>
                        <div class="card-body">
                            <!-- Filter -->
                            <div class="d-flex align-items-center justify-content-between flex-wrap row-gap-2 mb-4">
                                <div class="d-flex align-items-center flex-wrap row-gap-2">
                                    <div class="dropdown me-2">
                                        <a href="javascript:void(0);" class="dropdown-toggle"  data-bs-toggle="dropdown"><i class="ti ti-sort-ascending-2 me-2"></i>Sort </a>
                                        <div class="dropdown-menu  dropdown-menu-start">
                                            <ul>
                                                <li><a href="javascript:void(0);" class="dropdown-item"><i class="ti ti-circle-chevron-right me-1"></i>Recently Added</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /Filter -->

                            <!-- Employee List -->
                            <div class="table-responsive custom-table">
                                <table class="table" id="employee_list" data-url="{{ route('employees.index') }}"> 
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Code</th>
                                            <th>Name</th>
                                            <th>Position</th>
                                            <th>Phone</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                            <div class="row align-items-center mt-2" style="row-gap: 1em;">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center justify-content-center justify-content-md-start">
                                        <div class="datatable-info"></div>
                                        <div class="datatable-length"></div>
                                    </div>
                                </div>
                                <div class="col-md-6 flex-grow-1">
                                    <div class="datatable-paginate"></div>
                                </div>
                            </div>
                            <!-- /Employee List -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Page Wrapper -->

	@include('components.employees.create-modal')
	@include('components.employees.modal')
@endsection

@push('scripts')
    <script src="/build/js/employees/shared_var.js"></script>
    <script src="/build/js/employees/datatables.js"></script>
    <script src="/build/js/employees/events.js"></script>
@endpush
