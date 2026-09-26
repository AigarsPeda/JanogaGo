# JāņogaGO website handoff

## Goal

Maintain the JāņogaGO bilingual WordPress marketing site. Keep homepage text, images, buttons, menus, and sections editable in WordPress/Gutenberg; only visual behaviour and one-time content migrations belong in the theme code.

## Required WordPress content management

All visible website text and images must remain manageable through WordPress. This requirement applies to every future change.

- Users must be able to visually edit, replace, add, and remove text through Gutenberg or the relevant WordPress setting. This includes headings, body copy, buttons, FAQs, form labels and messages, contact details, and footer text.
- Users must be able to upload, replace, and delete images through the WordPress Media Library and select them in native Image blocks or WordPress image settings.
- Store new content images only in WordPress uploads with Media Library attachments. Do not add photo copies to the theme, repository, or code changes. Import supplied photos through WordPress Media or `wp media import`.
- These controls must work through the remote WordPress admin when the site is hosted. Routine content changes must not require theme-code edits, SSH, or a deployment.
- Store authored content in WordPress. Theme code may provide initial content and rendering, but must preserve later edits and deletions without restoring removed content.

## Where everything is

- Repository: `/Users/aigarspeda/Desktop/JanogaGo`
- Theme source: `theme/janogago`
- Local WordPress site: `http://janogago.local/`
- Local installed theme: `/Users/aigarspeda/Local Sites/janogago/app/public/wp-content/themes/janogago`
- Live droplet URL: `http://165.232.119.106/`
- Live WordPress installation: `/var/www/janogago`
- Deployment configuration: `scripts/janogago-droplet.env` (local-only and ignored by Git)

Do not commit the deployment configuration or SSH key.

## How the site is authored

- The LV and EN home pages are native Gutenberg block content, with Polylang providing translations.
- Images are WordPress Media Library attachments. Replace or delete them through Media, or edit the Image blocks directly in Gutenberg.
- Page copy, buttons, FAQ entries, contact details, and client logos are all editable in the relevant page's Gutenberg blocks.
- Form labels, consent text, submission messages, footer address, and copyright are native Paragraph/Button blocks too. The theme renders the form from those blocks. The current pages do not use the old form shortcode.
- The header and footer logo use WordPress's Custom Logo setting. The header CTA reads the first page Button block, so editing that block updates the header CTA for that language too.
- Deleting the first Button removes the header CTA. Gutenberg stores its URL in the button HTML after a visual save, so the theme reads that source when the block attribute is absent.
- Standard WordPress menus are used for navigation. The theme fallback menu is only used if a menu is not assigned.
- Edit LV page ID **6** and EN page ID **7** separately. Saved page content is authoritative: changing seed strings in PHP does not overwrite an already migrated page.
- Images are uploaded attachments, not Unsplash hotlinks. Replace them with Gutenberg's Replace/Media Library control, or upload/delete through WordPress Media. LV and EN can share an attachment, so remove its uses before deleting the file. See `IMAGE-CREDITS.md` for sources and licenses.

## Main theme files

| File | Responsibility |
| --- | --- |
| `theme/janogago/functions.php` | Theme setup, Gutenberg seed helpers, one-time/defensive homepage content migrations, contact form, fallback menu. |
| `theme/janogago/inc/business-content.php` | LV/EN business copy seed, once-only migration, editable form and footer rendering. |
| `theme/janogago/assets/css/business.css` | Layout and typography for the new business sections. |
| `theme/janogago/assets/css/editor.css` | Visual editing cues for native form labels, status messages, and footer blocks. |
| `theme/janogago/style.css` | Theme styles, responsive layout, animations, cache-busting theme version. |
| `theme/janogago/assets/js/site.js` | Navigation state, reveal effects, FAQ motion, mobile client-logo carousel. |
| `theme/janogago/assets/js/counters.js` | One-time, scroll-triggered count-up for Gutenberg experience figures. |
| `theme/janogago/header.php` / `footer.php` | Header, WordPress menu, logo, footer. |
| `scripts/sync-code-to-droplet.sh` | Safely synchronizes the local theme to the droplet, flushes cache/rewrite rules, verifies the file hash and HTTP response. |
| `scripts/sync-uploads-to-droplet.sh` | Synchronizes Media Library uploads to live without deleting existing live uploads. |
| `scripts/sync-content-to-droplet.sh` / `wordpress-content-sync.php` | Selectively transfers LV/EN homepage blocks and referenced media, with dry runs and private backups. Registers new photos through WordPress and maps live IDs/URLs. |
| `scripts/push-db-to-droplet.sh` | Full database replacement. Destructive: only run with explicit user approval. |

## Current homepage structure

1. Hero — “Maltītes uz vietas, bez savas ēdnīcas.” / “Meals on site, without running a canteen.”
2. Food assortment — left: “Pamatēdieni un salāti”; middle: “Sendviči un uzkodas”; right: “Deserti un dzērieni”, with an illustrative-photo note.
3. Janoga's catering experience — **20 years**, **250+ companies served**, **650+ daily café diners**. These are the user's updated figures for the existing catering business, not vending installations.
4. Client logos, presented under “Klienti, kuri mums uzticējušies.” with a description of the existing catering relationship.
5. Fully managed service — what Janoga provides and what the host provides.
6. Location suitability — daily users, meal alternatives, shifts, space and delivery feasibility.
7. Three-step cooperation process, five FAQs, and enquiry form.
8. Footer copy authored in a native group and rendered in the footer.

The hero and middle food card (“Sendviči un uzkodas”) use `leyli-sadeqian-wSmhn8taZpc-unsplash.jpg`, supplied from `/Users/aigarspeda/Desktop/vending-img/`. Both use Media Library attachment 41 in native Image blocks. The left card (“Pamatēdieni un salāti”) uses the existing Anh Nguyen salad-bowl attachment 42. The previous chicken attachment remains available in WordPress uploads. The initial content seed resolves the existing Media Library records; business migration markers are preserved.

The dessert card on both local pages now uses `fidel-fernando-KY5ZBCIE18E-unsplash.jpg`, supplied from `/Users/aigarspeda/Downloads/`. It shows glazed doughnuts in a box and has matching LV/EN alt text. It is stored only in WordPress uploads as Media Library attachment ID 55; only the dessert Image blocks were replaced. The previous cake attachment remains available. The initial content seed and `IMAGE-CREDITS.md` reflect the replacement, without resetting any migration markers.

Removed the newly added theme copies of the Lilas Yohane and Fidel Fernando photos. `jg_seed_attachment()` retains its legacy name for compatibility but now resolves existing Media Library attachments only, including WordPress's `-scaled` filenames. It no longer reads photo files from the theme or imports them automatically. Missing Media Library items return zero. Import required media before applying initial content migrations on another installation.

Verified filename lookup with the old ID cache bypassed, including the scaled dessert image. Both LV and EN pages have valid Image block attachments whose files are in WordPress uploads. Confirmed the food-card photos still load from `/wp-content/uploads/` after removing the duplicates; Git has no newly added photo files. The storage check is `/tmp/janogago-check-media-storage.php`.

The new offer is primarily a fully managed service. Machine model cards, demo specifications, the rental offer, and guaranteed hot-food/24/7 claims were removed from the current local pages. Costs, minimum demand, service territory, exact equipment, cooling/reheating arrangements and detailed service terms remain undecided; the copy says these are agreed in the proposal. Boost is the prospective equipment supplier, but no specific model is promised.

The business migration runs at `init` priority 30 and uses option `jg_business_content_v1` plus per-page `_jg_business_content_v1`. **Do not reset these markers during normal code sync.** It runs once and does not restore deleted sections or edited copy. `_jg_gutenberg_seeded` also prevents reseeding a deliberately empty page. Existing contact blocks were preserved from actual Gutenberg content, because old contact meta contained a stale placeholder phone number.

Removed sections:

- “Pusdienas, par kurām nav jādomā.”
- “Pusdienas, ko gaida, nevis izlaiž.”

`functions.php` removes those old root Gutenberg groups (`jg-block-proof` and `jg-block-menu`) from both homepage languages on WordPress initialization. It also retargets the existing `Ēdiens` / `Food` menu link from `#edieni` to `#sortiments`.

## Mobile client-logo carousel

The carousel is deliberately native horizontal scrolling on screens up to 700px, not a CSS transform marquee. This lets iPhone Safari users swipe it.

- `site.js` starts it in the middle of cloned logo batches.
- It auto-scrolls with `requestAnimationFrame`, retaining sub-pixel movement in an accumulator because iPhone Safari rounds `scrollLeft` to whole pixels.
- Touch/pointer interaction pauses automatic movement and it resumes after 700ms.
- `ensureBuffered()` prepends or appends two cloned logo batches before an edge becomes visible, so a quick swipe should not expose empty space.
- `prefers-reduced-motion` disables the automatic carousel and keeps the logos static.

Local and live theme versions are **1.0.41**, deployed on 2026-09-26 with both language pages. The clients section uses the same `--paper` background as the food assortment section, via `assets/css/business.css`. The carousel was unchanged in these updates. The buffering implementation should be tested on a real iPhone Safari after any future carousel change; do not revert to a transform-only marquee because it blocks natural manual swiping.

Desktop hero text now uses `align-self: center` at widths of 851px and above in `assets/css/business.css`, overriding the earlier top alignment. This lowers the text group within tall desktop viewports to align with the image. Mobile spacing and all Gutenberg text/image content remain unchanged.

## Experience figures animation

`assets/js/counters.js` counts each `.jg-experience-number` up once when half of it enters the viewport, over one second. It reads the integer and suffix from the native Paragraph text, so edits such as `20 gadi`, `250+`, or `650+` in Gutenberg remain authoritative. No extra counter fields, shortcode or stored numeric values are required. Non-integer formats remain static.

The full figures are visible without JavaScript and when reduced motion is enabled. During animation, assistive technology receives the final text through a visually hidden span, while the changing visual span is `aria-hidden`. On completion the original authored nodes are restored. Switching to reduced motion or hiding the tab finishes active animations immediately. The existing carousel code is unchanged.

Verified scroll triggering and final values on the LV desktop page and EN phone layout. A focused temporary Node harness at `/tmp/janogago-counter-check.cjs` checks once-only playback, Gutenberg-edited values, suffixes, original markup restoration, reduced motion and hidden-tab completion. No database content migration is needed for the animation.

## Local workflow

1. Edit source under `theme/janogago` using `apply_patch`.
2. Check syntax before copying:

   ```sh
   node --check theme/janogago/assets/js/site.js
   '/Users/aigarspeda/Library/Application Support/Local/lightning-services/php-8.2.30+1/bin/darwin-arm64/bin/php' -l theme/janogago/functions.php
   '/Users/aigarspeda/Library/Application Support/Local/lightning-services/php-8.2.30+1/bin/darwin-arm64/bin/php' -l theme/janogago/inc/business-content.php
   git diff --check
   ```

3. Copy changed theme files to the Local site, for example:

   ```sh
   cp theme/janogago/style.css '/Users/aigarspeda/Local Sites/janogago/app/public/wp-content/themes/janogago/style.css'
   cp theme/janogago/assets/js/site.js '/Users/aigarspeda/Local Sites/janogago/app/public/wp-content/themes/janogago/assets/js/site.js'
   cp theme/janogago/functions.php '/Users/aigarspeda/Local Sites/janogago/app/public/wp-content/themes/janogago/functions.php'
   ```

4. Copy all changed/new theme code, including `inc/` and CSS, before opening the pages. Import new content images into WordPress Media Library, not theme assets. Open both `http://janogago.local/` and `http://janogago.local/en/home/` to trigger and verify any Gutenberg migration.

WordPress CLI uses `/Applications/Local.app/Contents/Resources/extraResources/bin/wp-cli/wp-cli.phar` with the PHP executable above, `-d mysqli.default_socket='/Users/aigarspeda/Library/Application Support/Local/run/d3d0ClKR2/mysql/mysqld.sock'`, and `--path='/Users/aigarspeda/Local Sites/janogago/app/public'`. The Local filesystem and database socket need sandbox escalation; approved calls worked.

## Verification of the local business update

- Checked LV and EN layouts on desktop and phone widths, including 390px and 588px; no horizontal overflow or failed loaded food images.
- Opened the actual Gutenberg editor: no invalid/recovery blocks. Visually changed and saved the LV name label to “Kontaktpersonas vārds” and verified it on the frontend.
- Confirmed all authored blocks are native `core/*`, and images have Media Library attachments and files.
- Tested deleting a section and clearing the whole page: neither was reseeded. Restored the current content afterwards.
- Tested form handling: valid enquiry, missing consent, invalid phone and missing location. The valid test stored the location; optional notes could be blank. Email was intercepted with `pre_wp_mail`, so no external message was sent. The test lead was moved to Trash. Actual mail delivery was not tested.
- PHP syntax and Git whitespace checks passed. No carousel JavaScript changes.

Temporary recovery/test files: `/tmp/janogago-before-business-copy.json` contains the pre-update LV/EN posts; `/tmp/janogago-verify-business.php` contains the focused verification script. These are not repository files.

## Deploying safely

### Latest release, 2026-09-26

The user authorized deployment of new code and content. Used the existing `sync-uploads-to-droplet.sh` and `sync-code-to-droplet.sh` scripts, following successful dry runs. Transferred only LV page 6 and EN page 7 through a temporary WP-CLI importer, preserving the live database, users, settings, other pages, and enquiries. The importer updated native block content and media alt text, and set the existing migration markers before deploying the theme. No full database replacement was performed.

All eight referenced Media Library attachments already existed on live. Their original files were verified against the local photos using SHA-256, and the importer mapped image IDs and URLs to live records. Live IDs are 41, 42, 55 and 44–48. Live attachment 55 uses WordPress's renamed file `2026/09/fidel-fernando-KY5ZBCIE18E-unsplash-1-scaled.jpg`; preserve its actual URL rather than assuming the local filename. All content images remain in WordPress uploads. The uploads script ran with `LOCAL_UPLOADS_PATH=/tmp/janogago-release-uploads`, containing only the 43 original/derived files used by these attachments.

Private rollback backup on the droplet: `/root/janogago-backups/20260926-054215-before-business-release/`. It contains `database.sql`, `theme.tar.gz`, and `pages-before.json`. The exported release content and temporary importer are also retained there as deployment records. Directory permissions are 700 and files are 600. Local temporary transfer files are `/tmp/janogago-export-release.php`, `/tmp/janogago-import-release.php`, and `/tmp/janogago-release-content.json`; they are not repository files.

Verified theme version 1.0.36, exact source/theme checksum comparison, and saved page-content hashes after initialization. LV hash: `72365b1a822816715d0fb6237d9f2fd5486cfa40dac5de272ddbb306dc845c98`; EN hash: `38e20f12aca7ecd5a86faf171c56707d76137f1bd6c94337a5c2fb4d331107d9`. Cache and rewrite rules were flushed. Both language URLs returned HTTP 200; maintenance mode is inactive. Browser checks confirmed the new copy and functional form fields, all food photographs and client logos loaded from uploads, matching food/client backgrounds, final experience figures, and no horizontal overflow on LV desktop or EN at 390px. No live form submission or external email was sent. Future content edits will change these hashes normally.

### Future deployments

Theme-only deployment is the normal route:

```sh
./scripts/sync-code-to-droplet.sh
```

It deploys the current working tree, so commit first when a clean release history is required. For Media Library files that were added locally:

```sh
./scripts/sync-uploads-to-droplet.sh
```

The uploads script copies files only. It does not create Media Library records or transfer page content. For the homepage copy and its native Image-block photos, use the saved content sync instead:

```sh
./scripts/sync-content-to-droplet.sh --dry-run
./scripts/sync-content-to-droplet.sh
```

Add `--languages=lv` or `--languages=en` to transfer only one language. It uses the existing deployment configuration and Local PHP/socket paths. Its companion `scripts/wordpress-content-sync.php` exports current Gutenberg content and original media files to private temporary staging. Remote preflight verifies pages, image checksums and duplicate matches. Apply backs up the database to a new private directory under `REMOTE_BACKUP_DIR`, saves page/marker/alt snapshots, reuses matching media by original-file SHA-256 and imports missing photos with WordPress's native sideload API. WordPress handles uploads filenames, scaling, thumbnails and attachment records. No theme photo copies or full database replacement are used. A separate uploads sync is not needed for these page photos.

The helper runs with `--skip-themes` to avoid incidental migrations. It sets per-page migration markers, sets the global business marker when both pages are migrated, verifies saved content, and skips writing unchanged pages. The wrapper flushes cache afterwards and removes temporary staging. It deliberately overwrites selected live homepage blocks with Local's blocks, so review live edits before running. It preserves existing page IDs, titles, slugs, users, settings, enquiries and unrelated content.

Verified the reusable script in dry-run mode against the live droplet: two pages, eight reused photos, no new imports or live changes. `scripts/tests/content-sync.php` is a local-only WP-CLI integration test covering dry-run preservation, new native media import, a filename collision, nested block ID/URL mapping, media metadata, backups, unchanged-page revision counts, repeat runs without duplicate images and unrelated homepage preservation. It removes its fixtures and refuses production hostnames. PHP and Bash syntax checks passed. The reusable script was added after the initial release above and first applied live for the figure update below.

On 2026-09-26, the user updated the experience figures to **250+ companies** and **650+ daily canteen diners**. Changed only the two native Paragraph values on both local language pages, then deployed with `sync-content-to-droplet.sh` after a successful dry run. The matching initial defaults in `inc/business-content.php` were updated locally and deployed with `sync-code-to-droplet.sh`; no migration markers were reset. The theme remains 1.0.36 because no cached CSS/JS changed. Full-content comparisons against the before-update snapshots confirmed that only the two requested figures changed on local and live LV/EN pages. The live browser confirmed 20 gadi, 250+, 650+.

Figure-update rollback backup: `/var/backups/janogago/content-20260926-112002.eSIYSIvf/`, containing `database.sql.gz`, `pages-before.json` and `release.json`. Page hashes recorded for this figure update: LV `24b06efad2fd9b9d3e557baa4832fa4f889ef9f2a7791d2dd893314c9fa11fd3`; EN `cddef0d3ccce03a7ca607e22d03386d8ac63b3ffa07b7a32c1df9162c20f2c56`. The earlier release hashes above are historical. Local before-update content is `/tmp/janogago-before-figure-update.json`; live screenshot proof is `/tmp/janogago-250-650-live.png`.

The next user-requested wording update replaced `mūsu ēdnīcu apmeklētājiem` with `mūsu kafejnīcu apmeklētājiem` and `kur nav ēdnīcas` with `kur nav kafejnīcas` in the experience paragraph. Its English equivalent now uses `our cafés` and `without a café`. Only that paragraph was changed in each language. Native Gutenberg content, initial theme defaults and the copy brief were updated; content/code were deployed using the saved scripts. Hashes recorded for that wording update: LV `141f960878dc9821df2f839f17852966c5d773d1dd5a43f45f7d548cccf81b4e`; EN `5ae99763c229d6f8cd84f2a44e117a49da176f7f19a7a1e102970bc2eeaecfe2`. Rollback backup: `/var/backups/janogago/content-20260926-112357.FMK7Fax1/`. Local before-update content: `/tmp/janogago-before-cafe-copy.json`.

The client then requested three food categories, left to right: “Pamatēdieni un salāti”, “Sendviči / uzkodas / konditoreja”, “Deserti / našķi / dzērieni”. These were deployed on local and live LV pages, with EN equivalents “Mains and salads”, “Sandwiches / snacks / pastries”, “Desserts / treats / drinks”, before the later shortening below. Descriptions and the section introduction were updated. The existing salad-bowl photo is now on the left (attachment 42), the existing sandwich/hotdog photo in the middle (41), and the existing doughnuts on the right (55). The hero photo stays 41. No new photos were added. All remain native Gutenberg/Media Library content.

Checked the revised section at local desktop 1280px and phone 390px: photos loaded and headings wrapped without horizontal overflow. No CSS/JS changes were needed. Deployed through content sync and code sync after the dry run. All non-food page blocks were preserved. Historical category-update hashes: LV `066132b1d10bf3f977d93fd68531864145fac37020abcdb697eac0f7ab7f6c63`; EN `1dccfe88ce8bf6db53a215688a34f6688886a286b3421429fdf61e9fe8120797`. Rollback backup: `/var/backups/janogago/content-20260926-113011.iP9MEEvo/`. Local before-update content: `/tmp/janogago-before-food-category-update.json`.

The user approved simpler middle/right headings and descriptions, then requested a more natural left-card description. Final copy is now local and live:

| Card | LV heading | LV description | EN heading | EN description |
| --- | --- | --- | --- | --- |
| Left | Pamatēdieni un salāti | Pilnvērtīga maltīte pusdienu pauzei. | Mains and salads | A proper meal for your lunch break. |
| Middle | Sendviči un uzkodas | Arī konditoreja nelielai pauzei. | Sandwiches and snacks | Pastries for a short break. |
| Right | Deserti un dzērieni | Saldai pauzei. | Desserts and drinks | For a sweet break. |

The photos, their order, and other sections are unchanged from the category update. Native blocks, matching initial theme defaults and the copy brief were updated. Both updates used the saved content/code scripts and retained theme version 1.0.36. Rollback backups: `/var/backups/janogago/content-20260926-113437.is9UE8fm/` before shortening, and `/var/backups/janogago/content-20260926-113556.pyXwrGRY/` before the left-card description change. Hashes recorded for that wording update: LV `0bcddbfa0ec3e9c02ff881cdc292459bd5a74d2321d6455f687f486ca0ec31b4`; EN `1b1c004781ce63201e2e2963467727f6dbb44a035dcf78091f97cc08f041d4ac`. Local original snapshot for both changes is `/tmp/janogago-before-short-food-copy.json`. Full-content checks on local/live LV/EN confirmed only the approved card text changed. The live LV layout was checked at 1280px and 390px: photos loaded, no horizontal overflow, all desktop titles fit on one line. Screenshot proof: `/tmp/janogago-short-food-copy-live.png`.

For a combined content/code release, back up the existing theme and run content sync before code sync. Preserve actual Gutenberg edits, including changes made after the initial seed. Do not reset migration markers during routine updates. The PHP helper belongs alongside the shell scripts, not in the WordPress theme.

Only with explicit user approval, because this overwrites live WordPress data:

```sh
./scripts/push-db-to-droplet.sh --yes
```

After a live deployment, verify LV and EN with a cache-busting query string and check the relevant content/asset version. If changing `style.css` or `site.js`, bump `Version:` in `style.css` to force browsers to fetch the new asset.

## Git status at handoff

The business update is **deployed**. Its base source is committed as `bf5dea6` (`Add business content and styles for JāņogaGO service`). The updated figure defaults, copy brief, release notes and reusable content-sync scripts added afterwards remain uncommitted. The business update covers:

- `theme/janogago/functions.php`
- `theme/janogago/header.php`
- `theme/janogago/footer.php`
- `theme/janogago/style.css`
- `theme/janogago/inc/business-content.php` (new)
- `theme/janogago/assets/css/business.css` (new)
- `theme/janogago/assets/css/editor.css` (new)
- `theme/janogago/assets/js/counters.js` (new)
- `IMAGE-CREDITS.md` (new)
- `janogago-copy-brief.md` (new, earlier copy planning)
- `HANDOFF.md`
- `README.md` (deployment guidance)
- `scripts/sync-content-to-droplet.sh`, `scripts/wordpress-content-sync.php`, `scripts/tests/content-sync.php` (reusable selective content release and local integration test)

The generated `.impeccable/` directory is diagnostic output; do not add it to Git. Before committing, review the diff and stage only the intended files. Earlier commits include `0cdb546` (mobile carousel and removed homepage sections) and `3c8bd6e` (reusable Media Library deployment sync).

## Starting a fresh chat

Attach or reference this file and state the requested change, scope, and whether it should be local-only or deployed. A useful opening prompt is:

> Read `HANDOFF.md` in `/Users/aigarspeda/Desktop/JanogaGo`. Make this change locally first: [request]. Do not deploy until I approve.

For a production request, replace the last sentence with: “Deploy the verified change to the droplet and report the live check.”


The daily-diners stat label now reads `Cilvēku ik dienu paēd mūsu kafejnīcās` in LV and `People eat in our cafés every day` in EN. The figure remains 650+. Native Gutenberg content and matching initial theme defaults were updated locally and deployed with the saved content/code scripts; theme version remains 1.0.36. Full-content verification against local and remote backups confirmed only this label changed in each language. Hashes recorded for that label update: LV `82d892d1a5eff662a8aea65a7a606a116e3d92a284393516b52509174c68447f`; EN `e92cbef6e368e4c81df30d4f2ce63bd0e148e79e046bc44c05efc41224887b1e`. Rollback backup: `/var/backups/janogago/content-20260926-114003.108PnPGD/`. Local before-update content: `/tmp/janogago-before-cafe-label.json`. Live screenshot proof: `/tmp/janogago-cafe-label-live.png`.


The client section now uses `Mūsu ēdināšanas klienti.` / `Our catering clients.` and `Uzņēmumi, kuriem esam nodrošinājuši ēdināšanu.` / `Companies we have served through our catering services.` The user requested improved wording without mentioning Janoga here. Native Gutenberg blocks, matching initial theme defaults and the copy brief were updated; content and code were deployed using the saved scripts. Theme remains 1.0.36. Exact-content checks against local/live backups confirmed only these two strings changed in each language, and the live LV section was visually verified. Hashes recorded for that client-copy update: LV `1d7571ee90f996c143fa4ffbce5ec04ee42fd4d68cebbad8a44cf241ff904dff`; EN `fcf2458bc923fe89e7912c152a6daddbf42441d3df0fff56a998e160aa15e57c`. Rollback backup: `/var/backups/janogago/content-20260926-114501.r0AZP99P/`. Local snapshot: `/tmp/janogago-before-client-copy.json`. Screenshot: `/tmp/janogago-client-copy-live.png`.


## Enquiry notification release, 2026-09-26

Theme **1.0.41** is installed locally and deployed on the droplet. The user requested visible feedback after submission, then specified that notifications must overlay the layout with tasteful animation, use the existing page green for success, and show success whenever the enquiry data is saved even if the internal email notification fails.

The final LV message is `Paldies! Esam saņēmuši jūsu pieprasījumu. Sazināsimies ar jums.` EN is `Thank you! We have received your enquiry and will be in touch.` These remain native Gutenberg Paragraph blocks. The close labels `Aizvērt paziņojumu` / `Close notification` are also native Paragraph blocks with class `jg-status-dismiss`. The confusing `jg-status-mail_failed` warning blocks were removed. The legacy `?enquiry=mail_failed` URL displays the success copy for compatibility.

`jg_submit_enquiry` stores the private lead first. Save failure redirects to `failed`; invalid input redirects to `invalid`. Every successfully saved lead redirects to `sent`, regardless of internal email outcome. The mail result is retained in lead metadata `_jg_notification_sent` as 1 or 0. Redirects use HTTP 303 and the submitted language page's permalink with `#pieteikties`. No visitor-facing message claims confirmed inbox delivery.

`jg_enquiry_result_markup` renders the native copy as an accessible status/alert, with drawn SVG icon and optional close button. CSS fixes it at the lower right of the viewport, above page content, without occupying form space. Success uses `--green` with `--ink` text/icons. Mobile uses 16px side spacing and safe-area bottom spacing. Entrance is a 360ms fade, 12px upward movement and shadow settling; exit is 140ms. Reduced motion disables animation. The notification stays until dismissed by its close button or Escape. Dismissal hides it and removes the `enquiry` URL parameter, returning keyboard focus without scrolling. The submit button disables during valid submission and restores on browser history navigation.

Reusable helper: `scripts/update-enquiry-notifications.php`. Local-only test: `scripts/tests/enquiry-notifications.php`. Both belong in scripts, not the theme. The helper makes targeted native-block edits and is idempotent; it preserves custom confirmation wording. Do not run it automatically after future editorial deletions.

Verification: PHP/JavaScript syntax and `git diff --check` passed. Integration checks passed for LV/EN accepted submissions, saved lead with failed internal mail, save failure, validation error, accessible status markup and 303 redirects. All test mail was intercepted. Test leads and the temporary Local-only mail interceptor were removed. A browser submitted the Local form with intercepted mail. Local desktop close-button dismissal preserved form height, field offsets, document height and scroll position exactly. Live desktop/mobile checks confirmed fixed positioning, readable copy, no horizontal overflow, and Escape dismissal. Reduced-motion behavior is provided by CSS and the dismissal handler. Live views used status preview URLs; no live enquiry or external email was sent.

Final content/code deployed via saved sync scripts after dry-run. Hashes recorded for that notification update: LV `dadb617bf1dcb8446f45773ca9b548806c45967e347e4159401d44e42cbf7784`; EN `f2838757280b065b009d808b4407308d40350736b99973ecb40071d6b4055641`. Latest content/database backup before the final notification content: `/var/backups/janogago/content-20260926-120045.e627afBn/`. Backup before initial notification changes: `/var/backups/janogago/content-20260926-115218.0rP4iAW7/`; pre-notification theme archive: `/var/backups/janogago/theme-before-notifications-20260926.tar.gz`. Final green-notification screenshot: `/tmp/janogago-green-notification-live.png`.


The client heading is now `Klienti, kuri mums uzticējušies.` in LV and `Clients who have trusted us.` in EN, following the user's request to emphasize trust. The description explaining the existing catering relationship remains intact. Updated native Gutenberg headings, matching initial theme defaults and the copy brief locally, then deployed content/code with the saved scripts after dry-run. Full-content comparisons against local/live backups confirmed only the heading changed in each language. The live LV section was visually checked. Theme remains 1.0.41 because no cached assets changed. Current live hashes: LV `ebe77ec13752259bbb75539c876b3227a1e60845e520382757b258e7d4703cec`; EN `b2e432aae4138c0cb32320fe1835f813099eb1ddf904f41398a211c7a442831c`. Rollback backup: `/var/backups/janogago/content-20260926-120544.dp5hkOrh/`. Local snapshot: `/tmp/janogago-before-trusted-clients.json`. Screenshot: `/tmp/janogago-trusted-clients-live.png`.
