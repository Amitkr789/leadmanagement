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
  .lh-name:hover .lh-name-text { text-decoration: underline; }
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
  .lh-dr-quick { display: flex; gap: 10px; margin: 16px 0 14px; flex-wrap: wrap; }
  .lh-q { display: flex; flex-direction: column; align-items: center; gap: 5px; width: 62px; border: 0; background: transparent; font-size: 11.5px; font-weight: 600; color: var(--text-muted); text-decoration: none; }
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

  /* activity */
  .lh-act-item { display: flex; gap: 12px; padding: 12px 16px; border-top: 1px solid #F0F1F6; }
  .lh-act-item:first-child { border-top: 0; }
  .lh-act-ic { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; background: var(--info-bg); color: var(--info); }
  .lh-act-ic.note { background: var(--gold-bg); color: var(--gold); }
  .lh-act-ic.done { background: var(--success-bg); color: var(--success); }
  .lh-act-ic.late { background: var(--danger-bg); color: var(--danger); }
  .lh-act-ic.mute { background: #EEF1F6; color: var(--text-muted); }
  .lh-act-t { font-size: 13.5px; font-weight: 600; word-break: break-word; }
  .lh-act-m { font-size: 12px; color: var(--text-muted); margin-top: 3px; display: flex; gap: 10px; flex-wrap: wrap; }
  .lh-act-item.completed .lh-act-t { text-decoration: line-through; opacity: .6; }
  .lh-act-btns { display: flex; gap: 2px; margin-left: auto; align-items: flex-start; }
  .lh-act-btns button { width: 28px; height: 28px; border-radius: 8px; border: 0; background: transparent; color: var(--text-muted); font-size: 14px; }
  .lh-act-btns button:hover { background: var(--primary-50); color: var(--primary); }
  .lh-act-btns button.del:hover { background: var(--danger-bg); color: var(--danger); }
  .lh-compose { padding: 14px 16px; }
  .apt-type-group { display: flex; gap: 10px; }
  .apt-type-option { flex: 1; }
  .apt-type-option input { display: none; }
  .apt-type-option label { display: flex; align-items: center; justify-content: center; gap: 7px; border: 1.5px solid var(--border); border-radius: 10px; padding: 8px; font-size: 13px; font-weight: 600; color: var(--text-muted); cursor: pointer; background: #FBFBFE; margin: 0; }
  .apt-type-option input:checked + label { border-color: var(--primary); color: var(--primary); background: var(--primary-50); }

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
      <p>Contact se shuru karo, company jodo, aur lead ko pipeline me aage badhao.</p>
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

      <div id="lhPaneActivity" class="d-none">
        <div class="lh-sect">
          <div class="lh-sect-h"><i class="bi bi-plus-circle"></i>Log a reminder or note</div>
          <form id="reminderForm" class="lh-compose">
            <input type="hidden" id="remLeadId">
            <div class="apt-type-group mb-2" id="remKindGroup">
              <div class="apt-type-option">
                <input type="radio" name="remKind" id="remKindReminder" value="reminder" checked>
                <label for="remKindReminder"><i class="bi bi-alarm"></i>Reminder</label>
              </div>
              <div class="apt-type-option">
                <input type="radio" name="remKind" id="remKindNote" value="note">
                <label for="remKindNote"><i class="bi bi-sticky"></i>Note</label>
              </div>
            </div>
            <textarea class="form-control mb-2" id="remNote" rows="2" placeholder="e.g. Pricing par follow up karna hai" required></textarea>
            <div class="row g-2 mb-2">
              <div class="col-6"><input type="date" class="form-control" id="remDate"></div>
              <div class="col-6"><input type="time" class="form-control" id="remTime"></div>
            </div>
            <div id="remAppointmentFields">
              <div class="apt-type-group mb-2">
                <div class="apt-type-option">
                  <input type="radio" name="remAptType" id="remAptWhatsapp" value="whatsapp">
                  <label for="remAptWhatsapp"><i class="bi bi-whatsapp"></i>WhatsApp</label>
                </div>
                <div class="apt-type-option">
                  <input type="radio" name="remAptType" id="remAptMeeting" value="meeting">
                  <label for="remAptMeeting"><i class="bi bi-camera-video"></i>Meeting</label>
                </div>
              </div>
            </div>
            <button type="submit" class="btn btn-primary btn-sm w-100" id="remSubmitBtn">Add reminder</button>
          </form>
        </div>
        <div class="lh-sect" id="lhTimeline"></div>
      </div>

      <div id="lhPaneFiles" class="d-none">
        <label class="lh-drop" id="ldAttachmentUploadBtn" for="ldAttachmentInput">
          <i class="bi bi-cloud-arrow-up"></i>
          <b>Click karke file chuno</b> ya yahan drop karo
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
      <div class="modal-body"><p class="mb-0">Lead aur uske reminders/notes/files permanently delete ho jayenge. Contact aur company bache rahenge. Ye undo nahi hoga.</p></div>
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
      toast("Session expire ho gaya, dobara login karo.", "danger");
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
    if (b.missed.length) return `<span class="lh-rem-chip late"><i class="bi bi-exclamation-circle-fill"></i>${b.missed.length} overdue</span>`;
    const n = nextReminder(l);
    return n ? `<span class="lh-rem-chip"><i class="bi bi-alarm"></i>${esc(dayLabel(n.reminder_date))}${n.reminder_time ? " " + esc(fmtTime(n.reminder_time)) : ""}</span>` : DASH;
  }

  /* ---------------- reminders -> buckets ---------------- */

  function isMissed(r) {
    if (r.status === "completed" || r.status === "silent") return false;
    if (r.status === "missed") return true;
    if (!r.reminder_date) return false;
    return new Date(`${r.reminder_date}T${r.reminder_time || "23:59:59"}`).getTime() < Date.now();
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
      const hay = [l.customer_name, l.email, l.phone, l.whatsapp, l.company_name, l.product_name].join(" ").toLowerCase();
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
      return `<button type="button" class="it ${it.danger ? "danger" : ""} ${it.active ? "active" : ""}" data-i="${i}">${lead}<span>${esc(it.label)}</span>${it.cnt != null ? `<span class="cnt">${it.cnt}</span>` : ""}${it.checked ? '<i class="bi bi-check2 ck"></i>' : ""}</button>`;
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
    leads = data.leads;
    me = data.current_admin;
    if (detail) { const f = leads.find(l => l.id === detail.id); if (f) detail = f; }
    renderAll();
  }
  async function refreshSilently() { try { await loadLeads(); if (detail) renderDrawer(); } catch (e) { /* ignore */ } }
  function putLead(l) {
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
      <th class="c-end"><button type="button" class="lh-plus" id="lhAddCol" title="Columns chuno" aria-label="Choose columns"><i class="bi bi-plus-lg"></i></button></th>
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
        <h5>${leads.length ? "Koi lead match nahi hui" : "Abhi koi lead nahi hai"}</h5>
        <p>${leads.length ? "Filters ya search badal ke dekho." : "Pehle ek contact banao, phir uske naam se lead create karo."}</p>
        ${leads.length ? '<button class="btn btn-outline-secondary btn-sm" data-act="clear">Filters clear karo</button>' : '<button class="btn btn-primary btn-sm" data-act="create"><i class="bi bi-plus-lg me-1"></i>Create lead</button>'}
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
      <span class="lh-name-col"><span class="lh-name-text">${esc(l.customer_name)}</span><span class="lh-name-sub">${esc([l.phone, typeLabel(l.lead_type)].filter(Boolean).join(" · "))}</span></span>
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
        ? items.slice(0, lim).map(boardCard).join("") + (items.length > lim ? `<button type="button" class="lh-more" data-act="more" data-type="${t}">Aur ${items.length - lim} dikhao</button>` : "")
        : `<div class="lh-colempty">Koi lead nahi<br>Card yahan drag karo</div>`;
      return `<div class="lh-col ${stCls(t)}" data-type="${t}">
        <div class="lh-col-head"><span class="dot"></span><b>${esc(typeLabel(t))}</b><span class="n">${items.length}</span>
          <button type="button" class="lh-iconbtn add" data-act="addIn" data-type="${t}" title="${esc(typeLabel(t))} lead banao" aria-label="Add lead"><i class="bi bi-plus-lg"></i></button></div>
        <div class="lh-col-body">${body}</div>
      </div>`;
    }).join("");
    board.scrollLeft = scrollLeft;
  }
  function boardCard(l) {
    const b = reminderBuckets(l);
    const rem = b.missed.length || nextReminder(l) ? reminderChip(l) : "";
    return `<div class="lh-card-k" draggable="true" data-id="${l.id}">
      <div class="nm">${l.is_pinned ? '<i class="bi bi-pin-angle-fill pinmark"></i>' : ""}${avatarHtml(l.customer_name, "sm")}<span>${esc(l.customer_name)}</span></div>
      <button type="button" class="lh-rowbtn menu" data-act="cardmenu" data-id="${l.id}" aria-label="More"><i class="bi bi-three-dots"></i></button>
      ${l.company_name ? `<div class="ln"><i class="bi bi-building"></i><span>${esc(l.company_name)}</span></div>` : ""}
      ${l.phone ? `<div class="ln"><i class="bi bi-telephone"></i><span>${esc(l.phone)}</span></div>` : ""}
      ${l.email ? `<div class="ln"><i class="bi bi-envelope"></i><span>${esc(l.email)}</span></div>` : ""}
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
      if (!res.success) throw new Error(firstErr(res, "Status update nahi hua."));
      toast("Status: " + typeLabel(type));
    } catch (e) {
      l.lead_type = old;
      if (detail && detail.id === id) { detail.lead_type = old; renderDrawer(); }
      renderAll();
      toast(e.message || "Status update nahi hua.", "danger");
    }
  }
  function statusMenu(anchor, current, onPick) {
    popMenu(anchor, TYPES.map(t => ({ label: typeLabel(t), dot: t, checked: t === current, fn: () => onPick(t) })));
  }

  /* ---------------- delete ---------------- */

  function askDelete(ids) {
    pendingDel = ids.slice();
    $("lhDeleteTitle").textContent = ids.length > 1 ? `${ids.length} leads delete karein?` : "Lead delete karein?";
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
      } else toast(firstErr(res, "Delete nahi hua."), "danger");
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
      popMenu(e.currentTarget, items.concat([{ sep: true }, { label: "Sab clear karo", icon: "x-circle", fn: () => { F.statuses.clear(); renderAll(); } }]));
    });
    $("lhChipSource").addEventListener("click", e => {
      popMenu(e.currentTarget, [{ label: "Any source", checked: !F.source, fn: () => { F.source = ""; page = 1; renderAll(); } }]
        .concat(SOURCES.map(s => ({ label: s, checked: F.source === s, cnt: leads.filter(l => l.source === s).length, fn: () => { F.source = s; page = 1; renderAll(); } }))));
    });
    $("lhChipDate").addEventListener("click", e => {
      const el = openPop(e.currentTarget, `<div class="hd">Create date</div>
        <div class="dates"><input type="date" class="form-control form-control-sm" id="lhDFrom" value="${esc(F.from)}" aria-label="From"><span>–</span><input type="date" class="form-control form-control-sm" id="lhDTo" value="${esc(F.to)}" aria-label="To"></div>
        <div class="dates" style="padding-top:0"><button type="button" class="lh-ghost" data-r="7">7 din</button><button type="button" class="lh-ghost" data-r="30">30 din</button><button type="button" class="lh-link" data-r="0">Clear</button></div>`, { width: 300 });
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
        else toast(firstErr(res, "Status update nahi hua."), "danger");
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
      { label: "Open", icon: "box-arrow-up-right", fn: () => openDetail(id) },
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
        `<button type="button" class="it" data-k="${k}"><i class="bi bi-${COLDEFS[k].icon}"></i><span>${esc(COLDEFS[k].label)}</span>${cols.indexOf(k) >= 0 ? '<i class="bi bi-check2 ck"></i>' : ""}</button>`).join("") || '<div class="hd">Kuch nahi mila</div>';
    };
    const el = openPop(anchor, `<div class="hd">Is table me kya dikhana hai?</div><div class="srch"><input type="text" class="form-control form-control-sm" id="lhPopColQ" placeholder="Property search..."></div><div id="lhPopCols">${draw("")}</div><hr><button type="button" class="it" data-edit><i class="bi bi-layout-three-columns"></i><span>Columns edit / reorder…</span></button><button type="button" class="it" data-reset><i class="bi bi-arrow-counterclockwise"></i><span>Default par wapas</span></button>`, { width: 290, align: "right" });
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
      || '<div class="text-muted" style="font-size:13px;padding:10px">Sab properties already selected hain.</div>';
    $("lhColsN").textContent = cols.length;
    $("lhColsSel").innerHTML = cols.map((k, i) => k === "name"
      ? `<div class="lh-colrow sel"><i class="bi bi-lock-fill lock"></i><span>${esc(COLDEFS[k].label)}</span><span class="sp"></span><span class="text-muted" style="font-size:11.5px">Hamesha pehle</span></div>`
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
    if (!res.success) { toast(firstErr(res, "Lead nahi mili."), "danger"); return; }
    detail = putLead(res.lead);
    if (!opts.refresh) {
      drawerPane = opts.pane || "about";
      resetComposer();
      closePop();
      bootstrap.Offcanvas.getOrCreateInstance($("lhDrawer")).show();
    }
    renderDrawer();
    renderAll();
  }
  window.openLeadDetailById = id => openDetail(parseInt(id, 10));

  function setPane(p) {
    drawerPane = p;
    document.querySelectorAll("#lhDrTabs .lh-dr-tab").forEach(t => t.classList.toggle("active", t.dataset.pane === p));
    ["about", "activity", "files"].forEach(k => $("lhPane" + k.charAt(0).toUpperCase() + k.slice(1)).classList.toggle("d-none", k !== p));
    $("lhDrBody").scrollTop = 0;
  }

  function renderDrawer() {
    const l = detail;
    if (!l) return;
    $("remLeadId").value = l.id;
    const idx = orderIds.indexOf(l.id);
    const wa = waUrl(l.whatsapp || l.phone);
    $("lhDrTop").innerHTML = `
      <div class="lh-dr-bar"><span>Lead</span><span class="sp"></span>
        <button type="button" class="lh-iconbtn" data-dr="prev" ${idx <= 0 ? "disabled" : ""} title="Pichli lead" aria-label="Previous lead"><i class="bi bi-chevron-up"></i></button>
        <button type="button" class="lh-iconbtn" data-dr="next" ${idx < 0 || idx >= orderIds.length - 1 ? "disabled" : ""} title="Agli lead" aria-label="Next lead"><i class="bi bi-chevron-down"></i></button>
        <button type="button" class="lh-iconbtn" data-dr="share" title="Share" aria-label="Share"><i class="bi bi-share"></i></button>
        <button type="button" class="lh-iconbtn ${l.is_pinned ? "pinned" : ""}" data-dr="pin" title="${l.is_pinned ? "Unpin" : "Pin"}" aria-label="Pin"><i class="bi bi-pin-angle-fill"></i></button>
        <button type="button" class="lh-iconbtn danger" data-dr="delete" title="Delete" aria-label="Delete"><i class="bi bi-trash"></i></button>
        <button type="button" class="lh-iconbtn" data-bs-dismiss="offcanvas" aria-label="Close"><i class="bi bi-x-lg"></i></button>
      </div>
      <div class="lh-dr-id">
        ${avatarHtml(l.customer_name, "lg")}
        <div style="min-width:0">
          <h2 class="lh-dr-name">${esc(l.customer_name)}</h2>
          <div class="lh-dr-sub">${l.company_name ? `<span><i class="bi bi-building me-1"></i>${esc(l.company_name)}</span>` : ""}${pill(l.lead_type, true, l.id)}</div>
        </div>
      </div>
      <div class="lh-dr-quick">
        <button type="button" class="lh-q" data-dr="note"><span class="ic"><i class="bi bi-sticky"></i></span>Note</button>
        <a class="lh-q ${l.email ? "" : "off"}" href="${l.email ? "mailto:" + esc(l.email) : "#"}"><span class="ic"><i class="bi bi-envelope"></i></span>Email</a>
        <a class="lh-q ${l.phone ? "" : "off"}" href="${l.phone ? "tel:" + esc(l.phone) : "#"}"><span class="ic"><i class="bi bi-telephone"></i></span>Call</a>
        <a class="lh-q wa ${wa ? "" : "off"}" href="${wa ? esc(wa) : "#"}"><span class="ic"><i class="bi bi-whatsapp"></i></span>WhatsApp</a>
        <button type="button" class="lh-q" data-dr="rem"><span class="ic"><i class="bi bi-alarm"></i></span>Reminder</button>
      </div>`;
    setPane(drawerPane);
    renderAbout();
    renderTimeline();
    renderFiles();
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
      ${prop("Created by", "", l.creator ? esc(l.creator.name) : "", "", "", true)}
      ${prop("Create date", "", l.created_at ? esc(fmtDate(l.created_at)) + ` <span class="text-muted">(${esc(ago(l.created_at))})</span>` : "", "", "", true)}
    </div>`;

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
          <div><div class="nm">${l.company_name ? esc(l.company_name) : "Koi company linked nahi"}</div><small>${l.company_name ? "Sirf naam saved hai — company record se link kar do" : "Company jodo to GST aur address yahin edit ho jayenge"}</small></div>
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
        if (!res.success) { toast(firstErr(res, "Save nahi hua."), "danger"); return false; }
        Object.assign(l.company, res.company || { [field]: value });
        if (field === "name") l.company_name = l.company.name;
        if (field === "gst_number") l.gst_number = l.company.gst_number;
        refreshSilently();
      } else {
        res = await reqJson(url("patch", l.id), "PATCH", { [field]: value });
        if (!res.success) { toast(firstErr(res, "Save nahi hua."), "danger"); return false; }
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
        `<label class="it"><input type="checkbox" class="form-check-input m-0" value="${a.id}" ${set.has(a.id) ? "checked" : ""}>${avatarHtml(a.name, "sm")}<span>${esc(a.name)}</span></label>`).join("") || '<div class="hd">Koi nahi mila</div>';
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
    const el = openPop(anchor, `<div class="srch"><input type="text" class="form-control form-control-sm" placeholder="Company ka naam / GST likho..."></div><div class="clist"></div>`, { width: 330 });
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
        || '<div class="hd">Type karo — company search ya create</div>';
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
        else if (res.duplicate && res.company) { closePop(); toast("Ye company pehle se hai — wahi use ki.", "dark"); onPick(res.company); }
        else toast(firstErr(res, "Company nahi bani."), "danger");
      }
    });
  }
  async function setLeadCompany(companyId) {
    const res = await reqJson(url("patch", detail.id), "PATCH", { company_id: companyId });
    if (!res.success) { toast(firstErr(res, "Company update nahi hui."), "danger"); return; }
    Object.assign(detail, res.lead);
    renderAll(); renderDrawer();
    toast(companyId ? "Company linked" : "Company hata di", "dark");
  }

  /* ---- timeline (reminders + notes) ---- */

  function renderTimeline() {
    const all = (detail.reminders || []).slice().sort((a, b) => b.id - a.id);
    $("lhNActivity").textContent = all.length;
    const box = $("lhTimeline");
    if (!all.length) {
      box.innerHTML = `<div class="lh-emptybox" style="padding:36px 20px"><div class="ic" style="width:52px;height:52px;font-size:22px"><i class="bi bi-clock-history"></i></div><h6>Abhi koi activity nahi</h6><p class="mb-0" style="font-size:13px">Upar se reminder ya note add karo.</p></div>`;
      return;
    }
    box.innerHTML = all.map(r => {
      const isNote = (r.type || "reminder") === "note";
      const meta = `${r.reminder_date ? `<span><i class="bi bi-calendar3 me-1"></i>${esc(fmtDate(r.reminder_date))}</span>` : ""}${r.reminder_time ? `<span>${esc(fmtTime(r.reminder_time))}</span>` : ""}`;
      if (isNote) {
        return `<div class="lh-act-item"><div class="lh-act-ic note"><i class="bi bi-sticky"></i></div>
          <div style="min-width:0;flex:1"><div class="lh-act-t">${esc(r.note)}</div><div class="lh-act-m"><span>Note</span>${meta}</div></div>
          <div class="lh-act-btns"><button type="button" class="del" data-r="del" data-id="${r.id}" title="Delete" aria-label="Delete note"><i class="bi bi-x-lg"></i></button></div></div>`;
      }
      const missed = isMissed(r), silent = r.status === "silent", done = r.status === "completed";
      const label = done ? "Completed" : silent ? "Silenced" : missed ? "Missed" : "Scheduled";
      const ic = done ? ["done", "check2-circle"] : silent ? ["mute", "bell-slash"] : missed ? ["late", "exclamation-circle"] : ["", "alarm"];
      return `<div class="lh-act-item ${done ? "completed" : ""}"><div class="lh-act-ic ${ic[0]}"><i class="bi bi-${ic[1]}"></i></div>
        <div style="min-width:0;flex:1"><div class="lh-act-t">${esc(r.note)}</div>
          <div class="lh-act-m">${meta}${r.appointment_type ? `<span><i class="bi bi-${r.appointment_type === "whatsapp" ? "whatsapp" : "camera-video"} me-1"></i>${r.appointment_type === "whatsapp" ? "WhatsApp" : "Meeting"}</span>` : ""}<span style="font-weight:700;color:${missed ? "var(--danger)" : "inherit"}">${label}</span></div></div>
        <div class="lh-act-btns">
          ${done ? "" : `<button type="button" data-r="complete" data-id="${r.id}" title="Mark done" aria-label="Mark done"><i class="bi bi-check2"></i></button>`}
          ${(r.status === "scheduled" || r.status === "missed") ? `<button type="button" data-r="silence" data-id="${r.id}" title="Silence" aria-label="Silence"><i class="bi bi-volume-mute"></i></button>` : ""}
          <button type="button" class="del" data-r="del" data-id="${r.id}" title="Delete" aria-label="Delete"><i class="bi bi-x-lg"></i></button></div></div>`;
    }).join("");
  }
  async function reminderAction(act, id) {
    if (act === "del") await apiFetch(url("reminderDestroy", id), { method: "DELETE" });
    else {
      await reqJson(url("reminderStatus", id), "POST", { status: act === "complete" ? "completed" : "silent" });
      if (act === "silence") toast("Reminder silence ho gaya", "dark");
    }
    await openDetail(detail.id, { refresh: true });
    if (window.GlobalReminders) window.GlobalReminders.refresh();
  }

  function resetComposer() {
    const now = new Date();
    $("remNote").value = "";
    // default follow-up = an hour from now (rounded to 5 min) so a fresh reminder isn't instantly "missed"
    const at = new Date(Math.ceil((now.getTime() + 3600000) / 300000) * 300000);
    $("remDate").value = ymd(at);
    $("remTime").value = String(at.getHours()).padStart(2, "0") + ":" + String(at.getMinutes()).padStart(2, "0");
    document.querySelectorAll('input[name="remAptType"]').forEach(r => { r.checked = false; });
    $("remKindReminder").checked = true;
    updateRemKindUI();
  }
  function updateRemKindUI() {
    const isNote = $("remKindNote").checked;
    $("remAppointmentFields").style.display = isNote ? "none" : "";
    $("remSubmitBtn").textContent = isNote ? "Add note" : "Add reminder";
  }
  function bindComposer() {
    document.querySelectorAll('input[name="remKind"]').forEach(r => r.addEventListener("change", updateRemKindUI));
    $("reminderForm").addEventListener("submit", async e => {
      e.preventDefault();
      const leadId = $("remLeadId").value;
      const kind = document.querySelector('input[name="remKind"]:checked').value;
      const apt = document.querySelector('input[name="remAptType"]:checked');
      const btn = $("remSubmitBtn");
      btn.disabled = true;
      try {
        const res = await reqJson(CFG.routes.reminderStore, "POST", {
          lead_id: leadId, type: kind, note: $("remNote").value.trim(),
          reminder_date: $("remDate").value || null, reminder_time: $("remTime").value || null,
          appointment_type: kind === "note" ? null : (apt ? apt.value : null),
        });
        if (!res.success) { toast(firstErr(res, kind === "note" ? "Note add nahi hua." : "Reminder add nahi hua."), "danger"); return; }
        toast(kind === "note" ? "Note added" : "Reminder added");
        $("remNote").value = "";
        await openDetail(parseInt(leadId, 10), { refresh: true });
        if (kind === "reminder" && window.GlobalReminders) window.GlobalReminders.refresh();
      } finally { btn.disabled = false; }
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
      </div>`).join("") : `<div class="lh-emptybox" style="padding:30px 20px"><p class="mb-0" style="font-size:13px">Abhi koi file attach nahi hui.</p></div>`;
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
      if (xhr.status === 401 || xhr.status === 419) { finish("Session expire ho gaya, dobara login karo.", "danger"); return; }
      let res; try { res = JSON.parse(xhr.responseText); } catch (e) { res = { success: false }; }
      if (!res.success) { finish(firstErr(res, "File upload nahi hui."), "danger"); return; }
      finish("File upload ho gayi");
    };
    xhr.onerror = () => finish("Upload fail — internet check karo.", "danger");
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
      if (!b || !confirm("Ye file delete karni hai?")) return;
      const res = await apiFetch(url("attachmentDestroy", b.dataset.delAttachment), { method: "DELETE" });
      if (!res.success) { toast("File delete nahi hui.", "danger"); return; }
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
    if (typeof html2canvas !== "function") { toast("Card image nahi ban payi.", "danger"); return; }
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
      try { await navigator.clipboard.write([new ClipboardItem({ "image/png": blob })]); toast("Card image copy ho gayi"); }
      catch (e) {
        const a = document.createElement("a"); a.href = URL.createObjectURL(blob); a.download = "lead-card.png"; a.click();
        toast("Share supported nahi — image download ho gayi", "dark");
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
      if (r) { reminderAction(r.dataset.r, parseInt(r.dataset.id, 10)); return; }
      const b = e.target.closest("[data-dr]");
      if (b && detail) {
        const a = b.dataset.dr, idx = orderIds.indexOf(detail.id);
        if (a === "prev" && idx > 0) openDetail(orderIds[idx - 1], { refresh: true });
        else if (a === "next" && idx >= 0 && idx < orderIds.length - 1) openDetail(orderIds[idx + 1], { refresh: true });
        else if (a === "pin") togglePin(detail.id);
        else if (a === "delete") askDelete([detail.id]);
        else if (a === "share") afterDrawerClosed(() => { buildShareCard(detail); bootstrap.Modal.getOrCreateInstance($("shareCardModal")).show(); });
        else if (a === "note" || a === "rem") {
          setPane("activity");
          (a === "note" ? $("remKindNote") : $("remKindReminder")).checked = true;
          updateRemKindUI();
          $("remNote").focus();
        }
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
    dr.addEventListener("hidden.bs.offcanvas", () => { closePop(); });
  }

  /* ==================== CREATE LEAD: Contact -> Company -> Lead ==================== */

  let CR = null;
  let crToken = 0;

  function newCR(opts) {
    return {
      step: 1, busy: false,
      contact: null, mode: "search", q: "", results: null, form: { name: "", email: "", phone: "", whatsapp: "", job_title: "" }, dupe: null,
      company: null, cmode: "search", cq: "", cresults: null, cform: { name: "", website: "", phone: "", gst_number: "", address: "" }, cdupe: null,
      status: (opts && opts.status) || (TYPES.indexOf("cold") >= 0 ? "cold" : TYPES[0]),
      source: lsGet("lh.lastSource", SOURCES[0]) || SOURCES[0], platform: "", product: "", assigned: new Set(),
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
    if (CR.step === 3) drawAssignChips();
  }

  const crAlert = (msg, btn) => `<div class="lh-alert"><i class="bi bi-exclamation-triangle-fill"></i><span>${esc(msg)}</span>${btn || ""}</div>`;

  function crStep1() {
    const head = `<div class="lh-pane"><h6>Ye lead kiska hai?</h6><div class="hint">Pehle contact chuno ya naya banao. Phone / email se duplicate apne aap pakda jata hai.</div>`;
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
          <div class="col-md-6"><label class="form-label" for="crWa">WhatsApp</label><input class="form-control" id="crWa" data-f="whatsapp" inputmode="tel" value="${esc(f.whatsapp)}" placeholder="Same as phone ho to khaali chhodo" autocomplete="off"></div>
          <div class="col-md-6"><label class="form-label" for="crJob">Job title</label><input class="form-control" id="crJob" data-f="job_title" value="${esc(f.job_title)}" placeholder="e.g. Purchase Manager" autocomplete="off"></div>
        </div>
        ${CR.dupe ? crAlert(CR.dupe.message, CR.dupe.contact ? `<button type="button" class="btn btn-sm btn-warning" data-cr="useDupe">Use ${esc(CR.dupe.contact.name)}</button>` : "") : ""}
        <button type="button" class="lh-link mt-3 p-0" data-cr="backSearch"><i class="bi bi-arrow-left me-1"></i>Existing contact search karo</button>
      </div></div>`;
    }
    return head + `<div class="lh-search" style="flex:none;max-width:none"><i class="bi bi-search"></i><input type="text" class="form-control" id="crQ" placeholder="Naam, phone ya email se search..." autocomplete="off"></div>
      <div class="lh-res" id="crRes"></div>
      <button type="button" class="btn btn-outline-primary mt-3" data-cr="newContact"><i class="bi bi-person-plus me-1"></i>Naya contact banao</button></div>`;
  }
  function drawContactResults() {
    const box = $("crRes");
    if (!box) return;
    if (CR.results === null) { box.innerHTML = '<div class="p-3 text-muted" style="font-size:13px">Loading…</div>'; return; }
    if (!CR.results.length) { box.innerHTML = `<div class="p-3 text-muted" style="font-size:13px">${CR.q ? "Is naam / number ka koi contact nahi mila — naya bana lo." : "Abhi koi contact nahi hai — pehla contact banao."}</div>`; return; }
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
    const head = `<div class="lh-pane"><h6>Kaun si company?</h6><div class="hint">Optional hai — individual customer ho to Skip kar do.</div>`;
    if (CR.company) {
      return head + `<div class="lh-sel"><div class="lh-co-ic" style="background:#fff"><i class="bi bi-building"></i></div><div style="min-width:0"><div class="t">${esc(CR.company.name)}</div><div class="s">${CR.contact && CR.contact.company && CR.contact.company.id === CR.company.id ? "Contact ki company" : "Selected"}</div></div><button type="button" class="btn btn-sm btn-outline-secondary x" data-cr="changeCompany">Change</button></div></div>`;
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
        <button type="button" class="lh-link mt-3 p-0" data-cr="backCSearch"><i class="bi bi-arrow-left me-1"></i>Existing company search karo</button></div></div>`;
    }
    return head + `<div class="lh-search" style="flex:none;max-width:none"><i class="bi bi-search"></i><input type="text" class="form-control" id="crCq" placeholder="Company ka naam ya GST se search..." autocomplete="off"></div>
      <div class="lh-res" id="crCres"></div>
      <button type="button" class="btn btn-outline-primary mt-3" data-cr="newCompany"><i class="bi bi-building-add me-1"></i>Nayi company banao</button></div>`;
  }
  function drawCompanyResults() {
    const box = $("crCres");
    if (!box) return;
    if (CR.cresults === null) { box.innerHTML = '<div class="p-3 text-muted" style="font-size:13px">Loading…</div>'; return; }
    if (!CR.cresults.length) { box.innerHTML = `<div class="p-3 text-muted" style="font-size:13px">${CR.cq ? "Koi company nahi mili — nayi bana lo." : "Abhi koi company nahi hai."}</div>`; return; }
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
    return `<div class="lh-pane"><h6>Lead ki details</h6><div class="hint">Bas status aur source batao — baaki baad me drawer se edit ho jayega.</div>
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
        <div class="col-md-6"><label class="form-label fw-semibold" style="font-size:13px" for="crProduct">Product / interest</label>
          <input class="form-control" id="crProduct" value="${esc(CR.product)}" placeholder="e.g. CRM subscription" autocomplete="off"></div>
        <div class="col-md-6"><label class="form-label fw-semibold" style="font-size:13px">Assign to</label>
          <button type="button" class="lh-assignbtn" id="crAssign" aria-haspopup="true"></button></div>
      </div></div>`;
  }
  function drawAssignChips() {
    const b = $("crAssign");
    if (!b) return;
    const names = Array.from(CR.assigned).map(id => (CFG.admins.find(a => a.id === id) || {}).name).filter(Boolean);
    b.innerHTML = names.length ? names.map(n => `<span class="chip">${esc(n)}</span>`).join("") : '<span class="ph">Team member chuno...</span>';
  }

  async function crNext() {
    if (!CR || CR.busy) return;
    if (CR.step === 1) {
      if (CR.contact) { CR.step = 2; renderCreate(); return; }
      if (CR.mode !== "new") { toast("Pehle contact chuno ya naya banao.", "danger"); return; }
      const f = CR.form;
      if (!f.name.trim()) { toast("Name zaroori hai.", "danger"); $("crName").focus(); return; }
      if (!f.phone.trim() && !f.email.trim()) { toast("Phone ya email me se ek dena zaroori hai.", "danger"); $("crPhone").focus(); return; }
      CR.busy = true; renderCreate();
      let res;
      try { res = await reqJson(CFG.routes.contactStore, "POST", f); } catch (e) { CR.busy = false; renderCreate(); return; }
      CR.busy = false;
      if (res.success) { CR.dupe = null; pickContact(res.contact); CR.step = 2; renderCreate(); }
      else if (res.duplicate) { CR.dupe = res; renderCreate(); }
      else { renderCreate(); toast(firstErr(res, "Contact nahi bana."), "danger"); }
    } else if (CR.step === 2) {
      if (!CR.company && CR.cmode === "new") {
        const f = CR.cform;
        if (!f.name.trim()) { toast("Company name zaroori hai (ya Skip karo).", "danger"); $("crCoName").focus(); return; }
        CR.busy = true; renderCreate();
        let res;
        try { res = await reqJson(CFG.routes.companyStore, "POST", f); } catch (e) { CR.busy = false; renderCreate(); return; }
        CR.busy = false;
        if (res.success) { CR.company = res.company; CR.cdupe = null; CR.step = 3; renderCreate(); }
        else if (res.duplicate) { CR.cdupe = res; renderCreate(); }
        else { renderCreate(); toast(firstErr(res, "Company nahi bani."), "danger"); }
        return;
      }
      CR.step = 3; renderCreate();
    } else {
      if (!CR.source) { toast("Source chuno.", "danger"); return; }
      CR.busy = true; renderCreate();
      let res;
      try {
        res = await reqJson(CFG.routes.store, "POST", {
          contact_id: CR.contact.id, company_id: CR.company ? CR.company.id : null,
          product_name: CR.product.trim(), source: CR.source, social_platform: CR.source === "Social Media" ? (CR.platform || null) : null,
          lead_type: CR.status, assigned_admins: Array.from(CR.assigned),
        });
      } catch (e) { CR.busy = false; renderCreate(); return; }
      CR.busy = false;
      if (!res.success) { renderCreate(); toast(firstErr(res, "Lead nahi bani."), "danger"); return; }
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
    });
    const debouncedContactSearch = debounce(searchContacts, 220);
    const debouncedCompanySearch = debounce(searchCompanies, 220);
    body.addEventListener("change", e => {
      if (e.target.id === "crSource") { CR.source = e.target.value; renderCreate(); }
      else if (e.target.id === "crPlatform") CR.platform = e.target.value;
    });
    body.addEventListener("keydown", e => {
      if (e.key !== "Enter" || e.target.tagName === "BUTTON") return;
      e.preventDefault();
      if (e.target.id === "crQ") { if (CR.results && CR.results[0]) pickContact(CR.results[0]); else if (CR.results && !CR.results.length) body.querySelector('[data-cr="newContact"]').click(); }
      else if (e.target.id === "crCq") { if (CR.cresults && CR.cresults[0]) { CR.company = CR.cresults[0]; renderCreate(); } }
      else crNext();
    });
    body.addEventListener("click", e => {
      const ci = e.target.closest("[data-ci]");
      if (ci) { pickContact(CR.results[+ci.dataset.ci]); return; }
      const cj = e.target.closest("[data-cj]");
      if (cj) { CR.company = CR.cresults[+cj.dataset.cj]; renderCreate(); return; }
      const st = e.target.closest("[data-st]");
      if (st) { CR.status = st.dataset.st; renderCreate(); return; }
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
    bindComposer();
    bindFiles();
    bindShare();
    bindCreate();
    renderAll();                       // skeleton while loading
    loadLeads().then(openFromQuery).catch(() => { loading = false; renderAll(); });
  });
})();
</script>

<style>
  .lh-iconbtn:disabled { opacity: .35; pointer-events: none; }
  .lh-pop .it small { font-size: 11.5px; }
  .lh-pop .alist, .lh-pop .clist { max-height: 260px; overflow-y: auto; }
  .lh-pop .it > span:not(.cnt):not(.sdot) { min-width: 0; overflow: hidden; text-overflow: ellipsis; }
  .lh-newform .form-control, .lh-newform .form-select { background: #fff; }
</style>

@endsection