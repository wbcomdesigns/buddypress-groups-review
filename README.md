# BuddyPress Group Reviews

Let members review and rate BuddyPress groups across criteria you define, with moderation, a top-groups leaderboard and search-ready rating markup.

Current release: 3.9.2. WordPress plugin by [Wbcom Designs](https://wbcomdesigns.com/).

## What it does

- Members write a text review and rate the group on every rating criterion you set.
- Add, reorder, enable or disable criteria from the Criteria tab. Optionally let each group admin tailor the criteria for their own group.
- Reviews wait for approval by default. Group admins and site admins approve or deny from the group's Manage tab or from wp-admin, with bulk actions and optional auto approval.
- Exclude any group from reviews, export group reviews to CSV, and let members leave more than one review per group if you allow it.
- Average ratings show on the group page, the group header and the group directory. Ratings are published as Schema.org AggregateRating and Review markup.
- `[bgr_display_top_groups]` shortcode for a leaderboard, `[add_group_review_form]` for the review form, and two sidebar widgets.
- BuddyPress notifications, email notifications, optional activity stream posts, GamiPress rewards and multisite support.
- Rename the Review label and pick the star colour. Styled for the BuddyX and Reign themes, including dark mode. RTL ready.

## Who it is for

Community sites where members need to find the right group to join: clubs, teams, chapters, classes and membership communities running BuddyPress or BuddyBoss Platform.

## Get the plugin

- WordPress.org: https://wordpress.org/plugins/review-buddypress-groups/
- Store page: https://wbcomdesigns.com/downloads/buddypress-group-reviews/

## Documentation

- Docs: https://docs.wbcomdesigns.com/bp-family/buddypress-group-reviews/
- Release notes: https://wbcomdesigns.com/release-notes/buddypress-group-reviews/
- Source for the docs lives in [docs/website](docs/website).

## Requirements

- WordPress 6.5 or higher
- PHP 8.0 or higher
- BuddyPress or BuddyBoss Platform with the User Groups component active

## Install

1. In wp-admin go to Plugins > Add New, search for "BuddyPress Group Reviews" and click Install Now, or upload the plugin zip.
2. Activate the plugin. If the Groups component is off, turn it on at Settings > BuddyPress > Components first.
3. Open WB Plugins > Group Reviews to set criteria, approval and display options.

## For developers

- Text domain `bp-group-reviews`, function and hook prefix `bgr_`.
- REST API: group reviews under `buddypress/v1` (`/groups/{group_id}/reviews`) and group criteria under `bgr/v1`. See the [REST API guide](docs/website/developer-guide/rest-api.md).
- Actions and filters: see the [hooks and filters guide](docs/website/developer-guide/hooks-and-filters.md).
- Shares the `review` post type with BuddyPress Member Reviews: see [Member Reviews compatibility](docs/website/developer-guide/member-reviews-compatibility.md).

## Support and licence

Support: https://wbcomdesigns.com/support/ - feature requests: https://wbcomdesigns.com/contact/

Licensed under GPL v2 or later.
