# Ratings on the Directory and Group Header

Once a group has published reviews, its average rating is shown in two public places automatically, without any shortcode.

## Groups directory

Each group in the groups directory shows its average star rating, so visitors can compare groups at a glance. This is rendered on the `bp_directory_groups_item` hook.

## Single group header

On a single group page, the average rating and review count appear in the group header area, alongside the group's other header details. When a member is eligible to review, an **Add Review** button is also placed in the header actions next to the Join Group button.

## How the average is calculated

- Each published review's rating is the average of its criteria scores.
- A group's average is the mean of its published reviews' ratings, rounded to two decimals.
- Only **published** reviews count. Drafts (pending) and denied reviews do not affect the average.

## Excluded groups

Groups the site admin has excluded show no rating on the directory or header, and no review tabs.
