# What BuddyPress Group Reviews Does

BuddyPress Group Reviews adds a review and star-rating layer to BuddyPress groups. Members write a short review of a group and rate it against one or more criteria (for example Quality, Relevance, Engagement). Reviews wait for approval, and once a group admin approves them they appear on the group's Reviews tab, and the group's average rating shows on the groups directory and in the group header.

## Who it is for

- **Members** submit a review and star rating from a group's "Add Review" tab, or from any page through a shortcode.
- **Group admins** approve or deny the reviews left on their own group, from a "Manage Reviews" tab inside the group.
- **Site admins** control the criteria, labels, moderation behaviour, email and BuddyPress notifications, and which groups are excluded, from a settings page under WB Plugins.

## What you get

- Multi-criteria star ratings, with a per-group average that surfaces on the directory and group header.
- Review moderation: reviews are held as drafts until an admin approves them (or auto-approve can be turned on).
- BuddyPress notifications and optional emails to the group admin on a new review, and to the author on approve or deny.
- An activity entry when a review is published (optional).
- Optional group-level criteria, letting each group admin tailor the criteria for their own group.
- A group leaderboard shortcode and rating widgets.
- A REST API under the `buddypress/v1` namespace for headless and mobile use.

## Requirements at a glance

BuddyPress (with the Groups component active) is required. See [Installation and Requirements](installation.md) for versions.
