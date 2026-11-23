/* Company > My Profile (jQuery): account / company / password forms, e-mail verification with resend timer. */
$(function () {
  $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content'), 'Accept': 'application/json' } });
  var rx = { mobile: /^[6-9]\d{9}$/, gst: /^\d{2}[A-Z]{5}\d{4}[A-Z][1-9A-Z]Z[0-9A-Z]$/i };

  function bad($i, msg) { $i.addClass('is-invalid').closest('.mb-3').find('.invalid-feedback').first().text(msg); return false; }
  function check($f) {
    var ok = true; $f.find('.is-invalid').removeClass('is-invalid');
    $f.find('input[required]').each(function () { if (!$.trim(this.value)) ok = bad($(this), 'This field is required.'); });
    var $m = $f.find('[name=mobile]'); if ($m.length && $m.val() && !rx.mobile.test($m.val())) ok = bad($m, 'Enter a valid 10-digit mobile number.');
    var $g = $f.find('[name=gst_number]'); if ($g.length && $g.val() && !rx.gst.test($g.val())) ok = bad($g, 'Enter a valid 15-character GSTIN.');
    if (!ok) $f.find('.is-invalid').first().trigger('focus'); return ok;
  }

  // Save buttons stay disabled until something changes
  $('form[data-pf=account], form[data-pf=company]').each(function () { var $f = $(this); $f.find('.pf-save').prop('disabled', true); $f.on('input change', function () { $f.find('.pf-save').prop('disabled', false); }); });
  $(document).on('input', '.is-invalid', function () { $(this).removeClass('is-invalid'); });
  $(document).on('input', '[name=gst_number]', function () { this.value = this.value.toUpperCase(); });
  $(document).on('input', '[name=mobile]', function () { this.value = this.value.replace(/\D/g, ''); });
  $(document).on('click', '.js-eye', function () { var $i = $(this).closest('.input-group').find('input').first(), s = $i.attr('type') === 'password'; $i.attr('type', s ? 'text' : 'password'); $(this).find('i').toggleClass('bi-eye bi-eye-slash'); });

  // Password rules + strength
  function pwRules() {
    var v = $('#new_password').val() || '', c = $('#confirm_password').val() || '';
    var r = { len: v.length >= 10, letter: /[A-Za-z]/.test(v), number: /\d/.test(v), match: v !== '' && v === c };
    $.each(r, function (k, ok) { $('.pw-reqs [data-rule=' + k + ']').toggleClass('ok', ok); });
    var s = r.len + r.letter + r.number + (/[A-Z]/.test(v) && /[a-z]/.test(v)) + /[^A-Za-z0-9]/.test(v), col = s <= 2 ? '#DC2626' : s === 3 ? '#F59E0B' : '#16A34A';
    $('.pw-bar > span').css({ width: (v ? Math.max(s, 1) * 20 : 0) + '%', background: col });
  }
  $(document).on('input', '#new_password, #confirm_password', pwRules);

  // All three forms save over AJAX
  $(document).on('submit', 'form[data-pf]', function (e) {
    e.preventDefault();
    var $f = $(this), kind = $f.data('pf'), $b = $f.find('[type=submit]');
    if (kind !== 'password' && !check($f)) return;
    $f.find('.is-invalid').removeClass('is-invalid'); $b.prop('disabled', true).find('.spinner-border').removeClass('d-none');
    $.post($f.attr('action'), $f.serialize())
      .done(function (r) {
        MP.toast('success', r.message);
        if (kind === 'password') { $f[0].reset(); pwRules(); $b.prop('disabled', false); }
        if (kind === 'account') $('.js-user-name').text(r.name);
        if (kind === 'company') $('.js-company-name').text(r.name);
      })
      .fail(function (x) {
        $b.prop('disabled', false); var r = x.responseJSON || {};
        if (x.status === 422 && r.errors) { $.each(r.errors, function (n, m) { bad($f.find('[name="' + n + '"]').first(), m[0]); }); $f.find('.is-invalid').first().trigger('focus'); }
        else MP.toast('danger', r.message || 'Something went wrong. Please try again.');
      })
      .always(function () { $b.find('.spinner-border').addClass('d-none'); });
  });

  // E-mail verification: send / resend with a countdown
  var timer;
  function countdown(s) {
    var $r = $('.js-resend').prop('disabled', true); clearInterval(timer);
    timer = setInterval(function () {
      s--; if (s <= 0) { clearInterval(timer); $r.prop('disabled', false).text('Resend email'); return; }
      $r.text('Resend in ' + Math.floor(s / 60) + ':' + ('0' + s % 60).slice(-2));
    }, 1000);
  }
  function showSent(r) {
    $('#vcIdle').addClass('d-none'); $('#vcSent').removeClass('d-none');
    if (r.dev_link) $('#devLink').attr('href', r.dev_link).removeClass('d-none');
    countdown(r.retry_after || 60);
  }
  $(document).on('click', '.js-send-verify', function () {
    var $b = $(this).prop('disabled', true);
    $.post($b.data('url'))
      .done(function (r) { if (r.retry_after) showSent(r); else MP.toast('info', r.message); })
      .fail(function (x) {
        var r = x.responseJSON || {};
        if (x.status === 429) showSent({ retry_after: r.retry_after || 60 });
        else { $b.prop('disabled', false); MP.toast('danger', r.errors && r.errors.email ? r.errors.email[0] : (r.message || 'Unable to send the email.')); }
      })
      .always(function () { if (!$('#vcSent').hasClass('d-none')) return; $b.prop('disabled', false); });
  });
});