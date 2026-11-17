# Member Reviews Compatibility

BuddyPress Group Reviews and BuddyPress Member Reviews (plus Member Reviews Pro) can run on the same site. Both store reviews in the same `review` post type. This guide shows how each plugin tells its reviews apart.

## What They Share

- The `review` custom post type and the `review_category` taxonomy.
- The **Reviews** list in wp-admin. It shows group reviews and member reviews together.
- The review capabilities (`edit_review`, `delete_reviews` and so on).

## How a Review Is Tagged

| Plugin | Review type | Post meta that marks it |
|--------|-------------|-------------------------|
| Group Reviews | Review of a group | `linked_group` (group ID) |
| Member Reviews | Review of a member | `linked_bp_member` (member user ID) |

A review should carry only one of the two keys. Treat the key as the source of truth, not the post title or category.

## What Each Plugin Counts

Each plugin counts and lists only its own reviews.

| Plugin | Counts and lists | Ignores |
|--------|------------------|---------|
| Group Reviews | Reviews with `linked_group` | Reviews with `linked_bp_member` |
| Member Reviews | Reviews with `linked_bp_member` | Reviews with `linked_group` |

In Group Reviews this applies to:

- Group ratings, review counts and the Schema.org markup (`bgr_get_group_review_stats()`).
- The Top Rated Groups widget and the `[bgr_display_top_groups]` shortcode.
- The review CSV export. It exports group reviews only.
- Activity posts. A member review never creates a group review activity item.
- Uninstall. With `BGR_REMOVE_DATA_ON_UNINSTALL` set, only reviews with `linked_group` are deleted. Member Reviews data is kept.

Member Reviews Pro keeps its dashboard, verified and flagged filters to member reviews only. Group reviews no longer appear in them.

## Check a Review in Code

Use the post meta key that matches the plugin you are writing for.

```php
// Is this a group review?
$is_group_review = 'review' === get_post_type( $review_id )
    && (int) get_post_meta( $review_id, 'linked_group', true ) > 0;
```

Member Reviews 3.8.3 and later provide a shared helper for the member side:

```php
if ( function_exists( 'bupr_is_member_review' ) && bupr_is_member_review( $review_id ) ) {
    // A member review. Accepts a post ID or a WP_Post.
}
```

Always check `function_exists()`, because Member Reviews may not be active.

## Querying Reviews

Never query "all reviews" and assume they are group reviews. Scope the query by meta key and bound it:

```php
$reviews = get_posts( array(
    'post_type'      => 'review',
    'post_status'    => 'publish',
    'posts_per_page' => 20,
    'meta_key'       => 'linked_group',
    'meta_value'     => $group_id,
) );
```

## Styling

Both plugins print stars. Group Reviews colors only its own `bgr-` classes, so your star color setting does not recolor Member Reviews stars (fixed in 3.9.2).

## Troubleshooting

**A group count looks too high.** Check that custom code scopes queries to `linked_group`.

**Reviews vanished after removing Group Reviews.** This happens only on versions before 3.9.1, which deleted every `review` post on uninstall. Update before you remove the plugin.

## Related

- [Developer Guide](DEVELOPER-GUIDE.md)
- [Documentation Index](INDEX.md)
