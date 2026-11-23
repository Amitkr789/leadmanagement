@extends('superadminconsole.layouts.app')
@section('title', 'Branches & Users')
@section('active', 'directory')
@section('content')
<div class="mb-4"><h1 class="page-title">Branches &amp; Users</h1><div class="caption">Everyone and every branch across all companies.</div></div>
<ul class="nav nav-pills mb-3 gap-1">
  <li class="nav-item"><a class="nav-link {{ $tab === 'users' ? 'active' : 'bg-white border' }}" href="{{ route('console.directory.index', ['tab' => 'users']) }}"><i class="bi bi-people me-1"></i>Users <span class="badge text-bg-light">{{ $totals['users'] }}</span></a></li>
  <li class="nav-item"><a class="nav-link {{ $tab === 'branches' ? 'active' : 'bg-white border' }}" href="{{ route('console.directory.index', ['tab' => 'branches']) }}"><i class="bi bi-diagram-3 me-1"></i>Branches <span class="badge text-bg-light">{{ $totals['branches'] }}</span></a></li>
</ul>
<div class="mp-card">
  <form id="dirFilter" method="GET" class="mp-card-head flex-wrap">
    <input type="hidden" name="tab" value="{{ $tab }}">
    <div class="input-group" style="max-width:320px"><span class="input-group-text bg-white"><i class="bi bi-search"></i></span><input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="{{ $tab === 'users' ? 'Search name or email' : 'Search branch or city' }}" aria-label="Search"></div>
    <select name="company_id" class="form-select" style="max-width:240px" aria-label="Company"><option value="">All companies</option>@foreach ($companies as $co)<option value="{{ $co->id }}" @selected(request('company_id') == $co->id)>{{ $co->name }}</option>@endforeach</select>
  </form>
  <div class="table-responsive"><table class="table mp-table">
  @if ($tab === 'users')
    <thead><tr><th>User</th><th>Company</th><th>Role</th><th>Added</th><th class="text-end">Action</th></tr></thead><tbody>
    @forelse ($rows as $u)
      <tr><td><div class="d-flex align-items-center gap-2"><span class="avatar">{{ strtoupper(substr($u->name, 0, 2)) }}</span><div><div class="fw-semibold">{{ $u->name }}</div><div class="caption">{{ $u->email }}</div></div></div></td>
        <td><a href="#" class="js-company-open text-reset" data-url="{{ route('console.companies.show', $u->company_id) }}">{{ $u->company_name }}</a></td>
        <td><span class="status tone-{{ $u->role === 'admin' ? 'primary' : 'info' }}">{{ ucfirst($u->role) }}</span></td>
        <td class="text-nowrap">{{ $u->created_at->format('d M Y') }}</td>
        <td class="text-end"><button type="button" class="btn btn-sm btn-light border js-company-open" data-url="{{ route('console.companies.show', $u->company_id) }}" data-tab="edit"><i class="bi bi-key me-1"></i>Reset password</button></td></tr>
    @empty<tr><td colspan="5"><div class="empty-state"><i class="bi bi-people"></i>No users found.</div></td></tr>@endforelse
  @else
    <thead><tr><th>Branch</th><th>Company</th><th>City</th><th>Branch usage</th></tr></thead><tbody>
    @forelse ($rows as $b)
      @php $used = $branchCounts[$b->company_id] ?? 0; $max = $b->company->max_branches ?? 1; @endphp
      <tr><td class="fw-semibold">{{ $b->name }} @if ($b->is_head_office)<span class="status tone-primary ms-1">Head office</span>@endif</td>
        <td><a href="#" class="js-company-open text-reset" data-url="{{ route('console.companies.show', $b->company_id) }}">{{ $b->company->name ?? '—' }}</a></td>
        <td>{{ $b->city }}</td>
        <td style="min-width:150px">{{ $used }} of {{ $max }}<div class="progress mt-1" style="height:5px"><div class="progress-bar {{ $used >= $max ? 'bg-danger' : '' }}" style="width:{{ min(100, round(100 * $used / max(1, $max))) }}%"></div></div></td></tr>
    @empty<tr><td colspan="4"><div class="empty-state"><i class="bi bi-diagram-3"></i>No branches found.</div></td></tr>@endforelse
  @endif
  </tbody></table></div>
  @if ($rows->hasPages())<div class="p-3 border-top">{{ $rows->links('pagination::bootstrap-5') }}</div>@endif
</div>
@include('superadminconsole.pages.console.companies._modal')
@endsection
@push('scripts')<script src="{{ asset('assets/js/company-modal.js') }}"></script><script>
  $(function () { var t, $f = $('#dirFilter'); $f.on('change', 'select', function () { $f.trigger('submit'); }); $f.on('input', 'input[name=q]', function () { clearTimeout(t); t = setTimeout(function () { $f.trigger('submit'); }, 450); }); });
</script>@endpush
