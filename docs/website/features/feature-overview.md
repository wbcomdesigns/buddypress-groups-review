# Feature Overview

## Multi-criteria star ratings

Members rate a group from 1 to 5 stars against each active criterion. The plugin ships with Quality, Relevance, and Engagement, and the site admin can add or remove criteria. Each review's rating is the average of its criteria, and each group's rating is the average across its published reviews.

## Review moderation

Reviews are held as drafts until approved. Group admins approve or deny from a **Manage Reviews** tab inside the group; site admins can also approve, deny, and bulk-manage from the Reviews list in the WordPress admin. An **Auto approve reviews** setting publishes reviews immediately when you prefer no gate.

## Ratings on the directory and group header

A group's average rating shows on the groups directory and in the single-group header, so ratings are visible without opening the Reviews tab.

## Notifications and emails

- BuddyPress notification to the group admin on a new review, and to the author on approve or deny.
- Optional emails for the same events, with customizable subject and message per event.

## Activity entries

When a review is published, the plugin can post a BuddyPress activity entry. This is optional and respects group privacy.

## Group-level criteria

When the site admin enables it, each group admin can customize the review criteria for their own group: enable a subset of the global criteria, add custom criteria, or fall back to the global set. See [Group-Level Review Criteria](../usage/group-level-criteria.md).

## Exclude groups

The site admin can exclude specific groups from reviews. Excluded groups show no review tabs, form, or ratings.

## Multiple reviews

By default a member can leave one review per group. A **Multiple reviews** setting lets members review the same group more than once.

## Leaderboard and widgets

- A **group leaderboard** shortcode ranks groups by rating, review count, or members.
- Two widgets display group ratings and reviews in sidebars and footers.

See [Shortcodes](../shortcodes/shortcodes.md) and [Rating Widgets](../usage/widgets.md).

## Bulk actions and CSV export

The Reviews list in the WordPress admin offers Bulk Approve and Bulk Deny, and an **Export to CSV** button that exports reviews with their ratings.

## REST API

Reviews are readable and writable through the `buddypress/v1` REST namespace, for mobile apps and headless front ends. See [REST API](../developer-guide/rest-api.md).

## Integrations

- **BuddyBoss Platform** notification links resolve to the correct review page.
- **GamiPress** - a `gamipress_bp_group_review` action fires when a review is added or approved, for awarding points or achievements.
