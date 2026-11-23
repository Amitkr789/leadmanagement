@extends('layouts.auth')

@section('title', 'Workspace inactive')

@section('body')
<div class="d-flex align-items-center justify-content-center min-vh-100 p-3">
  <div class="bg-white border rounded-3 p-4 p-md-5 text-center w-100" style="max-width:480px">
    <div class="mb-3 text-warning" style="font-size:2.5rem"><i class="bi bi-pause-circle"></i></div>
    <h1 class="h4 fw-bold mb-2">Your workspace is inactive</h1>
    <p class="text-muted-2 mb-4">
      Your company's trial has ended or the workspace has been suspended.
      Your data is safe and will be available again once the workspace is reactivated.
    </p>

    <div class="d-grid gap-2">
      @if (config('app.support_email'))
        <a href="mailto:{{ config('app.support_email') }}" class="btn btn-primary">Contact support</a>
      @endif
      <a href="{{ route('login') }}" class="btn btn-outline-secondary">Back to sign in</a>
    </div>
  </div>
</div>
@endsection