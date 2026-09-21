# Troubleshooting

## The review tabs do not appear on a group

- Confirm BuddyPress is active with the **Groups** component enabled. The plugin only registers its group tabs when Groups is active.
- Check the group is not in **Exclude groups** (General settings). Excluded groups show no tabs, form, or ratings.
- The **Add Review** tab is only shown to logged-in members of the group who are not its admin. A group admin never sees Add Review for their own group.

## A submitted review does not show up

- With auto-approve off (the default), a new review is a **draft** and stays hidden until a group admin approves it on the **Manage Reviews** tab, or a site admin publishes it from the Reviews list in wp-admin.
- Only **published** reviews appear on the Reviews tab, directory, and header, and only they count toward the average.

## The Manage Reviews tab is empty

It lists **pending (draft)** reviews only. If every review is already approved or denied, the list shows the "no reviews found" message. Increase visibility by reviewing the Reviews list in wp-admin, which shows all statuses.

## The average rating looks wrong or is zero

- Averages use published reviews only; drafts and denied reviews are excluded.
- Each group average is the mean of its reviews' per-criterion averages, rounded to two decimals. A group with no published reviews shows no rating.

## Star colour did not change

Set **Rating star colour** on the Display settings tab. Clear any page or object cache after saving, since the colour is applied through generated CSS.

## The Review Criteria tab is missing on groups

The per-group **Review Criteria** tab only appears when the site admin turns on **Enable Group-Level Criteria** on the General settings tab. Until then, all groups use the global criteria.

## `[bgr_group_leaderboard]` outputs nothing

That shortcode name is not registered. Use `[bgr_display_top_groups]` for the leaderboard. See [Shortcodes](../shortcodes/shortcodes.md).

## The leaderboard shows fewer groups than expected

`[bgr_display_top_groups]` only includes **public** groups that have at least one **published** review. Groups with no published reviews, and non-public groups, are excluded.

## REST review creation returns an error

Creating a review requires an authenticated user who is a **member** of the target group. Non-members and logged-out requests are rejected on the POST endpoint. See [REST API](../developer-guide/rest-api.md).

## Still stuck?

Contact Wbcom Designs support at https://wbcomdesigns.com/contact/.
