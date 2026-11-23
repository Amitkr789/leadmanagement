/* Company > Branches (jQuery): live filters, details modal, add-branch modal. Needs team-ui.js. */
$(function () {
  TeamUI.init();
  var parts = ['#branchTop', '#branchTable'];
  TeamUI.live('#branchFilter', '#branchTable');
  TeamUI.detail({ modal: '#branchModal', content: '#branchModalContent', row: '.js-row', open: '.js-open', onChange: function () { TeamUI.soft(parts); } });

  var $m = $('#branchCreateModal'), add = new bootstrap.Modal($m[0]), $f = $('#branchCreateForm');
  $(document).on('click', '.js-branch-add', function () {
    $f[0].reset(); TeamUI.clear($f);
    $('#loginFields').addClass('d-none').find('input').prop('disabled', true);
    $('#lPass').attr('type', 'password'); $f.find('.js-eye i').removeClass('bi-eye-slash').addClass('bi-eye');
    add.show();
  });
  $('#bLogin').on('change', function () { $('#loginFields').toggleClass('d-none', !this.checked).find('input').prop('disabled', !this.checked); });
  $m.on('shown.bs.modal', function () { $('#bName').trigger('focus'); });
  TeamUI.forms($m, function () { add.hide(); TeamUI.soft(parts); });
});
