{{-- Shared styles + DirUI engine for the Contacts and Companies pages --}}
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

  /* ---- directory pages (contacts / companies) ---- */
  .lh-list-item { display: flex; gap: 12px; align-items: center; padding: 12px 16px; border-top: 1px solid #F0F1F6; color: var(--text); text-decoration: none; }
  .lh-list-item:first-child { border-top: 0; }
  a.lh-list-item:hover { background: var(--primary-50); }
  .lh-list-item .t { font-weight: 700; font-size: 13.5px; }
  .lh-list-item .s { font-size: 12px; color: var(--text-muted); }
  .lh-list-item .grow { flex: 1; min-width: 0; }
  .lh-list-item .grow > div { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .lh-list-item .end { margin-left: auto; display: flex; align-items: center; gap: 6px; flex-shrink: 0; }
  .lh-mini { padding: 14px 16px; border-bottom: 1px solid #F0F1F6; background: #FBFBFE; }
  .lh-mini label { font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; }
  .lh-hint { font-size: 12.5px; color: var(--text-muted); }
  .lh-iconbtn:disabled { opacity: .35; pointer-events: none; }
  .lh-pop .it small { font-size: 11.5px; }
  .lh-pop .alist, .lh-pop .clist { max-height: 260px; overflow-y: auto; }
  .lh-pop .it > span:not(.cnt):not(.sdot) { min-width: 0; overflow: hidden; text-overflow: ellipsis; }
  .lh-newform .form-control, .lh-newform .form-select { background: #fff; }
  .lh-co-link { color: var(--primary); font-weight: 600; }
</style>
<script>
/* ==========================================================================
   DirUI - shared engine for the Contacts and Companies pages.
   Table (column chooser "+", sorting, filters, tabs, pagination, bulk delete) +
   record drawer with click-to-edit fields. Each page passes a config object.
   ========================================================================== */
(function () {
  "use strict";

  const AVATAR_COLORS = ["#4338CA", "#0891B2", "#B45309", "#16A34A", "#DC2626", "#6D28D9", "#0D9488"];
  const DASH = '<span class="lh-empty">—</span>';
  const LEAD_TYPES = ["hot", "cold", "warm", "converted", "dealer"];
  let CSRF = "";

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
  function companyIcon(size) {
    return `<div class="lh-co-ic" style="${size === "sm" ? "width:30px;height:30px;font-size:14px;border-radius:9px" : ""}"><i class="bi bi-building"></i></div>`;
  }
  const typeLabel = t => t ? t.charAt(0).toUpperCase() + t.slice(1) : "";
  const stCls = t => LEAD_TYPES.indexOf(t) >= 0 ? "st-" + t : "st-other";
  function pill(type) { return `<span class="lh-pill ${stCls(type)}"><span class="dot"></span>${esc(typeLabel(type))}</span>`; }
  function digits(n) { return String(n == null ? "" : n).replace(/\D/g, ""); }
  function waUrl(n) { const d = digits(n); return d ? `whatsapp://send?phone=${d}` : null; }
  function fmtDate(d) {
    if (!d) return "";
    const dt = new Date(d);
    return isNaN(dt) ? d : dt.toLocaleDateString(undefined, { day: "numeric", month: "short", year: "numeric" });
  }
  function ymd(dt) { return dt.getFullYear() + "-" + String(dt.getMonth() + 1).padStart(2, "0") + "-" + String(dt.getDate()).padStart(2, "0"); }
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
  function debounce(fn, ms) { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; }

  function toast(message, variant) {
    let wrap = document.getElementById("lfToastWrap");
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
    setTimeout(() => el.remove(), 3400);
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
  const firstErr = (res, fb) => (res && (res.message || (res.errors && Object.values(res.errors)[0][0]))) || fb;

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
  window.addEventListener("scroll", e => { if (popEl && !popEl.contains(e.target) && Date.now() - popOpenedAt > 250) closePop(); }, true);

  /** Generic searchable picker for companies / contacts: searchUrl returns {<listKey>: [...]}. */
  function entityPicker(anchor, o) {
    const el = openPop(anchor, `<div class="srch"><input type="text" class="form-control form-control-sm" placeholder="${esc(o.placeholder)}"></div><div class="clist"></div>`, { width: 330 });
    if (!el) return;
    const input = el.querySelector("input"), list = el.querySelector(".clist");
    let token = 0, items = [];
    const draw = async () => {
      const my = ++token, q = input.value.trim();
      let res;
      try { res = await apiFetch(o.searchUrl + "?q=" + encodeURIComponent(q)); } catch (e) { return; }
      if (my !== token || !popEl) return;
      items = (res[o.listKey] || []).filter(x => !o.exclude || !o.exclude(x));
      const exact = items.some(c => c.name.toLowerCase() === q.toLowerCase());
      list.innerHTML = items.map((c, i) => `<button type="button" class="it" data-i="${i}">${o.icon ? `<i class="bi bi-${o.icon}"></i>` : ""}<span>${esc(c.name)}<br><small class="text-muted">${esc(o.meta(c))}</small></span></button>`).join("")
        + (o.create && q && !exact ? `<button type="button" class="it" data-create><i class="bi bi-plus-circle"></i><span>Create "${esc(q)}"</span></button>` : "")
        || '<div class="hd">Type to search</div>';
    };
    draw();
    input.focus();
    input.addEventListener("input", debounce(draw, 200));
    el.addEventListener("click", async e => {
      const b = e.target.closest("button.it");
      if (!b) return;
      if (b.dataset.i !== undefined) { closePop(); o.onPick(items[+b.dataset.i]); return; }
      if (b.hasAttribute("data-create")) {
        const res = await o.create(input.value.trim());
        if (res.success) { closePop(); o.onPick(res[o.itemKey]); }
        else if (res.duplicate && res[o.itemKey]) { closePop(); toast("That already exists - using the existing one.", "dark"); o.onPick(res[o.itemKey]); }
        else toast(firstErr(res, "Could not create it."), "danger");
      }
    });
  }

  /* ==================== the page engine ==================== */

  function create(cfg) {
    CSRF = cfg.csrf;
    const $ = id => document.getElementById(id);
    const K = s => "dir." + cfg.key + "." + s;
    const url = (key, id) => cfg.routes[key].replace("__ID__", id);
    const COLS = cfg.cols;
    const panes = cfg.drawer.panes;

    let rows = [], loading = true, cat = "all", page = 1;
    let pageSize = parseInt(lsGet(K("ps"), 25), 10) || 25;
    let F = { search: "", chips: {}, chipLabel: {}, from: "", to: "" };
    let viewAs = null, sort = null, detail = null, pane = panes[0].key, orderIds = [], pendingDel = [], pendingSkipped = 0;
    const selected = new Set();

    function sanitize(arr) {
      const out = ["name"];
      (Array.isArray(arr) ? arr : cfg.defaultCols).forEach(k => { if (COLS[k] && out.indexOf(k) < 0) out.push(k); });
      return out;
    }
    let cols = sanitize(lsGet(K("cols"), cfg.defaultCols));
    function setCols(arr) {
      cols = sanitize(arr);
      lsSet(K("cols"), cols);
      if (sort && cols.indexOf(sort.key) < 0) sort = null;
      renderAll();
      if ($("lhColsModal").classList.contains("show")) renderColsModal();
    }

    /* ---------- filtering ---------- */

    function matches(r, skipChip) {
      const t = F.search.trim().toLowerCase();
      if (t) {
        const hay = String(cfg.searchText(r)).toLowerCase();
        if (!hay.includes(t) && !(digits(t).length >= 3 && digits(hay).includes(digits(t)))) return false;
      }
      for (const ch of cfg.chips) {
        if (ch.id === skipChip) continue;
        const v = F.chips[ch.id];
        if (v !== undefined && v !== "" && !ch.test(r, v)) return false;
      }
      const d = (r.created_at || "").slice(0, 10);
      if (F.from && !(d && d >= F.from)) return false;
      if (F.to && !(d && d <= F.to)) return false;
      if (viewAs !== null && r.created_by !== viewAs) return false;
      return true;
    }
    const inTab = r => { const t = cfg.tabs.find(x => x.key === cat); return !t || t.test(r); };
    function filtered() {
      let list = rows.filter(r => matches(r) && inTab(r));
      if (sort && COLS[sort.key] && COLS[sort.key].sortv) {
        const f = COLS[sort.key].sortv, dir = sort.dir === "asc" ? 1 : -1;
        list = list.map((r, i) => [r, i]).sort((a, b) => { const x = f(a[0]), y = f(b[0]); return (x < y ? -1 : x > y ? 1 : a[1] - b[1]) * dir; }).map(p => p[0]);
      } else list = list.slice().sort((a, b) => b.id - a.id);
      return list;
    }
    const anyFilter = () => !!(F.search || Object.keys(F.chips).some(k => F.chips[k] !== "" && F.chips[k] !== undefined) || F.from || F.to || viewAs !== null);
    function clearFilters() {
      F = { search: "", chips: {}, chipLabel: {}, from: "", to: "" };
      viewAs = null; page = 1; $("lhSearch").value = "";
      renderAll();
    }

    /* ---------- rendering ---------- */

    function renderAll() {
      const list = filtered();
      orderIds = list.map(r => r.id);
      $("lhCount").textContent = list.length;
      renderTabs();
      renderToolbar();
      renderTable(list);
      renderBulk();
      fitHeights();
    }

    function renderTabs() {
      const base = rows.filter(r => viewAs === null || r.created_by === viewAs);
      $("lhTabs").innerHTML = cfg.tabs.map(t => `<button type="button" class="lh-tab ${t.key === cat ? "active" : ""}" data-cat="${t.key}">${esc(t.label)} <span class="n">${base.filter(t.test).length}</span></button>`).join("");
    }

    function setChip(id, label, value, on) {
      const el = $(id);
      if (!el) return;
      const icon = el.querySelector("i:not(.bi-chevron-down)");
      el.classList.toggle("on", on);
      el.innerHTML = (icon ? icon.outerHTML : "") + `<span>${esc(label)}${on ? ":" : ""}</span>` + (on ? `<span class="v">${esc(value)}</span>` : "") + '<i class="bi bi-chevron-down"></i>';
    }
    function renderToolbar() {
      cfg.chips.forEach(ch => setChip("dirChip-" + ch.id, ch.label, F.chipLabel[ch.id], !!F.chips[ch.id]));
      setChip("lhChipDate", "Create date", (F.from ? fmtDate(F.from) : "…") + " – " + (F.to ? fmtDate(F.to) : "…"), !!(F.from || F.to));
      if (cfg.isSuperAdmin && $("lhChipOwner")) {
        const a = viewAs === null ? null : cfg.admins.find(x => x.id === viewAs);
        setChip("lhChipOwner", "Created by", a ? a.name : "", viewAs !== null);
        if (viewAs === null) $("lhChipOwner").querySelector("span").textContent = "All users";
      }
      $("lhClear").classList.toggle("d-none", !anyFilter());
    }

    function nameCell(r) {
      return `<div class="lh-name">${cfg.avatar(r)}<span class="lh-name-col"><span class="lh-name-text">${esc(r.name)}</span><span class="lh-name-sub">${esc(cfg.nameSub ? cfg.nameSub(r) : "")}</span></span></div>`;
    }

    function renderTable(list) {
      const totalPages = Math.max(1, Math.ceil(list.length / pageSize));
      page = Math.min(page, totalPages);
      const start = (page - 1) * pageSize;
      const items = list.slice(start, start + pageSize);

      $("lhThead").innerHTML = `<tr>
        <th class="c-chk"><input type="checkbox" class="form-check-input" id="lhSelAll" aria-label="Select all"></th>
        ${cols.map(k => {
          const srt = sort && sort.key === k ? `<i class="bi bi-arrow-${sort.dir === "asc" ? "up" : "down"} srt"></i>` : "";
          return `<th class="${COLS[k].sortv ? "sortable" : ""} ${k === "name" ? "c-name" : ""}" data-col="${k}">${esc(COLS[k].label)}${srt}</th>`;
        }).join("")}
        <th class="c-end"><button type="button" class="lh-plus" id="lhAddCol" title="Choose columns" aria-label="Choose columns"><i class="bi bi-plus-lg"></i></button></th>
      </tr>`;
      const allBox = $("lhSelAll");
      const pageSel = items.filter(r => selected.has(r.id)).length;
      allBox.checked = items.length > 0 && pageSel === items.length;
      allBox.indeterminate = pageSel > 0 && pageSel < items.length;

      const tb = $("lhTbody");
      if (loading) {
        tb.innerHTML = Array.from({ length: 7 }, () => `<tr class="lh-skel">${`<td><span></span></td>`.repeat(cols.length + 2)}</tr>`).join("");
      } else if (!items.length) {
        tb.innerHTML = `<tr><td colspan="${cols.length + 2}" style="height:auto;max-width:none;white-space:normal"><div class="lh-emptybox">
          <div class="ic"><i class="bi bi-${rows.length ? "funnel" : cfg.emptyIcon}"></i></div>
          <h5>${rows.length ? "No " + esc(cfg.nouns) + " match your filters" : esc(cfg.emptyTitle)}</h5>
          <p>${rows.length ? "Try changing the search or filters." : esc(cfg.emptyText)}</p>
          ${rows.length ? '<button class="btn btn-outline-secondary btn-sm" data-act="clear">Clear filters</button>' : `<button class="btn btn-primary btn-sm" data-act="create"><i class="bi bi-plus-lg me-1"></i>${esc(cfg.createLabel)}</button>`}
        </div></td></tr>`;
      } else {
        tb.innerHTML = items.map(r => `<tr data-id="${r.id}" class="${selected.has(r.id) ? "sel" : ""}">
          <td class="c-chk"><input type="checkbox" class="form-check-input row-check" data-id="${r.id}" ${selected.has(r.id) ? "checked" : ""} aria-label="Select"></td>
          ${cols.map(k => k === "name" ? `<td class="c-name">${nameCell(r)}</td>` : `<td>${COLS[k].cell(r)}</td>`).join("")}
          <td class="c-end"><button type="button" class="lh-rowbtn" data-act="rowmenu" data-id="${r.id}" aria-label="More"><i class="bi bi-three-dots"></i></button></td>
        </tr>`).join("");
      }

      const from = list.length ? start + 1 : 0, to = Math.min(list.length, start + pageSize);
      $("lhPageInfo").textContent = `${from}–${to} of ${list.length}`;
      $("lhPageSize").value = String(pageSize);
      $("lhFoot").classList.toggle("d-none", loading || !list.length);
      const pages = [];
      pages.push(`<button type="button" data-page="${page - 1}" ${page <= 1 ? "disabled" : ""} aria-label="Previous"><i class="bi bi-chevron-left"></i></button>`);
      const around = new Set([1, totalPages, page - 1, page, page + 1]);
      let prev = 0;
      for (let p = 1; p <= totalPages; p++) {
        if (!around.has(p)) continue;
        if (p - prev > 1) pages.push(`<button type="button" disabled>…</button>`);
        pages.push(`<button type="button" data-page="${p}" class="${p === page ? "active" : ""}">${p}</button>`);
        prev = p;
      }
      pages.push(`<button type="button" data-page="${page + 1}" ${page >= totalPages ? "disabled" : ""} aria-label="Next"><i class="bi bi-chevron-right"></i></button>`);
      $("lhPages").innerHTML = totalPages > 1 ? pages.join("") : "";
    }

    function renderBulk() {
      $("lhBulk").classList.toggle("show", selected.size > 0);
      $("lhBulkCount").textContent = selected.size;
    }
    function fitHeights() {
      const sc = $("lhScroll");
      const top = sc.getBoundingClientRect().top;
      const foot = $("lhFoot").classList.contains("d-none") ? 0 : $("lhFoot").offsetHeight;
      sc.style.maxHeight = Math.max(280, window.innerHeight - top - foot - 24) + "px";
    }
    window.addEventListener("resize", () => { closePop(); fitHeights(); });

    /* ---------- data ---------- */

    async function load() {
      let d;
      try { d = await apiFetch(cfg.routes.data); } catch (e) { loading = false; renderAll(); return; }
      loading = false;
      if (d.success) {
        rows = d[cfg.listKey];
        if (detail) { const f = rows.find(r => r.id === detail.id); if (f) detail = f; }
      }
      renderAll();
    }

    /* ---------- filter popovers + toolbar ---------- */

    function chipPopover(ch, anchor) {
      const base = rows.filter(r => matches(r, ch.id) && inTab(r));
      const opts = ch.options(rows).map(o => Object.assign({ cnt: base.filter(r => ch.test(r, o.v)).length }, o));
      const cur = F.chips[ch.id];
      const draw = q => {
        const t = (q || "").toLowerCase();
        return `<button type="button" class="it ${!cur ? "active" : ""}" data-v=""><span>${esc(ch.any || "Any")}</span></button>` +
          (opts.filter(o => o.label.toLowerCase().includes(t)).map(o => `<button type="button" class="it ${cur === o.v ? "active" : ""}" data-v="${esc(o.v)}"><span>${esc(o.label)}</span><span class="cnt">${o.cnt}</span></button>`).join("") || '<div class="hd">Nothing found</div>');
      };
      const el = openPop(anchor, (opts.length > 8 ? `<div class="srch"><input type="text" class="form-control form-control-sm" placeholder="Search..."></div>` : "") + `<div class="clist">${draw("")}</div>`, { width: 290 });
      if (!el) return;
      const input = el.querySelector("input");
      if (input) { input.focus(); input.addEventListener("input", () => { el.querySelector(".clist").innerHTML = draw(input.value); }); }
      el.addEventListener("click", e => {
        const b = e.target.closest("[data-v]");
        if (!b) return;
        const v = b.dataset.v;
        if (v === "" || v === cur) { delete F.chips[ch.id]; delete F.chipLabel[ch.id]; }
        else { F.chips[ch.id] = v; F.chipLabel[ch.id] = (opts.find(o => o.v === v) || {}).label || v; }
        page = 1; closePop(); renderAll();
      });
    }

    function bindToolbar() {
      $("dirChips").innerHTML = cfg.chips.map(c => `<button type="button" class="lh-chip" id="dirChip-${c.id}"><i class="bi bi-${c.icon || "funnel"}"></i><span>${esc(c.label)}</span><i class="bi bi-chevron-down"></i></button>`).join("");
      cfg.chips.forEach(ch => $("dirChip-" + ch.id).addEventListener("click", e => chipPopover(ch, e.currentTarget)));

      $("lhSearch").addEventListener("input", debounce(e => { F.search = e.target.value; page = 1; renderAll(); }, 140));
      document.addEventListener("keydown", e => {
        if (e.key === "/" && !/^(INPUT|TEXTAREA|SELECT)$/.test((document.activeElement || {}).tagName || "") && !document.querySelector(".modal.show")) { e.preventDefault(); $("lhSearch").focus(); }
      });
      $("lhClear").addEventListener("click", clearFilters);
      $("lhTabs").addEventListener("click", e => { const t = e.target.closest("[data-cat]"); if (t) { cat = t.dataset.cat; page = 1; renderAll(); } });
      $("lhColsBtn").addEventListener("click", () => bootstrap.Modal.getOrCreateInstance($("lhColsModal")).show());
      $("lhPageSize").addEventListener("change", e => { pageSize = parseInt(e.target.value, 10) || 25; lsSet(K("ps"), pageSize); page = 1; renderAll(); });
      $("lhPages").addEventListener("click", e => {
        const b = e.target.closest("[data-page]");
        if (!b || b.disabled) return;
        page = parseInt(b.dataset.page, 10); renderAll(); $("lhScroll").scrollTop = 0;
      });
      $("dirCreateBtn").addEventListener("click", () => cfg.onCreate(api));

      $("lhChipDate").addEventListener("click", e => {
        const el = openPop(e.currentTarget, `<div class="hd">Create date</div>
          <div class="dates"><input type="date" class="form-control form-control-sm" id="lhDFrom" value="${esc(F.from)}" aria-label="From"><span>–</span><input type="date" class="form-control form-control-sm" id="lhDTo" value="${esc(F.to)}" aria-label="To"></div>
          <div class="dates" style="padding-top:0"><button type="button" class="lh-ghost" data-r="7">7 days</button><button type="button" class="lh-ghost" data-r="30">30 days</button><button type="button" class="lh-link" data-r="0">Clear</button></div>`, { width: 300 });
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
      if (cfg.isSuperAdmin && $("lhChipOwner")) {
        $("lhChipOwner").addEventListener("click", e => {
          const el = openPop(e.currentTarget, `<div class="srch"><input type="text" class="form-control form-control-sm" id="lhOwnQ" placeholder="Search users..."></div><div id="lhOwnList"></div>`, { width: 280 });
          if (!el) return;
          const draw = q => {
            const t = (q || "").toLowerCase();
            $("lhOwnList").innerHTML = [`<button type="button" class="it ${viewAs === null ? "active" : ""}" data-u=""><i class="bi bi-people"></i><span>All users</span><span class="cnt">${rows.length}</span></button>`]
              .concat(cfg.admins.filter(a => a.name.toLowerCase().includes(t)).map(a => `<button type="button" class="it ${viewAs === a.id ? "active" : ""}" data-u="${a.id}">${avatarHtml(a.name, "sm")}<span>${esc(a.name)}</span><span class="cnt">${rows.filter(r => r.created_by === a.id).length}</span></button>`)).join("");
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

      // bulk bar
      $("lhBulkClear").addEventListener("click", () => { selected.clear(); renderAll(); });
      $("lhBulkDelete").addEventListener("click", () => askDelete(Array.from(selected)));
      $("confirmDeleteBtn").addEventListener("click", confirmDelete);
    }

    /* ---------- table events ---------- */

    function rowMenu(anchor, id) {
      const r = rows.find(x => x.id === id);
      if (!r) return;
      const extra = cfg.rowMenu ? cfg.rowMenu(r, api) : [];
      popMenu(anchor, [{ label: "Open", icon: "box-arrow-up-right", fn: () => openDetail(id) }].concat(extra, [{ sep: true }, { label: "Delete", icon: "trash", danger: true, fn: () => askDelete([id]) }]), { align: "right" });
    }

    function bindTable() {
      const tbody = $("lhTbody");
      tbody.addEventListener("click", e => {
        const act = e.target.closest("[data-act]");
        if (act) {
          e.stopPropagation();
          if (act.dataset.act === "rowmenu") rowMenu(act, parseInt(act.dataset.id, 10));
          else if (act.dataset.act === "clear") clearFilters();
          else if (act.dataset.act === "create") cfg.onCreate(api);
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
        const th = e.target.closest("th.sortable[data-col]");
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

    /* ---------- column chooser (popover from "+" and the full modal) ---------- */

    function colsPopover(anchor) {
      const keys = Object.keys(COLS).filter(k => k !== "name");
      const draw = q => {
        const t = (q || "").toLowerCase();
        return keys.filter(k => COLS[k].label.toLowerCase().includes(t)).map(k =>
          `<button type="button" class="it" data-k="${k}"><i class="bi bi-${COLS[k].icon}"></i><span>${esc(COLS[k].label)}</span>${cols.indexOf(k) >= 0 ? '<i class="bi bi-check2 ck"></i>' : ""}</button>`).join("") || '<div class="hd">Nothing found</div>';
      };
      const el = openPop(anchor, `<div class="hd">What should this table show?</div><div class="srch"><input type="text" class="form-control form-control-sm" id="lhPopColQ" placeholder="Search properties..."></div><div id="lhPopCols">${draw("")}</div><hr><button type="button" class="it" data-edit><i class="bi bi-layout-three-columns"></i><span>Edit / reorder columns…</span></button><button type="button" class="it" data-reset><i class="bi bi-arrow-counterclockwise"></i><span>Reset to default</span></button>`, { width: 290, align: "right" });
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
        if (e.target.closest("[data-reset]")) { setCols(cfg.defaultCols); $("lhPopCols").innerHTML = draw(q.value); }
      });
    }
    function renderColsModal() {
      const q = ($("lhColsSearch").value || "").toLowerCase();
      $("lhColsAvail").innerHTML = Object.keys(COLS).filter(k => cols.indexOf(k) < 0 && COLS[k].label.toLowerCase().includes(q)).map(k =>
        `<div class="lh-colrow"><i class="bi bi-${COLS[k].icon}"></i><span>${esc(COLS[k].label)}</span><span class="sp"></span><button type="button" data-add="${k}" title="Add" aria-label="Add ${esc(COLS[k].label)}"><i class="bi bi-plus-lg"></i></button></div>`).join("")
        || '<div class="text-muted" style="font-size:13px;padding:10px">All properties are already selected.</div>';
      $("lhColsN").textContent = cols.length;
      $("lhColsSel").innerHTML = cols.map((k, i) => k === "name"
        ? `<div class="lh-colrow sel"><i class="bi bi-lock-fill lock"></i><span>${esc(COLS[k].label)}</span><span class="sp"></span><span class="text-muted" style="font-size:11.5px">Always first</span></div>`
        : `<div class="lh-colrow sel" draggable="true" data-k="${k}"><i class="bi bi-grip-vertical grip"></i><span>${esc(COLS[k].label)}</span><span class="sp"></span>
            <button type="button" data-up="${k}" ${i <= 1 ? "disabled" : ""} aria-label="Move up"><i class="bi bi-arrow-up"></i></button>
            <button type="button" data-down="${k}" ${i >= cols.length - 1 ? "disabled" : ""} aria-label="Move down"><i class="bi bi-arrow-down"></i></button>
            <button type="button" data-rm="${k}" aria-label="Remove"><i class="bi bi-x-lg"></i></button></div>`).join("");
    }
    function bindColsModal() {
      const modal = $("lhColsModal");
      modal.addEventListener("show.bs.modal", () => { $("lhColsSearch").value = ""; renderColsModal(); });
      $("lhColsSearch").addEventListener("input", renderColsModal);
      $("lhColsReset").addEventListener("click", () => setCols(cfg.defaultCols));
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

    /* ---------- delete ---------- */

    function afterDrawerClosed(fn) {
      const el = $("lhDrawer");
      if (el.classList.contains("show")) {
        el.addEventListener("hidden.bs.offcanvas", function h() { el.removeEventListener("hidden.bs.offcanvas", h); fn(); });
        bootstrap.Offcanvas.getOrCreateInstance(el).hide();
      } else fn();
    }
    function askDelete(ids) {
      const blocked = cfg.canDelete ? ids.map(id => rows.find(r => r.id === id)).filter(r => r && cfg.canDelete(r)) : [];
      if (ids.length === 1 && blocked.length) { toast(cfg.canDelete(blocked[0]), "danger"); return; }
      if (ids.length > 1 && blocked.length === ids.length) { toast("None of the selected " + cfg.nouns + " can be deleted: " + cfg.canDelete(blocked[0]), "danger"); return; }
      pendingDel = ids.filter(id => !blocked.some(b => b.id === id));
      pendingSkipped = blocked.length;
      const n = pendingDel.length;
      $("lhDeleteTitle").textContent = n > 1 ? `Delete ${n} ${cfg.nouns}?` : `Delete this ${cfg.noun}?`;
      $("lhDeleteText").textContent = cfg.deleteText + (blocked.length ? ` ${blocked.length} selected ${blocked.length === 1 ? cfg.noun + " is" : cfg.nouns + " are"} protected and will be skipped.` : "");
      afterDrawerClosed(() => bootstrap.Modal.getOrCreateInstance($("lhDeleteModal")).show());
    }
    async function confirmDelete() {
      const ids = pendingDel.slice();
      if (!ids.length) return;
      const btn = $("confirmDeleteBtn");
      btn.disabled = true;
      try {
        let res, gone;
        if (ids.length === 1) {
          res = await apiFetch(url("destroy", ids[0]), { method: "DELETE" });
          gone = res.success ? ids : [];
        } else {
          res = await reqJson(cfg.routes.bulkDelete, "POST", { ids });
          gone = res.deleted || [];
        }
        if (res.success) {
          rows = rows.filter(r => gone.indexOf(r.id) < 0);
          gone.forEach(i => selected.delete(i));
          if (detail && gone.indexOf(detail.id) >= 0) detail = null;
          renderAll();
          toast((res.message || "Deleted") + (pendingSkipped ? ` ${pendingSkipped} protected ${pendingSkipped === 1 ? cfg.noun : cfg.nouns} skipped.` : ""), "dark");
        } else toast(firstErr(res, "Could not delete."), "danger");
      } catch (e) { /* toast already shown */ }
      btn.disabled = false;
      bootstrap.Modal.getOrCreateInstance($("lhDeleteModal")).hide();
    }

    /* ---------- drawer ---------- */

    async function openDetail(id, opts) {
      opts = opts || {};
      let r = rows.find(x => x.id === id);
      if (!r) {
        let res;
        try { res = await apiFetch(url("show", id)); } catch (e) { return; }
        if (!res.success) { toast(firstErr(res, "Not found."), "danger"); return; }
        r = res[cfg.itemKey]; r._loaded = true;
        rows.unshift(r);
      }
      if (!opts.refresh) {
        detail = r;
        pane = opts.pane || panes[0].key;
        closePop();
        renderDrawer();
        bootstrap.Offcanvas.getOrCreateInstance($("lhDrawer")).show();
      }
      let res;
      try { res = await apiFetch(url("show", id)); } catch (e) { return; }
      if (!res.success) { if (!opts.refresh) toast(firstErr(res, "Could not load details."), "danger"); return; }
      Object.assign(r, res[cfg.itemKey]); r._loaded = true;
      if (detail && detail.id === r.id) renderDrawer(true);
      renderAll();
    }

    function setPane(p, keepScroll) {
      pane = p;
      document.querySelectorAll("#lhDrTabs .lh-dr-tab").forEach(t => t.classList.toggle("active", t.dataset.pane === p));
      panes.forEach(x => { const el = $("dirPane-" + x.key); if (el) el.classList.toggle("d-none", x.key !== p); });
      if (!keepScroll) $("lhDrBody").scrollTop = 0;
    }

    function renderDrawer(keepScroll) {
      const r = detail;
      if (!r) return;
      const scroll = $("lhDrBody").scrollTop;
      const idx = orderIds.indexOf(r.id);
      $("lhDrTop").innerHTML = `
        <div class="lh-dr-bar"><span>${esc(cfg.noun)}</span><span class="sp"></span>
          <button type="button" class="lh-iconbtn" data-dr="prev" ${idx <= 0 ? "disabled" : ""} title="Previous" aria-label="Previous"><i class="bi bi-chevron-up"></i></button>
          <button type="button" class="lh-iconbtn" data-dr="next" ${idx < 0 || idx >= orderIds.length - 1 ? "disabled" : ""} title="Next" aria-label="Next"><i class="bi bi-chevron-down"></i></button>
          <button type="button" class="lh-iconbtn danger" data-dr="delete" title="Delete" aria-label="Delete"><i class="bi bi-trash"></i></button>
          <button type="button" class="lh-iconbtn" data-bs-dismiss="offcanvas" aria-label="Close"><i class="bi bi-x-lg"></i></button>
        </div>
        ${cfg.drawer.header(r)}
        <div class="lh-dr-quick">${cfg.drawer.quick(r)}</div>`;
      $("lhDrTabs").innerHTML = panes.map(p => {
        const b = p.badge ? p.badge(r) : null;
        return `<button type="button" class="lh-dr-tab ${p.key === pane ? "active" : ""}" data-pane="${p.key}">${esc(p.label)}${b != null ? ` <span class="n">${b}</span>` : ""}</button>`;
      }).join("");
      $("lhDrBody").innerHTML = panes.map(p => `<div id="dirPane-${p.key}" class="${p.key === pane ? "" : "d-none"}">${p.render(r)}</div>`).join("");
      if (keepScroll) $("lhDrBody").scrollTop = scroll;
    }

    function prop(r, key, html) {
      const f = cfg.drawer.fields[key];
      const ro = f.kind === "ro";
      const empty = html === "" || html == null;
      return `<div class="lh-prop"><div class="lh-prop-l">${esc(f.label)}</div>
        <div class="lh-prop-v ${ro ? "ro" : ""}" ${ro ? "" : `tabindex="0" role="button" data-edit="${key}" aria-label="Edit ${esc(f.label)}"`}>
          ${empty ? `<span class="ph">${ro ? "—" : "Add " + esc(f.label.toLowerCase().replace("whatsapp", "WhatsApp"))}</span>` : html}${ro ? "" : '<i class="bi bi-pencil pen"></i>'}
        </div></div>`;
    }

    function startEdit(cell) {
      if (!detail || cell.classList.contains("editing") || cell.classList.contains("ro")) return;
      const key = cell.dataset.edit, f = cfg.drawer.fields[key];
      if (f.kind === "custom") { f.edit(cell, detail, api); return; }
      const cur = String(f.get(detail) == null ? "" : f.get(detail));
      let input;
      if (f.kind === "select") {
        input = document.createElement("select");
        input.className = "form-select form-select-sm";
        input.innerHTML = f.options(detail).map(o => `<option value="${esc(o[0])}" ${o[0] === cur ? "selected" : ""}>${esc(o[1])}</option>`).join("");
      } else {
        input = document.createElement("input");
        input.type = f.kind === "email" ? "email" : "text";
        if (f.kind === "tel") input.inputMode = "tel";
        input.className = "form-control form-control-sm";
        input.value = cur;
      }
      const origHtml = cell.innerHTML;
      cell.classList.add("editing");
      cell.innerHTML = "";
      cell.appendChild(input);
      input.focus();
      if (input.select && f.kind !== "select") input.select();
      let done = false;
      const finish = async save => {
        if (done) return;
        done = true;
        cell.classList.remove("editing");
        const v = input.value.trim();
        cell.innerHTML = origHtml;
        if (!save || v === cur.trim()) return;
        cell.classList.add("lh-saving");
        const target = detail;
        let res;
        try { res = await f.save(target, v); } catch (e) { cell.classList.remove("lh-saving"); return; }
        if (res && res.ok) {
          if (res.data) Object.assign(target, res.data);
          renderAll();
          if (detail === target) renderDrawer(true);
          toast("Saved", "dark");
        } else {
          cell.classList.remove("lh-saving");
          toast((res && res.message) || "Could not save.", "danger");
        }
      };
      input.addEventListener("keydown", e => {
        if (e.key === "Enter") { e.preventDefault(); finish(true); }
        else if (e.key === "Escape") { e.preventDefault(); e.stopPropagation(); finish(false); }
      });
      input.addEventListener("blur", () => finish(true));
      if (f.kind === "select") input.addEventListener("change", () => finish(true));
    }

    function bindDrawer() {
      const dr = $("lhDrawer");
      dr.addEventListener("click", e => {
        const tab = e.target.closest(".lh-dr-tab");
        if (tab) { setPane(tab.dataset.pane); return; }
        const act = e.target.closest("[data-dr-act]");
        if (act && detail) { cfg.drawer.onAction(act.dataset.drAct, act, detail, api); return; }
        const b = e.target.closest("[data-dr]");
        if (b && detail) {
          const a = b.dataset.dr, idx = orderIds.indexOf(detail.id);
          if (a === "prev" && idx > 0) openDetail(orderIds[idx - 1], { pane });
          else if (a === "next" && idx >= 0 && idx < orderIds.length - 1) openDetail(orderIds[idx + 1], { pane });
          else if (a === "delete") askDelete([detail.id]);
          return;
        }
        const cell = e.target.closest(".lh-prop-v[data-edit]");
        if (cell) startEdit(cell);
      });
      dr.addEventListener("keydown", e => { if (e.key === "Enter" && e.target.matches(".lh-prop-v[data-edit]")) { e.preventDefault(); startEdit(e.target); } });
      dr.addEventListener("hidden.bs.offcanvas", closePop);
    }

    /* ---------- public api for the page scripts ---------- */

    const api = {
      rows: () => rows,
      get: id => rows.find(r => r.id === id),
      current: () => detail,
      open: openDetail,
      refresh: () => detail ? openDetail(detail.id, { refresh: true }) : Promise.resolve(),
      reload: load,
      renderAll, renderDrawer, prop, setPane,
      addRow(r) { rows = rows.filter(x => x.id !== r.id); rows.unshift(r); page = 1; sort = null; renderAll(); },
      afterDrawerClosed,
    };

    /* ---------- boot ---------- */

    document.addEventListener("DOMContentLoaded", () => {
      bindToolbar(); bindTable(); bindColsModal(); bindDrawer();
      renderAll();
      load().then(() => {
        const params = new URLSearchParams(window.location.search);
        const id = params.get("id");
        if (!id) return;
        openDetail(parseInt(id, 10));
        params.delete("id");
        window.history.replaceState({}, "", window.location.pathname + (params.toString() ? "?" + params : ""));
      });
    });
    return api;
  }

  window.DirUI = { create, esc, lsGet, lsSet, toast, apiFetch, reqJson, firstErr, avatarHtml, companyIcon, initials, fmtDate, ago, digits, waUrl, debounce, ymd, pill, typeLabel, stCls, openPop, closePop, popMenu, entityPicker, DASH, LEAD_TYPES };
})();
</script>