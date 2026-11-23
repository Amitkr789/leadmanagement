@extends('superadminconsole.layouts.auth')
@section('title', 'Sign in')
@section('hero')
  <h2 class="hero-title">From first enquiry to final delivery, in one place.</h2>
  <ul class="hero-list">
    <li><i class="bi bi-receipt"></i>Create and print bilty in seconds</li>
    <li><i class="bi bi-geo-alt"></i>Track every shipment on the road</li>
    <li><i class="bi bi-wallet2"></i>Collect payments without chasing</li>
  </ul>
@endsection
@section('form')
  <h1 class="h4 fw-bold mb-1">Welcome back</h1>
  <p class="text-secondary mb-4">Sign in to manage your moves, bilty and payments.</p>
  @if (request('verified'))<div class="alert alert-success" role="status"><i class="bi bi-patch-check me-1"></i>Email verified. You can sign in now.</div>@endif
  @if (request('reset'))<div class="alert alert-success" role="status">Password updated. Sign in with your new password.</div>@endif
  <div class="alert alert-danger d-none js-form-alert" role="alert"></div>
  <form data-ajax method="POST" action="{{ route('login.store') }}" novalidate>
    @csrf
    <div class="mb-3">
      <label for="email" class="form-label fw-medium">Email <span class="text-danger">*</span></label>
      <div class="input-group"><span class="input-group-text"><i class="bi bi-envelope"></i></span>
        <input id="email" name="email" type="email" class="form-control" placeholder="you@company.com" autocomplete="username" required autofocus>
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
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div class="form-check"><input class="form-check-input" type="checkbox" name="remember" value="1" id="remember"><label class="form-check-label" for="remember">Keep me signed in</label></div>
      <a href="{{ route('password.request') }}" class="small text-decoration-none">Forgot password?</a>
    </div>
    <button class="btn btn-primary btn-lg w-100" type="submit"><span class="btn-label">Sign in</span> <span class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span></button>
  </form>
  <p class="caption text-center mt-4 mb-0">New here? <a href="{{ route('trial') }}" class="text-decoration-none fw-semibold">Start your free trial</a></p>
@endsection