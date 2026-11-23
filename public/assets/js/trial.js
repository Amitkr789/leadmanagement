/* Trial / buy-now wizard (jQuery): access type -> plan -> company -> login. Live summary, validation, AJAX, Razorpay. */
$(function () {
  var $f = $('#trialForm'), step = 1, total = 3, days = +$f.data('trialDays') || 14, gst = +$f.data('gst') || 18;
  var rx = { mobile: /^[6-9]\d{9}$/, email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/, gst: /^\d{2}[A-Z]{5}\d{4}[A-Z][1-9A-Z]Z[0-9A-Z]$/i };
  $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content'), 'Accept': 'application/json' } });
  var fmt = function (d) { return d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' }); };
  var inr = function (n) { return '₹' + Math.round(n).toLocaleString('en-IN'); };
  var isPaid = function () { return $('[name=access_type]:checked').val() === 'paid'; };
  var yearly = function () { return $('[name=billing_cycle]:checked').val() === 'yearly'; };

  function goTo(n) {
    step = n;
    $('.wiz-step').addClass('d-none').filter('[data-step=' + n + ']').removeClass('d-none');
    $('.stepper-item').each(function () { var s = $(this).data('step'); $(this).toggleClass('active', s === n).toggleClass('done', s < n); });
    $('.js-back').toggleClass('invisible', n === 1); $('.js-next').toggleClass('d-none', n === total); $('.js-submit').toggleClass('d-none', n !== total);
    $('html, body').animate({ scrollTop: $('.lp-trial').offset().top - 70 }, 150);
  }
  function bad($i, msg) { $i.addClass('is-invalid').closest('.mb-3, .form-check').find('.invalid-feedback').first().text(msg); return false; }
  function validate(n) {
    var ok = true, $p = $('.wiz-step[data-step=' + n + ']'); $p.find('.is-invalid').removeClass('is-invalid');
    $p.find('input[required]:visible').each(function () {
      var $i = $(this), v = $.trim($i.val()), nm = $i.attr('name');
      if ($i.is(':checkbox')) { if (!$i.is(':checked')) ok = bad($i, 'Please accept the terms to continue.'); return; }
      if (!v) { ok = bad($i, 'This field is required.'); return; }
      if (nm === 'mobile' && !rx.mobile.test(v)) ok = bad($i, 'Enter a valid 10-digit mobile number.');
      if (nm === 'email' && !rx.email.test(v)) ok = bad($i, 'Enter a valid email address.');
      if (nm === 'password' && (v.length < 10 || !/[A-Za-z]/.test(v) || !/\d/.test(v))) ok = bad($i, 'Use 10+ characters with letters and numbers.');
    });
    var $g = $p.find('[name=gst_number]'), g = $.trim($g.val()); if (g && !rx.gst.test(g)) ok = bad($g, 'Enter a valid 15-character GSTIN.');
    if (!ok) $p.find('.is-invalid').first().trigger('focus');
    return ok;
  }

  function refresh() {
    var p = $('[name=plan]:checked'), paid = isPaid(), end = new Date(), sub = +p.data(yearly() ? 'yearly' : 'price') || 0, total = sub + Math.round(sub * gst / 100);
    if (paid) { if (yearly()) end.setFullYear(end.getFullYear() + 1); else end.setMonth(end.getMonth() + 1); } else end.setDate(end.getDate() + days);
    $('.paid-opts').toggleClass('d-none', !paid); $('.trial-note').toggleClass('d-none', paid);
    $('#sName').text($.trim($('#name').val()) || '—');
    $('#sCity').text([$.trim($('#city').val()), $.trim($('#state').val())].filter(Boolean).join(', ') || '—');
    $('#sPlan').text(p.data('label') ? p.data('label') + (paid ? ' · ' + (yearly() ? 'yearly' : 'monthly') : ' · free for ' + days + ' days') : '—');
    $('#sLimits').text(p.length ? p.data('users') + ' users · ' + p.data('branches') + ' branch' + (p.data('branches') > 1 ? 'es' : '') : '—');
    $('#sPay').text(paid ? inr(total) + ' incl. GST' : '₹0'); $('#sEndsLabel').text(paid ? 'Renews on' : 'Trial ends on'); $('#sEnds').text(fmt(end));
    $('#sEmail').text($.trim($('#email').val()) || '—');
    $('.js-submit-label').text(paid ? 'Pay ' + inr(total) + ' & activate' : 'Start my free trial');
  }
  function strength(v) {
    var s = (v.length >= 10) + (/[a-z]/.test(v) && /[A-Z]/.test(v)) + /\d/.test(v) + /[^A-Za-z0-9]/.test(v), L = ['Too weak', 'Weak', 'Okay', 'Good', 'Strong'];
    $('#pwBar').css('width', (v ? Math.max(s, 1) * 25 : 0) + '%').attr('class', 'progress-bar bg-' + (s <= 1 ? 'danger' : s === 2 ? 'warning' : 'success'));
    $('#pwHint').text(v ? L[s] + (s < 3 ? ' · add length, numbers or symbols' : '') : 'Use 10+ characters with letters and numbers.');
  }

  $f.on('input change', refresh);
  $f.on('input', '.is-invalid', function () { $(this).removeClass('is-invalid'); });
  $f.on('input', '#password', function () { strength(this.value); });
  $f.on('input', '#gst_number', function () { this.value = this.value.toUpperCase(); });
  $f.on('input', '#mobile', function () { this.value = this.value.replace(/\D/g, ''); });
  $f.on('click', '.js-eye', function () { var $i = $('#password'), s = $i.attr('type') === 'password'; $i.attr('type', s ? 'text' : 'password'); $(this).find('i').toggleClass('bi-eye bi-eye-slash'); });
  $f.on('click', '.js-next', function () { if (validate(step)) goTo(step + 1); });
  $f.on('click', '.js-back', function () { goTo(step - 1); });
  $('.stepper').on('click', '.done', function () { goTo($(this).data('step')); });
  goTo(1); refresh();

  function busy(on) { var $b = $('.js-submit').prop('disabled', on); $b.find('.spinner-border').toggleClass('d-none', !on); }
  function alertMsg(t) { $('#formAlert').text(t).removeClass('d-none'); }
  function success(r) {
    var rows = ''; $.each(r.company, function (k) { rows += '<dt class="col-5 caption fw-medium">' + k + '</dt><dd class="col-7 fw-semibold"></dd>'; });
    $('#successDetails').html(rows).find('dd').each(function (i) { $(this).text(Object.values(r.company)[i]); });
    $('#successTitle').text(isPaid() ? 'Payment received. Your plan is active!' : 'Your free trial is live!');
    $('#goDashboard').attr('href', r.redirect); $('#trialWizard').addClass('d-none'); $('#successPanel').removeClass('d-none'); $('html, body').scrollTop(0);
  }

  $f.on('submit', function (e) {
    e.preventDefault();
    if (step < total) { $f.find('.js-next').trigger('click'); return; }
    if (!validate(3)) return;
    busy(true); $('#formAlert').addClass('d-none');
    $.post($f.attr('action'), $f.serialize())
      .done(function (r) {
        if (!r.pay) { success(r); return; }
        MPPay.open(r.pay, function (resp) {
          $.post(r.verify_url, resp).done(success).fail(function (x) { busy(false); var er = (x.responseJSON || {}).errors; alertMsg(er && er.access_type ? er.access_type[0] : 'We could not verify the payment. If money was deducted it will be adjusted automatically.'); });
        }, function (msg) { busy(false); alertMsg(msg || 'Payment was not completed. You have not been charged. You can try again.'); });
      })
      .fail(function (x) {
        busy(false); var r = x.responseJSON || {};
        if (x.status === 422 && r.errors) {
          var first = total;
          $.each(r.errors, function (name, msgs) {
            if (name === 'access_type') { alertMsg(msgs[0]); first = Math.min(first, 1); return; }
            var $i = $f.find('[name="' + name + '"]').first().addClass('is-invalid');
            $i.closest('.mb-3, .form-check').find('.invalid-feedback').first().text(msgs[0]);
            first = Math.min(first, $i.closest('.wiz-step').data('step') || total);
          });
          goTo(first); if (window.turnstile) turnstile.reset();
        } else alertMsg(x.status === 429 ? 'Too many attempts. Please wait a few minutes.' : 'Something went wrong. Please try again.');
      });
  });
});
