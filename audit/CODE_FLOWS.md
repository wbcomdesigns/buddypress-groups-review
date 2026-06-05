# Code Flows — BuddyPress Group Reviews

Source of truth: `audit/manifest.json`. Pipeline notation: `trigger → router → PHP → AJAX/REST → JS → output`.

## Bootstrap
`review-bp-grps.php` → `bp_loaded` ⇒ `bp_group_review_plugin_execute()` checks BuddyPress + groups component ⇒ `run_bp_group_reviews_plugin()` includes all `includes/*` + `admin/*` files and boots `BGR_Group_Criteria::get_instance()`. `bp_init` adds the group tab (`bgr-grp-extn.php`) and notifications. `admin_init` enforces BP dependency (deactivates if missing). `activated_plugin` redirects to `admin.php?page=group-review-settings`.

## Flow A — Member submits a review
Group "Reviews" tab form (`[add_group_review_form]` / tab template) → JS `assets/js/*` POST `action=bp_group_submit_review`, nonce `ajax-nonce` → `BGR_AJAX::bp_group_submit_review` (`bgr-ajax.php:392`): verify nonce → `is_user_logged_in()` → parse form → `groups_get_group()` exists → not group admin → `groups_is_user_member()` → insert `review` CPT (status per auto-approve setting) → fires `bgr_group_add_review` / `bgr_group_after_review_submit` / `bgr_group_review_after_review_insert` → JSON response.

## Flow B — Admin approves / denies
Settings page review list → JS `admin/assets/js/bgr-admin.js` POST `bp_group_review_admin_approve_review` / `..._deny_review`, nonce `admin-ajax-nonce` → handler: verify nonce → `current_user_can('manage_options')` → validate CPT → approve = `wp_update_post(publish)` + `do_action('gamipress_bp_group_review')`; deny = `wp_trash_post()` + `do_action('bgr_group_deny_review')`.

## Flow C — Admin saves settings (OLD wrapper, tabbed)
`admin.php?page=group-review-settings&tab={welcome|general|criteria|display|emails}` → `BGR_Admin::bp_group_review_admin_options_page` renders tabs, includes `admin/review-admin-options-page.php` → JS POSTs `bp_group_review_save_admin_{general|criteria|display}_settings` (nonce `admin-ajax-nonce`) → `BGR_AJAX` handlers verify nonce + `manage_options` → `update_option('bgr_admin_*_settings')`. Email tab uses `register_setting('bp_group_review_email_settings')`.

## Flow D — Per-group criteria (JS-driven)
Group criteria settings template → `assets/js/bgr-group-criteria-admin.js` POST `bgr_*_criteria` AJAX or REST `PUT /buddypress/v1/groups/{id}/criteria` → `bgr_ajax_*` / `bgr_rest_update_group_criteria` (access via `bgr_user_can_manage_group_criteria`). NOTE: this JS uses banned `alert()`/`confirm()`.

## Flow E — Public REST read
`GET /wp-json/buddypress/v1/groups/{group_id}/reviews` → `BGR_BP_REST_Integration::get_group_reviews` (`__return_true`) → returns review collection.

## Key files
| Concern | File |
|---|---|
| Bootstrap | `review-bp-grps.php` |
| Admin menu + CPT/tax + caps | `admin/bgr-admin.php` |
| OLD wrapper | `admin/wbcom/wbcom-admin-settings.php` |
| Settings page render | `admin/review-admin-options-page.php` |
| Member/admin AJAX | `includes/bgr-ajax.php` |
| Criteria AJAX + REST | `includes/bgr-group-criteria-ajax.php`, `class-bgr-group-criteria.php` |
| Review REST | `includes/bgr-bp-rest-integration.php` |
| Group tab | `includes/bgr-grp-extn.php` |
| Notifications / activity | `includes/bgr-notifications.php`, `bgr-activity.php` |
| Rating display | `includes/bgr-rating-display.php`, `bgr-dynamic-css.php` |

## Globals / state
- `global $bgr` — settings cache assembled from the 4 option arrays; read throughout `includes/`.
- `buddypress()->bgr_bp_review` — `BGR_Notifications` instance.

## Required settings
`bgr_admin_general_settings` (auto-approve etc.), `bgr_admin_criteria_settings`, `bgr_admin_display_settings`, `bp_group_review_email_settings`.
