@extends('superadminconsole.layouts.auth')
@section('title', 'Verify it is you')
@section('hero')
  <span class="status tone-warning mb-3">Step 2 of 2</span>
  <h2 class="hero-title">One more check before you enter.</h2>
@endsection
@section('form')
  <div class="kpi-icon tone-primary mb-3"><i class="bi bi-phone"></i></div>
  <h1 class="h4 fw-bold mb-1">Enter your 6-digit code</h1>
  <p class="text-secondary mb-4">Open your authenticator app and type the current code for MovePro.</p>
  <div class="alert alert-danger d-none js-form-alert" role="alert"></div>
  <form data-ajax method="POST" action="{{ route('console.2fa.verify') }}" novalidate>
    @csrf
    <input type="hidden" name="code" id="code">
    <div class="mb-3">
      <div class="otp" role="group" aria-label="6-digit code">
        @for ($i = 1; $i <= 6; $i++)
          <input class="form-control otp-box" inputmode="numeric" maxlength="1" autocomplete="{{ $i === 1 ? 'one-time-code' : 'off' }}" aria-label="Digit {{ $i }}">
        @endfor
      </div>
      <div class="invalid-feedback mt-2"></div>
    </div>
    <button class="btn btn-primary btn-lg w-100" type="submit"><span class="btn-label">Verify and sign in</span> <span class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span></button>
  </form>
  <p class="caption text-center mt-4 mb-0">This step expires in <span id="countdown" data-expire-url="{{ route('console.login') }}">5:00</span></p>
@endsection
