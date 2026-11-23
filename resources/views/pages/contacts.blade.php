@extends('layouts.app')
@section('title', 'Contacts | Dalal Adda')

@section('content')

@include('pages.directory._assets')

@include('pages.directory._shell', [
    'dirTitle'  => 'Contacts',
    'dirSub'    => 'Everyone you do business with. Open a contact to edit it, see its leads or start a new lead.',
    'dirCreate' => 'Create contact',
    'dirSearch' => 'Search name, email, phone or company...',
    'dirNoun'   => 'Contact',
])

<!-- ===================== CREATE CONTACT ===================== -->
<div class="modal fade" id="dirCreateModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Create contact</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="lh-pane"><div class="hint">Add a phone number or an email. Duplicates are caught automatically.</div></div>
        <div class="row g-3">
          <div class="col-12"><label class="form-label fw-semibold" style="font-size:13px" for="cName">Full name *</label><input class="form-control" id="cName" placeholder="e.g. Rahul Sharma" autocomplete="off"></div>
          <div class="col-md-6"><label class="form-label fw-semibold" style="font-size:13px" for="cEmail">Email</label><input class="form-control" id="cEmail" type="email" placeholder="name@example.com" autocomplete="off"></div>
          <div class="col-md-6"><label class="form-label fw-semibold" style="font-size:13px" for="cPhone">Phone number</label><input class="form-control" id="cPhone" inputmode="tel" placeholder="+91 98765 43210" autocomplete="off"></div>
          <div class="col-md-6"><label class="form-label fw-semibold" style="font-size:13px" for="cWa">WhatsApp</label><input class="form-control" id="cWa" inputmode="tel" placeholder="Leave empty if same as phone" autocomplete="off"></div>
          <div class="col-md-6"><label class="form-label fw-semibold" style="font-size:13px" for="cJob">Job title</label><input class="form-control" id="cJob" placeholder="e.g. Purchase Manager" autocomplete="off"></div>
          <div class="col-12">
            <label class="form-label fw-semibold" style="font-size:13px">Company</label>
            <div class="d-flex gap-2 align-items-center">
              <button type="button" class="lh-assignbtn" id="cCompany" aria-haspopup="true"></button>
              <button type="button" class="btn btn-sm btn-outline-secondary d-none" id="cCompanyClear" aria-label="Remove company"><i class="bi bi-x-lg"></i></button>
            </div>
          </div>
        </div>
        <div id="cAlert"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="cSubmit"><i class="bi bi-check2-circle me-1"></i>Create contact</button>
      </div>
    </div>
  </div>
</div>

<script>
  window.CONTACT_PAGE_DATA = {
    csrf: @json(csrf_token()),
    isSuperAdmin: @json((bool) $isSuperAdmin),
    meId: @json($meId ?? null),
    admins: @json($admins),
    leadTypes: @json($leadTypes ?? []),
    sources: @json($sources ?? []),
    platforms: @json($platforms ?? []),
    companiesUrl: @json(url('admin/companies')),
    leadsUrl: @json(url('admin/leads')),
    routes: {
      data: @json(route('contacts.data')),
      show: @json(url('admin/contacts') . '/__ID__'),
      update: @json(url('admin/contacts') . '/__ID__'),
      destroy: @json(url('admin/contacts') . '/__ID__'),
      bulkDelete: @json(route('contacts.bulkDestroy')),
      contactStore: @json(route('leads.contacts.store')),
      companiesSearch: @json(route('leads.companies.search')),
      companyStore: @json(route('leads.companies.store')),
      leadStore: @json(route('leads.store')),
    },
  };
</script>

<script>
(function () {
  "use strict";
  const D = window.DirUI, esc = D.esc, DASH = D.DASH;
  const CFG = window.CONTACT_PAGE_DATA, R = CFG.routes;
  const TYPES = CFG.leadTypes.length ? CFG.leadTypes : ["hot", "cold", "warm", "converted", "dealer"];
  const SOURCES = CFG.sources.length ? CFG.sources : ["Website", "Referral", "Cold Call", "Social Media", "Walk-in", "Email Campaign", "Trade Show", "Other"];
  const PLATFORMS = CFG.platforms.length ? CFG.platforms : ["Facebook", "Instagram", "LinkedIn", "WhatsApp", "Other"];
  const $ = id => document.getElementById(id);
  let api = null;

  /* ---------------- data helpers ---------------- */

  // The PATCH response carries a slim company; keep the fuller one we already have when it is the same company.
  function mergeContact(row, c) {
    const out = Object.assign({}, c);
    if (row.company && c.company && row.company.id === c.company.id) out.company = Object.assign({}, row.company, c.company);
    return out;
  }
  async function patch(row, body) {
    const res = await D.reqJson(R.update.replace("__ID__", row.id), "PATCH", body);
    if (res.success) return { ok: true, data: mergeContact(row, res.contact) };
    return { ok: false, message: D.firstErr(res, "Could not save.") };
  }
  const needOne = "A contact needs a phone number or an email address.";

  /* ---------------- table columns ---------------- */

  const link = (href, text, cls) => `<a href="${esc(href)}" class="${cls || ""}" style="color:inherit">${esc(text)}</a>`;
  const COLS = {
    name: { label: "Name", icon: "person", sortv: r => r.name.toLowerCase() },
    email: { label: "Email", icon: "envelope", sortv: r => (r.email || "").toLowerCase(), cell: r => r.email ? link("mailto:" + r.email, r.email) : DASH },
    phone: { label: "Phone number", icon: "telephone", sortv: r => r.phone || "", cell: r => r.phone ? link("tel:" + r.phone, r.phone) : DASH },
    company: { label: "Company", icon: "building", sortv: r => (r.company ? r.company.name : "").toLowerCase(), cell: r => r.company ? `<span class="lh-co"><i class="bi bi-building"></i>${esc(r.company.name)}</span>` : DASH },
    whatsapp: { label: "WhatsApp", icon: "whatsapp", sortv: r => r.whatsapp || "", cell: r => { const u = D.waUrl(r.whatsapp); return u ? link(u, r.whatsapp, "lh-wa") : DASH; } },
    job_title: { label: "Job title", icon: "briefcase", sortv: r => (r.job_title || "").toLowerCase(), cell: r => r.job_title ? esc(r.job_title) : DASH },
    leads: { label: "Leads", icon: "funnel", sortv: r => r.leads_count || 0, cell: r => `<span class="lh-count">${r.leads_count || 0}</span>` },
    latest: { label: "Latest lead status", icon: "flag", sortv: r => r.latest_lead_type || "", cell: r => r.latest_lead_type ? D.pill(r.latest_lead_type) : DASH },
    created_by: { label: "Created by", icon: "person-badge", sortv: r => (r.creator ? r.creator.name : "").toLowerCase(), cell: r => r.creator ? esc(r.creator.name) : DASH },
    created_at: { label: "Create date", icon: "calendar3", sortv: r => r.created_at || "", cell: r => r.created_at ? esc(D.fmtDate(r.created_at)) : DASH },
  };

  /* ---------------- drawer ---------------- */

  function aboutPane(r) {
    const c = r.company;
    let h = `<div class="lh-sect"><div class="lh-sect-h"><i class="bi bi-person-lines-fill"></i>About this contact</div>
      ${api.prop(r, "name", esc(r.name))}
      ${api.prop(r, "email", esc(r.email))}
      ${api.prop(r, "phone", esc(r.phone))}
      ${api.prop(r, "whatsapp", esc(r.whatsapp))}
      ${api.prop(r, "job_title", esc(r.job_title))}
      ${api.prop(r, "created_by", r.creator ? esc(r.creator.name) : "")}
      ${api.prop(r, "created_at", r.created_at ? esc(D.fmtDate(r.created_at)) + ` <span class="text-muted">(${esc(D.ago(r.created_at))})</span>` : "")}
    </div>`;
    if (c) {
      h += `<div class="lh-sect"><div class="lh-sect-h"><i class="bi bi-building"></i>Company<span class="sp"></span>
          <button type="button" class="lh-link" data-dr-act="coChange">Change</button>
          <button type="button" class="lh-link" style="color:var(--danger)" data-dr-act="coRemove">Remove</button></div>
        <a class="lh-list-item" href="${esc(CFG.companiesUrl)}?id=${c.id}">${D.companyIcon("sm")}
          <div class="grow"><div class="t">${esc(c.name)}</div><div class="s">Open company</div></div><div class="end"><i class="bi bi-chevron-right text-muted"></i></div></a>
        ${api.prop(r, "co_website", esc(c.website))}
        ${api.prop(r, "co_phone", esc(c.phone))}
        ${api.prop(r, "co_gst", esc(c.gst_number))}
        ${api.prop(r, "co_address", esc(c.address))}
      </div>`;
    } else {
      h += `<div class="lh-sect"><div class="lh-sect-h"><i class="bi bi-building"></i>Company</div>
        <div class="lh-co-card"><div class="lh-co-ic"><i class="bi bi-building-add"></i></div>
          <div><div class="nm">No company linked</div><small>Link a company to keep its GST and address handy.</small></div>
          <button type="button" class="btn btn-sm btn-outline-primary ms-auto" data-dr-act="coChange">Add company</button></div></div>`;
    }
    return h;
  }

  function leadsPane(r) {
    const form = `<div class="lh-sect"><div class="lh-sect-h"><i class="bi bi-plus-circle"></i>New lead for ${esc(r.name)}</div>
      <div class="lh-mini"><div class="row g-2">
        <div class="col-6"><label for="nlStatus">Lead status</label><select class="form-select form-select-sm" id="nlStatus">${TYPES.map(t => `<option value="${esc(t)}" ${t === "cold" ? "selected" : ""}>${esc(D.typeLabel(t))}</option>`).join("")}</select></div>
        <div class="col-6"><label for="nlSource">Source</label><select class="form-select form-select-sm" id="nlSource">${SOURCES.map(s => `<option>${esc(s)}</option>`).join("")}</select></div>
        <div class="col-6 d-none" id="nlPlatWrap"><label for="nlPlatform">Platform</label><select class="form-select form-select-sm" id="nlPlatform"><option value="">— Select —</option>${PLATFORMS.map(s => `<option>${esc(s)}</option>`).join("")}</select></div>
        <div class="col-12"><label for="nlProduct">Product / interest</label><input class="form-control form-control-sm" id="nlProduct" placeholder="e.g. CRM subscription" autocomplete="off"></div>
        <div class="col-12"><button type="button" class="btn btn-primary btn-sm" data-dr-act="createLead"><i class="bi bi-plus-lg me-1"></i>Create lead</button></div>
      </div></div></div>`;
    let list;
    if (!r.leads) list = `<div class="lh-sect"><div class="lh-hint p-3">Loading leads…</div></div>`;
    else if (!r.leads.length) list = `<div class="lh-sect"><div class="lh-hint p-3">No leads yet for this contact.</div></div>`;
    else {
      list = `<div class="lh-sect"><div class="lh-sect-h"><i class="bi bi-funnel"></i>Leads (${r.leads.length})</div>` + r.leads.map(l => {
        const who = (l.assigned_admins || []).map(a => a.name).join(", ");
        return `<a class="lh-list-item" href="${esc(CFG.leadsUrl)}?lead=${l.id}">
          <div class="grow"><div class="t">${esc(l.product_name || "Lead #" + l.id)}</div>
          <div class="s">${esc([l.source, D.fmtDate(l.created_at), who].filter(Boolean).join(" · "))}</div></div>
          <div class="end">${D.pill(l.lead_type)}<i class="bi bi-chevron-right text-muted"></i></div></a>`;
      }).join("") + "</div>";
    }
    return form + list;
  }

  async function createLead(row) {
    const source = $("nlSource").value;
    const body = {
      contact_id: row.id, company_id: row.company_id || null,
      product_name: $("nlProduct").value.trim(), source,
      social_platform: source === "Social Media" ? ($("nlPlatform").value || null) : null,
      lead_type: $("nlStatus").value, assigned_admins: [],
    };
    let res;
    try { res = await D.reqJson(R.leadStore, "POST", body); } catch (e) { return; }
    if (res.success) { D.toast(res.message || "Lead created"); await api.refresh(); api.setPane("leads", true); }
    else D.toast(D.firstErr(res, "Could not create the lead."), "danger");
  }

  function changeCompany(el, row) {
    D.entityPicker(el, {
      placeholder: "Search companies...", searchUrl: R.companiesSearch, listKey: "companies", itemKey: "company", icon: "building",
      meta: c => [c.website, c.gst_number].filter(Boolean).join(" · ") || "—",
      create: name => D.reqJson(R.companyStore, "POST", { name }),
      onPick: async c => {
        if (!c) return;
        const res = await patch(row, { company_id: c.id });
        if (!res.ok) { D.toast(res.message, "danger"); return; }
        Object.assign(row, res.data);
        api.renderAll();
        D.toast("Company updated", "dark");
        api.refresh();
      },
    });
  }

  const textField = (label, key, kind) => ({
    label, kind: kind || "text", get: r => r[key],
    save: (r, v) => {
      if (key === "name" && !v) return { ok: false, message: "Name is required." };
      if ((key === "email" && !v && !r.phone) || (key === "phone" && !v && !r.email)) return { ok: false, message: needOne };
      return patch(r, { [key]: v });
    },
  });
  const ro = label => ({ label, kind: "ro" });

  /* ---------------- create modal ---------------- */

  const CR = { company: null, busy: false };
  function drawCompanyBtn() {
    const b = $("cCompany");
    b.innerHTML = CR.company ? `<span class="chip">${esc(CR.company.name)}</span>` : '<span class="ph">Search or create a company...</span>';
    $("cCompanyClear").classList.toggle("d-none", !CR.company);
  }
  function openCreate() {
    CR.company = null; CR.busy = false;
    ["cName", "cEmail", "cPhone", "cWa", "cJob"].forEach(id => { $(id).value = ""; });
    $("cAlert").innerHTML = "";
    drawCompanyBtn();
    const m = $("dirCreateModal");
    const focus = () => { m.removeEventListener("shown.bs.modal", focus); $("cName").focus(); };
    m.addEventListener("shown.bs.modal", focus);
    bootstrap.Modal.getOrCreateInstance(m).show();
  }
  const alertHtml = (msg, btn) => `<div class="lh-alert"><i class="bi bi-exclamation-triangle-fill"></i><span>${esc(msg)}</span>${btn || ""}</div>`;
  function afterModalHidden(id, fn) {
    const m = $(id);
    m.addEventListener("hidden.bs.modal", function h() { m.removeEventListener("hidden.bs.modal", h); fn(); });
    bootstrap.Modal.getOrCreateInstance(m).hide();
  }
  async function submitCreate() {
    if (CR.busy) return;
    const f = { name: $("cName").value.trim(), email: $("cEmail").value.trim(), phone: $("cPhone").value.trim(), whatsapp: $("cWa").value.trim(), job_title: $("cJob").value.trim(), company_id: CR.company ? CR.company.id : null };
    if (!f.name) { D.toast("Name is required.", "danger"); $("cName").focus(); return; }
    if (!f.phone && !f.email) { D.toast(needOne, "danger"); $("cPhone").focus(); return; }
    CR.busy = true;
    const btn = $("cSubmit");
    btn.disabled = true;
    let res;
    try { res = await D.reqJson(R.contactStore, "POST", f); } catch (e) { CR.busy = false; btn.disabled = false; return; }
    CR.busy = false; btn.disabled = false;
    if (res.success) {
      $("cAlert").innerHTML = "";
      D.toast(res.message || "Contact created");
      await api.reload();
      afterModalHidden("dirCreateModal", () => api.open(res.contact.id));
    } else if (res.duplicate) {
      $("cAlert").innerHTML = alertHtml(res.message, res.contact ? `<button type="button" class="btn btn-sm btn-warning" id="cOpenDupe">Open existing</button>` : "");
      const b = $("cOpenDupe");
      if (b) b.addEventListener("click", () => afterModalHidden("dirCreateModal", () => api.open(res.contact.id)));
    } else D.toast(D.firstErr(res, "Could not create the contact."), "danger");
  }

  /* ---------------- boot ---------------- */

  api = D.create({
    key: "contacts", csrf: CFG.csrf, isSuperAdmin: CFG.isSuperAdmin, admins: CFG.admins,
    noun: "contact", nouns: "contacts", listKey: "contacts", itemKey: "contact",
    routes: R, cols: COLS, defaultCols: ["name", "email", "phone", "company"],
    createLabel: "Create contact", emptyIcon: "people", emptyTitle: "No contacts yet", emptyText: "Create your first contact to get started.",
    deleteText: "The contact is deleted permanently. This can't be undone.",
    avatar: r => D.avatarHtml(r.name), nameSub: r => r.job_title || "",
    searchText: r => [r.name, r.email, r.phone, r.whatsapp, r.job_title, r.company ? r.company.name : ""].join(" "),
    tabs: [
      { key: "all", label: "All contacts", test: () => true },
      { key: "company", label: "With company", test: r => !!r.company_id },
      { key: "nocompany", label: "No company", test: r => !r.company_id },
      { key: "leads", label: "With leads", test: r => (r.leads_count || 0) > 0 },
      { key: "noleads", label: "No leads", test: r => !(r.leads_count || 0) },
    ],
    chips: [
      {
        id: "company", label: "Company", icon: "building", any: "Any company",
        options: rows => {
          const seen = {};
          rows.forEach(r => { if (r.company) seen[r.company.id] = r.company.name; });
          return [{ v: "none", label: "No company" }].concat(Object.keys(seen).map(id => ({ v: id, label: seen[id] })).sort((a, b) => a.label.localeCompare(b.label)));
        },
        test: (r, v) => v === "none" ? !r.company_id : String(r.company_id) === v,
      },
      {
        id: "status", label: "Lead status", icon: "flag", any: "Any status",
        options: () => TYPES.map(t => ({ v: t, label: D.typeLabel(t) })),
        test: (r, v) => r.latest_lead_type === v,
      },
    ],
    canDelete: r => {
      if (!CFG.isSuperAdmin && r.created_by !== CFG.meId) return "Only the person who created this contact (or a super admin) can delete it.";
      const n = r.leads_count || 0;
      return n ? `This contact has ${n} lead${n === 1 ? "" : "s"}. Delete those leads first.` : null;
    },
    rowMenu: (r, a) => [{ label: "New lead", icon: "plus-circle", fn: () => a.open(r.id, { pane: "leads" }) }],
    drawer: {
      header: r => `<div class="lh-dr-id">${D.avatarHtml(r.name, "lg")}<div style="min-width:0"><h2 class="lh-dr-name">${esc(r.name)}</h2>
        <div class="lh-dr-sub">${r.job_title ? `<span>${esc(r.job_title)}</span>` : ""}${r.company ? `<span><i class="bi bi-building me-1"></i>${esc(r.company.name)}</span>` : ""}${r.latest_lead_type ? D.pill(r.latest_lead_type) : ""}</div></div></div>`,
      quick: r => {
        const wa = D.waUrl(r.whatsapp || r.phone);
        return `<a class="lh-q ${r.email ? "" : "off"}" href="${r.email ? "mailto:" + esc(r.email) : "#"}"><span class="ic"><i class="bi bi-envelope"></i></span>Email</a>
          <a class="lh-q ${r.phone ? "" : "off"}" href="${r.phone ? "tel:" + esc(r.phone) : "#"}"><span class="ic"><i class="bi bi-telephone"></i></span>Call</a>
          <a class="lh-q wa ${wa ? "" : "off"}" href="${wa ? esc(wa) : "#"}"><span class="ic"><i class="bi bi-whatsapp"></i></span>WhatsApp</a>
          <button type="button" class="lh-q" data-dr-act="newLead"><span class="ic"><i class="bi bi-plus-lg"></i></span>Lead</button>`;
      },
      panes: [
        { key: "about", label: "About", render: aboutPane },
        { key: "leads", label: "Leads", badge: r => r.leads ? r.leads.length : (r.leads_count || 0), render: leadsPane },
      ],
      fields: {
        name: textField("Name", "name"),
        email: textField("Email", "email", "email"),
        phone: textField("Phone number", "phone", "tel"),
        whatsapp: textField("WhatsApp", "whatsapp", "tel"),
        job_title: textField("Job title", "job_title"),
        created_by: ro("Created by"), created_at: ro("Create date"),
        co_website: ro("Website"), co_phone: ro("Company phone"), co_gst: ro("GST number"), co_address: ro("Address"),
      },
      onAction: async (act, el, row) => {
        if (act === "newLead") { api.setPane("leads"); const i = $("nlProduct"); if (i) i.focus(); }
        else if (act === "createLead") createLead(row);
        else if (act === "coChange") changeCompany(el, row);
        else if (act === "coRemove") {
          const res = await patch(row, { company_id: null });
          if (res.ok) { Object.assign(row, res.data, { company: null }); api.renderAll(); api.renderDrawer(true); D.toast("Company removed", "dark"); }
          else D.toast(res.message, "danger");
        }
      },
    },
    onCreate: openCreate,
  });

  document.addEventListener("DOMContentLoaded", () => {
    $("lhDrawer").addEventListener("change", e => {
      if (e.target.id === "nlSource") $("nlPlatWrap").classList.toggle("d-none", e.target.value !== "Social Media");
    });
    $("cSubmit").addEventListener("click", submitCreate);
    $("dirCreateModal").addEventListener("keydown", e => { if (e.key === "Enter" && e.target.tagName === "INPUT") { e.preventDefault(); submitCreate(); } });
    $("cCompany").addEventListener("click", e => {
      D.entityPicker(e.currentTarget, {
        placeholder: "Search companies...", searchUrl: R.companiesSearch, listKey: "companies", itemKey: "company", icon: "building",
        meta: c => [c.website, c.gst_number].filter(Boolean).join(" · ") || "—",
        create: name => D.reqJson(R.companyStore, "POST", { name }),
        onPick: c => { CR.company = c; drawCompanyBtn(); },
      });
    });
    $("cCompanyClear").addEventListener("click", () => { CR.company = null; drawCompanyBtn(); });
  });
})();
</script>

@endsection