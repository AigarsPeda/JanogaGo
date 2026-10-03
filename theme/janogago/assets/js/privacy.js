document.addEventListener('DOMContentLoaded', () => {
  const config = window.jgPrivacy || {};
  const banner = document.getElementById('jg-cookie-banner');
  if (!banner) return;
  const id = /^G-[A-Z0-9]+$/.test(config.measurementId || '') ? config.measurementId : '';
  const cookieName = 'jg_consent';
  const lifetime = 180 * 24 * 60 * 60;
  const openers = [...document.querySelectorAll('[data-cookie-settings]')];
  const closeButton = banner.querySelector('[data-cookie-close]');
  let opener = null;
  let started = false;
  const readChoice = () => {
    try {
      const raw = document.cookie.split('; ').find(value => value.startsWith(`${cookieName}=`));
      const value = JSON.parse(decodeURIComponent(raw?.slice(cookieName.length + 1) || 'null'));
      return value && value.v === config.version && typeof value.analytics === 'boolean' &&
        Number.isFinite(value.at) && value.at <= Date.now() && Date.now() - value.at < lifetime * 1000 ? value : null;
    } catch { return null; }
  };
  let choice = readChoice();
  let allowed = choice?.analytics === true;
  const cleanCookies = () => {
    const domains = location.hostname.split('.');
    document.cookie.split('; ').forEach(cookie => {
      const name = cookie.split('=')[0];
      if (!/^_ga(?:_|$)/.test(name)) return;
      document.cookie = `${name}=; Max-Age=0; path=/`;
      for (let i = 0; i < domains.length - 1; i++) {
        document.cookie = `${name}=; Max-Age=0; path=/; domain=${domains.slice(i).join('.')}`;
      }
    });
  };
  const safeUrl = value => {
    try { const url = new URL(value); return `${url.origin}${url.pathname}`; } catch { return ''; }
  };
  const start = () => {
    if (!id || !allowed || started) return;
    started = true;
    window[`ga-disable-${id}`] = false;
    window.dataLayer = window.dataLayer || [];
    window.gtag = function () { window.dataLayer.push(arguments); };
    window.gtag('consent', 'default', {analytics_storage: 'denied', ad_storage: 'denied', ad_user_data: 'denied', ad_personalization: 'denied'});
    window.gtag('consent', 'update', {analytics_storage: 'granted', ad_storage: 'denied', ad_user_data: 'denied', ad_personalization: 'denied'});
    window.gtag('js', new Date());
    window.gtag('config', id, {
      send_page_view: false, allow_google_signals: false, allow_ad_personalization_signals: false,
      cookie_expires: lifetime, cookie_update: false,
      page_location: safeUrl(location.href), page_referrer: safeUrl(document.referrer),
    });
    window.gtag('event', 'page_view', {page_location: safeUrl(location.href), page_referrer: safeUrl(document.referrer), language: config.language});
    const script = document.createElement('script');
    script.async = true;
    script.src = `https://www.googletagmanager.com/gtag/js?id=${id}`;
    document.head.append(script);
  };
  const stop = () => {
    allowed = false;
    if (id) window[`ga-disable-${id}`] = true;
    cleanCookies();
    // Unload the tag so withdrawal cannot send cookieless measurements.
    if (started) location.reload();
  };
  const show = () => {
    banner.hidden = false;
    closeButton.hidden = !choice;
    openers.forEach(button => button.setAttribute('aria-expanded', 'true'));
  };
  const hide = () => {
    if (banner.contains(document.activeElement)) (opener || openers[0])?.focus({preventScroll: true});
    banner.hidden = true;
    openers.forEach(button => button.setAttribute('aria-expanded', 'false'));
  };
  openers.forEach(button => {
    button.hidden = false;
    button.addEventListener('click', () => {
      opener = button;
      show();
      banner.querySelector('[data-cookie-choice]')?.focus({preventScroll: true});
    });
  });
  banner.querySelectorAll('[data-cookie-choice]').forEach(button => button.addEventListener('click', () => {
    choice = {v: config.version, analytics: button.dataset.cookieChoice === 'true', at: Date.now()};
    allowed = choice.analytics;
    document.cookie = `${cookieName}=${encodeURIComponent(JSON.stringify(choice))}; Max-Age=${lifetime}; path=/; SameSite=Lax${location.protocol === 'https:' ? '; Secure' : ''}`;
    // Notify other tabs; the preference cookie remains the source of truth.
    try {
      localStorage.setItem('jg_consent_changed', String(choice.at));
      localStorage.removeItem('jg_consent_changed');
    } catch { /* Cookies work without localStorage. */ }
    hide();
    if (allowed) start(); else stop();
  }));
  closeButton.addEventListener('click', hide);
  banner.addEventListener('keydown', event => { if (event.key === 'Escape' && choice) hide(); });
  const syncChoice = () => {
    const next = readChoice();
    if (allowed && next?.analytics !== true) stop();
    choice = next;
    allowed = next?.analytics === true;
    if (!next) show(); else { hide(); if (allowed) start(); }
  };
  window.addEventListener('storage', event => { if (event.key === 'jg_consent_changed') syncChoice(); });
  document.addEventListener('visibilitychange', () => { if (!document.hidden) syncChoice(); });
  const track = (name, fields = {}) => {
    if (allowed && !readChoice()?.analytics) { stop(); return; }
    if (id && allowed && started) window.gtag('event', name, {language: config.language, ...fields});
  };
  document.addEventListener('jg:enquiry-sent', () => track('generate_lead'));
  document.addEventListener('click', event => {
    const link = event.target.closest('a[href]');
    if (link?.getAttribute('href').startsWith('tel:')) track('contact_click', {contact_method: 'phone'});
    if (link?.getAttribute('href').startsWith('mailto:')) track('contact_click', {contact_method: 'email'});
  });
  if (!choice) show();
  if (allowed) start(); else cleanCookies();
});
