@php
  $end = $c->trial_ends_at ?? $c->subscription_ends_at;
  $days = $end ? (int) now()->startOfDay()->diffInDays($end->copy()->startOfDay(), false) : null;
  $tone = ['trial' => 'info', 'active' => 'success', 'suspended' => 'warning', 'expired' => 'danger'][$c->status] ?? 'info';
  $isTrial = $c->status === 'trial';
  $pct = ($isTrial && $end) ? min(100, max(0, round(100 * (now()->timestamp - $c->created_at->timestamp) / max(1, $end->timestamp - $c->created_at->timestamp)))) : 0;
  $usersPct = $c->max_users ? min(100, round(100 * $c->users_count / $c->max_users)) : 0;
  $upLabel = $c->status === 'active' ? 'Change plan' : ($isTrial ? 'Upgrade & activate' : 'Reactivate with plan');
@endphp
<div class="modal-header">
  <div class="d-flex align-items-center gap-3"><span class="avatar">{{ strtoupper(substr($c->name, 0, 2)) }}</span>
    <div><h2 class="h5 mb-0">{{ $c->name }} <span class="status tone-{{ $tone }} ms-1">{{ ucfirst($c->status) }}</span></h2><div class="caption">{{ $c->city }}, {{ $c->state }} · Added {{ $c->created_at->format('d M Y') }}</div></div></div>
  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
  <ul class="nav nav-tabs cm-tabs mb-3 flex-nowrap overflow-auto" role="tablist">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#cmOverview" type="button" role="tab"><i class="bi bi-eye me-1"></i>Overview</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#cmEdit" type="button" role="tab"><i class="bi bi-pencil me-1"></i>Edit</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#cmUpgrade" type="button" role="tab"><i class="bi bi-rocket-takeoff me-1"></i>{{ $c->status === 'active' ? 'Plan' : 'Upgrade' }}</button></li>
    <li class="nav-item"><button class="nav-link text-nowrap" data-bs-toggle="tab" data-bs-target="#cmInvoices" type="button" role="tab"><i class="bi bi-receipt me-1"></i>Invoices</button></li>
    <li class="nav-item"><button class="nav-link text-nowrap" data-bs-toggle="tab" data-bs-target="#cmActivity" type="button" role="tab"><i class="bi bi-clock-history me-1"></i>Activity</button></li>
  </ul>
  <div class="tab-content">

    {{-- OVERVIEW --}}
    <div class="tab-pane fade show active" id="cmOverview" role="tabpanel">
      @if ($isTrial)
        <div class="alert alert-info d-flex flex-wrap justify-content-between align-items-center gap-3">
          <div style="min-width:220px"><strong>Free trial</strong> · {{ $days >= 0 ? $days . ' days left' : 'trial has ended' }} <span class="caption">(ends {{ $end->format('d M Y') }})</span>
            <div class="progress mt-2" style="height:6px"><div class="progress-bar" style="width:{{ $pct }}%"></div></div></div>
          <button type="button" class="btn btn-primary btn-sm js-goto-tab" data-tab="#cmUpgrade"><i class="bi bi-rocket-takeoff me-1"></i>Upgrade to paid</button>
        </div>
      @elseif ($c->status !== 'active')
        <div class="alert alert-warning mb-3">This company is <strong>{{ $c->status }}</strong>. Pick a plan in the Upgrade tab to reactivate it.</div>
      @endif
      <div class="row g-3">
        <div class="col-6 col-md-4"><div class="caption">Plan</div><div class="fw-semibold">{{ $c->planDetail->name ?? ucfirst($c->plan) }}</div></div>
        <div class="col-6 col-md-4"><div class="caption">Billing</div><div class="fw-semibold">{{ $isTrial ? 'Free trial' : ucfirst($c->billing_cycle ?? '—') }}</div></div>
        <div class="col-6 col-md-4"><div class="caption">{{ $isTrial ? 'Trial ends' : 'Renews / ends' }}</div><div class="fw-semibold">{{ $end?->format('d M Y') ?? '—' }}</div></div>
        <div class="col-6 col-md-4"><div class="caption">Users</div><div class="fw-semibold">{{ $c->users_count }} / {{ $c->max_users }}</div><div class="progress mt-1" style="height:5px"><div class="progress-bar {{ $usersPct > 90 ? 'bg-danger' : '' }}" style="width:{{ $usersPct }}%"></div></div></div>
        <div class="col-6 col-md-4"><div class="caption">Branch limit</div><div class="fw-semibold">{{ $c->max_branches }}</div></div>
        <div class="col-6 col-md-4"><div class="caption">GST</div><div class="fw-semibold">{{ $c->gst_number ?: '—' }}</div></div>
        <div class="col-6 col-md-4"><div class="caption">Contact</div><div class="fw-semibold">{{ $c->contact_name }}</div></div>
        <div class="col-6 col-md-4"><div class="caption">Mobile</div><div class="fw-semibold">+91 {{ $c->mobile }}</div></div>
        <div class="col-12 col-md-4"><div class="caption">Email</div><div class="fw-semibold text-break">{{ $c->email }}</div></div>
        <div class="col-12"><div class="caption">Address</div><div class="fw-semibold">{{ $c->address ?: '—' }}</div></div>
        @if ($c->planDetail && $c->planDetail->features)
          <div class="col-12"><div class="caption mb-1">Modules included</div><div class="d-flex flex-wrap gap-1">@foreach ($c->planDetail->features as $f)<span class="status tone-primary">{{ \App\Models\Plan::FEATURES[$f]['label'] ?? $f }}</span>@endforeach</div></div>
        @endif
      </div>
      <div class="d-flex flex-wrap align-items-center gap-2 mt-4 pt-3 border-top">
        @if ($c->trial_ends_at && !$c->subscription_ends_at)
          <span class="caption">Extend trial:</span>
          @foreach ([7, 14, 30] as $dd)<button type="button" class="btn btn-sm btn-outline-primary js-act" data-url="{{ route('console.companies.extend', $c) }}" data-payload='{"days": {{ $dd }} }'>+{{ $dd }} days</button>@endforeach
        @endif
        @if ($c->status === 'suspended')
          <button type="button" class="btn btn-sm btn-success ms-auto js-act" data-url="{{ route('console.companies.status', $c) }}" data-payload='{"action": "reactivate"}'><i class="bi bi-play-circle me-1"></i>Reactivate</button>
        @else
          <button type="button" class="btn btn-sm btn-outline-warning ms-auto js-act" data-url="{{ route('console.companies.status', $c) }}" data-payload='{"action": "suspend"}'><i class="bi bi-pause-circle me-1"></i>Suspend</button>
        @endif
      </div>
    </div>

    {{-- EDIT --}}
    <div class="tab-pane fade" id="cmEdit" role="tabpanel">
      <form data-cm="edit" action="{{ route('console.companies.update', $c) }}" method="POST" novalidate>
        @csrf @method('PUT')
        <div class="row">
          <div class="col-12 mb-3"><label class="form-label fw-medium" for="cm_name">Company name <span class="text-danger">*</span></label><input id="cm_name" name="name" class="form-control" value="{{ $c->name }}" required><div class="invalid-feedback"></div></div>
          <div class="col-md-6 mb-3"><label class="form-label fw-medium" for="cm_contact">Contact person <span class="text-danger">*</span></label><input id="cm_contact" name="contact_name" class="form-control" value="{{ $c->contact_name }}" required><div class="invalid-feedback"></div></div>
          <div class="col-md-6 mb-3"><label class="form-label fw-medium" for="cm_mobile">Mobile <span class="text-danger">*</span></label><div class="input-group"><span class="input-group-text">+91</span><input id="cm_mobile" name="mobile" class="form-control cm-mobile" inputmode="numeric" maxlength="10" value="{{ $c->mobile }}" required><div class="invalid-feedback"></div></div></div>
          <div class="col-md-6 mb-3"><label class="form-label fw-medium" for="cm_email">Company email <span class="text-danger">*</span></label><input id="cm_email" name="email" type="email" class="form-control" value="{{ $c->email }}" required><div class="invalid-feedback"></div></div>
          <div class="col-md-6 mb-3"><label class="form-label fw-medium" for="cm_gst">GST number</label><input id="cm_gst" name="gst_number" class="form-control cm-gst" maxlength="15" value="{{ $c->gst_number }}"><div class="invalid-feedback"></div></div>
          <div class="col-md-6 mb-3"><label class="form-label fw-medium" for="cm_city">City <span class="text-danger">*</span></label><input id="cm_city" name="city" class="form-control" value="{{ $c->city }}" required><div class="invalid-feedback"></div></div>
          <div class="col-md-6 mb-3"><label class="form-label fw-medium" for="cm_state">State <span class="text-danger">*</span></label><input id="cm_state" name="state" class="form-control" list="cmStates" value="{{ $c->state }}" required><datalist id="cmStates">@foreach ($states as $st)<option value="{{ $st }}">@endforeach</datalist><div class="invalid-feedback"></div></div>
          <div class="col-12 mb-3"><label class="form-label fw-medium" for="cm_addr">Address</label><textarea id="cm_addr" name="address" rows="2" class="form-control">{{ $c->address }}</textarea></div>
        </div>
        <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1"></i>Save changes <span class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span></button>
      </form>

      @if ($users->isNotEmpty())
      <form data-cm="password" action="{{ route('console.companies.password', $c) }}" method="POST" class="border rounded-3 p-3 mt-4" novalidate>
        @csrf
        <h3 class="h6 mb-1"><i class="bi bi-key me-1"></i>Change login password</h3>
        <p class="caption mb-3">Choose a user and set a new password. Their active sessions are signed out.</p>
        <div class="mb-3"><label class="form-label fw-medium" for="cm_user">User</label><select id="cm_user" name="user_id" class="form-select">@foreach ($users as $u)<option value="{{ $u->id }}">{{ $u->name }} · {{ $u->email }} ({{ $u->role }})</option>@endforeach</select></div>
        <div class="mb-3"><label class="form-label fw-medium" for="cm_pw">New password</label>
          <div class="input-group"><input id="cm_pw" name="password" class="form-control font-monospace" autocomplete="new-password" required>
            <button type="button" class="btn btn-outline-secondary js-cm-gen" aria-label="Generate password"><i class="bi bi-arrow-repeat"></i></button>
            <button type="button" class="btn btn-outline-secondary js-cm-copy" aria-label="Copy password"><i class="bi bi-clipboard"></i></button><div class="invalid-feedback"></div></div></div>
        <div class="d-flex flex-wrap gap-2"><button type="submit" class="btn btn-outline-primary"><i class="bi bi-shield-lock me-1"></i>Change password <span class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span></button>
          <button type="button" class="btn btn-light border js-cm-link" data-url="{{ route('console.companies.reset-link', $c) }}"><i class="bi bi-envelope me-1"></i>Email reset link</button></div>
      </form>
      @endif

      <form data-cm="delete" action="{{ route('console.companies.destroy', $c) }}" method="POST" class="danger-zone mt-4" novalidate>
        @csrf @method('DELETE')
        <h3 class="h6 text-danger mb-1"><i class="bi bi-exclamation-triangle me-1"></i>Delete company</h3>
        <p class="caption mb-2">Permanently removes this company and its {{ $c->users_count }} {{ Str::plural('user', $c->users_count) }}. This cannot be undone.</p>
        <div class="mb-3"><input id="cmConfirm" name="confirm_name" class="form-control" data-name="{{ $c->name }}" placeholder="Type &quot;{{ $c->name }}&quot; to confirm" autocomplete="off" aria-label="Type the company name to confirm"><div class="invalid-feedback"></div></div>
        <button id="cmDeleteBtn" type="submit" class="btn btn-danger" disabled><i class="bi bi-trash me-1"></i>Delete permanently <span class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span></button>
      </form>
    </div>

    {{-- UPGRADE / CHANGE PLAN --}}
    <div class="tab-pane fade" id="cmUpgrade" role="tabpanel">
      <form id="cmUpgradeForm" data-cm="upgrade" action="{{ route('console.companies.upgrade', $c) }}" method="POST" novalidate
            data-current="{{ $c->plan }}" data-cycle="{{ $c->billing_cycle }}" data-active="{{ $c->status === 'active' ? 1 : 0 }}" data-end="{{ $c->subscription_ends_at?->format('d M Y') }}">
        @csrf
        <div class="alert alert-danger d-none cm-error" role="alert"></div>
        <label class="form-label fw-medium">{{ $isTrial ? 'Choose a plan to upgrade to' : 'Plan' }}</label>
        <div class="row g-3 mb-3">
          @foreach ($plans as $p)
            <div class="col-md-4"><label class="choice-card w-100 h-100"><input type="radio" name="plan" value="{{ $p->slug }}" data-price="{{ $p->monthly_price }}" data-yearly="{{ $p->yearly_price }}" @checked($p->slug === $c->plan)>
              <span class="box"><span class="d-flex justify-content-between align-items-center"><strong>{{ $p->name }}</strong>@if ($p->slug === $c->plan)<span class="status tone-primary">Current</span>@elseif ($p->is_popular)<span class="status tone-success">Popular</span>@endif</span>
              <span class="price">₹{{ number_format($p->monthly_price) }}<small>/month</small></span>
              <ul class="plan-feats"><li>{{ $p->max_users }} users</li><li>{{ $p->max_branches }} {{ Str::plural('branch', $p->max_branches) }}</li></ul></span></label></div>
          @endforeach
        </div>
        <label class="form-label fw-medium">Billing cycle</label>
        <div class="row g-3 mb-3">
          <div class="col-6"><label class="choice-card w-100"><input type="radio" name="billing_cycle" value="monthly" @checked(($c->billing_cycle ?? 'monthly') === 'monthly')><span class="box">Monthly</span></label></div>
          <div class="col-6"><label class="choice-card w-100"><input type="radio" name="billing_cycle" value="yearly" @checked($c->billing_cycle === 'yearly')><span class="box">Yearly <span class="status tone-success ms-1">2 months free</span></span></label></div>
        </div>
        <div class="bg-body-tertiary rounded-3 p-3 mb-3"><div class="caption">You will charge</div><div class="kpi-value" id="cmUpAmount">—</div><div class="caption" id="cmUpNote"></div></div>
        <button type="submit" class="btn btn-primary"><i class="bi bi-rocket-takeoff me-1"></i>{{ $upLabel }} <span class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span></button>
      </form>
    </div>

    {{-- INVOICES --}}
    <div class="tab-pane fade" id="cmInvoices" role="tabpanel">
      @forelse ($invoices as $i)
        <a href="{{ route('console.invoices.show', $i) }}" target="_blank" class="d-flex justify-content-between align-items-center gap-2 py-2 border-bottom text-reset text-decoration-none">
          <div><div class="fw-semibold">{{ $i->invoice_no }}</div><div class="caption">{{ $i->description }} · {{ $i->created_at->format('d M Y') }}</div></div>
          <div class="text-end"><div class="fw-semibold">₹{{ number_format($i->total) }}</div><span class="status tone-{{ $i->status === 'paid' ? 'success' : 'warning' }}">{{ ucfirst($i->status) }}</span></div>
        </a>
      @empty
        <div class="empty-state"><i class="bi bi-receipt"></i>No invoices yet. One is created automatically when the company is upgraded.</div>
      @endforelse
    </div>

    {{-- ACTIVITY --}}
    <div class="tab-pane fade" id="cmActivity" role="tabpanel">
      @forelse ($activities as $a)
        <div class="tl-item"><span class="tl-dot tl-{{ $a->type }}"></span><div><div class="fw-medium">{{ $a->message }}</div><div class="caption">{{ $a->created_at->format('d M Y, h:i A') }} · {{ $a->admin->name ?? 'System' }}</div></div></div>
      @empty
        <div class="empty-state"><i class="bi bi-clock-history"></i>No activity recorded yet.</div>
      @endforelse
    </div>
  </div>
</div>
