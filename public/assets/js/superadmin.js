/* MovePro Owner Console — demo data + charts. Replace arrays with Blade loops / API later. */
$(function () {
  // Real links come from <body data-url-dashboard="..." data-url-company-create="..."> (set in Blade with route()).
  var $b = $('body'), URL_DASH = $b.data('urlDashboard') || 'dashboard.html', URL_ADD_COMPANY = $b.data('urlCompanyCreate') ||  '#' , URL_ADD_CASHFREE = $b.data('urlCashfree') ||'#';

  // Owner menu overrides the customer-company menu from app.js
  MP.menu = [
    { group: 'Overview', items: [{ t: 'Dashboard', i: 'bi-grid-1x2', h: URL_DASH, key: 'dash' }] },
    { group: 'Tenants', items: [{ t: 'Add Company', i: 'bi-building-add', h: URL_ADD_COMPANY, key: 'company-create' }, { t: 'Companies', i: 'bi-buildings', h: $b.data('urlCompanies') || '#', key: 'companies' }, { t: 'Trials & Onboarding', i: 'bi-hourglass-split', h: $b.data('urlTrials') || '#', key: 'trials' }, { t: 'Branches & Users', i: 'bi-diagram-3', h: $b.data('urlDirectory') || '#', key: 'directory' }, ['Login as Company', 'bi-box-arrow-in-right']] },
    { group: 'Billing', items: [{ t: 'Plans & Pricing', i: 'bi-tags', h: $b.data('urlPlans') || '#', key: 'plans' }, { t: 'Subscriptions', i: 'bi-arrow-repeat', h: $b.data('urlSubscriptions') || '#', key: 'subscriptions' }, { t: 'Invoices', i: 'bi-file-earmark-ruled', h: $b.data('urlInvoices') || '#', key: 'invoices' }, ['Transactions', 'bi-credit-card'], ['Coupons', 'bi-ticket-perforated']] },
    { group: 'Product', items: [['Modules & Feature Flags', 'bi-toggles'], ['Usage Analytics', 'bi-bar-chart-line'], ['Releases & Changelog', 'bi-rocket-takeoff'], ['Announcements', 'bi-megaphone']] },
    { group: 'AI & Messaging', items: [['AI Usage & Credits', 'bi-stars'], ['WhatsApp Credits', 'bi-whatsapp'], ['SMS / Email Logs', 'bi-envelope']] },
    { group: 'Support', items: [['Support Tickets', 'bi-headset'], ['Knowledge Base', 'bi-book'], ['Feedback', 'bi-chat-heart']] },
    { group: 'Platform', items: [['Server Health', 'bi-activity'], ['Audit Logs', 'bi-journal-text'], ['Backups', 'bi-cloud-arrow-up'], ['API & Webhooks', 'bi-plug']] },
    { group: 'Settings', items: [{ t: 'Payment Gateway', i: 'bi-credit-card-2-front', h: $b.data('urlPayments') || '#', key: 'payments' }, { t: 'Platform Settings', i: 'bi-gear', h: $b.data('urlCashfree') || '#', key: 'cashfree' }, ['Owner Team', 'bi-person-gear'], ['Roles & Permissions', 'bi-shield-lock']] }
  ];


  MP.renderSidebar($b.data('active') || 'dash');

  var kpis = [
    ['Total Companies', '248', 'bi-buildings', 'primary', '+14', 'up', 'this month'],
    ['Active Subscriptions', '219', 'bi-patch-check', 'success', '+11', 'up', 'this month'],
    ['Trials Running', '23', 'bi-hourglass-split', 'info', '+6', 'up', 'vs last week'],
    ['MRR', '₹12,48,000', 'bi-currency-rupee', 'success', '8.4%', 'up', 'from last month'],
    ['ARR', '₹1.49 Cr', 'bi-graph-up-arrow', 'primary', '21.2%', 'up', 'year on year'],
    ['Churn Rate', '2.1%', 'bi-person-dash', 'warning', '0.4%', 'down', 'better than last month'],
    ['Open Tickets', '17', 'bi-headset', 'danger', '+3', 'up', '4 near SLA limit'],
    ['Renewals Due (7 days)', '31', 'bi-calendar-event', 'warning', '₹3,10,000', 'up', 'to collect']
  ];
  $('#kpiRow').html(kpis.map(function (k) {
    return '<div class="col-6 col-md-4 col-xl-3"><div class="mp-card kpi"><div class="kpi-icon tone-' + k[3] + '"><i class="bi ' + k[2] + '"></i></div><div class="min-w-0">' +
      '<div class="kpi-value">' + k[1] + '</div><div class="kpi-label">' + k[0] + '</div>' +
      '<div class="kpi-trend trend-' + (k[0] === 'Churn Rate' ? 'up' : k[5]) + '"><i class="bi bi-arrow-' + k[5] + '-short"></i>' + k[4] + ' <span class="caption fw-normal d-none d-sm-inline">' + k[6] + '</span></div></div></div></div>';
  }).join(''));

  var alerts = [
    ['danger', '6 subscription payments failed', 'High', '15 min ago', 'Retry'],
    ['warning', '4 tickets close to SLA breach', 'High', '40 min ago', 'Open'],
    ['warning', '9 trials ending in 3 days', 'Medium', '1 hr ago', 'Nudge'],
    ['danger', 'WhatsApp API credits low (12%)', 'High', '2 hrs ago', 'Top up'],
    ['info', 'v2.4.0 release ready to publish', 'Low', 'Today', 'Review']
  ];
  $('#alertList').html(alerts.map(function (a) {
    return '<div class="alert-row"><span class="alert-bar" style="background:var(--mp-' + a[0] + ')"></span><div class="alert-text">' + a[1] +
      '<div class="caption">' + a[2] + ' priority · ' + a[3] + '</div></div><button class="btn btn-sm btn-outline-primary js-alert-action" data-label="' + a[4] + '">' + a[4] + '</button></div>';
  }).join(''));
  $(document).on('click', '.js-alert-action', function () { MP.toast('info', $(this).data('label') + ' will open the related record.'); });

  var health = [['API uptime (30 days)', 99.96, 'success'], ['Queue workers', 92, 'success'], ['Database storage', 68, 'warning'], ['File storage', 41, 'success'], ['WhatsApp credits', 12, 'danger']];
  $('#healthList').html(health.map(function (h) {
    return '<div class="mb-3"><div class="d-flex justify-content-between small mb-1"><span>' + h[0] + '</span><span class="fw-semibold">' + h[1] + '%</span></div><div class="progress" style="height:6px"><div class="progress-bar bg-' + h[2] + '" style="width:' + h[1] + '%"></div></div></div>';
  }).join(''));

  var cos = [
    ['Sharma Packers & Movers', 'Rahul Sharma', 'Jaipur', 'Business', 18, '02 Oct', 'Active', 'success'],
    ['Safe Move Logistics', 'Amit Kumar', 'Mumbai', 'Enterprise', 64, '01 Oct', 'Active', 'success'],
    ['Royal Cargo Movers', 'Neha Singh', 'Lucknow', 'Starter', 4, '30 Sep', 'Trial · 9 days', 'info'],
    ['Verma Relocations', 'Pooja Verma', 'Pune', 'Business', 11, '28 Sep', 'Payment Failed', 'danger'],
    ['Gupta Transport Co.', 'Rakesh Gupta', 'Kolkata', 'Starter', 6, '25 Sep', 'Suspended', 'warning']
  ];
  $('#companyRows').html(cos.map(function (c) {
    return '<tr><td><div class="fw-semibold">' + c[0] + '</div><div class="caption">' + c[1] + '</div></td><td>' + c[2] + '</td><td>' + c[3] + '</td><td>' + c[4] + '</td><td>' + c[5] + '</td>' +
      '<td><span class="status tone-' + c[7] + '">' + c[6] + '</span></td>' +
      '<td class="text-nowrap"><a href="#" data-soon="1" title="Login as ' + c[0] + '" class="btn btn-sm btn-light" aria-label="Login as company"><i class="bi bi-box-arrow-in-right"></i></a> <a href="#" data-soon="1" title="' + c[0] + '" class="btn btn-sm btn-light">Manage</a></td></tr>';
  }).join(''));

  if (!window.Chart || !document.getElementById('mrrChart')) return; // charts only on the dashboard
  Chart.defaults.font.family = 'Inter, system-ui, sans-serif'; Chart.defaults.color = '#6B7280';
  var grid = { color: '#EEF1F6' }, nogrid = { grid: { display: false } };
  new Chart('mrrChart', { type: 'line', data: { labels: ['Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct'], datasets: [{ label: 'MRR (₹ lakh)', data: [6.1, 7.4, 8.2, 9.5, 10.6, 11.5, 12.48], borderColor: '#2563EB', backgroundColor: 'rgba(37,99,235,.08)', fill: true, tension: .35, pointRadius: 3 }] }, options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { grid: grid }, x: nogrid } } });
  new Chart('planChart', { type: 'doughnut', data: { labels: ['Starter', 'Business', 'Enterprise', 'Trial'], datasets: [{ data: [96, 88, 35, 23], backgroundColor: ['#94A3B8', '#2563EB', '#16A34A', '#0EA5E9'], borderWidth: 2 }] }, options: { maintainAspectRatio: false, cutout: '68%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, usePointStyle: true } } } } });
  new Chart('signupChart', { type: 'bar', data: { labels: ['Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct'], datasets: [{ label: 'New companies', data: [12, 15, 14, 19, 22, 24, 14], backgroundColor: '#2563EB', borderRadius: 6 }, { label: 'Churned', data: [3, 2, 4, 3, 5, 4, 2], backgroundColor: '#DC2626', borderRadius: 6 }] }, options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, usePointStyle: true } } }, scales: { y: { grid: grid }, x: nogrid } } });
  new Chart('moduleChart', { type: 'bar', data: { labels: ['Bilty', 'Bookings', 'Quotations', 'Inventory', 'AI Assistant', 'WhatsApp'], datasets: [{ data: [92, 88, 81, 63, 47, 39], backgroundColor: '#0EA5E9', borderRadius: 6 }] }, options: { indexAxis: 'y', maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { callbacks: { label: function (c) { return c.parsed.x + '% of companies use it'; } } } }, scales: { x: { grid: grid, max: 100 }, y: nogrid } } });
});
