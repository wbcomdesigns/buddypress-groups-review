<!-- READ-FIRST -->
# READ THIS FIRST

This plugin is **onboarded**. Before grepping the codebase, read the machine-readable inventory:

1. **`audit/manifest.json`** — canonical inventory (REST, AJAX, admin pages, settings, hooks, CPT, caps). Answer "what does this plugin do / where is X" from here.
2. **`audit/FEATURE_AUDIT.md`** — feature-by-feature human view.
3. **`audit/CODE_FLOWS.md`** — request pipelines (submit/approve/settings/criteria/REST).
4. **`audit/graph.html`** — interactive manifest graph (open in browser).
5. **`audit/wppqa-baseline-2026-06-05/SUMMARY.md`** — known bugs at onboarding.

Follow the **`/wp-plugin-development`** skill for all conventions (hooks ownership, REST patterns, security/escaping, admin UI, Lucide icons, release packaging). For UI work also see **`/ux-foundation`**; for QA **`/wp-plugin-smoke`**.

---

# buddypress-groups-review — Wbcom Designs - BuddyPress Group Reviews

- **Main file:** `review-bp-grps.php`
- **Version:** **3.9.0** — header (`review-bp-grps.php:18`), `BGR_PLUGIN_VERSION` (`:58`) and `package.json` all agree.
- **Requires:** WP **6.1** (`:19`) · PHP **7.4** (`:20`)
- **Text domain:** `bp-group-reviews` · **Type:** free BuddyPress addon
- **Hard dependency:** BuddyPress + Groups component (self-deactivates otherwise).

## What it does
Lets BuddyPress group members post text + multi-criteria star reviews of groups. Reviews are stored as a `review` CPT; group admins / site admins approve or deny them. Exposes member-facing AJAX, a REST surface across **two** namespaces (`buddypress/v1` for reviews, `bgr/v1` for group criteria), two widgets, two shortcodes, GamiPress + notifications + activity integration, and multisite support.

## Admin UI wrapper: **NEW card panel** (migration DONE in 3.9.0)
- Controller: **`includes/admin/class-bgr-admin-panel.php`** (`BGR_Admin_Panel`).
- Shell: **`includes/admin/views/shell.php`** — **present**; views in `includes/admin/views/`.
- Top-level menu `wbcomplugins` ("WB Plugins") registered at `class-bgr-admin-panel.php:129`, only if no other Wbcom plugin created it first; `takeover_hub_landing` (`admin_menu` prio 999) reclaims the landing render.
- Primary settings page: **`group-review-settings`** (`:140`, slug kept from the legacy page), callback `BGR_Admin_Panel::render_page`. **6 tabs** (`:54-104`): welcome, general, criteria, display, emails, discover.
- Cap `manage_options`, filterable via `bgr_admin_capability`. Tabs filterable via `bgr_admin_tabs` (`discover` appended after the filter).
- The legacy bundled wrapper `admin/wbcom/` was **REMOVED** in 3.9.0 — with it went the `wbcom_admin_setting_header` shortcode, the `wbcom_addons_cards` AJAX, and the `wbcom-plugins-page` / `-support-` / `-license-` pages. `admin/bgr-admin.php` survives but registers **no menu** — it only handles the `review` CPT, `review_category` taxonomy, capabilities and `map_meta_cap`.

## Settings options (all arrays; deleted on uninstall)
`bgr_admin_general_settings`, `bgr_admin_criteria_settings`, `bgr_admin_display_settings`, `bp_group_review_email_settings`.

## Big-site readiness flags
- `bp_group_review_filter_ratings` scan is now **bounded** by the `bgr_filter_ratings_max_scan` filter (default 500, `bgr-ajax.php:94`). Aggregate stats are cached (`bgr_get_group_review_stats`, limit filter `bgr_review_stats_query_limit` default 10000). Still no true pagination on the ratings filter — revisit before any very-large-site release.
- `uninstall.php` fetches all `review` posts with `posts_per_page => -1` (ids only) — accepted: one-shot path, gated behind `BGR_REMOVE_DATA_ON_UNINSTALL`.

## Vendored libraries (third-party) — source must ship beside every min

**Rule (owner decision, non-negotiable):** WordPress.org requires that any minified/compiled
file we distribute has its human-readable source distributed alongside it. So: **drop what
WordPress core already bundles; ship the unminified source for the rest.** Core bundles jQuery,
jQuery UI, underscore, backbone, lodash, react etc. — use core's registered handle for those and
do not vendor a copy. Core bundles **neither** selectize nor altcha (verified against WP 7.0.1),
so this plugin vendors selectize and ships its source.

| Lib | Version | Files | Upstream URL (exact, for refresh) | Stock or forked? |
|---|---|---|---|---|
| selectize.js (standalone build = sifter + microplugin + selectize) | **0.12.1** | `admin/assets/js/vendor/selectize.min.js` (enqueued) + `selectize.js` (source, added 2026-07-17) | min: https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.1/js/standalone/selectize.min.js <br> source: https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.1/js/standalone/selectize.js | **Stock.** Our min is byte-identical to upstream's apart from one trailing newline (verified 2026-07-17). |
| selectize.css | **0.12.1** | `admin/assets/css/vendor/selectize.css` (source only — no min shipped or needed) | https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.1/css/selectize.css | **LOCAL FORK — do not blindly overwrite.** See below. |

Enqueued at `includes/bgr-scripts.php:342` (`wbcom-selectize-css`) and `:350`
(`wbcom-selectize-js` — the **min**). `selectize.js` is shipped for compliance only and is never
enqueued; nothing should ever enqueue it.

**`selectize.css` is a deliberate fork of upstream v0.12.1** (verified 2026-07-17). Two deltas:
reindented to tabs, and `.selectize-input`'s upstream
`box-shadow: inset 0 1px 1px rgba(0,0,0,.1)` (plus its `-webkit-` twin) replaced with
`box-shadow: none` (line ~141) to sit flat in the admin UI. A naive re-download from cdnjs would
silently revert that style. If you refresh it, re-apply the fork and re-verify the Criteria /
Display tabs in a browser.

**When refreshing:**
1. Read the version banner in the `.min.js` first (`/*! selectize.js - v0.12.1 ... */`). Never guess.
2. Download the matching version's source from the URL above and confirm its banner matches.
   A source that does not correspond to its min is worse than shipping none.
3. **Never re-minify.** `selectize.min.js` is upstream's own build and must stay byte-identical so
   customer behaviour and cache-busting do not change. `gruntfile.js` already excludes
   `vendor/*.js` / `vendor/*.css` from `uglify` and `cssmin` — keep it that way.

**`.gitignore` trap (fixed 2026-07-17):** the Composer rule was an unanchored `vendor/`, which
matched **every** directory named `vendor/` at any depth — including `admin/assets/js/vendor/`.
Newly added third-party sources there were silently ignored by git (the libs already tracked were
unaffected only because .gitignore does not apply to files already in the index). It is now
anchored to `/vendor/`. **Do not remove the leading slash** — doing so re-breaks this silently.

## Known issues (updated 3.9.0 — supersedes the wppqa baseline)
- ~~8 banned `alert()`/`confirm()` calls~~ — **RESOLVED**. Replaced by `public/js/toast.js` + `public/js/confirm.js` (registered as `bgr-toast` / `bgr-confirm`, `bgr-scripts.php:206-207`) and a promise-based confirm modal in `bgr-admin.js`.
- One < 40px button tap target (`bgr-reviews.css:383`) — verify against current file before acting.
- 2 nonce-no-cap wppqa flags = **false positives** (public nopriv endpoints). The 3rd (echo-only wrapper) is moot — the wrapper is gone.

## Refresh
Re-run `/wp-plugin-onboard --refresh` after structural changes. Artifacts live in `audit/`.
