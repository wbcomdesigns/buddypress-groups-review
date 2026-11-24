#!/usr/bin/env bash
# Build dist/review-buddypress-groups-<version>.zip via `grunt dist` (minify, RTL, i18n artifacts), after local CI.
# The copy list lives in gruntfile.js; .distignore mirrors it for gate checks and other tooling.
# grunt stages the plugin in dist/<slug>/; this script is the only zip builder.
set -euo pipefail
cd "$(dirname "$0")/.."
slug=review-buddypress-groups   # WordPress.org slug = zip folder. Locked by CI (wporg-folder-lock.yml).
main=review-bp-grps.php
bin/local-ci.sh
npx grunt dist
version=$(grep -m1 -E '^\s*\*\s*Version:' "$main" | awk '{print $NF}')
stage="dist/$slug"
[ -d "$stage" ] || { echo "Missing $stage (grunt dist did not stage the plugin)"; exit 1; }
out="dist/$slug-$version.zip"
rm -f "$out"
# Deflate every entry: a PHP file stored uncompressed trips the store's upload firewall.
( cd dist && python3 -I -c 'import os,sys,zipfile
out,top=sys.argv[1],sys.argv[2]
with zipfile.ZipFile(out,"w",zipfile.ZIP_DEFLATED,compresslevel=9) as z:
    for r,d,f in os.walk(top):
        d.sort()
        for n in sorted(f): z.write(os.path.join(r,n))' "$slug-$version.zip" "$slug" )
rm -rf "$stage"
zip="$out"
# --- WordPress.org folder lock (Wbcom standard - do not edit) ----------------------
entries=$(unzip -Z1 "$zip")   # read the whole list once: no early-exit pipe, safe under pipefail
top=${entries%%/*}
[ "$top" = "$slug" ] || { echo "Zip folder '$top' is not the WordPress.org slug '$slug'"; exit 1; }
grep -qx "$slug/$main" <<<"$entries" || { echo "Main file $slug/$main missing from zip"; exit 1; }
[ "$(LC_ALL=C grep -a -c '<?php' "$zip" || true)" = 0 ] || { echo "Zip has plain-text PHP (upload firewall)"; exit 1; }
# -----------------------------------------------------------------------------------
leaks=$(unzip -l "$out" | grep -iE ' [^/ ]+/(audit|bin|docs|plan|marketing|tests|vendor|node_modules)/|CLAUDE|\.md$|composer\.|phpstan' || true)
[ -z "$leaks" ] || { echo "Zip contains dev files:"; echo "$leaks"; exit 1; }
echo "Built $out"
