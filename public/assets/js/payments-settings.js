/* Console > Payment Gateway (jQuery): save, save & test, copy webhook URL */
$(function () {
  var $f = $('#pgForm'), $st = $('#pgStatus');
  function status(ok, msg) { $st.html('<span class="status tone-' + (ok ? 'success' : 'danger') + '">' + (ok ? 'Connected' : 'Failed') + '</span> <span></span>').find('span:last').text(msg); }
  function save() { $f.find('.is-invalid').removeClass('is-invalid'); return $.post($f.attr('action'), $f.serialize()); }
  function invalid(x) {
    var r = x.responseJSON || {};
    if (x.status === 422 && r.errors) { $.each(r.errors, function (n, m) { $f.find('[name="' + n + '"]').first().addClass('is-invalid').closest('.mb-3').find('.invalid-feedback').first().text(m[0]); }); }
    else MP.toast('danger', 'Unable to save. Please try again.');
  }
  $f.on('input', '.is-invalid', function () { $(this).removeClass('is-invalid'); });
  $f.on('click', '.js-eye', function () { var $i = $(this).closest('.input-group').find('input'), s = $i.attr('type') === 'password'; $i.attr('type', s ? 'text' : 'password'); $(this).find('i').toggleClass('bi-eye bi-eye-slash'); });
  $f.on('submit', function (e) {
    e.preventDefault(); var $b = $f.find('[type=submit]').prop('disabled', true); $b.find('.spinner-border').removeClass('d-none');
    save().done(function (r) { MP.toast('success', r.message); setTimeout(function () { location.reload(); }, 700); }).fail(invalid)
      .always(function () { $b.prop('disabled', false).find('.spinner-border').addClass('d-none'); });
  });
  $f.on('click', '.js-test', function () {
    var $b = $(this).prop('disabled', true); $st.text('Testing...');
    save().done(function () {
      $.post($f.data('testUrl'), { _token: $f.find('[name=_token]').val() }).done(function (r) { status(true, r.message); }).fail(function (x) { status(false, (x.responseJSON && x.responseJSON.message) || 'Test failed.'); });
    }).fail(function (x) { $st.text(''); invalid(x); }).always(function () { $b.prop('disabled', false); });
  });
  $(document).on('click', '.js-copy-url', function () { navigator.clipboard.writeText($(this).data('url')).then(function () { MP.toast('success', 'Webhook URL copied.'); }); });
});
