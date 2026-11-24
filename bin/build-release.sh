#!/usr/bin/env bash
# Build dist/review-buddypress-groups-<version>.zip via `grunt dist` (minify, RTL, i18n artifacts), after local CI.
# The copy list lives in gruntfile.js; .distignore mirrors it for gate checks and other tooling.
# Grunt's zip uses the old folder name, so it is repacked here with the WordPress.org slug as the folder.
set -euo pipefail
cd "$(dirname "$0")/.."
slug=review-buddypress-groups   # WordPress.org slug = zip folder. Locked by CI (wporg-folder-lock.yml).
main=review-bp-grps.php
bin/local-ci.sh
npx grunt dist
version=$(grep -m1 -E '^\s*\*\s*Version:' "$main" | awk '{print $NF}')
grunt_zip="dist/buddypress-groups-review-$version.zip"
[ -f "$grunt_zip" ] || { echo "Missing $grunt_zip"; exit 1; }
out="dist/$slug-$version.zip"
out_abs="$PWD/$out"
work=$(mktemp -d); trap 'rm -rf "$work"' EXIT
unzip -q "$grunt_zip" -d "$work"
mv "$work/buddypress-groups-review" "$work/$slug"
rm -f "$grunt_zip" "$out"
# Deflate every entry: a PHP file stored uncompressed trips the store's upload firewall.
( cd "$work" && python3 -I -c 'import os,sys,zipfile
out,top=sys.argv[1],sys.argv[2]
with zipfile.ZipFile(out,"w",zipfile.ZIP_DEFLATED,compresslevel=9) as z:
    for r,d,f in os.walk(top):
        d.sort()
        for n in sorted(f): z.write(os.path.join(r,n))' "$out_abs" "$slug" )
zip="$out"
# pipefail off for the lock block: unzip piped into head / grep -q exits 141 (SIGPIPE) on larger zips.
set +o pipefail
# --- WordPress.org folder lock (Wbcom standard - do not edit) ----------------------
top=$(unzip -Z1 "$zip" | head -1 | cut -d/ -f1)
[ "$top" = "$slug" ] || { echo "Zip folder '$top' is not the WordPress.org slug '$slug'"; exit 1; }
unzip -Z1 "$zip" | grep -qx "$slug/$main" || { echo "Main file $slug/$main missing from zip"; exit 1; }
[ "$(LC_ALL=C grep -a -c '<?php' "$zip" || true)" = 0 ] || { echo "Zip has plain-text PHP (upload firewall)"; exit 1; }
# -----------------------------------------------------------------------------------
set -o pipefail
leaks=$(unzip -l "$out" | grep -iE ' [^/ ]+/(audit|bin|docs|plan|marketing|tests|vendor|node_modules)/|CLAUDE|\.md$|composer\.|phpstan' || true)
[ -z "$leaks" ] || { echo "Zip contains dev files:"; echo "$leaks"; exit 1; }
echo "Built $out"
