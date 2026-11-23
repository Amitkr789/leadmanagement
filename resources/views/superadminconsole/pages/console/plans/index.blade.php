@extends('superadminconsole.layouts.app')
@section('title', 'Plans & Pricing')
@section('active', 'plans')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <div><h1 class="page-title">Plans &amp; Pricing</h1><div class="caption">Plans created here appear instantly in Add Company.</div></div>
  <div class="d-flex gap-2"><a href="{{ route('console.subscriptions.index') }}" class="btn btn-light border"><i class="bi bi-arrow-repeat me-1"></i>Subscriptions</a>
    <button type="button" class="btn btn-primary js-plan-open"><i class="bi bi-plus-lg me-1"></i>Create plan</button></div>
</div>
<div id="planGrid">@include('superadminconsole.pages.console.plans._grid')</div>
@include('superadminconsole.pages.console.plans._modal')
@endsection
