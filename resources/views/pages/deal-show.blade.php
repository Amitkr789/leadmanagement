@extends('layouts.app')
@section('title', $dealName . ' | Deals | Dalal Adda')

@section('content')

<style>
  /* ==========================================================================
     Deal record page (.dr-*)
     Hero (name · amount · stage tracker · quick actions) → key facts → full-width tabs
     Overview | Activity | People & company | Files.   Same tokens as the Leads / Deals pages.
     ========================================================================== */
  .page-content:has(.dr-wrap) { padding-bottom: 28px; }
  .dr-wrap { max-width: 1200px; margin: 0 auto; }

  .sg-new         { --c: var(--info);    --bg: var(--info-bg); }
  .sg-qualified   { --c: var(--primary); --bg: var(--primary-50); }
  .sg-proposal    { --c: var(--warning); --bg: var(--warning-bg); }
  .sg-negotiation { --c: var(--gold);    --bg: var(--gold-bg); }
  .sg-won         { --c: var(--success); --bg: var(--success-bg); }
  .sg-lost        { --c: var(--danger);  --bg: var(--danger-bg); }
  .pr-high   { --c: var(--danger);  --bg: var(--danger-bg); }
  .pr-medium { --c: var(--warning); --bg: var(--warning-bg); }
  .pr-low    { --c: #8A8FA6;        --bg: #EEF1F6; }

  .dr-pill { display: inline-flex; align-items: center; gap: 6px; font-size: 12.5px; font-weight: 700; line-height: 1; padding: 7px 12px; border-radius: 999px; background: var(--bg); color: var(--c); white-space: nowrap; }
  .dr-pill .dot { width: 7px; height: 7px; border-radius: 50%; background: var(--c); }
  button.dr-pill { border: 0; cursor: pointer; transition: filter .12s, box-shadow .12s; }
  button.dr-pill:hover { filter: brightness(.96); box-shadow: 0 0 0 2px rgba(var(--primary-rgb), .12); }
  button.dr-pill .bi { font-size: 10px; opacity: .7; }
  .dr-prio { display: inline-flex; align-items: center; gap: 6px; font-weight: 700; color: var(--c); }
  .dr-empty { color: #B3B7C7; }
  .dr-due { display: inline-flex; align-items: center; gap: 6px; font-weight: 600; }
  .dr-due.late { color: var(--danger); }
  .dr-tag { font-size: 10.5px; font-weight: 700; background: var(--primary-50); color: var(--primary); border-radius: 999px; padding: 2px 8px; white-space: nowrap; }
  .dr-link { border: 0; background: transparent; color: var(--primary); font-weight: 600; font-size: 13px; padding: 4px 6px; }
  .dr-link:hover { text-decoration: underline; }
  .dr-ghost { display: inline-flex; align-items: center; gap: 7px; border: 1px solid var(--border); background: var(--card-bg); border-radius: 10px; padding: 6px 12px; font-size: 13px; font-weight: 600; color: var(--text); }
  .dr-ghost:hover { background: var(--primary-50); border-color: var(--primary-100); color: var(--primary); }
  .dr-iconbtn { width: 32px; height: 32px; border-radius: 50%; border: 0; background: transparent; color: var(--text-muted); display: inline-flex; align-items: center; justify-content: center; transition: background .12s, color .12s; flex-shrink: 0; text-decoration: none; }
  .dr-iconbtn:hover { background: #fff; color: var(--text); box-shadow: var(--shadow-sm); }
  .dr-iconbtn.danger:hover { background: var(--danger-bg); color: var(--danger); }
  .dr-act { display: inline-flex; align-items: center; gap: 8px; border: 1px solid var(--border); background: #fff; border-radius: 999px; padding: 7px 14px; font-size: 13px; font-weight: 700; color: var(--text); text-decoration: none; transition: all .12s; white-space: nowrap; }
  .dr-act i { color: var(--primary); font-size: 14px; }
  .dr-act:hover { background: var(--primary); border-color: var(--primary); color: #fff; transform: translateY(-1px); box-shadow: 0 6px 14px rgba(var(--primary-rgb), .22); }
  .dr-act:hover i { color: #fff; }
  .dr-act.wa i { color: var(--success); } .dr-act.wa:hover { background: var(--success); border-color: var(--success); }
  .dr-act.off { opacity: .4; pointer-events: none; }
  .dr-act.sm { padding: 5px 12px; font-size: 12.5px; }

  /* ---- breadcrumb ---- */
  .dr-crumb { display: flex; align-items: center; gap: 8px; margin-bottom: 12px; font-size: 13px; color: var(--text-muted); }
  .dr-crumb a { display: inline-flex; align-items: center; gap: 4px; font-weight: 700; color: var(--primary); text-decoration: none; }
  .dr-crumb a:hover { text-decoration: underline; }
  .dr-crumb .nm { font-weight: 600; color: var(--text); max-width: 45vw; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .dr-crumb .sp { flex: 1; }

  /* ---- hero ---- */
  .dr-hero { background: linear-gradient(135deg, var(--bg, var(--primary-50)) 0%, #fff 58%); border: 1px solid var(--border); border-top: 4px solid var(--c, var(--primary)); border-radius: 16px; box-shadow: var(--shadow-sm); margin-bottom: 14px; overflow: hidden; }
  .dr-hero-top { display: flex; gap: 16px; align-items: flex-start; padding: 22px 24px 12px; flex-wrap: wrap; }
  .dr-hero-ic { width: 56px; height: 56px; border-radius: 16px; background: var(--c, var(--primary)); color: #fff; font-size: 25px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 8px 18px rgba(0, 0, 0, .14); }
  .dr-hero-id { flex: 1 1 340px; min-width: 0; }
  .dr-hero-chips { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; align-items: center; }
  .dr-chip { display: inline-flex; align-items: center; gap: 7px; border: 1px solid var(--border); background: #fff; border-radius: 999px; padding: 4px 12px; font-size: 12.5px; font-weight: 600; color: var(--text); max-width: 100%; }
  .dr-chip .avatar { width: 20px; height: 20px; font-size: 9px; margin-left: -6px; }
  .dr-chip > i { color: var(--text-muted); }
  .dr-chip.dashed { border-style: dashed; color: var(--primary); background: transparent; }
  .dr-chip.dashed > i { color: var(--primary); }
  button.dr-chip:hover { background: var(--primary-50); border-color: var(--primary-100); color: var(--primary); }
  .dr-chip.ghost { background: transparent; border-color: transparent; color: var(--text-muted); padding-left: 4px; }
  .dr-hero-amt { margin-left: auto; text-align: right; }
  .dr-hero-w { font-size: 12.5px; color: var(--text-muted); font-weight: 600; margin-top: 2px; }
  .dr-hero-pipe { display: flex; gap: 14px; align-items: center; padding: 8px 24px 20px; flex-wrap: wrap; }
  .dr-track { display: flex; gap: 3px; overflow-x: auto; scrollbar-width: none; flex: 1 1 420px; min-width: 0; }
  .dr-track::-webkit-scrollbar { display: none; }
  .dr-track button { flex: 1 0 auto; min-width: 88px; border: 0; background: #E4E7F2; color: var(--text-muted); font-size: 12.5px; font-weight: 700; padding: 11px 16px 11px 22px; clip-path: polygon(0 0, calc(100% - 11px) 0, 100% 50%, calc(100% - 11px) 100%, 0 100%, 11px 50%); transition: filter .12s; }
  .dr-track button:first-child { padding-left: 16px; clip-path: polygon(0 0, calc(100% - 11px) 0, 100% 50%, calc(100% - 11px) 100%, 0 100%); }
  .dr-track button:hover { filter: brightness(.95); }
  .dr-track button.past { background: var(--primary-100); color: var(--primary); }
  .dr-track button.cur { background: var(--c); color: #fff; }
  .dr-pipe-acts { display: flex; gap: 8px; }
  .dr-pipe-acts .btn { font-weight: 700; border-radius: 10px; white-space: nowrap; }
  .dr-banner { display: flex; gap: 10px; align-items: flex-start; border-radius: 12px; padding: 11px 14px; font-size: 13px; margin: 0 24px 18px; }
  .dr-banner b { display: block; }
  .dr-banner > i { margin-top: 2px; }
  .dr-banner.lost { background: var(--danger-bg); color: var(--danger); }
  .dr-banner.won { background: var(--success-bg); color: var(--success); }
  .dr-hero-foot { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; padding: 12px 24px; border-top: 1px solid var(--border); background: rgba(255, 255, 255, .75); }
  .dr-hero-foot .lbl { font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: .05em; margin-right: 4px; }
  .dr-hero-foot .sp { flex: 1; }

  /* inline-editable values */
  .dr-prop-v { display: flex; align-items: center; gap: 8px; min-height: 34px; padding: 4px 10px; margin: 0 -10px; border-radius: 8px; border: 1px solid transparent; cursor: text; font-size: 13.5px; word-break: break-word; }
  .dr-prop-v:hover { background: rgba(var(--primary-rgb), .06); border-color: var(--border); }
  .dr-prop-v .pen { margin-left: auto; opacity: 0; color: var(--text-muted); font-size: 12px; }
  .dr-prop-v:hover .pen, .dr-prop-v:focus-visible .pen { opacity: 1; }
  .dr-prop-v:focus-visible { outline: 2px solid var(--primary-light); }
  .dr-prop-v.ro { cursor: default; }
  .dr-prop-v.ro:hover { background: transparent; border-color: transparent; }
  .dr-prop-v .ph { color: #9EA3B8; font-weight: 500; }
  .dr-prop-v.editing { background: #fff; border-color: var(--primary-light); box-shadow: 0 0 0 3px rgba(var(--primary-rgb), .1); }
  .dr-prop-v input, .dr-prop-v select, .dr-prop-v textarea, .dr-prop-v .input-group { width: 100%; }
  .dr-prop-v.hdr { font-size: 26px; font-weight: 800; letter-spacing: -.02em; line-height: 1.2; min-height: 0; padding: 3px 10px; margin: -3px -10px 0; }
  .dr-prop-v.hdr input { font-size: 20px; font-weight: 800; }
  .dr-prop-v.amt { font-size: 32px; font-weight: 800; letter-spacing: -.02em; justify-content: flex-end; margin: -3px -10px 0 0; min-height: 0; padding: 3px 10px; }
  .dr-prop-v.amt .pen { margin-left: 0; order: -1; }
  .dr-prop-v.tv { font-size: 17px; font-weight: 800; margin: 3px -10px 0; min-height: 32px; }
  .dr-saving { opacity: .55; pointer-events: none; }

  /* ---- key facts strip ---- */
  .dr-facts { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 12px; margin-bottom: 18px; }
  .dr-ft { background: var(--card-bg); border: 1px solid var(--border); border-radius: 14px; padding: 12px 15px; min-width: 0; box-shadow: var(--shadow-sm); text-align: left; display: block; }
  .dr-ft small { display: block; font-size: 11px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: var(--text-muted); }
  .dr-ft .v { font-size: 17px; font-weight: 800; margin-top: 5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .dr-ft .s { display: block; font-size: 12px; color: var(--text-muted); margin-top: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .dr-ft.late { border-color: #F3C4C4; }
  .dr-ft.late .s { color: var(--danger); font-weight: 700; }
  button.dr-ft { cursor: pointer; transition: box-shadow .12s, transform .12s; }
  button.dr-ft:hover { box-shadow: var(--shadow); transform: translateY(-1px); }
  .dr-bar { height: 6px; border-radius: 4px; background: #E9EBF4; overflow: hidden; margin-top: 8px; }
  .dr-bar i { display: block; height: 100%; background: var(--c, var(--primary)); border-radius: 4px; transition: width .25s; }

  /* ---- tabs ---- */
  .dr-tabs { display: flex; gap: 2px; border-bottom: 1px solid var(--border); margin-bottom: 18px; overflow-x: auto; scrollbar-width: none; }
  .dr-tabs::-webkit-scrollbar { display: none; }
  .dr-tab { border: 0; background: transparent; padding: 12px 18px; font-weight: 700; font-size: 14px; color: var(--text-muted); border-bottom: 2px solid transparent; margin-bottom: -1px; display: inline-flex; gap: 8px; align-items: center; white-space: nowrap; }
  .dr-tab:hover { color: var(--text); }
  .dr-tab.active { color: var(--primary); border-bottom-color: var(--primary); }
  .dr-tab .n { font-size: 11px; background: #EEF0F7; border-radius: 999px; padding: 1px 8px; color: var(--text-muted); }
  .dr-tab.active .n { background: var(--primary-50); color: var(--primary); }

  /* ---- cards ---- */
  .dr-card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 14px; box-shadow: var(--shadow-sm); margin-bottom: 16px; overflow: hidden; }
  .dr-card-h { display: flex; align-items: center; gap: 8px; padding: 13px 18px; font-weight: 700; font-size: 14px; }
  .dr-card-h > i:first-child { color: var(--primary); }
  .dr-card-h .sp { flex: 1; }
  .dr-card-h .n { font-size: 11px; font-weight: 700; background: #EEF0F7; color: var(--text-muted); border-radius: 999px; padding: 1px 8px; }
  .dr-card-h .add { border: 0; background: transparent; color: var(--primary); font-weight: 700; font-size: 13px; display: inline-flex; align-items: center; gap: 4px; padding: 3px 9px; border-radius: 8px; }
  .dr-card-h .add:hover { background: var(--primary-50); }
  .dr-none { padding: 24px 18px; text-align: center; color: var(--text-muted); font-size: 13px; }
  .dr-none .btn { margin-top: 10px; }

  /* ---- overview ---- */
  .dr-ov { display: grid; grid-template-columns: minmax(0, 1fr) 360px; gap: 18px; align-items: start; }
  .dr-sum { padding: 0 18px 14px; font-size: 14.5px; line-height: 1.65; }
  .dr-facts-row { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
  .dr-fact { display: inline-flex; align-items: center; gap: 7px; background: #F3F4FA; border-radius: 999px; padding: 4px 11px; font-size: 12.5px; font-weight: 600; }
  .dr-fact i { color: var(--text-muted); }
  .dr-sigs { display: flex; flex-direction: column; gap: 8px; padding: 0 18px 18px; }
  .dr-sig { display: flex; align-items: center; gap: 10px; border-radius: 10px; padding: 9px 12px; font-size: 13px; font-weight: 600; background: var(--info-bg); color: var(--info); }
  .dr-sig.warn { background: var(--warning-bg); color: #8A4B05; }
  .dr-sig.ok { background: var(--success-bg); color: var(--success); }
  .dr-sig .btn { margin-left: auto; white-space: nowrap; font-weight: 700; }
  .dr-up-i { display: flex; align-items: center; gap: 12px; padding: 11px 18px; border-top: 1px solid #F0F1F6; }
  .dr-pc { display: flex; gap: 12px; padding: 2px 18px 12px; align-items: flex-start; }
  .dr-pc .nm { font-weight: 700; font-size: 14px; }
  .dr-pc-acts { display: flex; gap: 8px; flex-wrap: wrap; padding: 0 18px 16px; }

  /* property list (Details card) */
  .dr-prop { display: grid; grid-template-columns: 112px minmax(0, 1fr); gap: 8px; align-items: center; padding: 2px 18px; min-height: 46px; }
  .dr-prop + .dr-prop { border-top: 1px solid #F6F7FB; }
  .dr-prop-l { font-size: 12.5px; color: var(--text-muted); font-weight: 600; }
  .dr-prio-seg { display: flex; gap: 6px; flex-wrap: wrap; }
  .dr-prio-seg button { border: 1.5px solid var(--border); background: #fff; border-radius: 999px; padding: 4px 11px; font-size: 12.5px; font-weight: 700; color: var(--text-muted); display: inline-flex; align-items: center; gap: 6px; transition: all .12s; }
  .dr-prio-seg button:hover { border-color: var(--c); color: var(--c); }
  .dr-prio-seg button.on { border-color: var(--c); background: var(--bg); color: var(--c); }
  .dr-prio-seg button i { font-size: 10px; }
  .dr-pad { height: 8px; }

  /* ---- composer + filters + timeline ---- */
  .dr-narrow { max-width: 860px; margin: 0 auto; }
  .dr-ctypes { display: flex; gap: 2px; padding: 0 12px; border-bottom: 1px solid var(--border); overflow-x: auto; scrollbar-width: none; }
  .dr-ctypes::-webkit-scrollbar { display: none; }
  .dr-ctypes button { border: 0; background: transparent; padding: 13px 14px; font-size: 13px; font-weight: 700; color: var(--text-muted); border-bottom: 2px solid transparent; margin-bottom: -1px; display: inline-flex; align-items: center; gap: 7px; white-space: nowrap; }
  .dr-ctypes button:hover { color: var(--text); }
  .dr-ctypes button.active { color: var(--primary); border-bottom-color: var(--primary); }
  .dr-cbody { padding: 14px 18px 18px; }
  .dr-crow { display: flex; align-items: flex-end; gap: 12px; flex-wrap: wrap; margin-top: 12px; }
  .dr-cwhen { flex: 1 1 260px; }
  .dr-cwhen label { font-size: 12px; font-weight: 600; color: var(--text-muted); margin-bottom: 4px; display: block; }
  .dr-filters { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 12px; }
  .dr-filters button { border: 1px solid var(--border); background: var(--card-bg); border-radius: 999px; padding: 5px 12px; font-size: 12.5px; font-weight: 600; color: var(--text-muted); display: inline-flex; gap: 7px; align-items: center; }
  .dr-filters button:hover { background: var(--primary-50); color: var(--primary); }
  .dr-filters button.on { background: var(--primary-50); border-color: var(--primary-100); color: var(--primary); }
  .dr-filters .n { font-size: 11px; font-weight: 700; opacity: .75; }
  .dr-grp { font-size: 11.5px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: var(--text-muted); margin: 20px 4px 8px; }
  .dr-grp:first-child { margin-top: 0; }
  .dr-grp.up { color: var(--primary); }
  .dr-tl { background: var(--card-bg); border: 1px solid var(--border); border-radius: 14px; box-shadow: var(--shadow-sm); padding: 2px 18px; }
  .dr-card .dr-tl { border: 0; box-shadow: none; padding: 0 18px 4px; border-radius: 0; }
  .dr-tl-i { display: flex; gap: 12px; padding: 13px 0; position: relative; }
  .dr-tl-i + .dr-tl-i { border-top: 1px solid #F0F1F6; }
  .dr-tl-ic { width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 15px; flex-shrink: 0; background: var(--info-bg); color: var(--info); }
  .dr-tl-ic.note { background: var(--gold-bg); color: var(--gold); }
  .dr-tl-ic.call { background: var(--success-bg); color: var(--success); }
  .dr-tl-ic.email { background: var(--primary-50); color: var(--primary); }
  .dr-tl-ic.meeting { background: #F1E9FB; color: #7C3AED; }
  .dr-tl-ic.won { background: var(--success-bg); color: var(--success); }
  .dr-tl-ic.lost, .dr-tl-ic.late { background: var(--danger-bg); color: var(--danger); }
  .dr-tl-ic.mute { background: #EEF1F6; color: var(--text-muted); }
  .dr-tl-b { min-width: 0; flex: 1; }
  .dr-tl-h { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; font-size: 13.5px; }
  .dr-tl-h .sys { font-weight: 600; }
  .dr-tl-at { margin-left: auto; font-size: 12px; color: var(--text-muted); white-space: nowrap; }
  .dr-tl-at.late { color: var(--danger); font-weight: 700; }
  .dr-tl-t { font-size: 13.5px; margin-top: 4px; white-space: pre-wrap; word-break: break-word; color: #3A3F55; }
  .dr-tl-m { font-size: 12px; color: var(--text-muted); margin-top: 4px; }
  .dr-tl-i.sys { padding: 10px 0; align-items: center; }
  .dr-tl-i.sys .dr-tl-ic { width: 28px; height: 28px; font-size: 12px; }
  .dr-tl-i.done .dr-tl-t, .dr-tl-i.done .dr-tl-h b { text-decoration: line-through; opacity: .55; }
  .dr-tl-del { border: 0; background: transparent; color: var(--text-muted); width: 26px; height: 26px; border-radius: 7px; opacity: 0; font-size: 12px; }
  .dr-tl-i:hover .dr-tl-del, .dr-tl-del:focus-visible { opacity: 1; }
  .dr-tl-del:hover { background: var(--danger-bg); color: var(--danger); }
  .dr-check { width: 34px; height: 34px; border-radius: 50%; border: 2px solid #C9CDDD; background: #fff; color: transparent; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all .12s; font-size: 16px; }
  .dr-check:hover { border-color: var(--success); color: var(--success); }
  .dr-check.on { background: var(--success); border-color: var(--success); color: #fff; }
  .dr-tl-i.late .dr-check:not(.on) { border-color: var(--danger); }

  /* ---- people & company ---- */
  .dr-cgrid { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px 20px; padding: 2px 18px 18px; }
  .dr-cgrid small { display: block; font-size: 11px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: var(--text-muted); margin-bottom: 3px; }
  .dr-cgrid div > div, .dr-cgrid div > a { font-size: 13.5px; font-weight: 600; word-break: break-word; }
  .dr-cgrid a { color: var(--primary); text-decoration: none; }
  .dr-cgrid a:hover { text-decoration: underline; }
  .dr-cgrid .cp { border: 0; background: transparent; color: var(--text-muted); padding: 0 4px; font-size: 12px; }
  .dr-cgrid .cp:hover { color: var(--primary); }
  .dr-co-head { display: flex; align-items: center; gap: 14px; padding: 4px 18px 14px; }
  .dr-co-ic { width: 46px; height: 46px; border-radius: 13px; background: var(--primary-50); color: var(--primary); font-size: 20px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
  .dr-co-head .nm { font-weight: 800; font-size: 16px; }
  .dr-people-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(290px, 1fr)); gap: 12px; padding: 2px 18px 18px; }
  .dr-pcard { border: 1px solid var(--border); border-radius: 12px; padding: 14px; background: #fff; display: flex; flex-direction: column; gap: 10px; }
  .dr-pcard-top { display: flex; gap: 12px; align-items: flex-start; }
  .dr-pcard .nm { font-weight: 700; font-size: 14px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
  .dr-pcard .jt { font-size: 12.5px; color: var(--text-muted); margin-top: 1px; }
  .dr-line { display: flex; align-items: center; gap: 6px; font-size: 12.5px; color: var(--text-muted); margin-top: 3px; min-width: 0; }
  .dr-line b { font-weight: 600; flex-shrink: 0; }
  .dr-line a { color: var(--primary); font-weight: 600; text-decoration: none; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; min-width: 0; }
  .dr-line a:hover { text-decoration: underline; }
  .dr-line .cp { border: 0; background: transparent; padding: 0 2px; color: var(--text-muted); font-size: 12px; }
  .dr-line .cp:hover { color: var(--primary); }
  .dr-pcard-acts { display: flex; gap: 6px; flex-wrap: wrap; margin-top: auto; }
  .dr-empty-assoc { text-align: center; padding: 26px 18px 24px; color: var(--text-muted); font-size: 13px; }
  .dr-empty-assoc .ill { width: 58px; height: 58px; border-radius: 18px; background: var(--primary-50); color: var(--primary); font-size: 26px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 10px; }
  .dr-empty-assoc .btn { margin-top: 12px; }

  /* ---- files ---- */
  .dr-drop { display: block; border: 2px dashed #C9CDDD; border-radius: 14px; padding: 24px; text-align: center; color: var(--text-muted); font-size: 13px; background: #fff; cursor: pointer; margin-bottom: 14px; transition: all .12s; }
  .dr-drop:hover, .dr-drop.over { border-color: var(--primary); background: var(--primary-50); color: var(--primary); }
  .dr-drop i { font-size: 28px; display: block; margin-bottom: 4px; }
  .dr-drop small { display: block; margin-top: 4px; opacity: .8; }
  .dr-upl { margin-bottom: 14px; padding: 10px 12px; border: 1px solid var(--border); border-radius: 10px; background: #fff; }
  .dr-upl-info { display: flex; justify-content: space-between; gap: 10px; font-size: 12.5px; margin-bottom: 6px; }
  .dr-upl-info .nm { font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .dr-upl-info .pc { color: var(--primary); font-weight: 700; }
  .dr-upl-track { height: 6px; border-radius: 4px; background: var(--border); overflow: hidden; }
  .dr-upl-fill { height: 100%; width: 0; background: var(--primary); transition: width .15s ease; }
  .dr-files-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 14px; }
  .dr-fcard { background: var(--card-bg); border: 1px solid var(--border); border-radius: 14px; overflow: hidden; box-shadow: var(--shadow-sm); display: flex; flex-direction: column; transition: box-shadow .12s, transform .12s; }
  .dr-fcard:hover { box-shadow: var(--shadow); transform: translateY(-1px); }
  .dr-fth { display: flex; align-items: center; justify-content: center; height: 124px; background: var(--primary-50); color: var(--primary); font-size: 42px; text-decoration: none; overflow: hidden; }
  .dr-fth img { width: 100%; height: 100%; object-fit: cover; }
  .dr-fbody { padding: 10px 14px 4px; min-width: 0; }
  .dr-fbody .nm { font-size: 13.5px; font-weight: 700; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .dr-fbody .mt { font-size: 12px; color: var(--text-muted); margin-top: 2px; }
  .dr-ffoot { display: flex; justify-content: flex-end; gap: 2px; padding: 4px 8px 8px; }

  /* ---- picker (modal) ---- */
  .dr-psearch { position: relative; }
  .dr-psearch i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); }
  .dr-psearch input.form-control { padding-left: 38px !important; }
  .dr-res { border: 1px solid var(--border); border-radius: 12px; overflow: hidden; margin-top: 10px; max-height: 320px; overflow-y: auto; }
  .dr-res button { display: flex; align-items: center; gap: 12px; width: 100%; border: 0; border-top: 1px solid #F0F1F6; background: #fff; padding: 10px 14px; text-align: left; }
  .dr-res button:first-child { border-top: 0; }
  .dr-res button:hover, .dr-res button:focus-visible { background: var(--primary-50); outline: 0; }
  .dr-res .t { font-weight: 700; font-size: 13.5px; }
  .dr-res .s { font-size: 12px; color: var(--text-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .dr-res .r { margin-left: auto; font-size: 11.5px; color: var(--text-muted); white-space: nowrap; text-align: right; line-height: 1.4; }
  .dr-res button > div:not(.dr-co-ic):not(.avatar) { min-width: 0; }
  .dr-res .none { padding: 14px; font-size: 13px; color: var(--text-muted); }
  .dr-newform { border: 1px solid var(--border); border-radius: 12px; padding: 14px; background: #FBFBFE; }
  .dr-newform .form-control { background: #fff; }
  .dr-newform label { font-size: 12.5px; font-weight: 600; margin-bottom: 4px; }
  .dr-nf-h { font-weight: 800; font-size: 14px; margin-bottom: 12px; }
  .dr-alert { border-radius: 10px; padding: 10px 13px; font-size: 13px; margin-top: 12px; display: flex; gap: 10px; align-items: center; background: var(--warning-bg); color: #8A4B05; }
  .dr-alert .btn { margin-left: auto; white-space: nowrap; }

  /* ---- popover ---- */
  .dr-pop { position: fixed; z-index: 2100; background: #fff; border: 1px solid var(--border); border-radius: 14px; box-shadow: var(--shadow-lg); min-width: 200px; max-width: min(360px, calc(100vw - 20px)); max-height: min(420px, calc(100vh - 20px)); overflow: auto; padding: 6px; animation: drPop .12s ease-out; }
  @keyframes drPop { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: none; } }
  .dr-pop .it { display: flex; align-items: center; gap: 10px; width: 100%; border: 0; background: transparent; text-align: left; padding: 8px 10px; border-radius: 8px; font-size: 13.5px; color: var(--text); }
  .dr-pop .it:hover, .dr-pop .it:focus-visible { background: var(--primary-50); outline: 0; }
  .dr-pop .it.danger { color: var(--danger); }
  .dr-pop .it.danger:hover { background: var(--danger-bg); }
  .dr-pop .it .ck { margin-left: auto; color: var(--primary); }
  .dr-pop .it > i:first-child { width: 16px; text-align: center; color: var(--text-muted); }
  .dr-pop .it .sdot { width: 10px; height: 10px; border-radius: 50%; background: var(--c); flex-shrink: 0; }
  .dr-pop hr { margin: 5px 4px; opacity: .6; }
  .dr-pop .hd { font-size: 11px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: var(--text-muted); padding: 8px 10px 4px; }

  /* ---- responsive ---- */
  @media (max-width: 1099.98px) { .dr-facts { grid-template-columns: repeat(3, minmax(0, 1fr)); } .dr-ov { grid-template-columns: minmax(0, 1fr) 320px; } }
  @media (max-width: 991.98px) { .dr-ov { grid-template-columns: 1fr; } }
  @media (max-width: 767.98px) {
    .dr-hero-top, .dr-hero-pipe, .dr-hero-foot { padding-left: 16px; padding-right: 16px; }
    .dr-banner { margin-left: 16px; margin-right: 16px; }
    .dr-hero-amt { margin-left: 0; text-align: left; width: 100%; }
    .dr-prop-v.amt { justify-content: flex-start; margin: 0 -10px; }
    .dr-prop-v.amt .pen { order: 0; margin-left: auto; }
    .dr-prop-v.hdr { font-size: 21px; }
    .dr-facts { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .dr-crumb .nm { max-width: 55vw; }
    .dr-hero-foot .lbl { display: none; }
  }
  @media (prefers-reduced-motion: reduce) { .dr-pop { animation: none; } }
</style>

<div class="page-content">
<div class="dr-wrap" id="drWrap">

  <nav class="dr-crumb" aria-label="Breadcrumb">
    <a href="{{ route('deals.index') }}"><i class="bi bi-chevron-left"></i>Deals</a>
    <span>/</span>
    <span class="nm" id="drCrumbName"></span>
    <span class="sp"></span>
    <button type="button" class="dr-ghost" data-dr="copylink"><i class="bi bi-link-45deg"></i>Copy link</button>
  </nav>

  <header class="dr-hero" id="drHero"></header>

  <div class="dr-facts" id="drFacts"></div>

  <div class="dr-tabs" id="drTabs" role="tablist"></div>

  <!-- OVERVIEW -->
  <section id="drPaneOverview"></section>

  <!-- ACTIVITY -->
  <section id="drPaneActivity" class="d-none">
    <div class="dr-narrow">
      <div class="dr-card" id="drComposer">
        <div class="dr-ctypes" id="drCTypes"></div>
        <div class="dr-cbody">
          <input type="text" class="form-control mb-2 d-none" id="drCSubject" placeholder="Email subject" autocomplete="off">
          <select class="form-select mb-2 d-none" id="drCOutcome">
            <option value="">Call outcome (optional)</option>
            <option>Connected</option><option>No answer</option><option>Left voicemail</option><option>Busy</option><option>Wrong number</option>
          </select>
          <textarea class="form-control" id="drCText" rows="3"></textarea>
          <div class="dr-crow">
            <div class="dr-cwhen">
              <label id="drCWhenLbl" for="drCDate">Happened on</label>
              <div class="d-flex gap-2"><input type="date" class="form-control form-control-sm" id="drCDate"><input type="time" class="form-control form-control-sm" id="drCTime"></div>
            </div>
            <button type="button" class="btn btn-primary" id="drCSubmit">Log note</button>
          </div>
        </div>
      </div>
      <div class="dr-filters" id="drFilters"></div>
      <div id="drTimeline"></div>
    </div>
  </section>

  <!-- PEOPLE & COMPANY -->
  <section id="drPanePeople" class="d-none"></section>

  <!-- FILES -->
  <section id="drPaneFiles" class="d-none">
    <label class="dr-drop" id="drDrop" for="drFileInput">
      <i class="bi bi-cloud-arrow-up"></i><b>Click to choose files</b> or drop them here
      <small>PDF, images, Word / Excel, CSV, ZIP, TXT — up to 10 MB each</small>
    </label>
    <input type="file" id="drFileInput" multiple hidden accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.csv,.zip,.txt">
    <div class="dr-upl d-none" id="drUpl">
      <div class="dr-upl-info"><span class="nm" id="drUplName"></span><span class="pc" id="drUplPct">0%</span></div>
      <div class="dr-upl-track"><div class="dr-upl-fill" id="drUplFill"></div></div>
    </div>
    <div id="drFiles"></div>
  </section>

</div>
</div>

<!-- ADD / CHANGE CONTACT OR COMPANY -->
<div class="modal fade" id="drAssocModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="drAssocTitle">Add contact</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body"><div id="drAssocBody"></div></div>
    </div>
  </div>
</div>

<!-- LOST REASON -->
<div class="modal fade" id="drLostModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Why was this deal lost?</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <select class="form-select mb-2" id="drLostSel"></select>
        <textarea class="form-control" id="drLostTxt" rows="2" placeholder="Details (optional)"></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="drLostOk">Mark as lost</button>
      </div>
    </div>
  </div>
</div>

<!-- DELETE CONFIRM -->
<div class="modal fade" id="drDeleteModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-content-compact">
    <div class="modal-content modal-content-danger">
      <div class="modal-header">
        <h5 class="modal-title">Delete deal?</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body"><p class="mb-0">This deal, its activity and its files will be permanently deleted. The company and contacts will not be deleted. This can't be undone.</p></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="drConfirmDelete">Delete</button>
      </div>
    </div>
  </div>
</div>

<script>
  window.DEAL_RECORD = {
    initial: @json($payload, JSON_HEX_TAG | JSON_HEX_AMP),
    me: @json($me),
    admins: @json($admins),
    stages: @json($stages),
    openStages: @json($openStages),
    sources: @json($sources),
    csrfToken: '{{ csrf_token() }}',
    routes: {
      index: '{{ route('deals.index') }}',
      patch: '{{ url('admin/deals/' . $dealId) }}',
      stage: '{{ url('admin/deals/' . $dealId) }}/stage',
      destroy: '{{ url('admin/deals/' . $dealId) }}',
      record: '{{ url('admin/deals/' . $dealId) }}/record',
      activities: '{{ url('admin/deals/' . $dealId) }}/activities',
      activityItem: '{{ url('admin/deals/activities') }}/__ID__',
      attachments: '{{ url('admin/deals/' . $dealId) }}/attachments',
      attachmentItem: '{{ url('admin/deals/attachments') }}/__ID__',
      // reused from the Leads module
      contactsSearch: '{{ route('leads.contacts.search') }}',
      contactStore: '{{ route('leads.contacts.store') }}',
      companiesSearch: '{{ route('leads.companies.search') }}',
      companyStore: '{{ route('leads.companies.store') }}',
    }
  };

(function () {
  "use strict";

  const CFG = window.DEAL_RECORD;
  const CSRF = CFG.csrfToken, ME = CFG.me, R = CFG.routes;
  const STAGES = CFG.stages, SK = Object.keys(STAGES), OPEN = CFG.openStages, SOURCES = CFG.sources;
  const PRIOS = [["low", "Low"], ["medium", "Medium"], ["high", "High"]];
  const DEAL_TYPES = [["new_business", "New business"], ["existing_business", "Existing business"]];
  const LOST_REASONS = ["Price too high", "Chose a competitor", "No budget", "No response", "Requirements not a fit", "Other"];
  const AVATAR_COLORS = ["#4338CA", "#0891B2", "#B45309", "#16A34A", "#DC2626", "#6D28D9", "#0D9488"];
  const ACT_TYPES = {
    note:    { label: "Note",    icon: "sticky",        ph: "Write a note about this deal…",    btn: "Log note",         when: "Happened on" },
    call:    { label: "Call",    icon: "telephone",     ph: "What was discussed on the call?", btn: "Log call",         when: "Call date" },
    email:   { label: "Email",   icon: "envelope",      ph: "Summarise the email…",            btn: "Log email",        when: "Sent on" },
    meeting: { label: "Meeting", icon: "camera-video",  ph: "Meeting agenda or outcome…",      btn: "Schedule meeting", when: "Scheduled for" },
    task:    { label: "Task",    icon: "check2-square", ph: "What needs to be done?",          btn: "Create task",      when: "Due on" },
  };
  const KIND_ICON = { created: ["plus-circle", ""], stage: ["flag", ""], won: ["trophy-fill", "won"], lost: ["x-octagon-fill", "lost"], field: ["pencil", "mute"], file: ["paperclip", "mute"], contact: ["person-plus", ""], company: ["building", ""] };
  const FILTERS = [["all", "All"], ["note", "Notes"], ["call", "Calls"], ["email", "Emails"], ["meeting", "Meetings"], ["task", "Tasks"], ["system", "Updates"]];
  const TABS = ["overview", "activity", "people", "files"];
  const $ = id => document.getElementById(id);
  const DASH = '<span class="dr-empty">—</span>';

  /* ---------------- helpers ---------------- */

  const esc = s => String(s == null ? "" : s).replace(/[&<>"']/g, c => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));
  function lsGet(k, d) { try { const v = localStorage.getItem(k); return v == null ? d : JSON.parse(v); } catch (e) { return d; } }
  const initials = n => (n || "?").trim().split(/\s+/).slice(0, 2).map(p => p[0]).join("").toUpperCase() || "?";
  function colorFor(name) { let h = 0; for (let i = 0; i < (name || "").length; i++) h = name.charCodeAt(i) + ((h << 5) - h); return AVATAR_COLORS[Math.abs(h) % AVATAR_COLORS.length]; }
  const avatarHtml = (name, size) => `<div class="avatar${size === "lg" ? " avatar-lg" : size === "sm" ? " avatar-sm" : ""}" style="background:${colorFor(name)}">${esc(initials(name))}</div>`;
  const debounce = (fn, ms) => { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; };
  const digits = n => (n || "").replace(/\D/g, "");
  const num = v => parseFloat(v) || 0;
  const money = v => "₹" + num(v).toLocaleString("en-IN", { maximumFractionDigits: num(v) % 1 ? 2 : 0 });
  const plural = (n, w) => n + " " + w + (n === 1 ? "" : "s");
  const waLink = p => { const d = digits(p); return d ? "https://wa.me/" + (d.length === 10 ? "91" + d : d) : null; };
  const siteUrl = w => /^https?:\/\//i.test(w) ? w : "https://" + w;
  const ymd = dt => dt.getFullYear() + "-" + String(dt.getMonth() + 1).padStart(2, "0") + "-" + String(dt.getDate()).padStart(2, "0");
  const hm = dt => String(dt.getHours()).padStart(2, "0") + ":" + String(dt.getMinutes()).padStart(2, "0");
  function fmtDate(d) {
    if (!d) return "";
    const dt = /^\d{4}-\d{2}-\d{2}$/.test(d) ? new Date(d + "T00:00:00") : new Date(d);
    return isNaN(dt) ? d : dt.toLocaleDateString(undefined, { day: "numeric", month: "short", year: "numeric" });
  }
  function fmtWhen(iso) {
    const d = new Date(iso);
    return isNaN(d) ? "" : fmtDate(iso) + " · " + d.toLocaleTimeString([], { hour: "numeric", minute: "2-digit" });
  }
  function daysTo(dateStr) { const t = new Date(); t.setHours(0, 0, 0, 0); return Math.round((new Date(dateStr + "T00:00:00") - t) / 86400000); }
  function dayLabel(dateStr) { const n = daysTo(dateStr); return n === 0 ? "Today" : n === 1 ? "Tomorrow" : n === -1 ? "Yesterday" : fmtDate(dateStr); }
  function ago(iso) {
    const t = new Date(iso).getTime();
    if (!iso || isNaN(t)) return "";
    const s = Math.max(0, (Date.now() - t) / 1000);
    return s < 60 ? "just now" : s < 3600 ? Math.floor(s / 60) + "m ago" : s < 86400 ? Math.floor(s / 3600) + "h ago" : s < 2592000 ? Math.floor(s / 86400) + "d ago" : fmtDate(iso);
  }
  function fileIcon(mime, name) {
    const m = (mime || "") + " " + (name || "").toLowerCase();
    if (m.includes("pdf")) return "bi-file-earmark-pdf";
    if (/image|\.(jpe?g|png)/.test(m)) return "bi-file-earmark-image";
    if (/sheet|excel|csv|\.xlsx?/.test(m)) return "bi-file-earmark-spreadsheet";
    if (/word|\.docx?/.test(m)) return "bi-file-earmark-word";
    if (/zip/.test(m)) return "bi-file-earmark-zip";
    return "bi-file-earmark";
  }
  const isImage = a => /^image\//.test(a.mime_type || "") || /\.(jpe?g|png)$/i.test(a.file_name || "");

  function toast(message, variant) {
    let wrap = $("drToastWrap");
    if (!wrap) {
      wrap = document.createElement("div");
      wrap.id = "drToastWrap";
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
  const firstErr = (res, fb) => (res && (res.message || (res.errors && Object.values(res.errors)[0][0]))) || fb;
  async function copyText(txt, msg) {
    try { await navigator.clipboard.writeText(txt); toast(msg || "Copied", "dark"); } catch (e) { toast("Couldn't copy.", "danger"); }
  }

  /* ---------------- state ---------------- */

  let D = CFG.initial.deal, ACT = CFG.initial.activities || [], ATT = CFG.initial.attachments || [];
  const h0 = location.hash.slice(1).replace("activities", "activity");
  let tab = TABS.indexOf(h0) >= 0 ? h0 : "overview";
  let actFilter = "all", cType = "note", SIGS = [];

  const isOpen = d => OPEN.indexOf(d.stage) >= 0;
  const stLabel = k => (STAGES[k] || {}).label || k;
  const actTime = a => new Date(a.activity_at || a.created_at).getTime();
  const isTaskLike = a => a.type === "task" || a.type === "meeting";
  const isUpcoming = a => isTaskLike(a) && !a.completed_at;
  const isOverdue = a => isUpcoming(a) && !!a.activity_at && actTime(a) < Date.now();
  const sortedActs = () => ACT.slice().sort((a, b) => actTime(b) - actTime(a));
  const byDue = (a, b) => (a.activity_at ? actTime(a) : 9e15) - (b.activity_at ? actTime(b) : 9e15);
  function lastContacted() {
    const t = ACT.filter(a => a.type === "call" || a.type === "email" || (a.type === "meeting" && a.completed_at)).map(actTime).filter(x => x <= Date.now() + 60000);
    return t.length ? Math.max.apply(null, t) : null;
  }
  const editing = () => !!document.querySelector(".dr-prop-v.editing");

  const prioHtml = p => { const x = PRIOS.find(o => o[0] === p); return x ? `<span class="dr-prio pr-${p}"><i class="bi bi-flag-fill"></i>${x[1]}</span>` : DASH; };
  const pill = (stage, interactive) => {
    const inner = `<span class="dot"></span>${esc(stLabel(stage))}`;
    return interactive ? `<button type="button" class="dr-pill sg-${stage}" data-act="stage">${inner}<i class="bi bi-chevron-down"></i></button>` : `<span class="dr-pill sg-${stage}">${inner}</span>`;
  };

  /* ---------------- popovers ---------------- */

  let popEl = null, popAnchor = null, popOpenedAt = 0;
  function closePop() { if (popEl) { popEl.remove(); popEl = null; popAnchor = null; } }
  function openPop(anchor, html, opts) {
    opts = opts || {};
    if (popEl && popAnchor === anchor) { closePop(); return null; }
    closePop();
    const el = document.createElement("div");
    el.className = "dr-pop";
    if (opts.width) el.style.width = opts.width + "px";
    el.innerHTML = html;
    document.body.appendChild(el);
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
      return `<button type="button" class="it ${it.danger ? "danger" : ""}" data-i="${i}">${lead}<span>${esc(it.label)}</span>${it.checked ? '<i class="bi bi-check2 ck"></i>' : ""}</button>`;
    }).join("");
    const el = openPop(anchor, html, opts);
    if (!el) return;
    el.addEventListener("click", e => {
      const b = e.target.closest("[data-i]");
      if (!b) return;
      closePop();
      const it = items[+b.dataset.i];
      if (it.fn) it.fn();
    });
    const first = el.querySelector(".it");
    if (first) first.focus({ preventScroll: true });
  }
  document.addEventListener("mousedown", e => { if (popEl && !popEl.contains(e.target) && !(popAnchor && popAnchor.contains(e.target))) closePop(); });
  document.addEventListener("keydown", e => { if (e.key === "Escape" && popEl) { e.stopPropagation(); closePop(); } }, true);
  window.addEventListener("scroll", e => { if (popEl && !popEl.contains(e.target) && Date.now() - popOpenedAt > 250) closePop(); }, true);
  window.addEventListener("resize", closePop);

  /* ==================== HERO ==================== */

  function tracker() {
    const idx = OPEN.indexOf(D.stage);
    return SK.map(k => {
      const i = OPEN.indexOf(k);
      const cls = k === D.stage ? "cur sg-" + k : (i >= 0 && idx >= 0 && i < idx) ? "past" : "";
      return `<button type="button" class="${cls}" data-stage="${k}">${esc(stLabel(k))}</button>`;
    }).join("");
  }
  function renderHero() {
    $("drCrumbName").textContent = D.name;
    document.title = D.name + " | Deals";
    const open = isOpen(D), p = D.probability != null ? D.probability : 0;
    let banner = "";
    if (D.stage === "lost") banner = `<div class="dr-banner lost"><i class="bi bi-x-octagon-fill"></i><div><b>Deal lost</b>${esc(D.lost_reason || "No reason given")}</div></div>`;
    else if (D.stage === "won") banner = `<div class="dr-banner won"><i class="bi bi-trophy-fill"></i><div><b>Deal won</b>${D.closed_at ? "Closed " + esc(fmtDate(D.closed_at)) : "Congratulations!"}</div></div>`;
    const qas = [["note", "Log note", "sticky"], ["call", "Log call", "telephone"], ["email", "Log email", "envelope"], ["task", "New task", "check2-square"], ["meeting", "Meeting", "camera-video"]];
    $("drHero").className = "dr-hero sg-" + D.stage;
    $("drHero").innerHTML = `
      <div class="dr-hero-top">
        <div class="dr-hero-ic"><i class="bi bi-briefcase"></i></div>
        <div class="dr-hero-id">
          <div class="dr-prop-v hdr" tabindex="0" role="button" data-edit="name" data-kind="text" title="Click to rename" aria-label="Edit deal name">${esc(D.name)}<i class="bi bi-pencil pen"></i></div>
          <div class="dr-hero-chips">
            ${pill(D.stage, true)}
            ${D.company ? `<button type="button" class="dr-chip" data-tab="people" title="View company"><i class="bi bi-building"></i>${esc(D.company.name)}</button>` : '<button type="button" class="dr-chip dashed" data-dr="changeCompany"><i class="bi bi-plus-lg"></i>Add company</button>'}
            <button type="button" class="dr-chip" data-dr="ownerMenu" title="Change deal owner">${D.owner ? avatarHtml(D.owner.name, "sm") + esc(D.owner.name) : '<i class="bi bi-person-dash"></i>Unassigned'}<i class="bi bi-chevron-down" style="font-size:9px"></i></button>
            ${D.deal_type ? `<span class="dr-chip"><i class="bi bi-tag"></i>${esc((DEAL_TYPES.find(t => t[0] === D.deal_type) || [])[1] || "")}</span>` : ""}
            <span class="dr-chip ghost">Created ${esc(ago(D.created_at))}</span>
          </div>
        </div>
        <div class="dr-hero-amt">
          <div class="dr-prop-v amt" tabindex="0" role="button" data-edit="amount" data-kind="number" title="Click to edit amount" aria-label="Edit amount">${money(D.amount)}<i class="bi bi-pencil pen"></i></div>
          <div class="dr-hero-w">${open ? "Weighted " + money(num(D.amount) * p / 100) + " · " + p + "% likely" : D.stage === "won" ? "Won value" : "Lost value"}</div>
        </div>
      </div>
      <div class="dr-hero-pipe"><div class="dr-track" id="drTrack">${tracker()}</div>
        <div class="dr-pipe-acts">${open
          ? '<button type="button" class="btn btn-success btn-sm" data-dr="won"><i class="bi bi-trophy-fill me-1"></i>Mark won</button><button type="button" class="btn btn-outline-danger btn-sm" data-dr="lost"><i class="bi bi-x-circle me-1"></i>Mark lost</button>'
          : '<button type="button" class="btn btn-outline-primary btn-sm" data-dr="reopen"><i class="bi bi-arrow-counterclockwise me-1"></i>Reopen deal</button>'}</div></div>
      ${banner}
      <div class="dr-hero-foot"><span class="lbl">Log</span>${qas.map(q => `<button type="button" class="dr-act" data-qa="${q[0]}"><i class="bi bi-${q[2]}"></i>${q[1]}</button>`).join("")}<span class="sp"></span>
        <button type="button" class="dr-ghost" data-dr="more"><i class="bi bi-three-dots"></i>More</button></div>`;
  }

  /* ---------------- key facts ---------------- */

  function renderFacts() {
    const open = isOpen(D), n = D.close_date ? daysTo(D.close_date) : null, late = open && n !== null && n < 0;
    const p = D.probability != null ? D.probability : 0, lc = lastContacted(), next = ACT.filter(isUpcoming).sort(byDue)[0];
    const closeSub = !D.close_date ? "Click to set a date" : !open ? (D.stage === "won" ? "Closed – won" : "Closed – lost") : n < 0 ? plural(-n, "day") + " overdue" : n === 0 ? "Due today" : "in " + plural(n, "day");
    $("drFacts").innerHTML = `
      <div class="dr-ft ${late ? "late" : ""}"><small>Expected close</small>
        <div class="dr-prop-v tv" tabindex="0" role="button" data-edit="close_date" data-kind="date" aria-label="Edit close date">${D.close_date ? esc(fmtDate(D.close_date)) : '<span class="ph">Set date</span>'}<i class="bi bi-pencil pen"></i></div><span class="s">${esc(closeSub)}</span></div>
      <div class="dr-ft sg-${D.stage}"><small>Win probability</small>
        <div class="dr-prop-v tv" tabindex="0" role="button" data-edit="probability" data-kind="number" aria-label="Edit win probability">${p}%<i class="bi bi-pencil pen"></i></div><div class="dr-bar" style="margin-top:4px"><i style="width:${Math.min(100, p)}%"></i></div></div>
      <button type="button" class="dr-ft" data-dr="prioMenu"><small>Priority</small><div class="v">${prioHtml(D.priority)}</div><span class="s">Click to change</span></button>
      <div class="dr-ft"><small>Last contacted</small><div class="v">${lc ? esc(ago(new Date(lc).toISOString())) : '<span class="dr-empty">Never</span>'}</div><span class="s">${lc ? esc(fmtWhen(new Date(lc).toISOString())) : "No call or email logged"}</span></div>
      ${next
        ? `<button type="button" class="dr-ft ${isOverdue(next) ? "late" : ""}" data-qa="task"><small>Next step</small><div class="v" style="font-size:14.5px">${esc(next.body)}</div><span class="s">${next.activity_at ? (isOverdue(next) ? "Overdue · " : "") + esc(fmtWhen(next.activity_at)) : "No due date"}</span></button>`
        : '<button type="button" class="dr-ft" data-qa="task"><small>Next step</small><div class="v" style="color:var(--primary)"><i class="bi bi-plus-lg me-1"></i>Add a task</div><span class="s">No follow-up scheduled</span></button>'}`;
  }

  /* ---------------- inline edit ---------------- */

  function valHtml(f) {
    switch (f) {
      case "amount": return money(D.amount);
      case "close_date": return D.close_date ? esc(fmtDate(D.close_date)) : "";
      case "probability": return D.probability != null ? D.probability + "%" : "";
      case "deal_type": return esc((DEAL_TYPES.find(t => t[0] === D.deal_type) || [])[1] || "");
      case "owner_id": return D.owner ? avatarHtml(D.owner.name, "sm") + `<span>${esc(D.owner.name)}</span>` : "";
      default: return esc(D[f]);
    }
  }
  function prop(label, field, kind, ro, html) {
    const h = html != null ? html : valHtml(field), empty = h === "" || h == null;
    return `<div class="dr-prop"><div class="dr-prop-l">${esc(label)}</div>
      <div class="dr-prop-v ${ro ? "ro" : ""}" ${ro ? "" : `tabindex="0" role="button" data-edit="${field}" data-kind="${kind}" aria-label="Edit ${esc(label)}"`}>
        ${empty ? `<span class="ph">${ro ? "—" : "Add " + esc(label.toLowerCase())}</span>` : h}${ro ? "" : '<i class="bi bi-pencil pen"></i>'}</div></div>`;
  }
  function selectOpts(field, cur) {
    let o = field === "deal_type" ? [["", "— None —"]].concat(DEAL_TYPES)
      : field === "owner_id" ? [["", "— Unassigned —"]].concat(CFG.admins.map(a => [String(a.id), a.name]))
      : [["", "— None —"]].concat(SOURCES.map(s => [s, s]));
    if (cur && !o.some(x => x[0] === cur)) o = o.concat([[cur, cur]]);
    return o;
  }
  function startEdit(cell) {
    if (cell.classList.contains("editing") || cell.classList.contains("ro")) return;
    const field = cell.dataset.edit, kind = cell.dataset.kind;
    const cur = D[field] == null ? "" : String(D[field]);
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
      cell.innerHTML = orig; cell.classList.add("dr-saving");
      await patchDeal({ [field]: v === "" ? null : (field === "owner_id" ? parseInt(v, 10) : v) });
      cell.classList.remove("dr-saving");
    };
    input.addEventListener("keydown", e => {
      if (e.key === "Enter" && (kind !== "textarea" || e.ctrlKey || e.metaKey)) { e.preventDefault(); finish(true); }
      else if (e.key === "Escape") { e.preventDefault(); e.stopPropagation(); finish(false); }
    });
    input.addEventListener("blur", () => finish(true));
    if (kind === "select") input.addEventListener("change", () => finish(true));
  }
  function jumpEdit(field) {
    const find = () => $("drWrap").querySelector(`.dr-prop-v[data-edit="${field}"]`);
    let c = find();
    if (!c && tab !== "overview") { setTab("overview"); c = find(); }
    if (c) { c.scrollIntoView({ block: "center", behavior: "smooth" }); startEdit(c); }
  }

  async function patchDeal(fields, quiet) {
    let res;
    try { res = await reqJson(R.patch, "PATCH", fields); } catch (e) { return false; }
    if (!res.success) { toast(firstErr(res, "Couldn't save."), "danger"); renderAll(); return false; }
    Object.assign(D, res.deal);
    renderAll();
    if (!quiet) toast("Saved", "dark");
    refreshRecord();
    return true;
  }
  /* pull timeline + files again (the server writes "system" rows for every change) */
  async function refreshRecord() {
    let res;
    try { res = await apiFetch(R.record); } catch (e) { return; }
    if (!res.success) return;
    ACT = res.activities || ACT; ATT = res.attachments || ATT;
    if (!editing()) { renderHero(); renderFacts(); renderOverview(); }
    renderTabs(); renderTimeline(); renderPeople(); renderFiles();
  }

  /* ---------------- stage changes ---------------- */

  async function applyStage(stage, reason) {
    const old = { stage: D.stage, probability: D.probability, lost_reason: D.lost_reason, closed_at: D.closed_at };
    D.stage = stage; D.probability = STAGES[stage].probability; D.lost_reason = stage === "lost" ? reason : null;
    renderAll();
    try {
      const res = await reqJson(R.stage, "PATCH", { stage, lost_reason: reason || null });
      if (!res.success) throw new Error(firstErr(res, "Couldn't update the stage."));
      Object.assign(D, res.deal);
      renderAll();
      toast(stage === "won" ? "Deal won!" : "Stage: " + stLabel(stage));
      refreshRecord();
    } catch (e) {
      Object.assign(D, old);
      renderAll();
      toast(e.message || "Couldn't update the stage.", "danger");
    }
  }
  function changeStage(stage) {
    if (D.stage === stage) return;
    if (stage === "lost") { $("drLostSel").selectedIndex = 0; $("drLostTxt").value = ""; bootstrap.Modal.getOrCreateInstance($("drLostModal")).show(); }
    else applyStage(stage);
  }
  const stageMenu = anchor => popMenu(anchor, SK.map(k => ({ label: stLabel(k), dot: k, checked: k === D.stage, fn: () => changeStage(k) })));

  /* ==================== OVERVIEW ==================== */

  function signals() {
    const s = [], open = isOpen(D), cts = (D.contacts || []).length;
    if (open && D.close_date && daysTo(D.close_date) < 0) s.push({ c: "warn", i: "exclamation-triangle-fill", t: `Close date passed ${plural(-daysTo(D.close_date), "day")} ago`, a: { k: "edit", f: "close_date" }, l: "Update date" });
    if (open && !D.close_date) s.push({ c: "info", i: "calendar-plus", t: "No expected close date set", a: { k: "edit", f: "close_date" }, l: "Set date" });
    if (open && !num(D.amount)) s.push({ c: "info", i: "currency-rupee", t: "No amount added yet", a: { k: "edit", f: "amount" }, l: "Add amount" });
    if (!cts) s.push({ c: "info", i: "person-plus", t: "No contact linked — add the decision maker", a: { k: "contact" }, l: "Add contact" });
    if (!D.company) s.push({ c: "info", i: "building-add", t: "No company linked to this deal", a: { k: "company" }, l: "Add company" });
    if (open) {
      const past = ACT.filter(a => a.type !== "system" && !isUpcoming(a)).map(actTime);
      if (!past.length) s.push({ c: "warn", i: "chat-dots", t: "No activity logged yet", a: { k: "composer", t: "note" }, l: "Log a note" });
      else { const d = Math.floor((Date.now() - Math.max.apply(null, past)) / 86400000); if (d >= 14) s.push({ c: "warn", i: "hourglass-split", t: `No activity for ${plural(d, "day")}`, a: { k: "composer", t: "call" }, l: "Log a call" }); }
      if (!ACT.some(isUpcoming)) s.push({ c: "info", i: "check2-square", t: "No follow-up task scheduled", a: { k: "composer", t: "task" }, l: "Create task" });
    }
    return s;
  }
  function summaryLine() {
    const p = D.probability || 0, amt = `<b>${money(D.amount)}</b>`;
    if (D.stage === "won") return `This deal was <b>won</b>${D.closed_at ? " on " + esc(fmtDate(D.closed_at)) : ""} for ${amt}.`;
    if (D.stage === "lost") return `This deal was <b>lost</b>${D.lost_reason ? " — " + esc(D.lost_reason) : ""}. It was worth ${amt}.`;
    const n = D.close_date ? daysTo(D.close_date) : null;
    const cl = n === null ? "" : n < 0 ? ", and its expected close date has passed" : n === 0 ? ", expected to close <b>today</b>" : `, expected to close in <b>${plural(n, "day")}</b>`;
    return `An <b>open</b> deal worth ${amt} in the <b>${esc(stLabel(D.stage))}</b> stage with a <b>${p}%</b> chance of winning${cl}.`;
  }
  function renderOverview() {
    const cts = D.contacts || [], pc = cts[0], real = ACT.filter(a => a.type !== "system").length;
    SIGS = signals();
    const sigHtml = SIGS.length
      ? SIGS.map((g, i) => `<div class="dr-sig ${g.c}"><i class="bi bi-${g.i}"></i><span>${esc(g.t)}</span><button type="button" class="btn btn-sm btn-light" data-sig="${i}">${esc(g.l)}</button></div>`).join("")
      : '<div class="dr-sig ok"><i class="bi bi-check-circle-fill"></i><span>Everything looks healthy — nothing needs attention.</span></div>';
    const up = ACT.filter(isUpcoming).sort(byDue).slice(0, 4);
    const recent = sortedActs().filter(a => !isUpcoming(a)).slice(0, 6);

    const main = `
      <div class="dr-card"><div class="dr-card-h"><i class="bi bi-stars"></i>Deal summary<span class="sp"></span><span class="dr-tag">Auto-generated</span></div>
        <div class="dr-sum">${summaryLine()}<div class="dr-facts-row">
          <span class="dr-fact"><i class="bi bi-people"></i>${plural(cts.length, "contact")}</span>
          ${D.company ? `<span class="dr-fact"><i class="bi bi-building"></i>${esc(D.company.name)}</span>` : ""}
          <span class="dr-fact"><i class="bi bi-paperclip"></i>${plural(ATT.length, "file")}</span>
          <span class="dr-fact"><i class="bi bi-chat-square-text"></i>${plural(real, "activity")}</span></div></div>
        <div class="dr-sigs">${sigHtml}</div></div>
      <div class="dr-card"><div class="dr-card-h"><i class="bi bi-calendar-check"></i>Next steps<span class="sp"></span><button type="button" class="add" data-qa="task"><i class="bi bi-plus-lg"></i>New task</button></div>
        ${up.length ? up.map(a => `<div class="dr-up-i ${isOverdue(a) ? "late" : ""}"><button type="button" class="dr-check" data-tl="toggle" data-id="${a.id}" title="Mark as done" aria-label="Mark as done"><i class="bi bi-check-lg"></i></button>
            <div style="min-width:0;flex:1"><div style="font-weight:600;font-size:13.5px;word-break:break-word">${esc(a.body)}</div><div class="dr-tl-m"><i class="bi bi-${ACT_TYPES[a.type].icon} me-1"></i>${ACT_TYPES[a.type].label}${a.activity_at ? ` · <span class="${isOverdue(a) ? "text-danger fw-bold" : ""}">${isOverdue(a) ? "Overdue · " : ""}${esc(fmtWhen(a.activity_at))}</span>` : " · No due date"}</div></div></div>`).join("")
          : '<div class="dr-none" style="padding-top:6px">No upcoming tasks or meetings.</div>'}</div>
      <div class="dr-card"><div class="dr-card-h"><i class="bi bi-clock-history"></i>Recent activity<span class="sp"></span><button type="button" class="add" data-dr="allActivity">View all</button></div>
        ${recent.length ? `<div class="dr-tl">${recent.map(a => actItem(a)).join("")}</div>` : '<div class="dr-none">Nothing logged yet.<br><button type="button" class="btn btn-sm btn-outline-primary" data-qa="note">Add the first note</button></div>'}</div>`;

    const primary = pc
      ? `<div class="dr-card"><div class="dr-card-h"><i class="bi bi-person-lines-fill"></i>Primary contact<span class="sp"></span><button type="button" class="add" data-tab="people">View all</button></div>
          <div class="dr-pc">${avatarHtml(pc.name, "lg")}<div style="min-width:0"><div class="nm">${esc(pc.name)}</div>
            <div class="dr-line" style="margin-top:1px">${esc([pc.job_title, D.company ? D.company.name : ""].filter(Boolean).join(" · ") || "—")}</div>
            ${pc.email ? `<div class="dr-line"><b>Email:</b><a href="mailto:${esc(pc.email)}">${esc(pc.email)}</a></div>` : ""}
            ${pc.phone ? `<div class="dr-line"><b>Phone:</b><a href="tel:${esc(pc.phone)}">${esc(pc.phone)}</a></div>` : ""}</div></div>
          <div class="dr-pc-acts">${contactActs(pc)}</div></div>`
      : `<div class="dr-card"><div class="dr-empty-assoc"><div class="ill"><i class="bi bi-person-plus"></i></div><div>Link the person you are dealing with to call, WhatsApp or email in one tap.</div><button type="button" class="btn btn-sm btn-outline-primary" data-dr="addContact">Add contact</button></div></div>`;

    const details = `<div class="dr-card"><div class="dr-card-h"><i class="bi bi-card-list"></i>Deal details</div>
        ${prop("Deal owner", "owner_id", "select")}
        ${prop("Deal type", "deal_type", "select")}
        ${prop("Source", "source", "select")}
        <div class="dr-prop"><div class="dr-prop-l">Priority</div><div class="dr-prio-seg">${PRIOS.map(p => `<button type="button" class="pr-${p[0]} ${D.priority === p[0] ? "on" : ""}" data-prio="${p[0]}"><i class="bi bi-flag-fill"></i>${p[1]}</button>`).join("")}</div></div>
        ${prop("Description", "description", "textarea")}
        ${D.stage === "lost" ? prop("Lost reason", "", "", true, esc(D.lost_reason || "")) : ""}
        ${prop("Created by", "", "", true, D.creator ? esc(D.creator.name) : "")}
        ${prop("Create date", "", "", true, D.created_at ? esc(fmtDate(D.created_at)) : "")}
        <div class="dr-pad"></div></div>`;

    $("drPaneOverview").innerHTML = `<div class="dr-ov"><div>${main}</div><div>${primary}${details}</div></div>`;
  }
  function contactActs(x) {
    const wa = waLink(x.whatsapp || x.phone);
    return `<a class="dr-act sm ${x.phone ? "" : "off"}" ${x.phone ? `href="tel:${esc(x.phone)}"` : ""}><i class="bi bi-telephone"></i>Call</a>
      <a class="dr-act sm wa ${wa ? "" : "off"}" ${wa ? `href="${esc(wa)}" target="_blank" rel="noopener"` : ""}><i class="bi bi-whatsapp"></i>WhatsApp</a>
      <a class="dr-act sm ${x.email ? "" : "off"}" ${x.email ? `href="mailto:${esc(x.email)}"` : ""}><i class="bi bi-envelope"></i>Email</a>`;
  }

  /* ==================== ACTIVITY ==================== */

  function actItem(a) {
    const sys = a.type === "system", od = isOverdue(a), done = !!a.completed_at, up = isUpcoming(a), meta = a.meta || {};
    let ic, cls;
    if (sys) { const k = KIND_ICON[meta.kind] || ["clock-history", "mute"]; ic = k[0]; cls = k[1]; }
    else { ic = ACT_TYPES[a.type].icon; cls = a.type; }
    const when = a.activity_at ? fmtWhen(a.activity_at) : a.type === "task" ? "No due date" : fmtWhen(a.created_at);
    const title = a.type === "email" ? (meta.subject || "Email") : (ACT_TYPES[a.type] || {}).label;
    const tag = a.type === "call" && meta.outcome ? `<span class="dr-tag">${esc(meta.outcome)}</span>` : "";
    const canDel = !sys && (a.created_by === ME.id || ME.role === "super_admin");
    const lead = isTaskLike(a)
      ? `<button type="button" class="dr-check ${done ? "on" : ""}" data-tl="toggle" data-id="${a.id}" title="${done ? "Mark as not done" : "Mark as done"}" aria-label="Toggle done"><i class="bi bi-check-lg"></i></button>`
      : `<div class="dr-tl-ic ${cls}"><i class="bi bi-${ic}"></i></div>`;
    return `<div class="dr-tl-i ${sys ? "sys" : ""} ${od ? "late" : ""} ${done ? "done" : ""}">${lead}
      <div class="dr-tl-b"><div class="dr-tl-h">${sys ? `<span class="sys">${esc(a.body)}</span>` : `<b>${esc(title)}</b>${tag}`}
          <span class="dr-tl-at ${od ? "late" : ""}">${od ? "Overdue · " : up ? "Due · " : ""}${esc(when)}</span>
          ${canDel ? `<button type="button" class="dr-tl-del" data-tl="del" data-id="${a.id}" title="Delete" aria-label="Delete"><i class="bi bi-trash"></i></button>` : ""}</div>
        ${sys ? "" : `<div class="dr-tl-t">${esc(a.body)}</div>`}
        ${a.creator ? `<div class="dr-tl-m">${esc(a.creator.name)}${done ? " · Completed " + esc(ago(a.completed_at)) : ""}</div>` : ""}</div></div>`;
  }
  function renderFilters() {
    $("drFilters").innerHTML = FILTERS.map(f => {
      const n = f[0] === "all" ? ACT.length : ACT.filter(a => a.type === f[0]).length;
      return `<button type="button" class="${actFilter === f[0] ? "on" : ""}" data-filter="${f[0]}">${f[1]}<span class="n">${n}</span></button>`;
    }).join("");
  }
  function renderTimeline() {
    renderFilters();
    const list = ACT.filter(a => actFilter === "all" || a.type === actFilter);
    const upc = list.filter(isUpcoming).sort(byDue);
    const rest = list.filter(a => !isUpcoming(a)).sort((a, b) => actTime(b) - actTime(a));
    let h = "";
    if (upc.length) h += `<div class="dr-grp up">Upcoming</div><div class="dr-tl">${upc.map(actItem).join("")}</div>`;
    let cur = "", buf = [];
    const flush = () => { if (buf.length) h += `<div class="dr-grp">${esc(cur)}</div><div class="dr-tl">${buf.join("")}</div>`; buf = []; };
    rest.forEach(a => {
      const g = new Date(actTime(a)).toLocaleDateString(undefined, { month: "long", year: "numeric" });
      if (g !== cur) { flush(); cur = g; }
      buf.push(actItem(a));
    });
    flush();
    $("drTimeline").innerHTML = h || `<div class="dr-card"><div class="dr-none">${actFilter === "all" ? "No activity yet. Log a note, call or task above." : "Nothing in this category."}</div></div>`;
  }

  function setCType(t) {
    cType = t;
    const c = ACT_TYPES[t], now = new Date();
    $("drCTypes").innerHTML = Object.keys(ACT_TYPES).map(k => `<button type="button" class="${k === t ? "active" : ""}" data-ct="${k}"><i class="bi bi-${ACT_TYPES[k].icon}"></i>${ACT_TYPES[k].label}</button>`).join("");
    $("drCText").placeholder = c.ph;
    $("drCSubject").classList.toggle("d-none", t !== "email");
    $("drCOutcome").classList.toggle("d-none", t !== "call");
    $("drCWhenLbl").textContent = c.when;
    $("drCSubmit").textContent = c.btn;
    if (t === "task" || t === "meeting") { $("drCDate").value = ymd(new Date(now.getTime() + 86400000)); $("drCTime").value = "10:00"; }
    else { $("drCDate").value = ymd(now); $("drCTime").value = hm(now); }
  }
  function openComposer(t) {
    setTab("activity");
    setCType(t);
    $("drComposer").scrollIntoView({ block: "center", behavior: "smooth" });
    $("drCText").focus({ preventScroll: true });
  }
  async function submitActivity() {
    const body = $("drCText").value.trim();
    if (!body) { toast("Write something first.", "danger"); $("drCText").focus(); return; }
    const btn = $("drCSubmit");
    btn.disabled = true;
    try {
      const res = await reqJson(R.activities, "POST", {
        type: cType, body, subject: $("drCSubject").value.trim() || null, outcome: $("drCOutcome").value || null,
        activity_date: $("drCDate").value || null, activity_time: $("drCTime").value || null,
      });
      if (!res.success) { toast(firstErr(res, "Couldn't save."), "danger"); return; }
      ACT.unshift(res.activity);
      $("drCText").value = ""; $("drCSubject").value = ""; $("drCOutcome").value = "";
      setCType(cType);
      refreshViews();
      toast(res.message || "Saved");
    } catch (e) { /* toast shown */ } finally { btn.disabled = false; }
  }
  function refreshViews() { renderFacts(); renderTabs(); renderTimeline(); if (!editing()) renderOverview(); }
  async function timelineAction(el) {
    const id = parseInt(el.dataset.id, 10), a = ACT.find(x => x.id === id);
    if (!a) return;
    if (el.dataset.tl === "toggle") {
      const res = await reqJson(R.activityItem.replace("__ID__", id), "PATCH", { completed: !a.completed_at });
      if (!res.success) { toast(firstErr(res, "Couldn't update."), "danger"); return; }
      Object.assign(a, res.activity);
      refreshViews();
      toast(a.completed_at ? "Marked as done" : "Marked as not done", "dark");
    } else if (el.dataset.tl === "del") {
      if (!confirm("Delete this " + (ACT_TYPES[a.type] || {}).label.toLowerCase() + "?")) return;
      const res = await apiFetch(R.activityItem.replace("__ID__", id), { method: "DELETE" });
      if (!res.success) { toast(firstErr(res, "Couldn't delete."), "danger"); return; }
      ACT = ACT.filter(x => x.id !== id);
      refreshViews();
      toast("Deleted", "dark");
    }
  }

  /* ==================== PEOPLE & COMPANY ==================== */

  function renderPeople() {
    const c = D.company, cts = D.contacts || [];
    const cell = (label, inner) => inner ? `<div><small>${label}</small>${inner}</div>` : "";
    const companyHtml = c
      ? `<div class="dr-card"><div class="dr-card-h"><i class="bi bi-building"></i>Company<span class="sp"></span><button type="button" class="add" data-dr="changeCompany">Change</button><button type="button" class="add" style="color:var(--danger)" data-dr="removeCompany">Remove</button></div>
          <div class="dr-co-head"><div class="dr-co-ic"><i class="bi bi-building"></i></div><div class="nm">${esc(c.name)}</div></div>
          ${c.website || c.phone || c.gst_number || c.address
            ? `<div class="dr-cgrid">${cell("Website", c.website ? `<a href="${esc(siteUrl(c.website))}" target="_blank" rel="noopener">${esc(c.website)}</a>` : "")}${cell("Phone", c.phone ? `<a href="tel:${esc(c.phone)}">${esc(c.phone)}</a>` : "")}${cell("GST number", c.gst_number ? `<div>${esc(c.gst_number)}<button type="button" class="cp" data-copy="${esc(c.gst_number)}" title="Copy GST number" aria-label="Copy GST number"><i class="bi bi-copy"></i></button></div>` : "")}${cell("Address", c.address ? `<div>${esc(c.address)}</div>` : "")}</div>`
            : '<div class="dr-none" style="padding-top:0">No company details saved yet.</div>'}</div>`
      : `<div class="dr-card"><div class="dr-card-h"><i class="bi bi-building"></i>Company</div><div class="dr-empty-assoc"><div class="ill"><i class="bi bi-buildings"></i></div><div>See the business or organisation associated with this deal — GST, phone and address appear here.</div><button type="button" class="btn btn-sm btn-outline-primary" data-dr="changeCompany">Add company</button></div></div>`;

    const peopleHtml = `<div class="dr-card"><div class="dr-card-h"><i class="bi bi-people"></i>Contacts<span class="n">${cts.length}</span><span class="sp"></span><button type="button" class="add" data-dr="addContact"><i class="bi bi-plus-lg"></i>Add contact</button></div>`
      + (cts.length ? `<div class="dr-people-grid">${cts.map((x, i) => `<div class="dr-pcard"><div class="dr-pcard-top">${avatarHtml(x.name, "lg")}<div style="min-width:0;flex:1">
            <div class="nm">${esc(x.name)}${i === 0 ? '<span class="dr-tag">Primary</span>' : ""}</div>${x.job_title ? `<div class="jt">${esc(x.job_title)}</div>` : ""}
            <div class="dr-line"><b>Email:</b>${x.email ? `<a href="mailto:${esc(x.email)}">${esc(x.email)}</a><button type="button" class="cp" data-copy="${esc(x.email)}" title="Copy email" aria-label="Copy email"><i class="bi bi-copy"></i></button>` : DASH}</div>
            <div class="dr-line"><b>Phone:</b>${x.phone ? `<a href="tel:${esc(x.phone)}">${esc(x.phone)}</a><button type="button" class="cp" data-copy="${esc(x.phone)}" title="Copy phone" aria-label="Copy phone"><i class="bi bi-copy"></i></button>` : DASH}</div></div>
            <button type="button" class="dr-iconbtn danger" data-dr="removeContact" data-id="${x.id}" title="Remove from deal" aria-label="Remove ${esc(x.name)}"><i class="bi bi-x-lg"></i></button></div>
          <div class="dr-pcard-acts">${contactActs(x)}</div></div>`).join("")}</div>`
        : '<div class="dr-empty-assoc"><div class="ill"><i class="bi bi-person-plus"></i></div><div>Link the people you are dealing with — decision maker, accountant, purchase manager.</div><button type="button" class="btn btn-sm btn-outline-primary" data-dr="addContact">Add contact</button></div>')
      + "</div>";
    $("drPanePeople").innerHTML = companyHtml + peopleHtml;
  }

  /* ==================== FILES ==================== */

  function renderFiles() {
    $("drFiles").innerHTML = ATT.length ? `<div class="dr-files-grid">${ATT.map(f => `<div class="dr-fcard">
        <a class="dr-fth" href="${esc(f.file_url)}" target="_blank" rel="noopener" aria-label="Open ${esc(f.file_name)}">${isImage(f) ? `<img src="${esc(f.file_url)}" alt="" loading="lazy">` : `<i class="bi ${fileIcon(f.mime_type, f.file_name)}"></i>`}</a>
        <div class="dr-fbody"><div class="nm" title="${esc(f.file_name)}">${esc(f.file_name)}</div><div class="mt">${esc(f.file_size_label || "")}${f.uploader ? " · " + esc(f.uploader.name) : ""} · ${esc(ago(f.created_at))}</div></div>
        <div class="dr-ffoot"><a class="dr-iconbtn" href="${esc(f.file_url)}" target="_blank" rel="noopener" title="Open / download" aria-label="Open file"><i class="bi bi-download"></i></a>
          ${f.uploaded_by === ME.id || ME.role === "super_admin" ? `<button type="button" class="dr-iconbtn danger" data-delfile="${f.id}" title="Delete" aria-label="Delete file"><i class="bi bi-trash"></i></button>` : ""}</div></div>`).join("")}</div>`
      : '<div class="dr-card"><div class="dr-none">No files attached yet.<br>Add quotations, proposals, contracts or screenshots.</div></div>';
  }
  function uploadOne(file, i, n) {
    return new Promise(resolve => {
      $("drUplName").textContent = file.name + (n > 1 ? `  (${i} of ${n})` : "");
      $("drUplPct").textContent = "0%"; $("drUplFill").style.width = "0%";
      $("drUpl").classList.remove("d-none");
      const fd = new FormData();
      fd.append("attachment", file);
      const xhr = new XMLHttpRequest();
      xhr.open("POST", R.attachments);
      xhr.setRequestHeader("X-CSRF-TOKEN", CSRF);
      xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
      xhr.setRequestHeader("Accept", "application/json");
      xhr.upload.addEventListener("progress", e => { if (e.lengthComputable) { const p = Math.round(e.loaded / e.total * 100); $("drUplPct").textContent = p + "%"; $("drUplFill").style.width = p + "%"; } });
      xhr.onload = () => {
        if (xhr.status === 401 || xhr.status === 419) { toast("Your session has expired. Please log in again.", "danger"); return resolve(); }
        let res; try { res = JSON.parse(xhr.responseText); } catch (e) { res = { success: false }; }
        if (res.success) { ATT.unshift(res.attachment); renderFiles(); renderTabs(); toast(file.name + " uploaded"); }
        else toast(firstErr(res, file.name + " couldn't be uploaded."), "danger");
        resolve();
      };
      xhr.onerror = () => { toast("Upload failed — check your internet connection.", "danger"); resolve(); };
      xhr.send(fd);
    });
  }
  async function uploadFiles(fileList) {
    const ok = /\.(pdf|jpe?g|png|docx?|xlsx?|csv|zip|txt)$/i;
    const q = Array.from(fileList).filter(f => {
      if (!ok.test(f.name)) { toast(f.name + ": file type not allowed", "danger"); return false; }
      if (f.size > 10485760) { toast(f.name + " is larger than 10 MB", "danger"); return false; }
      return true;
    });
    for (let i = 0; i < q.length; i++) await uploadOne(q[i], i + 1, q.length);
    $("drUpl").classList.add("d-none");
    $("drFileInput").value = "";
    if (q.length) { if (!editing()) renderOverview(); refreshRecord(); }
  }

  /* ==================== tabs ==================== */

  function renderTabs() {
    const n = ACT.filter(a => a.type !== "system").length, pn = (D.contacts || []).length + (D.company ? 1 : 0);
    const defs = [["overview", "Overview", ""], ["activity", "Activity", n ? `<span class="n">${n}</span>` : ""], ["people", "People & company", pn ? `<span class="n">${pn}</span>` : ""], ["files", "Files", ATT.length ? `<span class="n">${ATT.length}</span>` : ""]];
    $("drTabs").innerHTML = defs.map(t => `<button type="button" role="tab" aria-selected="${tab === t[0]}" class="dr-tab ${tab === t[0] ? "active" : ""}" data-tab="${t[0]}">${t[1]}${t[2]}</button>`).join("");
    TABS.forEach(k => $("drPane" + k.charAt(0).toUpperCase() + k.slice(1)).classList.toggle("d-none", tab !== k));
  }
  function setTab(t) {
    tab = t;
    try { history.replaceState(null, "", t === "overview" ? location.pathname + location.search : "#" + t); } catch (e) { /* ignore */ }
    renderTabs();
  }

  /* ---- contact / company picker (same flow as Leads: search → or create with full fields, duplicate warning) ---- */

  function Picker(kind, opts) {
    const isC = kind === "contact", noun = isC ? "contact" : "company";
    const blank = () => isC ? { name: "", email: "", phone: "", whatsapp: "", job_title: "" } : { name: "", website: "", phone: "", gst_number: "", address: "" };
    const S = { mode: "search", q: "", results: null, form: blank(), dupe: null, busy: false };
    let root = null, token = 0, shown = [];

    function resultRow(x, i) {
      if (isC) {
        const n = x.leads_count || 0;
        return `<button type="button" data-i="${i}">${avatarHtml(x.name, "sm")}<div style="flex:1"><div class="t">${esc(x.name)}</div><div class="s">${esc([x.email, x.phone].filter(Boolean).join(" · ") || "—")}</div>${x.company ? `<div class="s"><i class="bi bi-building me-1"></i>${esc(x.company.name)}</div>` : ""}</div><span class="r">${n} lead${n === 1 ? "" : "s"}</span></button>`;
      }
      return `<button type="button" data-i="${i}"><div class="dr-co-ic" style="width:32px;height:32px;font-size:15px;border-radius:9px"><i class="bi bi-building"></i></div><div style="flex:1"><div class="t">${esc(x.name)}</div><div class="s">${esc([x.website, x.gst_number].filter(Boolean).join(" · ") || "—")}</div></div><span class="r">${x.contacts_count || 0} contacts<br>${x.leads_count || 0} leads</span></button>`;
    }
    function drawResults() {
      const box = root && root.querySelector("[data-res]");
      if (!box) return;
      if (S.results === null) { box.innerHTML = '<div class="none">Loading…</div>'; return; }
      const ex = opts.exclude ? opts.exclude() : [];
      shown = S.results.filter(x => ex.indexOf(x.id) < 0);
      box.innerHTML = shown.length ? shown.map(resultRow).join("") : `<div class="none">${S.q ? `No ${noun} found — create a new one.` : `No ${noun} to show yet — create the first one.`}</div>`;
    }
    async function search() {
      const my = ++token;
      let res;
      try { res = await apiFetch((isC ? R.contactsSearch : R.companiesSearch) + "?q=" + encodeURIComponent(S.q.trim())); } catch (e) { return; }
      if (my !== token) return;
      S.results = (isC ? res.contacts : res.companies) || [];
      drawResults();
    }
    const dsearch = debounce(search, 220);
    function fieldsHtml() {
      const f = S.form, id = n => `pk-${kind}-${n}`;
      const inp = (key, label, extra, col) => `<div class="${col || "col-md-6"}"><label class="form-label" for="${id(key)}">${label}</label><input class="form-control" id="${id(key)}" data-f="${key}" value="${esc(f[key])}" autocomplete="off" ${extra || ""}></div>`;
      return isC
        ? inp("name", "Full name *", 'placeholder="e.g. Rahul Sharma"', "col-12") + inp("email", "Email", 'type="email" placeholder="rahul&#64;company.com"') + inp("phone", "Phone number", 'inputmode="tel" placeholder="+91 98765 43210"') + inp("whatsapp", "WhatsApp", 'inputmode="tel" placeholder="Leave empty if same as phone"') + inp("job_title", "Job title", 'placeholder="e.g. Purchase Manager"')
        : inp("name", "Company name *", 'placeholder="e.g. Sharma Traders Pvt Ltd"', "col-12") + inp("website", "Website", 'placeholder="www.example.com"') + inp("phone", "Company phone", 'inputmode="tel"') + inp("gst_number", "GST number", "") + inp("address", "Address", "");
    }
    function render() {
      if (!root) return;
      if (S.mode === "new") {
        const dup = S.dupe && (isC ? S.dupe.contact : S.dupe.company);
        root.innerHTML = `<div class="dr-newform"><div class="dr-nf-h">New ${noun}</div><div class="row g-3">${fieldsHtml()}</div>
          ${S.dupe ? `<div class="dr-alert"><i class="bi bi-exclamation-triangle-fill"></i><span>${esc(S.dupe.message || "A matching " + noun + " already exists.")}</span>${dup ? `<button type="button" class="btn btn-sm btn-warning" data-usedupe>Use ${esc(dup.name)}</button>` : ""}</div>` : ""}
          <div class="d-flex align-items-center gap-2 mt-3"><button type="button" class="dr-link p-0" data-back><i class="bi bi-arrow-left me-1"></i>Search existing ${noun}</button>
            <button type="button" class="btn btn-primary btn-sm ms-auto" data-save ${S.busy ? "disabled" : ""}>${S.busy ? '<span class="spinner-border spinner-border-sm me-1"></span>' : ""}Save ${noun}</button></div></div>`;
      } else {
        root.innerHTML = `<div class="dr-psearch"><i class="bi bi-search"></i><input type="text" class="form-control" data-q value="${esc(S.q)}" placeholder="${isC ? "Search by name, phone or email..." : "Search by company name or GST..."}" autocomplete="off"></div>
          <div class="dr-res" data-res></div>
          <button type="button" class="btn btn-outline-primary mt-3" data-new><i class="bi bi-${isC ? "person-plus" : "building-add"} me-1"></i>Create new ${noun}</button>`;
        drawResults();
      }
    }
    function focusFirst() { const i = root && root.querySelector(S.mode === "new" ? "[data-f]" : "[data-q]"); if (i) try { i.focus({ preventScroll: true }); } catch (e) { /* ignore */ } }
    function reset() { S.mode = "search"; S.q = ""; S.results = null; S.form = blank(); S.dupe = null; S.busy = false; }
    function pick(x) { if (x) { S.q = ""; S.results = null; opts.onPick(x); } }
    function openNew() {
      const q = S.q.trim(), f = blank();
      if (q) { if (isC && q.includes("@")) f.email = q; else if (isC && digits(q).length >= 6) f.phone = q; else f.name = q; }
      S.form = f; S.dupe = null; S.mode = "new"; render(); focusFirst();
    }
    async function submit() {
      if (S.busy) return;
      const f = S.form;
      if (!f.name.trim()) { toast(isC ? "Name is required." : "Company name is required.", "danger"); focusFirst(); return; }
      if (isC && !f.phone.trim() && !f.email.trim()) { toast("Add a phone number or an email.", "danger"); const p = root && root.querySelector('[data-f="phone"]'); if (p) p.focus(); return; }
      S.busy = true; render();
      let res;
      try { res = await reqJson(isC ? R.contactStore : R.companyStore, "POST", f); } catch (e) { S.busy = false; render(); return; }
      S.busy = false;
      const made = isC ? res.contact : res.company;
      if (res.success && made) { reset(); opts.onPick(made); }
      else if (res.duplicate) { S.dupe = res; render(); }
      else { render(); toast(firstErr(res, "Couldn't create the " + noun + "."), "danger"); }
    }
    function mount(el) {
      root = el;
      root.onclick = e => {
        const b = e.target.closest("button");
        if (!b || !root.contains(b)) return;
        if (b.dataset.i !== undefined) pick(shown[+b.dataset.i]);
        else if (b.hasAttribute("data-new")) openNew();
        else if (b.hasAttribute("data-back")) { S.mode = "search"; S.dupe = null; render(); focusFirst(); }
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
        if (t.dataset.q !== undefined) { e.preventDefault(); e.stopPropagation(); if (shown[0]) pick(shown[0]); else if (S.results && !S.results.length) openNew(); }
        else if (t.dataset.f) { e.preventDefault(); e.stopPropagation(); submit(); }
      };
      render();
      if (S.mode === "search" && S.results === null) search();
      focusFirst();
    }
    return { mount, reset };
  }

  let assocKind = "contact";
  const hideAssoc = () => bootstrap.Modal.getOrCreateInstance($("drAssocModal")).hide();
  const pcContact = Picker("contact", {
    exclude: () => (D.contacts || []).map(c => c.id),
    onPick: c => { hideAssoc(); const f = { contact_ids: (D.contacts || []).map(x => x.id).concat(c.id) }; if (!D.company && c.company) f.company_id = c.company.id; patchDeal(f); },
  });
  const pcCompany = Picker("company", { onPick: c => { hideAssoc(); patchDeal({ company_id: c.id }); } });
  function openAssoc(kind) {
    assocKind = kind;
    $("drAssocTitle").textContent = kind === "contact" ? "Add contact" : D.company ? "Change company" : "Add company";
    (kind === "contact" ? pcContact : pcCompany).reset();
    bootstrap.Modal.getOrCreateInstance($("drAssocModal")).show();
  }

  /* ==================== events ==================== */

  function sigAction(g) {
    if (!g) return;
    if (g.a.k === "edit") jumpEdit(g.a.f);
    else if (g.a.k === "contact") openAssoc("contact");
    else if (g.a.k === "company") openAssoc("company");
    else if (g.a.k === "composer") openComposer(g.a.t);
  }
  const showDelete = () => bootstrap.Modal.getOrCreateInstance($("drDeleteModal")).show();
  const copyLink = () => copyText(location.origin + location.pathname, "Link copied");
  function dealMenuItems() {
    return (isOpen(D) ? [{ label: "Mark as won", icon: "trophy", fn: () => changeStage("won") }, { label: "Mark as lost", icon: "x-circle", fn: () => changeStage("lost") }]
      : [{ hd: "Reopen deal as" }].concat(OPEN.map(k => ({ label: stLabel(k), dot: k, fn: () => applyStage(k) }))))
      .concat([{ sep: true }, { label: "Copy link", icon: "link-45deg", fn: copyLink }, { label: "Delete deal", icon: "trash", danger: true, fn: showDelete }]);
  }
  function act(name, el) {
    switch (name) {
      case "copylink": copyLink(); break;
      case "more": popMenu(el, dealMenuItems(), { align: "right" }); break;
      case "ownerMenu": popMenu(el, [{ label: "Unassigned", icon: "person-dash", checked: !D.owner_id, fn: () => patchDeal({ owner_id: null }) }].concat(CFG.admins.map(a => ({ label: a.name, icon: "person", checked: D.owner_id === a.id, fn: () => { if (D.owner_id !== a.id) patchDeal({ owner_id: a.id }); } })))); break;
      case "prioMenu": popMenu(el, PRIOS.map(p => ({ label: p[1], icon: "flag-fill", checked: D.priority === p[0], fn: () => { if (D.priority !== p[0]) patchDeal({ priority: p[0] }); } }))); break;
      case "addContact": openAssoc("contact"); break;
      case "changeCompany": openAssoc("company"); break;
      case "removeCompany": patchDeal({ company_id: null }); break;
      case "removeContact": patchDeal({ contact_ids: (D.contacts || []).map(x => x.id).filter(i => i !== parseInt(el.dataset.id, 10)) }); break;
      case "allActivity": setTab("activity"); break;
      case "won": changeStage("won"); break;
      case "lost": changeStage("lost"); break;
      case "reopen": popMenu(el, [{ hd: "Reopen deal as" }].concat(OPEN.map(k => ({ label: stLabel(k), dot: k, fn: () => applyStage(k) })))); break;
    }
  }

  function bindAll() {
    const wrap = $("drWrap");
    wrap.addEventListener("click", e => {
      const t = e.target;
      let el;
      if ((el = t.closest("[data-dr]"))) { act(el.dataset.dr, el); return; }
      if ((el = t.closest("[data-qa]"))) { openComposer(el.dataset.qa); return; }
      if ((el = t.closest("[data-ct]"))) { setCType(el.dataset.ct); return; }
      if ((el = t.closest("[data-tab]"))) { setTab(el.dataset.tab); $("drTabs").scrollIntoView({ block: "start", behavior: "smooth" }); return; }
      if ((el = t.closest("[data-filter]"))) { actFilter = el.dataset.filter; renderTimeline(); return; }
      if ((el = t.closest("#drTrack [data-stage]"))) { changeStage(el.dataset.stage); return; }
      if ((el = t.closest('[data-act="stage"]'))) { stageMenu(el); return; }
      if ((el = t.closest("[data-prio]"))) { if (el.dataset.prio !== D.priority) patchDeal({ priority: el.dataset.prio }); return; }
      if ((el = t.closest("[data-copy]"))) { copyText(el.dataset.copy, "Copied"); return; }
      if ((el = t.closest("[data-sig]"))) { sigAction(SIGS[+el.dataset.sig]); return; }
      if ((el = t.closest("[data-tl]"))) { timelineAction(el); return; }
      if ((el = t.closest("[data-delfile]"))) {
        if (!confirm("Delete this file?")) return;
        const id = parseInt(el.dataset.delfile, 10);
        apiFetch(R.attachmentItem.replace("__ID__", id), { method: "DELETE" }).then(res => {
          if (!res.success) { toast(firstErr(res, "Couldn't delete the file."), "danger"); return; }
          ATT = ATT.filter(f => f.id !== id);
          renderFiles(); renderTabs(); if (!editing()) renderOverview(); refreshRecord();
          toast("File deleted", "dark");
        });
        return;
      }
      if ((el = t.closest(".dr-prop-v[data-edit]"))) startEdit(el);
    });
    wrap.addEventListener("keydown", e => {
      if ((e.key === "Enter" || e.key === " ") && e.target.matches(".dr-prop-v[data-edit]")) { e.preventDefault(); startEdit(e.target); }
    });

    $("drCSubmit").addEventListener("click", submitActivity);
    $("drCText").addEventListener("keydown", e => { if (e.key === "Enter" && (e.ctrlKey || e.metaKey)) { e.preventDefault(); submitActivity(); } });

    const input = $("drFileInput"), drop = $("drDrop");
    input.addEventListener("change", e => { if (e.target.files.length) uploadFiles(e.target.files); });
    ["dragenter", "dragover"].forEach(ev => drop.addEventListener(ev, e => { e.preventDefault(); drop.classList.add("over"); }));
    ["dragleave", "drop"].forEach(ev => drop.addEventListener(ev, e => { e.preventDefault(); drop.classList.remove("over"); }));
    drop.addEventListener("drop", e => { if (e.dataTransfer.files.length) uploadFiles(e.dataTransfer.files); });

    $("drAssocModal").addEventListener("shown.bs.modal", () => (assocKind === "contact" ? pcContact : pcCompany).mount($("drAssocBody")));

    $("drLostSel").innerHTML = LOST_REASONS.map(r => `<option>${esc(r)}</option>`).join("");
    $("drLostOk").addEventListener("click", () => {
      const r = $("drLostSel").value, t = $("drLostTxt").value.trim();
      bootstrap.Modal.getOrCreateInstance($("drLostModal")).hide();
      applyStage("lost", (r + (t ? " — " + t : "")).slice(0, 255));
    });

    $("drConfirmDelete").addEventListener("click", async () => {
      const btn = $("drConfirmDelete");
      btn.disabled = true;
      try {
        const res = await apiFetch(R.destroy, { method: "DELETE" });
        if (res.success) { location.href = R.index; return; }
        toast(firstErr(res, "Couldn't delete."), "danger");
      } catch (e) { /* toast shown */ }
      btn.disabled = false;
      bootstrap.Modal.getOrCreateInstance($("drDeleteModal")).hide();
    });
  }

  function renderAll() {
    renderHero(); renderFacts(); renderTabs(); renderOverview(); renderTimeline(); renderPeople(); renderFiles();
  }

  document.addEventListener("DOMContentLoaded", () => {
    bindAll();
    setCType("note");
    renderAll();
  });
})();
</script>

@endsection