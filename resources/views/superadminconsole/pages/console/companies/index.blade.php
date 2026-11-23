@extends('superadminconsole.layouts.app')
@section('title', 'Companies')
@section('active', 'companies')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <div><h1 class="page-title">Companies</h1><div class="caption">Every registered company. Click a row for details, plan, password and activity.</div></div>
  <a href="{{ route('console.companies.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Company</a>
</div>
<div class="row g-3 mb-4">
  @foreach ([['Total companies', $stats['total'], 'bi-buildings', 'primary'], ['Active', $stats['active'], 'bi-patch-check', 'success'], ['On trial', $stats['trial'], 'bi-hourglass-split', 'info'], ['Suspended', $stats['suspended'], 'bi-pause-circle', 'warning']] as [$l, $v, $i, $t])
    <div class="col-6 col-xl-3"><div class="mp-card kpi"><div class="kpi-icon tone-{{ $t }}"><i class="bi {{ $i }}"></i></div><div><div class="kpi-value">{{ $v }}</div><div class="kpi-label">{{ $l }}</div></div></div></div>
  @endforeach
</div>
<div class="mp-card">
  <form id="coFilter" method="GET" class="mp-card-head flex-wrap">
    <div class="input-group" style="max-width:320px"><span class="input-group-text bg-white"><i class="bi bi-search"></i></span><input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search name, owner, email, city" aria-label="Search"></div>
    <div class="d-flex gap-2">
      <select name="plan" class="form-select" aria-label="Plan"><option value="">All plans</option>@foreach ($plans as $p)<option value="{{ $p->slug }}" @selected(request('plan') === $p->slug)>{{ $p->name }}</option>@endforeach</select>
      <select name="status" class="form-select" aria-label="Status"><option value="">All status</option>@foreach (['trial', 'active', 'suspended', 'expired'] as $s)<option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>@endforeach</select>
    </div>
  </form>
  <div class="table-responsive"><table class="table mp-table">
    <thead><tr><th>Company</th><th>Plan</th><th>Status</th><th>Trial / Billing</th><th>Registered</th><th>Users</th><th class="text-end">Action</th></tr></thead>
    <tbody>
    @forelse ($companies as $c)
      @php
        $isTrial = $c->trial_ends_at && !$c->subscription_ends_at;
        $left = $isTrial ? (int) now()->startOfDay()->diffInDays($c->trial_ends_at->copy()->startOfDay(), false) : null;
        $tone = ['trial' => 'info', 'active' => 'success', 'suspended' => 'warning', 'expired' => 'danger'][$c->status] ?? 'info';
        $url = route('console.companies.show', $c);
      @endphp
      <tr class="js-company-row" role="button" tabindex="0" data-url="{{ $url }}">
        <td><div class="d-flex align-items-center gap-2"><span class="avatar">{{ strtoupper(substr($c->name, 0, 2)) }}</span>
          <div><div class="fw-semibold"><a href="#" class="js-company-open text-reset text-decoration-none" data-url="{{ $url }}">{{ $c->name }}</a></div><div class="caption">{{ $c->contact_name }} · {{ $c->city }}</div></div></div></td>
        <td>{{ $c->planDetail->name ?? ucfirst($c->plan) }}</td>
        <td><span class="status tone-{{ $tone }}">{{ ucfirst($c->status) }}</span></td>
        <td class="text-nowrap">
          @if ($isTrial)<span class="{{ $left <= 3 ? 'text-danger fw-semibold' : '' }}">Trial · {{ $left >= 0 ? $left . ' days left' : 'ended' }}</span>
          @else Paid · {{ ucfirst($c->billing_cycle ?? '—') }}<div class="caption">Renews {{ $c->subscription_ends_at?->format('d M Y') ?? '—' }}</div>@endif
        </td>
        <td class="text-nowrap">{{ $c->created_at->format('d M Y') }}<div class="caption">{{ $c->created_at->diffForHumans() }}</div></td>
        <td>{{ $c->users_count }} <span class="caption">/ {{ $c->max_users }}</span></td>
        <td class="text-end">
          <div class="dropdown"><button type="button" class="btn btn-sm btn-light border" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-label="Actions for {{ $c->name }}"><i class="bi bi-three-dots-vertical"></i></button>
            <ul class="dropdown-menu dropdown-menu-end shadow">
              <li><button type="button" class="dropdown-item js-company-open" data-url="{{ $url }}" data-tab="overview"><i class="bi bi-eye me-2"></i>View details</button></li>
              <li><button type="button" class="dropdown-item js-company-open" data-url="{{ $url }}" data-tab="edit"><i class="bi bi-pencil me-2"></i>Edit company</button></li>
              <li><button type="button" class="dropdown-item js-company-open" data-url="{{ $url }}" data-tab="edit"><i class="bi bi-key me-2"></i>Change password</button></li>
              <li><button type="button" class="dropdown-item js-company-open" data-url="{{ $url }}" data-tab="upgrade"><i class="bi bi-rocket-takeoff me-2"></i>Upgrade / change plan</button></li>
              <li><hr class="dropdown-divider"></li>
              @if ($c->status === 'suspended')
                <li><button type="button" class="dropdown-item text-success js-act" data-url="{{ route('console.companies.status', $c) }}" data-payload='{"action": "reactivate"}'><i class="bi bi-play-circle me-2"></i>Reactivate</button></li>
              @else
                <li><button type="button" class="dropdown-item text-warning js-act" data-url="{{ route('console.companies.status', $c) }}" data-payload='{"action": "suspend"}'><i class="bi bi-pause-circle me-2"></i>Suspend</button></li>
              @endif
            </ul></div>
        </td>
      </tr>
    @empty
      <tr><td colspan="7"><div class="empty-state"><i class="bi bi-buildings"></i>No companies found.<div class="mt-2"><a href="{{ route('console.companies.create') }}" class="btn btn-primary btn-sm">+ Add Company</a></div></div></td></tr>
    @endforelse
    </tbody>
  </table></div>
  @if ($companies->hasPages())<div class="p-3 border-top">{{ $companies->links('pagination::bootstrap-5') }}</div>@endif
</div>
@include('superadminconsole.pages.console.companies._modal')
<script src="{{ asset('assets/js/company-modal.js') }}"></script><script>
  $(function () { var t, $f = $('#coFilter'); $f.on('change', 'select', function () { $f.trigger('submit'); }); $f.on('input', 'input[name=q]', function () { clearTimeout(t); t = setTimeout(function () { $f.trigger('submit'); }, 450); }); });
</script>

@endsection
