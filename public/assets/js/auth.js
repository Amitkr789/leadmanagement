/* MovePro auth pages (jQuery): password toggle, caps-lock hint, AJAX submit with inline errors, lockout timer, OTP boxes. */
$(function () {
  var $doc = $(document), lockTimer;
  $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content'), 'Accept': 'application/json' } });
  $('form[data-ajax] [type=submit]').each(function () { $(this).data('label', $(this).find('.btn-label').text()); });

  $doc.on('click', '.js-toggle-pass', function () {
    var $i = $(this).closest('.input-group').find('input').first(), show = $i.attr('type') === 'password';
    $i.attr('type', show ? 'text' : 'password');
    $(this).attr('aria-label', show ? 'Hide password' : 'Show password').find('i').toggleClass('bi-eye bi-eye-slash');
  });
  $doc.on('keydown keyup', 'input[type=password], input[type=text]', function (e) {
    var ev = e.originalEvent; if (ev && ev.getModifierState) $('.js-caps').toggleClass('d-none', !ev.getModifierState('CapsLock'));
  });

  function busy($f, on) { $f.data('busy', on).find('[type=submit]').prop('disabled', on).find('.spinner-border').toggleClass('d-none', !on); }
  function lockout($f, secs) {
    var $b = $f.find('[type=submit]'); clearInterval(lockTimer);
    lockTimer = setInterval(function () {
      secs--;
      if (secs <= 0) { clearInterval(lockTimer); $b.prop('disabled', false).find('.btn-label').text($b.data('label')); return; }
      $b.prop('disabled', true).find('.btn-label').text('Try again in ' + Math.floor(secs / 60) + ':' + ('0' + secs % 60).slice(-2));
    }, 1000);
  }

  $doc.on('submit', 'form[data-ajax]', function (e) {
    e.preventDefault();
    var $f = $(this); if ($f.data('busy')) return;
    $f.find('.is-invalid').removeClass('is-invalid'); $f.find('.invalid-feedback').removeClass('d-block').text(''); $('.js-form-alert').addClass('d-none');
    busy($f, true);
    $.post($f.attr('action'), $f.serialize())
      .done(function (res) { $f.find('.btn-label').text('Redirecting...'); window.location = res.redirect; })
      .fail(function (xhr) {
        var r = xhr.responseJSON || {};
        if (r.redirect) { window.location = r.redirect; return; }
        busy($f, false);
        if (xhr.status === 422 && r.errors) {
          $.each(r.errors, function (name, msgs) {
            var $i = $f.find('[name="' + name + '"]').addClass('is-invalid');
            $i.closest('.mb-3').find('.invalid-feedback').first().text(msgs[0]).addClass('d-block');
          });
        } else {
          $('.js-form-alert').text(r.message || 'Something went wrong. Please try again.').removeClass('d-none');
          if (xhr.status === 429 && r.retry_after) lockout($f, r.retry_after);
        }
        var $card = $('.auth-card').removeClass('shake'); void $card[0].offsetWidth; $card.addClass('shake');
        if (window.turnstile) turnstile.reset();
        $f.trigger('auth:failed');
      });
  });

  // OTP: 6 boxes -> hidden #code, auto-advance, paste, auto-submit
  var $boxes = $('.otp-box');
  if ($boxes.length) {
    var sync = function () {
      var v = $boxes.map(function () { return this.value; }).get().join(''); $('#code').val(v);
      if (v.length === 6) $boxes.closest('form').trigger('submit');
    };
    $boxes.first().trigger('focus');
    $doc.on('input', '.otp-box', function () {
      this.value = this.value.replace(/\D/g, '').slice(-1);
      if (this.value) $boxes.eq($boxes.index(this) + 1).trigger('focus');
      sync();
    });
    $doc.on('keydown', '.otp-box', function (e) {
      if (e.key === 'Backspace' && !this.value) $boxes.eq(Math.max($boxes.index(this) - 1, 0)).trigger('focus').val('');
    });
    $doc.on('paste', '.otp-box', function (e) {
      var t = (e.originalEvent.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0, 6); if (!t) return;
      e.preventDefault(); $boxes.each(function (i) { this.value = t[i] || ''; }); $boxes.eq(Math.min(t.length, 5)).trigger('focus'); sync();
    });
    $doc.on('auth:failed', 'form', function () { $boxes.val('').first().trigger('focus'); $('#code').val(''); });
  }

  // Step-2 expiry countdown
  var $cd = $('#countdown');
  if ($cd.length) {
    var s = 300, t = setInterval(function () {
      s--; $cd.text(Math.floor(s / 60) + ':' + ('0' + s % 60).slice(-2));
      if (s <= 0) { clearInterval(t); window.location = $cd.data('expire-url'); }
    }, 1000);
  }
});
