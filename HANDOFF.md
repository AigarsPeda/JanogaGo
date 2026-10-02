# JāņogaGO website handoff

## Goal

Maintain the bilingual WordPress site, with authored content editable in WordPress and layout and behavior in the theme.

## Current state

As of 2026-10-02:

- Local and the droplet run theme **1.0.74**, verified on 2026-10-02.
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
| Live website / WordPress | `http://165.232.119.106/` / `/var/www/janogago` |
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
| `assets/js/site.js`, `assets/js/counters.js` | Navigation, FAQs, enquiries, catalog interactions, mobile logo carousel and experience counters |
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
- An enquiry counts as successful when its private lead is saved, even if internal notification mail fails. Mail outcome stays in lead metadata. The dismissible green success notice overlays the layout; successful redirects and URL cleanup must not jump back to the form or replay the notice on reload.

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

The latest theme-only rollback archive is `/var/backups/janogago/theme-20261002-220737.SFrJMRyN/theme.tar.gz`. The latest homepage/media database backup is `/var/backups/janogago/content-20261002-215933.RzwWUXmN/`. The latest full live rollback backup is `/var/backups/janogago/food-release-20261002-212535.P6S7buuI/`, containing database and theme. Future release scripts create new private backups. After deployment, verify LV/EN pages, media, asset versions and affected interactions.

## Outstanding checks

- The new Gutenberg 3D controls still need an authenticated visual edit/save check. Server-side registration and frontend loading/rotation were verified.
- Real iPhone Safari momentum and actual enquiry inbox delivery remain unverified. Existing local enquiry tests intercept email; avoid sending live test enquiries without authorization.

Relevant regression checks are `scripts/tests/hero-model.cjs`, `scripts/tests/client-carousel.cjs`, `scripts/tests/content-sync.php` and `scripts/tests/enquiry-notifications.php`. Run checks appropriate to the change; the WordPress integration tests are Local-only.
