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

## Known issues (updated 3.9.0 — supersedes the wppqa baseline)
- ~~8 banned `alert()`/`confirm()` calls~~ — **RESOLVED**. Replaced by `public/js/toast.js` + `public/js/confirm.js` (registered as `bgr-toast` / `bgr-confirm`, `bgr-scripts.php:206-207`) and a promise-based confirm modal in `bgr-admin.js`.
- One < 40px button tap target (`bgr-reviews.css:383`) — verify against current file before acting.
- 2 nonce-no-cap wppqa flags = **false positives** (public nopriv endpoints). The 3rd (echo-only wrapper) is moot — the wrapper is gone.

## Refresh
Re-run `/wp-plugin-onboard --refresh` after structural changes. Artifacts live in `audit/`.
