# JāņogaGO website handoff

## Goal

Maintain the bilingual WordPress site, with authored content editable in WordPress and layout and behavior in the theme.

## Current state

As of 2026-10-03:

- The live site uses **https://janogago.lv/**. NIC.lv DNS has an apex A record to `165.232.119.106` and a `www` CNAME to `janogago.lv`. HTTP, the old HTTP IP address and HTTPS `www` redirect to the canonical HTTPS domain, preserving paths.
- Nginx serves a Let's Encrypt ECDSA certificate for both names, initially expiring 2027-01-01. `certbot.timer` renews it automatically; `/etc/letsencrypt/renewal-hooks/deploy/reload-nginx.sh` validates and reloads Nginx after renewal. Both WordPress URL settings, stored content/media URLs and the ignored deployment configuration now use the HTTPS domain. GUIDs were preserved.
- HTTPS and certificate verification passed in Zen for both language homepages, with the 3D hero rendering. Both catalogs and the login page returned HTTP 200. HTTP and HTTPS `www` redirects preserve paths, and `certbot renew --dry-run --run-deploy-hooks --no-random-sleep-on-renew` passed, including the Nginx reload hook.
- Local and the droplet run theme **1.0.76**, verified on 2026-10-03.
- Local and live have 19 food products, six categories and four featured homepage dishes. The catalog shows all matching dishes without pagination.
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
