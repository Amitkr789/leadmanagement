@extends('superadminconsole.layouts.app')
@section('title', 'Payment Gateway')
@section('active', 'payments')
@section('content')
@php $ready = $s['enabled'] && $s['key_id'] && $s['has_secret']; @endphp
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <div><h1 class="page-title">Payment Gateway</h1><div class="caption">Connect Razorpay so companies can pay for plans online.</div></div>
  <span class="status tone-{{ $ready ? 'success' : 'warning' }}">{{ $ready ? 'Online payments ON (' . strtoupper($s['mode']) . ')' : 'Not active yet' }}</span>
</div>
<div class="row g-3">
  <div class="col-lg-7">
    <form id="pgForm" class="mp-card" action="{{ route('console.payments.update') }}" method="POST" data-test-url="{{ route('console.payments.test') }}" novalidate>
      @csrf
      <div class="mp-card-body">
        <label class="form-label fw-medium">Mode</label>
        <div class="row g-3 mb-3">
          @foreach (['test' => ['Test mode', 'Fake payments for trying things out.'], 'live' => ['Live mode', 'Real money from real customers.']] as $m => [$t, $d])
            <div class="col-6"><label class="choice-card w-100 h-100"><input type="radio" name="mode" value="{{ $m }}" @checked($s['mode'] === $m)><span class="box"><strong>{{ $t }}</strong><span class="d-block caption mt-1">{{ $d }}</span></span></label></div>
          @endforeach
        </div>
        <div class="mb-3"><label class="form-label fw-medium" for="key_id">Key ID <span class="text-danger">*</span></label><input id="key_id" name="key_id" class="form-control font-monospace" value="{{ $s['key_id'] }}" placeholder="rzp_test_XXXXXXXXXXXX" autocomplete="off" required><div class="invalid-feedback"></div></div>
        <div class="mb-3"><label class="form-label fw-medium" for="key_secret">Key Secret {!! $s['has_secret'] ? '<span class="status tone-success ms-1">Saved</span>' : '<span class="text-danger">*</span>' !!}</label>
          <div class="input-group"><input id="key_secret" name="key_secret" type="password" class="form-control font-monospace" placeholder="{{ $s['has_secret'] ? 'Leave blank to keep the saved secret' : 'Paste your key secret' }}" autocomplete="new-password"><button type="button" class="btn btn-outline-secondary js-eye" aria-label="Show secret"><i class="bi bi-eye"></i></button><div class="invalid-feedback"></div></div>
          <div class="form-text">Stored encrypted. It is never shown again.</div></div>
        <div class="mb-3"><label class="form-label fw-medium" for="webhook_secret">Webhook secret {!! $s['has_webhook'] ? '<span class="status tone-success ms-1">Saved</span>' : '' !!}</label>
          <input id="webhook_secret" name="webhook_secret" type="password" class="form-control font-monospace" placeholder="{{ $s['has_webhook'] ? 'Leave blank to keep the saved secret' : 'The secret you set on the Razorpay webhook' }}" autocomplete="new-password"><div class="invalid-feedback"></div>
          <div class="form-text">Lets us activate plans even if the customer closes the browser after paying.</div></div>
        <div class="form-check form-switch mb-1"><input class="form-check-input" type="checkbox" role="switch" id="enabled" name="enabled" value="1" @checked($s['enabled'])><label class="form-check-label" for="enabled">Accept online payments (shows "Pay now" on the website and Billing)</label></div>
      </div>
      <div class="mp-card-head border-top border-bottom-0 flex-wrap">
        <div class="pg-status caption" id="pgStatus" role="status"></div>
        <div class="d-flex gap-2 ms-auto"><button type="button" class="btn btn-light border js-test"><i class="bi bi-plug me-1"></i>Save &amp; test connection</button>
          <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1"></i>Save <span class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span></button></div>
      </div>
    </form>
  </div>
  <aside class="col-lg-5"><div class="mp-card"><div class="mp-card-head"><h2 class="card-title-sm">Setup guide</h2></div><div class="mp-card-body">
    <ol class="ps-3 mb-0 d-grid gap-3">
      <li><strong>Get your keys.</strong> Razorpay Dashboard &rarr; Account &amp; Settings &rarr; API Keys &rarr; Generate Key. Paste the Key ID and Secret here.</li>
      <li><strong>Add the webhook.</strong> Razorpay Dashboard &rarr; Webhooks &rarr; Add. Use this URL and choose your own secret (paste the same secret on the left):
        <div class="cred mt-2"><code class="text-break">{{ $webhookUrl }}</code><button type="button" class="btn btn-sm btn-light border js-copy-url" data-url="{{ $webhookUrl }}" aria-label="Copy webhook URL"><i class="bi bi-clipboard"></i></button></div>
        <div class="caption">Events: <b>payment.captured</b> and <b>order.paid</b>.</div></li>
      <li><strong>Test it.</strong> Click "Save &amp; test connection". Start in Test mode, make a small payment from the website, then switch to Live.</li>
    </ol>
    @if (!str_starts_with($webhookUrl, 'https://'))<div class="alert alert-warning mt-3 mb-0 small">Razorpay can only reach a public <b>https</b> URL. On localhost use a tunnel (for example ngrok) for the webhook.</div>@endif
  </div></div></aside>
</div>
@endsection
