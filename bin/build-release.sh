#!/usr/bin/env bash
# Build dist/buddypress-groups-review-<version>.zip via `grunt dist` (minify, RTL, i18n artifacts), after local CI.
# The copy list lives in gruntfile.js; .distignore mirrors it for gate checks and other tooling.
set -euo pipefail
cd "$(dirname "$0")/.."
bin/local-ci.sh
npx grunt dist
version=$(grep -m1 -E '^\s*\*\s*Version:' review-bp-grps.php | awk '{print $NF}')
out="dist/buddypress-groups-review-$version.zip"
[ -f "$out" ] || { echo "Missing $out"; exit 1; }
leaks=$(unzip -l "$out" | grep -iE '/(audit|bin|docs|plan|marketing|tests|vendor|node_modules)/|CLAUDE|\.md$|composer\.|phpstan' || true)
[ -z "$leaks" ] || { echo "Zip contains dev files:"; echo "$leaks"; exit 1; }
echo "Built $out"
