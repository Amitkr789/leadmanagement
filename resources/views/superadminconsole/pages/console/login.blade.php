@extends('superadminconsole.layouts.auth')
@section('title', 'Owner console')
@section('hero')
  <span class="status tone-warning mb-3">Restricted area</span>
  <h2 class="hero-title">Owner console</h2>
  <ul class="hero-list">
    <li><i class="bi bi-shield-lock"></i>Two-step verification on every sign-in</li>
    <li><i class="bi bi-cloud-check"></i>Protected by Cloudflare Access</li>
    <li><i class="bi bi-journal-text"></i>Every attempt is logged with IP and device</li>
  </ul>
@endsection
@section('form')
  <h1 class="h4 fw-bold mb-1">Owner sign in</h1>
  <p class="text-secondary mb-4">Authorised personnel only. Activity on this page is monitored.</p>
  <div class="alert alert-danger d-none js-form-alert" role="alert"></div>
  <form data-ajax method="POST" action="{{ route('console.login.store') }}" novalidate>
    @csrf
    <div class="mb-3">
      <label for="email" class="form-label fw-medium">Owner email <span class="text-danger">*</span></label>
      <div class="input-group"><span class="input-group-text"><i class="bi bi-envelope"></i></span>
        <input id="email" name="email" type="email" class="form-control" placeholder="owner@yourcompany.com" autocomplete="username" required autofocus>
        <div class="invalid-feedback"></div></div>
    </div>
    <div class="mb-3">
      <label for="password" class="form-label fw-medium">Password <span class="text-danger">*</span></label>
      <div class="input-group"><span class="input-group-text"><i class="bi bi-lock"></i></span>
        <input id="password" name="password" type="password" class="form-control" placeholder="Enter your password" autocomplete="current-password" required>
        <button type="button" class="btn btn-outline-secondary js-toggle-pass" aria-label="Show password"><i class="bi bi-eye"></i></button>
        <div class="invalid-feedback"></div></div>
      <div class="form-text text-warning d-none js-caps"><i class="bi bi-capslock"></i> Caps Lock is on</div>
    </div>
    @if (config('console.turnstile.site_key'))
      <div class="cf-turnstile mb-3" data-sitekey="{{ config('console.turnstile.site_key') }}"></div>
    @endif
    <button class="btn btn-primary btn-lg w-100" type="submit"><span class="btn-label">Continue</span> <span class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span></button>
  </form>
@endsection
@push('scripts')
  @if (config('console.turnstile.site_key'))<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>@endif
@endpush
