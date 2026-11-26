{{--
  ==========================================================================================
  REUSABLE "CREATE SUPPORT TICKET" MODAL
  ------------------------------------------------------------------------------------------
  1. Include it ONCE on any page (it is guarded with @once, so including it twice is harmless):

         @include('partials.ticket-modal')

  2. Open it from anywhere:

     a) with no JavaScript – just add data attributes to any button / link:

         <button type="button" data-ticket-create data-ticket-type="deal" data-ticket-id="{{ $deal->id }}">
             <i class="bi bi-life-preserver"></i> Raise ticket
         </button>

         data-ticket-type      deal | lead | contact
         data-ticket-id        id of that record (omit to let the user search)
         data-ticket-subject   (optional) pre-filled subject
         data-ticket-priority  (optional) low | medium | high | urgent
         data-ticket-category  (optional) e.g. Delivery

        Works on buttons rendered later by JavaScript too (the click is delegated).

     b) from JavaScript:

         TicketModal.open({ type: 'lead', id: 42, subject: 'Callback request', onCreated: t => console.log(t.ticket_no) });

  3. React to a new ticket (optional):

         window.addEventListener('ticket:created', e => console.log(e.detail));   // e.detail = the ticket

  Needs: Bootstrap 5 JS, Bootstrap Icons, the tickets.* routes and the `admin` guard.
  ==========================================================================================
--}}
@once
@php
    $tkAdmins = \App\Models\Admin::orderBy('name')->get(['id', 'name']);
@endphp

<style>
  /* ---- create-ticket modal (.tk-*) — same tokens as the rest of the CRM ---- */
  #tkModal .modal-header { align-items: flex-start; }
  #tkModal .tk-sub { font-size: 12.5px; color: var(--text-muted); margin-top: 2px; }
  #tkModal .modal-body { padding: 20px 24px 8px; }
  .tk-sec { padding-bottom: 20px; margin-bottom: 20px; border-bottom: 1px solid #F0F1F6; }
  .tk-sec:last-child { border: 0; margin-bottom: 0; }
  .tk-h { display: flex; gap: 12px; align-items: flex-start; margin-bottom: 14px; }
  .tk-h .n { width: 26px; height: 26px; border-radius: 50%; background: var(--primary); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 12.5px; font-weight: 800; flex-shrink: 0; margin-top: 1px; }
  .tk-h b { display: block; font-size: 15px; }
  .tk-h small { color: var(--text-muted); font-size: 12.5px; }
  .tk-lbl { font-size: 12.5px; font-weight: 600; margin-bottom: 4px; display: block; }
  .tk-link { border: 0; background: transparent; color: var(--primary); font-weight: 700; font-size: 13px; padding: 4px 8px; border-radius: 8px; white-space: nowrap; }
  .tk-link:hover { background: var(--primary-50); }

  /* related-to */
  .tk-seg { display: inline-flex; flex-wrap: wrap; background: #F3F4FA; border-radius: 11px; padding: 3px; gap: 2px; margin-bottom: 12px; }
  .tk-seg button { border: 0; background: transparent; padding: 7px 14px; border-radius: 9px; font-size: 13px; font-weight: 700; color: var(--text-muted); display: inline-flex; align-items: center; gap: 7px; }
  .tk-seg button:hover { color: var(--text); }
  .tk-seg button.on { background: #fff; color: var(--primary); box-shadow: var(--shadow-sm); }
  .tk-search { position: relative; }
  .tk-search i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); }
  .tk-search input.form-control { padding-left: 38px !important; }
  .tk-res { border: 1px solid var(--border); border-radius: 12px; overflow: hidden; margin-top: 10px; max-height: 260px; overflow-y: auto; }
  .tk-res button { display: flex; align-items: center; gap: 12px; width: 100%; border: 0; border-top: 1px solid #F0F1F6; background: #fff; padding: 10px 14px; text-align: left; }
  .tk-res button:first-child { border-top: 0; }
  .tk-res button:hover, .tk-res button:focus-visible { background: var(--primary-50); outline: 0; }
  .tk-res .ic, .tk-rec .ic { width: 36px; height: 36px; border-radius: 10px; background: var(--primary-50); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
  .tk-res .t, .tk-rec .t { font-weight: 700; font-size: 13.5px; }
  .tk-res .s, .tk-rec .s { font-size: 12px; color: var(--text-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .tk-res .grow, .tk-rec .grow { min-width: 0; flex: 1; }
  .tk-res .none { padding: 16px; font-size: 13px; color: var(--text-muted); text-align: center; }
  .tk-rec { display: flex; align-items: center; gap: 12px; border: 1.5px solid var(--primary); background: var(--primary-50); border-radius: 12px; padding: 12px 14px; }
  .tk-rec .ic { background: #fff; }
  .tk-ctx { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; margin-top: 10px; font-size: 12.5px; }
  .tk-chip { display: inline-flex; align-items: center; gap: 7px; background: #F3F4FA; border-radius: 999px; padding: 4px 11px; font-weight: 600; }
  .tk-chip i { color: var(--text-muted); }
  .tk-ctx select { width: auto; max-width: 240px; display: inline-block; }
  .tk-note { display: flex; gap: 10px; align-items: center; background: #F3F4FA; border-radius: 10px; padding: 10px 13px; font-size: 13px; color: var(--text-muted); }
  .tk-skel { height: 52px; border-radius: 12px; background: linear-gradient(90deg, #F0F1F6, #E7E9F2, #F0F1F6); background-size: 200% 100%; animation: tkShimmer 1.2s infinite; }
  @keyframes tkShimmer { from { background-position: 200% 0; } to { background-position: -200% 0; } }

  /* details */
  .tk-sugg { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 8px; }
  .tk-sugg button { border: 1px dashed #C9CDDD; background: #fff; border-radius: 999px; padding: 3px 11px; font-size: 12px; font-weight: 600; color: var(--text-muted); }
  .tk-sugg button:hover { border-color: var(--primary); color: var(--primary); background: var(--primary-50); }
  .tk-pick { display: flex; flex-wrap: wrap; gap: 8px; }
  .tk-pick button { border: 1.5px solid var(--border); background: #fff; border-radius: 10px; padding: 7px 12px; font-size: 13px; font-weight: 600; color: var(--text-muted); display: inline-flex; align-items: center; gap: 7px; transition: all .12s; }
  .tk-pick button:hover { border-color: var(--primary-100); color: var(--text); }
  .tk-pick button.on { border-color: var(--primary); background: var(--primary-50); color: var(--primary); }
  .tk-pri { display: flex; flex-wrap: wrap; gap: 8px; }
  .tk-pri button { flex: 1 1 110px; border: 1.5px solid var(--border); background: #fff; border-radius: 12px; padding: 9px 12px; text-align: left; font-weight: 700; font-size: 13px; color: var(--text-muted); display: flex; flex-direction: column; gap: 2px; transition: all .12s; }
  .tk-pri button small { font-weight: 600; font-size: 11.5px; opacity: .8; }
  .tk-pri button .d { display: inline-flex; align-items: center; gap: 7px; }
  .tk-pri button .d i { width: 9px; height: 9px; border-radius: 50%; background: var(--c); display: inline-block; }
  .tk-pri button:hover { border-color: var(--c); }
  .tk-pri button.on { border-color: var(--c); background: var(--bg); color: var(--c); }
  .tk-p-low { --c: #8A8FA6; --bg: #EEF1F6; } .tk-p-medium { --c: var(--info); --bg: var(--info-bg); }
  .tk-p-high { --c: var(--warning); --bg: var(--warning-bg); } .tk-p-urgent { --c: var(--danger); --bg: var(--danger-bg); }
  .tk-sla { display: flex; align-items: center; gap: 8px; font-size: 12.5px; color: var(--text-muted); margin-top: 8px; }
  .tk-sla i { color: var(--primary); }

  /* success */
  .tk-done { text-align: center; padding: 22px 10px 14px; }
  .tk-done .ok { width: 68px; height: 68px; border-radius: 50%; background: var(--success-bg); color: var(--success); font-size: 32px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 14px; animation: tkPop .35s cubic-bezier(.2, 1.4, .4, 1); }
  @keyframes tkPop { from { transform: scale(.5); opacity: 0; } to { transform: none; opacity: 1; } }
  .tk-done h5 { margin: 0 0 6px; font-weight: 800; }
  .tk-done .no { display: inline-block; font-weight: 800; font-size: 13px; letter-spacing: .04em; color: var(--primary); background: var(--primary-50); border-radius: 999px; padding: 4px 14px; }
  .tk-done .sj { margin-top: 12px; font-weight: 600; font-size: 15px; word-break: break-word; }
  .tk-done .chips { display: flex; gap: 8px; flex-wrap: wrap; justify-content: center; margin-top: 14px; }
  .tk-pill { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; border-radius: 999px; padding: 5px 11px; background: var(--bg); color: var(--c); }
  @media (max-width: 575.98px) { #tkModal .modal-body { padding: 16px 16px 4px; } }
  @media (prefers-reduced-motion: reduce) { .tk-skel, .tk-done .ok { animation: none; } }
</style>

<div class="modal fade" id="tkModal" tabindex="-1" aria-labelledby="tkTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable modal-fullscreen-sm-down">
    <div class="modal-content">
      <div class="modal-header">
        <div><h5 class="modal-title" id="tkTitle">New support ticket</h5><div class="tk-sub" id="tkSub">Log a customer issue and assign it to your team.</div></div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="tkBody"></div>
      <div class="modal-footer" id="tkFoot"></div>
    </div>
  </div>
</div>

<script>
(function () {
  "use strict";
  if (window.TicketModal) return;

  const CFG = @json(\App\Models\SupportTicket::config());
  const ADMINS = @json($tkAdmins);
  const ME = {{ (int) auth('admin')->id() }};
  const CSRF = '{{ csrf_token() }}';
  const R = { store: '{{ route('tickets.store') }}', lookup: '{{ route('tickets.lookup') }}', index: '{{ route('tickets.index') }}' };

  const TYPES = { deal: { label: "Deal", icon: "briefcase" }, lead: { label: "Lead", icon: "funnel" }, contact: { label: "Contact", icon: "person" } };
  const CAT_ICON = { "General": "chat-dots", "Billing & payment": "credit-card", "Delivery": "truck", "Product / quality": "box-seam", "Technical": "tools", "Callback request": "telephone-inbound", "Other": "three-dots" };
  const SUGG = {
    "General": ["General enquiry", "Information request"],
    "Billing & payment": ["Payment not received", "Invoice correction needed", "Refund request"],
    "Delivery": ["Delivery delayed", "Wrong item delivered", "Order not received"],
    "Product / quality": ["Product defect", "Quality complaint", "Replacement request"],
    "Technical": ["Unable to use the product", "Installation help needed"],
    "Callback request": ["Customer asked for a callback"],
    "Other": [],
  };
  const $ = id => document.getElementById(id);
  const esc = s => String(s == null ? "" : s).replace(/[&<>"']/g, c => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));
  const debounce = (fn, ms) => { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; };

  function toast(message, variant) {
    let wrap = $("tkToastWrap");
    if (!wrap) {
      wrap = document.createElement("div");
      wrap.id = "tkToastWrap";
      wrap.style.cssText = "position:fixed;bottom:20px;right:20px;z-index:3000;display:flex;flex-direction:column;gap:8px;";
      document.body.appendChild(wrap);
    }
    const el = document.createElement("div");
    el.style.cssText = `background:${variant === "danger" ? "#DC2626" : "#16A34A"};color:#fff;padding:11px 16px;border-radius:10px;font-size:13.5px;font-weight:600;box-shadow:0 8px 20px rgba(0,0,0,.18);max-width:340px;`;
    el.textContent = message;
    wrap.appendChild(el);
    setTimeout(() => el.remove(), 3400);
  }
  async function api(u, method, body) {
    const opts = { method: method || "GET", headers: { "X-CSRF-TOKEN": CSRF, "X-Requested-With": "XMLHttpRequest", "Accept": "application/json" } };
    if (body) { opts.headers["Content-Type"] = "application/json"; opts.body = JSON.stringify(body); }
    const res = await fetch(u, opts);
    if (res.status === 401 || res.status === 419) { toast("Your session has expired. Please log in again.", "danger"); throw new Error("unauthenticated"); }
    try { return await res.json(); } catch (e) { return { success: false, message: "Server error (" + res.status + ")" }; }
  }

  /* ---------------- state ---------------- */

  let S = null, cb = null, token = 0;

  function fresh(o, keep) {
    const t = ["deal", "lead", "contact"].indexOf(o.type) >= 0 ? o.type : "contact";
    return Object.assign({
      step: "form", busy: false, type: t, record: null, loadingRec: !!o.id, q: "", results: null, contactId: null,
      subject: o.subject || "", description: o.description || "",
      category: CFG.categories.indexOf(o.category) >= 0 ? o.category : "General",
      priority: CFG.priorities[o.priority] ? o.priority : "medium",
      channel: CFG.channels[o.channel] ? o.channel : "", assigned: String(ME || ""), created: null,
    }, keep || {});
  }

  /* ---------------- rendering ---------------- */

  function renderShell() {
    $("tkTitle").textContent = "New support ticket";
    $("tkSub").textContent = "Log a customer issue and assign it to your team.";
    $("tkBody").innerHTML = `
      <div class="tk-sec"><div class="tk-h"><span class="n">1</span><div><b>Related to</b><small>Link the ticket to a deal, lead or contact so the whole history stays together.</small></div></div><div id="tkLink"></div></div>
      <div class="tk-sec"><div class="tk-h"><span class="n">2</span><div><b>What is the issue?</b><small>A clear subject helps the person who picks it up.</small></div></div>
        <label class="tk-lbl" for="tkSubject">Subject *</label>
        <input class="form-control" id="tkSubject" data-k="subject" value="${esc(S.subject)}" maxlength="255" placeholder="e.g. Payment not received for invoice #1042" autocomplete="off">
        <div class="tk-sugg" id="tkSugg"></div>
        <label class="tk-lbl mt-3">Category</label><div class="tk-pick" id="tkCat"></div>
        <div class="row g-3 mt-0">
          <div class="col-12"><label class="tk-lbl">Priority</label><div class="tk-pri" id="tkPri"></div><div class="tk-sla" id="tkSla"></div></div>
          <div class="col-md-6"><label class="tk-lbl" for="tkChannel">Raised via</label>
            <select class="form-select" id="tkChannel" data-k="channel"><option value="">— Not specified —</option>${Object.keys(CFG.channels).map(k => `<option value="${k}" ${S.channel === k ? "selected" : ""}>${esc(CFG.channels[k])}</option>`).join("")}</select></div>
          <div class="col-md-6"><label class="tk-lbl" for="tkAssign">Assign to</label>
            <select class="form-select" id="tkAssign" data-k="assigned"><option value="">Unassigned</option>${ADMINS.map(a => `<option value="${a.id}" ${String(a.id) === S.assigned ? "selected" : ""}>${esc(a.name)}${a.id === ME ? " (me)" : ""}</option>`).join("")}</select></div>
          <div class="col-12"><label class="tk-lbl" for="tkDesc">Description</label>
            <textarea class="form-control" id="tkDesc" data-k="description" rows="4" placeholder="What happened? Include dates, amounts, order numbers or anything the customer said.">${esc(S.description)}</textarea></div>
        </div></div>`;
    renderLink(); renderPicks(); renderFoot();
  }

  function renderLink() {
    const el = $("tkLink");
    if (!el || !S) return;
    const tabs = [["deal", "Deal", "briefcase"], ["lead", "Lead", "funnel"], ["contact", "Contact", "person"], ["none", "No link", "dash-circle"]];
    let h = `<div class="tk-seg" role="tablist">${tabs.map(t => `<button type="button" role="tab" class="${S.type === t[0] ? "on" : ""}" data-tk-type="${t[0]}"><i class="bi bi-${t[2]}"></i>${t[1]}</button>`).join("")}</div>`;
    if (S.type === "none") {
      h += '<div class="tk-note"><i class="bi bi-info-circle"></i>Standalone ticket — it will not be linked to any record.</div>';
    } else if (S.loadingRec) {
      h += '<div class="tk-skel"></div>';
    } else if (S.record) {
      const r = S.record, T = TYPES[r.type], cts = r.contacts || [];
      h += `<div class="tk-rec"><div class="ic"><i class="bi bi-${T.icon}"></i></div><div class="grow"><div class="t">${esc(r.title)}</div><div class="s">${esc(r.sub || "")}</div></div><button type="button" class="tk-link" data-tk="change">Change</button></div>
        <div class="tk-ctx">
          ${cts.length > 1 ? `<span class="tk-chip"><i class="bi bi-person"></i>Raised by</span><select class="form-select form-select-sm" id="tkContact">${cts.map(c => `<option value="${c.id}" ${c.id === S.contactId ? "selected" : ""}>${esc(c.name)}</option>`).join("")}</select>`
            : cts.length === 1 && r.type !== "contact" ? `<span class="tk-chip"><i class="bi bi-person"></i>${esc(cts[0].name)}</span>` : ""}
          ${r.company ? `<span class="tk-chip"><i class="bi bi-building"></i>${esc(r.company)}</span>` : ""}
          ${!cts.length && !r.company ? `<span class="tk-chip"><i class="bi bi-info-circle"></i>No contact or company on this ${T.label.toLowerCase()}</span>` : ""}
        </div>`;
    } else {
      h += `<div class="tk-search"><i class="bi bi-search"></i><input type="text" class="form-control" id="tkQ" value="${esc(S.q)}" placeholder="${S.type === "deal" ? "Search by deal, company or contact…" : S.type === "lead" ? "Search by name, phone or company…" : "Search by name, phone or email…"}" autocomplete="off"></div><div class="tk-res" id="tkRes"></div>`;
    }
    el.innerHTML = h;
    if (!S.record && !S.loadingRec && S.type !== "none") drawResults();
  }

  function drawResults() {
    const box = $("tkRes");
    if (!box || !S) return;
    if (S.results === null) { box.innerHTML = '<div class="none">Loading…</div>'; return; }
    box.innerHTML = S.results.length
      ? S.results.map((x, i) => `<button type="button" data-tk-pick="${i}"><div class="ic"><i class="bi bi-${TYPES[x.type].icon}"></i></div><div class="grow"><div class="t">${esc(x.title)}</div><div class="s">${esc(x.sub || "—")}</div></div></button>`).join("")
      : `<div class="none">${S.q ? "Nothing found for “" + esc(S.q) + "”." : "No " + TYPES[S.type].label.toLowerCase() + "s to show yet."}</div>`;
  }

  function renderPicks() {
    if (!$("tkCat")) return;
    $("tkCat").innerHTML = CFG.categories.map(c => `<button type="button" class="${S.category === c ? "on" : ""}" data-tk-cat="${esc(c)}"><i class="bi bi-${CAT_ICON[c] || "tag"}"></i>${esc(c)}</button>`).join("");
    $("tkPri").innerHTML = Object.keys(CFG.priorities).map(k => `<button type="button" class="tk-p-${k} ${S.priority === k ? "on" : ""}" data-tk-pri="${k}"><span class="d"><i></i>${esc(CFG.priorities[k])}</span><small>Respond in ${CFG.sla[k]}h</small></button>`).join("");
    const due = new Date(Date.now() + CFG.sla[S.priority] * 3600000);
    $("tkSla").innerHTML = `<i class="bi bi-clock-history"></i><span>Target response: <b>${CFG.sla[S.priority]} hours</b> — due ${esc(due.toLocaleString([], { day: "numeric", month: "short", hour: "numeric", minute: "2-digit" }))}</span>`;
    const sg = SUGG[S.category] || [];
    $("tkSugg").innerHTML = sg.map(s => `<button type="button" data-tk-sugg="${esc(s)}">${esc(s)}</button>`).join("");
  }

  function renderFoot() {
    const f = $("tkFoot");
    if (!S) return;
    if (S.step === "done") {
      const t = S.created;
      f.innerHTML = `<button type="button" class="btn btn-outline-secondary me-auto" data-tk="another"><i class="bi bi-plus-lg me-1"></i>Create another</button>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Done</button>
        <a class="btn btn-primary" href="${esc(R.index + "?ticket=" + t.id)}" data-tk-open="${t.id}">Open ticket<i class="bi bi-arrow-right ms-1"></i></a>`;
      return;
    }
    f.innerHTML = `<button type="button" class="btn btn-outline-secondary me-auto" data-bs-dismiss="modal">Cancel</button>
      <button type="button" class="btn btn-primary" data-tk="submit" ${S.busy ? "disabled" : ""}>${S.busy ? '<span class="spinner-border spinner-border-sm me-1"></span>Creating…' : '<i class="bi bi-life-preserver me-1"></i>Create ticket'}</button>`;
  }

  function renderDone() {
    const t = S.created, pr = t.priority;
    $("tkTitle").textContent = "Ticket created";
    $("tkSub").textContent = "Your team can now pick it up.";
    const linked = t.deal ? ["briefcase", t.deal.name] : t.lead ? ["funnel", t.lead.customer_name] : t.contact ? ["person", t.contact.name] : null;
    $("tkBody").innerHTML = `<div class="tk-done"><div class="ok"><i class="bi bi-check-lg"></i></div><h5>Ticket created</h5><span class="no">${esc(t.ticket_no)}</span>
      <div class="sj">${esc(t.subject)}</div>
      <div class="chips"><span class="tk-pill tk-p-${pr}">${esc(CFG.priorities[pr])} priority</span><span class="tk-pill" style="--c:var(--text-muted);--bg:#EEF1F6">${esc(t.category)}</span>
        ${t.assignee ? `<span class="tk-pill" style="--c:var(--primary);--bg:var(--primary-50)"><i class="bi bi-person"></i>${esc(t.assignee.name)}</span>` : ""}
        ${linked ? `<span class="tk-pill" style="--c:var(--primary);--bg:var(--primary-50)"><i class="bi bi-${linked[0]}"></i>${esc(linked[1])}</span>` : ""}
        ${t.due_at ? `<span class="tk-pill" style="--c:var(--warning);--bg:var(--warning-bg)"><i class="bi bi-clock"></i>Due ${esc(new Date(t.due_at).toLocaleString([], { day: "numeric", month: "short", hour: "numeric", minute: "2-digit" }))}</span>` : ""}</div></div>`;
    renderFoot();
  }

  /* ---------------- data ---------------- */

  async function loadRecord(type, id) {
    const my = ++token;
    S.loadingRec = true; renderLink();
    let res;
    try { res = await api(R.lookup + "?type=" + type + "&id=" + encodeURIComponent(id)); } catch (e) { res = {}; }
    if (my !== token || !S) return;
    S.loadingRec = false;
    const it = res.success && res.items && res.items[0];
    if (it) pickRecord(it); else { renderLink(); searchRecords(); }
  }
  async function searchRecords() {
    if (!S || S.type === "none") return;
    const my = ++token;
    S.results = null; drawResults();
    let res;
    try { res = await api(R.lookup + "?type=" + S.type + "&q=" + encodeURIComponent(S.q.trim())); } catch (e) { return; }
    if (my !== token || !S) return;
    S.results = res.items || [];
    drawResults();
  }
  const dsearch = debounce(searchRecords, 220);

  function pickRecord(it) {
    S.record = it; S.type = it.type;
    S.contactId = (it.contacts && it.contacts[0]) ? it.contacts[0].id : null;
    S.results = null; S.q = "";
    renderLink();
  }

  async function submit() {
    if (!S || S.busy || S.step !== "form") return;
    if (!S.subject.trim()) { toast("Add a subject for the ticket.", "danger"); $("tkSubject").focus(); return; }
    if (S.type !== "none" && !S.record) { toast("Pick the " + TYPES[S.type].label.toLowerCase() + " this ticket is about — or choose “No link”.", "danger"); const q = $("tkQ"); if (q) q.focus(); return; }
    S.busy = true; renderFoot();
    let res;
    try {
      res = await api(R.store, "POST", {
        subject: S.subject.trim(), description: S.description.trim() || null, category: S.category, priority: S.priority,
        channel: S.channel || null, assigned_to: S.assigned ? parseInt(S.assigned, 10) : null,
        related_type: S.type !== "none" && S.record ? S.type : null, related_id: S.record ? S.record.id : null, contact_id: S.contactId || null,
      });
    } catch (e) { S.busy = false; renderFoot(); return; }
    S.busy = false;
    if (!res.success) { renderFoot(); toast((res.message || (res.errors && Object.values(res.errors)[0][0])) || "Couldn't create the ticket.", "danger"); return; }
    S.step = "done"; S.created = res.ticket;
    renderDone();
    window.dispatchEvent(new CustomEvent("ticket:created", { detail: res.ticket }));
    if (cb) { try { cb(res.ticket); } catch (e) { /* ignore */ } }
  }

  /* ---------------- public API ---------------- */

  function open(o) {
    o = o || {};
    S = fresh(o);
    cb = typeof o.onCreated === "function" ? o.onCreated : null;
    renderShell();
    const m = $("tkModal");
    const focus = () => { m.removeEventListener("shown.bs.modal", focus); const i = $("tkQ") || $("tkSubject"); if (i) i.focus(); };
    m.addEventListener("shown.bs.modal", focus);
    bootstrap.Modal.getOrCreateInstance(m).show();
    if (o.id && S.type !== "none") loadRecord(S.type, o.id); else searchRecords();
  }
  window.TicketModal = { open, close: () => bootstrap.Modal.getOrCreateInstance($("tkModal")).hide() };

  /* ---------------- events ---------------- */

  document.addEventListener("click", e => {
    const b = e.target.closest("[data-ticket-create]");
    if (!b) return;
    e.preventDefault();
    open({ type: b.dataset.ticketType, id: b.dataset.ticketId, subject: b.dataset.ticketSubject, priority: b.dataset.ticketPriority, category: b.dataset.ticketCategory });
  });

  document.addEventListener("DOMContentLoaded", () => {
    const body = $("tkBody"), foot = $("tkFoot"), modal = $("tkModal");

    body.addEventListener("click", e => {
      if (!S) return;
      const t = e.target;
      let el;
      if ((el = t.closest("[data-tk-type]"))) {
        S.type = el.dataset.tkType; S.record = null; S.results = null; S.q = ""; S.contactId = null; S.loadingRec = false;
        renderLink();
        if (S.type !== "none") { searchRecords(); const q = $("tkQ"); if (q) q.focus(); }
      } else if ((el = t.closest("[data-tk-pick]"))) {
        const it = (S.results || [])[+el.dataset.tkPick]; if (it) pickRecord(it);
      } else if (t.closest('[data-tk="change"]')) {
        S.record = null; S.results = null; S.q = ""; S.contactId = null; renderLink(); searchRecords(); const q = $("tkQ"); if (q) q.focus();
      } else if ((el = t.closest("[data-tk-cat]"))) {
        S.category = el.dataset.tkCat; renderPicks();
      } else if ((el = t.closest("[data-tk-pri]"))) {
        S.priority = el.dataset.tkPri; renderPicks();
      } else if ((el = t.closest("[data-tk-sugg]"))) {
        S.subject = el.dataset.tkSugg; $("tkSubject").value = S.subject; $("tkSubject").focus();
      }
    });
    body.addEventListener("input", e => {
      if (!S) return;
      const t = e.target;
      if (t.id === "tkQ") { S.q = t.value; S.results = null; drawResults(); dsearch(); return; }
      if (t.dataset.k) S[t.dataset.k] = t.value;
    });
    body.addEventListener("change", e => {
      if (!S) return;
      const t = e.target;
      if (t.id === "tkContact") S.contactId = t.value ? parseInt(t.value, 10) : null;
      else if (t.dataset.k) S[t.dataset.k] = t.value;
    });
    body.addEventListener("keydown", e => {
      if (!S || e.key !== "Enter") return;
      if (e.target.id === "tkQ") { e.preventDefault(); const it = (S.results || [])[0]; if (it) pickRecord(it); }
      else if (e.target.id === "tkSubject") { e.preventDefault(); $("tkDesc").focus(); }
    });
    modal.addEventListener("keydown", e => { if (S && S.step === "form" && e.key === "Enter" && (e.ctrlKey || e.metaKey)) { e.preventDefault(); submit(); } });

    foot.addEventListener("click", e => {
      if (!S) return;
      if (e.target.closest('[data-tk="submit"]')) submit();
      else if (e.target.closest('[data-tk="another"]')) {
        // keep the linked record: raising a second ticket for the same customer is one click
        S = fresh({ type: S.type }, { record: S.record, contactId: S.contactId, assigned: S.assigned });
        renderShell();
        $("tkSubject").focus();
      } else {
        const a = e.target.closest("[data-tk-open]");
        if (!a) return;
        // a page that shows tickets (the Tickets page) can handle this itself and cancel the navigation
        const ev = new CustomEvent("ticket:open", { detail: { id: parseInt(a.dataset.tkOpen, 10) }, cancelable: true });
        if (!window.dispatchEvent(ev)) { e.preventDefault(); bootstrap.Modal.getOrCreateInstance(modal).hide(); }
      }
    });
    modal.addEventListener("hidden.bs.modal", () => { token++; });
  });
})();
</script>
@endonce