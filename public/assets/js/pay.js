/* Razorpay Checkout wrapper. MPPay.open(checkoutOptions, onSuccess(response), onDismiss(errorMessage?)) */
window.MPPay = {
  open: function (opts, onSuccess, onDismiss) {
    if (!window.Razorpay) { onDismiss && onDismiss('Could not load the payment window. Check your internet connection.'); return; }
    var rz = new Razorpay($.extend({}, opts, { handler: onSuccess, modal: { confirm_close: true, ondismiss: function () { onDismiss && onDismiss(); } } }));
    rz.on('payment.failed', function (r) { onDismiss && onDismiss(r.error && r.error.description ? r.error.description : 'Payment failed. Please try again.'); });
    rz.open();
  }
};
