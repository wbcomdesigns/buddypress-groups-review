# Customizing Review Criteria - Group Admin Guide

As a group admin, you can customize which rating criteria appear on your group's review form.

## Overview

Your site administrator must enable this feature. Once enabled, you can:

- Use the site's default criteria
- Choose which default criteria to show
- Add custom criteria specific to your group

## Accessing Criteria Settings

1. Go to your group's page
2. Click **Manage** (group admin menu)
3. Select **Review Criteria** from the sidebar

## Choosing Your Mode

You have two options for how criteria work in your group:

### Use Site-Wide Criteria (Inherit)

**What it means:** Your group uses exactly what the site admin configured.

**Pros:**
- No maintenance required
- Automatically stays updated with site changes
- Consistent with other groups

**Cons:**
- Less control over what appears
- May include irrelevant criteria for your group

**Best for:** General-purpose groups, new groups, or when you want simplicity.

### Customize Criteria for This Group

**What it means:** You choose which criteria appear on your review form.

**Pros:**
- Full control over review criteria
- Can add group-specific criteria
- Can disable irrelevant site-wide criteria

**Cons:**
- Requires manual updates
- You won't automatically get new site-wide criteria

**Best for:** Specialized groups with unique needs.

## Working with Site-Wide Criteria

When you select **Customize**, you'll see all available site-wide criteria.

### Enabling Criteria

Check the box next to each criterion you want to use:

- Quality
- Relevance
- Engagement

### Disabling Criteria

Uncheck the box next to criteria you don't want:

If the criterion shows **Inactive globally**, the site admin has deactivated it. You can still see it in the list but it won't appear on new reviews.

## Adding Custom Criteria

Custom criteria are rating categories unique to your group.

### Step 1: Enter Criterion Name

1. In the **Custom Criteria** section, find the text field
2. Type your criterion name (max 50 characters)
3. Click **Add Criteria**

**Examples:**
- "Instructor Quality" (for education groups)
- "Code Quality" (for developer groups)
- "Recipe Accuracy" (for cooking groups)
- "Workout Effectiveness" (for fitness groups)

### Step 2: Verify It Was Added

Your new criterion appears in the **Custom Criteria** list with your group-specific criteria.

### Naming Best Practices

**Good names:**
- Clear and specific: "Course Content"
- Action-focused: "Engagement Level"
- Easy to rate: "Organization"

**Avoid:**
- Too vague: "Stuff"
- Too long: "How well does this group meet your expectations for quality"
- Duplicates: Don't create "Quality" if site-wide "Quality" exists

## Archiving Custom Criteria

You can archive custom criteria you no longer need.

### How to Archive

1. Find the criterion in your Custom Criteria list
2. Click **Archive** next to it
3. Confirm the action

### What Happens When You Archive

**On new reviews:** The criterion no longer appears

**On existing reviews:** The criterion's ratings still display

**Why:** The plugin preserves historical review data. If members rated this criterion before, those ratings remain visible.

### When Archive vs Delete

**Archive (soft delete):**
- Reviews exist with ratings for this criterion
- Data is preserved for historical accuracy

**Delete (permanent):**
- No reviews have rated this criterion yet
- The criterion disappears completely

The plugin automatically chooses the right action based on review data.

## Archived Criteria

The **Archived Criteria** section shows:

- Custom criteria you've archived
- Global criteria the site admin removed
- When each was archived
- Why it was archived

This is informational only - you can't restore archived criteria.

## Saving Your Changes

1. Make your changes to mode, global criteria, or custom criteria
2. Click **Save Criteria Settings**
3. Wait for the success message

Your changes apply immediately to new reviews.

## Preview Your Active Criteria

The **Current Active Criteria** box shows exactly what reviewers will see on your review form.

This updates after you save changes.

## Important Notes

### Existing Reviews

Changing criteria doesn't affect existing reviews. Each review displays the criteria it was rated with when submitted.

### Switching Between Modes

**Inherit → Customize:**
Your enabled criteria start with all currently active site-wide criteria. You can then modify them.

**Customize → Inherit:**
Your custom settings are saved but ignored. The group uses site-wide criteria. If you switch back to Customize, your settings return.

### Can't Delete a Custom Criterion?

If members have rated it in reviews, the plugin archives it instead of deleting it. This protects review data integrity.

### Site-Wide Criteria Changes

When the site admin adds or removes criteria:

**If you're in Inherit mode:** Your group's criteria update automatically

**If you're in Customize mode:** Your settings don't change. New site criteria appear in your available list, but you must enable them manually.

## Examples

### Example 1: Book Club Group

**Situation:** Site-wide criteria are Quality, Relevance, and Engagement. You want book-specific criteria.

**Solution:**
1. Choose **Customize**
2. Keep "Quality" and "Engagement" enabled
3. Disable "Relevance"
4. Add custom criteria:
   - "Book Selection"
   - "Discussion Depth"
   - "Reading Pace"
5. Save changes

**Result:** Reviews show 5 criteria (2 global, 3 custom)

### Example 2: General Discussion Group

**Situation:** Site-wide criteria work perfectly for your group.

**Solution:**
1. Choose **Use Site-Wide Criteria**
2. Save changes

**Result:** Your group always has the latest site-wide criteria without management.

### Example 3: Tech Support Group

**Situation:** You started with custom criteria but want to simplify.

**Solution:**
1. Change mode from **Customize** to **Use Site-Wide Criteria**
2. Save changes

**Result:** Your custom criteria settings are preserved but not used. New reviews use site-wide criteria.

## Troubleshooting

### I don't see the Review Criteria tab

**Check:**
- Are you a group admin or moderator? Regular members can't access this.
- Did the site admin enable group-level criteria?
- Is your group excluded from reviews?

**Solution:** Contact your site administrator.

### My custom criterion won't delete

**Reason:** Members have rated it in reviews.

**What happens:** It's archived instead to preserve review data.

**Solution:** This is expected behavior. The criterion stays visible on old reviews but disappears from new ones.

### Changes aren't showing on the review form

**Check:**
- Did you click "Save Criteria Settings"?
- Did you see a success message?
- Try refreshing the page

**Solution:** If problems persist, switch modes and back, then save again.

### A site-wide criterion shows as inactive

**Reason:** The site admin deactivated it globally.

**Impact:** You can enable it in your settings, but it won't appear on review forms until the site admin reactivates it.

## Need Help?

Contact your site administrator if:
- The feature isn't available
- You need criteria the site doesn't offer
- You want to delete an archived criterion permanently

This feature requires site administrator to enable it at: **WB Plugins → Group Reviews → General → Group-Level Criteria**
