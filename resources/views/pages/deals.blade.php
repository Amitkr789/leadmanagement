@extends('layouts.app')
@section('title', 'Deals | Dalal Adda')

@section('content')

<style>
  /* ==========================================================================
     Deals — HubSpot-style workspace (.dl-*). Same tokens + same create flow as the Leads page
     ========================================================================== */
  .page-content:has(.dl-wrap) { padding-bottom: 28px; }

  /* stage + priority colours (--c = text/dot, --bg = soft background) */
  .sg-new         { --c: var(--info);    --bg: var(--info-bg); }
  .sg-qualified   { --c: var(--primary); --bg: var(--primary-50); }
  .sg-proposal    { --c: var(--warning); --bg: var(--warning-bg); }
  .sg-negotiation { --c: var(--gold);    --bg: var(--gold-bg); }
  .sg-won         { --c: var(--success); --bg: var(--success-bg); }
  .sg-lost        { --c: var(--danger);  --bg: var(--danger-bg); }
  .pr-high   { --c: var(--danger);  --bg: var(--danger-bg); }
  .pr-medium { --c: var(--warning); --bg: var(--warning-bg); }
  .pr-low    { --c: #8A8FA6;        --bg: #EEF1F6; }

  .dl-pill { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; line-height: 1; padding: 6px 11px; border-radius: 999px; background: var(--bg); color: var(--c); white-space: nowrap; }
  .dl-pill .dot { width: 7px; height: 7px; border-radius: 50%; background: var(--c); }
  button.dl-pill { border: 0; cursor: pointer; transition: filter .12s, box-shadow .12s; }
  button.dl-pill:hover { filter: brightness(.96); box-shadow: 0 0 0 2px rgba(var(--primary-rgb), .12); }
  button.dl-pill .bi { font-size: 10px; opacity: .7; }
  .dl-prio { display: inline-flex; align-items: center; gap: 6px; font-size: 12.5px; font-weight: 600; color: var(--c); }
  .dl-prio .bi { font-size: 11px; }
  .dl-empty { color: #B3B7C7; }

  /* ---- header ---- */
  .dl-head { display: flex; align-items: flex-end; gap: 14px; flex-wrap: wrap; margin-bottom: 14px; }
  .dl-head h1 { font-size: 24px; margin: 0; display: flex; align-items: center; gap: 10px; }
  .dl-head h1 .cnt { font-size: 12.5px; font-weight: 700; color: var(--primary); background: var(--primary-50); border-radius: 999px; padding: 4px 11px; letter-spacing: 0; }
  .dl-head p { margin: 4px 0 0; color: var(--text-muted); font-size: 13.5px; }
  .dl-head .grow { flex: 1; min-width: 200px; }
  .dl-seg { display: inline-flex; background: var(--card-bg); border: 1px solid var(--border); border-radius: 11px; padding: 3px; gap: 2px; }
  .dl-seg button { border: 0; background: transparent; padding: 7px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; color: var(--text-muted); display: inline-flex; align-items: center; gap: 7px; }
  .dl-seg button:hover { color: var(--text); }
  .dl-seg button.active { background: var(--primary); color: #fff; box-shadow: 0 2px 6px rgba(var(--primary-rgb), .3); }
  .dl-create { display: inline-flex; align-items: center; gap: 8px; border: 0; border-radius: 11px; padding: 10px 18px; font-weight: 700; font-size: 14px; color: #fff; background: linear-gradient(135deg, var(--primary), var(--primary-light)); box-shadow: 0 6px 16px rgba(var(--primary-rgb), .26); transition: transform .12s, box-shadow .12s; }
  .dl-create:hover { transform: translateY(-1px); box-shadow: 0 10px 22px rgba(var(--primary-rgb), .32); }

  /* ---- forecast strip ---- */
  .dl-kpis { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 14px; }
  .dl-kpi { flex: 1 1 190px; background: var(--card-bg); border: 1px solid var(--border); border-radius: 12px; padding: 11px 16px; border-left: 3px solid var(--c, var(--primary)); }
  .dl-kpi small { display: block; color: var(--text-muted); font-size: 12px; font-weight: 600; }
  .dl-kpi b { font-size: 20px; font-weight: 800; letter-spacing: -.01em; }
  .dl-kpi span { font-size: 12px; color: var(--text-muted); margin-left: 6px; }

  /* ---- tabs ---- */
  .dl-tabs { display: flex; gap: 4px; overflow-x: auto; border-bottom: 1px solid var(--border); margin-bottom: 14px; scrollbar-width: none; }
  .dl-tabs::-webkit-scrollbar { display: none; }
  .dl-tab { border: 0; background: transparent; padding: 10px 14px; font-size: 13.5px; font-weight: 600; color: var(--text-muted); white-space: nowrap; border-bottom: 2px solid transparent; margin-bottom: -1px; display: inline-flex; align-items: center; gap: 8px; }
  .dl-tab:hover { color: var(--text); }
  .dl-tab.active { color: var(--primary); border-bottom-color: var(--primary); }
  .dl-tab .n { font-size: 11px; font-weight: 700; background: #EEF0F7; color: var(--text-muted); border-radius: 999px; padding: 2px 8px; }
  .dl-tab.active .n { background: var(--primary-50); color: var(--primary); }

  /* ---- toolbar ---- */
  .dl-toolbar { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 12px; }
  .dl-search { position: relative; flex: 0 1 300px; min-width: 200px; }
  .dl-search i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); }
  .dl-search input.form-control { padding-left: 38px !important; padding-right: 30px !important; }
  .dl-search kbd { position: absolute; right: 9px; top: 50%; transform: translateY(-50%); font-size: 11px; background: var(--bg); color: var(--text-muted); border: 1px solid var(--border); border-radius: 5px; padding: 0 6px; }
  .dl-chip { display: inline-flex; align-items: center; gap: 6px; border: 1px solid var(--border); background: var(--card-bg); border-radius: 999px; padding: 7px 13px; font-size: 13px; font-weight: 600; color: var(--text); white-space: nowrap; transition: background .12s, border-color .12s; }
  .dl-chip:hover { background: var(--primary-50); border-color: var(--primary-100); }
  .dl-chip .bi-chevron-down { font-size: 10px; color: var(--text-muted); }
  .dl-chip.on { background: var(--primary-50); border-color: var(--primary-100); color: var(--primary); }
  .dl-chip .v { font-weight: 700; max-width: 150px; overflow: hidden; text-overflow: ellipsis; }
  .dl-link { border: 0; background: transparent; color: var(--primary); font-weight: 600; font-size: 13px; padding: 6px 8px; }
  .dl-link:hover { text-decoration: underline; }
  .dl-spacer { flex: 1; }
  .dl-ghost { display: inline-flex; align-items: center; gap: 7px; border: 1px solid var(--border); background: var(--card-bg); border-radius: 10px; padding: 7px 13px; font-size: 13px; font-weight: 600; color: var(--text); }
  .dl-ghost:hover { background: var(--primary-50); border-color: var(--primary-100); color: var(--primary); }

  .dl-bulk { display: none; align-items: center; gap: 10px; flex-wrap: wrap; background: var(--primary-50); border: 1px solid var(--primary-100); border-radius: 12px; padding: 8px 14px; margin-bottom: 12px; font-size: 13px; }
  .dl-bulk.show { display: flex; }
  .dl-bulk b { color: var(--primary); }

  /* ---- table ---- */
  .dl-card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 14px; box-shadow: var(--shadow-sm); overflow: hidden; }
  .dl-scroll { overflow: auto; min-height: 280px; }
  .dl-table { border-collapse: separate; border-spacing: 0; min-width: 100%; width: max-content; }
  .dl-table th { position: sticky; top: 0; z-index: 3; background: #F7F8FC; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); padding: 0 16px; height: 44px; border-bottom: 1px solid var(--border); white-space: nowrap; user-select: none; text-align: left; cursor: pointer; }
  .dl-table th:hover { color: var(--text); }
  .dl-table th .srt { margin-left: 5px; font-size: 10px; color: var(--primary); }
  .dl-table td { height: 56px; padding: 0 16px; border-bottom: 1px solid #F0F1F6; font-size: 13.5px; white-space: nowrap; background: var(--card-bg); max-width: 300px; overflow: hidden; text-overflow: ellipsis; }
  .dl-table tbody tr { cursor: pointer; }
  .dl-table tbody tr:hover td { background: #F8F9FF; }
  .dl-table tbody tr.sel td { background: var(--primary-50); }
  .dl-table .c-chk { width: 46px; min-width: 46px; max-width: 46px; padding: 0 0 0 16px; position: sticky; left: 0; z-index: 2; cursor: default; }
  .dl-table th.c-chk { z-index: 5; }
  .dl-table .c-name { position: sticky; left: 46px; z-index: 2; min-width: 260px; box-shadow: 1px 0 0 var(--border); }
  .dl-table th.c-name { z-index: 5; }
  .dl-table .c-end { position: sticky; right: 0; z-index: 2; width: 56px; min-width: 56px; padding: 0 10px; text-align: center; box-shadow: -1px 0 0 var(--border); cursor: default; }
  .dl-table th.c-end { z-index: 5; }
  .dl-name { display: flex; align-items: center; gap: 10px; min-width: 0; }
  .dl-ic { width: 32px; height: 32px; border-radius: 9px; background: var(--primary-50); color: var(--primary); display: inline-flex; align-items: center; justify-content: center; font-size: 15px; flex-shrink: 0; }
  .dl-name-col { display: flex; flex-direction: column; min-width: 0; }
  .dl-name-text { font-weight: 700; color: var(--primary); overflow: hidden; text-overflow: ellipsis; }
  .dl-name:hover .dl-name-text { text-decoration: underline; }
  .dl-name-sub { display: none; font-size: 12px; color: var(--text-muted); }
  .dl-co { display: inline-flex; align-items: center; gap: 7px; background: #F3F4FA; border-radius: 8px; padding: 4px 10px; font-size: 12.5px; font-weight: 600; max-width: 100%; overflow: hidden; text-overflow: ellipsis; }
  .dl-co i { color: var(--text-muted); }
  .dl-own { display: inline-flex; align-items: center; gap: 8px; }
  .dl-unassigned { font-size: 12.5px; color: #B3B7C7; }
  .dl-due { display: inline-flex; align-items: center; gap: 6px; font-size: 12.5px; font-weight: 600; }
  .dl-due.late { color: var(--danger); }
  .dl-more-c { font-size: 11.5px; font-weight: 700; background: #EEF0F7; color: var(--text-muted); border-radius: 999px; padding: 2px 8px; margin-left: 6px; }
  .dl-rowbtn { width: 32px; height: 32px; border-radius: 8px; border: 0; background: transparent; color: var(--text-muted); }
  .dl-rowbtn:hover { background: var(--primary-50); color: var(--primary); }
  .dl-foot { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; padding: 10px 16px; border-top: 1px solid var(--border); background: #FBFBFE; font-size: 13px; color: var(--text-muted); }
  .dl-foot select { width: auto; display: inline-block; font-size: 13px; padding: 4px 28px 4px 10px; }
  .dl-pages { display: flex; gap: 4px; margin-left: auto; }
  .dl-pages button { min-width: 32px; height: 32px; border-radius: 8px; border: 1px solid var(--border); background: #fff; font-size: 13px; font-weight: 600; color: var(--text); }
  .dl-pages button:hover:not(:disabled) { background: var(--primary-50); }
  .dl-pages button.active { background: var(--primary); color: #fff; border-color: var(--primary); }
  .dl-pages button:disabled { opacity: .4; }
  .dl-skel td > span { display: block; height: 12px; border-radius: 6px; background: linear-gradient(90deg, #F0F1F6, #E7E9F2, #F0F1F6); background-size: 200% 100%; animation: dlShimmer 1.2s infinite; width: 70%; }
  @keyframes dlShimmer { from { background-position: 200% 0; } to { background-position: -200% 0; } }
  .dl-emptybox { text-align: center; padding: 64px 20px; }
  .dl-emptybox .ic { width: 64px; height: 64px; border-radius: 20px; background: var(--primary-50); color: var(--primary); font-size: 28px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 14px; }
  .dl-emptybox h5 { margin: 0 0 6px; }
  .dl-emptybox p { color: var(--text-muted); margin: 0 0 16px; font-size: 13.5px; }

  /* ---- board ---- */
  .dl-board { display: flex; gap: 14px; overflow-x: auto; align-items: flex-start; min-height: 360px; padding-bottom: 10px; scroll-snap-type: x proximity; }
  .dl-col { flex: 0 0 292px; width: 292px; max-height: 100%; display: flex; flex-direction: column; background: #F1F2F9; border-radius: 14px; scroll-snap-align: start; border-top: 3px solid var(--c); }
  .dl-col.over { outline: 2px dashed var(--primary-light); outline-offset: -2px; background: var(--primary-50); }
  .dl-col-head { padding: 12px 12px 8px 14px; }
  .dl-col-top { display: flex; align-items: center; gap: 8px; }
  .dl-col-top b { font-size: 13.5px; }
  .dl-col-top .n { font-size: 11.5px; font-weight: 700; background: #fff; color: var(--text-muted); border-radius: 999px; padding: 2px 9px; }
  .dl-col-top .dot { width: 9px; height: 9px; border-radius: 50%; background: var(--c); }
  .dl-col-top .add { margin-left: auto; }
  .dl-col-sum { font-size: 12px; color: var(--text-muted); margin-top: 3px; font-weight: 600; }
  .dl-col-body { overflow-y: auto; padding: 4px 10px 12px; display: flex; flex-direction: column; gap: 9px; min-height: 70px; flex: 1; }
  .dl-card-k { background: #fff; border: 1px solid var(--border); border-radius: 12px; padding: 12px 13px; box-shadow: var(--shadow-sm); cursor: pointer; transition: box-shadow .12s, transform .12s; position: relative; }
  .dl-card-k:hover { box-shadow: var(--shadow); transform: translateY(-1px); }
  .dl-card-k.dragging { opacity: .35; }
  .dl-card-k .nm { font-weight: 700; font-size: 13.5px; padding-right: 28px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .dl-card-k .amt { font-size: 15px; font-weight: 800; margin-top: 4px; }
  .dl-card-k .ln { display: flex; align-items: center; gap: 7px; font-size: 12.5px; color: var(--text-muted); margin-top: 6px; min-width: 0; }
  .dl-card-k .ln span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .dl-card-k .bt { display: flex; align-items: center; gap: 10px; margin-top: 10px; }
  .dl-card-k .menu { position: absolute; top: 8px; right: 6px; width: 28px; height: 28px; }
  .dl-more { border: 1px dashed #C9CDDD; background: transparent; color: var(--text-muted); border-radius: 10px; padding: 8px; font-size: 12.5px; font-weight: 600; }
  .dl-more:hover { background: #fff; color: var(--primary); }
  .dl-colempty { text-align: center; color: #A7ABC2; font-size: 12.5px; padding: 18px 8px; }

  /* ---- popover / menu ---- */
  .dl-pop { position: fixed; z-index: 2100; background: #fff; border: 1px solid var(--border); border-radius: 14px; box-shadow: var(--shadow-lg); min-width: 200px; max-width: min(360px, calc(100vw - 20px)); max-height: min(440px, calc(100vh - 20px)); overflow: auto; padding: 6px; animation: dlPop .12s ease-out; }
  @keyframes dlPop { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: none; } }
  .dl-pop .it { display: flex; align-items: center; gap: 10px; width: 100%; border: 0; background: transparent; text-align: left; padding: 8px 10px; border-radius: 8px; font-size: 13.5px; color: var(--text); }
  .dl-pop .it:hover, .dl-pop .it:focus-visible { background: var(--primary-50); outline: 0; }
  .dl-pop .it.danger { color: var(--danger); }
  .dl-pop .it.danger:hover { background: var(--danger-bg); }
  .dl-pop .it .ck { margin-left: auto; color: var(--primary); }
  .dl-pop .it > i:first-child { width: 16px; text-align: center; color: var(--text-muted); }
  .dl-pop .it .sdot { width: 10px; height: 10px; border-radius: 50%; background: var(--c); flex-shrink: 0; }
  .dl-pop .it > span:not(.cnt):not(.sdot) { min-width: 0; overflow: hidden; text-overflow: ellipsis; }
  .dl-pop .it small { font-size: 11.5px; color: var(--text-muted); }
  .dl-pop hr { margin: 5px 4px; opacity: .6; }
  .dl-pop .hd { font-size: 11px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: var(--text-muted); padding: 8px 10px 4px; }
  .dl-pop .srch { padding: 4px; }
  .dl-pop .alist { max-height: 260px; overflow-y: auto; }
  .dl-pop .cnt { margin-left: auto; font-size: 11px; font-weight: 700; background: #EEF0F7; color: var(--text-muted); border-radius: 999px; padding: 2px 8px; }
  .dl-pop .pad { padding: 6px 8px 8px; }

  .avatar-stack { display: flex; align-items: center; }

  /* ---- drawer (CRM record view) ---- */
  .dl-drawer { width: 640px !important; max-width: 100vw; }
  .dl-drawer .offcanvas-header { display: none; }
  .dl-drawer .offcanvas-body { padding: 0; display: flex; flex-direction: column; overflow-y: auto; background: var(--bg); }

  .dl-dr-top { padding: 14px 24px 20px; background: linear-gradient(180deg, var(--primary-50), #fff 90%); }
  .dl-dr-bar { display: flex; align-items: center; gap: 2px; margin-bottom: 14px; font-size: 12px; font-weight: 700; color: var(--text-muted); }
  .dl-dr-bar .crumb { display: inline-flex; align-items: center; gap: 6px; text-transform: uppercase; letter-spacing: .06em; }
  .dl-dr-bar .pos { margin-left: 10px; font-weight: 600; background: #fff; border: 1px solid var(--border); border-radius: 999px; padding: 2px 10px; }
  .dl-dr-bar .sp { flex: 1; }
  .dl-iconbtn { width: 34px; height: 34px; border-radius: 50%; border: 0; background: transparent; color: var(--text-muted); display: inline-flex; align-items: center; justify-content: center; transition: background .12s, color .12s; }
  .dl-iconbtn:hover { background: #fff; color: var(--text); box-shadow: var(--shadow-sm); }
  .dl-iconbtn.danger:hover { background: var(--danger-bg); color: var(--danger); }
  .dl-iconbtn:disabled { opacity: .35; pointer-events: none; }

  .dl-dr-id { display: flex; gap: 14px; align-items: flex-start; }
  .dl-dr-ic { width: 56px; height: 56px; border-radius: 16px; background: var(--c, var(--primary)); color: #fff; font-size: 25px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 6px 14px rgba(0, 0, 0, .12); }
  .dl-dr-idc { min-width: 0; flex: 1; }
  .dl-prop-v.hdr { font-size: 20px; font-weight: 800; letter-spacing: -.01em; line-height: 1.25; min-height: 0; padding: 3px 10px; margin: -3px -10px 0; }
  .dl-prop-v.hdr input { font-size: 18px; font-weight: 800; }
  .dl-dr-sub { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-top: 8px; font-size: 12.5px; color: var(--text-muted); }
  .dl-subchip { display: inline-flex; align-items: center; gap: 6px; border: 1px solid var(--border); background: #fff; border-radius: 999px; padding: 3px 10px 3px 4px; font-size: 12.5px; font-weight: 600; color: var(--text); max-width: 100%; }
  .dl-subchip > i { margin-left: 6px; color: var(--text-muted); }
  .dl-subchip .avatar { width: 20px; height: 20px; font-size: 9px; }
  button.dl-subchip:hover { background: var(--primary-50); border-color: var(--primary-100); color: var(--primary); }

  .dl-track { display: flex; gap: 3px; margin-top: 18px; overflow-x: auto; scrollbar-width: none; }
  .dl-track::-webkit-scrollbar { display: none; }
  .dl-track button { flex: 1 0 auto; min-width: 82px; border: 0; background: #E9EBF4; color: var(--text-muted); font-size: 12px; font-weight: 700; padding: 9px 14px 9px 20px; clip-path: polygon(0 0, calc(100% - 10px) 0, 100% 50%, calc(100% - 10px) 100%, 0 100%, 10px 50%); transition: filter .12s; }
  .dl-track button:first-child { padding-left: 14px; clip-path: polygon(0 0, calc(100% - 10px) 0, 100% 50%, calc(100% - 10px) 100%, 0 100%); }
  .dl-track button:hover { filter: brightness(.95); }
  .dl-track button.past { background: var(--primary-100); color: var(--primary); }
  .dl-track button.cur { background: var(--c); color: #fff; }

  .dl-dr-actions { display: flex; align-items: center; gap: 8px; margin-top: 14px; flex-wrap: wrap; }
  .dl-dr-actions .sp { flex: 1; }
  .dl-dr-actions .btn { font-weight: 700; border-radius: 10px; }
  .dl-qa { width: 36px; height: 36px; border-radius: 50%; border: 1px solid var(--border); background: #fff; display: inline-flex; align-items: center; justify-content: center; color: var(--primary); font-size: 15px; text-decoration: none; transition: all .12s; box-shadow: var(--shadow-sm); }
  .dl-qa:hover { background: var(--primary); color: #fff; border-color: var(--primary); transform: translateY(-1px); }
  .dl-qa.wa { color: var(--success); } .dl-qa.wa:hover { background: var(--success); border-color: var(--success); color: #fff; }
  .dl-qa.off { opacity: .35; pointer-events: none; }

  .dl-metrics { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-top: 16px; }
  .dl-m { text-align: left; background: #fff; border: 1px solid var(--border); border-top: 3px solid var(--c, var(--primary)); border-radius: 12px; padding: 10px 12px; min-width: 0; display: block; transition: box-shadow .12s, transform .12s; }
  button.dl-m { cursor: pointer; }
  button.dl-m:hover { box-shadow: var(--shadow); transform: translateY(-1px); }
  .dl-m small { display: block; font-size: 11.5px; font-weight: 600; color: var(--text-muted); }
  .dl-m b { display: block; font-size: 15px; font-weight: 800; letter-spacing: -.01em; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--text); }
  .dl-m span { display: block; font-size: 11.5px; color: var(--text-muted); margin-top: 2px; }
  .dl-m.late { border-color: #F3C4C4; border-top-color: var(--danger); }
  .dl-m.late span { color: var(--danger); font-weight: 700; }
  .dl-bar { height: 6px; border-radius: 4px; background: #E9EBF4; overflow: hidden; margin-top: 9px; }
  .dl-bar i { display: block; height: 100%; background: var(--c, var(--primary)); border-radius: 4px; transition: width .25s; }

  .dl-dr-tabs { position: sticky; top: 0; z-index: 6; display: flex; gap: 2px; padding: 0 24px; background: #fff; border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); overflow-x: auto; scrollbar-width: none; flex-shrink: 0; }
  .dl-dr-tabs::-webkit-scrollbar { display: none; }
  .dl-dr-tab { border: 0; background: transparent; padding: 12px 14px; font-weight: 600; font-size: 13.5px; color: var(--text-muted); border-bottom: 2px solid transparent; margin-bottom: -1px; display: inline-flex; gap: 7px; align-items: center; white-space: nowrap; }
  .dl-dr-tab:hover { color: var(--text); }
  .dl-dr-tab.active { color: var(--primary); border-bottom-color: var(--primary); }
  .dl-dr-tab .n { font-size: 11px; background: #EEF0F7; border-radius: 999px; padding: 1px 7px; color: var(--text-muted); }
  .dl-dr-tab.active .n { background: var(--primary-50); color: var(--primary); }

  .dl-dr-body { flex: 1 0 auto; padding: 18px 24px 40px; background: var(--bg); }
  .dl-sect { background: #fff; border: 1px solid var(--border); border-radius: 14px; margin-bottom: 14px; overflow: hidden; }
  .dl-sect-h { display: flex; align-items: center; gap: 8px; padding: 12px 16px; font-weight: 700; font-size: 13.5px; border-bottom: 1px solid #F0F1F6; }
  .dl-sect-h[data-toggle] { cursor: pointer; user-select: none; }
  .dl-sect-h[data-toggle]:hover { background: #FAFBFE; }
  .dl-sect-h > i:first-child { color: var(--primary); }
  .dl-sect-h .sp { flex: 1; }
  .dl-sect-h .chev { color: var(--text-muted); font-size: 11px; transition: transform .15s; }
  .dl-sect.collapsed .chev { transform: rotate(-90deg); }
  .dl-sect.collapsed .dl-sect-h { border-bottom: 0; }
  .dl-sect.collapsed .dl-sect-b { display: none; }
  .dl-pickpad { padding: 14px 16px 16px; }

  .dl-prop { display: grid; grid-template-columns: 130px 1fr; gap: 10px; align-items: center; padding: 3px 12px 3px 16px; min-height: 46px; }
  .dl-prop + .dl-prop { border-top: 1px solid #F6F7FB; }
  .dl-prop-l { font-size: 12.5px; color: var(--text-muted); font-weight: 600; }
  .dl-prop-v { display: flex; align-items: center; gap: 8px; min-height: 34px; padding: 4px 10px; margin: 0 -10px; border-radius: 8px; border: 1px solid transparent; cursor: text; font-size: 13.5px; word-break: break-word; }
  .dl-prop-v:hover { background: #F6F7FD; border-color: var(--border); }
  .dl-prop-v .pen { margin-left: auto; opacity: 0; color: var(--text-muted); font-size: 12px; }
  .dl-prop-v:hover .pen, .dl-prop-v:focus-visible .pen { opacity: 1; }
  .dl-prop-v:focus-visible { outline: 2px solid var(--primary-light); }
  .dl-prop-v.ro { cursor: default; }
  .dl-prop-v.ro:hover { background: transparent; border-color: transparent; }
  .dl-prop-v .ph { color: #B3B7C7; }
  .dl-prop-v input, .dl-prop-v select, .dl-prop-v textarea, .dl-prop-v .input-group { width: 100%; }
  .dl-prop-v.editing { background: #fff; border-color: var(--primary-light); box-shadow: 0 0 0 3px rgba(var(--primary-rgb), .1); }
  .dl-saving { opacity: .55; pointer-events: none; }
  .dl-probv { display: flex; align-items: center; gap: 12px; width: 100%; }
  .dl-probv b { min-width: 42px; }
  .dl-probv .dl-bar { flex: 1; margin: 0; max-width: 180px; }
  .dl-prio-seg { display: flex; gap: 6px; flex-wrap: wrap; }
  .dl-prio-seg button { border: 1.5px solid var(--border); background: #fff; border-radius: 999px; padding: 5px 12px; font-size: 12.5px; font-weight: 700; color: var(--text-muted); display: inline-flex; align-items: center; gap: 6px; transition: all .12s; }
  .dl-prio-seg button:hover { border-color: var(--c); color: var(--c); }
  .dl-prio-seg button.on { border-color: var(--c); background: var(--bg); color: var(--c); }
  .dl-prio-seg button i { font-size: 10px; }

  .dl-banner { display: flex; gap: 10px; align-items: flex-start; border-radius: 12px; padding: 11px 14px; font-size: 13px; margin-bottom: 14px; }
  .dl-banner b { display: block; }
  .dl-banner > i { margin-top: 2px; }
  .dl-banner .btn { margin-left: auto; white-space: nowrap; align-self: center; }
  .dl-banner.lost { background: var(--danger-bg); color: var(--danger); }
  .dl-banner.won { background: var(--success-bg); color: var(--success); }
  .dl-banner.warn { background: var(--warning-bg); color: #8A4B05; }

  /* company + people */
  .dl-co-ic { width: 40px; height: 40px; border-radius: 12px; background: var(--primary-50); color: var(--primary); font-size: 18px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
  .dl-company, .dl-empty-assoc, .dl-person { padding: 14px 16px; display: flex; gap: 12px; align-items: center; }
  .dl-person + .dl-person { border-top: 1px solid #F0F1F6; }
  .dl-company .nm, .dl-empty-assoc .nm, .dl-person .nm { font-weight: 700; font-size: 13.5px; display: flex; align-items: center; gap: 8px; }
  .dl-empty-assoc small { color: var(--text-muted); display: block; font-size: 12px; }
  .dl-cmeta { display: flex; flex-wrap: wrap; gap: 6px 14px; margin-top: 5px; }
  .dl-cmeta a, .dl-cmeta button { border: 0; background: transparent; padding: 0; font-size: 12.5px; color: var(--text-muted); text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
  .dl-cmeta a:hover, .dl-cmeta button:hover { color: var(--primary); }
  .dl-addr { font-size: 12.5px; color: var(--text-muted); margin-top: 5px; display: flex; gap: 6px; }
  .dl-person .pm { min-width: 0; flex: 1; }
  .dl-person .pm small { display: flex; align-items: center; gap: 6px; font-size: 12.5px; color: var(--text-muted); margin-top: 2px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .dl-person .pa { display: flex; align-items: center; gap: 6px; flex-shrink: 0; }
  .dl-person .pa .dl-qa { width: 32px; height: 32px; font-size: 14px; box-shadow: none; }
  .dl-tag { font-size: 10.5px; font-weight: 700; background: var(--primary-50); color: var(--primary); border-radius: 999px; padding: 2px 8px; }

  /* timeline */
  .dl-tl { padding: 6px 16px 10px; }
  .dl-tl-i { display: flex; gap: 12px; padding: 10px 0; position: relative; }
  .dl-tl-i:not(:last-child)::before { content: ""; position: absolute; left: 15px; top: 44px; bottom: -8px; width: 2px; background: #ECEEF6; }
  .dl-tl-ic { width: 32px; height: 32px; border-radius: 50%; background: var(--info-bg); color: var(--info); display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; }
  .dl-tl-ic.won { background: var(--success-bg); color: var(--success); }
  .dl-tl-ic.lost, .dl-tl-ic.late { background: var(--danger-bg); color: var(--danger); }
  .dl-tl-ic.mute { background: #EEF1F6; color: var(--text-muted); }
  .dl-tl-t { font-weight: 700; font-size: 13.5px; }
  .dl-tl-s { font-size: 12.5px; color: var(--text-muted); margin-top: 2px; word-break: break-word; }
  .dl-tl-at { margin-left: auto; font-size: 12px; color: var(--text-muted); white-space: nowrap; padding-top: 2px; }

  @media (max-width: 575.98px) {
    .dl-metrics { grid-template-columns: repeat(2, 1fr); }
    .dl-dr-top, .dl-dr-body { padding-left: 16px; padding-right: 16px; }
    .dl-dr-tabs { padding: 0 10px; }
    .dl-person { flex-wrap: wrap; }
  }

  /* ---- create-deal stepper (same look as Leads) ---- */
  .dl-steps { display: flex; align-items: center; gap: 8px; margin-bottom: 20px; }
  .dl-step { display: flex; align-items: center; gap: 9px; font-size: 13px; font-weight: 700; color: var(--text-muted); }
  .dl-step .no { width: 28px; height: 28px; border-radius: 50%; background: #EEF0F7; display: flex; align-items: center; justify-content: center; font-size: 12.5px; }
  .dl-step.active { color: var(--primary); } .dl-step.active .no { background: var(--primary); color: #fff; box-shadow: 0 0 0 4px rgba(var(--primary-rgb), .14); }
  .dl-step.done { color: var(--success); } .dl-step.done .no { background: var(--success); color: #fff; }
  .dl-steps .bar { flex: 1; height: 2px; background: var(--border); border-radius: 2px; min-width: 18px; }
  .dl-steps .bar.done { background: var(--success); }
  .dl-pane h6 { font-size: 16px; margin: 0 0 4px; }
  .dl-pane .hint { color: var(--text-muted); font-size: 13px; margin-bottom: 14px; }
  .dl-lbl { font-size: 12.5px; font-weight: 600; margin-bottom: 4px; display: block; }

  /* picker: search list + "create new" form (shared by create modal and drawer) */
  .dl-psearch { position: relative; }
  .dl-psearch i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); }
  .dl-psearch input.form-control { padding-left: 38px !important; }
  .dl-res { border: 1px solid var(--border); border-radius: 12px; overflow: hidden; margin-top: 10px; max-height: 300px; overflow-y: auto; }
  .dl-res button { display: flex; align-items: center; gap: 12px; width: 100%; border: 0; border-top: 1px solid #F0F1F6; background: #fff; padding: 10px 14px; text-align: left; }
  .dl-res button:first-child { border-top: 0; }
  .dl-res button:hover, .dl-res button:focus-visible { background: var(--primary-50); outline: 0; }
  .dl-res .t { font-weight: 700; font-size: 13.5px; }
  .dl-res .s { font-size: 12px; color: var(--text-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .dl-res .r { margin-left: auto; font-size: 11.5px; color: var(--text-muted); white-space: nowrap; text-align: right; line-height: 1.4; }
  .dl-res button > div:not(.dl-co-ic):not(.avatar) { min-width: 0; }
  .dl-res .none { padding: 14px; font-size: 13px; color: var(--text-muted); }
  .dl-sels { display: flex; flex-direction: column; gap: 8px; }
  .dl-sel { display: flex; align-items: center; gap: 12px; border: 1.5px solid var(--primary); background: var(--primary-50); border-radius: 12px; padding: 12px 14px; }
  .dl-sel .t { font-weight: 700; font-size: 13.5px; }
  .dl-sel .s { font-size: 12.5px; color: var(--text-muted); }
  .dl-sel .x { margin-left: auto; flex-shrink: 0; }
  .dl-newform { border: 1px solid var(--border); border-radius: 12px; padding: 14px; background: #FBFBFE; }
  .dl-newform .form-control, .dl-newform .form-select { background: #fff; }
  .dl-newform label { font-size: 12.5px; font-weight: 600; margin-bottom: 4px; }
  .dl-nf-h { font-weight: 800; font-size: 14px; margin-bottom: 12px; }
  .dl-alert { border-radius: 10px; padding: 10px 13px; font-size: 13px; margin-top: 12px; display: flex; gap: 10px; align-items: center; background: var(--warning-bg); color: #8A4B05; }
  .dl-alert .btn { margin-left: auto; white-space: nowrap; }

  /* deal details step */
  .dl-sumrow { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; margin-bottom: 16px; }
  .dl-sumchip { display: inline-flex; align-items: center; gap: 8px; background: #F3F4FA; border-radius: 10px; padding: 7px 12px; font-size: 13px; font-weight: 600; }
  .dl-pick { display: flex; flex-wrap: wrap; gap: 8px; }
  .dl-pick button { border: 1.5px solid var(--border); background: #fff; border-radius: 999px; padding: 7px 14px; font-size: 13px; font-weight: 700; color: var(--text-muted); display: inline-flex; align-items: center; gap: 7px; }
  .dl-pick button .dot { width: 8px; height: 8px; border-radius: 50%; background: var(--c); }
  .dl-pick button small { font-weight: 600; opacity: .7; }
  .dl-pick button.on { border-color: var(--c); background: var(--bg); color: var(--c); }
  .dl-qchips { display: flex; gap: 6px; margin-top: 6px; flex-wrap: wrap; }
  .dl-qchips button { border: 1px solid var(--border); background: #fff; border-radius: 999px; padding: 3px 10px; font-size: 12px; font-weight: 600; color: var(--text-muted); }
  .dl-qchips button:hover { background: var(--primary-50); color: var(--primary); border-color: var(--primary-100); }
  .dl-fc { display: flex; align-items: center; gap: 8px; background: var(--warning-bg); color: #8A4B05; border-radius: 10px; padding: 9px 13px; font-size: 13px; }

  /* ---- column chooser ---- */
  .dl-plus { width: 34px; height: 34px; border-radius: 50%; border: 1.5px dashed #B9BDD3; background: #fff; color: var(--primary); font-size: 16px; display: inline-flex; align-items: center; justify-content: center; vertical-align: middle; transition: all .12s; }
  .dl-plus:hover { background: var(--primary); color: #fff; border-style: solid; border-color: var(--primary); transform: rotate(90deg); }
  .dl-cols { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
  .dl-cols h6 { font-size: 12px; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); margin: 0 0 6px; }
  .dl-cols .lst { max-height: 380px; overflow-y: auto; }
  .dl-colrow { display: flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 9px; font-size: 13.5px; border: 1px solid transparent; }
  .dl-colrow:hover { background: var(--primary-50); }
  .dl-colrow .sp { flex: 1; }
  .dl-colrow > span:nth-child(2) { white-space: nowrap; }
  .dl-colrow button { border: 0; background: transparent; width: 26px; height: 26px; border-radius: 7px; color: var(--text-muted); }
  .dl-colrow button:hover:not(:disabled) { background: #fff; color: var(--primary); }
  .dl-colrow button:disabled { opacity: .35; }
  .dl-colrow.sel { background: #F7F8FC; border-color: var(--border); margin-bottom: 5px; cursor: grab; }
  .dl-colrow.sel.dragging { opacity: .4; }
  .dl-colrow.sel.dropt { border-color: var(--primary); box-shadow: 0 -2px 0 var(--primary); }
  .dl-colrow .lock, .dl-colrow .grip { color: #B3B7C7; font-size: 12px; }
  @media (max-width: 767.98px) { .dl-cols { grid-template-columns: 1fr; } .dl-step span.l { display: none; } }

  @media (min-width: 768px) { #dlCreateModal .modal-dialog { max-width: 720px; } #dlColsModal .modal-dialog { max-width: 700px; } }
  @media (max-width: 767.98px) {
    .dl-name-sub { display: block; }
    .dl-table .c-chk, .dl-table .c-name { position: static; box-shadow: none; }
    .dl-table .c-name { min-width: 220px; }
    .dl-head h1 { font-size: 21px; }
    .dl-head .dl-seg { order: 3; }
    .dl-create span.t { display: none; }
    .dl-create { padding: 10px 14px; }
    .dl-search { flex: 1 1 100%; }
    .dl-col { flex-basis: 84vw; width: 84vw; }
    .dl-prop { grid-template-columns: 1fr; gap: 0; padding-top: 8px; padding-bottom: 8px; }
    .dl-dr-top, .dl-dr-body { padding-left: 16px; padding-right: 16px; }
    #dlCreateModal .modal-dialog { margin: 0; max-width: none; min-height: 100%; }
    #dlCreateModal .modal-content { min-height: 100vh; border-radius: 0; }
  }
  @media (prefers-reduced-motion: reduce) { .dl-pop, .dl-skel td > span { animation: none; } }
</style>

<div class="page-content">
<div class="dl-wrap" id="dlWrap">

  <!-- HEADER -->
  <div class="dl-head">
    <div class="grow">
      <h1>Deals <span class="cnt" id="dlCount">0</span></h1>
      <p>Link a deal to a company or contact and move it through your pipeline.</p>
    </div>
    <div class="dl-seg" role="tablist" aria-label="View">
      <button type="button" data-view="board" class="active"><i class="bi bi-kanban"></i><span>Board</span></button>
      <button type="button" data-view="table"><i class="bi bi-table"></i><span>Table</span></button>
    </div>
    <button type="button" class="dl-create" id="openCreateBtn"><i class="bi bi-plus-lg"></i><span class="t">Create deal</span></button>
  </div>

  <!-- FORECAST -->
  <div class="dl-kpis">
    <div class="dl-kpi" style="--c:var(--primary)"><small>Open pipeline</small><b id="kOpen">₹0</b><span id="kOpenN"></span></div>
    <div class="dl-kpi" style="--c:var(--warning)"><small>Weighted forecast</small><b id="kWeighted">₹0</b><span>based on win probability</span></div>
    <div class="dl-kpi" style="--c:var(--success)"><small>Won</small><b id="kWon">₹0</b><span id="kWonN"></span></div>
  </div>

  <!-- TABS -->
  <div class="dl-tabs" id="dlTabs">
    <button type="button" class="dl-tab active" data-cat="all">All deals <span class="n" data-n="all">0</span></button>
    <button type="button" class="dl-tab" data-cat="mine">My deals <span class="n" data-n="mine">0</span></button>
    <button type="button" class="dl-tab" data-cat="open">Open <span class="n" data-n="open">0</span></button>
    <button type="button" class="dl-tab" data-cat="soon">Closing in 7 days <span class="n" data-n="soon">0</span></button>
    <button type="button" class="dl-tab" data-cat="won">Won <span class="n" data-n="won">0</span></button>
    <button type="button" class="dl-tab" data-cat="lost">Lost <span class="n" data-n="lost">0</span></button>
  </div>

  <!-- TOOLBAR -->
  <div class="dl-toolbar">
    <div class="dl-search">
      <i class="bi bi-search"></i>
      <input type="text" class="form-control" id="dlSearch" placeholder="Search deals, company, contact..." autocomplete="off">
      <kbd>/</kbd>
    </div>
    <button type="button" class="dl-chip" id="dlChipStage"><span>Deal stage</span><i class="bi bi-chevron-down"></i></button>
    <button type="button" class="dl-chip" id="dlChipPrio"><span>Priority</span><i class="bi bi-chevron-down"></i></button>
    <button type="button" class="dl-chip" id="dlChipOwner"><i class="bi bi-person"></i><span>Deal owner</span><i class="bi bi-chevron-down"></i></button>
    <button type="button" class="dl-link d-none" id="dlClear">Clear all</button>
    <div class="dl-spacer"></div>
    <button type="button" class="dl-ghost" id="dlColsBtn"><i class="bi bi-layout-three-columns"></i><span>Edit columns</span></button>
  </div>

  <!-- BULK -->
  <div class="dl-bulk" id="dlBulk">
    <b><span id="dlBulkCount">0</span> selected</b>
    <button type="button" class="dl-ghost" id="dlBulkDelete" style="color:var(--danger)"><i class="bi bi-trash"></i>Delete</button>
    <button type="button" class="dl-link ms-auto" id="dlBulkClear">Clear selection</button>
  </div>

  <!-- TABLE -->
  <div class="dl-card d-none" id="dlTableWrap">
    <div class="dl-scroll" id="dlScroll">
      <table class="dl-table">
        <thead id="dlThead"></thead>
        <tbody id="dlTbody"></tbody>
      </table>
    </div>
    <div class="dl-foot" id="dlFoot">
      <span id="dlPageInfo"></span>
      <span>Rows: <select class="form-select form-select-sm" id="dlPageSize"><option>10</option><option>25</option><option>50</option><option>100</option></select></span>
      <div class="dl-pages" id="dlPages"></div>
    </div>
  </div>

  <!-- BOARD -->
  <div class="dl-board" id="dlBoard"></div>

</div>
</div>

<!-- DEAL DRAWER -->
<div class="offcanvas offcanvas-end dl-drawer" tabindex="-1" id="dlDrawer" aria-label="Deal details">
  <div class="offcanvas-header"><h5 class="offcanvas-title">Deal</h5></div>
  <div class="offcanvas-body">
    <div class="dl-dr-top" id="dlDrTop"></div>
    <div class="dl-dr-tabs" id="dlDrTabs"></div>
    <div class="dl-dr-body" id="dlDrBody"></div>
  </div>
</div>

<!-- CREATE DEAL (Contact → Company → Deal) -->
<div class="modal fade" id="dlCreateModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Create deal</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="dl-steps" id="dlSteps"></div>
        <div id="dlCreateBody"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary me-auto" id="dlCrBack">Back</button>
        <button type="button" class="btn btn-outline-secondary d-none" id="dlCrSkip">Skip</button>
        <button type="button" class="btn btn-outline-primary d-none" id="dlCrAnother">Create &amp; add another</button>
        <button type="button" class="btn btn-primary" id="dlCrNext">Continue</button>
      </div>
    </div>
  </div>
</div>

<!-- EDIT COLUMNS -->
<div class="modal fade" id="dlColsModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit columns</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="dl-cols">
          <div>
            <h6>Available properties</h6>
            <input type="text" class="form-control mb-2" id="dlColsSearch" placeholder="Search properties...">
            <div class="lst" id="dlColsAvail"></div>
          </div>
          <div>
            <h6>Selected columns (<span id="dlColsN">0</span>)</h6>
            <div class="lst" id="dlColsSel"></div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary me-auto" id="dlColsReset">Reset to default</button>
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Done</button>
      </div>
    </div>
  </div>
</div>

<!-- DELETE CONFIRM -->
<div class="modal fade" id="dlDeleteModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-content-compact">
    <div class="modal-content modal-content-danger">
      <div class="modal-header">
        <h5 class="modal-title" id="dlDeleteTitle">Delete deal?</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body"><p class="mb-0">This deal will be permanently deleted. The company and contacts will not be deleted. This can't be undone.</p></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="dlConfirmDelete">Delete</button>
      </div>
    </div>
  </div>
</div>

<script>
  window.DEAL_PAGE_DATA = {
    admins: @json($admins),
    stages: @json($stages),
    openStages: @json($openStages),
    sources: @json($sources),
    csrfToken: '{{ csrf_token() }}',
    isSuperAdmin: @json($isSuperAdmin),
    routes: {
      data: '{{ route('deals.data') }}',
      store: '{{ route('deals.store') }}',
      patch: '{{ url('admin/deals') }}/__ID__',
      destroy: '{{ url('admin/deals') }}/__ID__',
      stage: '{{ url('admin/deals') }}/__ID__/stage',
      bulkDelete: '{{ route('deals.bulkDestroy') }}',
      // reused from the Leads module
      contactsSearch: '{{ route('leads.contacts.search') }}',
      contactStore: '{{ route('leads.contacts.store') }}',
      companiesSearch: '{{ route('leads.companies.search') }}',
      companyStore: '{{ route('leads.companies.store') }}',
    }
  };

(function () {
  "use strict";

  const CFG = window.DEAL_PAGE_DATA;
  const CSRF = CFG.csrfToken;
  const STAGES = CFG.stages;
  const SK = Object.keys(STAGES);
  const OPEN = CFG.openStages;
  const SOURCES = CFG.sources;
  const PRIOS = [["low", "Low"], ["medium", "Medium"], ["high", "High"]];
  const DEAL_TYPES = [["new_business", "New business"], ["existing_business", "Existing business"]];
  const LOST_REASONS = ["Price too high", "Chose a competitor", "No budget", "No response", "Requirements not a fit", "Other"];
  const AVATAR_COLORS = ["#4338CA", "#0891B2", "#B45309", "#16A34A", "#DC2626", "#6D28D9", "#0D9488"];
  const $ = id => document.getElementById(id);
  const DASH = '<span class="dl-empty">—</span>';

  /* ---------------- helpers ---------------- */

  const esc = s => String(s == null ? "" : s).replace(/[&<>"']/g, c => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));
  function lsGet(k, d) { try { const v = localStorage.getItem(k); return v == null ? d : JSON.parse(v); } catch (e) { return d; } }
  function lsSet(k, v) { try { localStorage.setItem(k, JSON.stringify(v)); } catch (e) { /* private mode */ } }
  const initials = n => (n || "?").trim().split(/\s+/).slice(0, 2).map(p => p[0]).join("").toUpperCase() || "?";
  function colorFor(name) { let h = 0; for (let i = 0; i < (name || "").length; i++) h = name.charCodeAt(i) + ((h << 5) - h); return AVATAR_COLORS[Math.abs(h) % AVATAR_COLORS.length]; }
  const avatarHtml = (name, size) => `<div class="avatar${size === "lg" ? " avatar-lg" : size === "sm" ? " avatar-sm" : ""}" style="background:${colorFor(name)}">${esc(initials(name))}</div>`;
  const debounce = (fn, ms) => { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; };
  const digits = n => (n || "").replace(/\D/g, "");
  const num = v => parseFloat(v) || 0;
  const money = v => "₹" + num(v).toLocaleString("en-IN", { maximumFractionDigits: num(v) % 1 ? 2 : 0 });
  function moneyShort(v) {
    v = num(v);
    const f = (x, s) => "₹" + (Math.round(x * 100) / 100) + s;
    return v >= 1e7 ? f(v / 1e7, " Cr") : v >= 1e5 ? f(v / 1e5, " L") : v >= 1e3 ? f(v / 1e3, "K") : "₹" + Math.round(v);
  }
  const ymd = dt => dt.getFullYear() + "-" + String(dt.getMonth() + 1).padStart(2, "0") + "-" + String(dt.getDate()).padStart(2, "0");
  function fmtDate(d) {
    if (!d) return "";
    const dt = /^\d{4}-\d{2}-\d{2}$/.test(d) ? new Date(d + "T00:00:00") : new Date(d);
    return isNaN(dt) ? d : dt.toLocaleDateString(undefined, { day: "numeric", month: "short", year: "numeric" });
  }
  function daysTo(dateStr) {
    const t = new Date(); t.setHours(0, 0, 0, 0);
    return Math.round((new Date(dateStr + "T00:00:00") - t) / 86400000);
  }
  function dayLabel(dateStr) {
    const n = daysTo(dateStr);
    return n === 0 ? "Today" : n === 1 ? "Tomorrow" : n === -1 ? "Yesterday" : fmtDate(dateStr);
  }
  function ago(iso) {
    const t = new Date(iso).getTime();
    if (!iso || isNaN(t)) return "";
    const s = Math.max(0, (Date.now() - t) / 1000);
    return s < 60 ? "just now" : s < 3600 ? Math.floor(s / 60) + "m ago" : s < 86400 ? Math.floor(s / 3600) + "h ago" : s < 2592000 ? Math.floor(s / 86400) + "d ago" : fmtDate(iso);
  }

  function toast(message, variant) {
    let wrap = $("dlToastWrap");
    if (!wrap) {
      wrap = document.createElement("div");
      wrap.id = "dlToastWrap";
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

  const csrfHeaders = extra => Object.assign({ "X-CSRF-TOKEN": CSRF, "X-Requested-With": "XMLHttpRequest" }, extra || {});
  async function apiFetch(u, options) {
    const res = await fetch(u, Object.assign({ headers: csrfHeaders({ "Accept": "application/json" }) }, options));
    if (res.status === 401 || res.status === 419) { toast("Your session has expired. Please log in again.", "danger"); throw new Error("unauthenticated"); }
    let j;
    try { j = await res.json(); } catch (e) { j = { success: false, message: "Server error (" + res.status + ")" }; }
    j._status = res.status;
    return j;
  }
  const reqJson = (u, method, body) => apiFetch(u, { method, headers: csrfHeaders({ "Content-Type": "application/json", "Accept": "application/json" }), body: JSON.stringify(body || {}) });
  const url = (key, id) => CFG.routes[key].replace("__ID__", id);
  const firstErr = (res, fb) => (res && (res.message || (res.errors && Object.values(res.errors)[0][0]))) || fb;

  /* ---------------- state ---------------- */

  let deals = [], me = null, loading = true;
  let view = lsGet("dl.view", "board") === "table" ? "table" : "board";
  let cat = "all", page = 1, pageSize = parseInt(lsGet("dl.ps", 25), 10) || 25;
  let F = { search: "", stages: new Set(), priority: "", owner: null };
  let sort = null, selected = new Set(), detail = null, orderIds = [], pendingDel = [];
  let assoc = null;               // drawer: "company" | "contact" while an inline picker is open
  const boardLimit = {};

  const isOpen = d => OPEN.indexOf(d.stage) >= 0;
  const stLabel = k => (STAGES[k] || {}).label || k;
  const prioHtml = p => { const x = PRIOS.find(o => o[0] === p); return x ? `<span class="dl-prio pr-${p}"><i class="bi bi-flag-fill"></i>${x[1]}</span>` : DASH; };
  function pill(stage, interactive, id) {
    const inner = `<span class="dot"></span>${esc(stLabel(stage))}`;
    return interactive
      ? `<button type="button" class="dl-pill sg-${stage}" data-act="stage" data-id="${id}">${inner}<i class="bi bi-chevron-down"></i></button>`
      : `<span class="dl-pill sg-${stage}">${inner}</span>`;
  }
  function closeChip(d) {
    if (!d.close_date) return DASH;
    const late = isOpen(d) && daysTo(d.close_date) < 0;
    return `<span class="dl-due ${late ? "late" : ""}"><i class="bi bi-${late ? "exclamation-circle-fill" : "calendar3"}"></i>${esc(isOpen(d) ? dayLabel(d.close_date) : fmtDate(d.close_date))}</span>`;
  }
  function contactsCell(d) {
    const c = d.contacts || [];
    return c.length ? esc(c[0].name) + (c.length > 1 ? `<span class="dl-more-c">+${c.length - 1}</span>` : "") : DASH;
  }
  const ownerCell = d => d.owner ? `<span class="dl-own">${avatarHtml(d.owner.name, "sm")}${esc(d.owner.name)}</span>` : '<span class="dl-unassigned">Unassigned</span>';

  const COLS = [
    { k: "name", label: "Deal name", sortv: d => d.name.toLowerCase() },
    { k: "amount", label: "Amount", sortv: d => num(d.amount), cell: d => `<b>${money(d.amount)}</b>` },
    { k: "stage", label: "Deal stage", sortv: d => SK.indexOf(d.stage), cell: d => pill(d.stage, true, d.id) },
    { k: "close", label: "Close date", sortv: d => d.close_date || "9999-99-99", cell: closeChip },
    { k: "company", label: "Company", sortv: d => ((d.company || {}).name || "").toLowerCase(), cell: d => d.company ? `<span class="dl-co"><i class="bi bi-building"></i>${esc(d.company.name)}</span>` : DASH },
    { k: "contacts", label: "Contacts", sortv: d => ((d.contacts || [])[0] || {}).name || "", cell: contactsCell },
    { k: "owner", label: "Deal owner", sortv: d => ((d.owner || {}).name || "").toLowerCase(), cell: ownerCell },
    { k: "priority", label: "Priority", sortv: d => PRIOS.findIndex(p => p[0] === d.priority), cell: d => prioHtml(d.priority) },
    { k: "created", label: "Create date", sortv: d => d.created_at || "", cell: d => d.created_at ? `<span title="${esc(fmtDate(d.created_at))}">${esc(ago(d.created_at))}</span>` : DASH },
  ];

  // extra properties that can be added from the column chooser
  COLS.push(
    { k: "probability", label: "Probability", sortv: d => d.probability || 0, cell: d => d.probability != null ? d.probability + "%" : DASH },
    { k: "weighted", label: "Weighted amount", sortv: d => num(d.amount) * (d.probability || 0) / 100, cell: d => isOpen(d) ? money(num(d.amount) * (d.probability || 0) / 100) : DASH },
    { k: "source", label: "Source", sortv: d => d.source || "", cell: d => d.source ? esc(d.source) : DASH },
    { k: "type", label: "Deal type", sortv: d => d.deal_type || "", cell: d => esc((DEAL_TYPES.find(t => t[0] === d.deal_type) || [])[1] || "") || DASH },
    { k: "creator", label: "Created by", sortv: d => ((d.creator || {}).name || "").toLowerCase(), cell: d => d.creator ? esc(d.creator.name) : DASH }
  );
  const COL_ICONS = { name: "briefcase", amount: "currency-rupee", stage: "flag", close: "calendar-event", company: "building", contacts: "people", owner: "person-badge", priority: "flag-fill", created: "calendar3", probability: "percent", weighted: "graph-up", source: "bullseye", type: "tag", creator: "person" };
  const COLDEFS = {};
  COLS.forEach(c => { c.icon = COL_ICONS[c.k] || "dot"; COLDEFS[c.k] = c; });

  const DEFAULT_COLS = ["name", "amount", "stage", "close", "company", "owner"];
  function sanitizeCols(arr) {
    const out = ["name"];
    (Array.isArray(arr) ? arr : DEFAULT_COLS).forEach(k => { if (COLDEFS[k] && out.indexOf(k) < 0) out.push(k); });
    return out;
  }
  let cols = sanitizeCols(lsGet("dl.cols", DEFAULT_COLS));
  function setCols(arr) {
    cols = sanitizeCols(arr);
    lsSet("dl.cols", cols);
    if (sort && cols.indexOf(sort.key) < 0) sort = null;
    renderAll();
    if ($("dlColsModal").classList.contains("show")) renderColsModal();
  }

  /* ---------------- filtering ---------------- */

  function inCategory(d, c) {
    switch (c) {
      case "mine": return !!me && d.owner_id === me.id;
      case "open": return isOpen(d);
      case "won": return d.stage === "won";
      case "lost": return d.stage === "lost";
      case "soon": { if (!isOpen(d) || !d.close_date) return false; const n = daysTo(d.close_date); return n >= 0 && n <= 7; }
      default: return true;
    }
  }
  function matches(d, ignoreStage) {
    const t = F.search.trim().toLowerCase();
    if (t) {
      const hay = [d.name, (d.company || {}).name, (d.owner || {}).name].concat((d.contacts || []).map(c => [c.name, c.email, c.phone].join(" "))).join(" ").toLowerCase();
      if (!hay.includes(t)) return false;
    }
    if (!ignoreStage && F.stages.size && !F.stages.has(d.stage)) return false;
    if (F.priority && d.priority !== F.priority) return false;
    if (F.owner !== null && d.owner_id !== F.owner) return false;
    return true;
  }
  function filtered(ignoreStage) {
    let list = deals.filter(d => matches(d, ignoreStage) && inCategory(d, cat));
    const col = sort && COLS.find(c => c.k === sort.key);
    if (col && col.sortv) {
      const dir = sort.dir === "asc" ? 1 : -1;
      list = list.map((d, i) => [d, i]).sort((a, b) => { const x = col.sortv(a[0]), y = col.sortv(b[0]); return (x < y ? -1 : x > y ? 1 : a[1] - b[1]) * dir; }).map(p => p[0]);
    } else list = list.slice().sort((a, b) => b.id - a.id);
    return list;
  }
  const anyFilter = () => !!(F.search || F.stages.size || F.priority || F.owner !== null);

  /* ---------------- popovers ---------------- */

  let popEl = null, popAnchor = null, popOpenedAt = 0;
  function closePop() { if (popEl) { popEl.remove(); popEl = null; popAnchor = null; } }
  function openPop(anchor, html, opts) {
    opts = opts || {};
    if (popEl && popAnchor === anchor) { closePop(); return null; }
    closePop();
    const el = document.createElement("div");
    el.className = "dl-pop";
    if (opts.width) el.style.width = opts.width + "px";
    el.innerHTML = html;
    (anchor.closest(".modal, .offcanvas") || document.body).appendChild(el);
    popEl = el; popAnchor = anchor; popOpenedAt = Date.now();
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
      const lead = it.dot ? `<span class="sdot sg-${it.dot}"></span>` : it.icon ? `<i class="bi bi-${it.icon}"></i>` : "";
      return `<button type="button" class="it ${it.danger ? "danger" : ""}" data-i="${i}">${lead}<span>${esc(it.label)}</span>${it.cnt != null ? `<span class="cnt">${it.cnt}</span>` : ""}${it.checked ? '<i class="bi bi-check2 ck"></i>' : ""}</button>`;
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
  document.addEventListener("mousedown", e => { if (popEl && !popEl.contains(e.target) && !(popAnchor && popAnchor.contains(e.target))) closePop(); });
  document.addEventListener("keydown", e => { if (e.key === "Escape" && popEl) { e.stopPropagation(); closePop(); } }, true);
  window.addEventListener("resize", () => { closePop(); fitHeights(); });
  window.addEventListener("scroll", e => { if (popEl && !popEl.contains(e.target) && Date.now() - popOpenedAt > 250) closePop(); }, true);

  function afterDrawerClosed(fn) {
    const el = $("dlDrawer");
    if (el.classList.contains("show")) {
      el.addEventListener("hidden.bs.offcanvas", function h() { el.removeEventListener("hidden.bs.offcanvas", h); fn(); });
      bootstrap.Offcanvas.getOrCreateInstance(el).hide();
    } else fn();
  }

  /* ---------------- data ---------------- */

  async function loadDeals() {
    const data = await apiFetch(CFG.routes.data);
    loading = false;
    if (data.success) {
      deals = data.deals; me = data.current_admin;
      if (detail) detail = deals.find(d => d.id === detail.id) || null;
    }
    renderAll();
  }
  function putDeal(d) {
    const i = deals.findIndex(x => x.id === d.id);
    if (i >= 0) deals[i] = Object.assign(deals[i], d); else deals.unshift(d);
    return deals[i >= 0 ? i : 0];
  }

  /* ---------------- rendering: chrome ---------------- */

  function renderTabs() {
    const base = deals.filter(d => matches(d));
    document.querySelectorAll("#dlTabs [data-n]").forEach(el => { el.textContent = base.filter(d => inCategory(d, el.dataset.n)).length; });
    document.querySelectorAll("#dlTabs .dl-tab").forEach(t => t.classList.toggle("active", t.dataset.cat === cat));
  }
  function setChip(id, label, value, on) {
    const el = $(id), icon = el.querySelector("i:not(.bi-chevron-down)");
    el.classList.toggle("on", on);
    el.innerHTML = (icon ? icon.outerHTML : "") + `<span>${esc(label)}${on ? ":" : ""}</span>` + (on ? `<span class="v">${esc(value)}</span>` : "") + '<i class="bi bi-chevron-down"></i>';
  }
  function renderToolbar() {
    setChip("dlChipStage", "Deal stage", Array.from(F.stages).map(stLabel).join(", "), F.stages.size > 0);
    setChip("dlChipPrio", "Priority", (PRIOS.find(p => p[0] === F.priority) || [])[1], !!F.priority);
    const o = F.owner === null ? null : CFG.admins.find(a => a.id === F.owner);
    setChip("dlChipOwner", "Deal owner", o ? o.name : "", F.owner !== null);
    $("dlClear").classList.toggle("d-none", !anyFilter());
    $("dlColsBtn").classList.toggle("d-none", view !== "table");
  }
  function renderKpis(list) {
    const open = list.filter(isOpen), won = list.filter(d => d.stage === "won");
    $("kOpen").textContent = moneyShort(open.reduce((s, d) => s + num(d.amount), 0));
    $("kOpenN").textContent = open.length + " deals";
    $("kWeighted").textContent = moneyShort(open.reduce((s, d) => s + num(d.amount) * (d.probability || 0) / 100, 0));
    $("kWon").textContent = moneyShort(won.reduce((s, d) => s + num(d.amount), 0));
    $("kWonN").textContent = won.length + " deals";
  }

  function renderAll() {
    renderTabs();
    renderToolbar();
    const list = filtered(view === "board");
    orderIds = list.map(d => d.id);
    $("dlCount").textContent = list.length;
    renderKpis(list);
    $("dlTableWrap").classList.toggle("d-none", view !== "table");
    $("dlBoard").classList.toggle("d-none", view !== "board");
    document.querySelectorAll(".dl-seg [data-view]").forEach(b => b.classList.toggle("active", b.dataset.view === view));
    if (view === "table") renderTable(list); else renderBoard(list);
    const n = selected.size;
    $("dlBulk").classList.toggle("show", n > 0 && view === "table");
    $("dlBulkCount").textContent = n;
    fitHeights();
  }

  /* ---------------- rendering: table ---------------- */

  function nameCell(d) {
    return `<div class="dl-name"><span class="dl-ic"><i class="bi bi-briefcase"></i></span>
      <span class="dl-name-col"><span class="dl-name-text">${esc(d.name)}</span><span class="dl-name-sub">${esc(money(d.amount) + " · " + stLabel(d.stage))}</span></span></div>`;
  }
  function renderTable(list) {
    const totalPages = Math.max(1, Math.ceil(list.length / pageSize));
    page = Math.min(page, totalPages);
    const start = (page - 1) * pageSize, items = list.slice(start, start + pageSize);

    $("dlThead").innerHTML = `<tr><th class="c-chk"><input type="checkbox" class="form-check-input" id="dlSelAll" aria-label="Select all"></th>
      ${cols.map(k => `<th class="${k === "name" ? "c-name" : ""}" data-col="${k}">${esc(COLDEFS[k].label)}${sort && sort.key === k ? `<i class="bi bi-arrow-${sort.dir === "asc" ? "up" : "down"} srt"></i>` : ""}</th>`).join("")}
      <th class="c-end"><button type="button" class="dl-plus" id="dlAddCol" title="Choose columns" aria-label="Choose columns"><i class="bi bi-plus-lg"></i></button></th></tr>`;
    const allBox = $("dlSelAll"), pageSel = items.filter(d => selected.has(d.id)).length;
    allBox.checked = items.length > 0 && pageSel === items.length;
    allBox.indeterminate = pageSel > 0 && pageSel < items.length;

    const tb = $("dlTbody"), span = cols.length + 2;
    if (loading) {
      tb.innerHTML = Array.from({ length: 7 }, () => `<tr class="dl-skel">${"<td><span></span></td>".repeat(span)}</tr>`).join("");
    } else if (!items.length) {
      tb.innerHTML = `<tr><td colspan="${span}" style="height:auto;max-width:none;white-space:normal"><div class="dl-emptybox">
        <div class="ic"><i class="bi bi-${deals.length ? "funnel" : "briefcase"}"></i></div>
        <h5>${deals.length ? "No deals match" : "No deals yet"}</h5>
        <p>${deals.length ? "Try changing your filters or search." : "Create your first deal and link it to a company or contact."}</p>
        ${deals.length ? '<button class="btn btn-outline-secondary btn-sm" data-act="clear">Clear filters</button>' : '<button class="btn btn-primary btn-sm" data-act="create"><i class="bi bi-plus-lg me-1"></i>Create deal</button>'}
      </div></td></tr>`;
    } else {
      tb.innerHTML = items.map(d => `<tr data-id="${d.id}" class="${selected.has(d.id) ? "sel" : ""}">
        <td class="c-chk"><input type="checkbox" class="form-check-input row-check" data-id="${d.id}" ${selected.has(d.id) ? "checked" : ""} aria-label="Select"></td>
        ${cols.map(k => k === "name" ? `<td class="c-name">${nameCell(d)}</td>` : `<td>${COLDEFS[k].cell(d)}</td>`).join("")}
        <td class="c-end"><button type="button" class="dl-rowbtn" data-act="rowmenu" data-id="${d.id}" aria-label="More"><i class="bi bi-three-dots"></i></button></td></tr>`).join("");
    }

    const from = list.length ? start + 1 : 0, to = Math.min(list.length, start + pageSize);
    $("dlPageInfo").textContent = `${from}–${to} of ${list.length}`;
    $("dlPageSize").value = String(pageSize);
    $("dlFoot").classList.toggle("d-none", loading || !list.length);
    const pages = [`<button type="button" data-page="${page - 1}" ${page <= 1 ? "disabled" : ""} aria-label="Previous"><i class="bi bi-chevron-left"></i></button>`];
    const around = new Set([1, totalPages, page - 1, page, page + 1]);
    let prev = 0;
    for (let p = 1; p <= totalPages; p++) {
      if (!around.has(p)) continue;
      if (p - prev > 1) pages.push('<button type="button" disabled>…</button>');
      pages.push(`<button type="button" data-page="${p}" class="${p === page ? "active" : ""}">${p}</button>`);
      prev = p;
    }
    pages.push(`<button type="button" data-page="${page + 1}" ${page >= totalPages ? "disabled" : ""} aria-label="Next"><i class="bi bi-chevron-right"></i></button>`);
    $("dlPages").innerHTML = totalPages > 1 ? pages.join("") : "";
  }

  /* ---------------- rendering: board ---------------- */

  function renderBoard(list) {
    const board = $("dlBoard"), keys = F.stages.size ? SK.filter(k => F.stages.has(k)) : SK;
    if (loading) { board.innerHTML = keys.map(k => `<div class="dl-col sg-${k}"><div class="dl-col-head"><div class="dl-col-top"><span class="dot"></span><b>${esc(stLabel(k))}</b></div></div><div class="dl-col-body"><div class="dl-colempty">Loading…</div></div></div>`).join(""); return; }
    const sl = board.scrollLeft;
    board.innerHTML = keys.map(k => {
      const items = list.filter(d => d.stage === k), lim = boardLimit[k] || 20;
      const body = items.length
        ? items.slice(0, lim).map(boardCard).join("") + (items.length > lim ? `<button type="button" class="dl-more" data-act="more" data-stage="${k}">Show ${items.length - lim} more</button>` : "")
        : '<div class="dl-colempty">No deals<br>Drag a card here</div>';
      return `<div class="dl-col sg-${k}" data-stage="${k}">
        <div class="dl-col-head"><div class="dl-col-top"><span class="dot"></span><b>${esc(stLabel(k))}</b><span class="n">${items.length}</span>
          ${OPEN.indexOf(k) >= 0 ? `<button type="button" class="dl-iconbtn add" data-act="addIn" data-stage="${k}" title="Add a deal to ${esc(stLabel(k))}" aria-label="Add deal"><i class="bi bi-plus-lg"></i></button>` : ""}</div>
          <div class="dl-col-sum">${moneyShort(items.reduce((s, d) => s + num(d.amount), 0))}${OPEN.indexOf(k) >= 0 ? " · " + (STAGES[k].probability) + "% probability" : ""}</div></div>
        <div class="dl-col-body">${body}</div></div>`;
    }).join("");
    board.scrollLeft = sl;
  }
  function boardCard(d) {
    const c = d.contacts || [];
    return `<div class="dl-card-k" draggable="true" data-id="${d.id}">
      <div class="nm">${esc(d.name)}</div>
      <button type="button" class="dl-rowbtn menu" data-act="cardmenu" data-id="${d.id}" aria-label="More"><i class="bi bi-three-dots"></i></button>
      <div class="amt">${money(d.amount)}</div>
      ${d.company ? `<div class="ln"><i class="bi bi-building"></i><span>${esc(d.company.name)}</span></div>` : ""}
      ${c.length ? `<div class="ln"><i class="bi bi-person"></i><span>${esc(c[0].name)}${c.length > 1 ? " +" + (c.length - 1) : ""}</span></div>` : ""}
      <div class="bt">${d.close_date ? closeChip(d) : ""}${d.priority === "high" ? prioHtml("high") : ""}<span style="margin-left:auto">${d.owner ? avatarHtml(d.owner.name, "sm") : ""}</span></div>
    </div>`;
  }

  function fitHeights() {
    const vh = window.innerHeight;
    if (view === "table") {
      const sc = $("dlScroll"), foot = $("dlFoot").classList.contains("d-none") ? 0 : $("dlFoot").offsetHeight;
      sc.style.maxHeight = Math.max(280, vh - sc.getBoundingClientRect().top - foot - 24) + "px";
    } else {
      const b = $("dlBoard");
      b.style.height = Math.max(360, vh - b.getBoundingClientRect().top - 20) + "px";
    }
  }

  /* ---------------- stage change ---------------- */

  async function applyStage(id, stage, reason) {
    const d = deals.find(x => x.id === id);
    if (!d) return;
    const old = { stage: d.stage, probability: d.probability, lost_reason: d.lost_reason };
    d.stage = stage; d.probability = STAGES[stage].probability; d.lost_reason = stage === "lost" ? reason : null;
    const redraw = () => { renderAll(); if (detail && detail.id === id) renderDrawer(); };
    redraw();
    try {
      const res = await reqJson(url("stage", id), "PATCH", { stage, lost_reason: reason || null });
      if (!res.success) throw new Error(firstErr(res, "Couldn't update the stage."));
      Object.assign(d, res.deal);
      redraw();
      toast(stage === "won" ? "Deal won!" : "Stage: " + stLabel(stage));
    } catch (e) {
      Object.assign(d, old);
      redraw();
      toast(e.message || "Couldn't update the stage.", "danger");
    }
  }
  function changeStage(id, stage, anchor) {
    const d = deals.find(x => x.id === id);
    if (!d || d.stage === stage) return;
    if (stage === "lost") lostPop(anchor, id); else applyStage(id, stage);
  }
  function lostPop(anchor, id) {
    const el = openPop(anchor, `<div class="hd">Why was this deal lost?</div><div class="pad">
      <select class="form-select form-select-sm mb-2" id="dlLostSel">${LOST_REASONS.map(r => `<option>${esc(r)}</option>`).join("")}</select>
      <textarea class="form-control form-control-sm mb-2" id="dlLostTxt" rows="2" placeholder="Details (optional)"></textarea>
      <div class="d-flex gap-2"><button type="button" class="btn btn-sm btn-outline-secondary flex-fill" data-x>Cancel</button><button type="button" class="btn btn-sm btn-danger flex-fill" data-ok>Mark as lost</button></div></div>`, { width: 300 });
    if (!el) return;
    el.addEventListener("click", e => {
      if (e.target.closest("[data-x]")) closePop();
      else if (e.target.closest("[data-ok]")) {
        const r = $("dlLostSel").value, t = $("dlLostTxt").value.trim();
        closePop();
        applyStage(id, "lost", (r + (t ? " — " + t : "")).slice(0, 255));
      }
    });
  }
  function stageMenu(anchor, d) {
    popMenu(anchor, SK.map(k => ({ label: stLabel(k), dot: k, checked: k === d.stage, fn: () => changeStage(d.id, k, anchor) })));
  }

  /* ---------------- delete ---------------- */

  function askDelete(ids) {
    pendingDel = ids.slice();
    $("dlDeleteTitle").textContent = ids.length > 1 ? `Delete ${ids.length} deals?` : "Delete deal?";
    afterDrawerClosed(() => bootstrap.Modal.getOrCreateInstance($("dlDeleteModal")).show());
  }
  async function confirmDelete() {
    const ids = pendingDel.slice();
    if (!ids.length) return;
    const btn = $("dlConfirmDelete");
    btn.disabled = true;
    try {
      const res = ids.length === 1 ? await apiFetch(url("destroy", ids[0]), { method: "DELETE" }) : await reqJson(CFG.routes.bulkDelete, "POST", { ids });
      if (res.success) {
        deals = deals.filter(d => ids.indexOf(d.id) < 0);
        ids.forEach(i => selected.delete(i));
        if (detail && ids.indexOf(detail.id) >= 0) detail = null;
        renderAll();
        toast(res.message || "Deleted", "dark");
      } else toast(firstErr(res, "Couldn't delete."), "danger");
    } catch (e) { /* toast already shown */ }
    btn.disabled = false;
    bootstrap.Modal.getOrCreateInstance($("dlDeleteModal")).hide();
  }

  /* ---------------- toolbar wiring ---------------- */

  function bindToolbar() {
    $("dlSearch").addEventListener("input", debounce(e => { F.search = e.target.value; page = 1; renderAll(); }, 140));
    document.addEventListener("keydown", e => {
      if (e.key === "/" && !/^(INPUT|TEXTAREA|SELECT)$/.test((document.activeElement || {}).tagName || "") && !document.querySelector(".modal.show")) { e.preventDefault(); $("dlSearch").focus(); }
    });
    $("dlClear").addEventListener("click", clearFilters);
    $("dlTabs").addEventListener("click", e => { const t = e.target.closest("[data-cat]"); if (t) { cat = t.dataset.cat; page = 1; renderAll(); } });
    document.querySelector(".dl-seg").addEventListener("click", e => { const b = e.target.closest("[data-view]"); if (b) { view = b.dataset.view; lsSet("dl.view", view); renderAll(); } });
    $("dlPageSize").addEventListener("change", e => { pageSize = parseInt(e.target.value, 10) || 25; lsSet("dl.ps", pageSize); page = 1; renderAll(); });
    $("dlPages").addEventListener("click", e => { const b = e.target.closest("[data-page]"); if (b && !b.disabled) { page = parseInt(b.dataset.page, 10); renderAll(); $("dlScroll").scrollTop = 0; } });
    $("openCreateBtn").addEventListener("click", () => openCreate());
    $("dlColsBtn").addEventListener("click", () => bootstrap.Modal.getOrCreateInstance($("dlColsModal")).show());
    $("dlBulkClear").addEventListener("click", () => { selected.clear(); renderAll(); });
    $("dlBulkDelete").addEventListener("click", () => askDelete(Array.from(selected)));
    $("dlConfirmDelete").addEventListener("click", confirmDelete);

    $("dlChipStage").addEventListener("click", e => {
      const counts = {};
      deals.forEach(d => { if (matches(d, true) && inCategory(d, cat)) counts[d.stage] = (counts[d.stage] || 0) + 1; });
      const refresh = () => {
        if (!popEl) return;
        popEl.querySelectorAll(".it[data-i]").forEach((b, i) => {
          if (i >= SK.length) return;
          const on = F.stages.has(SK[i]), ck = b.querySelector(".ck");
          if (on && !ck) b.insertAdjacentHTML("beforeend", '<i class="bi bi-check2 ck"></i>');
          if (!on && ck) ck.remove();
        });
      };
      popMenu(e.currentTarget, SK.map(k => ({ label: stLabel(k), dot: k, checked: F.stages.has(k), cnt: counts[k] || 0, keep: true, fn: () => { if (F.stages.has(k)) F.stages.delete(k); else F.stages.add(k); page = 1; renderAll(); refresh(); } }))
        .concat([{ sep: true }, { label: "Clear all", icon: "x-circle", fn: () => { F.stages.clear(); renderAll(); } }]));
    });
    $("dlChipPrio").addEventListener("click", e => {
      popMenu(e.currentTarget, [{ label: "Any priority", checked: !F.priority, fn: () => { F.priority = ""; page = 1; renderAll(); } }]
        .concat(PRIOS.map(p => ({ label: p[1], icon: "flag-fill", checked: F.priority === p[0], cnt: deals.filter(d => d.priority === p[0]).length, fn: () => { F.priority = p[0]; page = 1; renderAll(); } }))));
    });
    $("dlChipOwner").addEventListener("click", e => {
      popMenu(e.currentTarget, [{ label: "All owners", checked: F.owner === null, fn: () => { F.owner = null; page = 1; renderAll(); } }]
        .concat(CFG.admins.map(a => ({ label: a.name, icon: "person", checked: F.owner === a.id, cnt: deals.filter(d => d.owner_id === a.id).length, fn: () => { F.owner = a.id; page = 1; renderAll(); } }))));
    });
  }
  function clearFilters() {
    F = { search: "", stages: new Set(), priority: "", owner: null };
    page = 1; $("dlSearch").value = "";
    renderAll();
  }

  /* ---------------- column chooser ---------------- */

  function colsPopover(anchor) {
    const keys = Object.keys(COLDEFS).filter(k => k !== "name");
    const draw = q => {
      const t = (q || "").toLowerCase();
      return keys.filter(k => COLDEFS[k].label.toLowerCase().includes(t)).map(k =>
        `<button type="button" class="it" data-k="${k}"><i class="bi bi-${COLDEFS[k].icon}"></i><span>${esc(COLDEFS[k].label)}</span>${cols.indexOf(k) >= 0 ? '<i class="bi bi-check2 ck"></i>' : ""}</button>`).join("") || '<div class="hd">Nothing found</div>';
    };
    const el = openPop(anchor, `<div class="hd">Show in this table</div><div class="srch"><input type="text" class="form-control form-control-sm" id="dlPopColQ" placeholder="Search properties..."></div><div class="alist" id="dlPopCols">${draw("")}</div><hr><button type="button" class="it" data-edit><i class="bi bi-layout-three-columns"></i><span>Edit / reorder columns…</span></button><button type="button" class="it" data-reset><i class="bi bi-arrow-counterclockwise"></i><span>Reset to default</span></button>`, { width: 290, align: "right" });
    if (!el) return;
    const q = $("dlPopColQ");
    q.focus();
    q.addEventListener("input", () => { $("dlPopCols").innerHTML = draw(q.value); });
    el.addEventListener("click", e => {
      const b = e.target.closest("[data-k]");
      if (b) {
        const k = b.dataset.k;
        setCols(cols.indexOf(k) >= 0 ? cols.filter(c => c !== k) : cols.concat(k));
        const popQ = $("dlPopColQ");
        if (popQ) $("dlPopCols").innerHTML = draw(popQ.value);
        return;
      }
      if (e.target.closest("[data-edit]")) { closePop(); bootstrap.Modal.getOrCreateInstance($("dlColsModal")).show(); }
      if (e.target.closest("[data-reset]")) { setCols(DEFAULT_COLS); const popQ = $("dlPopColQ"); if (popQ) $("dlPopCols").innerHTML = draw(popQ.value); }
    });
  }

  function renderColsModal() {
    const q = ($("dlColsSearch").value || "").toLowerCase();
    $("dlColsAvail").innerHTML = Object.keys(COLDEFS).filter(k => cols.indexOf(k) < 0 && COLDEFS[k].label.toLowerCase().includes(q)).map(k =>
      `<div class="dl-colrow"><i class="bi bi-${COLDEFS[k].icon}"></i><span>${esc(COLDEFS[k].label)}</span><span class="sp"></span><button type="button" data-add="${k}" title="Add" aria-label="Add ${esc(COLDEFS[k].label)}"><i class="bi bi-plus-lg"></i></button></div>`).join("")
      || '<div class="text-muted" style="font-size:13px;padding:10px">All properties are already selected.</div>';
    $("dlColsN").textContent = cols.length;
    $("dlColsSel").innerHTML = cols.map((k, i) => k === "name"
      ? `<div class="dl-colrow sel"><i class="bi bi-lock-fill lock"></i><span>${esc(COLDEFS[k].label)}</span><span class="sp"></span><span class="text-muted" style="font-size:11.5px">Always first</span></div>`
      : `<div class="dl-colrow sel" draggable="true" data-k="${k}"><i class="bi bi-grip-vertical grip"></i><span>${esc(COLDEFS[k].label)}</span><span class="sp"></span>
          <button type="button" data-up="${k}" ${i <= 1 ? "disabled" : ""} aria-label="Move up"><i class="bi bi-arrow-up"></i></button>
          <button type="button" data-down="${k}" ${i >= cols.length - 1 ? "disabled" : ""} aria-label="Move down"><i class="bi bi-arrow-down"></i></button>
          <button type="button" data-rm="${k}" aria-label="Remove"><i class="bi bi-x-lg"></i></button></div>`).join("");
  }
  function bindColsModal() {
    const modal = $("dlColsModal");
    modal.addEventListener("show.bs.modal", () => { $("dlColsSearch").value = ""; renderColsModal(); });
    $("dlColsSearch").addEventListener("input", renderColsModal);
    $("dlColsReset").addEventListener("click", () => setCols(DEFAULT_COLS));
    const move = (k, d) => { const a = cols.slice(), i = a.indexOf(k), j = i + d; if (j < 1 || j >= a.length) return; a.splice(i, 1); a.splice(j, 0, k); setCols(a); };
    modal.addEventListener("click", e => {
      const t = e.target.closest("button");
      if (!t) return;
      if (t.dataset.add) setCols(cols.concat(t.dataset.add));
      else if (t.dataset.rm) setCols(cols.filter(c => c !== t.dataset.rm));
      else if (t.dataset.up) move(t.dataset.up, -1);
      else if (t.dataset.down) move(t.dataset.down, 1);
    });
    let dragK = null;
    modal.addEventListener("dragstart", e => { const r = e.target.closest(".dl-colrow.sel[data-k]"); if (!r) return; dragK = r.dataset.k; r.classList.add("dragging"); e.dataTransfer.effectAllowed = "move"; try { e.dataTransfer.setData("text/plain", dragK); } catch (er) { /* ignore */ } });
    modal.addEventListener("dragover", e => { const r = e.target.closest(".dl-colrow.sel[data-k]"); if (!r || !dragK) return; e.preventDefault(); modal.querySelectorAll(".dropt").forEach(x => x.classList.remove("dropt")); r.classList.add("dropt"); });
    modal.addEventListener("drop", e => {
      const r = e.target.closest(".dl-colrow.sel[data-k]");
      if (!r || !dragK) return;
      e.preventDefault();
      const a = cols.filter(c => c !== dragK), at = a.indexOf(r.dataset.k);
      a.splice(at, 0, dragK); dragK = null; setCols(a);
    });
    modal.addEventListener("dragend", () => { dragK = null; renderColsModal(); });
  }

  /* ---------------- table + board events ---------------- */

  function dealMenu(anchor, id) {
    const d = deals.find(x => x.id === id);
    if (!d) return;
    popMenu(anchor, [{ label: "Open", icon: "box-arrow-up-right", fn: () => openDetail(id) }, { sep: true }, { hd: "Move to" }]
      .concat(SK.filter(k => k !== d.stage).map(k => ({ label: stLabel(k), dot: k, fn: () => changeStage(id, k, anchor) })))
      .concat([{ sep: true }, { label: "Delete", icon: "trash", danger: true, fn: () => askDelete([id]) }]), { align: "right" });
  }

  function bindTable() {
    const tbody = $("dlTbody");
    tbody.addEventListener("click", e => {
      const act = e.target.closest("[data-act]");
      if (act) {
        e.stopPropagation();
        const id = parseInt(act.dataset.id, 10), a = act.dataset.act;
        if (a === "stage") stageMenu(act, deals.find(x => x.id === id));
        else if (a === "rowmenu") dealMenu(act, id);
        else if (a === "clear") clearFilters();
        else if (a === "create") openCreate();
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
      renderAll();
    });
    $("dlThead").addEventListener("click", e => {
      if (e.target.closest("#dlSelAll")) return;
      if (e.target.closest("#dlAddCol")) { colsPopover($("dlAddCol")); return; }
      const th = e.target.closest("th[data-col]");
      if (!th) return;
      const k = th.dataset.col;
      sort = !sort || sort.key !== k ? { key: k, dir: "asc" } : sort.dir === "asc" ? { key: k, dir: "desc" } : null;
      renderAll();
    });
    $("dlThead").addEventListener("change", e => {
      if (e.target.id !== "dlSelAll") return;
      $("dlTbody").querySelectorAll(".row-check").forEach(cb => {
        const id = parseInt(cb.dataset.id, 10);
        if (e.target.checked) selected.add(id); else selected.delete(id);
      });
      renderAll();
    });
  }

  function bindBoard() {
    const board = $("dlBoard");
    board.addEventListener("click", e => {
      const act = e.target.closest("[data-act]");
      if (act) {
        const a = act.dataset.act;
        if (a === "cardmenu") dealMenu(act, parseInt(act.dataset.id, 10));
        else if (a === "addIn") openCreate({ stage: act.dataset.stage });
        else if (a === "more") { boardLimit[act.dataset.stage] = (boardLimit[act.dataset.stage] || 20) + 20; renderAll(); }
        return;
      }
      const card = e.target.closest(".dl-card-k");
      if (card) openDetail(parseInt(card.dataset.id, 10));
    });
    let dragId = null;
    board.addEventListener("dragstart", e => {
      const card = e.target.closest(".dl-card-k");
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
      const col = e.target.closest(".dl-col");
      if (!col || dragId === null) return;
      e.preventDefault();
      board.querySelectorAll(".over").forEach(c => { if (c !== col) c.classList.remove("over"); });
      col.classList.add("over");
    });
    board.addEventListener("drop", e => {
      const col = e.target.closest(".dl-col");
      if (!col || dragId === null) return;
      e.preventDefault();
      const id = dragId; dragId = null;
      col.classList.remove("over");
      changeStage(id, col.dataset.stage, col.querySelector(".dl-col-head"));
    });
  }

  /* ==================== PICKER (contact / company) ====================
     Same flow as the Leads create modal: search an existing record, or "Create new" with the full
     field set (contact: name / email / phone / WhatsApp / job title — company: name / website /
     phone / GST / address) and the duplicate warning. Used by the create modal AND the drawer. */

  function Picker(kind, opts) {
    const isC = kind === "contact", noun = isC ? "contact" : "company";
    const blank = () => isC ? { name: "", email: "", phone: "", whatsapp: "", job_title: "" } : { name: "", website: "", phone: "", gst_number: "", address: "" };
    const S = { mode: "search", q: "", results: null, form: blank(), dupe: null, busy: false };
    let root = null, token = 0, shown = [];
    const changed = () => { if (opts.onMode) opts.onMode(); };
    const exclude = () => (opts.exclude ? opts.exclude() : []);

    function resultRow(x, i) {
      if (isC) {
        const n = x.leads_count || 0;
        return `<button type="button" data-i="${i}">${avatarHtml(x.name, "sm")}<div style="flex:1"><div class="t">${esc(x.name)}</div><div class="s">${esc([x.email, x.phone].filter(Boolean).join(" · ") || "—")}</div>${x.company ? `<div class="s"><i class="bi bi-building me-1"></i>${esc(x.company.name)}</div>` : ""}</div><span class="r">${n} lead${n === 1 ? "" : "s"}</span></button>`;
      }
      return `<button type="button" data-i="${i}"><div class="dl-co-ic" style="width:32px;height:32px;font-size:15px;border-radius:9px"><i class="bi bi-building"></i></div><div style="flex:1"><div class="t">${esc(x.name)}</div><div class="s">${esc([x.website, x.gst_number].filter(Boolean).join(" · ") || "—")}</div></div><span class="r">${x.contacts_count || 0} contacts<br>${x.leads_count || 0} leads</span></button>`;
    }
    function drawResults() {
      const box = root && root.querySelector("[data-res]");
      if (!box) return;
      if (S.results === null) { box.innerHTML = '<div class="none">Loading…</div>'; return; }
      const ex = exclude();
      shown = S.results.filter(x => ex.indexOf(x.id) < 0);
      box.innerHTML = shown.length ? shown.map(resultRow).join("")
        : `<div class="none">${S.q ? `No ${noun} found — create a new one.` : `No ${noun} to show yet — create the first one.`}</div>`;
    }
    async function search() {
      const my = ++token;
      let res;
      try { res = await apiFetch((isC ? CFG.routes.contactsSearch : CFG.routes.companiesSearch) + "?q=" + encodeURIComponent(S.q.trim())); } catch (e) { return; }
      if (my !== token) return;
      S.results = (isC ? res.contacts : res.companies) || [];
      drawResults();
    }
    const dsearch = debounce(search, 220);

    function fieldsHtml() {
      const f = S.form, id = n => `pk-${kind}-${n}`;
      const inp = (key, label, extra, col) => `<div class="${col || "col-md-6"}"><label class="form-label" for="${id(key)}">${label}</label><input class="form-control" id="${id(key)}" data-f="${key}" value="${esc(f[key])}" autocomplete="off" ${extra || ""}></div>`;
      return isC
        ? inp("name", "Full name *", 'placeholder="e.g. Rahul Sharma"', "col-12") + inp("email", "Email", 'type="email" placeholder="rahul@company.com"') + inp("phone", "Phone number", 'inputmode="tel" placeholder="+91 98765 43210"') + inp("whatsapp", "WhatsApp", 'inputmode="tel" placeholder="Leave empty if same as phone"') + inp("job_title", "Job title", 'placeholder="e.g. Purchase Manager"')
        : inp("name", "Company name *", 'placeholder="e.g. Sharma Traders Pvt Ltd"', "col-12") + inp("website", "Website", 'placeholder="www.example.com"') + inp("phone", "Company phone", 'inputmode="tel"') + inp("gst_number", "GST number", "") + inp("address", "Address", "");
    }
    function render() {
      if (!root) return;
      if (S.mode === "new") {
        const dupItem = S.dupe && (isC ? S.dupe.contact : S.dupe.company);
        root.innerHTML = `<div class="dl-newform"><div class="dl-nf-h">New ${noun}</div><div class="row g-3">${fieldsHtml()}</div>
          ${S.dupe ? `<div class="dl-alert"><i class="bi bi-exclamation-triangle-fill"></i><span>${esc(S.dupe.message || "A matching " + noun + " already exists.")}</span>${dupItem ? `<button type="button" class="btn btn-sm btn-warning" data-usedupe>Use ${esc(dupItem.name)}</button>` : ""}</div>` : ""}
          <div class="d-flex align-items-center gap-2 mt-3"><button type="button" class="dl-link p-0" data-back><i class="bi bi-arrow-left me-1"></i>Search existing ${noun}</button>
            <button type="button" class="btn btn-primary btn-sm ms-auto" data-save ${S.busy ? "disabled" : ""}>${S.busy ? '<span class="spinner-border spinner-border-sm me-1"></span>' : ""}Save ${noun}</button></div></div>`;
      } else {
        root.innerHTML = `<div class="dl-psearch"><i class="bi bi-search"></i><input type="text" class="form-control" data-q value="${esc(S.q)}" placeholder="${isC ? "Search by name, phone or email..." : "Search by company name or GST..."}" autocomplete="off"></div>
          <div class="dl-res" data-res></div>
          <button type="button" class="btn btn-outline-primary mt-3" data-new><i class="bi bi-${isC ? "person-plus" : "building-add"} me-1"></i>Create new ${noun}</button>`;
        drawResults();
      }
    }
    function focusFirst() {
      const i = root && root.querySelector(S.mode === "new" ? "[data-f]" : "[data-q]");
      if (i) try { i.focus({ preventScroll: true }); } catch (e) { /* ignore */ }
    }
    function reset() {
      S.mode = "search"; S.q = ""; S.results = null; S.form = blank(); S.dupe = null; S.busy = false;
      changed();
    }
    function pick(x) {
      if (!x) return;
      S.q = ""; S.results = null;
      opts.onPick(x);
    }
    function openNew() {
      const q = S.q.trim(), f = blank();
      if (q) {
        if (isC && q.includes("@")) f.email = q;
        else if (isC && digits(q).length >= 6) f.phone = q;
        else f.name = q;
      }
      S.form = f; S.dupe = null; S.mode = "new";
      render(); focusFirst(); changed();
    }
    async function submit() {
      if (S.busy) return;
      const f = S.form;
      if (!f.name.trim()) { toast(isC ? "Name is required." : "Company name is required.", "danger"); focusFirst(); return; }
      if (isC && !f.phone.trim() && !f.email.trim()) { toast("Add a phone number or an email.", "danger"); const p = root && root.querySelector('[data-f="phone"]'); if (p) p.focus(); return; }
      S.busy = true; render();
      let res;
      try { res = await reqJson(isC ? CFG.routes.contactStore : CFG.routes.companyStore, "POST", f); } catch (e) { S.busy = false; render(); return; }
      S.busy = false;
      const made = isC ? res.contact : res.company;
      if (res.success && made) { reset(); opts.onPick(made); }
      else if (res.duplicate) { S.dupe = res; render(); }
      else { render(); toast(firstErr(res, "Couldn't create the " + noun + "."), "danger"); }
    }
    function mount(el) {
      root = el;
      if (!root) return;
      root.onclick = e => {
        const b = e.target.closest("button");
        if (!b || !root.contains(b)) return;
        if (b.dataset.i !== undefined) pick(shown[+b.dataset.i]);
        else if (b.hasAttribute("data-new")) openNew();
        else if (b.hasAttribute("data-back")) { S.mode = "search"; S.dupe = null; render(); focusFirst(); changed(); }
        else if (b.hasAttribute("data-save")) submit();
        else if (b.hasAttribute("data-usedupe")) { const x = isC ? S.dupe.contact : S.dupe.company; reset(); opts.onPick(x); }
      };
      root.oninput = e => {
        const t = e.target;
        if (t.dataset.q !== undefined) { S.q = t.value; S.results = null; drawResults(); dsearch(); }
        else if (t.dataset.f) { S.form[t.dataset.f] = t.value; if (S.dupe) S.dupe = null; }
      };
      root.onkeydown = e => {
        if (e.key !== "Enter") return;
        const t = e.target;
        if (t.dataset.q !== undefined) {
          e.preventDefault(); e.stopPropagation();
          if (shown[0]) pick(shown[0]); else if (S.results && !S.results.length) openNew();
        } else if (t.dataset.f) { e.preventDefault(); e.stopPropagation(); submit(); }
      };
      render();
      if (S.mode === "search" && S.results === null) search();
      focusFirst();
    }
    return { mount, reset, submit, isNew: () => S.mode === "new" };
  }

  /* ==================== DRAWER (CRM record view) ==================== */

  // inline pickers inside the drawer (no popovers / stacked modals — they fight the offcanvas focus-trap)
  const drPc = Picker("contact", {
    exclude: () => ((detail && detail.contacts) || []).map(c => c.id),
    onPick: c => {
      if (!detail) return;
      const f = { contact_ids: (detail.contacts || []).map(x => x.id).concat(c.id) };
      if (!detail.company && c.company) f.company_id = c.company.id;
      assoc = null; patchDeal(f);
    },
  });
  const drPco = Picker("company", { onPick: c => { assoc = null; patchDeal({ company_id: c.id }); } });

  let drTab = "overview";
  let drCollapsed = lsGet("dl.drcol", {});
  const plural = (n, w) => n + " " + w + (n === 1 ? "" : "s");
  const waLink = p => { const d = digits(p); return d ? "https://wa.me/" + (d.length === 10 ? "91" + d : d) : null; };
  const siteUrl = w => /^https?:\/\//i.test(w) ? w : "https://" + w;
  async function copyText(txt, msg) {
    try { await navigator.clipboard.writeText(txt); toast(msg || "Copied", "dark"); } catch (e) { toast("Couldn't copy.", "danger"); }
  }
  const ageDays = iso => { const t = new Date(iso).getTime(); return isNaN(t) ? null : Math.max(0, Math.floor((Date.now() - t) / 86400000)); };
  const qa = (href, icon, title, cls, ext) => `<a class="dl-qa ${cls || ""} ${href ? "" : "off"}" ${href ? `href="${esc(href)}"` : ""} ${ext && href ? 'target="_blank" rel="noopener"' : ""} title="${esc(title)}" aria-label="${esc(title)}"><i class="bi bi-${icon}"></i></a>`;

  function openDetail(id) {
    const d = deals.find(x => x.id === id);
    if (!d) return;
    detail = d; assoc = null; drTab = "overview";
    closePop();
    renderDrawer();
    bootstrap.Offcanvas.getOrCreateInstance($("dlDrawer")).show();
  }

  function tracker(d) {
    const idx = OPEN.indexOf(d.stage);
    return SK.map(k => {
      const i = OPEN.indexOf(k);
      const cls = k === d.stage ? "cur sg-" + k : (i >= 0 && idx >= 0 && i < idx) ? "past" : "";
      return `<button type="button" class="${cls}" data-stage="${k}">${esc(stLabel(k))}</button>`;
    }).join("");
  }

  function metricsHtml(d) {
    const open = isOpen(d), n = d.close_date ? daysTo(d.close_date) : null, p = d.probability != null ? d.probability : 0;
    const late = open && n !== null && n < 0;
    const closeSub = !d.close_date ? "Click to set" : !open ? (d.stage === "won" ? "Closed – won" : "Closed – lost") : n < 0 ? plural(-n, "day") + " overdue" : n === 0 ? "Due today" : "in " + plural(n, "day");
    const age = d.created_at ? ageDays(d.created_at) : null;
    return `<div class="dl-metrics sg-${d.stage}">
      <button type="button" class="dl-m" data-quick="amount"><small>Amount</small><b>${money(d.amount)}</b><span>${open ? "Expected value" : d.stage === "won" ? "Won value" : "Lost value"}</span></button>
      <button type="button" class="dl-m ${late ? "late" : ""}" data-quick="close_date"><small>Close date</small><b>${d.close_date ? esc(fmtDate(d.close_date)) : "Not set"}</b><span>${esc(closeSub)}</span></button>
      <button type="button" class="dl-m" data-quick="probability"><small>Win probability</small><b>${p}%</b><div class="dl-bar"><i style="width:${Math.min(100, p)}%"></i></div></button>
      <div class="dl-m"><small>${open ? "Weighted value" : "Deal age"}</small><b>${open ? money(num(d.amount) * p / 100) : (age !== null ? plural(age, "day") : "—")}</b><span>${open && age !== null ? "Open for " + plural(age, "day") : "&nbsp;"}</span></div>
    </div>`;
  }

  function renderDrawer() {
    const d = detail;
    if (!d) return;
    const idx = orderIds.indexOf(d.id), prim = (d.contacts || [])[0], open = isOpen(d);
    $("dlDrTop").innerHTML = `
      <div class="dl-dr-bar"><span class="crumb"><i class="bi bi-briefcase"></i>Deal</span>${idx >= 0 ? `<span class="pos">${idx + 1} of ${orderIds.length}</span>` : ""}<span class="sp"></span>
        <button type="button" class="dl-iconbtn" data-dr="prev" ${idx <= 0 ? "disabled" : ""} title="Previous deal (Alt + ↑)" aria-label="Previous deal"><i class="bi bi-chevron-up"></i></button>
        <button type="button" class="dl-iconbtn" data-dr="next" ${idx < 0 || idx >= orderIds.length - 1 ? "disabled" : ""} title="Next deal (Alt + ↓)" aria-label="Next deal"><i class="bi bi-chevron-down"></i></button>
        <button type="button" class="dl-iconbtn" data-dr="copylink" title="Copy link" aria-label="Copy link"><i class="bi bi-link-45deg"></i></button>
        <button type="button" class="dl-iconbtn danger" data-dr="delete" title="Delete" aria-label="Delete"><i class="bi bi-trash"></i></button>
        <button type="button" class="dl-iconbtn" data-bs-dismiss="offcanvas" aria-label="Close"><i class="bi bi-x-lg"></i></button>
      </div>
      <div class="dl-dr-id sg-${d.stage}"><div class="dl-dr-ic"><i class="bi bi-briefcase"></i></div>
        <div class="dl-dr-idc">
          <div class="dl-prop-v hdr" tabindex="0" role="button" data-edit="name" data-kind="text" title="Click to rename" aria-label="Edit deal name">${esc(d.name)}<i class="bi bi-pencil pen"></i></div>
          <div class="dl-dr-sub">${pill(d.stage, true, d.id)}
            ${d.company ? `<button type="button" class="dl-subchip" data-tab="people" title="View company"><i class="bi bi-building"></i>${esc(d.company.name)}</button>` : ""}
            ${d.owner ? `<span class="dl-subchip">${avatarHtml(d.owner.name, "sm")}${esc(d.owner.name)}</span>` : '<span class="dl-subchip"><i class="bi bi-person-dash"></i>Unassigned</span>'}
          </div></div></div>
      <div class="dl-track" id="dlTrack">${tracker(d)}</div>
      <div class="dl-dr-actions">
        ${open
          ? '<button type="button" class="btn btn-success btn-sm" data-dr="won"><i class="bi bi-trophy-fill me-1"></i>Mark won</button><button type="button" class="btn btn-outline-danger btn-sm" data-dr="lost"><i class="bi bi-x-circle me-1"></i>Mark lost</button>'
          : '<button type="button" class="btn btn-outline-primary btn-sm" data-dr="reopen"><i class="bi bi-arrow-counterclockwise me-1"></i>Reopen deal</button>'}
        <span class="sp"></span>
        ${prim ? qa(prim.phone ? "tel:" + prim.phone : null, "telephone", "Call " + prim.name) + qa(waLink(prim.phone), "whatsapp", "WhatsApp " + prim.name, "wa", true) + qa(prim.email ? "mailto:" + prim.email : null, "envelope", "Email " + prim.name) : ""}
      </div>
      ${metricsHtml(d)}`;
    renderBody();
  }

  function renderDrTabs() {
    const d = detail, n = (d.contacts || []).length + (d.company ? 1 : 0);
    $("dlDrTabs").innerHTML = [["overview", "Overview", ""], ["people", "People & company", n ? `<span class="n">${n}</span>` : ""], ["timeline", "Timeline", ""]]
      .map(t => `<button type="button" class="dl-dr-tab ${drTab === t[0] ? "active" : ""}" data-tab="${t[0]}">${t[1]}${t[2]}</button>`).join("");
  }

  function propVal(d, f) {
    switch (f) {
      case "amount": return money(d.amount);
      case "close_date": return d.close_date ? esc(fmtDate(d.close_date)) + (isOpen(d) && daysTo(d.close_date) < 0 ? ' <span class="dl-due late">(overdue)</span>' : "") : "";
      case "probability": return d.probability != null ? `<div class="dl-probv sg-${d.stage}"><b>${d.probability}%</b><div class="dl-bar"><i style="width:${Math.min(100, d.probability)}%"></i></div></div>` : "";
      case "deal_type": return esc((DEAL_TYPES.find(t => t[0] === d.deal_type) || [])[1] || "");
      case "owner_id": return d.owner ? avatarHtml(d.owner.name, "sm") + `<span>${esc(d.owner.name)}</span>` : "";
      default: return esc(d[f]);
    }
  }
  function prop(label, field, kind, ro, htmlOverride) {
    const d = detail, html = htmlOverride != null ? htmlOverride : propVal(d, field);
    const empty = html === "" || html == null;
    return `<div class="dl-prop"><div class="dl-prop-l">${esc(label)}</div>
      <div class="dl-prop-v ${ro ? "ro" : ""}" ${ro ? "" : `tabindex="0" role="button" data-edit="${field}" data-kind="${kind}" aria-label="Edit ${esc(label)}"`}>
        ${empty ? `<span class="ph">${ro ? "—" : "Add " + esc(label.toLowerCase())}</span>` : html}${ro ? "" : '<i class="bi bi-pencil pen"></i>'}</div></div>`;
  }
  const prioRow = d => `<div class="dl-prop"><div class="dl-prop-l">Priority</div><div class="dl-prio-seg">${PRIOS.map(p => `<button type="button" class="pr-${p[0]} ${d.priority === p[0] ? "on" : ""}" data-prio="${p[0]}"><i class="bi bi-flag-fill"></i>${p[1]}</button>`).join("")}</div></div>`;

  function sect(key, icon, title, inner, right) {
    return `<div class="dl-sect ${drCollapsed[key] ? "collapsed" : ""}"><div class="dl-sect-h" data-toggle="${key}" tabindex="0" role="button" aria-label="Toggle ${esc(title)}"><i class="bi bi-${icon}"></i>${title}<span class="sp"></span>${right || ""}<i class="bi bi-chevron-down chev"></i></div><div class="dl-sect-b">${inner}</div></div>`;
  }
  const pickSect = (icon, title) => `<div class="dl-sect"><div class="dl-sect-h"><i class="bi bi-${icon}"></i>${title}<span class="sp"></span><button type="button" class="dl-link" data-dr="assocCancel">Cancel</button></div><div class="dl-pickpad" id="drPick"></div></div>`;

  function bannersHtml(d) {
    if (d.stage === "lost") return `<div class="dl-banner lost"><i class="bi bi-x-octagon-fill"></i><div><b>Deal lost</b>${esc(d.lost_reason || "No reason given")}</div></div>`;
    if (d.stage === "won") return `<div class="dl-banner won"><i class="bi bi-trophy-fill"></i><div><b>Deal won</b>${d.closed_at ? "Closed " + esc(fmtDate(d.closed_at)) : "Congratulations!"}</div></div>`;
    if (d.close_date && daysTo(d.close_date) < 0) return `<div class="dl-banner warn"><i class="bi bi-exclamation-triangle-fill"></i><div><b>Close date has passed</b>It was due ${plural(-daysTo(d.close_date), "day")} ago. Update the date or close the deal.</div><button type="button" class="btn btn-sm btn-warning" data-quick="close_date">Update date</button></div>`;
    return "";
  }

  function bodyOverview() {
    const d = detail;
    return bannersHtml(d)
      + sect("key", "star", "Key information",
          prop("Amount", "amount", "number") + prop("Close date", "close_date", "date") + prop("Probability", "probability", "number") + prioRow(d) + prop("Deal owner", "owner_id", "select"))
      + sect("details", "card-text", "Deal details",
          prop("Deal type", "deal_type", "select") + prop("Source", "source", "select") + prop("Description", "description", "textarea"));
  }

  function bodyPeople() {
    const d = detail, c = d.company, cts = d.contacts || [];
    let h = "";
    if (assoc === "company") h += pickSect("building", c ? "Change company" : "Add company");
    else {
      const inner = c
        ? `<div class="dl-company"><div class="dl-co-ic"><i class="bi bi-building"></i></div><div style="min-width:0;flex:1"><div class="nm">${esc(c.name)}</div>
            <div class="dl-cmeta">${c.website ? `<a href="${esc(siteUrl(c.website))}" target="_blank" rel="noopener"><i class="bi bi-globe2"></i>${esc(c.website)}</a>` : ""}${c.phone ? `<a href="tel:${esc(c.phone)}"><i class="bi bi-telephone"></i>${esc(c.phone)}</a>` : ""}${c.gst_number ? `<button type="button" data-copy="${esc(c.gst_number)}" title="Copy GST number"><i class="bi bi-receipt"></i>${esc(c.gst_number)}<i class="bi bi-copy"></i></button>` : ""}</div>
            ${c.address ? `<div class="dl-addr"><i class="bi bi-geo-alt"></i><span>${esc(c.address)}</span></div>` : ""}</div></div>`
        : `<div class="dl-empty-assoc"><div class="dl-co-ic"><i class="bi bi-building-add"></i></div><div><div class="nm">No company linked</div><small>Link a company to see its GST, phone and address here.</small></div><button type="button" class="btn btn-sm btn-outline-primary ms-auto" data-dr="coChange">Add company</button></div>`;
      h += sect("company", "building", "Company", inner, c ? '<button type="button" class="dl-link" data-dr="coChange">Change</button><button type="button" class="dl-link" style="color:var(--danger)" data-dr="coRemove">Remove</button>' : "");
    }
    if (assoc === "contact") h += pickSect("person-plus", "Add contact");
    else {
      const inner = cts.length ? cts.map((x, i) => `<div class="dl-person">${avatarHtml(x.name)}<div class="pm"><div class="nm">${esc(x.name)}${i === 0 ? '<span class="dl-tag">Primary</span>' : ""}</div>
            ${x.email ? `<small><i class="bi bi-envelope"></i>${esc(x.email)}</small>` : ""}${x.phone ? `<small><i class="bi bi-telephone"></i>${esc(x.phone)}</small>` : ""}${!x.email && !x.phone ? "<small>No contact details</small>" : ""}</div>
          <div class="pa">${qa(x.phone ? "tel:" + x.phone : null, "telephone", "Call " + x.name)}${qa(waLink(x.phone), "whatsapp", "WhatsApp " + x.name, "wa", true)}${qa(x.email ? "mailto:" + x.email : null, "envelope", "Email " + x.name)}
            <button type="button" class="dl-iconbtn danger" data-dr="ctRemove" data-id="${x.id}" title="Remove from deal" aria-label="Remove ${esc(x.name)}"><i class="bi bi-x-lg"></i></button></div></div>`).join("")
        : '<div class="dl-empty-assoc"><div class="dl-co-ic"><i class="bi bi-person-plus"></i></div><div><div class="nm">No contacts linked yet</div><small>Add the people you are dealing with.</small></div><button type="button" class="btn btn-sm btn-outline-primary ms-auto" data-dr="ctAdd">Add contact</button></div>';
      h += sect("contacts", "people", `Contacts <span class="dl-more-c" style="margin-left:0">${cts.length}</span>`, inner, cts.length ? '<button type="button" class="dl-link" data-dr="ctAdd"><i class="bi bi-plus-lg me-1"></i>Add contact</button>' : "");
    }
    return h;
  }

  function bodyTimeline() {
    const d = detail, open = isOpen(d), late = open && d.close_date && daysTo(d.close_date) < 0;
    const ev = [];
    if (d.closed_at) ev.push({ ic: d.stage === "won" ? "trophy-fill" : "x-octagon-fill", cls: d.stage === "won" ? "won" : "lost", t: d.stage === "won" ? "Deal won" : "Deal lost", s: d.stage === "lost" ? d.lost_reason : "", at: d.closed_at });
    ev.push({ ic: "flag", cls: "", t: "Stage: " + stLabel(d.stage), s: (d.probability != null ? d.probability + "% win probability" : "") + (open ? " · " + money(num(d.amount) * (d.probability || 0) / 100) + " weighted" : "") });
    if (d.close_date) ev.push({ ic: "calendar-event", cls: late ? "late" : "mute", t: open ? (late ? "Expected close date passed" : "Expected close") : "Planned close date", s: fmtDate(d.close_date) + (open ? " · " + (daysTo(d.close_date) < 0 ? plural(-daysTo(d.close_date), "day") + " ago" : daysTo(d.close_date) === 0 ? "today" : "in " + plural(daysTo(d.close_date), "day")) : "") });
    if (d.updated_at && d.created_at && d.updated_at !== d.created_at) ev.push({ ic: "pencil", cls: "mute", t: "Last updated", s: "", at: d.updated_at });
    ev.push({ ic: "plus-circle", cls: "", t: "Deal created", s: d.creator ? "by " + d.creator.name : "", at: d.created_at });
    return sect("timeline", "clock-history", "Deal timeline", `<div class="dl-tl">${ev.map(e => `<div class="dl-tl-i"><div class="dl-tl-ic ${e.cls}"><i class="bi bi-${e.ic}"></i></div><div style="min-width:0"><div class="dl-tl-t">${esc(e.t)}</div>${e.s ? `<div class="dl-tl-s">${esc(e.s)}</div>` : ""}</div>${e.at ? `<span class="dl-tl-at" title="${esc(fmtDate(e.at))}">${esc(ago(e.at))}</span>` : ""}</div>`).join("")}</div>`);
  }

  function renderBody() {
    renderDrTabs();
    $("dlDrBody").innerHTML = drTab === "people" ? bodyPeople() : drTab === "timeline" ? bodyTimeline() : bodyOverview();
    if (drTab === "people" && assoc) {
      const box = $("drPick");
      (assoc === "company" ? drPco : drPc).mount(box);
      box.closest(".dl-sect").scrollIntoView({ block: "nearest", behavior: "smooth" });
    }
  }

  /* ---- inline edit ---- */

  function selectOpts(field, cur) {
    let o = field === "priority" ? PRIOS
      : field === "deal_type" ? [["", "— None —"]].concat(DEAL_TYPES)
      : field === "owner_id" ? [["", "— Unassigned —"]].concat(CFG.admins.map(a => [String(a.id), a.name]))
      : [["", "— None —"]].concat(SOURCES.map(s => [s, s]));
    if (cur && !o.some(x => x[0] === cur)) o = o.concat([[cur, cur]]);
    return o;
  }
  function startEdit(cell) {
    if (!detail || cell.classList.contains("editing") || cell.classList.contains("ro")) return;
    const field = cell.dataset.edit, kind = cell.dataset.kind;
    const cur = detail[field] == null ? "" : String(detail[field]);
    let input;
    if (kind === "select") {
      input = document.createElement("select");
      input.className = "form-select form-select-sm";
      input.innerHTML = selectOpts(field, cur).map(o => `<option value="${esc(o[0])}" ${o[0] === cur ? "selected" : ""}>${esc(o[1])}</option>`).join("");
    } else if (kind === "textarea") {
      input = document.createElement("textarea"); input.rows = 4; input.className = "form-control form-control-sm"; input.value = cur;
      input.placeholder = "Requirements, pricing discussion, next steps…  (Ctrl + Enter to save)";
    } else {
      input = document.createElement("input");
      input.type = kind === "number" ? "number" : kind === "date" ? "date" : "text";
      input.className = "form-control form-control-sm"; input.value = cur;
      if (kind === "number") { input.min = "0"; input.step = field === "probability" ? "1" : "any"; if (field === "probability") input.max = "100"; }
    }
    let holder = input;
    if (field === "amount" || field === "probability") {
      holder = document.createElement("div");
      holder.className = "input-group input-group-sm";
      holder.innerHTML = field === "amount" ? '<span class="input-group-text">₹</span>' : "";
      holder.appendChild(input);
      if (field === "probability") holder.insertAdjacentHTML("beforeend", '<span class="input-group-text">%</span>');
    }
    const orig = cell.innerHTML;
    cell.classList.add("editing"); cell.innerHTML = ""; cell.appendChild(holder); input.focus();
    if (input.select && kind !== "select") input.select();
    let done = false;
    const finish = async save => {
      if (done) return;
      done = true;
      cell.classList.remove("editing");
      const v = input.value.trim();
      if (!save || v === cur.trim()) { cell.innerHTML = orig; return; }
      if (field === "name" && !v) { cell.innerHTML = orig; toast("Deal name can't be empty.", "danger"); return; }
      cell.innerHTML = orig; cell.classList.add("dl-saving");
      await patchDeal({ [field]: v === "" ? null : (field === "owner_id" ? parseInt(v, 10) : v) });
      cell.classList.remove("dl-saving");
    };
    input.addEventListener("keydown", e => {
      if (e.key === "Enter" && (kind !== "textarea" || e.ctrlKey || e.metaKey)) { e.preventDefault(); finish(true); }
      else if (e.key === "Escape") { e.preventDefault(); e.stopPropagation(); finish(false); }
    });
    input.addEventListener("blur", () => finish(true));
    if (kind === "select") input.addEventListener("change", () => finish(true));
  }

  async function patchDeal(fields) {
    const d = detail;
    let res;
    try { res = await reqJson(url("patch", d.id), "PATCH", fields); } catch (e) { return false; }
    if (!res.success) { toast(firstErr(res, "Couldn't save."), "danger"); renderDrawer(); return false; }
    Object.assign(d, res.deal);
    renderAll(); renderDrawer();
    toast("Saved", "dark");
    return true;
  }

  function jumpToEdit(field) {
    drTab = "overview"; assoc = null;
    if (drCollapsed.key) { drCollapsed.key = false; lsSet("dl.drcol", drCollapsed); }
    renderBody();
    const c = $("dlDrBody").querySelector(`.dl-prop-v[data-edit="${field}"]`);
    if (c) { c.scrollIntoView({ block: "center", behavior: "smooth" }); startEdit(c); }
  }
  function stepDeal(delta) {
    const idx = orderIds.indexOf(detail.id), nx = orderIds[idx + delta];
    if (idx < 0 || nx === undefined) return;
    detail = deals.find(x => x.id === nx) || detail; assoc = null; renderDrawer();
  }

  function bindDrawer() {
    const dr = $("dlDrawer");
    dr.addEventListener("click", e => {
      if (popEl && popEl.contains(e.target)) return;
      if (!detail) return;
      const st = e.target.closest("#dlTrack [data-stage]");
      if (st) { changeStage(detail.id, st.dataset.stage, st); return; }
      const sp = e.target.closest('[data-act="stage"]');
      if (sp) { stageMenu(sp, detail); return; }
      const tab = e.target.closest("[data-tab]");
      if (tab) { drTab = tab.dataset.tab; assoc = null; renderBody(); return; }
      const q = e.target.closest("[data-quick]");
      if (q) { jumpToEdit(q.dataset.quick); return; }
      const pr = e.target.closest("[data-prio]");
      if (pr) { if (pr.dataset.prio !== detail.priority) patchDeal({ priority: pr.dataset.prio }); return; }
      const cp = e.target.closest("[data-copy]");
      if (cp) { copyText(cp.dataset.copy, "GST number copied"); return; }
      const b = e.target.closest("[data-dr]");
      if (b) {
        const a = b.dataset.dr;
        if (a === "prev") stepDeal(-1);
        else if (a === "next") stepDeal(1);
        else if (a === "copylink") copyText(location.origin + location.pathname + "?deal=" + detail.id, "Link copied");
        else if (a === "delete") askDelete([detail.id]);
        else if (a === "won") changeStage(detail.id, "won", b);
        else if (a === "lost") changeStage(detail.id, "lost", b);
        else if (a === "reopen") popMenu(b, [{ hd: "Reopen deal as" }].concat(OPEN.map(k => ({ label: stLabel(k), dot: k, fn: () => applyStage(detail.id, k) }))));
        else if (a === "coChange") { drPco.reset(); assoc = "company"; drTab = "people"; renderBody(); }
        else if (a === "coRemove") patchDeal({ company_id: null });
        else if (a === "ctAdd") { drPc.reset(); assoc = "contact"; drTab = "people"; renderBody(); }
        else if (a === "assocCancel") { assoc = null; renderBody(); }
        else if (a === "ctRemove") patchDeal({ contact_ids: (detail.contacts || []).map(x => x.id).filter(i => i !== parseInt(b.dataset.id, 10)) });
        return;
      }
      const tg = e.target.closest("[data-toggle]");
      if (tg) { drCollapsed[tg.dataset.toggle] = !drCollapsed[tg.dataset.toggle]; lsSet("dl.drcol", drCollapsed); renderBody(); return; }
      const cell = e.target.closest(".dl-prop-v[data-edit]");
      if (cell) startEdit(cell);
    });
    dr.addEventListener("keydown", e => {
      if ((e.key === "Enter" || e.key === " ") && e.target.matches(".dl-prop-v[data-edit]")) { e.preventDefault(); startEdit(e.target); }
      else if ((e.key === "Enter" || e.key === " ") && e.target.matches("[data-toggle]")) { e.preventDefault(); e.target.click(); }
      else if (e.altKey && (e.key === "ArrowUp" || e.key === "ArrowDown") && detail && !/^(INPUT|TEXTAREA|SELECT)$/.test(e.target.tagName)) { e.preventDefault(); stepDeal(e.key === "ArrowUp" ? -1 : 1); }
    });
    dr.addEventListener("hidden.bs.offcanvas", () => { closePop(); assoc = null; });
  }

  /* ==================== CREATE DEAL: Contact -> Company -> Deal ==================== */

  const STEPS = ["Contact", "Company", "Deal"];
  let CR = null;

  function newCR(stage, keep) {
    const t = new Date(); t.setDate(t.getDate() + 30);
    const cr = Object.assign({
      step: 1, busy: false, contacts: [], company: null,
      name: "", nameTouched: false, amount: "", close: ymd(t), owner: me ? String(me.id) : "",
      stage: OPEN.indexOf(stage) >= 0 ? stage : "new", priority: "medium", type: "", source: "", desc: "",
    }, keep || {});
    cr.pc = Picker("contact", {
      exclude: () => cr.contacts.map(c => c.id),
      onMode: renderFooter,
      onPick: c => {
        cr.contacts.push(c);
        if (!cr.company && c.company) cr.company = c.company;   // the contact already knows its company
        renderCreate();
        toast(c.name + " added", "dark");
      },
    });
    cr.pco = Picker("company", { onMode: renderFooter, onPick: c => { cr.company = c; renderCreate(); } });
    return cr;
  }
  const optsHtml = (list, cur) => list.map(o => `<option value="${esc(o[0])}" ${String(o[0]) === String(cur) ? "selected" : ""}>${esc(o[1])}</option>`).join("");

  function crStep1() {
    const sel = CR.contacts.map(c => `<div class="dl-sel">${avatarHtml(c.name)}<div style="min-width:0"><div class="t">${esc(c.name)}</div><div class="s">${esc([c.email, c.phone].filter(Boolean).join(" · ") || "—")}${c.company ? " · " + esc(c.company.name) : ""}</div></div><button type="button" class="dl-iconbtn x" data-cr="rmContact" data-id="${c.id}" aria-label="Remove ${esc(c.name)}"><i class="bi bi-x-lg"></i></button></div>`).join("");
    return `<div class="dl-pane"><h6>Who is this deal with?</h6><div class="hint">Pick existing contacts or create a new one. Duplicates are caught by phone / email. This step is optional.</div>
      ${sel ? `<div class="dl-sels">${sel}</div><label class="dl-lbl mt-3">Add another contact</label>` : ""}
      <div id="crPickC"></div></div>`;
  }

  function crStep2() {
    const head = `<div class="dl-pane"><h6>Which company?</h6><div class="hint">Optional — skip it for an individual customer.</div>`;
    if (CR.company) {
      const fromContact = CR.contacts.some(c => c.company && c.company.id === CR.company.id);
      return head + `<div class="dl-sel"><div class="dl-co-ic" style="background:#fff"><i class="bi bi-building"></i></div><div style="min-width:0"><div class="t">${esc(CR.company.name)}</div><div class="s">${esc([CR.company.website, CR.company.gst_number].filter(Boolean).join(" · ") || (fromContact ? "Contact's company" : "Selected"))}</div></div><button type="button" class="btn btn-sm btn-outline-secondary x" data-cr="changeCompany">Change</button></div></div>`;
    }
    return head + `<div id="crPickCo"></div></div>`;
  }

  const crForecast = () => { const p = STAGES[CR.stage].probability; return `<i class="bi bi-graph-up"></i><span>Weighted forecast <b>${money(num(CR.amount) * p / 100)}</b> at ${p}% win probability</span>`; };

  function crStep3() {
    const n = CR.contacts.length;
    return `<div class="dl-pane"><h6>Deal details</h6><div class="hint">Name it, pick a stage and add the expected value. Everything stays editable from the deal panel.</div>
      <div class="dl-sumrow">
        ${CR.contacts.length ? `<span class="dl-sumchip">${avatarHtml(CR.contacts[0].name, "sm")}${esc(CR.contacts[0].name)}${n > 1 ? ` <span class="dl-more-c" style="margin:0">+${n - 1}</span>` : ""}</span>` : ""}
        ${CR.company ? `<span class="dl-sumchip"><i class="bi bi-building"></i>${esc(CR.company.name)}</span>` : ""}
        ${!n && !CR.company ? '<span class="dl-sumchip text-muted"><i class="bi bi-link-45deg"></i>Not linked to a contact or company</span>' : ""}
      </div>
      <div class="row g-3">
        <div class="col-12"><label class="dl-lbl" for="cdName">Deal name *</label><input class="form-control" id="cdName" data-c="name" value="${esc(CR.name)}" placeholder="e.g. Sharma Traders - 50 units order" autocomplete="off"></div>
        <div class="col-12"><label class="dl-lbl">Deal stage</label>
          <div class="dl-pick">${OPEN.map(k => `<button type="button" class="sg-${k} ${CR.stage === k ? "on" : ""}" data-cs="${k}"><span class="dot"></span>${esc(stLabel(k))}<small>${STAGES[k].probability}%</small></button>`).join("")}</div></div>
        <div class="col-md-6"><label class="dl-lbl" for="cdAmount">Amount</label>
          <div class="input-group"><span class="input-group-text">₹</span><input class="form-control" id="cdAmount" data-c="amount" type="number" min="0" step="any" inputmode="decimal" value="${esc(CR.amount)}" placeholder="0"></div></div>
        <div class="col-md-6"><label class="dl-lbl" for="cdClose">Expected close date</label><input class="form-control" id="cdClose" data-c="close" type="date" value="${esc(CR.close)}">
          <div class="dl-qchips"><button type="button" data-cd="30">+30 days</button><button type="button" data-cd="60">+60 days</button><button type="button" data-cd="90">+90 days</button></div></div>
        <div class="col-12"><div class="dl-fc" id="cdForecast">${crForecast()}</div></div>
        <div class="col-md-6"><label class="dl-lbl" for="cdOwner">Deal owner</label><select class="form-select" id="cdOwner" data-c="owner">${optsHtml(CFG.admins.map(a => [String(a.id), a.name]), CR.owner)}</select></div>
        <div class="col-md-6"><label class="dl-lbl">Priority</label>
          <div class="dl-pick">${PRIOS.map(p => `<button type="button" class="pr-${p[0]} ${CR.priority === p[0] ? "on" : ""}" data-cp="${p[0]}"><span class="dot"></span>${p[1]}</button>`).join("")}</div></div>
        <div class="col-md-6"><label class="dl-lbl" for="cdType">Deal type</label><select class="form-select" id="cdType" data-c="type">${optsHtml([["", "— None —"]].concat(DEAL_TYPES), CR.type)}</select></div>
        <div class="col-md-6"><label class="dl-lbl" for="cdSource">Source</label><select class="form-select" id="cdSource" data-c="source">${optsHtml([["", "— None —"]].concat(SOURCES.map(x => [x, x])), CR.source)}</select></div>
        <div class="col-12"><label class="dl-lbl" for="cdDesc">Description</label><textarea class="form-control" id="cdDesc" data-c="desc" rows="3" placeholder="Requirements, pricing discussion, next steps...">${esc(CR.desc)}</textarea></div>
      </div></div>`;
  }

  function renderFooter() {
    if (!CR) return;
    const p = CR.step === 1 ? CR.pc : CR.step === 2 && !CR.company ? CR.pco : null;
    const isNew = !!p && p.isNew();
    $("dlCrBack").style.visibility = CR.step > 1 ? "visible" : "hidden";
    $("dlCrBack").disabled = CR.busy;
    const canSkip = !isNew && ((CR.step === 1 && !CR.contacts.length) || (CR.step === 2 && !CR.company));
    $("dlCrSkip").classList.toggle("d-none", !canSkip);
    $("dlCrSkip").textContent = CR.step === 1 ? "Skip contact" : "Skip company";
    $("dlCrAnother").classList.toggle("d-none", CR.step !== 3);
    $("dlCrAnother").disabled = CR.busy;
    const next = $("dlCrNext");
    next.disabled = CR.busy;
    next.innerHTML = CR.busy ? '<span class="spinner-border spinner-border-sm me-1"></span>Please wait'
      : isNew ? `Save ${CR.step === 1 ? "contact" : "company"}`
      : CR.step === 3 ? '<i class="bi bi-check2-circle me-1"></i>Create deal' : "Continue";
  }

  function renderCreate() {
    $("dlSteps").innerHTML = STEPS.map((label, i) => {
      const n = i + 1, st = n < CR.step ? "done" : n === CR.step ? "active" : "";
      return `<div class="dl-step ${st}"><span class="no">${n < CR.step ? '<i class="bi bi-check-lg"></i>' : n}</span><span class="l">${label}</span></div>${i < STEPS.length - 1 ? `<div class="bar ${n < CR.step ? "done" : ""}"></div>` : ""}`;
    }).join("");
    $("dlCreateBody").innerHTML = CR.step === 1 ? crStep1() : CR.step === 2 ? crStep2() : crStep3();
    if (CR.step === 1) CR.pc.mount($("crPickC"));
    else if (CR.step === 2 && !CR.company) CR.pco.mount($("crPickCo"));
    renderFooter();
  }

  function openCreate(opts) {
    CR = newCR((opts || {}).stage);
    renderCreate();
    const m = $("dlCreateModal");
    const focus = () => { m.removeEventListener("shown.bs.modal", focus); const i = $("dlCreateBody").querySelector("[data-q], #cdName"); if (i) i.focus(); };
    m.addEventListener("shown.bs.modal", focus);
    bootstrap.Modal.getOrCreateInstance(m).show();
  }

  function goDealStep() {
    if (!CR.nameTouched && !CR.name.trim()) {
      const base = (CR.company || {}).name || (CR.contacts[0] || {}).name || "";
      if (base) CR.name = base + " - Deal";
    }
    CR.step = 3; renderCreate();
    const i = $("cdName"); if (i) { i.focus(); i.select(); }
  }

  function crNext() {
    if (!CR || CR.busy) return;
    if (CR.step === 1) {
      if (CR.pc.isNew()) { CR.pc.submit(); return; }
      CR.step = 2; renderCreate();
    } else if (CR.step === 2) {
      if (!CR.company && CR.pco.isNew()) { CR.pco.submit(); return; }
      goDealStep();
    } else submitCreate(false);
  }

  async function submitCreate(another) {
    if (!CR || CR.busy) return;
    if (!CR.name.trim()) { toast("Deal name is required.", "danger"); $("cdName").focus(); return; }
    CR.busy = true; renderFooter();
    let res;
    try {
      res = await reqJson(CFG.routes.store, "POST", {
        name: CR.name.trim(), amount: CR.amount === "" ? null : CR.amount, stage: CR.stage, priority: CR.priority,
        deal_type: CR.type || null, source: CR.source || null, close_date: CR.close || null,
        owner_id: CR.owner ? parseInt(CR.owner, 10) : null, company_id: CR.company ? CR.company.id : null,
        contact_ids: CR.contacts.map(c => c.id), description: CR.desc.trim() || null,
      });
    } catch (e) { CR.busy = false; renderFooter(); return; }
    CR.busy = false;
    if (!res.success) { renderFooter(); toast(firstErr(res, "Couldn't create the deal."), "danger"); return; }

    const deal = putDeal(res.deal);
    page = 1; sort = null;
    renderAll();
    if (another) {
      // keep the people / company / owner so a second deal for the same customer is one step
      CR = newCR(CR.stage, { step: 3, owner: CR.owner, company: CR.company, contacts: CR.contacts.slice(), source: CR.source, type: CR.type });
      renderCreate();
      const i = $("cdName"); if (i) i.focus();
      toast("Deal created — add another");
      return;
    }
    const m = $("dlCreateModal");
    m.addEventListener("hidden.bs.modal", function h() { m.removeEventListener("hidden.bs.modal", h); openDetail(deal.id); });
    bootstrap.Modal.getOrCreateInstance(m).hide();
    toast(res.message || "Deal created");
  }

  function bindCreate() {
    const body = $("dlCreateBody");
    const onIn = e => {
      const k = e.target.dataset && e.target.dataset.c;
      if (!k || !CR) return;
      CR[k] = e.target.value;
      if (k === "name") CR.nameTouched = true;
      if (k === "amount" && $("cdForecast")) $("cdForecast").innerHTML = crForecast();
    };
    body.addEventListener("input", onIn);
    body.addEventListener("change", onIn);
    body.addEventListener("keydown", e => { if (e.key === "Enter" && CR && CR.step === 3 && e.target.tagName === "INPUT") { e.preventDefault(); crNext(); } });
    body.addEventListener("click", e => {
      const cs = e.target.closest("[data-cs]");
      if (cs) { CR.stage = cs.dataset.cs; renderCreate(); return; }
      const cp = e.target.closest("[data-cp]");
      if (cp) { CR.priority = cp.dataset.cp; renderCreate(); return; }
      const cd = e.target.closest("[data-cd]");
      if (cd) { const t = new Date(); t.setDate(t.getDate() + parseInt(cd.dataset.cd, 10)); CR.close = ymd(t); $("cdClose").value = CR.close; return; }
      const a = e.target.closest("[data-cr]");
      if (!a) return;
      const act = a.dataset.cr;
      if (act === "rmContact") { CR.contacts = CR.contacts.filter(c => c.id !== parseInt(a.dataset.id, 10)); renderCreate(); }
      else if (act === "changeCompany") { CR.company = null; renderCreate(); }
    });
    $("dlCrNext").addEventListener("click", crNext);
    $("dlCrBack").addEventListener("click", () => { if (CR && CR.step > 1 && !CR.busy) { CR.step--; renderCreate(); } });
    $("dlCrSkip").addEventListener("click", () => {
      if (!CR || CR.busy) return;
      if (CR.step === 1) { CR.step = 2; renderCreate(); }
      else if (CR.step === 2) goDealStep();
    });
    $("dlCrAnother").addEventListener("click", () => submitCreate(true));
    $("dlCreateModal").addEventListener("hidden.bs.modal", closePop);
  }

  /* ---------------- boot ---------------- */

  function openFromQuery() {
    const params = new URLSearchParams(window.location.search), id = params.get("deal");
    if (!id) return;
    openDetail(parseInt(id, 10));
    params.delete("deal");
    window.history.replaceState({}, "", window.location.pathname + (params.toString() ? "?" + params : ""));
  }

  document.addEventListener("DOMContentLoaded", () => {
    bindToolbar();
    bindTable();
    bindBoard();
    bindColsModal();
    bindDrawer();
    bindCreate();
    renderAll();   // skeleton while loading
    loadDeals().then(openFromQuery).catch(() => { loading = false; renderAll(); });
  });
})();
</script>

@endsection