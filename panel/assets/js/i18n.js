/**
 * Uz / Ru / En — panel tarjimalari
 */
(function (global) {
  'use strict';

  const LANGS = ['uz', 'ru', 'en'];
  const STORAGE_KEY = 'webphisher_uz_panel_lang';
  const DEFAULT_LANG = 'uz';

  const DATE_LOCALES = { uz: 'uz-UZ', ru: 'ru-RU', en: 'en-US' };
  const HTML_LANG = { uz: 'uz', ru: 'ru', en: 'en' };

  let currentLang = DEFAULT_LANG;
  let strings = {};
  const listeners = [];

  function t(key, params = {}) {
    let text = strings[key] ?? key;
    Object.entries(params).forEach(([k, v]) => {
      text = text.replace(new RegExp(`\\{${k}\\}`, 'g'), String(v));
    });
    return text;
  }

  async function loadLang(lang) {
    const res = await fetch(`/assets/i18n/${lang}.json?v=3.0`);
    if (!res.ok) throw new Error('Locale load failed: ' + lang);
    strings = await res.json();
    currentLang = lang;
    try {
      localStorage.setItem(STORAGE_KEY, lang);
    } catch (_) { /* ignore */ }
  }

  function applyDom() {
    document.documentElement.lang = HTML_LANG[currentLang] || currentLang;
    document.title = t('meta.title');

    document.querySelectorAll('[data-i18n]').forEach((el) => {
      const key = el.getAttribute('data-i18n');
      const val = t(key);
      if (el.hasAttribute('data-i18n-html')) {
        el.innerHTML = val;
      } else {
        el.textContent = val;
      }
    });

    document.querySelectorAll('[data-i18n-placeholder]').forEach((el) => {
      el.placeholder = t(el.getAttribute('data-i18n-placeholder'));
    });

    document.querySelectorAll('[data-i18n-title]').forEach((el) => {
      el.title = t(el.getAttribute('data-i18n-title'));
    });

    const tag = document.querySelector('[data-i18n-meta-tag]');
    if (tag) {
      const ver = tag.getAttribute('data-version') || '';
      tag.textContent = t('meta.tag', { version: ver });
    }

    document.querySelectorAll('.lang-btn').forEach((btn) => {
      btn.classList.toggle('active', btn.dataset.lang === currentLang);
    });

    document.querySelectorAll('[data-feed-empty]').forEach((el) => {
      if (!el.classList.contains('empty') || el.querySelector('.feed-item')) return;
      const icon = el.id === 'live-ips' ? 'fa-satellite-dish' : 'fa-hourglass-half';
      el.innerHTML = `<i class="fas ${icon}"></i><br>${t(el.getAttribute('data-feed-empty'))}`;
    });
  }

  async function setLang(lang) {
    if (!LANGS.includes(lang)) return;
    await loadLang(lang);
    applyDom();
    listeners.forEach((fn) => fn(currentLang));
  }

  async function init() {
    let saved = DEFAULT_LANG;
    try {
      const s = localStorage.getItem(STORAGE_KEY);
      if (s && LANGS.includes(s)) saved = s;
    } catch (_) { /* ignore */ }
    await loadLang(saved);
    applyDom();
    bindSwitcher();
  }

  function bindSwitcher() {
    document.querySelectorAll('.lang-btn').forEach((btn) => {
      btn.addEventListener('click', () => setLang(btn.dataset.lang));
    });
  }

  function onChange(fn) {
    listeners.push(fn);
  }

  function getLang() {
    return currentLang;
  }

  function getDateLocale() {
    return DATE_LOCALES[currentLang] || 'en-US';
  }

  global.I18n = { init, t, setLang, getLang, getDateLocale, applyDom, onChange, LANGS };
})(window);
