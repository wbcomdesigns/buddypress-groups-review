# Moderating Reviews (Group Admins)

Reviews are held for approval unless the site admin has turned on auto-approve. There are two places to moderate them.

## Inside the group (group admins)

Each group has a **Manage Reviews** tab (its label follows the Review label, shown as "Manage ..."). It is available to group admins and lists the pending (draft) reviews for that group.

For each pending review you see the author, a short excerpt, the full description, and the star ratings, with two actions:

- **Accept** - publishes the review. It now appears on the group's Reviews tab and counts toward the group average. The author is notified (and emailed, if enabled), and the `gamipress_bp_group_review` action fires.
- **Deny** - rejects the review. The author is notified (and emailed, if enabled).

If Accept, Deny or Remove is refused (for example the review was already handled), the message from the server appears in a notice in that review's row instead of the page reloading.

The list is paginated using the **Reviews per page** setting. Prev and Next links appear when there is more than one page.

## In the WordPress admin (site admins)

Reviews are a `review` custom post type, so site admins can manage them under the **Reviews** menu in wp-admin:

- Approve a review by publishing it, or deny it by leaving it as a draft or trashing it.
- **Bulk Approve** and **Bulk Deny** act on multiple selected reviews at once.
- **Export to CSV** exports reviews with their ratings from the Reviews list.

## Notes

- The **Reviews** tab and rating displays are hidden for groups the site admin has excluded. The REST API also refuses to list or accept reviews for them.
- When a member double-submits, the plugin prevents duplicate reviews.
