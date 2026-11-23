/* MovePro CRM > Calendar (jQuery + Bootstrap 5). Talks to routes/calendar.php.
   Times are wall-clock strings "YYYY-MM-DDTHH:mm" with no timezone; here they are handled as "UTC ms" so DST never shifts anything. */
(function ($) {
  'use strict';
  var $app = $('#calApp');
  if (!$app.length) return;

  var cfg = {};
  try { cfg = JSON.parse($app.attr('data-cfg') || '{}'); } catch (e) { cfg = {}; }
  var URLS = cfg.urls || {};

  /* ------------------------------------------------------------------ helpers */
  var DAY = 864e5, MIN = 6e4, HOUR = 36e5, HH = 48, WEEK0 = 1;   // week starts on Monday (0 = Sunday)
  var MON = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
  var MONS = MON.map(function (m) { return m.slice(0, 3); });
  var DOW = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
  var DOWS = DOW.map(function (d) { return d.slice(0, 3); });

  function pad(n) { return n < 10 ? '0' + n : '' + n; }
  function P(s) {
    var m = /^(\d{4})-(\d\d)-(\d\d)(?:[T ](\d\d):(\d\d))?/.exec(s || '');
    return m ? Date.UTC(+m[1], +m[2] - 1, +m[3], +(m[4] || 0), +(m[5] || 0)) : NaN;
  }
  function ymd(ms) { var d = new Date(ms); return d.getUTCFullYear() + '-' + pad(d.getUTCMonth() + 1) + '-' + pad(d.getUTCDate()); }
  function hm(ms) { var d = new Date(ms); return pad(d.getUTCHours()) + ':' + pad(d.getUTCMinutes()); }
  function F(ms) { return ymd(ms) + 'T' + hm(ms); }
  function day0(ms) { return Math.floor(ms / DAY) * DAY; }
  function dnum(ms) { return new Date(ms).getUTCDate(); }
  function dow(ms) { return new Date(ms).getUTCDay(); }
  function monthFirst(ms) { var d = new Date(ms); return Date.UTC(d.getUTCFullYear(), d.getUTCMonth(), 1); }
  function daysIn(ms) { var d = new Date(ms); return new Date(Date.UTC(d.getUTCFullYear(), d.getUTCMonth() + 1, 0)).getUTCDate(); }
  function addMonths(ms, n) {
    var d = new Date(ms), y = d.getUTCFullYear(), m = d.getUTCMonth() + n;
    var last = new Date(Date.UTC(y, m + 1, 0)).getUTCDate();
    return Date.UTC(y, m, Math.min(d.getUTCDate(), last));
  }
  function weekStart(ms) { return day0(ms) - ((dow(ms) - WEEK0 + 7) % 7) * DAY; }
  function clamp(v, a, b) { return Math.max(a, Math.min(b, v)); }
  function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
  function nl2br(s) { return esc(s).replace(/\n/g, '<br>'); }

  function fmtTime(ms) {
    var d = new Date(ms), h = d.getUTCHours(), m = d.getUTCMinutes(), ap = h < 12 ? 'AM' : 'PM';
    h = h % 12 || 12;
    return h + ':' + pad(m) + ' ' + ap;
  }
  function fmtTimeShort(ms) {
    var d = new Date(ms), h = d.getUTCHours(), m = d.getUTCMinutes(), ap = h < 12 ? 'a' : 'p';
    h = h % 12 || 12;
    return h + (m ? ':' + pad(m) : '') + ap;
  }
  function fmtDay(ms, withYear) { return dnum(ms) + ' ' + MONS[new Date(ms).getUTCMonth()] + (withYear ? ' ' + new Date(ms).getUTCFullYear() : ''); }
  function fmtLong(ms) { return DOW[dow(ms)] + ', ' + dnum(ms) + ' ' + MON[new Date(ms).getUTCMonth()]; }
  function hourLabel(h) { return (h % 12 || 12) + ' ' + (h < 12 ? 'AM' : 'PM'); }

  function textOn(hex) {
    var m = /^#?([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$/i.exec(hex || '');
    if (!m) return '#fff';
    var l = (0.299 * parseInt(m[1], 16) + 0.587 * parseInt(m[2], 16) + 0.114 * parseInt(m[3], 16)) / 255;
    return l > 0.64 ? '#1F2937' : '#fff';
  }
  function human(m) {
    if (m <= 0) return '0 minutes';
    if (m % 10080 === 0) { var w = m / 10080; return w + (w === 1 ? ' week' : ' weeks'); }
    if (m % 1440 === 0) { var d = m / 1440; return d + (d === 1 ? ' day' : ' days'); }
    if (m % 60 === 0) { var h = m / 60; return h + (h === 1 ? ' hour' : ' hours'); }
    return m + (m === 1 ? ' minute' : ' minutes');
  }

  function store(k, v) { try { if (v === undefined) return localStorage.getItem(k); localStorage.setItem(k, v); } catch (e) { return null; } }
  function toast(type, msg) {
    if (window.MP && typeof MP.toast === 'function') { MP.toast(type, msg); return; }
    var $w = $('#toastWrap');
    if ($w.length) { var $t = $('<div class="alert alert-' + (type === 'error' ? 'danger' : type) + ' shadow-sm mb-2"></div>').text(msg).appendTo($w); setTimeout(function () { $t.remove(); }, 4000); }
  }
  function isMobile() { return window.matchMedia('(max-width: 767.98px)').matches; }

  var csrf = $('meta[name="csrf-token"]').attr('content');
  function api(method, url, data) {
    return $.ajax({
      url: url, method: method, dataType: 'json', contentType: 'application/json', data: data === undefined ? undefined : JSON.stringify(data),
      headers: Object.assign({ 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, csrf ? { 'X-CSRF-TOKEN': csrf } : {})
    });
  }
  function errMsg(x) {
    var j = x.responseJSON || {};
    if (j.errors) { var k = Object.keys(j.errors)[0]; if (k) return [].concat(j.errors[k])[0]; }
    if (j.message && x.status !== 500) return j.message;
    if (x.status === 419) return 'Your session expired. Reload the page and try again.';
    if (x.status === 0) return 'No connection. Check your internet and try again.';
    if (x.status === 403) return 'You do not have permission to do that.';
    return 'Something went wrong. Please try again.';
  }

  /* ------------------------------------------------------------------ state */
  var NOW0 = P(cfg.now) || Date.now(), T0 = Date.now();
  function nowMs() { return NOW0 + (Date.now() - T0); }

  var S = {
    view: 'week', cur: day0(nowMs()), mini: monthFirst(nowMs()), events: [], range: [0, 0], tok: 0, sup: 0,
    calendars: (cfg.calendars || []).slice(), hidden: {}, byOid: {}, scrollTop: null, groupOpen: {}
  };
  try { var hid = JSON.parse(store('mp_cal_hidden') || '[]'); (hid || []).forEach(function (id) { S.hidden[id] = true; }); } catch (e) { /* ignore */ }
  var savedView = store('mp_cal_view');
  S.view = ['day', '3day', 'week', 'month', 'agenda'].indexOf(savedView) > -1 ? savedView : (isMobile() ? '3day' : 'week');
  fixView();

  function fixView() {
    if (isMobile() && S.view === 'week') S.view = '3day';
    if (!isMobile() && S.view === '3day') S.view = 'week';
  }
  function calById(id) { return S.calendars.filter(function (c) { return c.id === +id; })[0]; }
  function writable() { return S.calendars.filter(function (c) { return c.write; }); }
  function visEvents() { return S.events.filter(function (e) { return !S.hidden[e.cal] && calById(e.cal); }); }
  function colorOf(e) { var c = calById(e.cal); return c ? c.color : '#2563EB'; }
  function styleVars(c) { return '--c:' + c + ';--tc:' + textOn(c); }
  function lastDay(e) { var s = P(e.start), en = P(e.end); return day0(Math.max(s, en - 1)); }
  function isBar(e) { return e.allDay || day0(P(e.start)) !== lastDay(e); }
  function suppressed() { return Date.now() - S.sup < 350; }

  function nDays() { return S.view === 'day' ? 1 : S.view === '3day' ? 3 : 7; }
  function rangeFor() {
    var c = S.cur;
    if (S.view === 'day') return { a: c, b: c + DAY };
    if (S.view === '3day') return { a: c, b: c + 3 * DAY };
    if (S.view === 'week') { var ws = weekStart(c); return { a: ws, b: ws + 7 * DAY }; }
    if (S.view === 'agenda') return { a: c, b: c + 30 * DAY };
    var first = monthFirst(c), gs = weekStart(first), rows = Math.ceil(((first - gs) / DAY + daysIn(first)) / 7);
    return { a: gs, b: gs + rows * 7 * DAY, rows: rows, first: first };
  }
  function rangeKey() { var r = rangeFor(); return S.view + r.a; }

  /* ------------------------------------------------------------------ loading */
  var $stage = $('#calStage');
  function load(opts) {
    opts = opts || {};
    var r = rangeFor(), tok = ++S.tok;
    $stage.addClass('is-loading');
    return api('GET', URLS.feed + '?start=' + ymd(r.a) + '&end=' + ymd(r.b)).done(function (res) {
      if (tok !== S.tok) return;
      S.events = res.events || [];
      S.range = [r.a, r.b];
      $stage.removeClass('is-loading');
      render(true);
      if (opts.done) opts.done();
    }).fail(function (x) {
      if (tok !== S.tok) return;
      $stage.removeClass('is-loading');
      toast('danger', errMsg(x));
    });
  }

  /* ------------------------------------------------------------------ navigation */
  function go(ms, view) {
    closePop();
    S.cur = day0(ms);
    if (view) { S.view = view; store('mp_cal_view', view); }
    S.scrollTop = null;
    render(false);
    load();
  }
  function step(dir) {
    var c = S.cur;
    if (S.view === 'day') c += dir * DAY;
    else if (S.view === '3day') c += dir * 3 * DAY;
    else if (S.view === 'week') c += dir * 7 * DAY;
    else if (S.view === 'month') c = addMonths(c, dir);
    else c += dir * 30 * DAY;
    closePop();
    S.cur = day0(c);
    render(false);
    load();
  }

  function titleText() {
    var r = rangeFor(), m = new Date(S.cur);
    if (S.view === 'month') return MON[m.getUTCMonth()] + ' ' + m.getUTCFullYear();
    if (S.view === 'day') return dnum(S.cur) + ' ' + MON[m.getUTCMonth()] + ' ' + m.getUTCFullYear();
    var a = new Date(r.a), b = new Date(r.b - DAY);
    if (a.getUTCFullYear() !== b.getUTCFullYear()) return fmtDay(r.a, true) + ' – ' + fmtDay(r.b - DAY, true);
    if (a.getUTCMonth() !== b.getUTCMonth()) return fmtDay(r.a) + ' – ' + fmtDay(r.b - DAY) + ' ' + b.getUTCFullYear();
    return dnum(r.a) + ' – ' + dnum(r.b - DAY) + ' ' + MONS[a.getUTCMonth()] + ' ' + a.getUTCFullYear();
  }

  /* ------------------------------------------------------------------ render */
  function render(keepScroll) {
    fixView();
    $app.attr('data-view', S.view);
    $('#calTitle').text(titleText());
    $app.find('.cal-seg[aria-label="View"] > button').each(function () {
      var on = $(this).data('view') === S.view;
      $(this).toggleClass('is-on', on).attr('aria-pressed', on ? 'true' : 'false');
    });
    var $sc = $stage.find('.tg-scroll');
    if ($sc.length && keepScroll !== false) S.scrollTop = $sc.scrollTop();

    S.byOid = {};
    S.events.forEach(function (e) { S.byOid[e.oid] = e; });

    if (S.view === 'month') renderMonth();
    else if (S.view === 'agenda') renderAgenda();
    else renderGrid();

    $('#monthList').toggleClass('d-none', !(S.view === 'month' && isMobile()));
    if (S.view === 'month' && isMobile()) renderMonthList();
    renderMini();
    if (S.noCals) S.noCals = false; else renderCals();
  }

  /* ---- week / day / 3 day ---- */
  function renderGrid() {
    var n = nDays(), r = rangeFor(), start = r.a, today = day0(nowMs()), days = [], i;
    for (i = 0; i < n; i++) days.push(start + i * DAY);
    var evs = visEvents();
    var h = '<div class="tg-scroll" style="--n:' + n + '"><div class="tg-top"><div class="tg-row"><div class="tg-gut"></div>';
    days.forEach(function (d) {
      h += '<div class="tg-hd' + (d === today ? ' is-today' : '') + '"><span class="tg-dow">' + DOWS[dow(d)] + '</span><span class="tg-num" role="button" tabindex="0" data-day="' + ymd(d) + '" aria-label="' + esc(fmtLong(d)) + '">' + dnum(d) + '</span></div>';
    });
    h += '</div><div class="tg-row tg-allday"><div class="tg-gut">All-day</div>';
    days.forEach(function (d) {
      h += '<div class="tg-ad" data-day="' + ymd(d) + '">';
      evs.forEach(function (e) {
        if (!e.allDay) return;
        var s = day0(P(e.start)), l = lastDay(e);
        if (d < s || d > l) return;
        var c = colorOf(e);
        h += '<div class="ev ev-a' + (s < d ? ' cont-l' : '') + (l > d ? ' cont-r' : '') + '" data-oid="' + e.oid + '" style="' + styleVars(c) + '" tabindex="0"><span class="ev-title">' + esc(e.title) + '</span></div>';
      });
      h += '</div>';
    });
    h += '</div></div><div class="tg-body"><div class="tg-gut">';
    for (i = 1; i < 24; i++) h += '<div class="tg-hr" style="top:' + (i * HH) + 'px">' + hourLabel(i) + '</div>';
    h += '</div>';
    days.forEach(function (d) {
      h += '<div class="tg-col' + (d === today ? ' is-today' : '') + '" data-day="' + ymd(d) + '">' + gridEvents(evs, d);
      if (d === today) h += '<div class="tg-now" style="top:' + nowTop() + 'px"><i></i></div>';
      h += '</div>';
    });
    h += '</div></div>';
    $stage.html(h);

    var $s = $stage.find('.tg-scroll');
    if (S.scrollTop == null) {
      var base = (days.indexOf(today) > -1) ? (nowMs() - today) / HOUR - 1 : 7;
      S.scrollTop = Math.max(0, base * HH);
    }
    $s.scrollTop(S.scrollTop);
  }
  function nowTop() { var n = nowMs(); return (n - day0(n)) / HOUR * HH; }

  function gridEvents(evs, d) {
    var segs = [];
    evs.forEach(function (e) {
      if (e.allDay) return;
      var s = P(e.start), en = P(e.end);
      if (en <= s) en = s + 15 * MIN;
      var a = Math.max(s, d), b = Math.min(en, d + DAY);
      if (a < b) segs.push({ e: e, a: a, b: b, s: s, en: en });
    });
    segs.sort(function (x, y) { return x.a - y.a || (y.b - y.a) - (x.b - x.a); });
    var cluster = [], colsEnd = [], cend = 0;
    function flush() {
      var nc = 1;
      cluster.forEach(function (c) { nc = Math.max(nc, c.col + 1); });
      cluster.forEach(function (c) { c.n = nc; });
      cluster = []; colsEnd = [];
    }
    segs.forEach(function (sg) {
      if (cluster.length && sg.a >= cend) flush();
      var c = 0;
      while (colsEnd[c] !== undefined && colsEnd[c] > sg.a) c++;
      colsEnd[c] = sg.b; sg.col = c; cluster.push(sg); cend = Math.max(cend, sg.b);
    });
    flush();
    var h = '';
    segs.forEach(function (sg) {
      var e = sg.e, c = colorOf(e), top = (sg.a - d) / HOUR * HH, ht = Math.max((sg.b - sg.a) / HOUR * HH - 1, 18);
      var short = ht < 38, left = sg.col / sg.n * 100, w = 100 / sg.n;
      var editable = e.edit && e.repeat === 'none';
      h += '<div class="te' + (short ? ' is-short' : '') + '" data-oid="' + e.oid + '" tabindex="0" style="top:' + top + 'px;height:' + ht + 'px;left:calc(' + left + '% + 1px);width:calc(' + w + '% - 3px);' + styleVars(c) + '">';
      if (short) h += '<div class="te-t">' + esc(e.title) + ', ' + fmtTime(sg.s) + '</div>';
      else {
        h += '<div class="te-t">' + esc(e.title) + '</div><div class="te-s">' + fmtTime(sg.s) + ' – ' + fmtTime(sg.en) + '</div>';
        if (e.loc && ht > 64) h += '<div class="te-l"><i class="bi bi-geo-alt-fill me-1"></i>' + esc(e.loc) + '</div>';
      }
      if (editable && sg.en <= d + DAY && ht >= 28) h += '<div class="te-rz" title="Drag to change the end time"></div>';
      h += '</div>';
    });
    return h;
  }

  /* ---- month ---- */
  function renderMonth() {
    var r = rangeFor(), today = day0(nowMs()), evs = visEvents(), byDay = {};
    evs.forEach(function (e) {
      var l = lastDay(e);
      for (var d = day0(P(e.start)); d <= l; d += DAY) {
        if (d < r.a || d >= r.b) continue;
        (byDay[d] = byDay[d] || []).push(e);
      }
    });
    var h = '<div class="mv" style="--rows:' + r.rows + '"><div class="mv-head">';
    for (var k = 0; k < 7; k++) h += '<div>' + DOWS[(WEEK0 + k) % 7] + '</div>';
    h += '</div><div class="mv-grid">';
    var curM = new Date(r.first).getUTCMonth();
    for (var d = r.a; d < r.b; d += DAY) {
      var list = (byDay[d] || []).slice().sort(function (x, y) {
        var bx = isBar(x) ? 0 : 1, by = isBar(y) ? 0 : 1;
        return bx - by || P(x.start) - P(y.start);
      });
      var dm = new Date(d).getUTCMonth();
      h += '<div class="mc' + (dm !== curM ? ' is-other' : '') + (d === today ? ' is-today' : '') + (d === S.cur ? ' is-sel' : '') + '" data-day="' + ymd(d) + '">';
      h += '<div class="mc-n"><span class="mc-d" data-day="' + ymd(d) + '" role="button" aria-label="' + esc(fmtLong(d)) + '">' + (dnum(d) === 1 ? dnum(d) + ' ' + MONS[dm] : dnum(d)) + '</span></div><div class="mc-ev">';
      list.forEach(function (e) {
        var c = colorOf(e);
        if (isBar(e)) {
          h += '<div class="ev ev-a' + (day0(P(e.start)) < d ? ' cont-l' : '') + (lastDay(e) > d ? ' cont-r' : '') + '" data-oid="' + e.oid + '" data-day="' + ymd(d) + '" style="' + styleVars(c) + '" tabindex="0"><span class="ev-title">' + esc(e.title) + '</span></div>';
        } else {
          h += '<div class="ev ev-t" data-oid="' + e.oid + '" data-day="' + ymd(d) + '" style="' + styleVars(c) + '" tabindex="0"><i class="ev-dot"></i><span class="ev-time">' + fmtTimeShort(P(e.start)) + '</span><span class="ev-title">' + esc(e.title) + '</span></div>';
        }
      });
      h += '</div><div class="mc-dots">';
      list.slice(0, 3).forEach(function (e) { h += '<i style="background:' + colorOf(e) + '"></i>'; });
      h += '</div></div>';
    }
    h += '</div></div>';
    $stage.html(h);
    fitMonth();
  }
  function fitMonth() {
    if (S.view !== 'month' || isMobile()) return;
    $stage.find('.mc').each(function () {
      var $c = $(this), $e = $c.find('.mc-ev'), kids = $e.children('.ev');
      $e.children('.mc-more').remove();
      kids.show();
      var cap = Math.floor((this.clientHeight - 26 - 4) / 22);
      if (kids.length > cap) {
        var keep = Math.max(cap - 1, 0);
        kids.slice(keep).hide();
        $e.append('<button type="button" class="mc-more" data-day="' + $c.data('day') + '">+' + (kids.length - keep) + ' more</button>');
      }
    });
  }
  function renderMonthList() {
    var d = S.cur, evs = visEvents().filter(function (e) { return day0(P(e.start)) <= d && lastDay(e) >= d; });
    evs.sort(function (x, y) { return (isBar(y) ? 1 : 0) - (isBar(x) ? 1 : 0) || P(x.start) - P(y.start); });
    var h = '<div class="ml-h"><span>' + esc(fmtLong(d)) + '</span><span class="caption">' + (evs.length ? evs.length + (evs.length === 1 ? ' event' : ' events') : '') + '</span></div>';
    if (!evs.length) h += '<div class="caption py-3 text-center">Nothing scheduled</div>';
    evs.forEach(function (e) { h += agendaRow(e, d); });
    $('#monthList').html(h);
  }

  /* ---- agenda ---- */
  function agendaRow(e, d) {
    var c = colorOf(e), cal = calById(e.cal), s = P(e.start), en = P(e.end), t;
    if (e.allDay) t = 'All day';
    else if (day0(s) === d && lastDay(e) === d) t = fmtTime(s) + ' – ' + fmtTime(en);
    else if (day0(s) === d) t = fmtTime(s) + ' →';
    else if (lastDay(e) === d) t = 'Until ' + fmtTime(en);
    else t = 'All day';
    return '<div class="ag-ev" data-oid="' + e.oid + '" tabindex="0" style="--c:' + c + '"><span class="ag-time">' + esc(t) + '</span><i class="ag-dot"></i><span class="ag-t">' + esc(e.title) + '</span><span class="ag-cal">' + esc(cal ? cal.name : '') + '</span></div>';
  }
  function renderAgenda() {
    var r = rangeFor(), today = day0(nowMs()), evs = visEvents(), byDay = {}, days = [];
    evs.forEach(function (e) {
      var l = lastDay(e);
      for (var d = Math.max(day0(P(e.start)), r.a); d <= l && d < r.b; d += DAY) (byDay[d] = byDay[d] || []).push(e);
    });
    Object.keys(byDay).map(Number).sort(function (a, b) { return a - b; }).forEach(function (d) { days.push(d); });
    if (!days.length) {
      $stage.html('<div class="cal-empty"><i class="bi bi-calendar2-x"></i><div><strong>Nothing scheduled</strong><div class="caption">No events in the next 30 days.</div></div><button type="button" class="btn btn-primary js-new-event"><i class="bi bi-plus-lg me-1"></i>Create event</button></div>');
      return;
    }
    var h = '<div class="ag">';
    days.forEach(function (d) {
      var list = byDay[d].sort(function (x, y) { return (isBar(y) ? 1 : 0) - (isBar(x) ? 1 : 0) || P(x.start) - P(y.start); });
      h += '<div class="ag-day' + (d === today ? ' is-today' : '') + '"><div class="ag-date"><span class="ag-dow">' + DOWS[dow(d)] + '</span><span class="ag-num">' + dnum(d) + '</span><span class="ag-mon">' + MONS[new Date(d).getUTCMonth()] + '</span></div><div class="ag-list">';
      list.forEach(function (e) { h += agendaRow(e, d); });
      h += '</div></div>';
    });
    $stage.html(h + '</div>');
  }

  /* ---- mini calendar ---- */
  function renderMini() {
    var m = S.mini, today = day0(nowMs()), r = rangeFor(), gs = weekStart(m), i;
    var h = '<div class="mini-head"><span class="mini-t">' + MON[new Date(m).getUTCMonth()] + ' ' + new Date(m).getUTCFullYear() + '</span><span><button type="button" class="btn-ico js-mini-prev" aria-label="Previous month"><i class="bi bi-chevron-left"></i></button><button type="button" class="btn-ico js-mini-next" aria-label="Next month"><i class="bi bi-chevron-right"></i></button></span></div><div class="mini-grid">';
    for (i = 0; i < 7; i++) h += '<div class="mini-dow">' + DOW[(WEEK0 + i) % 7].charAt(0) + '</div>';
    for (i = 0; i < 42; i++) {
      var d = gs + i * DAY, other = new Date(d).getUTCMonth() !== new Date(m).getUTCMonth();
      var inRange = S.view !== 'month' && d >= r.a && d < r.b;
      h += '<button type="button" class="md' + (other ? ' is-other' : '') + (d === today ? ' is-today' : '') + (d === S.cur ? ' is-sel' : '') + (inRange ? ' in-range' : '') + '" data-day="' + ymd(d) + '" aria-label="' + esc(fmtLong(d)) + '">' + dnum(d) + '</button>';
    }
    $('#miniCal').html(h + '</div>');
  }

  /* ---- calendar list ---- */
  function renderCals() {
    var groups = [], map = {};
    S.calendars.forEach(function (c) {
      if (!map[c.group]) { map[c.group] = { name: c.group, items: [] }; groups.push(map[c.group]); }
      map[c.group].items.push(c);
    });
    var h = '';
    groups.forEach(function (g, gi) {
      var open = S.groupOpen[g.name] !== false;
      h += '<div class="cl-g"><button type="button" class="cl-gh" data-g="' + esc(g.name) + '" aria-expanded="' + open + '"><i class="bi bi-chevron-down"></i><span>' + esc(g.name) + '</span></button><div class="cl-items"' + (open ? '' : ' hidden') + '>';
      g.items.forEach(function (c) {
        h += '<div class="cl-row"><label><input type="checkbox" class="cl-chk" data-id="' + c.id + '" style="--c:' + c.color + '"' + (S.hidden[c.id] ? '' : ' checked') + '><span class="cl-name" title="' + esc(c.desc || c.name) + '">' + esc(c.name) + '</span>' + (c.write ? '' : '<i class="bi bi-lock-fill cl-lock" title="View only"></i>') + '</label>';
        if (c.manage) h += '<button type="button" class="btn-ico cl-edit" data-id="' + c.id + '" title="Edit calendar" aria-label="Edit ' + esc(c.name) + '"><i class="bi bi-pencil"></i></button>';
        h += '</div>';
      });
      h += '</div></div>';
    });
    $('#calList').html(h);
  }
  function saveHidden() { store('mp_cal_hidden', JSON.stringify(Object.keys(S.hidden).filter(function (k) { return S.hidden[k]; }).map(Number))); }

  /* ------------------------------------------------------------------ popover */
  var $pop = $('#evPop'), $back = $('#popBack'), popEv = null;
  function repeatLabel(e) {
    var s = P(e.mstart || e.start), t;
    switch (e.repeat) {
      case 'daily': t = 'Every day'; break;
      case 'weekdays': t = 'Every weekday (Monday to Friday)'; break;
      case 'weekly': t = 'Every week on ' + DOW[dow(s)]; break;
      case 'monthly': t = 'Every month on day ' + dnum(s); break;
      case 'yearly': t = 'Every year on ' + dnum(s) + ' ' + MON[new Date(s).getUTCMonth()]; break;
      default: return '';
    }
    return e.until ? t + ', until ' + fmtDay(P(String(e.until).slice(0, 10)), true) : t;
  }
  function whenText(e) {
    var s = P(e.start), en = P(e.end), ls = day0(s), le = lastDay(e);
    if (e.allDay) return ls === le ? fmtLong(s) : fmtDay(s) + ' – ' + fmtDay(le, true);
    if (ls === le) return fmtLong(s) + ' · ' + fmtTime(s) + ' – ' + fmtTime(en);
    return fmtDay(s) + ', ' + fmtTime(s) + ' – ' + fmtDay(en) + ', ' + fmtTime(en);
  }
  function remText(e) {
    return e.rem.map(function (m) {
      if (e.allDay) return m === 0 ? 'At 9:00 AM on the day' : human(m) + ' before 9:00 AM';
      return m === 0 ? 'At time of event' : human(m) + ' before';
    }).join(', ');
  }
  function openPop(e, anchor) {
    popEv = e;
    var cal = calById(e.cal), c = colorOf(e), h = '<div class="pop-bar">';
    if (e.edit) h += '<button type="button" class="btn-ico js-pop-edit" title="Edit" aria-label="Edit event"><i class="bi bi-pencil"></i></button><button type="button" class="btn-ico js-pop-del" title="Delete" aria-label="Delete event"><i class="bi bi-trash"></i></button>';
    h += '<button type="button" class="btn-ico js-pop-x" title="Close" aria-label="Close"><i class="bi bi-x-lg"></i></button></div><div class="pop-body">';
    h += '<div class="pop-title"><i class="pop-swatch" style="--c:' + c + '"></i><span>' + esc(e.title) + '</span></div><div class="pop-when">' + esc(whenText(e)) + '</div>';
    if (e.repeat !== 'none') h += '<div class="pop-row"><i class="bi bi-arrow-repeat"></i><div>' + esc(repeatLabel(e)) + '</div></div>';
    if (e.loc) h += '<div class="pop-row"><i class="bi bi-geo-alt"></i><div><a href="https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(e.loc) + '" target="_blank" rel="noopener">' + esc(e.loc) + '</a></div></div>';
    if (e.lead) h += '<div class="pop-row"><i class="bi bi-person"></i><div><a href="' + esc(URLS.leadPage || '#') + '?q=' + encodeURIComponent(e.lead.name) + '">' + esc(e.lead.name) + '</a></div></div>';
    if (e.desc) h += '<div class="pop-row"><i class="bi bi-text-left"></i><div>' + nl2br(e.desc) + '</div></div>';
    if (e.rem.length) h += '<div class="pop-row"><i class="bi bi-bell"></i><div>' + esc(remText(e)) + '<div class="caption">' + (e.notify === 'everyone' ? 'Notifies everyone on this calendar' : 'Notifies only the person who created it') + '</div></div></div>';
    h += '<div class="pop-row"><i class="bi bi-calendar3"></i><div>' + esc(cal ? cal.name : '') + (cal ? ' <span class="caption">· ' + esc(cal.group) + (cal.write ? '' : ' · view only') + '</span>' : '') + '</div></div></div>';
    h += '<div class="pop-del d-none"></div>';
    $pop.html(h).removeClass('d-none').css({ visibility: 'hidden', left: 0, top: 0 });
    $back.removeClass('d-none');
    if (!isMobile()) {
      var w = $pop.outerWidth(), ph = $pop.outerHeight(), vw = $(window).width(), vh = $(window).height(), x, y;
      if (anchor && anchor.length && anchor[0].getBoundingClientRect) {
        var r = anchor[0].getBoundingClientRect();
        x = r.right + 8; if (x + w > vw - 12) x = r.left - w - 8; if (x < 12) x = clamp(r.left, 12, vw - w - 12);
        y = r.top - 8;
      } else { x = (vw - w) / 2; y = 90; }
      $pop.css({ left: x, top: clamp(y, 12, Math.max(12, vh - ph - 12)) });
    }
    $pop.css('visibility', 'visible');
    $pop.find('.js-pop-x').trigger('focus');
  }
  function closePop() { popEv = null; $pop.addClass('d-none').empty(); $back.addClass('d-none'); }

  function askDelete(e, $host, after) {
    var rep = e.repeat !== 'none', h;
    if (rep) h = '<span class="w-100 fw-semibold">Delete repeating event</span><button type="button" class="btn btn-sm btn-light border js-d-one">Only this one</button><button type="button" class="btn btn-sm btn-danger js-d-all">All events</button><button type="button" class="btn btn-sm btn-light border js-d-no">Cancel</button>';
    else h = '<span class="w-100 fw-semibold">Delete this event?</span><button type="button" class="btn btn-sm btn-danger js-d-all">Delete</button><button type="button" class="btn btn-sm btn-light border js-d-no">Cancel</button>';
    $host.removeClass('d-none').html(h);
    function done(occ) {
      $host.find('button').prop('disabled', true);
      api('DELETE', URLS.events + '/' + e.id, occ ? { occurrence: e.start.slice(0, 10) } : {}).done(function (r) {
        toast('success', r.message || 'Event deleted.'); after(true); load();
      }).fail(function (x) { toast('danger', errMsg(x)); $host.find('button').prop('disabled', false); });
    }
    $host.off('click.del')
      .on('click.del', '.js-d-one', function () { done(true); })
      .on('click.del', '.js-d-all', function () { done(false); })
      .on('click.del', '.js-d-no', function () { $host.addClass('d-none').empty(); after(false); });
  }

  /* ------------------------------------------------------------------ event editor */
  var evModalEl = document.getElementById('evModal'), evModal = null;
  var EV = { id: null, edit: null, lead: null, prevStart: NaN, busy: false };
  function modalOf(el) { return window.bootstrap ? bootstrap.Modal.getOrCreateInstance(el) : null; }

  function clearErrors($f) {
    $f.find('.is-invalid').removeClass('is-invalid');
    $f.find('.invalid-feedback').removeClass('d-block').filter(':not([data-for])').text('');
    $f.find('.invalid-feedback[data-for]').text('');
    $f.find('.cm-error').addClass('d-none').text('');
  }
  function setFieldError($f, name, msg, $input) {
    var $fb = $f.find('.invalid-feedback[data-for="' + name + '"]');
    if ($fb.length) { $fb.text(msg).addClass('d-block'); return true; }
    if ($input && $input.length) { $input.addClass('is-invalid'); $input.nextAll('.invalid-feedback').first().text(msg); return true; }
    return false;
  }
  function showError($f, msg) { $f.find('.cm-error').removeClass('d-none').text(msg); }
  function busy($f, on) { $f.find('button[type=submit]').prop('disabled', on).find('.spinner-border').toggleClass('d-none', !on); }

  var UNITS = [[10080, 'weeks'], [1440, 'days'], [60, 'hours'], [1, 'minutes']];
  function remRow(min) {
    var u = 1, n = min;
    for (var i = 0; i < UNITS.length; i++) if (min > 0 && min % UNITS[i][0] === 0) { u = UNITS[i][0]; n = min / u; break; }
    var $r = $('<div class="rm-r"><input type="number" min="0" max="9999" inputmode="numeric" class="form-control form-control-sm rm-n" aria-label="Amount"><select class="form-select form-select-sm rm-u" aria-label="Unit"></select><span class="rm-b">before</span><button type="button" class="btn-ico js-rem-x" aria-label="Remove notification"><i class="bi bi-x-lg"></i></button></div>');
    UNITS.slice().reverse().forEach(function (x) { $r.find('.rm-u').append('<option value="' + x[0] + '">' + x[1] + '</option>'); });
    $r.find('.rm-n').val(n); $r.find('.rm-u').val(u);
    return $r;
  }
  function remSync() {
    var n = $('#evRems .rm-r').length;
    $('#evForm .js-rem-add').toggleClass('d-none', n >= 5);
    var all = $('#evAllDay').prop('checked'), txt = '';
    if (n && all) txt = 'All-day events notify at 9:00 AM on the day (0 = at 9:00 AM).';
    var st = window.MPPush ? MPPush.status() : null;
    if (n && st && st.supported && !st.enabled) txt += (txt ? ' ' : '') + 'Turn on notifications with the bell icon to get these on this device.';
    $('#evRemHint').text(txt);
  }
  function fillRepeat() {
    var s = P($('#evSD').val() || ymd(S.cur)), $r = $('#evRepeat'), v = $r.val() || 'none';
    var o = [['none', 'Does not repeat'], ['daily', 'Every day'], ['weekdays', 'Every weekday (Mon–Fri)'], ['weekly', 'Every week on ' + DOW[dow(s)]], ['monthly', 'Every month on day ' + dnum(s)], ['yearly', 'Every year on ' + dnum(s) + ' ' + MONS[new Date(s).getUTCMonth()]]];
    $r.empty(); o.forEach(function (x) { $r.append($('<option>').val(x[0]).text(x[1])); });
    $r.val(v);
  }
  function fillCalSelect(sel) {
    var $c = $('#evCal').empty(), groups = {}, order = [];
    writable().forEach(function (c) {
      if (!groups[c.group]) { groups[c.group] = $('<optgroup>').attr('label', c.group); order.push(c.group); }
      groups[c.group].append($('<option>').val(c.id).text(c.name));
    });
    order.forEach(function (g) { $c.append(groups[g]); });
    if (sel && $c.find('option[value="' + sel + '"]').length) $c.val(sel);
    var cal = calById($c.val());
    $('#evCalDot').css('background', cal ? cal.color : '#2563EB');
  }
  function setLead(l) {
    EV.lead = l;
    $('#evLeadChip').toggleClass('d-none', !l).find('span').text(l ? l.name : '');
    $('#evLeadQ').toggleClass('d-none', !!l).val('');
    $('#evLeadRes').addClass('d-none').empty();
  }
  function setAllDay(on) {
    $('#evAllDay').prop('checked', on);
    $('#evForm .ev-time-only').toggleClass('d-none', on);
    remSync();
  }
  function readStart() { var d = $('#evSD').val(), t = $('#evAllDay').prop('checked') ? '00:00' : ($('#evST').val() || '00:00'); return d ? P(d + 'T' + t) : NaN; }
  function readEnd() { var d = $('#evED').val(), t = $('#evAllDay').prop('checked') ? '00:00' : ($('#evET').val() || '00:00'); return d ? P(d + 'T' + t) : NaN; }

  function openEvent(e, def) {
    if (!writable().length) { toast('warning', 'You do not have a calendar you can add events to.'); return; }
    closePop();
    var $f = $('#evForm'); clearErrors($f);
    EV.id = e ? e.id : null; EV.edit = e || null; EV.busy = false; busy($f, false);
    $('#evDelChoice').addClass('d-none').empty();
    var s, en, allDay = false, repeat = 'none', until = '', rem = [10], notify = 'me', calId, title = '', loc = '', desc = '', lead = null;
    if (e) {
      repeat = e.repeat; allDay = e.allDay;
      s = P(repeat !== 'none' ? e.mstart : e.start); en = P(repeat !== 'none' ? e.mend : e.end);
      until = e.until ? String(e.until).slice(0, 10) : ''; rem = e.rem.slice(); notify = e.notify || 'me'; calId = e.cal;
      title = e.title === '(No title)' ? '' : e.title; loc = e.loc || ''; desc = e.desc || ''; lead = e.lead;
    } else {
      def = def || {};
      s = def.start != null ? def.start : day0(S.cur) + 9 * HOUR;
      en = def.end != null ? def.end : s + HOUR;
      allDay = !!def.allDay;
      var last = store('mp_cal_last'); calId = last && calById(last) && calById(last).write ? +last : (writable()[0] || {}).id;
      var vis = writable().filter(function (c) { return !S.hidden[c.id]; });
      if (!(last && calById(last) && calById(last).write && !S.hidden[last]) && vis.length) calId = vis[0].id;
    }
    $('#evModalTitle').text(e ? 'Edit event' : 'New event');
    $('#evTitle').val(title); $('#evLoc').val(loc); $('#evDesc').val(desc);
    $('#evSD').val(ymd(s)); $('#evED').val(ymd(allDay ? day0(en) : en));
    $('#evST').val(allDay ? '09:00' : hm(s)); $('#evET').val(allDay ? '10:00' : hm(en));
    fillRepeat(); $('#evRepeat').val(repeat);
    $('#evUntil').val(until); $('#evUntilWrap').toggleClass('d-none', repeat === 'none');
    $('#evSeriesNote').toggleClass('d-none', !(e && repeat !== 'none'));
    fillCalSelect(calId);
    setLead(lead);
    $('#evRems').empty(); rem.forEach(function (m) { $('#evRems').append(remRow(m)); });
    $f.find('input[name=notify][value="' + notify + '"]').prop('checked', true);
    setAllDay(allDay);
    EV.prevStart = readStart();
    $f.find('.js-ev-del').toggleClass('d-none', !(e && e.edit));
    evModal = modalOf(evModalEl); evModal && evModal.show();
  }

  function collectEvent() {
    var $f = $('#evForm'), allDay = $('#evAllDay').prop('checked'), sd = $('#evSD').val(), ed = $('#evED').val();
    var start = sd + 'T' + (allDay ? '00:00' : ($('#evST').val() || '00:00'));
    var end = ed + 'T' + (allDay ? '23:59' : ($('#evET').val() || '00:00'));
    var rem = [], bad = false;
    $('#evRems .rm-r').each(function () {
      var n = parseInt($(this).find('.rm-n').val(), 10), m = n * parseInt($(this).find('.rm-u').val(), 10);
      if (isNaN(m) || n < 0) return;
      if (m > 40320) bad = true;
      if (rem.indexOf(m) < 0) rem.push(m);
    });
    return {
      bad: bad,
      data: {
        calendar_id: +$('#evCal').val(), title: $.trim($('#evTitle').val()), start: start, end: end, all_day: allDay,
        repeat: $('#evRepeat').val(), repeat_until: $('#evRepeat').val() === 'none' ? null : ($('#evUntil').val() || null),
        location: $.trim($('#evLoc').val()), description: $.trim($('#evDesc').val()), lead_id: EV.lead ? EV.lead.id : null,
        reminders: rem.sort(function (a, b) { return a - b; }), notify: $f.find('input[name=notify]:checked').val() || 'me'
      }
    };
  }

  /* ------------------------------------------------------------------ calendar editor */
  var calModalEl = document.getElementById('calModal'), calModal = null, CM = { id: null, color: (cfg.palette || ['#2563EB'])[0] };
  function paintSwatches() {
    var pal = cfg.palette || [], h = '', isCustom = pal.map(function (p) { return p.toLowerCase(); }).indexOf(CM.color.toLowerCase()) < 0;
    pal.forEach(function (c) { h += '<button type="button" class="sw' + (c.toLowerCase() === CM.color.toLowerCase() ? ' is-on' : '') + '" data-color="' + c + '" style="--c:' + c + '" aria-label="Colour ' + c + '">' + (c.toLowerCase() === CM.color.toLowerCase() ? '<i class="bi bi-check-lg"></i>' : '') + '</button>'; });
    h += '<label class="sw sw-custom' + (isCustom ? ' is-on' : '') + '" title="Custom colour" style="--c:' + CM.color + '"><input type="color" value="' + CM.color + '" aria-label="Custom colour"></label>';
    $('#calSwatches').html(h);
  }
  function calOwnerNote() {
    var admin = cfg.me && cfg.me.admin, v = $('#calOwner').val(), t = '';
    if (CM.id) t = 'A calendar cannot be moved to another branch.';
    else if (!admin) t = 'Everyone in your branch can add events to it.';
    else if (v === '' || v == null) t = 'Head office calendar. Company admins can add events; turn on sharing so branches can see it.';
    else t = 'Everyone in this branch can add events to it.';
    $('#calOwnerNote').text(t);
    $('#calSharedWrap').toggleClass('d-none', !(admin && (v === '' || v == null)));
  }
  function openCalendar(c) {
    var $f = $('#calForm'); clearErrors($f); busy($f, false);
    CM.id = c ? c.id : null; CM.color = c ? c.color : (cfg.palette || ['#2563EB'])[0];
    $('#calModalTitle').text(c ? 'Edit calendar' : 'New calendar');
    $('#calName').val(c ? c.name : ''); $('#calDesc').val(c ? (c.desc || '') : '');
    var $o = $('#calOwner').empty(), owners = cfg.owners || [];
    owners.forEach(function (o) { $o.append($('<option>').val(o.id == null ? '' : o.id).text(o.name)); });
    if (c) $o.val(c.branch_id == null ? '' : c.branch_id);
    $o.prop('disabled', !!c || owners.length < 2);
    $('#calShared').prop('checked', !!(c && c.shared));
    $('#calForm .js-cal-del').removeClass('d-none').toggleClass('d-none', !(c && c.manage)).text('').html('<i class="bi bi-trash me-1"></i>Delete').removeData('armed');
    paintSwatches(); calOwnerNote();
    calModal = modalOf(calModalEl); calModal && calModal.show();
  }

  /* ------------------------------------------------------------------ push UI (bell) */
  function renderNotif() {
    var st = window.MPPush ? MPPush.status() : { supported: false, configured: false, permission: 'default', enabled: false };
    var h = '', on = !!(st.supported && st.enabled), dot = '';
    if (!st.supported) {
      dot = '<i class="cn-dot is-warn"></i>';
      h = '<div class="cn-state">' + dot + 'Not available here</div><div class="caption">' + (st.needsInstall
        ? 'On iPhone, open this CRM in Safari, tap <b>Share → Add to Home Screen</b>, then open it from the home screen and turn notifications on.'
        : 'This browser cannot receive notifications. Try Chrome, Edge or Firefox.') + '</div>';
    } else if (!st.configured) {
      dot = '<i class="cn-dot is-warn"></i>';
      h = '<div class="cn-state">' + dot + 'Phone alerts not set up</div><div class="caption">Firebase is not configured on the server yet, so reminders only pop up while the CRM is open. Ask your admin to add the FIREBASE values.</div>';
    } else if (st.permission === 'denied') {
      dot = '<i class="cn-dot is-warn"></i>';
      h = '<div class="cn-state">' + dot + 'Blocked in this browser</div><div class="caption">Allow notifications for this site in your browser settings (lock icon next to the address), then reload.</div>';
    } else if (on) {
      dot = '<i class="cn-dot is-on"></i>';
      h = '<div class="cn-state">' + dot + 'On for this device</div><div class="caption mb-3">Event reminders will reach this browser even when the CRM is closed.</div><div class="d-flex gap-2"><button type="button" class="btn btn-sm btn-light border js-push-test"><i class="bi bi-send me-1"></i>Send test</button><button type="button" class="btn btn-sm btn-outline-danger js-push-off">Turn off</button></div>';
    } else {
      dot = '<i class="cn-dot"></i>';
      h = '<div class="cn-state">' + dot + 'Off for this device</div><div class="caption mb-3">Get event reminders as notifications on this device, even when the CRM is closed.</div><button type="button" class="btn btn-sm btn-primary js-push-on"><i class="bi bi-bell me-1"></i>Turn on notifications</button>';
    }
    h += '<hr class="my-3"><div class="caption"><i class="bi bi-phone me-1"></i>Each device is set up on its own. Open the CRM on your phone and turn it on there too.</div>';
    $('#calNotifMenu').html(h);
    $('#calNotifBtn').toggleClass('is-on', on).find('i').attr('class', on ? 'bi bi-bell-fill' : 'bi bi-bell');

    var dismissed = parseInt(store('mp_cal_nudge_x') || '0', 10), recent = dismissed && (Date.now() - dismissed) < 7 * DAY;
    $('#calNudge').toggleClass('d-none', !(st.supported && st.configured && st.permission === 'default' && !st.enabled && !recent));
    remSync();
  }
  function pushAct(btn, fn, okType) {
    var $b = $(btn).prop('disabled', true);
    if (!window.MPPush) { toast('warning', 'Notifications are not available on this page.'); $b.prop('disabled', false); return; }
    Promise.resolve(fn()).then(function (r) {
      r = r || {};
      if (r.message) toast(r.ok ? (okType || 'success') : 'warning', r.message);
    }).catch(function (e) {
      toast('danger', (e && e.message) || 'Could not change notifications.');
    }).then(function () { $b.prop('disabled', false); renderNotif(); });
  }

  /* ------------------------------------------------------------------ interactions */
  function occOf($el) { return S.byOid[$el.closest('[data-oid]').attr('data-oid')]; }

  /* toolbar */
  $app.on('click', '.js-today', function () { go(nowMs(), null); });
  $app.on('click', '.js-prev', function () { step(-1); });
  $app.on('click', '.js-next', function () { step(1); });
  $app.on('click', '.cal-seg[aria-label="View"] > button', function () { var v = $(this).data('view'); if (v !== S.view) go(S.cur, v); });
  $app.on('click', '.js-new-event', function () { newEventAt(); });
  $app.on('click', '.js-new-cal', function () { offcanvasHide(); openCalendar(null); });
  $app.on('click', '.js-nudge-x', function () { store('mp_cal_nudge_x', String(Date.now())); $('#calNudge').addClass('d-none'); });
  $(document).on('click', '#calApp .js-push-on', function () { pushAct(this, function () { return MPPush.enable(); }); });
  $(document).on('click', '#calApp .js-push-off', function () { pushAct(this, function () { return MPPush.disable(); }, 'info'); });
  $(document).on('click', '#calApp .js-push-test', function () { pushAct(this, function () { return MPPush.test(); }); });
  document.addEventListener('mppush:change', renderNotif);

  function offcanvasHide() {
    var el = document.getElementById('calSide');
    if (el && window.bootstrap && el.classList.contains('show')) { var oc = bootstrap.Offcanvas.getInstance(el); oc && oc.hide(); }
  }
  function newEventAt(dayMs, startMs, endMs, allDay) {
    offcanvasHide();
    var d = dayMs != null ? dayMs : day0(S.cur);
    var today = day0(nowMs());
    var s = startMs;
    if (s == null) {
      s = d + 9 * HOUR;
      if (d === today) { var nm = nowMs(); s = day0(nm) + (Math.floor((nm - day0(nm)) / HOUR) + 1) * HOUR; if (s >= d + DAY) s = d + 9 * HOUR; }
    }
    openEvent(null, { start: s, end: endMs != null ? endMs : s + HOUR, allDay: !!allDay });
  }

  /* mini calendar + calendar list */
  $app.on('click', '.md', function () { var d = P($(this).data('day')); S.mini = monthFirst(d); go(d, null); if (isMobile()) offcanvasHide(); });
  $app.on('click', '.js-mini-prev', function () { S.mini = addMonths(S.mini, -1); renderMini(); });
  $app.on('click', '.js-mini-next', function () { S.mini = addMonths(S.mini, 1); renderMini(); });
  $app.on('click', '.cl-gh', function () { var g = $(this).data('g'); S.groupOpen[g] = S.groupOpen[g] === false; renderCals(); });
  $app.on('change', '.cl-chk', function () {
    var id = +$(this).data('id'); if (this.checked) delete S.hidden[id]; else S.hidden[id] = true;
    saveHidden(); closePop(); S.noCals = true; render(true);
  });
  $app.on('click', '.cl-edit', function () { var c = calById($(this).data('id')); if (c) { offcanvasHide(); openCalendar(c); } });

  /* open an event */
  $app.on('click', '.te, .ev, .ag-ev', function (ev) {
    if (suppressed()) return;
    ev.stopPropagation();
    var e = occOf($(this)); if (e) openPop(e, $(this));
  });
  $app.on('keydown', '.te, .ev, .ag-ev', function (ev) { if (ev.key === 'Enter' || ev.key === ' ') { ev.preventDefault(); $(this).trigger('click'); } });
  $app.on('click', '.tg-num, .mc-d', function (ev) {
    if (suppressed()) return;
    ev.stopPropagation();
    var d = P($(this).data('day'));
    if (S.view === 'month' && isMobile()) { S.cur = d; render(true); return; }
    go(d, 'day');
  });
  $app.on('keydown', '.tg-num', function (ev) { if (ev.key === 'Enter' || ev.key === ' ') { ev.preventDefault(); $(this).trigger('click'); } });
  $app.on('click', '.mc-more', function (ev) { ev.stopPropagation(); go(P($(this).data('day')), 'day'); });
  $app.on('click', '.mc', function () {
    if (suppressed()) return;
    var d = P($(this).data('day'));
    if (isMobile()) { S.cur = d; if (new Date(d).getUTCMonth() !== new Date(S.mini).getUTCMonth()) S.mini = monthFirst(d); render(true); }
    else newEventAt(d);
  });
  $app.on('click', '.tg-ad', function (ev) {
    if (suppressed() || $(ev.target).closest('.ev').length) return;
    var d = P($(this).data('day')); newEventAt(d, d, d, true);
  });

  /* popover buttons */
  $(document).on('click', '#popBack, #evPop .js-pop-x', function () { closePop(); });
  $(document).on('click', '#evPop .js-pop-edit', function () { var e = popEv; closePop(); if (e) openEvent(e); });
  $(document).on('click', '#evPop .js-pop-del', function () { if (popEv) askDelete(popEv, $pop.find('.pop-del'), function (gone) { if (gone) closePop(); }); });
  $(document).on('keydown', function (ev) {
    if (ev.key === 'Escape' && popEv) { closePop(); return; }
    var t = ev.target, tag = (t && t.tagName || '').toLowerCase();
    if (ev.ctrlKey || ev.metaKey || ev.altKey || tag === 'input' || tag === 'textarea' || tag === 'select' || (t && t.isContentEditable) || $('.modal.show').length) return;
    var k = ev.key.toLowerCase();
    if (k === 't') go(nowMs(), null);
    else if (k === 'j' || k === 'n') step(1);
    else if (k === 'k' || k === 'p') step(-1);
    else if (k === 'd') go(S.cur, 'day');
    else if (k === 'w') go(S.cur, isMobile() ? '3day' : 'week');
    else if (k === 'm') go(S.cur, 'month');
    else if (k === 'a') go(S.cur, 'agenda');
    else if (k === 'c') { ev.preventDefault(); newEventAt(); }
  });

  /* ---------- drag: create / move / resize in the time grid, move in the month ---------- */
  function colAt(x) {
    var best = null, bd = 1e9;
    $stage.find('.tg-col').each(function () {
      var r = this.getBoundingClientRect(), d = x < r.left ? r.left - x : x > r.right ? x - r.right : 0;
      if (d < bd) { bd = d; best = this; }
    });
    return best;
  }
  function minAt(col, y, snap) { var r = col.getBoundingClientRect(); return (y - r.top) / HH * 60; }
  function snap15(m) { return Math.round(m / 15) * 15; }
  function dragOn(move, up) {
    $('body').addClass('cal-dragging');
    $(document).on('mousemove.caldrag', move).on('mouseup.caldrag', function (e) { $(document).off('.caldrag'); $('body').removeClass('cal-dragging'); up(e); });
  }

  // create by clicking / dragging on an empty slot
  $stage.on('mousedown', '.tg-col', function (ev) {
    if (ev.button !== 0 || $(ev.target).closest('.te').length || !writable().length) return;
    ev.preventDefault();
    var col = this, $col = $(col), d = P($col.data('day'));
    var anchor = clamp(Math.floor(minAt(col, ev.clientY) / 15) * 15, 0, 1425), a = anchor, b = anchor + 15, moved = false;
    var $g = $('<div class="tg-new"></div>').appendTo($col);
    function paint() {
      $g.css({ top: a / 60 * HH, height: Math.max((b - a) / 60 * HH - 1, 14) }).text(fmtTime(d + a * MIN) + ' – ' + fmtTime(d + b * MIN));
    }
    paint();
    dragOn(function (e) {
      var m = clamp(Math.floor(minAt(col, e.clientY) / 15) * 15, 0, 1425);
      if (m >= anchor) { a = anchor; b = m + 15; } else { a = m; b = anchor + 15; }
      moved = moved || b - a > 15; paint();
    }, function () {
      $g.remove(); S.sup = Date.now();
      newEventAt(d, d + a * MIN, d + (moved ? b : a + 60) * MIN, false);
    });
  });

  // move or resize an existing timed event
  $stage.on('mousedown', '.te', function (ev) {
    if (ev.button !== 0) return;
    var $te = $(this), e = occOf($te);
    if (!e || !e.edit || e.repeat !== 'none') return;
    var resizing = $(ev.target).hasClass('te-rz'), srcCol = $te.closest('.tg-col')[0], d0 = P($(srcCol).data('day'));
    var s = P(e.start), en = P(e.end), dur = Math.max(en - s, 15 * MIN);
    if (!resizing && s < d0) return;                      // continuation of a multi-day event: edit it instead
    ev.preventDefault();
    var x0 = ev.clientX, y0 = ev.clientY, started = false, grab = minAt(srcCol, ev.clientY) - (s - d0) / MIN, $g = null, ns = s, ne = en;
    dragOn(function (m) {
      if (!started) {
        if (Math.abs(m.clientX - x0) + Math.abs(m.clientY - y0) < 5) return;
        started = true; closePop();
        if (!resizing) { $g = $te.clone().addClass('te-ghost').removeAttr('data-oid').find('.te-rz').remove().end(); $te.addClass('is-src'); }
      }
      if (resizing) {
        var mm = clamp(snap15(minAt(srcCol, m.clientY)), (s - d0) / MIN + 15, 1440);
        ne = d0 + mm * MIN; ns = s;
        $te.css('height', Math.max((ne - s) / HOUR * HH - 1, 18)).find('.te-s').text(fmtTime(s) + ' – ' + fmtTime(ne));
      } else {
        var col = colAt(m.clientX), dd = P($(col).data('day')), pos = clamp(snap15(minAt(col, m.clientY) - grab), 0, 1425);
        ns = dd + pos * MIN; ne = ns + dur;
        $g.appendTo(col).css({ top: pos / 60 * HH, height: Math.max(Math.min(dur / MIN, 1440 - pos) / 60 * HH - 1, 18), left: '2px', width: 'calc(100% - 6px)' });
        $g.find('.te-s').text(fmtTime(ns) + ' – ' + fmtTime(ne));
      }
    }, function () {
      if (!started) return;
      S.sup = Date.now();
      if ($g) $g.remove();
      $te.removeClass('is-src');
      if (ns === s && ne === en) { render(true); return; }
      applyMove(e, ns, ne);
    });
  });

  // month: drag a chip onto another day
  $stage.on('mousedown', '.mc .ev', function (ev) {
    if (ev.button !== 0 || isMobile()) return;
    var $c = $(this), e = occOf($c);
    if (!e || !e.edit || e.repeat !== 'none') return;
    var fromDay = P($c.data('day')), x0 = ev.clientX, y0 = ev.clientY, started = false, $g = null, $over = null, to = fromDay;
    dragOn(function (m) {
      if (!started) {
        if (Math.abs(m.clientX - x0) + Math.abs(m.clientY - y0) < 5) return;
        started = true; closePop();
        $g = $('<div class="ev-ghost"></div>').text(e.title).appendTo($app);
        $g[0].style.setProperty('--c', colorOf(e)); $g[0].style.setProperty('--tc', textOn(colorOf(e)));
      }
      $g.css({ left: m.clientX + 10, top: m.clientY + 8 });
      var el = document.elementFromPoint(m.clientX, m.clientY), $cell = $(el).closest('.mc');
      if ($over && (!$cell.length || $cell[0] !== $over[0])) $over.removeClass('is-drop');
      if ($cell.length) { $over = $cell.addClass('is-drop'); to = P($cell.data('day')); }
    }, function () {
      if (!started) return;
      S.sup = Date.now();
      $g.remove(); $stage.find('.is-drop').removeClass('is-drop');
      var off = to - fromDay;
      if (!off) return;
      applyMove(e, P(e.start) + off, P(e.end) + off);
    });
  });

  function applyMove(e, ns, ne) {
    var old = { start: e.start, end: e.end };
    e.start = F(ns); e.end = F(ne);
    render(true);
    api('POST', URLS.events + '/' + e.id + '/move', { start: e.start, end: e.end }).done(function (r) {
      toast('success', r.message || 'Event moved.'); load();
    }).fail(function (x) {
      e.start = old.start; e.end = old.end; render(true); toast('danger', errMsg(x));
    });
  }

  /* ---------- event form ---------- */
  $('#evForm').on('change', '#evAllDay', function () { setAllDay(this.checked); });
  $('#evForm').on('change', '#evSD, #evST', function () {
    var ns = readStart();
    if (!isNaN(ns) && !isNaN(EV.prevStart)) {
      var delta = ns - EV.prevStart, ne = readEnd();
      if (!isNaN(ne)) { ne += delta; $('#evED').val(ymd(ne)); $('#evET').val(hm(ne)); }
    }
    EV.prevStart = ns;
    fillRepeat();
  });
  $('#evForm').on('change', '#evRepeat', function () { $('#evUntilWrap').toggleClass('d-none', this.value === 'none'); });
  $('#evForm').on('change', '#evCal', function () { var c = calById(this.value); $('#evCalDot').css('background', c ? c.color : '#2563EB'); });
  $('#evForm').on('click', '.js-rem-add', function () { if ($('#evRems .rm-r').length < 5) { $('#evRems').append(remRow(30)); remSync(); } });
  $('#evForm').on('click', '.js-rem-x', function () { $(this).closest('.rm-r').remove(); remSync(); });
  $('#evForm').on('click', '.js-lead-x', function () { setLead(null); $('#evLeadQ').trigger('focus'); });
  var leadTimer = null, leadReq = 0;
  $('#evForm').on('input', '#evLeadQ', function () {
    var q = $.trim(this.value), $res = $('#evLeadRes'), n = ++leadReq;
    clearTimeout(leadTimer);
    if (q.length < 2) { $res.addClass('d-none').empty(); return; }
    leadTimer = setTimeout(function () {
      api('GET', URLS.leads + '?q=' + encodeURIComponent(q)).done(function (rows) {
        if (n !== leadReq) return;
        if (!rows.length) { $res.removeClass('d-none').html('<div class="caption p-2">No lead found</div>'); return; }
        $res.removeClass('d-none').html(rows.map(function (r) { return '<button type="button" class="ev-lead-opt" data-id="' + r.id + '" data-name="' + esc(r.name) + '">' + esc(r.name) + (r.mobile ? '<small>' + esc(r.mobile) + '</small>' : '') + '</button>'; }).join(''));
      });
    }, 250);
  });
  $('#evForm').on('click', '.ev-lead-opt', function () { setLead({ id: +$(this).data('id'), name: String($(this).attr('data-name')) }); });
  $(document).on('click', function (ev) { if (!$(ev.target).closest('.ev-lead').length) $('#evLeadRes').addClass('d-none'); });

  $('#evForm').on('click', '.js-ev-del', function () {
    if (!EV.edit) return;
    var $b = $(this).addClass('d-none');
    askDelete(EV.edit, $('#evDelChoice'), function (gone) {
      if (gone) { evModal && evModal.hide(); } else { $b.removeClass('d-none'); }
    });
  });

  $('#evForm').on('submit', function (ev) {
    ev.preventDefault();
    if (EV.busy) return;
    var $f = $(this); clearErrors($f);
    var c = collectEvent(), d = c.data;
    var s = P(d.start), en = P(d.end), bad = false;
    if (isNaN(s)) { setFieldError($f, 'end', 'Pick a start date.'); bad = true; }
    else if (isNaN(en) || en < s) { setFieldError($f, 'end', 'The event cannot end before it starts.'); bad = true; }
    if (d.repeat !== 'none' && d.repeat_until && !isNaN(s) && d.repeat_until < ymd(s)) { setFieldError($f, 'repeat_until', 'The repeat end date is before the event starts.'); bad = true; }
    if (!d.calendar_id) { setFieldError($f, 'calendar_id', 'Choose a calendar.'); bad = true; }
    if (c.bad) { showError($f, 'A notification can be at most 4 weeks before the event.'); bad = true; }
    if (bad) return;

    EV.busy = true; busy($f, true);
    var req = EV.id ? api('PUT', URLS.events + '/' + EV.id, d) : api('POST', URLS.events, d);
    req.done(function (r) {
      store('mp_cal_last', String(d.calendar_id));
      if (S.hidden[d.calendar_id]) { delete S.hidden[d.calendar_id]; saveHidden(); }
      evModal && evModal.hide();
      toast('success', r.message || 'Event saved.');
      // jump to the day of the event if it is not on screen
      var rr = rangeFor();
      if (!EV.id && (s < rr.a || s >= rr.b)) go(s, null); else load();
    }).fail(function (x) {
      var j = x.responseJSON || {}, shown = false;
      if (x.status === 422 && j.errors) {
        var map = { title: '#evTitle', start: null, end: null, repeat_until: null, calendar_id: null, lead_id: null };
        Object.keys(j.errors).forEach(function (k) {
          var msg = [].concat(j.errors[k])[0], key = k === 'start' ? 'end' : k;
          if (!setFieldError($f, key, msg, map[k] ? $(map[k]) : null)) showError($f, msg); shown = true;
        });
      }
      if (!shown) showError($f, errMsg(x));
    }).always(function () { EV.busy = false; busy($f, false); });
  });
  $(evModalEl).on('shown.bs.modal', function () { if (!EV.id) $('#evTitle').trigger('focus'); });
  $(evModalEl).on('hidden.bs.modal', function () { $('#evDelChoice').addClass('d-none').empty(); setLead(null); });

  /* ---------- calendar form ---------- */
  $('#calForm').on('click', '.sw[data-color]', function () { CM.color = String($(this).data('color')); paintSwatches(); });
  $('#calForm').on('input change', '.sw-custom input', function () { CM.color = this.value.toUpperCase(); $(this).parent().css('--c', CM.color).addClass('is-on'); $('#calSwatches .sw[data-color]').removeClass('is-on').empty(); });
  $('#calForm').on('change', '#calOwner', calOwnerNote);
  $('#calForm').on('submit', function (ev) {
    ev.preventDefault();
    var $f = $(this); clearErrors($f);
    var name = $.trim($('#calName').val());
    if (!name) { setFieldError($f, 'name', 'Give the calendar a name.', $('#calName')); return; }
    var ownerV = $('#calOwner').val(), admin = cfg.me && cfg.me.admin;
    var body = { name: name, color: CM.color, description: $.trim($('#calDesc').val()), branch_id: ownerV === '' || ownerV == null ? null : +ownerV, is_shared: !!(admin && (ownerV === '' || ownerV == null) && $('#calShared').prop('checked')) };
    busy($f, true);
    var req = CM.id ? api('PUT', URLS.calendars + '/' + CM.id, body) : api('POST', URLS.calendars, body);
    req.done(function (r) {
      var c = r.calendar, i = -1;
      S.calendars.forEach(function (x, k) { if (x.id === c.id) i = k; });
      if (i > -1) S.calendars[i] = c; else S.calendars.push(c);
      S.calendars.sort(function (a, b) { return (a.branch_id == null ? 0 : 1) - (b.branch_id == null ? 0 : 1) || String(a.group).localeCompare(b.group) || a.name.localeCompare(b.name); });
      delete S.hidden[c.id]; saveHidden();
      calModal && calModal.hide(); toast('success', r.message || 'Saved.');
      render(true); load();
    }).fail(function (x) {
      var j = x.responseJSON || {}, shown = false;
      if (x.status === 422 && j.errors) {
        Object.keys(j.errors).forEach(function (k) {
          var msg = [].concat(j.errors[k])[0], $in = k === 'name' ? $('#calName') : k === 'branch_id' ? $('#calOwner') : null;
          if (!setFieldError($f, k, msg, $in)) showError($f, msg); shown = true;
        });
      }
      if (!shown) showError($f, errMsg(x));
    }).always(function () { busy($f, false); });
  });
  $('#calForm').on('click', '.js-cal-del', function () {
    var $b = $(this), c = calById(CM.id); if (!c) return;
    if (!$b.data('armed')) {
      $b.data('armed', 1).html('<i class="bi bi-exclamation-triangle me-1"></i>Delete calendar and its events?');
      setTimeout(function () { $b.removeData('armed').html('<i class="bi bi-trash me-1"></i>Delete'); }, 5000);
      return;
    }
    $b.prop('disabled', true);
    api('DELETE', URLS.calendars + '/' + c.id).done(function (r) {
      S.calendars = S.calendars.filter(function (x) { return x.id !== c.id; });
      calModal && calModal.hide(); toast('success', r.message || 'Calendar deleted.');
      if (!S.calendars.length) { location.reload(); return; }
      render(true); load();
    }).fail(function (x) { showError($('#calForm'), errMsg(x)); }).always(function () { $b.prop('disabled', false); });
  });

  /* ---------- window ---------- */
  var rz = null, wasMobile = isMobile();
  $(window).on('resize', function () {
    clearTimeout(rz);
    rz = setTimeout(function () {
      var m = isMobile();
      if (m !== wasMobile) { wasMobile = m; var before = S.view; fixView(); closePop(); if (before !== S.view) { S.scrollTop = null; render(false); load(); return; } render(true); }
      else fitMonth();
    }, 120);
  });
  $stage.on('scroll', '.tg-scroll', function () { S.scrollTop = this.scrollTop; });
  setInterval(function () {            // keep the red "now" line moving
    var $n = $stage.find('.tg-now'); if ($n.length) $n.css('top', nowTop());
  }, 60000);

  /* ------------------------------------------------------------------ start */
  render(false);
  renderNotif();
  var o = cfg.open;
  if (o && o.at) { S.cur = day0(P(o.at)); S.mini = monthFirst(S.cur); }
  if (o && o.id) {
    var a = o.at ? P(o.at) : nowMs() - 30 * DAY;
    var rA = o.at ? a - 3 * DAY : day0(a), rB = o.at ? a + 4 * DAY : rA + 70 * DAY;
    api('GET', URLS.feed + '?start=' + ymd(rA) + '&end=' + ymd(rB)).done(function (res) {
      var hits = (res.events || []).filter(function (e) { return e.id === o.id; });
      var hit = hits.filter(function (e) { return o.at && e.start.slice(0, 10) === o.at; })[0] || hits[0];
      if (!hit) { toast('warning', 'That event is no longer available.'); load(); return; }
      S.cur = day0(P(hit.start)); S.mini = monthFirst(S.cur); render(false);
      load({ done: function () { var e = S.byOid[hit.oid]; if (e) openPop(e, $stage.find('[data-oid="' + hit.oid + '"]').first()); } });
    }).fail(function () { load(); });
    try { history.replaceState(null, '', location.pathname); } catch (e2) { /* ignore */ }
  } else {
    load();
  }
})(jQuery);
