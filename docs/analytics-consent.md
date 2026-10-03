# Analytics and consent

Theme 1.0.76 adds GA4 integration and consent controls and is installed on Local and live. The current bilingual privacy text is published on both sites with its draft notice and unresolved controller details preserved. Google Site Kit 1.188.0 is installed and active on both installations; its account/property configuration remains unverified. Analytics is disabled until a real measurement ID is saved and the production switch is enabled. `.local`, `.test`, localhost, non-production WordPress environments and logged-in visits are excluded.

## Google account connection

Google's official Site Kit plugin, slug `google-site-kit`, provides the WordPress connection to the business's Google account and dashboards. Both installations use the same official WordPress.org 1.188.0 package and passed plugin checksum verification. Complete account setup in the live WordPress Site Kit screen at `https://janogago.lv/wp-admin/admin.php?page=googlesitekit-dashboard`. Google does not support setup on a local-only `.local` website. The Local installation is available for development, without connecting production account credentials.

`mu-plugins/janogago-site-kit-consent.php`, version 1.0.0, is tracked in this repository and installed in `wp-content/mu-plugins` on both sites. It blocks Site Kit's automatic Analytics and Tag Manager tags, including AMP variants, through Site Kit's existing filters. Account connections and dashboard reports remain available; the JāņogaGO consent integration remains the sole Analytics loader. Connecting Site Kit alone therefore does not start visitor tracking. Do not enable additional advertising services without adding their consent controls.

The droplet installation backed up the database and original plugin list at `/var/backups/janogago/site-kit-20261003-190942.5Vo5d6ys/`. It did not deploy the local theme or privacy drafts, and preserved existing page content and plugins. The guard is outside the theme and is not transferred by the theme sync script; include it explicitly in future installations. Site Kit can receive normal WordPress plugin updates.

References: [Official Site Kit installation](https://sitekit.withgoogle.com/documentation/getting-started/install/), [local/staging limitations](https://sitekit.withgoogle.com/documentation/using-site-kit/staging/), [Site Kit code placement](https://sitekit.withgoogle.com/documentation/using-site-kit/manage-site-kit-placed-code/).

## Visitor controls

The fixed bottom banner follows the theme's cream/ink/green palette, with Playfair Display headings and Manrope text. Accept and reject have equal prominence. It covers one optional purpose: analytics. Necessary language and preference storage is explained in the banner. The footer's Cookie settings control reopens the choices. It is a nonmodal section; keyboard focus returns to the footer after a decision, and Escape closes an existing preference view without changing it. On smaller screens, the actions sit below the copy; the banner scrolls internally if it exceeds 75% of the viewport height. No-JavaScript visits have policy links and no Analytics tag.

`jg_consent` stores a version, Boolean analytics choice and timestamp for 180 days, shared across LV/EN routes. It uses `SameSite=Lax`, path `/`, and `Secure` on HTTPS. Invalid, expired and old-version choices require a new decision. An immediately removed localStorage notification synchronises open tabs; visibility changes also reread the preference cookie. Withdrawal disables the GA property, removes `_ga`/`_ga_*` cookies at the host and parent domain, and reloads to unload the tag. It does not request deletion of already collected Google data.

## Collection

Basic Consent Mode v2: no Google tag or Analytics request before acceptance or after rejection. Ads storage, ads user data and ads personalisation stay denied. Google signals and ads personalisation signals are disabled in the tag configuration. Analytics cookie lifetime is capped at 180 days, with cookie refresh disabled.

Authored events are `page_view`, `generate_lead` and `contact_click`. Lead events occur only after the server confirms a newly saved enquiry, excluding honeypot successes and duplicate retries. Contact events contain only `phone` or `email`; lead events contain no form fields. URLs and referrers passed to GA omit query strings and fragments. The GA tag can also produce standard automatic events; the stream’s Enhanced Measurement settings must be reviewed before enabling it. Do not enable automatic form-interaction collection, site-search or other URL-based collection without reviewing the payloads.

Existing Manrope, Playfair Display and DM Mono files are hosted in the theme, with SIL Open Font Licenses in `assets/fonts`. Google Fonts no longer loads on site visits. The stylesheet preserves the existing requested font weights.

## Editing

WordPress → Settings → Privacy & Analytics holds the measurement ID, production switch, LV/EN privacy page selections, banner text and all button/link labels. Intentionally empty saved copy stays empty. Pages → Privātums un sīkdatnes / Privacy & cookies holds the policy copy as native Gutenberg Heading and Paragraph blocks. Footer and enquiry forms link to the selected published page for the current language.

The three external website links within each policy open in a new tab with `rel="noopener noreferrer"`. These are saved Gutenberg link attributes on both Local and live, and the Local seed uses the same attributes. Email links retain their normal behavior. Verified all four policy pages and browser tab behavior on 2026-10-03. Before-change page backups: Local `/private/tmp/janogago-privacy-links-local-20261003.json`, live `/var/backups/janogago/privacy-links-20261003.json`.

`scripts/setup-local-privacy.php` is an explicit, Local-only setup. It created LV page **263** (`/privatums/`) and EN page **264** (`/en/privacy/`) and linked their Polylang translations. They are published locally for preview and prominently marked as drafts. Reruns preserve existing page content, including deliberately empty content, and analytics settings. Trashed or permanently deleted pages stay deleted, and deliberately cleared page selections stay empty. Setup seeds a language only when its page selection has never been configured; it is never automatic on theme updates.

The recipients/retention section was rewritten in both local pages to remove its bracketed editing notes, then released with the pages to live. It names the verified public-site providers, DigitalOcean hosting and Gmail enquiry delivery, and states that Analytics is currently disabled. Local WP Mail SMTP is configured for SendLayer, while live is configured for Gmail; the policy describes the public site. The text uses purpose-based retention criteria without inventing numeric periods. Exact operational retention and the controller identity still need confirmation before the notice is finalised. All other page blocks were preserved. The updated initial setup copy matches this section.

## Live release, 2026-10-03

Live privacy pages are LV **154**, `https://janogago.lv/privatums/`, and EN **155**, `https://janogago.lv/en/privacy/`. Polylang links them, and `jg_privacy` selections use those live IDs rather than Local's IDs. Current local page content was copied without changes, including the visible draft notice and unresolved legal-company placeholder. The live Analytics switch is explicitly false. Footer, banner and enquiry-form policy links resolve to the current language.

Reviewed theme and selective privacy-content dry runs before applying. Full private database/theme backup: `/var/backups/janogago/privacy-20261003-192600.KtjkdyNY/`. Additional theme-only rollback archive: `/var/backups/janogago/theme-20261003-192606.8zVU0Pwn/theme.tar.gz`. Existing pages, products, enquiries and mail/Site Kit settings were compared against the preflight snapshot and preserved. All 61 deployed theme file hashes match the repository. Live desktop LV/mobile EN checks verified the banner, secure consent cookie, acceptance/rejection/reload, language persistence, policy text and translation links, catalog/login responses, absence of horizontal overflow and absence of Google Analytics/Google Fonts requests. No test enquiries or telemetry were sent.

## Before finalising the notice and enabling Analytics

Confirm the controller’s legal company name, registration number, registered address and privacy contact; actual enquiry, server-log, email and backup retention; and applicable Google processing terms and international transfer safeguards. Replace the remaining legal-company placeholder and draft warning in both policies. The current live pages are explicitly draft text, not a completed privacy notice.

Create or select a GA4 property owned by the business, with a web stream for `https://janogago.lv/`. Supply its `G-…` measurement ID. Use a documented user/event retention period (2 months is a conservative starting choice; aggregate-report retention differs), turn off Google signals and advertising features, and review account data-sharing settings. Review Enhanced Measurement, especially form interactions, search, outbound clicks and history-based page views, against the policy and URL sanitisation. Mark `generate_lead` as a key event if enquiry conversion reporting is wanted. Do not install another plugin that injects a second tag.

Review code/content dry runs before a user-requested live release. The standard theme-only deployment does not transfer these new policy pages or `jg_privacy` options; migrate these explicitly with correct live page IDs and Polylang links, preserving live edits. Verify the policies, banner and absence of Google requests before consent on HTTPS, then enable analytics when the property and text are ready.

## Validation

Run `scripts/tests/privacy.cjs` with Playwright available through `NODE_PATH`; set `CHROMIUM_EXECUTABLE_PATH` if using installed Chrome. It accepts only a `.local` test URL. Google requests are intercepted and the tag is mocked, so no test telemetry is transmitted. Checks cover first visit, rejection/reload, language persistence, saved and invalid preferences, withdrawal across tabs, cookie deletion, URL/private-data exclusion, local exclusion, local fonts, mobile overflow and no-JavaScript policy links.

`scripts/tests/enquiry-submissions.php` uses Local WP-CLI and intercepts mail. It also checks that only a newly saved lead sets `lead_created=true`. Syntax and whitespace checks are required for changed theme files. Actual receipt in GA4 reports remains unverified until a real production property is supplied and enabled.

References: [Google basic vs advanced consent mode](https://developers.google.com/tag-platform/security/concepts/consent-mode), [Google consent implementation](https://developers.google.com/tag-platform/security/guides/consent), [GA data retention](https://support.google.com/analytics/answer/7667196), [Latvian DVI cookie policy guidance](https://www.dvi.gov.lv/lv/jaunums/dviskaidro-kas-ir-sikdatnu-politika).
