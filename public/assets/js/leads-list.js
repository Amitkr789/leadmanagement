/* Company > Leads (jQuery): live filters, bulk select + delete, lead modal (status / notes / files with progress / edit),
   add lead, import wizard. Needs jQuery, Bootstrap and MP.toast from the layout. */
$(function () {
  var $page = $('#leadPage'); if (!$page.length) return;
  $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content'), 'Accept': 'application/json' } });

  var base = $page.data('base'), parts = ['#leadTop', '#leadChips', '#leadTable'];
  var esc = function (s) { return $('<div>').text(s == null ? '' : s).html(); };
  var toast = function (t, m) { MP.toast(t, m); };
  var firstError = function (x) {
    var r = x.responseJSON || {}, e = r.errors;
    if (x.status === 413) return 'The file is larger than the server allows.';
    return e ? e[Object.keys(e)[0]][0] : (r.message || 'Something went wrong. Please try again.');
  };
  var bytes = function (n) { return n >= 1048576 ? (n / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(n / 1024)) + ' KB'; };

  /* ---------- form helpers ---------- */
  function fb($i) { return $i.parents().filter(function () { return $(this).children('.invalid-feedback').length; }).first().children('.invalid-feedback').first(); }
  function bad($i, msg) { $i.addClass('is-invalid'); fb($i).text(msg).addClass('d-block'); return false; }
  function clear($f) { $f.find('.is-invalid').removeClass('is-invalid'); $f.find('.invalid-feedback').removeClass('d-block').text(''); $f.find('.cm-error').addClass('d-none'); }
  function busy($b, on) { $b.prop('disabled', on).find('.spinner-border').toggleClass('d-none', !on); }
  function fail(x, $f) {
    var r = x.responseJSON || {}, rest = [];
    if (x.status === 422 && r.errors) {
      $.each(r.errors, function (n, m) { var $i = $f.find('[name="' + n + '"]').not(':radio').first(); if ($i.length) bad($i, m[0]); else rest.push(m[0]); });
      if (rest.length) { if ($f.find('.cm-error').length) $f.find('.cm-error').text(rest.join(' ')).removeClass('d-none'); else toast('danger', rest[0]); }
      $f.find('.is-invalid').first().trigger('focus');
    } else toast('danger', firstError(x));
  }
  function leadCheck($f) {
    var ok = true; clear($f);
    $f.find('[required]:not(:disabled)').each(function () { if (!$.trim(this.value)) ok = bad($(this), 'This field is required.'); });
    var $m = $f.find('[name=mobile]'), $e = $f.find('[name=email]'), d = ($m.val() || '').replace(/\D/g, '');
    if ($m.val() && !(d.length === 10 || (d.length === 12 && d.indexOf('91') === 0) || (d.length === 11 && d.charAt(0) === '0'))) ok = bad($m, 'Enter a valid 10-digit number.');
    else if (!$m.val() && !$.trim($e.val())) ok = bad($m, 'Add a mobile number or an email.');
    if ($e.val() && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test($e.val())) ok = bad($e, 'Enter a valid email address.');
    if (!ok) $f.find('.is-invalid').first().trigger('focus');
    return ok;
  }
  // First click arms the button, second click does it (for deletes)
  function twoClick($b, fn) {
    if (!$b.data('armed')) {
      $b.data('armed', true).data('label', $b.html()).addClass('is-armed').text('Sure?');
      setTimeout(function () { if ($b.data('armed')) $b.data('armed', false).removeClass('is-armed').html($b.data('label')); }, 3000); return;
    }
    $b.data('armed', false).removeClass('is-armed').html($b.data('label')); fn();
  }
  $(document).on('input change', '#leadPage .is-invalid', function () { $(this).removeClass('is-invalid'); fb($(this)).removeClass('d-block'); });

  /* ---------- live filters: no page refresh ---------- */
  var $f = $('#leadFilter'), $t = $('#leadTable'), timer, xhr, seq = 0;
  function hasFilters() { return $f.serializeArray().some(function (v) { return v.value; }); }
  function sync() {
    $('.js-lclear').first().toggleClass('d-none', !hasFilters());
    var branch = $f.find('[name=scope]:checked').val() === 'branch'; $f.find('.lp-branch').toggleClass('d-none', !branch); if (!branch) $f.find('.lp-branch').val('');
  }
  function run(url) {
    var my = ++seq; if (xhr) xhr.abort(); $t.addClass('is-loading');
    xhr = $.ajax({ url: url || $f.attr('action'), data: url ? null : $f.serialize(), dataType: 'html', headers: { 'X-Partial': '1', 'Accept': 'text/html' } })
      .done(function (html) {
        $t.html(html); var qs = url ? (url.split('?')[1] || '') : $f.serialize();
        history.replaceState(null, '', location.pathname + (qs ? '?' + qs : '')); bulk();
      })
      .fail(function (x) { if (x.statusText !== 'abort') toast('danger', 'Could not load leads. Please try again.'); })
      .always(function () { if (my === seq) $t.removeClass('is-loading'); });
  }
  function soft() {   // refresh KPI cards, counts and table in place after a change
    $.ajax({ url: location.href, dataType: 'html', headers: { 'Accept': 'text/html' } }).done(function (html) {
      var $d = $('<div>').append($.parseHTML(html));
      $.each(parts, function (_, s) { var $n = $d.find(s); if ($n.length) $(s).html($n.html()); });
      bulk(); sync();
    });
  }
  $f.on('change', 'select, [name=scope]', function () { sync(); run(); });
  $f.on('input', '[name=q]', function () { clearTimeout(timer); timer = setTimeout(run, 350); });
  $f.on('submit', function (e) { e.preventDefault(); run(); });
  $f.on('click', '.lp-pill[data-source]', function () { $f.find('[name=source]').val($(this).data('source')); $f.find('.lp-pill').removeClass('is-on'); $(this).addClass('is-on'); sync(); run(); });
  $(document).on('click', '.js-lclear', function (e) {
    e.preventDefault(); $f.find('[name=q]').val(''); $f.find('select').val(''); $f.find('[name=scope][value=""]').prop('checked', true);
    $f.find('[name=source]').val(''); $f.find('.lp-pill').removeClass('is-on').filter('[data-source=""]').addClass('is-on'); sync(); run();
  });
  $t.on('click', '.pagination a', function (e) { e.preventDefault(); run($(this).attr('href')); $t[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' }); });
  sync();

  /* ---------- select many + delete ---------- */
  function bulk() {
    var n = $t.find('.js-chk:checked').length, all = $t.find('.js-chk').length;
    $('#bulkBar').toggleClass('d-none', !n); $('#bulkN').text(n);
    $t.find('.js-chk-all').prop('checked', !!n && n === all).prop('indeterminate', n > 0 && n < all);
    $t.find('.js-chk').each(function () { $(this).closest('tr').toggleClass('is-sel', this.checked); });
  }
  $t.on('change', '.js-chk', bulk);
  $t.on('change', '.js-chk-all', function () { $t.find('.js-chk').prop('checked', this.checked); bulk(); });
  $t.on('click', '.js-bulk-clear', function () { $t.find('.js-chk, .js-chk-all').prop('checked', false); bulk(); });

  var cf = new bootstrap.Modal('#leadConfirm');
  function ask(o, go) {
    $('#lcfTitle').text(o.title); $('#lcfText').text(o.text); $('#lcfOk span:first').text(o.btn); busy($('#lcfOk'), false);
    $('#lcfOk').off('click').on('click', function () {
      var $b = $(this); busy($b, true);
      go().done(function (r) { toast('success', r.message); cf.hide(); soft(); })
          .fail(function (x) { busy($b, false); cf.hide(); toast('danger', firstError(x)); });
    });
    cf.show();
  }
  $t.on('click', '.js-bulk-del', function () {
    var ids = $t.find('.js-chk:checked').map(function () { return this.value; }).get(); if (!ids.length) return;
    ask({ title: 'Delete ' + ids.length + (ids.length === 1 ? ' lead?' : ' leads?'), text: 'Their notes and files are deleted too. This cannot be undone.', btn: 'Delete' },
      function () { return $.post(base, { _method: 'DELETE', ids: ids }); });
  });

  /* ---------- lead modal (skeleton while loading) ---------- */
  var dm = new bootstrap.Modal('#leadModal'), $m = $('#leadModal'), $c = $('#leadModalContent'), curUrl, dirty = false, active = 0;
  var skeleton = '<div class="modal-header"><div class="skeleton" style="height:28px;width:45%"></div></div><div class="modal-body"><div class="skeleton mb-3" style="height:70px"></div><div class="skeleton mb-2" style="height:18px;width:80%"></div><div class="skeleton" style="height:18px;width:60%"></div></div>';
  var tabId = function (n) { return n ? '#tab' + n.charAt(0).toUpperCase() + n.slice(1) : null; };
  function showTab(sel) { var t = $c.find('.nav-link[data-bs-target="' + sel + '"]'); if (t.length) bootstrap.Tab.getOrCreateInstance(t[0]).show(); }
  function loadDetail(url, tab) {
    curUrl = url; var keep = tab || $c.find('.cm-tabs .nav-link.active').attr('data-bs-target'); $c.html(skeleton);
    $.ajax({ url: url, dataType: 'html', headers: { 'Accept': 'text/html' } })
      .done(function (html) { $c.html(html); if (keep) showTab(keep); })
      .fail(function () { $c.html('<div class="modal-body"><div class="empty-state"><i class="bi bi-exclamation-circle"></i>Unable to load this lead.<div class="mt-2"><button class="btn btn-light border btn-sm" data-bs-dismiss="modal">Close</button></div></div></div>'); });
  }
  function openLead(url, tab) { dirty = false; dm.show(); loadDetail(url, tabId(tab)); }
  $(document).on('click', '#leadPage .js-lopen', function (e) { e.stopPropagation(); openLead($(this).data('url')); });
  $(document).on('click', '#leadPage .js-lrow', function (e) { if (!$(e.target).closest('a, button, input, label').length) openLead($(this).data('url')); });
  $(document).on('keydown', '#leadPage .js-lrow', function (e) { if (e.key === 'Enter' && e.target === this) openLead($(this).data('url')); });
  $m.on('hide.bs.modal', function (e) { if (active) { e.preventDefault(); toast('info', 'Uploads are still running. Please wait for them to finish.'); } });
  $m.on('hidden.bs.modal', function () { $c.empty(); jobs = {}; queue = []; if (dirty) soft(); });

  var setCount = function (k, n) { $('.js-cnt-' + k).text(n); };

  // status chips: update in place
  $m.on('click', '.st-chip', function () {
    var $b = $(this), $w = $('#stChips'); if ($b.hasClass('is-on')) return; $w.addClass('is-busy');
    $.post($w.data('url'), { status: $b.data('status') })
      .done(function (r) { $w.find('.st-chip').removeClass('is-on'); $b.addClass('is-on'); $('#hdStatus').attr('class', 'status ms-1 tone-' + r.tone).text(r.label); dirty = true; toast('success', r.message); })
      .fail(function (x) { toast('danger', firstError(x)); })
      .always(function () { $w.removeClass('is-busy'); });
  });

  // edit lead
  $m.on('submit', 'form[data-cm=edit]', function (e) {
    e.preventDefault(); var $fm = $(this), $b = $fm.find('[type=submit]'); if (!leadCheck($fm)) return; busy($b, true);
    $.post($fm.attr('action'), $fm.serialize())
      .done(function (r) { dirty = true; toast('success', r.message); if (!active) loadDetail(curUrl, '#tabEdit'); })
      .fail(function (x) { fail(x, $fm); }).always(function () { busy($b, false); });
  });
  // delete lead (inside the modal)
  $m.on('click', '.js-act', function () {
    var $b = $(this);
    twoClick($b, function () {
      $b.prop('disabled', true);
      $.post($b.data('url'), $b.data('payload')).done(function (r) { dirty = true; toast('success', r.message); dm.hide(); })
        .fail(function (x) { $b.prop('disabled', false); toast('danger', firstError(x)); });
    });
  });

  /* ---------- notes ---------- */
  $m.on('input', '#noteBody', function () { this.style.height = 'auto'; this.style.height = Math.min(this.scrollHeight, 220) + 'px'; $('#noteCnt').text(this.value.length); });
  $m.on('keydown', '#noteBody', function (e) { if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') { e.preventDefault(); $('#noteForm').trigger('submit'); } });
  $m.on('submit', '#noteForm', function (e) {
    e.preventDefault(); var $fm = $(this), $b = $fm.find('[type=submit]'), $ta = $('#noteBody'); clear($fm);
    if (!$.trim($ta.val())) { bad($ta, 'Write something first.'); $ta.trigger('focus'); return; }
    busy($b, true);
    $.post($fm.attr('action'), $fm.serialize())
      .done(function (r) { $('#noteList .ln-empty').remove(); $('#noteList').prepend(r.html); setCount('notes', r.count); $ta.val('').trigger('input').trigger('focus'); dirty = true; toast('success', r.message); })
      .fail(function (x) { fail(x, $fm); }).always(function () { busy($b, false); });
  });
  $m.on('click', '.js-ndel', function () {
    var $b = $(this), $it = $b.closest('.ln-item');
    twoClick($b, function () {
      $.post($b.data('url'), { _method: 'DELETE' }).done(function (r) {
        $it.slideUp(150, function () { $(this).remove(); if (!$('#noteList .ln-item').length) $('#noteList').html('<div class="empty-state ln-empty"><i class="bi bi-chat-left-text"></i>No notes yet. Write what you discussed and what to do next.</div>'); });
        setCount('notes', r.count); dirty = true;
      }).fail(function (x) { toast('danger', firstError(x)); });
    });
  });

  /* ---------- files: one request per file, so every file shows its own progress ---------- */
  var ACCEPT = /\.(jpe?g|png|webp|gif|pdf|docx?|xlsx?|csv|txt|pptx?|mp3|m4a|wav|ogg)$/i, MAXB = 10 * 1024 * 1024, uid = 0, queue = [], running = 0, jobs = {};
  function itemHtml(id, f, err) {
    return '<div class="up-item' + (err ? ' is-error' : '') + '" id="' + id + '"><i class="bi bi-file-earmark up-ico"></i><div class="up-main"><div class="up-top"><span class="up-name text-truncate">' + esc(f.name) + '</span><span class="caption">' + bytes(f.size) + '</span></div>' +
      '<div class="progress" style="height:5px"><div class="progress-bar" style="width:0%"></div></div><div class="up-msg caption">' + (err ? esc(err) : 'Waiting…') + '</div></div>' +
      '<button type="button" class="btn-ico js-upx" aria-label="Cancel or dismiss"><i class="bi bi-x-lg"></i></button></div>';
  }
  function addFiles(list) {
    var url = $('#dz').data('upload'); if (!url) return;
    $.each(list, function (_, f) {
      var id = 'up' + (++uid), err = !ACCEPT.test(f.name) ? 'This file type is not allowed.' : f.size > MAXB ? 'Larger than 10 MB.' : !f.size ? 'The file is empty.' : '';
      $('#upQueue').append(itemHtml(id, f, err));
      if (!err) { jobs[id] = { id: id, f: f, url: url }; queue.push(id); }
    });
    pump();
  }
  function pump() { while (running < 2 && queue.length) send(jobs[queue.shift()]); }
  function send(j) {
    if (!j) return;
    var $i = $('#' + j.id), fd = new FormData(); fd.append('file', j.f); running++; active++;
    $i.addClass('is-up').find('.up-msg').text('Uploading…'); $i.find('.progress-bar').css('width', '0%');
    j.xhr = $.ajax({ url: j.url, method: 'POST', data: fd, processData: false, contentType: false, dataType: 'json',
      xhr: function () {
        var x = $.ajaxSettings.xhr();
        if (x.upload) x.upload.addEventListener('progress', function (e) {
          if (!e.lengthComputable) return; var p = Math.round(e.loaded / e.total * 100);
          $i.find('.progress-bar').css('width', p + '%'); $i.find('.up-msg').text(p < 100 ? 'Uploading… ' + p + '%' : 'Saving…');
        });
        return x;
      } })
      .done(function (r) {
        $i.removeClass('is-up').addClass('is-done').find('.progress-bar').css('width', '100%'); $i.find('.up-msg').text('Uploaded');
        $('#fileList .lf-empty').remove(); $('#fileList').prepend(r.html); setCount('files', r.count); dirty = true; delete jobs[j.id];
        setTimeout(function () { $i.slideUp(150, function () { $(this).remove(); }); }, 1200);
      })
      .fail(function (x, st) {
        if (st === 'abort') { $i.remove(); delete jobs[j.id]; return; }
        $i.removeClass('is-up').addClass('is-error').find('.up-msg').html(esc(firstError(x)) + ' <a href="#" class="js-upretry">Try again</a>');
      })
      .always(function () { running--; active--; pump(); });
  }
  $m.on('click', '#dz', function () { $('#fileInput').trigger('click'); });
  $m.on('keydown', '#dz', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); $('#fileInput').trigger('click'); } });
  $m.on('change', '#fileInput', function () { addFiles(this.files); this.value = ''; });
  $m.on('dragenter dragover', '#dz', function (e) { e.preventDefault(); $(this).addClass('is-over'); });
  $m.on('dragleave drop', '#dz', function (e) { e.preventDefault(); $(this).removeClass('is-over'); if (e.type === 'drop') addFiles(e.originalEvent.dataTransfer.files); });
  $m.on('click', '.js-upx', function () {
    var $i = $(this).closest('.up-item'), id = $i.attr('id'), j = jobs[id];
    if (j && j.xhr && $i.hasClass('is-up')) { j.xhr.abort(); return; }
    queue = $.grep(queue, function (q) { return q !== id; }); delete jobs[id]; $i.remove();
  });
  $m.on('click', '.js-upretry', function (e) { e.preventDefault(); var $i = $(this).closest('.up-item'); $i.removeClass('is-error').find('.up-msg').text('Waiting…'); queue.push($i.attr('id')); pump(); });

  function fsel() {
    var n = $('#fileList .js-fchk:checked').length, all = $('#fileList .js-fchk').length;
    $('.js-fdel').toggleClass('d-none', !n).find('b').text(n); $('.js-fall').prop('checked', !!n && n === all).prop('indeterminate', n > 0 && n < all);
  }
  $m.on('change', '.js-fchk', fsel);
  $m.on('change', '.js-fall', function () { $('#fileList .js-fchk').prop('checked', this.checked); fsel(); });
  function delFiles(ids) {
    $.post($('#fileList').data('delete'), { _method: 'DELETE', ids: ids }).done(function (r) {
      $.each(r.deleted, function (_, id) { $('#fileList .lf-row[data-id=' + id + ']').remove(); });
      if (!$('#fileList .lf-row').length) $('#fileList').html('<div class="empty-state lf-empty"><i class="bi bi-paperclip"></i>No files yet. Add quotes, photos, ID proof or call recordings.</div>');
      setCount('files', r.count); fsel(); dirty = true; toast('success', r.message);
    }).fail(function (x) { toast('danger', firstError(x)); });
  }
  $m.on('click', '.js-fone', function () { var $b = $(this); twoClick($b, function () { delFiles([$b.data('id')]); }); });
  $m.on('click', '.js-fdel', function () { var $b = $(this); twoClick($b, function () { delFiles($('#fileList .js-fchk:checked').map(function () { return this.value; }).get()); }); });

  /* ---------- reminders (the alarm itself lives in alerts.js) ---------- */
  var A = function () { return window.MPAlerts; };
  $m.on('click', '.rm-chip', function () {
    var $b = $(this), a = A(); if (!a) return;
    var v = $b.data('min') ? a.plus(a.nowLocal(), +$b.data('min')) : a.daysAt(+$b.data('days'), +$b.data('at') || 10);
    $('#remAt').val(v).trigger('input'); $m.find('.rm-chip').removeClass('is-on'); $b.addClass('is-on');
  });
  $m.on('input', '#remAt', function () { if (!$(this).data('chip')) $m.find('.rm-chip').removeClass('is-on'); });
  $m.on('shown.bs.tab', '[data-bs-target="#tabReminders"]', function () {
    var a = A(); if (!a) return; $('#remAt').attr('min', a.nowLocal()); var tz = a.tzMessage(); $('#remTz').text(tz).toggleClass('d-none', !tz);
  });
  $m.on('submit', '#remForm', function (e) {
    e.preventDefault(); var $fm = $(this), $b = $fm.find('[type=submit]'), $at = $('#remAt'); clear($fm);
    if (!$at.val()) { bad($at, 'Pick a date and time.'); $at.trigger('focus'); return; }
    busy($b, true);
    $.post($fm.attr('action'), $fm.serialize())
      .done(function (r) {
        $('#remList .rm-empty').remove(); $('#remList').html(r.html); setCount('reminders', r.count); dirty = true; toast('success', r.message);
        $fm[0].reset(); $m.find('.rm-chip').removeClass('is-on'); if (window.MPAlerts) MPAlerts.poll();
      })
      .fail(function (x) { fail(x, $fm); }).always(function () { busy($b, false); });
  });
  // done / remind me later / delete are handled in alerts.js; this refreshes the list afterwards
  $(document).on('reminders:changed', function () {
    var $l = $('#remList'); dirty = true; if (!$l.length) return;
    $.getJSON($l.data('url')).done(function (r) {
      $l.html(r.html || '<div class="empty-state rm-empty"><i class="bi bi-alarm"></i>No reminders yet. Set one so this lead never goes cold.</div>'); setCount('reminders', r.count);
    });
  });

  /* ---------- history tab: fetched fresh each time it opens ---------- */
  $m.on('shown.bs.tab', '[data-bs-target="#tabActivity"]', function () {
    var $a = $('#actList'); $a.css('opacity', .5);
    $.ajax({ url: $a.data('url'), dataType: 'html', headers: { 'Accept': 'text/html' } }).done(function (html) { $a.html(html); }).always(function () { $a.css('opacity', 1); });
  });

  /* ---------- open a lead from anywhere (bell, reminder popup, ?open=ID) ---------- */
  window.MPLeads = { open: function (id, tab) { openLead(base + '/' + id, tab); } };
  var qp = new URLSearchParams(location.search), oid = qp.get('open');
  if (oid && /^\d+$/.test(oid)) {
    openLead(base + '/' + oid, qp.get('tab')); qp.delete('open'); qp.delete('tab');
    history.replaceState(null, '', location.pathname + (qp.toString() ? '?' + qp.toString() : ''));
  }

  /* ---------- add a lead ---------- */
  var ac = new bootstrap.Modal('#leadCreateModal'), $cf = $('#leadCreateForm');
  $(document).on('click', '#leadPage .js-lead-add', function () { $cf[0].reset(); clear($cf); ac.show(); });
  $('#leadCreateModal').on('shown.bs.modal', function () { $('#c_name').trigger('focus'); });
  $cf.on('submit', function (e) {
    e.preventDefault(); var $b = $cf.find('[type=submit]'); if (!leadCheck($cf)) return; busy($b, true);
    $.post($cf.attr('action'), $cf.serialize()).done(function (r) { toast('success', r.message); ac.hide(); soft(); })
      .fail(function (x) { fail(x, $cf); }).always(function () { busy($b, false); });
  });

  /* ---------- import wizard ---------- */
  var $im = $('#leadImportModal'), iw = new bootstrap.Modal($im[0]), IM = {}, fields = $im.data('fields');
  function imErr(msg) { $('#imErr').text(msg || '').toggleClass('d-none', !msg); return false; }
  function imStep(n) {
    $im.find('.im-step').addClass('d-none').filter('[data-step=' + n + ']').removeClass('d-none');
    $im.find('.im-dot').each(function () { var s = +$(this).data('s'); $(this).toggleClass('is-on', s === n).toggleClass('is-done', s < n); });
    $('#imBack, #imGo').toggleClass('d-none', n !== 2); $('#imDone').toggleClass('d-none', n !== 3); $('#imCancel').toggleClass('d-none', n === 3);
  }
  function imReset() { IM = {}; imErr(''); $('#imProg').addClass('d-none'); $('#imDz').removeClass('d-none'); $('#imBar').css('width', '0%'); imStep(1); }
  $(document).on('click', '#leadPage .js-lead-import', function () { imReset(); iw.show(); });
  $('#imBack').on('click', imReset);
  function imUpload(f) {
    if (!f) return;
    if (!/\.(csv|txt|xlsx)$/i.test(f.name)) return imErr('Upload a CSV or Excel (.xlsx) file.');
    if (f.size > 5 * 1024 * 1024) return imErr('The file is larger than 5 MB.');
    imErr(''); var fd = new FormData(); fd.append('file', f);
    $('#imDz').addClass('d-none'); $('#imProg').removeClass('d-none').find('.im-pn').text(f.name); $('#imBar').css('width', '0%'); $('#imMsg').text('Uploading…');
    $.ajax({ url: $im.data('preview'), method: 'POST', data: fd, processData: false, contentType: false, dataType: 'json',
      xhr: function () {
        var x = $.ajaxSettings.xhr();
        if (x.upload) x.upload.addEventListener('progress', function (e) { if (e.lengthComputable) { var p = Math.round(e.loaded / e.total * 100); $('#imBar').css('width', p + '%'); $('#imMsg').text(p < 100 ? p + '%' : 'Reading file…'); } });
        return x;
      } })
      .done(function (r) { IM = r; buildMap(r); imStep(2); })
      .fail(function (x) { $('#imProg').addClass('d-none'); $('#imDz').removeClass('d-none'); imErr(firstError(x)); });
  }
  function buildMap(r) {
    var opts = '<option value="">Don’t import</option>' + $.map(r.headers, function (h, i) { return '<option value="' + i + '">' + esc(h || 'Column ' + (i + 1)) + '</option>'; }).join(''), html = '';
    $.each(fields, function (k, label) {
      html += '<div class="im-mrow"><label for="map_' + k + '">' + esc(label) + (k === 'name' || k === 'mobile' ? '' : ' <span class="caption fw-normal">(optional)</span>') + '</label><select id="map_' + k + '" name="map[' + k + ']" class="form-select form-select-sm">' + opts + '</select></div>';
    });
    $('#imMap').html(html); $.each(r.map, function (k, i) { $('#map_' + k).val(String(i)); });
    var th = $.map(r.headers, function (h) { return '<th>' + esc(h) + '</th>'; }).join('');
    var tb = $.map(r.sample, function (row) { return '<tr>' + $.map(row, function (c) { return '<td>' + esc(c) + '</td>'; }).join('') + '</tr>'; }).join('');
    $('#imPreview').html('<table class="table table-sm mp-table"><thead><tr>' + th + '</tr></thead><tbody>' + tb + '</tbody></table>');
    $('#imFileName').text(r.name + ' · ' + r.total + (r.total === 1 ? ' lead' : ' leads') + ' found'); $('#imGo .im-n').text(r.total);
  }
  $im.on('click', '#imDz', function () { $('#imFile').trigger('click'); });
  $im.on('keydown', '#imDz', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); $('#imFile').trigger('click'); } });
  $im.on('change', '#imFile', function () { imUpload(this.files[0]); this.value = ''; });
  $im.on('dragenter dragover', '#imDz', function (e) { e.preventDefault(); $(this).addClass('is-over'); });
  $im.on('dragleave drop', '#imDz', function (e) { e.preventDefault(); $(this).removeClass('is-over'); if (e.type === 'drop') imUpload(e.originalEvent.dataTransfer.files[0]); });
  $('#imForm').on('submit', function (e) { e.preventDefault(); });
  $('#imGo').on('click', function () {
    var $b = $(this); if (!$('#map_name').val() && !$('#map_mobile').val()) return imErr('Choose at least the Name or the Mobile column.');
    imErr(''); busy($b, true); $('#imBack').prop('disabled', true); $('#imRun').removeClass('d-none');
    $.post($im.data('run'), $('#imForm').serialize() + '&token=' + encodeURIComponent(IM.token))
      .done(function (r) {
        $('#imOk').text(r.created); $('#imDup').text(r.duplicates); $('#imBad').text(r.invalid);
        $('#imErrs').html($.map(r.errors, function (m) { return '<li>' + esc(m) + '</li>'; }).join('')); imStep(3); soft(); toast('success', r.message);
      })
      .fail(function (x) { imErr(firstError(x)); })
      .always(function () { busy($b, false); $('#imBack').prop('disabled', false); $('#imRun').addClass('d-none'); });
  });
});
