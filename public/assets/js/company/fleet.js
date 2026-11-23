/* Company > Drivers / Vehicles / Assignments (jQuery): live filters, confirm dialog, form helpers, Cashfree "fetch", assign / release. */
$(function () {
  $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content'), 'Accept': 'application/json' } });
  var esc = function (s) { return $('<div>').text(s == null ? '' : s).html(); };

  /* ---------- live filters (no page reload) ---------- */
  var $live = $('form.js-live'), $target = $($live.data('target')), timer, xhr;
  function active() { return $live.find(':input').filter(function () { return this.name && ((this.type === 'checkbox') ? this.checked : $.trim(this.value)); }).length > 0; }
  function load(url) {
    if (xhr) xhr.abort(); $target.addClass('is-loading');
    xhr = $.ajax({ url: url, dataType: 'html', headers: { 'X-Partial': '1', 'Accept': 'text/html' } })
      .done(function (h) { $target.html(h); history.replaceState(null, '', url); }).always(function () { $target.removeClass('is-loading'); });
  }
  function apply() {
    var qs = $live.find(':input').filter(function () { return this.name && ((this.type === 'checkbox') ? this.checked : this.value); }).serialize();
    $live.find('.js-clear').toggleClass('d-none', !active()); load($live.attr('action') + (qs ? '?' + qs : ''));
  }
  $live.on('change', 'select, input[type=checkbox]', apply);
  $live.on('input', 'input[type=search]', function () { clearTimeout(timer); timer = setTimeout(apply, 400); });
  $live.on('submit', function (e) { e.preventDefault(); apply(); });
  $(document).on('click', '.js-clear', function () { $live.find('input[type=search]').val(''); $live.find('select').val(''); $live.find('input[type=checkbox]').prop('checked', false); apply(); });
  $target.on('click', '.pagination a', function (e) { e.preventDefault(); load(this.href); });

  /* ---------- rows, print, confirm ---------- */
  $(document).on('click', '.js-row', function (e) { if (!$(e.target).closest('a,button,form,input,.dropdown').length) location.href = $(this).data('url'); });
  $(document).on('keydown', '.js-row', function (e) { if (e.key === 'Enter' && e.target === this) location.href = $(this).data('url'); });
  $(document).on('click', '.js-print', function () { window.print(); });
  var $cm = $('#flConfirm'), pending;
  if ($cm.length) {
    var cmodal = new bootstrap.Modal($cm[0]);
    $(document).on('submit', 'form.js-confirm-form', function (e) {
      if ($(this).data('ok')) return; e.preventDefault(); pending = $(this);
      $('#flConfirmTitle').text(pending.data('title') || 'Are you sure?'); $('#flConfirmText').text(pending.data('text') || ''); cmodal.show();
    });
    $cm.on('click', '.js-confirm-yes', function () { if (pending) { pending.data('ok', true); cmodal.hide(); pending[0].submit(); } });
  }

  /* ---------- form helpers ---------- */
  $(document).on('input', '[data-upper]', function () { var p = this.selectionStart; this.value = this.value.toUpperCase().replace(/[\s\-]/g, ''); this.setSelectionRange(p, p); });
  $(document).on('input', '[data-digits]', function () { this.value = this.value.replace(/\D/g, ''); });
  // expiry date can never be before the issue date
  $(document).on('change', '[data-issue]', function () { $(this).closest('.row').find('[data-expiry]').attr('min', this.value || ''); });
  $('[data-issue]').trigger('change');
  // files: size + type check and a preview before saving
  $(document).on('change', '.js-file', function () {
    var f = this.files[0], $p = $(this).siblings('.file-preview').empty(); $(this).removeClass('is-invalid');
    if (!f) return;
    if (f.size > +$(this).data('max')) { this.value = ''; $p.html('<div class="text-danger small">That file is larger than 4 MB. Please choose a smaller one.</div>'); return; }
    if (/^image\//.test(f.type)) { var r = new FileReader(); r.onload = function (e) { $p.html('<img src="' + e.target.result + '" alt="Preview">'); }; r.readAsDataURL(f); }
    else $p.html('<div class="small"><i class="bi bi-file-earmark-pdf me-1"></i>' + esc(f.name) + '</div>');
  });
  // highlight the section being read
  var $links = $('.fl-nav a'), $secs = $('.fl-section');
  if ($links.length && 'IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (en) { en.forEach(function (x) { if (x.isIntersecting) { $links.removeClass('is-on').filter('[href="#' + x.target.id + '"]').addClass('is-on'); } }); }, { rootMargin: '-20% 0px -70% 0px' });
    $secs.each(function () { io.observe(this); });
  }

  /* ---------- Cashfree: fetch details, review, then use ---------- */
  $(document).on('click', '.js-verify', function () {
    var $b = $(this), type = $b.data('type'), $box = $b.closest('.cf-box'), $panel = $box.find('.cf-panel').removeClass('d-none');
    var data = type === 'dl' ? { dl_number: $('[name=dl_number]').val(), dob: $('[name=dob]').val() } : { reg_number: $('[name=reg_number]').val() };
    var miss = type === 'dl' ? (!data.dl_number || !data.dob ? 'Enter the licence number and the date of birth first.' : '') : (!data.reg_number ? 'Enter the registration number first.' : '');
    if (miss) { $panel.html('<div class="text-danger small">' + miss + '</div>'); return; }
    var label = $b.html(); $b.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Fetching...'); $panel.html('<div class="small text-muted">Checking with Cashfree...</div>');
    $.post($b.data('url'), data)
      .done(function (r) {
        var rows = ''; $.each(r.fields, function (k, v) {
          var lbl = $('label[for="f_' + k + '"]').first().clone().children().remove().end().text().replace('*', '').trim() || k.replace(/_/g, ' ');
          rows += '<tr><td class="text-muted">' + esc(lbl) + '</td><td class="fw-semibold">' + esc(v) + '</td></tr>';
        });
        $panel.data({ fields: r.fields, vid: r.verification_id })
          .html('<div class="small text-success mb-2"><i class="bi bi-check-circle-fill me-1"></i>' + esc(r.message) + '</div><table>' + rows + '</table><div class="mt-2"><button type="button" class="btn btn-primary btn-sm js-use"><i class="bi bi-magic me-1"></i>Use these details</button></div>');
      })
      .fail(function (x) { var r = x.responseJSON || {}; $panel.html('<div class="small text-danger"><i class="bi bi-exclamation-circle me-1"></i>' + esc(r.message || (r.errors ? r.errors[Object.keys(r.errors)[0]][0] : 'Could not verify. Please enter the details manually.')) + '</div>'); })
      .always(function () { $b.prop('disabled', false).html(label); });
  });
  $(document).on('click', '.js-use', function () {
    var $panel = $(this).closest('.cf-panel'), fields = $panel.data('fields') || {}, n = 0;
    $.each(fields, function (k, v) {
      var $i = $('[name="' + k + '"]').first(); if (!$i.length) return;
      if ($i.is('select')) { var hit = $i.find('option').filter(function () { return this.value.toLowerCase() === String(v).toLowerCase(); }).first(); if (!hit.length) return; $i.val(hit.val()); }
      else $i.val(v);
      $i.addClass('fl-filled').trigger('change'); n++;
    });
    $(this).closest('.cf-box').find('input[type=hidden]').val($panel.data('vid'));
    $panel.html('<div class="small text-success"><i class="bi bi-check-circle-fill me-1"></i>' + n + ' fields filled. Please check them, then save. They are marked in green.</div>');
  });

  /* ---------- assignments ---------- */
  var $am = $('#assignModal');
  if ($am.length) {
    var amodal = new bootstrap.Modal($am[0]);
    $(document).on('click', '.js-assign-open', function () { amodal.show(); });
    if (+$am.data('reopen') === 1) amodal.show();
  }
  var $rm = $('#releaseModal');
  if ($rm.length) {
    var rmodal = new bootstrap.Modal($rm[0]);
    $(document).on('click', '.js-release', function () { $('#releaseForm').attr('action', $(this).data('url')); $('#rlLabel').text($(this).data('label')); $('#rl_date').attr('min', $(this).data('from')); rmodal.show(); });
  }
});
