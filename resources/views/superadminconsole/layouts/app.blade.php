<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Owner Dashboard · MovePro Console')</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="{{ asset('assets/css/app.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/css/components.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/css/responsive.css') }}" rel="stylesheet">
<link href="{{ asset('assets/css/console.css') }}" rel="stylesheet">
</head>

<body data-active="@yield('active')" data-url-cashfree="{{ route('console.cashfree.edit') }}" data-url-dashboard="{{ route('console.dashboard') }}" data-url-company-create="{{ route('console.companies.create') }}" data-url-plans="{{ route('console.plans.index') }}" data-url-subscriptions="{{ route('console.subscriptions.index') }}" data-url-invoices="{{ route('console.invoices.index') }}" data-url-payments="{{ route('console.payments.edit') }}" data-url-companies="{{ route('console.companies.index') }}" data-url-trials="{{ route('console.trials.index') }}" data-url-directory="{{ route('console.directory.index') }}">
  
<aside class="app-sidebar" aria-label="Main navigation">
  <a href="dashboard.html" class="brand">
    <span class="brand-logo"><i class="bi bi-shield-check"></i></span>
    <span class="brand-text"><div class="brand-name">MovePro Console</div><div class="brand-sub">Software Owner Panel</div></span>
  </a>
  <nav class="sidebar-scroll" id="sidebarNav"></nav>
</aside>
<div class="drawer-backdrop"></div>

<div class="app-main">
  @include('superadminconsole.partials.header')

  <main class="app-content">
  	@yield('content')
  </main>
</div>

<nav class="bottom-nav" aria-label="Quick navigation">
  <a href="dashboard.html" class="active"><i class="bi bi-grid-1x2-fill"></i>Home</a>
  <a href="#" data-soon="1" title="Companies"><i class="bi bi-buildings"></i>Companies</a>
  <a href="#" data-soon="1" title="Add Company" class="fab"><i class="bi bi-plus-lg"></i></a>
  <a href="#" data-soon="1" title="Billing"><i class="bi bi-credit-card"></i>Billing</a>
  <button class="js-drawer-open" aria-label="More"><i class="bi bi-list"></i>More</button>
</nav>

<div class="toast-wrap" id="toastWrap" aria-live="polite"></div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="{{ asset('assets/js/app.js') }}"></script>
<script src="{{ asset('assets/js/superadmin.js') }}"></script>
<script src="{{ asset('assets/js/company-create.js') }}"></script>
<script src="{{ asset('assets/js/plan-form.js') }}"></script>
<script src="{{ asset('assets/js/console.js') }}"></script>
<script src="{{ asset('assets/js/company-modal.js') }}"></script>
<script src="{{ asset('assets/js/payments-settings.js') }}"></script>
<script src="{{ asset('assets/js/cashfree-settings.js') }}"></script>

<script>
  // Auto-apply filters: selects instantly, search after a short pause
  $(function () { var t, $f = $('#subFilter'); $f.on('change', 'select', function () { $f.trigger('submit'); }); $f.on('input', 'input[name=q]', function () { clearTimeout(t); t = setTimeout(function () { $f.trigger('submit'); }, 450); }); });

$(document).on('click', '.js-pay', function () {

    var $button = $(this);

    $.ajax({
        url: $button.data('url'),
        type: 'POST',

        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            payment_mode: $button.data('mode')
        },

        success: function (r) {
            MP.toast('success', r.message);

            setTimeout(function () {
                location.reload();
            }, 600);
        },

        error: function (xhr) {
            console.error(xhr.responseText);

            MP.toast(
                'danger',
                'Unable to update the invoice.'
            );
        }
    });

});
</script>

</body>
</html>
