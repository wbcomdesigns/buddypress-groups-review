# Code Flows — BuddyPress Group Reviews

Source of truth: `audit/manifest.json`. Pipeline notation: `trigger → router → PHP → AJAX/REST → JS → output`.

## Bootstrap
`review-bp-grps.php` → `bp_loaded` ⇒ `bp_group_review_plugin_execute()` checks BuddyPress + groups component ⇒ `run_bp_group_reviews_plugin()` includes all `includes/*` + `admin/*` files and boots `BGR_Group_Criteria::get_instance()`. `bp_init` adds the group tab (`bgr-grp-extn.php`) and notifications. `admin_init` enforces BP dependency (deactivates if missing). `activated_plugin` redirects to `admin.php?page=group-review-settings`.

## Flow A — Member submits a review
Group Manage tab form (`[add_group_review_form]` / tab template) → JS `assets/js/*` POST `action=bp_group_submit_review`, nonce `ajax-nonce` → `BGR_AJAX::bp_group_submit_review` (`includes/bgr-ajax.php:462`): verify nonce → `is_user_logged_in()` → parse form → `groups_get_group()` exists → not group admin → `groups_is_user_member()` → submit lock transient → insert `review` CPT (status per auto-approve setting) → `do_action( 'bgr_group_after_review_submit', $author_id, $group_id, $review_id )` (`:619`) → `bgr_handle_review_submitted()` (`includes/bgr-functions.php:310`: category term, GamiPress when published, `bgr_group_review_after_review_insert( $review_id, $group_id, $author_id )`, `bgr_group_add_review` per group admin) + `BGR_Activity` listener (prio 15) → JSON response. REST create (`POST /buddypress/v1/groups/{id}/reviews`) fires the same `bgr_group_after_review_submit` (`includes/bgr-bp-rest-integration.php:453`).

## Flow B — Approve / deny (every path)
Entry points: group Manage tab AJAX (`bp_group_review_accept_review` / `_deny_review` / `bp_group_remove_review`, nonce `ajax-nonce`, `groups_is_user_admin() || manage_options`), site-admin AJAX (`bp_group_review_admin_approve_review` / `_deny_review`, nonce `admin-ajax-nonce`, `manage_options`), wp-admin row + bulk actions (`includes/bgr-filters.php:422`), the post editor, REST update/delete. Each only changes post status; `bgr_handle_review_decision()` (`includes/bgr-functions.php:226`, `transition_post_status`) turns pending→publish into `gamipress_bp_group_review` + `bgr_group_accept_review` and pending→trash into `bgr_group_deny_review`. Listeners: `BGR_Notifications` (prio 99) and cache invalidation (`includes/bgr-filters.php:81-82`).

## Flow C — Admin saves settings (card panel)
`admin.php?page=group-review-settings&tab={welcome|general|criteria|display|emails|discover}` → `BGR_Admin_Panel::render_page` (`includes/admin/class-bgr-admin-panel.php`) renders `includes/admin/views/*` → JS POSTs `bp_group_review_save_admin_{general|criteria|display}_settings` (nonce `admin-ajax-nonce`) → `BGR_AJAX` handlers verify nonce + `manage_options` → `update_option('bgr_admin_*_settings')`. Emails tab uses `options.php` + `register_setting('bp_group_review_email_settings')` (registered once, `BGR_Admin_Panel::register_settings`).

## Flow D — Per-group criteria (JS-driven)
Group criteria screen (`review-criteria`) → `assets/js/bgr-group-criteria-admin.js` POST `bgr_*_criteria` AJAX (nonce `bgr_group_criteria_nonce`) or REST `PUT /bgr/v1/groups/{id}/criteria` → `bgr_ajax_*` / `bgr_rest_update_group_criteria` (access via `bgr_user_can_manage_group_criteria`). Uses the plugin's `bgr-toast` / `bgr-confirm` modules (no native `alert()`/`confirm()` since 3.9.0). Delete-criterion and toggle-global handlers have no UI caller.

## Flow E — REST read
`GET /wp-json/buddypress/v1/groups/{group_id}/reviews` → `BGR_BP_REST_Integration::read_reviews_permissions_check` (public group = anyone; private/hidden = members + `bp_moderate`) → `get_group_reviews` → review collection. Same check guards the single-review GET.

## Key files
| Concern | File |
|---|---|
| Bootstrap | `review-bp-grps.php` |
| CPT / taxonomy / caps (no menu) | `admin/bgr-admin.php` |
| Admin menu + settings panel | `includes/admin/class-bgr-admin-panel.php`, `includes/admin/views/` |
| Member/admin AJAX | `includes/bgr-ajax.php` |
| Lifecycle handlers + visibility rule + stats | `includes/bgr-functions.php` |
| Criteria AJAX + REST | `includes/bgr-group-criteria-ajax.php`, `class-bgr-group-criteria.php` |
| Review REST | `includes/bgr-bp-rest-integration.php` |
| Group tab | `includes/bgr-grp-extn.php` |
| Notifications / activity / schema | `includes/bgr-notifications.php`, `bgr-activity.php`, `bgr-schema.php` |
| Rating display | `includes/bgr-rating-display.php`, `bgr-dynamic-css.php` |

## Globals / state
- `global $bgr` — settings cache assembled from the 4 option arrays; read throughout `includes/`.
- `buddypress()->bgr_bp_review` — `BGR_Notifications` instance.

## Required settings
`bgr_admin_general_settings` (auto-approve etc.), `bgr_admin_criteria_settings`, `bgr_admin_display_settings`, `bp_group_review_email_settings`.
