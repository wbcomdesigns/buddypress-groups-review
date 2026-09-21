# Shortcodes

The plugin registers two shortcodes.

## `[add_group_review_form]`

Renders the review submission form.

- On a **single group page**, it shows the full rating form for eligible members (logged in, a member, not the group admin), or a login prompt for visitors, or an "already reviewed" notice when the member has already reviewed and Multiple reviews is off.
- The criteria shown are the group's effective criteria (global, or the group's own if group-level criteria are enabled).

```
[add_group_review_form]
```

This shortcode takes no attributes. It is designed for the single-group context, since it needs a current group to bind the form to.

## `[bgr_display_top_groups]`

Renders a group leaderboard. Only groups with at least one published review are included, and only public groups.

```
[bgr_display_top_groups title="Top Rated Groups" total_groups="10" type="top rated" show_avatar="true" show_rating="true"]
```

| Attribute | Default | Values | Description |
|---|---|---|---|
| `title` | (empty) | any text | Heading shown above the list. Omit for no heading. |
| `total_groups` | `10` | integer | Number of groups to display. |
| `type` | `top rated` | `top rated`, `most reviewed`, `members` | Ranking. `top rated` orders by average rating (review count breaks ties); `most reviewed` orders by review count (rating breaks ties); `members` orders by member count. |
| `show_avatar` | `true` | `true`, `false` | Show each group's avatar. |
| `show_rating` | `true` | `true`, `false` | Show the star rating, numeric average, and review count. |

Each row links to the group and shows its avatar, name, star rating with numeric average, review count, and member count.

> Note: the shortcode is `[bgr_display_top_groups]`. A `[bgr_group_leaderboard]` name mentioned in some changelog notes is not registered and does not work.
