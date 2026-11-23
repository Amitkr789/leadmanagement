<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="robots" content="noindex, nofollow">
  <title>@yield('title') · {{ config('app.name', 'MovePro CRM') }}</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="{{ asset('assets/css/app.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/css/components.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/css/auth.css') }}" rel="stylesheet">
</head>
<body>
<div class="auth-shell">
  <section class="auth-hero">
    <div class="brand"><span class="brand-logo"><i class="bi bi-truck-front-fill"></i></span>
      <span><div class="brand-name">{{ config('app.name', 'MovePro CRM') }}</div><div class="brand-sub">Packers &amp; Movers Management System</div></span></div>
    <div class="hero-extra">@yield('hero')</div>
    <div class="hero-extra caption">&copy; {{ date('Y') }} {{ config('app.name', 'MovePro CRM') }}</div>
  </section>
  <section class="auth-panel"><div class="auth-card">@yield('form')</div></section>
</div>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('assets/js/auth.js') }}"></script>
@stack('scripts')
</body>
</html>
