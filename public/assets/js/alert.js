/* MovePro CRM: bell + notifications, reminder alarm popup (sound, opens the lead), "remind me later".
   Loaded on every company page. Asks the server every ~15 seconds.
   Self-contained: it adds its own stylesheet, popup holder and (if your header still has the old static bell) the live bell. */
(function ($) {
  if (window.__alertsLoaded) return; window.__alertsLoaded = true;   // never run twice
  var script = document.currentScript, base = script && script.src ? script.src.split('/assets/')[0] : '';

  $(function () {
    var title0 = document.title, csrf = $('meta[name=csrf-token]').attr('content');
    var esc = function (s) { return $('<div>').text(s == null ? '' : s).html(); };
    var toast = function (t, m) { if (window.MP && MP.toast) MP.toast(t, m); };
    var err = function (x) { var r = x.responseJSON || {}, e = r.errors; return e ? e[Object.keys(e)[0]][0] : (r.message || 'Something went wrong. Please try again.'); };

    /* ---------- make sure the page has everything we need ---------- */
    if (base && !$('link[href*="alerts.css"]').length) $('head').append('<link rel="stylesheet" href="' + base + '/assets/css/alerts.css">');
    if (!$('#remWrap').length) $('body').append('<div id="remWrap" role="alertdialog" aria-live="assertive" aria-label="Reminders"></div>');
    if (!$('#bellBtn').length) {   // the old static bell is still in the header: swap it for the live one
      var $old = $('.app-topbar [aria-label="Notifications"]').first().closest('.dropdown');
      if ($old.length) $old.replaceWith(
        '<div class="dropdown" id="bellWrap"><button class="icon-btn" id="bellBtn" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-label="Notifications" aria-expanded="false"><i class="bi bi-bell"></i><span class="bell-count d-none" id="bellCount">0</span></button>' +
        '<div class="dropdown-menu dropdown-menu-end p-0 shadow bell-menu"><div class="bell-head"><span class="fw-semibold">Notifications</span><button type="button" class="bell-link" id="bellReadAll">Mark all as read</button></div>' +
        '<div id="bellRem"></div><div id="bellList" class="bell-list"><div class="bell-empty">Loading…</div></div>' +
        '<div class="bell-foot"><a class="bell-link text-decoration-none" id="bellAllRem" href="#"><i class="bi bi-alarm me-1"></i>All reminders</a><button type="button" class="bell-link" id="bellDesk"><i class="bi bi-laptop me-1"></i>Desktop alerts</button><button type="button" class="bell-link" id="bellTest"><i class="bi bi-volume-up me-1"></i>Test sound</button></div></div></div>');
    }
    var $bell = $('#bellBtn');
    var cfg = {
      poll: $bell.data('poll') || base + '/alerts/poll', read: $bell.data('read') || base + '/alerts/read',
      rem: $bell.data('rem') || base + '/reminders', leads: $bell.data('leads') || base + '/leads'
    };
    $('#bellAllRem').attr('href', cfg.rem);
    var state = { nowLocal: null, at: Date.now(), cards: {}, muted: false, errors: 0, stopped: false, timer: null, unread: null };

    /* ---------- server time (the app's timezone), used by "tomorrow 10 AM" etc. ---------- */
    var pad = function (n) { return (n < 10 ? '0' : '') + n; };
    function parse(s) { var m = /^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})/.exec(s); return Date.UTC(+m[1], +m[2] - 1, +m[3], +m[4], +m[5]); }
    function fmt(ms) { var d = new Date(ms); return d.getUTCFullYear() + '-' + pad(d.getUTCMonth() + 1) + '-' + pad(d.getUTCDate()) + 'T' + pad(d.getUTCHours()) + ':' + pad(d.getUTCMinutes()); }
    function plus(s, min) { return fmt(parse(s) + min * 60000); }
    function browserLocal() { return fmt(Date.now() - new Date().getTimezoneOffset() * 60000); }   // wall-clock on this computer
    state.nowLocal = String($bell.attr('data-now') || browserLocal());
    function nowLocal() { return plus(state.nowLocal, Math.floor((Date.now() - state.at) / 60000)); }
    function daysAt(days, hour) { var d = new Date(parse(nowLocal())); d.setUTCDate(d.getUTCDate() + days); d.setUTCHours(hour, 0, 0, 0); return fmt(+d); }
    function clock12(s) { var m = /T(\d{2}):(\d{2})/.exec(s), h = +m[1]; return ((h % 12) || 12) + ':' + m[2] + ' ' + (h < 12 ? 'AM' : 'PM'); }
    // Reminders follow the SERVER clock. If it is hours away from this computer, say so (APP_TIMEZONE in .env).
    function tzMessage() {
      if (!state.polled) return '';
      var diff = Math.round((parse(browserLocal()) - parse(nowLocal())) / 60000);
      return Math.abs(diff) < 15 ? '' : 'Your server clock shows ' + clock12(nowLocal()) + ' but this computer shows ' + clock12(browserLocal()) + '. Reminders follow the server clock, so set APP_TIMEZONE=Asia/Kolkata in the .env file and run "php artisan config:clear".';
    }
    window.MPAlerts = { nowLocal: nowLocal, plus: plus, daysAt: daysAt, tzMessage: tzMessage, poll: poll };

    function api(url, data, method) {
      var d = $.extend({}, data); if (method === 'DELETE') d._method = 'DELETE';
      return $.ajax({ url: url, method: method === 'GET' ? 'GET' : 'POST', data: d, dataType: 'json', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } });
    }
    function beacon(url, body) {   // survives the page navigating away
      try { fetch(url, { method: 'POST', keepalive: true, credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify(body || {}) }); } catch (e) { api(url, body); }
    }
    function openLead(id, tab) {
      if (window.MPLeads) window.MPLeads.open(id, tab);
      else location.href = cfg.leads + '?open=' + id + (tab ? '&tab=' + tab : '');
    }

    /* ---------- sound: a loud two-tone alarm made in the browser (no file needed) ---------- */
    var ctx = null, alarm = null, alarmEnd = 0, blink = null;
    function audio() { if (!ctx) { var C = window.AudioContext || window.webkitAudioContext; if (C) ctx = new C(); } return ctx; }
    function unlock() { var c = audio(); return c && c.state === 'suspended' ? c.resume() : Promise.resolve(); }
    $(document).one('click keydown touchstart', unlock);   // browsers only allow sound after one click on the page
    function beep(freq, t, dur, vol) {
      var o = ctx.createOscillator(), g = ctx.createGain(); o.type = 'square'; o.frequency.value = freq;
      g.gain.setValueAtTime(0.0001, t); g.gain.exponentialRampToValueAtTime(vol, t + 0.01); g.gain.exponentialRampToValueAtTime(0.0001, t + dur);
      o.connect(g); g.connect(ctx.destination); o.start(t); o.stop(t + dur + 0.02);
    }
    function ring(soft) {
      var c = audio(); if (!c) return;
      if (c.state !== 'running') { var p = c.resume(); if (p && p.then) p.then(function () { if (c.state === 'running') ring(soft); else $('.rem-hint').removeClass('d-none'); }); return; }
      $('.rem-hint').addClass('d-none'); var t = c.currentTime;
      if (soft) { beep(880, t, .14, .12); beep(1175, t + .16, .2, .12); return; }
      for (var i = 0; i < 4; i++) beep(i % 2 ? 740 : 988, t + i * .2, .16, .28);
      if (navigator.vibrate) navigator.vibrate([250, 120, 250]);
    }
    function startAlarm() {
      if (alarm || state.muted) return; alarmEnd = Date.now() + 120000; ring();
      alarm = setInterval(function () { if (Date.now() > alarmEnd || state.muted || !Object.keys(state.cards).length) return stopAlarm(); ring(); }, 1600);
    }
    function stopAlarm() { clearInterval(alarm); alarm = null; }

    /* ---------- reminder popups ---------- */
    function cardHtml(r) {
      var wa = r.mobile ? '<a class="btn btn-light border text-success" href="https://wa.me/91' + esc(r.mobile) + '" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a><a class="btn btn-light border" href="tel:+91' + esc(r.mobile) + '" aria-label="Call"><i class="bi bi-telephone"></i></a>' : '';
      return '<div class="rem-card" data-rem="' + r.id + '" data-lead="' + r.lead_id + '"><div class="rem-top"><span class="rem-ring"><i class="bi bi-alarm-fill"></i></span>' +
        '<div class="rem-main js-rem-open" role="button" tabindex="0" aria-label="Open this lead"><div class="rem-kicker">Reminder</div><div class="rem-title">' + esc(r.title) + '</div>' +
        '<div class="rem-lead"><i class="bi bi-person me-1"></i>' + esc(r.lead_name) + (r.mobile ? ' · +91 ' + esc(r.mobile) : '') + '</div><div class="rem-sub">Set for ' + esc(r.at) + ' · ' + esc(r.ago) + '</div>' +
        (r.note ? '<div class="rem-note">' + esc(r.note) + '</div>' : '') + '<span class="rem-open">Open lead <i class="bi bi-arrow-right"></i></span></div>' +
        '<button type="button" class="rem-mute js-rem-mute" aria-label="Mute the alarm sound" title="Mute sound"><i class="bi bi-volume-up"></i></button></div>' +
        '<div class="rem-hint d-none"><i class="bi bi-volume-mute me-1"></i>Your browser is holding the sound. Click anywhere on this page to turn the alarm on.</div>' +
        '<div class="rem-actions"><button type="button" class="btn btn-success js-rm-done"><i class="bi bi-check2 me-1"></i>Done</button>' +
        '<button type="button" class="btn btn-light border js-sn-toggle"><i class="bi bi-clock-history me-1"></i>Remind me later</button>' + wa + '</div><div class="sn-slot"></div></div>';
    }
    function showCard(r) {
      var $w = $('#remWrap'); if (!$w.children('.rem-stack').length) $w.html('<div class="rem-stack"></div>');
      $w.children('.rem-stack').append(cardHtml(r)); $w.addClass('is-on'); state.cards[r.id] = r; state.muted = false;
      if (!blink) { var on = false; blink = setInterval(function () { document.title = (on = !on) ? '⏰ Reminder due!' : title0; }, 1000); }
      startAlarm();
      if (document.hidden && 'Notification' in window && Notification.permission === 'granted') {
        try { var n = new Notification('⏰ ' + r.title, { body: r.lead_name + (r.note ? ' · ' + r.note : ''), tag: 'rem-' + r.id, requireInteraction: true }); n.onclick = function () { window.focus(); openCard($('.rem-card[data-rem=' + r.id + ']'), r); n.close(); }; } catch (e) {}
      }
    }
    function removeCard(id) {
      delete state.cards[id];
      $('.rem-card[data-rem=' + id + ']').fadeOut(150, function () {
        $(this).remove();
        if (!$('#remWrap .rem-card').length) { $('#remWrap').removeClass('is-on').empty(); stopAlarm(); clearInterval(blink); blink = null; document.title = title0; }
      });
      if (!Object.keys(state.cards).length) stopAlarm();
    }
    function openCard($c, r) {
      var id = r ? r.id : $c.data('rem'), lead = r ? r.lead_id : $c.data('lead');
      unlock(); beacon(cfg.rem + '/' + id + '/ack'); if ($c && $c.length) removeCard(id); else delete state.cards[id];
      openLead(lead, 'reminders');
    }
    $(document).on('click', '.js-rem-open', function () { openCard($(this).closest('.rem-card')); });
    $(document).on('keydown', '.js-rem-open', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openCard($(this).closest('.rem-card')); } });
    $(document).on('click', '.js-rem-mute', function () { state.muted = true; stopAlarm(); $(this).find('i').attr('class', 'bi bi-volume-mute-fill'); toast('info', 'Alarm sound muted. The reminder stays on screen.'); });
    $(document).on('click', '#remWrap', unlock);

    /* ---------- done / remind me later / delete (popup, lead's reminder list and the Reminders page) ---------- */
    function changed(lead) { $(document).trigger('reminders:changed', [lead]); poll(); }
    $(document).on('click', '.js-rm-done', function () {
      var $b = $(this).prop('disabled', true), $box = $b.closest('[data-rem]'), id = $box.data('rem'), lead = $box.data('lead');
      api(cfg.rem + '/' + id + '/done').done(function (r) { toast('success', r.message); if ($box.hasClass('rem-card')) removeCard(id); changed(lead); })
        .fail(function (x) { $b.prop('disabled', false); toast('danger', err(x)); });
    });
    $(document).on('click', '.js-rm-del', function () {
      var $b = $(this), $box = $b.closest('[data-rem]');
      if (!$b.data('armed')) { $b.data('armed', true).data('label', $b.html()).addClass('is-armed').text('Sure?'); setTimeout(function () { if ($b.data('armed')) $b.data('armed', false).removeClass('is-armed').html($b.data('label')); }, 3000); return; }
      api(cfg.rem + '/' + $box.data('rem'), {}, 'DELETE').done(function (r) { toast('success', r.message); changed($box.data('lead')); }).fail(function (x) { toast('danger', err(x)); });
    });
    function snoozeHtml() {
      var n = nowLocal();
      return '<div class="sn-panel"><div class="sn-chips"><button type="button" class="sn-chip js-sn" data-min="5">5 min</button><button type="button" class="sn-chip js-sn" data-min="10">10 min</button>' +
        '<button type="button" class="sn-chip js-sn" data-min="60">1 hour</button><button type="button" class="sn-chip js-sn" data-tom="1">Tomorrow 10 AM</button></div>' +
        '<div class="sn-custom"><label class="caption fw-semibold">Or pick a date and time</label><div class="d-flex gap-2"><input type="datetime-local" class="form-control form-control-sm js-sn-at" min="' + n + '" value="' + plus(n, 30) + '" aria-label="New date and time"><button type="button" class="btn btn-sm btn-primary js-sn-set">Set</button></div></div></div>';
    }
    $(document).on('click', '.js-sn-toggle', function () { var $s = $(this).closest('[data-rem]').find('.sn-slot').first(); $s.html($s.children().length ? '' : snoozeHtml()); });
    function snooze($box, p) {
      var id = $box.data('rem'), lead = $box.data('lead'); $box.find('.sn-panel button').prop('disabled', true);
      api(cfg.rem + '/' + id + '/snooze', p).done(function (r) { toast('success', r.message); if ($box.hasClass('rem-card')) removeCard(id); changed(lead); })
        .fail(function (x) { $box.find('.sn-panel button').prop('disabled', false); toast('danger', err(x)); });
    }
    $(document).on('click', '.js-sn', function () { var $b = $(this); snooze($b.closest('[data-rem]'), $b.data('min') ? { minutes: +$b.data('min') } : { at: daysAt(1, 10) }); });
    $(document).on('click', '.js-sn-set', function () { var $box = $(this).closest('[data-rem]'), v = $box.find('.js-sn-at').val(); if (!v) return toast('danger', 'Pick a date and time.'); snooze($box, { at: v }); });

    /* ---------- the bell ---------- */
    var ICON = { lead_new: ['bi-person-plus', 'info'], lead_status: ['bi-arrow-repeat', 'primary'], lead_note: ['bi-chat-left-text', 'primary'], lead_file: ['bi-paperclip', 'info'], lead_branch: ['bi-diagram-3', 'primary'], lead_import: ['bi-upload', 'info'], reminder_due: ['bi-alarm', 'danger'], survey_new: ['bi-clipboard-check', 'info'], survey_assigned: ['bi-person-check', 'primary'], survey_done: ['bi-patch-check', 'info'] };
    function renderRem(items) {
      if (!items.length) { $('#bellRem').html(''); return; }
      $('#bellRem').html('<div class="bell-sec">Your reminders</div>' + $.map(items, function (x) {
        return '<a href="' + cfg.leads + '?open=' + x.lead_id + '&tab=reminders" class="bell-item js-bellrem" data-lead="' + x.lead_id + '"><span class="bell-ico t-' + (x.overdue ? 'danger' : 'info') + '"><i class="bi bi-alarm"></i></span>' +
          '<span class="bell-main"><span class="bell-t d-block">' + esc(x.title) + '</span><span class="bell-b d-block">' + esc(x.lead_name) + '</span><span class="bell-time d-block' + (x.overdue ? ' text-danger' : '') + '">' + (x.overdue ? 'Overdue · ' : '') + esc(x.at) + ' · ' + esc(x.when) + '</span></span></a>';
      }).join(''));
    }
    function renderList(items) {
      if (!items.length) { $('#bellList').html('<div class="bell-empty"><i class="bi bi-bell-slash fs-3 d-block mb-1"></i>No notifications yet.</div>'); return; }
      $('#bellList').html('<div class="bell-sec">Notifications</div>' + $.map(items, function (n) {
        var m = ICON[n.type] || ['bi-bell', ''], href = n.url ? n.url : (n.lead_id ? cfg.leads + '?open=' + n.lead_id + (n.type === 'reminder_due' ? '&tab=reminders' : '') : cfg.leads);
        return '<a href="' + href + '" class="bell-item js-bell' + (n.read ? '' : ' is-unread') + '" data-id="' + n.id + '" data-lead="' + (n.lead_id || '') + '" data-type="' + n.type + '"><span class="bell-ico t-' + m[1] + '"><i class="bi ' + m[0] + '"></i></span>' +
          '<span class="bell-main"><span class="bell-t d-block">' + esc(n.title) + '</span><span class="bell-b d-block">' + esc(n.body || '') + '</span><span class="bell-time d-block">' + esc(n.time) + '</span></span>' + (n.read ? '' : '<span class="bell-dot"></span>') + '</a>';
      }).join(''));
    }
    function badge(n) {
      $('#bellCount').toggleClass('d-none', !n).text(n > 99 ? '99+' : n);
      if (state.unread !== null && n > state.unread) { $bell.addClass('bell-shake'); setTimeout(function () { $bell.removeClass('bell-shake'); }, 1000); if (!alarm) ring(true); }
      state.unread = n;
    }
    $(document).on('click', '.js-bell', function (e) {
      var $a = $(this), id = $a.data('id'), lead = $a.data('lead');
      if ($a.hasClass('is-unread')) { beacon(cfg.read, { ids: [id] }); $a.removeClass('is-unread').find('.bell-dot').remove(); }
      if (window.MPLeads && lead) { e.preventDefault(); $bell.dropdown('hide'); window.MPLeads.open(lead, $a.data('type') === 'reminder_due' ? 'reminders' : null); }
    });
    $(document).on('click', '.js-bellrem', function (e) { if (window.MPLeads) { e.preventDefault(); $bell.dropdown('hide'); window.MPLeads.open($(this).data('lead'), 'reminders'); } });
    $(document).on('click', '#bellReadAll', function () { api(cfg.read, { all: 1 }).done(poll); });
    $(document).on('click', '#bellTest', function () { unlock().then(function () { ring(); if (ctx && ctx.state !== 'running') toast('info', 'Your browser is holding the sound. Click once on the page and try again.'); }); });
    $(document).on('click', '#bellDesk', function () {
      if (!('Notification' in window)) return toast('info', 'This browser does not support desktop alerts.');
      if (Notification.permission === 'granted') return toast('info', 'Desktop alerts are already on.');
      if (Notification.permission === 'denied') return toast('danger', 'Desktop alerts are blocked. Allow them in the browser settings for this site.');
      Notification.requestPermission().then(function (p) { toast(p === 'granted' ? 'success' : 'info', p === 'granted' ? 'Desktop alerts are on. Reminders will pop up even when this tab is hidden.' : 'Desktop alerts stay off.'); });
    });

    /* ---------- polling ---------- */
    function apply(r) {
      state.nowLocal = r.nowLocal; state.at = Date.now(); state.polled = true; renderList(r.items); renderRem(r.upcoming || []); badge(r.unread);
      var ids = {};
      $.each(r.due, function (_, d) { ids[d.id] = 1; if (!state.cards[d.id]) showCard(d); });
      $.each(Object.keys(state.cards), function (_, id) { if (!ids[id]) removeCard(id); });   // done / moved in another tab
    }
    function schedule() { clearTimeout(state.timer); if (!state.stopped) state.timer = setTimeout(poll, document.hidden ? 30000 : 15000); }
    function poll() {
      clearTimeout(state.timer);
      api(cfg.poll, null, 'GET').done(function (r) { state.errors = 0; apply(r); })
        .fail(function (x) { if (x.status === 401 || x.status === 403 || x.status === 419 || ++state.errors >= 6) state.stopped = true; })
        .always(schedule);
    }
    document.addEventListener('visibilitychange', function () { if (!document.hidden && !state.stopped) poll(); });
    $(document).on('show.bs.dropdown', '#bellWrap', function () { if (!state.stopped) poll(); });
    poll();
  });
})(jQuery);
