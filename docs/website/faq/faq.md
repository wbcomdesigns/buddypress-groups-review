# Frequently Asked Questions

## How does a member submit a review?

Open a single group and use the **Add Review** tab. The tab is shown to logged-in members of the group who are not that group's admin. See [Adding a Review](../usage/member-adding-a-review.md).

## Why is the Add Review option missing in a group I created?

A group admin cannot review their own group. The Add Review tab is only shown to members who are not admins of that group.

## Can a member review the same group more than once?

By default, no - one review per group. Turn on **Multiple reviews** on the General settings tab to allow more than one.

## How do I stop a group from being reviewed?

Add it to **Exclude groups** on the General settings tab. Excluded groups show no review tabs, form, or ratings.

## How do I hide a rating criterion from the form?

Disable it on the **Criteria** settings tab. Disabling keeps the criterion but removes it from the form; deleting removes it entirely.

## Can I change the "Reviews" and "Review" tab labels?

Yes, on the **Display** settings tab. The Review label drives the Add Review tab and buttons; the Manage review label drives the Reviews listing tab. Changing a label also changes its tab slug.

## Can a review be submitted without a star rating?

Yes. If no rating criteria are active, the form shows a single "Your Rating" field and the review can still be submitted as text.

## Where do the group ratings show up?

Automatically on the groups directory and in the single-group header, plus the group's Reviews tab. You can also place them with widgets or the leaderboard shortcode.

## How can I show the review form or a leaderboard on another page?

Use `[add_group_review_form]` for the form and `[bgr_display_top_groups]` for a leaderboard. See [Shortcodes](../shortcodes/shortcodes.md).

## Does it work with BuddyBoss Platform?

Yes. Notification links resolve to the correct review page on BuddyBoss.

## Does it work with GamiPress?

Yes. A `gamipress_bp_group_review` action fires when a review is added or approved, so you can award points or achievements.

## Is there a REST API?

Yes, under `buddypress/v1`. See [REST API](../developer-guide/rest-api.md).

## Where do I get support?

Contact Wbcom Designs at https://wbcomdesigns.com/contact/.
