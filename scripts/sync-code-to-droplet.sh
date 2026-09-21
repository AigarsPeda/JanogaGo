#!/usr/bin/env bash

set -Eeuo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"

# TODO: once the droplet exists, copy janogago-droplet.env.example to
# janogago-droplet.env and replace its placeholder SSH host, key, and domain.
CONFIG_FILE="${CONFIG_FILE:-$SCRIPT_DIR/janogago-droplet.env}"
if [ -f "$CONFIG_FILE" ]; then
	# shellcheck disable=SC1090
	. "$CONFIG_FILE"
fi

SSH_KEY="${SSH_KEY:-}"
REMOTE_HOST="${REMOTE_HOST:-}"
REMOTE_WP_PATH="${REMOTE_WP_PATH:-/var/www/janogago}"
REMOTE_URL="${REMOTE_URL:-}"
LOCAL_THEME_PATH="${LOCAL_THEME_PATH:-$PROJECT_ROOT/theme/janogago}"
REMOTE_THEME_PATH="${REMOTE_THEME_PATH:-$REMOTE_WP_PATH/wp-content/themes/janogago}"

DRY_RUN=0

usage() {
	cat <<'EOF'
Usage: ./scripts/sync-code-to-droplet.sh [--dry-run]

Synchronize the local JāņogaGO theme with the DigitalOcean droplet.
The remote theme directory becomes an exact copy of the local theme directory.

Before the first use, copy scripts/janogago-droplet.env.example to
scripts/janogago-droplet.env and fill in the TODO values.

Environment overrides:
  CONFIG_FILE, SSH_KEY, REMOTE_HOST, REMOTE_WP_PATH, REMOTE_URL,
  LOCAL_THEME_PATH, REMOTE_THEME_PATH
EOF
}

die() {
	printf 'Error: %s\n' "$*" >&2
	exit 1
}

for arg in "$@"; do
	case "$arg" in
		--dry-run)
			DRY_RUN=1
			;;
		-h|--help)
			usage
			exit 0
			;;
		*)
			die "Unknown argument: $arg"
			;;
	esac
done

command -v ssh >/dev/null 2>&1 || die "ssh is not installed."
command -v rsync >/dev/null 2>&1 || die "rsync is not installed."
command -v shasum >/dev/null 2>&1 || die "shasum is not installed."
command -v curl >/dev/null 2>&1 || die "curl is not installed."

[ -r "$SSH_KEY" ] || die "SSH key not found: $SSH_KEY"
[ -n "$REMOTE_HOST" ] || die "REMOTE_HOST is not configured. Update scripts/janogago-droplet.env."
[ -n "$REMOTE_URL" ] || die "REMOTE_URL is not configured. Update scripts/janogago-droplet.env."
[ -d "$LOCAL_THEME_PATH" ] || die "Local theme not found: $LOCAL_THEME_PATH"
[ -f "$LOCAL_THEME_PATH/style.css" ] || die "Local theme is missing style.css."

case "$REMOTE_THEME_PATH" in
	*/wp-content/themes/janogago)
		;;
	*)
		die "Refusing to synchronize unexpected remote path: $REMOTE_THEME_PATH"
		;;
esac

SSH_ARGS=(-i "$SSH_KEY" -o BatchMode=yes -o ConnectTimeout=10)
RSYNC_SSH="ssh -i $SSH_KEY -o BatchMode=yes -o ConnectTimeout=10"

ssh "${SSH_ARGS[@]}" "$REMOTE_HOST" \
	"test -d '$REMOTE_THEME_PATH' && test -f '$REMOTE_THEME_PATH/style.css'" \
	|| die "Remote JāņogaGO theme was not found at $REMOTE_THEME_PATH"

RSYNC_ARGS=(
	--archive
	--no-owner
	--no-group
	--compress
	--checksum
	--delete-after
	--itemize-changes
	--exclude=.DS_Store
)

if [ "$DRY_RUN" -eq 1 ]; then
	RSYNC_ARGS+=(--dry-run)
	printf 'Dry run. No remote files will change.\n'
fi

rsync "${RSYNC_ARGS[@]}" -e "$RSYNC_SSH" \
	"$LOCAL_THEME_PATH/" \
	"$REMOTE_HOST:$REMOTE_THEME_PATH/"

if [ "$DRY_RUN" -eq 1 ]; then
	exit 0
fi

ssh "${SSH_ARGS[@]}" "$REMOTE_HOST" \
	"chown -R www-data:www-data '$REMOTE_THEME_PATH' && wp --allow-root --path='$REMOTE_WP_PATH' cache flush && wp --allow-root --path='$REMOTE_WP_PATH' rewrite flush"

LOCAL_HASH="$(shasum -a 256 "$LOCAL_THEME_PATH/functions.php" | awk '{print $1}')"
REMOTE_HASH="$(ssh "${SSH_ARGS[@]}" "$REMOTE_HOST" "sha256sum '$REMOTE_THEME_PATH/functions.php'" | awk '{print $1}')"

[ "$LOCAL_HASH" = "$REMOTE_HASH" ] || die "Remote verification failed: functions.php hashes differ."

curl --fail --silent --show-error --location "$REMOTE_URL/" >/dev/null

printf 'Theme synchronized and verified at %s\n' "$REMOTE_URL"
