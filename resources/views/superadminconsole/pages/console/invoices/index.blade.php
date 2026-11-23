@extends('superadminconsole.layouts.app')
@section('title', 'Invoices')
@section('active', 'invoices')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <div><h1 class="page-title">Invoices</h1><div class="caption">Created automatically when a company is upgraded or changes plan.</div></div>
  <a href="{{ route('console.subscriptions.index') }}" class="btn btn-light border"><i class="bi bi-arrow-repeat me-1"></i>Subscriptions</a>
</div>
<div class="row g-3 mb-4">
  @foreach ([['Total billed', '₹' . number_format($stats['billed']), 'bi-receipt', 'primary'], ['Collected', '₹' . number_format($stats['paid']), 'bi-check2-circle', 'success'], ['Pending', '₹' . number_format($stats['pending']), 'bi-hourglass-split', 'warning'], ['Invoices this month', $stats['month'], 'bi-calendar-month', 'info']] as [$l, $v, $i, $t])
    <div class="col-6 col-xl-3"><div class="mp-card kpi"><div class="kpi-icon tone-{{ $t }}"><i class="bi {{ $i }}"></i></div><div><div class="kpi-value">{{ $v }}</div><div class="kpi-label">{{ $l }}</div></div></div></div>
  @endforeach
</div>
<div class="mp-card">
  <form id="invFilter" method="GET" class="mp-card-head flex-wrap">
    <div class="input-group" style="max-width:320px"><span class="input-group-text bg-white"><i class="bi bi-search"></i></span><input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search invoice no or company" aria-label="Search"></div>
    <select name="status" class="form-select" style="max-width:170px" aria-label="Status"><option value="">All status</option><option value="pending" @selected(request('status') === 'pending')>Pending</option><option value="paid" @selected(request('status') === 'paid')>Paid</option></select>
  </form>
  <div class="table-responsive"><table class="table mp-table">
    <thead><tr><th>Invoice</th><th>Company</th><th>Period</th><th>Total</th><th>Status</th><th class="text-end">Action</th></tr></thead>
    <tbody>
    @forelse ($invoices as $i)
      <tr>
        <td><div class="fw-semibold">{{ $i->invoice_no }}</div><div class="caption">{{ $i->created_at->format('d M Y') }}</div></td>
        <td>{{ $i->company_name }}<div class="caption">{{ $i->description }}</div></td>
        <td class="text-nowrap">{{ $i->period_start->format('d M') }} – {{ $i->period_end->format('d M Y') }}</td>
        <td class="fw-semibold">₹{{ number_format($i->total) }}<div class="caption fw-normal">incl. {{ $i->gst_percent }}% GST</div></td>
        <td><span class="status tone-{{ $i->status === 'paid' ? 'success' : 'warning' }}">{{ ucfirst($i->status) }}</span>@if ($i->payment_mode)<div class="caption">{{ $i->payment_mode }}</div>@endif</td>
        <td class="text-end text-nowrap">
          <a href="{{ route('console.invoices.show', $i) }}" target="_blank" class="btn btn-sm btn-light border"><i class="bi bi-printer me-1"></i>View</a>
          @if ($i->status === 'pending')
            <div class="dropdown d-inline-block"><button class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown">Mark paid</button>
              <ul class="dropdown-menu dropdown-menu-end">@foreach (\App\Models\Invoice::MODES as $mode)<li><button type="button" class="dropdown-item js-pay" data-url="{{ route('console.invoices.paid', $i) }}" data-mode="{{ $mode }}">{{ $mode }}</button></li>@endforeach</ul></div>
          @endif
        </td>
      </tr>
    @empty
      <tr><td colspan="6"><div class="empty-state"><i class="bi bi-receipt"></i>No invoices found.</div></td></tr>
    @endforelse
    </tbody>
  </table></div>
  @if ($invoices->hasPages())<div class="p-3 border-top">{{ $invoices->links('pagination::bootstrap-5') }}</div>@endif
</div>

@endsection
