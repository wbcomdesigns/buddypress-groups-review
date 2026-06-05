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
- **Version:** header/constant `BGR_PLUGIN_VERSION` = **3.8.1**; dev branch = **3.9.0** (⚠ version constant not yet bumped — do this before release).
- **Text domain:** `bp-group-reviews` · **Type:** free BuddyPress addon
- **Hard dependency:** BuddyPress + Groups component (self-deactivates otherwise).

## What it does
Lets BuddyPress group members post text + multi-criteria star reviews of groups. Reviews are stored as a `review` CPT; group admins / site admins approve or deny them. Exposes member-facing AJAX, a `buddypress/v1` REST surface, two widgets, two shortcodes, GamiPress + notifications + activity integration, and multisite support.

## Admin UI wrapper: **OLD**
- Wrapper file: **`admin/wbcom/wbcom-admin-settings.php`** (legacy tab-based pattern).
- Top-level menu `wbcomplugins` ("WB Plugins") registered in `admin/bgr-admin.php:58` on `admin_menu`.
- Primary settings page: **`group-review-settings`** (tabs: welcome/general/criteria/display/emails), callback `BGR_Admin::bp_group_review_admin_options_page`.
- Wrapper adds `wbcom-plugins-page`, `wbcom-support-page`, `wbcom-license-page`.
- All pages cap `manage_options`.
- Migration target: NEW card wrapper (`includes/admin/views/shell.php`) is **not** present — this plugin is a candidate for the wrapper migration (Task #2).

## Settings options (all arrays; deleted on uninstall)
`bgr_admin_general_settings`, `bgr_admin_criteria_settings`, `bgr_admin_display_settings`, `bp_group_review_email_settings`.

## Big-site readiness flags
- `bp_group_review_filter_ratings` runs `get_posts(posts_per_page => -1)` over `review` CPT (`bgr-ajax.php`) — unbounded query, no pagination. Will not scale past a few hundred reviews. Revisit before any large-site release.

## Known issues at onboarding (see wppqa baseline)
- 8 banned `alert()`/`confirm()` calls in admin JS (`bgr-group-criteria-admin.js`, `bgr-admin.js`).
- One < 40px button tap target (`bgr-reviews.css:383`).
- 3 nonce-no-cap wppqa flags = **false positives** (public nopriv endpoints / echo-only wrapper).

## Refresh
Re-run `/wp-plugin-onboard --refresh` after structural changes. Artifacts live in `audit/`.
