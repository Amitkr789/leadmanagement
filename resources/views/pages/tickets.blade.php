@extends('layouts.app')
@section('title', 'Support tickets | Dalal Adda')

@section('content')

<style>
  /* ==========================================================================
     Support tickets (.tp-*) — list, board and ticket drawer. Same tokens as Leads / Deals.
     ========================================================================== */
  .page-content:has(.tp-wrap) { padding-bottom: 28px; }

  .st-open        { --c: var(--info);    --bg: var(--info-bg); }
  .st-in_progress { --c: var(--primary); --bg: var(--primary-50); }
  .st-waiting     { --c: var(--warning); --bg: var(--warning-bg); }
  .st-resolved    { --c: var(--success); --bg: var(--success-bg); }
  .st-closed      { --c: #6B7280;        --bg: #EEF1F6; }
  .pri-low    { --c: #8A8FA6;        --bg: #EEF1F6; }
  .pri-medium { --c: var(--info);    --bg: var(--info-bg); }
  .pri-high   { --c: var(--warning); --bg: var(--warning-bg); }
  .pri-urgent { --c: var(--danger);  --bg: var(--danger-bg); }

  .tp-pill { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; line-height: 1; padding: 6px 11px; border-radius: 999px; background: var(--bg); color: var(--c); white-space: nowrap; }
  .tp-pill .dot { width: 7px; height: 7px; border-radius: 50%; background: var(--c); }
  button.tp-pill { border: 0; cursor: pointer; transition: filter .12s, box-shadow .12s; }
  button.tp-pill:hover { filter: brightness(.96); box-shadow: 0 0 0 2px rgba(var(--primary-rgb), .12); }
  button.tp-pill .bi { font-size: 10px; opacity: .7; }
  .tp-prio { display: inline-flex; align-items: center; gap: 6px; font-size: 12.5px; font-weight: 700; color: var(--c); white-space: nowrap; }
  .tp-prio .bi { font-size: 11px; }
  .tp-empty { color: #B3B7C7; }
  .tp-sla { display: inline-flex; align-items: center; gap: 6px; font-size: 12.5px; font-weight: 600; white-space: nowrap; color: var(--text); }
  .tp-sla.late { color: var(--danger); font-weight: 700; }
  .tp-sla.soon { color: #B45309; }
  .tp-sla.done { color: var(--text-muted); }
  .tp-link { border: 0; background: transparent; color: var(--primary); font-weight: 600; font-size: 13px; padding: 6px 8px; }
  .tp-link:hover { text-decoration: underline; }
  .tp-ghost { display: inline-flex; align-items: center; gap: 7px; border: 1px solid var(--border); background: var(--card-bg); border-radius: 10px; padding: 7px 13px; font-size: 13px; font-weight: 600; color: var(--text); }
  .tp-ghost:hover { background: var(--primary-50); border-color: var(--primary-100); color: var(--primary); }

  /* ---- header ---- */
  .tp-head { display: flex; align-items: flex-end; gap: 14px; flex-wrap: wrap; margin-bottom: 14px; }
  .tp-head h1 { font-size: 24px; margin: 0; display: flex; align-items: center; gap: 10px; }
  .tp-head h1 .cnt { font-size: 12.5px; font-weight: 700; color: var(--primary); background: var(--primary-50); border-radius: 999px; padding: 4px 11px; letter-spacing: 0; }
  .tp-head p { margin: 4px 0 0; color: var(--text-muted); font-size: 13.5px; }
  .tp-head .grow { flex: 1; min-width: 200px; }
  .tp-seg { display: inline-flex; background: var(--card-bg); border: 1px solid var(--border); border-radius: 11px; padding: 3px; gap: 2px; }
  .tp-seg button { border: 0; background: transparent; padding: 7px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; color: var(--text-muted); display: inline-flex; align-items: center; gap: 7px; }
  .tp-seg button:hover { color: var(--text); }
  .tp-seg button.active { background: var(--primary); color: #fff; box-shadow: 0 2px 6px rgba(var(--primary-rgb), .3); }
  .tp-create { display: inline-flex; align-items: center; gap: 8px; border: 0; border-radius: 11px; padding: 10px 18px; font-weight: 700; font-size: 14px; color: #fff; background: linear-gradient(135deg, var(--primary), var(--primary-light)); box-shadow: 0 6px 16px rgba(var(--primary-rgb), .26); transition: transform .12s, box-shadow .12s; }
  .tp-create:hover { transform: translateY(-1px); box-shadow: 0 10px 22px rgba(var(--primary-rgb), .32); }

  /* ---- KPI strip ---- */
  .tp-kpis { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin-bottom: 14px; }
  .tp-kpi { text-align: left; background: var(--card-bg); border: 1px solid var(--border); border-left: 3px solid var(--c, var(--primary)); border-radius: 12px; padding: 11px 16px; transition: box-shadow .12s, transform .12s; }
  .tp-kpi:hover { box-shadow: var(--shadow); transform: translateY(-1px); }
  .tp-kpi small { display: block; color: var(--text-muted); font-size: 12px; font-weight: 600; }
  .tp-kpi b { font-size: 22px; font-weight: 800; letter-spacing: -.01em; }
  .tp-kpi span { font-size: 12px; color: var(--text-muted); margin-left: 6px; }

  /* ---- tabs / toolbar ---- */
  .tp-tabs { display: flex; gap: 4px; overflow-x: auto; border-bottom: 1px solid var(--border); margin-bottom: 14px; scrollbar-width: none; }
  .tp-tabs::-webkit-scrollbar { display: none; }
  .tp-tab { border: 0; background: transparent; padding: 10px 14px; font-size: 13.5px; font-weight: 600; color: var(--text-muted); white-space: nowrap; border-bottom: 2px solid transparent; margin-bottom: -1px; display: inline-flex; align-items: center; gap: 8px; }
  .tp-tab:hover { color: var(--text); }
  .tp-tab.active { color: var(--primary); border-bottom-color: var(--primary); }
  .tp-tab .n { font-size: 11px; font-weight: 700; background: #EEF0F7; color: var(--text-muted); border-radius: 999px; padding: 2px 8px; }
  .tp-tab.active .n { background: var(--primary-50); color: var(--primary); }
  .tp-tab .n.danger { background: var(--danger-bg); color: var(--danger); }
  .tp-toolbar { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 12px; }
  .tp-search { position: relative; flex: 0 1 320px; min-width: 200px; }
  .tp-search i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); }
  .tp-search input.form-control { padding-left: 38px !important; padding-right: 30px !important; }
  .tp-search kbd { position: absolute; right: 9px; top: 50%; transform: translateY(-50%); font-size: 11px; background: var(--bg); color: var(--text-muted); border: 1px solid var(--border); border-radius: 5px; padding: 0 6px; }
  .tp-chip { display: inline-flex; align-items: center; gap: 6px; border: 1px solid var(--border); background: var(--card-bg); border-radius: 999px; padding: 7px 13px; font-size: 13px; font-weight: 600; color: var(--text); white-space: nowrap; transition: background .12s, border-color .12s; }
  .tp-chip:hover { background: var(--primary-50); border-color: var(--primary-100); }
  .tp-chip .bi-chevron-down { font-size: 10px; color: var(--text-muted); }
  .tp-chip.on { background: var(--primary-50); border-color: var(--primary-100); color: var(--primary); }
  .tp-chip .v { font-weight: 700; max-width: 150px; overflow: hidden; text-overflow: ellipsis; }

  /* ---- table ---- */
  .tp-card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 14px; box-shadow: var(--shadow-sm); overflow: hidden; }
  .tp-scroll { overflow: auto; min-height: 280px; }
  .tp-table { border-collapse: separate; border-spacing: 0; min-width: 100%; width: max-content; }
  .tp-table th { position: sticky; top: 0; z-index: 3; background: #F7F8FC; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); padding: 0 16px; height: 44px; border-bottom: 1px solid var(--border); white-space: nowrap; user-select: none; text-align: left; cursor: pointer; }
  .tp-table th:hover { color: var(--text); }
  .tp-table th .srt { margin-left: 5px; font-size: 10px; color: var(--primary); }
  .tp-table td { height: 58px; padding: 0 16px; border-bottom: 1px solid #F0F1F6; font-size: 13.5px; white-space: nowrap; background: var(--card-bg); max-width: 320px; overflow: hidden; text-overflow: ellipsis; }
  .tp-table tbody tr { cursor: pointer; }
  .tp-table tbody tr:hover td { background: #F8F9FF; }
  .tp-table .c-main { position: sticky; left: 0; z-index: 2; min-width: 320px; box-shadow: 1px 0 0 var(--border); }
  .tp-table th.c-main { z-index: 5; }
  .tp-table .c-end { position: sticky; right: 0; z-index: 2; width: 56px; min-width: 56px; padding: 0 10px; text-align: center; box-shadow: -1px 0 0 var(--border); cursor: default; }
  .tp-table th.c-end { z-index: 5; }
  .tp-main { display: flex; align-items: center; gap: 11px; min-width: 0; }
  .tp-ic { width: 34px; height: 34px; border-radius: 10px; background: var(--c, var(--primary)); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 15px; flex-shrink: 0; }
  .tp-main-col { display: flex; flex-direction: column; min-width: 0; }
  .tp-no { font-size: 11.5px; font-weight: 800; letter-spacing: .04em; color: var(--text-muted); }
  .tp-subj { font-weight: 700; color: var(--primary); overflow: hidden; text-overflow: ellipsis; }
  tr:hover .tp-subj { text-decoration: underline; }
  .tp-msgs { margin-left: 8px; font-size: 11.5px; color: var(--text-muted); font-weight: 600; }
  .tp-ln { display: inline-flex; align-items: center; gap: 7px; background: #F3F4FA; border-radius: 8px; padding: 4px 10px; font-size: 12.5px; font-weight: 600; max-width: 100%; overflow: hidden; text-overflow: ellipsis; color: var(--text); text-decoration: none; }
  .tp-ln i { color: var(--text-muted); }
  a.tp-ln:hover { background: var(--primary-50); color: var(--primary); }
  .tp-own { display: inline-flex; align-items: center; gap: 8px; }
  .tp-unassigned { font-size: 12.5px; color: #B3B7C7; }
  .tp-rowbtn { width: 32px; height: 32px; border-radius: 8px; border: 0; background: transparent; color: var(--text-muted); }
  .tp-rowbtn:hover { background: var(--primary-50); color: var(--primary); }
  .tp-foot { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; padding: 10px 16px; border-top: 1px solid var(--border); background: #FBFBFE; font-size: 13px; color: var(--text-muted); }
  .tp-foot select { width: auto; display: inline-block; font-size: 13px; padding: 4px 28px 4px 10px; }
  .tp-pages { display: flex; gap: 4px; margin-left: auto; }
  .tp-pages button { min-width: 32px; height: 32px; border-radius: 8px; border: 1px solid var(--border); background: #fff; font-size: 13px; font-weight: 600; color: var(--text); }
  .tp-pages button:hover:not(:disabled) { background: var(--primary-50); }
  .tp-pages button.active { background: var(--primary); color: #fff; border-color: var(--primary); }
  .tp-pages button:disabled { opacity: .4; }
  .tp-skel td > span { display: block; height: 12px; border-radius: 6px; background: linear-gradient(90deg, #F0F1F6, #E7E9F2, #F0F1F6); background-size: 200% 100%; animation: tpShimmer 1.2s infinite; width: 70%; }
  @keyframes tpShimmer { from { background-position: 200% 0; } to { background-position: -200% 0; } }
  .tp-emptybox { text-align: center; padding: 64px 20px; }
  .tp-emptybox .ic { width: 64px; height: 64px; border-radius: 20px; background: var(--primary-50); color: var(--primary); font-size: 28px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 14px; }
  .tp-emptybox h5 { margin: 0 0 6px; }
  .tp-emptybox p { color: var(--text-muted); margin: 0 0 16px; font-size: 13.5px; }

  /* ---- board ---- */
  .tp-board { display: flex; gap: 14px; overflow-x: auto; align-items: flex-start; min-height: 360px; padding-bottom: 10px; scroll-snap-type: x proximity; }
  .tp-col { flex: 0 0 300px; width: 300px; max-height: 100%; display: flex; flex-direction: column; background: #F1F2F9; border-radius: 14px; scroll-snap-align: start; border-top: 3px solid var(--c); }
  .tp-col.over { outline: 2px dashed var(--primary-light); outline-offset: -2px; background: var(--primary-50); }
  .tp-col-head { display: flex; align-items: center; gap: 8px; padding: 12px 14px 8px; }
  .tp-col-head b { font-size: 13.5px; }
  .tp-col-head .n { font-size: 11.5px; font-weight: 700; background: #fff; color: var(--text-muted); border-radius: 999px; padding: 2px 9px; }
  .tp-col-head .dot { width: 9px; height: 9px; border-radius: 50%; background: var(--c); }
  .tp-col-body { overflow-y: auto; padding: 4px 10px 12px; display: flex; flex-direction: column; gap: 9px; min-height: 70px; flex: 1; }
  .tp-card-k { background: #fff; border: 1px solid var(--border); border-radius: 12px; padding: 12px 13px; box-shadow: var(--shadow-sm); cursor: pointer; transition: box-shadow .12s, transform .12s; border-left: 3px solid var(--pc, var(--border)); }
  .tp-card-k:hover { box-shadow: var(--shadow); transform: translateY(-1px); }
  .tp-card-k.dragging { opacity: .35; }
  .tp-card-k .nm { font-weight: 700; font-size: 13.5px; margin-top: 3px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
  .tp-card-k .ln { margin-top: 8px; }
  .tp-card-k .bt { display: flex; align-items: center; gap: 10px; margin-top: 10px; }
  .tp-colempty { text-align: center; color: #A7ABC2; font-size: 12.5px; padding: 18px 8px; }
  .tp-more { border: 1px dashed #C9CDDD; background: transparent; color: var(--text-muted); border-radius: 10px; padding: 8px; font-size: 12.5px; font-weight: 600; }
  .tp-more:hover { background: #fff; color: var(--primary); }

  /* ---- popover ---- */
  .tp-pop { position: fixed; z-index: 2100; background: #fff; border: 1px solid var(--border); border-radius: 14px; box-shadow: var(--shadow-lg); min-width: 200px; max-width: min(360px, calc(100vw - 20px)); max-height: min(420px, calc(100vh - 20px)); overflow: auto; padding: 6px; animation: tpPop .12s ease-out; }
  @keyframes tpPop { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: none; } }
  .tp-pop .it { display: flex; align-items: center; gap: 10px; width: 100%; border: 0; background: transparent; text-align: left; padding: 8px 10px; border-radius: 8px; font-size: 13.5px; color: var(--text); }
  .tp-pop .it:hover, .tp-pop .it:focus-visible { background: var(--primary-50); outline: 0; }
  .tp-pop .it.danger { color: var(--danger); }
  .tp-pop .it.danger:hover { background: var(--danger-bg); }
  .tp-pop .it .ck { margin-left: auto; color: var(--primary); }
  .tp-pop .it > i:first-child { width: 16px; text-align: center; color: var(--text-muted); }
  .tp-pop .it .sdot { width: 10px; height: 10px; border-radius: 50%; background: var(--c); flex-shrink: 0; }
  .tp-pop .it .cnt { margin-left: auto; font-size: 11px; font-weight: 700; background: #EEF0F7; color: var(--text-muted); border-radius: 999px; padding: 2px 8px; }
  .tp-pop hr { margin: 5px 4px; opacity: .6; }
  .tp-pop .hd { font-size: 11px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: var(--text-muted); padding: 8px 10px 4px; }

  /* ---- drawer ---- */
  .tp-drawer { width: 660px !important; max-width: 100vw; }
  .tp-drawer .offcanvas-header { display: none; }
  .tp-drawer .offcanvas-body { padding: 0; display: flex; flex-direction: column; overflow: hidden; background: var(--bg); }
  .tp-dr-top { padding: 16px 22px 0; background: linear-gradient(180deg, var(--primary-50), #fff 92%); flex-shrink: 0; }
  .tp-dr-bar { display: flex; align-items: center; gap: 2px; margin-bottom: 10px; }
  .tp-dr-bar .no { font-size: 12px; font-weight: 800; letter-spacing: .05em; color: var(--primary); background: #fff; border: 1px solid var(--primary-100); border-radius: 999px; padding: 3px 11px; }
  .tp-dr-bar .sp { flex: 1; }
  .tp-iconbtn { width: 34px; height: 34px; border-radius: 50%; border: 0; background: transparent; color: var(--text-muted); display: inline-flex; align-items: center; justify-content: center; transition: background .12s, color .12s; flex-shrink: 0; text-decoration: none; }
  .tp-iconbtn:hover { background: #fff; color: var(--text); box-shadow: var(--shadow-sm); }
  .tp-iconbtn.danger:hover { background: var(--danger-bg); color: var(--danger); }
  .tp-dr-sub { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; margin-top: 10px; }
  .tp-dr-sub .tp-chipb { display: inline-flex; align-items: center; gap: 7px; border: 1px solid var(--border); background: #fff; border-radius: 999px; padding: 4px 11px; font-size: 12.5px; font-weight: 600; color: var(--text); text-decoration: none; max-width: 100%; }
  .tp-dr-sub .tp-chipb .avatar { width: 20px; height: 20px; font-size: 9px; margin-left: -5px; }
  .tp-dr-sub .tp-chipb > i { color: var(--text-muted); }
  .tp-dr-sub button.tp-chipb:hover, .tp-dr-sub a.tp-chipb:hover { background: var(--primary-50); border-color: var(--primary-100); color: var(--primary); }
  .tp-dr-sub .tp-chipb.late { color: var(--danger); border-color: #F3C4C4; background: var(--danger-bg); }
  .tp-track { display: flex; gap: 3px; margin-top: 16px; overflow-x: auto; scrollbar-width: none; }
  .tp-track::-webkit-scrollbar { display: none; }
  .tp-track button { flex: 1 0 auto; min-width: 84px; border: 0; background: #E9EBF4; color: var(--text-muted); font-size: 12px; font-weight: 700; padding: 9px 14px 9px 20px; clip-path: polygon(0 0, calc(100% - 10px) 0, 100% 50%, calc(100% - 10px) 100%, 0 100%, 10px 50%); transition: filter .12s; white-space: nowrap; }
  .tp-track button:first-child { padding-left: 14px; clip-path: polygon(0 0, calc(100% - 10px) 0, 100% 50%, calc(100% - 10px) 100%, 0 100%); }
  .tp-track button:hover { filter: brightness(.95); }
  .tp-track button.past { background: var(--primary-100); color: var(--primary); }
  .tp-track button.cur { background: var(--c); color: #fff; }
  .tp-dr-act { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; margin: 14px 0 14px; }
  .tp-dr-act .btn { font-weight: 700; border-radius: 10px; }
  .tp-dr-act .sp { flex: 1; }
  .tp-qa { display: inline-flex; align-items: center; gap: 7px; border: 1px solid var(--border); background: #fff; border-radius: 999px; padding: 5px 12px; font-size: 12.5px; font-weight: 700; color: var(--text); text-decoration: none; transition: all .12s; }
  .tp-qa i { color: var(--primary); }
  .tp-qa:hover { background: var(--primary); border-color: var(--primary); color: #fff; }
  .tp-qa:hover i { color: #fff; }
  .tp-qa.wa i { color: var(--success); } .tp-qa.wa:hover { background: var(--success); border-color: var(--success); }
  .tp-qa.off { opacity: .4; pointer-events: none; }
  .tp-dr-tabs { display: flex; gap: 2px; margin: 0 -22px; padding: 0 22px; border-top: 1px solid var(--border); background: #fff; }
  .tp-dr-tab { border: 0; background: transparent; padding: 11px 14px; font-weight: 700; font-size: 13.5px; color: var(--text-muted); border-bottom: 2px solid transparent; margin-bottom: -1px; display: inline-flex; gap: 7px; align-items: center; }
  .tp-dr-tab:hover { color: var(--text); }
  .tp-dr-tab.active { color: var(--primary); border-bottom-color: var(--primary); }
  .tp-dr-tab .n { font-size: 11px; background: #EEF0F7; border-radius: 999px; padding: 1px 7px; color: var(--text-muted); }
  .tp-dr-body { flex: 1; overflow-y: auto; padding: 18px 22px 24px; border-top: 1px solid var(--border); }
  .tp-sect { background: #fff; border: 1px solid var(--border); border-radius: 14px; margin-bottom: 14px; overflow: hidden; }
  .tp-sect-h { display: flex; align-items: center; gap: 8px; padding: 12px 16px; font-weight: 700; font-size: 13.5px; border-bottom: 1px solid #F0F1F6; }
  .tp-sect-h > i:first-child { color: var(--primary); }
  .tp-sect-h .sp { flex: 1; }

  /* conversation */
  .tp-msg { display: flex; gap: 11px; margin-bottom: 14px; }
  .tp-msg .tp-bub { background: #fff; border: 1px solid var(--border); border-radius: 4px 14px 14px 14px; padding: 11px 14px; min-width: 0; flex: 1; box-shadow: var(--shadow-sm); }
  .tp-msg.reply .tp-bub { background: var(--primary-50); border-color: var(--primary-100); }
  .tp-msg.note .tp-bub { background: #FFF8E6; border-color: #F6E3A6; }
  .tp-mh { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; font-size: 12.5px; color: var(--text-muted); }
  .tp-mh b { color: var(--text); font-size: 13px; }
  .tp-mh .tag { font-size: 10.5px; font-weight: 800; border-radius: 999px; padding: 2px 8px; background: #fff; color: var(--text-muted); text-transform: uppercase; letter-spacing: .04em; }
  .tp-msg.note .tp-mh .tag { background: #F6E3A6; color: #7A5A00; }
  .tp-msg.reply .tp-mh .tag { background: var(--primary); color: #fff; }
  .tp-mh .at { margin-left: auto; }
  .tp-mb { font-size: 13.5px; margin-top: 6px; white-space: pre-wrap; word-break: break-word; }
  .tp-mf { font-size: 12px; color: var(--text-muted); margin-top: 8px; }
  .tp-sys { display: flex; align-items: center; gap: 8px; justify-content: center; font-size: 12px; color: var(--text-muted); margin: 4px 0 14px; text-align: center; }
  .tp-sys::before, .tp-sys::after { content: ""; flex: 1; height: 1px; background: #E6E8F2; max-width: 60px; }
  .tp-sys span.at { opacity: .75; }
  .tp-compose { flex-shrink: 0; background: #fff; border-top: 1px solid var(--border); padding: 12px 22px 14px; }
  .tp-ctabs { display: inline-flex; background: #F3F4FA; border-radius: 10px; padding: 3px; gap: 2px; margin-bottom: 10px; }
  .tp-ctabs button { border: 0; background: transparent; padding: 5px 12px; border-radius: 8px; font-size: 12.5px; font-weight: 700; color: var(--text-muted); display: inline-flex; gap: 6px; align-items: center; }
  .tp-ctabs button.on { background: #fff; color: var(--primary); box-shadow: var(--shadow-sm); }
  .tp-ctabs button.on.note { color: #7A5A00; }
  .tp-compose textarea.note { background: #FFFBEE; border-color: #F6E3A6; }
  .tp-cfoot { display: flex; align-items: center; gap: 8px; margin-top: 10px; flex-wrap: wrap; }
  .tp-cfoot .hint { font-size: 12px; color: var(--text-muted); }
  .tp-cfoot .sp { flex: 1; }

  /* details */
  .tp-prop { display: grid; grid-template-columns: 130px 1fr; gap: 10px; align-items: center; padding: 3px 12px 3px 16px; min-height: 46px; }
  .tp-prop + .tp-prop { border-top: 1px solid #F6F7FB; }
  .tp-prop-l { font-size: 12.5px; color: var(--text-muted); font-weight: 600; }
  .tp-prop-v { display: flex; align-items: center; gap: 8px; min-height: 34px; padding: 4px 10px; margin: 0 -10px; border-radius: 8px; border: 1px solid transparent; cursor: text; font-size: 13.5px; word-break: break-word; }
  .tp-prop-v:hover { background: #F6F7FD; border-color: var(--border); }
  .tp-prop-v .pen { margin-left: auto; opacity: 0; color: var(--text-muted); font-size: 12px; }
  .tp-prop-v:hover .pen, .tp-prop-v:focus-visible .pen { opacity: 1; }
  .tp-prop-v:focus-visible { outline: 2px solid var(--primary-light); }
  .tp-prop-v.ro { cursor: default; }
  .tp-prop-v.ro:hover { background: transparent; border-color: transparent; }
  .tp-prop-v .ph { color: #B3B7C7; }
  .tp-prop-v.editing { background: #fff; border-color: var(--primary-light); box-shadow: 0 0 0 3px rgba(var(--primary-rgb), .1); }
  .tp-prop-v input, .tp-prop-v select, .tp-prop-v textarea { width: 100%; }
  .tp-prop-v.hdr { font-size: 20px; font-weight: 800; letter-spacing: -.01em; line-height: 1.25; min-height: 0; padding: 3px 10px; margin: -3px -10px 0; }
  .tp-prop-v.hdr input { font-size: 17px; font-weight: 800; }
  .tp-saving { opacity: .55; pointer-events: none; }
  .tp-prio-seg { display: flex; gap: 6px; flex-wrap: wrap; padding: 4px 0; }
  .tp-prio-seg button { border: 1.5px solid var(--border); background: #fff; border-radius: 999px; padding: 4px 11px; font-size: 12.5px; font-weight: 700; color: var(--text-muted); display: inline-flex; align-items: center; gap: 6px; transition: all .12s; }
  .tp-prio-seg button:hover { border-color: var(--c); color: var(--c); }
  .tp-prio-seg button.on { border-color: var(--c); background: var(--bg); color: var(--c); }
  .tp-person { display: flex; gap: 12px; align-items: center; padding: 12px 16px; }
  .tp-person + .tp-person { border-top: 1px solid #F0F1F6; }
  .tp-person .ic { width: 38px; height: 38px; border-radius: 11px; background: var(--primary-50); color: var(--primary); font-size: 17px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
  .tp-person .t { font-weight: 700; font-size: 13.5px; }
  .tp-person .s { font-size: 12px; color: var(--text-muted); }
  .tp-person .grow { min-width: 0; flex: 1; }
  .tp-none { padding: 24px 18px; text-align: center; color: var(--text-muted); font-size: 13px; }

  .avatar-stack { display: flex; align-items: center; }

  @media (max-width: 991.98px) { .tp-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
  @media (max-width: 767.98px) {
    .tp-table .c-main { position: static; box-shadow: none; min-width: 240px; }
    .tp-head h1 { font-size: 21px; }
    .tp-head .tp-seg { order: 3; }
    .tp-create span.t { display: none; }
    .tp-create { padding: 10px 14px; }
    .tp-search { flex: 1 1 100%; }
    .tp-col { flex-basis: 84vw; width: 84vw; }
    .tp-prop { grid-template-columns: 1fr; gap: 0; padding-top: 8px; padding-bottom: 8px; }
    .tp-dr-top, .tp-dr-body, .tp-compose { padding-left: 16px; padding-right: 16px; }
    .tp-dr-tabs { margin: 0 -16px; padding: 0 16px; }
  }
  @media (prefers-reduced-motion: reduce) { .tp-pop, .tp-skel td > span { animation: none; } }
</style>

<div class="page-content">
<div class="tp-wrap" id="tpWrap">

  <!-- HEADER -->
  <div class="tp-head">
    <div class="grow">
      <h1>Support tickets <span class="cnt" id="tpCount">0</span></h1>
      <p>Track customer issues linked to your deals, leads and contacts — and resolve them on time.</p>
    </div>
    <div class="tp-seg" role="tablist" aria-label="View">
      <button type="button" data-view="table" class="active"><i class="bi bi-table"></i><span>Table</span></button>
      <button type="button" data-view="board"><i class="bi bi-kanban"></i><span>Board</span></button>
    </div>
    <button type="button" class="tp-create" id="tpNewBtn"><i class="bi bi-plus-lg"></i><span class="t">New ticket</span></button>
  </div>

  <!-- KPIs -->
  <div class="tp-kpis" id="tpKpis"></div>

  <!-- TABS -->
  <div class="tp-tabs" id="tpTabs">
    <button type="button" class="tp-tab active" data-cat="all">All tickets <span class="n" data-n="all">0</span></button>
    <button type="button" class="tp-tab" data-cat="mine">My tickets <span class="n" data-n="mine">0</span></button>
    <button type="button" class="tp-tab" data-cat="open">Open <span class="n" data-n="open">0</span></button>
    <button type="button" class="tp-tab" data-cat="unassigned">Unassigned <span class="n" data-n="unassigned">0</span></button>
    <button type="button" class="tp-tab" data-cat="overdue">Overdue <span class="n danger" data-n="overdue">0</span></button>
    <button type="button" class="tp-tab" data-cat="resolved">Resolved <span class="n" data-n="resolved">0</span></button>
  </div>

  <!-- TOOLBAR -->
  <div class="tp-toolbar">
    <div class="tp-search">
      <i class="bi bi-search"></i>
      <input type="text" class="form-control" id="tpSearch" placeholder="Search ticket, customer, deal…" autocomplete="off">
      <kbd>/</kbd>
    </div>
    <button type="button" class="tp-chip" id="tpChipStatus"><span>Status</span><i class="bi bi-chevron-down"></i></button>
    <button type="button" class="tp-chip" id="tpChipPrio"><span>Priority</span><i class="bi bi-chevron-down"></i></button>
    <button type="button" class="tp-chip" id="tpChipCat"><span>Category</span><i class="bi bi-chevron-down"></i></button>
    <button type="button" class="tp-chip" id="tpChipOwner"><i class="bi bi-person"></i><span>Assignee</span><i class="bi bi-chevron-down"></i></button>
    <button type="button" class="tp-link d-none" id="tpClear">Clear all</button>
  </div>

  <!-- TABLE -->
  <div class="tp-card" id="tpTableWrap">
    <div class="tp-scroll" id="tpScroll">
      <table class="tp-table">
        <thead id="tpThead"></thead>
        <tbody id="tpTbody"></tbody>
      </table>
    </div>
    <div class="tp-foot" id="tpFoot">
      <span id="tpPageInfo"></span>
      <span>Rows: <select class="form-select form-select-sm" id="tpPageSize"><option>10</option><option>25</option><option>50</option><option>100</option></select></span>
      <div class="tp-pages" id="tpPages"></div>
    </div>
  </div>

  <!-- BOARD -->
  <div class="tp-board d-none" id="tpBoard"></div>

</div>
</div>

<!-- TICKET DRAWER -->
<div class="offcanvas offcanvas-end tp-drawer" tabindex="-1" id="tpDrawer" aria-label="Ticket details">
  <div class="offcanvas-header"><h5 class="offcanvas-title">Ticket</h5></div>
  <div class="offcanvas-body">
    <div class="tp-dr-top">
      <div id="tpDrTop"></div>
      <div class="tp-dr-tabs" id="tpDrTabs"></div>
    </div>
    <div class="tp-dr-body" id="tpDrBody"></div>
    <div class="tp-compose" id="tpCompose">
      <div class="tp-ctabs" id="tpCTabs">
        <button type="button" class="on" data-cm="reply"><i class="bi bi-reply"></i>Reply</button>
        <button type="button" data-cm="note"><i class="bi bi-lock"></i>Internal note</button>
      </div>
      <textarea class="form-control" id="tpMsg" rows="3"></textarea>
      <div class="tp-cfoot">
        <span class="hint" id="tpCHint"></span><span class="sp"></span>
        <button type="button" class="btn btn-outline-success btn-sm" id="tpSendResolve">Send &amp; resolve</button>
        <button type="button" class="btn btn-primary btn-sm" id="tpSend">Send reply</button>
      </div>
    </div>
  </div>
</div>

<!-- DELETE CONFIRM -->
<div class="modal fade" id="tpDeleteModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-content-compact">
    <div class="modal-content modal-content-danger">
      <div class="modal-header">
        <h5 class="modal-title">Delete ticket?</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body"><p class="mb-0">The ticket and its whole conversation will be permanently deleted. This can't be undone.</p></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="tpConfirmDelete">Delete</button>
      </div>
    </div>
  </div>
</div>

{{-- the reusable create-ticket modal — this is all it takes to use it on any page --}}
@include('partials.ticket-modal')

<script>
  window.TICKET_PAGE = {
    admins: @json($admins),
    tk: @json(\App\Models\SupportTicket::config()),
    csrfToken: '{{ csrf_token() }}',
    routes: {
      data: '{{ route('tickets.data') }}',
      show: '{{ url('admin/tickets') }}/__ID__',
      patch: '{{ url('admin/tickets') }}/__ID__',
      destroy: '{{ url('admin/tickets') }}/__ID__',
      message: '{{ url('admin/tickets') }}/__ID__/messages',
      deal: '{{ url('admin/deals') }}/__ID__',
      lead: '{{ url('admin/leads') }}?lead=__ID__',
    }
  };

(function () {
  "use strict";

  const CFG = window.TICKET_PAGE, CSRF = CFG.csrfToken;
  const ST = CFG.tk.statuses, SK = Object.keys(ST), OPEN = CFG.tk.openStatuses, PRI = CFG.tk.priorities, CATS = CFG.tk.categories, CHANNELS = CFG.tk.channels;
  const AVATAR_COLORS = ["#4338CA", "#0891B2", "#B45309", "#16A34A", "#DC2626", "#6D28D9", "#0D9488"];
  const $ = id => document.getElementById(id);
  const DASH = '<span class="tp-empty">—</span>';

  /* ---------------- helpers ---------------- */

  const esc = s => String(s == null ? "" : s).replace(/[&<>"']/g, c => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));
  function lsGet(k, d) { try { const v = localStorage.getItem(k); return v == null ? d : JSON.parse(v); } catch (e) { return d; } }
  function lsSet(k, v) { try { localStorage.setItem(k, JSON.stringify(v)); } catch (e) { /* private mode */ } }
  const initials = n => (n || "?").trim().split(/\s+/).slice(0, 2).map(p => p[0]).join("").toUpperCase() || "?";
  function colorFor(name) { let h = 0; for (let i = 0; i < (name || "").length; i++) h = name.charCodeAt(i) + ((h << 5) - h); return AVATAR_COLORS[Math.abs(h) % AVATAR_COLORS.length]; }
  const avatarHtml = (name, size) => `<div class="avatar${size === "lg" ? " avatar-lg" : size === "sm" ? " avatar-sm" : ""}" style="background:${colorFor(name)}">${esc(initials(name))}</div>`;
  const debounce = (fn, ms) => { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; };
  const digits = n => (n || "").replace(/\D/g, "");
  const waLink = p => { const d = digits(p); return d ? "https://wa.me/" + (d.length === 10 ? "91" + d : d) : null; };
  const ymd = dt => dt.getFullYear() + "-" + String(dt.getMonth() + 1).padStart(2, "0") + "-" + String(dt.getDate()).padStart(2, "0");
  const toLocalInput = iso => { const d = new Date(iso); return !iso || isNaN(d) ? "" : ymd(d) + "T" + String(d.getHours()).padStart(2, "0") + ":" + String(d.getMinutes()).padStart(2, "0"); };
  function fmtDate(d) { if (!d) return ""; const dt = new Date(d); return isNaN(dt) ? d : dt.toLocaleDateString(undefined, { day: "numeric", month: "short", year: "numeric" }); }
  function fmtWhen(iso) { const d = new Date(iso); return isNaN(d) ? "" : fmtDate(iso) + " · " + d.toLocaleTimeString([], { hour: "numeric", minute: "2-digit" }); }
  function ago(iso) {
    const t = new Date(iso).getTime();
    if (!iso || isNaN(t)) return "";
    const s = Math.max(0, (Date.now() - t) / 1000);
    return s < 60 ? "just now" : s < 3600 ? Math.floor(s / 60) + "m ago" : s < 86400 ? Math.floor(s / 3600) + "h ago" : s < 2592000 ? Math.floor(s / 86400) + "d ago" : fmtDate(iso);
  }
  function toast(message, variant) {
    let wrap = $("tpToastWrap");
    if (!wrap) {
      wrap = document.createElement("div");
      wrap.id = "tpToastWrap";
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
  async function copyText(txt, msg) { try { await navigator.clipboard.writeText(txt); toast(msg || "Copied", "dark"); } catch (e) { toast("Couldn't copy.", "danger"); } }

  /* ---------------- state ---------------- */

  let tickets = [], me = null, loading = true;
  let view = lsGet("tp.view", "table") === "board" ? "board" : "table";
  let cat = "all", page = 1, pageSize = parseInt(lsGet("tp.ps", 25), 10) || 25;
  let F = { search: "", statuses: new Set(), priority: "", category: "", owner: null };
  let sort = null, detail = null, MSG = [], dTab = "conversation", cMode = "reply", orderIds = [];
  const boardLimit = {};

  const isOpen = t => OPEN.indexOf(t.status) >= 0;
  const dueMs = t => t.due_at ? new Date(t.due_at).getTime() : null;
  const isOverdue = t => isOpen(t) && dueMs(t) !== null && dueMs(t) < Date.now();
  const pill = (status, interactive, id) => {
    const inner = `<span class="dot"></span>${esc(ST[status] || status)}`;
    return interactive ? `<button type="button" class="tp-pill st-${status}" data-act="status" data-id="${id}">${inner}<i class="bi bi-chevron-down"></i></button>` : `<span class="tp-pill st-${status}">${inner}</span>`;
  };
  const prioHtml = p => PRI[p] ? `<span class="tp-prio pri-${p}"><i class="bi bi-flag-fill"></i>${esc(PRI[p])}</span>` : DASH;

  function slaInfo(t) {
    if (!t.due_at) return null;
    if (!isOpen(t)) return { cls: "done", icon: "check2-circle", text: t.resolved_at && new Date(t.resolved_at).getTime() > dueMs(t) ? "Resolved late" : "Within SLA" };
    const diff = dueMs(t) - Date.now(), m = Math.round(Math.abs(diff) / 60000);
    const txt = m < 60 ? m + "m" : m < 1440 ? Math.floor(m / 60) + "h" : Math.floor(m / 1440) + "d";
    return diff < 0 ? { cls: "late", icon: "exclamation-circle-fill", text: "Overdue " + txt } : { cls: diff < 4 * 3600000 ? "soon" : "", icon: "clock", text: "Due in " + txt };
  }
  const slaHtml = t => { const s = slaInfo(t); return s ? `<span class="tp-sla ${s.cls}" title="${esc(fmtWhen(t.due_at))}"><i class="bi bi-${s.icon}"></i>${esc(s.text)}</span>` : DASH; };

  function linkOf(t) {
    if (t.deal) return { icon: "briefcase", label: t.deal.name, href: url("deal", t.deal.id), kind: "Deal" };
    if (t.lead) return { icon: "funnel", label: t.lead.customer_name, href: url("lead", t.lead.id), kind: "Lead" };
    if (t.contact) return { icon: "person", label: t.contact.name, href: null, kind: "Contact" };
    if (t.company) return { icon: "building", label: t.company.name, href: null, kind: "Company" };
    return null;
  }
  const linkChip = t => { const l = linkOf(t); return l ? (l.href ? `<a class="tp-ln" href="${esc(l.href)}" title="Open ${l.kind.toLowerCase()}"><i class="bi bi-${l.icon}"></i>${esc(l.label)}</a>` : `<span class="tp-ln"><i class="bi bi-${l.icon}"></i>${esc(l.label)}</span>`) : DASH; };
  const ownerCell = t => t.assignee ? `<span class="tp-own">${avatarHtml(t.assignee.name, "sm")}${esc(t.assignee.name)}</span>` : '<span class="tp-unassigned">Unassigned</span>';

  const COLS = [
    { k: "ticket", label: "Ticket", sortv: t => t.id },
    { k: "status", label: "Status", sortv: t => SK.indexOf(t.status), cell: t => pill(t.status, true, t.id) },
    { k: "priority", label: "Priority", sortv: t => Object.keys(PRI).indexOf(t.priority), cell: t => prioHtml(t.priority) },
    { k: "linked", label: "Related to", sortv: t => (linkOf(t) || {}).label || "", cell: linkChip },
    { k: "contact", label: "Customer", sortv: t => ((t.contact || {}).name || "").toLowerCase(), cell: t => t.contact ? esc(t.contact.name) : DASH },
    { k: "assignee", label: "Assignee", sortv: t => ((t.assignee || {}).name || "").toLowerCase(), cell: ownerCell },
    { k: "due", label: "SLA", sortv: t => dueMs(t) || 9e15, cell: slaHtml },
    { k: "category", label: "Category", sortv: t => t.category || "", cell: t => esc(t.category) },
    { k: "created", label: "Created", sortv: t => t.created_at || "", cell: t => `<span title="${esc(fmtWhen(t.created_at))}">${esc(ago(t.created_at))}</span>` },
  ];

  /* ---------------- filtering ---------------- */

  function inCategory(t, c) {
    switch (c) {
      case "mine": return !!me && t.assigned_to === me.id;
      case "open": return isOpen(t);
      case "unassigned": return isOpen(t) && !t.assigned_to;
      case "overdue": return isOverdue(t);
      case "resolved": return !isOpen(t);
      default: return true;
    }
  }
  function matches(t, ignoreStatus) {
    const q = F.search.trim().toLowerCase();
    if (q) {
      const hay = [t.ticket_no, t.subject, (t.contact || {}).name, (t.contact || {}).email, (t.contact || {}).phone, (t.company || {}).name, (t.deal || {}).name, (t.lead || {}).customer_name, (t.assignee || {}).name].join(" ").toLowerCase();
      if (!hay.includes(q)) return false;
    }
    if (!ignoreStatus && F.statuses.size && !F.statuses.has(t.status)) return false;
    if (F.priority && t.priority !== F.priority) return false;
    if (F.category && t.category !== F.category) return false;
    if (F.owner !== null && t.assigned_to !== F.owner) return false;
    return true;
  }
  function filtered(ignoreStatus) {
    let list = tickets.filter(t => matches(t, ignoreStatus) && inCategory(t, cat));
    const col = sort && COLS.find(c => c.k === sort.key);
    if (col && col.sortv) {
      const dir = sort.dir === "asc" ? 1 : -1;
      list = list.map((t, i) => [t, i]).sort((a, b) => { const x = col.sortv(a[0]), y = col.sortv(b[0]); return (x < y ? -1 : x > y ? 1 : a[1] - b[1]) * dir; }).map(p => p[0]);
    } else list = list.slice().sort((a, b) => b.id - a.id);
    return list;
  }
  const anyFilter = () => !!(F.search || F.statuses.size || F.priority || F.category || F.owner !== null);

  /* ---------------- popovers ---------------- */

  let popEl = null, popAnchor = null, popOpenedAt = 0;
  function closePop() { if (popEl) { popEl.remove(); popEl = null; popAnchor = null; } }
  function openPop(anchor, html, opts) {
    opts = opts || {};
    if (popEl && popAnchor === anchor) { closePop(); return null; }
    closePop();
    const el = document.createElement("div");
    el.className = "tp-pop";
    if (opts.width) el.style.width = opts.width + "px";
    el.innerHTML = html;
    (anchor.closest(".offcanvas, .modal") || document.body).appendChild(el);
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
      const lead = it.dot ? `<span class="sdot ${it.dot}"></span>` : it.icon ? `<i class="bi bi-${it.icon}"></i>` : "";
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
    const el = $("tpDrawer");
    if (el.classList.contains("show")) {
      el.addEventListener("hidden.bs.offcanvas", function h() { el.removeEventListener("hidden.bs.offcanvas", h); fn(); });
      bootstrap.Offcanvas.getOrCreateInstance(el).hide();
    } else fn();
  }

  /* ---------------- data ---------------- */

  async function loadTickets() {
    const data = await apiFetch(CFG.routes.data);
    loading = false;
    if (data.success) { tickets = data.tickets; me = data.current_admin; if (detail) detail = tickets.find(t => t.id === detail.id) || null; }
    renderAll();
  }
  function putTicket(t) {
    const i = tickets.findIndex(x => x.id === t.id);
    if (i >= 0) tickets[i] = Object.assign(tickets[i], t); else tickets.unshift(t);
    return tickets[i >= 0 ? i : 0];
  }

  /* ---------------- rendering: chrome ---------------- */

  function renderKpis() {
    const base = tickets.filter(t => matches(t, true));
    const open = base.filter(isOpen).length, od = base.filter(isOverdue).length, un = base.filter(t => isOpen(t) && !t.assigned_to).length;
    const wk = Date.now() - 7 * 86400000, rs = base.filter(t => !isOpen(t) && t.resolved_at && new Date(t.resolved_at).getTime() >= wk).length;
    const tile = (c, label, n, sub, cat2) => `<button type="button" class="tp-kpi" style="--c:${c}" data-cat="${cat2}"><small>${label}</small><b>${n}</b><span>${sub}</span></button>`;
    $("tpKpis").innerHTML = tile("var(--info)", "Open tickets", open, "need attention", "open") + tile("var(--danger)", "Overdue", od, "past their SLA", "overdue")
      + tile("var(--warning)", "Unassigned", un, "no owner yet", "unassigned") + tile("var(--success)", "Resolved", rs, "in the last 7 days", "resolved");
  }
  function renderTabs() {
    const base = tickets.filter(t => matches(t));
    document.querySelectorAll("#tpTabs [data-n]").forEach(el => { el.textContent = base.filter(t => inCategory(t, el.dataset.n)).length; });
    document.querySelectorAll("#tpTabs .tp-tab").forEach(b => b.classList.toggle("active", b.dataset.cat === cat));
  }
  function setChip(id, label, value, on) {
    const el = $(id), icon = el.querySelector("i:not(.bi-chevron-down)");
    el.classList.toggle("on", on);
    el.innerHTML = (icon ? icon.outerHTML : "") + `<span>${esc(label)}${on ? ":" : ""}</span>` + (on ? `<span class="v">${esc(value)}</span>` : "") + '<i class="bi bi-chevron-down"></i>';
  }
  function renderToolbar() {
    setChip("tpChipStatus", "Status", Array.from(F.statuses).map(k => ST[k]).join(", "), F.statuses.size > 0);
    setChip("tpChipPrio", "Priority", PRI[F.priority], !!F.priority);
    setChip("tpChipCat", "Category", F.category, !!F.category);
    const o = F.owner === null ? null : CFG.admins.find(a => a.id === F.owner);
    setChip("tpChipOwner", "Assignee", o ? o.name : "", F.owner !== null);
    $("tpClear").classList.toggle("d-none", !anyFilter());
  }
  function renderAll() {
    renderKpis(); renderTabs(); renderToolbar();
    const list = filtered(view === "board");
    orderIds = list.map(t => t.id);
    $("tpCount").textContent = list.length;
    $("tpTableWrap").classList.toggle("d-none", view !== "table");
    $("tpBoard").classList.toggle("d-none", view !== "board");
    document.querySelectorAll(".tp-seg [data-view]").forEach(b => b.classList.toggle("active", b.dataset.view === view));
    if (view === "table") renderTable(list); else renderBoard(list);
    fitHeights();
  }

  /* ---------------- rendering: table ---------------- */

  function mainCell(t) {
    return `<div class="tp-main"><span class="tp-ic st-${t.status}"><i class="bi bi-life-preserver"></i></span>
      <span class="tp-main-col"><span class="tp-no">${esc(t.ticket_no || "#" + t.id)}${t.messages_count ? `<span class="tp-msgs"><i class="bi bi-chat-left-text me-1"></i>${t.messages_count}</span>` : ""}</span><span class="tp-subj">${esc(t.subject)}</span></span></div>`;
  }
  function renderTable(list) {
    const totalPages = Math.max(1, Math.ceil(list.length / pageSize));
    page = Math.min(page, totalPages);
    const start = (page - 1) * pageSize, items = list.slice(start, start + pageSize);
    $("tpThead").innerHTML = `<tr>${COLS.map(c => `<th class="${c.k === "ticket" ? "c-main" : ""}" data-col="${c.k}">${esc(c.label)}${sort && sort.key === c.k ? `<i class="bi bi-arrow-${sort.dir === "asc" ? "up" : "down"} srt"></i>` : ""}</th>`).join("")}<th class="c-end"></th></tr>`;
    const tb = $("tpTbody"), span = COLS.length + 1;
    if (loading) {
      tb.innerHTML = Array.from({ length: 7 }, () => `<tr class="tp-skel">${"<td><span></span></td>".repeat(span)}</tr>`).join("");
    } else if (!items.length) {
      tb.innerHTML = `<tr><td colspan="${span}" style="height:auto;max-width:none;white-space:normal"><div class="tp-emptybox">
        <div class="ic"><i class="bi bi-${tickets.length ? "funnel" : "life-preserver"}"></i></div>
        <h5>${tickets.length ? "No tickets match" : "No tickets yet"}</h5>
        <p>${tickets.length ? "Try changing your filters or search." : "When a customer reports an issue, raise a ticket and link it to their deal, lead or contact."}</p>
        ${tickets.length ? '<button class="btn btn-outline-secondary btn-sm" data-act="clear">Clear filters</button>' : '<button class="btn btn-primary btn-sm" data-act="create"><i class="bi bi-plus-lg me-1"></i>New ticket</button>'}</div></td></tr>`;
    } else {
      tb.innerHTML = items.map(t => `<tr data-id="${t.id}">${COLS.map(c => c.k === "ticket" ? `<td class="c-main">${mainCell(t)}</td>` : `<td>${c.cell(t)}</td>`).join("")}
        <td class="c-end"><button type="button" class="tp-rowbtn" data-act="rowmenu" data-id="${t.id}" aria-label="More"><i class="bi bi-three-dots"></i></button></td></tr>`).join("");
    }
    const from = list.length ? start + 1 : 0, to = Math.min(list.length, start + pageSize);
    $("tpPageInfo").textContent = `${from}–${to} of ${list.length}`;
    $("tpPageSize").value = String(pageSize);
    $("tpFoot").classList.toggle("d-none", loading || !list.length);
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
    $("tpPages").innerHTML = totalPages > 1 ? pages.join("") : "";
  }

  /* ---------------- rendering: board ---------------- */

  function renderBoard(list) {
    const board = $("tpBoard"), keys = F.statuses.size ? SK.filter(k => F.statuses.has(k)) : SK;
    if (loading) { board.innerHTML = keys.map(k => `<div class="tp-col st-${k}"><div class="tp-col-head"><span class="dot"></span><b>${esc(ST[k])}</b></div><div class="tp-col-body"><div class="tp-colempty">Loading…</div></div></div>`).join(""); return; }
    const sl = board.scrollLeft;
    board.innerHTML = keys.map(k => {
      const items = list.filter(t => t.status === k), lim = boardLimit[k] || 20;
      const body = items.length ? items.slice(0, lim).map(boardCard).join("") + (items.length > lim ? `<button type="button" class="tp-more" data-act="more" data-status="${k}">Show ${items.length - lim} more</button>` : "")
        : '<div class="tp-colempty">No tickets<br>Drag a card here</div>';
      return `<div class="tp-col st-${k}" data-status="${k}"><div class="tp-col-head"><span class="dot"></span><b>${esc(ST[k])}</b><span class="n">${items.length}</span></div><div class="tp-col-body">${body}</div></div>`;
    }).join("");
    board.scrollLeft = sl;
  }
  function boardCard(t) {
    return `<div class="tp-card-k pri-${t.priority}" style="--pc:var(--c)" draggable="true" data-id="${t.id}">
      <div class="tp-no">${esc(t.ticket_no || "#" + t.id)}</div>
      <div class="nm">${esc(t.subject)}</div>
      <div class="ln">${linkChip(t)}</div>
      <div class="bt">${prioHtml(t.priority)}${slaHtml(t)}<span style="margin-left:auto">${t.assignee ? avatarHtml(t.assignee.name, "sm") : ""}</span></div></div>`;
  }
  function fitHeights() {
    const vh = window.innerHeight;
    if (view === "table") {
      const sc = $("tpScroll"), foot = $("tpFoot").classList.contains("d-none") ? 0 : $("tpFoot").offsetHeight;
      sc.style.maxHeight = Math.max(280, vh - sc.getBoundingClientRect().top - foot - 24) + "px";
    } else { const b = $("tpBoard"); b.style.height = Math.max(360, vh - b.getBoundingClientRect().top - 20) + "px"; }
  }

  /* ---------------- updates ---------------- */

  async function patchById(id, fields, quiet) {
    const t = tickets.find(x => x.id === id);
    let res;
    try { res = await reqJson(url("patch", id), "PATCH", fields); } catch (e) { return false; }
    if (!res.success) { toast(firstErr(res, "Couldn't save."), "danger"); renderAll(); if (detail && detail.id === id) renderDrawer(); return false; }
    if (t) Object.assign(t, res.ticket); else putTicket(res.ticket);
    renderAll();
    if (detail && detail.id === id) { renderDrawer(); loadMessages(id); }
    if (!quiet) toast("Saved", "dark");
    return true;
  }
  async function setStatus(id, status) {
    const t = tickets.find(x => x.id === id);
    if (!t || t.status === status) return;
    const old = t.status;
    t.status = status;                                   // optimistic
    renderAll(); if (detail && detail.id === id) renderDrawer();
    const ok = await patchById(id, { status }, true);
    if (ok) toast(status === "resolved" ? "Ticket resolved" : "Status: " + ST[status]);
    else { t.status = old; renderAll(); if (detail && detail.id === id) renderDrawer(); }
  }
  const statusMenu = (anchor, t) => popMenu(anchor, SK.map(k => ({ label: ST[k], dot: "st-" + k, checked: k === t.status, fn: () => setStatus(t.id, k) })));

  async function confirmDelete() {
    const id = pendingDel;
    if (!id) return;
    const btn = $("tpConfirmDelete");
    btn.disabled = true;
    try {
      const res = await apiFetch(url("destroy", id), { method: "DELETE" });
      if (res.success) { tickets = tickets.filter(t => t.id !== id); if (detail && detail.id === id) detail = null; renderAll(); toast("Ticket deleted", "dark"); }
      else toast(firstErr(res, "Couldn't delete."), "danger");
    } catch (e) { /* toast shown */ }
    btn.disabled = false;
    bootstrap.Modal.getOrCreateInstance($("tpDeleteModal")).hide();
  }
  let pendingDel = null;
  const askDelete = id => { pendingDel = id; afterDrawerClosed(() => bootstrap.Modal.getOrCreateInstance($("tpDeleteModal")).show()); };

  /* ---------------- toolbar wiring ---------------- */

  function bindToolbar() {
    $("tpSearch").addEventListener("input", debounce(e => { F.search = e.target.value; page = 1; renderAll(); }, 140));
    document.addEventListener("keydown", e => { if (e.key === "/" && !/^(INPUT|TEXTAREA|SELECT)$/.test((document.activeElement || {}).tagName || "") && !document.querySelector(".modal.show, .offcanvas.show")) { e.preventDefault(); $("tpSearch").focus(); } });
    $("tpClear").addEventListener("click", clearFilters);
    const catClick = e => { const t = e.target.closest("[data-cat]"); if (t) { cat = t.dataset.cat; page = 1; renderAll(); } };
    $("tpTabs").addEventListener("click", catClick);
    $("tpKpis").addEventListener("click", catClick);
    document.querySelector(".tp-seg").addEventListener("click", e => { const b = e.target.closest("[data-view]"); if (b) { view = b.dataset.view; lsSet("tp.view", view); renderAll(); } });
    $("tpPageSize").addEventListener("change", e => { pageSize = parseInt(e.target.value, 10) || 25; lsSet("tp.ps", pageSize); page = 1; renderAll(); });
    $("tpPages").addEventListener("click", e => { const b = e.target.closest("[data-page]"); if (b && !b.disabled) { page = parseInt(b.dataset.page, 10); renderAll(); $("tpScroll").scrollTop = 0; } });
    $("tpNewBtn").addEventListener("click", () => window.TicketModal.open({}));

    $("tpChipStatus").addEventListener("click", e => {
      const counts = {};
      tickets.forEach(t => { if (matches(t, true) && inCategory(t, cat)) counts[t.status] = (counts[t.status] || 0) + 1; });
      popMenu(e.currentTarget, SK.map(k => ({ label: ST[k], dot: "st-" + k, checked: F.statuses.has(k), cnt: counts[k] || 0, keep: true, fn: () => {
        if (F.statuses.has(k)) F.statuses.delete(k); else F.statuses.add(k);
        page = 1; renderAll();
        if (popEl) popEl.querySelectorAll(".it[data-i]").forEach((b, i) => { if (i >= SK.length) return; const on = F.statuses.has(SK[i]), ck = b.querySelector(".ck"); if (on && !ck) b.insertAdjacentHTML("beforeend", '<i class="bi bi-check2 ck"></i>'); if (!on && ck) ck.remove(); });
      } })).concat([{ sep: true }, { label: "Clear all", icon: "x-circle", fn: () => { F.statuses.clear(); renderAll(); } }]));
    });
    $("tpChipPrio").addEventListener("click", e => popMenu(e.currentTarget, [{ label: "Any priority", checked: !F.priority, fn: () => { F.priority = ""; page = 1; renderAll(); } }]
      .concat(Object.keys(PRI).map(k => ({ label: PRI[k], icon: "flag-fill", checked: F.priority === k, cnt: tickets.filter(t => t.priority === k).length, fn: () => { F.priority = k; page = 1; renderAll(); } })))));
    $("tpChipCat").addEventListener("click", e => popMenu(e.currentTarget, [{ label: "Any category", checked: !F.category, fn: () => { F.category = ""; page = 1; renderAll(); } }]
      .concat(CATS.map(c => ({ label: c, icon: "tag", checked: F.category === c, cnt: tickets.filter(t => t.category === c).length, fn: () => { F.category = c; page = 1; renderAll(); } })))));
    $("tpChipOwner").addEventListener("click", e => popMenu(e.currentTarget, [{ label: "Anyone", checked: F.owner === null, fn: () => { F.owner = null; page = 1; renderAll(); } }]
      .concat(CFG.admins.map(a => ({ label: a.name, icon: "person", checked: F.owner === a.id, cnt: tickets.filter(t => t.assigned_to === a.id).length, fn: () => { F.owner = a.id; page = 1; renderAll(); } })))));
  }
  function clearFilters() { F = { search: "", statuses: new Set(), priority: "", category: "", owner: null }; page = 1; $("tpSearch").value = ""; renderAll(); }

  function rowMenu(anchor, id) {
    const t = tickets.find(x => x.id === id);
    if (!t) return;
    popMenu(anchor, [{ label: "Open", icon: "box-arrow-up-right", fn: () => openDetail(id) }, { sep: true }, { hd: "Set status" }]
      .concat(SK.filter(k => k !== t.status).map(k => ({ label: ST[k], dot: "st-" + k, fn: () => setStatus(id, k) })))
      .concat([{ sep: true }, { label: "Delete", icon: "trash", danger: true, fn: () => askDelete(id) }]), { align: "right" });
  }
  function bindTable() {
    $("tpTbody").addEventListener("click", e => {
      const act = e.target.closest("[data-act]");
      if (act) {
        e.stopPropagation();
        const id = parseInt(act.dataset.id, 10), a = act.dataset.act;
        if (a === "status") statusMenu(act, tickets.find(x => x.id === id));
        else if (a === "rowmenu") rowMenu(act, id);
        else if (a === "clear") clearFilters();
        else if (a === "create") window.TicketModal.open({});
        return;
      }
      if (e.target.closest("a")) return;
      const tr = e.target.closest("tr[data-id]");
      if (tr) openDetail(parseInt(tr.dataset.id, 10));
    });
    $("tpThead").addEventListener("click", e => {
      const th = e.target.closest("th[data-col]");
      if (!th) return;
      const k = th.dataset.col;
      sort = !sort || sort.key !== k ? { key: k, dir: "asc" } : sort.dir === "asc" ? { key: k, dir: "desc" } : null;
      renderAll();
    });
  }
  function bindBoard() {
    const board = $("tpBoard");
    board.addEventListener("click", e => {
      if (e.target.closest("a")) return;
      const more = e.target.closest('[data-act="more"]');
      if (more) { boardLimit[more.dataset.status] = (boardLimit[more.dataset.status] || 20) + 20; renderAll(); return; }
      const card = e.target.closest(".tp-card-k");
      if (card) openDetail(parseInt(card.dataset.id, 10));
    });
    let dragId = null;
    board.addEventListener("dragstart", e => { const c = e.target.closest(".tp-card-k"); if (!c) return; dragId = parseInt(c.dataset.id, 10); e.dataTransfer.effectAllowed = "move"; try { e.dataTransfer.setData("text/plain", String(dragId)); } catch (er) { /* ignore */ } setTimeout(() => c.classList.add("dragging"), 0); });
    board.addEventListener("dragend", () => { dragId = null; board.querySelectorAll(".dragging").forEach(c => c.classList.remove("dragging")); board.querySelectorAll(".over").forEach(c => c.classList.remove("over")); });
    board.addEventListener("dragover", e => { const col = e.target.closest(".tp-col"); if (!col || dragId === null) return; e.preventDefault(); board.querySelectorAll(".over").forEach(c => { if (c !== col) c.classList.remove("over"); }); col.classList.add("over"); });
    board.addEventListener("drop", e => { const col = e.target.closest(".tp-col"); if (!col || dragId === null) return; e.preventDefault(); const id = dragId; dragId = null; col.classList.remove("over"); setStatus(id, col.dataset.status); });
  }

  /* ==================== DRAWER ==================== */

  async function openDetail(id) {
    let t = tickets.find(x => x.id === id);
    if (!t) {
      let res;
      try { res = await apiFetch(url("show", id)); } catch (e) { return; }
      if (!res.success) { toast(firstErr(res, "Ticket not found."), "danger"); return; }
      t = putTicket(res.ticket);
    }
    detail = t; MSG = []; dTab = "conversation"; cMode = "reply";
    closePop(); resetComposer(); renderDrawer();
    bootstrap.Offcanvas.getOrCreateInstance($("tpDrawer")).show();
    loadMessages(id);
  }
  async function loadMessages(id) {
    let res;
    try { res = await apiFetch(url("show", id)); } catch (e) { return; }
    if (!res.success || !detail || detail.id !== id) return;
    MSG = res.messages || [];
    Object.assign(detail, res.ticket);
    renderThread(); renderDrTabs(); renderAll();
    if (dTab === "conversation") { const b = $("tpDrBody"); b.scrollTop = b.scrollHeight; }
  }

  function tracker(t) {
    const idx = SK.indexOf(t.status);
    return SK.map((k, i) => `<button type="button" class="${k === t.status ? "cur st-" + k : i < idx ? "past" : ""}" data-status="${k}">${esc(ST[k])}</button>`).join("");
  }
  function renderDrawer() {
    const t = detail;
    if (!t) return;
    const l = linkOf(t), c = t.contact, open = isOpen(t), sla = slaInfo(t), pos = orderIds.indexOf(t.id);
    $("tpDrTop").innerHTML = `
      <div class="tp-dr-bar"><span class="no">${esc(t.ticket_no || "#" + t.id)}</span><span class="sp"></span>
        <button type="button" class="tp-iconbtn" data-dr="copy" title="Copy link" aria-label="Copy link"><i class="bi bi-link-45deg"></i></button>
        <button type="button" class="tp-iconbtn danger" data-dr="delete" title="Delete" aria-label="Delete"><i class="bi bi-trash"></i></button>
        <button type="button" class="tp-iconbtn" data-bs-dismiss="offcanvas" aria-label="Close"><i class="bi bi-x-lg"></i></button></div>
      <div class="tp-prop-v hdr" tabindex="0" role="button" data-edit="subject" data-kind="text" title="Click to edit the subject" aria-label="Edit subject">${esc(t.subject)}<i class="bi bi-pencil pen"></i></div>
      <div class="tp-dr-sub">${pill(t.status, true, t.id)}
        <button type="button" class="tp-chipb" data-dr="prioMenu">${prioHtml(t.priority)}<i class="bi bi-chevron-down" style="font-size:9px"></i></button>
        <button type="button" class="tp-chipb" data-dr="ownerMenu">${t.assignee ? avatarHtml(t.assignee.name, "sm") + esc(t.assignee.name) : '<i class="bi bi-person-dash"></i>Unassigned'}<i class="bi bi-chevron-down" style="font-size:9px"></i></button>
        ${sla ? `<span class="tp-chipb ${sla.cls === "late" ? "late" : ""}"><i class="bi bi-${sla.icon}"></i>${esc(sla.text)}</span>` : ""}</div>
      <div class="tp-dr-sub">
        ${t.deal ? `<a class="tp-chipb" href="${esc(url("deal", t.deal.id))}"><i class="bi bi-briefcase"></i>${esc(t.deal.name)}</a>` : ""}
        ${t.lead ? `<a class="tp-chipb" href="${esc(url("lead", t.lead.id))}"><i class="bi bi-funnel"></i>${esc(t.lead.customer_name)}</a>` : ""}
        ${c ? `<span class="tp-chipb"><i class="bi bi-person"></i>${esc(c.name)}</span>` : ""}
        ${t.company ? `<span class="tp-chipb"><i class="bi bi-building"></i>${esc(t.company.name)}</span>` : ""}
        ${!l ? '<span class="tp-chipb"><i class="bi bi-dash-circle"></i>Not linked to a record</span>' : ""}</div>
      <div class="tp-track" id="tpTrack">${tracker(t)}</div>
      <div class="tp-dr-act">
        ${open ? '<button type="button" class="btn btn-success btn-sm" data-dr="resolve"><i class="bi bi-check2-circle me-1"></i>Mark resolved</button>' : '<button type="button" class="btn btn-outline-primary btn-sm" data-dr="reopen"><i class="bi bi-arrow-counterclockwise me-1"></i>Reopen ticket</button>'}
        <span class="sp"></span>
        ${c ? `<a class="tp-qa ${c.phone ? "" : "off"}" ${c.phone ? `href="tel:${esc(c.phone)}"` : ""}><i class="bi bi-telephone"></i>Call</a><a class="tp-qa wa ${waLink(c.whatsapp || c.phone) ? "" : "off"}" ${waLink(c.whatsapp || c.phone) ? `href="${esc(waLink(c.whatsapp || c.phone))}" target="_blank" rel="noopener"` : ""}><i class="bi bi-whatsapp"></i>WhatsApp</a><a class="tp-qa ${c.email ? "" : "off"}" ${c.email ? `href="mailto:${esc(c.email)}"` : ""}><i class="bi bi-envelope"></i>Email</a>` : ""}</div>`;
    renderDrTabs();
    renderDrBody();
  }
  function renderDrTabs() {
    const n = MSG.filter(m => m.type !== "system").length;
    $("tpDrTabs").innerHTML = [["conversation", "Conversation", n ? `<span class="n">${n}</span>` : ""], ["details", "Details", ""]]
      .map(t => `<button type="button" class="tp-dr-tab ${dTab === t[0] ? "active" : ""}" data-dtab="${t[0]}">${t[1]}${t[2]}</button>`).join("");
    $("tpCompose").classList.toggle("d-none", dTab !== "conversation");
  }
  function renderDrBody() { if (dTab === "conversation") renderThread(); else renderDetails(); }

  function renderThread() {
    if (dTab !== "conversation" || !detail) return;
    const t = detail;
    let h = "";
    if (t.description) h += `<div class="tp-msg"><div>${avatarHtml((t.creator || {}).name || "?")}</div><div class="tp-bub"><div class="tp-mh"><b>Original request</b><span class="tag">${esc(t.channel ? CHANNELS[t.channel] || "" : "Ticket")}</span><span class="at" title="${esc(fmtWhen(t.created_at))}">${esc(ago(t.created_at))}</span></div><div class="tp-mb">${esc(t.description)}</div><div class="tp-mf">Raised by ${esc((t.creator || {}).name || "—")}</div></div></div>`;
    MSG.forEach(m => {
      if (m.type === "system") { h += `<div class="tp-sys">${esc(m.body)}<span class="at">· ${esc(ago(m.created_at))}</span></div>`; return; }
      const name = (m.creator || {}).name || "Someone";
      h += `<div class="tp-msg ${m.type}"><div>${avatarHtml(name)}</div><div class="tp-bub"><div class="tp-mh"><b>${esc(name)}</b><span class="tag">${m.type === "note" ? "Internal note" : "Reply to customer"}</span><span class="at" title="${esc(fmtWhen(m.created_at))}">${esc(ago(m.created_at))}</span></div><div class="tp-mb">${esc(m.body)}</div></div></div>`;
    });
    $("tpDrBody").innerHTML = h || '<div class="tp-none">No conversation yet.<br>Send a reply or add an internal note below.</div>';
  }

  function propRow(label, field, kind, html, ro) {
    const empty = html === "" || html == null;
    return `<div class="tp-prop"><div class="tp-prop-l">${esc(label)}</div><div class="tp-prop-v ${ro ? "ro" : ""}" ${ro ? "" : `tabindex="0" role="button" data-edit="${field}" data-kind="${kind}" aria-label="Edit ${esc(label)}"`}>${empty ? `<span class="ph">${ro ? "—" : "Add " + esc(label.toLowerCase())}</span>` : html}${ro ? "" : '<i class="bi bi-pencil pen"></i>'}</div></div>`;
  }
  function renderDetails() {
    const t = detail;
    if (!t) return;
    const person = (icon, title, sub) => `<div class="tp-person"><div class="ic"><i class="bi bi-${icon}"></i></div><div class="grow"><div class="t">${esc(title)}</div><div class="s">${esc(sub || "")}</div></div></div>`;
    $("tpDrBody").innerHTML = `
      <div class="tp-sect"><div class="tp-sect-h"><i class="bi bi-card-list"></i>Ticket details</div>
        ${propRow("Category", "category", "select", esc(t.category))}
        ${propRow("Raised via", "channel", "select", esc(CHANNELS[t.channel] || ""))}
        <div class="tp-prop"><div class="tp-prop-l">Priority</div><div class="tp-prio-seg">${Object.keys(PRI).map(k => `<button type="button" class="pri-${k} ${t.priority === k ? "on" : ""}" data-pri="${k}"><i class="bi bi-flag-fill"></i>${esc(PRI[k])}</button>`).join("")}</div></div>
        ${propRow("Assigned to", "assigned_to", "select", t.assignee ? avatarHtml(t.assignee.name, "sm") + `<span>${esc(t.assignee.name)}</span>` : "")}
        ${propRow("Due date", "due_at", "datetime", t.due_at ? esc(fmtWhen(t.due_at)) : "")}
        ${propRow("Description", "description", "textarea", esc(t.description))}</div>
      <div class="tp-sect"><div class="tp-sect-h"><i class="bi bi-link-45deg"></i>Linked records</div>
        ${t.deal ? `<a class="tp-person" style="text-decoration:none;color:inherit" href="${esc(url("deal", t.deal.id))}"><div class="ic"><i class="bi bi-briefcase"></i></div><div class="grow"><div class="t">${esc(t.deal.name)}</div><div class="s">Deal</div></div><i class="bi bi-box-arrow-up-right text-muted"></i></a>` : ""}
        ${t.lead ? `<a class="tp-person" style="text-decoration:none;color:inherit" href="${esc(url("lead", t.lead.id))}"><div class="ic"><i class="bi bi-funnel"></i></div><div class="grow"><div class="t">${esc(t.lead.customer_name)}</div><div class="s">Lead${t.lead.phone ? " · " + esc(t.lead.phone) : ""}</div></div><i class="bi bi-box-arrow-up-right text-muted"></i></a>` : ""}
        ${t.contact ? person("person", t.contact.name, [t.contact.email, t.contact.phone].filter(Boolean).join(" · ") || "Contact") : ""}
        ${t.company ? person("building", t.company.name, "Company") : ""}
        ${!t.deal && !t.lead && !t.contact && !t.company ? '<div class="tp-none">This ticket is not linked to any record.</div>' : ""}</div>
      <div class="tp-sect"><div class="tp-sect-h"><i class="bi bi-clock-history"></i>Timeline</div>
        ${propRow("Created", "", "", esc(fmtWhen(t.created_at)) + (t.creator ? ` <span class="text-muted">by ${esc(t.creator.name)}</span>` : ""), true)}
        ${propRow("First response", "", "", t.first_response_at ? esc(fmtWhen(t.first_response_at)) : "", true)}
        ${propRow("Resolved", "", "", t.resolved_at ? esc(fmtWhen(t.resolved_at)) : "", true)}
        ${propRow("Closed", "", "", t.closed_at ? esc(fmtWhen(t.closed_at)) : "", true)}</div>`;
  }

  /* ---- inline edit ---- */

  function selectOpts(field, cur) {
    let o = field === "category" ? CATS.map(c => [c, c])
      : field === "channel" ? [["", "— Not specified —"]].concat(Object.keys(CHANNELS).map(k => [k, CHANNELS[k]]))
      : [["", "— Unassigned —"]].concat(CFG.admins.map(a => [String(a.id), a.name]));
    if (cur && !o.some(x => x[0] === cur)) o = o.concat([[cur, cur]]);
    return o;
  }
  function startEdit(cell) {
    if (!detail || cell.classList.contains("editing") || cell.classList.contains("ro")) return;
    const field = cell.dataset.edit, kind = cell.dataset.kind;
    const cur = field === "due_at" ? toLocalInput(detail.due_at) : detail[field] == null ? "" : String(detail[field]);
    let input;
    if (kind === "select") {
      input = document.createElement("select"); input.className = "form-select form-select-sm";
      input.innerHTML = selectOpts(field, cur).map(o => `<option value="${esc(o[0])}" ${o[0] === cur ? "selected" : ""}>${esc(o[1])}</option>`).join("");
    } else if (kind === "textarea") {
      input = document.createElement("textarea"); input.rows = 4; input.className = "form-control form-control-sm"; input.value = cur;
    } else {
      input = document.createElement("input"); input.type = kind === "datetime" ? "datetime-local" : "text"; input.className = "form-control form-control-sm"; input.value = cur;
    }
    const orig = cell.innerHTML;
    cell.classList.add("editing"); cell.innerHTML = ""; cell.appendChild(input); input.focus();
    if (input.select && kind === "text") input.select();
    let done = false;
    const finish = async save => {
      if (done) return;
      done = true;
      cell.classList.remove("editing");
      const v = input.value.trim();
      if (!save || v === cur.trim()) { cell.innerHTML = orig; return; }
      if (field === "subject" && !v) { cell.innerHTML = orig; toast("Subject can't be empty.", "danger"); return; }
      cell.innerHTML = orig; cell.classList.add("tp-saving");
      let val = v === "" ? null : v;
      if (field === "assigned_to" && val) val = parseInt(val, 10);
      if (field === "due_at" && val) val = val.replace("T", " ");
      await patchById(detail.id, { [field]: val });
      cell.classList.remove("tp-saving");
    };
    input.addEventListener("keydown", e => {
      if (e.key === "Enter" && (kind !== "textarea" || e.ctrlKey || e.metaKey)) { e.preventDefault(); finish(true); }
      else if (e.key === "Escape") { e.preventDefault(); e.stopPropagation(); finish(false); }
    });
    input.addEventListener("blur", () => finish(true));
    if (kind === "select") input.addEventListener("change", () => finish(true));
  }

  /* ---- composer ---- */

  function resetComposer() { $("tpMsg").value = ""; setCMode("reply"); }
  function setCMode(m) {
    cMode = m;
    document.querySelectorAll("#tpCTabs button").forEach(b => { b.classList.toggle("on", b.dataset.cm === m); b.classList.toggle("note", b.dataset.cm === "note"); });
    $("tpMsg").classList.toggle("note", m === "note");
    $("tpMsg").placeholder = m === "reply" ? "Write what you told (or will tell) the customer…" : "Private note for your team — the customer never sees this.";
    $("tpCHint").textContent = m === "reply" ? "Logged as a reply to the customer." : "Only your team can see internal notes.";
    $("tpSend").textContent = m === "reply" ? "Send reply" : "Add note";
    $("tpSendResolve").classList.toggle("d-none", m !== "reply");
  }
  async function sendMessage(resolve) {
    const body = $("tpMsg").value.trim();
    if (!body) { toast("Write something first.", "danger"); $("tpMsg").focus(); return; }
    const id = detail.id, btns = [$("tpSend"), $("tpSendResolve")];
    btns.forEach(b => { b.disabled = true; });
    try {
      const res = await reqJson(url("message", id), "POST", { type: cMode, body });
      if (!res.success) { toast(firstErr(res, "Couldn't send."), "danger"); return; }
      MSG.push(res.item);
      Object.assign(detail, res.ticket);
      $("tpMsg").value = "";
      renderThread(); renderDrTabs(); renderAll();
      const b = $("tpDrBody"); b.scrollTop = b.scrollHeight;
      toast(res.message);
      if (resolve && isOpen(detail)) await setStatus(id, "resolved");
    } catch (e) { /* toast shown */ } finally { btns.forEach(b => { b.disabled = false; }); }
  }

  function bindDrawer() {
    const dr = $("tpDrawer");
    dr.addEventListener("click", e => {
      if (popEl && popEl.contains(e.target)) return;
      if (!detail) return;
      let el;
      if ((el = e.target.closest("#tpTrack [data-status]"))) { setStatus(detail.id, el.dataset.status); return; }
      if ((el = e.target.closest('[data-act="status"]'))) { statusMenu(el, detail); return; }
      if ((el = e.target.closest("[data-dtab]"))) { dTab = el.dataset.dtab; closePop(); renderDrTabs(); renderDrBody(); if (dTab === "conversation") { const b = $("tpDrBody"); b.scrollTop = b.scrollHeight; } return; }
      if ((el = e.target.closest("[data-pri]"))) { if (el.dataset.pri !== detail.priority) patchById(detail.id, { priority: el.dataset.pri }); return; }
      if ((el = e.target.closest("[data-cm]"))) { setCMode(el.dataset.cm); $("tpMsg").focus(); return; }
      if ((el = e.target.closest("[data-dr]"))) {
        const a = el.dataset.dr;
        if (a === "copy") copyText(location.origin + location.pathname + "?ticket=" + detail.id, "Link copied");
        else if (a === "delete") askDelete(detail.id);
        else if (a === "resolve") setStatus(detail.id, "resolved");
        else if (a === "reopen") popMenu(el, [{ hd: "Reopen as" }].concat(OPEN.map(k => ({ label: ST[k], dot: "st-" + k, fn: () => setStatus(detail.id, k) }))));
        else if (a === "prioMenu") popMenu(el, Object.keys(PRI).map(k => ({ label: PRI[k], icon: "flag-fill", checked: detail.priority === k, fn: () => { if (detail.priority !== k) patchById(detail.id, { priority: k }); } })));
        else if (a === "ownerMenu") popMenu(el, [{ label: "Unassigned", icon: "person-dash", checked: !detail.assigned_to, fn: () => patchById(detail.id, { assigned_to: null }) }].concat(CFG.admins.map(x => ({ label: x.name, icon: "person", checked: detail.assigned_to === x.id, fn: () => { if (detail.assigned_to !== x.id) patchById(detail.id, { assigned_to: x.id }); } }))));
        return;
      }
      if ((el = e.target.closest(".tp-prop-v[data-edit]"))) startEdit(el);
    });
    dr.addEventListener("keydown", e => { if ((e.key === "Enter" || e.key === " ") && e.target.matches(".tp-prop-v[data-edit]")) { e.preventDefault(); startEdit(e.target); } });
    $("tpMsg").addEventListener("keydown", e => { if (e.key === "Enter" && (e.ctrlKey || e.metaKey)) { e.preventDefault(); sendMessage(false); } });
    $("tpSend").addEventListener("click", () => sendMessage(false));
    $("tpSendResolve").addEventListener("click", () => sendMessage(true));
    $("tpConfirmDelete").addEventListener("click", confirmDelete);
    dr.addEventListener("hidden.bs.offcanvas", closePop);
  }

  /* ---------------- reusable modal integration ---------------- */

  window.addEventListener("ticket:created", e => {
    putTicket(e.detail);
    page = 1; sort = null; cat = "all";
    renderAll();
  });
  // "Open ticket" in the modal's success screen: open the drawer here instead of reloading the page
  window.addEventListener("ticket:open", e => {
    e.preventDefault();
    const id = e.detail.id, m = $("tkModal");
    m.addEventListener("hidden.bs.modal", function h() { m.removeEventListener("hidden.bs.modal", h); openDetail(id); });
  });

  /* ---------------- deep links:  ?ticket=12   or   ?new=1&type=deal&id=5 ---------------- */

  function openFromQuery() {
    const p = new URLSearchParams(window.location.search);
    if (p.get("ticket")) openDetail(parseInt(p.get("ticket"), 10));
    else if (p.get("new")) window.TicketModal.open({ type: p.get("type"), id: p.get("id"), subject: p.get("subject") });
    else return;
    ["ticket", "new", "type", "id", "subject"].forEach(k => p.delete(k));
    window.history.replaceState({}, "", window.location.pathname + (p.toString() ? "?" + p : ""));
  }

  document.addEventListener("DOMContentLoaded", () => {
    bindToolbar(); bindTable(); bindBoard(); bindDrawer();
    setCMode("reply");
    renderAll();
    loadTickets().then(openFromQuery).catch(() => { loading = false; renderAll(); });
  });
})();
</script>

@endsection