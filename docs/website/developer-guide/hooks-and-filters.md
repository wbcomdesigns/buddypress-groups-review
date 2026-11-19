# Hooks and Filters

**Included in Free**

## Actions

| Hook | When it fires |
|------|---------------|
| `bgr_group_after_review_submit` | After a review is submitted, from the form and the REST API. Arguments: author ID, group ID, review ID. |
| `bgr_group_accept_review` | A pending review is approved. |
| `bgr_group_deny_review` | A pending review is denied. |
| `bgr_group_review_after_review_insert` | After the review row and meta are saved. |
| `bgr_display_group_average_ratings` | Renders the average stars. |
| `bgr_display_ratings` | Renders a set of stars. |

## Filters

| Filter | Purpose |
|--------|---------|
| `bgr_admin_capability` | Capability needed for the settings screen. Default `manage_options`. |
| `bgr_admin_tabs` | Add or remove settings tabs. |
| `bgr_review_stats_query_limit` | Most reviews aggregated per group. Default 10000. |
| `bgr_filter_ratings_max_scan` | Reviews scanned by the ratings filter. Default 500. |
| `bgr_schema_reviews_limit` | Reviews embedded in the JSON-LD markup. |
| `bp_group_review_tab_name` | Group tab name. |
| `bp_group_review_tab_slug` | Group tab address. |
| `bgr_add_roles_capability` | Roles that receive the review capabilities. |

## Constants

`BGR_REMOVE_DATA_ON_UNINSTALL` - set to true in wp-config.php to delete group reviews and settings when the plugin is deleted. Member reviews are never deleted.

## More

- [REST API](rest-api.md)
- [Member Reviews compatibility](member-reviews-compatibility.md)
