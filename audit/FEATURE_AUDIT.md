# Feature Audit — BuddyPress Group Reviews

- Slug: `buddypress-groups-review` · Main file: `review-bp-grps.php`
- Version: **3.9.1** (release branch `3.9.1`; header, `BGR_PLUGIN_VERSION` and `package.json` are bumped with the release commit)
- Text domain: `bp-group-reviews` · Requires: WP 6.5+, PHP 8.0+, BuddyPress + Groups component
- Source of truth: `audit/manifest.json` (code-verified at the 3.9.1 refresh). Buyer-level view: `CAPABILITIES.md`.

## 1. Frontend (BuddyPress group tab)
- Group Manage-reviews tab: `Group_Reviews_Management_Extn` (`includes/bgr-grp-extn.php:31`), top-level group nav `manage-{tab_slug}` at position 200, skipped for groups in `exclude_groups`. Review Criteria screen: `Group_Reviews_Criteria_Extn` (`:283`, slug `review-criteria`, position 201), only when `enable_group_criteria` is `yes`.
- Tab name/slug/count are filterable: `bp_group_review_add_review_tab_name`, `bp_group_review_tab_name`, `bp_group_review_tab_slug`, `bp_group_review_manage_tab_slug` (`includes/bgr-globals.php:257`), `bp_group_review_nav_has_count`, `buddypress_groups_review_nav_count`.
- Templates: `includes/templates/bgr-reviews-tab-template.php`, `bgr-single-review-template.php`, `bgr-group-criteria-settings.php`.
- Shortcodes: `[add_group_review_form]`, `[bgr_display_top_groups]` (leaderboard).
- Two widgets: `includes/widgets/bgr-review.php` (`Bgr_Review_Widget`, ranking cached in transient `bgr_widget_group_ranking`, 1 day), `includes/widgets/group-rating.php` (`Bgr_Single_Group_Rating_Widget`).
- Visibility: reviews and ratings of private/hidden groups are shown only to members and `bp_moderate` users (`bgr_can_view_group_reviews()`, `includes/bgr-functions.php:198`). Applied to REST, group header, directory, JSON-LD schema, both widgets and the ratings filter.
- Review editing is not offered on the front end (owner decision); update exists only as a REST route. Per-criterion averages are not rendered in the UI (REST/AJAX payload only).

## 2. AJAX handlers (17 `add_action` calls: 16 authenticated `wp_ajax_*` + exactly 1 `nopriv`)
Member-facing (nonce `ajax-nonce`, authenticated only; the `nopriv` registrations on submit/accept/deny/remove were removed in 3.9.0):
`bp_group_submit_review` (`is_user_logged_in` + group member, `includes/bgr-ajax.php:468`), `bp_group_review_accept_review`, `bp_group_review_deny_review`, `bp_group_remove_review` (the last three: `groups_is_user_admin() || manage_options`, `:664/:709/:754`).
Public: `bp_group_review_filter_ratings` — the ONLY `wp_ajax_nopriv_` action (`includes/bgr-ajax.php:54`); read-only render, skips private/hidden groups the viewer cannot see, scan bounded by `bgr_filter_ratings_max_scan` (default 500).
Admin (nonce `admin-ajax-nonce` + `manage_options`, all in `includes/bgr-ajax.php`): `bp_group_review_save_admin_general_settings`, `..._criteria_settings`, `..._display_settings`, `bp_group_review_admin_approve_review`, `bp_group_review_admin_deny_review`.
Group criteria (`includes/bgr-group-criteria-ajax.php`, nonce `bgr_group_criteria_nonce`, cap `bgr_user_can_manage_group_criteria()`): `bgr_save_group_criteria_settings`, `bgr_add_custom_criteria`, `bgr_archive_custom_criteria`, `bgr_delete_custom_criteria`, `bgr_toggle_global_criteria`, `bgr_get_group_criteria`.
No `wbcom_addons_cards` action exists any more (legacy wrapper removed in 3.9.0).

## 3. REST endpoints (4 `register_rest_route` calls = 7 logical endpoints)
Namespace `buddypress/v1` (`includes/bgr-bp-rest-integration.php`):
- `GET  /groups/{group_id}/reviews` — list (`read_reviews_permissions_check`: public group = anyone; private/hidden = members + moderators; unknown group = 404)
- `POST /groups/{group_id}/reviews` — create (`is_user_logged_in`); fires `bgr_group_after_review_submit`, so tagging, notifications, email and `bgr_group_review_after_review_insert` match the group form
- `GET  /groups/{group_id}/reviews/{review_id}` — read single (`read_reviews_permissions_check`)
- `PUT/PATCH /groups/{group_id}/reviews/{review_id}` — update (`update_review_permissions_check`)
- `DELETE /groups/{group_id}/reviews/{review_id}` — delete (`delete_review_permissions_check`)
Namespace `bgr/v1` (`includes/bgr-group-criteria-ajax.php`):
- `GET  /groups/{id}/criteria` — criteria + per-criterion averages (closure: public group = logged in; private/hidden = member or `manage_options`)
- `PUT/PATCH /groups/{id}/criteria` — update criteria (`bgr_user_can_manage_group_criteria`)
Also: a `reviews` field is added to BuddyPress's own `/groups` response (`bp_rest_groups_prepare_value`, gated by `bgr_can_view_group_reviews()`).

## 4. Admin pages & settings — wrapper = **NEW card panel**
Controller `includes/admin/class-bgr-admin-panel.php` (`BGR_Admin_Panel`, since 3.9.0); views in `includes/admin/views/` (`shell`, `hub`, `welcome`, `settings-general|criteria|display|emails`, `discover`). The old `admin/wbcom/` wrapper no longer exists; `admin/bgr-admin.php` registers no menu.
Menu: top-level `wbcomplugins` ("WB Plugins", `:129`, only when no other Wbcom plugin created it) and submenu **`group-review-settings`** (`:140`, tabs welcome/general/criteria/display/emails/discover, filter `bgr_admin_tabs`). Capability `manage_options`, filter `bgr_admin_capability`.
Settings options (all `array`): `bgr_admin_general_settings`, `bgr_admin_criteria_settings`, `bgr_admin_display_settings` (written by AJAX), `bp_group_review_email_settings` (`register_setting` once, in `BGR_Admin_Panel::register_settings`, `:170`; sanitized by `bp_group_review_sanitize_email_settings`).
Review list table: row actions, bulk approve/deny and a nonce + `manage_options` CSV export (`includes/bgr-filters.php:39-46`, `:422`, `:549`).

## 5. Content types
- CPT `review` (`public:false`, custom `*_review` caps, `map_meta_cap`) — `admin/bgr-admin.php:103`. Shared with BuddyPress Member Reviews; group reviews are the posts carrying `linked_group` meta.
- Taxonomy `review_category` (hierarchical, `show_ui:false`) — `admin/bgr-admin.php:133`.

## 6. Review lifecycle (single owners, 3.9.1)
- Submit (AJAX form and REST create) → `do_action( 'bgr_group_after_review_submit', $author_id, $group_id, $review_id )` → `bgr_handle_review_submitted()` (`includes/bgr-functions.php:310`): category term, GamiPress award when published, `bgr_group_review_after_review_insert( $review_id, $group_id, $author_id )`, per-admin `bgr_group_add_review`. `BGR_Activity` also listens (prio 15).
- Moderate (Manage tab, wp-admin row/bulk, editor, REST) → status change → `bgr_handle_review_decision()` on `transition_post_status` (`:226`): pending→publish fires `gamipress_bp_group_review` + `bgr_group_accept_review`; pending→trash fires `bgr_group_deny_review`. Cache invalidation hangs on those actions.
- Caches: transients `bgr_review_count_{id}`, `bgr_review_rating_{id}` (1 h), `bgr_review_lock_{user}_{group}`, `bgr_widget_group_ranking` (1 day). `bgr_get_group_review_stats()` returns average, index and a `criteria` totals array.

## 7. Capabilities
Custom `*_review` caps granted via `bp_group_review_add_capabilities_to_roles` (filter `bgr_add_roles_capability`, default `administrator`); `map_meta_cap` lets a review author edit/delete their own review. Admin AJAX gated by `manage_options`; group-scoped handlers by `groups_is_user_admin() || manage_options`.

## 8. Integrations
- **BuddyPress** (hard dependency; self-deactivates if BP/Groups missing).
- **GamiPress** — `gamipress_bp_group_review` trigger on approval / auto-published submit.
- **Activity** — registered type `groups / bgr_group_posts_activity_action` (`includes/bgr-activity.php:86`); star rating injected into activity content.
- **Notifications / email** — `BGR_Notifications` (BP component) + configurable email settings.
- **SEO schema** — JSON-LD `aggregateRating` on `wp_head` (`includes/bgr-schema.php:38`).
- **Theme integration** — `BGR_Theme_Integration` (BuddyX / Reign token map); **Multisite** — `Buddypress_Group_Review_Multi_Support`.

## 9. Uninstall (`uninstall.php`, gated on `BGR_REMOVE_DATA_ON_UNINSTALL`; multisite-aware)
Removes: review posts that carry `linked_group` only (Member Reviews posts survive); options `bgr_admin_general_settings`, `bgr_admin_criteria_settings`, `bgr_admin_display_settings`, `bp_group_review_email_settings`, `bgr_display_labels_migrated`; `bgr_review_*` transients; groupmeta `bgr_group_criteria_settings`; site options `bp-group-reviews_activation_date`, `bp-group-reviews_no_bug`; the 10 review caps from every role. Not removed: transient `bgr_widget_group_ranking` (expires in a day).

## 10. Tables / Cron / Blocks
None. No custom DB tables (uses `review` CPT + post meta `linked_group`), no cron, no Gutenberg blocks.

## 11. Known issues
- `bgr_delete_custom_criteria` and `bgr_toggle_global_criteria` AJAX handlers have no UI caller (see `AUDIT-VERDICT.md`).
- Ratings filter is bounded but not paginated.
- One < 40px button tap target flagged at onboarding; re-measure before acting. The 8 banned `alert()`/`confirm()` calls were resolved in 3.9.0. See `audit/wppqa-baseline-2026-06-05/SUMMARY.md` (historical).
