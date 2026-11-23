// /* Owner console shared JS: CSRF for jQuery + sidebar menu (links built from body data-url-* attributes). */
// $(function () {
//   var $b = $('body');
//   $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content'), 'Accept': 'application/json' } });
//   MP.menu = [
//     { group: 'Overview', items: [{ t: 'Dashboard', i: 'bi-grid-1x2', h: $b.data('urlDashboard'), key: 'dash' }] },
//     { group: 'Tenants', items: [{ t: 'Add Company', i: 'bi-building-add', h: $b.data('urlCompanyCreate'), key: 'company-create' }, ['Companies', 'bi-buildings'], ['Trials & Onboarding', 'bi-hourglass-split']] },
//     { group: 'Billing', items: [{ t: 'Plans & Pricing', i: 'bi-tags', h: $b.data('urlPlans') || '#', key: 'plans' }, { t: 'Subscriptions', i: 'bi-arrow-repeat', h: $b.data('urlSubscriptions') || '#', key: 'subscriptions' }, { t: 'Invoices', i: 'bi-file-earmark-ruled', h: $b.data('urlInvoices') || '#', key: 'invoices' }] },
//     { group: 'Support', items: [['Support Tickets', 'bi-headset']] },
//     { group: 'Platform', items: [['Audit Logs', 'bi-journal-text'], ['Server Health', 'bi-activity']] }
//   ];
//   MP.renderSidebar($b.data('active'));
// });
