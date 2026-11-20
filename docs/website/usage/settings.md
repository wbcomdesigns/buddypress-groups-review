# Site Admin Settings

All settings live under **WB Plugins, Group Reviews** in the WordPress admin (page slug `group-review-settings`). The page has four tabs plus a welcome screen.

## General

Stored in the `bgr_admin_general_settings` option.

| Setting | Default | What it does |
|---|---|---|
| Auto approve reviews | Off (`no`) | Publish reviews immediately instead of holding them for approval. |
| Reviews per page | 5 | Page size for the group's Manage Reviews list. Allowed range 1 to 100; a blank, zero or negative value falls back to 5. |
| Allow notification | On (`yes`) | Send BuddyPress notifications for review events. |
| Allow activity | On (`yes`) | Post a BuddyPress activity entry when a review is published. |
| Exclude groups | none | Groups to exclude from reviews (no tabs, form, or ratings). The REST API refuses both listing and submitting reviews for them, and their pages print no rating markup. |
| Multiple reviews | Off (`no`) | Allow a member to submit more than one review per group. |
| Enable group-level criteria | Off (`no`) | Show the per-group Review Criteria tab. See [Group-Level Review Criteria](group-level-criteria.md). |

## Criteria

Stored in the `bgr_admin_criteria_settings` option (`add_review_rating_fields` and `active_rating_fields`).

Add the rating criteria members score. The plugin ships with **Quality**, **Relevance**, and **Engagement**. Disable a criterion to hide it from the review form without deleting it. If no criteria are active, the form shows a single "Your Rating" field.

## Display

Stored in the `bgr_admin_display_settings` option.

| Setting | Default | What it does |
|---|---|---|
| Review label | Review | Label for the Add Review tab and buttons. |
| Manage review label | Reviews | Label for the Reviews listing tab (and the group's Manage Reviews tab). |
| Rating star colour | `#FFC400` | Colour applied to the rating stars on the front end. |

Changing the labels also changes the tab slugs, which are derived from the labels.

## Emails

Stored in the `bp_group_review_email_settings` option. Emails are separate from BuddyPress notifications.

- **Allow emails** - master switch for review emails.
- **New review email** - to the group admin when a review is submitted (subject and message).
- **Accept email** - to the author when their review is approved (toggle, subject, message).
- **Deny email** - to the author when their review is denied (toggle, subject, message).
