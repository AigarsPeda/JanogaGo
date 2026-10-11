# JāņogaGO website handoff

## Goal

Maintain the bilingual WordPress site, with authored content editable in WordPress and layout and behavior in the theme.

## Current state

As of 2026-10-03:

- The live site uses **https://janogago.lv/**. NIC.lv DNS has an apex A record to `165.232.119.106` and a `www` CNAME to `janogago.lv`. HTTP, the old HTTP IP address and HTTPS `www` redirect to the canonical HTTPS domain, preserving paths.
- Nginx serves a Let's Encrypt ECDSA certificate for both names, initially expiring 2027-01-01. `certbot.timer` renews it automatically; `/etc/letsencrypt/renewal-hooks/deploy/reload-nginx.sh` validates and reloads Nginx after renewal. Both WordPress URL settings, stored content/media URLs and the ignored deployment configuration now use the HTTPS domain. GUIDs were preserved.
- HTTPS and certificate verification passed in Zen for both language homepages, with the 3D hero rendering. Both catalogs and the login page returned HTTP 200. HTTP and HTTPS `www` redirects preserve paths, and `certbot renew --dry-run --run-deploy-hooks --no-random-sleep-on-renew` passed, including the Nginx reload hook.
- Local and the droplet run theme **1.0.79**, verified on 2026-10-04.
- Rank Math 1.0.279 on the droplet now loads its dashboard, page editor panel and SEO Details column. Its initial account-registration screen had prevented those tools from loading. The account connection was skipped; existing settings were preserved. Missing page focus keywords were added, and the official browser Recalculate Scores tool calculated all seven published pages on 2026-10-04. Verified saved scores: LV homepage 25, EN homepage 33, LV catalog 61, EN catalog 67, LV privacy 23, EN privacy 26, Sample Page 21. No live page displays N/A. Database rollback: `/var/backups/janogago/rank-math-20261004/database.sql`. Local also has the registration skip and missing keywords, but its scores have not yet been recalculated. Local backup: `/tmp/janogago-rank-math/local-before.sql`. Helper: `scripts/setup-rank-math-scores.php`; no theme release was needed.
- Local and live have 22 food products, six categories and four featured homepage dishes, reconciled on 2026-10-10. The catalog shows all matching dishes without pagination. See the reconciliation entry below for current photos, ordering and text synchronization.
- Both homepages have a dark machine-photo section after the catering experience and before client logos.
- Both languages use the rotating 3D hero, with a matching rendered preview during loading and without JavaScript. Model/preview load errors restore the original machine photo. The sealed GLB is attachment **204** locally and **142** live; live fallback photo is **134**.
- Homepage and catalog headings have no trailing periods in either language. Questions and paragraph punctuation are preserved.
- The 3D implementation, media-sync changes and related documentation have uncommitted changes. Check `git status` before committing.

## Locations

| Item | Location |
| --- | --- |
| Repository / theme source | `/Users/aigarspeda/Desktop/JanogaGo` / `theme/janogago` |
| Local website | `http://janogago.local/` |
| Local WordPress | `/Users/aigarspeda/Local Sites/janogago/app/public` |
| Installed local theme | Local WordPress `wp-content/themes/janogago` |
| Live website / WordPress | `https://janogago.lv/` / `/var/www/janogago` |
| Deployment configuration | `scripts/janogago-droplet.env`, ignored by Git |
| Blender model | `/Users/aigarspeda/Desktop/JanogaGo-doc/automati/janoga-smart-fridge.blend` |

Do not commit deployment configuration or SSH keys.

Homepages are LV **6** and EN **7**. Catalog URLs are `/edieni/` and `/en/food/`; local page IDs are **152/153**, live IDs **111/112**. Polylang links the language pages.

## WordPress editing requirements

- Keep all visitor-facing copy and images editable through Gutenberg, Media Library or the relevant WordPress setting. Routine content changes must work through remote WordPress admin without code edits or deployment.
- Existing Gutenberg content is authoritative. Seed strings and setup scripts must preserve later edits, deletions and deliberately empty pages. Do not reset content-migration markers during normal updates.
- Import new content photos into WordPress uploads with Media Library attachments. Do not add photo copies to the theme or repository. Shared media can be used by both languages; check its uses before deleting it.
- Homepage headings, sections, FAQs, form labels/messages, contact details and footer text are native blocks. Edit LV and EN separately. Header/footer branding uses WordPress Custom Logo; navigation uses WordPress menus.
- The header CTA reads the first page Button block, including its saved HTML URL. Removing that Button also removes the header CTA.
- Food is managed under **Ēdieni / Products**. Each dish has an LV title, EN name, optional descriptions, Featured image, category, Order and homepage/dietary switches. Categories have EN names; catalog interface copy is under **Catalog labels**. Apply dietary labels only after recipe review.

Homepage order: hero, featured food, catering experience, machine photos, client logos, managed service, location suitability, cooperation steps, FAQs, enquiry form, footer. The experience figures are 20 years, 250+ companies and 650+ daily café diners, referring to the catering business. Equipment, costs, demand thresholds, service territory and cooling/reheating arrangements are agreed in the proposal; avoid adding unsupported promises.

## Main code

Paths below are relative to `theme/janogago` unless prefixed with `scripts/`.

| Files | Responsibility |
| --- | --- |
| `functions.php`, `inc/business-content.php` | Theme setup, initial block content, migrations, enquiry handling and editable form/footer rendering |
| `inc/product-catalog.php`, `page-food.php` | Shared food records, translations, catalog and filters |
| `inc/hero-model.php`, `assets/js/hero-model-editor.js` | Optional GLB on native Image blocks, upload validation and Gutenberg controls |
| `assets/js/hero-model.js`, `assets/js/vendor/` | Hero rotation and bundled model-viewer 4.2.0 with Apache license |
| `style.css`, `assets/css/business.css`, `assets/css/food.css` | Theme version, layout, responsive styles and catalog styling |
| `assets/js/site.js`, `assets/js/enquiry-form.js`, `assets/js/counters.js` | Navigation, FAQs, AJAX enquiries, catalog interactions, mobile logo carousel and experience counters |
| `header.php`, `footer.php`, `assets/css/editor.css` | Site chrome and Gutenberg editing cues |
| `scripts/build-smart-fridge.py` | Reproducible Blender model build |

## 3D hero

Select the hero Image block in Gutenberg and open **3D machine / 3D automāts** to replace/remove its GLB, matching 3D preview or accessible pause/resume labels. The native Image remains the original photo and supplies alt text and the error fallback. The preview is attachment **223** locally and **145** live, exported from the same renderer at the initial camera/lighting. Replacing the GLB clears the preview; regenerate/select it for the new model. Media IDs must be mapped between installations.

The model has six shelves, 149 individually editable packaging meshes and packed source textures. Shapes and unseen packaging details are approximate. Its rear is opaque; front artwork masks transparent cut-out fringe, and side wraps preserve logo proportions. The GLB is about 4.16 MB. Model construction, source references and provisional **H1930 × W1105 × D760 mm** dimensions are in [docs/smart-fridge-model.md](docs/smart-fridge-model.md). Dimensions are unconfirmed for the exact customer variant and are not installation clearances.

The model starts front-on at `0deg 90deg 4.74m`, centered to match the rendered preview. The built-in loading bar is hidden; the matching preview covers loading. After two paint frames the model fades in for 320 ms over the unchanged preview, then starts a 32-second turn between −15° and +15°. Reduced motion skips the fade and autoplay; load errors restore the original photo and cancel any pending reveal. It pauses offscreen, in hidden tabs, on direct interaction and for reduced motion. A 44px icon-only pause/play control sits at the upper-right. Manual rotation stays centered on `0m .965m 0m`; panning, tap-to-recenter and zoom are disabled. Preserve these settings to prevent clicks moving the fridge out of frame.

Rebuild through Blender's Python runner, passing `--output`, `--photo` and `--wrap` after Blender's `--`. Source references are in `/Users/aigarspeda/Desktop/JanogaGo-doc/automati/cut-out` and `/Users/aigarspeda/Desktop/JanogaGo-doc`. `scripts/setup-local-hero-model.php` is a one-time Local setup, not a routine model updater.

## Behavior to preserve

- Mobile logos use native horizontal scrolling, a fixed repeated track and recentering. Touch/wheel input pauses autoplay until momentum settles. Do not replace this with a transform marquee or insert lazy-loaded slides during swipes. Reduced motion disables autoplay.
- Catalog filters update results and browser history without a full reload, with normal GET forms as fallback. Mobile filters collapse after applying; desktop filters and category headings are sticky. Long dish names must remain readable.
- Experience counters read the authored Paragraph text and run once; reduced motion and no JavaScript show final values.
- An enquiry counts as successful when its private lead is saved, even if internal notification mail fails. Mail outcome stays in lead metadata. The dismissible green success notice overlays the layout; AJAX sends must retain scroll position and allow a second submission. Clear custom validity on input/change and before validation; preserve entries on errors. No-JS uses the existing POST redirect fallback.

## Local work and deployment

Edit repository source, check changed PHP/JS syntax and `git diff --check`, then copy changed theme files into the installed Local theme. Bump `Version:` in `style.css` for cached CSS/JS changes. Check both languages, desktop/mobile layout, keyboard controls and reduced motion as relevant.

Local WP-CLI uses `/Applications/Local.app/Contents/Resources/extraResources/bin/wp-cli/wp-cli.phar`, PHP at `/Users/aigarspeda/Library/Application Support/Local/lightning-services/php-8.2.30+1/bin/darwin-arm64/bin/php`, the Local WordPress path above, and `-d mysqli.default_socket='/Users/aigarspeda/Library/Application Support/Local/run/d3d0ClKR2/mysql/mysqld.sock'`. Start the site in Local first; service paths can change after Local updates.

`scripts/wordpress-content-sync.php` transfers Image-block photos and GLBs, remaps `id`, `jgModelId` and `jgModelPosterId`, validates GLB headers/checksums and reuses matching Media Library records. Its Local integration test covers model import/export, dimension metadata, original-image preservation and repeat runs.

For user-requested releases, review the corresponding dry run first:

| Script | Scope |
| --- | --- |
| `scripts/sync-code-to-droplet.sh` | Theme code; archives the previous live theme |
| `scripts/sync-content-to-droplet.sh` | Selected homepage blocks and referenced media; supports `--languages=lv` or `--languages=en` |
| `scripts/sync-food-to-droplet.sh` | Products, categories, catalog labels/pages and photos; review products marked for trash |
| `scripts/release-food-to-droplet.sh` | Combined theme, catalog and homepage release |
| `scripts/sync-uploads-to-droplet.sh` | Files only; does not create Media records or transfer page content |

Selective sync preserves users, enquiries and unrelated content, but overwrites selected live content with Local content. Check for newer live edits first. Full database replacement via `push-db-to-droplet.sh --yes` requires explicit user authorization. Deployment details are in [README.md](README.md).

The latest theme-only rollback archive is `/var/backups/janogago/theme-20261003-133639.axLeoekx/theme.tar.gz`. The archive before the enquiry UX changes is `/var/backups/janogago/theme-20261003-133419.8WZAhKNv/theme.tar.gz`. The latest homepage/media database backup is `/var/backups/janogago/content-20261002-215933.RzwWUXmN/`. The latest full live rollback backup is `/var/backups/janogago/food-release-20261002-212535.P6S7buuI/`, containing database and theme. Future release scripts create new private backups. After deployment, verify LV/EN pages, media, asset versions and affected interactions.

## Outstanding checks

- Theme **1.0.76** on Local and live adds the consent banner, GA4 settings, policy links and self-hosted fonts. Analytics is disabled pending the real GA4 ID and property configuration. Settings → Privacy & Analytics edits bilingual banner copy and policy selections. Local privacy pages **263/264** and live pages **154/155** are Gutenberg content at `/privatums/` and `/en/privacy/`, linked through Polylang. The current local text, including the draft notice and unresolved legal-company placeholder, was released to live at the user's request. Hosting/email providers are verified; controller identity and exact retention remain unfinished. See [docs/analytics-consent.md](docs/analytics-consent.md); future theme-only sync does not transfer page copy or settings.
- Privacy release backup: `/var/backups/janogago/privacy-20261003-192600.KtjkdyNY/`, containing database, theme and preflight snapshot. Additional theme rollback: `/var/backups/janogago/theme-20261003-192606.8zVU0Pwn/theme.tar.gz`. Existing live content and mail/Site Kit settings were preserved. All 61 theme file hashes match the repository; live desktop LV/mobile EN checks passed for consent, privacy links/translations, saved choices, layout and catalog/login responses. No Analytics or Google Fonts requests were sent, including after accepting analytics while it remains disabled.
- **Google Site Kit 1.188.0** is installed and active on Local and the droplet, with official package checksums verified. Google account setup remains pending and should happen on live; Google does not support setup on local-only `.local` sites. `mu-plugins/janogago-site-kit-consent.php` **1.0.0** is installed on both and blocks Site Kit's automatic Analytics/Tag Manager tags so the existing consent integration owns collection. Real Site Kit web/AMP filter checks passed, and the local consent suite passed after activation. Droplet rollback database and previous plugin list: `/var/backups/janogago/site-kit-20261003-190942.5Vo5d6ys/`. No theme/content was deployed during this plugin installation.

The domain migration backup is `/var/backups/janogago/domain-20261003.vv0h0psi/`, containing the pre-migration `database.sql.gz` and `nginx.conf`. DNS was initially unpublished; NIC's nameservers and public resolvers subsequently returned the correct records, allowing certificate issuance. Keep DNS pointed to this droplet for HTTP certificate validation and renewal.

- The new Gutenberg 3D controls still need an authenticated visual edit/save check. Server-side registration and frontend loading/rotation were verified.
- Real iPhone Safari momentum and actual enquiry inbox delivery remain unverified. Existing local enquiry tests intercept email; avoid sending live test enquiries without authorization.

Relevant regression checks are `scripts/tests/hero-model.cjs`, `scripts/tests/client-carousel.cjs`, `scripts/tests/content-sync.php`, `scripts/tests/enquiry-notifications.php` and `scripts/tests/enquiry-submissions.php`. Run checks appropriate to the change; the WordPress integration tests are Local-only.

## Enquiry fixes, 2026-10-03

Theme 1.0.75 submits enquiry forms through the existing admin-post handler with JSON feedback. Native editable labels and success messages remain in the page blocks. Validation gives field-specific errors; requests refresh the nonce, retain entries on errors and reset after success. `inc/enquiry.php` provides LV/EN feedback, honeypot and short database-lock-protected counters. Five distinct enquiries per ten minutes per IP/email are accepted; identical completed retries return success without another lead/email and pending retries return busy. The rate guard holds no lock during mail sending.

Both local integration suites passed with intercepted mail, including recipient changes and account password-reset routing. CUA browser checks passed for a corrected invalid email, two consecutive desktop LV sends and an English mobile send. Scroll stayed at the form within one pixel on desktop and exactly on mobile. No external test mail was sent. Temporary local browser fixtures and mail interceptor are removed after verification. Enquiry recipient is `info@janoga.lv`, editable under Settings → General. Gmail API mailer is authorized according to the user, who confirmed delivery.

Deployed theme 1.0.75 after local verification. All changed JS/CSS/helper hashes match live; deployed PHP syntax passed. Live browser confirmed the new email-specific error, unchanged URL and versioned script. Live HTTP validation returned 422 JSON with email/phone/people errors and a fresh nonce, creating no lead/email. Only theme files were deployed, preserving live content and Gmail authorization. Rollback archive: `/var/backups/janogago/theme-20261003-133419.8WZAhKNv/theme.tar.gz`.

Final server review also verified that string `0` is rejected as an email or phone number. Local submission tests passed again before the final PHP-only sync. Latest pre-adjustment archive: `/var/backups/janogago/theme-20261003-133639.axLeoekx/theme.tar.gz`.

## Logo release, 2026-10-04

Theme 1.0.77 replaces the fallback text wordmarks with the supplied transparent PNGs: `assets/images/JanogaGoLogo.png` in the dark header and `assets/images/JanogaGoLogoDark.png` in the light footer. Both keep their original proportions at 174px wide and retain WordPress Custom Logo support. Local and live LV/EN pages load both variants. Changed templates pass PHP syntax checks; all five deployed files match repository checksums. The theme-only deployment preserves WordPress content and settings. Rollback archive: `/var/backups/janogago/theme-20261004-094401.m0hJiFK6/theme.tar.gz`.

Logo correction: theme 1.0.78 uses `JanogaGoLogoNew.png` in the header and `JanogaGoLogoDarkNew.png` in the footer, replacing the first supplied files. Original dimensions are 1305×309 and 1305×310; displayed width stays 174px. Both corrected images load locally and live; LV/EN markup and all five deployed file checksums are verified. The superseded theme assets are removed from the repository and droplet. Latest rollback archive: `/var/backups/janogago/theme-20261004-100143.CkOSZYHm/theme.tar.gz`.

Header loading fix: theme 1.0.79 embeds the exact corrected PNG as a data URI in the header, avoiding a separate image request. The link supplies its accessible name; the decorative image has empty alt text and synchronous decoding, preventing the old styled fallback text from appearing while the PNG loads. The user reported the first-load flash in the top-left header. No logo-switching script or page cache was found; the text fallback was the likely cause. Local browser rendering and live LV/EN embedded bytes were verified. Rollback archive: `/var/backups/janogago/theme-20261004-100750.zIyTyHfg/theme.tar.gz`.

## Local page refresh, 2026-10-07

Pulled the droplet's authored LV/EN homepage, catalog-page and privacy-page content into Local. Local page IDs, slugs, Polylang links, templates and metadata remain intact. Droplet media IDs and URLs were mapped to the existing local attachments; all 11 referenced images/GLB files matched by checksum, so no new media imports were needed. Content verification passed for all six pages, with no live-site URLs left in their blocks. All six local routes load successfully; `/en/` redirects to `/en/home/`. Browser checks confirmed the newer LV/EN homepage copy and rendered 3D hero.

Local users, user metadata, products, enquiries, categories and page metadata match their pre-import hashes. Site configuration matches the backup; only routine cron, Rank Math IndexNow log and WP Mail SMTP notification-state options changed during WordPress page saves. The live pages and configuration were not changed. Full local database rollback backup: `/tmp/janogago-page-pull/local-before.sql.gz`; page/media snapshots and verification records: `/tmp/janogago-page-pull/backup/`. This was a page-content refresh, so live SEO metadata and scores were not imported. Temporary droplet export files were removed.

Privacy punctuation correction, 2026-10-07: removed only the square brackets around the company/controller details on both Local privacy pages (263/264) and both live privacy pages (154/155). Other wording and blocks were preserved. Saved-content checks and HTTP verification passed on all four routes. Local rollback snapshot: `/tmp/janogago-privacy-brackets-local-before-20261007.json`; live database and page snapshots: `/var/backups/janogago/privacy-brackets-20261007.EjCYrx6Q/`. Temporary remote helper was removed.

## Dish-name corrections, 2026-10-07

Matched four catalog photos to the supplied dish labels and updated their LV titles and `_jg_name_en` fields in Local and live WordPress:

| Local / live product | Latvian | English |
| --- | --- | --- |
| 179 / 125 | Siera salāti | Cheese salad |
| 172 / 119 | Cūkgaļas kotlete ar ceptiem kartupeļiem | Pork cutlet with roast potatoes |
| 177 / 123 | Vistas filejas karbonāde ar kartupeļu biezeni | Chicken fillet schnitzel with mashed potatoes |
| 175 / 121 | Cūkgaļas karbonāde ar kartupeļu biezeni | Pork schnitzel with mashed potatoes |

The four Local/live original-photo checksums matched before updating. Post-update comparisons verified all 19 products: only the four LV titles, EN names and normal modification timestamps changed. Photos, categories, slugs, descriptions, ordering and dietary/homepage flags were preserved. Both live catalog pages show the corrected headings and image alt text in the browser. No theme release or full catalog sync was needed.

Live database and product rollback snapshots: `/var/backups/janogago/dish-renames-20261007.cmaYxKVV/`. Local database backup and verification records: `/tmp/janogago-dish-renames/`.

## Search Console and sitemap cleanup, 2026-10-10

Created and verified the Google Search Console HTTPS URL-prefix property `https://janogago.lv/` through the signed-in Google account. Verification uses `googlee1855318fb319cbb.html` in the live WordPress root; keep that file on the server. Search Console setup is independent of Site Kit, which was not connected or changed.

Installed `mu-plugins/janogago-sitemap.php` on live and Local. It uses Rank Math's `rank_math/sitemap/entry` filter at priority 99 to use `get_permalink()` for Polylang's translated front pages. The live page sitemap now has `/en/home/` rather than the redirect `/en/`. Source, live and installed Local MU-plugin hashes match; live PHP syntax and sitemap URL assertions pass. Ordinary theme deployment does not copy MU plugins.

Moved live Sample Page, ID 2, to WordPress Trash as requested. `/sample-page/` returns HTTP 404, and the page sitemap has only the six authored LV/EN homepage, food and privacy pages. Local Sample Page content was not changed. Pre-change live database backup: `/var/backups/janogago/search-console-20261010.Ig7P21/database.sql`.

Submitted `sitemap_index.xml` and, after the initial fetch status, `page-sitemap.xml` through Search Console. Both submission dialogs confirmed success, but the Sitemaps report initially showed `Couldn't fetch`, type Unknown and zero discovered pages. Google's live URL test of `sitemap_index.xml` confirmed Crawl allowed = Yes and Page fetch = Successful on 2026-10-10. Its `noindex` header applies to the XML document, not to the pages listed inside it. No server, DNS or robots blocker was found. Googlebot also received HTTP 200 for the sitemap in the live Nginx log. Sitemap processing status needs a later check; do not claim it is already Success.

Confirmed Search Console's `Indexing requested` dialog for all four main URLs: `/`, `/en/home/`, `/edieni/` and `/en/food/`. Each passed Google's automatic live eligibility check and was added to the priority crawl queue. This is a request, not confirmation that the URLs are already indexed. Manual actions report shows `No issues detected`. The Latvian homepage was newly crawled during setup and initially showed `Crawled - currently not indexed`; the other three were initially unknown to Google.

## Analytics setup completed, 2026-10-10

Created a dedicated Google Analytics account **JāņogaGO** (`411473984`) through live Site Kit, with property **janogago.lv** (`558230683`), web stream `16100796142`, measurement ID `G-0FNW1GE9X7`, and Latvia timezone. The user approved accepting the required Analytics and GDPR agreements. Enhanced Measurement was off during creation; optional Google account-sharing settings were all off. Site Kit confirmed initial setup complete and shows Search Console and Analytics gathering data, with up to 72 hours for dashboard statistics.

Live `jg_privacy` now enables `G-0FNW1GE9X7` while preserving its other options and page IDs. Disabled Site Kit's automatic snippet and plugin conversion tracking; the existing consent MU plugin still blocks duplicate/unconditional tags. Changed only the two future/disabled Analytics statements in each live privacy page to describe current consent-based collection. Controller details were already present. Local configuration/content and theme code were not changed. Live browser verification found no Google tag before acceptance, exactly one correct tag after acceptance, and no tag after withdrawal/reload. The verification browser was left rejecting Analytics. Event receipt in GA reports, property retention and `generate_lead` key-event classification have not been checked. Website rollback: `/var/backups/janogago/analytics-20261010.Z32a6G/database.sql`; Google account creation is separate from this backup. See `docs/analytics-consent.md` for the current configuration; earlier disabled/pending entries are historical.

## Local and live content reconciliation, 2026-10-10

Pulled the droplet's six authored LV/EN pages into Local with attachment-ID and URL mapping. All 11 page media files matched existing Local originals, so no page media imports were needed. The substantive new text was the two consent-based Analytics statements in each privacy policy. Local also received the live Rank Math page fields, public privacy configuration with local page IDs, and current homepage selections. The unset/empty catalog-label and dietary values were equivalent defaults. Local Sample Page and the unused default Privacy Policy page now match the droplet's Trash status. Public navigation text and category text already matched. Account credentials and environment-specific URLs were preserved.

Merged the Local food changes into the current droplet catalog rather than replacing either database. Both sites now have 22 dishes. New Local/live products are salmon sandwich **286/193**, shrimp Caesar salad **288/188**, and tuna-and-egg salad **290/190**. Their English names are Salmon sandwich, Caesar salad with shrimp, and Tuna and egg salad. New image originals have real transparency.

AI-cleaned tray photos now supply chicken schnitzel with mash and sour cream sauce **141/100**, using attachment **291/191**, and pork schnitzel with mash **175/121**, using attachment **292/186**. Original Media Library photos remain available. Only exposed sauce/grease marks on the trays were targeted; generated edits were visually reviewed before import.

Salads start with chicken Caesar, shrimp Caesar, and tuna-and-egg. Main courses start with pork cutlet with roast potatoes, pork schnitzel with mash, and grilled chicken with mash. The two promoted mains use Order **-1/0**; the new salads use **3/4**. The live-selected homepage dishes were retained: pork cutlet with roast potatoes, chicken Caesar, chicken sandwich, and chia dessert. Future catalog releases can use `scripts/sync-food-to-droplet.sh`, but always inspect newer live edits first.

Verification compared all 22 products by stable key, bilingual name, descriptions, category, dietary/homepage flags, order, original-photo checksum and alt text. Six authored page contents match after mapping site URLs and media IDs; page titles, excerpts, statuses, category text, public settings and Rank Math fields also match. All 12 public routes returned HTTP 200; both catalogs render 22 dishes and all five new/edited photos. Browser checks verified the live LV salad order and new photos, plus EN mains and cleaned trays. Hash checks confirmed users, enquiries/other posts, mail settings and Site Kit connections remained unchanged on each site. Local has the same public `jg_privacy` measurement ID/settings but emits an empty runtime measurement ID, so its Analytics exclusion remains active.

Rollback: Local `/tmp/janogago-reconcile-20261010/local-before.sql`; droplet `/var/backups/janogago/reconcile-20261010/database-before.sql`; catalog release backup `/var/backups/janogago/food-20261010-135149.zewQZzbd/database.sql.gz`. Content snapshots, mapped page packages and verification records are under `/tmp/janogago-reconcile-20261010/`. Browser proof is `output/validation/synchronized-live-catalog.jpg`. No theme or plugin code was deployed. Remote staging helpers were removed after verification. Outstanding Analytics property checks from the earlier entry remain pending.

## Local maintenance notice resolved, 2026-10-10

Local displayed WordPress maintenance mode after a marker was written at 15:29 Riga time. Neither update lock was active, the upgrade staging folders were empty, and no standalone updater process was running. Archived the unchanged `.maintenance` marker to `/tmp/janogago-maintenance-20261010/maintenance-before.php`. WP-CLI confirms maintenance mode inactive; all six authored Local routes return HTTP 200 without the notice. Browser verification shows the catalog and requested main-course order. Proof: `output/validation/local-maintenance-restored.jpg`. No database or droplet changes were made for this fix.

## Local pricing preview, 2026-10-10

Theme **1.0.80** is installed on Local only. Both homepages now show a conditional pricing summary before the opening CTA and a native Gutenberg pricing section immediately after the hero, anchored at `#izmaksas`. High-footfall locations show free installation, maintenance and daily restocking; smaller companies/offices show **150–350 EUR + PVN per month** for full upkeep and servicing, based on the client-supplied terms. Copy identifies these as service costs and leaves the location assessment and confirmed fee to the proposal. No numeric demand threshold was invented. The existing cost FAQ now matches the new figures. All new text is editable in Gutenberg.

Source styles are in `assets/css/business.css`; `scripts/setup-local-pricing.php` applies the bilingual preview with a `.local` guard, snapshots the prior content and preserves subsequent editor changes when pricing already exists. This helper is not a theme migration. Existing seed copy remains historical. Nothing was deployed to the droplet.

Desktop 1440px and mobile 390px checks passed for pricing layout and overflow. Mobile opening copy shows both conditions before the CTA. PHP syntax, diff whitespace and the style detector passed. Snapshot comparisons confirm unrelated root sections and every image/3D block remain unchanged on pages 6/7. Content rollback: `/var/folders/xc/s0pspdrj03d4l87l37mvyg4h0000gn/T/janogago-pricing-before-20261010-125217.json`; previous installed styles: `/tmp/janogago-pricing-theme-before-20261010/`. Preview screenshots and verification helper are under `/tmp/janogago-pricing-validation/`.

## Pricing release to droplet, 2026-10-10

Published the approved Local pricing preview to both live homepages. Theme **1.0.80**, the opening pricing summary, native pricing section and cost FAQ are now live. Preflight comparison confirmed the live LV/EN blocks still matched the pre-pricing Local snapshot after domain/media-ID normalization, so no newer authored homepage edits were overwritten. Theme dry run changed only `style.css` and `assets/css/business.css`. The content sync reused all 11 media references with zero imports; catalog, privacy and other page content were outside this release.

Both release scripts passed. The two deployed file hashes match repository source; saved LV/EN pricing blocks occur once and contain no Local URLs. Live browser checks verified the Latvian desktop pricing section, English mobile opening summary, responsive columns, versioned `business.css?ver=1.0.80`, no horizontal overflow and the updated English cost FAQ. Proof files: `/tmp/janogago-pricing-release/live-desktop.jpg` and `/tmp/janogago-pricing-release/live-mobile-en.jpg`.

Rollback theme archive: `/var/backups/janogago/theme-20261010-155549.jfzA1phw/theme.tar.gz`. Database and content rollback: `/var/backups/janogago/content-20261010-155615.FL9dQpaW/`, including `database.sql.gz` and `pages-before.json`. The Local-only setup helper was not installed on the droplet. The content-sync script removed its temporary staging helpers.

## PNG favicon and homepage recrawl, 2026-10-10

Changed the fallback `rel="icon"` in `header.php` from SVG to the existing 512×512 PNG, with `type="image/png"` and `sizes="512x512"`. The Apple touch icon already used that PNG. Repository, installed Local theme and live header checksums match. The existing `scripts/sync-code-to-droplet.sh` completed successfully after transient SSH connection timeouts; its dry run and deployment changed only `header.php`. No deployment scripts needed changes. PHP syntax and whitespace checks passed. Live rollback: `/var/backups/janogago/theme-20261010-171235.q0XYHpQ7/theme.tar.gz`; installed Local header before this change: `/private/tmp/janogago-favicon-local-header-before.php`.

The public PNG returns HTTP 200 with `Content-Type: image/png` for the Googlebot-Image user agent; its bytes match the repository asset. Robots.txt allows Googlebot to fetch the homepage and Googlebot-Image to fetch the icon. Search Console's Google Index view now confirms the homepage is indexed. Google's live test at 17:15 Riga time fetched the updated homepage successfully, and its tested HTML contains the PNG icon declaration. Requested a fresh homepage recrawl after deployment and confirmed the `Indexing requested` dialog. Google controls when its search-result favicon refreshes; this does not confirm the icon is already displayed in search results. Proof: `/private/tmp/janogago-favicon-google-html.png` and `/private/tmp/janogago-favicon-recrawl.png`.

## Separate email settings during sync, 2026-10-11

The user requires Local and the droplet to keep separate email-sending configuration during all synchronization. Recorded this policy in `AGENTS.md` and `README.md`. Read-only checks confirm Local uses SendLayer and the droplet uses Gmail; neither configuration was changed.

Updated the full database push/pull scripts to exclude source mail options, mail log/queue/task tables, WP-Cron and Action Scheduler jobs. The destination retains its SMTP connections/tokens, legacy SMTP settings, administrator email/change state and enquiry recipient continuously. Other options merge in an InnoDB transaction, using WP-CLI's serialized-value URL replacer. General table URL replacement skips protected tables and options. Both scripts check table-prefix equality and the options-table engine before importing. Rollback dumps remain complete in their original environment. The helper stays in `scripts/wordpress-db-sync.php`, outside WordPress/theme deployment. Selective content/catalog and theme sync already preserve mail settings.

`scripts/tests/db-sync-mail.php` passed on Local with temporary SQL tables and read-only database exports. It verifies both directions, unchanged destination bytes/autoload values, an unconfigured destination, source-secret exclusion, serialized Unicode/quoted/empty content values, and actual push/pull export flags. The export test caught WP-CLI ignoring `exclude_tables` when `tables` is supplied; push now supplies only a filtered whitelist. Real WordPress options stayed unchanged. PHP/shell syntax and whitespace checks passed. No full database sync, production database import or email send was performed. Temporary export files were removed.
