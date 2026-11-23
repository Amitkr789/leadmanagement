/* Company > WhatsApp settings (jQuery): save, test, subscribe, rotate, copy. */
$(function () {
  $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content'), 'Accept': 'application/json' } });
  var $f = $('#waForm'), $st = $('#waStatus');
  var firstErr = function (x) { var r = x.responseJSON || {}, e = r.errors; return e ? e[Object.keys(e)[0]][0] : (r.message || 'Something went wrong.'); };
  var say = function (ok, msg) { $st.html('<span class="status tone-' + (ok ? 'success' : 'danger') + '">' + (ok ? 'OK' : 'Failed') + '</span> <span></span>').find('span:last').text(msg); };
  var reload = function () { setTimeout(function () { location.reload(); }, 700); };

  $f.on('input', '.is-invalid', function () { $(this).removeClass('is-invalid'); });
  $(document).on('click', '.js-eye', function () { var $i = $(this).closest('.input-group').find('input').first(), s = $i.attr('type') === 'password'; $i.attr('type', s ? 'text' : 'password'); $(this).find('i').toggleClass('bi-eye bi-eye-slash'); });
  $(document).on('click', '.js-copy', function () { navigator.clipboard.writeText($($(this).data('target')).val()).then(function () { MP.toast('success', 'Copied.'); }); });

  $f.on('submit', function (e) {
    e.preventDefault(); var $b = $f.find('[type=submit]').prop('disabled', true); $b.find('.spinner-border').removeClass('d-none'); $f.find('.is-invalid').removeClass('is-invalid');
    $.post($f.attr('action'), $f.serialize()).done(function (r) { MP.toast('success', r.message); reload(); })
      .fail(function (x) {
        var r = x.responseJSON || {};
        if (x.status === 422 && r.errors) $.each(r.errors, function (n, m) { $f.find('[name="' + n + '"]').first().addClass('is-invalid').closest('.mb-3').find('.invalid-feedback').first().text(m[0]); }); else MP.toast('danger', firstErr(x));
      }).always(function () { $b.prop('disabled', false).find('.spinner-border').addClass('d-none'); });
  });
  function act($b, url, done) { $b.prop('disabled', true); $st.text('Working...'); $.post(url).done(function (r) { say(true, r.message); if (done) done(); }).fail(function (x) { say(false, firstErr(x)); }).always(function () { $b.prop('disabled', false); }); }
  $f.on('click', '.js-wa-test', function () { act($(this), $f.data('test'), reload); });
  $f.on('click', '.js-wa-sub', function () { act($(this), $f.data('sub')); });
  $(document).on('click', '.js-wa-rotate', function () {
    var $b = $(this); if (!$b.data('armed')) { $b.data('armed', true).data('label', $b.html()).text('Click again to confirm'); setTimeout(function () { $b.data('armed', false).html($b.data('label')); }, 3000); return; }
    act($b, $f.data('rotate'), reload);
  });
});
