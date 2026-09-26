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
| `scripts/push-db-to-droplet.sh` | Full database replacement. Destructive: only run with explicit user approval. |

## Current homepage structure

1. Hero — “Maltītes uz vietas, bez savas ēdnīcas.” / “Meals on site, without running a canteen.”
2. Food assortment — lunch meals, salads/bowls, desserts, with an illustrative-photo note.
3. Janoga's catering experience — **20 years**, **200+ companies served**, **500+ daily canteen diners**. These are the user's figures for the existing catering business, not vending installations.
4. Client logos, explicitly presented as Janoga catering clients.
5. Fully managed service — what Janoga provides and what the host provides.
6. Location suitability — daily users, meal alternatives, shifts, space and delivery feasibility.
7. Three-step cooperation process, five FAQs, and enquiry form.
8. Footer copy authored in a native group and rendered in the footer.

The hero and lunch-card photos on both local pages now use `leyli-sadeqian-wSmhn8taZpc-unsplash.jpg`, as requested from `/Users/aigarspeda/Desktop/vending-img/`. Both use the same Media Library attachment, ID 41, in native Image blocks. The lunch card's previous chicken attachment remains available in WordPress uploads. The initial content seed resolves this photo from Media Library for both positions; the existing business migration markers were preserved.

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

Latest local version is **1.0.36**; live remains **1.0.31**. The clients section now uses the same `--paper` background as the food assortment section, via `assets/css/business.css`. The carousel was unchanged in these updates. The buffering implementation should be tested on a real iPhone Safari after any future carousel change; do not revert to a transform-only marquee because it blocks natural manual swiping.

Desktop hero text now uses `align-self: center` at widths of 851px and above in `assets/css/business.css`, overriding the earlier top alignment. This lowers the text group within tall desktop viewports to align with the image. Mobile spacing and all Gutenberg text/image content remain unchanged.

## Experience figures animation

`assets/js/counters.js` counts each `.jg-experience-number` up once when half of it enters the viewport, over one second. It reads the integer and suffix from the native Paragraph text, so edits such as `20 gadi`, `200+`, or `500+` in Gutenberg remain authoritative. No extra counter fields, shortcode or stored numeric values are required. Non-integer formats remain static.

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

Theme-only deployment (the normal route):

**No deployment was requested or performed for this update.** A future code deployment will run the once-only business migration on live if its markers are absent. Import the required photos into live Media Library before triggering that migration; it now resolves existing attachments rather than importing theme assets. Local edits made after the seed, such as changes in Gutenberg, must be carried across deliberately; do not overwrite live with the entire local database merely to move copy.

```sh
./scripts/sync-code-to-droplet.sh
```

It deploys the current working tree, so commit first when a clean release history is required. For Media Library files that were added locally:

```sh
./scripts/sync-uploads-to-droplet.sh
```

This script copies upload files only. It does not create Media Library records or transfer page content. Import/register new images through live WordPress Media or WP-CLI and use the live attachment IDs when transferring page content. Never assume local attachment IDs match live IDs.

Only with explicit user approval, because this overwrites live WordPress data:

```sh
./scripts/push-db-to-droplet.sh --yes
```

After a live deployment, verify LV and EN with a cache-busting query string and check the relevant content/asset version. If changing `style.css` or `site.js`, bump `Version:` in `style.css` to force browsers to fetch the new asset.

## Git status at handoff

The business update is **local only and uncommitted**. Intended source/document changes are in:

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

The generated `.impeccable/` directory is untracked diagnostic output; do not add it to Git. Before committing, review the diff and stage only the intended files. Previous GitHub commits include `2f318e2` (homepage/client carousel) and `3c8bd6e` (reusable Media Library deployment sync).

## Starting a fresh chat

Attach or reference this file and state the requested change, scope, and whether it should be local-only or deployed. A useful opening prompt is:

> Read `HANDOFF.md` in `/Users/aigarspeda/Desktop/JanogaGo`. Make this change locally first: [request]. Do not deploy until I approve.

For a production request, replace the last sentence with: “Deploy the verified change to the droplet and report the live check.”
