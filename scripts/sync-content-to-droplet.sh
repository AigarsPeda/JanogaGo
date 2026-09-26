#!/usr/bin/env bash

set -Eeuo pipefail
umask 077

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
CONFIG_FILE="${CONFIG_FILE:-$SCRIPT_DIR/janogago-droplet.env}"
if [ -f "$CONFIG_FILE" ]; then
	# shellcheck disable=SC1090
	. "$CONFIG_FILE"
fi

SSH_KEY="${SSH_KEY:-}"
REMOTE_HOST="${REMOTE_HOST:-}"
REMOTE_WP_PATH="${REMOTE_WP_PATH:-/var/www/janogago}"
REMOTE_BACKUP_DIR="${REMOTE_BACKUP_DIR:-/var/backups/janogago}"
LOCAL_WP_PATH="${LOCAL_WP_PATH:-/Users/aigarspeda/Local Sites/janogago/app/public}"
LOCAL_PHP_BIN="${LOCAL_PHP_BIN:-}"
LOCAL_PHP_INI="${LOCAL_PHP_INI:-}"
LOCAL_WP_CLI="${LOCAL_WP_CLI:-/Applications/Local.app/Contents/Resources/extraResources/bin/wp-cli/wp-cli.phar}"
LOCAL_MYSQL_SOCKET="${LOCAL_MYSQL_SOCKET:-}"
LANGUAGES="lv,en"
DRY_RUN=0
TEMP_DIR=""
REMOTE_TEMP=""

usage() {
	cat <<'EOF'
Usage: ./scripts/sync-content-to-droplet.sh [--dry-run] [--languages=lv,en]

Transfer the selected LV/EN home page blocks and referenced Image-block media
from Local to the droplet. Existing page IDs, titles, slugs, users, settings,
enquiries and unrelated content are preserved. Matching photos are reused;
new photos are imported through WordPress into its Media Library/uploads.

--dry-run checks the transfer without changing live content or uploads.
--languages=lv or --languages=en transfers just that language; default is both.
An apply run backs up the live database and page/media state before changes.
This does not deploy theme code. See README.md for the release order.

Uses the same scripts/janogago-droplet.env configuration as the other scripts:
  SSH_KEY, REMOTE_HOST, REMOTE_WP_PATH, REMOTE_BACKUP_DIR,
  LOCAL_WP_PATH, LOCAL_PHP_BIN, LOCAL_PHP_INI, LOCAL_WP_CLI, LOCAL_MYSQL_SOCKET
EOF
}

die() { printf 'Error: %s\n' "$*" >&2; exit 1; }
quote() { printf "'%s'" "${1//\'/\'\\\'\'}"; }
cleanup() {
	if [ -n "$REMOTE_TEMP" ]; then
		ssh "${SSH_ARGS[@]}" "$REMOTE_HOST" "rm -rf -- $(quote "$REMOTE_TEMP")" >/dev/null 2>&1 || true
	fi
	if [ -n "$TEMP_DIR" ]; then rm -rf -- "$TEMP_DIR"; fi
}
trap cleanup EXIT

for arg in "$@"; do
	case "$arg" in
		--dry-run) DRY_RUN=1 ;;
		--languages=*) LANGUAGES="${arg#*=}" ;;
		-h|--help) usage; exit 0 ;;
		*) die "Unknown argument: $arg" ;;
	esac
done
case "$LANGUAGES" in lv|en|lv,en|en,lv) ;; *) die "Languages must be lv, en, or lv,en." ;; esac
for command in ssh scp gzip; do command -v "$command" >/dev/null || die "$command is not installed."; done
[ -r "$SSH_KEY" ] || die "Configured SSH key is unavailable."
[ -n "$REMOTE_HOST" ] || die "REMOTE_HOST is not configured."
[ -x "$LOCAL_PHP_BIN" ] || die "LOCAL_PHP_BIN is unavailable."
[ -r "$LOCAL_WP_CLI" ] || die "LOCAL_WP_CLI is unavailable."
[ -d "$LOCAL_WP_PATH" ] || die "Local WordPress directory is unavailable."
[ -S "$LOCAL_MYSQL_SOCKET" ] || die "Start the JanogaGo site in Local; its database socket is unavailable."
case "$REMOTE_WP_PATH" in /*) ;; *) die "REMOTE_WP_PATH must be absolute." ;; esac
case "$REMOTE_BACKUP_DIR" in /*) ;; *) die "REMOTE_BACKUP_DIR must be absolute." ;; esac
case "$REMOTE_BACKUP_DIR/" in "$REMOTE_WP_PATH/"*) die "Backups must be outside the public WordPress directory." ;; esac
SSH_ARGS=(-i "$SSH_KEY" -o BatchMode=yes -o ConnectTimeout=10)
PHP_ARGS=(-d "mysqli.default_socket=$LOCAL_MYSQL_SOCKET")
if [ -n "$LOCAL_PHP_INI" ]; then
	[ -r "$LOCAL_PHP_INI" ] || die "LOCAL_PHP_INI is unavailable."
	PHP_ARGS=(-c "$LOCAL_PHP_INI" "${PHP_ARGS[@]}")
fi

TEMP_DIR="$(mktemp -d /tmp/janogago-content.XXXXXX)"
"$LOCAL_PHP_BIN" "${PHP_ARGS[@]}" "$LOCAL_WP_CLI" --path="$LOCAL_WP_PATH" --skip-themes \
	eval-file "$SCRIPT_DIR/wordpress-content-sync.php" export "$TEMP_DIR" "$LANGUAGES"
REMOTE_TEMP="$(ssh "${SSH_ARGS[@]}" "$REMOTE_HOST" 'umask 077; mktemp -d /tmp/janogago-content.XXXXXXXX')"
case "$REMOTE_TEMP" in /tmp/janogago-content.*) ;; *) REMOTE_TEMP=""; die "Unexpected remote staging directory." ;; esac
scp -q -r "${SSH_ARGS[@]}" "$TEMP_DIR/release.json" "$TEMP_DIR/media" \
	"$SCRIPT_DIR/wordpress-content-sync.php" "$REMOTE_HOST:$REMOTE_TEMP/"
REMOTE_WP="wp --allow-root --path=$(quote "$REMOTE_WP_PATH") --skip-themes"
REMOTE_HELPER="$(quote "$REMOTE_TEMP/wordpress-content-sync.php")"
REMOTE_PACKAGE="$(quote "$REMOTE_TEMP")"
ssh "${SSH_ARGS[@]}" "$REMOTE_HOST" "$REMOTE_WP eval-file $REMOTE_HELPER check $REMOTE_PACKAGE"
if [ "$DRY_RUN" -eq 1 ]; then
	printf 'Dry run passed. No live content or uploads changed.\n'
	exit 0
fi

REMOTE_BACKUP="$(ssh "${SSH_ARGS[@]}" "$REMOTE_HOST" "umask 077; mkdir -p $(quote "$REMOTE_BACKUP_DIR") && mktemp -d $(quote "$REMOTE_BACKUP_DIR/content-$(date +%Y%m%d-%H%M%S).XXXXXXXX")")"
printf 'Private rollback backup: %s\n' "$REMOTE_BACKUP"
ssh "${SSH_ARGS[@]}" "$REMOTE_HOST" \
	"set -e; umask 077; $REMOTE_WP db export $(quote "$REMOTE_BACKUP/database.sql") --quiet; gzip $(quote "$REMOTE_BACKUP/database.sql"); $REMOTE_WP eval-file $REMOTE_HELPER apply $REMOTE_PACKAGE $(quote "$REMOTE_BACKUP"); $REMOTE_WP cache flush"
printf 'Selected homepage content and Media Library references synchronized.\n'
