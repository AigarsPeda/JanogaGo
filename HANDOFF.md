# JāņogaGO website handoff

## Goal

Maintain the JāņogaGO bilingual WordPress marketing site. Keep homepage text, images, buttons, menus, and sections editable in WordPress/Gutenberg; only visual behaviour and one-time content migrations belong in the theme code.

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
- The header and footer logo use WordPress's Custom Logo setting. The header CTA reads the first page Button block, so editing that block updates the header CTA for that language too.
- Standard WordPress menus are used for navigation. The theme fallback menu is only used if a menu is not assigned.

## Main theme files

| File | Responsibility |
| --- | --- |
| `theme/janogago/functions.php` | Theme setup, Gutenberg seed helpers, one-time/defensive homepage content migrations, contact form, fallback menu. |
| `theme/janogago/style.css` | Theme styles, responsive layout, animations, cache-busting theme version. |
| `theme/janogago/assets/js/site.js` | Navigation state, reveal effects, FAQ motion, mobile client-logo carousel. |
| `theme/janogago/header.php` / `footer.php` | Header, WordPress menu, logo, footer. |
| `scripts/sync-code-to-droplet.sh` | Safely synchronizes the local theme to the droplet, flushes cache/rewrite rules, verifies the file hash and HTTP response. |
| `scripts/sync-uploads-to-droplet.sh` | Synchronizes Media Library uploads to live without deleting existing live uploads. |
| `scripts/push-db-to-droplet.sh` | Full database replacement. Destructive: only run with explicit user approval. |

## Current homepage structure

1. Hero — “Svaigs un veselīgs ēdiens jūsu darba vietā 24/7.” / English equivalent.
2. Vending-machine models — Boostic and Necta Festival.
3. Food assortment cards.
4. Client logos.
5. Service models — full service and equipment rental.
6. Process, FAQ, and contact form.

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

Latest deployed version is **1.0.31**. The newest buffering implementation should be tested on a real iPhone Safari after any future carousel change; do not revert to a transform-only marquee because it blocks natural manual swiping.

## Local workflow

1. Edit source under `theme/janogago` using `apply_patch`.
2. Check syntax before copying:

   ```sh
   node --check theme/janogago/assets/js/site.js
   '/Users/aigarspeda/Library/Application Support/Local/lightning-services/php-8.2.30+1/bin/darwin-arm64/bin/php' -l theme/janogago/functions.php
   git diff --check
   ```

3. Copy changed theme files to the Local site, for example:

   ```sh
   cp theme/janogago/style.css '/Users/aigarspeda/Local Sites/janogago/app/public/wp-content/themes/janogago/style.css'
   cp theme/janogago/assets/js/site.js '/Users/aigarspeda/Local Sites/janogago/app/public/wp-content/themes/janogago/assets/js/site.js'
   cp theme/janogago/functions.php '/Users/aigarspeda/Local Sites/janogago/app/public/wp-content/themes/janogago/functions.php'
   ```

4. Open both `http://janogago.local/` and `http://janogago.local/en/home/` to trigger and verify any Gutenberg migration.

## Deploying safely

Theme-only deployment (the normal route):

```sh
./scripts/sync-code-to-droplet.sh
```

It deploys the current working tree, so commit first when a clean release history is required. For Media Library files that were added locally:

```sh
./scripts/sync-uploads-to-droplet.sh
```

Only with explicit user approval, because this overwrites live WordPress data:

```sh
./scripts/push-db-to-droplet.sh --yes
```

After a live deployment, verify LV and EN with a cache-busting query string and check the relevant content/asset version. If changing `style.css` or `site.js`, bump `Version:` in `style.css` to force browsers to fetch the new asset.

## Git status at handoff

The latest local and live work is **not committed yet**. Intended source changes are in:

- `theme/janogago/assets/js/site.js`
- `theme/janogago/functions.php`
- `theme/janogago/style.css`

The generated `.impeccable/` directory is untracked diagnostic output; do not add it to Git. Before committing, review the diff and stage only the intended files. Previous GitHub commits include `2f318e2` (homepage/client carousel) and `3c8bd6e` (reusable Media Library deployment sync).

## Starting a fresh chat

Attach or reference this file and state the requested change, scope, and whether it should be local-only or deployed. A useful opening prompt is:

> Read `HANDOFF.md` in `/Users/aigarspeda/Desktop/JanogaGo`. Make this change locally first: [request]. Do not deploy until I approve.

For a production request, replace the last sentence with: “Deploy the verified change to the droplet and report the live check.”
