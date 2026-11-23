@extends('layouts.auth')

@section('title', 'Sign in')

@section('body')
<div class="login-shell">

  <aside class="login-aside">
    <div class="login-brand">
      <div class="mark">LF</div>
      <div class="name">LeadFlow CRM</div>
    </div>

    <div class="login-pitch">
      <h2>Turn more leads into closed deals.</h2>
      <p>One place to capture, qualify, and follow up with every lead — before your competitors do.</p>

      <ul class="login-feature-list">
        <li><span class="check"><i class="bi bi-check-lg"></i></span> Smart filters &amp; lead scoring</li>
        <li><span class="check"><i class="bi bi-check-lg"></i></span> Automatic follow-up reminders</li>
        <li><span class="check"><i class="bi bi-check-lg"></i></span> Full activity timeline per lead</li>
        <li><span class="check"><i class="bi bi-check-lg"></i></span> Your company's data, fully private</li>
      </ul>
    </div>

    <p class="login-quote">"LeadFlow cut our response time from a day to under an hour." — Ops Lead, Orbit Logistics</p>
  </aside>

  <main class="login-main">
    <div class="login-form-wrap">
      <h1>Welcome back</h1>
      <p class="sub">Sign in to your workspace to keep your pipeline moving.</p>

      @if (config('services.google.enabled'))
        <a href="{{ route('google.redirect') }}" class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center gap-2" id="googleBtn">
          <svg width="18" height="18" viewBox="0 0 48 48" aria-hidden="true">
            <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
            <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
            <path fill="#FBBC05" d="M10.53 28.59A14.5 14.5 0 0 1 9.5 24c0-1.59.28-3.14.76-4.59l-7.98-6.19A23.99 23.99 0 0 0 0 24c0 3.77.9 7.35 2.56 10.78l7.97-6.19z"/>
            <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
          </svg>
          <span>Continue with Google</span>
        </a>
        @if (config('tenancy.google_signup'))
          <p class="text-center fs-12 text-muted-2 mt-2 mb-0">New here? Continue with Google to start your free trial.</p>
        @endif

        <div class="d-flex align-items-center my-3">
          <hr class="flex-grow-1 m-0"><span class="px-3 fs-13 text-muted-2">or sign in with email</span><hr class="flex-grow-1 m-0">
        </div>
      @endif

      <form id="loginForm" data-action="{{ url('/admin/login') }}" novalidate>
        <div class="mb-3">
          <label class="form-label" for="email">Work email</label>
          <input type="email" class="form-control" id="email" placeholder="you@company.com" autocomplete="username" required>
          <div class="invalid-feedback d-block text-danger fs-12" id="err_email"></div>
        </div>

        <div class="mb-2">
          <label class="form-label" for="password">Password</label>
          <div class="pwd-wrap">
            <input type="password" class="form-control" id="password" placeholder="Enter your password" autocomplete="current-password" required>
            <button type="button" class="toggle-pwd" id="togglePwd" aria-label="Show password"><i class="bi bi-eye"></i></button>
          </div>
          <div class="invalid-feedback d-block text-danger fs-12" id="err_password"></div>
        </div>

        <div class="d-flex align-items-center justify-content-between mb-3 mt-2">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="rememberMe">
            <label class="form-check-label fs-13" for="rememberMe">Remember me</label>
          </div>
        </div>

        <button type="submit" class="btn btn-primary w-100" id="loginBtn">
          <span id="loginBtnText">Sign in</span>
        </button>
      </form>

      <p class="text-center fs-13 text-muted-2 mt-4 mb-0">Access is restricted to authorized users only.</p>
    </div>
  </main>
</div>
@endsection

@push('scripts')
<script>
$(function () {
  // Show / hide password
  $('#togglePwd').on('click', function () {
    const $pwd = $('#password');
    const show = $pwd.attr('type') === 'password';
    $pwd.attr('type', show ? 'text' : 'password');
    $(this).attr('aria-label', show ? 'Hide password' : 'Show password')
           .find('i').attr('class', show ? 'bi bi-eye-slash' : 'bi bi-eye');
  });

  // Prevent double clicks on the Google button
  $('#googleBtn').on('click', function () {
    $(this).addClass('disabled').attr('aria-disabled', 'true')
           .find('span').text('Redirecting to Google…');
  });

  function clearErrors() { $('.invalid-feedback').text(''); }

  // Submit login
  $('#loginForm').on('submit', function (e) {
    e.preventDefault();
    clearErrors();

    const $btn = $('#loginBtn');
    const $txt = $('#loginBtnText');
    $btn.prop('disabled', true);
    $txt.html('<span class="spinner-border spinner-border-sm me-2"></span>Signing in…');

    $.ajax({
      url: $('#loginForm').data('action'),
      method: 'POST',
      data: {
        email: $('#email').val(),
        password: $('#password').val(),
        remember: $('#rememberMe').is(':checked') ? 1 : 0,
      },
      success: function (res) {
        showToast('success', res.message || 'Login successful.');
        setTimeout(function () { window.location.href = res.redirect; }, 500);
      },
      error: function (xhr) {
        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
          const errors = xhr.responseJSON.errors;
          Object.keys(errors).forEach(function (f) { $('#err_' + f).text(errors[f][0]); });
        } else if (xhr.status === 429) {
          showToast('error', 'Too many attempts. Please wait a minute and try again.');
        } else if (xhr.status === 419) {
          showToast('error', 'Your session expired. Reloading the page…');
          setTimeout(function () { window.location.reload(); }, 1200);
        } else {
          showToast('error', (xhr.responseJSON && xhr.responseJSON.message) || 'Sign-in failed. Please try again.');
        }
      },
      complete: function () {
        $btn.prop('disabled', false);
        $txt.text('Sign in');
      }
    });
  });
});
</script>
@endpush