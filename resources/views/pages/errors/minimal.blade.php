@extends('layouts.auth')

@section('title', $heading)

@section('body')
<div class="d-flex align-items-center justify-content-center min-vh-100 p-3">
  <div class="bg-white border rounded-3 p-4 p-md-5 text-center w-100" style="max-width:480px">
    <div class="display-4 fw-bold text-muted-2 mb-2">{{ $code }}</div>
    <h1 class="h5 fw-bold mb-2">{{ $heading }}</h1>
    <p class="text-muted-2 mb-4">{{ $text }}</p>
    <div class="d-grid gap-2">
      @auth('admin')
        <a href="{{ route('auth.home.main') }}" class="btn btn-primary">Go to dashboard</a>
      @else
        <a href="{{ route('login') }}" class="btn btn-primary">Back to sign in</a>
      @endauth
    </div>
  </div>
</div>
@endsection