<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>@yield('title') · LeadFlow CRM</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="robots" content="noindex, nofollow">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
@stack('head')
</head>
<body>

@yield('body')

<div class="toast-container position-fixed bottom-0 end-0 p-3" id="toastStack" style="z-index:1080"></div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

  // Safe toast: message is inserted as text, never as HTML.
  window.showToast = function (type, msg) {
    const bg = type === 'success' ? 'text-bg-success' : 'text-bg-danger';
    const $toast = $(
      '<div class="toast align-items-center ' + bg + ' border-0" role="alert">' +
        '<div class="d-flex">' +
          '<div class="toast-body"></div>' +
          '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>' +
        '</div>' +
      '</div>'
    );
    $toast.find('.toast-body').text(msg);
    $('#toastStack').append($toast);
    new bootstrap.Toast($toast[0], { delay: 5000 }).show();
  };

  $(function () {
    @if (session('auth_error'))
      showToast('error', @json(session('auth_error')));
    @endif
    @if (session('auth_status'))
      showToast('success', @json(session('auth_status')));
    @endif
  });
</script>
@stack('scripts')
</body>
</html>