/* Company > Users (jQuery): live filters, details modal, add-user modal. Needs team-ui.js. */
$(function () {
  TeamUI.init();
  var parts = ['#userTop', '#userTable'];
  TeamUI.live('#userFilter', '#userTable');
  TeamUI.detail({ modal: '#userModal', content: '#userModalContent', row: '.js-row', open: '.js-open', onChange: function () { TeamUI.soft(parts); } });

  var $m = $('#userCreateModal'), add = new bootstrap.Modal($m[0]), $f = $('#userCreateForm');
  function openAdd(branch) {
    $f[0].reset(); TeamUI.clear($f);
    $('#uPass').attr('type', 'password'); $f.find('.js-eye i').removeClass('bi-eye-slash').addClass('bi-eye');
    if (branch) $f.find('[name=branch_id]').val(branch);
    add.show();
  }
  $(document).on('click', '.js-user-add', function () { openAdd(); });
  $m.on('shown.bs.modal', function () { $('#uName').trigger('focus'); });
  TeamUI.forms($m, function () { add.hide(); TeamUI.soft(parts); });

  // Arrived from a branch's "Add user" button: ?branch=ID&add=1
  var q = new URLSearchParams(location.search);
  if (q.get('add') === '1' && !$('.js-user-add').first().prop('disabled')) {
    openAdd(q.get('branch'));
    history.replaceState(null, '', location.pathname + (q.get('branch') ? '?branch=' + q.get('branch') : ''));
  }
});
