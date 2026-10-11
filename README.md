# JanogaGo

Custom WordPress theme for JanogaGo's managed food-vending service website.

The theme supports Latvian and English content through Polylang, editable page content, WordPress Media Library images, standard menus, a Custom Logo, and website enquiries stored in WordPress.

## Local theme location

`theme/janogago`

## Food catalog on Local

The local WordPress site has one `Ēdieni / Products` entry per dish. Add, edit, trash, or delete dishes in WordPress; changing a dish never requires editing theme or setup code. The entry title is Latvian; the Product details box holds the English name, optional descriptions, the homepage switch and Vegan, Vegetarian and Gluten-free recipe switches. Set its Featured image from the Media Library, choose a category in the editor sidebar, and use the Order field to control listing order. Both language catalogs and the homepage read these same entries. Dietary switches add filters and hover, focus or tap labels to compact badges on the catalog only. Category English names are edited under `Ēdieni / Products → Kategorijas / Categories`; filter and empty-state labels are under `Ēdieni / Products → Catalog labels`. Page headings, introductions, the homepage link and contact copy stay in Gutenberg. `scripts/setup-local-food-catalog.php` only creates the food pages and homepage links on a new Local site; it contains no product data.

Local pages: `/edieni/` and `/en/food/`. The `Drinks` and `Snacks` filters are present but disabled until a published product uses those categories. Dietary tags should be set only after checking the recipe; photos do not establish ingredients.

The catalog shows all dishes on one page, grouped by category. Filters update results in place with a short animation. The URL and browser Back button reflect the selected filters; standard page navigation remains available when JavaScript is disabled.

`scripts/setup-local-food-catalog.php` is the one-time, idempotent Local page setup. It creates the bilingual food pages and replaces the old illustrative homepage cards. Add products and categories in WordPress, or restore them from a database backup. The script refuses non-local sites and saves the old homepage content in `/tmp` before replacement.

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

The release script backs up the live database and theme outside the public WordPress directory, deploys the theme, transfers food categories, products, original photos, dietary and homepage switches, LV/EN catalog pages and labels, then synchronizes both homepages. Each content transfer also saves its own database backup. It preserves live enquiries, users and unrelated pages. Previously synced products absent from the Local published catalog move to WordPress Trash; review the catalog dry run before release. The dry run checks theme and homepage changes; catalog preflight runs after the theme is deployed because the live site must first register the product type. A failed step stops the release and leaves the printed backups available for recovery.

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

Uploads sync transfers files only. To transfer both homepage languages and their referenced Image-block photos and GLB models, use the reusable content script:

```bash
./scripts/sync-content-to-droplet.sh --dry-run
./scripts/sync-content-to-droplet.sh
```

Use `--languages=lv` or `--languages=en` to transfer one language. The script exports the current native Gutenberg blocks, maps image IDs, `jgModelId`, `jgModelPosterId` and URLs to live records, and reuses media by their original-file checksum, including files WordPress renamed or scaled. It imports missing images and validated GLBs through WordPress into the Media Library and uploads, generates image sizes, and transfers model dimension metadata. An optional matching render provides the loading preview. The original Image block stays available as the error fallback. A separate uploads sync is unnecessary for these files. Ambiguous duplicate live media records stop the transfer for review.

Before applying, it creates a private database backup plus page, marker and media-alt snapshots under `REMOTE_BACKUP_DIR`. Existing page IDs, titles, slugs, users, settings, enquiries and unrelated content stay intact. Theme migrations are skipped during export/import; the homepage migration markers are preserved/set so subsequent code sync does not replace authored content. Repeated runs reuse media and skip saving unchanged pages. The PHP helper `scripts/wordpress-content-sync.php` belongs with the scripts and must not be installed in the theme.

For combined food releases, use the release script above. For other combined content and code changes, back up the existing theme and dry-run both relevant scripts first. Check LV/EN in the browser after deployment. Routine edits made directly in live WordPress need no deployment; content sync intentionally replaces the selected live homepage blocks with Local's versions.

To bring the live database and uploads back into Local, `scripts/pull-db-from-droplet.sh` backs up the local database and then imports the droplet state. It replaces local WordPress data except the environment-specific email settings and runtime queues described below. Use it only when a complete local refresh is intended.

Do not use `push-db-to-droplet.sh --yes` for a routine code/content release: it replaces the whole live database and requires explicit authorization for that replacement.

Both full database scripts preserve the destination's WP Mail SMTP options, connections and authentication tokens, legacy SMTP settings, administrator email and pending email-change state, and `jg_enquiry_recipient`. They exclude source mail settings from the transfer, leave mail log/queue/task tables in place, and preserve WP-Cron and Action Scheduler jobs. Ordinary options transfer separately in an atomic merge with serialized URLs converted. URL replacement skips protected mail tables and options. The sites must have matching table prefixes and InnoDB options tables; the scripts stop before import otherwise. Rollback backups remain complete and stay in the environment where they were created.

Configure **WP Mail SMTP → Settings** and **Settings → General → Enquiry recipient** separately on Local and the droplet. No shared mail configuration file is deployed. Theme, uploads, homepage and catalog sync already leave these settings unchanged. If another mail plugin is introduced, update `scripts/wordpress-db-sync.php` to protect its options and tables too.

`scripts/tests/db-sync-mail.php` checks both directions with temporary SQL tables on Local, using WP-CLI `--skip-plugins --skip-themes eval-file`. It checks credential exclusion, unchanged destination options, an unconfigured destination, queue-table exclusions and serialized URL conversion. It does not send mail or replace either site's database.

The local-only integration test is `scripts/tests/content-sync.php`. Run it with Local's WP-CLI using `--skip-themes eval-file`. It creates and removes temporary pages, image and GLB fixtures, tests import/export, reference mapping, dimensions and repeated runs, and refuses sites whose hostname does not end in `.local`.

See `HANDOFF.md` for the verified live state and private rollback backup location.

## Rank Math page scores

`mu-plugins/janogago-sitemap.php` keeps translated homepage sitemap entries aligned with `get_permalink()`. Install it in WordPress `wp-content/mu-plugins` alongside the Site Kit consent guard. It corrects the English sitemap entry to `/en/home/`, which is the page's canonical URL; `/en/` remains a redirect. After installing or updating it, clear Rank Math's sitemap cache and check `/page-sitemap.xml`. The ordinary theme deployment script does not deploy MU plugins.

Google Search Console ownership for the HTTPS URL-prefix property is verified through `googlee1855318fb319cbb.html` in the live WordPress root. Keep that verification file on the server. It is independent of the theme and Google Site Kit setup.

On the droplet, **Pages → All Pages → SEO Details** shows Rank Math's saved score out of 100. If the column is hidden, enable **SEO Details** under **Screen Options**. In Gutenberg, open the Rank Math button at the top right to inspect its recommendations and edit the focus keyword. New pages need a relevant focus keyword and a saved analysis before a score appears. Rank Math deliberately shows N/A for pages set to noindex.

`scripts/setup-rank-math-scores.php` enables the installed plugin using its account-registration Skip Step flag, shows the score column for administrators, and adds missing keywords for existing named pages. It preserves existing keywords and does not assign scores. Back up the database before running it with WP-CLI `eval-file`, then use **Rank Math SEO → Status & Tools → Database Tools → Recalculate Scores** in the browser. Scores on all seven live pages were calculated through that tool and verified on 2026-10-04. The helper is not an automatic theme migration and does not belong in the theme.


## Enquiry notifications

Both language forms send enquiries to **Settings → General → Enquiry recipient / Pieteikumu saņēmējs**, initially `info@janoga.lv`. Change that address in WordPress when the receiving inbox changes; no code deployment or homepage edits are needed. The public contact links are separate. The visitor's email is the enquiry's Reply-To address. WP Mail SMTP controls the sender and delivery connection, while its Email Test sends to the address entered in the test form. WordPress password resets still go to the email in the requested user's profile.

A saved enquiry shows a floating success notification in the site's existing green. Notification messages and the close-button label are native Paragraph blocks in each language's enquiry form group. The notification overlays the page without affecting layout, uses a short entrance/exit animation, supports Escape and close-button dismissal, and respects reduced motion. Saving failures and validation errors still display actionable errors. The internal `wp_mail` result is stored as `_jg_notification_sent` on the lead; an internal email failure does not turn a saved enquiry into a visitor-facing failure.

`scripts/update-enquiry-notifications.php` updates existing homepage notification blocks while preserving other content and custom confirmation wording. Run it with Local's WP-CLI `eval-file` after installing the current theme. It removes the retired mail-failure warning, shortens the original confirmation, and adds missing editable close labels. Do not reset homepage seed markers. It is a release helper, not an automatic theme migration.

`scripts/tests/enquiry-notifications.php` is a Local-only integration test. Run it with WP-CLI `eval-file` with the theme loaded. It intercepts all email, checks successful and failed mail, saving failure and invalid submissions, verifies accessible results and redirect behavior, configurable recipients, visitor Reply-To and account password-reset routing, and deletes its own test leads and user. Actual inbox delivery is not tested.

Theme 1.0.42 clears submission status URLs after showing the notification and removes automatic notification focus. Successful submissions redirect without a form anchor, preventing later opens from jumping to the form. Normal offer links still navigate to the form.

The mobile client carousel preloads fixed repeated logo batches, keeps native horizontal scrolling in both directions, and waits for swipe momentum to settle before resuming autoplay. Run `node scripts/tests/client-carousel.cjs` for the scrolling logic regression checks. Verify physical iPhone Safari swipes separately.

Theme 1.0.74, the updated food catalog, bilingual heading punctuation and the 3D hero with photo fallback are on Local and the droplet. See `HANDOFF.md` for release verification and rollback locations.

Enquiry forms submit in place without a page reload. Validation names the field that needs correction and preserves entries on failure. Successful submissions clear the form so it can be used again. The server allows five new enquiries in ten minutes per IP address and per email address, quietly discards the hidden honeypot and suppresses identical retries for ten minutes. A saved private lead remains the success condition even if its notification fails. `scripts/tests/enquiry-submissions.php` checks these controls locally with all email intercepted.
