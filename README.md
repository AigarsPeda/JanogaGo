# JanogaGo

Custom WordPress theme for JanogaGo's managed food-vending service website.

The theme supports Latvian and English content through Polylang, editable page content, WordPress Media Library images, standard menus, a Custom Logo, and website enquiries stored in WordPress.

## Local theme location

`theme/janogago`

## Droplet deployment

The `scripts/` folder contains theme, content, uploads, and database synchronization scripts for the JāņogaGO droplet. The scripts contain no private-key details; the local configuration must stay out of Git.

The existing droplet uses `scripts/janogago-droplet.env`. For another installation, copy the example configuration, add the real values, and keep it out of Git. Each script explains the required fields and stops if the droplet details are missing.

For a routine theme release:

```bash
./scripts/sync-code-to-droplet.sh --dry-run
./scripts/sync-code-to-droplet.sh
```

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

For a combined content and code release, back up the existing theme, dry-run both scripts, then run content sync before code sync. This ensures media and authored content are ready before any new theme seed can run. Check LV/EN in the browser after deployment. Routine edits made directly in live WordPress need no deployment; content sync intentionally replaces the selected live homepage blocks with Local's versions.

Do not use `push-db-to-droplet.sh --yes` for a routine code/content release: it replaces the whole live database and requires explicit authorization for that replacement.

The local-only integration test is `scripts/tests/content-sync.php`. Run it with Local's WP-CLI using `--skip-themes eval-file`. It creates and removes temporary pages and an image fixture, tests imports and repeated runs, and refuses sites whose hostname does not end in `.local`.

The 2026-09-26 release deployed theme 1.0.36 and both language pages this way. See `HANDOFF.md` for the verified live state and private rollback backup location.


## Enquiry notifications

A saved enquiry shows a floating success notification in the site's existing green. Notification messages and the close-button label are native Paragraph blocks in each language's enquiry form group. The notification overlays the page without affecting layout, uses a short entrance/exit animation, supports Escape and close-button dismissal, and respects reduced motion. Saving failures and validation errors still display actionable errors. The internal `wp_mail` result is stored as `_jg_notification_sent` on the lead; an internal email failure does not turn a saved enquiry into a visitor-facing failure.

`scripts/update-enquiry-notifications.php` updates existing homepage notification blocks while preserving other content and custom confirmation wording. Run it with Local's WP-CLI `eval-file` after installing the current theme. It removes the retired mail-failure warning, shortens the original confirmation, and adds missing editable close labels. Do not reset homepage seed markers. It is a release helper, not an automatic theme migration.

`scripts/tests/enquiry-notifications.php` is a Local-only integration test. Run it with WP-CLI `eval-file` with the theme loaded. It intercepts all email, checks successful and failed mail, saving failure and invalid submissions, verifies accessible results and redirect behavior, and deletes its own test leads. Actual inbox delivery is not tested.

Theme 1.0.42 clears submission status URLs after showing the notification and removes automatic notification focus. Successful submissions redirect without a form anchor, preventing later opens from jumping to the form. Normal offer links still navigate to the form.

The mobile client carousel preloads fixed repeated logo batches, keeps native horizontal scrolling in both directions, and waits for swipe momentum to settle before resuming autoplay. Run `node scripts/tests/client-carousel.cjs` for the scrolling logic regression checks. Verify physical iPhone Safari swipes separately.

Theme 1.0.43 is deployed locally and on the droplet; see `HANDOFF.md` for the current content hashes and rollback locations.
