@extends('superadminconsole.layouts.app')
@section('title', 'Modules')
@section('active', 'modules')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <div>
    <h1 class="page-title">Modules</h1>
    <div class="caption">Features that can be included in a plan. Changes show up instantly in the plan form.</div>
  </div>
  <div class="d-flex gap-2">
    <a href="{{ route('console.plans.index') }}" class="btn btn-light border"><i class="bi bi-tags me-1"></i>Plans</a>
    <button type="button" class="btn btn-primary js-mod-open"><i class="bi bi-plus-lg me-1"></i>New module</button>
  </div>
</div>

<div class="mp-card mb-3">
  <div class="mp-card-body py-3 d-flex flex-wrap align-items-center gap-3">
    <div class="input-group" style="max-width: 320px;">
      <span class="input-group-text"><i class="bi bi-search"></i></span>
      <input type="search" id="moduleSearch" class="form-control" placeholder="Search modules" aria-label="Search modules">
    </div>
    <div class="d-flex flex-wrap gap-2" id="moduleFilters">
      <button type="button" class="pf-chip active" data-filter="all">All <span class="n"></span></button>
      <button type="button" class="pf-chip" data-filter="system">Built-in <span class="n"></span></button>
      <button type="button" class="pf-chip" data-filter="custom">Custom <span class="n"></span></button>
      <button type="button" class="pf-chip" data-filter="inactive">Switched off <span class="n"></span></button>
    </div>
  </div>
</div>

<div class="mp-card">
  <div id="moduleList">@include('superadminconsole.pages.console.modules._list')</div>
</div>

<div class="caption mt-3">
  <i class="bi bi-info-circle me-1"></i>
  Switching a module off only hides it from new plans. Plans that already include it keep it.
  A module's key never changes, so renaming is always safe.
</div>

@include('superadminconsole.pages.console.modules._modal')
@endsection
