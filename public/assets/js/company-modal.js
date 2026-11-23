/* Company details modal (jQuery): view, edit, upgrade / change plan, delete. Content comes from companies.show. */
$(function () {
  var $m = $('#companyModal'); if (!$m.length) return;
  var modal = new bootstrap.Modal($m[0]), $c = $('#companyModalContent'), url, dirty = false;
  var fmt = function (d) { return d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' }); };
  var skeleton = '<div class="modal-header"><div class="skeleton" style="height:28px;width:45%"></div></div><div class="modal-body"><div class="skeleton mb-3" style="height:70px"></div><div class="skeleton mb-2" style="height:18px;width:80%"></div><div class="skeleton" style="height:18px;width:60%"></div></div>';

  function showTab(sel) { var t = $c.find('.nav-link[data-bs-target="' + sel + '"]'); if (t.length) bootstrap.Tab.getOrCreateInstance(t[0]).show(); }
  function load(u, tab) {
    url = u; $c.html(skeleton);
    $.get(u).done(function (html) { $c.html(html); if (tab) showTab(tab); refreshUp(); })
      .fail(function () { $c.html('<div class="modal-body"><div class="empty-state"><i class="bi bi-exclamation-circle"></i>Unable to load this company.<div class="mt-2"><button class="btn btn-light border btn-sm" data-bs-dismiss="modal">Close</button></div></div></div>'); });
  }
  function open(u, tab) { dirty = false; modal.show(); load(u, tab ? '#cm' + tab.charAt(0).toUpperCase() + tab.slice(1) : null); }

  // Open from a table row (click / Enter) or from an explicit button/link
  $(document).on('click', '.js-company-open', function (e) { e.preventDefault(); e.stopPropagation(); open($(this).data('url'), $(this).data('tab')); });
  $(document).on('click', '.js-company-row', function (e) { if (!$(e.target).closest('a,button').length) open($(this).data('url')); });
  $(document).on('keydown', '.js-company-row', function (e) { if (e.key === 'Enter' && e.target === this) open($(this).data('url')); });
  $m.on('hidden.bs.modal', function () { $c.empty(); if (dirty) location.reload(); }); // refresh table + KPIs after changes
  $m.on('click', '.js-goto-tab', function () { showTab($(this).data('tab')); });

  // Field helpers
  $m.on('input', '.is-invalid', function () { $(this).removeClass('is-invalid'); });
  $m.on('input', '.cm-gst', function () { this.value = this.value.toUpperCase(); });
  $m.on('input', '.cm-mobile', function () { this.value = this.value.replace(/\D/g, ''); });
  $m.on('input', '#cmConfirm', function () { $('#cmDeleteBtn').prop('disabled', $.trim(this.value) !== $(this).data('name')); });

  // Upgrade summary: price + what happens to the billing date
  function refreshUp() {
    var $f = $('#cmUpgradeForm'); if (!$f.length) return;
    var p = $f.find('[name=plan]:checked'), y = $f.find('[name=billing_cycle]:checked').val() === 'yearly', cycle = y ? 'yearly' : 'monthly';
    var active = +$f.data('active') === 1, same = p.val() === $f.data('current') && cycle === $f.data('cycle') && active, restart = !active || cycle !== $f.data('cycle');
    var d = new Date(); if (y) d.setFullYear(d.getFullYear() + 1); else d.setMonth(d.getMonth() + 1);
    $('#cmUpAmount').text('₹' + (+p.data(y ? 'yearly' : 'price')).toLocaleString('en-IN') + (y ? ' / year' : ' / month'));
    $('#cmUpNote').text(same ? 'This is already the current plan and billing cycle.' : restart ? 'Billing starts today. Next renewal on ' + fmt(d) + '.' : 'Plan changes immediately. Renewal date stays ' + $f.data('end') + '.');
    $f.find('[type=submit]').prop('disabled', same);
  }
  $m.on('change', '#cmUpgradeForm', refreshUp);

  // One-tap actions (extend trial, suspend, reactivate): first click arms the button, second click confirms
  $m.on('click', '.js-act', function () {
    var $b = $(this);
    if (!$b.data('armed')) {
      $b.data('armed', true).data('label', $b.html()).text('Click again to confirm');
      setTimeout(function () { if ($b.data('armed')) $b.data('armed', false).html($b.data('label')); }, 3000); return;
    }
    $b.prop('disabled', true).data('armed', false);
    $.post($b.data('url'), $b.data('payload'))
      .done(function (r) { dirty = true; MP.toast('success', r.message); load(url); })
      .fail(function (x) {
        var r = x.responseJSON || {}, msg = r.errors ? r.errors[Object.keys(r.errors)[0]][0] : (r.message || 'Something went wrong.');
        MP.toast('danger', msg); $b.prop('disabled', false).html($b.data('label'));
      });
  });

  // Edit / upgrade / delete all submit via AJAX
  $m.on('submit', 'form[data-cm]', function (e) {
    e.preventDefault();
    var $f = $(this), $b = $f.find('[type=submit]').prop('disabled', true);
    $b.find('.spinner-border').removeClass('d-none'); $f.find('.is-invalid').removeClass('is-invalid'); $f.find('.cm-error').addClass('d-none');
    $.post($f.attr('action'), $f.serialize())
      .done(function (r) { dirty = true; MP.toast('success', r.message); if ($f.data('cm') === 'delete') modal.hide(); else load(url); })
      .fail(function (x) {
        $b.prop('disabled', false).find('.spinner-border').addClass('d-none');
        var r = x.responseJSON || {};
        if (x.status === 422 && r.errors) {
          var rest = [];
          $.each(r.errors, function (n, msgs) {
            var $i = $f.find('input[name="' + n + '"]').not(':radio').first();
            if ($i.length) $i.addClass('is-invalid').closest('.mb-3').find('.invalid-feedback').first().text(msgs[0]); else rest.push(msgs[0]);
          });
          if (rest.length) $f.find('.cm-error').text(rest.join(' ')).removeClass('d-none');
          if ($f.data('cm') === 'upgrade') refreshUp();
        } else MP.toast('danger', 'Something went wrong. Please try again.');
      });
  });
});
