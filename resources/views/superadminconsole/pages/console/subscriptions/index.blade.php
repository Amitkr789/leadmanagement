@extends('superadminconsole.layouts.app')
@section('title', 'Subscriptions')
@section('active', 'subscriptions')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <div><h1 class="page-title">Subscriptions</h1><div class="caption">Every company with its plan, status and renewal date.</div></div>
  <div class="d-flex gap-2"><a href="{{ route('console.plans.index') }}" class="btn btn-light border"><i class="bi bi-tags me-1"></i>Manage plans</a>
    <a href="{{ route('console.companies.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Company</a></div>
</div>

<div class="row g-3 mb-4">
  @foreach ([['Active', $stats['active'], 'bi-patch-check', 'success'], ['On trial', $stats['trial'], 'bi-hourglass-split', 'info'], ['Ending in 7 days', $stats['expiring'], 'bi-calendar-event', 'warning'], ['MRR', '₹' . number_format($stats['mrr']), 'bi-currency-rupee', 'primary']] as [$l, $v, $i, $t])
    <div class="col-6 col-xl-3"><div class="mp-card kpi"><div class="kpi-icon tone-{{ $t }}"><i class="bi {{ $i }}"></i></div><div><div class="kpi-value">{{ $v }}</div><div class="kpi-label">{{ $l }}</div></div></div></div>
  @endforeach
</div>

<div class="mp-card">
  <form id="subFilter" method="GET" class="mp-card-head flex-wrap">
    <div class="input-group" style="max-width:320px"><span class="input-group-text bg-white"><i class="bi bi-search"></i></span><input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search company, email, city" aria-label="Search"></div>
    <div class="d-flex gap-2">
      <select name="plan" class="form-select" aria-label="Plan"><option value="">All plans</option>@foreach ($plans as $p)<option value="{{ $p->slug }}" @selected(request('plan') === $p->slug)>{{ $p->name }}</option>@endforeach</select>
      <select name="status" class="form-select" aria-label="Status"><option value="">All status</option>@foreach (['trial', 'active', 'suspended', 'expired'] as $s)<option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>@endforeach</select>
    </div>
  </form>
  <div class="table-responsive"><table class="table mp-table">
    <thead><tr><th>Company</th><th>Plan</th><th>Billing</th><th>Status</th><th>Ends / Renews</th><th>Amount</th><th></th></tr></thead>
    <tbody>
    @forelse ($companies as $c)
      @php
        $end = $c->trial_ends_at ?? $c->subscription_ends_at;
        $days = $end ? (int) now()->startOfDay()->diffInDays($end->copy()->startOfDay(), false) : null;
        $tone = ['trial' => 'info', 'active' => 'success', 'suspended' => 'warning', 'expired' => 'danger'][$c->status] ?? 'info';
        $amount = $c->billing_cycle === 'yearly' ? ($c->planDetail->yearly_price ?? 0) : ($c->planDetail->monthly_price ?? 0);
      @endphp
      <tr class="js-company-row" role="button" tabindex="0" data-url="{{ route('console.companies.show', $c) }}">
        <td><div class="fw-semibold"><a href="#" class="js-company-open text-reset text-decoration-none" data-url="{{ route('console.companies.show', $c) }}">{{ $c->name }}</a></div><div class="caption">{{ $c->contact_name }} · {{ $c->city }}</div></td>
        <td>{{ $c->planDetail->name ?? ucfirst($c->plan) }}</td>
        <td>{{ $c->status === 'trial' ? 'Free trial' : ucfirst($c->billing_cycle ?? '—') }}</td>
        <td><span class="status tone-{{ $tone }}">{{ ucfirst($c->status) }}</span></td>
        <td>{{ $end?->format('d M Y') ?? '—' }}<div class="caption">@if ($days !== null){{ $days >= 0 ? $days . ' days left' : 'Expired' }}@endif</div></td>
        <td class="fw-semibold">{{ $c->status === 'trial' ? '—' : '₹' . number_format($amount) }}</td>
        <td class="text-end text-nowrap"><button type="button" class="btn btn-sm {{ $c->status === 'active' ? 'btn-light border' : 'btn-primary' }} js-company-open" data-url="{{ route('console.companies.show', $c) }}" data-tab="upgrade"><i class="bi bi-rocket-takeoff me-1"></i>{{ $c->status === 'active' ? 'Change plan' : 'Upgrade' }}</button></td>
      </tr>
    @empty
      <tr><td colspan="7"><div class="empty-state"><i class="bi bi-buildings"></i>No companies found.<div class="mt-2"><a href="{{ route('console.companies.create') }}" class="btn btn-primary btn-sm">+ Add Company</a></div></div></td></tr>
    @endforelse
    </tbody>
  </table></div>
  @if ($companies->hasPages())<div class="p-3 border-top">{{ $companies->links('pagination::bootstrap-5') }}</div>@endif
</div>
@include('superadminconsole.pages.console.companies._modal')
@endsection
