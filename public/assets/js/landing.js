/* Landing page (jQuery): price toggle, navbar shadow, scroll-reveal */
$(function () {
  var inr = function (n) { return '₹' + (+n).toLocaleString('en-IN'); };
  $('input[name=bill]').on('change', function () {
    var y = $('#billY').is(':checked');
    $('.js-price').each(function () { $(this).text(inr($(this).data(y ? 'yearly' : 'monthly'))); });
    $('.js-per').text(y ? '/year' : '/month');
  });
  $(window).on('scroll', function () { $('.lp-nav').toggleClass('is-scrolled', window.scrollY > 8); }).trigger('scroll');
  $('#lpMenu a').on('click', function () { bootstrap.Collapse.getOrCreateInstance('#lpMenu', { toggle: false }).hide(); });
});

/* Scroll-reveal: plain JS so it works even if jQuery fails to load */
(function () {
  var els = document.querySelectorAll('.reveal');
  if (!('IntersectionObserver' in window) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) { els.forEach(function (e) { e.classList.add('in'); }); return; }
  var io = new IntersectionObserver(function (en) { en.forEach(function (x) { if (x.isIntersecting) { x.target.classList.add('in'); io.unobserve(x.target); } }); }, { threshold: .12 });
  els.forEach(function (e) { io.observe(e); });
})();