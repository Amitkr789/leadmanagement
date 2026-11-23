/* Company > Billing & Plan (jQuery): live price with GST, create Razorpay order, open Checkout, verify. */
$(function () {
  var $f = $('#billForm'); if (!$f.length) return;
  var gst = +$f.data('gst') || 18, inr = function (n) { return '₹' + Math.round(n).toLocaleString('en-IN'); };
  $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content'), 'Accept': 'application/json' } });

  function refresh() {
    var p = $f.find('[name=plan]:checked'), y = $f.find('[name=billing_cycle]:checked').val() === 'yearly', sub = +p.data(y ? 'yearly' : 'price'), g = Math.round(sub * gst / 100);
    $('#bSub').text(inr(sub)); $('#bGst').text(inr(g)); $('#bTotal').text(inr(sub + g)); $('#payLabel').text('Pay ' + inr(sub + g) + ' securely');
  }
  $f.on('change', refresh); refresh();

  function busy(on) { $f.find('[type=submit]').prop('disabled', on).find('.spinner-border').toggleClass('d-none', !on); }
  $f.on('submit', function (e) {
    e.preventDefault(); busy(true); $('#billAlert').addClass('d-none');
    $.post($f.data('orderUrl'), $f.serialize())
      .done(function (r) {
        MPPay.open(r.checkout, function (resp) {
          $.post($f.data('verifyUrl'), resp).done(function (v) { MP.toast('success', v.message); setTimeout(function () { location = v.redirect; }, 900); })
            .fail(function (x) { busy(false); $('#billAlert').text(((x.responseJSON || {}).errors || {}).plan ? x.responseJSON.errors.plan[0] : 'Payment verification failed.').removeClass('d-none'); });
        }, function (msg) { busy(false); if (msg) $('#billAlert').text(msg).removeClass('d-none'); });
      })
      .fail(function (x) { busy(false); var er = (x.responseJSON || {}).errors; $('#billAlert').text(er && er.plan ? er.plan[0] : (x.responseJSON || {}).message || 'Unable to start the payment.').removeClass('d-none'); });
  });
});
