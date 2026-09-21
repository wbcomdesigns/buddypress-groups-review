# Adding a Review (Members)

## Who can add a review

The **Add Review** tab appears on a single group only when the current user is:

- logged in, and
- a member of that group, and
- **not** an admin of that group.

A group admin cannot review their own group. Visitors who are not logged in can read published reviews but see a prompt to log in before the form.

## Submitting a review

1. Open the group and click the **Add Review** tab (its label follows the Review label set by the site admin).
2. Rate each criterion from 1 to 5 stars. Every shown criterion is required.
3. Optionally add a text description.
4. Click **Submit**.

If no rating criteria are active, the form shows a single "Your Rating" star field instead of per-criterion rows, and a review can still be submitted.

## What happens after you submit

- If auto-approve is off (default), the review is saved as a **draft** and waits for the group admin to approve it. It does not appear publicly yet.
- If auto-approve is on, the review is **published** immediately and appears on the group's Reviews tab.
- The group admin receives a BuddyPress notification (and an email, if enabled) about the new review.

## One review or many

By default you can submit one review per group. If the site admin has enabled **Multiple reviews**, you can submit more than one review for the same group.

## Using the form on another page

The submission form can be placed on any page with the `[add_group_review_form]` shortcode. See [Shortcodes](../shortcodes/shortcodes.md).
