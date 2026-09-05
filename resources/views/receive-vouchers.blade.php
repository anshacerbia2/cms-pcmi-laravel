<?php $page = 'rvs'; ?>
@extends('layout.mainlayout')
@section('content')

    <div class="page-wrapper">
        <div class="content">

            <div class="row">
                <div class="col-md-12">

                    @component('components.breadcrumb')
                        @slot('title')
                            Receive Vouchers
                        @endslot
                        @slot('item1')
                            Finance
                        @endslot
                        @slot('item2')
                            rvs
                        @endslot
                    @endcomponent

                    <div class="card ">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col-sm-4">
									<form class="icon-form mb-3 mb-sm-0" id="c_rv_list_search_form">
										<span class="form-icon"><i class="ti ti-search"></i></span>
										<input type="text" class="form-control" placeholder="Search RV" id="c_rv_list_search_input">
									</form>	
								</div>	
                                <div class="col-sm-8">					
                                    <div class="d-flex align-items-center flex-wrap row-gap-2 justify-content-sm-end">
                                        <a href="javascript:void(0);" id="c_rv_create_btn" class="btn btn-primary"><i class="ti ti-square-rounded-plus me-2"></i>Add New RV</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive custom-table">
								<table class="table" id="rv_list" data-url="{{ route('rvs.index') }}">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>RV Number</th>
                                            <th>RV Date</th>
                                            <th>Payer</th>
                                            <th>Purpose</th>
                                            <th>Bank</th>
                                            <th class="text-end">Amount</th>
                                            <th class="no-sort text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>								
                            </div>
                            <div class="row align-items-center mt-2" style="row-gap: 1em;">
								<div class="col-md-6">
									<div class="d-flex align-items-center justify-content-center justify-content-md-start">
										<div class="rv_list_datatable_info"></div>
										<div class="rv_list_datatable_length"></div>
									</div>
								</div>
								<div class="col-md-6 flex-grow-1">
									<div class="rv_list_datatable_paginate"></div>
								</div>
							</div>
                        </div>
					</div>
                </div>
            </div>
        </div>
    </div>
    @include('components.rvs.modal')
    @include('components.rvs.create-modal')
@endsection

@push('scripts')
    <script src="/build/js/rvs/shared_var.js"></script>
    <script src="/build/js/rvs/datatables.js"></script>
    <script src="/build/js/rvs/events.js"></script>
@endpush
