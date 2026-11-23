/* Company > Lead Integrations (jQuery): live filters, connect wizard, details modal (setup / WhatsApp / test / rotate / remove). */
$(function () {
  $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content'), 'Accept': 'application/json' } });
  var esc = function (s) { return $('<div>').text(s == null ? '' : s).html(); };
  var firstError = function (x) { var r = x.responseJSON || {}, e = r.errors; return e ? e[Object.keys(e)[0]][0] : (r.message || 'Something went wrong. Please try again.'); };
  var inputName = function (n) { return n.replace(/\.([^.]+)/g, '[$1]'); };          // cfg.page_id -> cfg[page_id]
  var dirty = false;

  /* ---------- live filters (no page reload) ---------- */
  var $filter = $('#intFilter'), $table = $('#intTable'), timer, xhr;
  var filtered = function () { return $.trim($filter.find('[name=q]').val()) || $filter.find('[name=provider]').val() || $filter.find('[name=status]').val() || $filter.find('[name=method]').val(); };
  function load(url) {
    if (xhr) xhr.abort(); $table.addClass('is-loading');
    xhr = $.ajax({ url: url, dataType: 'html', headers: { 'X-Partial': '1', 'Accept': 'text/html' } })
      .done(function (html) { $table.html(html); history.replaceState(null, '', url); })
      .always(function () { $table.removeClass('is-loading'); });
  }
  function apply() {
    var qs = $filter.find(':input').filter(function () { return this.name && this.value; }).serialize();
    $filter.find('.js-clear').toggleClass('d-none', !filtered());
    load($filter.attr('action') + (qs ? '?' + qs : ''));
  }
  $filter.on('change', 'select', apply);
  $filter.on('input', '[name=q]', function () { clearTimeout(timer); timer = setTimeout(apply, 400); });
  $filter.on('submit', function (e) { e.preventDefault(); apply(); });
  $filter.on('click', '.prov-pill', function () { $('.prov-pill').removeClass('is-on'); $(this).addClass('is-on'); $filter.find('[name=provider]').val($(this).data('provider')); apply(); });
  $(document).on('click', '.js-clear', function () {
    $filter.find('[name=q], [name=provider], [name=status], [name=method]').val(''); $('.prov-pill').removeClass('is-on').first().addClass('is-on'); apply();
  });
  $table.on('click', '.pagination a', function (e) { e.preventDefault(); load(this.href); });

  /* ---------- details modal ---------- */
  var detail = new bootstrap.Modal('#intModal'), $c = $('#intModalContent'), curUrl;
  var skeleton = '<div class="modal-header"><div class="skeleton" style="height:28px;width:45%"></div></div><div class="modal-body"><div class="skeleton mb-3" style="height:70px"></div><div class="skeleton mb-2" style="height:18px;width:80%"></div><div class="skeleton" style="height:18px;width:60%"></div></div>';
  var tabId = function (n) { return n ? '#tab' + n.charAt(0).toUpperCase() + n.slice(1) : null; };
  function showTab(sel) { var t = $c.find('.nav-link[data-bs-target="' + sel + '"]'); if (t.length) bootstrap.Tab.getOrCreateInstance(t[0]).show(); }
  function loadDetail(url, tab) {
    curUrl = url; $c.html(skeleton);
    $.ajax({ url: url, dataType: 'html', headers: { 'Accept': 'text/html' } })
      .done(function (html) { $c.html(html); if (tab) showTab(tab); })
      .fail(function () { $c.html('<div class="modal-body"><div class="empty-state"><i class="bi bi-exclamation-circle"></i>Unable to load this source.<div class="mt-2"><button class="btn btn-light border btn-sm" data-bs-dismiss="modal">Close</button></div></div></div>'); });
  }
  function openDetail(url, tab) { detail.show(); loadDetail(url, tabId(tab)); }
  var currentTab = function () { return $c.find('.nav-link.active').data('bsTarget'); };

  $(document).on('click', '.js-open', function (e) { e.stopPropagation(); dirty = false; openDetail($(this).data('url'), $(this).data('tab')); });
  $(document).on('click', '.js-row', function (e) { if (!$(e.target).closest('a,button').length) { dirty = false; openDetail($(this).data('url')); } });
  $(document).on('keydown', '.js-row', function (e) { if (e.key === 'Enter' && e.target === this) { dirty = false; openDetail($(this).data('url')); } });
  $('#intModal').on('hidden.bs.modal', function () { $c.empty(); if (dirty) location.reload(); }); // refresh table and counters after changes
  // Arrived from the Connect page (?open=ID): show the new source's URL / token / steps
  var openUrl = $table.data('open');
  if (openUrl) { dirty = false; openDetail(openUrl); history.replaceState(null, '', location.pathname); }
  $(document).on('click', '.js-eye', function () { var $i = $(this).closest('.input-group').find('input').first(), s = $i.attr('type') === 'password'; $i.attr('type', s ? 'text' : 'password'); $(this).find('i').toggleClass('bi-eye bi-eye-slash'); });
  $(document).on('click', '.js-copy', function () { navigator.clipboard.writeText($($(this).data('target')).val()).then(function () { MP.toast('success', 'Copied.'); }); });
  $(document).on('change', '.js-wa-toggle', function () { $(this).closest('form').find('.wa-box').toggleClass('d-none', !this.checked); });
  $(document).on('input', '[name=wa_number]', function () { this.value = this.value.replace(/\D/g, ''); });
  $(document).on('input', '.is-invalid', function () { $(this).removeClass('is-invalid'); });
  $(document).on('input', '#intConfirm', function () { $('#intDeleteBtn').prop('disabled', $.trim(this.value) !== $(this).data('name')); });

  // One-tap actions. Pause / rotate / resume need a second click; "test" runs at once.
  function post($b, confirmFirst) {
    if (confirmFirst && !$b.data('armed')) {
      $b.data('armed', true).data('label', $b.html()).text('Click again to confirm');
      setTimeout(function () { if ($b.data('armed')) $b.data('armed', false).html($b.data('label')); }, 3000); return;
    }
    var label = $b.data('label') || $b.html(); $b.prop('disabled', true).data('armed', false);
    $.post($b.data('url'), $b.data('payload') || {})
      .done(function (r) { dirty = true; MP.toast('success', r.message); loadDetail(curUrl, currentTab()); })
      .fail(function (x) { MP.toast('danger', firstError(x)); $b.prop('disabled', false).html(label); });
  }
  $(document).on('click', '.js-act', function () { post($(this), true); });
  $(document).on('click', '.js-run', function () { post($(this), false); });

  // Setup / WhatsApp / remove forms inside the modal
  $('#intModal').on('submit', 'form[data-cm]', function (e) {
    e.preventDefault();
    var $f = $(this), kind = $f.data('cm'), $b = $f.find('[type=submit]').prop('disabled', true), tab = currentTab();
    $b.find('.spinner-border').removeClass('d-none'); $f.find('.is-invalid').removeClass('is-invalid'); $f.find('.cm-error').addClass('d-none');
    $.post($f.attr('action'), $f.serialize())
      .done(function (r) { dirty = true; MP.toast('success', r.message); if (kind === 'delete') detail.hide(); else loadDetail(curUrl, tab); })
      .fail(function (x) {
        $b.prop('disabled', false).find('.spinner-border').addClass('d-none'); showErrors($f, x);
      });
  });
  function showErrors($f, x) {
    var r = x.responseJSON || {}, rest = [];
    if (x.status === 422 && r.errors) {
      $.each(r.errors, function (n, m) {
        var $i = $f.find('[name="' + inputName(n) + '"]').first();
        if ($i.length) $i.addClass('is-invalid').closest('.mb-3').find('.invalid-feedback').first().text(m[0]); else rest.push(m[0]);
      });
      if (rest.length) $f.find('.cm-error').text(rest.join(' ')).removeClass('d-none');
      $f.find('.is-invalid').first().trigger('focus');
    } else $f.find('.cm-error').text(firstError(x)).removeClass('d-none');
  }

  /* ---------- connect wizard ---------- */
  var $cm = $('#intCreateModal'); if (!$cm.length) return;
  var create = new bootstrap.Modal($cm[0]), $cf = $('#intCreateForm'), spec = $cm.data('spec'), lastDefault = '';
  function renderFields() {
    var p = $cf.find('[name=provider]:checked').val(), m = $cf.find('[name=method]:checked').val(), s = spec[p][m], html = '';
    $.each(s.fields, function (_, f) {
      html += '<div class="mb-3"><label class="form-label" for="f_' + f.key + '">' + esc(f.label) + (f.req ? ' <span class="text-danger">*</span>' : ' <span class="caption">(optional)</span>') + '</label>' +
        '<input id="f_' + f.key + '" name="cfg[' + f.key + ']" type="' + (f.secret ? 'password' : 'text') + '" class="form-control' + (f.secret ? ' font-monospace' : '') + '" placeholder="' + esc(f.ph) + '" autocomplete="' + (f.secret ? 'new-password' : 'off') + '">' +
        '<div class="invalid-feedback"></div>' + (f.help ? '<div class="form-text">' + esc(f.help) + '</div>' : '') + '</div>';
    });
    $('#fieldsBox').html(html); $('#methodHint').text(s.hint);
    var $n = $('#intName'); if (!$.trim($n.val()) || $n.val() === lastDefault) { lastDefault = spec[p].label; $n.val(lastDefault); }
  }
  $cf.on('change', '[name=provider], [name=method]', renderFields);
  $(document).on('click', '.js-int-add', function () {
    $cf[0].reset(); $cf.find('.is-invalid').removeClass('is-invalid'); $cf.find('.cm-error').addClass('d-none'); $cf.find('.wa-box').addClass('d-none'); lastDefault = ''; renderFields(); create.show();
  });
  $cm.on('shown.bs.modal', function () { $('#intName').trigger('focus').select(); });
  $cf.on('submit', function (e) {
    e.preventDefault();
    var $b = $cf.find('[type=submit]').prop('disabled', true); $b.find('.spinner-border').removeClass('d-none'); $cf.find('.is-invalid').removeClass('is-invalid'); $cf.find('.cm-error').addClass('d-none');
    $.post($cf.attr('action'), $cf.serialize())
      .done(function (r) {
        dirty = true; MP.toast('success', r.message);
        $cm.one('hidden.bs.modal', function () { openDetail(r.show_url); }); create.hide(); // show the new URL / token straight away
      })
      .fail(function (x) { showErrors($cf, x); })
      .always(function () { $b.prop('disabled', false).find('.spinner-border').addClass('d-none'); });
  });
});