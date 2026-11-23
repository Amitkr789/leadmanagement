<header class="app-topbar">
    <button class="icon-btn js-sidebar-toggle" aria-label="Toggle sidebar"><i class="bi bi-list fs-4"></i></button>
    <nav aria-label="breadcrumb" class="d-none d-md-block"><ol class="breadcrumb mb-0 small">@yield('breadcrumb')</ol></nav>
    <div class="ms-auto dropdown">
      <button class="btn d-flex align-items-center gap-2 p-1" data-bs-toggle="dropdown" aria-label="User menu">
        <span class="avatar">{{ strtoupper(substr(auth('superadmin')->user()->name, 0, 2)) }}</span>
        <span class="user-meta text-start lh-sm d-none d-md-block"><span class="d-block fw-semibold small">{{ auth('superadmin')->user()->name }}</span><span class="caption">Super Admin</span></span>
      </button>
      <ul class="dropdown-menu dropdown-menu-end shadow"><li>
        <form method="POST" action="{{ route('console.logout') }}">@csrf<button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Logout</button></form>
      </li></ul>
    </div>
  </header>