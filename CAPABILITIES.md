# CAPABILITIES — BuddyPress Group Reviews

Buyer-level roll-up, verified against code at 3.9.1. Read this before planning a
feature so we do not rebuild something that already exists. Structure lives in
`audit/manifest.json`; this file says what that structure adds up to.

- **Slug:** `buddypress-groups-review` · **Text domain:** `bp-group-reviews`
- **Requires:** WP 6.5+, PHP 8.0+, BuddyPress with the Groups component (self-deactivates otherwise)
- **Status key:** `YES` / `PARTIAL` / `NO` / `PLANNED`

## Reviewing a group

| Can it... | Status | Delivered by |
|---|---|---|
| Let members review a group with a subject, text and star ratings? | YES | Group tab form + `[add_group_review_form]` (`includes/bgr-shortcodes.php:32`), AJAX `bp_group_submit_review` (`includes/bgr-ajax.php:462`) |
| Rate against several criteria, set site-wide? | YES | `bgr_admin_criteria_settings`, Criteria tab (`includes/admin/views/settings-criteria.php`) |
| Let each group admin define its own criteria? | YES | `BGR_Group_Criteria` (`includes/class-bgr-group-criteria.php:23`), `review-criteria` screen (`includes/bgr-grp-extn.php:283`), needs `enable_group_criteria` = yes |
| Let group admins delete a custom criterion or switch a global one off per group? | PARTIAL | Handlers exist (`includes/bgr-group-criteria-ajax.php:190`, `:229`) but no UI calls them; archive works |
| Let a member edit their review from the front end? | NO | Owner decision: not offered. Update exists only as REST `PUT/PATCH` (`includes/bgr-bp-rest-integration.php:246`) |
| Show per-criterion averages in the UI? | NO | Owner decision: REST/AJAX data only (`includes/bgr-group-criteria-ajax.php:257`, `:376`); overall average and count are shown |
| Keep reviews of private/hidden groups private? | YES | `bgr_can_view_group_reviews()` (`includes/bgr-functions.php:198`) on REST, header, directory, schema, widgets, ratings filter |

## Moderation and notifications

| Can it... | Status | Delivered by |
|---|---|---|
| Auto-approve, or hold reviews for group-admin approval? | YES | `auto_approve_reviews` setting; AJAX accept/deny/remove (`includes/bgr-ajax.php:634`, `:679`, `:724`) |
| Approve/deny from wp-admin, incl. bulk? | YES | Row + bulk actions (`includes/bgr-filters.php:39-41`, `:422`); site-admin AJAX (`includes/bgr-ajax.php:248`, `:284`) |
| Run every approval path through one rule? | YES | `bgr_handle_review_decision()` on `transition_post_status` (`includes/bgr-functions.php:226`) |
| Notify and email group admins on a new review, and the reviewer on accept/deny? | YES | `bgr_handle_review_submitted()` (`includes/bgr-functions.php:310`), `BGR_Notifications`, Emails tab (`bp_group_review_email_settings`) |
| Post approved reviews to BP activity? | YES | `BGR_Activity`, type `bgr_group_posts_activity_action` (`includes/bgr-activity.php:86`) |
| Award GamiPress points? | YES | `gamipress_bp_group_review` (`includes/bgr-functions.php:242`, `:322`; trigger `includes/bgr-filters.php:72`) |

## Display and SEO

| Can it... | Status | Delivered by |
|---|---|---|
| Show ratings on the group header and directory? | YES | `includes/bgr-filters.php:49`, `:60` |
| Show a top-rated / most-reviewed leaderboard? | YES | `[bgr_display_top_groups]` (`includes/bgr-shortcodes.php:308`) |
| Offer sidebar widgets? | YES | `Bgr_Review_Widget`, `Bgr_Single_Group_Rating_Widget` (`includes/widgets/`) |
| Output review structured data? | YES | JSON-LD `aggregateRating` (`includes/bgr-schema.php:38`) |
| Fit BuddyX / Reign / dark mode? | YES | `BGR_Theme_Integration` (`includes/class-bgr-theme-integration.php:29`); dark mode explicit-selectors only |

## Platform

| Can it... | Status | Delivered by |
|---|---|---|
| Expose reviews over REST? | YES | `buddypress/v1` group reviews (list/create/read/update/delete) and `bgr/v1` group criteria; `reviews` field on BP `/groups` (`includes/bgr-bp-rest-integration.php:39`) |
| Export reviews to CSV? | YES | `manage_options` + nonce, 200-row batches (`includes/bgr-filters.php:549`) |
| Work on multisite? | YES | `Buddypress_Group_Review_Multi_Support`; per-blog uninstall (`uninstall.php:37`) |
| Be translated? | YES | de_DE, es_ES, fr_FR, it_IT, pt_BR + JS JSON (`includes/bgr-scripts.php:41` fixes the `/min/` path) |
| Handle large sites? | PARTIAL | Cached stats (`includes/bgr-functions.php:36`), bounded ratings filter (default 500, `includes/bgr-ajax.php:97`); ratings filter has no real pagination |
| Remove its data on uninstall without touching Member Reviews? | YES | Opt-in constant `BGR_REMOVE_DATA_ON_UNINSTALL`; deletes only reviews with `linked_group`, options, `bgr_review_*` transients, criteria groupmeta, 2 site options, 10 caps from all roles (`uninstall.php`) |

## Three entry points

1. **Frontend** — group tabs, 2 shortcodes, 2 widgets.
2. **Backend** — `WB Plugins > Group Reviews` card panel (General / Criteria / Display / Emails tabs, `includes/admin/class-bgr-admin-panel.php`), review list table, CSV export.
3. **API** — REST (7 endpoints, 2 namespaces); 17 `admin-ajax` registrations: 16 authenticated plus one `nopriv` (`bp_group_review_filter_ratings`, public read-only).

## Data model

- CPT `review` (private; shared with Member Reviews — group reviews carry `linked_group` meta), taxonomy `review_category`.
- Options: `bgr_admin_general_settings`, `bgr_admin_criteria_settings`, `bgr_admin_display_settings`, `bp_group_review_email_settings`; groupmeta `bgr_group_criteria_settings`.
- Transients: `bgr_review_count_*`, `bgr_review_rating_*`, `bgr_review_lock_*`, `bgr_widget_group_ranking`. No custom tables, no cron, no blocks.

## Extension points

39 fired hooks (`hooks_fired` in the manifest). Common: `bgr_add_roles_capability`,
`bgr_admin_capability`, `bgr_filter_ratings_max_scan`, `bgr_review_stats_query_limit`,
`bgr_schema_reviews_limit`, `bgr_group_after_review_submit`,
`bgr_group_review_after_review_insert( $review_id, $group_id, $author_id )`,
`bp_group_review_manage_tab_slug`.
