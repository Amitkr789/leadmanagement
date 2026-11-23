  
@extends('superadminconsole.layouts.app')
@section('title', 'Owner Dashboard · MovePro Console')

@section('content')
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <div><h1 class="page-title">Owner Dashboard</h1><div class="caption">Manage every company using MovePro CRM · Saturday, 03 Oct 2026</div></div>
      <div class="d-flex gap-2">
        <button class="btn btn-light border" data-soon="1" title="Export"><i class="bi bi-download me-1"></i>Export</button>
        <a href="#" data-soon="1" title="Add Company" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Company</a>
      </div>
    </div>

    <section class="row g-3 mb-4" id="kpiRow" aria-label="Key metrics"></section>

    <section class="row g-3 mb-4">
      <div class="col-lg-8"><div class="mp-card h-100"><div class="mp-card-head"><h2 class="card-title-sm">Monthly Recurring Revenue</h2><span class="caption">Last 7 months</span></div><div class="mp-card-body" style="height:290px"><canvas id="mrrChart" aria-label="MRR growth chart" role="img"></canvas></div></div></div>
      <div class="col-lg-4"><div class="mp-card h-100"><div class="mp-card-head"><h2 class="card-title-sm">Companies by Plan</h2></div><div class="mp-card-body" style="height:290px"><canvas id="planChart" aria-label="Plan distribution chart" role="img"></canvas></div></div></div>
      <div class="col-lg-6"><div class="mp-card h-100"><div class="mp-card-head"><h2 class="card-title-sm">New Companies vs Churn</h2></div><div class="mp-card-body" style="height:260px"><canvas id="signupChart" aria-label="Signups and churn chart" role="img"></canvas></div></div></div>
      <div class="col-lg-6"><div class="mp-card h-100"><div class="mp-card-head"><h2 class="card-title-sm">Module Adoption</h2><span class="caption">Most used features</span></div><div class="mp-card-body" style="height:260px"><canvas id="moduleChart" aria-label="Module adoption chart" role="img"></canvas></div></div></div>
    </section>

    <section class="row g-3">
      <div class="col-xl-4">
        <div class="mp-card mb-3"><div class="mp-card-head"><h2 class="card-title-sm">Action Required</h2><span class="status tone-danger">5 open</span></div><div id="alertList"></div></div>
        <div class="mp-card"><div class="mp-card-head"><h2 class="card-title-sm">Platform Health</h2><span class="status tone-success">Operational</span></div><div class="mp-card-body" id="healthList"></div></div>
      </div>
      <div class="col-xl-8"><div class="mp-card h-100">
        <div class="mp-card-head"><h2 class="card-title-sm">Recent Companies</h2><a href="#" data-soon="1" title="Companies" class="small text-decoration-none">View all</a></div>
        <div class="table-responsive"><table class="table mp-table">
          <thead><tr><th>Company</th><th>City</th><th>Plan</th><th>Users</th><th>Joined</th><th>Status</th><th>Action</th></tr></thead>
          <tbody id="companyRows"></tbody>
        </table></div>
      </div></div>
    </section>
    @endsection