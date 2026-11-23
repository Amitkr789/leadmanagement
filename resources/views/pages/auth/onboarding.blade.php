@extends('layouts.auth')

@section('title', 'Set up your workspace')

@section('body')
<div class="d-flex align-items-center justify-content-center min-vh-100 p-3">
  <div class="bg-white border rounded-3 p-4 p-md-5 w-100" style="max-width:480px">
    <h1 class="h4 fw-bold mb-1">Name your workspace</h1>
    <p class="text-muted-2 mb-4">
      This is your company's private space in LeadFlow CRM.
      @if ($tenant?->trial_ends_at)
        Your free trial runs until {{ $tenant->trial_ends_at->format('d M Y') }}.
      @endif
    </p>

    <form method="POST" action="{{ route('workspace.onboarding.save') }}" novalidate>
      @csrf
      <div class="mb-3">
        <label class="form-label" for="name">Company name</label>
        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
               value="{{ old('name', $tenant?->name) }}" maxlength="120" required autofocus>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <div class="d-grid gap-2">
        <button type="submit" class="btn btn-primary">Save and continue</button>
        <a href="{{ route('auth.home.main') }}" class="btn btn-outline-secondary">Skip for now</a>
      </div>
    </form>

    <p class="fs-13 text-muted-2 mt-4 mb-0">
      You are the owner of this workspace. You can add your team from the Access Panel.
    </p>
  </div>
</div>
@endsection