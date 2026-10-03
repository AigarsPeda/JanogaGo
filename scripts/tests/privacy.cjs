/** Local browser checks. Google requests are intercepted, never transmitted. */
const assert = require('node:assert/strict');
const {chromium} = require('playwright');
const origin = process.env.JG_TEST_URL || 'http://janogago.local';
if (!new URL(origin).hostname.endsWith('.local')) throw new Error('Local only.');

(async () => {
  const browser = await chromium.launch({headless: true, ...(process.env.CHROMIUM_EXECUTABLE_PATH ? {executablePath: process.env.CHROMIUM_EXECUTABLE_PATH} : {})});
  const contexts = [];
  async function fixture({tag = false, width = 1440, choice, javaScriptEnabled = true} = {}) {
    const context = await browser.newContext({viewport: {width, height: 900}, reducedMotion: 'reduce', javaScriptEnabled});
    contexts.push(context);
    if (choice !== undefined) await context.addCookies([{name: 'jg_consent', value: encodeURIComponent(typeof choice === 'string' ? choice : JSON.stringify(choice)), url: origin}]);
    let googleRequests = 0;
    let fontRequests = 0;
    context.on('request', request => { if (/fonts\.(googleapis|gstatic)\.com/.test(request.url())) fontRequests++; });
    await context.route(/https:\/\/[^/]*(?:google-analytics|googletagmanager|doubleclick)\.[^/]+\//, async route => {
      googleRequests++;
      if (route.request().url().includes('/gtag/js')) await route.fulfill({contentType: 'application/javascript', body: "window.__testTagLoaded = true; document.cookie='_ga=mock; path=/'; document.cookie='_ga_TEST=mock; path=/';"});
      else await route.abort();
    });
    if (tag) await context.addInitScript(() => {
      let config;
      Object.defineProperty(window, 'jgPrivacy', {
        get: () => config,
        set: value => { config = {...value, measurementId: 'G-TEST123'}; },
      });
    });
    const page = await context.newPage();
    await page.goto(origin + '/');
    await page.waitForLoadState('networkidle');
    return {context, page, requests: () => googleRequests, fonts: () => fontRequests};
  }
  const choice = analytics => ({v: 1, analytics, at: Date.now()});
  const events = page => page.evaluate(() => (window.dataLayer || []).filter(item => item[0] === 'event').map(item => [item[1], item[2]]));
  try {
    const local = await fixture();
    assert.equal(await local.page.locator('#jg-cookie-banner').isVisible(), true);
    assert.equal(local.requests(), 0);
    assert.equal(local.fonts(), 0, 'Font hosting must not contact Google.');
    await local.page.locator('[data-cookie-choice="true"]').click();
    await local.page.waitForLoadState('networkidle');
    assert.equal(local.requests(), 0, 'Local analytics must stay excluded even after acceptance.');
    await local.page.goto(origin + '/en/');
    assert.equal(await local.page.locator('#jg-cookie-banner').isVisible(), false);
    await local.page.getByRole('button', {name: 'Cookie settings', exact: true}).click();
    assert.equal(await local.page.locator('#jg-cookie-banner').isVisible(), true);
    await local.page.keyboard.press('Escape');
    assert.equal(await local.page.locator('#jg-cookie-banner').isVisible(), false);

    const enabled = await fixture({tag: true});
    await enabled.page.goto(origin + '/?email=private%40example.invalid#secret');
    await enabled.page.waitForLoadState('networkidle');
    assert.equal(enabled.requests(), 0, 'No tag before consent.');
    await enabled.page.evaluate(() => document.dispatchEvent(new CustomEvent('jg:enquiry-sent')));
    assert.deepEqual(await events(enabled.page), []);
    await enabled.page.locator('[data-cookie-choice="false"]').click();
    await enabled.page.reload();
    await enabled.page.waitForLoadState('networkidle');
    assert.equal(enabled.requests(), 0, 'Rejection must remain effective after reload.');
    await enabled.page.locator('[data-cookie-settings]').click();
    await enabled.page.locator('[data-cookie-choice="true"]').click();
    await enabled.page.waitForFunction(() => window.__testTagLoaded === true);
    assert.equal(enabled.requests(), 1);
    const calls = await enabled.page.evaluate(() => (window.dataLayer || []).map(item => [...item]));
    assert.equal(calls[0][0], 'consent');
    assert.equal(calls[0][2].analytics_storage, 'denied');
    assert.equal(calls[1][2].analytics_storage, 'granted');
    assert.equal(calls[1][2].ad_user_data, 'denied');
    assert.equal(calls[1][2].ad_personalization, 'denied');
    assert.equal(calls[3][2].allow_google_signals, false);
    assert.equal(calls[3][2].cookie_expires, 15552000);
    assert.equal(JSON.stringify(calls).includes('private'), false, 'URLs must not leak query strings or fragments.');
    await enabled.page.evaluate(() => {
      document.dispatchEvent(new CustomEvent('jg:enquiry-sent', {detail: {email: 'private@example.invalid'}}));
      const a = document.createElement('a'); a.href = 'mailto:private@example.invalid'; document.body.append(a);
      a.addEventListener('click', event => event.preventDefault()); a.click(); a.remove();
    });
    const tracked = await events(enabled.page);
    assert.deepEqual(tracked.map(item => item[0]), ['page_view', 'generate_lead', 'contact_click']);
    assert.equal(JSON.stringify(tracked).includes('private'), false, 'No form or contact values in events.');

    // A second open language tab must stop too when the first withdraws.
    const second = await enabled.context.newPage();
    await second.goto(origin + '/en/');
    await second.waitForFunction(() => window.__testTagLoaded === true);
    const beforeWithdrawal = enabled.requests();
    await enabled.page.locator('[data-cookie-settings]').click();
    await enabled.page.locator('[data-cookie-choice="false"]').click();
    await enabled.page.waitForLoadState('networkidle');
    await second.waitForLoadState('networkidle');
    await second.waitForFunction(() => !window.__testTagLoaded);
    assert.equal(enabled.requests(), beforeWithdrawal, 'Withdrawal must not send a consent ping or reload the tag.');
    assert.equal((await enabled.context.cookies()).some(cookie => /^_ga(?:_|$)/.test(cookie.name)), false);
    assert.equal(await enabled.page.locator('#jg-cookie-banner').isVisible(), false);

    for (const invalid of [choice(false), {...choice(true), v: 0}, {...choice(true), at: Date.now() - 181 * 86400000}, 'bad-json', {...choice(true), at: Date.now() + 86400000}]) {
      const f = await fixture({tag: true, choice: invalid, width: 390});
      assert.equal(f.requests(), 0, 'Denied, expired, malformed or outdated choices must not load Analytics.');
      if (typeof invalid !== 'object' || invalid.v !== 1 || invalid.analytics) assert.equal(await f.page.locator('#jg-cookie-banner').isVisible(), true);
      assert.equal(await f.page.evaluate(() => document.documentElement.scrollWidth > innerWidth), false);
    }
    const returning = await fixture({tag: true, choice: choice(true)});
    await returning.page.waitForFunction(() => window.__testTagLoaded === true);
    assert.equal(returning.requests(), 1, 'Valid saved acceptance loads once.');
    assert.equal(await returning.page.locator('#jg-cookie-banner').isVisible(), false);

    const nojs = await fixture({javaScriptEnabled: false});
    assert.equal(nojs.requests(), 0);
    assert.equal(await nojs.page.locator('#jg-cookie-banner').isVisible(), false);
    assert.equal(await nojs.page.locator('.jg-privacy-links a').isVisible(), true);
    assert.equal(await nojs.page.locator('[data-cookie-settings]').isVisible(), false);
    for (const path of ['/privatums/', '/en/privacy/', '/edieni/', '/en/food/']) {
      const response = await nojs.page.goto(origin + path);
      assert.equal(response.status(), 200);
    }
    console.log('PASS: consent gating, rejection/reload, language persistence, saved/expired/malformed choices, withdrawal across tabs, cookie deletion, PII-free events, local exclusion, local fonts, mobile overflow and no-JS policy links. Google tag mocked; no telemetry transmitted.');
  } finally { for (const context of contexts) await context.close(); await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });
