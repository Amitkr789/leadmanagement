/* Company > WhatsApp chat (jQuery). Conversations, live updates, photo / video / document / link sharing, templates, one-click lead. */
$(function () {
  var $wa = $('#wa'); if (!$wa.length) return;
  var U = $wa.data('urls'), url = function (k, id) { return U[k].replace('__ID__', id); };
  $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content'), 'Accept': 'application/json' } });

  var st = { contacts: [], active: null, lastId: 0, firstId: 0, hasMore: false, filter: '', q: '', open: false, templates: null, queue: [], sending: false, lastPoll: 0 };
  var $msgs = $('#waMsgs'), $in = $('#waInput');

  /* ---------- helpers ---------- */
  var esc = function (s) { return $('<div>').text(s == null ? '' : s).html(); };
  var uuid = function () { return (window.crypto && crypto.randomUUID) ? crypto.randomUUID() : 'r' + Date.now() + Math.random().toString(16).slice(2); };
  var initials = function (n) { return esc((n || '?').replace(/[^\p{L}\p{N} ]/gu, '').trim().slice(0, 2).toUpperCase() || '#'); };
  var tm = function (iso) { return new Date(iso).toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit' }); };
  var sameDay = function (a, b) { return a.toDateString() === b.toDateString(); };
  function dayLabel(iso) { var d = new Date(iso), n = new Date(), y = new Date(Date.now() - 864e5); return sameDay(d, n) ? 'Today' : sameDay(d, y) ? 'Yesterday' : d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' }); }
  function listTime(iso) { if (!iso) return ''; var d = new Date(iso), n = new Date(); return sameDay(d, n) ? tm(iso) : sameDay(d, new Date(Date.now() - 864e5)) ? 'Yesterday' : d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short' }); }
  var size = function (b) { return !b ? '' : b > 1048576 ? (b / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(b / 1024)) + ' KB'; };
  /** Escape first, then turn links into safe anchors. */
  function linkify(t) { return esc(t).replace(/(https?:\/\/[^\s<]+[^\s<.,;:!?)\]'"])/g, '<a href="$1" target="_blank" rel="noopener noreferrer nofollow">$1</a>').replace(/\n/g, '<br>'); }
  var errText = function (x) { var r = x.responseJSON || {}, e = r.errors; return r.error || (e ? e[Object.keys(e)[0]][0] : (r.message || 'Something went wrong. Please try again.')); };
  var active = function () { return st.contacts.filter(function (c) { return c.id === st.active; })[0]; };

  /* ---------- conversation list ---------- */
  function renderList() {
    var top = $('#waList').scrollTop();
    var h = st.contacts.map(function (c) {
      return '<button type="button" class="wa-c' + (c.id === st.active ? ' is-on' : '') + '" data-id="' + c.id + '"><span class="avatar">' + initials(c.name) + '</span><div class="wa-c-main"><div class="wa-c-top"><b>' + esc(c.name) + '</b><span class="wa-c-time">' + listTime(c.at) + '</span></div>' +
        '<div class="wa-c-bot"><span class="wa-c-prev">' + esc(c.preview || c.phone) + '</span>' + (c.lead_id ? '<i class="bi bi-person-check-fill wa-lead-ico" title="Already a lead"></i>' : '') + (c.unread ? '<span class="wa-badge">' + c.unread + '</span>' : '') + '</div></div></button>';
    }).join('');
    $('#waList').html(h || '<div class="wa-empty"><i class="bi bi-chat-dots fs-2 d-block mb-2"></i>' + (st.q || st.filter ? 'No chats match.' : 'No conversations yet. Press the new chat button, or wait for a customer to message you.') + '</div>').scrollTop(top);
    var n = st.contacts.reduce(function (a, c) { return a + c.unread; }, 0); document.title = (n ? '(' + n + ') ' : '') + 'WhatsApp';
  }
  function loadContacts() { return $.get(U.contacts, { q: st.q, filter: st.filter }).done(function (r) { st.contacts = r.contacts; renderList(); }); }
  var t; $('#waSearch').on('input', function () { clearTimeout(t); var v = $.trim(this.value); t = setTimeout(function () { st.q = v; loadContacts(); }, 300); });
  $('.wa-chip').on('click', function () { $('.wa-chip').removeClass('is-on'); $(this).addClass('is-on'); st.filter = $(this).data('filter'); loadContacts(); });
  $('#waList').on('click', '.wa-c', function () { openChat($(this).data('id')); });

  /* ---------- header, 24-hour window, lead button ---------- */
  function setHeader(c) {
    $('#waAv').text(initials(c.name)); $('#waName').text(c.name); $('#waPhone').text(c.phone);
    var has = !!c.lead_id;
    $('#waMakeLead').toggleClass('d-none', has); $('#waLeadBadge').toggleClass('d-none', !has || !!c.lead_url);
    $('#waLeadLink').toggleClass('d-none', !(has && c.lead_url)).attr('href', c.lead_url || '#');
    st.open = !!c.open; $('#waComposer').toggleClass('d-none', !st.open); $('#waTemplate').toggleClass('d-none', st.open);
    var $w = $('#waWindow');
    if (!st.open) $w.removeClass('d-none').html('<i class="bi bi-clock-history me-1"></i>The 24-hour reply window is closed. Send an approved template to continue.');
    else { var left = c.window_ends ? new Date(c.window_ends) - Date.now() : 0; if (left > 0 && left < 3 * 36e5) $w.removeClass('d-none').text('You can reply freely for ' + Math.max(1, Math.round(left / 6e4)) + ' more minutes.'); else $w.addClass('d-none'); }
    if (!st.open && !st.templates) loadTemplates();
  }

  /* ---------- messages ---------- */
  var ticks = function (m) {
    if (m.dir !== 'out') return '';
    return { queued: '<i class="bi bi-clock"></i>', sent: '<i class="bi bi-check2"></i>', delivered: '<i class="bi bi-check2-all"></i>', read: '<i class="bi bi-check2-all is-read"></i>', failed: '<i class="bi bi-exclamation-circle text-danger"></i>' }[m.status] || '';
  };
  function bubble(m) {
    var body = '', media = esc(m.media || '');
    if (m.type === 'image' || m.type === 'sticker') body = '<a href="#" class="wa-img" data-src="' + media + '"><img src="' + media + '" alt="Photo" loading="lazy"></a>';
    else if (m.type === 'video') body = '<video controls preload="metadata" src="' + media + '"></video>';
    else if (m.type === 'audio') body = '<audio controls preload="none" src="' + media + '"></audio>';
    else if (m.type === 'document') body = '<a class="wa-doc" href="' + media + '" target="_blank" rel="noopener"><i class="bi bi-file-earmark-text"></i><div><b>' + esc(m.name || 'Document') + '</b><span>' + size(m.size) + ' · Open or download</span></div></a>';
    else body = linkify(m.body || '');
    var cap = m.caption ? '<div class="wa-cap">' + linkify(m.caption) + '</div>' : '';
    var err = (m.status === 'failed') ? '<div class="wa-err">' + esc(m.error || 'Not sent') + (m.type === 'text' || m.type === 'template' ? ' · <a href="#" class="wa-retry" data-id="' + m.id + '">Try again</a>' : '') + '</div>' : '';
    return '<div class="wa-m is-' + (m.dir === 'out' ? 'out' : 'in') + (m.status === 'failed' ? ' is-failed' : '') + '" data-id="' + m.id + '" data-ref="' + esc(m.ref || '') + '" data-at="' + esc(m.at) + '" data-body="' + esc(m.body || '') + '" data-type="' + esc(m.type) + '" data-status="' + esc(m.status || '') + '">' + body + cap + err +
      '<div class="wa-meta"><span>' + tm(m.at) + '</span>' + ticks(m) + '</div></div>';
  }
  function append(list, initial) {
    var near = $msgs[0].scrollHeight - $msgs.scrollTop() - $msgs.innerHeight() < 160;
    list.forEach(function (m) {
      var $old = m.ref ? $msgs.find('.wa-m[data-ref="' + m.ref + '"]') : $();
      if (!$old.length && m.id) $old = $msgs.find('.wa-m[data-id="' + m.id + '"]');
      if ($old.length) { $old.replaceWith(bubble(m)); }
      else {
        var $last = $msgs.find('.wa-m').last(), prev = $last.length ? $last.data('at') : null;
        if (!prev || !sameDay(new Date(prev), new Date(m.at))) $msgs.append('<div class="wa-day">' + dayLabel(m.at) + '</div>');
        $msgs.append(bubble(m));
      }
      if (m.id) { st.lastId = Math.max(st.lastId, m.id); if (!st.firstId || m.id < st.firstId) st.firstId = m.id; }
    });
    if (initial || near) $msgs.scrollTop($msgs[0].scrollHeight);
  }
  $msgs.on('click', '.wa-img', function (e) { e.preventDefault(); $('#waLightImg').attr('src', $(this).data('src')); new bootstrap.Modal('#waLight').show(); });
  $msgs.on('click', '.wa-more', function () {
    var $b = $(this).prop('disabled', true), h0 = $msgs[0].scrollHeight;
    $.get(url('messages', st.active), { before: st.firstId }).done(function (r) {
      var html = r.messages.map(bubble).join(''); $b.remove(); $msgs.prepend((r.has_more ? '<button type="button" class="wa-more">Load earlier messages</button>' : '') + html);
      r.messages.forEach(function (m) { st.firstId = Math.min(st.firstId, m.id); }); $msgs.scrollTop($msgs[0].scrollHeight - h0);
    });
  });

  function openChat(id) {
    st.active = id; st.lastId = 0; st.firstId = 0; $wa.addClass('is-chat'); $('#waPlaceholder').addClass('d-none'); $('#waPane').removeClass('d-none'); renderList();
    $msgs.html('<div class="wa-empty"><span class="spinner-border spinner-border-sm"></span></div>');
    $.get(url('messages', id)).done(function (r) {
      if (id !== st.active) return;
      $msgs.empty(); if (r.has_more) $msgs.append('<button type="button" class="wa-more">Load earlier messages</button>');
      append(r.messages, true); setHeader(r.contact); $in.trigger('focus');
      if (r.contact.unread) $.post(url('read', id)).done(function () { var c = active(); if (c) { c.unread = 0; renderList(); } });
    }).fail(function () { $msgs.html('<div class="wa-empty">Could not load this chat.</div>'); });
  }
  $('.wa-back').on('click', function () { $wa.removeClass('is-chat'); st.active = null; renderList(); });

  /* ---------- sending text and links ---------- */
  function sendText(ref, body) {
    $.post(url('send', st.active), { body: body, client_ref: ref })
      .done(function (r) { append([r.message]); loadContacts(); })
      .fail(function (x) { var r = x.responseJSON || {}; if (r.message && r.message.id) append([r.message]); else append([{ id: 0, ref: ref, dir: 'out', type: 'text', body: body, status: 'failed', error: errText(x), at: new Date().toISOString() }]); if (r.needs_template) refreshActive(); MP.toast('danger', errText(x)); });
  }
  function submitText() {
    var body = $.trim($in.val()); if (!body || !st.active) return;
    var ref = uuid(); append([{ id: 0, ref: ref, dir: 'out', type: 'text', body: body, status: 'queued', at: new Date().toISOString() }], true);
    $in.val('').css('height', ''); sendText(ref, body);
  }
  $('#waSend').on('click', submitText);
  $in.on('keydown', function (e) { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); submitText(); } });
  $in.on('input', function () { this.style.height = 'auto'; this.style.height = Math.min(this.scrollHeight, 130) + 'px'; });
  $msgs.on('click', '.wa-retry', function (e) { e.preventDefault(); var $m = $(this).closest('.wa-m'), ref = $m.data('ref'), body = $m.data('body'); if (!ref) return; $m.find('.wa-err').remove(); sendText(ref, String(body)); });
  function refreshActive() { $.get(url('messages', st.active)).done(function (r) { setHeader(r.contact); }); }

  // emoji tray
  $('#waEmoji').html('😀 😂 😊 😍 👍 🙏 👌 🎉 ✅ ❗ 📍 📞 🚚 📦 🏠 💰 ⏰ 🙂 😅 🤝 ❤️ 👋 🔥 ⭐'.split(' ').map(function (e) { return '<button type="button" data-e="' + e + '">' + e + '</button>'; }).join(''));
  $('#waEmoji').on('click', 'button', function () { var el = $in[0], s = el.selectionStart, v = $in.val(); $in.val(v.slice(0, s) + $(this).data('e') + v.slice(el.selectionEnd)); el.focus(); el.selectionStart = el.selectionEnd = s + $(this).data('e').length; });

  /* ---------- files: pick, drop, paste -> preview -> send ---------- */
  var ALLOWED = /^(image\/(jpeg|png)|video\/(mp4|3gpp)|audio\/(mpeg|ogg|aac|amr|mp4)|application\/(pdf|msword|vnd\.ms-excel|vnd\.ms-powerpoint|vnd\.openxmlformats-officedocument\..+)|text\/plain)$/;
  var limitMb = function (mime) { return /^image\//.test(mime) ? 5 : /^(video|audio)\//.test(mime) ? 16 : 25; };
  $(document).on('click', '.js-wa-pick', function () { $('#waPick').attr('accept', $(this).data('accept')).val('').trigger('click'); });
  $('#waPick').on('change', function () { queueFiles(this.files); this.value = ''; });
  function queueFiles(files) {
    if (!st.active || !st.open) { MP.toast('warning', 'The reply window is closed. Send a template first.'); return; }
    Array.prototype.forEach.call(files, function (f) { st.queue.push(f); }); if (!$('#waPreview').hasClass('show')) nextFile();
  }
  var pv = new bootstrap.Modal('#waPreview'), cur = null, objUrl = null;
  function nextFile() {
    cur = st.queue.shift(); if (!cur) { pv.hide(); return; }
    var mime = cur.type || '', err = !ALLOWED.test(mime) ? 'This file type cannot be sent on WhatsApp.' : (cur.size > limitMb(mime) * 1048576 ? 'Too large. WhatsApp allows up to ' + limitMb(mime) + ' MB for this type.' : '');
    if (objUrl) URL.revokeObjectURL(objUrl); objUrl = URL.createObjectURL(cur);
    $('#wpBox').html(/^image\//.test(mime) ? '<img src="' + objUrl + '" alt="Preview">' : /^video\//.test(mime) ? '<video controls src="' + objUrl + '"></video>' : '<div class="wa-doc"><i class="bi bi-file-earmark-text"></i><div><b>' + esc(cur.name) + '</b><span>' + size(cur.size) + '</span></div></div>');
    $('#wpErr').toggleClass('d-none', !err).text(err); $('#wpSend').prop('disabled', !!err); $('#wpCaption').val('').prop('disabled', /^audio\//.test(mime)); $('#wpProg').addClass('d-none').find('.progress-bar').css('width', 0);
    $('#wpQueue').text(st.queue.length ? st.queue.length + ' more file(s) waiting' : ''); pv.show();
  }
  $('#waPreview').on('shown.bs.modal', function () { $('#wpCaption').trigger('focus'); }).on('hidden.bs.modal', function () { st.queue = []; cur = null; if (objUrl) { URL.revokeObjectURL(objUrl); objUrl = null; } });
  $('#wpSend').on('click', function () {
    if (!cur) return; var fd = new FormData(), ref = uuid(), $b = $(this).prop('disabled', true);
    fd.append('file', cur); fd.append('caption', $('#wpCaption').val()); fd.append('client_ref', ref); $('#wpProg').removeClass('d-none');
    $.ajax({ url: url('upload', st.active), type: 'POST', data: fd, processData: false, contentType: false,
      xhr: function () { var x = $.ajaxSettings.xhr(); if (x.upload) x.upload.onprogress = function (e) { if (e.lengthComputable) $('#wpProg .progress-bar').css('width', Math.round(100 * e.loaded / e.total) + '%'); }; return x; } })
      .done(function (r) { append([r.message], true); loadContacts(); if (st.queue.length) nextFile(); else pv.hide(); })
      .fail(function (x) { $('#wpErr').removeClass('d-none').text(errText(x)); if ((x.responseJSON || {}).message && x.responseJSON.message.id) append([x.responseJSON.message]); })
      .always(function () { $b.prop('disabled', false); $('#wpProg').addClass('d-none'); });
  });
  var drag = 0, $pane = $('#waPane');
  $pane.on('dragenter', function (e) { e.preventDefault(); drag++; $('#waDrop').removeClass('d-none'); }).on('dragover', function (e) { e.preventDefault(); })
    .on('dragleave', function () { if (--drag <= 0) { drag = 0; $('#waDrop').addClass('d-none'); } })
    .on('drop', function (e) { e.preventDefault(); drag = 0; $('#waDrop').addClass('d-none'); queueFiles(e.originalEvent.dataTransfer.files); });
  $in.on('paste', function (e) { var items = (e.originalEvent.clipboardData || {}).items || [], files = []; Array.prototype.forEach.call(items, function (i) { if (i.kind === 'file') files.push(i.getAsFile()); }); if (files.length) { e.preventDefault(); queueFiles(files); } });

  /* ---------- templates (when the 24-hour window is closed, and for brand-new numbers) ---------- */
  function loadTemplates(cb) {
    if (st.templates) { cb && cb(); return; }
    $.get(U.templates).done(function (r) { st.templates = r.templates; }).fail(function () { st.templates = []; }).always(function () { fillTpl('#waTplSel', '#waTplVars', '#waTplPrev'); cb && cb(); });
  }
  function fillTpl(sel, vars, prev) {
    var $s = $(sel); if (!st.templates) return;
    $s.html(st.templates.length ? st.templates.map(function (t, i) { return '<option value="' + i + '">' + esc(t.name) + ' (' + esc(t.language) + ')</option>'; }).join('') : '<option value="">No approved templates found</option>');
    tplVars(sel, vars, prev);
  }
  function tplVars(sel, vars, prev) {
    var t = st.templates && st.templates[+$(sel).val()], h = ''; if (!t) { $(vars).empty(); $(prev).text('Create an approved template in WhatsApp Manager first.'); return; }
    for (var i = 1; i <= t.vars; i++) h += '<input class="form-control form-control-sm mb-1 js-tv" data-i="' + i + '" placeholder="Value for {{' + i + '}}" maxlength="200" aria-label="Variable ' + i + '">';
    $(vars).html(h); renderPrev(sel, vars, prev);
  }
  function renderPrev(sel, vars, prev) { var t = st.templates[+$(sel).val()]; if (!t) return; var b = t.body; $(vars).find('.js-tv').each(function () { b = b.replace('{{' + $(this).data('i') + '}}', $(this).val() || '{{' + $(this).data('i') + '}}'); }); $(prev).text(b); }
  var tvals = function (vars) { return $(vars).find('.js-tv').map(function () { return $(this).val(); }).get(); };
  $('#waTplSel').on('change', function () { tplVars('#waTplSel', '#waTplVars', '#waTplPrev'); });
  $('#waTplVars').on('input', '.js-tv', function () { renderPrev('#waTplSel', '#waTplVars', '#waTplPrev'); });
  $('#waTplSend').on('click', function () {
    var t = st.templates && st.templates[+$('#waTplSel').val()]; if (!t) return MP.toast('warning', 'Choose a template first.');
    var $b = $(this).prop('disabled', true);
    $.post(url('template', st.active), { name: t.name, language: t.language, vars: tvals('#waTplVars'), client_ref: uuid() })
      .done(function (r) { append([r.message], true); loadContacts(); MP.toast('success', 'Template sent. You can chat freely once the customer replies.'); })
      .fail(function (x) { MP.toast('danger', errText(x)); }).always(function () { $b.prop('disabled', false); });
  });

  /* ---------- new chat ---------- */
  var nm = new bootstrap.Modal('#waNew');
  $(document).on('click', '.js-wa-new', function () { $('#waNewForm')[0].reset(); $('#wnTpl, #wnErr').addClass('d-none'); $('#wnPhoneErr').text(''); $('#wnGo').text('Start chat'); nm.show(); });
  $('#waNew').on('shown.bs.modal', function () { $('#wnPhone').trigger('focus'); });
  $('#wnPhone').on('input', function () { this.value = this.value.replace(/\D/g, ''); });
  $('#wnTplSel').on('change', function () { tplVars('#wnTplSel', '#wnTplVars', '#wnTplPrevDummy'); });
  $('#waNewForm').on('submit', function (e) {
    e.preventDefault(); var phone = $('#wnPhone').val(); $('#wnErr').addClass('d-none'); $('#wnPhoneErr').text('');
    if (!/^[6-9]\d{9}$/.test(phone)) { $('#wnPhoneErr').text('Enter a valid 10-digit mobile number.'); return; }
    var data = { phone: phone, name: $('#wnName').val() }, tplOn = !$('#wnTpl').hasClass('d-none');
    if (tplOn) { var t = st.templates && st.templates[+$('#wnTplSel').val()]; if (!t) { $('#wnErr').removeClass('d-none').text('Choose an approved template.'); return; } data.template = t.name; data.language = t.language; data.vars = tvals('#wnTplVars'); }
    var $b = $('#wnGo').prop('disabled', true);
    $.post(U.start, data).done(function (r) {
      if (r.needs_template) { $('#wnTpl').removeClass('d-none'); $('#wnGo').text('Send template and start'); loadTemplates(function () { fillTpl('#wnTplSel', '#wnTplVars', '#wnTplPrevDummy'); }); return; }
      nm.hide(); if (r.existed) MP.toast('info', 'You already have a conversation with this number. Opened it.'); loadContacts().always(function () { openChat(r.contact.id); });
    }).fail(function (x) { var r = x.responseJSON || {}; if (x.status === 422 && r.errors && r.errors.phone) $('#wnPhoneErr').text(r.errors.phone[0]); else $('#wnErr').removeClass('d-none').text(errText(x)); }).always(function () { $b.prop('disabled', false); });
  });

  /* ---------- one-click lead ---------- */
  $('#waMakeLead').on('click', function () {
    var $b = $(this).prop('disabled', true), id = st.active;
    $.post(url('lead', id)).done(function (r) {
      var c = active(); if (c) { c.lead_id = r.lead.id; c.lead_url = r.lead.url; setHeader(c); renderList(); }
      MP.toast(r.status === 'created' ? 'success' : 'info', r.message);
    }).fail(function (x) { MP.toast('danger', errText(x)); }).always(function () { $b.prop('disabled', false); });
  });

  /* ---------- live updates ---------- */
  function poll() {
    if (document.hidden && Date.now() - st.lastPoll < 15000) return; st.lastPoll = Date.now();
    $.get(U.poll, { contact: st.active || '', after: st.lastId, q: st.q, filter: st.filter }).done(function (r) {
      st.contacts = r.contacts; renderList();
      if (st.active && r.contact) {
        var fresh = r.messages || [];
        if (fresh.length) { append(fresh); if (fresh.some(function (m) { return m.dir === 'in'; })) $.post(url('read', st.active)); }
        setHeader(r.contact);
        $.each(r.statuses || {}, function (id, s) { // delivered / read ticks of messages already on screen
          var $e = $msgs.find('.wa-m[data-id="' + id + '"]');
          if ($e.length && $e.attr('data-status') !== s) { $e.attr('data-status', s).find('.wa-meta i').remove(); $e.find('.wa-meta').append(ticks({ dir: 'out', status: s })); }
        });
      }
    });
  }
  setInterval(poll, 4000);
  loadContacts();
});
