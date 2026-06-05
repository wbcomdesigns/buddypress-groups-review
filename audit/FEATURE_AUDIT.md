# Feature Audit — BuddyPress Group Reviews

- Slug: `buddypress-groups-review` · Main file: `review-bp-grps.php`
- Version (header/constant): **3.8.1** · Dev branch: **3.9.0** (version not yet bumped)
- Text domain: `bp-group-reviews` · Requires: BuddyPress + Groups component
- Source of truth: `audit/manifest.json`

## 1. Frontend (BuddyPress group tab)
- Group "Reviews" tab injected via `includes/bgr-grp-extn.php` (registered on `bp_init`).
  Tab name/slug/count are filterable: `bp_group_review_add_review_tab_name`, `bp_group_review_tab_name`, `bp_group_review_tab_slug`, `bp_group_review_nav_has_count`, `buddypress_groups_review_nav_count`.
- Templates: `includes/templates/bgr-reviews-tab-template.php`, `bgr-single-review-template.php`, `bgr-group-criteria-settings.php`.
- Shortcodes: `[add_group_review_form]`, `[bgr_display_top_groups]` (leaderboard).
- Two widgets: `includes/widgets/bgr-review.php`, `includes/widgets/group-rating.php` (both extend `WP_Widget`).

## 2. AJAX handlers (17 logical; 22 raw add_action incl. 5 nopriv mirrors)
Member-facing (nonce `ajax-nonce`, several with nopriv mirror): `bp_group_submit_review` (gated by `is_user_logged_in` + group membership), `bp_group_review_accept_review`, `bp_group_review_deny_review`, `bp_group_remove_review`, `bp_group_review_filter_ratings` (public render).
Admin (nonce `admin-ajax-nonce` + `current_user_can('manage_options')`): `bp_group_review_save_admin_general_settings`, `..._criteria_settings`, `..._display_settings`, `bp_group_review_admin_approve_review`, `bp_group_review_admin_deny_review`.
Group criteria (`includes/bgr-group-criteria-ajax.php`): `bgr_save_group_criteria_settings`, `bgr_add_custom_criteria`, `bgr_archive_custom_criteria`, `bgr_delete_custom_criteria`, `bgr_toggle_global_criteria`, `bgr_get_group_criteria`.
Wrapper: `wbcom_addons_cards` (echo only).

## 3. REST endpoints (`buddypress/v1`)
- `GET  /groups/{group_id}/reviews` — list (public)
- `POST /groups/{group_id}/reviews` — create (`is_user_logged_in`)
- `GET  /groups/{group_id}/reviews/{review_id}` — read single (public)
- `PUT  /groups/{group_id}/reviews/{review_id}` — update (`update_review_permissions_check`)
- `DELETE /groups/{group_id}/reviews/{review_id}` — delete (`delete_review_permissions_check`)
- `GET  /groups/{id}/criteria` — group criteria (closure access check)
- `PUT  /groups/{id}/criteria` — update criteria (`bgr_user_can_manage_group_criteria`)

## 4. Admin pages & settings — wrapper = **OLD**
Menu: top-level `wbcomplugins` ("WB Plugins", `add_menu_page`, cap `manage_options`, hook `admin_menu` via `BGR_Admin::bp_group_review_add_submenu_page_admin_settings`, `admin/bgr-admin.php:58`).
Submenus: `wbcomplugins` (General), **`group-review-settings`** (Group Reviews — primary settings page, tabs: welcome/general/criteria/display/emails), plus OLD-wrapper pages `wbcom-plugins-page`, `wbcom-support-page`, `wbcom-license-page` (`admin/wbcom/wbcom-admin-settings.php:230/238/246`).
Settings options (all `array`, deleted on uninstall): `bgr_admin_general_settings`, `bgr_admin_criteria_settings`, `bgr_admin_display_settings`, `bp_group_review_email_settings` (only one using `register_setting`, sanitized by `bp_group_review_sanitize_email_settings`).

## 5. Content types
- CPT `review` (`public:false`, custom `*_review` caps, mapped via `map_meta_cap`) — `admin/bgr-admin.php:147`.
- Taxonomy `review_category` (hierarchical, `show_ui:false`) — `admin/bgr-admin.php:202`.

## 6. Capabilities
Custom `*_review` caps granted to roles via `bp_group_review_add_capabilities_to_roles` (filter `bgr_add_roles_capability`, default `administrator`). Admin AJAX gated by `manage_options`.

## 7. Integrations
- **BuddyPress** (hard dependency; deactivates if BP/groups missing).
- **GamiPress** — fires `gamipress_bp_group_review` on review approval (`bgr-ajax.php:215`).
- **Notifications / Activity** — `BGR_Notifications`, `includes/bgr-activity.php`.
- **Multisite** — `class-bgr-multi-support.php`.

## 8. Tables / Cron / Blocks
None. No custom DB tables (uses `review` CPT + post meta `linked_group`), no cron, no Gutenberg blocks.

## 9. Known issues (see wppqa baseline)
8 banned `alert()`/`confirm()` calls in admin JS; one < 40px button tap target. Nonce-no-cap flags are pre-triaged false positives.
