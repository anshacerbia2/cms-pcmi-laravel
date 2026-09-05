<?php $page = 'employees.detail'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <div class="row">
                <div class="col-md-12">
                    <!-- Employee Info Card -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Employee Profile</h5>
                            <div class="d-flex gap-2">
                                <a href="/employees" class="btn btn-outline-secondary">
                                    <i class="ti ti-arrow-left me-1"></i>Back to List
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <h6 class="mb-3 text-primary">Personal Information</h6>
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <label class="fw-semibold">Employee Code</label>
                                    <p>{{ $employee->code }}</p>
                                </div>
                                <div class="col-md-4">
                                    <label class="fw-semibold">Full Name</label>
                                    <p>{{ $employee->name }}</p>
                                </div>
                                <div class="col-md-4">
                                    <label class="fw-semibold">Join Date</label>
                                    <p>{{ $employee->join_date ? $employee->join_date->format('d M Y') : '-' }}</p>
                                </div>
                                <div class="col-md-4">
                                    <label class="fw-semibold">Position</label>
                                    <p>{{ $employee->position ?? '-' }}</p>
                                </div>
                                <div class="col-md-4">
                                    <label class="fw-semibold">Marital Status</label>
                                    <p>{{ $employee->marital_status }}</p>
                                </div>
                                <div class="col-md-4">
                                    <label class="fw-semibold">Phone</label>
                                    <p>{{ $employee->phone ?? '-' }}</p>
                                </div>
                                <div class="col-md-4">
                                    <label class="fw-semibold">Personal Email</label>
                                    <p>{{ $employee->personal_email ?? '-' }}</p>
                                </div>
                            </div>

                            <h6 class="mb-3 text-primary">Identity Documents</h6>
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <label class="fw-semibold">KTP Number</label>
                                    <p>{{ $employee->ktp_number ?? '-' }}</p>
                                </div>
                                <div class="col-md-4">
                                    <label class="fw-semibold">SIM Number</label>
                                    <p>{{ $employee->sim_number ?? '-' }}</p>
                                </div>
                                <div class="col-md-4">
                                    <label class="fw-semibold">Passport</label>
                                    <p>{{ $employee->passport_number ?? '-' }}</p>
                                </div>
                                <div class="col-md-4">
                                    <label class="fw-semibold">NPWP</label>
                                    <p>{{ $employee->npwp_number ?? '-' }}</p>
                                </div>
                                <div class="col-md-4">
                                    <label class="fw-semibold">BPJS</label>
                                    <p>{{ $employee->bpjs_number ?? '-' }}</p>
                                </div>
                            </div>

                            <h6 class="mb-3 text-primary">Address (KTP)</h6>
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <label class="fw-semibold">Address Details</label>
                                    <p>{{ $employee->ktp_address ?? '-' }}</p>
                                </div>
                                <div class="col-md-3">
                                    <label class="fw-semibold">Province</label>
                                    <p>{{ $employee->ktp_province ?? '-' }}</p>
                                </div>
                                <div class="col-md-3">
                                    <label class="fw-semibold">City</label>
                                    <p>{{ $employee->ktp_city ?? '-' }}</p>
                                </div>
                                <div class="col-md-3">
                                    <label class="fw-semibold">District</label>
                                    <p>{{ $employee->ktp_district ?? '-' }}</p>
                                </div>
                                <div class="col-md-3">
                                    <label class="fw-semibold">Village</label>
                                    <p>{{ $employee->ktp_village ?? '-' }}</p>
                                </div>
                            </div>

                            <h6 class="mb-3 text-primary">Resident Address</h6>
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <label class="fw-semibold">Address Details</label>
                                    <p>{{ $employee->current_address ?? '-' }}</p>
                                </div>
                                <div class="col-md-3">
                                    <label class="fw-semibold">Province</label>
                                    <p>{{ $employee->current_province ?? '-' }}</p>
                                </div>
                                <div class="col-md-3">
                                    <label class="fw-semibold">City</label>
                                    <p>{{ $employee->current_city ?? '-' }}</p>
                                </div>
                                <div class="col-md-3">
                                    <label class="fw-semibold">District</label>
                                    <p>{{ $employee->current_district ?? '-' }}</p>
                                </div>
                                <div class="col-md-3">
                                    <label class="fw-semibold">Village</label>
                                    <p>{{ $employee->current_village ?? '-' }}</p>
                                </div>
                            </div>

                            <h6 class="mb-3 text-primary">Financial Information</h6>
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <label class="fw-semibold">Bank</label>
                                    <p>{{ $employee->bank->bank_name ?? '-' }}</p>
                                </div>
                                <div class="col-md-4">
                                    <label class="fw-semibold">Account Number</label>
                                    <p>{{ $employee->bank_account_number ?? '-' }}</p>
                                </div>
                                <div class="col-md-4">
                                    <label class="fw-semibold">Account Holder</label>
                                    <p>{{ $employee->bank_account_name ?? '-' }}</p>
                                </div>
                            </div>

                            <h6 class="mb-3 text-primary">System Credentials</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="fw-semibold">Username</label>
                                    <p>{{ $employee->username ?? '-' }}</p>
                                </div>
                                <div class="col-md-4">
                                    <label class="fw-semibold">Role</label>
                                    <p>{{ $employee->role ?? '-' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Page Wrapper -->
@endsection
