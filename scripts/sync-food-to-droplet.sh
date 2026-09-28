#!/usr/bin/env bash
set -Eeuo pipefail
umask 077

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
CONFIG_FILE="${CONFIG_FILE:-$SCRIPT_DIR/janogago-droplet.env}"
if [ -f "$CONFIG_FILE" ]; then . "$CONFIG_FILE"; fi
SSH_KEY="${SSH_KEY:-}"
REMOTE_HOST="${REMOTE_HOST:-}"
REMOTE_WP_PATH="${REMOTE_WP_PATH:-/var/www/janogago}"
REMOTE_BACKUP_DIR="${REMOTE_BACKUP_DIR:-/var/backups/janogago}"
LOCAL_WP_PATH="${LOCAL_WP_PATH:-/Users/aigarspeda/Local Sites/janogago/app/public}"
LOCAL_PHP_BIN="${LOCAL_PHP_BIN:-}"
LOCAL_PHP_INI="${LOCAL_PHP_INI:-}"
LOCAL_WP_CLI="${LOCAL_WP_CLI:-/Applications/Local.app/Contents/Resources/extraResources/bin/wp-cli/wp-cli.phar}"
LOCAL_MYSQL_SOCKET="${LOCAL_MYSQL_SOCKET:-}"
DRY_RUN=0
LOCAL_STAGE=""
REMOTE_STAGE=""
die() { printf 'Error: %s\n' "$*" >&2; exit 1; }
quote() { printf "'%s'" "${1//\'/\'\\\'\'}"; }
cleanup() {
	if [ -n "$REMOTE_STAGE" ]; then ssh "${SSH_ARGS[@]}" "$REMOTE_HOST" "rm -rf -- $(quote "$REMOTE_STAGE")" >/dev/null 2>&1 || true; fi
	if [ -n "$LOCAL_STAGE" ]; then rm -rf -- "$LOCAL_STAGE"; fi
}
trap cleanup EXIT
for arg in "$@"; do
	case "$arg" in
		--dry-run) DRY_RUN=1 ;;
		-h|--help) printf 'Usage: %s [--dry-run]\nSelectively transfer local food products, photos, categories, labels and LV/EN food pages.\n' "$0"; exit 0 ;;
		*) die "Unknown argument: $arg" ;;
	esac
done
for command in ssh scp gzip; do command -v "$command" >/dev/null || die "$command is not installed."; done
[ -r "$SSH_KEY" ] || die 'SSH key unavailable.'
[ -n "$REMOTE_HOST" ] || die 'REMOTE_HOST unavailable.'
[ -x "$LOCAL_PHP_BIN" ] || die 'Local PHP unavailable.'
[ -r "$LOCAL_WP_CLI" ] || die 'Local WP-CLI unavailable.'
[ -S "$LOCAL_MYSQL_SOCKET" ] || die 'Start the local WordPress site.'
case "$REMOTE_BACKUP_DIR/" in "$REMOTE_WP_PATH/"*) die 'Backup directory must be outside WordPress.' ;; esac
SSH_ARGS=(-i "$SSH_KEY" -o BatchMode=yes -o ConnectTimeout=10)
PHP_ARGS=(-d "mysqli.default_socket=$LOCAL_MYSQL_SOCKET")
if [ -n "$LOCAL_PHP_INI" ]; then PHP_ARGS=(-c "$LOCAL_PHP_INI" "${PHP_ARGS[@]}"); fi
LOCAL_STAGE="$(mktemp -d /tmp/janogago-food.XXXXXXXX)"
"$LOCAL_PHP_BIN" "${PHP_ARGS[@]}" "$LOCAL_WP_CLI" --path="$LOCAL_WP_PATH" eval-file "$SCRIPT_DIR/wordpress-food-sync.php" export "$LOCAL_STAGE"
REMOTE_STAGE="$(ssh "${SSH_ARGS[@]}" "$REMOTE_HOST" 'umask 077; mktemp -d /tmp/janogago-food.XXXXXXXX')"
case "$REMOTE_STAGE" in /tmp/janogago-food.*) ;; *) REMOTE_STAGE=''; die 'Unexpected remote staging directory.' ;; esac
scp -q -r "${SSH_ARGS[@]}" "$LOCAL_STAGE/release.json" "$LOCAL_STAGE/media" "$SCRIPT_DIR/wordpress-food-sync.php" "$REMOTE_HOST:$REMOTE_STAGE/"
REMOTE_WP="wp --allow-root --path=$(quote "$REMOTE_WP_PATH")"
REMOTE_HELPER="$(quote "$REMOTE_STAGE/wordpress-food-sync.php")"
REMOTE_PACKAGE="$(quote "$REMOTE_STAGE")"
ssh "${SSH_ARGS[@]}" "$REMOTE_HOST" "$REMOTE_WP eval-file $REMOTE_HELPER check $REMOTE_PACKAGE"
if [ "$DRY_RUN" -eq 1 ]; then printf 'Dry run passed. Live catalog unchanged.\n'; exit 0; fi
REMOTE_BACKUP="$(ssh "${SSH_ARGS[@]}" "$REMOTE_HOST" "umask 077; mkdir -p $(quote "$REMOTE_BACKUP_DIR") && mktemp -d $(quote "$REMOTE_BACKUP_DIR/food-$(date +%Y%m%d-%H%M%S).XXXXXXXX")")"
printf 'Private rollback backup: %s\n' "$REMOTE_BACKUP"
ssh "${SSH_ARGS[@]}" "$REMOTE_HOST" "set -e; umask 077; $REMOTE_WP db export $(quote "$REMOTE_BACKUP/database.sql") --quiet; gzip $(quote "$REMOTE_BACKUP/database.sql"); cp $REMOTE_PACKAGE/release.json $(quote "$REMOTE_BACKUP/release.json"); $REMOTE_WP eval-file $REMOTE_HELPER apply $REMOTE_PACKAGE; $REMOTE_WP cache flush; $REMOTE_WP rewrite flush"
printf 'Food catalog synchronized.\n'
