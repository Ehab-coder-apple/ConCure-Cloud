@extends('master.layouts.app')

@section('title', 'Reports | ConCure Master')

@section('content')
<div class="d-flex align-items-center mb-4">
    <i class="fas fa-chart-bar fa-2x text-primary me-3"></i>
    <div>
        <h4 class="mb-0">System Reports</h4>
        <small class="text-muted">High-level analytics across all clinics</small>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card border-left-primary h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs text-uppercase text-primary mb-1">Clinics Overview</div>
                        <div class="h5 mb-0 text-gray-800">Active vs Inactive</div>
                    </div>
                    <div class="icon-circle bg-primary text-white">
                        <i class="fas fa-hospital"></i>
                    </div>
                </div>
                <p class="text-muted mt-3 mb-0">Summary of registered clinics and activation status.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-left-success h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs text-uppercase text-success mb-1">Users Growth</div>
                        <div class="h5 mb-0 text-gray-800">By Role</div>
                    </div>
                    <div class="icon-circle bg-success text-white">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <p class="text-muted mt-3 mb-0">Track user growth and distribution across roles.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-left-info h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs text-uppercase text-info mb-1">Activity</div>
                        <div class="h5 mb-0 text-gray-800">Audit Highlights</div>
                    </div>
                    <div class="icon-circle bg-info text-white">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                </div>
                <p class="text-muted mt-3 mb-0">Top recent activities across the platform.</p>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header d-flex align-items-center">
        <i class="fas fa-tools me-2 text-muted"></i>
        <strong class="me-2">Coming soon</strong>
        <span class="text-muted">Downloadable CSV/PDF and filters</span>
    </div>
    <div class="card-body">
        <p class="text-muted mb-0">This page is a placeholder to prevent 500 errors. Detailed reports and exports will be added here.</p>
    </div>
</div>
@endsection

