/* MovePro CRM: surveys (jQuery). One file for the list, the form, the details page, the links page and the public form.
   Each part only runs when its page is open. */
$(function () {
  var csrf = $('meta[name=csrf-token]').attr('content');
  $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } });
  var esc = function (s) { return $('<div>').text(s == null ? '' : s).html(); };
  var toast = function (t, m) { if (window.MP && MP.toast) MP.toast(t, m); else window.alert(m); };
  var firstError = function (x) {
    var r = x.responseJSON || {}, e = r.errors;
    if (x.status === 413) return 'The file is larger than the server allows.';
    return e ? e[Object.keys(e)[0]][0] : (r.message || 'Something went wrong. Please try again.');
  };
  var bytes = function (n) { return n >= 1048576 ? (n / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(n / 1024)) + ' KB'; };
  function twoClick($b, fn) {   // first click arms, second click does it
    if (!$b.data('armed')) {
      $b.data('armed', true).data('label', $b.html()).addClass('is-armed').text('Sure?');
      setTimeout(function () { if ($b.data('armed')) $b.data('armed', false).removeClass('is-armed').html($b.data('label')); }, 3000); return;
    }
    $b.data('armed', false).removeClass('is-armed').html($b.data('label')); fn();
  }

  /* ---------- confirmation before delete / stop / complete ---------- */
  function ask(text, ok) {
    var $m = $('#svConfirm');
    if (!$m.length) $m = $('<div class="modal fade" id="svConfirm" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered modal-sm"><div class="modal-content"><div class="modal-body"><h2 class="fs-6 fw-bold mb-1">Are you sure?</h2><div class="caption" id="svConfirmText"></div></div><div class="modal-footer border-0 pt-0"><button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button><button type="button" class="btn btn-danger" id="svConfirmOk">Yes, continue</button></div></div></div></div>').appendTo('body');
    $('#svConfirmText').text(text);
    var modal = bootstrap.Modal.getOrCreateInstance($m[0]);
    $('#svConfirmOk').off('click').on('click', function () { modal.hide(); ok(); });
    modal.show();
  }
  $(document).on('submit', 'form[data-confirm]', function (e) {
    var f = this; if (f.__ok) return; e.preventDefault();
    ask($(f).data('confirm'), function () { f.__ok = true; f.submit(); });
  });

  /* ---------- survey list: filters, sorting, pages without a refresh ---------- */
  var $f = $('#svFilter'), $l = $('#svList');
  if ($f.length) {
    var timer, xhr, seq = 0, exp = ($('#svExport').attr('href') || '').split('?')[0];
    var any = function () { return $f.serializeArray().some(function (v) { return v.value; }); };
    var sync = function () { $f.find('.js-sv-clear').toggleClass('d-none', !any()); };
    var run = function (url) {
      var my = ++seq; if (xhr) xhr.abort(); $l.addClass('is-loading');
      xhr = $.ajax({ url: url || $f.attr('action'), data: url ? null : $f.serialize(), dataType: 'html', headers: { 'X-Partial': '1', 'Accept': 'text/html' } })
        .done(function (html) {
          $l.html(html); var qs = url ? (url.split('?')[1] || '') : $f.serialize();
          history.replaceState(null, '', location.pathname + (qs ? '?' + qs : '')); if (exp) $('#svExport').attr('href', exp + (qs ? '?' + qs : ''));
        })
        .fail(function (x) { if (x.statusText !== 'abort') toast('danger', 'Could not load surveys. Please try again.'); })
        .always(function () { if (my === seq) $l.removeClass('is-loading'); });
    };
    $f.on('change', 'select, input[type=date]', function () { sync(); run(); });
    $f.on('input', 'input[type=search]', function () { sync(); clearTimeout(timer); timer = setTimeout(run, 350); });
    $f.on('submit', function (e) { e.preventDefault(); run(); });
    $l.on('click', '.pagination a, .sv-sort', function (e) { e.preventDefault(); run($(this).attr('href')); });
    $(document).on('click', '.js-sv-clear', function () { $f.find('input[type=search], input[type=date]').val(''); $f.find('select').val(''); sync(); run(); });
    sync();
  }

  /* ---------- survey form ---------- */
  var $form = $('#svForm');
  if ($form.length) {
    // customer type + lead picker
    var lt;
    $('#f_customer_type').on('change', function () { $('#svLeadBox').toggleClass('d-none', this.value !== 'lead'); });
    function leadSearch() {
      var url = $form.data('lead-url'); if (!url) return;
      $.getJSON(url, { q: $.trim($('#svLeadQ').val()) }).done(function (rows) {
        $('#svLeadRes').html(rows.length ? $.map(rows, function (r) {
          return '<button type="button" class="sv-pick" data-lead=\'' + esc(JSON.stringify(r)).replace(/'/g, '&#39;') + '\'><span class="fw-semibold d-block">' + esc(r.name) + '</span><span class="caption">' + esc((r.mobile ? '+91 ' + r.mobile : 'No mobile') + (r.city ? ' · ' + r.city : '')) + '</span></button>';
        }).join('') : '<div class="p-3 caption">No lead found.</div>').removeClass('d-none');
      });
    }
    $('#svLeadQ').on('input focus', function () { if (!$.trim(this.value)) $('#svLeadId').val(''); clearTimeout(lt); lt = setTimeout(leadSearch, 220); });
    $(document).on('click', '.sv-pick', function () {
      var r = JSON.parse($(this).attr('data-lead'));
      var set = function (n, v, onlyIfEmpty) { var $i = $form.find('[name="' + n + '"]'); if (v && (!onlyIfEmpty || !$i.val())) $i.val(v); };
      $('#svLeadId').val(r.id); $('#svLeadQ').val(r.name); $('#svLeadRes').addClass('d-none');
      set('customer_name', r.name); set('mobile', r.mobile); set('email', r.email); set('pickup[city]', r.city, true); set('move_requirements', r.message, true);
    });
    $(document).on('click', function (e) { if (!$(e.target).closest('#svLeadRes, #svLeadQ').length) $('#svLeadRes').addClass('d-none'); });

    // surveyors of the chosen branch only
    function surveyors() {
      var $b = $('#f_branch_id'), $a = $('#f_assigned_to'); if (!$b.length || !$a.length) return;
      var sel = String($b.val() || $a.data('head') || '');
      $a.find('option[data-branch]').each(function () { var br = String($(this).data('branch') || ''), ok = !br || br === sel; $(this).prop('disabled', !ok).prop('hidden', !ok); });
      if ($a.find('option:selected').prop('disabled')) $a.val('');
    }
    $('#f_branch_id').on('change', surveyors); surveyors();

    // services
    $form.on('change', '.js-sv-svc', function () { var on = this.checked; $(this).closest('.sv-svc').toggleClass('is-on', on).find('input[type=text], input:not([type])').toggleClass('d-none', !on); });

    // inventory
    var $rows = $('#svRows'), tpl = $('#svRowTpl').html(), idx = +$rows.data('next') || 0;
    function recalc() {
      var rows = 0, qty = 0, w = 0, wr = 0, v = 0, vr = 0;
      $rows.find('.sv-item').each(function () {
        var $r = $(this), g = function (k) { return $r.find('[name$="[' + k + ']"]').val(); };
        if (!$.trim(g('name'))) return; rows++;
        var q = parseInt(g('qty'), 10); q = q > 0 ? q : 0; qty += q;
        var wt = parseFloat(g('weight')); if (g('weight') !== '' && !isNaN(wt) && wt >= 0) { w += wt * q; wr++; }
        var L = parseFloat(g('length')), W = parseFloat(g('width')), H = parseFloat(g('height')); if (L > 0 && W > 0 && H > 0) { v += L * W * H * q; vr++; }
      });
      $('#svTQty').text(qty); $('#svTRows').text(rows);
      $('#svTW').toggleClass('d-none', !wr).text(wr ? 'Weight ' + (Math.round(w * 10) / 10) + ' kg (from ' + wr + ' of ' + rows + ' items)' : '');
      $('#svTV').toggleClass('d-none', !vr).text(vr ? 'Volume ' + (Math.round(v * 10) / 10) + ' cu ft (from ' + vr + ' of ' + rows + ' items)' : '');
    }
    function addRow(name) {
      var $r = $(tpl.replace(/__i__/g, idx++));
      if (name) $r.find('[name$="[name]"]').val(name);
      $rows.append($r); recalc(); return $r;
    }
    $('#svAddRow').on('click', function () { addRow().find('[name$="[name]"]').trigger('focus'); });
    $form.on('click', '.js-sv-sug', function () { var $r = addRow($(this).data('name')); $r.find('[name$="[qty]"]').trigger('focus'); });
    $rows.on('click', '.sv-del', function () { $(this).closest('.sv-item').remove(); recalc(); });
    $rows.on('input change', 'input, select', recalc);
    $rows.on('keydown', 'input', function (e) { if (e.key === 'Enter') e.preventDefault(); });
    recalc();

    // stop double submits
    $form.on('submit', function () { var $b = $('#svSubmit'); setTimeout(function () { $b.prop('disabled', true).find('.spinner-border').removeClass('d-none'); }, 0); });
  }

  /* ---------- survey page: files with progress ---------- */
  var $show = $('#svShow');
  if ($show.length) {
    if (location.hash === '#files') { var t = $('[data-bs-target="#t-files"]'); if (t.length) bootstrap.Tab.getOrCreateInstance(t[0]).show(); }
    var ACCEPT = /\.(jpe?g|png|webp|gif|pdf|docx?|xlsx?|csv|txt)$/i, MAXB = 8 * 1024 * 1024, uid = 0, queue = [], running = 0, jobs = {};
    var url = $show.data('files');
    function item(id, f, err) {
      return '<div class="up-item' + (err ? ' is-error' : '') + '" id="' + id + '"><i class="bi bi-file-earmark up-ico"></i><div class="up-main"><div class="up-top"><span class="text-truncate">' + esc(f.name) + '</span><span class="caption">' + bytes(f.size) + '</span></div>' +
        '<div class="progress" style="height:5px"><div class="progress-bar" style="width:0%"></div></div><div class="up-msg caption">' + (err ? esc(err) : 'Waiting…') + '</div></div><button type="button" class="btn-ico js-sv-upx" aria-label="Cancel or dismiss"><i class="bi bi-x-lg"></i></button></div>';
    }
    function add(list) {
      $.each(list, function (_, f) {
        var id = 'up' + (++uid), err = !ACCEPT.test(f.name) ? 'This file type is not allowed.' : f.size > MAXB ? 'Larger than 8 MB.' : !f.size ? 'The file is empty.' : '';
        $('#svQueue').append(item(id, f, err)); if (!err) { jobs[id] = { id: id, f: f, cat: $('#svCat').val() }; queue.push(id); }
      }); pump();
    }
    function pump() { while (running < 2 && queue.length) send(jobs[queue.shift()]); }
    function send(j) {
      if (!j) return; var $i = $('#' + j.id), fd = new FormData(); fd.append('file', j.f); fd.append('category', j.cat); running++;
      $i.find('.up-msg').text('Uploading…'); $i.find('.progress-bar').css('width', '0%');
      j.xhr = $.ajax({ url: url, method: 'POST', data: fd, processData: false, contentType: false, dataType: 'json',
        xhr: function () { var x = $.ajaxSettings.xhr(); if (x.upload) x.upload.addEventListener('progress', function (e) { if (e.lengthComputable) { var p = Math.round(e.loaded / e.total * 100); $i.find('.progress-bar').css('width', p + '%'); $i.find('.up-msg').text(p < 100 ? 'Uploading… ' + p + '%' : 'Saving…'); } }); return x; } })
        .done(function (r) {
          $i.addClass('is-done').find('.progress-bar').css('width', '100%'); $i.find('.up-msg').text('Uploaded');
          $('#svFileList .sv-fempty').remove(); $('#svFileList').prepend(r.html); $('#svFCount').text(r.count); delete jobs[j.id];
          setTimeout(function () { $i.slideUp(150, function () { $(this).remove(); }); }, 1200);
        })
        .fail(function (x, st) {
          if (st === 'abort') { $i.remove(); delete jobs[j.id]; return; }
          $i.addClass('is-error').find('.up-msg').html(esc(firstError(x)) + ' <a href="#" class="js-sv-retry">Try again</a>');
        })
        .always(function () { running--; pump(); });
    }
    $show.on('click', '#svDz', function () { $('#svFileInput').trigger('click'); });
    $show.on('keydown', '#svDz', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); $('#svFileInput').trigger('click'); } });
    $show.on('change', '#svFileInput', function () { add(this.files); this.value = ''; });
    $show.on('dragenter dragover', '#svDz', function (e) { e.preventDefault(); $(this).addClass('is-over'); });
    $show.on('dragleave drop', '#svDz', function (e) { e.preventDefault(); $(this).removeClass('is-over'); if (e.type === 'drop') add(e.originalEvent.dataTransfer.files); });
    $show.on('click', '.js-sv-upx', function () { var $i = $(this).closest('.up-item'), id = $i.attr('id'), j = jobs[id]; if (j && j.xhr && j.xhr.readyState !== 4) { j.xhr.abort(); return; } queue = $.grep(queue, function (q) { return q !== id; }); delete jobs[id]; $i.remove(); });
    $show.on('click', '.js-sv-retry', function (e) { e.preventDefault(); var $i = $(this).closest('.up-item'); $i.removeClass('is-error').find('.up-msg').text('Waiting…'); queue.push($i.attr('id')); pump(); });

    var fsel = function () {
      var n = $('#svFileList .js-sv-fchk:checked').length, all = $('#svFileList .js-sv-fchk').length;
      $('.js-sv-fdel').toggleClass('d-none', !n).find('b').text(n); $('#svFAll').prop('checked', !!n && n === all).prop('indeterminate', n > 0 && n < all);
    };
    $show.on('change', '.js-sv-fchk', fsel); $show.on('change', '#svFAll', function () { $('#svFileList .js-sv-fchk').prop('checked', this.checked); fsel(); });
    function del(ids) {
      $.post($show.data('del'), { _method: 'DELETE', ids: ids }).done(function (r) {
        $.each(r.deleted, function (_, id) { $('#svFileList .sv-file[data-id=' + id + ']').remove(); });
        if (!$('#svFileList .sv-file').length) $('#svFileList').html('<div class="empty-state sv-fempty"><i class="bi bi-images"></i>No photos or documents yet.</div>');
        $('#svFCount').text(r.count); fsel(); toast('success', r.message);
      }).fail(function (x) { toast('danger', firstError(x)); });
    }
    $show.on('click', '.js-sv-fone', function () { var $b = $(this); twoClick($b, function () { del([$b.data('id')]); }); });
    $show.on('click', '.js-sv-fdel', function () { var $b = $(this); twoClick($b, function () { del($('#svFileList .js-sv-fchk:checked').map(function () { return this.value; }).get()); }); });
  }

  /* ---------- public links page ---------- */
  $(document).on('click', '.js-lk-copy', function () {
    var v = $($(this).data('target')).val();
    (navigator.clipboard ? navigator.clipboard.writeText(v) : $.Deferred().reject()).then(function () { toast('success', 'Link copied.'); }, function () { $($('.js-lk-copy').data('target')).trigger('select'); toast('info', 'Press Ctrl+C to copy the link.'); });
  });
  $('#svLinks input[name=duration]').on('change', function () { $('#lkCustom').toggleClass('d-none', this.value !== 'custom'); });
});
