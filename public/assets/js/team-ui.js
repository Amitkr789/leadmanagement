/* MovePro CRM: shared helpers for the Branches and Users pages (jQuery).
   live()    filters + pagination over AJAX, no page refresh
   detail()  details modal loaded from the server (view / edit / password / delete)
   forms()   AJAX form submit with inline validation
   soft()    refresh KPI cards + table in place after a change */
window.TeamUI = (function ($) {
  var rx = { phone: /^[6-9]\d{9}$/, email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/ };
  var skeleton = '<div class="modal-header"><div class="skeleton" style="height:28px;width:45%"></div></div><div class="modal-body"><div class="skeleton mb-3" style="height:70px"></div><div class="skeleton mb-2" style="height:18px;width:80%"></div><div class="skeleton" style="height:18px;width:60%"></div></div>';

  function fb($i) { return $i.parents().filter(function () { return $(this).children('.invalid-feedback').length; }).first().children('.invalid-feedback').first(); }
  function bad($i, msg) { $i.addClass('is-invalid'); fb($i).text(msg).addClass('d-block'); return false; }
  function clear($f) { $f.find('.is-invalid').removeClass('is-invalid'); $f.find('.invalid-feedback').removeClass('d-block').text(''); $f.find('.cm-error').addClass('d-none'); }
  function busy($b, on) { $b.prop('disabled', on).find('.spinner-border').toggleClass('d-none', !on); }
  function gen() {
    var U = 'ABCDEFGHJKLMNPQRSTUVWXYZ', L = 'abcdefghjkmnpqrstuvwxyz', D = '23456789', A = U + L + D + '!@#$%', a = new Uint32Array(12), s, i;
    crypto.getRandomValues(a);
    s = U[a[0] % U.length] + L[a[1] % L.length] + D[a[2] % D.length];
    for (i = 3; i < 12; i++) s += A[a[i] % A.length];
    return s;
  }
  function cap(s) { return s.charAt(0).toUpperCase() + s.slice(1); }

  function init() {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content'), 'Accept': 'application/json' } });
    $(document).on('input', '[name=phone], [name=mobile]', function () { this.value = this.value.replace(/\D/g, ''); });
    $(document).on('click', '.js-eye', function () { var $i = $(this).closest('.input-group').find('input').first(), s = $i.attr('type') === 'password'; $i.attr('type', s ? 'text' : 'password'); $(this).find('i').toggleClass('bi-eye bi-eye-slash'); });
    $(document).on('click', '.js-gen', function () {
      $($(this).data('target')).val(gen()).attr('type', 'text').trigger('input');
      $(this).closest('.input-group').find('.js-eye i').removeClass('bi-eye').addClass('bi-eye-slash');
    });
    $(document).on('click', '.js-copy', function () {
      var v = $($(this).data('target')).val(); if (!v) return;
      (navigator.clipboard ? navigator.clipboard.writeText(v) : $.Deferred().reject()).then(
        function () { MP.toast('info', 'Password copied.'); },
        function () { MP.toast('danger', 'Could not copy. Select the password and copy it manually.'); });
    });
  }

  // Inline validation before anything is sent
  function check($f) {
    var ok = true; clear($f);
    $f.find('[required]:not(:disabled)').each(function () { if (!$.trim(this.value)) ok = bad($(this), this.tagName === 'SELECT' ? 'Please choose an option.' : 'This field is required.'); });
    $f.find('[name=phone]:not(:disabled), [name=mobile]:not(:disabled)').each(function () { if (this.value && !rx.phone.test(this.value)) ok = bad($(this), 'Enter a valid 10-digit mobile number.'); });
    $f.find('[type=email]:not(:disabled)').each(function () { if (this.value && !rx.email.test(this.value)) ok = bad($(this), 'Enter a valid email address.'); });
    $f.find('[name=password]:not(:disabled), [name=login_password]:not(:disabled)').each(function () { if (this.value && this.value.length < 10) ok = bad($(this), 'Use at least 10 characters.'); });
    if (!ok) $f.find('.is-invalid').first().trigger('focus');
    return ok;
  }

  // Server errors: field errors inline, everything else in the form's alert (or a toast)
  function fail(x, $f) {
    var r = x.responseJSON || {}, rest = [];
    if (x.status === 422 && r.errors) {
      $.each(r.errors, function (n, m) {
        var $i = $f.find('[name="' + n + '"]').not(':radio, :disabled').first();
        if ($i.length) bad($i, m[0]); else rest.push(m[0]);
      });
      if (rest.length) { if ($f.find('.cm-error').length) $f.find('.cm-error').text(rest.join(' ')).removeClass('d-none'); else MP.toast('danger', rest[0]); }
      $f.find('.is-invalid').first().trigger('focus');
    } else MP.toast('danger', r.message || 'Something went wrong. Please try again.');
  }

  function forms($root, done) {
    $root.on('input change', '.is-invalid', function () { $(this).removeClass('is-invalid'); fb($(this)).removeClass('d-block'); });
    $root.on('submit', 'form[data-cm]', function (e) {
      e.preventDefault();
      var $f = $(this), $b = $f.find('[type=submit]');
      if (!check($f)) return;
      busy($b, true);
      $.post($f.attr('action'), $f.serialize())
        .done(function (r) { MP.toast('success', r.message); done($f.data('cm'), r, $f); })
        .fail(function (x) { fail(x, $f); })
        .always(function () { busy($b, false); });
    });
  }

  // Filters + pagination without a page refresh
  function live(form, target) {
    var $f = $(form), $t = $(target), timer, xhr, seq = 0;
    function sync() { $('.js-clear[data-form="' + form + '"]').toggleClass('d-none', !$f.serializeArray().some(function (v) { return v.value; })); }
    function run(url) {
      var my = ++seq; if (xhr) xhr.abort();
      $t.addClass('is-loading');
      xhr = $.ajax({ url: url || $f.attr('action'), data: url ? null : $f.serialize(), headers: { 'X-Partial': '1' } })
        .done(function (html) {
          $t.html(html);
          var qs = url ? (url.split('?')[1] || '') : $f.serialize();
          history.replaceState(null, '', location.pathname + (qs ? '?' + qs : ''));
          sync();
        })
        .fail(function (x) { if (x.statusText !== 'abort') MP.toast('danger', 'Could not load results. Please try again.'); })
        .always(function () { if (my === seq) $t.removeClass('is-loading'); });
    }
    $f.on('submit', function (e) { e.preventDefault(); run(); });
    $f.on('change', 'select', function () { run(); });
    $f.on('input', 'input[type=search]', function () { clearTimeout(timer); timer = setTimeout(run, 350); });
    $t.on('click', '.pagination a', function (e) { e.preventDefault(); run($(this).attr('href')); $t[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' }); });
    $(document).on('click', '.js-clear[data-form="' + form + '"]', function (e) { e.preventDefault(); $f.find('input[type=search]').val(''); $f.find('select').val(''); run(); });
    sync();
  }

  // Re-fetch the page and swap only the named regions (KPI cards, table)
  function soft(sels) {
    $.get(location.href).done(function (html) {
      var $d = $('<div>').append($.parseHTML(html));
      $.each(sels, function (_, s) { var $n = $d.find(s); if ($n.length) $(s).html($n.html()); });
    });
  }

  // Details modal: content comes from the server (see the *_detail partials)
  function detail(o) {
    var $m = $(o.modal), $c = $(o.content), modal = new bootstrap.Modal($m[0]), url, dirty = false;

    function showTab(sel) { var t = $c.find('.nav-link[data-bs-target="' + sel + '"]'); if (t.length) bootstrap.Tab.getOrCreateInstance(t[0]).show(); }
    function load(u, tab) {
      url = u; var keep = tab || $c.find('.cm-tabs .nav-link.active').attr('data-bs-target');
      $c.html(skeleton);
      $.get(u).done(function (html) { $c.html(html); if (keep) showTab(keep); })
        .fail(function () { $c.html('<div class="modal-body"><div class="empty-state"><i class="bi bi-exclamation-circle"></i>Unable to load the details.<div class="mt-2"><button class="btn btn-light border btn-sm" data-bs-dismiss="modal">Close</button></div></div></div>'); });
    }
    function open(u, tab) { dirty = false; modal.show(); load(u, tab ? '#tab' + cap(tab) : null); }

    $(document).on('click', o.open, function (e) { e.preventDefault(); e.stopPropagation(); open($(this).data('url'), $(this).data('tab')); });
    $(document).on('click', o.row, function (e) { if (!$(e.target).closest('a, button').length) open($(this).data('url')); });
    $(document).on('keydown', o.row, function (e) { if (e.key === 'Enter' && e.target === this) open($(this).data('url')); });
    $m.on('hidden.bs.modal', function () { $c.empty(); if (dirty) o.onChange(); });
    $m.on('click', '.js-goto-tab', function () { showTab($(this).data('tab')); });
    $m.on('input', '#cmConfirm', function () { $('#cmDeleteBtn').prop('disabled', $.trim(this.value) !== $(this).data('name')); });

    // One-tap actions: first click arms the button, second click confirms
    $m.on('click', '.js-act', function () {
      var $b = $(this);
      if (!$b.data('armed')) {
        $b.data('armed', true).data('label', $b.html()).text('Click again to confirm');
        setTimeout(function () { if ($b.data('armed')) $b.data('armed', false).html($b.data('label')); }, 3000); return;
      }
      $b.prop('disabled', true).data('armed', false);
      $.post($b.data('url'), $b.data('payload'))
        .done(function (r) { dirty = true; MP.toast('success', r.message); if ($b.data('close')) modal.hide(); else load(url); })
        .fail(function (x) {
          var r = x.responseJSON || {};
          MP.toast('danger', r.errors ? r.errors[Object.keys(r.errors)[0]][0] : (r.message || 'Something went wrong.'));
          $b.prop('disabled', false).html($b.data('label'));
        });
    });

    forms($m, function (kind, r, $f) {
      if (kind === 'password') { $f[0].reset(); $f.find('input[type=text]').attr('type', 'password'); return; }
      dirty = true;
      if (kind === 'delete') modal.hide(); else load(url);
    });
    return { open: open };
  }

  return { init: init, live: live, soft: soft, detail: detail, forms: forms, clear: clear };
})(jQuery);
