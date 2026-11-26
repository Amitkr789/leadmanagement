@extends('layouts.app')
@section('title', 'Leads | Dalal Adda')

@section('content')

<style>
  /* ==========================================================================
     Leads — HubSpot-style workspace (.lh-*). Uses the tokens from assets/css/style.css
     ========================================================================== */
  .page-content:has(.lh-wrap) { padding-bottom: 28px; }

  /* ---- status colours (set --c text/dot, --bg soft background) ---- */
  .st-hot       { --c: var(--danger);  --bg: var(--danger-bg); }
  .st-warm      { --c: var(--warning); --bg: var(--warning-bg); }
  .st-cold      { --c: var(--info);    --bg: var(--info-bg); }
  .st-converted { --c: var(--success); --bg: var(--success-bg); }
  .st-dealer    { --c: var(--gold);    --bg: var(--gold-bg); }
  .st-other     { --c: #6B7280;        --bg: #EEF1F6; }
  /* kept for other pages / the share card that still use these class names */
  .badge-status.lt-hot       { background: var(--danger-bg);  color: var(--danger); }
  .badge-status.lt-warm      { background: var(--warning-bg); color: var(--warning); }
  .badge-status.lt-cold      { background: var(--info-bg);    color: var(--info); }
  .badge-status.lt-converted { background: var(--success-bg); color: var(--success); }
  .badge-status.lt-dealer    { background: var(--gold-bg);    color: var(--gold); }

  .lh-pill { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; line-height: 1; padding: 6px 11px; border-radius: 999px; background: var(--bg); color: var(--c); white-space: nowrap; }
  .lh-pill .dot { width: 7px; height: 7px; border-radius: 50%; background: var(--c); }
  button.lh-pill { border: 0; cursor: pointer; transition: filter .12s, box-shadow .12s; }
  button.lh-pill:hover { filter: brightness(.96); box-shadow: 0 0 0 2px rgba(var(--primary-rgb), .12); }
  button.lh-pill .bi { font-size: 10px; opacity: .7; }

  .lh-wrap { --lh-top: 330px; }

  /* ---- header ---- */
  .lh-head { display: flex; align-items: flex-end; gap: 14px; flex-wrap: wrap; margin-bottom: 14px; }
  .lh-head h1 { font-size: 24px; margin: 0; display: flex; align-items: center; gap: 10px; }
  .lh-head h1 .cnt { font-size: 12.5px; font-weight: 700; color: var(--primary); background: var(--primary-50); border-radius: 999px; padding: 4px 11px; letter-spacing: 0; }
  .lh-head p { margin: 4px 0 0; color: var(--text-muted); font-size: 13.5px; }
  .lh-head .grow { flex: 1; min-width: 200px; }
  .lh-seg { display: inline-flex; background: var(--card-bg); border: 1px solid var(--border); border-radius: 11px; padding: 3px; gap: 2px; }
  .lh-seg button { border: 0; background: transparent; padding: 7px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; color: var(--text-muted); display: inline-flex; align-items: center; gap: 7px; }
  .lh-seg button:hover { color: var(--text); }
  .lh-seg button.active { background: var(--primary); color: #fff; box-shadow: 0 2px 6px rgba(var(--primary-rgb), .3); }
  .lh-create { display: inline-flex; align-items: center; gap: 8px; border: 0; border-radius: 11px; padding: 10px 18px; font-weight: 700; font-size: 14px; color: #fff; background: linear-gradient(135deg, var(--primary), var(--primary-light)); box-shadow: 0 6px 16px rgba(var(--primary-rgb), .26); transition: transform .12s, box-shadow .12s; }
  .lh-create:hover { transform: translateY(-1px); box-shadow: 0 10px 22px rgba(var(--primary-rgb), .32); }

  /* ---- tabs ---- */
  .lh-tabs { display: flex; gap: 4px; overflow-x: auto; border-bottom: 1px solid var(--border); margin-bottom: 14px; scrollbar-width: none; }
  .lh-tabs::-webkit-scrollbar { display: none; }
  .lh-tab { border: 0; background: transparent; padding: 10px 14px; font-size: 13.5px; font-weight: 600; color: var(--text-muted); white-space: nowrap; border-bottom: 2px solid transparent; margin-bottom: -1px; display: inline-flex; align-items: center; gap: 8px; }
  .lh-tab:hover { color: var(--text); }
  .lh-tab.active { color: var(--primary); border-bottom-color: var(--primary); }
  .lh-tab .n { font-size: 11px; font-weight: 700; background: #EEF0F7; color: var(--text-muted); border-radius: 999px; padding: 2px 8px; }
  .lh-tab.active .n { background: var(--primary-50); color: var(--primary); }
  .lh-tab .n.danger { background: var(--danger-bg); color: var(--danger); }

  /* ---- toolbar ---- */
  .lh-toolbar { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 12px; }
  .lh-search { position: relative; flex: 0 1 300px; min-width: 200px; }
  .lh-search i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); }
  .lh-search input.form-control, .modal-body .lh-search input.form-control { padding-left: 38px !important; padding-right: 30px !important; }
  .lh-search kbd { position: absolute; right: 9px; top: 50%; transform: translateY(-50%); font-size: 11px; background: var(--bg); color: var(--text-muted); border: 1px solid var(--border); border-radius: 5px; padding: 0 6px; }
  .lh-chip { display: inline-flex; align-items: center; gap: 6px; border: 1px solid var(--border); background: var(--card-bg); border-radius: 999px; padding: 7px 13px; font-size: 13px; font-weight: 600; color: var(--text); white-space: nowrap; transition: background .12s, border-color .12s; }
  .lh-chip:hover { background: var(--primary-50); border-color: var(--primary-100); }
  .lh-chip .bi-chevron-down { font-size: 10px; color: var(--text-muted); }
  .lh-chip.on { background: var(--primary-50); border-color: var(--primary-100); color: var(--primary); }
  .lh-chip .v { font-weight: 700; max-width: 150px; overflow: hidden; text-overflow: ellipsis; }
  .lh-link { border: 0; background: transparent; color: var(--primary); font-weight: 600; font-size: 13px; padding: 6px 8px; }
  .lh-link:hover { text-decoration: underline; }
  .lh-spacer { flex: 1; }
  .lh-ghost { display: inline-flex; align-items: center; gap: 7px; border: 1px solid var(--border); background: var(--card-bg); border-radius: 10px; padding: 7px 13px; font-size: 13px; font-weight: 600; color: var(--text); }
  .lh-ghost:hover { background: var(--primary-50); border-color: var(--primary-100); color: var(--primary); }

  /* ---- bulk bar ---- */
  .lh-bulk { display: none; align-items: center; gap: 10px; flex-wrap: wrap; background: var(--primary-50); border: 1px solid var(--primary-100); border-radius: 12px; padding: 8px 14px; margin-bottom: 12px; font-size: 13px; }
  .lh-bulk.show { display: flex; }
  .lh-bulk b { color: var(--primary); }

  /* ---- table ---- */
  .lh-card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 14px; box-shadow: var(--shadow-sm); overflow: hidden; }
  .lh-scroll { overflow: auto; max-height: calc(100vh - var(--lh-top)); min-height: 280px; }
  .lh-table { border-collapse: separate; border-spacing: 0; min-width: 100%; width: max-content; }
  .lh-table th { position: sticky; top: 0; z-index: 3; background: #F7F8FC; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); padding: 0 16px; height: 44px; border-bottom: 1px solid var(--border); white-space: nowrap; user-select: none; text-align: left; }
  .lh-table th.sortable { cursor: pointer; }
  .lh-table th.sortable:hover { color: var(--text); }
  .lh-table th .srt { margin-left: 5px; font-size: 10px; color: var(--primary); }
  .lh-table td { height: 54px; padding: 0 16px; border-bottom: 1px solid #F0F1F6; font-size: 13.5px; white-space: nowrap; background: var(--card-bg); max-width: 320px; overflow: hidden; text-overflow: ellipsis; }
  .lh-table tbody tr { cursor: pointer; }
  .lh-table tbody tr:hover td { background: #F8F9FF; }
  .lh-table tbody tr.sel td { background: var(--primary-50); }
  .lh-table .c-chk { width: 46px; min-width: 46px; max-width: 46px; padding: 0 0 0 16px; position: sticky; left: 0; z-index: 2; }
  .lh-table th.c-chk { z-index: 5; }
  .lh-table .c-name { position: sticky; left: 46px; z-index: 2; min-width: 250px; box-shadow: 1px 0 0 var(--border); }
  .lh-table th.c-name { z-index: 5; }
  .lh-table .c-end { position: sticky; right: 0; z-index: 2; width: 64px; min-width: 64px; padding: 0 10px; text-align: center; box-shadow: -1px 0 0 var(--border); }
  .lh-table th.c-end { z-index: 5; }
  .lh-name { display: flex; align-items: center; gap: 10px; min-width: 0; }
  .lh-name-col { display: flex; flex-direction: column; min-width: 0; }
  .lh-name-sub { display: none; font-size: 12px; color: var(--text-muted); overflow: hidden; text-overflow: ellipsis; }
  .lh-name-text { font-weight: 700; color: var(--primary); overflow: hidden; text-overflow: ellipsis; }
  .lh-pin { background: none; border: 0; padding: 2px; width: 18px; color: transparent; font-size: 13px; line-height: 1; flex-shrink: 0; }
  tr:hover .lh-pin { color: #CBCEDB; }
  .lh-pin:hover { color: var(--gold) !important; }
  .lh-pin.on { color: var(--gold) !important; }
  .lh-empty { color: #B3B7C7; }
  .lh-table a.lh-a { color: var(--text); }
  .lh-table a.lh-a:hover { color: var(--primary); text-decoration: underline; }
  .lh-wa { color: var(--success) !important; font-weight: 600; }
  .lh-co { display: inline-flex; align-items: center; gap: 7px; background: #F3F4FA; border-radius: 8px; padding: 4px 10px; font-size: 12.5px; font-weight: 600; max-width: 100%; overflow: hidden; text-overflow: ellipsis; }
  .lh-co i { color: var(--text-muted); }
  .lh-rem-chip { display: inline-flex; align-items: center; gap: 6px; font-size: 12.5px; font-weight: 600; color: var(--text); }
  .lh-rem-chip.late { color: var(--danger); }
  .lh-count { display: inline-flex; min-width: 24px; justify-content: center; font-size: 12px; font-weight: 700; padding: 2px 8px; border-radius: 999px; background: #EEF0F7; color: var(--text-muted); }
  .lh-count.hot { background: var(--danger-bg); color: var(--danger); }
  .lh-rowbtn { width: 32px; height: 32px; border-radius: 8px; border: 0; background: transparent; color: var(--text-muted); }
  .lh-rowbtn:hover { background: var(--primary-50); color: var(--primary); }
  .lh-plus { width: 34px; height: 34px; border-radius: 50%; border: 1.5px dashed #B9BDD3; background: #fff; color: var(--primary); font-size: 16px; display: inline-flex; align-items: center; justify-content: center; vertical-align: middle; transition: all .12s; }
  .lh-plus:hover { background: var(--primary); color: #fff; border-style: solid; border-color: var(--primary); transform: rotate(90deg); }
  .lh-foot { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; padding: 10px 16px; border-top: 1px solid var(--border); background: #FBFBFE; font-size: 13px; color: var(--text-muted); }
  .lh-foot select { width: auto; display: inline-block; font-size: 13px; padding: 4px 28px 4px 10px; }
  .lh-pages { display: flex; gap: 4px; margin-left: auto; }
  .lh-pages button { min-width: 32px; height: 32px; border-radius: 8px; border: 1px solid var(--border); background: #fff; font-size: 13px; font-weight: 600; color: var(--text); }
  .lh-pages button:hover:not(:disabled) { background: var(--primary-50); }
  .lh-pages button.active { background: var(--primary); color: #fff; border-color: var(--primary); }
  .lh-pages button:disabled { opacity: .4; }
  .lh-skel td > span { display: block; height: 12px; border-radius: 6px; background: linear-gradient(90deg, #F0F1F6, #E7E9F2, #F0F1F6); background-size: 200% 100%; animation: lhShimmer 1.2s infinite; width: 70%; }
  @keyframes lhShimmer { from { background-position: 200% 0; } to { background-position: -200% 0; } }

  .lh-emptybox { text-align: center; padding: 64px 20px; }
  .lh-emptybox .ic { width: 64px; height: 64px; border-radius: 20px; background: var(--primary-50); color: var(--primary); font-size: 28px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 14px; }
  .lh-emptybox h5 { margin: 0 0 6px; }
  .lh-emptybox p { color: var(--text-muted); margin: 0 0 16px; font-size: 13.5px; }

  /* ---- board ---- */
  .lh-board { display: flex; gap: 14px; overflow-x: auto; align-items: flex-start; height: calc(100vh - var(--lh-top) + 44px); min-height: 360px; padding-bottom: 10px; scroll-snap-type: x proximity; }
  .lh-col { flex: 0 0 300px; width: 300px; max-height: 100%; display: flex; flex-direction: column; background: #F1F2F9; border-radius: 14px; scroll-snap-align: start; border-top: 3px solid var(--c); }
  .lh-col.over { outline: 2px dashed var(--primary-light); outline-offset: -2px; background: var(--primary-50); }
  .lh-col-head { display: flex; align-items: center; gap: 8px; padding: 12px 12px 8px 14px; }
  .lh-col-head b { font-size: 13.5px; }
  .lh-col-head .n { font-size: 11.5px; font-weight: 700; background: #fff; color: var(--text-muted); border-radius: 999px; padding: 2px 9px; }
  .lh-col-head .dot { width: 9px; height: 9px; border-radius: 50%; background: var(--c); }
  .lh-col-head .add { margin-left: auto; }
  .lh-col-body { overflow-y: auto; padding: 4px 10px 12px; display: flex; flex-direction: column; gap: 9px; min-height: 70px; flex: 1; }
  .lh-card-k { background: #fff; border: 1px solid var(--border); border-radius: 12px; padding: 12px 13px; box-shadow: var(--shadow-sm); cursor: pointer; transition: box-shadow .12s, transform .12s; position: relative; }
  .lh-card-k:hover { box-shadow: var(--shadow); transform: translateY(-1px); }
  .lh-card-k.dragging { opacity: .35; }
  .lh-card-k .nm { font-weight: 700; font-size: 13.5px; display: flex; align-items: center; gap: 8px; padding-right: 26px; }
  .lh-card-k .nm span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .lh-card-k .ln { display: flex; align-items: center; gap: 7px; font-size: 12.5px; color: var(--text-muted); margin-top: 6px; min-width: 0; }
  .lh-card-k .ln span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .lh-card-k .ln i { flex-shrink: 0; width: 14px; text-align: center; }
  .lh-card-k .bt { display: flex; align-items: center; gap: 8px; margin-top: 10px; }
  .lh-card-k .menu { position: absolute; top: 8px; right: 6px; width: 28px; height: 28px; }
  .lh-card-k .pinmark { color: var(--gold); font-size: 12px; }
  .lh-more { border: 1px dashed #C9CDDD; background: transparent; color: var(--text-muted); border-radius: 10px; padding: 8px; font-size: 12.5px; font-weight: 600; }
  .lh-more:hover { background: #fff; color: var(--primary); }
  .lh-colempty { text-align: center; color: #A7ABC2; font-size: 12.5px; padding: 18px 8px; }

  /* ---- floating popover / menu ---- */
  .lh-pop { position: fixed; z-index: 2100; background: #fff; border: 1px solid var(--border); border-radius: 14px; box-shadow: var(--shadow-lg); min-width: 200px; max-width: min(360px, calc(100vw - 20px)); max-height: min(420px, calc(100vh - 20px)); overflow: auto; padding: 6px; animation: lhPop .12s ease-out; }
  @keyframes lhPop { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: none; } }
  .lh-pop .it { display: flex; align-items: center; gap: 10px; width: 100%; border: 0; background: transparent; text-align: left; padding: 8px 10px; border-radius: 8px; font-size: 13.5px; color: var(--text); }
  .lh-pop .it:hover, .lh-pop .it:focus-visible { background: var(--primary-50); outline: 0; }
  .lh-pop .it.danger { color: var(--danger); }
  .lh-pop .it.danger:hover { background: var(--danger-bg); }
  .lh-pop .it .ck { margin-left: auto; color: var(--primary); }
  .lh-pop .it > i:first-child { width: 16px; text-align: center; color: var(--text-muted); }
  .lh-pop .it .sdot { width: 10px; height: 10px; border-radius: 50%; background: var(--c); flex-shrink: 0; }
  .lh-pop hr { margin: 5px 4px; opacity: .6; }
  .lh-pop .hd { font-size: 11px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: var(--text-muted); padding: 8px 10px 4px; }
  .lh-pop .srch { padding: 4px; }
  .lh-pop label.it { cursor: pointer; }
  .lh-pop .cnt { margin-left: auto; font-size: 11px; font-weight: 700; background: #EEF0F7; color: var(--text-muted); border-radius: 999px; padding: 2px 8px; }
  .lh-pop .it.active .cnt { background: var(--primary); color: #fff; }
  .lh-pop .dates { display: flex; gap: 6px; align-items: center; padding: 6px 8px 8px; }
  .lh-pop .dates input { font-size: 12.5px; }

  /* ---- avatars (re-uses .avatar from style.css) ---- */
  .avatar-stack { display: flex; align-items: center; }
  .avatar-stack .avatar { width: 26px; height: 26px; font-size: 10px; border: 2px solid #fff; margin-left: -8px; }
  .avatar-stack .avatar:first-child { margin-left: 0; }
  .avatar-stack .avatar-more { background: var(--bg); color: var(--text-muted); }
  .unassigned-text { font-size: 12.5px; color: #B3B7C7; }

  /* ---- drawer ---- */
  .lh-drawer { width: 580px !important; max-width: 100vw; }
  .lh-drawer .offcanvas-header { display: none; }
  .lh-drawer .offcanvas-body { padding: 0; display: flex; flex-direction: column; overflow: hidden; }
  .lh-dr-top { padding: 18px 22px 0; background: linear-gradient(180deg, var(--primary-50), #fff); }
  .lh-dr-bar { display: flex; align-items: center; gap: 4px; margin-bottom: 12px; font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: .06em; }
  .lh-dr-bar .sp { flex: 1; }
  .lh-iconbtn { width: 34px; height: 34px; border-radius: 50%; border: 0; background: transparent; color: var(--text-muted); display: inline-flex; align-items: center; justify-content: center; transition: background .12s, color .12s; }
  .lh-iconbtn:hover { background: #fff; color: var(--text); box-shadow: var(--shadow-sm); }
  .lh-iconbtn.pinned { color: var(--gold); }
  .lh-iconbtn.danger:hover { background: var(--danger-bg); color: var(--danger); }
  .lh-dr-id { display: flex; gap: 14px; align-items: center; }
  .lh-dr-id .avatar { width: 58px; height: 58px; font-size: 20px; }
  .lh-dr-name { font-size: 20px; font-weight: 800; letter-spacing: -.01em; line-height: 1.2; margin: 0; word-break: break-word; }
  .lh-dr-sub { color: var(--text-muted); font-size: 13px; margin-top: 3px; display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
  .lh-dr-quick { display: flex; gap: 6px; margin: 16px 0 14px; }
  .lh-q { display: flex; flex-direction: column; align-items: center; gap: 5px; flex: 1 1 0; min-width: 0; max-width: 76px; white-space: nowrap; border: 0; background: transparent; font-size: 11.5px; font-weight: 600; color: var(--text-muted); text-decoration: none; }
  .lh-q .ic { width: 42px; height: 42px; border-radius: 50%; background: #fff; border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; font-size: 17px; color: var(--primary); box-shadow: var(--shadow-sm); transition: all .12s; }
  .lh-q:hover .ic { background: var(--primary); color: #fff; border-color: var(--primary); transform: translateY(-1px); }
  .lh-q.wa .ic { color: var(--success); } .lh-q.wa:hover .ic { background: var(--success); color: #fff; border-color: var(--success); }
  .lh-q.off { opacity: .4; pointer-events: none; }
  .lh-dr-tabs { display: flex; gap: 2px; margin: 0 -22px; padding: 0 22px; border-bottom: 1px solid var(--border); background: #fff; }
  .lh-dr-tab { border: 0; background: transparent; padding: 11px 14px; font-weight: 600; font-size: 13.5px; color: var(--text-muted); border-bottom: 2px solid transparent; margin-bottom: -1px; display: inline-flex; gap: 7px; align-items: center; }
  .lh-dr-tab.active { color: var(--primary); border-bottom-color: var(--primary); }
  .lh-dr-tab .n { font-size: 11px; background: #EEF0F7; border-radius: 999px; padding: 1px 7px; color: var(--text-muted); }
  .lh-dr-body { flex: 1; overflow-y: auto; padding: 18px 22px 40px; background: var(--bg); }
  .lh-sect { background: #fff; border: 1px solid var(--border); border-radius: 14px; margin-bottom: 14px; overflow: hidden; }
  .lh-sect-h { display: flex; align-items: center; gap: 8px; padding: 12px 16px; font-weight: 700; font-size: 13.5px; border-bottom: 1px solid #F0F1F6; }
  .lh-sect-h .sp { flex: 1; }
  .lh-prop { display: grid; grid-template-columns: 130px 1fr; gap: 10px; align-items: center; padding: 3px 12px 3px 16px; min-height: 44px; }
  .lh-prop + .lh-prop { border-top: 1px solid #F6F7FB; }
  .lh-prop-l { font-size: 12.5px; color: var(--text-muted); font-weight: 600; }
  .lh-prop-v { display: flex; align-items: center; gap: 8px; min-height: 34px; padding: 4px 10px; margin: 0 -10px; border-radius: 8px; border: 1px solid transparent; cursor: text; font-size: 13.5px; word-break: break-word; position: relative; }
  .lh-prop-v:hover { background: #F6F7FD; border-color: var(--border); }
  .lh-prop-v .pen { margin-left: auto; opacity: 0; color: var(--text-muted); font-size: 12px; }
  .lh-prop-v:hover .pen, .lh-prop-v:focus-visible .pen { opacity: 1; }
  .lh-prop-v:focus-visible { outline: 2px solid var(--primary-light); }
  .lh-prop-v.ro { cursor: default; }
  .lh-prop-v.ro:hover { background: transparent; border-color: transparent; }
  .lh-prop-v .ph { color: #B3B7C7; }
  .lh-prop-v input, .lh-prop-v select { width: 100%; }
  .lh-saving { opacity: .55; pointer-events: none; }
  .lh-co-card { padding: 14px 16px; display: flex; gap: 12px; align-items: center; }
  .lh-co-ic { width: 42px; height: 42px; border-radius: 12px; background: var(--primary-50); color: var(--primary); font-size: 19px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
  .lh-co-card .nm { font-weight: 700; }
  .lh-co-card small { color: var(--text-muted); display: block; font-size: 12px; }

  /* attachments */
  .attachment-item { display: flex; align-items: center; gap: 10px; padding: 10px 16px; border-top: 1px solid #F0F1F6; }
  .attachment-item:first-child { border-top: 0; }
  .attachment-icon { width: 36px; height: 36px; border-radius: 10px; background: var(--primary-50); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 17px; flex-shrink: 0; }
  .attachment-name { font-size: 13px; font-weight: 600; word-break: break-all; }
  .attachment-meta { font-size: 11.5px; color: var(--text-muted); }
  .lh-drop { border: 2px dashed #C9CDDD; border-radius: 14px; padding: 20px; text-align: center; color: var(--text-muted); font-size: 13px; background: #fff; cursor: pointer; margin-bottom: 14px; transition: all .12s; }
  .lh-drop:hover, .lh-drop.over { border-color: var(--primary); background: var(--primary-50); color: var(--primary); }
  .lh-drop i { font-size: 24px; display: block; margin-bottom: 4px; }
  .attachment-progress { margin-bottom: 14px; padding: 10px 12px; border: 1px solid var(--border); border-radius: 10px; background: #fff; }
  .attachment-progress-info { display: flex; justify-content: space-between; font-size: 12.5px; margin-bottom: 6px; }
  .attachment-progress-name { font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 300px; }
  .attachment-progress-pct { color: var(--primary); font-weight: 700; margin-left: 8px; }
  .attachment-progress-track { height: 6px; border-radius: 4px; background: var(--border); overflow: hidden; }
  .attachment-progress-fill { height: 100%; width: 0; background: var(--primary); transition: width .15s ease; }

  /* ---- create-lead stepper ---- */
  .lh-steps { display: flex; align-items: center; gap: 8px; margin-bottom: 20px; }
  .lh-step { display: flex; align-items: center; gap: 9px; font-size: 13px; font-weight: 700; color: var(--text-muted); }
  .lh-step .no { width: 28px; height: 28px; border-radius: 50%; background: #EEF0F7; display: flex; align-items: center; justify-content: center; font-size: 12.5px; }
  .lh-step.active { color: var(--primary); } .lh-step.active .no { background: var(--primary); color: #fff; box-shadow: 0 0 0 4px rgba(var(--primary-rgb), .14); }
  .lh-step.done { color: var(--success); } .lh-step.done .no { background: var(--success); color: #fff; }
  .lh-steps .bar { flex: 1; height: 2px; background: var(--border); border-radius: 2px; min-width: 18px; }
  .lh-steps .bar.done { background: var(--success); }
  .lh-pane h6 { font-size: 16px; margin: 0 0 4px; }
  .lh-pane .hint { color: var(--text-muted); font-size: 13px; margin-bottom: 14px; }
  .lh-res { border: 1px solid var(--border); border-radius: 12px; overflow: hidden; margin-top: 10px; }
  .lh-res button { display: flex; align-items: center; gap: 12px; width: 100%; border: 0; border-top: 1px solid #F0F1F6; background: #fff; padding: 10px 14px; text-align: left; }
  .lh-res button:first-child { border-top: 0; }
  .lh-res button:hover, .lh-res button.act { background: var(--primary-50); }
  .lh-res .t { font-weight: 700; font-size: 13.5px; }
  .lh-res .s { font-size: 12px; color: var(--text-muted); }
  .lh-res .r { margin-left: auto; font-size: 11.5px; color: var(--text-muted); white-space: nowrap; text-align: right; line-height: 1.4; }
  .lh-res .s { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .lh-res button > div:not(.lh-co-ic):not(.avatar) { min-width: 0; }
  .lh-sel { display: flex; align-items: center; gap: 12px; border: 1.5px solid var(--primary); background: var(--primary-50); border-radius: 12px; padding: 12px 14px; }
  .lh-sel .t { font-weight: 700; } .lh-sel .s { font-size: 12.5px; color: var(--text-muted); }
  .lh-sel .x { margin-left: auto; }
  .lh-newform { border: 1px solid var(--border); border-radius: 12px; padding: 14px; margin-top: 12px; background: #FBFBFE; }
  .lh-newform label { font-size: 12.5px; font-weight: 600; margin-bottom: 4px; }
  .lh-alert { border-radius: 10px; padding: 10px 13px; font-size: 13px; margin-top: 10px; display: flex; gap: 10px; align-items: center; background: var(--warning-bg); color: #8A4B05; }
  .lh-alert .btn { margin-left: auto; white-space: nowrap; }
  .lh-sumrow { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 16px; }
  .lh-sumchip { display: inline-flex; align-items: center; gap: 8px; background: #F3F4FA; border-radius: 10px; padding: 7px 12px; font-size: 13px; font-weight: 600; }
  .lh-statuspick { display: flex; flex-wrap: wrap; gap: 8px; }
  .lh-statuspick button { border: 1.5px solid var(--border); background: #fff; border-radius: 999px; padding: 7px 14px; font-size: 13px; font-weight: 700; color: var(--text-muted); display: inline-flex; align-items: center; gap: 7px; }
  .lh-statuspick button .dot { width: 8px; height: 8px; border-radius: 50%; background: var(--c); }
  .lh-statuspick button.on { border-color: var(--c); background: var(--bg); color: var(--c); }
  .lh-assignbtn { width: 100%; text-align: left; background: #FBFBFE; border: 1.5px solid var(--border); border-radius: 10px; padding: 9px 13px; font-size: 13.5px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; min-height: 42px; }
  .lh-assignbtn .ph { color: #A7ABC2; }
  .lh-assignbtn .chip { background: var(--primary-50); color: var(--primary); font-size: 12px; font-weight: 700; padding: 2px 9px; border-radius: 999px; }

  /* ---- columns modal ---- */
  .lh-cols { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
  .lh-cols h6 { font-size: 12px; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); margin: 14px 0 6px; }
  .lh-cols .lst { max-height: 380px; overflow-y: auto; }
  .lh-colrow { display: flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 9px; font-size: 13.5px; border: 1px solid transparent; }
  .lh-colrow:hover { background: var(--primary-50); }
  .lh-colrow .sp { flex: 1; }
  .lh-colrow button { border: 0; background: transparent; width: 26px; height: 26px; border-radius: 7px; color: var(--text-muted); }
  .lh-colrow button:hover { background: #fff; color: var(--primary); }
  .lh-colrow.sel { background: #F7F8FC; border-color: var(--border); margin-bottom: 5px; cursor: grab; }
  .lh-colrow.sel.dragging { opacity: .4; }
  .lh-colrow.sel.dropt { border-color: var(--primary); box-shadow: 0 -2px 0 var(--primary); }
  .lh-colrow .lock { color: #B3B7C7; font-size: 12px; }
  .lh-colrow .grip { color: #B3B7C7; cursor: grab; }
  @media (max-width: 767.98px) { .lh-cols { grid-template-columns: 1fr; } }

  /* ---- assign / view-as (kept from the old page; used by pickers) ---- */
  .share-lead-card { width: 340px; border-radius: 18px; overflow: hidden; background: #fff; border: 1px solid var(--border); box-shadow: var(--shadow-lg); font-family: inherit; }
  .share-lead-card .slc-header { background: linear-gradient(135deg, var(--primary) 0%, #4338CA 100%); padding: 22px 20px 44px; color: #fff; position: relative; }
  .share-lead-card .slc-brand { font-size: 11px; letter-spacing: .08em; text-transform: uppercase; opacity: .85; font-weight: 700; }
  .share-lead-card .slc-avatar { width: 56px; height: 56px; border-radius: 50%; background: #fff; color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 20px; position: absolute; left: 20px; bottom: -28px; border: 3px solid #fff; box-shadow: var(--shadow-lg); }
  .share-lead-card .slc-body { padding: 36px 20px 18px; }
  .share-lead-card .slc-name { font-size: 17px; font-weight: 800; color: var(--text); }
  .share-lead-card .slc-product { font-size: 13px; color: var(--text-muted); margin-bottom: 12px; }
  .share-lead-card .slc-row { display: flex; justify-content: space-between; padding: 7px 0; border-top: 1px dashed var(--border); font-size: 12.5px; }
  .share-lead-card .slc-row span:first-child { color: var(--text-muted); }
  .share-lead-card .slc-row span:last-child { font-weight: 700; color: var(--text); text-align: right; }
  .share-lead-card .slc-footer { text-align: center; font-size: 10.5px; color: var(--text-muted); padding: 12px 0 2px; }

  /* ---- responsive ---- */
  @media (min-width: 768px) {
    #lhColsModal .modal-dialog, #lhCreateModal .modal-dialog { max-width: 700px; }
  }
  .lh-colrow > span:nth-child(2) { white-space: nowrap; }
  @media (max-width: 767.98px) {
    .lh-name-sub { display: block; }
    .lh-table .c-chk, .lh-table .c-name { position: static; box-shadow: none; }
    .lh-table .c-name { min-width: 220px; }
    .lh-table td { height: 60px; }
    .lh-wrap { --lh-top: 360px; }
    .lh-head h1 { font-size: 21px; }
    .lh-head .lh-seg { order: 3; }
    .lh-create span.t { display: none; }
    .lh-create { padding: 10px 14px; }
    .lh-search { flex: 1 1 100%; }
    .lh-col { flex-basis: 84vw; width: 84vw; }
    .lh-prop { grid-template-columns: 1fr; gap: 0; padding-top: 8px; padding-bottom: 8px; }
    .lh-prop-v { margin: 0 -10px; }
    .lh-dr-top, .lh-dr-body { padding-left: 16px; padding-right: 16px; }
    .lh-dr-tabs { margin: 0 -16px; padding: 0 16px; }
    #lhCreateModal .modal-dialog { margin: 0; max-width: none; min-height: 100%; }
    #lhCreateModal .modal-content { min-height: 100vh; border-radius: 0; }
    .lh-step span.l { display: none; }
  }
  @media (prefers-reduced-motion: reduce) { .lh-pop, .lh-skel td > span { animation: none; } }
</style>

<div class="page-content">
<div class="lh-wrap" id="lhWrap">

  <!-- ===================== HEADER ===================== -->
  <div class="lh-head">
    <div class="grow">
      <h1>Leads <span class="cnt" id="lhCount">0</span></h1>
      <p>Start with a contact, link a company, and move every lead through your pipeline.</p>
    </div>
    <div class="lh-seg" role="tablist" aria-label="View">
      <button type="button" data-view="table" class="active"><i class="bi bi-table"></i><span>Table</span></button>
      <button type="button" data-view="board"><i class="bi bi-kanban"></i><span>Board</span></button>
    </div>
    <button type="button" class="lh-create" id="openAddLeadBtn"><i class="bi bi-plus-lg"></i><span class="t">Create lead</span></button>
  </div>

  <!-- ===================== TABS ===================== -->
  <div class="lh-tabs" id="lhTabs">
    <button type="button" class="lh-tab active" data-cat="all">All leads <span class="n" data-n="all">0</span></button>
    <button type="button" class="lh-tab" data-cat="mine">My leads <span class="n" data-n="mine">0</span></button>
    <button type="button" class="lh-tab" data-cat="pinned"><i class="bi bi-pin-angle-fill"></i> Pinned <span class="n" data-n="pinned">0</span></button>
    <button type="button" class="lh-tab" data-cat="scheduled">Scheduled <span class="n" data-n="scheduled">0</span></button>
    <button type="button" class="lh-tab" data-cat="missed">Missed <span class="n danger" data-n="missed">0</span></button>
    <button type="button" class="lh-tab" data-cat="silent">Silenced <span class="n" data-n="silent">0</span></button>
  </div>

  <!-- ===================== TOOLBAR ===================== -->
  <div class="lh-toolbar">
    <div class="lh-search">
      <i class="bi bi-search"></i>
      <input type="text" class="form-control" id="lhSearch" placeholder="Search leads..." autocomplete="off">
      <kbd>/</kbd>
    </div>
    <button type="button" class="lh-chip" id="lhChipStatus"><span>Lead status</span><i class="bi bi-chevron-down"></i></button>
    <button type="button" class="lh-chip" id="lhChipSource"><span>Source</span><i class="bi bi-chevron-down"></i></button>
    <button type="button" class="lh-chip" id="lhChipDate"><span>Create date</span><i class="bi bi-chevron-down"></i></button>
    @if($isSuperAdmin)
    <button type="button" class="lh-chip" id="lhChipOwner"><i class="bi bi-people"></i><span>All users</span><i class="bi bi-chevron-down"></i></button>
    @endif
    <button type="button" class="lh-link d-none" id="lhClear">Clear all</button>
    <div class="lh-spacer"></div>
    <button type="button" class="lh-ghost" id="lhColsBtn"><i class="bi bi-layout-three-columns"></i><span>Edit columns</span></button>
  </div>

  <!-- ===================== BULK BAR ===================== -->
  <div class="lh-bulk" id="lhBulk">
    <b><span id="lhBulkCount">0</span> selected</b>
    <button type="button" class="lh-ghost" id="lhBulkStatus"><i class="bi bi-flag"></i>Change status</button>
    <button type="button" class="lh-ghost" id="lhBulkDelete" style="color:var(--danger)"><i class="bi bi-trash"></i>Delete</button>
    <button type="button" class="lh-link ms-auto" id="lhBulkClear">Clear selection</button>
  </div>

  <!-- ===================== TABLE VIEW ===================== -->
  <div class="lh-card" id="lhTableWrap">
    <div class="lh-scroll" id="lhScroll">
      <table class="lh-table">
        <thead id="lhThead"></thead>
        <tbody id="lhTbody"></tbody>
      </table>
    </div>
    <div class="lh-foot" id="lhFoot">
      <span id="lhPageInfo"></span>
      <span>Rows: <select class="form-select form-select-sm" id="lhPageSize"><option>10</option><option>25</option><option>50</option><option>100</option></select></span>
      <div class="lh-pages" id="lhPages"></div>
    </div>
  </div>

  <!-- ===================== BOARD VIEW ===================== -->
  <div class="lh-board d-none" id="lhBoard"></div>

</div>
</div>

<!-- ===================== LEAD DRAWER ===================== -->
<div class="offcanvas offcanvas-end lh-drawer" tabindex="-1" id="lhDrawer" aria-label="Lead details">
  <div class="offcanvas-header"><h5 class="offcanvas-title">Lead</h5></div>
  <div class="offcanvas-body">
    <div class="lh-dr-top">
      <div id="lhDrTop"></div>
      <div class="lh-dr-tabs" id="lhDrTabs">
        <button type="button" class="lh-dr-tab active" data-pane="about">About</button>
        <button type="button" class="lh-dr-tab" data-pane="activity">Activity <span class="n" id="lhNActivity">0</span></button>
        <button type="button" class="lh-dr-tab" data-pane="files">Files <span class="n" id="lhNFiles">0</span></button>
      </div>
    </div>
    <div class="lh-dr-body" id="lhDrBody">

      <div id="lhPaneAbout"></div>

      <div id="lhPaneActivity" class="d-none"><div id="lhActivity"></div></div>

      <div id="lhPaneFiles" class="d-none">
        <label class="lh-drop" id="ldAttachmentUploadBtn" for="ldAttachmentInput">
          <i class="bi bi-cloud-arrow-up"></i>
          <b>Click to choose a file</b> or drop it here
          <input type="file" id="ldAttachmentInput" hidden>
        </label>
        <div class="attachment-progress d-none" id="ldAttachmentUploadProgress">
          <div class="attachment-progress-info">
            <span class="attachment-progress-name" id="ldAttachmentUploadName"></span>
            <span class="attachment-progress-pct" id="ldAttachmentUploadPct">0%</span>
          </div>
          <div class="attachment-progress-track"><div class="attachment-progress-fill" id="ldAttachmentUploadFill"></div></div>
        </div>
        <div class="lh-sect" id="ldAttachments"></div>
      </div>

    </div>
  </div>

  <!-- Floating, draggable composer (Note / Follow-up) -->
  <div class="lh-win is-note" id="lhWin" role="dialog" aria-label="Add note" hidden>
    <div class="lh-win-h" id="lhWinHandle" tabindex="0" title="Drag to move · double-click to reset">
      <button type="button" class="lh-win-ic" data-win="min" title="Minimize" aria-label="Minimize"><i class="bi bi-chevron-down"></i></button>
      <div class="lh-win-modes" id="lhWinModes" role="tablist">
        <button type="button" role="tab" data-mode="note" class="on"><i class="bi bi-sticky"></i>Note</button>
        <button type="button" role="tab" data-mode="fu"><i class="bi bi-alarm"></i>Follow-up</button>
      </div>
      <div class="sp"><i class="bi bi-grip-horizontal"></i></div>
      <button type="button" class="lh-win-ic" data-win="big" title="Expand" aria-label="Expand"><i class="bi bi-arrows-angle-expand"></i></button>
      <button type="button" class="lh-win-ic" data-win="close" title="Close" aria-label="Close"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="lh-win-body">
      <div class="lh-win-for"><span class="lbl">For</span><span class="lh-for-chip" id="lhWinFor"></span></div>

      <div class="lh-win-ed">
        <textarea id="lhWinText" maxlength="1000" rows="4" aria-label="Text"></textarea>
        <div class="lh-win-tools only-note">
          <button type="button" data-fmt="b" title="Bold (Ctrl+B)" aria-label="Bold"><i class="bi bi-type-bold"></i></button>
          <button type="button" data-fmt="i" title="Italic (Ctrl+I)" aria-label="Italic"><i class="bi bi-type-italic"></i></button>
          <button type="button" data-fmt="s" title="Strikethrough" aria-label="Strikethrough"><i class="bi bi-type-strikethrough"></i></button>
          <button type="button" data-fmt="list" title="Bulleted list" aria-label="Bulleted list"><i class="bi bi-list-ul"></i></button>
          <button type="button" data-fmt="link" title="Insert link" aria-label="Insert link"><i class="bi bi-link-45deg"></i></button>
          <span class="cnt" id="lhWinCount">0 / 1000</span>
        </div>
        <div class="lh-win-tools only-fu"><span class="cnt" id="lhWinCountFu">0 / 1000</span></div>
      </div>

      <!-- follow-up mode: when + type -->
      <div class="lh-win-sec only-fu">
        <div class="lh-win-lbl">When</div>
        <div class="lh-chips" id="lhWinChips"></div>
        <div class="lh-win-dt">
          <input type="date" class="form-control" data-wd aria-label="Date">
          <input type="time" class="form-control" data-wt aria-label="Time">
        </div>
        <div class="lh-win-sum"><i class="bi bi-calendar-check"></i><span id="lhWinSum"></span></div>
        <div class="lh-win-lbl" style="margin-top:14px">Type</div>
        <div class="apt-type-group" id="lhWinApt">
          <div class="apt-type-option"><input type="radio" name="lhWinApt" id="lhAptAny" value="" checked><label for="lhAptAny"><i class="bi bi-alarm"></i>Any</label></div>
          <div class="apt-type-option"><input type="radio" name="lhWinApt" id="lhAptWa" value="whatsapp"><label for="lhAptWa"><i class="bi bi-whatsapp"></i>WhatsApp</label></div>
          <div class="apt-type-option"><input type="radio" name="lhWinApt" id="lhAptMeet" value="meeting"><label for="lhAptMeet"><i class="bi bi-camera-video"></i>Meeting</label></div>
        </div>
      </div>

      <!-- both modes: related records -->
      <div class="lh-win-sec">
        <button type="button" class="lh-win-relbtn" id="lhWinRelBtn" aria-expanded="false"></button>
        <div class="lh-win-rellist d-none" id="lhWinRelList"></div>
      </div>

      <!-- note mode: optional follow-up -->
      <div class="lh-win-sec only-note">
        <div class="lh-win-fucheck">
          <input type="checkbox" class="form-check-input m-0" id="lhWinFuCheck">
          <label for="lhWinFuCheck">Create a follow-up reminder</label>
          <button type="button" class="lh-whenbtn" id="lhWinWhenBtn"></button>
        </div>
        <div class="lh-win-dt d-none" id="lhWinNoteDt">
          <input type="date" class="form-control" data-wd aria-label="Follow-up date">
          <input type="time" class="form-control" data-wt aria-label="Follow-up time">
        </div>
      </div>

      <div class="lh-win-foot">
        <span class="lh-win-msg" id="lhWinMsg" role="alert"></span>
        <button type="button" class="btn btn-primary btn-sm" id="lhWinSubmit">Create note</button>
      </div>
    </div>
  </div>
</div>

<!-- ===================== CREATE LEAD (Contact → Company → Lead) ===================== -->
<div class="modal fade" id="lhCreateModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Create lead</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="lh-steps" id="lhSteps"></div>
        <div id="lhCreateBody"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary me-auto" id="lhCrBack">Back</button>
        <button type="button" class="btn btn-outline-secondary d-none" id="lhCrSkip">Skip company</button>
        <button type="button" class="btn btn-primary" id="lhCrNext">Continue</button>
      </div>
    </div>
  </div>
</div>

<!-- ===================== EDIT COLUMNS ===================== -->
<div class="modal fade" id="lhColsModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit columns</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="lh-cols">
          <div>
            <h6 style="margin-top:0">Available properties</h6>
            <input type="text" class="form-control mb-2" id="lhColsSearch" placeholder="Search properties...">
            <div class="lst" id="lhColsAvail"></div>
          </div>
          <div>
            <h6 style="margin-top:0">Selected columns (<span id="lhColsN">0</span>)</h6>
            <div class="lst" id="lhColsSel"></div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary me-auto" id="lhColsReset">Reset to default</button>
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Done</button>
      </div>
    </div>
  </div>
</div>

<!-- ===================== SHARE CARD ===================== -->
<div class="modal fade" id="shareCardModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Share lead</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div style="display:flex;justify-content:center;padding:6px"><div id="shareCard" class="share-lead-card"></div></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" id="shareCardDownloadBtn"><i class="bi bi-download me-1"></i>Download</button>
        <button type="button" class="btn btn-primary" id="shareCardShareBtn"><i class="bi bi-share-fill me-1"></i>Share</button>
      </div>
    </div>
  </div>
</div>

<!-- ===================== DELETE CONFIRM ===================== -->
<div class="modal fade" id="lhDeleteModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-content-compact">
    <div class="modal-content modal-content-danger">
      <div class="modal-header">
        <h5 class="modal-title" id="lhDeleteTitle">Delete lead?</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body"><p class="mb-0">This permanently deletes the lead along with its follow-ups, notes and files. The contact and company are kept. This can't be undone.</p></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="confirmDeleteBtn">Delete</button>
      </div>
    </div>
  </div>
</div>

<!-- html2canvas turns the share card into a PNG. -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
  window.LEAD_PAGE_DATA = {
    admins: @json($admins),
    leadTypes: @json($leadTypes),
    sources: @json($sources ?? []),
    platforms: @json($platforms ?? []),
    csrfToken: '{{ csrf_token() }}',
    isSuperAdmin: @json($isSuperAdmin),
    dealStages: @json(class_exists(\App\Models\Deal::class) ? \App\Models\Deal::STAGES : []),
    dealOpenStages: @json(class_exists(\App\Models\Deal::class) ? \App\Models\Deal::OPEN_STAGES : []),
    routes: {
      data: '{{ route('leads.data') }}',
      store: '{{ route('leads.store') }}',
      update: '{{ url('admin/leads') }}/__ID__',
      destroy: '{{ url('admin/leads') }}/__ID__',
      show: '{{ url('admin/leads') }}/__ID__',
      patch: '{{ url('admin/leads') }}/__ID__',
      togglePin: '{{ url('admin/leads') }}/__ID__/toggle-pin',
      updateType: '{{ url('admin/leads') }}/__ID__/type',
      bulkDelete: '{{ route('leads.bulkDestroy') }}',
      bulkType: '{{ route('leads.bulkType') }}',
      reminderStore: '{{ route('leads.reminders.store') }}',
      reminderStatus: '{{ url('admin/leads/reminders') }}/__ID__/status',
      reminderDestroy: '{{ url('admin/leads/reminders') }}/__ID__',
      attachmentStore: '{{ route('leads.attachments.store') }}',
      attachmentDestroy: '{{ url('admin/leads/attachments') }}/__ID__',
      contactsSearch: '{{ route('leads.contacts.search') }}',
      contactStore: '{{ route('leads.contacts.store') }}',
      companiesSearch: '{{ route('leads.companies.search') }}',
      companyStore: '{{ route('leads.companies.store') }}',
      companyUpdate: '{{ url('admin/leads/companies') }}/__ID__',
      dealsSearch: '{{ route('leads.deals.search') }}',
      profile: '{{ url('admin/leads') }}/__ID__/view',
      reminderUpdate: '{{ url('admin/leads/reminders') }}/__ID__',
      deal: '{{ url('admin/deals') }}?deal=__ID__',
      dealsIndex: '{{ url('admin/deals') }}',
      contactOpen: '{{ url('admin/contacts') }}?id=__ID__',
      companyOpen: '{{ url('admin/companies') }}?id=__ID__',
    }
  };

  /* ==========================================================================
     Leads workspace (HubSpot-style): Table + Board, record drawer with inline editing,
     Contact -> Company -> Lead create flow, column chooser.

     The reminder ALARM POPUP + 30s poll live in /js/global-reminders.js. This page
     exposes window.openLeadDetailById so that script can open a lead without a reload.
     ========================================================================== */
(function () {
  "use strict";

  const CFG = window.LEAD_PAGE_DATA;
  const CSRF = CFG.csrfToken;
  const TYPES = CFG.leadTypes || [];
  const SOURCES = (CFG.sources && CFG.sources.length) ? CFG.sources : ["Website", "Referral", "Cold Call", "Social Media", "Walk-in", "Email Campaign", "Trade Show", "Other"];
  const PLATFORMS = (CFG.platforms && CFG.platforms.length) ? CFG.platforms : ["Facebook", "Instagram", "LinkedIn", "WhatsApp", "Other"];
  const AVATAR_COLORS = ["#4338CA", "#0891B2", "#B45309", "#16A34A", "#DC2626", "#6D28D9", "#0D9488"];
  const $ = id => document.getElementById(id);
  const DASH = '<span class="lh-empty">—</span>';

  /* ---------------- helpers ---------------- */

  function esc(s) {
    return String(s == null ? "" : s).replace(/[&<>"']/g, c => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));
  }
  function lsGet(k, d) { try { const v = localStorage.getItem(k); return v == null ? d : JSON.parse(v); } catch (e) { return d; } }
  function lsSet(k, v) { try { localStorage.setItem(k, JSON.stringify(v)); } catch (e) { /* private mode */ } }
  function initials(name) { return (name || "?").trim().split(/\s+/).slice(0, 2).map(p => p[0]).join("").toUpperCase() || "?"; }
  function colorFor(name) {
    let h = 0;
    for (let i = 0; i < (name || "").length; i++) h = name.charCodeAt(i) + ((h << 5) - h);
    return AVATAR_COLORS[Math.abs(h) % AVATAR_COLORS.length];
  }
  function avatarHtml(name, size) {
    const cls = size === "lg" ? "avatar avatar-lg" : size === "sm" ? "avatar avatar-sm" : "avatar";
    return `<div class="${cls}" style="background:${colorFor(name)}">${esc(initials(name))}</div>`;
  }
  const typeLabel = t => t ? t.charAt(0).toUpperCase() + t.slice(1) : "";
  const stCls = t => TYPES.indexOf(t) >= 0 ? "st-" + t : "st-other";
  function pill(type, interactive, id) {
    const inner = `<span class="dot"></span>${esc(typeLabel(type))}`;
    return interactive
      ? `<button type="button" class="lh-pill ${stCls(type)}" data-act="status" data-id="${id}">${inner}<i class="bi bi-chevron-down"></i></button>`
      : `<span class="lh-pill ${stCls(type)}">${inner}</span>`;
  }
  function digits(n) { return (n || "").replace(/\D/g, ""); }
  function waUrl(n) { const d = digits(n); return d ? `whatsapp://send?phone=${d}` : null; }
  function fmtDate(d) {
    if (!d) return "";
    const dt = new Date(d);
    if (isNaN(dt)) return d;
    return dt.toLocaleDateString(undefined, { day: "numeric", month: "short", year: "numeric" });
  }
  function fmtTime(t) {
    if (!t) return "";
    const [h, m] = t.split(":");
    const hr = parseInt(h, 10);
    return `${((hr + 11) % 12) + 1}:${m} ${hr >= 12 ? "PM" : "AM"}`;
  }
  /* Reminder dates must be plain "YYYY-MM-DD" and times "HH:mm". If the server (or a model cast) sends an ISO datetime
     such as "2026-11-23T18:30:00.000000Z" (= midnight IST in UTC), taking its first 10 characters gives the PREVIOUS day,
     so a follow-up set for today shows as "Yesterday". Convert to the viewer's local date instead. */
  function normDate(v) {
    if (!v) return v;
    v = String(v);
    if (/^\d{4}-\d{2}-\d{2}$/.test(v)) return v;
    if (/^\d{4}-\d{2}-\d{2}[T ]00:00:00(\.0+)?(Z|[+-]00:?00)?$/.test(v)) return v.slice(0, 10);   // date-only value sent as UTC midnight
    const d = new Date(v);
    return isNaN(d) ? v.slice(0, 10) : ymd(d);
  }
  function normTime(v) {
    if (!v) return v;
    v = String(v);
    if (/^\d{1,2}:\d{2}(:\d{2})?$/.test(v)) return v.slice(0, 5);
    const d = new Date(v);
    return isNaN(d) ? v : String(d.getHours()).padStart(2, "0") + ":" + String(d.getMinutes()).padStart(2, "0");
  }
  function normLead(l) {
    if (l && Array.isArray(l.reminders)) l.reminders.forEach(r => { r.reminder_date = normDate(r.reminder_date); r.reminder_time = normTime(r.reminder_time); });
    return l;
  }
  function ymd(dt) { return dt.getFullYear() + "-" + String(dt.getMonth() + 1).padStart(2, "0") + "-" + String(dt.getDate()).padStart(2, "0"); }
  function dayLabel(dateStr) {
    if (!dateStr) return "";
    const today = new Date(); today.setHours(0, 0, 0, 0);
    const d = new Date(dateStr + "T00:00:00");
    const diff = Math.round((d - today) / 86400000);
    if (diff === 0) return "Today";
    if (diff === 1) return "Tomorrow";
    if (diff === -1) return "Yesterday";
    return d.toLocaleDateString(undefined, { day: "numeric", month: "short" });
  }
  function ago(iso) {
    if (!iso) return "";
    const t = new Date(iso).getTime();
    if (isNaN(t)) return "";
    const s = Math.max(0, (Date.now() - t) / 1000);
    if (s < 60) return "just now";
    if (s < 3600) return Math.floor(s / 60) + "m ago";
    if (s < 86400) return Math.floor(s / 3600) + "h ago";
    if (s < 86400 * 30) return Math.floor(s / 86400) + "d ago";
    return fmtDate(iso);
  }
  function fileIcon(mime) {
    if (!mime) return "bi-file-earmark";
    if (mime.includes("pdf")) return "bi-file-earmark-pdf";
    if (mime.includes("image")) return "bi-file-earmark-image";
    if (mime.includes("sheet") || mime.includes("excel") || mime.includes("csv")) return "bi-file-earmark-spreadsheet";
    if (mime.includes("word")) return "bi-file-earmark-word";
    if (mime.includes("zip")) return "bi-file-earmark-zip";
    return "bi-file-earmark";
  }
  function debounce(fn, ms) { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; }

  function toast(message, variant) {
    let wrap = $("lfToastWrap");
    if (!wrap) {
      wrap = document.createElement("div");
      wrap.id = "lfToastWrap";
      wrap.style.cssText = "position:fixed;bottom:20px;right:20px;z-index:3000;display:flex;flex-direction:column;gap:8px;";
      document.body.appendChild(wrap);
    }
    const bg = variant === "danger" ? "#DC2626" : variant === "dark" ? "#1E2233" : "#16A34A";
    const el = document.createElement("div");
    el.style.cssText = `background:${bg};color:#fff;padding:11px 16px;border-radius:10px;font-size:13.5px;font-weight:600;box-shadow:0 8px 20px rgba(0,0,0,.18);max-width:340px;`;
    el.textContent = message;
    wrap.appendChild(el);
    while (wrap.children.length > 3) wrap.firstChild.remove();
    setTimeout(() => el.remove(), 3200);
  }

  function csrfHeaders(extra) { return Object.assign({ "X-CSRF-TOKEN": CSRF, "X-Requested-With": "XMLHttpRequest" }, extra || {}); }
  async function apiFetch(url, options) {
    const res = await fetch(url, Object.assign({ headers: csrfHeaders({ "Accept": "application/json" }) }, options));
    if (res.status === 401 || res.status === 419) {
      toast("Your session has expired. Please sign in again.", "danger");
      throw new Error("unauthenticated");
    }
    let j;
    try { j = await res.json(); } catch (e) { j = { success: false, message: "Server error (" + res.status + ")" }; }
    j._status = res.status;
    return j;
  }
  const reqJson = (url, method, body) => apiFetch(url, { method, headers: csrfHeaders({ "Content-Type": "application/json", "Accept": "application/json" }), body: JSON.stringify(body || {}) });
  const url = (key, id) => CFG.routes[key].replace("__ID__", id);
  const firstErr = (res, fb) => (res && (res.message || (res.errors && Object.values(res.errors)[0][0]))) || fb;

  /* ---------------- dates, money, deals ---------------- */

  const WEEKDAYS = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
  const dOnly = s => (s || "").slice(0, 10);
  const hm = dt => String(dt.getHours()).padStart(2, "0") + ":" + String(dt.getMinutes()).padStart(2, "0");
  function parseYmd(s) { const p = dOnly(s).split("-").map(Number); return new Date(p[0], (p[1] || 1) - 1, p[2] || 1); }
  function shortDay(s) { return parseYmd(s).toLocaleDateString(undefined, { weekday: "short", day: "numeric", month: "short" }); }
  function friendlyDay(s) {
    if (!s) return "";
    const today = new Date(); today.setHours(0, 0, 0, 0);
    const d = parseYmd(s);
    const diff = Math.round((d - today) / 86400000);
    if (diff === 0) return "Today";
    if (diff === 1) return "Tomorrow";
    if (diff === -1) return "Yesterday";
    if (diff > 1 && diff < 7) return WEEKDAYS[d.getDay()];
    const o = { weekday: "short", day: "numeric", month: "short" };
    if (d.getFullYear() !== today.getFullYear()) o.year = "numeric";
    return d.toLocaleDateString(undefined, o);
  }
  function spanText(ms) {
    const m = Math.max(1, Math.round(Math.abs(ms) / 60000));
    if (m < 60) return m + (m === 1 ? " min" : " mins");
    const h = Math.round(m / 60);
    if (h < 24) return h + (h === 1 ? " hour" : " hours");
    const d = Math.round(h / 24);
    return d + (d === 1 ? " day" : " days");
  }
  function dueAt(r) {
    if (!r.reminder_date) return null;
    const t = new Date(`${dOnly(r.reminder_date)}T${r.reminder_time || "23:59:59"}`);
    return isNaN(t) ? null : t;
  }
  const whenText = r => r.reminder_date ? friendlyDay(r.reminder_date) + (r.reminder_time ? " · " + fmtTime(r.reminder_time) : "") : "No date";
  /** One place that decides how a follow-up looks: overdue / today / upcoming / done / silent. */
  function dueInfo(r) {
    const when = whenText(r);
    if (r.status === "completed") return { k: "done", label: "Completed", when };
    if (r.status === "silent") return { k: "silent", label: "Silenced", when };
    const d = dueAt(r);
    if (!d) return { k: "nodate", label: "No date set", when };
    if (isMissed(r)) return { k: "overdue", label: "Overdue by " + spanText(Date.now() - d.getTime()), when };
    const today = dOnly(r.reminder_date) === ymd(new Date());
    return { k: today ? "today" : "upcoming", label: today ? "Due today" : "Upcoming", rel: "in " + spanText(d.getTime() - Date.now()), when };
  }

  /** Quick time presets used by the follow-up window and the Snooze menu. */
  function nextBizDays(n) { const d = new Date(); let k = 0; while (k < n) { d.setDate(d.getDate() + 1); if (d.getDay() % 6) k++; } return d; }
  function presets() {
    const now = new Date(), out = [];
    const mk = (key, label, full, dt, time) => out.push({ key, label, full, date: ymd(dt), time, sub: shortDay(ymd(dt)) + " · " + fmtTime(time) });
    const in1 = new Date(Math.ceil((now.getTime() + 3600000) / 300000) * 300000);
    mk("1h", "In 1 hour", "In 1 hour", in1, hm(in1));
    if (now.getHours() < 17 || (now.getHours() === 17 && now.getMinutes() < 30)) mk("eve", "Today evening", "Today evening (6:00 PM)", now, "18:00");
    const tom = new Date(now); tom.setDate(tom.getDate() + 1);
    mk("tom", "Tomorrow", `Tomorrow (${WEEKDAYS[tom.getDay()]})`, tom, "10:00");
    const b3 = nextBizDays(3);
    mk("b3", "In 3 business days", `In 3 business days (${WEEKDAYS[b3.getDay()]})`, b3, "10:00");
    const mon = new Date(now); mon.setDate(mon.getDate() + ((8 - mon.getDay()) % 7 || 7));
    mk("mon", "Next week", "Next week (Monday)", mon, "10:00");
    return out;
  }
  const customFull = (date, time) => (date ? friendlyDay(date) + (time ? " · " + fmtTime(time) : "") : "Pick a date");
  const inFuture = (date, time) => { if (!date) return false; const t = new Date(`${dOnly(date)}T${time || "23:59"}`); return !isNaN(t) && t.getTime() > Date.now(); };

  const inr = n => "₹" + Number(n || 0).toLocaleString("en-IN", { maximumFractionDigits: 0 });
  function inrShort(n) {
    n = Number(n || 0);
    const f = (v, u) => "₹" + String(Math.round(v * 10) / 10) + u;
    if (n >= 1e7) return f(n / 1e7, " Cr");
    if (n >= 1e5) return f(n / 1e5, " L");
    if (n >= 1e3) return f(n / 1e3, "K");
    return n > 0 ? "₹" + n : "";
  }
  const profileUrl = id => url("profile", id);
  const dealUrl = id => url("deal", id);
  const dealsOf = l => l.linked_deals || [];
  const dealCls = s => s === "won" ? "won" : s === "lost" ? "lost" : "open";
  function dealChip(d) {
    const amt = inrShort(d.amount), stg = d.stage_label || d.stage || "";
    return `<a class="lh-deal ${dealCls(d.stage)}" href="${esc(dealUrl(d.id))}" draggable="false" title="${esc(d.name)} · ${esc(stg)}${d.via === "company" ? " (same company)" : ""}"><i class="bi bi-briefcase"></i><span class="t">${esc(d.name)}</span>${amt ? `<span class="a">${esc(amt)}</span>` : ""}<span class="s">${esc(stg)}</span></a>`;
  }
  function dealCell(l) {
    const ds = dealsOf(l);
    if (!ds.length) return DASH;
    return dealChip(ds[0]) + (ds.length > 1 ? ` <span class="lh-count" title="${esc(ds.slice(1).map(d => d.name).join(", "))}">+${ds.length - 1}</span>` : "");
  }

  /** Safe mini-formatting for notes: **bold**, *italic*, ~~strike~~, "- " bullets, auto-linked URLs. Input is escaped first. */
  function fmtText(s) {
    const inline = t => {
      let h = esc(t);
      h = h.replace(/\*\*(.+?)\*\*/g, "<strong>$1</strong>")
        .replace(/(^|[^*\w])\*([^*\s][^*]*?)\*(?!\*)/g, "$1<em>$2</em>")
        .replace(/~~(.+?)~~/g, "<s>$1</s>");
      return h.replace(/\bhttps?:\/\/[^\s<]*?(?=(?:&quot;|&#39;|&lt;|&gt;)|[.,;:!?)]*(?:\s|<|$))/g, u => `<a href="${u}" target="_blank" rel="noopener noreferrer">${u}</a>`);
    };
    const out = []; let list = [];
    const flush = () => { if (list.length) { out.push("<ul>" + list.map(x => "<li>" + inline(x) + "</li>").join("") + "</ul>"); list = []; } };
    String(s == null ? "" : s).split(/\r?\n/).forEach(ln => {
      const m = ln.match(/^\s*[-•]\s+(.*)$/);
      if (m) list.push(m[1]);
      else { flush(); out.push(ln.trim() === "" ? "<br>" : "<div>" + inline(ln) + "</div>"); }
    });
    flush();
    return out.join("");
  }

  /* ---------------- state ---------------- */

  const DEFAULT_COLS = ["name", "email", "phone", "status"];
  let leads = [];
  let me = null;
  let loading = true;
  let view = lsGet("lh.view", "table") === "board" ? "board" : "table";
  let cat = "all";
  let page = 1;
  let pageSize = parseInt(lsGet("lh.ps", 25), 10) || 25;
  let F = { search: "", statuses: new Set(), source: "", from: "", to: "" };
  let viewAs = null;
  let sort = null;                    // {key, dir}
  let selected = new Set();
  let detail = null;                  // lead open in the drawer
  let drawerPane = "about";
  let orderIds = [];                  // visible order (for prev / next in the drawer)
  const boardLimit = {};
  let pendingDel = [];

  /* ---------------- columns ---------------- */

  const dash = v => (v === null || v === undefined || v === "") ? DASH : v;
  const lnk = (href, text, cls) => `<a class="lh-a ${cls || ""}" href="${esc(href)}">${esc(text)}</a>`;

  function nextReminder(l) {
    const rel = (l.reminders || []).filter(r => (r.type || "reminder") !== "note" && r.status === "scheduled" && !isMissed(r));
    rel.sort((a, b) => ((a.reminder_date || "9") + (a.reminder_time || "")).localeCompare((b.reminder_date || "9") + (b.reminder_time || "")));
    return rel[0] || null;
  }
  const COLDEFS = {
    name:      { label: "Name", locked: true, icon: "person", sortv: l => (l.customer_name || "").toLowerCase() },
    email:     { label: "Email", icon: "envelope", sortv: l => (l.email || "").toLowerCase(), cell: l => l.email ? lnk("mailto:" + l.email, l.email) : DASH },
    phone:     { label: "Phone number", icon: "telephone", sortv: l => l.phone || "", cell: l => l.phone ? lnk("tel:" + l.phone, l.phone) : DASH },
    status:    { label: "Lead status", icon: "flag", sortv: l => TYPES.indexOf(l.lead_type), cell: l => pill(l.lead_type, true, l.id) },
    whatsapp:  { label: "WhatsApp", icon: "whatsapp", sortv: l => l.whatsapp || "", cell: l => l.whatsapp ? `<a class="lh-a lh-wa" href="${esc(waUrl(l.whatsapp))}"><i class="bi bi-whatsapp me-1"></i>${esc(l.whatsapp)}</a>` : DASH },
    company:   { label: "Company", icon: "building", sortv: l => (l.company_name || "").toLowerCase(), cell: l => l.company_name ? `<span class="lh-co"><i class="bi bi-building"></i>${esc(l.company_name)}</span>` : DASH },
    gst:       { label: "GST number", icon: "receipt", sortv: l => l.gst_number || "", cell: l => dash(esc(l.gst_number)) },
    product:   { label: "Product / interest", icon: "box", sortv: l => (l.product_name || "").toLowerCase(), cell: l => dash(esc(l.product_name)) },
    source:    { label: "Source", icon: "bullseye", sortv: l => l.source || "", cell: l => l.source ? esc(l.source) + (l.social_platform ? ` <span class="text-muted">· ${esc(l.social_platform)}</span>` : "") : DASH },
    assigned:  { label: "Assigned to", icon: "people", sortv: l => ((l.assigned_admins || [])[0] || {}).name || "", cell: l => assignedHtml(l) },
    owner:     { label: "Created by", icon: "person-badge", sortv: l => ((l.creator || {}).name || "").toLowerCase(), cell: l => l.creator ? esc(l.creator.name) : DASH },
    created:   { label: "Create date", icon: "calendar3", sortv: l => l.created_at || "", cell: l => l.created_at ? `<span title="${esc(fmtDate(l.created_at))}">${esc(ago(l.created_at))}</span>` : DASH },
    reminder:  { label: "Next follow-up", icon: "alarm", sortv: l => { const r = nextReminder(l); return r ? (r.reminder_date || "") + (r.reminder_time || "") : "9999"; }, cell: l => reminderChip(l) },
    deals:     { label: "Deals", icon: "briefcase", sortv: l => dealsOf(l).length, cell: l => dealCell(l) },
    attachments: { label: "Files", icon: "paperclip", sortv: l => (l.attachments || []).length, cell: l => (l.attachments || []).length ? `<span class="lh-count">${l.attachments.length}</span>` : DASH },
  };
  function sanitizeCols(arr) {
    const out = ["name"];
    (Array.isArray(arr) ? arr : DEFAULT_COLS).forEach(k => { if (COLDEFS[k] && out.indexOf(k) < 0) out.push(k); });
    return out;
  }
  let cols = sanitizeCols(lsGet("lh.cols", DEFAULT_COLS));
  function setCols(arr) {
    cols = sanitizeCols(arr);
    lsSet("lh.cols", cols);
    if (sort && cols.indexOf(sort.key) < 0) sort = null;
    renderAll();
    if ($("lhColsModal").classList.contains("show")) renderColsModal();
  }

  function assignedHtml(l) {
    const a = l.assigned_admins || [];
    if (!a.length) return `<span class="unassigned-text">Unassigned</span>`;
    return `<div class="avatar-stack" title="${esc(a.map(x => x.name).join(", "))}">${a.slice(0, 3).map(x => avatarHtml(x.name, "sm")).join("")}${a.length > 3 ? `<div class="avatar avatar-sm avatar-more">+${a.length - 3}</div>` : ""}</div>`;
  }
  function reminderChip(l) {
    const b = reminderBuckets(l);
    if (b.missed.length) { const i = dueInfo(b.missed.slice().sort(byDue)[0]); return `<span class="lh-rem-chip late" title="${esc(i.when)}"><i class="bi bi-exclamation-circle-fill"></i>Overdue${b.missed.length > 1 ? " · " + b.missed.length : ""} <small>${esc(spanText(Date.now() - dueAt(b.missed.slice().sort(byDue)[0]).getTime()))}</small></span>`; }
    const n = nextReminder(l);
    if (!n) return DASH;
    const i = dueInfo(n);
    return `<span class="lh-rem-chip ${i.k === "today" ? "today" : ""}"><i class="bi bi-alarm"></i>${esc(i.when)}</span>`;
  }

  /* ---------------- reminders -> buckets ---------------- */

  function isMissed(r) {
    if (r.status === "completed" || r.status === "silent") return false;
    if (r.status === "missed") return true;
    const d = dueAt(r);
    return d ? d.getTime() < Date.now() : false;
  }
  function reminderBuckets(l) {
    const rel = (l.reminders || []).filter(r => (r.type || "reminder") !== "note");
    return {
      scheduled: rel.filter(r => r.status === "scheduled" && !isMissed(r)),
      missed: rel.filter(r => r.status !== "completed" && r.status !== "silent" && (r.status === "missed" || isMissed(r))),
      silent: rel.filter(r => r.status === "silent"),
    };
  }
  const belongsTo = (l, adminId) => l.created_by === adminId || (l.assigned_admins || []).some(a => a.id === adminId);

  /* ---------------- filtering / sorting ---------------- */

  function inCategory(l, c) {
    if (c === "all") return true;
    if (c === "pinned") return !!l.is_pinned;
    if (c === "mine") return me && l.created_by === me.id;
    const b = reminderBuckets(l);
    return b[c] ? b[c].length > 0 : true;
  }
  function matchesFilters(l, ignoreStatus) {
    const t = F.search.trim().toLowerCase();
    if (t) {
      const hay = [l.customer_name, l.email, l.phone, l.whatsapp, l.company_name, l.product_name].concat(dealsOf(l).map(d => d.name)).join(" ").toLowerCase();
      if (!hay.includes(t) && !(digits(t).length >= 3 && digits(l.phone + " " + l.whatsapp).includes(digits(t)))) return false;
    }
    if (!ignoreStatus && F.statuses.size && !F.statuses.has(l.lead_type)) return false;
    if (F.source && l.source !== F.source) return false;
    const d = (l.created_at || "").slice(0, 10);
    if (F.from && !(d && d >= F.from)) return false;
    if (F.to && !(d && d <= F.to)) return false;
    if (viewAs !== null && !belongsTo(l, viewAs)) return false;
    return true;
  }
  function filtered(ignoreStatus) {
    let list = leads.filter(l => matchesFilters(l, ignoreStatus) && inCategory(l, cat));
    if (sort && COLDEFS[sort.key]) {
      const f = COLDEFS[sort.key].sortv, dir = sort.dir === "asc" ? 1 : -1;
      list = list.map((l, i) => [l, i]).sort((a, b) => {
        const x = f(a[0]), y = f(b[0]);
        return (x < y ? -1 : x > y ? 1 : a[1] - b[1]) * dir;
      }).map(p => p[0]);
    } else {
      list = list.map((l, i) => [l, i]).sort((a, b) => (b[0].is_pinned ? 1 : 0) - (a[0].is_pinned ? 1 : 0) || b[0].id - a[0].id).map(p => p[0]);
    }
    return list;
  }
  const anyFilter = () => !!(F.search || F.statuses.size || F.source || F.from || F.to || viewAs !== null);

  /* ---------------- popovers ---------------- */

  let popEl = null, popAnchor = null, popOnClose = null, popOpenedAt = 0;
  function closePop() {
    if (popEl) { popEl.remove(); popEl = null; popAnchor = null; }
    if (popOnClose) { const f = popOnClose; popOnClose = null; try { f(); } catch (e) { /* ignore */ } }
  }
  function openPop(anchor, html, opts) {
    opts = opts || {};
    if (popEl && popAnchor === anchor) { closePop(); return null; }
    closePop();
    const el = document.createElement("div");
    el.className = "lh-pop";
    if (opts.width) el.style.width = opts.width + "px";
    el.innerHTML = html;
    document.body.appendChild(el);
    popEl = el; popAnchor = anchor; popOnClose = opts.onClose || null; popOpenedAt = Date.now();
    const r = anchor.getBoundingClientRect();
    let left = opts.align === "right" ? r.right - el.offsetWidth : r.left;
    left = Math.max(8, Math.min(left, window.innerWidth - el.offsetWidth - 8));
    let top = r.bottom + 6;
    if (top + el.offsetHeight > window.innerHeight - 8) top = Math.max(8, r.top - el.offsetHeight - 6);
    el.style.left = left + "px"; el.style.top = top + "px";
    return el;
  }
  function popMenu(anchor, items, opts) {
    const html = items.map((it, i) => {
      if (it.sep) return "<hr>";
      if (it.hd) return `<div class="hd">${esc(it.hd)}</div>`;
      const lead = it.dot ? `<span class="sdot ${stCls(it.dot)}"></span>` : it.icon ? `<i class="bi bi-${it.icon}"></i>` : "";
      return `<button type="button" class="it ${it.danger ? "danger" : ""} ${it.active ? "active" : ""}" data-i="${i}">${lead}<span>${esc(it.label)}</span>${it.sub ? `<span class="sub">${esc(it.sub)}</span>` : ""}${it.cnt != null ? `<span class="cnt">${it.cnt}</span>` : ""}${it.checked ? '<i class="bi bi-check2 ck"></i>' : ""}</button>`;
    }).join("");
    const el = openPop(anchor, html, opts);
    if (!el) return;
    el.addEventListener("click", e => {
      const b = e.target.closest("[data-i]");
      if (!b) return;
      const it = items[+b.dataset.i];
      if (!it.keep) closePop();
      if (it.fn) it.fn();
    });
    const first = el.querySelector(".it");
    if (first) first.focus({ preventScroll: true });
  }
  document.addEventListener("mousedown", e => {
    if (popEl && !popEl.contains(e.target) && !(popAnchor && popAnchor.contains(e.target))) closePop();
  });
  document.addEventListener("keydown", e => { if (e.key === "Escape" && popEl) { e.stopPropagation(); closePop(); } }, true);
  window.addEventListener("resize", () => { closePop(); fitHeights(); });
  window.addEventListener("scroll", e => { if (popEl && !popEl.contains(e.target) && Date.now() - popOpenedAt > 250) closePop(); }, true);

  function afterDrawerClosed(fn) {
    const el = $("lhDrawer");
    if (el.classList.contains("show")) {
      el.addEventListener("hidden.bs.offcanvas", function h() { el.removeEventListener("hidden.bs.offcanvas", h); fn(); });
      bootstrap.Offcanvas.getOrCreateInstance(el).hide();
    } else fn();
  }

  /* ---------------- data ---------------- */

  async function loadLeads() {
    const data = await apiFetch(CFG.routes.data);
    loading = false;
    if (!data.success) { renderAll(); return; }
    leads = data.leads.map(normLead);
    me = data.current_admin;
    if (detail) { const f = leads.find(l => l.id === detail.id); if (f) detail = f; }
    renderAll();
  }
  async function refreshSilently() { try { await loadLeads(); if (detail) renderDrawer(true); } catch (e) { /* ignore */ } }
  function putLead(l) {
    normLead(l);
    const i = leads.findIndex(x => x.id === l.id);
    if (i >= 0) leads[i] = Object.assign(leads[i], l); else leads.unshift(l);
    return leads[i >= 0 ? i : 0];
  }

  /* ---------------- rendering: tabs / toolbar ---------------- */

  function renderTabs() {
    const base = leads.filter(l => viewAs === null || belongsTo(l, viewAs));
    const n = { all: base.length, mine: base.filter(l => inCategory(l, "mine")).length, pinned: base.filter(l => l.is_pinned).length };
    ["scheduled", "missed", "silent"].forEach(c => { n[c] = base.filter(l => reminderBuckets(l)[c].length > 0).length; });
    document.querySelectorAll("#lhTabs [data-n]").forEach(el => {
      el.textContent = n[el.dataset.n] || 0;
      if (el.dataset.n === "missed") el.style.display = n.missed ? "" : "none";
    });
    document.querySelectorAll("#lhTabs .lh-tab").forEach(t => t.classList.toggle("active", t.dataset.cat === cat));
  }
  function setChip(id, label, value, on) {
    const el = $(id);
    if (!el) return;
    const icon = el.querySelector("i:not(.bi-chevron-down)");
    el.classList.toggle("on", on);
    el.innerHTML = (icon ? icon.outerHTML : "") + `<span>${esc(label)}${on ? ":" : ""}</span>` + (on ? `<span class="v">${esc(value)}</span>` : "") + '<i class="bi bi-chevron-down"></i>';
  }
  function renderToolbar() {
    setChip("lhChipStatus", "Lead status", Array.from(F.statuses).map(typeLabel).join(", "), F.statuses.size > 0);
    setChip("lhChipSource", "Source", F.source, !!F.source);
    setChip("lhChipDate", "Create date", (F.from ? fmtDate(F.from) : "…") + " – " + (F.to ? fmtDate(F.to) : "…"), !!(F.from || F.to));
    if (CFG.isSuperAdmin) {
      const a = viewAs === null ? null : CFG.admins.find(x => x.id === viewAs);
      setChip("lhChipOwner", "User", a ? a.name : "", viewAs !== null);
      if (viewAs === null) $("lhChipOwner").querySelector("span").textContent = "All users";
    }
    $("lhClear").classList.toggle("d-none", !anyFilter());
    $("lhColsBtn").classList.toggle("d-none", view !== "table");
  }

  /* ---------------- rendering: table ---------------- */

  function renderAll() {
    renderTabs();
    renderToolbar();
    const list = filtered(view === "board");
    orderIds = list.map(l => l.id);
    $("lhCount").textContent = list.length;
    $("lhTableWrap").classList.toggle("d-none", view !== "table");
    $("lhBoard").classList.toggle("d-none", view !== "board");
    document.querySelectorAll(".lh-seg [data-view]").forEach(b => b.classList.toggle("active", b.dataset.view === view));
    if (view === "table") renderTable(list); else renderBoard(list);
    renderBulk();
    fitHeights();
  }

  function renderTable(list) {
    const totalPages = Math.max(1, Math.ceil(list.length / pageSize));
    page = Math.min(page, totalPages);
    const start = (page - 1) * pageSize;
    const items = list.slice(start, start + pageSize);

    $("lhThead").innerHTML = `<tr>
      <th class="c-chk"><input type="checkbox" class="form-check-input" id="lhSelAll" aria-label="Select all"></th>
      ${cols.map(k => {
        const d = COLDEFS[k];
        const srt = sort && sort.key === k ? `<i class="bi bi-arrow-${sort.dir === "asc" ? "up" : "down"} srt"></i>` : "";
        return `<th class="sortable ${k === "name" ? "c-name" : ""}" data-col="${k}">${esc(d.label)}${srt}</th>`;
      }).join("")}
      <th class="c-end"><button type="button" class="lh-plus" id="lhAddCol" title="Choose columns" aria-label="Choose columns"><i class="bi bi-plus-lg"></i></button></th>
    </tr>`;
    const allBox = $("lhSelAll");
    const pageSel = items.filter(l => selected.has(l.id)).length;
    allBox.checked = items.length > 0 && pageSel === items.length;
    allBox.indeterminate = pageSel > 0 && pageSel < items.length;

    const tb = $("lhTbody");
    if (loading) {
      tb.innerHTML = Array.from({ length: 7 }, () => `<tr class="lh-skel">${`<td><span></span></td>`.repeat(cols.length + 2)}</tr>`).join("");
    } else if (!items.length) {
      tb.innerHTML = `<tr><td colspan="${cols.length + 2}" style="height:auto;max-width:none;white-space:normal"><div class="lh-emptybox">
        <div class="ic"><i class="bi bi-${leads.length ? "funnel" : "person-plus"}"></i></div>
        <h5>${leads.length ? "No leads match" : "No leads yet"}</h5>
        <p>${leads.length ? "Try changing your filters or search." : "Start by creating a contact, then create a lead for them."}</p>
        ${leads.length ? '<button class="btn btn-outline-secondary btn-sm" data-act="clear">Clear filters</button>' : '<button class="btn btn-primary btn-sm" data-act="create"><i class="bi bi-plus-lg me-1"></i>Create lead</button>'}
      </div></td></tr>`;
    } else {
      tb.innerHTML = items.map(l => `<tr data-id="${l.id}" class="${selected.has(l.id) ? "sel" : ""}">
        <td class="c-chk"><input type="checkbox" class="form-check-input row-check" data-id="${l.id}" ${selected.has(l.id) ? "checked" : ""} aria-label="Select"></td>
        ${cols.map(k => k === "name" ? `<td class="c-name">${nameCell(l)}</td>` : `<td>${COLDEFS[k].cell(l)}</td>`).join("")}
        <td class="c-end"><button type="button" class="lh-rowbtn" data-act="rowmenu" data-id="${l.id}" aria-label="More"><i class="bi bi-three-dots"></i></button></td>
      </tr>`).join("");
    }

    // footer
    const from = list.length ? start + 1 : 0, to = Math.min(list.length, start + pageSize);
    $("lhPageInfo").textContent = `${from}–${to} of ${list.length}`;
    $("lhPageSize").value = String(pageSize);
    $("lhFoot").classList.toggle("d-none", loading || !list.length);
    const pages = [];
    const add = p => pages.push(`<button type="button" data-page="${p}" class="${p === page ? "active" : ""}">${p}</button>`);
    pages.push(`<button type="button" data-page="${page - 1}" ${page <= 1 ? "disabled" : ""} aria-label="Previous"><i class="bi bi-chevron-left"></i></button>`);
    const around = new Set([1, totalPages, page - 1, page, page + 1]);
    let prev = 0;
    for (let p = 1; p <= totalPages; p++) {
      if (!around.has(p)) continue;
      if (p - prev > 1) pages.push(`<button type="button" disabled>…</button>`);
      add(p); prev = p;
    }
    pages.push(`<button type="button" data-page="${page + 1}" ${page >= totalPages ? "disabled" : ""} aria-label="Next"><i class="bi bi-chevron-right"></i></button>`);
    $("lhPages").innerHTML = totalPages > 1 ? pages.join("") : "";
  }
  function nameCell(l) {
    return `<div class="lh-name">
      <button type="button" class="lh-pin ${l.is_pinned ? "on" : ""}" data-act="pin" data-id="${l.id}" title="${l.is_pinned ? "Unpin" : "Pin"}"><i class="bi bi-pin-angle-fill"></i></button>
      ${avatarHtml(l.customer_name, "sm")}
      <span class="lh-name-col"><a class="lh-name-text" href="${esc(profileUrl(l.id))}" title="Open lead page">${esc(l.customer_name)}</a><span class="lh-name-sub">${esc([l.phone, typeLabel(l.lead_type)].filter(Boolean).join(" · "))}</span></span>
    </div>`;
  }

  function renderBulk() {
    const n = selected.size;
    $("lhBulk").classList.toggle("show", n > 0 && view === "table");
    $("lhBulkCount").textContent = n;
  }

  /* ---------------- rendering: board ---------------- */

  function renderBoard(list) {
    const types = F.statuses.size ? TYPES.filter(t => F.statuses.has(t)) : TYPES;
    const board = $("lhBoard");
    if (loading) { board.innerHTML = types.map(t => `<div class="lh-col ${stCls(t)}"><div class="lh-col-head"><span class="dot"></span><b>${esc(typeLabel(t))}</b></div><div class="lh-col-body"><div class="lh-colempty">Loading…</div></div></div>`).join(""); return; }
    const scrollLeft = board.scrollLeft;
    board.innerHTML = types.map(t => {
      const items = list.filter(l => l.lead_type === t);
      const lim = boardLimit[t] || 20;
      const body = items.length
        ? items.slice(0, lim).map(boardCard).join("") + (items.length > lim ? `<button type="button" class="lh-more" data-act="more" data-type="${t}">Show ${items.length - lim} more</button>` : "")
        : `<div class="lh-colempty">No leads<br>Drag a card here</div>`;
      return `<div class="lh-col ${stCls(t)}" data-type="${t}">
        <div class="lh-col-head"><span class="dot"></span><b>${esc(typeLabel(t))}</b><span class="n">${items.length}</span>
          <button type="button" class="lh-iconbtn add" data-act="addIn" data-type="${t}" title="Create ${esc(typeLabel(t))} lead" aria-label="Add lead"><i class="bi bi-plus-lg"></i></button></div>
        <div class="lh-col-body">${body}</div>
      </div>`;
    }).join("");
    board.scrollLeft = scrollLeft;
  }
  function boardCard(l) {
    const b = reminderBuckets(l);
    const rem = b.missed.length || nextReminder(l) ? reminderChip(l) : "";
    return `<div class="lh-card-k" draggable="true" data-id="${l.id}">
      <div class="nm">${l.is_pinned ? '<i class="bi bi-pin-angle-fill pinmark"></i>' : ""}${avatarHtml(l.customer_name, "sm")}<a class="lh-deal-mini" href="${esc(profileUrl(l.id))}" draggable="false" style="color:inherit!important;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${esc(l.customer_name)}</a></div>
      <button type="button" class="lh-rowbtn menu" data-act="cardmenu" data-id="${l.id}" aria-label="More"><i class="bi bi-three-dots"></i></button>
      ${l.company_name ? `<div class="ln"><i class="bi bi-building"></i><span>${esc(l.company_name)}</span></div>` : ""}
      ${l.phone ? `<div class="ln"><i class="bi bi-telephone"></i><span>${esc(l.phone)}</span></div>` : ""}
      ${l.email ? `<div class="ln"><i class="bi bi-envelope"></i><span>${esc(l.email)}</span></div>` : ""}
      ${dealsOf(l).length ? `<div class="ln"><i class="bi bi-briefcase"></i><a class="lh-deal-mini" href="${esc(dealUrl(dealsOf(l)[0].id))}" draggable="false"><span>${esc(dealsOf(l)[0].name)}</span></a>${dealsOf(l).length > 1 ? `<span class="lh-count">+${dealsOf(l).length - 1}</span>` : ""}</div>` : ""}
      <div class="bt">${rem}<span style="margin-left:auto">${assignedHtml(l)}</span></div>
    </div>`;
  }

  function fitHeights() {
    const wrap = $("lhWrap");
    if (!wrap) return;
    const vh = window.innerHeight;
    if (view === "table") {
      const sc = $("lhScroll");
      const top = sc.getBoundingClientRect().top;
      const foot = $("lhFoot").classList.contains("d-none") ? 0 : $("lhFoot").offsetHeight;
      sc.style.maxHeight = Math.max(280, vh - top - foot - 24) + "px";
    } else {
      const b = $("lhBoard");
      b.style.height = Math.max(360, vh - b.getBoundingClientRect().top - 20) + "px";
    }
  }

  /* ---------------- status change (inline / drag / bulk) ---------------- */

  async function setStatus(id, type) {
    const l = leads.find(x => x.id === id);
    if (!l || l.lead_type === type) return;
    const old = l.lead_type;
    l.lead_type = type;
    if (detail && detail.id === id) { detail.lead_type = type; renderDrawer(); }
    renderAll();
    try {
      const res = await reqJson(url("patch", id), "PATCH", { lead_type: type });
      if (!res.success) throw new Error(firstErr(res, "Couldn't update the status."));
      toast("Status: " + typeLabel(type));
    } catch (e) {
      l.lead_type = old;
      if (detail && detail.id === id) { detail.lead_type = old; renderDrawer(); }
      renderAll();
      toast(e.message || "Couldn't update the status.", "danger");
    }
  }
  function statusMenu(anchor, current, onPick) {
    popMenu(anchor, TYPES.map(t => ({ label: typeLabel(t), dot: t, checked: t === current, fn: () => onPick(t) })));
  }

  /* ---------------- delete ---------------- */

  function askDelete(ids) {
    pendingDel = ids.slice();
    $("lhDeleteTitle").textContent = ids.length > 1 ? `Delete ${ids.length} leads?` : "Delete this lead?";
    afterDrawerClosed(() => bootstrap.Modal.getOrCreateInstance($("lhDeleteModal")).show());
  }
  async function confirmDelete() {
    const ids = pendingDel.slice();
    if (!ids.length) return;
    const btn = $("confirmDeleteBtn");
    btn.disabled = true;
    try {
      const res = ids.length === 1
        ? await apiFetch(url("destroy", ids[0]), { method: "DELETE" })
        : await reqJson(CFG.routes.bulkDelete, "POST", { ids });
      if (res.success) {
        leads = leads.filter(l => ids.indexOf(l.id) < 0);
        ids.forEach(i => selected.delete(i));
        if (detail && ids.indexOf(detail.id) >= 0) detail = null;
        renderAll();
        toast(res.message || "Deleted", "dark");
      } else toast(firstErr(res, "Couldn't delete."), "danger");
    } catch (e) { /* toast already shown */ }
    btn.disabled = false;
    bootstrap.Modal.getOrCreateInstance($("lhDeleteModal")).hide();
  }

  /* ---------------- toolbar wiring ---------------- */

  function filterPopovers() {
    $("lhChipStatus").addEventListener("click", e => {
      const counts = {};
      leads.forEach(l => { if (matchesFilters(l, true) && inCategory(l, cat)) counts[l.lead_type] = (counts[l.lead_type] || 0) + 1; });
      const items = TYPES.map(t => ({ label: typeLabel(t), dot: t, checked: F.statuses.has(t), cnt: counts[t] || 0, keep: true, fn: () => {
        if (F.statuses.has(t)) F.statuses.delete(t); else F.statuses.add(t);
        page = 1; renderAll(); statusFilterRefresh();
      } }));
      function statusFilterRefresh() {
        if (!popEl) return;
        popEl.querySelectorAll(".it").forEach((b, i) => {
          const on = F.statuses.has(TYPES[i]);
          const ck = b.querySelector(".ck");
          if (on && !ck) b.insertAdjacentHTML("beforeend", '<i class="bi bi-check2 ck"></i>');
          if (!on && ck) ck.remove();
        });
      }
      popMenu(e.currentTarget, items.concat([{ sep: true }, { label: "Clear all", icon: "x-circle", fn: () => { F.statuses.clear(); renderAll(); } }]));
    });
    $("lhChipSource").addEventListener("click", e => {
      popMenu(e.currentTarget, [{ label: "Any source", checked: !F.source, fn: () => { F.source = ""; page = 1; renderAll(); } }]
        .concat(SOURCES.map(s => ({ label: s, checked: F.source === s, cnt: leads.filter(l => l.source === s).length, fn: () => { F.source = s; page = 1; renderAll(); } }))));
    });
    $("lhChipDate").addEventListener("click", e => {
      const el = openPop(e.currentTarget, `<div class="hd">Create date</div>
        <div class="dates"><input type="date" class="form-control form-control-sm" id="lhDFrom" value="${esc(F.from)}" aria-label="From"><span>–</span><input type="date" class="form-control form-control-sm" id="lhDTo" value="${esc(F.to)}" aria-label="To"></div>
        <div class="dates" style="padding-top:0"><button type="button" class="lh-ghost" data-r="7">Last 7 days</button><button type="button" class="lh-ghost" data-r="30">Last 30 days</button><button type="button" class="lh-link" data-r="0">Clear</button></div>`, { width: 300 });
      if (!el) return;
      const apply = () => { F.from = $("lhDFrom").value; F.to = $("lhDTo").value; page = 1; renderAll(); };
      el.addEventListener("change", apply);
      el.addEventListener("click", ev => {
        const b = ev.target.closest("[data-r]");
        if (!b) return;
        const n = +b.dataset.r;
        $("lhDTo").value = n ? ymd(new Date()) : "";
        $("lhDFrom").value = n ? ymd(new Date(Date.now() - n * 86400000)) : "";
        apply();
      });
    });
    if (CFG.isSuperAdmin) {
      $("lhChipOwner").addEventListener("click", e => {
        const el = openPop(e.currentTarget, `<div class="srch"><input type="text" class="form-control form-control-sm" id="lhOwnQ" placeholder="Search users..."></div><div id="lhOwnList"></div>`, { width: 280 });
        if (!el) return;
        const draw = q => {
          const t = (q || "").toLowerCase();
          const rows = [`<button type="button" class="it ${viewAs === null ? "active" : ""}" data-u=""><i class="bi bi-people"></i><span>All users</span><span class="cnt">${leads.length}</span></button>`]
            .concat(CFG.admins.filter(a => a.name.toLowerCase().includes(t)).map(a => `<button type="button" class="it ${viewAs === a.id ? "active" : ""}" data-u="${a.id}">${avatarHtml(a.name, "sm")}<span>${esc(a.name)}</span><span class="cnt">${leads.filter(l => belongsTo(l, a.id)).length}</span></button>`));
          $("lhOwnList").innerHTML = rows.join("");
        };
        draw("");
        $("lhOwnQ").focus();
        $("lhOwnQ").addEventListener("input", ev => draw(ev.target.value));
        el.addEventListener("click", ev => {
          const b = ev.target.closest("[data-u]");
          if (!b) return;
          viewAs = b.dataset.u === "" ? null : parseInt(b.dataset.u, 10);
          page = 1; closePop(); renderAll();
        });
      });
    }
  }

  function bindToolbar() {
    $("lhSearch").addEventListener("input", debounce(e => { F.search = e.target.value; page = 1; renderAll(); }, 140));
    document.addEventListener("keydown", e => {
      if (e.key === "/" && !/^(INPUT|TEXTAREA|SELECT)$/.test((document.activeElement || {}).tagName || "") && !document.querySelector(".modal.show")) {
        e.preventDefault(); $("lhSearch").focus();
      }
    });
    $("lhClear").addEventListener("click", clearFilters);
    $("lhTabs").addEventListener("click", e => {
      const t = e.target.closest("[data-cat]");
      if (!t) return;
      cat = t.dataset.cat; page = 1; renderAll();
    });
    document.querySelector(".lh-seg").addEventListener("click", e => {
      const b = e.target.closest("[data-view]");
      if (!b) return;
      view = b.dataset.view; lsSet("lh.view", view); renderAll();
    });
    $("lhColsBtn").addEventListener("click", () => bootstrap.Modal.getOrCreateInstance($("lhColsModal")).show());
    $("lhPageSize").addEventListener("change", e => { pageSize = parseInt(e.target.value, 10) || 25; lsSet("lh.ps", pageSize); page = 1; renderAll(); });
    $("lhPages").addEventListener("click", e => {
      const b = e.target.closest("[data-page]");
      if (!b || b.disabled) return;
      page = parseInt(b.dataset.page, 10); renderAll();
      $("lhScroll").scrollTop = 0;
    });
    $("openAddLeadBtn").addEventListener("click", () => openCreate());

    // bulk
    $("lhBulkClear").addEventListener("click", () => { selected.clear(); renderAll(); });
    $("lhBulkDelete").addEventListener("click", () => askDelete(Array.from(selected)));
    $("lhBulkStatus").addEventListener("click", e => {
      popMenu(e.currentTarget, [{ hd: "Move selected to" }].concat(TYPES.map(t => ({ label: typeLabel(t), dot: t, fn: async () => {
        const ids = Array.from(selected);
        const res = await reqJson(CFG.routes.bulkType, "POST", { ids, lead_type: t });
        if (res.success) { leads.forEach(l => { if (ids.indexOf(l.id) >= 0) l.lead_type = t; }); renderAll(); toast(res.message); }
        else toast(firstErr(res, "Couldn't update the status."), "danger");
      } }))));
    });
    $("confirmDeleteBtn").addEventListener("click", confirmDelete);
    filterPopovers();
  }
  function clearFilters() {
    F = { search: "", statuses: new Set(), source: "", from: "", to: "" };
    viewAs = null; page = 1; $("lhSearch").value = "";
    renderAll();
  }

  /* ---------------- table + board events ---------------- */

  function leadMenu(anchor, id) {
    const l = leads.find(x => x.id === id);
    if (!l) return;
    popMenu(anchor, [
      { label: "Quick view", icon: "layout-sidebar-inset-reverse", fn: () => openDetail(id) },
      { label: "Open lead page", icon: "box-arrow-up-right", fn: () => { location.href = profileUrl(id); } },
      { label: "Add note", icon: "sticky", fn: () => openComposer(id, "note") },
      { label: "Schedule follow-up", icon: "alarm", fn: () => openComposer(id, "fu") },
      l.phone ? { label: "Call", icon: "telephone", fn: () => { location.href = "tel:" + l.phone; } } : null,
      waUrl(l.whatsapp || l.phone) ? { label: "WhatsApp", icon: "whatsapp", fn: () => { location.href = waUrl(l.whatsapp || l.phone); } } : null,
      { label: l.is_pinned ? "Unpin" : "Pin", icon: "pin-angle", fn: () => togglePin(id) },
      { sep: true },
      { hd: "Move to" },
    ].filter(Boolean).concat(TYPES.filter(t => t !== l.lead_type).map(t => ({ label: typeLabel(t), dot: t, fn: () => setStatus(id, t) })))
      .concat([{ sep: true }, { label: "Delete", icon: "trash", danger: true, fn: () => askDelete([id]) }]), { align: "right" });
  }
  async function togglePin(id) {
    const res = await apiFetch(url("togglePin", id), { method: "POST" });
    if (!res.success) return;
    const l = leads.find(x => x.id === id);
    if (l) l.is_pinned = res.is_pinned;
    if (detail && detail.id === id) { detail.is_pinned = res.is_pinned; renderDrawer(); }
    renderAll();
  }

  function bindTable() {
    const tbody = $("lhTbody");
    tbody.addEventListener("click", e => {
      const act = e.target.closest("[data-act]");
      if (act) {
        e.stopPropagation();
        const id = parseInt(act.dataset.id, 10);
        if (act.dataset.act === "pin") togglePin(id);
        else if (act.dataset.act === "status") { const l = leads.find(x => x.id === id); statusMenu(act, l.lead_type, t => setStatus(id, t)); }
        else if (act.dataset.act === "rowmenu") leadMenu(act, id);
        else if (act.dataset.act === "clear") clearFilters();
        else if (act.dataset.act === "create") openCreate();
        return;
      }
      if (e.target.closest("a,input,label")) return;
      const tr = e.target.closest("tr[data-id]");
      if (tr) openDetail(parseInt(tr.dataset.id, 10));
    });
    tbody.addEventListener("change", e => {
      const cb = e.target.closest(".row-check");
      if (!cb) return;
      const id = parseInt(cb.dataset.id, 10);
      if (cb.checked) selected.add(id); else selected.delete(id);
      cb.closest("tr").classList.toggle("sel", cb.checked);
      renderBulk();
      const items = Array.from(tbody.querySelectorAll(".row-check")), n = items.filter(x => x.checked).length;
      const all = $("lhSelAll"); all.checked = n === items.length; all.indeterminate = n > 0 && n < items.length;
    });
    $("lhThead").addEventListener("click", e => {
      if (e.target.closest("#lhAddCol")) { colsPopover($("lhAddCol")); return; }
      const th = e.target.closest("th[data-col]");
      if (th) {
        const k = th.dataset.col;
        sort = !sort || sort.key !== k ? { key: k, dir: "asc" } : sort.dir === "asc" ? { key: k, dir: "desc" } : null;
        renderAll();
      }
    });
    $("lhThead").addEventListener("change", e => {
      if (e.target.id !== "lhSelAll") return;
      Array.from($("lhTbody").querySelectorAll(".row-check")).forEach(cb => {
        const id = parseInt(cb.dataset.id, 10);
        cb.checked = e.target.checked;
        if (e.target.checked) selected.add(id); else selected.delete(id);
      });
      renderAll();
    });
  }

  function bindBoard() {
    const board = $("lhBoard");
    board.addEventListener("click", e => {
      const act = e.target.closest("[data-act]");
      if (act) {
        const id = parseInt(act.dataset.id, 10);
        if (act.dataset.act === "cardmenu") leadMenu(act, id);
        else if (act.dataset.act === "addIn") openCreate({ status: act.dataset.type });
        else if (act.dataset.act === "more") { boardLimit[act.dataset.type] = (boardLimit[act.dataset.type] || 20) + 20; renderAll(); }
        return;
      }
      if (e.target.closest("a")) return;
      const card = e.target.closest(".lh-card-k");
      if (card) openDetail(parseInt(card.dataset.id, 10));
    });
    let dragId = null;
    board.addEventListener("dragstart", e => {
      const card = e.target.closest(".lh-card-k");
      if (!card) return;
      dragId = parseInt(card.dataset.id, 10);
      e.dataTransfer.effectAllowed = "move";
      try { e.dataTransfer.setData("text/plain", String(dragId)); } catch (err) { /* ignore */ }
      setTimeout(() => card.classList.add("dragging"), 0);
    });
    board.addEventListener("dragend", () => {
      dragId = null;
      board.querySelectorAll(".dragging").forEach(c => c.classList.remove("dragging"));
      board.querySelectorAll(".over").forEach(c => c.classList.remove("over"));
    });
    board.addEventListener("dragover", e => {
      const col = e.target.closest(".lh-col");
      if (!col || dragId === null) return;
      e.preventDefault();
      board.querySelectorAll(".over").forEach(c => { if (c !== col) c.classList.remove("over"); });
      col.classList.add("over");
    });
    board.addEventListener("drop", e => {
      const col = e.target.closest(".lh-col");
      if (!col || dragId === null) return;
      e.preventDefault();
      const id = dragId; dragId = null;
      col.classList.remove("over");
      setStatus(id, col.dataset.type);
    });
  }

  /* ---------------- column chooser ---------------- */

  function colsPopover(anchor) {
    const keys = Object.keys(COLDEFS).filter(k => k !== "name");
    const draw = q => {
      const t = (q || "").toLowerCase();
      return keys.filter(k => COLDEFS[k].label.toLowerCase().includes(t)).map(k =>
        `<button type="button" class="it" data-k="${k}"><i class="bi bi-${COLDEFS[k].icon}"></i><span>${esc(COLDEFS[k].label)}</span>${cols.indexOf(k) >= 0 ? '<i class="bi bi-check2 ck"></i>' : ""}</button>`).join("") || '<div class="hd">Nothing found</div>';
    };
    const el = openPop(anchor, `<div class="hd">What should this table show?</div><div class="srch"><input type="text" class="form-control form-control-sm" id="lhPopColQ" placeholder="Property search..."></div><div id="lhPopCols">${draw("")}</div><hr><button type="button" class="it" data-edit><i class="bi bi-layout-three-columns"></i><span>Columns edit / reorder…</span></button><button type="button" class="it" data-reset><i class="bi bi-arrow-counterclockwise"></i><span>Reset to default</span></button>`, { width: 290, align: "right" });
    if (!el) return;
    const q = $("lhPopColQ");
    q.focus();
    q.addEventListener("input", () => { $("lhPopCols").innerHTML = draw(q.value); });
    el.addEventListener("click", e => {
      const b = e.target.closest("[data-k]");
      if (b) {
        const k = b.dataset.k;
        setCols(cols.indexOf(k) >= 0 ? cols.filter(c => c !== k) : cols.concat(k));
        $("lhPopCols").innerHTML = draw(q.value);
        return;
      }
      if (e.target.closest("[data-edit]")) { closePop(); bootstrap.Modal.getOrCreateInstance($("lhColsModal")).show(); }
      if (e.target.closest("[data-reset]")) { setCols(DEFAULT_COLS); $("lhPopCols").innerHTML = draw(q.value); }
    });
  }

  function renderColsModal() {
    const q = ($("lhColsSearch").value || "").toLowerCase();
    $("lhColsAvail").innerHTML = Object.keys(COLDEFS).filter(k => cols.indexOf(k) < 0 && COLDEFS[k].label.toLowerCase().includes(q)).map(k =>
      `<div class="lh-colrow"><i class="bi bi-${COLDEFS[k].icon}"></i><span>${esc(COLDEFS[k].label)}</span><span class="sp"></span><button type="button" data-add="${k}" title="Add" aria-label="Add ${esc(COLDEFS[k].label)}"><i class="bi bi-plus-lg"></i></button></div>`).join("")
      || '<div class="text-muted" style="font-size:13px;padding:10px">All properties are already selected.</div>';
    $("lhColsN").textContent = cols.length;
    $("lhColsSel").innerHTML = cols.map((k, i) => k === "name"
      ? `<div class="lh-colrow sel"><i class="bi bi-lock-fill lock"></i><span>${esc(COLDEFS[k].label)}</span><span class="sp"></span><span class="text-muted" style="font-size:11.5px">Always first</span></div>`
      : `<div class="lh-colrow sel" draggable="true" data-k="${k}"><i class="bi bi-grip-vertical grip"></i><span>${esc(COLDEFS[k].label)}</span><span class="sp"></span>
          <button type="button" data-up="${k}" ${i <= 1 ? "disabled" : ""} aria-label="Move up"><i class="bi bi-arrow-up"></i></button>
          <button type="button" data-down="${k}" ${i >= cols.length - 1 ? "disabled" : ""} aria-label="Move down"><i class="bi bi-arrow-down"></i></button>
          <button type="button" data-rm="${k}" aria-label="Remove"><i class="bi bi-x-lg"></i></button></div>`).join("");
  }
  function bindColsModal() {
    const modal = $("lhColsModal");
    modal.addEventListener("show.bs.modal", () => { $("lhColsSearch").value = ""; renderColsModal(); });
    $("lhColsSearch").addEventListener("input", renderColsModal);
    $("lhColsReset").addEventListener("click", () => setCols(DEFAULT_COLS));
    const move = (k, d) => { const a = cols.slice(); const i = a.indexOf(k), j = i + d; if (j < 1 || j >= a.length) return; a.splice(i, 1); a.splice(j, 0, k); setCols(a); };
    modal.addEventListener("click", e => {
      const t = e.target.closest("button");
      if (!t) return;
      if (t.dataset.add) setCols(cols.concat(t.dataset.add));
      else if (t.dataset.rm) setCols(cols.filter(c => c !== t.dataset.rm));
      else if (t.dataset.up) move(t.dataset.up, -1);
      else if (t.dataset.down) move(t.dataset.down, 1);
    });
    let dragK = null;
    modal.addEventListener("dragstart", e => { const r = e.target.closest(".lh-colrow.sel[data-k]"); if (!r) return; dragK = r.dataset.k; r.classList.add("dragging"); e.dataTransfer.effectAllowed = "move"; try { e.dataTransfer.setData("text/plain", dragK); } catch (er) { /* ignore */ } });
    modal.addEventListener("dragover", e => { const r = e.target.closest(".lh-colrow.sel[data-k]"); if (!r || !dragK) return; e.preventDefault(); modal.querySelectorAll(".dropt").forEach(x => x.classList.remove("dropt")); r.classList.add("dropt"); });
    modal.addEventListener("drop", e => {
      const r = e.target.closest(".lh-colrow.sel[data-k]");
      if (!r || !dragK) return;
      e.preventDefault();
      const a = cols.filter(c => c !== dragK), at = a.indexOf(r.dataset.k);
      a.splice(at, 0, dragK); dragK = null; setCols(a);
    });
    modal.addEventListener("dragend", () => { dragK = null; renderColsModal(); });
  }

  /* ==================== DRAWER (record view with inline editing) ==================== */

  async function openDetail(id, opts) {
    opts = opts || {};
    let res;
    try { res = await apiFetch(url("show", id)); } catch (e) { return; }
    if (!res.success) { toast(firstErr(res, "Lead not found."), "danger"); return; }
    detail = putLead(res.lead);
    if (!opts.refresh) {
      drawerPane = opts.pane || "about";
      closeWin();
      closePop();
      bootstrap.Offcanvas.getOrCreateInstance($("lhDrawer")).show();
    }
    renderDrawer(!!opts.refresh);
    renderAll();
  }
  window.openLeadDetailById = id => openDetail(parseInt(id, 10));

  function setPane(p, keep) {
    drawerPane = p;
    document.querySelectorAll("#lhDrTabs .lh-dr-tab").forEach(t => t.classList.toggle("active", t.dataset.pane === p));
    ["about", "activity", "files"].forEach(k => $("lhPane" + k.charAt(0).toUpperCase() + k.slice(1)).classList.toggle("d-none", k !== p));
    if (!keep) $("lhDrBody").scrollTop = 0;
  }

  function companyLink(l) {
    if (l.company) return `<a class="lh-colink" href="${esc(url("companyOpen", l.company.id))}" title="Open company"><i class="bi bi-building"></i>${esc(l.company.name)}</a>`;
    return l.company_name ? `<span><i class="bi bi-building me-1"></i>${esc(l.company_name)}</span>` : "";
  }
  function dealsStrip(l) {
    const ds = dealsOf(l);
    if (!ds.length) return `<div class="lh-dr-deals none"><i class="bi bi-briefcase"></i><span>No deals linked</span></div>`;
    return `<div class="lh-dr-deals"><span class="lbl"><i class="bi bi-briefcase"></i>Deals</span>${ds.slice(0, 2).map(dealChip).join("")}${ds.length > 2 ? `<button type="button" class="lh-deal more" data-dr="deals">+${ds.length - 2} more</button>` : ""}</div>`;
  }
  function dealsSection(l) {
    const ds = dealsOf(l);
    const rows = ds.map(d => `<a class="lh-deal-row" href="${esc(dealUrl(d.id))}">
      <span class="ic"><i class="bi bi-briefcase"></i></span>
      <span class="mid"><span class="nm">${esc(d.name)}</span><span class="mt">${d.amount ? `<b>${esc(inr(d.amount))}</b>` : ""}${d.close_date ? `<span><i class="bi bi-calendar3"></i>Close ${esc(fmtDate(d.close_date))}</span>` : ""}${d.owner ? `<span><i class="bi bi-person"></i>${esc(d.owner)}</span>` : ""}</span></span>
      <span class="rt"><span class="lh-dstage ${dealCls(d.stage)}">${esc(d.stage_label || d.stage)}</span><small>${d.via === "company" ? "via company" : "via contact"}</small></span></a>`).join("");
    return `<div class="lh-sect" id="lhDealsSect"><div class="lh-sect-h"><i class="bi bi-briefcase"></i>Deals${ds.length ? `<span class="lh-count">${ds.length}</span>` : ""}<span class="sp"></span><a class="lh-link" href="${esc(CFG.routes.dealsIndex)}">All deals</a></div>
      ${rows || `<div class="lh-co-card"><div class="lh-co-ic"><i class="bi bi-briefcase"></i></div><div><div class="nm">No deals linked yet</div><small>Deals that include this contact${l.company ? " or company" : ""} will show up here.</small></div></div>`}</div>`;
  }

  function renderDrawer(keep) {
    const l = detail;
    if (!l) return;
    const idx = orderIds.indexOf(l.id);
    const wa = waUrl(l.whatsapp || l.phone);
    $("lhDrTop").innerHTML = `
      <div class="lh-dr-bar"><span>Lead</span><span class="sp"></span>
        <button type="button" class="lh-iconbtn" data-dr="prev" ${idx <= 0 ? "disabled" : ""} title="Previous lead" aria-label="Previous lead"><i class="bi bi-chevron-up"></i></button>
        <button type="button" class="lh-iconbtn" data-dr="next" ${idx < 0 || idx >= orderIds.length - 1 ? "disabled" : ""} title="Next lead" aria-label="Next lead"><i class="bi bi-chevron-down"></i></button>
        <a class="lh-iconbtn" href="${esc(profileUrl(l.id))}" title="Open full lead page" aria-label="Open full lead page"><i class="bi bi-box-arrow-up-right"></i></a>
        <button type="button" class="lh-iconbtn" data-dr="share" title="Share" aria-label="Share"><i class="bi bi-share"></i></button>
        <button type="button" class="lh-iconbtn ${l.is_pinned ? "pinned" : ""}" data-dr="pin" title="${l.is_pinned ? "Unpin" : "Pin"}" aria-label="Pin"><i class="bi bi-pin-angle-fill"></i></button>
        <button type="button" class="lh-iconbtn danger" data-dr="delete" title="Delete" aria-label="Delete"><i class="bi bi-trash"></i></button>
        <button type="button" class="lh-iconbtn" data-bs-dismiss="offcanvas" aria-label="Close"><i class="bi bi-x-lg"></i></button>
      </div>
      <div class="lh-dr-id">
        ${avatarHtml(l.customer_name, "lg")}
        <div style="min-width:0">
          <h2 class="lh-dr-name"><a href="${esc(profileUrl(l.id))}" title="Open full lead page">${esc(l.customer_name)}<i class="bi bi-box-arrow-up-right"></i></a></h2>
          <div class="lh-dr-sub">${companyLink(l)}${pill(l.lead_type, true, l.id)}</div>
        </div>
      </div>
      <div class="lh-dr-meta">${fuChip(l)}${dealsStrip(l)}</div>
      <div class="lh-dr-quick">
        <button type="button" class="lh-q" data-dr="note"><span class="ic"><i class="bi bi-sticky"></i></span>Note</button>
        <a class="lh-q ${l.email ? "" : "off"}" href="${l.email ? "mailto:" + esc(l.email) : "#"}"><span class="ic"><i class="bi bi-envelope"></i></span>Email</a>
        <a class="lh-q ${l.phone ? "" : "off"}" href="${l.phone ? "tel:" + esc(l.phone) : "#"}"><span class="ic"><i class="bi bi-telephone"></i></span>Call</a>
        <a class="lh-q wa ${wa ? "" : "off"}" href="${wa ? esc(wa) : "#"}"><span class="ic"><i class="bi bi-whatsapp"></i></span>WhatsApp</a>
        <button type="button" class="lh-q" data-dr="fu"><span class="ic"><i class="bi bi-alarm"></i></span>Follow-up</button>
      </div>`;
    setPane(drawerPane, keep);
    renderAbout();
    renderActivity();
    renderFiles();
    syncWin();
  }

  function prop(label, field, html, kind, scope, ro) {
    const empty = html === "" || html == null;
    return `<div class="lh-prop"><div class="lh-prop-l">${esc(label)}</div>
      <div class="lh-prop-v ${ro ? "ro" : ""}" ${ro ? "" : `tabindex="0" role="button" data-edit="${field}" data-kind="${kind}" data-scope="${scope || "lead"}" aria-label="Edit ${esc(label)}"`}>
        ${empty ? `<span class="ph">${ro ? "—" : "Add " + esc(label.toLowerCase().replace("whatsapp", "WhatsApp"))}</span>` : html}${ro ? "" : '<i class="bi bi-pencil pen"></i>'}
      </div></div>`;
  }

  function renderAbout() {
    const l = detail;
    if (!l) return;
    const c = l.company;
    const assigned = (l.assigned_admins || []).map(a => esc(a.name)).join(", ");
    let html = `<div class="lh-sect"><div class="lh-sect-h"><i class="bi bi-person-lines-fill"></i>About this lead</div>
      ${prop("Name", "customer_name", esc(l.customer_name), "text")}
      ${prop("Email", "email", esc(l.email), "email")}
      ${prop("Phone number", "phone", esc(l.phone), "tel")}
      ${prop("WhatsApp", "whatsapp", esc(l.whatsapp), "tel")}
      ${prop("Lead status", "lead_type", pill(l.lead_type, false), "select")}
      ${prop("Product / interest", "product_name", esc(l.product_name), "text")}
      ${prop("Source", "source", esc(l.source), "select")}
      ${l.source === "Social Media" ? prop("Platform", "social_platform", esc(l.social_platform), "select") : ""}
      ${prop("Assigned to", "assigned_admins", (l.assigned_admins || []).length ? assignedHtml(l) + `<span>${assigned}</span>` : "", "assign")}
      ${prop("Contact record", "", l.contact ? `<a class="lh-a" href="${esc(url("contactOpen", l.contact.id))}">${esc(l.contact.name)} <i class="bi bi-box-arrow-up-right ms-1" style="font-size:11px"></i></a>` : "", "", "", true)}
      ${prop("Created by", "", l.creator ? esc(l.creator.name) : "", "", "", true)}
      ${prop("Create date", "", l.created_at ? esc(fmtDate(l.created_at)) + ` <span class="text-muted">(${esc(ago(l.created_at))})</span>` : "", "", "", true)}
    </div>`;

    html += dealsSection(l);

    if (c) {
      html += `<div class="lh-sect"><div class="lh-sect-h"><i class="bi bi-building"></i>Company<span class="sp"></span>
          <button type="button" class="lh-link" data-dr="coChange">Change</button><button type="button" class="lh-link" style="color:var(--danger)" data-dr="coRemove">Remove</button></div>
        ${prop("Company name", "name", esc(c.name), "text", "co")}
        ${prop("Website", "website", esc(c.website), "text", "co")}
        ${prop("Phone", "phone", esc(c.phone), "tel", "co")}
        ${prop("GST number", "gst_number", esc(c.gst_number), "text", "co")}
        ${prop("Address", "address", esc(c.address), "text", "co")}
      </div>`;
    } else {
      html += `<div class="lh-sect"><div class="lh-sect-h"><i class="bi bi-building"></i>Company</div>
        <div class="lh-co-card"><div class="lh-co-ic"><i class="bi bi-building-add"></i></div>
          <div><div class="nm">${l.company_name ? esc(l.company_name) : "No company linked"}</div><small>${l.company_name ? "Only a name is saved — link a company record" : "Link a company to edit its GST number and address right here"}</small></div>
          <button type="button" class="btn btn-sm btn-outline-primary ms-auto" data-dr="coChange">${l.company_name ? "Link" : "Add company"}</button></div></div>`;
    }
    $("lhPaneAbout").innerHTML = html;
  }

  /* ---- inline edit ---- */

  function getVal(scope, field) { const o = scope === "co" ? (detail.company || {}) : detail; return o[field] == null ? "" : o[field]; }

  function startEdit(cell) {
    if (!detail || cell.classList.contains("editing") || cell.classList.contains("ro")) return;
    const field = cell.dataset.edit, kind = cell.dataset.kind, scope = cell.dataset.scope || "lead";
    if (kind === "assign") { openAssignEdit(cell); return; }
    const cur = String(getVal(scope, field));
    let input;
    if (kind === "select") {
      input = document.createElement("select");
      input.className = "form-select form-select-sm";
      let opts = field === "lead_type" ? TYPES.map(t => [t, typeLabel(t)]) : field === "source" ? SOURCES.map(s => [s, s]) : [["", "— None —"]].concat(PLATFORMS.map(s => [s, s]));
      if (cur && !opts.some(o => o[0] === cur)) opts = opts.concat([[cur, cur]]);
      input.innerHTML = opts.map(o => `<option value="${esc(o[0])}" ${o[0] === cur ? "selected" : ""}>${esc(o[1])}</option>`).join("");
    } else {
      input = document.createElement("input");
      input.type = kind === "email" ? "email" : "text";
      if (kind === "tel") input.inputMode = "tel";
      input.className = "form-control form-control-sm";
      input.value = cur;
    }
    const origHtml = cell.innerHTML;
    cell.classList.add("editing");
    cell.innerHTML = "";
    cell.appendChild(input);
    input.focus();
    if (input.select && kind !== "select") input.select();
    let done = false;
    const finish = async save => {
      if (done) return;
      done = true;
      cell.classList.remove("editing");
      const v = input.value.trim();
      if (save && v !== cur.trim()) {
        cell.innerHTML = origHtml;
        cell.classList.add("lh-saving");
        await saveField(scope, field, v);
        renderDrawer_headOnly();
      } else {
        cell.innerHTML = origHtml;   // restore this cell only, so a click on another cell still lands
      }
    };
    input.addEventListener("keydown", e => {
      if (e.key === "Enter") { e.preventDefault(); finish(true); }
      else if (e.key === "Escape") { e.preventDefault(); e.stopPropagation(); finish(false); }
    });
    input.addEventListener("blur", () => finish(true));
    if (kind === "select") input.addEventListener("change", () => finish(true));
  }
  function renderDrawer_headOnly() { if (detail) { const keep = drawerPane; renderDrawer(); setPaneNoScroll(keep); } }
  function setPaneNoScroll(p) { const s = $("lhDrBody").scrollTop; setPane(p); $("lhDrBody").scrollTop = s; }

  async function saveField(scope, field, value) {
    const l = detail;
    let res;
    try {
      if (scope === "co") {
        res = await reqJson(url("companyUpdate", l.company.id), "PATCH", { [field]: value });
        if (!res.success) { toast(firstErr(res, "Couldn't save."), "danger"); return false; }
        Object.assign(l.company, res.company || { [field]: value });
        if (field === "name") l.company_name = l.company.name;
        if (field === "gst_number") l.gst_number = l.company.gst_number;
        refreshSilently();
      } else {
        res = await reqJson(url("patch", l.id), "PATCH", { [field]: value });
        if (!res.success) { toast(firstErr(res, "Couldn't save."), "danger"); return false; }
        Object.assign(l, res.lead);
        if (["customer_name", "email", "phone", "whatsapp"].indexOf(field) >= 0) refreshSilently();
      }
    } catch (e) { return false; }
    renderAll();
    toast("Saved", "dark");
    return true;
  }

  function assignPicker(anchor, set, onChange, onClose) {
    const el = openPop(anchor, `<div class="srch"><input type="text" class="form-control form-control-sm" placeholder="Team search..."></div><div class="alist"></div>`, { width: 280, onClose });
    if (!el) return;
    const input = el.querySelector("input"), list = el.querySelector(".alist");
    const draw = q => {
      const t = (q || "").toLowerCase();
      list.innerHTML = CFG.admins.filter(a => a.name.toLowerCase().includes(t)).map(a =>
        `<label class="it"><input type="checkbox" class="form-check-input m-0" value="${a.id}" ${set.has(a.id) ? "checked" : ""}>${avatarHtml(a.name, "sm")}<span>${esc(a.name)}</span></label>`).join("") || '<div class="hd">No one found</div>';
    };
    draw("");
    input.focus();
    input.addEventListener("input", () => draw(input.value));
    list.addEventListener("change", e => {
      const id = parseInt(e.target.value, 10);
      if (e.target.checked) set.add(id); else set.delete(id);
      if (onChange) onChange(set);
    });
  }
  function openAssignEdit(cell) {
    const orig = (detail.assigned_admins || []).map(a => a.id).sort().join(",");
    const set = new Set((detail.assigned_admins || []).map(a => a.id));
    assignPicker(cell, set, null, async () => {
      if (Array.from(set).sort().join(",") === orig) return;
      await saveField("lead", "assigned_admins", Array.from(set));
      renderAbout();
    });
  }

  function companyPicker(anchor, onPick) {
    const el = openPop(anchor, `<div class="srch"><input type="text" class="form-control form-control-sm" placeholder="Search by company name or GST…"></div><div class="clist"></div>`, { width: 330 });
    if (!el) return;
    const input = el.querySelector("input"), list = el.querySelector(".clist");
    let token = 0, items = [];
    const draw = async () => {
      const my = ++token, q = input.value.trim();
      let res;
      try { res = await apiFetch(CFG.routes.companiesSearch + "?q=" + encodeURIComponent(q)); } catch (e) { return; }
      if (my !== token || !popEl) return;
      items = res.companies || [];
      const exact = items.some(c => c.name.toLowerCase() === q.toLowerCase());
      list.innerHTML = items.map((c, i) => `<button type="button" class="it" data-i="${i}"><i class="bi bi-building"></i><span>${esc(c.name)}<br><small class="text-muted">${c.leads_count || 0} leads${c.gst_number ? " · " + esc(c.gst_number) : ""}</small></span></button>`).join("")
        + (q && !exact ? `<button type="button" class="it" data-create><i class="bi bi-plus-circle"></i><span>Create "${esc(q)}"</span></button>` : "")
        || '<div class="hd">Type to search or create a company</div>';
    };
    draw();
    input.focus();
    input.addEventListener("input", debounce(draw, 200));
    el.addEventListener("click", async e => {
      const b = e.target.closest("button.it");
      if (!b) return;
      if (b.dataset.i !== undefined) { closePop(); onPick(items[+b.dataset.i]); return; }
      if (b.hasAttribute("data-create")) {
        const name = input.value.trim();
        const res = await reqJson(CFG.routes.companyStore, "POST", { name });
        if (res.success) { closePop(); onPick(res.company); }
        else if (res.duplicate && res.company) { closePop(); toast("This company already exists — using it.", "dark"); onPick(res.company); }
        else toast(firstErr(res, "Couldn't create the company."), "danger");
      }
    });
  }
  async function setLeadCompany(companyId) {
    const res = await reqJson(url("patch", detail.id), "PATCH", { company_id: companyId });
    if (!res.success) { toast(firstErr(res, "Couldn't update the company."), "danger"); return; }
    Object.assign(detail, res.lead);
    renderAll(); renderDrawer();
    toast(companyId ? "Company linked" : "Company removed", "dark");
  }

  /* ==================== ACTIVITY: follow-ups + notes ==================== */

  const isNote = r => (r.type || "reminder") === "note";
  const byDue = (a, b) => ((dOnly(a.reminder_date) || "9999-99-99") + (a.reminder_time || "23:59")).localeCompare((dOnly(b.reminder_date) || "9999-99-99") + (b.reminder_time || "23:59"));
  function fuGroups(l) {
    const fus = (l.reminders || []).filter(r => !isNote(r));
    const open = fus.filter(r => r.status !== "completed" && r.status !== "silent");
    const overdue = open.filter(isMissed).sort(byDue);
    const upcoming = open.filter(r => !isMissed(r)).sort(byDue);
    return { fus, overdue, upcoming, hero: overdue[0] || upcoming[0] || null };
  }
  let actFilter = lsGet("lh.actf", "all");
  if (["all", "fu", "note"].indexOf(actFilter) < 0) actFilter = "all";
  let actLimit = 15;

  const aptBadge = t => t ? `<span class="lh-apt"><i class="bi bi-${t === "whatsapp" ? "whatsapp" : "camera-video"}"></i>${t === "whatsapp" ? "WhatsApp" : "Meeting"}</span>` : "";

  function fuChip(l) {
    const g = fuGroups(l), r = g.hero;
    if (!r) return `<button type="button" class="lh-fuchip none" data-dr="fu"><i class="bi bi-plus-lg"></i>Schedule follow-up</button>`;
    const i = dueInfo(r);
    return `<button type="button" class="lh-fuchip ${i.k}" data-dr="fu" title="Schedule another follow-up"><i class="bi bi-alarm"></i>${esc(i.k === "overdue" ? i.label : i.when)}<span class="tx">${esc(r.note)}</span></button>`;
  }

  function heroHtml(r) {
    const i = dueInfo(r), late = i.k === "overdue";
    return `<div class="lh-hero ${i.k}">
      <div class="lh-hero-top"><span><i class="bi bi-${late ? "exclamation-circle-fill" : "alarm-fill"} me-1"></i>${late ? "Needs attention" : "Next follow-up"}</span><span class="lh-hero-state">${esc(late ? i.label : i.k === "today" ? "Due today · " + i.rel : i.rel ? "Due " + i.rel : i.label)}</span></div>
      <div class="lh-hero-when">${esc(i.when)}</div>
      <div class="lh-hero-note">${esc(r.note)}</div>
      <div class="lh-hero-foot">${aptBadge(r.appointment_type)}<span class="sp"></span>
        <button type="button" class="lh-hbtn primary" data-r="complete" data-id="${r.id}"><i class="bi bi-check2-circle"></i>Mark done</button>
        <button type="button" class="lh-hbtn" data-r="snooze" data-id="${r.id}"><i class="bi bi-clock-history"></i>Snooze<i class="bi bi-chevron-down"></i></button>
        <button type="button" class="lh-hbtn icon" data-r="more" data-id="${r.id}" aria-label="More actions"><i class="bi bi-three-dots"></i></button>
      </div></div>`;
  }

  function fuRow(r) {
    const i = dueInfo(r), done = r.status === "completed", silent = r.status === "silent";
    const ic = done ? ["done", "check2-circle"] : silent ? ["mute", "bell-slash"] : i.k === "overdue" ? ["late", "exclamation-circle"] : i.k === "today" ? ["today", "alarm"] : ["", "alarm"];
    const st = i.k === "overdue" ? `<span class="st late">${esc(i.label)}</span>` : (done || silent) ? `<span class="st">${esc(i.label)}</span>` : i.rel ? `<span class="st">${esc(i.rel)}</span>` : "";
    return `<div class="lh-fu ${i.k}">
      <div class="lh-act-ic ${ic[0]}"><i class="bi bi-${ic[1]}"></i></div>
      <div class="bd"><div class="tx">${esc(r.note)}</div><div class="mt"><span class="when">${esc(i.when)}</span>${st}${aptBadge(r.appointment_type)}</div></div>
      <div class="acts">
        ${done ? "" : `<button type="button" data-r="complete" data-id="${r.id}" title="Mark done" aria-label="Mark done"><i class="bi bi-check2"></i></button>`}
        ${done ? "" : `<button type="button" data-r="snooze" data-id="${r.id}" title="Snooze" aria-label="Snooze"><i class="bi bi-clock-history"></i></button>`}
        <button type="button" data-r="more" data-id="${r.id}" title="More" aria-label="More actions"><i class="bi bi-three-dots"></i></button>
      </div></div>`;
  }

  function noteCard(r) {
    const stamp = r.created_at ? `<span title="${esc(fmtDate(r.created_at))}">${esc(ago(r.created_at))}</span>` : "";
    const old = r.reminder_date ? `<span><i class="bi bi-calendar3 me-1"></i>${esc(fmtDate(r.reminder_date))}${r.reminder_time ? " · " + esc(fmtTime(r.reminder_time)) : ""}</span>` : "";
    return `<div class="lh-note"><div class="ic"><i class="bi bi-sticky"></i></div>
      <div class="bd"><div class="tx">${fmtText(r.note)}</div><div class="mt"><span>Note</span>${stamp}${old}</div></div>
      <button type="button" class="del" data-r="del" data-id="${r.id}" title="Delete note" aria-label="Delete note"><i class="bi bi-trash"></i></button></div>`;
  }

  const grp = (title, n, cls) => `<div class="lh-grp ${cls || ""}"><span>${esc(title)}</span><span class="n">${n}</span></div>`;

  function renderActivity() {
    const l = detail, box = $("lhActivity");
    if (!l || !box) return;
    const all = l.reminders || [], notes = all.filter(isNote).sort((a, b) => b.id - a.id), G = fuGroups(l);
    const closed = G.fus.filter(r => r.status === "completed" || r.status === "silent").sort((a, b) => b.id - a.id);
    $("lhNActivity").textContent = all.length;

    const bar = `<div class="lh-actbar">
      <button type="button" class="lh-actbtn" data-dr="note"><i class="bi bi-sticky"></i><span><b>Add note</b><small>Log a call, meeting or detail</small></span></button>
      <button type="button" class="lh-actbtn" data-dr="fu"><i class="bi bi-alarm"></i><span><b>Schedule follow-up</b><small>Get reminded at the right time</small></span></button></div>`;

    if (!all.length) {
      box.innerHTML = bar + `<div class="lh-emptybox" style="padding:34px 20px"><div class="ic" style="width:56px;height:56px;font-size:24px"><i class="bi bi-clock-history"></i></div><h6>No activity yet</h6><p class="mb-0" style="font-size:13px">Add a note after your next call, or schedule a follow-up so this lead never goes cold.</p></div>`;
      return;
    }

    const seg = `<div class="lh-seg2" id="lhActSeg" role="tablist">
      <button type="button" data-af="all" class="${actFilter === "all" ? "on" : ""}">All <b>${all.length}</b></button>
      <button type="button" data-af="fu" class="${actFilter === "fu" ? "on" : ""}">Follow-ups <b>${G.fus.length}</b></button>
      <button type="button" data-af="note" class="${actFilter === "note" ? "on" : ""}">Notes <b>${notes.length}</b></button></div>`;

    let html = bar;
    if (actFilter !== "note" && G.hero) html += heroHtml(G.hero);
    html += seg;

    const rest = { overdue: G.overdue.filter(r => r !== G.hero), upcoming: G.upcoming.filter(r => r !== G.hero) };
    if (actFilter !== "note") {
      if (rest.overdue.length) html += grp("Overdue", rest.overdue.length, "late") + `<div class="lh-feed">${rest.overdue.map(fuRow).join("")}</div>`;
      if (rest.upcoming.length) html += grp("Upcoming", rest.upcoming.length) + `<div class="lh-feed">${rest.upcoming.map(fuRow).join("")}</div>`;
    }
    const history = (actFilter === "all" ? closed.concat(notes) : actFilter === "fu" ? closed : notes).sort((a, b) => b.id - a.id);
    if (history.length) {
      const title = actFilter === "all" ? "History" : actFilter === "fu" ? "Completed & silenced" : "Notes";
      html += grp(title, history.length) + `<div class="lh-feed">${history.slice(0, actLimit).map(r => isNote(r) ? noteCard(r) : fuRow(r)).join("")}${history.length > actLimit ? `<button type="button" class="lh-more2" data-act2="more">Show ${Math.min(15, history.length - actLimit)} more</button>` : ""}</div>`;
    }
    if (actFilter === "fu" && !G.fus.length) html += `<div class="lh-emptybox" style="padding:30px 20px"><p class="mb-2" style="font-size:13px">No follow-ups yet.</p><button type="button" class="btn btn-primary btn-sm" data-dr="fu">Schedule one</button></div>`;
    if (actFilter === "note" && !notes.length) html += `<div class="lh-emptybox" style="padding:30px 20px"><p class="mb-2" style="font-size:13px">No notes yet.</p><button type="button" class="btn btn-primary btn-sm" data-dr="note">Add a note</button></div>`;
    box.innerHTML = html;
  }

  async function reminderAction(act, id) {
    try {
      if (act === "del") {
        const res = await apiFetch(url("reminderDestroy", id), { method: "DELETE" });
        if (res && res.success === false) { toast(firstErr(res, "Couldn't delete."), "danger"); return; }
        toast("Deleted", "dark");
      } else {
        const res = await reqJson(url("reminderStatus", id), "POST", { status: act === "complete" ? "completed" : "silent" });
        if (!res.success) { toast(firstErr(res, "Couldn't update the follow-up."), "danger"); return; }
        toast(act === "complete" ? "Marked as done" : "Follow-up silenced", "dark");
      }
    } catch (e) { return; }
    await openDetail(detail.id, { refresh: true });
    if (window.GlobalReminders) window.GlobalReminders.refresh();
  }
  async function rescheduleTo(id, date, time, label) {
    if (!inFuture(date, time)) { toast("Pick a time in the future.", "danger"); return; }
    let res;
    try { res = await reqJson(url("reminderUpdate", id), "PATCH", { reminder_date: date, reminder_time: time || null }); } catch (e) { return; }
    if (!res.success) { toast(firstErr(res, "Couldn't reschedule."), "danger"); return; }
    toast("Follow-up set for " + (label || customFull(date, time)), "dark");
    await openDetail(detail.id, { refresh: true });
    if (window.GlobalReminders) window.GlobalReminders.refresh();
  }
  function snoozeMenu(anchor, r) {
    const items = [{ hd: "Snooze until" }].concat(presets().map(p => ({ label: p.label, sub: p.sub, icon: "clock", fn: () => rescheduleTo(r.id, p.date, p.time, p.full) })));
    items.push({ sep: true }, { label: "Pick date & time…", icon: "calendar3", fn: () => reschedulePop(anchor, r) });
    popMenu(anchor, items, { width: 340, align: "right" });
  }
  function reschedulePop(anchor, r) {
    const d0 = r.reminder_date && inFuture(r.reminder_date, r.reminder_time) ? dOnly(r.reminder_date) : presets().find(p => p.key === "tom").date;
    const t0 = (r.reminder_time || "10:00").slice(0, 5);
    const el = openPop(anchor, `<div class="hd">Reschedule follow-up</div>
      <div class="dates"><input type="date" class="form-control form-control-sm" id="lhRsD" value="${esc(d0)}" aria-label="Date"><input type="time" class="form-control form-control-sm" id="lhRsT" value="${esc(t0)}" aria-label="Time"></div>
      <div class="dates" style="padding-top:0"><button type="button" class="btn btn-primary btn-sm w-100" id="lhRsSave">Save</button></div>`, { width: 290, align: "right" });
    if (!el) return;
    $("lhRsSave").addEventListener("click", () => {
      const d = $("lhRsD").value, t = $("lhRsT").value;
      if (!d) { toast("Pick a date.", "danger"); return; }
      closePop();
      rescheduleTo(r.id, d, t, customFull(d, t));
    });
  }
  function moreMenu(anchor, r) {
    const items = [];
    if (r.status !== "completed") items.push({ label: "Reschedule…", icon: "calendar3", fn: () => reschedulePop(anchor, r) });
    if (r.status === "silent" && r.reminder_date) items.push({ label: "Reactivate", icon: "bell", fn: () => rescheduleTo(r.id, dOnly(r.reminder_date), (r.reminder_time || "").slice(0, 5), null) });
    else if (r.status === "scheduled" || r.status === "missed") items.push({ label: "Silence alarm", icon: "bell-slash", fn: () => reminderAction("silence", r.id) });
    items.push({ sep: true }, { label: "Delete", icon: "trash", danger: true, fn: () => { if (confirm("Delete this follow-up?")) reminderAction("del", r.id); } });
    popMenu(anchor, items, { width: 220, align: "right" });
  }
  function reminderClick(btn) {
    const act = btn.dataset.r, id = parseInt(btn.dataset.id, 10);
    const r = (detail.reminders || []).find(x => x.id === id);
    if (!r) return;
    if (act === "snooze") snoozeMenu(btn, r);
    else if (act === "more") moreMenu(btn, r);
    else if (act === "del") { if (confirm(isNote(r) ? "Delete this note?" : "Delete this follow-up?")) reminderAction("del", id); }
    else reminderAction(act, id);
  }
  function bindActivity() {
    $("lhPaneActivity").addEventListener("click", e => {
      const af = e.target.closest("[data-af]");
      if (af) { actFilter = af.dataset.af; lsSet("lh.actf", actFilter); actLimit = 15; const s = $("lhDrBody").scrollTop; renderActivity(); $("lhDrBody").scrollTop = s; return; }
      if (e.target.closest("[data-act2='more']")) { actLimit += 15; const s = $("lhDrBody").scrollTop; renderActivity(); $("lhDrBody").scrollTop = s; }
    });
  }

  /* ==================== FLOATING COMPOSER (draggable Note / Follow-up window) ==================== */

  const W = { open: false, mode: "note", leadId: null, min: false, big: false, h: "", drafts: {}, pos: lsGet("lh.win.pos", null) };
  function draftOf(id) {
    const d = W.drafts[id] || (W.drafts[id] = { note: "", fu: "", withFu: false, noteWhen: null, fuWhen: null, apt: "" });
    const mk = key => Object.assign({}, presets().find(p => p.key === key) || presets()[0]);
    if (!d.noteWhen) d.noteWhen = mk("b3");
    if (!d.fuWhen) d.fuWhen = mk("tom");
    return d;
  }
  const whenProp = () => W.mode === "note" ? "noteWhen" : "fuWhen";

  function clampWin(x, y) {
    const el = $("lhWin"), w = el.offsetWidth, h = el.offsetHeight;
    x = Math.max(0, Math.min(x, window.innerWidth - w));
    y = Math.max(0, Math.min(y, window.innerHeight - h));
    el.style.left = x + "px"; el.style.top = y + "px";
    return { x, y };
  }
  function reclampWin() {
    if (!W.open || window.innerWidth < 768) return;
    const r = $("lhWin").getBoundingClientRect();
    clampWin(r.left, r.top);
  }
  function placeWin() {
    const el = $("lhWin");
    if (window.innerWidth < 768) { el.style.left = el.style.top = ""; return; }
    if (W.pos) { clampWin(W.pos.x, W.pos.y); return; }
    const w = el.offsetWidth, h = el.offsetHeight;
    let x = window.innerWidth - Math.min(580, window.innerWidth) - w - 16;   // beside the drawer, so the record stays readable
    if (x < 8) x = window.innerWidth - w - 16;
    clampWin(x, window.innerHeight - h - 20);
  }

  function showWin(mode) {
    if (!detail) return;
    if (W.open && W.leadId !== detail.id) { /* drafts are stored per lead on every keystroke */ }
    W.leadId = detail.id;
    if (mode) W.mode = mode;
    W.open = true;
    $("lhWin").hidden = false;
    if (W.min) toggleMin(false);
    renderWin();
    placeWin();
    setTimeout(() => { const t = $("lhWinText"); if (t && W.open) { t.focus(); t.setSelectionRange(t.value.length, t.value.length); } }, 40);
  }
  function closeWin() { W.open = false; const el = $("lhWin"); if (el) el.hidden = true; }
  function toggleMin(force) {
    const el = $("lhWin");
    W.min = force === undefined ? !W.min : force;
    if (W.min) { W.h = el.style.height; el.style.height = ""; } else if (W.h) { el.style.height = W.h; }
    el.classList.toggle("min", W.min);
    el.querySelector('[data-win="min"] i').className = "bi bi-chevron-" + (W.min ? "up" : "down");
    reclampWin();
  }
  function toggleBig() {
    const el = $("lhWin");
    W.big = !W.big;
    el.classList.toggle("big", W.big);
    el.style.width = el.style.height = "";
    el.querySelector('[data-win="big"] i').className = "bi bi-arrows-angle-" + (W.big ? "contract" : "expand");
    reclampWin();
  }
  function winMsg(t) { $("lhWinMsg").textContent = t || ""; }

  function renderWinMeta() {
    const l = detail;
    if (!l) return;
    $("lhWinFor").innerHTML = `${avatarHtml(l.customer_name, "sm")}<span class="nm">${esc(l.customer_name)}</span>${l.company_name ? `<span class="co">· ${esc(l.company_name)}</span>` : ""}`;
    const items = [`<span class="lh-rel"><i class="bi bi-person"></i>${esc(l.customer_name)}<small>Lead</small></span>`];
    if (l.contact) items.push(`<a class="lh-rel" target="_blank" rel="noopener" href="${esc(url("contactOpen", l.contact.id))}"><i class="bi bi-person-lines-fill"></i>${esc(l.contact.name)}<small>Contact</small></a>`);
    if (l.company) items.push(`<a class="lh-rel" target="_blank" rel="noopener" href="${esc(url("companyOpen", l.company.id))}"><i class="bi bi-building"></i>${esc(l.company.name)}<small>Company</small></a>`);
    dealsOf(l).forEach(d => items.push(`<a class="lh-rel" target="_blank" rel="noopener" href="${esc(dealUrl(d.id))}"><i class="bi bi-briefcase"></i>${esc(d.name)}<small>Deal</small></a>`));
    $("lhWinRelBtn").innerHTML = `<span>Related records <b>${items.length}</b></span><i class="bi bi-chevron-down"></i>`;
    $("lhWinRelList").innerHTML = items.join("");
  }
  function renderWhen() {
    const d = draftOf(W.leadId), w = d[whenProp()];
    $("lhWinFuCheck").checked = d.withFu;
    $("lhWinWhenBtn").disabled = !d.withFu;
    $("lhWinWhenBtn").innerHTML = `${esc(w.full)}<i class="bi bi-chevron-down"></i>`;
    $("lhWinNoteDt").classList.toggle("d-none", !(d.withFu && w.key === "custom"));
    $("lhWinChips").innerHTML = presets().map(p => `<button type="button" class="lh-chipbtn ${w.key === p.key ? "on" : ""}" data-p="${p.key}"><b>${esc(p.label)}</b><small>${esc(p.sub)}</small></button>`).join("")
      + `<button type="button" class="lh-chipbtn ${w.key === "custom" ? "on" : ""}" data-p="custom"><b>Custom</b><small>Pick date &amp; time</small></button>`;
    document.querySelectorAll("#lhWin [data-wd]").forEach(i => { if (i.value !== w.date) i.value = w.date || ""; });
    document.querySelectorAll("#lhWin [data-wt]").forEach(i => { if (i.value !== w.time) i.value = w.time || ""; });
    $("lhWinSum").textContent = customFull(w.date, w.time) + (inFuture(w.date, w.time) ? "" : " · already passed");
  }
  function renderWin() {
    if (!detail) return;
    const d = draftOf(W.leadId), note = W.mode === "note", el = $("lhWin");
    el.classList.toggle("is-note", note); el.classList.toggle("is-fu", !note);
    el.setAttribute("aria-label", note ? "Add note" : "Schedule follow-up");
    document.querySelectorAll("#lhWinModes [data-mode]").forEach(b => { const on = b.dataset.mode === W.mode; b.classList.toggle("on", on); b.setAttribute("aria-selected", on ? "true" : "false"); });
    const ta = $("lhWinText"), v = note ? d.note : d.fu;
    if (ta.value !== v) ta.value = v;
    ta.placeholder = note ? "Start typing to leave a note…" : `What needs to happen? Optional — defaults to “Follow up with ${detail.customer_name}”`;
    document.querySelectorAll("#lhWinApt input").forEach(r => { r.checked = r.value === (d.apt || ""); });
    $("lhWinSubmit").textContent = note ? (d.withFu ? "Create note & follow-up" : "Create note") : "Schedule follow-up";
    renderWinMeta(); renderWhen(); updateCount(); winMsg("");
    reclampWin();
  }
  function updateCount() {
    const n = $("lhWinText").value.length;
    ["lhWinCount", "lhWinCountFu"].forEach(id => { const c = $(id); c.textContent = n + " / 1000"; c.classList.toggle("warn", n > 950); });
  }
  function syncWin() {
    if (!W.open || !detail) return;
    if (W.leadId !== detail.id) { W.leadId = detail.id; renderWin(); } else renderWinMeta();
  }

  function applyFmt(kind) {
    const ta = $("lhWinText");
    ta.focus();
    const s = ta.selectionStart, e = ta.selectionEnd, v = ta.value, sel = v.slice(s, e);
    if (kind === "list") {
      const a = v.lastIndexOf("\n", s - 1) + 1, bi = v.indexOf("\n", e), b = bi === -1 ? v.length : bi;
      const block = v.slice(a, b).split("\n").map(x => /^\s*[-•]\s/.test(x) ? x : "- " + x).join("\n");
      ta.value = v.slice(0, a) + block + v.slice(b);
      ta.setSelectionRange(a, a + block.length);
    } else if (kind === "link") {
      const ins = sel && /^https?:\/\//.test(sel) ? sel : "https://";
      ta.value = v.slice(0, s) + ins + v.slice(e);
      ta.setSelectionRange(s + ins.length, s + ins.length);
    } else {
      const m = { b: "**", i: "*", s: "~~" }[kind], txt = sel || "text";
      ta.value = v.slice(0, s) + m + txt + m + v.slice(e);
      ta.setSelectionRange(s + m.length, s + m.length + txt.length);
    }
    ta.dispatchEvent(new Event("input", { bubbles: true }));
  }

  async function submitWin() {
    const id = W.leadId, d = draftOf(id), note = W.mode === "note";
    const text = $("lhWinText").value.trim(), btn = $("lhWinSubmit");
    const w = d[whenProp()], name = detail ? detail.customer_name : "this lead";
    winMsg("");
    if (note && !text) { winMsg("Write something first."); $("lhWinText").focus(); return; }
    if ((!note || d.withFu) && !inFuture(w.date, w.time)) { winMsg("Pick a follow-up time in the future."); return; }
    btn.disabled = true;
    let remindersTouched = false;
    try {
      if (note) {
        const res = await reqJson(CFG.routes.reminderStore, "POST", { lead_id: id, type: "note", note: text });
        if (!res.success) { winMsg(firstErr(res, "Couldn't save the note.")); return; }
        d.note = "";
        if (d.withFu) {
          const snippet = text.replace(/[*~]/g, "").replace(/\s+/g, " ").slice(0, 120);
          const r2 = await reqJson(CFG.routes.reminderStore, "POST", { lead_id: id, type: "reminder", note: "Follow up: " + snippet, reminder_date: w.date, reminder_time: w.time || null, appointment_type: null });
          if (r2.success) { remindersTouched = true; d.withFu = false; toast("Note added · follow-up set for " + w.full, "dark"); }
          else toast("Note saved, but the follow-up failed: " + firstErr(r2, "unknown error"), "danger");
        } else toast("Note added");
      } else {
        const res = await reqJson(CFG.routes.reminderStore, "POST", { lead_id: id, type: "reminder", note: text || "Follow up with " + name, reminder_date: w.date, reminder_time: w.time || null, appointment_type: d.apt || null });
        if (!res.success) { winMsg(firstErr(res, "Couldn't schedule the follow-up.")); return; }
        remindersTouched = true; d.fu = ""; d.apt = "";
        toast("Follow-up scheduled · " + w.full);
      }
    } catch (e) { return; } finally { btn.disabled = false; }
    closeWin();
    if (detail && detail.id === id) { actFilter = "all"; lsSet("lh.actf", "all"); setPane("activity"); }
    await openDetail(id, { refresh: true });
    if (remindersTouched && window.GlobalReminders) window.GlobalReminders.refresh();
  }

  async function openComposer(id, mode) {
    if (!detail || detail.id !== id || !$("lhDrawer").classList.contains("show")) await openDetail(id, { pane: "activity" });
    if (detail && detail.id === id) showWin(mode);
  }

  function bindWindow() {
    const el = $("lhWin"), handle = $("lhWinHandle");

    // drag by the header (mouse, touch and pen)
    let drag = null;
    handle.addEventListener("pointerdown", e => {
      if (e.button !== 0 || e.target.closest("button") || window.innerWidth < 768) return;
      const r = el.getBoundingClientRect();
      drag = { dx: e.clientX - r.left, dy: e.clientY - r.top };
      handle.setPointerCapture(e.pointerId);
      el.classList.add("dragging");
    });
    handle.addEventListener("pointermove", e => { if (drag) clampWin(e.clientX - drag.dx, e.clientY - drag.dy); });
    const end = () => {
      if (!drag) return;
      drag = null; el.classList.remove("dragging");
      const r = el.getBoundingClientRect();
      W.pos = { x: Math.round(r.left), y: Math.round(r.top) };
      lsSet("lh.win.pos", W.pos);
    };
    handle.addEventListener("pointerup", end);
    handle.addEventListener("pointercancel", end);
    handle.addEventListener("dblclick", e => { if (e.target.closest("button")) return; W.pos = null; lsSet("lh.win.pos", null); placeWin(); });
    handle.addEventListener("keydown", e => {
      if (!e.altKey || e.key.indexOf("Arrow") !== 0 || window.innerWidth < 768) return;
      e.preventDefault();
      const r = el.getBoundingClientRect(), s = e.shiftKey ? 60 : 20;
      const p = clampWin(r.left + (e.key === "ArrowRight" ? s : e.key === "ArrowLeft" ? -s : 0), r.top + (e.key === "ArrowDown" ? s : e.key === "ArrowUp" ? -s : 0));
      W.pos = p; lsSet("lh.win.pos", p);
    });
    window.addEventListener("resize", reclampWin);

    el.addEventListener("click", e => {
      const b = e.target.closest("button");
      if (!b) return;
      if (b.dataset.win === "close") closeWin();
      else if (b.dataset.win === "min") toggleMin();
      else if (b.dataset.win === "big") toggleBig();
      else if (b.dataset.mode) { W.mode = b.dataset.mode; renderWin(); $("lhWinText").focus(); }
      else if (b.dataset.fmt) applyFmt(b.dataset.fmt);
      else if (b.dataset.p) {
        const d = draftOf(W.leadId), prop = whenProp();
        if (b.dataset.p === "custom") { const c = d[prop]; d[prop] = { key: "custom", date: c.date, time: c.time || "10:00", full: customFull(c.date, c.time || "10:00") }; }
        else d[prop] = Object.assign({}, presets().find(p => p.key === b.dataset.p));
        renderWhen();
      }
      else if (b.id === "lhWinRelBtn") { const open = b.getAttribute("aria-expanded") !== "true"; b.setAttribute("aria-expanded", open ? "true" : "false"); $("lhWinRelList").classList.toggle("d-none", !open); }
      else if (b.id === "lhWinWhenBtn") {
        const d = draftOf(W.leadId);
        popMenu(b, presets().map(p => ({ label: p.full, sub: p.sub, checked: d.noteWhen.key === p.key, fn: () => { d.noteWhen = Object.assign({}, p); renderWhen(); } }))
          .concat([{ sep: true }, { label: "Pick date & time…", icon: "calendar3", fn: () => { const c = d.noteWhen; d.noteWhen = { key: "custom", date: c.date, time: c.time || "10:00", full: customFull(c.date, c.time || "10:00") }; renderWhen(); } }]), { width: 330 });
      }
      else if (b.id === "lhWinSubmit") submitWin();
    });

    $("lhWinText").addEventListener("input", e => {
      const d = draftOf(W.leadId);
      if (W.mode === "note") d.note = e.target.value; else d.fu = e.target.value;
      updateCount(); winMsg("");
    });
    $("lhWinText").addEventListener("keydown", e => {
      if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === "b" && W.mode === "note") { e.preventDefault(); applyFmt("b"); }
      else if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === "i" && W.mode === "note") { e.preventDefault(); applyFmt("i"); }
    });
    el.addEventListener("keydown", e => {
      if (e.key === "Escape") { e.stopPropagation(); closeWin(); }
      else if ((e.ctrlKey || e.metaKey) && e.key === "Enter") { e.preventDefault(); submitWin(); }
    });
    $("lhWinFuCheck").addEventListener("change", e => { draftOf(W.leadId).withFu = e.target.checked; renderWin(); });
    el.addEventListener("change", e => {
      const d = draftOf(W.leadId), prop = whenProp();
      if (e.target.name === "lhWinApt") { d.apt = e.target.value; return; }
      if (e.target.matches("[data-wd],[data-wt]")) {
        const c = d[prop], box = e.target.closest(".lh-win-dt, .lh-win-sec"), dI = box.querySelector("[data-wd]"), tI = box.querySelector("[data-wt]");
        d[prop] = { key: "custom", date: dI.value, time: tI.value, full: customFull(dI.value, tI.value) };
        renderWhen();
      }
    });
  }

  /* ---- files ---- */

  function renderFiles() {
    const a = (detail.attachments || []).slice().sort((x, y) => y.id - x.id);
    $("lhNFiles").textContent = a.length;
    $("ldAttachments").innerHTML = a.length ? a.map(f => `
      <div class="attachment-item">
        <div class="attachment-icon"><i class="bi ${fileIcon(f.mime_type)}"></i></div>
        <div style="min-width:0;flex:1"><div class="attachment-name">${esc(f.file_name)}</div><div class="attachment-meta">${esc(f.file_size_label || "")}${f.uploader ? " · " + esc(f.uploader.name) : ""}</div></div>
        <a class="lh-iconbtn" href="${esc(f.file_url)}" target="_blank" rel="noopener" title="Download" aria-label="Download"><i class="bi bi-download"></i></a>
        <button type="button" class="lh-iconbtn danger" data-del-attachment="${f.id}" title="Delete" aria-label="Delete file"><i class="bi bi-trash"></i></button>
      </div>`).join("") : `<div class="lh-emptybox" style="padding:30px 20px"><p class="mb-0" style="font-size:13px">No files attached yet.</p></div>`;
  }
  function uploadAttachment(file) {
    const leadId = detail.id, input = $("ldAttachmentInput"), btn = $("ldAttachmentUploadBtn");
    const setPct = p => { $("ldAttachmentUploadFill").style.width = p + "%"; $("ldAttachmentUploadPct").textContent = p + "%"; };
    btn.style.pointerEvents = "none"; btn.style.opacity = ".5";
    $("ldAttachmentUploadName").textContent = file.name;
    setPct(0);
    $("ldAttachmentUploadProgress").classList.remove("d-none");

    const fd = new FormData();
    fd.append("lead_id", leadId);
    fd.append("attachment", file);
    const xhr = new XMLHttpRequest();
    xhr.open("POST", CFG.routes.attachmentStore);
    xhr.setRequestHeader("X-CSRF-TOKEN", CSRF);
    xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
    xhr.setRequestHeader("Accept", "application/json");
    xhr.upload.addEventListener("progress", e => { if (e.lengthComputable) setPct(Math.round((e.loaded / e.total) * 100)); });
    const finish = async (msg, variant) => {
      input.value = ""; btn.style.pointerEvents = ""; btn.style.opacity = "";
      $("ldAttachmentUploadProgress").classList.add("d-none");
      if (msg) toast(msg, variant);
      if (detail && detail.id === leadId) { await openDetail(leadId, { refresh: true }); }
    };
    xhr.onload = () => {
      if (xhr.status === 401 || xhr.status === 419) { finish("Your session has expired. Please sign in again.", "danger"); return; }
      let res; try { res = JSON.parse(xhr.responseText); } catch (e) { res = { success: false }; }
      if (!res.success) { finish(firstErr(res, "Couldn't upload the file."), "danger"); return; }
      finish("File uploaded");
    };
    xhr.onerror = () => finish("Upload failed — check your connection.", "danger");
    xhr.send(fd);
  }
  function bindFiles() {
    const input = $("ldAttachmentInput"), drop = $("ldAttachmentUploadBtn");
    input.addEventListener("change", e => { const f = e.target.files[0]; if (f && detail) uploadAttachment(f); });
    ["dragenter", "dragover"].forEach(ev => drop.addEventListener(ev, e => { e.preventDefault(); drop.classList.add("over"); }));
    ["dragleave", "drop"].forEach(ev => drop.addEventListener(ev, e => { e.preventDefault(); drop.classList.remove("over"); }));
    drop.addEventListener("drop", e => { const f = e.dataTransfer.files[0]; if (f && detail) uploadAttachment(f); });
    $("ldAttachments").addEventListener("click", async e => {
      const b = e.target.closest("[data-del-attachment]");
      if (!b || !confirm("Delete this file?")) return;
      const res = await apiFetch(url("attachmentDestroy", b.dataset.delAttachment), { method: "DELETE" });
      if (!res.success) { toast("Couldn't delete the file.", "danger"); return; }
      await openDetail(detail.id, { refresh: true });
    });
  }

  /* ---- share card ---- */

  function buildShareCard(l) {
    $("shareCard").innerHTML = `
      <div class="slc-header"><div class="slc-brand">Dalal Adda · Lead</div><div class="slc-avatar">${esc(initials(l.customer_name))}</div></div>
      <div class="slc-body">
        <div class="slc-name">${esc(l.customer_name)}</div>
        <div class="slc-product">${esc(l.company_name || l.product_name || "")}</div>
        ${pill(l.lead_type, false)}
        <div class="slc-row" style="margin-top:12px"><span>Phone</span><span>${esc(l.phone || "—")}</span></div>
        ${l.email ? `<div class="slc-row"><span>Email</span><span>${esc(l.email)}</span></div>` : ""}
        ${l.whatsapp ? `<div class="slc-row"><span>WhatsApp</span><span>${esc(l.whatsapp)}</span></div>` : ""}
        ${l.product_name && l.company_name ? `<div class="slc-row"><span>Interest</span><span>${esc(l.product_name)}</span></div>` : ""}
        <div class="slc-row"><span>Source</span><span>${esc(l.source || "—")}${l.social_platform ? " · " + esc(l.social_platform) : ""}</span></div>
        <div class="slc-row"><span>Assigned to</span><span>${esc((l.assigned_admins || []).map(a => a.name).join(", ") || "Unassigned")}</span></div>
        <div class="slc-footer">Shared from Dalal Adda CRM</div>
      </div>`;
  }
  function renderCardToBlob(cb) {
    if (typeof html2canvas !== "function") { toast("Couldn't create the card image.", "danger"); return; }
    html2canvas($("shareCard"), { backgroundColor: null, scale: 2 }).then(canvas => canvas.toBlob(b => { if (b) cb(b); }));
  }
  function bindShare() {
    $("shareCardDownloadBtn").addEventListener("click", () => renderCardToBlob(blob => {
      const a = document.createElement("a"); a.href = URL.createObjectURL(blob); a.download = "lead-card.png"; a.click();
    }));
    $("shareCardShareBtn").addEventListener("click", () => renderCardToBlob(async blob => {
      const file = new File([blob], "lead-card.png", { type: "image/png" });
      if (navigator.canShare && navigator.canShare({ files: [file] })) {
        try { await navigator.share({ files: [file], title: "Lead", text: "Sharing a lead" }); } catch (e) { /* cancelled */ }
        return;
      }
      try { await navigator.clipboard.write([new ClipboardItem({ "image/png": blob })]); toast("Card image copied"); }
      catch (e) {
        const a = document.createElement("a"); a.href = URL.createObjectURL(blob); a.download = "lead-card.png"; a.click();
        toast("Sharing isn't supported here — the image was downloaded", "dark");
      }
    }));
  }

  function bindDrawer() {
    const dr = $("lhDrawer");
    dr.addEventListener("click", e => {
      const tab = e.target.closest(".lh-dr-tab");
      if (tab) { setPane(tab.dataset.pane); return; }
      const st = e.target.closest('[data-act="status"]');
      if (st) { statusMenu(st, detail.lead_type, t => setStatus(detail.id, t)); return; }
      const r = e.target.closest("[data-r]");
      if (r) { reminderClick(r); return; }
      const b = e.target.closest("[data-dr]");
      if (b && detail) {
        const a = b.dataset.dr, idx = orderIds.indexOf(detail.id);
        if (a === "prev" && idx > 0) openDetail(orderIds[idx - 1], { refresh: true });
        else if (a === "next" && idx >= 0 && idx < orderIds.length - 1) openDetail(orderIds[idx + 1], { refresh: true });
        else if (a === "pin") togglePin(detail.id);
        else if (a === "delete") askDelete([detail.id]);
        else if (a === "share") afterDrawerClosed(() => { buildShareCard(detail); bootstrap.Modal.getOrCreateInstance($("shareCardModal")).show(); });
        else if (a === "note" || a === "fu") showWin(a);
        else if (a === "deals") { setPane("about"); const ds = $("lhDealsSect"); if (ds) ds.scrollIntoView({ block: "start", behavior: "smooth" }); }
        else if (a === "coChange") companyPicker(b, c => setLeadCompany(c.id));
        else if (a === "coRemove") setLeadCompany(null);
        return;
      }
      const cell = e.target.closest(".lh-prop-v[data-edit]");
      if (cell) startEdit(cell);
    });
    dr.addEventListener("keydown", e => {
      if (e.key === "Enter" && e.target.matches(".lh-prop-v[data-edit]")) { e.preventDefault(); startEdit(e.target); }
    });
    dr.addEventListener("hidden.bs.offcanvas", e => { if (e.target === dr) { closePop(); closeWin(); } });
  }

  /* ==================== CREATE LEAD: Contact -> Company -> Lead ==================== */

  let CR = null;
  let crToken = 0;

  const DEAL_STAGES = CFG.dealStages || {};
  const DEAL_OPEN = (CFG.dealOpenStages && CFG.dealOpenStages.length) ? CFG.dealOpenStages : Object.keys(DEAL_STAGES).filter(k => k !== "won" && k !== "lost");
  const dealStageLabel = k => (DEAL_STAGES[k] && DEAL_STAGES[k].label) || String(k || "").replace(/_/g, " ").replace(/\b\w/g, c => c.toUpperCase());
  function closeIn(days) { const t = new Date(); t.setDate(t.getDate() + days); return ymd(t); }

  function newCR(opts) {
    return {
      step: 1, busy: false,
      contact: null, mode: "search", q: "", results: null, form: { name: "", email: "", phone: "", whatsapp: "", job_title: "" }, dupe: null,
      company: null, cmode: "search", cq: "", cresults: null, cform: { name: "", website: "", phone: "", gst_number: "", address: "" }, cdupe: null,
      status: (opts && opts.status) || (TYPES.indexOf("cold") >= 0 ? "cold" : TYPES[0]),
      source: lsGet("lh.lastSource", SOURCES[0]) || SOURCES[0], platform: "", product: "", assigned: new Set(),
      // "Interested in": free-text product, OR a deal (existing -> pick it, or create a new one)
      imode: "text", deal: null, dmode: "search", dq: "", dresults: null, dform: { name: "", stage: DEAL_OPEN[0] || "new", amount: "", close: closeIn(30) },
    };
  }
  function openCreate(opts) {
    CR = newCR(opts || {});
    renderCreate();
    const m = $("lhCreateModal");
    const focus = () => { m.removeEventListener("shown.bs.modal", focus); const i = $("crQ") || $("crName"); if (i) i.focus(); };
    m.addEventListener("shown.bs.modal", focus);
    bootstrap.Modal.getOrCreateInstance(m).show();
  }

  function renderCreate() {
    const labels = [["Contact", "person"], ["Company", "building"], ["Lead", "funnel"]];
    $("lhSteps").innerHTML = labels.map((s, i) => {
      const n = i + 1, st = n < CR.step ? "done" : n === CR.step ? "active" : "";
      return `<div class="lh-step ${st}"><span class="no">${n < CR.step ? '<i class="bi bi-check-lg"></i>' : n}</span><span class="l">${s[0]}</span></div>${i < 2 ? `<div class="bar ${n < CR.step ? "done" : ""}"></div>` : ""}`;
    }).join("");
    $("lhCreateBody").innerHTML = CR.step === 1 ? crStep1() : CR.step === 2 ? crStep2() : crStep3();
    $("lhCrBack").style.visibility = CR.step > 1 ? "visible" : "hidden";
    $("lhCrSkip").classList.toggle("d-none", CR.step !== 2);
    const next = $("lhCrNext");
    next.disabled = CR.busy;
    next.innerHTML = CR.busy ? '<span class="spinner-border spinner-border-sm me-1"></span>Please wait' : CR.step === 3 ? '<i class="bi bi-check2-circle me-1"></i>Create lead' : "Continue";
    if (CR.step === 1 && CR.mode === "search" && !CR.contact) { $("crQ").value = CR.q; drawContactResults(); if (CR.results === null) searchContacts(); }
    if (CR.step === 2 && CR.cmode === "search" && !CR.company) { $("crCq").value = CR.cq; drawCompanyResults(); if (CR.cresults === null) searchCompanies(); }
    if (CR.step === 3) {
      drawAssignChips();
      if (CR.imode === "deal" && CR.dmode === "search" && !CR.deal) { const q = $("crDq"); if (q) q.value = CR.dq; drawDealResults(); if (CR.dresults === null) searchDeals(); }
    }
  }

  const crAlert = (msg, btn) => `<div class="lh-alert"><i class="bi bi-exclamation-triangle-fill"></i><span>${esc(msg)}</span>${btn || ""}</div>`;

  function crStep1() {
    const head = `<div class="lh-pane"><h6>Who is this lead for?</h6><div class="hint">Pick an existing contact or create a new one. Duplicates are caught by phone number and email.</div>`;
    if (CR.contact) {
      const c = CR.contact;
      return head + `<div class="lh-sel">${avatarHtml(c.name)}<div style="min-width:0"><div class="t">${esc(c.name)}</div><div class="s">${esc([c.email, c.phone].filter(Boolean).join(" · "))}${c.company ? " · " + esc(c.company.name) : ""}</div></div><button type="button" class="btn btn-sm btn-outline-secondary x" data-cr="changeContact">Change</button></div></div>`;
    }
    if (CR.mode === "new") {
      const f = CR.form;
      return head + `<div class="lh-newform">
        <div class="row g-3">
          <div class="col-12"><label class="form-label" for="crName">Full name *</label><input class="form-control" id="crName" data-f="name" value="${esc(f.name)}" placeholder="e.g. Rahul Sharma" autocomplete="off"></div>
          <div class="col-md-6"><label class="form-label" for="crEmail">Email</label><input class="form-control" id="crEmail" data-f="email" type="email" value="${esc(f.email)}" placeholder="rahul@company.com" autocomplete="off"></div>
          <div class="col-md-6"><label class="form-label" for="crPhone">Phone number</label><input class="form-control" id="crPhone" data-f="phone" inputmode="tel" value="${esc(f.phone)}" placeholder="+91 98765 43210" autocomplete="off"></div>
          <div class="col-md-6"><label class="form-label" for="crWa">WhatsApp</label><input class="form-control" id="crWa" data-f="whatsapp" inputmode="tel" value="${esc(f.whatsapp)}" placeholder="Leave empty if same as phone" autocomplete="off"></div>
          <div class="col-md-6"><label class="form-label" for="crJob">Job title</label><input class="form-control" id="crJob" data-f="job_title" value="${esc(f.job_title)}" placeholder="e.g. Purchase Manager" autocomplete="off"></div>
        </div>
        ${CR.dupe ? crAlert(CR.dupe.message, CR.dupe.contact ? `<button type="button" class="btn btn-sm btn-warning" data-cr="useDupe">Use ${esc(CR.dupe.contact.name)}</button>` : "") : ""}
        <button type="button" class="lh-link mt-3 p-0" data-cr="backSearch"><i class="bi bi-arrow-left me-1"></i>Search existing contacts</button>
      </div></div>`;
    }
    return head + `<div class="lh-search" style="flex:none;max-width:none"><i class="bi bi-search"></i><input type="text" class="form-control" id="crQ" placeholder="Search by name, phone or email…" autocomplete="off"></div>
      <div class="lh-res" id="crRes"></div>
      <button type="button" class="btn btn-outline-primary mt-3" data-cr="newContact"><i class="bi bi-person-plus me-1"></i>Create a new contact</button></div>`;
  }
  function drawContactResults() {
    const box = $("crRes");
    if (!box) return;
    if (CR.results === null) { box.innerHTML = '<div class="p-3 text-muted" style="font-size:13px">Loading…</div>'; return; }
    if (!CR.results.length) { box.innerHTML = `<div class="p-3 text-muted" style="font-size:13px">${CR.q ? "No contact matches — create a new one." : "No contacts yet — create the first one."}</div>`; return; }
    box.innerHTML = CR.results.map((c, i) => `<button type="button" data-ci="${i}">${avatarHtml(c.name, "sm")}<div style="min-width:0;flex:1"><div class="t">${esc(c.name)}</div><div class="s">${esc([c.email, c.phone].filter(Boolean).join(" · ") || "—")}</div>${c.company ? `<div class="s"><i class="bi bi-building me-1"></i>${esc(c.company.name)}</div>` : ""}</div><span class="r">${c.leads_count || 0} lead${(c.leads_count || 0) === 1 ? "" : "s"}</span></button>`).join("");
  }
  async function searchContacts() {
    const my = ++crToken;
    let res;
    try { res = await apiFetch(CFG.routes.contactsSearch + "?q=" + encodeURIComponent(CR.q.trim())); } catch (e) { return; }
    if (my !== crToken || !CR) return;
    CR.results = res.contacts || [];
    drawContactResults();
  }
  function pickContact(c) {
    CR.contact = c; CR.dupe = null;
    if (c.company && !CR.company) CR.company = c.company;
    renderCreate();
  }

  function crStep2() {
    const head = `<div class="lh-pane"><h6>Which company?</h6><div class="hint">Optional — skip this for an individual customer.</div>`;
    if (CR.company) {
      return head + `<div class="lh-sel"><div class="lh-co-ic" style="background:#fff"><i class="bi bi-building"></i></div><div style="min-width:0"><div class="t">${esc(CR.company.name)}</div><div class="s">${CR.contact && CR.contact.company && CR.contact.company.id === CR.company.id ? "Contact's company" : "Selected"}</div></div><button type="button" class="btn btn-sm btn-outline-secondary x" data-cr="changeCompany">Change</button></div></div>`;
    }
    if (CR.cmode === "new") {
      const f = CR.cform;
      return head + `<div class="lh-newform"><div class="row g-3">
          <div class="col-12"><label class="form-label" for="crCoName">Company name *</label><input class="form-control" id="crCoName" data-cf="name" value="${esc(f.name)}" placeholder="e.g. Sharma Traders Pvt Ltd" autocomplete="off"></div>
          <div class="col-md-6"><label class="form-label" for="crCoSite">Website</label><input class="form-control" id="crCoSite" data-cf="website" value="${esc(f.website)}" placeholder="www.example.com" autocomplete="off"></div>
          <div class="col-md-6"><label class="form-label" for="crCoPhone">Company phone</label><input class="form-control" id="crCoPhone" data-cf="phone" inputmode="tel" value="${esc(f.phone)}" autocomplete="off"></div>
          <div class="col-md-6"><label class="form-label" for="crCoGst">GST number</label><input class="form-control" id="crCoGst" data-cf="gst_number" value="${esc(f.gst_number)}" autocomplete="off"></div>
          <div class="col-md-6"><label class="form-label" for="crCoAddr">Address</label><input class="form-control" id="crCoAddr" data-cf="address" value="${esc(f.address)}" autocomplete="off"></div>
        </div>
        ${CR.cdupe ? crAlert(CR.cdupe.message, CR.cdupe.company ? `<button type="button" class="btn btn-sm btn-warning" data-cr="useCDupe">Use ${esc(CR.cdupe.company.name)}</button>` : "") : ""}
        <button type="button" class="lh-link mt-3 p-0" data-cr="backCSearch"><i class="bi bi-arrow-left me-1"></i>Search existing companies</button></div></div>`;
    }
    return head + `<div class="lh-search" style="flex:none;max-width:none"><i class="bi bi-search"></i><input type="text" class="form-control" id="crCq" placeholder="Search by company name or GST number…" autocomplete="off"></div>
      <div class="lh-res" id="crCres"></div>
      <button type="button" class="btn btn-outline-primary mt-3" data-cr="newCompany"><i class="bi bi-building-add me-1"></i>Create a new company</button></div>`;
  }
  function drawCompanyResults() {
    const box = $("crCres");
    if (!box) return;
    if (CR.cresults === null) { box.innerHTML = '<div class="p-3 text-muted" style="font-size:13px">Loading…</div>'; return; }
    if (!CR.cresults.length) { box.innerHTML = `<div class="p-3 text-muted" style="font-size:13px">${CR.cq ? "No company found — create a new one." : "No companies yet."}</div>`; return; }
    box.innerHTML = CR.cresults.map((c, i) => `<button type="button" data-cj="${i}"><div class="lh-co-ic" style="width:32px;height:32px;font-size:15px;border-radius:9px"><i class="bi bi-building"></i></div><div style="min-width:0;flex:1"><div class="t">${esc(c.name)}</div><div class="s">${esc([c.website, c.gst_number].filter(Boolean).join(" · ") || "—")}</div></div><span class="r">${c.contacts_count || 0} contacts<br>${c.leads_count || 0} leads</span></button>`).join("");
  }
  async function searchCompanies() {
    const my = ++crToken;
    let res;
    try { res = await apiFetch(CFG.routes.companiesSearch + "?q=" + encodeURIComponent(CR.cq.trim())); } catch (e) { return; }
    if (my !== crToken || !CR) return;
    CR.cresults = res.companies || [];
    drawCompanyResults();
  }

  function crStep3() {
    return `<div class="lh-pane"><h6>Lead details</h6><div class="hint">Pick a status and source, and say what they're interested in — a free-text product or a deal. You can edit everything later in the drawer.</div>
      <div class="lh-sumrow">
        <span class="lh-sumchip">${avatarHtml(CR.contact.name, "sm")}${esc(CR.contact.name)}</span>
        ${CR.company ? `<span class="lh-sumchip"><i class="bi bi-building"></i>${esc(CR.company.name)}</span>` : ""}
      </div>
      <label class="form-label fw-semibold" style="font-size:13px">Lead status</label>
      <div class="lh-statuspick mb-3">${TYPES.map(t => `<button type="button" class="${stCls(t)} ${CR.status === t ? "on" : ""}" data-st="${t}"><span class="dot"></span>${esc(typeLabel(t))}</button>`).join("")}</div>
      <div class="row g-3">
        <div class="col-md-6"><label class="form-label fw-semibold" style="font-size:13px" for="crSource">Source *</label>
          <select class="form-select" id="crSource">${SOURCES.map(s => `<option ${s === CR.source ? "selected" : ""}>${esc(s)}</option>`).join("")}</select></div>
        <div class="col-md-6 ${CR.source === "Social Media" ? "" : "d-none"}"><label class="form-label fw-semibold" style="font-size:13px" for="crPlatform">Platform</label>
          <select class="form-select" id="crPlatform"><option value="">— Select —</option>${PLATFORMS.map(s => `<option ${s === CR.platform ? "selected" : ""}>${esc(s)}</option>`).join("")}</select></div>
        <div class="col-md-6"><label class="form-label fw-semibold" style="font-size:13px">Assign to</label>
          <button type="button" class="lh-assignbtn" id="crAssign" aria-haspopup="true"></button></div>
        <div class="col-12"><label class="form-label fw-semibold" style="font-size:13px">Interested in <span class="text-muted fw-normal">(optional)</span></label>
          <div class="lh-seg2 lh-imseg" role="tablist" aria-label="Interested in">
            <button type="button" role="tab" data-im="text" class="${CR.imode === "text" ? "on" : ""}" aria-selected="${CR.imode === "text"}"><i class="bi bi-box-seam"></i>Product / interest</button>
            <button type="button" role="tab" data-im="deal" class="${CR.imode === "deal" ? "on" : ""}" aria-selected="${CR.imode === "deal"}"><i class="bi bi-briefcase"></i>Link a deal</button>
          </div>
          ${CR.imode === "text" ? crProductBox() : crDealBox()}
        </div>
      </div></div>`;
  }

  function crProductBox() {
    return `<input class="form-control" id="crProduct" value="${esc(CR.product)}" placeholder="e.g. CRM subscription" autocomplete="off">`;
  }
  function dealMeta(d) {
    return [d.amount > 0 ? inr(d.amount) : "", d.company ? d.company.name : "", d.close_date ? "Close " + fmtDate(d.close_date) : ""].filter(Boolean).join(" · ");
  }
  function crDealBox() {
    if (CR.deal) {
      const d = CR.deal;
      return `<div class="lh-sel"><div class="lh-co-ic" style="background:#fff"><i class="bi bi-briefcase"></i></div>
        <div style="min-width:0"><div class="t">${esc(d.name)} <span class="lh-dstage ${dealCls(d.stage)} ms-1">${esc(d.stage_label || dealStageLabel(d.stage))}</span></div>
        <div class="s">${esc(dealMeta(d) || "Existing deal")}</div>
        <div class="s"><i class="bi bi-link-45deg"></i> ${esc(CR.contact.name)} will be added to this deal's people.</div></div>
        <button type="button" class="btn btn-sm btn-outline-secondary x" data-cr="changeDeal">Change</button></div>`;
    }
    if (CR.dmode === "new") {
      const f = CR.dform;
      return `<div class="lh-newform"><div class="row g-3">
          <div class="col-12"><label class="form-label" for="crDname">Deal name *</label><input class="form-control" id="crDname" data-df="name" value="${esc(f.name)}" placeholder="e.g. Sharma Traders - 50 units order" autocomplete="off"></div>
          <div class="col-md-6"><label class="form-label" for="crDstage">Deal stage</label><select class="form-select" id="crDstage" data-df="stage">${DEAL_OPEN.map(k => `<option value="${esc(k)}" ${f.stage === k ? "selected" : ""}>${esc(dealStageLabel(k))}${DEAL_STAGES[k] && DEAL_STAGES[k].probability != null ? " (" + DEAL_STAGES[k].probability + "%)" : ""}</option>`).join("")}</select></div>
          <div class="col-md-6"><label class="form-label" for="crDamount">Amount</label><div class="input-group"><span class="input-group-text">₹</span><input class="form-control" id="crDamount" data-df="amount" type="number" min="0" step="any" inputmode="decimal" value="${esc(f.amount)}" placeholder="0"></div></div>
          <div class="col-md-6"><label class="form-label" for="crDclose">Expected close date</label><input class="form-control" id="crDclose" data-df="close" type="date" value="${esc(f.close)}"></div>
        </div>
        <div class="s mt-3" style="font-size:12.5px;color:var(--text-muted)"><i class="bi bi-link-45deg"></i> It will be linked to ${esc(CR.contact.name)}${CR.company ? " and " + esc(CR.company.name) : ""}, and you'll be the deal owner.</div>
        <button type="button" class="lh-link mt-2 p-0" data-cr="backDSearch"><i class="bi bi-arrow-left me-1"></i>Search existing deals</button></div>`;
    }
    return `<div class="lh-search" style="flex:none;max-width:none"><i class="bi bi-search"></i><input type="text" class="form-control" id="crDq" placeholder="Search deals by name, company or contact…" autocomplete="off"></div>
      <div class="lh-res" id="crDres"></div>
      <button type="button" class="btn btn-outline-primary mt-3" data-cr="newDeal"><i class="bi bi-plus-circle me-1"></i>Create a new deal</button>`;
  }
  function drawDealResults() {
    const box = $("crDres");
    if (!box) return;
    if (CR.dresults === null) { box.innerHTML = '<div class="p-3 text-muted" style="font-size:13px">Loading…</div>'; return; }
    if (!CR.dresults.length) { box.innerHTML = `<div class="p-3 text-muted" style="font-size:13px">${CR.dq ? "No deal found — create a new one." : "No deals yet."}</div>`; return; }
    box.innerHTML = CR.dresults.map((d, i) => `<button type="button" data-cd="${i}"><div class="lh-co-ic" style="width:32px;height:32px;font-size:15px;border-radius:9px"><i class="bi bi-briefcase"></i></div><div style="min-width:0;flex:1"><div class="t">${esc(d.name)}</div><div class="s">${esc(dealMeta(d) || "—")}</div>${d.has_contact ? `<div class="s"><i class="bi bi-check2-circle me-1"></i>Already includes ${esc(CR.contact.name)}</div>` : ""}</div><span class="r"><span class="lh-dstage ${dealCls(d.stage)}">${esc(d.stage_label || dealStageLabel(d.stage))}</span><br>${d.contacts_count || 0} contact${(d.contacts_count || 0) === 1 ? "" : "s"}</span></button>`).join("");
  }
  async function searchDeals() {
    const my = ++crToken;
    let res;
    try { res = await apiFetch(CFG.routes.dealsSearch + "?q=" + encodeURIComponent(CR.dq.trim()) + (CR.contact ? "&contact_id=" + CR.contact.id : "")); } catch (e) { return; }
    if (my !== crToken || !CR) return;
    CR.dresults = res.deals || [];
    drawDealResults();
  }
  function drawAssignChips() {
    const b = $("crAssign");
    if (!b) return;
    const names = Array.from(CR.assigned).map(id => (CFG.admins.find(a => a.id === id) || {}).name).filter(Boolean);
    b.innerHTML = names.length ? names.map(n => `<span class="chip">${esc(n)}</span>`).join("") : '<span class="ph">Choose team members…</span>';
  }

  async function crNext() {
    if (!CR || CR.busy) return;
    if (CR.step === 1) {
      if (CR.contact) { CR.step = 2; renderCreate(); return; }
      if (CR.mode !== "new") { toast("Pick a contact or create a new one first.", "danger"); return; }
      const f = CR.form;
      if (!f.name.trim()) { toast("Name is required.", "danger"); $("crName").focus(); return; }
      if (!f.phone.trim() && !f.email.trim()) { toast("Enter a phone number or an email address.", "danger"); $("crPhone").focus(); return; }
      CR.busy = true; renderCreate();
      let res;
      try { res = await reqJson(CFG.routes.contactStore, "POST", f); } catch (e) { CR.busy = false; renderCreate(); return; }
      CR.busy = false;
      if (res.success) { CR.dupe = null; pickContact(res.contact); CR.step = 2; renderCreate(); }
      else if (res.duplicate) { CR.dupe = res; renderCreate(); }
      else { renderCreate(); toast(firstErr(res, "Couldn't create the contact."), "danger"); }
    } else if (CR.step === 2) {
      if (!CR.company && CR.cmode === "new") {
        const f = CR.cform;
        if (!f.name.trim()) { toast("Company name is required (or skip this step).", "danger"); $("crCoName").focus(); return; }
        CR.busy = true; renderCreate();
        let res;
        try { res = await reqJson(CFG.routes.companyStore, "POST", f); } catch (e) { CR.busy = false; renderCreate(); return; }
        CR.busy = false;
        if (res.success) { CR.company = res.company; CR.cdupe = null; CR.step = 3; renderCreate(); }
        else if (res.duplicate) { CR.cdupe = res; renderCreate(); }
        else { renderCreate(); toast(firstErr(res, "Couldn't create the company."), "danger"); }
        return;
      }
      CR.step = 3; renderCreate();
    } else {
      if (!CR.source) { toast("Choose a source.", "danger"); return; }
      const payload = {
        contact_id: CR.contact.id, company_id: CR.company ? CR.company.id : null,
        product_name: "", source: CR.source, social_platform: CR.source === "Social Media" ? (CR.platform || null) : null,
        lead_type: CR.status, assigned_admins: Array.from(CR.assigned),
      };
      if (CR.imode === "text") payload.product_name = CR.product.trim();
      else if (CR.deal) payload.deal_id = CR.deal.id;
      else if (CR.dmode === "new") {
        const f = CR.dform;
        if (!f.name.trim()) { toast("Deal name is required (or switch back to Product / interest).", "danger"); const i = $("crDname"); if (i) i.focus(); return; }
        payload.new_deal = { name: f.name.trim(), stage: f.stage, amount: f.amount === "" ? null : f.amount, close_date: f.close || null };
      }
      CR.busy = true; renderCreate();
      let res;
      try { res = await reqJson(CFG.routes.store, "POST", payload); } catch (e) { CR.busy = false; renderCreate(); return; }
      CR.busy = false;
      if (!res.success) { renderCreate(); toast(firstErr(res, "Couldn't create the lead."), "danger"); return; }
      lsSet("lh.lastSource", CR.source);
      const lead = res.lead;
      lead.reminders = lead.reminders || []; lead.attachments = lead.attachments || [];
      putLead(lead);
      page = 1; sort = null;
      renderAll();
      const m = $("lhCreateModal");
      m.addEventListener("hidden.bs.modal", function h() { m.removeEventListener("hidden.bs.modal", h); openDetail(lead.id); });
      bootstrap.Modal.getOrCreateInstance(m).hide();
      toast(res.message || "Lead created");
    }
  }

  function bindCreate() {
    $("lhCrNext").addEventListener("click", crNext);
    $("lhCrBack").addEventListener("click", () => { if (CR && CR.step > 1) { CR.step--; renderCreate(); } });
    $("lhCrSkip").addEventListener("click", () => { CR.company = null; CR.cmode = "search"; CR.step = 3; renderCreate(); });
    const body = $("lhCreateBody");
    body.addEventListener("input", e => {
      const t = e.target;
      if (t.dataset.f) CR.form[t.dataset.f] = t.value;
      else if (t.dataset.cf) CR.cform[t.dataset.cf] = t.value;
      else if (t.id === "crQ") { CR.q = t.value; CR.results = null; drawContactResults(); debouncedContactSearch(); }
      else if (t.id === "crCq") { CR.cq = t.value; CR.cresults = null; drawCompanyResults(); debouncedCompanySearch(); }
      else if (t.id === "crProduct") CR.product = t.value;
      else if (t.dataset.df) CR.dform[t.dataset.df] = t.value;
      else if (t.id === "crDq") { CR.dq = t.value; CR.dresults = null; drawDealResults(); debouncedDealSearch(); }
    });
    const debouncedContactSearch = debounce(searchContacts, 220);
    const debouncedCompanySearch = debounce(searchCompanies, 220);
    const debouncedDealSearch = debounce(searchDeals, 220);
    body.addEventListener("change", e => {
      if (e.target.id === "crSource") { CR.source = e.target.value; renderCreate(); }
      else if (e.target.id === "crPlatform") CR.platform = e.target.value;
      else if (e.target.dataset.df) CR.dform[e.target.dataset.df] = e.target.value;
    });
    body.addEventListener("keydown", e => {
      if (e.key !== "Enter" || e.target.tagName === "BUTTON") return;
      e.preventDefault();
      if (e.target.id === "crQ") { if (CR.results && CR.results[0]) pickContact(CR.results[0]); else if (CR.results && !CR.results.length) body.querySelector('[data-cr="newContact"]').click(); }
      else if (e.target.id === "crCq") { if (CR.cresults && CR.cresults[0]) { CR.company = CR.cresults[0]; renderCreate(); } }
      else if (e.target.id === "crDq") { if (CR.dresults && CR.dresults[0]) { CR.deal = CR.dresults[0]; renderCreate(); } else if (CR.dresults && !CR.dresults.length) body.querySelector('[data-cr="newDeal"]').click(); }
      else crNext();
    });
    body.addEventListener("click", e => {
      const ci = e.target.closest("[data-ci]");
      if (ci) { pickContact(CR.results[+ci.dataset.ci]); return; }
      const cj = e.target.closest("[data-cj]");
      if (cj) { CR.company = CR.cresults[+cj.dataset.cj]; renderCreate(); return; }
      const st = e.target.closest("[data-st]");
      if (st) { CR.status = st.dataset.st; renderCreate(); return; }
      const cd = e.target.closest("[data-cd]");
      if (cd) { CR.deal = CR.dresults[+cd.dataset.cd]; renderCreate(); return; }
      const im = e.target.closest("[data-im]");
      if (im) {
        if (CR.imode !== im.dataset.im) { CR.imode = im.dataset.im; renderCreate(); const f = $(CR.imode === "text" ? "crProduct" : "crDq"); if (f) f.focus(); }
        return;
      }
      if (e.target.closest("#crAssign")) { assignPicker($("crAssign"), CR.assigned, drawAssignChips); return; }
      const a = e.target.closest("[data-cr]");
      if (!a) return;
      const act = a.dataset.cr;
      if (act === "changeContact") { CR.contact = null; CR.mode = "search"; CR.results = null; renderCreate(); $("crQ").focus(); }
      else if (act === "newContact") {
        const q = CR.q.trim();
        if (q) { if (q.includes("@")) CR.form.email = q; else if (digits(q).length >= 6) CR.form.phone = q; else CR.form.name = q; }
        CR.mode = "new"; CR.dupe = null; renderCreate(); $("crName").focus();
      }
      else if (act === "backSearch") { CR.mode = "search"; CR.dupe = null; renderCreate(); $("crQ").focus(); }
      else if (act === "useDupe") { pickContact(CR.dupe.contact); }
      else if (act === "changeCompany") { CR.company = null; CR.cmode = "search"; CR.cresults = null; renderCreate(); $("crCq").focus(); }
      else if (act === "newCompany") { if (CR.cq.trim()) CR.cform.name = CR.cq.trim(); CR.cmode = "new"; CR.cdupe = null; renderCreate(); $("crCoName").focus(); }
      else if (act === "backCSearch") { CR.cmode = "search"; CR.cdupe = null; renderCreate(); $("crCq").focus(); }
      else if (act === "useCDupe") { CR.company = CR.cdupe.company; CR.cdupe = null; CR.cmode = "search"; renderCreate(); }
      else if (act === "changeDeal") { CR.deal = null; CR.dmode = "search"; CR.dresults = null; renderCreate(); const q = $("crDq"); if (q) q.focus(); }
      else if (act === "newDeal") {
        const f = CR.dform;
        if (!f.name.trim()) f.name = CR.dq.trim() || ((CR.company && CR.company.name) || CR.contact.name) + " - Deal";
        CR.dmode = "new"; renderCreate(); const i = $("crDname"); if (i) { i.focus(); i.select(); }
      }
      else if (act === "backDSearch") { CR.dmode = "search"; CR.dresults = null; renderCreate(); const q = $("crDq"); if (q) q.focus(); }
    });
  }

  /* ---------------- deep link ---------------- */

  function openFromQuery() {
    const params = new URLSearchParams(window.location.search);
    const id = params.get("lead");
    if (!id) return;
    openDetail(parseInt(id, 10));
    params.delete("lead");
    window.history.replaceState({}, "", window.location.pathname + (params.toString() ? "?" + params : ""));
  }

  /* ---------------- boot ---------------- */

  document.addEventListener("DOMContentLoaded", () => {
    bindToolbar();
    bindTable();
    bindBoard();
    bindColsModal();
    bindDrawer();
    bindActivity();
    bindWindow();
    bindFiles();
    bindShare();
    bindCreate();
    renderAll();                       // skeleton while loading
    loadLeads().then(openFromQuery).catch(() => { loading = false; renderAll(); });
  });
})();
</script>

<style>

  /* ==========================================================================
     Lead name links, deals, follow-up UI, floating composer window
     ========================================================================== */
  a.lh-name-text { text-decoration: none; align-self: flex-start; max-width: 100%; }
  a.lh-name-text:hover { text-decoration: underline; color: var(--primary); }

  /* ---- deal chips ---- */
  .lh-deal { display: inline-flex; align-items: center; gap: 6px; max-width: 250px; border-radius: 999px; padding: 4px 10px 4px 8px; font-size: 12px; font-weight: 600; text-decoration: none; background: var(--primary-50); color: var(--primary); border: 1px solid var(--primary-100); white-space: nowrap; vertical-align: middle; transition: background .12s, box-shadow .12s; }
  .lh-deal:hover { background: #fff; color: var(--primary); box-shadow: 0 0 0 2px rgba(var(--primary-rgb), .14); }
  .lh-deal .t { overflow: hidden; text-overflow: ellipsis; min-width: 0; }
  .lh-deal .a { color: var(--text); font-weight: 700; }
  .lh-deal .s { font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; padding: 2px 6px; border-radius: 999px; background: #fff; flex-shrink: 0; }
  .lh-deal.won { background: var(--success-bg); color: var(--success); border-color: transparent; }
  .lh-deal.lost { background: var(--danger-bg); color: var(--danger); border-color: transparent; }
  button.lh-deal.more { background: transparent; border-style: dashed; cursor: pointer; }
  .lh-deal-mini { color: var(--primary) !important; font-weight: 600; text-decoration: none; }
  .lh-deal-mini:hover { text-decoration: underline; }

  /* ---- drawer header additions ---- */
  .lh-dr-name a { color: inherit; text-decoration: none; display: inline-flex; align-items: baseline; gap: 8px; }
  .lh-dr-name a i { font-size: 13px; opacity: 0; color: var(--text-muted); transition: opacity .12s; }
  .lh-dr-name a:hover { color: var(--primary); }
  .lh-dr-name a:hover i { opacity: 1; }
  .lh-colink { color: inherit; text-decoration: none; display: inline-flex; gap: 5px; align-items: center; }
  .lh-colink:hover { color: var(--primary); text-decoration: underline; }
  .lh-dr-meta { display: flex; flex-direction: column; gap: 9px; margin-top: 14px; align-items: flex-start; }
  .lh-dr-deals { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
  .lh-dr-deals .lbl { font-size: 11px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: var(--text-muted); display: inline-flex; gap: 5px; align-items: center; margin-right: 2px; }
  .lh-dr-deals.none { font-size: 12.5px; color: #A7ABC2; gap: 7px; }
  .lh-fuchip { display: inline-flex; align-items: center; gap: 7px; border: 1px solid transparent; border-radius: 999px; padding: 5px 12px 5px 10px; font-size: 12.5px; font-weight: 700; max-width: 100%; background: var(--primary-50); color: var(--primary); transition: box-shadow .12s; }
  .lh-fuchip:hover { box-shadow: 0 0 0 2px rgba(var(--primary-rgb), .16); }
  .lh-fuchip .tx { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-weight: 600; color: var(--text); max-width: 190px; }
  .lh-fuchip.overdue { background: var(--danger-bg); color: var(--danger); }
  .lh-fuchip.today { background: var(--warning-bg); color: #9A5B00; }
  .lh-fuchip.none { background: transparent; border: 1px dashed #C9CDDD; color: var(--text-muted); font-weight: 600; }
  .lh-fuchip.none:hover { color: var(--primary); border-color: var(--primary); }

  /* ---- deals section in About ---- */
  .lh-deal-row { display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-top: 1px solid #F0F1F6; text-decoration: none; color: var(--text); }
  .lh-deal-row:first-of-type { border-top: 0; }
  .lh-deal-row:hover { background: #F8F9FF; }
  .lh-deal-row .ic { width: 38px; height: 38px; border-radius: 11px; background: var(--primary-50); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 17px; flex-shrink: 0; }
  .lh-deal-row .mid { min-width: 0; flex: 1; display: block; }
  .lh-deal-row .nm { display: block; font-weight: 700; font-size: 13.5px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .lh-deal-row .mt { display: flex; gap: 12px; flex-wrap: wrap; font-size: 12px; color: var(--text-muted); margin-top: 3px; }
  .lh-deal-row .mt b { color: var(--text); }
  .lh-deal-row .mt i { margin-right: 4px; }
  .lh-deal-row .rt { text-align: right; flex-shrink: 0; display: flex; flex-direction: column; align-items: flex-end; gap: 3px; }
  .lh-deal-row .rt small { font-size: 11px; color: var(--text-muted); }
  .lh-dstage { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; padding: 3px 9px; border-radius: 999px; background: var(--primary-50); color: var(--primary); }
  .lh-dstage.won { background: var(--success-bg); color: var(--success); }
  .lh-dstage.lost { background: var(--danger-bg); color: var(--danger); }
  .lh-sect-h .lh-count { margin-left: 2px; }

  /* ---- reminder chip states (table / board) ---- */
  .lh-rem-chip.today { color: #9A5B00; }
  .lh-rem-chip small { font-weight: 600; opacity: .8; }

  /* ---- activity pane ---- */
  .lh-actbar { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 14px; }
  .lh-actbtn { display: flex; align-items: center; gap: 11px; text-align: left; border: 1px solid var(--border); background: #fff; border-radius: 13px; padding: 11px 13px; transition: all .12s; }
  .lh-actbtn:hover { border-color: var(--primary); box-shadow: 0 4px 14px rgba(var(--primary-rgb), .14); transform: translateY(-1px); }
  .lh-actbtn > i { width: 36px; height: 36px; border-radius: 10px; background: var(--gold-bg); color: var(--gold); display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
  .lh-actbtn[data-dr="fu"] > i { background: var(--primary-50); color: var(--primary); }
  .lh-actbtn b { display: block; font-size: 13.5px; }
  .lh-actbtn small { display: block; font-size: 11.5px; color: var(--text-muted); line-height: 1.3; }

  .lh-hero { border-radius: 16px; padding: 15px 16px 13px; margin-bottom: 14px; background: linear-gradient(135deg, var(--primary-50), #fff); border: 1px solid var(--primary-100); box-shadow: var(--shadow-sm); border-left: 5px solid var(--primary); }
  .lh-hero.overdue { background: linear-gradient(135deg, var(--danger-bg), #fff); border-color: #F6CACA; border-left-color: var(--danger); }
  .lh-hero.today { background: linear-gradient(135deg, var(--warning-bg), #fff); border-color: #F4DDB0; border-left-color: var(--warning); }
  .lh-hero-top { display: flex; align-items: center; gap: 8px; font-size: 11px; font-weight: 800; letter-spacing: .07em; text-transform: uppercase; color: var(--primary); }
  .lh-hero.overdue .lh-hero-top { color: var(--danger); }
  .lh-hero.today .lh-hero-top { color: #9A5B00; }
  .lh-hero-state { margin-left: auto; font-size: 12px; letter-spacing: 0; text-transform: none; font-weight: 700; }
  .lh-hero-when { font-size: 20px; font-weight: 800; letter-spacing: -.01em; margin: 8px 0 4px; }
  .lh-hero-note { font-size: 14px; color: var(--text); word-break: break-word; white-space: pre-wrap; }
  .lh-hero-foot { display: flex; align-items: center; gap: 8px; margin-top: 13px; flex-wrap: wrap; }
  .lh-hero-foot .sp { flex: 1; }
  .lh-hbtn { display: inline-flex; align-items: center; gap: 7px; border: 1px solid var(--border); background: #fff; border-radius: 10px; padding: 7px 12px; font-size: 13px; font-weight: 700; color: var(--text); transition: all .12s; }
  .lh-hbtn:hover { border-color: var(--primary); color: var(--primary); }
  .lh-hbtn.primary { background: var(--primary); border-color: var(--primary); color: #fff; box-shadow: 0 4px 12px rgba(var(--primary-rgb), .25); }
  .lh-hbtn.primary:hover { filter: brightness(1.08); color: #fff; }
  .lh-hbtn.icon { padding: 7px 10px; }
  .lh-hbtn .bi-chevron-down { font-size: 10px; opacity: .7; }
  .lh-apt { display: inline-flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 600; color: var(--text-muted); background: #fff; border: 1px solid var(--border); border-radius: 999px; padding: 3px 9px; }

  .lh-seg2 { display: flex; gap: 4px; background: #EBEDF6; border-radius: 11px; padding: 3px; margin-bottom: 12px; }
  .lh-seg2 button { flex: 1; border: 0; background: transparent; border-radius: 8px; padding: 6px 8px; font-size: 12.5px; font-weight: 700; color: var(--text-muted); display: inline-flex; align-items: center; justify-content: center; gap: 6px; }
  .lh-seg2 button b { font-size: 11px; background: #fff; border-radius: 999px; padding: 0 7px; }
  .lh-seg2 button.on { background: #fff; color: var(--primary); box-shadow: var(--shadow-sm); }
  .lh-seg2 button.on b { background: var(--primary-50); }

  .lh-grp { display: flex; align-items: center; gap: 8px; margin: 16px 2px 8px; font-size: 11.5px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: var(--text-muted); }
  .lh-grp .n { background: #EBEDF6; border-radius: 999px; padding: 0 8px; font-size: 11px; letter-spacing: 0; }
  .lh-grp.late { color: var(--danger); }
  .lh-grp.late .n { background: var(--danger-bg); }
  .lh-feed { background: #fff; border: 1px solid var(--border); border-radius: 14px; overflow: hidden; }

  .lh-fu, .lh-note { display: flex; gap: 12px; padding: 12px 14px; border-top: 1px solid #F0F1F6; position: relative; }
  .lh-fu:first-child, .lh-note:first-child { border-top: 0; }
  .lh-fu.overdue { background: #FFFAFA; box-shadow: inset 3px 0 0 var(--danger); }
  .lh-fu.today { box-shadow: inset 3px 0 0 var(--warning); }
  .lh-fu .bd, .lh-note .bd { min-width: 0; flex: 1; }
  .lh-fu .tx { font-size: 13.5px; font-weight: 600; word-break: break-word; white-space: pre-wrap; }
  .lh-fu.done .tx, .lh-fu.silent .tx { opacity: .6; }
  .lh-fu.done .tx { text-decoration: line-through; }
  .lh-fu .mt, .lh-note .mt { font-size: 12px; color: var(--text-muted); margin-top: 4px; display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
  .lh-fu .mt .when { font-weight: 700; color: var(--text); }
  .lh-fu .mt .st.late { color: var(--danger); font-weight: 700; }
  .lh-fu .acts { display: flex; gap: 1px; align-items: flex-start; margin-left: auto; }
  .lh-fu .acts button, .lh-note .del { width: 28px; height: 28px; border-radius: 8px; border: 0; background: transparent; color: var(--text-muted); font-size: 14px; }
  .lh-fu .acts button:hover { background: var(--primary-50); color: var(--primary); }
  .lh-fu .acts button[data-r="complete"]:hover { background: var(--success-bg); color: var(--success); }
  .lh-act-ic { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; background: var(--info-bg); color: var(--info); }
  .lh-act-ic.today { background: var(--warning-bg); color: #9A5B00; }
  .lh-act-ic.done { background: var(--success-bg); color: var(--success); }
  .lh-act-ic.late { background: var(--danger-bg); color: var(--danger); }
  .lh-act-ic.mute { background: #EEF1F6; color: var(--text-muted); }

  .lh-note { background: #FFFDF5; }
  .lh-note .ic { width: 32px; height: 32px; border-radius: 10px; background: var(--gold-bg); color: var(--gold); display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; }
  .lh-note .tx { font-size: 13.5px; word-break: break-word; line-height: 1.5; }
  .lh-note .tx ul { margin: 4px 0; padding-left: 20px; }
  .lh-note .tx a { color: var(--primary); }
  .lh-note .del { opacity: 0; }
  .lh-note:hover .del { opacity: 1; }
  .lh-note .del:hover { background: var(--danger-bg); color: var(--danger); }
  .lh-more2 { display: block; width: 100%; border: 0; border-top: 1px solid #F0F1F6; background: #FBFBFE; padding: 9px; font-size: 12.5px; font-weight: 700; color: var(--primary); }
  .lh-more2:hover { background: var(--primary-50); }

  /* ---- floating composer window (draggable) ---- */
  .lh-win { position: fixed; z-index: 30; width: 440px; min-width: 340px; max-width: calc(100vw - 16px); max-height: calc(100vh - 16px); background: #fff; border: 1px solid var(--border); border-radius: 14px; box-shadow: 0 18px 50px rgba(20, 24, 48, .28), 0 2px 8px rgba(20, 24, 48, .12); display: flex; flex-direction: column; overflow: hidden; resize: both; }
  .lh-win[hidden] { display: none; }
  .lh-win.big { width: 720px; }
  .lh-win.dragging { box-shadow: 0 28px 70px rgba(20, 24, 48, .36); user-select: none; }
  .lh-win.min { resize: none; height: auto !important; min-height: 0; }
  .lh-win.min .lh-win-body { display: none; }
  .lh-win.min .lh-win-modes button:not(.on) { display: none; }
  .lh-win-h { display: flex; align-items: center; gap: 6px; padding: 7px 8px; border-bottom: 1px solid var(--border); background: linear-gradient(180deg, #fff, #F8F9FD); cursor: grab; touch-action: none; user-select: none; flex: none; }
  .lh-win.dragging .lh-win-h { cursor: grabbing; }
  .lh-win-h .sp { flex: 1; display: flex; align-items: center; justify-content: center; color: #C0C4D6; align-self: stretch; }
  .lh-win-ic { width: 30px; height: 30px; border: 0; background: transparent; border-radius: 8px; color: var(--text-muted); display: inline-flex; align-items: center; justify-content: center; cursor: pointer; }
  .lh-win-ic:hover { background: var(--primary-50); color: var(--primary); }
  .lh-win-modes { display: inline-flex; background: #EBEDF6; border-radius: 9px; padding: 2px; gap: 2px; }
  .lh-win-modes button { border: 0; background: transparent; font-size: 12.5px; font-weight: 700; padding: 5px 11px; border-radius: 7px; color: var(--text-muted); display: inline-flex; align-items: center; gap: 6px; }
  .lh-win-modes button.on { background: #fff; color: var(--primary); box-shadow: var(--shadow-sm); }
  .lh-win-body { display: flex; flex-direction: column; flex: 1; min-height: 0; overflow-y: auto; }
  .lh-win-for { display: flex; align-items: center; gap: 10px; padding: 9px 16px; border-bottom: 1px solid var(--border); font-size: 13px; }
  .lh-win-for .lbl { color: var(--text-muted); font-weight: 600; }
  .lh-for-chip { display: inline-flex; align-items: center; gap: 8px; background: #EEF0F7; border-radius: 999px; padding: 3px 11px 3px 4px; font-weight: 600; min-width: 0; max-width: 100%; }
  .lh-for-chip .nm { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .lh-for-chip .co { color: var(--text-muted); font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .lh-win-ed { padding: 4px 16px 0; flex: 1; display: flex; flex-direction: column; min-height: 118px; }
  .lh-win-ed textarea { flex: 1; min-height: 100px; border: 0; outline: 0; resize: none; font-size: 14px; padding: 10px 0; background: transparent; width: 100%; line-height: 1.5; }
  .lh-win-tools { display: flex; align-items: center; gap: 2px; padding: 8px 0; border-top: 1px solid #F0F1F6; }
  .lh-win-tools button { width: 30px; height: 30px; border: 0; background: transparent; border-radius: 7px; color: var(--text-muted); }
  .lh-win-tools button:hover { background: var(--primary-50); color: var(--primary); }
  .lh-win-tools .cnt { margin-left: auto; font-size: 11.5px; color: var(--text-muted); }
  .lh-win-tools .cnt.warn { color: var(--danger); font-weight: 700; }
  .lh-win-sec { padding: 10px 16px; border-top: 1px solid var(--border); font-size: 13px; }
  .lh-win-relbtn { display: inline-flex; align-items: center; gap: 8px; border: 0; background: transparent; padding: 0; font-weight: 700; font-size: 13px; color: var(--text); }
  .lh-win-relbtn b { color: var(--primary); }
  .lh-win-relbtn .bi { font-size: 10px; transition: transform .15s; }
  .lh-win-relbtn[aria-expanded="true"] .bi { transform: rotate(180deg); }
  .lh-win-rellist { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 9px; }
  .lh-rel { display: inline-flex; align-items: center; gap: 6px; background: #F3F4FA; border-radius: 8px; padding: 4px 9px; font-size: 12.5px; font-weight: 600; color: var(--text); text-decoration: none; max-width: 100%; }
  .lh-rel small { color: var(--text-muted); font-weight: 600; font-size: 11px; }
  a.lh-rel:hover { background: var(--primary-50); color: var(--primary); }
  .lh-win-fucheck { display: flex; align-items: center; gap: 9px; flex-wrap: wrap; }
  .lh-win-fucheck label { margin: 0; font-weight: 600; cursor: pointer; }
  .lh-whenbtn { display: inline-flex; align-items: center; gap: 7px; border: 0; background: transparent; font-weight: 700; font-size: 13px; color: var(--text); padding: 3px 6px; border-radius: 7px; }
  .lh-whenbtn:hover:not(:disabled) { background: var(--primary-50); color: var(--primary); }
  .lh-whenbtn:disabled { opacity: .5; }
  .lh-whenbtn .bi { font-size: 10px; }
  .lh-win-dt { display: flex; gap: 8px; margin-top: 9px; }
  .lh-win-dt .form-control { font-size: 13px; }
  .apt-type-group { display: flex; gap: 8px; }
  .apt-type-option { flex: 1; }
  .apt-type-option input { display: none; }
  .apt-type-option label { display: flex; align-items: center; justify-content: center; gap: 7px; border: 1.5px solid var(--border); border-radius: 10px; padding: 8px; font-size: 13px; font-weight: 600; color: var(--text-muted); cursor: pointer; background: #FBFBFE; margin: 0; }
  .apt-type-option input:checked + label { border-color: var(--primary); color: var(--primary); background: var(--primary-50); }
  .lh-chips { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 7px; }
  .lh-chipbtn { text-align: left; border: 1.5px solid var(--border); background: #FBFBFE; border-radius: 10px; padding: 7px 10px; line-height: 1.25; }
  .lh-chipbtn b { display: block; font-size: 12.5px; color: var(--text); }
  .lh-chipbtn small { display: block; font-size: 11px; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .lh-chipbtn:hover { border-color: var(--primary-light); }
  .lh-chipbtn.on { border-color: var(--primary); background: var(--primary-50); }
  .lh-chipbtn.on b { color: var(--primary); }
  .lh-win-sum { display: flex; align-items: center; gap: 8px; margin-top: 10px; font-weight: 700; font-size: 13.5px; color: var(--primary); }
  .lh-win-lbl { font-size: 11px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: var(--text-muted); margin-bottom: 7px; }
  .lh-win-foot { display: flex; align-items: center; gap: 10px; padding: 11px 16px; border-top: 1px solid var(--border); background: #FBFBFE; margin-top: auto; flex: none; }
  .lh-win-msg { font-size: 12.5px; color: var(--danger); font-weight: 600; flex: 1; min-width: 0; }
  .lh-win.is-note .only-fu, .lh-win.is-fu .only-note { display: none !important; }
  .lh-pop .it > span:first-of-type:not(.sub) { white-space: nowrap; }
  .lh-pop .it .sub { margin-left: auto; font-size: 11.5px; color: var(--text-muted); white-space: nowrap; padding-left: 14px; }
  @media (max-width: 767.98px) {
    .lh-win { left: 0 !important; right: 0; top: auto !important; bottom: 0; width: 100% !important; max-width: none; min-width: 0; border-radius: 16px 16px 0 0; resize: none; max-height: 88vh; }
    .lh-win-h { cursor: default; }
    .lh-win [data-win="big"] { display: none; }
    .lh-chips { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .lh-actbar { grid-template-columns: 1fr; }
  }
  @media (prefers-reduced-motion: reduce) { .lh-actbtn { transition: none; } }
  .lh-iconbtn:disabled { opacity: .35; pointer-events: none; }
  .lh-pop .it small { font-size: 11.5px; }
  .lh-pop .alist, .lh-pop .clist { max-height: 260px; overflow-y: auto; }
  .lh-pop .it > span:not(.cnt):not(.sdot) { min-width: 0; overflow: hidden; text-overflow: ellipsis; }
  .lh-newform .form-control, .lh-newform .form-select { background: #fff; }
  .lh-imseg { margin-bottom: 10px; max-width: 360px; }
  .lh-imseg button i { font-size: 13px; }
  .lh-res .r .lh-dstage { font-size: 10px; padding: 2px 8px; }
  @media (max-width: 575.98px) { .lh-imseg { max-width: none; } }
</style>

@endsection