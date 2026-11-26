@extends('layouts.app')
@section('title', $lead->customer_name . ' | Lead')

@section('content')
@php
    $name     = $lead->customer_name;
    $initials = collect(preg_split('/\s+/', trim($name)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
    $digits   = preg_replace('/\D+/', '', (string) ($lead->whatsapp ?: $lead->phone));
    $waUrl    = $digits ? 'https://wa.me/' . (strlen($digits) === 10 ? '91' . $digits : $digits) : null;
    $deals    = $lead->linked_deals ?? [];
    $type     = (string) $lead->lead_type;
@endphp

<style>
  .lv-wrap { padding: 22px 28px 40px; max-width: 1280px; margin: 0 auto; }
  .lv-crumb { display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-muted); margin-bottom: 14px; }
  .lv-crumb a { color: var(--primary); text-decoration: none; font-weight: 600; }
  .lv-crumb a:hover { text-decoration: underline; }
  .lv-head { background: #fff; border: 1px solid var(--border); border-radius: 18px; box-shadow: var(--shadow-sm); padding: 22px 24px; }
  .lv-top { display: flex; gap: 18px; align-items: center; flex-wrap: wrap; }
  .lv-av { width: 68px; height: 68px; border-radius: 50%; background: var(--primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 800; flex-shrink: 0; }
  .lv-id { min-width: 0; flex: 1; }
  .lv-name { font-size: 26px; font-weight: 800; letter-spacing: -.02em; margin: 0; word-break: break-word; }
  .lv-sub { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; margin-top: 6px; font-size: 14px; color: var(--text-muted); }
  .lv-sub a { color: inherit; text-decoration: none; }
  .lv-sub a:hover { color: var(--primary); text-decoration: underline; }
  .lv-pill { display: inline-flex; align-items: center; gap: 6px; border-radius: 999px; padding: 4px 12px; font-size: 12.5px; font-weight: 700; background: var(--primary-50); color: var(--primary); }
  .lv-pill::before { content: ""; width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
  .lv-pill.hot { background: var(--danger-bg); color: var(--danger); }
  .lv-pill.warm { background: var(--warning-bg); color: #9A5B00; }
  .lv-pill.cold { background: var(--info-bg); color: var(--info); }
  .lv-pill.converted { background: var(--success-bg); color: var(--success); }
  .lv-actions { display: flex; gap: 8px; flex-wrap: wrap; }
  .lv-btn { display: inline-flex; align-items: center; gap: 7px; border: 1px solid var(--border); background: #fff; border-radius: 10px; padding: 8px 14px; font-size: 13px; font-weight: 700; color: var(--text); text-decoration: none; transition: all .12s; }
  .lv-btn:hover { border-color: var(--primary); color: var(--primary); }
  .lv-btn.off { opacity: .45; pointer-events: none; }
  .lv-btn.wa i { color: #25D366; }
  .lv-facts { display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 16px 24px; margin-top: 22px; padding-top: 20px; border-top: 1px solid #F0F1F6; }
  .lv-fact small { display: block; font-size: 11px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: var(--text-muted); margin-bottom: 3px; }
  .lv-fact span, .lv-fact a { font-size: 14px; font-weight: 600; color: var(--text); word-break: break-word; }
  .lv-fact a { color: var(--primary); text-decoration: none; }
  .lv-fact a:hover { text-decoration: underline; }
  .lv-fact .muted { color: #A7ABC2; font-weight: 500; }
  .lv-deals { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-top: 18px; }
  .lv-deals .lbl { font-size: 11px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: var(--text-muted); display: inline-flex; gap: 6px; align-items: center; }
  .lv-deal { display: inline-flex; align-items: center; gap: 7px; border-radius: 999px; padding: 5px 12px 5px 9px; font-size: 12.5px; font-weight: 600; text-decoration: none; background: var(--primary-50); color: var(--primary); border: 1px solid var(--primary-100); max-width: 100%; }
  .lv-deal:hover { background: #fff; box-shadow: 0 0 0 2px rgba(var(--primary-rgb), .14); }
  .lv-deal b { color: var(--text); }
  .lv-deal em { font-style: normal; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; background: #fff; border-radius: 999px; padding: 2px 7px; }
  .lv-deal.won { background: var(--success-bg); color: var(--success); border-color: transparent; }
  .lv-deal.lost { background: var(--danger-bg); color: var(--danger); border-color: transparent; }
  .lv-body { margin-top: 18px; background: #fff; border: 1.5px dashed #D5D9EA; border-radius: 18px; min-height: 280px; display: flex; align-items: center; justify-content: center; text-align: center; color: var(--text-muted); padding: 30px; }
  .lv-body i { font-size: 30px; color: #B9BED6; display: block; margin-bottom: 10px; }
  @media (max-width: 767.98px) { .lv-wrap { padding: 16px 14px 32px; } .lv-name { font-size: 21px; } .lv-av { width: 54px; height: 54px; font-size: 19px; } .lv-actions { width: 100%; } .lv-btn { flex: 1; justify-content: center; } }
</style>

<div class="lv-wrap">
  <nav class="lv-crumb" aria-label="Breadcrumb">
    <a href="{{ url('admin/leads') }}"><i class="bi bi-arrow-left me-1"></i>Leads</a>
    <span>/</span>
    <span>{{ $name }}</span>
  </nav>

  <header class="lv-head">
    <div class="lv-top">
      <div class="lv-av" aria-hidden="true">{{ $initials }}</div>
      <div class="lv-id">
        <h1 class="lv-name">{{ $name }}</h1>
        <div class="lv-sub">
          @if ($type !== '')
            <span class="lv-pill {{ strtolower($type) }}">{{ ucfirst($type) }}</span>
          @endif
          @if ($lead->company)
            <a href="{{ url('admin/companies') }}?id={{ $lead->company->id }}"><i class="bi bi-building me-1"></i>{{ $lead->company->name }}</a>
          @elseif ($lead->company_name)
            <span><i class="bi bi-building me-1"></i>{{ $lead->company_name }}</span>
          @endif
          @if ($lead->contact)
            <a href="{{ url('admin/contacts') }}?id={{ $lead->contact->id }}"><i class="bi bi-person-lines-fill me-1"></i>Contact record</a>
          @endif
        </div>
      </div>
      <div class="lv-actions">
        <a class="lv-btn {{ $lead->phone ? '' : 'off' }}" href="{{ $lead->phone ? 'tel:' . $lead->phone : '#' }}"><i class="bi bi-telephone"></i>Call</a>
        <a class="lv-btn wa {{ $waUrl ? '' : 'off' }}" href="{{ $waUrl ?: '#' }}" target="_blank" rel="noopener"><i class="bi bi-whatsapp"></i>WhatsApp</a>
        <a class="lv-btn {{ $lead->email ? '' : 'off' }}" href="{{ $lead->email ? 'mailto:' . $lead->email : '#' }}"><i class="bi bi-envelope"></i>Email</a>
        <a class="lv-btn" href="{{ url('admin/leads') }}?lead={{ $lead->id }}"><i class="bi bi-layout-sidebar-inset-reverse"></i>Quick view</a>
      </div>
    </div>

    <div class="lv-deals">
      <span class="lbl"><i class="bi bi-briefcase"></i>Deals</span>
      @forelse ($deals as $d)
        <a class="lv-deal {{ $d['stage'] === 'won' ? 'won' : ($d['stage'] === 'lost' ? 'lost' : '') }}" href="{{ url('admin/deals') }}?deal={{ $d['id'] }}" title="{{ $d['via'] === 'company' ? 'Linked through the same company' : 'Linked through this contact' }}">
          <i class="bi bi-briefcase"></i>{{ $d['name'] }}
          @if (($d['amount'] ?? 0) > 0)<b>₹{{ number_format($d['amount']) }}</b>@endif
          <em>{{ $d['stage_label'] }}</em>
        </a>
      @empty
        <span class="muted" style="color:#A7ABC2;font-size:13px">No deals linked yet</span>
      @endforelse
    </div>

    <div class="lv-facts">
      <div class="lv-fact"><small>Phone</small>@if ($lead->phone)<span>{{ $lead->phone }}</span>@else<span class="muted">—</span>@endif</div>
      <div class="lv-fact"><small>Email</small>@if ($lead->email)<span>{{ $lead->email }}</span>@else<span class="muted">—</span>@endif</div>
      <div class="lv-fact"><small>WhatsApp</small>@if ($lead->whatsapp)<span>{{ $lead->whatsapp }}</span>@else<span class="muted">—</span>@endif</div>
      <div class="lv-fact"><small>Product / interest</small>@if ($lead->product_name)<span>{{ $lead->product_name }}</span>@else<span class="muted">—</span>@endif</div>
      <div class="lv-fact"><small>Source</small><span>{{ $lead->source ?: '—' }}@if ($lead->social_platform) · {{ $lead->social_platform }}@endif</span></div>
      <div class="lv-fact"><small>Assigned to</small><span>{{ $lead->assignedAdmins->pluck('name')->implode(', ') ?: '—' }}</span></div>
      <div class="lv-fact"><small>Created by</small><span>{{ optional($lead->creator)->name ?: '—' }}</span></div>
      <div class="lv-fact"><small>Created</small><span>{{ optional($lead->created_at)->format('d M Y, h:i A') ?: '—' }}</span></div>
    </div>
  </header>

  <section class="lv-body" aria-label="Lead workspace">
    <div><i class="bi bi-layout-text-window-reverse"></i>This page is ready for its content.</div>
  </section>
</div>
@endsection