<!-- ===================== SIDEBAR ===================== -->
 {{-- ==========================================================================
     SIDEBAR (HubSpot-style) — pinned pages, live search, collapsible groups,
     icon-rail mode, scroll fades, live badges, mobile drawer.

     Usage (layouts/app.blade.php):  replace the whole  <aside class="sidebar"> … </aside>
     and the  <div class="sidebar-backdrop"> …  with:   @include('layouts.partials.sidebar')

     Add / remove pages in the $sbGroups array below — nothing else needs editing.
     ========================================================================== --}}
@php
    $sbUser     = auth('admin')->user();
    $sbSuper    = $sbUser && $sbUser->isSuperAdmin();
    $sbInitials = strtoupper(collect(explode(' ', trim($sbUser->name)))->filter()->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->join(''));
    $sbRole     = \Illuminate\Support\Str::headline((string) $sbUser->role);

    // key = stable id used for pins / badges.  exact = highlight only on this exact path.
    // super = visible to super admins only.  kw = extra words the sidebar search understands.
    $sbGroups = [
        ['key' => 'home', 'label' => 'Home', 'plain' => true, 'items' => [
            ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'grid-1x2', 'href' => url('admin'), 'exact' => true, 'kw' => 'home overview stats'],
        ]],
        ['key' => 'crm', 'label' => 'CRM', 'items' => [
            ['key' => 'leads',     'label' => 'Leads',      'icon' => 'person-lines-fill', 'href' => url('admin/leads'),      'kw' => 'pipeline prospects enquiries'],
            ['key' => 'deals',     'label' => 'Deals',      'icon' => 'briefcase',         'href' => url('admin/deals'),      'kw' => 'opportunities sales'],
            ['key' => 'contacts',  'label' => 'Contacts',   'icon' => 'people',            'href' => url('admin/contacts'),   'kw' => 'people customers clients'],
            ['key' => 'companies', 'label' => 'Companies',  'icon' => 'building',          'href' => url('admin/companies'),  'kw' => 'organisations accounts gst'],

            ['key' => 'tickets', 'label' => 'Support tickets',  'icon' => 'bi bi-life-preserver',          'href' => url('admin/tickets'),  'kw' => 'stock products vehicles', 'super' => true],


            ['key' => 'meta',      'label' => 'Meta Leads', 'icon' => 'megaphone',         'href' => url('admin/meta-leads'), 'kw' => 'facebook instagram ads', 'super' => true],
            ['key' => 'inventory', 'label' => 'Inventory',  'icon' => 'box-seam',          'href' => url('admin/inventory'),  'kw' => 'stock products vehicles', 'super' => true],
        ]],
        ['key' => 'work', 'label' => 'Productivity', 'items' => [
            ['key' => 'chat',      'label' => 'Chat Room', 'icon' => 'chat-dots',    'href' => url('admin/chat'),      'kw' => 'whatsapp inbox messages'],
            ['key' => 'reminders', 'label' => 'Reminders', 'icon' => 'bell',         'href' => url('admin/reminders'), 'kw' => 'alarms follow up'],
            ['key' => 'tasks',     'label' => 'Tasks',     'icon' => 'check2-square', 'href' => url('admin/tasks'),    'kw' => 'todo to-do work'],
            ['key' => 'billing',   'label' => 'Billings',  'icon' => 'receipt',      'href' => url('admin/billing'),   'kw' => 'invoice bills payments'],
        ]],
        ['key' => 'ai', 'label' => 'Manage AI', 'super' => true, 'items' => [
            ['key' => 'kb',      'label' => 'Knowledge Base', 'icon' => 'journal-text', 'href' => url('admin/knowledge-base'),   'kw' => 'documents training answers'],
            ['key' => 'aiset',   'label' => 'AI settings',    'icon' => 'sliders',      'href' => url('admin/chatbot-settings'), 'kw' => 'chatbot bot prompt'],
        ]],
    ];

    // Drop what this user may not see.
    $sbGroups = collect($sbGroups)
        ->filter(fn ($g) => !($g['super'] ?? false) || $sbSuper)
        ->map(function ($g) use ($sbSuper) {
            $g['items'] = collect($g['items'])->filter(fn ($i) => !($i['super'] ?? false) || $sbSuper)->values()->all();
            return $g;
        })
        ->filter(fn ($g) => count($g['items']))
        ->values()->all();
@endphp

<script>
  /* Apply the saved "icon rail" state before first paint so the page never jumps. */
  (function () {
    try {
      var s = JSON.parse(localStorage.getItem('lf.sidebar.v1.' + @json($sbUser->id)) || '{}');
      if (s.rail) document.body.classList.add('sb-rail');
    } catch (e) { /* storage blocked */ }
  })();
</script>

<style>
  /* ---- shell ---- */
  .sidebar { width: 248px; }
  .sidebar [hidden] { display: none !important; }
  body.sb-lock { overflow: hidden; }
  body:not(.sb-ready) .sidebar, body:not(.sb-ready) .main-col { transition: none !important; }
  .main-col { transition: margin-left .2s ease; }
  .sidebar { transition: transform .25s ease, width .2s ease; }

  .sb-top { display: flex; align-items: center; gap: 6px; padding: 18px 12px 12px 18px; }
  .sb-brand { display: flex; align-items: center; gap: 10px; color: #fff; text-decoration: none; min-width: 0; flex: 1; border-radius: 10px; outline: 0; }
  .sb-brand:focus-visible { box-shadow: 0 0 0 2px rgba(129,140,248,.8); }
  .sb-brand .mark { width: 32px; height: 32px; border-radius: 9px; background: linear-gradient(135deg, var(--primary-light), #818CF8); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 15px; flex-shrink: 0; }
  .sb-brand .name { font-weight: 700; font-size: 16.5px; line-height: 1.1; white-space: nowrap; }
  .sb-brand .name small { display: block; font-size: 10.5px; font-weight: 500; color: var(--sidebar-text); letter-spacing: .06em; margin-top: 3px; }
  .sb-iconbtn { width: 30px; height: 30px; border-radius: 8px; border: 0; background: transparent; color: #7E84A3; display: inline-flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; outline: 0; transition: background .12s, color .12s; }
  .sb-iconbtn:hover { background: rgba(255,255,255,.09); color: #fff; }
  .sb-iconbtn:focus-visible { box-shadow: 0 0 0 2px rgba(129,140,248,.8); }

  /* ---- search ---- */
  .sb-search { position: relative; margin: 2px 14px 8px; }
  .sb-search input { width: 100%; height: 36px; border-radius: 9px; background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.08); color: #fff; font-size: 13px; padding: 0 52px 0 34px; outline: 0; transition: background .12s, border-color .12s, box-shadow .12s; }
  .sb-search input::placeholder { color: #6B7090; }
  .sb-search input:focus { background: rgba(255,255,255,.09); border-color: rgba(129,140,248,.75); box-shadow: 0 0 0 3px rgba(99,102,241,.25); }
  .sb-search > .bi-search { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #6B7090; font-size: 13px; pointer-events: none; }
  .sb-search kbd { position: absolute; right: 8px; top: 50%; transform: translateY(-50%); font: 600 10.5px/1 inherit; background: rgba(255,255,255,.08); color: #8C92B0; border-radius: 5px; padding: 3px 6px; pointer-events: none; }
  .sb-search .sb-clear { position: absolute; right: 5px; top: 50%; transform: translateY(-50%); width: 26px; height: 26px; display: none; font-size: 11px; }
  .sb-search.has-q kbd { display: none; }
  .sb-search.has-q .sb-clear { display: inline-flex; }
  .sb-search-rail { display: none; position: absolute; inset: 0; border: 0; background: transparent; cursor: pointer; border-radius: 9px; }

  /* ---- scroll area ---- */
  .sb-navwrap { position: relative; flex: 1; min-height: 0; }
  .sb-scroll { height: 100%; overflow-y: auto; overscroll-behavior: contain; padding: 2px 10px 18px; scrollbar-width: thin; scrollbar-color: transparent transparent; }
  .sb-navwrap:hover .sb-scroll { scrollbar-color: rgba(255,255,255,.2) transparent; }
  .sb-scroll::-webkit-scrollbar { width: 8px; }
  .sb-scroll::-webkit-scrollbar-thumb { background: transparent; border-radius: 8px; border: 2px solid var(--sidebar-bg); }
  .sb-navwrap:hover .sb-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,.2); }
  .sb-fade { position: absolute; left: 0; right: 0; height: 26px; pointer-events: none; opacity: 0; transition: opacity .15s; z-index: 2; }
  .sb-fade.top { top: 0; background: linear-gradient(var(--sidebar-bg), rgba(21,25,46,0)); }
  .sb-fade.bottom { bottom: 0; background: linear-gradient(rgba(21,25,46,0), var(--sidebar-bg)); }
  .sb-navwrap.can-up .sb-fade.top, .sb-navwrap.can-down .sb-fade.bottom { opacity: 1; }

  /* ---- groups ---- */
  .sb-group, .sb-pinned { margin-top: 6px; }
  .sb-group.plain { margin-top: 0; }
  .sb-head { display: flex; align-items: center; gap: 6px; width: 100%; border: 0; background: transparent; color: #5F6585; font-size: 10.5px; text-transform: uppercase; letter-spacing: .08em; font-weight: 700; padding: 10px 10px 6px; text-align: left; border-radius: 6px; outline: 0; }
  button.sb-head { cursor: pointer; }
  button.sb-head:hover { color: #9BA0BD; }
  button.sb-head:focus-visible { box-shadow: inset 0 0 0 2px rgba(129,140,248,.8); }
  .sb-head .bi-chevron-down { margin-left: auto; font-size: 10px; transition: transform .18s; }
  .sb-group.closed .sb-head .bi-chevron-down { transform: rotate(-90deg); }
  .sb-pinned .sb-head { color: #D9A441; cursor: default; }
  .sb-pinned .sb-head .n { margin-left: auto; font-weight: 700; color: #7E84A3; letter-spacing: 0; }
  .sb-collapse { display: grid; grid-template-rows: 1fr; transition: grid-template-rows .2s ease; }
  .sb-collapse > .sb-list { overflow: hidden; min-height: 0; }
  .sb-group.closed .sb-collapse { grid-template-rows: 0fr; }
  .sb-group.closed .sb-collapse > .sb-list { visibility: hidden; transition: visibility 0s .2s; }

  /* ---- items ---- */
  .sb-item { position: relative; margin-bottom: 2px; }
  .sb-link { display: flex; align-items: center; gap: 11px; padding: 8px 38px 8px 11px; border-radius: 8px; color: var(--sidebar-text); font-size: 13.5px; font-weight: 500; text-decoration: none; position: relative; outline: 0; transition: background .12s, color .12s; }
  .sb-link > i { font-size: 16px; width: 18px; text-align: center; flex-shrink: 0; }
  .sb-label { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; min-width: 0; }
  .sb-label mark { padding: 0; background: transparent; color: #C7D2FE; font-weight: 700; text-decoration: underline; text-decoration-color: rgba(165,180,252,.55); text-underline-offset: 3px; }
  .sb-crumb { display: none; margin-left: auto; font-size: 10.5px; color: #6B7090; padding-left: 8px; white-space: nowrap; }
  .sb-link:hover, .sb-item:focus-within .sb-link { background: rgba(255,255,255,.05); color: #fff; }
  .sb-link:focus-visible { box-shadow: inset 0 0 0 2px rgba(129,140,248,.8); }
  .sb-link.active { background: var(--sidebar-bg-active); color: #fff; }
  .sb-link.active > i { color: #A5B4FC; }
  .sb-link.active::before { content: ''; position: absolute; left: -10px; top: 8px; bottom: 8px; width: 3px; border-radius: 0 4px 4px 0; background: var(--primary-light); }
  .sb-link.kb { background: rgba(99,102,241,.24); color: #fff; }

  .sb-pin { position: absolute; right: 6px; top: 50%; transform: translateY(-50%); width: 26px; height: 26px; border: 0; background: transparent; border-radius: 7px; color: #7E84A3; opacity: 0; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; outline: 0; transition: opacity .12s, background .12s, color .12s; }
  .sb-item:hover .sb-pin, .sb-item:focus-within .sb-pin, .sb-pin:focus-visible { opacity: 1; }
  .sb-pin:hover { background: rgba(255,255,255,.1); color: #fff; }
  .sb-pin:focus-visible { box-shadow: 0 0 0 2px rgba(129,140,248,.8); }
  .sb-pin[aria-pressed="true"] { color: #FBBF24; }
  @media (hover: none) { .sb-pin { opacity: .6; } }

  .sb-badge { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); min-width: 18px; height: 18px; padding: 0 6px; border-radius: 999px; background: rgba(255,255,255,.12); color: #D5D9EE; font-size: 10.5px; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; transition: opacity .12s; pointer-events: none; }
  .sb-badge.danger { background: var(--danger); color: #fff; }
  .sb-item:hover .sb-badge, .sb-item:focus-within .sb-badge { opacity: 0; }

  .sb-grip { position: absolute; left: -9px; top: 50%; transform: translateY(-50%); color: #555B7C; font-size: 12px; opacity: 0; pointer-events: none; }
  .sb-pinned .sb-item { cursor: grab; }
  .sb-pinned .sb-item:hover .sb-grip { opacity: 1; }
  .sb-item.dragging { opacity: .35; }
  .sb-item.drop-before { box-shadow: 0 -2px 0 0 var(--primary-light); }
  .sb-item.drop-after { box-shadow: 0 2px 0 0 var(--primary-light); }

  .sb-hint { display: flex; gap: 8px; align-items: flex-start; margin: 2px 2px 6px; padding: 10px 10px 10px 12px; border: 1px dashed rgba(255,255,255,.15); border-radius: 10px; color: #8C92B0; font-size: 12px; line-height: 1.45; }
  .sb-hint .bi-pin-angle-fill { color: #FBBF24; margin-top: 1px; }
  .sb-hint .sb-iconbtn { width: 22px; height: 22px; font-size: 10px; margin: -2px -2px 0 auto; }

  .sb-empty { display: none; text-align: center; padding: 28px 14px; color: #8C92B0; font-size: 12.5px; line-height: 1.5; }
  .sb-empty b { display: block; color: #fff; font-size: 13.5px; margin-bottom: 3px; }
  .sb-empty i { font-size: 22px; display: block; margin-bottom: 8px; color: #555B7C; }
  .sidebar.sb-searching.sb-noresult .sb-empty { display: block; }

  /* search mode: flat list of matches */
  .sidebar.sb-searching .sb-pinned { display: none; }
  .sidebar.sb-searching .sb-head .bi-chevron-down { display: none; }
  .sidebar.sb-searching .sb-group.closed .sb-collapse { grid-template-rows: 1fr; }
  .sidebar.sb-searching .sb-group.closed .sb-collapse > .sb-list { visibility: visible; }
  .sidebar.sb-searching .sb-crumb { display: block; }
  .sidebar.sb-searching .sb-group.plain .sb-head { display: none; }

  /* ---- footer ---- */
  .sb-footer { padding: 10px 12px; border-top: 1px solid rgba(255,255,255,.07); display: flex; align-items: center; gap: 4px; }
  .sb-user { display: flex; align-items: center; gap: 10px; padding: 7px 8px; border-radius: 9px; color: #fff; text-decoration: none; flex: 1; min-width: 0; outline: 0; }
  .sb-user:hover { background: rgba(255,255,255,.05); color: #fff; }
  .sb-user:focus-visible { box-shadow: 0 0 0 2px rgba(129,140,248,.8); }
  .sb-user .info { line-height: 1.25; overflow: hidden; min-width: 0; }
  .sb-user .uname { font-size: 13px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .sb-user .urole { font-size: 11px; color: var(--sidebar-text); }

  /* ---- tooltip (icon rail) ---- */
  .sb-tip { position: fixed; z-index: 3000; background: #0F1224; color: #fff; font-size: 12.5px; font-weight: 600; padding: 6px 10px; border-radius: 7px; box-shadow: 0 6px 18px rgba(0,0,0,.3); pointer-events: none; opacity: 0; transition: opacity .1s; white-space: nowrap; }
  .sb-tip.show { opacity: 1; }

  /* ---- icon rail (desktop only) ---- */
  @media (min-width: 992px) {
    body.sb-rail .sidebar { width: 72px; }
    body.sb-rail .main-col { margin-left: 72px; }
    body.sb-rail .sb-top { flex-direction: column; padding: 16px 0 6px; gap: 8px; }
    body.sb-rail .sb-brand { flex: none; }
    body.sb-rail .sb-brand .name, body.sb-rail .sb-label, body.sb-rail .sb-crumb, body.sb-rail .sb-pin, body.sb-rail .sb-grip,
    body.sb-rail .sb-hint, body.sb-rail .sb-search input, body.sb-rail .sb-search kbd, body.sb-rail .sb-search .sb-clear,
    body.sb-rail .sb-user .info, body.sb-rail .sb-pinned .sb-head .n, body.sb-rail .sb-head .bi-chevron-down { display: none; }
    body.sb-rail .sb-search { margin: 2px 14px 6px; height: 40px; border-radius: 10px; background: rgba(255,255,255,.06); }
    body.sb-rail .sb-search > .bi-search { left: 50%; transform: translate(-50%, -50%); font-size: 15px; color: #9BA0BD; }
    body.sb-rail .sb-search-rail { display: block; }
    body.sb-rail .sb-scroll { padding-left: 12px; padding-right: 12px; }
    body.sb-rail .sb-head { font-size: 0; height: 1px; padding: 0; margin: 12px 10px 8px; background: rgba(255,255,255,.09); pointer-events: none; }
    body.sb-rail .sb-group.plain .sb-head { display: none; }
    body.sb-rail .sb-pinned .sb-head > i { display: none; }
    body.sb-rail .sb-group.closed .sb-collapse { grid-template-rows: 1fr; }
    body.sb-rail .sb-group.closed .sb-collapse > .sb-list { visibility: visible; }
    body.sb-rail .sb-link { justify-content: center; padding: 10px 0; }
    body.sb-rail .sb-link > i { font-size: 18px; }
    body.sb-rail .sb-link.active::before { left: -12px; }
    body.sb-rail .sb-badge { right: 8px; top: 6px; transform: none; min-width: 9px; height: 9px; padding: 0; font-size: 0; opacity: 1 !important; border: 2px solid var(--sidebar-bg); box-sizing: content-box; }
    body.sb-rail .sb-pinned .sb-item { cursor: pointer; }
    body.sb-rail .sb-footer { flex-direction: column; padding: 10px 0; }
    body.sb-rail .sb-user { flex: none; padding: 6px; }
  }

  /* ---- mobile drawer ---- */
  @media (max-width: 991.98px) {
    .sidebar { width: 284px; max-width: 86vw; }
    .sb-rail-btn { display: none; }
    .sb-pin { opacity: .6; }
  }
  @media (prefers-reduced-motion: reduce) {
    .sidebar, .main-col, .sb-collapse, .sb-head .bi-chevron-down, .sb-fade, .sb-link, .sb-pin, .sb-tip { transition: none !important; }
  }
</style>
<aside class="sidebar" id="sidebar" aria-label="Main navigation">
  <div class="sb-top">
    <a class="sb-brand" href="{{ url('admin') }}" aria-label="LEADFLOW">
      <span class="mark text-light">LF</span>
      <span class="name">LEADFLOW<small>CRM WORKSPACE</small></span>
    </a>
    <button type="button" class="sb-iconbtn sb-rail-btn" id="sbRail" aria-pressed="false" aria-label="Collapse sidebar" data-tip="Collapse sidebar ( [ )"><i class="bi bi-chevron-double-left"></i></button>
  </div>

  <div class="sb-search" id="sbSearchBox" role="search">
    <i class="bi bi-search" aria-hidden="true"></i>
    <input type="text" id="sbSearch" placeholder="Search pages" autocomplete="off" spellcheck="false" aria-label="Search pages">
    <kbd aria-hidden="true">Ctrl K</kbd>
    <button type="button" class="sb-iconbtn sb-clear" id="sbClear" aria-label="Clear search"><i class="bi bi-x-lg"></i></button>
    <button type="button" class="sb-search-rail" id="sbSearchRail" aria-label="Search pages" data-tip="Search pages"></button>
  </div>

  <nav class="sb-navwrap" id="sbNavWrap" aria-label="Pages">
    <div class="sb-scroll" id="sbScroll">

      <section class="sb-pinned" id="sbPinned" hidden>
        <div class="sb-head"><i class="bi bi-pin-angle-fill" aria-hidden="true"></i><span>Pinned</span><span class="n" id="sbPinCount"></span></div>
        <div class="sb-list" id="sbPinnedList"></div>
        <div class="sb-hint" id="sbHint" hidden>
          <i class="bi bi-pin-angle-fill" aria-hidden="true"></i>
          <span>Hover any page and click the pin to keep it here. Drag to reorder.</span>
          <button type="button" class="sb-iconbtn" id="sbHintClose" aria-label="Dismiss tip"><i class="bi bi-x-lg"></i></button>
        </div>
      </section>

      @foreach($sbGroups as $g)
        <section class="sb-group {{ !empty($g['plain']) ? 'plain' : '' }}" data-group="{{ $g['key'] }}" data-glabel="{{ $g['label'] }}">
          @if(empty($g['plain']))
            <button type="button" class="sb-head" data-toggle="{{ $g['key'] }}" aria-expanded="true" aria-controls="sbg-{{ $g['key'] }}">
              <span>{{ $g['label'] }}</span><i class="bi bi-chevron-down" aria-hidden="true"></i>
            </button>
          @else
            <div class="sb-head"><span>{{ $g['label'] }}</span></div>
          @endif
          <div class="sb-collapse"><div class="sb-list" id="sbg-{{ $g['key'] }}">
            @foreach($g['items'] as $it)
              <div class="sb-item" data-key="{{ $it['key'] }}" data-label="{{ $it['label'] }}" data-kw="{{ $it['kw'] ?? '' }}" data-icon="{{ $it['icon'] }}" @if(!empty($it['exact'])) data-exact="1" @endif>
                <a class="sb-link" href="{{ $it['href'] }}" data-tip="{{ $it['label'] }}">
                  <i class="bi bi-{{ $it['icon'] }}" aria-hidden="true"></i>
                  <span class="sb-label">{{ $it['label'] }}</span>
                  <span class="sb-crumb">{{ $g['label'] }}</span>
                  <span class="sb-badge" hidden></span>
                </a>
                <button type="button" class="sb-pin" aria-pressed="false" aria-label="Pin {{ $it['label'] }}" title="Pin to top"><i class="bi bi-pin-angle" aria-hidden="true"></i></button>
              </div>
            @endforeach
          </div></div>
        </section>
      @endforeach

      <div class="sb-empty" id="sbEmpty" role="status">
        <i class="bi bi-search" aria-hidden="true"></i>
        <b>No pages found</b>
        Try another word, e.g. “lead”, “invoice” or “people”.
      </div>
    </div>
    <div class="sb-fade top" aria-hidden="true"></div>
    <div class="sb-fade bottom" aria-hidden="true"></div>
  </nav>

  <div class="sb-footer">
    <a class="sb-user" href="{{ route('admin.profile') }}" data-tip="{{ $sbUser->name }}">
      <div class="avatar avatar-sm" style="background:#6366F1">{{ $sbInitials }}</div>
      <div class="info">
        <div class="uname">{{ $sbUser->name }}</div>
        <div class="urole">{{ $sbRole }}</div>
      </div>
    </a>
    <button type="button" class="sb-iconbtn" id="sbLogout" aria-label="Sign out" data-tip="Sign out"><i class="bi bi-box-arrow-right"></i></button>
  </div>

  <div class="visually-hidden" id="sbLive" aria-live="polite"></div>
</aside>

<div class="sidebar-backdrop" id="sidebarBackdrop"></div>
<div class="sb-tip" id="sbTip" role="tooltip"></div>
  <!-- ===================== MAIN ===================== -->
  <div class="main-col">

    <!-- Topbar -->
    <header class="topbar">
      <button class="btn-hamburger" id="hamburgerBtn"><i class="bi bi-list fs-5"></i></button>
   
      <div class="topbar-search">
        <i class="bi bi-search"></i>
        <input type="text" class="form-control" placeholder="Search leads, contacts, deals...">
      </div>

      <div class="topbar-actions">
        <!-- Notification bell: now a real dropdown fed by the global reminder poll -->
        <div class="dropdown">
  <button class="icon-btn" id="taskBellBtn" data-bs-toggle="dropdown" data-bs-auto-close="outside" title="Task notifications">
    <i class="bi bi-list-check fs-5"></i>
    <span class="notif-badge" id="taskBellCount" style="display:none"></span>
  </button>
  <div class="dropdown-menu dropdown-menu-end notif-dropdown" id="taskBellList" style="width:320px">
    <div class="notif-empty">No task notifications</div>
  </div>
</div>
        <div class="dropdown">
          <button class="icon-btn" id="notifBellBtn" data-bs-toggle="dropdown" data-bs-auto-close="outside" title="Notifications">
            <i class="bi bi-bell fs-5"></i>
            <span class="notif-badge" id="notifBellCount" style="display:none"></span>
          </button>
          <div class="dropdown-menu dropdown-menu-end notif-dropdown" id="notifBellList" style="width:320px">
            <div class="notif-empty">No reminders due right now</div>
          </div>
        </div>
        <button class="icon-btn d-none d-md-flex" data-bs-toggle="tooltip" title="Help"><i class="bi bi-question-circle fs-5"></i></button>
        <div class="dropdown">
          <a href="#" class="topbar-profile" data-bs-toggle="dropdown">
            <div class="avatar avatar-sm" style="background:#6366F1">{{ strtoupper(collect(explode(' ', Auth::guard('admin')->user()->name))->map(fn($word) => substr($word, 0, 1))->join('')) }}</div>
            <i class="bi bi-chevron-down fs-12 d-none d-sm-inline" style="color:var(--text-muted)"></i>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><h6 class="dropdown-header">{{ Auth::guard('admin')->user()->name }}</h6></li>
            <li><a class="dropdown-item" href="{{route('admin.profile')}}"><i class="bi bi-person me-2"></i>My profile</a></li>
            @if(auth('admin')->user()->isSuperAdmin())
            <li><a class="dropdown-item" href="{{route('admin.register')}}"><i class="bi bi-gear me-2"></i>Settings</a></li>
            @endif
            <li><hr class="dropdown-divider"></li>
            <li><form id="logoutForm" method="POST" action="{{ route('admin.logout') }}" class="d-none">@csrf</form>
<a class="dropdown-item" href="{{route('admin.logout')}}" id="logoutBtn"><i class="bi bi-box-arrow-right me-2"></i>Sign out</a></li>
          </ul>
        </div>
      </div>
    </header>

    <style>
      /* Global reminder alarm popup — lives here (not on the leads page) because
         the modal can now appear on any page. */
      .alarm-card { border: 1px solid var(--border); border-radius: 12px; padding: 12px 14px; margin-bottom: 10px; }
      .alarm-card:last-child { margin-bottom: 0; }
      .alarm-card .alarm-lead-name { font-weight: 700; font-size: 14px; }
      .alarm-card .alarm-note { font-size: 12.5px; color: var(--text-muted); margin: 3px 0 10px; }
      .alarm-card .alarm-actions { display: flex; gap: 8px; }
      .alarm-card .alarm-actions .btn { flex: 1; padding: 7px 10px; font-size: 12.5px; }

      /* Topbar notification bell */
      .icon-btn { position: relative; }
      .notif-badge {
        position: absolute; top: 2px; right: 2px; min-width: 16px; height: 16px; padding: 0 4px;
        background: var(--danger); color: #fff; border-radius: 20px; font-size: 10px; font-weight: 700;
        display: flex; align-items: center; justify-content: center; line-height: 1;
      }
      .notif-dropdown { padding: 6px; max-height: 360px; overflow-y: auto; }
      .notif-dropdown .notif-item { display: flex; flex-direction: column; gap: 2px; padding: 9px 10px; border-radius: 9px; }
      .notif-dropdown .notif-item:hover { background: var(--primary-50); }
      .notif-dropdown .notif-item .notif-name { font-weight: 700; font-size: 13px; }
      .notif-dropdown .notif-item .notif-note { font-size: 12px; color: var(--text-muted); }
      .notif-dropdown .notif-item .notif-meta { font-size: 11px; color: var(--danger); font-weight: 600; }
      .notif-dropdown .notif-item .notif-actions { display: flex; gap: 6px; margin-top: 4px; }
      .notif-dropdown .notif-item .notif-actions .btn { font-size: 11.5px; padding: 3px 9px; border-radius: 7px; }
      .notif-dropdown .notif-empty { padding: 20px 10px; text-align: center; font-size: 12.5px; color: var(--text-muted); }
    </style>

<script>
(function () {
  "use strict";
  var UID = @json($sbUser->id);
  var LOGOUT_URL = @json(route('admin.logout'));
  var STORE = "lf.sidebar.v1." + UID, SCROLL_KEY = "lf.sidebar.scroll";

  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
  var esc = function (s) { return String(s == null ? "" : s).replace(/[&<>"']/g, function (c) { return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]; }); };

  var side = $("#sidebar"), scroller = $("#sbScroll"), wrap = $("#sbNavWrap"), search = $("#sbSearch"), searchBox = $("#sbSearchBox");
  var pinnedSec = $("#sbPinned"), pinnedList = $("#sbPinnedList"), hint = $("#sbHint"), live = $("#sbLive"), tip = $("#sbTip");
  var isDesktop = function () { return window.innerWidth >= 992; };

  /* ---------------- state ---------------- */
  var st = (function () {
    try {
      var o = JSON.parse(localStorage.getItem(STORE) || "{}");
      return { pins: Array.isArray(o.pins) ? o.pins : [], closed: Array.isArray(o.closed) ? o.closed : [], rail: !!o.rail, hint: o.hint !== false };
    } catch (e) { return { pins: [], closed: [], rail: false, hint: true }; }
  })();
  function save() { try { localStorage.setItem(STORE, JSON.stringify(st)); } catch (e) { /* ignore */ } }
  function announce(msg) { live.textContent = ""; setTimeout(function () { live.textContent = msg; }, 30); }

  /* ---------------- items ---------------- */
  var items = {}, order = [];
  $$(".sb-group .sb-item").forEach(function (el) {
    var a = $(".sb-link", el), g = el.closest(".sb-group");
    items[el.dataset.key] = { key: el.dataset.key, label: el.dataset.label, kw: el.dataset.kw || "", icon: el.dataset.icon, exact: !!el.dataset.exact,
      href: a.href, path: new URL(a.href, location.href).pathname.replace(/\/+$/, "") || "/", group: g.dataset.glabel, groupEl: g, el: el };
    order.push(el.dataset.key);
  });
  st.pins = st.pins.filter(function (k, i, a) { return items[k] && a.indexOf(k) === i; });

  /* ---------------- active page ---------------- */
  var activeKey = null;
  function markActive() {
    var path = location.pathname.replace(/\/+$/, "") || "/", best = 0;
    activeKey = null;
    order.forEach(function (k) {
      var it = items[k], p = it.path, score = 0;
      if (it.exact) score = path === p ? 1000 : 0;
      else if (path === p || path.indexOf(p + "/") === 0) score = p.length;
      if (score > best) { best = score; activeKey = k; }
    });
    $$(".sb-link").forEach(function (a) {
      var on = !!activeKey && a.closest(".sb-item").dataset.key === activeKey;
      a.classList.toggle("active", on);
      if (on) a.setAttribute("aria-current", "page"); else a.removeAttribute("aria-current");
    });
  }

  /* ---------------- badges ---------------- */
  var badges = {};
  function paintBadges() {
    $$(".sb-item").forEach(function (el) {
      var b = $(".sb-badge", el), d = badges[el.dataset.key];
      if (!b) return;
      if (d && d.n > 0) { b.hidden = false; b.textContent = d.n > 99 ? "99+" : String(d.n); b.className = "sb-badge" + (d.tone ? " " + d.tone : ""); }
      else { b.hidden = true; b.textContent = ""; }
    });
  }
  function setBadge(key, n, tone) { badges[key] = { n: parseInt(n, 10) || 0, tone: tone || "" }; paintBadges(); }
  // Mirror the counters the topbar bells already maintain (reminders + tasks).
  function mirror(id, key) {
    var el = document.getElementById(id);
    if (!el) return;
    var read = function () {
      var t = (el.textContent || "").trim(), hidden = getComputedStyle(el).display === "none";
      var n = parseInt(t, 10);
      setBadge(key, hidden || !t ? 0 : (isNaN(n) ? 1 : n), "danger");
    };
    new MutationObserver(read).observe(el, { childList: true, characterData: true, subtree: true, attributes: true, attributeFilter: ["style", "class"] });
    read();
  }

  /* ---------------- pins ---------------- */
  function renderPinned() {
    pinnedList.innerHTML = st.pins.map(function (k) {
      var it = items[k];
      return '<div class="sb-item" data-key="' + esc(k) + '" draggable="true">' +
        '<i class="bi bi-grip-vertical sb-grip" aria-hidden="true"></i>' +
        '<a class="sb-link" href="' + esc(it.href) + '" data-tip="' + esc(it.label) + '"><i class="bi bi-' + esc(it.icon) + '" aria-hidden="true"></i><span class="sb-label">' + esc(it.label) + '</span><span class="sb-badge" hidden></span></a>' +
        '<button type="button" class="sb-pin" aria-pressed="true" aria-label="Unpin ' + esc(it.label) + '" title="Unpin"><i class="bi bi-pin-angle-fill" aria-hidden="true"></i></button></div>';
    }).join("");
    var showHint = st.hint && !st.pins.length;
    hint.hidden = !showHint;
    pinnedSec.hidden = !(st.pins.length || showHint);
    $("#sbPinCount").textContent = st.pins.length ? String(st.pins.length) : "";
    order.forEach(function (k) {
      var b = $(".sb-pin", items[k].el), on = st.pins.indexOf(k) >= 0;
      b.setAttribute("aria-pressed", on ? "true" : "false");
      b.setAttribute("aria-label", (on ? "Unpin " : "Pin ") + items[k].label);
      b.title = on ? "Unpin" : "Pin to top";
      b.innerHTML = '<i class="bi bi-pin-angle' + (on ? "-fill" : "") + '" aria-hidden="true"></i>';
    });
    markActive(); paintBadges(); updateFades();
  }
  function togglePin(key) {
    var i = st.pins.indexOf(key);
    if (i >= 0) { st.pins.splice(i, 1); announce(items[key].label + " unpinned"); }
    else { st.pins.push(key); st.hint = false; announce(items[key].label + " pinned"); }
    save(); renderPinned();
  }
  function movePin(key, targetKey, before) {
    var a = st.pins.filter(function (k) { return k !== key; });
    var at = a.indexOf(targetKey);
    if (at < 0) return;
    a.splice(before ? at : at + 1, 0, key);
    st.pins = a; save(); renderPinned();
  }

  /* drag & drop reorder (pinned list) */
  var dragKey = null;
  function clearMarks() { $$(".drop-before,.drop-after,.dragging", pinnedList).forEach(function (e) { e.classList.remove("drop-before", "drop-after", "dragging"); }); }
  pinnedList.addEventListener("dragstart", function (e) {
    var it = e.target.closest && e.target.closest(".sb-item");
    if (!it || document.body.classList.contains("sb-rail")) { e.preventDefault(); return; }
    dragKey = it.dataset.key;
    e.dataTransfer.effectAllowed = "move";
    try { e.dataTransfer.setData("text/plain", dragKey); } catch (er) { /* ignore */ }
    setTimeout(function () { it.classList.add("dragging"); }, 0);
  });
  pinnedList.addEventListener("dragover", function (e) {
    if (!dragKey) return;
    e.preventDefault();
    var it = e.target.closest(".sb-item");
    $$(".drop-before,.drop-after", pinnedList).forEach(function (x) { x.classList.remove("drop-before", "drop-after"); });
    if (!it || it.dataset.key === dragKey) return;
    var r = it.getBoundingClientRect();
    it.classList.add(e.clientY < r.top + r.height / 2 ? "drop-before" : "drop-after");
  });
  pinnedList.addEventListener("drop", function (e) {
    if (!dragKey) return;
    e.preventDefault();
    var it = e.target.closest(".sb-item"), k = dragKey;
    dragKey = null;
    if (!it || it.dataset.key === k) { clearMarks(); return; }
    var r = it.getBoundingClientRect();
    movePin(k, it.dataset.key, e.clientY < r.top + r.height / 2);
  });
  pinnedList.addEventListener("dragend", function () { dragKey = null; clearMarks(); });
  // keyboard reorder: Alt + ↑ / ↓ on a pinned link
  pinnedList.addEventListener("keydown", function (e) {
    if (!e.altKey || (e.key !== "ArrowUp" && e.key !== "ArrowDown")) return;
    var it = e.target.closest(".sb-item");
    if (!it) return;
    e.preventDefault();
    var k = it.dataset.key, i = st.pins.indexOf(k), j = e.key === "ArrowUp" ? i - 1 : i + 1;
    if (j < 0 || j >= st.pins.length) return;
    movePin(k, st.pins[j], e.key === "ArrowUp");
    var again = $('.sb-item[data-key="' + k + '"] .sb-link', pinnedList);
    if (again) again.focus();
    announce(items[k].label + " moved to position " + (j + 1));
  });

  scroller.addEventListener("click", function (e) {
    var pin = e.target.closest(".sb-pin");
    if (pin) { e.preventDefault(); togglePin(pin.closest(".sb-item").dataset.key); return; }
    var head = e.target.closest("[data-toggle]");
    if (head && !side.classList.contains("sb-searching")) { toggleGroup(head.dataset.toggle); return; }
    if (e.target.closest(".sb-link")) { rememberScroll(); if (!isDesktop()) closeMobile(); }
  });
  $("#sbHintClose").addEventListener("click", function () { st.hint = false; save(); renderPinned(); });

  /* ---------------- groups ---------------- */
  function applyGroups() {
    $$(".sb-group[data-group]").forEach(function (g) {
      var key = g.dataset.group, btn = $("[data-toggle]", g);
      if (!btn) return;
      var hasActive = !!activeKey && items[activeKey].groupEl === g;
      var closed = st.closed.indexOf(key) >= 0 && !hasActive;
      g.classList.toggle("closed", closed);
      btn.setAttribute("aria-expanded", closed ? "false" : "true");
    });
    updateFades();
  }
  function toggleGroup(key) {
    var i = st.closed.indexOf(key);
    if (i >= 0) st.closed.splice(i, 1); else st.closed.push(key);
    save(); applyGroups();
    setTimeout(updateFades, 230);
  }

  /* ---------------- search ---------------- */
  var kbIndex = -1, kbLinks = [];
  function highlight(label, tokens) {
    var low = label.toLowerCase(), mask = new Array(label.length), any = false;
    tokens.forEach(function (t) { var at = low.indexOf(t); if (at >= 0) { any = true; for (var i = at; i < at + t.length; i++) mask[i] = true; } });
    if (!any) return esc(label);
    var out = "", open = false;
    for (var i = 0; i < label.length; i++) {
      if (mask[i] && !open) { out += "<mark>"; open = true; }
      if (!mask[i] && open) { out += "</mark>"; open = false; }
      out += esc(label[i]);
    }
    return out + (open ? "</mark>" : "");
  }
  function setKb(i) {
    $$(".sb-link.kb").forEach(function (a) { a.classList.remove("kb"); });
    kbIndex = i;
    var a = kbLinks[i];
    if (a) { a.classList.add("kb"); keepVisible(a); }
  }
  function applySearch() {
    var q = search.value.trim().toLowerCase(), tokens = q ? q.split(/\s+/) : [];
    side.classList.toggle("sb-searching", !!q);
    searchBox.classList.toggle("has-q", !!q);
    var shown = 0;
    order.forEach(function (k) {
      var it = items[k], hay = (it.label + " " + it.kw + " " + it.group).toLowerCase();
      var hit = !tokens.length || tokens.every(function (t) { return hay.indexOf(t) >= 0; });
      it.el.hidden = !hit;
      $(".sb-label", it.el).innerHTML = tokens.length ? highlight(it.label, tokens) : esc(it.label);
      if (hit) shown++;
    });
    $$(".sb-group").forEach(function (g) { g.hidden = !$(".sb-item:not([hidden])", g); });
    side.classList.toggle("sb-noresult", !!q && shown === 0);
    kbLinks = q ? order.filter(function (k) { return !items[k].el.hidden; }).map(function (k) { return $(".sb-link", items[k].el); }) : [];
    setKb(kbLinks.length ? 0 : -1);
    scroller.scrollTop = 0;
    updateFades();
  }
  search.addEventListener("input", applySearch);
  search.addEventListener("keydown", function (e) {
    if (e.key === "ArrowDown" || e.key === "ArrowUp") {
      if (!kbLinks.length) return;
      e.preventDefault();
      setKb((kbIndex + (e.key === "ArrowDown" ? 1 : -1) + kbLinks.length) % kbLinks.length);
    } else if (e.key === "Enter") {
      if (kbLinks[kbIndex]) { e.preventDefault(); rememberScroll(); location.href = kbLinks[kbIndex].href; }
    } else if (e.key === "Escape") {
      e.stopPropagation();
      if (search.value) { search.value = ""; applySearch(); } else search.blur();
    }
  });
  $("#sbClear").addEventListener("click", function () { search.value = ""; applySearch(); search.focus(); });

  function focusSearch() {
    if (isDesktop() && document.body.classList.contains("sb-rail")) setRail(false);
    if (!isDesktop()) openMobile();
    search.focus(); search.select();
  }
  $("#sbSearchRail").addEventListener("click", focusSearch);
  document.addEventListener("keydown", function (e) {
    var tag = ((document.activeElement || {}).tagName || "").toLowerCase();
    var typing = tag === "input" || tag === "textarea" || tag === "select" || (document.activeElement && document.activeElement.isContentEditable);
    if ((e.ctrlKey || e.metaKey) && !e.altKey && !e.shiftKey && (e.key === "k" || e.key === "K")) { e.preventDefault(); focusSearch(); return; }
    if (e.key === "[" && !typing && !e.ctrlKey && !e.metaKey && !e.altKey && isDesktop() && !document.querySelector(".modal.show, .offcanvas.show")) { e.preventDefault(); setRail(!st.rail); }
    if (e.key === "Escape" && side.classList.contains("show")) closeMobile();
  });

  /* ---------------- rail (collapsed) ---------------- */
  var railBtn = $("#sbRail");
  function setRail(on) {
    st.rail = !!on; save();
    document.body.classList.toggle("sb-rail", st.rail);
    railBtn.setAttribute("aria-pressed", st.rail ? "true" : "false");
    railBtn.setAttribute("aria-label", st.rail ? "Expand sidebar" : "Collapse sidebar");
    railBtn.dataset.tip = st.rail ? "Expand sidebar ( [ )" : "Collapse sidebar ( [ )";
    railBtn.innerHTML = '<i class="bi bi-chevron-double-' + (st.rail ? "right" : "left") + '"></i>';
    hideTip();
    setTimeout(updateFades, 230);
  }
  railBtn.addEventListener("click", function () { setRail(!st.rail); });

  /* tooltips while in rail mode */
  function showTip(el) {
    if (!(document.body.classList.contains("sb-rail") && isDesktop())) return;
    var t = el.getAttribute("data-tip");
    if (!t) return;
    tip.textContent = t;
    var r = el.getBoundingClientRect();
    tip.style.left = (r.right + 10) + "px";
    tip.style.top = (r.top + r.height / 2 - tip.offsetHeight / 2) + "px";
    tip.classList.add("show");
  }
  function hideTip() { tip.classList.remove("show"); }
  side.addEventListener("mouseover", function (e) { var el = e.target.closest("[data-tip]"); if (el) showTip(el); else hideTip(); });
  side.addEventListener("mouseleave", hideTip);
  side.addEventListener("focusin", function (e) { var el = e.target.closest("[data-tip]"); if (el) showTip(el); });
  side.addEventListener("focusout", hideTip);
  scroller.addEventListener("scroll", hideTip, { passive: true });

  /* ---------------- scroll: fades, memory, reveal ---------------- */
  var raf = 0;
  function updateFades() {
    cancelAnimationFrame(raf);
    raf = requestAnimationFrame(function () {
      wrap.classList.toggle("can-up", scroller.scrollTop > 2);
      wrap.classList.toggle("can-down", scroller.scrollTop + scroller.clientHeight < scroller.scrollHeight - 2);
    });
  }
  function rememberScroll() { try { sessionStorage.setItem(SCROLL_KEY, String(scroller.scrollTop)); } catch (e) { /* ignore */ } }
  var saveT;
  scroller.addEventListener("scroll", function () { updateFades(); clearTimeout(saveT); saveT = setTimeout(rememberScroll, 120); }, { passive: true });
  window.addEventListener("pagehide", rememberScroll);
  window.addEventListener("resize", updateFades);
  if (window.ResizeObserver) new ResizeObserver(updateFades).observe(scroller);

  function keepVisible(a) {
    var r = a.getBoundingClientRect(), s = scroller.getBoundingClientRect();
    if (r.top < s.top + 26) scroller.scrollTop -= (s.top + 26 - r.top);
    else if (r.bottom > s.bottom - 26) scroller.scrollTop += (r.bottom - s.bottom + 26);
  }
  function restoreScroll() {
    var y = 0;
    try { y = parseFloat(sessionStorage.getItem(SCROLL_KEY)) || 0; } catch (e) { /* ignore */ }
    scroller.scrollTop = y;
    var a = $(".sb-group .sb-link.active");
    if (a) keepVisible(a);
  }

  /* ---------------- mobile drawer ---------------- */
  var backdrop = $("#sidebarBackdrop"), lastFocus = null;
  function openMobile() { lastFocus = document.activeElement; side.classList.add("show"); if (backdrop) backdrop.classList.add("show"); document.body.classList.add("sb-lock"); }
  function closeMobile() {
    side.classList.remove("show"); if (backdrop) backdrop.classList.remove("show"); document.body.classList.remove("sb-lock");
    if (lastFocus && lastFocus.focus && lastFocus.id === "hamburgerBtn") lastFocus.focus();
  }
  // Capture phase: take over the hamburger / backdrop from any older sidebar script on the page.
  document.addEventListener("click", function (e) {
    if (e.target.closest("#hamburgerBtn")) { e.stopImmediatePropagation(); e.preventDefault(); side.classList.contains("show") ? closeMobile() : openMobile(); return; }
    if (e.target.closest("#sidebarBackdrop")) { e.stopImmediatePropagation(); closeMobile(); }
  }, true);
  window.addEventListener("resize", function () { if (isDesktop() && side.classList.contains("show")) closeMobile(); });
  var tx = null;
  side.addEventListener("touchstart", function (e) { tx = e.touches[0].clientX; }, { passive: true });
  side.addEventListener("touchmove", function (e) { if (tx !== null && e.touches[0].clientX - tx < -70) { tx = null; closeMobile(); } }, { passive: true });

  /* ---------------- sign out ---------------- */
  $("#sbLogout").addEventListener("click", function () {
    var f = document.getElementById("logoutForm");
    if (f) f.submit(); else location.href = LOGOUT_URL;
  });

  /* ---------------- boot ---------------- */
  markActive();
  applyGroups();
  renderPinned();
  setRail(st.rail);
  restoreScroll();
  updateFades();
  var wired = false;
  function wire() {
    if (wired) return;
    wired = true;
    mirror("notifBellCount", "reminders");
    mirror("taskBellCount", "tasks");
    requestAnimationFrame(function () { document.body.classList.add("sb-ready"); });
  }
  if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", wire); else wire();

  // Small public API, e.g.  LeadFlowSidebar.setBadge('leads', 5)
  window.LeadFlowSidebar = {
    setBadge: setBadge,
    pin: function (k) { if (items[k] && st.pins.indexOf(k) < 0) togglePin(k); },
    unpin: function (k) { if (st.pins.indexOf(k) >= 0) togglePin(k); },
    isPinned: function (k) { return st.pins.indexOf(k) >= 0; },
  };
})();
</script>