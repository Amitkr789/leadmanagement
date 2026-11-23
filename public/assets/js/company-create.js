/* Add Company wizard (jQuery): step validation, live summary, trial/paid toggle, password generator, AJAX save. */
$(function () {
  var $f = $('#companyForm'), step = 1, total = 3;
  var rx = { mobile: /^[6-9]\d{9}$/, email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/, gst: /^\d{2}[A-Z]{5}\d{4}[A-Z][1-9A-Z]Z[0-9A-Z]$/i };
  var fmt = function (d) { return d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' }); };
  var copy = function (t) { navigator.clipboard.writeText(t).then(function () { MP.toast('success', 'Copied to clipboard.'); }); };
  function gen() { var cs = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789@#$%', a = new Uint32Array(14), o = ''; crypto.getRandomValues(a); a.forEach(function (n) { o += cs[n % cs.length]; }); return o + '#7'; }

  function goTo(n) {
    step = n;
    $('.wiz-step').addClass('d-none').filter('[data-step=' + n + ']').removeClass('d-none');
    $('.stepper-item').each(function () { var s = $(this).data('step'); $(this).toggleClass('active', s === n).toggleClass('done', s < n); });
    $('.js-back').toggleClass('invisible', n === 1); $('.js-next').toggleClass('d-none', n === total); $('.js-submit').toggleClass('d-none', n !== total);
    $('html, body').animate({ scrollTop: 0 }, 150);
  }
  function bad($i, msg) { $i.addClass('is-invalid').closest('.mb-3').find('.invalid-feedback').first().text(msg); return false; }
  function validate(n) {
    var ok = true, $p = $('.wiz-step[data-step=' + n + ']'); $p.find('.is-invalid').removeClass('is-invalid');
    $p.find('input[required]:visible').each(function () {
      var $i = $(this), v = $.trim($i.val()), nm = $i.attr('name');
      if (!v) { ok = bad($i, 'This field is required.'); return; }
      if (nm === 'mobile' && !rx.mobile.test(v)) ok = bad($i, 'Enter a valid 10-digit mobile number.');
      if (/email/.test(nm) && !rx.email.test(v)) ok = bad($i, 'Enter a valid email address.');
      if (nm === 'admin_password' && v.length < 10) ok = bad($i, 'Use at least 10 characters.');
    });
    var $t = $p.find('#trial_days:visible'); if ($t.length && ($t.val() < 1 || $t.val() > 90)) ok = bad($t, 'Choose 1 to 90 days.');
    var $g = $p.find('[name=gst_number]'), g = $.trim($g.val()); if (g && !rx.gst.test(g)) ok = bad($g, 'Enter a valid 15-character GSTIN.');
    if (!ok) $p.find('.is-invalid').first().trigger('focus');
    return ok;
  }

  function refresh() {
    var plan = $('[name=plan]:checked'), trial = $('[name=access_type]:checked').val() === 'trial', days = +$('#trial_days').val() || 0;
    $('#sName').text($.trim($('#name').val()) || '—');
    $('#sCity').text([$.trim($('#city').val()), $.trim($('#state').val())].filter(Boolean).join(', ') || '—');
    $('#sPlan').text(plan.data('label')); $('#sAdmin').text($('#admin_email').val() || '—');
    if (trial) {
      var d = new Date(); d.setDate(d.getDate() + days);
      $('#sAccess').text('Free trial · ' + days + ' days'); $('#sBill').text('Ends ' + fmt(d)); $('#trialEnd').text(fmt(d));
    } else {
      var y = $('[name=billing_cycle]:checked').val() === 'yearly', price = +plan.data('price') * (y ? 10 : 1);
      $('#sAccess').text('Paid · ' + (y ? 'Yearly' : 'Monthly')); $('#sBill').text('₹' + price.toLocaleString('en-IN') + (y ? ' / year' : ' / month'));
    }
    $('.trial-opts').toggleClass('d-none', !trial); $('.paid-opts').toggleClass('d-none', trial);
    $('.js-trial-chip').each(function () { $(this).toggleClass('active', +$(this).data('days') === days); });
  }
  $f.on('input change', refresh);
  $f.on('input', '.is-invalid', function () { $(this).removeClass('is-invalid'); });
  $f.on('input', '#gst_number', function () { this.value = this.value.toUpperCase(); });
  $f.on('input', '#mobile', function () { this.value = this.value.replace(/\D/g, ''); });
  $f.on('click', '.js-trial-chip', function () { $('#trial_days').val($(this).data('days')); refresh(); });
  $f.on('click', '.js-regen', function () { $('#admin_password').val(gen()); });
  var $doc = $(document);
  $doc.on('click', '.js-copy', function () { copy($($(this).data('target')).val()); });

  $f.on('click', '.js-next', function () {
    if (!validate(step)) return;
    if (step === 2) { if (!$('#admin_name').val()) $('#admin_name').val($('#contact_name').val()); if (!$('#admin_email').val()) $('#admin_email').val($('#email').val()); refresh(); }
    goTo(step + 1);
  });
  $f.on('click', '.js-back', function () { goTo(step - 1); });
  $('.stepper').on('click', '.done', function () { goTo($(this).data('step')); });
  $('#admin_password').val(gen()); goTo(1); refresh();

  $f.on('submit', function (e) {
    e.preventDefault();
    if (step < total) { $f.find('.js-next').trigger('click'); return; }
    if (!validate(3)) return;
    var $b = $('.js-submit').prop('disabled', true); $b.find('.spinner-border').removeClass('d-none');
    $.post($f.attr('action'), $f.serialize())
      .done(function (r) {
        $('#okName').text(r.company.name); $('#okAccess').text(r.company.access); $('#okUrl').text(r.login_url);
        $('#okEmail').text(r.email); $('#okPass').text($('#admin_password').val());
        new bootstrap.Modal('#successModal').show(); MP.toast('success', 'Company created successfully.');
      })
      .fail(function (x) {
        $b.prop('disabled', false).find('.spinner-border').addClass('d-none');
        var r = x.responseJSON || {};
        if (x.status === 422 && r.errors) {
          var first = total;
          $.each(r.errors, function (name, msgs) {
            var $i = $f.find('[name="' + name + '"]').addClass('is-invalid');
            $i.closest('.mb-3').find('.invalid-feedback').first().text(msgs[0]);
            first = Math.min(first, $i.closest('.wiz-step').data('step') || total);
          });
          goTo(first); MP.toast('danger', 'Please fix the highlighted fields.');
        } else MP.toast('danger', x.status === 419 ? 'Session expired. Refresh the page.' : 'Unable to create company.');
      });
  });

  $doc.on('click', '.js-copy-all', function () { copy('Login: ' + $('#okUrl').text() + '\nEmail: ' + $('#okEmail').text() + '\nPassword: ' + $('#okPass').text()); });
});
