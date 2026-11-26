/* Modules page (jQuery): create, edit, switch on/off, delete, search and filter. */
$(function () {
  var $m = $('#moduleModal'); if (!$m.length) return;
  var $f = $('#moduleForm'), modal = new bootstrap.Modal($m[0]), editing = false, filter = 'all';
  var token = $('meta[name="csrf-token"]').attr('content') || $f.find('[name=_token]').val();

  /* ---------- Search + filter (client side) ---------- */
  function applyFilter() {
    var q = $.trim($('#moduleSearch').val()).toLowerCase(), shown = 0;
    var counts = { all: 0, system: 0, custom: 0, inactive: 0 };

    $('.js-mod-row').each(function () {
      var $r = $(this), type = $r.data('type'), off = +$r.data('active') === 0;
      counts.all++; counts[type]++; if (off) counts.inactive++;

      var okFilter = filter === 'all' || filter === type || (filter === 'inactive' && off);
      var okSearch = !q || String($r.data('search')).indexOf(q) > -1;
      var show = okFilter && okSearch;
      $r.toggleClass('d-none', !show); if (show) shown++;
    });

    $('#moduleFilters .pf-chip').each(function () {
      var f = $(this).data('filter');
      $(this).toggleClass('active', f === filter).find('.n').text('(' + counts[f] + ')');
    });
    $('.js-mod-empty').toggleClass('d-none', shown > 0);
    $('.mm-table').toggleClass('d-none', shown === 0);
  }
  $('#moduleSearch').on('input', applyFilter);
  $('#moduleFilters').on('click', '.pf-chip', function () { filter = $(this).data('filter'); applyFilter(); });

  function setList(html) { $('#moduleList').html(html); applyFilter(); }
  applyFilter();

  /* ---------- Icon picker ---------- */
  function previewIcon(icon) {
    icon = $.trim(icon) || 'bi-puzzle';
    $f.find('.mm-icon-preview i').attr('class', 'bi ' + icon);
    $f.find('.pf-icon-btn').each(function () { $(this).toggleClass('active', $(this).data('icon') === icon); });
  }
  $f.on('click', '.pf-icon-btn', function () { $f.find('[name=icon]').val($(this).data('icon')); previewIcon($(this).data('icon')); });
  $f.on('input', '[name=icon]', function () { previewIcon($(this).val()); });
  $f.on('input', '.is-invalid', function () { $(this).removeClass('is-invalid'); });

  /* ---------- Open (create / edit) ---------- */
  $(document).on('click', '.js-mod-open', function () {
    var mod = $(this).data('module') || null;
    $f[0].reset(); $f.find('.is-invalid').removeClass('is-invalid');
    editing = !!mod;
    $f.data('url', editing ? $(this).data('updateUrl') : $m.data('storeUrl'));
    $('#moduleModalTitle').text(editing ? 'Edit module' : 'New module');
    $f.find('.js-save-label').text(editing ? 'Save changes' : 'Create module');
    $f.find('.js-mm-key-row').toggleClass('d-none', !editing);

    if (mod) {
      $f.find('[name=label]').val(mod.label);
      $f.find('[name=description]').val(mod.description || '');
      $f.find('[name=icon]').val(mod.icon);
      $f.find('[name=is_active][type=checkbox]').prop('checked', !!mod.is_active);
      $f.find('.js-mm-key').text(mod.key);
    }
    previewIcon($f.find('[name=icon]').val());
    modal.show();
  });
  $m.on('shown.bs.modal', function () { $f.find('[name=label]').trigger('focus'); });

  /* ---------- Save ---------- */
  $f.on('submit', function (e) {
    e.preventDefault();
    var $b = $f.find('[type=submit]').prop('disabled', true); $b.find('.spinner-border').removeClass('d-none');
    $f.find('.is-invalid').removeClass('is-invalid');

    $.post($f.data('url'), $f.serialize() + (editing ? '&_method=PUT' : ''))
      .done(function (r) { modal.hide(); setList(r.list); MP.toast('success', r.message); })
      .fail(function (x) {
        var r = x.responseJSON || {};
        if (x.status === 422 && r.errors) {
          $.each(r.errors, function (n, msgs) {
            var $i = $f.find('[name="' + n + '"]').first().addClass('is-invalid');
            $i.parent().find('.invalid-feedback').first().text(msgs[0]);
          });
          $f.find('.is-invalid').first().trigger('focus');
        } else MP.toast('danger', 'Unable to save module.');
      })
      .always(function () { $b.prop('disabled', false).find('.spinner-border').addClass('d-none'); });
  });

  /* ---------- Switch on / off ---------- */
  $(document).on('change', '.js-mod-status', function () {
    var $sw = $(this), on = $sw.prop('checked');
    $sw.prop('disabled', true);
    $.ajax({ url: $sw.data('url'), type: 'PATCH', data: { _token: token } })
      .done(function (r) { setList(r.list); MP.toast('success', r.message); })
      .fail(function (x) {
        $sw.prop('checked', !on).prop('disabled', false);
        MP.toast('danger', (x.responseJSON || {}).message || 'Unable to update module.');
      });
  });

  /* ---------- Delete ---------- */
  $(document).on('click', '.js-mod-delete', function () {
    var $btn = $(this), name = $btn.data('name'), used = parseInt($btn.data('used'), 10) || 0;

    if (used > 0) {
      MP.toast('warning', '"' + name + '" is used in ' + used + ' plan(s). Remove it from those plans first, or switch it off.');
      return;
    }
    if (!confirm('Delete module "' + name + '"?\n\nThis action cannot be undone.')) return;

    $btn.prop('disabled', true);
    $.ajax({ url: $btn.data('url'), type: 'DELETE', data: { _token: token } })
      .done(function (r) { setList(r.list); MP.toast('success', r.message); })
      .fail(function (x) {
        $btn.prop('disabled', false);
        MP.toast('danger', (x.responseJSON || {}).message || 'Unable to delete module.');
      });
  });
});