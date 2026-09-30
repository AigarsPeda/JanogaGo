#!/usr/bin/env bash
# One-command release for the theme, catalog records/photos, and LV/EN homepages.
set -Eeuo pipefail
umask 077
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
CONFIG_FILE="${CONFIG_FILE:-$SCRIPT_DIR/janogago-droplet.env}"
if [ -f "$CONFIG_FILE" ]; then . "$CONFIG_FILE"; fi
SSH_KEY="${SSH_KEY:-}"
REMOTE_HOST="${REMOTE_HOST:-}"
REMOTE_WP_PATH="${REMOTE_WP_PATH:-/var/www/janogago}"
REMOTE_THEME_PATH="${REMOTE_THEME_PATH:-$REMOTE_WP_PATH/wp-content/themes/janogago}"
REMOTE_BACKUP_DIR="${REMOTE_BACKUP_DIR:-/var/backups/janogago}"
DRY_RUN=0
quote() { printf "'%s'" "${1//\'/\'\\\'\'}"; }
die() { printf 'Error: %s\n' "$*" >&2; exit 1; }
for arg in "$@"; do
	case "$arg" in
		--dry-run) DRY_RUN=1 ;;
		-h|--help) printf 'Usage: %s [--dry-run]\nBack up and release the food catalog and homepage code/content.\n' "$0"; exit 0 ;;
		*) die "Unknown argument: $arg" ;;
	esac
done
[ -r "$SSH_KEY" ] || die 'SSH key unavailable.'
[ -n "$REMOTE_HOST" ] || die 'REMOTE_HOST unavailable.'
case "$REMOTE_THEME_PATH" in */wp-content/themes/janogago) ;; *) die 'Unexpected theme path.' ;; esac
case "$REMOTE_BACKUP_DIR/" in "$REMOTE_WP_PATH/"*) die 'Backup path must be outside WordPress.' ;; esac
"$SCRIPT_DIR/sync-code-to-droplet.sh" --dry-run
"$SCRIPT_DIR/sync-content-to-droplet.sh" --dry-run
if [ "$DRY_RUN" -eq 1 ]; then
	printf 'Theme and homepage preflight passed. Food catalog preflight runs after the theme is deployed.\n'
	exit 0
fi
SSH_ARGS=(-i "$SSH_KEY" -o BatchMode=yes -o ConnectTimeout=10)
REMOTE_WP="wp --allow-root --path=$(quote "$REMOTE_WP_PATH")"
BACKUP="$(ssh "${SSH_ARGS[@]}" "$REMOTE_HOST" "umask 077; mkdir -p $(quote "$REMOTE_BACKUP_DIR") && mktemp -d $(quote "$REMOTE_BACKUP_DIR/food-release-$(date +%Y%m%d-%H%M%S).XXXXXXXX")")"
printf 'Full pre-release rollback backup: %s\n' "$BACKUP"
ssh "${SSH_ARGS[@]}" "$REMOTE_HOST" "set -e; umask 077; $REMOTE_WP db export $(quote "$BACKUP/database.sql") --quiet; gzip $(quote "$BACKUP/database.sql"); tar -czf $(quote "$BACKUP/theme.tar.gz") -C $(quote "$(dirname "$REMOTE_THEME_PATH")") janogago"
"$SCRIPT_DIR/sync-code-to-droplet.sh"
"$SCRIPT_DIR/sync-food-to-droplet.sh" --dry-run
"$SCRIPT_DIR/sync-food-to-droplet.sh"
"$SCRIPT_DIR/sync-content-to-droplet.sh" --dry-run
"$SCRIPT_DIR/sync-content-to-droplet.sh"
REMOTE_PAGES="$(ssh "${SSH_ARGS[@]}" "$REMOTE_HOST" "$REMOTE_WP option get jg_food_pages --format=json")"
python3 -c 'import json,sys; pages=json.loads(sys.argv[1]); assert all(int(pages.get(lang, 0)) > 0 for lang in ("lv", "en")), "Catalog page verification failed"; print("Verified both food pages.")' "$REMOTE_PAGES"
printf 'Food release complete. Pre-release backup: %s\n' "$BACKUP"
