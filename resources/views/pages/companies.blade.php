@extends('layouts.app')
@section('title', 'Companies | Dalal Adda')

@section('content')

@include('pages.directory._assets')

@include('pages.directory._shell', [
    'dirTitle'  => 'Companies',
    'dirSub'    => 'Every organisation you deal with. Open a company to edit it, manage its contacts and see its leads.',
    'dirCreate' => 'Create company',
    'dirSearch' => 'Search name, GST, phone or website...',
    'dirNoun'   => 'Company',
])

<!-- ===================== CREATE COMPANY ===================== -->
<div class="modal fade" id="dirCreateModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Create company</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="lh-pane"><div class="hint">Only the name is required. Duplicate names and GST numbers are caught automatically.</div></div>
        <div class="row g-3">
          <div class="col-12"><label class="form-label fw-semibold" style="font-size:13px" for="coName">Company name *</label><input class="form-control" id="coName" placeholder="e.g. Sharma Traders Pvt Ltd" autocomplete="off"></div>
          <div class="col-md-6"><label class="form-label fw-semibold" style="font-size:13px" for="coSite">Website</label><input class="form-control" id="coSite" placeholder="www.example.com" autocomplete="off"></div>
          <div class="col-md-6"><label class="form-label fw-semibold" style="font-size:13px" for="coPhone">Company phone</label><input class="form-control" id="coPhone" inputmode="tel" autocomplete="off"></div>
          <div class="col-md-6"><label class="form-label fw-semibold" style="font-size:13px" for="coGst">GST number</label><input class="form-control" id="coGst" autocomplete="off"></div>
          <div class="col-md-6"><label class="form-label fw-semibold" style="font-size:13px" for="coAddr">Address</label><input class="form-control" id="coAddr" autocomplete="off"></div>
        </div>
        <div id="coAlert"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="coSubmit"><i class="bi bi-check2-circle me-1"></i>Create company</button>
      </div>
    </div>
  </div>
</div>

<script>
  window.COMPANY_PAGE_DATA = {
    csrf: @json(csrf_token()),
    isSuperAdmin: @json((bool) $isSuperAdmin),
    meId: @json($meId ?? null),
    admins: @json($admins),
    contactsUrl: @json(url('admin/contacts')),
    leadsUrl: @json(url('admin/leads')),
    routes: {
      data: @json(route('companies.data')),
      show: @json(url('admin/companies') . '/__ID__'),
      destroy: @json(url('admin/companies') . '/__ID__'),
      bulkDelete: @json(route('companies.bulkDestroy')),
      update: @json(url('admin/leads/companies') . '/__ID__'),
      companyStore: @json(route('leads.companies.store')),
      contactsSearch: @json(route('leads.contacts.search')),
      contactStore: @json(route('leads.contacts.store')),
      contactUpdate: @json(url('admin/contacts') . '/__ID__'),
    },
  };
</script>

<script>
(function () {
  "use strict";
  const D = window.DirUI, esc = D.esc, DASH = D.DASH;
  const CFG = window.COMPANY_PAGE_DATA, R = CFG.routes;
  const $ = id => document.getElementById(id);
  let api = null;

  /* ---------------- data helpers ---------------- */

  async function patch(row, body) {
    const res = await D.reqJson(R.update.replace("__ID__", row.id), "PATCH", body);
    if (!res.success) return { ok: false, message: D.firstErr(res, "Could not save.") };
    const c = res.company || {};
    const data = {};
    ["name", "website", "phone", "gst_number", "address"].forEach(k => { if (k in c) data[k] = c[k]; });
    return { ok: true, data };
  }
  async function patchContact(id, body) {
    const res = await D.reqJson(R.contactUpdate.replace("__ID__", id), "PATCH", body);
    return res.success ? { ok: true } : { ok: false, message: D.firstErr(res, "Could not update the contact.") };
  }
  const siteHref = w => !w ? "#" : /^https?:\/\//i.test(w) ? w : "https://" + w;

  /* ---------------- table columns ---------------- */

  const COLS = {
    name: { label: "Company name", icon: "building", sortv: r => r.name.toLowerCase() },
    phone: { label: "Phone number", icon: "telephone", sortv: r => r.phone || "", cell: r => r.phone ? `<a href="tel:${esc(r.phone)}" style="color:inherit">${esc(r.phone)}</a>` : DASH },
    gst_number: { label: "GST number", icon: "receipt", sortv: r => (r.gst_number || "").toUpperCase(), cell: r => r.gst_number ? esc(r.gst_number) : DASH },
    contacts: { label: "Contacts", icon: "people", sortv: r => r.contacts_count || 0, cell: r => `<span class="lh-count">${r.contacts_count || 0}</span>` },
    website: { label: "Website", icon: "globe2", sortv: r => (r.website || "").toLowerCase(), cell: r => r.website ? `<a href="${esc(siteHref(r.website))}" target="_blank" rel="noopener" style="color:inherit">${esc(r.website)}</a>` : DASH },
    address: { label: "Address", icon: "geo-alt", sortv: r => (r.address || "").toLowerCase(), cell: r => r.address ? esc(r.address) : DASH },
    leads: { label: "Leads", icon: "funnel", sortv: r => r.leads_count || 0, cell: r => `<span class="lh-count">${r.leads_count || 0}</span>` },
    created_by: { label: "Created by", icon: "person-badge", sortv: r => (r.creator ? r.creator.name : "").toLowerCase(), cell: r => r.creator ? esc(r.creator.name) : DASH },
    created_at: { label: "Create date", icon: "calendar3", sortv: r => r.created_at || "", cell: r => r.created_at ? esc(D.fmtDate(r.created_at)) : DASH },
  };

  /* ---------------- drawer ---------------- */

  function aboutPane(r) {
    return `<div class="lh-sect"><div class="lh-sect-h"><i class="bi bi-building"></i>About this company</div>
      ${api.prop(r, "name", esc(r.name))}
      ${api.prop(r, "website", esc(r.website))}
      ${api.prop(r, "phone", esc(r.phone))}
      ${api.prop(r, "gst_number", esc(r.gst_number))}
      ${api.prop(r, "address", esc(r.address))}
      ${api.prop(r, "created_by", r.creator ? esc(r.creator.name) : "")}
      ${api.prop(r, "created_at", r.created_at ? esc(D.fmtDate(r.created_at)) + ` <span class="text-muted">(${esc(D.ago(r.created_at))})</span>` : "")}
    </div>`;
  }

  function contactsPane(r) {
    const add = `<div class="lh-sect"><div class="lh-sect-h"><i class="bi bi-person-plus"></i>Add a contact<span class="sp"></span>
        <button type="button" class="lh-link" data-dr-act="attach">Add existing</button></div>
      <div class="lh-mini" style="border-bottom:0"><div class="row g-2">
        <div class="col-12"><label for="ncName">New contact name</label><input class="form-control form-control-sm" id="ncName" placeholder="e.g. Rahul Sharma" autocomplete="off"></div>
        <div class="col-6"><label for="ncPhone">Phone</label><input class="form-control form-control-sm" id="ncPhone" inputmode="tel" autocomplete="off"></div>
        <div class="col-6"><label for="ncEmail">Email</label><input class="form-control form-control-sm" id="ncEmail" type="email" autocomplete="off"></div>
        <div class="col-12"><button type="button" class="btn btn-primary btn-sm" data-dr-act="newContact"><i class="bi bi-plus-lg me-1"></i>Create contact</button></div>
      </div></div></div>`;
    let list;
    if (!r.contacts) list = `<div class="lh-sect"><div class="lh-hint p-3">Loading contacts…</div></div>`;
    else if (!r.contacts.length) list = `<div class="lh-sect"><div class="lh-hint p-3">No contacts in this company yet.</div></div>`;
    else {
      list = `<div class="lh-sect"><div class="lh-sect-h"><i class="bi bi-people"></i>Contacts (${r.contacts.length})</div>` + r.contacts.map(c =>
        `<div class="lh-list-item">${D.avatarHtml(c.name, "sm")}
          <a class="grow" href="${esc(CFG.contactsUrl)}?id=${c.id}" style="color:inherit;text-decoration:none"><div class="t">${esc(c.name)}</div>
          <div class="s">${esc([c.job_title, c.email, c.phone].filter(Boolean).join(" · ") || "—")}</div></a>
          <div class="end"><button type="button" class="lh-iconbtn danger" data-dr-act="detach" data-cid="${c.id}" title="Remove from company" aria-label="Remove ${esc(c.name)} from company"><i class="bi bi-x-lg"></i></button></div></div>`).join("") + "</div>";
    }
    return add + list;
  }

  function leadsPane(r) {
    if (!r.leads) return `<div class="lh-sect"><div class="lh-hint p-3">Loading leads…</div></div>`;
    if (!r.leads.length) return `<div class="lh-sect"><div class="lh-hint p-3">No leads for this company yet. Create one from a contact.</div></div>`;
    return `<div class="lh-sect"><div class="lh-sect-h"><i class="bi bi-funnel"></i>Leads (${r.leads.length})</div>` + r.leads.map(l =>
      `<a class="lh-list-item" href="${esc(CFG.leadsUrl)}/${l.id}/view">${D.avatarHtml(l.customer_name, "sm")}
        <div class="grow"><div class="t">${esc(l.customer_name)}</div><div class="s">${esc([l.product_name, l.source, D.fmtDate(l.created_at)].filter(Boolean).join(" · "))}</div></div>
        <div class="end">${D.pill(l.lead_type)}<i class="bi bi-chevron-right text-muted"></i></div></a>`).join("") + "</div>";
  }

  async function reloadCounts(row) { await api.refresh(); api.renderAll(); }

  function attachExisting(el, row) {
    D.entityPicker(el, {
      placeholder: "Search contacts...", searchUrl: R.contactsSearch, listKey: "contacts", itemKey: "contact", icon: "person",
      exclude: c => c.company && c.company.id === row.id,
      meta: c => [c.email, c.phone, c.company ? "in " + c.company.name : ""].filter(Boolean).join(" · ") || "—",
      onPick: async c => {
        if (!c) return;
        const res = await patchContact(c.id, { company_id: row.id });
        if (res.ok) { D.toast("Contact added", "dark"); reloadCounts(row); } else D.toast(res.message, "danger");
      },
    });
  }

  async function newContact(row) {
    const f = { name: $("ncName").value.trim(), phone: $("ncPhone").value.trim(), email: $("ncEmail").value.trim(), company_id: row.id };
    if (!f.name) { D.toast("Name is required.", "danger"); $("ncName").focus(); return; }
    if (!f.phone && !f.email) { D.toast("A contact needs a phone number or an email address.", "danger"); $("ncPhone").focus(); return; }
    let res;
    try { res = await D.reqJson(R.contactStore, "POST", f); } catch (e) { return; }
    if (res.success) { D.toast(res.message || "Contact created"); reloadCounts(row); }
    else D.toast(D.firstErr(res, "Could not create the contact."), "danger");
  }

  const textField = (label, key) => ({
    label, kind: "text", get: r => r[key],
    save: (r, v) => key === "name" && !v ? { ok: false, message: "Company name is required." } : patch(r, { [key]: v }),
  });
  const ro = label => ({ label, kind: "ro" });

  /* ---------------- create modal ---------------- */

  let busy = false;
  const alertHtml = (msg, btn) => `<div class="lh-alert"><i class="bi bi-exclamation-triangle-fill"></i><span>${esc(msg)}</span>${btn || ""}</div>`;
  function afterModalHidden(id, fn) {
    const m = $(id);
    m.addEventListener("hidden.bs.modal", function h() { m.removeEventListener("hidden.bs.modal", h); fn(); });
    bootstrap.Modal.getOrCreateInstance(m).hide();
  }
  function openCreate() {
    ["coName", "coSite", "coPhone", "coGst", "coAddr"].forEach(id => { $(id).value = ""; });
    $("coAlert").innerHTML = ""; busy = false;
    const m = $("dirCreateModal");
    const focus = () => { m.removeEventListener("shown.bs.modal", focus); $("coName").focus(); };
    m.addEventListener("shown.bs.modal", focus);
    bootstrap.Modal.getOrCreateInstance(m).show();
  }
  async function submitCreate() {
    if (busy) return;
    const f = { name: $("coName").value.trim(), website: $("coSite").value.trim(), phone: $("coPhone").value.trim(), gst_number: $("coGst").value.trim(), address: $("coAddr").value.trim() };
    if (!f.name) { D.toast("Company name is required.", "danger"); $("coName").focus(); return; }
    busy = true; $("coSubmit").disabled = true;
    let res;
    try { res = await D.reqJson(R.companyStore, "POST", f); } catch (e) { busy = false; $("coSubmit").disabled = false; return; }
    busy = false; $("coSubmit").disabled = false;
    if (res.success) {
      $("coAlert").innerHTML = "";
      D.toast(res.message || "Company created");
      await api.reload();
      afterModalHidden("dirCreateModal", () => api.open(res.company.id));
    } else if (res.duplicate) {
      $("coAlert").innerHTML = alertHtml(res.message, res.company ? `<button type="button" class="btn btn-sm btn-warning" id="coOpenDupe">Use existing</button>` : "");
      const b = $("coOpenDupe");
      if (b) b.addEventListener("click", () => afterModalHidden("dirCreateModal", () => api.open(res.company.id)));
    } else D.toast(D.firstErr(res, "Could not create the company."), "danger");
  }

  /* ---------------- boot ---------------- */

  api = D.create({
    key: "companies", csrf: CFG.csrf, isSuperAdmin: CFG.isSuperAdmin, admins: CFG.admins,
    noun: "company", nouns: "companies", listKey: "companies", itemKey: "company",
    routes: R, cols: COLS, defaultCols: ["name", "phone", "gst_number", "contacts"],
    createLabel: "Create company", emptyIcon: "building", emptyTitle: "No companies yet", emptyText: "Create your first company to get started.",
    deleteText: "The company is deleted permanently. This can't be undone.",
    avatar: () => D.companyIcon("sm"), nameSub: r => r.gst_number || "",
    searchText: r => [r.name, r.website, r.phone, r.gst_number, r.address].join(" "),
    tabs: [
      { key: "all", label: "All companies", test: () => true },
      { key: "gst", label: "With GST", test: r => !!r.gst_number },
      { key: "nogst", label: "No GST", test: r => !r.gst_number },
      { key: "leads", label: "With leads", test: r => (r.leads_count || 0) > 0 },
      { key: "nocontacts", label: "No contacts", test: r => !(r.contacts_count || 0) },
    ],
    chips: [
      {
        id: "contacts", label: "Contacts", icon: "people", any: "Any number",
        options: () => [{ v: "0", label: "No contacts" }, { v: "1", label: "1 contact" }, { v: "2", label: "2 or more" }],
        test: (r, v) => { const n = r.contacts_count || 0; return v === "0" ? n === 0 : v === "1" ? n === 1 : n >= 2; },
      },
    ],
    canDelete: r => {
      if (!CFG.isSuperAdmin && r.created_by !== CFG.meId) return "Only the person who created this company (or a super admin) can delete it.";
      const c = r.contacts_count || 0, l = r.leads_count || 0;
      return c || l ? `This company still has ${c} contact(s) and ${l} lead(s). Move or delete them first.` : null;
    },
    rowMenu: (r, a) => [{ label: "Add contact", icon: "person-plus", fn: () => a.open(r.id, { pane: "contacts" }) }],
    drawer: {
      header: r => `<div class="lh-dr-id"><div class="lh-co-ic" style="width:58px;height:58px;font-size:26px;border-radius:16px;background:#fff"><i class="bi bi-building"></i></div><div style="min-width:0"><h2 class="lh-dr-name">${esc(r.name)}</h2>
        <div class="lh-dr-sub">${r.gst_number ? `<span><i class="bi bi-receipt me-1"></i>${esc(r.gst_number)}</span>` : ""}<span><i class="bi bi-people me-1"></i>${r.contacts_count || 0} contact${(r.contacts_count || 0) === 1 ? "" : "s"}</span><span><i class="bi bi-funnel me-1"></i>${r.leads_count || 0} lead${(r.leads_count || 0) === 1 ? "" : "s"}</span></div></div></div>`,
      quick: r => `<a class="lh-q ${r.phone ? "" : "off"}" href="${r.phone ? "tel:" + esc(r.phone) : "#"}"><span class="ic"><i class="bi bi-telephone"></i></span>Call</a>
        <a class="lh-q ${r.website ? "" : "off"}" href="${esc(siteHref(r.website))}" target="_blank" rel="noopener"><span class="ic"><i class="bi bi-globe2"></i></span>Website</a>
        <button type="button" class="lh-q" data-dr-act="goContacts"><span class="ic"><i class="bi bi-person-plus"></i></span>Contact</button>`,
      panes: [
        { key: "about", label: "About", render: aboutPane },
        { key: "contacts", label: "Contacts", badge: r => r.contacts ? r.contacts.length : (r.contacts_count || 0), render: contactsPane },
        { key: "leads", label: "Leads", badge: r => r.leads ? r.leads.length : (r.leads_count || 0), render: leadsPane },
      ],
      fields: {
        name: textField("Company name", "name"),
        website: textField("Website", "website"),
        phone: textField("Phone number", "phone"),
        gst_number: textField("GST number", "gst_number"),
        address: textField("Address", "address"),
        created_by: ro("Created by"), created_at: ro("Create date"),
      },
      onAction: (act, el, row) => {
        if (act === "goContacts") { api.setPane("contacts"); const i = $("ncName"); if (i) i.focus(); }
        else if (act === "attach") attachExisting(el, row);
        else if (act === "newContact") newContact(row);
        else if (act === "detach") {
          patchContact(parseInt(el.dataset.cid, 10), { company_id: null }).then(res => {
            if (res.ok) { D.toast("Contact removed from company", "dark"); reloadCounts(row); } else D.toast(res.message, "danger");
          });
        }
      },
    },
    onCreate: openCreate,
  });

  document.addEventListener("DOMContentLoaded", () => {
    $("coSubmit").addEventListener("click", submitCreate);
    $("dirCreateModal").addEventListener("keydown", e => { if (e.key === "Enter" && e.target.tagName === "INPUT") { e.preventDefault(); submitCreate(); } });
  });
})();
</script>

@endsection