/* Create / edit plan modal (jQuery). Works on the Plans page and inside Add Company. */
$(function () {
  var $m = $('#planModal'); if (!$m.length) return;
  var $f = $('#planForm'), modal = new bootstrap.Modal($m[0]), editing = false, yearlyTouched = false;
  var inr = new Intl.NumberFormat('en-IN');
  var pickedIcon = 'bi-puzzle';

  var $monthly = $f.find('[name=monthly_price]'),
      $yearly  = $f.find('[name=yearly_price]'),
      $trial   = $f.find('[name=trial_days]'),
      $freeMo  = $('#pf_free_months');

  function num($i) { return +$i.val() || 0; }
  function clamp(v, lo, hi) { return Math.max(lo, Math.min(hi, v)); }
  function isFree() { return $f.find('[name=is_free]:checked').val() === '1'; }

  /* ---------- Pricing ---------- */
  function refreshPricing() {
    var m = num($monthly), y = num($yearly), $s = $f.find('.js-price-summary');
    $f.find('.js-yearly-auto').toggleClass('d-none', !yearlyTouched);
    if (!m) { $s.text('Enter a monthly price to see the yearly offer.'); return; }
    var saved = m * 12 - y, txt = '₹' + inr.format(m) + ' per month, or ₹' + inr.format(y) + ' per year';
    if (saved > 0) txt += '. Customer saves ₹' + inr.format(saved) + ' (' + (+(saved / m).toFixed(1)) + ' months free).';
    else txt += '.';
    $s.text(txt);
  }

  function recalcYearly(force) {
    if (force) yearlyTouched = false;
    var free = clamp(num($freeMo), 0, 11);
    $f.find('.js-paid-months').text(12 - free);
    if (!yearlyTouched) $yearly.val(num($monthly) * (12 - free) || '');
    refreshPricing();
  }

  /* ---------- Free / paid ---------- */
  function syncType() {
    var free = isFree();
    $f.find('.js-paid-only').toggleClass('d-none', free);
    $f.find('.js-free-only').toggleClass('d-none', !free);
    if (free) {
      $monthly.val(0); $yearly.val(0); $trial.val(0);
    } else {
      if (num($monthly) === 0) { $monthly.val(''); $yearly.val(''); yearlyTouched = false; }
      recalcYearly(false);
    }
    syncTrial();
  }

  /* ---------- Trial ---------- */
  function syncTrial() {
    var d = num($trial);
    $f.find('.pf-chip[data-days]').each(function () { $(this).toggleClass('active', +$(this).data('days') === d); });
    $f.find('.js-trial-hint').text(d > 0
      ? 'New companies get full access for ' + d + ' day' + (d > 1 ? 's' : '') + ', then need to pay to continue.'
      : 'No free trial. Billing starts as soon as the company joins.');
  }

  /* ---------- Modules ---------- */
  function syncModules() {
    $f.find('.js-mod-count').text($f.find('[name="features[]"]:checked').length + ' selected');
  }
  function closeCreator() {
    $f.find('.js-mod-new').addClass('d-none');
    $f.find('.js-mod-new-toggle').attr('aria-expanded', 'false');
    $('#pf_mod_label').val('').removeClass('is-invalid');
  }
  function addModule() {
    var $l = $('#pf_mod_label'), label = $.trim($l.val()), $btn = $f.find('.js-mod-add');
    if (!label) { $l.addClass('is-invalid'); $f.find('.js-mod-error').text('Enter a module name.'); return; }
    $btn.prop('disabled', true);
    $.post($m.data('moduleUrl'), { _token: $f.find('[name=_token]').val(), label: label, icon: pickedIcon })
      .done(function (r) {
        var mod = r.module, $chip = $('<label class="feat is-custom"></label>');
        $('<input type="checkbox" name="features[]">').val(mod.key).prop('checked', true).appendTo($chip);
        $('<span></span>').append($('<i class="bi"></i>').addClass(mod.icon)).append(document.createTextNode(mod.label)).appendTo($chip);
        $('<button type="button" class="pf-chip-del js-mod-del" title="Delete module"><i class="bi bi-x"></i></button>')
          .attr({ 'data-url': mod.destroy_url, 'data-label': mod.label, 'aria-label': 'Delete ' + mod.label }).appendTo($chip);
        $('#pfModules').append($chip);
        closeCreator(); syncModules(); MP.toast('success', r.message);
      })
      .fail(function (x) {
        var r = x.responseJSON || {};
        if (x.status === 422 && r.errors && r.errors.label) {
          $l.addClass('is-invalid'); $f.find('.js-mod-error').text(r.errors.label[0]);
        } else MP.toast('danger', 'Unable to create module.');
      })
      .always(function () { $btn.prop('disabled', false); });
  }

  /* ---------- Open (create / edit) ---------- */
  function open(p, updateUrl) {
    $f[0].reset(); $f.find('.is-invalid').removeClass('is-invalid'); $f.find('.js-keep').remove();
    editing = !!p; yearlyTouched = false;
    $f.data('url', editing ? updateUrl : $m.data('storeUrl'));
    $('#planModalTitle').text(editing ? 'Edit plan' : 'Create plan'); $f.find('.js-save-label').text(editing ? 'Save changes' : 'Create plan');
    closeCreator();
    if (p) {
      $.each(p, function (k, v) {
        if (k === 'features') return;
        var $i = $f.find('[name="' + k + '"]');
        if ($i.is(':checkbox')) $i.prop('checked', !!v);
        else if ($i.is(':radio')) $i.prop('checked', false).filter('[value="' + (+v) + '"]').prop('checked', true);
        else $i.val(v);
      });
      $f.find('[name="features[]"]').each(function () { $(this).prop('checked', (p.features || []).indexOf(this.value) > -1); });
      // A module switched off on the Modules page has no chip here; keep it on the plan instead of silently dropping it.
      $.each(p.features || [], function (i, key) {
        if (!$f.find('[name="features[]"]').filter(function () { return this.value === key; }).length)
          $('<input type="hidden" name="features[]" class="js-keep">').val(key).appendTo($f);
      });

      // Work backwards from stored prices to "free months"; a non-whole result means a custom yearly price.
      var mo = +p.monthly_price || 0, yr = +p.yearly_price || 0;
      if (mo > 0) {
        var fm = 12 - yr / mo;
        if (Math.abs(fm - Math.round(fm)) < 1e-9 && fm >= 0 && fm <= 11) $freeMo.val(Math.round(fm));
        else { yearlyTouched = true; $freeMo.val(clamp(Math.round(fm), 0, 11)); }
      }
    }
    syncType(); syncModules(); modal.show();
  }
  $(document).on('click', '.js-plan-open', function () { open($(this).data('plan') || null, $(this).data('updateUrl')); });
  $m.on('shown.bs.modal', function () { $f.find('[name=name]').trigger('focus'); });

  /* ---------- Events ---------- */
  $f.on('change', '[name=is_free]', syncType);
  $f.on('input', '[name=monthly_price]', function () { recalcYearly(false); });
  $f.on('input', '#pf_free_months', function () { recalcYearly(true); });
  $f.on('input', '[name=yearly_price]', function () { yearlyTouched = true; refreshPricing(); });
  $f.on('click', '.js-yearly-auto', function () { recalcYearly(true); });

  $f.on('input', '[name=trial_days]', syncTrial);
  $f.on('click', '.pf-chip[data-days]', function () { $trial.val($(this).data('days')); syncTrial(); });

  $f.on('input', '.is-invalid', function () { $(this).removeClass('is-invalid'); });
  $f.on('click', '.js-step', function () {
    var $i = $(this).closest('.input-group').find('input'), v = (+$i.val() || 0) + (+$(this).data('step'));
    var min = $i.attr('min'), max = $i.attr('max');
    if (min !== undefined) v = Math.max(+min, v);
    if (max !== undefined) v = Math.min(+max, v);
    $i.val(v).trigger('input');
  });

  // Modules
  $f.on('change', '[name="features[]"]', syncModules);
  $f.on('click', '.js-mod-all', function () { $f.find('[name="features[]"]').prop('checked', true); syncModules(); });
  $f.on('click', '.js-mod-none', function () { $f.find('[name="features[]"]').prop('checked', false); syncModules(); });
  $f.on('click', '.js-mod-new-toggle', function () {
    var $p = $f.find('.js-mod-new'), show = $p.hasClass('d-none');
    $p.toggleClass('d-none', !show); $(this).attr('aria-expanded', show);
    if (show) $('#pf_mod_label').trigger('focus');
  });
  $f.on('click', '.pf-icon-btn', function () {
    pickedIcon = $(this).data('icon');
    $f.find('.pf-icon-btn').removeClass('active'); $(this).addClass('active');
  });
  $f.on('click', '.js-mod-add', addModule);
  // Enter inside the module name must add the module, not submit the whole plan.
  $f.on('keydown', '#pf_mod_label', function (e) { if (e.key === 'Enter') { e.preventDefault(); addModule(); } });
  $f.on('click', '.js-mod-del', function (e) {
    e.preventDefault(); e.stopPropagation();
    var $b = $(this), label = $b.data('label');
    if (!confirm('Delete module "' + label + '"?')) return;
    $.ajax({ url: $b.data('url'), type: 'DELETE', data: { _token: $f.find('[name=_token]').val() } })
      .done(function (r) { $b.closest('.feat').remove(); syncModules(); MP.toast('success', r.message); })
      .fail(function (x) { MP.toast('danger', (x.responseJSON || {}).message || 'Unable to delete module.'); });
  });

  /* ---------- Submit ---------- */
  $f.on('submit', function (e) {
    e.preventDefault();
    var $b = $f.find('[type=submit]').prop('disabled', true); $b.find('.spinner-border').removeClass('d-none');
    $f.find('.is-invalid').removeClass('is-invalid');
    $.post($f.data('url'), $f.serialize() + (editing ? '&_method=PUT' : ''))
      .done(function (r) { modal.hide(); MP.toast('success', r.message); $(document).trigger('plan:saved', [r]); })
      .fail(function (x) {
        var r = x.responseJSON || {};
        if (x.status === 422 && r.errors) {
          $.each(r.errors, function (n, msgs) {
            var $i = $f.find('[name="' + n + '"]').first().addClass('is-invalid');
            $i.parent().find('.invalid-feedback').first().text(msgs[0]);
          });
          $f.find('.is-invalid').first().trigger('focus');
        } else MP.toast('danger', 'Unable to save plan.');
      })
      .always(function () { $b.prop('disabled', false).find('.spinner-border').addClass('d-none'); });
  });

  // Refresh whichever plan list the current page shows
  $(document).on('plan:saved', function (e, r) {
    if ($('#planGrid').length) $('#planGrid').html(r.grid);
    if ($('#planChoices').length) {
      $('#planChoices').html(r.choices);
      $('[name=plan][value="' + r.plan.slug + '"]').prop('checked', true); $('#companyForm').trigger('change');
    }
  });
});

/* ---------- Suspend / Unsuspend plan ---------- */
$(document).on('click', '.js-plan-status', function () {
    var $btn = $(this);
    var action = $btn.data('status');
    var name = $btn.data('name');

    var message = action === 'suspend'
        ? 'Are you sure you want to suspend "' + name + '"?'
        : 'Are you sure you want to activate "' + name + '" again?';

    if (!confirm(message)) return;

    $btn.prop('disabled', true);

    $.ajax({
        url: $btn.data('url'),
        type: 'PATCH',
        data: {
            action: action,
            _token: $('meta[name="csrf-token"]').attr('content')
        }
    })
    .done(function (r) {
        if ($('#planGrid').length) $('#planGrid').html(r.grid);
        if ($('#planChoices').length) $('#planChoices').html(r.choices);
        MP.toast('success', r.message);
    })
    .fail(function (x) {
        var r = x.responseJSON || {};
        MP.toast('danger', r.message || 'Unable to update plan status.');
    })
    .always(function () {
        $btn.prop('disabled', false);
    });
});

/* ---------- Delete plan ---------- */
$(document).on('click', '.js-plan-delete', function () {
    var $btn = $(this);
    var name = $btn.data('name');
    var used = parseInt($btn.data('used'), 10) || 0;

    if (used > 0) {
        MP.toast('warning', 'This plan is assigned to ' + used + ' company(s). Suspend it instead.');
        return;
    }

    if (!confirm('Permanently delete "' + name + '"?\n\nThis action cannot be undone.')) return;

    $btn.prop('disabled', true);

    $.ajax({
        url: $btn.data('url'),
        type: 'DELETE',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content')
        }
    })
    .done(function (r) {
        if ($('#planGrid').length) $('#planGrid').html(r.grid);
        if ($('#planChoices').length) $('#planChoices').html(r.choices);
        MP.toast('success', r.message);
    })
    .fail(function (x) {
        var r = x.responseJSON || {};
        MP.toast('danger', r.message || 'Unable to delete plan.');
    })
    .always(function () {
        $btn.prop('disabled', false);
    });
});