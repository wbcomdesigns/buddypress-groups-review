# Hooks and Filters

These are the hooks the plugin fires or exposes for extension. Only hooks present in the code are listed.

## Action hooks

| Hook | Fires when | Arguments |
|---|---|---|
| `bgr_group_add_review` | A review is added, before notifying the group admin. | `$group_id`, `$group_admin` |
| `bgr_group_after_review_submit` | After a review is submitted. | `$post_author_id`, `$group_id`, `$review_id` |
| `bgr_group_review_after_review_insert` | After the review row and meta are saved. Also fires for reviews created through the REST API. | `$review_id`, `$group_id`, `$reviewer_id` |
| `bgr_group_accept_review` | A review is approved. | `$review_id` |
| `bgr_group_deny_review` | A review is denied. | `$review_id` |
| `bgr_display_ratings` | Rendering a single review's ratings. | `$post_id` |
| `bgr_display_group_average_ratings` | Rendering a group's average rating. | `$avg_rating` |
| `bgr_display_widget_average_ratings` | Rendering the average rating in a widget. | `$avg_rating` |
| `bgr_global_criteria_deleted` | A global criterion is deleted. | `$deleted` |
| `bgr_global_criteria_archived` | A global criterion is archived. | `$archived` |
| `gamipress_bp_group_review` | A review is added or approved (GamiPress trigger). | `$user_id` |

## Filter hooks

| Filter | Filters | Arguments |
|---|---|---|
| `bp_group_review_tab_name` | The Reviews (manage) tab label. | `$tab_name` |
| `bp_group_review_add_review_tab_name` | The Add Review tab label. | `$tab_name` |
| `bp_group_review_tab_slug` | The review tab slug. | `$slug` |
| `bp_group_review_nav_has_count` | The review count used on the nav. | `$count` |
| `buddypress_groups_review_nav_count` | The nav count value. | `$count` |
| `bgr_filter_group_header_rating_details` | The group header rating markup. | `$content`, `$avg_rating`, `$review_label`, `$total_review_count`, `$group_id` |
| `bp_group_review_group_create_posts_activity_action` | The activity action string for a review. | `$action` |
| `bp_group_review_add_review_notification_format` | The "new review" notification format. | `$grp_id`, `$review_id`, `$user_id`, `$id`, `$format` |
| `bp_group_review_accept_review_notification_format` | The "accepted" notification format. | `$grp_id`, `$review_id`, `$id`, `$format` |
| `bp_group_review_deny_review_notification_format` | The "denied" notification format. | `$grp_id`, `$review_id`, `$id`, `$format` |
| `bgr_add_roles_capability` | Capabilities granted to roles. | (capability list) |

Widget-related filters (`bgr_widget_title`, `bgr_post_num`, `bgr_thumbnail`, `bgr_listing_cat`, `rating_default`) are also exposed for the rating/review widgets.

## Helper functions

- `bgr_can_view_group_reviews( $group )` - whether the current visitor may see a group's reviews and ratings. Private and hidden groups are limited to members and `bp_moderate`. Use it in integrations so your output follows the same rule (since 3.9.1).
- `bgr_get_effective_criteria( $group_id )` - the criteria a group actually uses (group-level if enabled, otherwise global).
- `bgr_is_using_custom_criteria( $group_id )` - whether the group uses its own criteria.
- `bgr_get_criteria_mode( $group_id )` - the group's criteria mode.
