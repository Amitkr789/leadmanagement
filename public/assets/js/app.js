/* MovePro CRM — app shell behaviour (jQuery). Brand + menu are config objects: swap for Blade later. */
window.MP = window.MP || {};

MP.brand = { name: 'MovePro CRM', sub: 'Packers & Movers Management System' };

// Menu config. href '#' = page not built yet (shows an info toast).
MP.menu = [
  { group: 'Main', items: [
  { t: 'Dashboard', i: 'bi-grid-1x2', h: 'dashboard.html', key: 'dashboard' },
  { t: 'Calendar', i: 'bi-calendar3', h: '#', key: 'calendar' }
] },
  { group: 'CRM', items: [{ t: 'Leads', i: 'bi-person-plus', h: '#', key: 'leads' }, ['Customers', 'bi-people'], ['Follow-ups', 'bi-telephone-outbound'], { t: 'Surveys', i: 'bi-clipboard-check', h: '#', key: 'surveys' }] },
  { group: 'Sales', items: [['Quotations', 'bi-file-earmark-text'], ['Bookings', 'bi-calendar-check']] },
  { group: 'Operations', items: [['Moving Orders', 'bi-box-seam'], ['Bilty', 'bi-receipt'], ['Inventory', 'bi-boxes'], { t: 'Vehicles', i: 'bi-truck', h: '#', key: 'vehicles' },{ t: 'Drivers', i: 'bi-person-badge', h: '#', key: 'drivers' }, ['Warehouse', 'bi-building'], ['Trips', 'bi-signpost-split'], ['Delivery / POD', 'bi-patch-check']] },
  { group: 'Finance', items: [['Payments', 'bi-wallet2'], ['Expenses', 'bi-cash-stack'], ['Invoices', 'bi-file-earmark-ruled'], ['Outstanding', 'bi-exclamation-circle']] },
  { group: 'Communication', items: [{ t: 'WhatsApp', i: 'bi-whatsapp', h: '#', key: 'whatsapp' }, ['Notifications', 'bi-bell']] },
  { group: 'Reports', items: [['Sales Reports', 'bi-graph-up'], ['Operations Reports', 'bi-bar-chart'], ['Financial Reports', 'bi-pie-chart'], ['Customer Reports', 'bi-person-lines-fill']] },
  { group: 'AI', items: [['AI Assistant', 'bi-stars'], ['AI Insights', 'bi-lightbulb'], ['AI Documents', 'bi-file-earmark-richtext']] },
  { group: 'Settings', items: [{ t: 'Billing & Plan', i: 'bi-credit-card', h: '#', key: 'billing' }, { t: 'Company', i: 'bi-building-gear', h:'#', key: 'company'}, { t: 'Branches', i: 'bi-diagram-3', h: '#', key: 'branch' }, { t: 'Users', i: 'bi-person-gear', h: '#', key: 'users' }, ['Roles & Permissions', 'bi-shield-lock'], { t: 'Integrations', i: 'bi-list-check', h: '#', key: 'integrations' }, ['System Settings', 'bi-gear']] }
];


MP.renderSidebar = function (activeKey) {
  var html = '';
  MP.menu.forEach(function (g, gi) {
    var id = 'grp' + gi;
    html += '<button class="nav-group-btn" data-bs-toggle="collapse" data-bs-target="#' + id + '" aria-expanded="true"><span>' + g.group + '</span><i class="bi bi-chevron-down"></i></button>';
    html += '<div class="collapse show" id="' + id + '">';
    g.items.forEach(function (it) {
      var o = Array.isArray(it) ? { t: it[0], i: it[1], h: '#' } : it;
      var u = o.key && $('body').data('url' + o.key.charAt(0).toUpperCase() + o.key.slice(1)); if (u) o.h = u; // links come from <body data-url-...>
      var active = o.key && o.key === activeKey ? ' active' : '';
      html += '<a class="nav-link-item' + active + '" href="' + o.h + '" title="' + o.t + '"' + (o.h === '#' ? ' data-soon="1"' : '') + '><i class="bi ' + o.i + '"></i><span>' + o.t + '</span></a>';
    });
    html += '</div>';
  });
  $('#sidebarNav').html(html);
};

/* Toast: MP.toast('success'|'danger'|'warning'|'info', 'Message') */
MP.toast = function (type, msg) {
  var icons = { success: 'check-circle-fill', danger: 'x-circle-fill', warning: 'exclamation-triangle-fill', info: 'info-circle-fill' };
  var $t = $('<div class="toast align-items-center border-0 shadow" role="alert"><div class="d-flex"><div class="toast-body d-flex gap-2 align-items-center">' +
    '<i class="bi bi-' + icons[type] + ' text-' + type + '"></i><span></span></div>' +
    '<button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div></div>');
  $t.find('span').text(msg).end().appendTo('#toastWrap');
  var t = new bootstrap.Toast($t[0], { delay: 3500 }); t.show();
  $t.on('hidden.bs.toast', function () { $t.remove(); });
};

/* Demo search data — replace with API call */
MP.searchData = [
  ['Customer', 'Rahul Sharma', '+91 98290 12345'], ['Lead', 'LD-2041 · Neha Singh', 'Pune → Jaipur'],
  ['Booking', 'BK-1024 · Rahul Sharma', 'Jaipur → Delhi'], ['Bilty', 'BLT-1021', 'Mumbai → Jaipur'],
  ['Vehicle', 'RJ14AB1234', 'Eicher 14ft'], ['Driver', 'Amit Kumar', 'RJ14AB1234']
];

$(function () {
  var $b = $('body');
  // Sidebar toggle: collapse on desktop, drawer on tablet/mobile
  $(document).on('click', '.js-sidebar-toggle', function () {
    if (window.innerWidth < 992) { $b.toggleClass('drawer-open'); }
    else { $b.toggleClass('sidebar-collapsed'); try { localStorage.setItem('mpCollapsed', $b.hasClass('sidebar-collapsed') ? 1 : 0); } catch (e) {} }
  });
  $(document).on('click', '.drawer-backdrop, .js-drawer-open', function () { $b.toggleClass('drawer-open', $(this).hasClass('js-drawer-open')); });
  try { if (localStorage.getItem('mpCollapsed') === '1' && window.innerWidth >= 992) $b.addClass('sidebar-collapsed'); } catch (e) {}
  if (window.innerWidth >= 992 && window.innerWidth < 1200) $b.addClass('sidebar-collapsed'); // tablet default

  // Pages not built yet
  $(document).on('click', '[data-soon]', function (e) { e.preventDefault(); MP.toast('info', $(this).attr('title') + ' page is coming in the next build.'); $b.removeClass('drawer-open'); });

  // Mobile search toggle
  $(document).on('click', '.js-search-toggle', function () { $b.toggleClass('search-open'); $('#globalSearch').trigger('focus'); });

  // Global search dropdown
  $('#globalSearch').on('input focus', function () {
    var q = $.trim(this.value).toLowerCase(), $r = $('#searchResults');
    var hits = MP.searchData.filter(function (r) { return !q || (r[1] + r[2] + r[0]).toLowerCase().indexOf(q) > -1; });
    $r.html(hits.length ? hits.map(function (r) { return '<a href="#" data-soon="1" title="' + r[1] + '" class="search-item"><span class="search-type">' + r[0] + '</span><span><div class="fw-semibold">' + r[1] + '</div><div class="caption">' + r[2] + '</div></span></a>'; }).join('') : '<div class="empty-state py-3">No results found.</div>').addClass('show');
  });
  $(document).on('click', function (e) { if (!$(e.target).closest('.global-search').length) $('#searchResults').removeClass('show'); });
  $(document).on('keydown', function (e) { if (e.key === '/' && !$(e.target).is('input,textarea,select')) { e.preventDefault(); $('#globalSearch').trigger('focus'); } if (e.key === 'Escape') $('#searchResults').removeClass('show'); });

  // Logout demo
  $(document).on('click', '.js-logout', function (e) { e.preventDefault(); MP.toast('info', 'Logout will connect to Laravel auth.'); });
});
