#!/usr/bin/env bash

set -Eeuo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
CONFIG_FILE="${CONFIG_FILE:-$SCRIPT_DIR/janogago-droplet.env}"
if [ -f "$CONFIG_FILE" ]; then
	# shellcheck disable=SC1090
	. "$CONFIG_FILE"
fi

SSH_KEY="${SSH_KEY:-}"
REMOTE_HOST="${REMOTE_HOST:-}"
REMOTE_WP_PATH="${REMOTE_WP_PATH:-/var/www/janogago}"
LOCAL_WP_PATH="${LOCAL_WP_PATH:-/Users/aigarspeda/Local Sites/janogago/app/public}"
LOCAL_UPLOADS_PATH="${LOCAL_UPLOADS_PATH:-$LOCAL_WP_PATH/wp-content/uploads}"
REMOTE_UPLOADS_PATH="${REMOTE_UPLOADS_PATH:-$REMOTE_WP_PATH/wp-content/uploads}"
DRY_RUN=0

usage() {
	cat <<'EOF'
Usage: ./scripts/sync-uploads-to-droplet.sh [--dry-run]

Synchronize Local's WordPress Media Library files to the JāņogaGO droplet.
New and changed uploads are copied to the droplet. Existing droplet uploads are
never deleted, so this is safe for incremental content releases.

Environment overrides:
  CONFIG_FILE, SSH_KEY, REMOTE_HOST, REMOTE_WP_PATH, LOCAL_WP_PATH,
  LOCAL_UPLOADS_PATH, REMOTE_UPLOADS_PATH
EOF
}

die() {
	printf 'Error: %s\n' "$*" >&2
	exit 1
}

for arg in "$@"; do
	case "$arg" in
		--dry-run) DRY_RUN=1 ;;
		-h|--help) usage; exit 0 ;;
		*) die "Unknown argument: $arg" ;;
	esac
done

command -v ssh >/dev/null 2>&1 || die "ssh is not installed."
command -v rsync >/dev/null 2>&1 || die "rsync is not installed."
[ -r "$SSH_KEY" ] || die "SSH key not found: $SSH_KEY"
[ -n "$REMOTE_HOST" ] || die "REMOTE_HOST is not configured."
[ -d "$LOCAL_UPLOADS_PATH" ] || die "Local uploads path is unavailable: $LOCAL_UPLOADS_PATH"

case "$REMOTE_UPLOADS_PATH" in
	*/wp-content/uploads) ;;
	*) die "Refusing to synchronize unexpected remote path: $REMOTE_UPLOADS_PATH" ;;
esac

SSH_ARGS=(-i "$SSH_KEY" -o BatchMode=yes -o ConnectTimeout=10)
RSYNC_ARGS=(--archive --no-owner --no-group --compress --checksum --itemize-changes --exclude=.DS_Store)

if [ "$DRY_RUN" -eq 1 ]; then
	ssh "${SSH_ARGS[@]}" "$REMOTE_HOST" "test -d '$REMOTE_UPLOADS_PATH'" \
		|| die "Droplet uploads path is unavailable: $REMOTE_UPLOADS_PATH"
	RSYNC_ARGS+=(--dry-run)
	printf 'Dry run. No uploads will change.\n'
else
	ssh "${SSH_ARGS[@]}" "$REMOTE_HOST" "mkdir -p '$REMOTE_UPLOADS_PATH'"
fi

rsync "${RSYNC_ARGS[@]}" \
	-e "ssh -i $SSH_KEY -o BatchMode=yes -o ConnectTimeout=10" \
	"$LOCAL_UPLOADS_PATH/" "$REMOTE_HOST:$REMOTE_UPLOADS_PATH/"

if [ "$DRY_RUN" -eq 0 ]; then
	ssh "${SSH_ARGS[@]}" "$REMOTE_HOST" "chown -R www-data:www-data '$REMOTE_UPLOADS_PATH'"
	printf 'Media Library uploads synchronized to %s\n' "$REMOTE_HOST"
fi
