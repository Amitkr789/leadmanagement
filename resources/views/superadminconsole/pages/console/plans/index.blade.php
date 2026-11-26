@extends('superadminconsole.layouts.app')
@section('title', 'Plans & Pricing')
@section('active', 'plans')
@section('content')
<style>
  /* Plan modal: sections, Paid/Free toggle, trial chips, module creator.
   Load after the MovePro forms stylesheet. Uses its --mp-* tokens with fallbacks. */

.pf-section { border: 1px solid var(--mp-border, #E6EAF2); border-radius: 16px; padding: 1.1rem 1.2rem; margin-bottom: 1rem; background: #fff; }
.pf-section-head { display: flex; align-items: center; gap: .7rem; margin-bottom: 1rem; }
.pf-section-head h3 { font-size: .95rem; font-weight: 700; margin: 0; }
.pf-ico { width: 32px; height: 32px; flex-shrink: 0; display: grid; place-items: center; border-radius: 10px; background: var(--mp-primary-soft, #EEF4FF); color: var(--mp-primary, #2563EB); }

/* Paid / Free segmented control */
.pf-segment { display: grid; grid-template-columns: 1fr 1fr; gap: .35rem; padding: .3rem; background: #F3F5F9; border-radius: 14px; }
.pf-segment label { position: relative; margin: 0; cursor: pointer; }
.pf-segment input { position: absolute; opacity: 0; pointer-events: none; }
.pf-segment span { display: flex; align-items: center; justify-content: center; gap: .5rem; padding: .65rem .8rem; border-radius: 11px; font-weight: 600; font-size: .9rem; color: var(--mp-muted, #6B7280); transition: background-color .15s, color .15s, box-shadow .15s; }
.pf-segment input:checked + span { background: #fff; color: var(--mp-primary, #2563EB); box-shadow: 0 1px 3px rgba(15, 23, 42, .14); }
.pf-segment input[value="1"]:checked + span { color: #15803D; }
.pf-segment input:focus-visible + span { box-shadow: var(--mp-ring, 0 0 0 4px rgba(37, 99, 235, .14)); }

/* Free-plan note and price summary */
.pf-free-note { display: flex; gap: .7rem; align-items: flex-start; padding: .9rem 1rem; border-radius: 12px; background: #ECFDF3; color: #166534; font-size: .9rem; }
.pf-free-note i { margin-top: .15rem; }
.pf-summary { display: flex; align-items: center; gap: .6rem; padding: .75rem 1rem; border-radius: 12px; background: var(--mp-bg, #F5F7FB); font-size: .88rem; color: #374151; }
.pf-summary i { color: var(--mp-primary, #2563EB); }

/* Trial chips */
.pf-chip { border: 1.5px solid var(--mp-input-border, #DDE3EE); background: #fff; color: #4B5563; border-radius: 999px; padding: .4rem .95rem; font-size: .85rem; font-weight: 600; transition: border-color .15s, background-color .15s, color .15s; }
.pf-chip:hover { border-color: var(--mp-primary, #2563EB); color: var(--mp-primary, #2563EB); }
.pf-chip.active { background: var(--mp-primary-soft, #EEF4FF); border-color: var(--mp-primary, #2563EB); color: var(--mp-primary, #2563EB); }
.pf-chip:focus-visible { outline: 0; box-shadow: var(--mp-ring, 0 0 0 4px rgba(37, 99, 235, .14)); }
.pf-trial-input { width: 130px; }
.pf-trial-input .form-control { height: 38px; border-radius: 999px 0 0 999px; }
.pf-trial-input .input-group-text { height: 38px; border-radius: 0 999px 999px 0; }

/* Modules */
.pf-mod-tools { display: flex; align-items: center; gap: .25rem; }
.pf-mod-new { margin-top: .9rem; padding: 1rem; border: 1.5px dashed var(--mp-input-border, #DDE3EE); border-radius: 14px; background: #FAFBFD; }
.pf-icons { display: flex; flex-wrap: wrap; gap: .4rem; }
.pf-icon-btn { width: 38px; height: 38px; display: grid; place-items: center; border: 1.5px solid var(--mp-input-border, #DDE3EE); background: #fff; border-radius: 10px; color: #4B5563; transition: border-color .15s, background-color .15s, color .15s; }
.pf-icon-btn:hover { border-color: var(--mp-primary, #2563EB); color: var(--mp-primary, #2563EB); }
.pf-icon-btn.active { background: var(--mp-primary-soft, #EEF4FF); border-color: var(--mp-primary, #2563EB); color: var(--mp-primary, #2563EB); }
.pf-icon-btn:focus-visible { outline: 0; box-shadow: var(--mp-ring, 0 0 0 4px rgba(37, 99, 235, .14)); }

/* Delete button on owner-created module chips */
.feat.is-custom { position: relative; }
.pf-chip-del { position: absolute; top: -7px; right: -7px; width: 20px; height: 20px; padding: 0; display: grid; place-items: center; border: 1px solid #fff; border-radius: 50%; background: #DC2626; color: #fff; font-size: .8rem; line-height: 1; opacity: 0; transition: opacity .15s; z-index: 2; }
.feat.is-custom:hover .pf-chip-del, .pf-chip-del:focus-visible { opacity: 1; }
@media (hover: none) { .pf-chip-del { opacity: 1; } }

@media (max-width: 767.98px) {
  .pf-section { padding: 1rem; border-radius: 14px; }
  .pf-trial-input { width: 100%; }
}
@media (prefers-reduced-motion: reduce) {
  .pf-segment span, .pf-chip, .pf-icon-btn, .pf-chip-del { transition: none; }
}
</style>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <div><h1 class="page-title">Plans &amp; Pricing</h1><div class="caption">Plans created here appear instantly in Add Company.</div></div>
  <div class="d-flex gap-2"><a href="{{ route('console.subscriptions.index') }}" class="btn btn-light border"><i class="bi bi-arrow-repeat me-1"></i>Subscriptions</a>
    <button type="button" class="btn btn-primary js-plan-open"><i class="bi bi-plus-lg me-1"></i>Create plan</button></div>
</div>
<div id="planGrid">@include('superadminconsole.pages.console.plans._grid')</div>
@include('superadminconsole.pages.console.plans._modal')
@endsection
