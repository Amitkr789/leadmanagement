@extends('superadminconsole.layouts.app')
@section('title', 'Add company')
@section('active', 'company-create')
@section('breadcrumb')<li class="breadcrumb-item">Tenants</li><li class="breadcrumb-item active" aria-current="page">Add Company</li>@endsection
@section('content')
<div class="mb-4"><h1 class="page-title">Add Company</h1><div class="caption">Set up a new packers &amp; movers company in three quick steps.</div></div>

<div class="stepper" role="list">
  <div class="stepper-item active" data-step="1" role="listitem"><span class="num">1</span><span class="lbl">Company</span></div>
  <div class="stepper-item" data-step="2" role="listitem"><span class="num">2</span><span class="lbl">Plan &amp; Access</span></div>
  <div class="stepper-item" data-step="3" role="listitem"><span class="num">3</span><span class="lbl">Admin Login</span></div>
</div>

<div class="row g-3">
  <div class="col-lg-8">
    <form id="companyForm" class="mp-card" action="{{ route('console.companies.store') }}" method="POST" novalidate>
      @csrf
      <div class="mp-card-body">

        {{-- STEP 1 --}}
        <section class="wiz-step" data-step="1">
          <h2 class="section-title mb-3">Company details</h2>
          <div class="row">
            <div class="col-12 mb-3"><label class="form-label fw-medium" for="name">Company name <span class="text-danger">*</span></label><input id="name" name="name" class="form-control" placeholder="e.g. Sharma Packers &amp; Movers" required autofocus><div class="invalid-feedback"></div></div>
            <div class="col-md-6 mb-3"><label class="form-label fw-medium" for="contact_name">Owner / contact person <span class="text-danger">*</span></label><input id="contact_name" name="contact_name" class="form-control" placeholder="e.g. Rahul Sharma" required><div class="invalid-feedback"></div></div>
            <div class="col-md-6 mb-3"><label class="form-label fw-medium" for="mobile">Mobile <span class="text-danger">*</span></label><div class="input-group"><span class="input-group-text">+91</span><input id="mobile" name="mobile" class="form-control" inputmode="numeric" maxlength="10" placeholder="98290 12345" required><div class="invalid-feedback"></div></div></div>
            <div class="col-md-6 mb-3"><label class="form-label fw-medium" for="email">Company email <span class="text-danger">*</span></label><input id="email" name="email" type="email" class="form-control" placeholder="info@company.com" required><div class="invalid-feedback"></div></div>
            <div class="col-md-6 mb-3"><label class="form-label fw-medium" for="gst_number">GST number <span class="caption">(optional)</span></label><input id="gst_number" name="gst_number" class="form-control text-uppercase" maxlength="15" placeholder="08ABCDE1234F1Z5"><div class="invalid-feedback"></div></div>
            <div class="col-md-6 mb-3"><label class="form-label fw-medium" for="city">City <span class="text-danger">*</span></label><input id="city" name="city" class="form-control" placeholder="e.g. Jaipur" required><div class="invalid-feedback"></div></div>
            <div class="col-md-6 mb-3"><label class="form-label fw-medium" for="state">State <span class="text-danger">*</span></label><input id="state" name="state" class="form-control" list="stateList" placeholder="Start typing..." required><datalist id="stateList">@foreach ($states as $s)<option value="{{ $s }}">@endforeach</datalist><div class="invalid-feedback"></div></div>
            <div class="col-12 mb-1"><label class="form-label fw-medium" for="address">Address <span class="caption">(optional)</span></label><textarea id="address" name="address" rows="2" class="form-control" placeholder="Office address"></textarea></div>
          </div>
        </section>

        {{-- STEP 2 --}}
        <section class="wiz-step d-none" data-step="2">
          <h2 class="section-title mb-3">How should this company start?</h2>
          <div class="row g-3 mb-4">
            <div class="col-sm-6"><label class="choice-card w-100 h-100"><input type="radio" name="access_type" value="trial" checked><span class="box"><strong><i class="bi bi-hourglass-split text-info me-1"></i>Free trial</strong><span class="d-block caption mt-1">Full access for a limited time. No payment needed now.</span></span></label></div>
            <div class="col-sm-6"><label class="choice-card w-100 h-100"><input type="radio" name="access_type" value="paid"><span class="box"><strong><i class="bi bi-patch-check text-success me-1"></i>Paid subscription</strong><span class="d-block caption mt-1">Activate immediately with a billing cycle.</span></span></label></div>
          </div>

          <div class="trial-opts mb-4">
            <label class="form-label fw-medium">Trial length</label>
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
              @foreach ([7, 14, 30] as $d)<button type="button" class="chip js-trial-chip" data-days="{{ $d }}">{{ $d }} days</button>@endforeach
              <div class="input-group" style="width:150px"><input type="number" id="trial_days" name="trial_days" class="form-control" value="14" min="1" max="90" aria-label="Custom trial days"><span class="input-group-text">days</span><div class="invalid-feedback"></div></div>
            </div>
            <div class="caption"><i class="bi bi-calendar-event me-1"></i>Trial ends on <strong id="trialEnd">—</strong></div>
          </div>
          <div class="paid-opts mb-4 d-none">
            <label class="form-label fw-medium">Billing cycle</label>
            <div class="row g-3">
              <div class="col-6"><label class="choice-card w-100"><input type="radio" name="billing_cycle" value="monthly" checked><span class="box">Monthly</span></label></div>
              <div class="col-6"><label class="choice-card w-100"><input type="radio" name="billing_cycle" value="yearly"><span class="box">Yearly <span class="status tone-success ms-1">2 months free</span></span></label></div>
            </div>
          </div>

          <label class="form-label fw-medium">Plan</label>
          <div class="row g-3" id="planChoices">@include('superadminconsole.pages.console.plans._choices')</div>
        </section>

        {{-- STEP 3 --}}
        <section class="wiz-step d-none" data-step="3">
          <h2 class="section-title mb-1">First admin login</h2>
          <p class="caption mb-3">This person signs in at the company login page and can invite the rest of the team.</p>
          <div class="row">
            <div class="col-md-6 mb-3"><label class="form-label fw-medium" for="admin_name">Admin name <span class="text-danger">*</span></label><input id="admin_name" name="admin_name" class="form-control" required><div class="invalid-feedback"></div></div>
            <div class="col-md-6 mb-3"><label class="form-label fw-medium" for="admin_email">Admin email (login) <span class="text-danger">*</span></label><input id="admin_email" name="admin_email" type="email" class="form-control" autocomplete="off" required><div class="invalid-feedback"></div></div>
            <div class="col-12 mb-1"><label class="form-label fw-medium" for="admin_password">Password <span class="text-danger">*</span></label>
              <div class="input-group"><input id="admin_password" name="admin_password" class="form-control font-monospace" autocomplete="off" required>
                <button type="button" class="btn btn-outline-secondary js-regen" aria-label="Generate new password"><i class="bi bi-arrow-repeat"></i></button>
                <button type="button" class="btn btn-outline-secondary js-copy" data-target="#admin_password" aria-label="Copy password"><i class="bi bi-clipboard"></i></button>
                <div class="invalid-feedback"></div></div>
              <div class="form-text">A strong password is generated for you. You will see it once more after saving.</div></div>
          </div>
        </section>
      </div>
      <div class="mp-card-head border-top border-bottom-0">
        <button type="button" class="btn btn-light border js-back invisible"><i class="bi bi-arrow-left me-1"></i>Back</button>
        <button type="button" class="btn btn-primary js-next">Next<i class="bi bi-arrow-right ms-1"></i></button>
        <button type="submit" class="btn btn-primary js-submit d-none"><i class="bi bi-check2-circle me-1"></i>Create Company <span class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span></button>
      </div>
    </form>
  </div>

  <aside class="col-lg-4"><div class="mp-card summary"><div class="mp-card-head"><h2 class="card-title-sm">Summary</h2></div>
    <dl class="mp-card-body mb-0">
      <dt>Company</dt><dd id="sName">—</dd><dt>Location</dt><dd id="sCity">—</dd>
      <dt>Plan</dt><dd id="sPlan">Business</dd><dt>Access</dt><dd id="sAccess">—</dd>
      <dt>Trial ends / Billing</dt><dd id="sBill">—</dd><dt>Admin login</dt><dd id="sAdmin" class="mb-0">—</dd>
    </dl></div></aside>
</div>

<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="okTitle" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <div class="modal-body p-4">
      <div class="kpi-icon tone-success mb-3"><i class="bi bi-check2-circle"></i></div>
      <h2 class="h5 fw-bold" id="okTitle">Company created</h2>
      <p class="text-secondary"><strong id="okName"></strong> &middot; <span id="okAccess"></span></p>
      <p class="caption mb-2">Share these login details. The password is not shown again.</p>
      <div class="cred"><span id="okUrl"></span></div><div class="cred"><span id="okEmail"></span></div><div class="cred font-monospace"><span id="okPass"></span></div>
    </div>
    <div class="modal-footer"><button class="btn btn-light border js-copy-all"><i class="bi bi-clipboard me-1"></i>Copy all</button><a href="{{ route('console.companies.create') }}" class="btn btn-primary">Add another company</a></div>
  </div></div>
</div>
@include('superadminconsole.pages.console.plans._modal')
@endsection
