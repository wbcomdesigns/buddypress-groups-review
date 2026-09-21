# How Reviews Are Stored

Reviews are stored as a WordPress custom post type. There is no custom database table and no comment-based storage.

## The `review` custom post type

Registered on `init` with:

- `public` = false, but `show_ui` and `show_in_menu` = true, so reviews are managed from the WordPress admin under a **Reviews** menu (dashicon `testimonial`).
- Custom capabilities (`edit_review`, `read_review`, `delete_review`, `edit_reviews`, `edit_others_reviews`, `publish_reviews`, `read_private_reviews`), with `create_posts` mapped to `do_not_allow` so reviews are created only through the plugin's own flow. `map_meta_cap` is on.
- `supports`: title, editor, author, thumbnail.

A `review_category` taxonomy is registered for the post type; group reviews use the `group` category.

## Review status = moderation state

| Post status | Meaning |
|---|---|
| `draft` | Pending. Awaiting group admin (or site admin) approval. Not public, not counted in averages. |
| `publish` | Approved. Shown on the Reviews tab, directory, and header, and counted in the group average. |

Approving a review publishes it; denying leaves it as a draft (or trashes it).

## Post meta

| Meta key | Stored on | Value |
|---|---|---|
| `linked_group` | the `review` post | The BuddyPress group ID the review belongs to. |
| `review_star_rating` | the `review` post | A PHP-serialized array of per-criterion star values (1-5). |

Because `review_star_rating` is a serialized array, per-review and per-group averages are computed in PHP, not directly in SQL.

## Group-level criteria

Per-group criteria are stored in BuddyPress group meta under the key `bgr_group_criteria_settings`, an array with `enabled_global_criteria`, `custom_criteria`, and `archived_criteria`.

## Plugin options

| Option | Holds |
|---|---|
| `bgr_admin_general_settings` | Moderation, per-page, notifications, activity, excluded groups, multiple reviews, group-criteria toggle. |
| `bgr_admin_criteria_settings` | `add_review_rating_fields` and `active_rating_fields` (the global criteria). |
| `bgr_admin_display_settings` | `review_label`, `manage_review_label`, `bgr_rating_color`. |
| `bp_group_review_email_settings` | Email toggles, subjects, and messages. |
