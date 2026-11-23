@extends('superadminconsole.layouts.app')
@section('title', 'Trials & Onboarding')
@section('active', 'trials')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <div><h1 class="page-title">Trials &amp; Onboarding</h1><div class="caption">Companies on a free trial, how far they are, and how set up they are.</div></div>
  <a href="{{ route('console.companies.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Company</a>
</div>
<div class="row g-3 mb-4">
  @foreach ([['On trial', $stats['trial'], 'bi-hourglass-split', 'info'], ['Ending in 3 days', $stats['soon'], 'bi-alarm', 'warning'], ['Expired trials', $stats['expired'], 'bi-x-octagon', 'danger'], ['Converted this month', $stats['converted'], 'bi-patch-check', 'success']] as [$l, $v, $i, $t])
    <div class="col-6 col-xl-3"><div class="mp-card kpi"><div class="kpi-icon tone-{{ $t }}"><i class="bi {{ $i }}"></i></div><div><div class="kpi-value">{{ $v }}</div><div class="kpi-label">{{ $l }}</div></div></div></div>
  @endforeach
</div>
<ul class="nav nav-pills mb-3 gap-1">
  @foreach (['all' => 'All trials', 'soon' => 'Ending soon', 'expired' => 'Expired'] as $k => $label)
    <li class="nav-item"><a class="nav-link {{ $filter === $k ? 'active' : 'bg-white border' }}" href="{{ route('console.trials.index', ['filter' => $k]) }}">{{ $label }}</a></li>
  @endforeach
</ul>
<div class="row g-3">
  @forelse ($trials as $c)
    @php
      $left = (int) now()->startOfDay()->diffInDays($c->trial_ends_at->copy()->startOfDay(), false);
      $pct = min(100, max(0, round(100 * (now()->timestamp - $c->created_at->timestamp) / max(1, $c->trial_ends_at->timestamp - $c->created_at->timestamp))));
      $steps = ['Admin account' => true, 'Branch added' => $c->branches_count > 0, 'Team invited' => $c->users_count > 1];
      $done = count(array_filter($steps)); $url = route('console.companies.show', $c);
    @endphp
    <div class="col-md-6 col-xl-4"><div class="mp-card h-100"><div class="mp-card-body">
      <div class="d-flex justify-content-between align-items-start mb-2">
        <div><div class="fw-semibold"><a href="#" class="js-company-open text-reset text-decoration-none" data-url="{{ $url }}">{{ $c->name }}</a></div><div class="caption">{{ $c->city }} · {{ $c->planDetail->name ?? ucfirst($c->plan) }} · started {{ $c->created_at->format('d M') }}</div></div>
        <span class="status tone-{{ $c->status === 'expired' ? 'danger' : ($left <= 3 ? 'warning' : 'info') }}">{{ $c->status === 'expired' ? 'Expired' : ($left >= 0 ? $left . 'd left' : 'Ended') }}</span>
      </div>
      <div class="progress mb-3" style="height:6px"><div class="progress-bar {{ $left <= 3 ? 'bg-warning' : '' }}" style="width:{{ $pct }}%"></div></div>
      <div class="caption mb-1">Onboarding {{ $done }}/{{ count($steps) }}</div>
      <div class="d-flex flex-wrap gap-1 mb-3">@foreach ($steps as $label => $ok)<span class="status tone-{{ $ok ? 'success' : 'warning' }}"><i class="bi bi-{{ $ok ? 'check2' : 'circle' }}"></i>{{ $label }}</span>@endforeach</div>
      <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-sm btn-primary js-company-open" data-url="{{ $url }}" data-tab="upgrade"><i class="bi bi-rocket-takeoff me-1"></i>Upgrade</button>
        <button type="button" class="btn btn-sm btn-outline-primary js-act" data-url="{{ route('console.companies.extend', $c) }}" data-payload='{"days": 7}'>+7 days</button>
        <button type="button" class="btn btn-sm btn-outline-primary js-act" data-url="{{ route('console.companies.extend', $c) }}" data-payload='{"days": 14}'>+14 days</button>
      </div>
    </div></div></div>
  @empty
    <div class="col-12"><div class="mp-card"><div class="empty-state"><i class="bi bi-hourglass-split"></i>No trials here.<div class="mt-2"><a href="{{ route('console.companies.create') }}" class="btn btn-primary btn-sm">+ Add Company</a></div></div></div></div>
  @endforelse
</div>
@if ($trials->hasPages())<div class="mt-3">{{ $trials->links('pagination::bootstrap-5') }}</div>@endif
@include('superadminconsole.pages.console.companies._modal')
@endsection
@push('scripts')<script src="{{ asset('assets/js/company-modal.js') }}"></script>@endpush
