/* Create / edit plan modal (jQuery). Works on the Plans page and inside Add Company. */
$(function () {
  var $m = $('#planModal'); if (!$m.length) return;
  var $f = $('#planForm'), modal = new bootstrap.Modal($m[0]), editing = false, yearlyTouched = false;

  function open(p, updateUrl) {
    $f[0].reset(); $f.find('.is-invalid').removeClass('is-invalid');
    editing = !!p; yearlyTouched = editing;
    $f.data('url', editing ? updateUrl : $m.data('storeUrl'));
    $('#planModalTitle').text(editing ? 'Edit plan' : 'Create plan'); $f.find('.js-save-label').text(editing ? 'Save changes' : 'Create plan');
    if (p) {
      $.each(p, function (k, v) {
        if (k === 'features') return;
        var $i = $f.find('[name="' + k + '"]'); if ($i.is(':checkbox')) $i.prop('checked', !!v); else $i.val(v);
      });
      $f.find('[name="features[]"]').each(function () { $(this).prop('checked', (p.features || []).indexOf(this.value) > -1); });
    }
    modal.show();
  }
  $(document).on('click', '.js-plan-open', function () { open($(this).data('plan') || null, $(this).data('updateUrl')); });
  $m.on('shown.bs.modal', function () { $f.find('[name=name]').trigger('focus'); });

  // Yearly = 10 x monthly until the owner edits it by hand
  $f.on('input', '[name=monthly_price]', function () { if (!yearlyTouched) $f.find('[name=yearly_price]').val((+this.value || 0) * 10); });
  $f.on('input', '[name=yearly_price]', function () { yearlyTouched = true; });
  $f.on('input', '.is-invalid', function () { $(this).removeClass('is-invalid'); });
  $f.on('click', '.js-step', function () {
    var $i = $(this).closest('.input-group').find('input'), v = (+$i.val() || 0) + (+$(this).data('step'));
    $i.val(Math.max(+$i.attr('min') || 0, v));
  });

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
            $i.closest('.mb-3').find('.invalid-feedback').first().text(msgs[0]);
          });
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
