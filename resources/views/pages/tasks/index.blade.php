@extends('layouts.app')
@section('title', 'Tasks | LeadFlow')

@section('content')

<style>
  /* ==========================================================================
     Tasks — scoped styles (.tf-*, .td-*, .cl-*)
     Uses the design tokens from assets/css/style.css
     ========================================================================== */

  /* ---- semantic colours: set --c (text/accent), --bg (soft background), --bar (stripe) ---- */
  .st-pending     { --c: var(--info);    --bg: var(--info-bg); }
  .st-in_progress { --c: var(--warning); --bg: var(--warning-bg); }
  .st-on_hold     { --c: #6B7280;        --bg: #EEF1F6; }
  .st-completed   { --c: var(--success); --bg: var(--success-bg); }
  .st-reverted    { --c: var(--danger);  --bg: var(--danger-bg); }
  .tone-all       { --c: var(--primary); --bg: var(--primary-50); }
  .tone-over      { --c: var(--danger);  --bg: var(--danger-bg); }
  .pr-low         { --c: #0E7490; --bg: var(--info-bg);    --bar: #22A6C4; }
  .pr-medium      { --c: var(--warning); --bg: var(--warning-bg); --bar: #F5A524; }
  .pr-high        { --c: #C2410C; --bg: #FFEDD5;           --bar: #F97316; }
  .pr-urgent      { --c: #fff;    --bg: var(--danger);     --bar: var(--danger); }

  .tf-pill { display: inline-flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 700; line-height: 1; padding: 5px 10px; border-radius: 999px; background: var(--bg); color: var(--c); white-space: nowrap; }
  .tf-pill i { font-size: 12px; }

  .tf-status {
    appearance: none; -webkit-appearance: none; -moz-appearance: none;
    border: 0; border-radius: 999px; padding: 6px 28px 6px 12px; font-size: 12px; font-weight: 700; cursor: pointer;
    background-color: var(--bg); color: var(--c);
    background-repeat: no-repeat; background-position: right 10px center; background-size: 11px;
    background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23666' stroke-width='2.5'><polyline points='6 9 12 15 18 9'/></svg>");
    transition: filter .15s;
  }
  .tf-status:hover { filter: brightness(.97); }
  .tf-status:focus-visible { outline: 2px solid var(--primary-light); outline-offset: 2px; }

  /* ---- Header: view switch ---- */
  .tf-seg { display: inline-flex; background: #fff; border: 1px solid var(--border); border-radius: 12px; padding: 3px; gap: 2px; }
  .tf-seg button { border: 0; background: transparent; border-radius: 9px; padding: 7px 14px; font-size: 13px; font-weight: 700; color: var(--text-muted); display: inline-flex; align-items: center; gap: 7px; transition: all .15s; }
  .tf-seg button:hover { color: var(--text); }
  .tf-seg button.active { background: var(--primary); color: #fff; box-shadow: 0 4px 10px -3px rgba(var(--primary-rgb), .5); }

  /* ---- Stat cards ---- */
  .tf-stats { display: grid; grid-template-columns: repeat(5, 1fr); gap: 14px; margin-bottom: 18px; }
  .tf-stat { text-align: left; background: var(--card-bg); border: 1.5px solid var(--border); border-radius: 16px; padding: 15px 16px; display: flex; gap: 13px; align-items: flex-start; box-shadow: var(--shadow-sm); transition: transform .15s, box-shadow .15s, border-color .15s; width: 100%; }
  .tf-stat:hover { transform: translateY(-2px); box-shadow: var(--shadow); }
  .tf-stat.active { border-color: var(--c); box-shadow: 0 0 0 3px color-mix(in srgb, var(--c) 14%, transparent); }
  .tf-stat-ico { width: 42px; height: 42px; border-radius: 12px; background: var(--bg); color: var(--c); display: grid; place-items: center; font-size: 19px; flex-shrink: 0; }
  .tf-stat-body { min-width: 0; flex: 1; }
  .tf-stat-label { font-size: 12px; font-weight: 700; color: var(--text-muted); }
  .tf-stat-value { font-size: 26px; font-weight: 800; line-height: 1.15; color: var(--text); }
  .tf-stat-cap { font-size: 11.5px; color: var(--text-muted); margin-top: 2px; }
  .tf-statbar { height: 5px; border-radius: 5px; background: var(--border); overflow: hidden; margin: 7px 0 4px; }
  .tf-statbar span { display: block; height: 100%; background: var(--success); border-radius: 5px; transition: width .4s; }
  @media (max-width: 1199.98px) { .tf-stats { grid-template-columns: repeat(3, 1fr); } }
  @media (max-width: 767.98px)  { .tf-stats { grid-template-columns: repeat(2, 1fr); gap: 10px; } .tf-stat[data-cat="all"] { grid-column: 1 / -1; } .tf-stat { padding: 12px; } .tf-stat-ico { width: 36px; height: 36px; font-size: 16px; } .tf-stat-value { font-size: 22px; } }

  /* ---- Toolbar ---- */
  .tf-toolbar { background: var(--card-bg); border: 1px solid var(--border); border-radius: 16px; padding: 12px 14px; display: flex; flex-wrap: wrap; gap: 10px; align-items: center; margin-bottom: 14px; box-shadow: var(--shadow-sm); }
  .tf-search { position: relative; flex: 1 1 260px; min-width: 200px; }
  .tf-search > i { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: #9AA0B8; pointer-events: none; }
  .tf-search .form-control { padding-left: 38px !important; padding-right: 36px !important; height: 40px; }
  .tf-search kbd { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); font-size: 11px; font-weight: 700; color: var(--text-muted); background: #fff; border: 1px solid var(--border); border-bottom-width: 2px; border-radius: 6px; padding: 0 6px; font-family: inherit; pointer-events: none; }
  .tf-search .form-control:focus ~ kbd, .tf-search .form-control:not(:placeholder-shown) ~ kbd { display: none; }
  .tf-select { height: 40px; width: auto; font-size: 13px; font-weight: 600; border: 1.5px solid var(--border); border-radius: 10px; background-color: #FBFBFE; padding: 0 32px 0 12px; color: var(--text); }
  .tf-select:focus { border-color: var(--primary-light); box-shadow: 0 0 0 .22rem rgba(var(--primary-rgb), .16); }
  .tf-chips { display: flex; gap: 6px; flex-wrap: wrap; }
  .tf-chip { border: 1.5px solid var(--border); background: #fff; color: var(--text-muted); border-radius: 999px; padding: 6px 12px; font-size: 12.5px; font-weight: 700; transition: all .15s; }
  .tf-chip:hover { border-color: var(--primary-light); color: var(--primary); }
  .tf-chip.active { background: var(--primary-50); border-color: var(--primary-light); color: var(--primary); }
  .tf-clear { border: 0; background: none; color: var(--primary); font-weight: 700; font-size: 12.5px; padding: 6px 4px; margin-left: auto; }
  .tf-clear:hover { text-decoration: underline; }

  /* ---- Category tabs ---- */
  .tf-tabs { display: flex; gap: 2px; overflow-x: auto; border-bottom: 1px solid var(--border); margin-bottom: 14px; scrollbar-width: none; }
  .tf-tabs::-webkit-scrollbar { display: none; }
  .tf-tab { background: none; border: 0; padding: 10px 13px; font-size: 13px; font-weight: 700; color: var(--text-muted); border-bottom: 2px solid transparent; margin-bottom: -1px; white-space: nowrap; display: inline-flex; align-items: center; gap: 7px; }
  .tf-tab:hover { color: var(--text); }
  .tf-tab .n { font-size: 11px; background: var(--bg); color: var(--text-muted); border-radius: 20px; padding: 1px 7px; min-width: 22px; text-align: center; }
  .tf-tab.active { color: var(--primary); border-bottom-color: var(--primary); }
  .tf-tab.active .n { background: var(--primary-50); color: var(--primary); }

  /* ---- Table ---- */
  .tf-table { min-width: 900px; }
  .tf-th-check { width: 46px; }
  .tf-th-act { width: 56px; }
  .tf-row.is-over { box-shadow: inset 3px 0 0 var(--danger); }
  .tf-row.selected { background: var(--primary-50); }
  .tf-row:focus-visible { outline: 2px solid var(--primary-light); outline-offset: -2px; }
  .tf-task { display: flex; gap: 12px; align-items: flex-start; min-width: 0; }
  .tf-tick { width: 21px; height: 21px; border-radius: 50%; border: 2px solid #C9CCE0; background: #fff; flex-shrink: 0; margin-top: 1px; display: grid; place-items: center; padding: 0; color: transparent; transition: all .15s; }
  .tf-tick i { font-size: 11px; }
  .tf-tick:hover { border-color: var(--success); color: var(--success); }
  .is-done .tf-tick { background: var(--success); border-color: var(--success); color: #fff; }
  .tf-name { font-weight: 650; font-size: 13.5px; color: var(--text); overflow-wrap: anywhere; }
  .is-done .tf-name { text-decoration: line-through; color: var(--text-muted); }
  .tf-sub { display: flex; gap: 12px; align-items: center; margin-top: 4px; font-size: 12px; color: var(--text-muted); min-width: 0; }
  .tf-desc { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 280px; }
  .tf-meta { display: inline-flex; gap: 4px; align-items: center; white-space: nowrap; font-weight: 600; }
  .tf-meta.full { color: var(--success); }
  .tf-date { font-size: 13px; font-weight: 600; }
  .tf-rel { font-size: 11.5px; color: var(--text-muted); margin-top: 2px; }
  .tf-due { white-space: nowrap; }
  .tf-table td.text-muted-2 { white-space: nowrap; }
  .tf-due.over .tf-date, .tf-due.over .tf-rel { color: var(--danger); }
  .tf-due.over .tf-rel { font-weight: 700; }
  .tf-due.today .tf-rel { color: var(--warning); font-weight: 700; }
  .tf-due.soon .tf-rel { color: var(--gold); font-weight: 600; }
  .tf-due.none .tf-date { color: #B6BAD0; font-weight: 500; }
  .tf-person { display: flex; align-items: center; gap: 8px; font-size: 13px; min-width: 0; }
  .tf-person span.nm { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .tf-act-btn { width: 32px; height: 32px; padding: 0; border-radius: 9px; border: 1px solid var(--border); background: #fff; color: var(--text-muted); display: inline-grid; place-items: center; }
  .tf-act-btn:hover { background: var(--bg); color: var(--text); }
  .tf-flash > td { animation: tfFlash 1.8s ease; }
  @keyframes tfFlash { 0% { background: var(--primary-100); } 100% { background: transparent; } }

  .tf-empty { text-align: center; padding: 56px 20px; color: var(--text-muted); }
  .tf-empty .ico { width: 72px; height: 72px; margin: 0 auto 14px; border-radius: 22px; background: var(--primary-50); color: var(--primary); display: grid; place-items: center; font-size: 30px; }
  .tf-empty h6 { color: var(--text); font-size: 15px; margin-bottom: 4px; }
  .tf-empty p { font-size: 13px; margin-bottom: 14px; }
  .tf-empty-sm { text-align: center; padding: 22px 10px; color: var(--text-muted); font-size: 12.5px; }
  .tf-empty-sm i { display: block; font-size: 26px; color: #C9CCE0; margin-bottom: 6px; }

  .tf-psize { display: inline-flex; align-items: center; gap: 8px; font-size: 12.5px; color: var(--text-muted); margin: 0; }
  .tf-psize .tf-select { height: 32px; font-size: 12.5px; padding-right: 28px; }

  /* ---- Skeleton ---- */
  .tf-skel { display: block; border-radius: 8px; background: linear-gradient(90deg, #ECEEF7 25%, #F6F7FC 37%, #ECEEF7 63%); background-size: 400% 100%; animation: tfSkel 1.4s ease infinite; }
  @keyframes tfSkel { 0% { background-position: 100% 50%; } 100% { background-position: 0 50%; } }

  /* ---- Board ---- */
  .tf-board { display: flex; gap: 14px; overflow-x: auto; padding: 2px 2px 12px; align-items: flex-start; scroll-snap-type: x proximity; }
  .tf-col { flex: 1 0 272px; max-width: 380px; background: #EFF0F8; border: 1.5px solid transparent; border-radius: 16px; padding: 10px; display: flex; flex-direction: column; max-height: calc(100vh - 290px); min-height: 220px; scroll-snap-align: start; transition: border-color .15s, background .15s; }
  .tf-col.drag-over { border-color: var(--primary-light); border-style: dashed; background: var(--primary-50); }
  .tf-col-head { display: flex; align-items: center; gap: 8px; padding: 4px 6px 10px; font-weight: 800; font-size: 13px; color: var(--text); }
  .tf-dot { width: 9px; height: 9px; border-radius: 50%; background: var(--c); }
  .tf-col-count { margin-left: auto; font-size: 11.5px; font-weight: 700; color: var(--c); background: var(--bg); border-radius: 20px; padding: 2px 9px; }
  .tf-col-body { overflow-y: auto; flex: 1; min-height: 60px; padding: 2px; }
  .tf-col-empty { text-align: center; color: #A7ABC2; font-size: 12.5px; padding: 26px 8px; border: 1.5px dashed #D7D9EA; border-radius: 12px; }
  .tf-col-empty i { display: block; font-size: 22px; margin-bottom: 4px; }
  .tf-kcard { background: #fff; border: 1px solid var(--border); border-left: 4px solid var(--bar); border-radius: 12px; padding: 12px 12px 10px; margin-bottom: 10px; cursor: grab; box-shadow: var(--shadow-sm); transition: box-shadow .15s, transform .15s; }
  .tf-kcard:hover { box-shadow: var(--shadow); transform: translateY(-1px); }
  .tf-kcard:focus-visible { outline: 2px solid var(--primary-light); outline-offset: 2px; }
  .tf-kcard.dragging { opacity: .45; transform: rotate(1.5deg); }
  .tf-kcard.tf-flash { animation: tfFlashCard 1.8s ease; }
  @keyframes tfFlashCard { 0%, 40% { box-shadow: 0 0 0 4px rgba(var(--primary-rgb), .35); } 100% { box-shadow: var(--shadow-sm); } }
  .tf-kcard-top { display: flex; gap: 6px; align-items: flex-start; }
  .tf-kname { font-weight: 650; font-size: 13.5px; line-height: 1.35; flex: 1; overflow-wrap: anywhere; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
  .tf-kcard.is-done .tf-kname { text-decoration: line-through; color: var(--text-muted); }
  .tf-kmore { border: 0; background: transparent; color: var(--text-muted); width: 26px; height: 26px; border-radius: 8px; flex-shrink: 0; display: grid; place-items: center; margin: -3px -4px 0 0; }
  .tf-kmore:hover { background: var(--bg); color: var(--text); }
  .tf-kmeta { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 9px; }
  .tf-kmeta .tf-pill { padding: 4px 8px; font-size: 11.5px; }
  .tf-kdue { display: inline-flex; gap: 5px; align-items: center; font-size: 11.5px; font-weight: 700; color: var(--text-muted); background: var(--bg-soft, #F3F4FA); border-radius: 999px; padding: 4px 8px; }
  .tf-kdue.over { background: var(--danger-bg); color: var(--danger); }
  .tf-kdue.today { background: var(--warning-bg); color: var(--warning); }
  .tf-kdue.soon { background: var(--gold-bg); color: var(--gold); }
  .tf-kprog { display: flex; align-items: center; gap: 8px; margin-top: 10px; font-size: 11.5px; font-weight: 700; color: var(--text-muted); }
  .tf-kprog .cl-progress { flex: 1; }
  .tf-kfoot { display: flex; align-items: center; justify-content: space-between; margin-top: 10px; }
  .tf-kicons { display: flex; gap: 10px; font-size: 12px; color: var(--text-muted); font-weight: 600; }
  .tf-kicons span { display: inline-flex; gap: 4px; align-items: center; }

  /* ---- Bulk bar ---- */
  .tf-bulk { position: fixed; bottom: 24px; left: calc(248px + (100vw - 248px) / 2); transform: translate(-50%, 24px); opacity: 0; pointer-events: none; z-index: 1030; background: #15192E; color: #fff; border-radius: 16px; padding: 10px 12px 10px 18px; display: flex; align-items: center; gap: 8px; box-shadow: var(--shadow-lg); transition: opacity .2s, transform .2s; max-width: calc(100vw - 24px); flex-wrap: wrap; justify-content: center; }
  .tf-bulk.show { opacity: 1; pointer-events: auto; transform: translate(-50%, 0); }
  .tf-bulk-count { font-size: 13px; font-weight: 600; margin-right: 6px; white-space: nowrap; }
  .tf-bulk-count b { font-weight: 800; }
  .tf-bulk .btn-bulk { background: rgba(255,255,255,.1); color: #fff; border: 0; border-radius: 10px; font-size: 12.5px; font-weight: 700; padding: 8px 13px; display: inline-flex; align-items: center; gap: 6px; }
  .tf-bulk .btn-bulk:hover { background: rgba(255,255,255,.18); }
  .tf-bulk .btn-bulk.danger { background: rgba(220, 38, 38, .22); color: #FCA5A5; }
  .tf-bulk .btn-bulk.danger:hover { background: rgba(220, 38, 38, .38); }
  .tf-bulk .tf-bulk-x { background: transparent; color: #9BA0BD; border: 0; width: 32px; height: 32px; border-radius: 9px; }
  .tf-bulk .tf-bulk-x:hover { background: rgba(255,255,255,.1); color: #fff; }
  .tf-bulk .dropdown-menu { max-height: 280px; overflow-y: auto; }
  @media (max-width: 991.98px) { .tf-bulk { left: 50%; } }

  /* ---- Add / edit modal ---- */
  .tf-prio-group { display: flex; gap: 8px; flex-wrap: wrap; }
  .tf-prio { display: inline-flex; align-items: center; gap: 6px; border: 1.5px solid var(--border); background: #fff; color: var(--c); border-radius: 10px; padding: 8px 13px; font-size: 13px; font-weight: 700; cursor: pointer; margin: 0; transition: all .15s; text-transform: none; letter-spacing: 0; }
  .tf-prio.pr-urgent { color: var(--danger); }
  .tf-prio:hover { border-color: var(--bar); }
  .btn-check:checked + .tf-prio { background: var(--bar); border-color: var(--bar); color: #fff; box-shadow: 0 6px 14px -5px var(--bar); }
  .btn-check:focus-visible + .tf-prio { outline: 2px solid var(--primary-light); outline-offset: 2px; }
  .tf-quickdates { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 8px; }
  .tf-quickdates button { border: 1px solid var(--border); background: #fff; border-radius: 999px; font-size: 12px; font-weight: 700; color: var(--text-muted); padding: 4px 11px; }
  .tf-quickdates button:hover { border-color: var(--primary-light); color: var(--primary); }
  .tf-field-err { color: var(--danger); font-size: 12px; font-weight: 600; margin-top: 5px; min-height: 0; }
  .tf-field-hint { color: var(--warning); font-size: 12px; font-weight: 600; margin-top: 5px; }
  .tf-has-err .form-control, .tf-has-err .form-select { border-color: var(--danger) !important; }

  /* ---- Detail panel ---- */
  .tf-oc { --bs-offcanvas-width: 540px; max-width: 100vw; }
  .tf-oc .offcanvas-body { padding-top: 14px; }
  .tf-iconbtn { width: 34px; height: 34px; border-radius: 10px; border: 1px solid var(--border); background: #fff; color: var(--text-muted); display: grid; place-items: center; box-shadow: var(--shadow-sm); transition: all .15s; }
  .tf-iconbtn:hover { background: var(--primary-50); color: var(--primary); border-color: var(--primary-100); }
  .tf-iconbtn.danger:hover { background: var(--danger-bg); color: var(--danger); border-color: var(--danger-bg); }
  .td-title { font-size: 19px; font-weight: 800; line-height: 1.3; letter-spacing: -.01em; overflow-wrap: anywhere; }
  .td-badges { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; margin: 12px 0; }
  .td-desc { font-size: 13.5px; line-height: 1.65; color: #4B5068; white-space: pre-wrap; overflow-wrap: anywhere; }
  .td-desc a, .comment-text a { color: var(--primary); text-decoration: underline; }
  .td-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin: 16px 0; }
  .td-meta .m { background: var(--bg); border-radius: 12px; padding: 10px 12px; min-width: 0; }
  .td-meta .k { font-size: 10.5px; text-transform: uppercase; letter-spacing: .06em; color: var(--text-muted); font-weight: 800; margin-bottom: 5px; }
  .td-meta .v { font-weight: 650; font-size: 13px; display: flex; gap: 8px; align-items: center; flex-wrap: wrap; min-width: 0; }
  .td-rel { font-size: 11.5px; font-weight: 700; color: var(--text-muted); }
  .td-rel.over, .td-due.over { color: var(--danger); }
  .td-rel.today { color: var(--warning); }
  .td-rel.soon { color: var(--gold); }
  .td-banner { display: flex; align-items: center; gap: 12px; background: var(--success-bg); border: 1px solid #BFE8CD; color: #14532D; border-radius: 12px; padding: 10px 12px; margin-bottom: 14px; font-size: 13px; }
  .td-banner i { font-size: 20px; color: var(--success); }
  .td-banner > div { flex: 1; display: flex; flex-direction: column; line-height: 1.35; }
  .td-banner span { font-size: 12px; opacity: .8; }
  .td-tabs { display: flex; gap: 2px; border-bottom: 1px solid var(--border); margin-bottom: 14px; overflow-x: auto; scrollbar-width: none; }
  .td-tab { background: none; border: 0; padding: 9px 10px; font-size: 13px; font-weight: 700; color: var(--text-muted); border-bottom: 2px solid transparent; margin-bottom: -1px; white-space: nowrap; display: inline-flex; gap: 6px; align-items: center; }
  .td-tab .n { font-size: 11px; background: var(--bg); border-radius: 20px; padding: 1px 7px; }
  .td-tab.active { color: var(--primary); border-bottom-color: var(--primary); }
  .td-tab.active .n { background: var(--primary-50); }
  .td-pane { display: none; }
  .td-pane.active { display: block; animation: tfIn .25s ease; }
  @keyframes tfIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }

  /* checklist */
  .cl-head { display: flex; justify-content: space-between; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 6px; }
  .cl-head b { color: var(--text); }
  .cl-progress { height: 6px; border-radius: 6px; background: var(--border); overflow: hidden; }
  .cl-progress > span { display: block; height: 100%; background: var(--primary); border-radius: 6px; transition: width .3s; }
  .cl-progress > span.full { background: var(--success); }
  #clList { margin: 10px 0 8px; }
  .cl-item { display: flex; align-items: center; gap: 10px; padding: 8px 6px; border-radius: 10px; }
  .cl-item:hover { background: var(--bg); }
  .cl-box { width: 20px; height: 20px; border-radius: 6px; border: 2px solid #C9CCE0; background: #fff; display: grid; place-items: center; color: transparent; padding: 0; flex-shrink: 0; transition: all .15s; }
  .cl-box i { font-size: 11px; }
  .cl-box:hover { border-color: var(--primary-light); }
  .cl-item.done .cl-box { background: var(--primary); border-color: var(--primary); color: #fff; }
  .cl-title { flex: 1; font-size: 13.5px; overflow-wrap: anywhere; }
  .cl-item.done .cl-title { text-decoration: line-through; color: var(--text-muted); }
  .cl-del { border: 0; background: transparent; color: var(--text-muted); width: 26px; height: 26px; border-radius: 8px; opacity: 0; transition: opacity .15s; }
  .cl-item:hover .cl-del, .cl-del:focus-visible { opacity: 1; }
  .cl-del:hover { background: var(--danger-bg); color: var(--danger); }
  @media (hover: none) { .cl-del { opacity: 1; } }
  .cl-add { display: flex; align-items: center; gap: 10px; border: 1.5px dashed var(--border); border-radius: 12px; padding: 4px 12px; transition: border-color .15s; }
  .cl-add:focus-within { border-color: var(--primary-light); border-style: solid; }
  .cl-add i { color: var(--text-muted); }
  .cl-add input { flex: 1; border: 0; outline: 0; background: transparent; padding: 8px 0; font-size: 13.5px; }

  /* comments */
  .comment-item { display: flex; gap: 10px; margin-bottom: 14px; }
  .comment-body { background: var(--bg); border-radius: 12px 12px 12px 4px; padding: 9px 12px; flex: 1; min-width: 0; }
  .comment-head { display: flex; justify-content: space-between; gap: 8px; align-items: baseline; }
  .comment-author { font-weight: 700; font-size: 12.5px; }
  .comment-time { font-size: 11px; color: var(--text-muted); white-space: nowrap; }
  .comment-text { font-size: 13px; margin-top: 3px; white-space: pre-wrap; overflow-wrap: anywhere; }
  #commentForm { margin-bottom: 16px; }
  #commentText { resize: vertical; min-height: 64px; }

  /* attachments */
  .tf-drop { display: block; border: 1.5px dashed #C9CCE0; border-radius: 14px; padding: 18px 12px; text-align: center; font-size: 13px; color: var(--text-muted); cursor: pointer; margin-bottom: 12px; transition: all .15s; }
  .tf-drop i { font-size: 26px; color: var(--primary); display: block; margin-bottom: 4px; }
  .tf-drop b { color: var(--primary); }
  .tf-drop small { display: block; font-size: 11.5px; margin-top: 2px; }
  .tf-drop:hover, .tf-drop.dragover { border-color: var(--primary); background: var(--primary-50); }
  .up-item { background: var(--bg); border-radius: 10px; padding: 9px 12px; margin-bottom: 8px; }
  .up-name { font-size: 12.5px; font-weight: 600; margin-bottom: 6px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .attach-item { display: flex; align-items: center; gap: 12px; padding: 9px 4px; border-bottom: 1px dashed var(--border); }
  .attach-item:last-child { border-bottom: 0; }
  .attach-icon { width: 38px; height: 38px; border-radius: 10px; background: var(--primary-50); color: var(--primary); display: grid; place-items: center; font-size: 17px; flex-shrink: 0; overflow: hidden; }
  .attach-icon img { width: 100%; height: 100%; object-fit: cover; }
  .attach-name { font-size: 13px; font-weight: 650; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: var(--text); }
  .attach-meta { font-size: 11.5px; color: var(--text-muted); }
  .attach-del.confirm { background: var(--danger); color: #fff; border-color: var(--danger); width: auto; padding: 0 10px; font-size: 12px; font-weight: 700; }

  /* activity */
  .activity-item { display: flex; gap: 12px; padding-bottom: 16px; position: relative; }
  .activity-item:not(:last-child)::before { content: ""; position: absolute; left: 13px; top: 28px; bottom: 0; width: 2px; background: var(--border); }
  .activity-ico { width: 28px; height: 28px; border-radius: 50%; background: var(--primary-50); color: var(--primary); display: grid; place-items: center; font-size: 13px; flex-shrink: 0; z-index: 1; }
  .activity-text { font-size: 13px; line-height: 1.45; }
  .activity-time { font-size: 11.5px; color: var(--text-muted); margin-top: 1px; }

  @media (max-width: 575.98px) {
    .td-meta { grid-template-columns: 1fr; }
    .tf-toolbar { padding: 10px; }
    .tf-seg button span { display: none; }
    .tf-seg button { padding: 8px 12px; }
    .tf-col { flex-basis: 84vw; }
    .tf-search kbd { display: none; }
    .tf-wrap .page-header .page-header-actions { width: 100%; flex-wrap: nowrap !important; }
    .tf-wrap .page-header .page-header-actions .btn { width: auto; }
    .tf-wrap .page-header .page-header-actions #openAddTaskBtn { flex: 1; }
  }
  @media (prefers-reduced-motion: reduce) {
    .tf-wrap *, .tf-wrap *::before, .tf-wrap *::after, .tf-oc *, .tf-bulk { animation: none !important; transition: none !important; }
  }
</style>

<div class="page-content">
  <section class="page-section active tf-wrap" id="page-tasks">

    <div class="page-header">
      <div>
        <span class="breadcrumb-eyebrow">Workspace</span>
        <h1>Tasks</h1>
        <p>@if($isSuperAdmin) Assign, track and follow up on work across the team. @else Tasks assigned to you — update status as you make progress. @endif</p>
      </div>
      <div class="page-header-actions d-flex gap-2 flex-wrap align-items-center">
        <div class="tf-seg" role="group" aria-label="Change view">
          <button type="button" data-view="list" class="active" title="List view (L)"><i class="bi bi-list-ul"></i><span>List</span></button>
          <button type="button" data-view="board" title="Board view (B)"><i class="bi bi-kanban"></i><span>Board</span></button>
        </div>
        <button class="btn btn-outline-secondary" id="exportBtn" title="Download the filtered tasks as CSV" aria-label="Export CSV"><i class="bi bi-download"></i><span class="d-none d-sm-inline ms-1">Export</span></button>
        @if($isSuperAdmin)
        <button class="btn btn-primary" id="openAddTaskBtn" title="New task (N)"><i class="bi bi-plus-lg me-1"></i>Add Task</button>
        @endif
      </div>
    </div>

    <!-- Stat cards (click to filter) -->
    <div class="tf-stats" id="tfStats"></div>

    <!-- Toolbar -->
    <div class="tf-toolbar">
      <div class="tf-search">
        <i class="bi bi-search"></i>
        <input type="text" class="form-control" id="filterSearch" placeholder="Search tasks or people…" autocomplete="off" aria-label="Search tasks">
        <kbd>/</kbd>
      </div>
      <select class="form-select tf-select" id="filterPriority" aria-label="Filter by priority"></select>
      @if($isSuperAdmin)
      <select class="form-select tf-select" id="filterAssignee" aria-label="Filter by employee"></select>
      @endif
      <select class="form-select tf-select" id="sortSelect" aria-label="Sort tasks">
        <option value="newest">Newest first</option>
        <option value="oldest">Oldest first</option>
        <option value="due">Due date · soonest</option>
        <option value="priority">Priority · highest</option>
        <option value="name">Name · A–Z</option>
      </select>
      <div class="tf-chips" id="tfChips">
        @if($isSuperAdmin)<button type="button" class="tf-chip" data-quick="mine"><i class="bi bi-person me-1"></i>Assigned to me</button>@endif
        <button type="button" class="tf-chip" data-quick="today"><i class="bi bi-calendar-check me-1"></i>Due today</button>
        <button type="button" class="tf-chip" data-quick="week"><i class="bi bi-calendar-week me-1"></i>Next 7 days</button>
      </div>
      <button type="button" class="tf-clear d-none" id="clearFiltersBtn"><i class="bi bi-x-circle me-1"></i>Clear filters</button>
    </div>

    <!-- Category tabs (list view) -->
    <div class="tf-tabs" id="tfTabs" role="tablist"></div>

    <!-- LIST VIEW -->
    <div class="section-card" id="tfListWrap">
      <div class="table-wrap">
        <table class="crm-table tf-table">
          <thead>
            <tr>
              @if($isSuperAdmin)<th class="tf-th-check"><input type="checkbox" class="form-check-input" id="selectAllCheck" aria-label="Select all tasks on this page"></th>@endif
              <th>Task</th>
              <th>Priority</th>
              <th>Due</th>
              <th>Status</th>
              <th>Assigned to</th>
              <th class="d-none d-xl-table-cell">Assigned by</th>
              <th class="tf-th-act"></th>
            </tr>
          </thead>
          <tbody id="tasksTbody"></tbody>
        </table>
      </div>
      <div class="crm-pagination">
        <span class="page-info" id="pageInfo"></span>
        <div class="d-flex align-items-center gap-3 flex-wrap">
          <label class="tf-psize">Rows
            <select class="form-select tf-select" id="pageSizeSelect" aria-label="Rows per page">
              <option value="10">10</option><option value="20">20</option><option value="50">50</option>
            </select>
          </label>
          <div class="d-flex gap-1" id="pageButtons"></div>
        </div>
      </div>
    </div>

    <!-- BOARD VIEW -->
    <div id="tfBoardWrap" class="d-none">
      <div class="tf-board" id="tfBoard"></div>
    </div>
  </section>
</div>

<!-- Floating bulk-action bar -->
@if($isSuperAdmin)
<div class="tf-bulk" id="bulkBar" aria-live="polite">
  <span class="tf-bulk-count"><b id="bulkCount">0</b> selected</span>
  <div class="dropup">
    <button type="button" class="btn-bulk" data-bs-toggle="dropdown"><i class="bi bi-arrow-repeat"></i>Set status</button>
    <ul class="dropdown-menu" id="bulkStatusMenu"></ul>
  </div>
  <div class="dropup">
    <button type="button" class="btn-bulk" data-bs-toggle="dropdown"><i class="bi bi-person-plus"></i>Assign to</button>
    <ul class="dropdown-menu" id="bulkAssignMenu"></ul>
  </div>
  <button type="button" class="btn-bulk danger" id="bulkDeleteBtn"><i class="bi bi-trash"></i>Delete</button>
  <button type="button" class="tf-bulk-x" id="bulkClearBtn" aria-label="Clear selection"><i class="bi bi-x-lg"></i></button>
</div>
@endif

<!-- ===================== ADD / EDIT TASK MODAL ===================== -->
@if($isSuperAdmin)
<div class="modal fade" id="taskModal" tabindex="-1" aria-labelledby="taskModalTitle">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <form id="taskForm" novalidate>
        <input type="hidden" id="taskId">
        <div class="modal-header">
          <h5 class="modal-title" id="taskModalTitle">Add new task</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12" data-field="task_name">
              <label class="form-label" for="taskName">Task name</label>
              <input type="text" class="form-control" id="taskName" placeholder="e.g. Follow up with vendor about pending invoice" maxlength="255" autocomplete="off">
              <div class="tf-field-err" id="err_task_name"></div>
            </div>
            <div class="col-12" data-field="description">
              <label class="form-label" for="taskDescription">Description</label>
              <textarea class="form-control" id="taskDescription" rows="3" placeholder="What needs to be done? Add links, context, expected result…"></textarea>
              <div class="tf-field-err" id="err_description"></div>
            </div>
            <div class="col-12" data-field="priority">
              <label class="form-label">Priority</label>
              <div class="tf-prio-group" id="taskPriorityGroup" role="radiogroup" aria-label="Priority"></div>
              <div class="tf-field-err" id="err_priority"></div>
            </div>
            <div class="col-md-6" data-field="assigned_to">
              <label class="form-label" for="taskAssignedTo">Assign to</label>
              <select class="form-select" id="taskAssignedTo"></select>
              <div class="tf-field-err" id="err_assigned_to"></div>
            </div>
            <div class="col-md-6" data-field="due_date">
              <label class="form-label" for="taskDueDate">Due date</label>
              <input type="date" class="form-control" id="taskDueDate">
              <div class="tf-quickdates">
                <button type="button" data-add="0">Today</button>
                <button type="button" data-add="1">Tomorrow</button>
                <button type="button" data-add="7">Next week</button>
                <button type="button" data-add="clear">Clear</button>
              </div>
              <div class="tf-field-hint d-none" id="dueHint"><i class="bi bi-exclamation-triangle me-1"></i>This date is in the past.</div>
              <div class="tf-field-err" id="err_due_date"></div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <span class="me-auto text-muted-2 fs-12 d-none d-md-inline"><kbd>Ctrl</kbd> + <kbd>Enter</kbd> to save</span>
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary" id="taskSubmitBtn">Add task</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif

<!-- ===================== TASK DETAIL OFFCANVAS ===================== -->
<div class="offcanvas offcanvas-end tf-oc" tabindex="-1" id="taskDetailOffcanvas" aria-labelledby="tdPanelTitle">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="tdPanelTitle">Task details</h5>
    <div class="d-flex align-items-center gap-2 ms-auto ps-3">
      @if($isSuperAdmin)
      <button type="button" class="tf-iconbtn" id="tdEditBtn" title="Edit task" aria-label="Edit task"><i class="bi bi-pencil"></i></button>
      <button type="button" class="tf-iconbtn" id="tdDupBtn" title="Duplicate task" aria-label="Duplicate task"><i class="bi bi-copy"></i></button>
      <button type="button" class="tf-iconbtn danger" id="tdDelBtn" title="Delete task" aria-label="Delete task"><i class="bi bi-trash"></i></button>
      @endif
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
  </div>
  <div class="offcanvas-body">
    <div class="td-title" id="tdName"></div>
    <div class="td-badges">
      <span id="tdPriorityBadge"></span>
      <select class="tf-status" id="tdStatusSelect" aria-label="Change status"></select>
    </div>
    <div class="td-desc" id="tdDescription"></div>
    <div class="td-meta" id="tdMeta"></div>
    <div id="tdBanner"></div>

    <div class="td-tabs" id="tdTabs" role="tablist">
      <button type="button" class="td-tab" data-tab="checklist"><i class="bi bi-check2-square"></i>Checklist <span class="n" id="cntChecklist">0</span></button>
      <button type="button" class="td-tab" data-tab="comments"><i class="bi bi-chat-left-text"></i>Comments <span class="n" id="cntComments">0</span></button>
      <button type="button" class="td-tab" data-tab="files"><i class="bi bi-paperclip"></i>Files <span class="n" id="cntFiles">0</span></button>
      <button type="button" class="td-tab" data-tab="activity"><i class="bi bi-clock-history"></i>Activity</button>
    </div>

    <div class="td-pane" id="tdPaneChecklist">
      <div id="clProgress"></div>
      <div id="clList"></div>
      <form class="cl-add" id="clForm" autocomplete="off">
        <i class="bi bi-plus-lg"></i>
        <input type="text" id="clInput" maxlength="255" placeholder="Add a step and press Enter" aria-label="New checklist item">
      </form>
    </div>

    <div class="td-pane" id="tdPaneComments">
      <form id="commentForm">
        <textarea class="form-control mb-2" id="commentText" rows="2" maxlength="5000" placeholder="Write a comment…  (Ctrl + Enter to send)"></textarea>
        <button type="submit" class="btn btn-primary btn-sm w-100" id="commentBtn">Post comment</button>
      </form>
      <div id="commentsList"></div>
    </div>

    <div class="td-pane" id="tdPaneFiles">
      <label class="tf-drop" id="dropZone">
        <input type="file" id="fileInput" multiple hidden>
        <i class="bi bi-cloud-arrow-up"></i>
        <span><b>Click to upload</b> or drag &amp; drop</span>
        <small>Up to 10 MB per file</small>
      </label>
      <div id="uploadQueue"></div>
      <div id="attachmentsList"></div>
    </div>

    <div class="td-pane" id="tdPaneActivity">
      <div id="activityList"></div>
    </div>
  </div>
</div>

<!-- ===================== DELETE CONFIRM MODAL ===================== -->
@if($isSuperAdmin)
<div class="modal fade" id="deleteConfirmModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-content-compact">
    <div class="modal-content modal-content-danger">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteTitle">Delete task?</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="mb-0" id="deleteText">This will permanently delete the task, its comments, checklist and attachments. This can't be undone.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="confirmDeleteBtn">Delete</button>
      </div>
    </div>
  </div>
</div>
@endif

<script>
  window.TASK_PAGE_DATA = {
    isSuperAdmin: @json($isSuperAdmin),
    admins: @json($admins),
    priorities: @json($priorities),
    statuses: @json($statuses),
    csrfToken: '{{ csrf_token() }}',
    routes: {
      data: '{{ route('tasks.data') }}',
      store: '{{ route('tasks.store') }}',
      update: '{{ url('admin/tasks') }}/__ID__',
      destroy: '{{ url('admin/tasks') }}/__ID__',
      show: '{{ url('admin/tasks') }}/__ID__',
      updateStatus: '{{ url('admin/tasks') }}/__ID__/status',
      duplicate: '{{ url('admin/tasks') }}/__ID__/duplicate',
      bulkDelete: '{{ route('tasks.bulkDestroy') }}',
      bulkStatus: '{{ url('admin/tasks/bulk-status') }}',
      bulkAssign: '{{ url('admin/tasks/bulk-assign') }}',
      checklistStore: '{{ url('admin/tasks/checklist') }}',
      checklistItem: '{{ url('admin/tasks/checklist') }}/__ID__',
      commentStore: '{{ route('tasks.comments.store') }}',
      attachmentStore: '{{ route('tasks.attachments.store') }}',
      attachmentDestroy: '{{ url('admin/tasks/attachments') }}/__ID__',
    }
  };

  (function () {
    "use strict";

    const CFG = window.TASK_PAGE_DATA;
    const SA = !!CFG.isSuperAdmin;
    const $ = (s, r) => (r || document).querySelector(s);
    const $$ = (s, r) => Array.from((r || document).querySelectorAll(s));
    const url = (tpl, id) => tpl.replace("__ID__", id);

    const PRIORITIES = CFG.priorities && CFG.priorities.length ? CFG.priorities : ["low", "medium", "high", "urgent"];
    const STATUSES = CFG.statuses && CFG.statuses.length ? CFG.statuses : ["pending", "in_progress", "completed", "reverted", "on_hold"];
    const FLOW = ["pending", "in_progress", "on_hold", "completed", "reverted"];
    const BOARD_ORDER = FLOW.filter(s => STATUSES.includes(s)).concat(STATUSES.filter(s => !FLOW.includes(s)));
    const PR_ICON = { low: "bi-arrow-down", medium: "bi-dash-lg", high: "bi-arrow-up", urgent: "bi-lightning-charge-fill" };
    const ST_ICON = { pending: "bi-hourglass-split", in_progress: "bi-arrow-repeat", on_hold: "bi-pause-circle", completed: "bi-check2-circle", reverted: "bi-arrow-counterclockwise" };
    const ACT_ICON = { created: "bi-plus-circle", assigned: "bi-person-check", updated: "bi-pencil", status_changed: "bi-arrow-repeat", commented: "bi-chat-left-text", attachment_added: "bi-paperclip", attachment_removed: "bi-paperclip", checklist_added: "bi-check2-square", checklist_completed: "bi-check2-all" };
    const AVATAR_COLORS = ["#4338CA", "#0891B2", "#B45309", "#16A34A", "#DC2626", "#6D28D9", "#0D9488"];
    const MS_DAY = 86400000;
    const MAX_UPLOAD = 10 * 1024 * 1024;

    /* ------------------------------------------------------------------ *
     *  State
     * ------------------------------------------------------------------ */
    const store = {
      get(k, d) { try { const v = localStorage.getItem(k); return v === null ? d : v; } catch (e) { return d; } },
      set(k, v) { try { localStorage.setItem(k, v); } catch (e) { /* storage blocked */ }
      }
    };

    let tasks = [];
    let me = null;
    let loaded = false;
    let dragId = null;
    let flashId = null;
    let pendingDelete = [];
    const selected = new Set();
    const detail = { id: null, task: null, tab: null };

    const state = {
      view: store.get("tf_view", "list") === "board" ? "board" : "list",
      pageSize: [10, 20, 50].includes(+store.get("tf_psize", 10)) ? +store.get("tf_psize", 10) : 10,
      sort: store.get("tf_sort", "newest"),
      category: "all",
      search: "",
      priority: "",
      assignee: "",
      quick: "",
      page: 1,
    };

    /* ------------------------------------------------------------------ *
     *  Small helpers
     * ------------------------------------------------------------------ */
    function esc(v) {
      return String(v == null ? "" : v).replace(/[&<>"']/g, c => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));
    }
    function linkify(escaped) {
      return escaped.replace(/(https?:\/\/[^\s<]+)/g, '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>');
    }
    const cap = s => String(s || "").charAt(0).toUpperCase() + String(s || "").slice(1);
    const label = s => String(s || "").split("_").map(cap).join(" ");
    const byId = id => tasks.find(t => t.id === id);
    const initials = n => (n || "?").trim().split(/\s+/).slice(0, 2).map(p => p[0]).join("").toUpperCase();
    function colorFor(name) { let h = 0; for (let i = 0; i < (name || "").length; i++) h = name.charCodeAt(i) + ((h << 5) - h); return AVATAR_COLORS[Math.abs(h) % AVATAR_COLORS.length]; }
    function avatar(name, size) {
      const cls = "avatar" + (size === "sm" ? " avatar-sm" : size === "lg" ? " avatar-lg" : "");
      return `<span class="${cls}" style="background:${colorFor(name)}" title="${esc(name)}">${esc(initials(name))}</span>`;
    }
    function prioPill(p) { return `<span class="tf-pill pr-${esc(p)}"><i class="bi ${PR_ICON[p] || "bi-dash-lg"}"></i>${esc(cap(p))}</span>`; }

    function startOfToday() { const d = new Date(); d.setHours(0, 0, 0, 0); return d; }
    function parseYmd(v) {
      if (!v) return null;
      const m = /^(\d{4})-(\d{2})-(\d{2})/.exec(String(v));
      if (m) return new Date(+m[1], +m[2] - 1, +m[3]);
      const d = new Date(v);
      return isNaN(d) ? null : new Date(d.getFullYear(), d.getMonth(), d.getDate());
    }
    function ymd(d) { return d.getFullYear() + "-" + String(d.getMonth() + 1).padStart(2, "0") + "-" + String(d.getDate()).padStart(2, "0"); }
    function diffDays(t) { const d = parseYmd(t.due_date); return d ? Math.round((d - startOfToday()) / MS_DAY) : null; }
    function isOverdue(t) { const n = diffDays(t); return n !== null && n < 0 && t.status !== "completed"; }
    function fmtDate(d) {
      if (!d) return "";
      const dt = d instanceof Date ? d : parseYmd(d);
      if (!dt) return "";
      return dt.toLocaleDateString(undefined, { day: "numeric", month: "short", year: dt.getFullYear() !== new Date().getFullYear() ? "numeric" : undefined });
    }
    function fullDate(v) {
      const d = new Date(v);
      return isNaN(d) ? "" : d.toLocaleString(undefined, { day: "numeric", month: "short", year: "numeric", hour: "numeric", minute: "2-digit" });
    }
    function timeAgo(v) {
      const d = new Date(v);
      if (isNaN(d)) return "";
      const s = Math.floor((Date.now() - d) / 1000);
      if (s < 45) return "just now";
      const m = Math.floor(s / 60); if (m < 60) return m + "m ago";
      const h = Math.floor(m / 60); if (h < 24) return h + "h ago";
      const dd = Math.floor(h / 24); if (dd < 7) return dd + "d ago";
      return d.toLocaleDateString(undefined, { day: "numeric", month: "short", year: "numeric" });
    }
    function dueInfo(t) {
      const n = diffDays(t);
      if (n === null) return { label: "No due date", rel: "", cls: "none" };
      const lab = fmtDate(t.due_date);
      if (t.status === "completed") return { label: lab, rel: "", cls: "done" };
      if (n < 0) return { label: lab, rel: n === -1 ? "Yesterday" : (-n) + " days overdue", cls: "over" };
      if (n === 0) return { label: lab, rel: "Due today", cls: "today" };
      if (n === 1) return { label: lab, rel: "Tomorrow", cls: "soon" };
      return { label: lab, rel: "In " + n + " days", cls: n <= 3 ? "soon" : "" };
    }

    function toast(type, message) {
      const icons = { success: "bi-check-circle-fill", error: "bi-exclamation-octagon-fill", info: "bi-info-circle-fill" };
      const colors = { success: "var(--success)", error: "var(--danger)", info: "var(--primary)" };
      const stack = $("#toastStack");
      if (!stack || !window.bootstrap) return;
      const el = document.createElement("div");
      el.className = "toast";
      el.setAttribute("role", "alert");
      el.style.cssText = `background:#fff;border:1px solid var(--border);border-left:4px solid ${colors[type] || colors.info};border-radius:12px;box-shadow:var(--shadow-lg);font-size:13.5px;font-weight:600;min-width:280px`;
      el.innerHTML = `<div class="d-flex align-items-start gap-2 p-3"><i class="bi ${icons[type] || icons.info}" style="color:${colors[type] || colors.info};font-size:18px"></i><div class="flex-grow-1"></div><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div>`;
      el.querySelector(".flex-grow-1").textContent = message;
      stack.appendChild(el);
      const inst = new bootstrap.Toast(el, { delay: type === "error" ? 6000 : 3200 });
      el.addEventListener("hidden.bs.toast", () => el.remove());
      inst.show();
    }

    async function api(endpoint, opts) {
      opts = Object.assign({}, opts);
      const headers = { "X-CSRF-TOKEN": CFG.csrfToken, "X-Requested-With": "XMLHttpRequest", "Accept": "application/json" };
      if (opts.json !== undefined) { headers["Content-Type"] = "application/json"; opts.body = JSON.stringify(opts.json); delete opts.json; }
      let res;
      try { res = await fetch(endpoint, Object.assign({ headers, credentials: "same-origin" }, opts)); }
      catch (e) { return { success: false, message: "Network error — check your connection." }; }
      if (res.status === 401 || res.status === 419) {
        toast("error", "Your session expired. Please refresh the page and sign in again.");
        return { success: false, message: "Session expired." };
      }
      let data;
      try { data = await res.json(); }
      catch (e) { data = { success: false, message: res.status >= 500 ? "Server error. Please try again." : "Unexpected response from server." }; }
      if (data.success === undefined) data.success = res.ok;
      if (!data.success && !data.message && data.errors) data.message = Object.values(data.errors)[0][0];
      return data;
    }

    function setBusy(btn, busy, text) {
      if (!btn) return;
      if (busy) { btn.dataset.html = btn.innerHTML; btn.disabled = true; btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>${esc(text)}`; }
      else { if (btn.dataset.html) btn.innerHTML = btn.dataset.html; delete btn.dataset.html; btn.disabled = false; }
    }

    /* ------------------------------------------------------------------ *
     *  Data loading
     * ------------------------------------------------------------------ */
    async function loadTasks(silent) {
      const data = await api(CFG.routes.data);
      if (!data.success) {
        if (!silent) { loaded = true; renderAll(); toast("error", data.message || "Could not load tasks."); }
        return;
      }
      tasks = data.tasks || [];
      me = data.current_admin || me;
      loaded = true;
      const ids = new Set(tasks.map(t => t.id));
      Array.from(selected).forEach(id => { if (!ids.has(id)) selected.delete(id); });
      renderAll();
    }

    function mergeTasks(list) {
      (list || []).forEach(nt => {
        const i = tasks.findIndex(t => t.id === nt.id);
        if (i >= 0) tasks[i] = Object.assign(tasks[i], nt); else tasks.unshift(nt);
      });
    }

    /* ------------------------------------------------------------------ *
     *  Filtering / sorting
     * ------------------------------------------------------------------ */
    function matches(t, ignoreCategory) {
      const q = state.search.trim().toLowerCase();
      if (q && !((t.task_name || "").toLowerCase().includes(q) || (t.description || "").toLowerCase().includes(q) || (t.assignee && t.assignee.name.toLowerCase().includes(q)))) return false;
      if (state.priority && t.priority !== state.priority) return false;
      if (state.assignee && String(t.assigned_to) !== String(state.assignee)) return false;
      if (state.quick === "mine" && me && t.assigned_to !== me.id) return false;
      if (state.quick === "today" && (diffDays(t) !== 0 || t.status === "completed")) return false;
      if (state.quick === "week") { const n = diffDays(t); if (n === null || n < 0 || n > 7 || t.status === "completed") return false; }
      if (!ignoreCategory && state.category !== "all") {
        if (state.category === "overdue") { if (!isOverdue(t)) return false; }
        else if (t.status !== state.category) return false;
      }
      return true;
    }

    const PRI_RANK = { urgent: 0, high: 1, medium: 2, low: 3 };
    function sortList(list) {
      const arr = list.slice();
      const byNewest = (a, b) => b.id - a.id;
      switch (state.sort) {
        case "oldest": arr.sort((a, b) => a.id - b.id); break;
        case "due": arr.sort((a, b) => { const x = diffDays(a), y = diffDays(b); if (x === null && y === null) return byNewest(a, b); if (x === null) return 1; if (y === null) return -1; return x - y || byNewest(a, b); }); break;
        case "priority": arr.sort((a, b) => (PRI_RANK[a.priority] ?? 9) - (PRI_RANK[b.priority] ?? 9) || byNewest(a, b)); break;
        case "name": arr.sort((a, b) => (a.task_name || "").localeCompare(b.task_name || "")); break;
        default: arr.sort(byNewest);
      }
      return arr;
    }
    const filteredList = () => sortList(tasks.filter(t => matches(t)));
    const hasFilters = () => !!(state.search.trim() || state.priority || state.assignee || state.quick);
    function resetPage() { state.page = 1; selected.clear(); }

    /* ------------------------------------------------------------------ *
     *  Rendering — shared
     * ------------------------------------------------------------------ */
    function renderAll() {
      renderStats();
      renderTabs();
      applyView();
      if (state.view === "list") renderList(); else renderBoard();
      renderToolbarState();
      updateBulkBar();
      if (flashId !== null) {
        const id = flashId; flashId = null;
        const el = $(`[data-id="${id}"]`, state.view === "list" ? $("#tasksTbody") : $("#tfBoard"));
        if (el) { el.classList.add("tf-flash"); setTimeout(() => el.classList.remove("tf-flash"), 1900); }
      }
    }

    function applyView() {
      $$(".tf-seg button").forEach(b => b.classList.toggle("active", b.dataset.view === state.view));
      $("#tfListWrap").classList.toggle("d-none", state.view !== "list");
      $("#tfTabs").classList.toggle("d-none", state.view !== "list");
      $("#tfBoardWrap").classList.toggle("d-none", state.view !== "board");
    }

    function renderToolbarState() {
      $$("#tfChips .tf-chip").forEach(c => c.classList.toggle("active", c.dataset.quick === state.quick));
      $("#clearFiltersBtn").classList.toggle("d-none", !hasFilters());
    }

    function renderStats() {
      const total = tasks.length;
      const by = s => tasks.filter(t => t.status === s).length;
      const over = tasks.filter(isOverdue).length;
      const done = by("completed");
      const pct = total ? Math.round(done / total * 100) : 0;
      const defs = [
        { k: "all", label: "Total tasks", v: total, icon: "bi-list-task", cls: "tone-all", cap: `<div class="tf-statbar"><span style="width:${pct}%"></span></div><div class="tf-stat-cap">${pct}% completed</div>` },
        { k: "pending", label: "Pending", v: by("pending"), icon: "bi-hourglass-split", cls: "st-pending", cap: "Waiting to start" },
        { k: "in_progress", label: "In progress", v: by("in_progress"), icon: "bi-arrow-repeat", cls: "st-in_progress", cap: "Being worked on" },
        { k: "completed", label: "Completed", v: done, icon: "bi-check2-circle", cls: "st-completed", cap: "Finished tasks" },
        { k: "overdue", label: "Overdue", v: over, icon: "bi-exclamation-triangle", cls: "tone-over", cap: over ? "Need attention" : "All clear" },
      ];
      $("#tfStats").innerHTML = defs.map(d => {
        const active = state.view === "list" && state.category === d.k;
        const capHtml = d.cap.startsWith("<") ? d.cap : `<div class="tf-stat-cap">${d.cap}</div>`;
        return `<button type="button" class="tf-stat ${d.cls}${active ? " active" : ""}" data-cat="${d.k}" aria-pressed="${active}">
          <span class="tf-stat-ico"><i class="bi ${d.icon}"></i></span>
          <span class="tf-stat-body"><span class="tf-stat-label">${d.label}</span><div class="tf-stat-value">${loaded ? d.v : "–"}</div>${capHtml}</span>
        </button>`;
      }).join("");
    }

    function renderTabs() {
      const base = tasks.filter(t => matches(t, true));
      const count = s => base.filter(t => t.status === s).length;
      const defs = [["all", "All", base.length]]
        .concat(FLOW.filter(s => STATUSES.includes(s)).map(s => [s, label(s), count(s)]))
        .concat([["overdue", "Overdue", base.filter(isOverdue).length]]);
      $("#tfTabs").innerHTML = defs.map(d => `<button type="button" class="tf-tab${state.category === d[0] ? " active" : ""}" data-cat="${d[0]}" role="tab" aria-selected="${state.category === d[0]}">${d[1]} <span class="n">${d[2]}</span></button>`).join("");
    }

    function emptyState(noTasksAtAll) {
      if (noTasksAtAll) {
        return `<div class="tf-empty"><div class="ico"><i class="bi bi-clipboard2-check"></i></div><h6>No tasks yet</h6><p>${SA ? "Create your first task and assign it to a teammate." : "You have no tasks assigned right now. Enjoy the calm!"}</p>${SA ? '<button type="button" class="btn btn-primary" data-act="add"><i class="bi bi-plus-lg me-1"></i>Add task</button>' : ""}</div>`;
      }
      return `<div class="tf-empty"><div class="ico"><i class="bi bi-funnel"></i></div><h6>No tasks match your filters</h6><p>Try a different search or clear the filters.</p><button type="button" class="btn btn-outline-secondary" data-act="clear">Clear filters</button></div>`;
    }

    /* ------------------------------------------------------------------ *
     *  Rendering — list
     * ------------------------------------------------------------------ */
    function checklistMeta(t) {
      const total = t.checklist_items_count || 0, done = t.checklist_done_count || 0;
      return total ? `<span class="tf-meta${done === total ? " full" : ""}" title="Checklist"><i class="bi bi-check2-square"></i>${done}/${total}</span>` : "";
    }

    function rowHtml(t) {
      const due = dueInfo(t), over = isOverdue(t), done = t.status === "completed";
      const meta = checklistMeta(t)
        + (t.comments_count ? `<span class="tf-meta" title="Comments"><i class="bi bi-chat-left-text"></i>${t.comments_count}</span>` : "")
        + (t.attachments_count ? `<span class="tf-meta" title="Attachments"><i class="bi bi-paperclip"></i>${t.attachments_count}</span>` : "");
      const desc = t.description ? `<span class="tf-desc">${esc(t.description)}</span>` : "";
      const statusOpts = STATUSES.map(s => `<option value="${s}"${s === t.status ? " selected" : ""}>${label(s)}</option>`).join("");
      return `<tr class="tf-row${over ? " is-over" : ""}${done ? " is-done" : ""}${selected.has(t.id) ? " selected" : ""}" data-id="${t.id}" tabindex="0">
        ${SA ? `<td class="no-open"><input class="form-check-input row-check" type="checkbox" ${selected.has(t.id) ? "checked" : ""} aria-label="Select task"></td>` : ""}
        <td>
          <div class="tf-task">
            <button type="button" class="tf-tick no-open" data-act="tick" title="${done ? "Reopen task" : "Mark as completed"}" aria-label="${done ? "Reopen task" : "Mark as completed"}"><i class="bi bi-check-lg"></i></button>
            <div style="min-width:0">
              <div class="tf-name">${esc(t.task_name)}</div>
              ${(meta || desc) ? `<div class="tf-sub">${meta}${desc}</div>` : ""}
            </div>
          </div>
        </td>
        <td>${prioPill(t.priority)}</td>
        <td><div class="tf-due ${due.cls}"><div class="tf-date">${due.cls === "none" ? "—" : esc(due.label)}</div>${due.rel ? `<div class="tf-rel">${due.rel}</div>` : ""}</div></td>
        <td class="no-open"><select class="tf-status st-${esc(t.status)}" aria-label="Change status">${statusOpts}</select></td>
        <td>${t.assignee ? `<div class="tf-person">${avatar(t.assignee.name, "sm")}<span class="nm">${esc(t.assignee.name)}</span></div>` : '<span class="text-muted-2">Unassigned</span>'}</td>
        <td class="d-none d-xl-table-cell text-muted-2">${t.assigner ? esc(t.assigner.name) : "—"}</td>
        <td class="no-open">
          <div class="dropdown">
            <button class="tf-act-btn" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}' aria-label="Task actions"><i class="bi bi-three-dots"></i></button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="#" data-act="view"><i class="bi bi-eye me-2"></i>View details</a></li>
              ${SA ? `<li><a class="dropdown-item" href="#" data-act="edit"><i class="bi bi-pencil me-2"></i>Edit task</a></li>
              <li><a class="dropdown-item" href="#" data-act="dup"><i class="bi bi-copy me-2"></i>Duplicate</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="#" data-act="del"><i class="bi bi-trash me-2"></i>Delete</a></li>` : ""}
            </ul>
          </div>
        </td>
      </tr>`;
    }

    function skeletonRows() {
      const cols = SA ? 8 : 7;
      return Array.from({ length: 6 }, () => `<tr>${Array.from({ length: cols }, (_, i) => `<td><span class="tf-skel" style="height:${i === (SA ? 1 : 0) ? 34 : 18}px;width:${i === (SA ? 1 : 0) ? "85%" : "60%"}"></span></td>`).join("")}</tr>`).join("");
    }

    function pageList(cur, total) {
      if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
      const s = new Set([1, total, cur, cur - 1, cur + 1]);
      if (cur <= 3) { s.add(2); s.add(3); s.add(4); }
      if (cur >= total - 2) { s.add(total - 1); s.add(total - 2); s.add(total - 3); }
      const arr = Array.from(s).filter(n => n >= 1 && n <= total).sort((a, b) => a - b);
      const out = [];
      arr.forEach((n, i) => { if (i && n - arr[i - 1] > 1) out.push("…"); out.push(n); });
      return out;
    }

    function renderList() {
      const tbody = $("#tasksTbody");
      if (!loaded) { tbody.innerHTML = skeletonRows(); $("#pageInfo").textContent = ""; $("#pageButtons").innerHTML = ""; return; }
      const filtered = filteredList();
      const totalPages = Math.max(1, Math.ceil(filtered.length / state.pageSize));
      state.page = Math.min(state.page, totalPages);
      const start = (state.page - 1) * state.pageSize;
      const items = filtered.slice(start, start + state.pageSize);
      const cols = SA ? 8 : 7;

      tbody.innerHTML = items.length ? items.map(rowHtml).join("") : `<tr><td colspan="${cols}">${emptyState(tasks.length === 0)}</td></tr>`;

      $("#pageInfo").textContent = filtered.length ? `Showing ${start + 1}–${Math.min(start + state.pageSize, filtered.length)} of ${filtered.length}` : "";
      $("#pageButtons").innerHTML = totalPages > 1
        ? `<button class="btn-page" ${state.page === 1 ? "disabled" : ""} data-page="prev" aria-label="Previous page"><i class="bi bi-chevron-left"></i></button>`
          + pageList(state.page, totalPages).map(n => n === "…" ? `<span class="btn-page" style="border:0;background:none">…</span>` : `<button class="btn-page${n === state.page ? " active" : ""}" data-page="${n}">${n}</button>`).join("")
          + `<button class="btn-page" ${state.page === totalPages ? "disabled" : ""} data-page="next" aria-label="Next page"><i class="bi bi-chevron-right"></i></button>`
        : "";
      syncSelectAll(items.map(t => t.id));
    }

    function syncSelectAll(pageIds) {
      const el = $("#selectAllCheck");
      if (!el) return;
      const n = pageIds.filter(id => selected.has(id)).length;
      el.checked = pageIds.length > 0 && n === pageIds.length;
      el.indeterminate = n > 0 && n < pageIds.length;
    }

    /* ------------------------------------------------------------------ *
     *  Rendering — board
     * ------------------------------------------------------------------ */
    function cardHtml(t) {
      const due = dueInfo(t), total = t.checklist_items_count || 0, done = t.checklist_done_count || 0;
      const pct = total ? Math.round(done / total * 100) : 0;
      const moves = BOARD_ORDER.filter(s => s !== t.status).map(s => `<li><a class="dropdown-item" href="#" data-act="move" data-status="${s}"><i class="bi ${ST_ICON[s] || "bi-circle"} me-2"></i>${label(s)}</a></li>`).join("");
      return `<div class="tf-kcard pr-${esc(t.priority)}${t.status === "completed" ? " is-done" : ""}" draggable="true" data-id="${t.id}" tabindex="0">
        <div class="tf-kcard-top">
          <div class="tf-kname">${esc(t.task_name)}</div>
          <div class="dropdown no-open">
            <button type="button" class="tf-kmore" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}' aria-label="Move task"><i class="bi bi-three-dots"></i></button>
            <ul class="dropdown-menu dropdown-menu-end"><li><h6 class="dropdown-header">Move to</h6></li>${moves}</ul>
          </div>
        </div>
        <div class="tf-kmeta">${prioPill(t.priority)}${due.cls !== "none" ? `<span class="tf-kdue ${due.cls}"><i class="bi bi-calendar-event"></i>${esc(due.cls === "over" ? due.rel : due.cls === "today" ? "Today" : due.label)}</span>` : ""}</div>
        ${total ? `<div class="tf-kprog"><div class="cl-progress"><span class="${pct === 100 ? "full" : ""}" style="width:${pct}%"></span></div><span>${done}/${total}</span></div>` : ""}
        <div class="tf-kfoot">
          <div class="tf-kicons">${t.comments_count ? `<span><i class="bi bi-chat-left-text"></i>${t.comments_count}</span>` : ""}${t.attachments_count ? `<span><i class="bi bi-paperclip"></i>${t.attachments_count}</span>` : ""}</div>
          ${t.assignee ? avatar(t.assignee.name, "sm") : ""}
        </div>
      </div>`;
    }

    function renderBoard() {
      const board = $("#tfBoard");
      if (!loaded) { board.innerHTML = BOARD_ORDER.map(s => `<div class="tf-col st-${s}"><div class="tf-col-head"><span class="tf-dot"></span>${label(s)}</div><span class="tf-skel" style="height:84px;margin-bottom:10px"></span><span class="tf-skel" style="height:84px"></span></div>`).join(""); return; }
      const items = sortList(tasks.filter(t => matches(t, true)));
      board.innerHTML = BOARD_ORDER.map(s => {
        const col = items.filter(t => t.status === s);
        return `<div class="tf-col st-${s}" data-status="${s}">
          <div class="tf-col-head"><span class="tf-dot"></span><span>${label(s)}</span><span class="tf-col-count">${col.length}</span></div>
          <div class="tf-col-body">${col.length ? col.map(cardHtml).join("") : `<div class="tf-col-empty"><i class="bi ${ST_ICON[s] || "bi-inbox"}"></i>${hasFilters() ? "No matches" : "Drop tasks here"}</div>`}</div>
        </div>`;
      }).join("");
    }

    /* ------------------------------------------------------------------ *
     *  Status changes (optimistic)
     * ------------------------------------------------------------------ */
    async function setStatus(id, status, quiet) {
      const t = byId(id);
      if (!t) return false;
      if (t.status === status) { renderAll(); return true; }
      const prev = { status: t.status, completed_at: t.completed_at };
      t.status = status;
      t.completed_at = status === "completed" ? new Date().toISOString() : null;
      renderAll();
      const res = await api(url(CFG.routes.updateStatus, id), { method: "PATCH", json: { status } });
      if (!res.success) {
        Object.assign(t, prev);
        renderAll();
        toast("error", res.message || "Could not update status.");
      } else {
        if (res.task) Object.assign(t, res.task);
        renderAll();
        if (!quiet) toast("success", res.message || "Status updated.");
      }
      if (detail.id === id) openTaskDetail(id, true);
      return !!res.success;
    }

    /* ------------------------------------------------------------------ *
     *  Delete / duplicate
     * ------------------------------------------------------------------ */
    function askDelete(ids) {
      if (!ids.length) return;
      pendingDelete = ids.slice();
      const one = ids.length === 1 ? byId(ids[0]) : null;
      $("#deleteTitle").textContent = ids.length === 1 ? "Delete task?" : `Delete ${ids.length} tasks?`;
      $("#deleteText").textContent = one
        ? `“${one.task_name}” will be permanently deleted along with its comments, checklist and attachments. This can't be undone.`
        : `These ${ids.length} tasks will be permanently deleted along with their comments, checklists and attachments. This can't be undone.`;
      bootstrap.Modal.getOrCreateInstance($("#deleteConfirmModal")).show();
    }

    async function confirmDelete() {
      const ids = pendingDelete.slice();
      if (!ids.length) return;
      const btn = $("#confirmDeleteBtn");
      setBusy(btn, true, "Deleting…");
      const res = ids.length === 1
        ? await api(url(CFG.routes.destroy, ids[0]), { method: "DELETE" })
        : await api(CFG.routes.bulkDelete, { method: "POST", json: { ids } });
      setBusy(btn, false);
      if (!res.success) { toast("error", res.message || "Could not delete."); return; }
      tasks = tasks.filter(t => !ids.includes(t.id));
      ids.forEach(id => selected.delete(id));
      pendingDelete = [];
      if (detail.id && ids.includes(detail.id)) bootstrap.Offcanvas.getOrCreateInstance($("#taskDetailOffcanvas")).hide();
      renderAll();
      bootstrap.Modal.getOrCreateInstance($("#deleteConfirmModal")).hide();
      toast("success", res.message || "Deleted.");
    }

    async function duplicateTask(id) {
      const res = await api(url(CFG.routes.duplicate, id), { method: "POST" });
      if (!res.success) { toast("error", res.message || "Could not duplicate."); return; }
      tasks.unshift(res.task);
      flashId = res.task.id;
      state.page = 1;
      renderAll();
      toast("success", res.message || "Task duplicated.");
    }

    /* ------------------------------------------------------------------ *
     *  Bulk actions
     * ------------------------------------------------------------------ */
    function updateBulkBar() {
      const bar = $("#bulkBar");
      if (!bar) return;
      $("#bulkCount").textContent = selected.size;
      bar.classList.toggle("show", selected.size > 0 && state.view === "list");
    }

    async function bulkStatus(status) {
      const ids = Array.from(selected);
      const res = await api(CFG.routes.bulkStatus, { method: "POST", json: { ids, status } });
      if (!res.success) { toast("error", res.message || "Could not update tasks."); return; }
      mergeTasks(res.tasks); selected.clear(); renderAll(); toast("success", res.message);
    }
    async function bulkAssign(adminId) {
      const ids = Array.from(selected);
      const res = await api(CFG.routes.bulkAssign, { method: "POST", json: { ids, assigned_to: adminId } });
      if (!res.success) { toast("error", res.message || "Could not reassign tasks."); return; }
      mergeTasks(res.tasks); selected.clear(); renderAll(); toast("success", res.message);
    }

    /* ------------------------------------------------------------------ *
     *  Export CSV
     * ------------------------------------------------------------------ */
    function csvCell(v) {
      v = v == null ? "" : String(v);
      if (/^[=+\-@]/.test(v)) v = "'" + v; // neutralise spreadsheet formulas
      return /[",\n\r]/.test(v) ? '"' + v.replace(/"/g, '""') + '"' : v;
    }
    function exportCsv() {
      const list = state.view === "list" ? filteredList() : sortList(tasks.filter(t => matches(t, true)));
      if (!list.length) { toast("info", "Nothing to export for the current filters."); return; }
      const head = ["ID", "Task", "Description", "Priority", "Status", "Due date", "Assigned to", "Assigned by", "Checklist", "Comments", "Files"];
      const rows = list.map(t => [t.id, t.task_name, t.description || "", cap(t.priority), label(t.status), t.due_date ? String(t.due_date).slice(0, 10) : "", t.assignee ? t.assignee.name : "", t.assigner ? t.assigner.name : "", (t.checklist_items_count ? (t.checklist_done_count || 0) + "/" + t.checklist_items_count : ""), t.comments_count || 0, t.attachments_count || 0]);
      const csv = "﻿" + [head].concat(rows).map(r => r.map(csvCell).join(",")).join("\r\n");
      const a = document.createElement("a");
      a.href = URL.createObjectURL(new Blob([csv], { type: "text/csv;charset=utf-8" }));
      a.download = "tasks-" + ymd(new Date()) + ".csv";
      document.body.appendChild(a); a.click(); a.remove();
      setTimeout(() => URL.revokeObjectURL(a.href), 1000);
      toast("success", `Exported ${list.length} task${list.length === 1 ? "" : "s"}.`);
    }

    /* ------------------------------------------------------------------ *
     *  Add / edit modal
     * ------------------------------------------------------------------ */
    function clearFormErrors() {
      $$("#taskForm .tf-field-err").forEach(e => e.textContent = "");
      $$("#taskForm .tf-has-err").forEach(e => e.classList.remove("tf-has-err"));
    }
    function showFieldError(field, msg) {
      const el = $("#err_" + field); if (el) el.textContent = msg;
      const wrap = $(`#taskForm [data-field="${field}"]`); if (wrap) wrap.classList.add("tf-has-err");
    }
    function updateDueHint() {
      const v = $("#taskDueDate").value, d = parseYmd(v);
      $("#dueHint").classList.toggle("d-none", !(d && d < startOfToday()));
    }

    function openTaskModal(id) {
      if (!SA) return;
      $("#taskForm").reset();
      clearFormErrors();
      $("#taskId").value = "";
      const t = id ? byId(id) : null;
      $("#taskModalTitle").textContent = t ? "Edit task" : "Add new task";
      $("#taskSubmitBtn").textContent = t ? "Save changes" : "Add task";
      const prio = t ? t.priority : (PRIORITIES.includes("medium") ? "medium" : PRIORITIES[0]);
      const radio = $(`#taskPriorityGroup input[value="${prio}"]`); if (radio) radio.checked = true;
      if (t) {
        $("#taskId").value = t.id;
        $("#taskName").value = t.task_name;
        $("#taskDescription").value = t.description || "";
        $("#taskDueDate").value = t.due_date ? String(t.due_date).slice(0, 10) : "";
        $("#taskAssignedTo").value = t.assigned_to || "";
      }
      updateDueHint();
      bootstrap.Modal.getOrCreateInstance($("#taskModal")).show();
    }

    async function submitTaskForm(e) {
      e.preventDefault();
      clearFormErrors();
      const id = $("#taskId").value;
      const prio = $("#taskPriorityGroup input:checked");
      const payload = {
        task_name: $("#taskName").value.trim(),
        description: $("#taskDescription").value.trim(),
        priority: prio ? prio.value : "medium",
        due_date: $("#taskDueDate").value || null,
        assigned_to: $("#taskAssignedTo").value,
      };
      let bad = false;
      if (!payload.task_name) { showFieldError("task_name", "Please enter a task name."); bad = true; }
      if (!payload.assigned_to) { showFieldError("assigned_to", "Please choose who this task is for."); bad = true; }
      if (bad) { const first = $("#taskForm .tf-has-err .form-control, #taskForm .tf-has-err .form-select"); if (first) first.focus(); return; }

      const btn = $("#taskSubmitBtn");
      setBusy(btn, true, id ? "Saving…" : "Adding…");
      const res = await api(id ? url(CFG.routes.update, id) : CFG.routes.store, { method: id ? "PUT" : "POST", json: payload });
      setBusy(btn, false);

      if (!res.success) {
        if (res.errors) Object.keys(res.errors).forEach(f => showFieldError(f, res.errors[f][0]));
        else toast("error", res.message || "Something went wrong.");
        return;
      }
      if (id) { tasks = tasks.map(t => t.id === +id ? res.task : t); }
      else { tasks.unshift(res.task); state.page = 1; }
      flashId = res.task.id;
      renderAll();
      bootstrap.Modal.getOrCreateInstance($("#taskModal")).hide();
      toast("success", res.message || "Saved.");
      if (id && detail.id === +id) openTaskDetail(+id, true);
    }

    /* ------------------------------------------------------------------ *
     *  Detail panel
     * ------------------------------------------------------------------ */
    const ocEl = () => $("#taskDetailOffcanvas");
    const oc = () => bootstrap.Offcanvas.getOrCreateInstance(ocEl());

    async function openTaskDetail(id, keepTab) {
      const res = await api(url(CFG.routes.show, id));
      if (!res.success) { toast("error", res.message || "Could not open task."); return; }
      const isNew = detail.id !== id;
      detail.id = id;
      detail.task = res.task;
      if (isNew || !keepTab || !detail.tab) detail.tab = (res.task.checklist_items || []).length ? "checklist" : "comments";
      renderDetail();
      if (!ocEl().classList.contains("show")) oc().show();
    }
    window.openTaskDetailById = openTaskDetail; // used by the notification bell

    function closeDetailThen(fn) {
      if (!ocEl().classList.contains("show")) return fn();
      ocEl().addEventListener("hidden.bs.offcanvas", () => fn(), { once: true });
      oc().hide();
    }

    function renderDetail() {
      const t = detail.task, due = dueInfo(t);
      $("#tdName").textContent = t.task_name;
      $("#tdPriorityBadge").innerHTML = prioPill(t.priority);
      const sel = $("#tdStatusSelect");
      sel.value = t.status;
      sel.className = "tf-status st-" + t.status;
      $("#tdDescription").innerHTML = t.description ? linkify(esc(t.description)) : '<span class="text-muted-2">No description provided.</span>';

      const metas = [
        ["Due date", `<span class="td-due ${due.cls}">${due.cls === "none" ? "—" : esc(due.label)}</span>${due.rel ? `<span class="td-rel ${due.cls}">${due.rel}</span>` : ""}`],
        ["Assigned to", t.assignee ? `${avatar(t.assignee.name, "sm")}<span>${esc(t.assignee.name)}</span>` : "Unassigned"],
        ["Assigned by", t.assigner ? `${avatar(t.assigner.name, "sm")}<span>${esc(t.assigner.name)}</span>` : "—"],
        ["Created", esc(fullDate(t.created_at)) || "—"],
      ];
      if (t.status === "completed" && t.completed_at) metas.push(["Completed", esc(fullDate(t.completed_at))]);
      $("#tdMeta").innerHTML = metas.map(m => `<div class="m"><div class="k">${m[0]}</div><div class="v">${m[1]}</div></div>`).join("");

      renderBanner(); renderChecklist(); renderComments(); renderAttachments(); renderActivity(); updateTabCounts(); setTab(detail.tab);
    }

    function setTab(name) {
      detail.tab = name;
      $$("#tdTabs .td-tab").forEach(b => b.classList.toggle("active", b.dataset.tab === name));
      const map = { checklist: "#tdPaneChecklist", comments: "#tdPaneComments", files: "#tdPaneFiles", activity: "#tdPaneActivity" };
      Object.keys(map).forEach(k => $(map[k]).classList.toggle("active", k === name));
    }

    function updateTabCounts() {
      const t = detail.task, items = t.checklist_items || [];
      $("#cntChecklist").textContent = items.length ? `${items.filter(i => i.is_done).length}/${items.length}` : "0";
      $("#cntComments").textContent = (t.comments || []).length;
      $("#cntFiles").textContent = (t.attachments || []).length;
    }

    function syncListFromDetail() {
      const t = detail.task, local = byId(detail.id);
      if (!t || !local) return;
      const items = t.checklist_items || [];
      local.comments_count = (t.comments || []).length;
      local.attachments_count = (t.attachments || []).length;
      local.checklist_items_count = items.length;
      local.checklist_done_count = items.filter(i => i.is_done).length;
      renderAll();
    }

    function renderBanner() {
      const t = detail.task, items = t.checklist_items || [];
      $("#tdBanner").innerHTML = (items.length && items.every(i => i.is_done) && t.status !== "completed")
        ? `<div class="td-banner"><i class="bi bi-stars"></i><div><b>All checklist steps are done</b><span>Ready to close this task?</span></div><button type="button" class="btn btn-sm btn-primary" data-act="complete">Mark completed</button></div>` : "";
    }

    function renderChecklist() {
      const items = detail.task.checklist_items || [];
      const total = items.length, done = items.filter(i => i.is_done).length, pct = total ? Math.round(done / total * 100) : 0;
      $("#clProgress").innerHTML = total ? `<div class="cl-head"><span>${done} of ${total} steps done</span><b>${pct}%</b></div><div class="cl-progress"><span class="${pct === 100 ? "full" : ""}" style="width:${pct}%"></span></div>` : "";
      $("#clList").innerHTML = total
        ? items.map(i => `<div class="cl-item${i.is_done ? " done" : ""}" data-id="${i.id}"><button type="button" class="cl-box" data-act="toggle" aria-label="${i.is_done ? "Mark as not done" : "Mark as done"}"><i class="bi bi-check-lg"></i></button><span class="cl-title">${esc(i.title)}</span><button type="button" class="cl-del" data-act="remove" aria-label="Remove step"><i class="bi bi-x-lg"></i></button></div>`).join("")
        : `<div class="tf-empty-sm"><i class="bi bi-check2-square"></i>Break this task into small steps and tick them off.</div>`;
    }

    function renderComments() {
      const list = detail.task.comments || [];
      $("#commentsList").innerHTML = list.length
        ? list.map(c => { const n = c.author ? c.author.name : "Unknown"; return `<div class="comment-item">${avatar(n, "sm")}<div class="comment-body"><div class="comment-head"><span class="comment-author">${esc(n)}</span><span class="comment-time" title="${esc(fullDate(c.created_at))}">${esc(timeAgo(c.created_at))}</span></div><div class="comment-text">${linkify(esc(c.comment))}</div></div></div>`; }).join("")
        : `<div class="tf-empty-sm"><i class="bi bi-chat-left"></i>No comments yet — start the conversation.</div>`;
    }

    function fileIcon(a) {
      const n = (a.original_name || "").toLowerCase(), m = (a.mime_type || "").toLowerCase();
      if (m.startsWith("image/")) return `<img src="${esc(a.url)}" alt="" loading="lazy">`;
      let ic = "bi-file-earmark";
      if (m.includes("pdf") || n.endsWith(".pdf")) ic = "bi-file-earmark-pdf";
      else if (/\.(xlsx?|csv)$/.test(n)) ic = "bi-file-earmark-spreadsheet";
      else if (/\.(docx?|rtf|txt)$/.test(n)) ic = "bi-file-earmark-text";
      else if (/\.(zip|rar|7z)$/.test(n)) ic = "bi-file-earmark-zip";
      return `<i class="bi ${ic}"></i>`;
    }

    function renderAttachments() {
      const list = detail.task.attachments || [];
      $("#attachmentsList").innerHTML = list.length
        ? list.map(a => {
          const canDel = SA || (me && a.admin_id === me.id);
          return `<div class="attach-item" data-id="${a.id}">
            <div class="attach-icon">${fileIcon(a)}</div>
            <div class="flex-grow-1" style="min-width:0"><a class="attach-name d-block" href="${esc(a.url)}" target="_blank" rel="noopener">${esc(a.original_name)}</a><div class="attach-meta">${esc(a.human_size)} · ${a.uploader ? esc(a.uploader.name) : "—"} · ${esc(timeAgo(a.created_at))}</div></div>
            <a href="${esc(a.url)}" download class="tf-act-btn" aria-label="Download"><i class="bi bi-download"></i></a>
            ${canDel ? `<button type="button" class="tf-act-btn attach-del" data-act="remove" aria-label="Remove file"><i class="bi bi-x-lg"></i></button>` : ""}
          </div>`;
        }).join("")
        : `<div class="tf-empty-sm"><i class="bi bi-paperclip"></i>No files attached yet.</div>`;
    }

    function renderActivity() {
      const list = detail.task.activity_logs || [];
      $("#activityList").innerHTML = list.length
        ? list.map(l => `<div class="activity-item"><div class="activity-ico"><i class="bi ${ACT_ICON[l.action] || "bi-dot"}"></i></div><div><div class="activity-text">${esc(l.description)}</div><div class="activity-time" title="${esc(fullDate(l.created_at))}">${esc(timeAgo(l.created_at))}</div></div></div>`).join("")
        : `<div class="tf-empty-sm"><i class="bi bi-clock-history"></i>No activity yet.</div>`;
    }

    /* ---- checklist actions ---- */
    async function addChecklistItem(title) {
      const taskId = detail.id;
      const res = await api(CFG.routes.checklistStore, { method: "POST", json: { task_id: taskId, title } });
      if (!res.success) { toast("error", res.message || "Could not add the step."); return false; }
      if (detail.id !== taskId) return true;
      (detail.task.checklist_items = detail.task.checklist_items || []).push(res.item);
      renderChecklist(); renderBanner(); updateTabCounts(); syncListFromDetail();
      return true;
    }
    async function toggleChecklistItem(itemId) {
      const items = detail.task.checklist_items || [], it = items.find(i => i.id === itemId);
      if (!it) return;
      it.is_done = !it.is_done;
      renderChecklist(); renderBanner(); updateTabCounts(); syncListFromDetail();
      const res = await api(url(CFG.routes.checklistItem, itemId), { method: "PATCH", json: { is_done: it.is_done } });
      if (!res.success) { it.is_done = !it.is_done; renderChecklist(); renderBanner(); updateTabCounts(); syncListFromDetail(); toast("error", res.message || "Could not update the step."); }
    }
    async function removeChecklistItem(itemId) {
      const items = detail.task.checklist_items || [], idx = items.findIndex(i => i.id === itemId);
      if (idx < 0) return;
      const [removed] = items.splice(idx, 1);
      renderChecklist(); renderBanner(); updateTabCounts(); syncListFromDetail();
      const res = await api(url(CFG.routes.checklistItem, itemId), { method: "DELETE" });
      if (!res.success) { items.splice(idx, 0, removed); renderChecklist(); renderBanner(); updateTabCounts(); syncListFromDetail(); toast("error", res.message || "Could not remove the step."); }
    }

    /* ---- comments ---- */
    async function postComment(e) {
      if (e) e.preventDefault();
      const box = $("#commentText"), text = box.value.trim();
      if (!text || !detail.id) return;
      const btn = $("#commentBtn"), taskId = detail.id;
      setBusy(btn, true, "Posting…");
      const res = await api(CFG.routes.commentStore, { method: "POST", json: { task_id: taskId, comment: text } });
      setBusy(btn, false);
      if (!res.success) { toast("error", res.message || "Could not add comment."); return; }
      box.value = "";
      if (detail.id === taskId) {
        detail.task.comments = [res.comment].concat(detail.task.comments || []);
        renderComments(); updateTabCounts(); syncListFromDetail();
        openTaskDetail(taskId, true); // refresh the activity log quietly
      }
    }

    /* ---- attachments ---- */
    function uploadOne(taskId, file, onProgress) {
      return new Promise(resolve => {
        const fd = new FormData();
        fd.append("task_id", taskId); fd.append("file", file);
        const x = new XMLHttpRequest();
        x.open("POST", CFG.routes.attachmentStore);
        x.setRequestHeader("X-CSRF-TOKEN", CFG.csrfToken);
        x.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        x.setRequestHeader("Accept", "application/json");
        x.upload.onprogress = ev => { if (ev.lengthComputable) onProgress(ev.loaded / ev.total); };
        x.onload = () => {
          let d; try { d = JSON.parse(x.responseText); } catch (err) { d = { success: false, message: "Upload failed." }; }
          if (d.success === undefined) d.success = x.status >= 200 && x.status < 300;
          if (!d.success && !d.message && d.errors) d.message = Object.values(d.errors)[0][0];
          resolve(d);
        };
        x.onerror = () => resolve({ success: false, message: "Network error during upload." });
        x.send(fd);
      });
    }

    async function handleFiles(files) {
      const taskId = detail.id;
      if (!taskId || !files || !files.length) return;
      let ok = 0;
      for (const f of Array.from(files)) {
        if (f.size > MAX_UPLOAD) { toast("error", `“${f.name}” is larger than 10 MB.`); continue; }
        const row = document.createElement("div");
        row.className = "up-item";
        row.innerHTML = `<div class="up-name"></div><div class="cl-progress"><span style="width:0%"></span></div>`;
        row.querySelector(".up-name").textContent = f.name;
        $("#uploadQueue").appendChild(row);
        const bar = row.querySelector(".cl-progress span");
        const res = await uploadOne(taskId, f, p => { bar.style.width = Math.round(p * 100) + "%"; });
        row.remove();
        if (!res.success) { toast("error", res.message || "Upload failed."); continue; }
        ok++;
        if (detail.id === taskId) {
          detail.task.attachments = [res.attachment].concat(detail.task.attachments || []);
          renderAttachments(); updateTabCounts(); syncListFromDetail();
        }
      }
      if (ok) { toast("success", ok === 1 ? "File attached." : `${ok} files attached.`); if (detail.id === taskId) openTaskDetail(taskId, true); }
    }

    async function removeAttachment(id) {
      const res = await api(url(CFG.routes.attachmentDestroy, id), { method: "DELETE" });
      if (!res.success) { toast("error", res.message || "Could not remove file."); return; }
      detail.task.attachments = (detail.task.attachments || []).filter(a => a.id !== id);
      renderAttachments(); updateTabCounts(); syncListFromDetail();
      toast("success", "Attachment removed.");
    }

    /* ------------------------------------------------------------------ *
     *  Event wiring
     * ------------------------------------------------------------------ */
    function clearFilters() {
      state.search = ""; state.priority = ""; state.assignee = ""; state.quick = "";
      $("#filterSearch").value = ""; $("#filterPriority").value = "";
      const a = $("#filterAssignee"); if (a) a.value = "";
      resetPage(); renderAll();
    }

    function setView(v) {
      if (state.view === v) return;
      state.view = v; store.set("tf_view", v); selected.clear(); renderAll();
    }

    function init() {
      // ---- static option lists ----
      $("#filterPriority").innerHTML = `<option value="">All priorities</option>` + PRIORITIES.map(p => `<option value="${p}">${cap(p)}</option>`).join("");
      const fa = $("#filterAssignee");
      if (fa) fa.innerHTML = `<option value="">All employees</option>` + (CFG.admins || []).map(a => `<option value="${a.id}">${esc(a.name)}</option>`).join("");
      $("#tdStatusSelect").innerHTML = STATUSES.map(s => `<option value="${s}">${label(s)}</option>`).join("");
      $("#sortSelect").value = ["newest", "oldest", "due", "priority", "name"].includes(state.sort) ? state.sort : "newest";
      $("#pageSizeSelect").value = String(state.pageSize);
      if (SA) {
        $("#taskPriorityGroup").innerHTML = PRIORITIES.map(p => `<input type="radio" class="btn-check" name="taskPriority" id="prio_${p}" value="${p}" autocomplete="off"><label class="tf-prio pr-${p}" for="prio_${p}"><i class="bi ${PR_ICON[p] || "bi-dash-lg"}"></i>${cap(p)}</label>`).join("");
        $("#taskAssignedTo").innerHTML = `<option value="">Select employee</option>` + (CFG.admins || []).map(a => `<option value="${a.id}">${esc(a.name)}</option>`).join("");
        $("#bulkStatusMenu").innerHTML = STATUSES.map(s => `<li><a class="dropdown-item" href="#" data-bulk-status="${s}"><i class="bi ${ST_ICON[s] || "bi-circle"} me-2"></i>${label(s)}</a></li>`).join("");
        $("#bulkAssignMenu").innerHTML = (CFG.admins || []).map(a => `<li><a class="dropdown-item" href="#" data-bulk-assign="${a.id}">${avatar(a.name, "sm")}<span class="ms-2">${esc(a.name)}</span></a></li>`).join("");
      }

      renderAll(); // skeletons

      // ---- header / toolbar ----
      $$(".tf-seg button").forEach(b => b.addEventListener("click", () => setView(b.dataset.view)));
      $("#exportBtn").addEventListener("click", exportCsv);
      const addBtn = $("#openAddTaskBtn"); if (addBtn) addBtn.addEventListener("click", () => openTaskModal(null));

      let st;
      $("#filterSearch").addEventListener("input", e => { clearTimeout(st); const v = e.target.value; st = setTimeout(() => { state.search = v; resetPage(); renderAll(); }, 120); });
      $("#filterPriority").addEventListener("change", e => { state.priority = e.target.value; resetPage(); renderAll(); });
      if (fa) fa.addEventListener("change", e => { state.assignee = e.target.value; resetPage(); renderAll(); });
      $("#sortSelect").addEventListener("change", e => { state.sort = e.target.value; store.set("tf_sort", state.sort); resetPage(); renderAll(); });
      $("#pageSizeSelect").addEventListener("change", e => { state.pageSize = +e.target.value; store.set("tf_psize", state.pageSize); state.page = 1; renderAll(); });
      $("#tfChips").addEventListener("click", e => { const c = e.target.closest("[data-quick]"); if (!c) return; state.quick = state.quick === c.dataset.quick ? "" : c.dataset.quick; resetPage(); renderAll(); });
      $("#clearFiltersBtn").addEventListener("click", clearFilters);

      $("#tfStats").addEventListener("click", e => {
        const b = e.target.closest("[data-cat]"); if (!b) return;
        state.category = state.view === "list" && state.category === b.dataset.cat && b.dataset.cat !== "all" ? "all" : b.dataset.cat;
        if (state.view !== "list") { state.view = "list"; store.set("tf_view", "list"); }
        resetPage(); renderAll();
      });
      $("#tfTabs").addEventListener("click", e => { const b = e.target.closest("[data-cat]"); if (!b) return; state.category = b.dataset.cat; resetPage(); renderAll(); });

      // ---- pagination / empty-state buttons ----
      $("#pageButtons").addEventListener("click", e => {
        const b = e.target.closest("[data-page]"); if (!b || b.disabled) return;
        const total = Math.max(1, Math.ceil(filteredList().length / state.pageSize));
        state.page = b.dataset.page === "prev" ? Math.max(1, state.page - 1) : b.dataset.page === "next" ? Math.min(total, state.page + 1) : +b.dataset.page;
        renderList();
        const wrap = $("#tfListWrap"); if (wrap && wrap.getBoundingClientRect().top < 0) wrap.scrollIntoView({ block: "start", behavior: "smooth" });
      });
      $("#page-tasks").addEventListener("click", e => {
        const a = e.target.closest('[data-act="add"], [data-act="clear"]'); if (!a || a.closest("tr[data-id]")) return;
        if (a.dataset.act === "add") openTaskModal(null); else clearFilters();
      });

      // ---- list table (delegated) ----
      const tbody = $("#tasksTbody");
      tbody.addEventListener("click", e => {
        const row = e.target.closest("tr[data-id]"); if (!row) return;
        const id = +row.dataset.id;
        const act = e.target.closest("[data-act]");
        if (act) {
          e.preventDefault();
          if (act.dataset.act === "tick") { const t = byId(id); if (t) setStatus(id, t.status === "completed" ? "pending" : "completed"); }
          else if (act.dataset.act === "view") openTaskDetail(id);
          else if (act.dataset.act === "edit") openTaskModal(id);
          else if (act.dataset.act === "dup") duplicateTask(id);
          else if (act.dataset.act === "del") askDelete([id]);
          return;
        }
        if (e.target.closest(".no-open")) return;
        openTaskDetail(id);
      });
      tbody.addEventListener("change", e => {
        const row = e.target.closest("tr[data-id]"); if (!row) return;
        const id = +row.dataset.id;
        if (e.target.matches(".row-check")) {
          if (e.target.checked) selected.add(id); else selected.delete(id);
          row.classList.toggle("selected", e.target.checked);
          updateBulkBar();
          syncSelectAll($$("tr[data-id]", tbody).map(r => +r.dataset.id));
        } else if (e.target.matches(".tf-status")) {
          setStatus(id, e.target.value);
        }
      });
      tbody.addEventListener("keydown", e => { if (e.key === "Enter" && e.target.matches("tr[data-id]")) openTaskDetail(+e.target.dataset.id); });
      const selAll = $("#selectAllCheck");
      if (selAll) selAll.addEventListener("change", () => {
        $$("tr[data-id]", tbody).forEach(r => { const id = +r.dataset.id, cb = $(".row-check", r); if (!cb) return; cb.checked = selAll.checked; r.classList.toggle("selected", selAll.checked); if (selAll.checked) selected.add(id); else selected.delete(id); });
        updateBulkBar();
      });

      // ---- bulk bar ----
      if (SA) {
        $("#bulkClearBtn").addEventListener("click", () => { selected.clear(); renderAll(); });
        $("#bulkDeleteBtn").addEventListener("click", () => askDelete(Array.from(selected)));
        $("#bulkStatusMenu").addEventListener("click", e => { const a = e.target.closest("[data-bulk-status]"); if (a) { e.preventDefault(); bulkStatus(a.dataset.bulkStatus); } });
        $("#bulkAssignMenu").addEventListener("click", e => { const a = e.target.closest("[data-bulk-assign]"); if (a) { e.preventDefault(); bulkAssign(+a.dataset.bulkAssign); } });
        $("#confirmDeleteBtn").addEventListener("click", confirmDelete);
      }

      // ---- board (delegated, incl. drag & drop) ----
      const board = $("#tfBoard");
      board.addEventListener("click", e => {
        const card = e.target.closest(".tf-kcard"); if (!card) return;
        const id = +card.dataset.id, act = e.target.closest('[data-act="move"]');
        if (act) { e.preventDefault(); setStatus(id, act.dataset.status); return; }
        if (e.target.closest(".no-open")) return;
        openTaskDetail(id);
      });
      board.addEventListener("keydown", e => { if (e.key === "Enter" && e.target.matches(".tf-kcard")) openTaskDetail(+e.target.dataset.id); });
      board.addEventListener("dragstart", e => {
        const c = e.target.closest(".tf-kcard"); if (!c) return;
        dragId = +c.dataset.id;
        e.dataTransfer.effectAllowed = "move";
        try { e.dataTransfer.setData("text/plain", String(dragId)); } catch (err) { /* ignore */ }
        requestAnimationFrame(() => c.classList.add("dragging"));
      });
      board.addEventListener("dragend", () => { dragId = null; $$(".tf-kcard.dragging", board).forEach(c => c.classList.remove("dragging")); $$(".tf-col.drag-over", board).forEach(c => c.classList.remove("drag-over")); });
      board.addEventListener("dragover", e => { const col = e.target.closest(".tf-col"); if (!col || dragId === null) return; e.preventDefault(); e.dataTransfer.dropEffect = "move"; col.classList.add("drag-over"); });
      board.addEventListener("dragleave", e => { const col = e.target.closest(".tf-col"); if (col && !col.contains(e.relatedTarget)) col.classList.remove("drag-over"); });
      board.addEventListener("drop", e => {
        const col = e.target.closest(".tf-col"); if (!col || dragId === null) return;
        e.preventDefault();
        const id = dragId, status = col.dataset.status;
        dragId = null; col.classList.remove("drag-over");
        if (status) setStatus(id, status);
      });

      // ---- add / edit modal ----
      if (SA) {
        $("#taskForm").addEventListener("submit", submitTaskForm);
        $("#taskForm").addEventListener("keydown", e => { if ((e.ctrlKey || e.metaKey) && e.key === "Enter") { e.preventDefault(); $("#taskForm").requestSubmit(); } });
        $("#taskForm").addEventListener("input", e => { const w = e.target.closest("[data-field]"); if (w) { w.classList.remove("tf-has-err"); const er = $(".tf-field-err", w); if (er) er.textContent = ""; } });
        $("#taskDueDate").addEventListener("input", updateDueHint);
        $(".tf-quickdates").addEventListener("click", e => {
          const b = e.target.closest("[data-add]"); if (!b) return;
          if (b.dataset.add === "clear") $("#taskDueDate").value = "";
          else { const d = startOfToday(); d.setDate(d.getDate() + +b.dataset.add); $("#taskDueDate").value = ymd(d); }
          updateDueHint();
        });
        $("#taskModal").addEventListener("shown.bs.modal", () => $("#taskName").focus());
      }

      // ---- detail panel ----
      $("#tdStatusSelect").addEventListener("change", e => { if (detail.id) setStatus(detail.id, e.target.value); });
      $("#tdTabs").addEventListener("click", e => { const b = e.target.closest("[data-tab]"); if (b) setTab(b.dataset.tab); });
      $("#tdBanner").addEventListener("click", e => { if (e.target.closest('[data-act="complete"]') && detail.id) setStatus(detail.id, "completed"); });
      $("#taskDetailOffcanvas").addEventListener("hidden.bs.offcanvas", () => { detail.id = null; detail.task = null; detail.tab = null; });
      if (SA) {
        $("#tdEditBtn").addEventListener("click", () => { const id = detail.id; closeDetailThen(() => openTaskModal(id)); });
        $("#tdDupBtn").addEventListener("click", () => { const id = detail.id; closeDetailThen(() => duplicateTask(id)); });
        $("#tdDelBtn").addEventListener("click", () => { const id = detail.id; closeDetailThen(() => askDelete([id])); });
      }

      $("#clForm").addEventListener("submit", async e => {
        e.preventDefault();
        const input = $("#clInput"), title = input.value.trim();
        if (!title) return;
        input.value = "";
        const ok = await addChecklistItem(title);
        if (!ok) input.value = title;
        input.focus();
      });
      $("#clList").addEventListener("click", e => {
        const row = e.target.closest(".cl-item"), act = e.target.closest("[data-act]"); if (!row || !act) return;
        const id = +row.dataset.id;
        if (act.dataset.act === "toggle") toggleChecklistItem(id); else if (act.dataset.act === "remove") removeChecklistItem(id);
      });

      $("#commentForm").addEventListener("submit", postComment);
      $("#commentText").addEventListener("keydown", e => { if ((e.ctrlKey || e.metaKey) && e.key === "Enter") postComment(e); });

      const fileInput = $("#fileInput"), zone = $("#dropZone");
      fileInput.addEventListener("change", () => { handleFiles(fileInput.files); fileInput.value = ""; });
      ["dragenter", "dragover"].forEach(ev => zone.addEventListener(ev, e => { e.preventDefault(); zone.classList.add("dragover"); }));
      ["dragleave", "drop"].forEach(ev => zone.addEventListener(ev, e => { e.preventDefault(); zone.classList.remove("dragover"); }));
      zone.addEventListener("drop", e => handleFiles(e.dataTransfer.files));
      $("#attachmentsList").addEventListener("click", e => {
        const btn = e.target.closest(".attach-del"), row = e.target.closest(".attach-item"); if (!btn || !row) return;
        if (btn.classList.contains("confirm")) { removeAttachment(+row.dataset.id); return; }
        btn.classList.add("confirm"); btn.textContent = "Remove?";
        setTimeout(() => { if (btn.isConnected) { btn.classList.remove("confirm"); btn.innerHTML = '<i class="bi bi-x-lg"></i>'; } }, 3000);
      });

      // ---- keyboard shortcuts ----
      document.addEventListener("keydown", e => {
        if (e.ctrlKey || e.metaKey || e.altKey) return;
        const tg = e.target, typing = tg && (/^(INPUT|TEXTAREA|SELECT)$/.test(tg.tagName) || tg.isContentEditable);
        if (typing || document.body.classList.contains("modal-open") || ocEl().classList.contains("show")) return;
        if (e.key === "/") { e.preventDefault(); $("#filterSearch").focus(); }
        else if (e.key === "n" || e.key === "N") { if (SA) { e.preventDefault(); openTaskModal(null); } }
        else if (e.key === "b" || e.key === "B") setView("board");
        else if (e.key === "l" || e.key === "L") setView("list");
      });

      // ---- silent refresh (teammates change things too) ----
      const busy = () => document.hidden || dragId !== null || document.body.classList.contains("modal-open") || ocEl().classList.contains("show");
      setInterval(() => { if (!busy()) loadTasks(true); }, 60000);
      document.addEventListener("visibilitychange", () => { if (!busy()) loadTasks(true); });

      loadTasks().then(() => {
        const taskId = new URLSearchParams(window.location.search).get("task");
        if (!taskId) return;
        openTaskDetail(parseInt(taskId, 10));
        const p = new URLSearchParams(window.location.search); p.delete("task");
        window.history.replaceState({}, "", window.location.pathname + (p.toString() ? "?" + p : ""));
      });
    }

    document.addEventListener("DOMContentLoaded", init);
  })();
</script>

@endsection