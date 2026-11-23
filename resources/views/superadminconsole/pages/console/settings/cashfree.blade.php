@extends('superadminconsole.layouts.app')
@section('title', 'Cashfree Verification')
@section('active', 'cashfree')
@section('content')
@php $ready = $s['enabled'] && $s['client_id'] && $s['has_secret']; @endphp
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <div><h1 class="page-title">Cashfree Verification</h1><div class="caption">Automatic checks for driving licences, vehicle RC and pollution certificates. Only companies on a plan with the Verification feature can use it.</div></div>
  <span class="status tone-{{ $ready ? 'success' : 'warning' }}">{{ $ready ? 'ON (' . strtoupper($s['mode']) . ')' : 'Not active yet' }}</span>
</div>
<div class="row g-3">
  <div class="col-lg-8">
    <form id="cfForm" class="mp-card" action="{{ route('console.cashfree.update') }}" method="POST" data-test-url="{{ route('console.cashfree.test') }}" novalidate>
      @csrf
      <div class="mp-card-body">
        <label class="form-label fw-medium">Environment</label>
        <div class="row g-3 mb-3">
          @foreach (['sandbox' => ['Sandbox', 'Test data only. Nothing is charged.'], 'live' => ['Live', 'Real checks. Cashfree charges per call.']] as $m => [$t, $d])
            <div class="col-6"><label class="choice-card w-100 h-100"><input type="radio" name="mode" value="{{ $m }}" @checked($s['mode'] === $m)><span class="box"><strong>{{ $t }}</strong><span class="d-block caption mt-1">{{ $d }}</span><span class="d-block caption font-monospace mt-1">{{ $hosts[$m] }}</span></span></label></div>
          @endforeach
        </div>
        <div class="row">
          <div class="col-md-6 mb-3"><label class="form-label fw-medium" for="client_id">Client ID <span class="text-danger">*</span></label><input id="client_id" name="client_id" class="form-control font-monospace" value="{{ $s['client_id'] }}" autocomplete="off" required><div class="invalid-feedback"></div></div>
          <div class="col-md-6 mb-3"><label class="form-label fw-medium" for="client_secret">Client Secret {!! $s['has_secret'] ? '<span class="status tone-success ms-1">Saved</span>' : '<span class="text-danger">*</span>' !!}</label>
            <div class="input-group"><input id="client_secret" name="client_secret" type="password" class="form-control font-monospace" placeholder="{{ $s['has_secret'] ? 'Leave blank to keep the saved secret' : 'Paste the secret' }}" autocomplete="new-password"><button type="button" class="btn btn-outline-secondary js-eye" aria-label="Show secret"><i class="bi bi-eye"></i></button><div class="invalid-feedback"></div></div></div>
        </div>
        <div class="int-label mb-2 fw-medium">Services and endpoint paths</div>
        <div class="d-grid gap-2 mb-3">
          @foreach ($services as $k => $sv)
            <div class="border rounded-3 p-3"><div class="d-flex align-items-center gap-2 mb-2"><div class="form-check form-switch mb-0"><input class="form-check-input" type="checkbox" role="switch" id="svc_{{ $k }}" name="svc[{{ $k }}]" value="1" @checked($sv['on'])><label class="form-check-label fw-semibold" for="svc_{{ $k }}">{{ $sv['label'] }}</label></div></div>
              <div class="input-group"><span class="input-group-text font-monospace small">{{ parse_url($hosts[$s['mode']], PHP_URL_HOST) }}</span><input name="path[{{ $k }}]" class="form-control font-monospace" value="{{ $sv['path'] }}" placeholder="{{ $k === 'puc' ? '/path from the Cashfree docs' : '/verification/...' }}" autocomplete="off"><div class="invalid-feedback"></div></div></div>
          @endforeach
          <div class="form-text">Paste the exact path from your Cashfree dashboard / docs. Only a path starting with "/" is accepted, so requests can only go to Cashfree.</div>
        </div>
        <div class="row">
          <div class="col-md-6 mb-3"><label class="form-label fw-medium" for="monthly_limit">Checks per company per month <span class="text-danger">*</span></label><input id="monthly_limit" name="monthly_limit" type="number" min="1" class="form-control" value="{{ $s['limit'] }}" required><div class="invalid-feedback"></div><div class="form-text">Protects you from surprise bills.</div></div>
          <div class="col-md-6 mb-3"><label class="form-label fw-medium" for="api_version">API version <span class="caption">(optional)</span></label><input id="api_version" name="api_version" class="form-control font-monospace" value="{{ $s['api_version'] }}" placeholder="e.g. 2023-12-18" autocomplete="off"><div class="invalid-feedback"></div></div>
        </div>
        <div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="enabled" name="enabled" value="1" @checked($s['enabled'])><label class="form-check-label" for="enabled">Turn Cashfree verification ON for companies that have the Verification feature</label></div>
      </div>
      <div class="mp-card-head border-top border-bottom-0 flex-wrap"><div class="caption" id="cfStatus" role="status"></div>
        <div class="d-flex gap-2 ms-auto"><button type="button" class="btn btn-light border js-test"><i class="bi bi-plug me-1"></i>Save &amp; test connection</button><button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1"></i>Save <span class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span></button></div></div>
    </form>
  </div>
  <aside class="col-lg-4 d-grid gap-3 align-content-start">
    <div class="mp-card"><div class="mp-card-head"><h2 class="card-title-sm">This month</h2></div><div class="mp-card-body"><div class="row g-3 text-center">
      <div class="col-4"><div class="kpi-value">{{ $usage['calls'] }}</div><div class="caption">Checks</div></div><div class="col-4"><div class="kpi-value">{{ $usage['ok'] }}</div><div class="caption">Successful</div></div><div class="col-4"><div class="kpi-value">{{ $usage['companies'] }}</div><div class="caption">Companies</div></div></div></div></div>
    <div class="mp-card"><div class="mp-card-head"><h2 class="card-title-sm">How it works</h2></div><div class="mp-card-body"><ol class="ps-3 mb-0 d-grid gap-2 small">
      <li>Add the <b>Verification</b> feature to the plans that should get it (Plans &amp; Pricing).</li>
      <li>Those companies see a "Fetch from Cashfree" button on the driver and vehicle forms. Everyone else keeps typing the details by hand.</li>
      <li>For <b>Live</b> mode Cashfree needs your server's IP whitelisted in its dashboard.</li>
      <li>Start in <b>Sandbox</b>, press "Save &amp; test connection", then switch to Live.</li></ol></div></div>
  </aside>
</div>
@endsection
