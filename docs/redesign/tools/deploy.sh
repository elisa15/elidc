#!/usr/bin/env bash
# Deploy the B− theme to elisaarmas.com, one stage at a time. See docs/redesign/07-deploy-plan.md.
#
#   docs/redesign/tools/deploy.sh <stage>
#
# Stages, in order: preflight · backup · upload · migrate · verify · cleanup
# Re-send only the scripts and screenshots: upload-tools
# Undo:             rollback-theme · rollback-db <backup-dir>
#
# Only touches public_html/wp-content (themes/elisa-dela-cruz*, a temporary
# edc-deploy-tmp folder, and uploads via the media import) and this site's database.
set -euo pipefail

HOST="${EDC_HOST:-dcx}"
ROOT="public_html"
THEMES="$ROOT/wp-content/themes"
TMP="$ROOT/wp-content/edc-deploy-tmp"
REPO="$(cd "$(dirname "$0")/../../.." && pwd)"
BACKUPS="${EDC_BACKUPS:-$HOME/Backups/elisaarmas-deploy}"
SITE="https://elisaarmas.com"
UA="Mozilla/5.0 (Macintosh; Intel Mac OS X 14_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130 Safari/537.36"

# One shared SSH connection: the host resets rapid repeated logins.
SSH_OPTS=(-o BatchMode=yes -o ConnectTimeout=20 -o ControlMaster=auto -o "ControlPath=$HOME/.ssh/sockets/edc-%r@%h-%p" -o ControlPersist=15m)
mkdir -p "$HOME/.ssh/sockets"
remote() { ssh "${SSH_OPTS[@]}" "$HOST" "cd ~/$ROOT && $*"; }
say() { printf '\n\033[1m== %s\033[0m\n' "$*"; }

preflight() {
	say "Local branch and tests"
	git -C "$REPO" status --short | grep -q . && { echo "Uncommitted changes in the repo. Commit first."; exit 1; }
	git -C "$REPO" log --oneline -1
	find "$REPO/themes/elisa-dela-cruz" -name '*.php' -exec php -l {} \; | grep -v '^No syntax errors' || true
	node --check "$REPO/themes/elisa-dela-cruz/assets/js/site.js"
	say "Live site"
	remote 'wp option get home && wp option get stylesheet && grep -m1 Version wp-content/themes/elisa-dela-cruz/style.css && wp plugin is-active advanced-custom-fields-pro && echo "ACF PRO active" && df -h ~ | tail -1'
	remote "test ! -e wp-content/themes/elisa-dela-cruz.incoming && test ! -e wp-content/themes/elisa-dela-cruz.previous && echo 'No leftovers from an earlier deploy.'"
}

backup() {
	local dir="$BACKUPS/$(date +%Y-%m-%d-%H%M)"
	mkdir -p "$dir"
	say "Database → $dir/db.sql.gz"
	remote 'wp db export - --single-transaction --quiet' | gzip > "$dir/db.sql.gz"
	gzip -t "$dir/db.sql.gz"
	echo "Tables: $(gunzip -c "$dir/db.sql.gz" | grep -c '^CREATE TABLE')  Size: $(du -h "$dir/db.sql.gz" | cut -f1)"
	say "Theme files → $dir/theme/"
	rsync -a -e "ssh ${SSH_OPTS[*]}" "$HOST:$THEMES/elisa-dela-cruz/" "$dir/theme/"
	echo "Files: $(find "$dir/theme" -type f | wc -l | tr -d ' ')"
	say "Settings snapshot → $dir/settings.txt"
	remote 'wp menu item list 33 --fields=db_id,title,url --format=csv; for k in show_work show_hero show_cta nav_cta_label cf7_shortcode contact_email; do printf "%s=" $k; wp eval "echo json_encode( get_field( \"$k\", \"option\" ) );"; echo; done' > "$dir/settings.txt"
	cat "$dir/settings.txt"
	echo "$dir" > "$BACKUPS/latest"
	say "Backup done: $dir"
}

upload() {
	say "Upload theme to $THEMES/elisa-dela-cruz.incoming"
	rsync -a --delete --exclude '.DS_Store' -e "ssh ${SSH_OPTS[*]}" "$REPO/themes/elisa-dela-cruz/" "$HOST:$THEMES/elisa-dela-cruz.incoming/"
	upload_tools
	say "Swap themes (old copy kept as elisa-dela-cruz.previous)"
	remote 'cd wp-content/themes && test -d elisa-dela-cruz.incoming && test ! -e elisa-dela-cruz.previous && mv elisa-dela-cruz elisa-dela-cruz.previous && mv elisa-dela-cruz.incoming elisa-dela-cruz && grep -m1 Version elisa-dela-cruz/style.css'
}

upload_tools() {
	say "Upload deploy tools and screenshots to $TMP"
	# Relative paths from the repo root (macOS rsync ignores the /./ marker).
	remote 'rm -rf wp-content/edc-deploy-tmp && mkdir -p wp-content/edc-deploy-tmp'
	( cd "$REPO" && rsync -aR -e "ssh ${SSH_OPTS[*]}" docs/redesign/tools docs/redesign/reference/prototype/assets/shots "$HOST:$TMP/" )
	remote 'find wp-content/edc-deploy-tmp -type f | sort'
}

migrate() {
	local t="wp-content/edc-deploy-tmp/docs/redesign/tools"
	say "ACF field groups (dry run, then sync)"
	remote "wp eval-file $t/acf-sync.php --exec=\"define('EDC_DRY_RUN', true);\""
	remote "wp eval-file $t/acf-sync.php"
	say "Content import (verified content only; ⏳ fields stay empty)"
	remote "wp eval-file $t/seed-content.php --exec=\"define('EDC_SEED_LIVE', 'approved-by-elisa');\""
	say "Work section, page templates, menu links"
	remote "wp eval-file $t/live-settings.php --exec=\"define('EDC_DRY_RUN', true);\""
	remote "wp eval-file $t/live-settings.php"
	say "Thumbnails for the new image sizes"
	remote 'wp media regenerate --only-missing --yes 2>&1 | tail -2'
	remote 'wp cache flush; wp rewrite flush'
}

verify() {
	say "Pages"
	local fail=0 p html
	for p in / /projects/ "/projects/?type=solved" /projects/us-beauty-brand/ /about/ /services/ /contact/; do
		html="$(curl -s -A "$UA" "$SITE$p" -w '\n%{http_code}')"
		local code="${html##*$'\n'}"
		local h1; h1="$(grep -o '<h1' <<<"$html" | wc -l | tr -d ' ')"
		local hash; hash="$(grep -c 'href="#"' <<<"$html" || true)"
		local leak; leak="$(grep -ciE 'revel|lorem' <<<"$html" || true)"
		printf '%-30s %s  h1=%s  href#=%s  leaks=%s\n' "$p" "$code" "$h1" "$hash" "$leak"
		[[ "$code" == 200 && "$h1" == 1 && "$hash" == 0 && "$leak" == 0 ]] || fail=1
	done
	printf '%-30s %s\n' "/projects/three-wineries/" "$(curl -s -A "$UA" -o /dev/null -w '%{http_code} → %{redirect_url}' "$SITE/projects/three-wineries/")"
	say "PHP errors from this theme since the deploy"
	remote 'grep -h "elisa-dela-cruz" error_log wp-content/debug.log 2>/dev/null | tail -20 || true'
	[[ $fail == 0 ]] && say "Verify passed" || { say "Verify FAILED: see above"; exit 1; }
}

cleanup() {
	say "Remove $TMP (the old theme copy stays until Elisa confirms)"
	remote 'rm -rf wp-content/edc-deploy-tmp && echo removed'
}

rollback_theme() {
	say "Put the previous theme back"
	remote 'cd wp-content/themes && test -d elisa-dela-cruz.previous && mv elisa-dela-cruz elisa-dela-cruz.failed-$(date +%Y%m%d%H%M) && mv elisa-dela-cruz.previous elisa-dela-cruz && grep -m1 Version elisa-dela-cruz/style.css'
}

rollback_db() {
	local dir="${1:-$(cat "$BACKUPS/latest")}"
	say "Restore the database from $dir/db.sql.gz"
	gzip -t "$dir/db.sql.gz"
	gunzip -c "$dir/db.sql.gz" | remote 'wp db import -'
	remote 'wp cache flush'
}

case "${1:-}" in
	preflight|backup|upload|migrate|verify|cleanup) "$1" ;;
	upload-tools) upload_tools ;;
	rollback-theme) rollback_theme ;;
	rollback-db) rollback_db "${2:-}" ;;
	*) sed -n '2,10p' "$0"; exit 1 ;;
esac
