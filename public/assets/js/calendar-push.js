/* MovePro CRM > push notifications + in-app reminder pop-ups.
   Exposes window.MPPush { status(), enable(), disable(), test() } and fires "mppush:change" on document.
   Push goes through Firebase Cloud Messaging (browser + Android; iPhone only for the CRM added to the home screen).
   As a safety net, reminders are also polled every 30 s and shown as an in-app pop-up when push is not active on this device. */
(function () {
  'use strict';
  if (window.MPPush) return;
  var C = window.MPPushConfig || {};
  var SDK = 'https://www.gstatic.com/firebasejs/10.12.2/';
  var KEY_TOKEN = 'mp_push_token', KEY_REFRESH = 'mp_push_refreshed';

  var UA = navigator.userAgent || '';
  var ios = /iPad|iPhone|iPod/.test(UA) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
  var standalone = (window.matchMedia && matchMedia('(display-mode: standalone)').matches) || navigator.standalone === true;
  var supported = 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window;

  function ls(k, v) { try { if (v === undefined) return localStorage.getItem(k); if (v === null) localStorage.removeItem(k); else localStorage.setItem(k, v); } catch (e) { return null; } }
  function csrf() { var m = document.querySelector('meta[name="csrf-token"]'); return m ? m.getAttribute('content') : ''; }
  function emit() { try { document.dispatchEvent(new CustomEvent('mppush:change')); } catch (e) { /* old browser */ } }

  function post(url, body) {
    return fetch(url, {
      method: 'POST', credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf() },
      body: JSON.stringify(body || {})
    }).then(function (r) { return r.json().catch(function () { return {}; }).then(function (j) { return { ok: r.ok, status: r.status, json: j }; }); });
  }

  function permission() { return 'Notification' in window ? Notification.permission : 'denied'; }
  function enabled() { return supported && permission() === 'granted' && !!ls(KEY_TOKEN); }

  function status() {
    return {
      supported: supported, configured: !!C.configured, permission: permission(), enabled: enabled(),
      ios: ios, standalone: standalone, needsInstall: ios && !standalone
    };
  }

  /* ---- Firebase SDK, loaded only when needed ---- */
  var sdkPromise = null;
  function script(src) {
    return new Promise(function (res, rej) {
      var s = document.createElement('script'); s.src = src; s.async = true;
      s.onload = res; s.onerror = function () { rej(new Error('Could not load Google notification library. Check your internet connection.')); };
      document.head.appendChild(s);
    });
  }
  function loadSdk() {
    if (!sdkPromise) {
      sdkPromise = (window.firebase && firebase.messaging ? Promise.resolve() : script(SDK + 'firebase-app-compat.js').then(function () { return script(SDK + 'firebase-messaging-compat.js'); }))
        .then(function () {
          if (!firebase.apps.length) firebase.initializeApp(C.firebase);
          return firebase.messaging();
        });
      sdkPromise.catch(function () { sdkPromise = null; });
    }
    return sdkPromise;
  }
  function swReg() {
    return navigator.serviceWorker.register(C.swUrl, { scope: '/' }).then(function (reg) {
      return navigator.serviceWorker.ready.then(function () { return reg; });
    });
  }
  function deviceLabel() {
    var b = /Edg\//.test(UA) ? 'Edge' : /OPR\//.test(UA) ? 'Opera' : /Chrome\//.test(UA) ? 'Chrome' : /Firefox\//.test(UA) ? 'Firefox' : /Safari\//.test(UA) ? 'Safari' : 'Browser';
    var o = /Android/.test(UA) ? 'Android' : ios ? 'iPhone/iPad' : /Windows/.test(UA) ? 'Windows' : /Mac/.test(UA) ? 'Mac' : /Linux/.test(UA) ? 'Linux' : '';
    return (b + (o ? ' on ' + o : '')).slice(0, 120);
  }
  function fetchToken() {
    return Promise.all([loadSdk(), swReg()]).then(function (r) {
      return r[0].getToken({ vapidKey: C.vapid, serviceWorkerRegistration: r[1] });
    });
  }

  function enable() {
    if (!supported) return Promise.resolve({ ok: false, message: ios && !standalone ? 'On iPhone, add the CRM to your home screen first (Share → Add to Home Screen), then open it from there.' : 'This browser cannot receive notifications.' });
    if (!C.configured) return Promise.resolve({ ok: false, message: 'Push is not set up on the server yet (Firebase values missing).' });
    return Notification.requestPermission().then(function (p) {
      if (p !== 'granted') { emit(); return { ok: false, message: p === 'denied' ? 'Notifications are blocked for this site. Allow them in the browser settings.' : 'Notifications were not allowed.' }; }
      return fetchToken().then(function (token) {
        if (!token) return { ok: false, message: 'Google did not give this device a notification address. Try again.' };
        return post(C.registerUrl, { token: token, label: deviceLabel() }).then(function (r) {
          if (!r.ok) return { ok: false, message: (r.json && r.json.message) || 'Could not save this device. Try again.' };
          ls(KEY_TOKEN, token); ls(KEY_REFRESH, String(Date.now())); emit();
          return { ok: true, message: 'Notifications are on for this device.' };
        });
      });
    }).catch(function (e) {
      emit();
      return { ok: false, message: (e && e.message) || 'Could not turn on notifications.' };
    });
  }

  function disable() {
    var token = ls(KEY_TOKEN);
    ls(KEY_TOKEN, null);
    var done = function () { emit(); return { ok: true, message: 'Notifications are off for this device.' }; };
    if (!token) return Promise.resolve(done());
    return post(C.unregisterUrl, { token: token }).catch(function () { /* server cleans stale tokens itself */ })
      .then(function () { return loadSdk().then(function (m) { return m.deleteToken(); }).catch(function () { /* ignore */ }); })
      .then(done);
  }

  /* The service worker tells open tabs when a push arrived, so "Send test" can confirm delivery to THIS browser. */
  var waiters = [];
  if (supported) {
    navigator.serviceWorker.addEventListener('message', function (e) {
      if (e.data && e.data.mpPush) { waiters.splice(0).forEach(function (f) { f(e.data); }); }
    });
    try { navigator.serviceWorker.startMessages(); } catch (e) { /* not needed everywhere */ }
  }
  function test() {
    var got = new Promise(function (res) { waiters.push(res); setTimeout(function () { res(null); }, 9000); });
    return post(C.testUrl, {}).then(function (r) {
      var msg = (r.json && r.json.message) || (r.ok ? 'Test sent.' : 'Could not send the test.');
      if (!r.ok || !enabled()) return { ok: r.ok, message: msg };
      return got.then(function (m) {
        if (m) return { ok: true, message: 'Push received by this browser. If no pop-up appeared on screen, your system is hiding it: turn off Windows Focus assist / Do not disturb and allow notifications for your browser in system settings.' };
        return { ok: false, message: 'Google accepted the test, but this browser did not receive it. Keep the browser running, check the internet, then turn notifications off and on again.' };
      });
    }).catch(function () { return { ok: false, message: 'No connection. Try again.' }; });
  }

  window.MPPush = { status: status, enable: enable, disable: disable, test: test };

  /* ---- keep the token fresh: re-register at most twice a day while notifications are on ---- */
  function refresh() {
    if (!enabled() || !C.configured) return;
    var last = parseInt(ls(KEY_REFRESH) || '0', 10);
    if (Date.now() - last < 12 * 36e5) return;
    fetchToken().then(function (token) {
      if (!token) return;
      return post(C.registerUrl, { token: token, label: deviceLabel() }).then(function (r) {
        if (r.ok) { ls(KEY_TOKEN, token); ls(KEY_REFRESH, String(Date.now())); }
      });
    }).catch(function () { /* try again next visit */ });
  }
  if (enabled()) setTimeout(refresh, 4000);

  /* ---------------------------------------------------------------- in-app reminder pop-ups */
  if (!C.pollUrl) return;
  var shown = {}, timer = null, stack = null;

  function css() {
    if (document.getElementById('mpAlertCss')) return;
    var s = document.createElement('style'); s.id = 'mpAlertCss';
    s.textContent = '#mpAlerts{position:fixed;top:72px;right:16px;z-index:4000;display:grid;gap:10px;width:340px;max-width:calc(100vw - 32px)}' +
      '.mp-al{background:#fff;border:1px solid var(--mp-border,#E5E7EB);border-left:5px solid var(--mp-primary,#2563EB);border-radius:14px;box-shadow:0 12px 36px rgba(17,24,39,.22);padding:12px 14px;display:flex;gap:12px;align-items:flex-start;animation:mpAlIn .25s ease}' +
      '.mp-al-i{width:36px;height:36px;border-radius:50%;background:var(--mp-primary-soft,#EAF0FE);color:var(--mp-primary,#2563EB);display:grid;place-items:center;flex:none;font-size:1.05rem}' +
      '.mp-al-b{min-width:0;flex:1}.mp-al-t{font-weight:700;font-size:.92rem;line-height:1.3;word-break:break-word}.mp-al-m{font-size:.82rem;color:var(--mp-muted,#6B7280);margin-top:2px;word-break:break-word}' +
      '.mp-al-a{display:flex;gap:8px;margin-top:8px}.mp-al-a a,.mp-al-a button{font-size:.8rem;font-weight:600;border-radius:8px;padding:.28rem .7rem;border:1px solid var(--mp-border,#E5E7EB);background:#fff;color:inherit;text-decoration:none;cursor:pointer}' +
      '.mp-al-a a{background:var(--mp-primary,#2563EB);border-color:var(--mp-primary,#2563EB);color:#fff}' +
      '@keyframes mpAlIn{from{opacity:0;transform:translateX(24px)}to{opacity:1;transform:none}}' +
      '@media (max-width:575.98px){#mpAlerts{top:auto;bottom:16px;left:16px;right:16px;width:auto}}' +
      '@media (prefers-reduced-motion:reduce){.mp-al{animation:none}}';
    document.head.appendChild(s);
  }
  function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
  function beep() {
    try {
      var A = window.AudioContext || window.webkitAudioContext; if (!A) return;
      var ctx = new A(), o = ctx.createOscillator(), g = ctx.createGain();
      o.type = 'sine'; o.frequency.value = 880; g.gain.setValueAtTime(0.0001, ctx.currentTime);
      g.gain.exponentialRampToValueAtTime(0.18, ctx.currentTime + 0.02); g.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.5);
      o.connect(g); g.connect(ctx.destination); o.start(); o.stop(ctx.currentTime + 0.55);
      setTimeout(function () { ctx.close && ctx.close(); }, 800);
    } catch (e) { /* sound is optional */ }
  }
  function popup(a) {
    css();
    if (!stack) { stack = document.createElement('div'); stack.id = 'mpAlerts'; stack.setAttribute('role', 'region'); stack.setAttribute('aria-label', 'Reminders'); document.body.appendChild(stack); }
    var el = document.createElement('div');
    el.className = 'mp-al'; el.setAttribute('role', 'alert');
    el.innerHTML = '<div class="mp-al-i"><i class="bi bi-bell-fill"></i></div><div class="mp-al-b"><div class="mp-al-t">' + esc(a.title) + '</div>' + (a.body ? '<div class="mp-al-m">' + esc(a.body) + '</div>' : '') +
      '<div class="mp-al-a">' + (a.url ? '<a href="' + esc(a.url) + '">Open</a>' : '') + '<button type="button">Dismiss</button></div></div>';
    el.querySelector('button').addEventListener('click', function () { el.remove(); });
    stack.appendChild(el);
    while (stack.children.length > 4) stack.removeChild(stack.firstChild);
    beep();
  }

  function poll() {
    if (document.hidden) return;
    fetch(C.pollUrl, { credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function (r) { if (r.status === 401 || r.status === 419) { clearInterval(timer); return null; } return r.ok ? r.json() : null; })
      .then(function (res) {
        if (!res || !res.alerts || !res.alerts.length) return;
        var ids = [], pushActive = enabled();
        res.alerts.forEach(function (a) {
          ids.push(a.id);
          if (shown[a.id]) return;
          shown[a.id] = true;
          if (a.pushed && pushActive) return;      // the phone / browser notification already told them
          popup(a);
        });
        if (ids.length) post(C.ackUrl, { ids: ids }).catch(function () { /* will be acknowledged next poll */ });
      })
      .catch(function () { /* offline: try again on the next tick */ });
  }
  timer = setInterval(poll, 30000);
  document.addEventListener('visibilitychange', function () { if (!document.hidden) poll(); });
  setTimeout(poll, 2500);
})();
