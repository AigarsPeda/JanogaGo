# JanogaGo

Custom WordPress theme for JanogaGo's managed food-vending service website.

The theme supports Latvian and English content through Polylang, editable page content, WordPress Media Library images, standard menus, a Custom Logo, and website enquiries stored in WordPress.

## Local theme location

`theme/janogago`

## Food catalog on Local

The local WordPress site has one `Ēdieni / Products` entry per dish. The entry title is Latvian; the Product details box holds the English name, optional descriptions, the homepage switch and Vegan, Vegetarian and Gluten-free recipe switches. Set its Featured image from the Media Library, choose a category in the editor sidebar, and use the Order field to control listing order. Both language catalogs and the homepage read these same entries. Dietary switches add filters and hover, focus or tap labels to compact badges on the catalog only. Category English names are edited under `Ēdieni / Products → Kategorijas / Categories`; filter and empty-state labels are under `Ēdieni / Products → Catalog labels`. Page headings, introductions, the homepage link and contact copy stay in Gutenberg.

Local pages: `/edieni/` and `/en/food/`. The `Drinks` and `Snacks` filters are present but disabled until a published product uses those categories. Dietary tags should be set only after checking the recipe; photos do not establish ingredients.

Catalog filters and pagination update results in place with a short animation. The URL and browser Back button reflect the selected filters; standard page navigation remains available when JavaScript is disabled.

`scripts/setup-local-food-catalog.php` is the one-time, idempotent Local setup. It imports the supplied photos into WordPress uploads, creates the initial products and bilingual pages, and replaces the old illustrative homepage cards. It refuses non-local sites. The old homepage content is saved in `/tmp` before replacement.

The homepage content sync transfers homepage blocks only. For catalog releases, use `scripts/release-food-to-droplet.sh` or the dedicated `sync-food-to-droplet.sh` as described below.

## Droplet deployment

The `scripts/` folder contains theme, content, uploads, and database synchronization scripts for the JāņogaGO droplet. The scripts contain no private-key details; the local configuration must stay out of Git.

The existing droplet uses `scripts/janogago-droplet.env`. For another installation, copy the example configuration, add the real values, and keep it out of Git. Each script explains the required fields and stops if the droplet details are missing.

For a routine theme release:

```bash
./scripts/sync-code-to-droplet.sh --dry-run
./scripts/sync-code-to-droplet.sh
```

An apply run now archives the current live theme under `REMOTE_BACKUP_DIR` before rsync changes it. The script prints the private archive path.

For a complete food release from Local to the droplet:

```bash
./scripts/release-food-to-droplet.sh --dry-run
./scripts/release-food-to-droplet.sh
```

The release script backs up the live database and theme outside the public WordPress directory, deploys the theme, transfers food categories, products, original photos, dietary and homepage switches, LV/EN catalog pages and labels, then synchronizes both homepages. Each content transfer also saves its own database backup. It preserves live enquiries, users and unrelated pages. It does not delete products that are absent locally. The dry run checks theme and homepage changes; catalog preflight runs after the theme is deployed because the live site must first register the product type. A failed step stops the release and leaves the printed backups available for recovery.

For catalog data only, after the current theme is already live:

```bash
./scripts/sync-food-to-droplet.sh --dry-run
./scripts/sync-food-to-droplet.sh
```

The catalog helper matches existing seeded dishes by their source key and future dishes by a stable key derived from their WordPress GUID, so repeat releases update the same records and reuse matching photos. Publish a product with a featured image before syncing. The helper is `scripts/wordpress-food-sync.php`; keep it outside the theme. Running a sync replaces matching live catalog fields and both food pages with Local's versions.

For new Media Library files:

```bash
./scripts/sync-uploads-to-droplet.sh --dry-run
./scripts/sync-uploads-to-droplet.sh
```

`sync-uploads-to-droplet.sh` copies new and changed Media Library files but never deletes existing files on the droplet. Use `--dry-run` first whenever you want to review the file changes.

Uploads sync transfers files only. To transfer both homepage languages and their referenced Image-block photos, use the reusable content script:

```bash
./scripts/sync-content-to-droplet.sh --dry-run
./scripts/sync-content-to-droplet.sh
```

Use `--languages=lv` or `--languages=en` to transfer one language. The script exports the current native Gutenberg blocks, maps local image IDs and URLs to live records, and reuses photos by their original-file checksum, including files WordPress renamed or scaled. It imports missing photos through WordPress, which stores them in uploads, creates Media Library records and generates image sizes. A separate uploads sync is unnecessary for those photos. Ambiguous duplicate live media records stop the transfer for review.

Before applying, it creates a private database backup plus page, marker and media-alt snapshots under `REMOTE_BACKUP_DIR`. Existing page IDs, titles, slugs, users, settings, enquiries and unrelated content stay intact. Theme migrations are skipped during export/import; the homepage migration markers are preserved/set so subsequent code sync does not replace authored content. Repeated runs reuse images and skip saving unchanged pages. The PHP helper `scripts/wordpress-content-sync.php` belongs with the scripts and must not be installed in the theme.

For combined food releases, use the release script above. For other combined content and code changes, back up the existing theme and dry-run both relevant scripts first. Check LV/EN in the browser after deployment. Routine edits made directly in live WordPress need no deployment; content sync intentionally replaces the selected live homepage blocks with Local's versions.

To bring the live database and uploads back into Local, `scripts/pull-db-from-droplet.sh` already backs up the local database and then imports the droplet state. It replaces all local WordPress data, so use it only when a complete local refresh is intended.

Do not use `push-db-to-droplet.sh --yes` for a routine code/content release: it replaces the whole live database and requires explicit authorization for that replacement.

The local-only integration test is `scripts/tests/content-sync.php`. Run it with Local's WP-CLI using `--skip-themes eval-file`. It creates and removes temporary pages and an image fixture, tests imports and repeated runs, and refuses sites whose hostname does not end in `.local`.

The 2026-09-26 release deployed theme 1.0.36 and both language pages this way. See `HANDOFF.md` for the verified live state and private rollback backup location.


## Enquiry notifications

A saved enquiry shows a floating success notification in the site's existing green. Notification messages and the close-button label are native Paragraph blocks in each language's enquiry form group. The notification overlays the page without affecting layout, uses a short entrance/exit animation, supports Escape and close-button dismissal, and respects reduced motion. Saving failures and validation errors still display actionable errors. The internal `wp_mail` result is stored as `_jg_notification_sent` on the lead; an internal email failure does not turn a saved enquiry into a visitor-facing failure.

`scripts/update-enquiry-notifications.php` updates existing homepage notification blocks while preserving other content and custom confirmation wording. Run it with Local's WP-CLI `eval-file` after installing the current theme. It removes the retired mail-failure warning, shortens the original confirmation, and adds missing editable close labels. Do not reset homepage seed markers. It is a release helper, not an automatic theme migration.

`scripts/tests/enquiry-notifications.php` is a Local-only integration test. Run it with WP-CLI `eval-file` with the theme loaded. It intercepts all email, checks successful and failed mail, saving failure and invalid submissions, verifies accessible results and redirect behavior, and deletes its own test leads. Actual inbox delivery is not tested.

Theme 1.0.42 clears submission status URLs after showing the notification and removes automatic notification focus. Successful submissions redirect without a form anchor, preventing later opens from jumping to the form. Normal offer links still navigate to the form.

The mobile client carousel preloads fixed repeated logo batches, keeps native horizontal scrolling in both directions, and waits for swipe momentum to settle before resuming autoplay. Run `node scripts/tests/client-carousel.cjs` for the scrolling logic regression checks. Verify physical iPhone Safari swipes separately.

Theme 1.0.58 is on Local and the droplet. See `HANDOFF.md` for release verification and rollback locations.
