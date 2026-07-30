# CAPABILITIES — BuddyPress Group Reviews

What this plugin actually ships, rolled up from `audit/manifest.json`. Read this
before planning any feature so we do not rebuild something that already exists.

- **Slug:** `buddypress-groups-review` · **Text domain:** `bp-group-reviews`
- **Version:** 3.9.0 · **Requires:** WP 6.5+, PHP 8.0+, BuddyPress with the Groups component
- **Main file:** `review-bp-grps.php` · **Manifest:** `audit/manifest.json`

Self-deactivates when BuddyPress or its Groups component is missing.

## Core capability

Members review a BuddyPress group: a subject, a description, and a star rating
against one or more configurable criteria. Reviews are a private `review` CPT,
optionally moderated, and surfaced back on the group, in activity, in widgets,
in a leaderboard, and as JSON-LD `aggregateRating` schema.

## Feature surfaces

| Surface | What it does | Status |
|---|---|---|
| Review submission | `[add_group_review_form]` shortcode + group tab form | shipped |
| Moderation | Auto-approve or group-admin accept/deny queue | shipped |
| Rating criteria | Site-wide criteria, plus optional per-group criteria | shipped |
| Leaderboard | `[bgr_display_top_groups]` — top rated / most reviewed | shipped |
| Widgets | Group Review widget + Displayed Group Rating widget | shipped |
| Activity | Posts to BP activity on approval; stars injected into content | shipped |
| Notifications | BP notifications + configurable email on submit/accept/deny | shipped |
| SEO schema | JSON-LD `aggregateRating` + individual reviews on `wp_head` | shipped |
| REST API | `buddypress/v1` and `bgr/v1` namespaces | shipped |
| CSV export | Admin export of all reviews | shipped |
| Theme integration | `BGR_Theme_Integration` — BuddyX / Reign token map + dark mode | shipped |
| Multisite | Per-blog data handling incl. uninstall | shipped |

## Three entry points

1. **Frontend** — group tabs (`manage-{tab_slug}`, `review-criteria`), the two
   shortcodes, and the two widgets.
2. **Backend** — `WB Plugins > Group Reviews` (`group-review-settings`), a
   card-panel settings page with General / Criteria / Display / Email tabs, plus
   the `review` CPT list table and CSV export.
3. **API** — REST under `buddypress/v1` (group reviews, read + create) and
   `bgr/v1`; 16 authenticated `admin-ajax` actions.

## Data model

- **CPT** `review` — private (`public: false`, `show_ui: true`), mapped to
  `*_review` meta caps, `create_posts => do_not_allow`.
- **Taxonomy** `review_category` (hierarchical, no UI).
- **Options** — `bgr_admin_general_settings`, `bgr_admin_criteria_settings`,
  `bgr_admin_display_settings`, `bp_group_review_email_settings`.
- **No custom tables.**

## Security posture

- All 16 AJAX actions are **authenticated**; only
  `bp_group_review_filter_ratings` also registers a `nopriv` twin (public
  read-only rating filter). The `nopriv` registrations on accept / deny / remove
  / submit were removed in 3.9.0.
- Nonces: `admin-ajax-nonce` (admin), `ajax-nonce` (member-facing).
- Caps: admin handlers require `manage_options`; group-scoped handlers require
  `groups_is_user_admin() || manage_options`.
- CSV export is a GET `admin_init` flow gated by `manage_options` + nonce — it is
  deliberately **not** an AJAX endpoint (see `.contract-audit-baseline.json`).
- REST uses per-route permission callbacks; the two group-review GET routes are
  intentionally public.

## Extension points

39 fired hooks (see `hooks_fired` in the manifest). Frequently used:
`bgr_add_roles_capability`, `bgr_admin_capability`,
`bgr_filter_ratings_max_scan`, `bgr_exclude_groups_select_limit`,
`bgr_review_stats_query_limit`, `bgr_schema_reviews_limit`,
`bgr_group_after_review_submit`.

## Internationalisation

Ships `de_DE`, `es_ES`, `fr_FR`, `it_IT`, `pt_BR`. JS strings are delivered via
`languages/*.json`; `load_script_textdomain_relative_path` is filtered in
`includes/bgr-scripts.php` so JED lookups resolve past the `/min/` path segment.
Regenerate the JSON with `php bin/make-js-translations.php`.

## Known gaps

- `bgr_delete_custom_criteria` and `bgr_toggle_global_criteria` are fully
  implemented, nonce- and cap-checked AJAX handlers with **no UI caller** — group
  admins can archive a criterion but never delete one, and cannot toggle a global
  criterion off per group. Tracked as `AUDIT-VERDICT.md` finding #7: either wire
  the buttons in `assets/js/bgr-group-criteria-admin.js` or remove the handlers.
- The ratings filter is bounded (`bgr_filter_ratings_max_scan`, default 500) but
  has no true pagination — revisit before any very-large-site release.
