# Group-Level Review Criteria - Site Admin Guide

Group-level review criteria (added in 3.7.0) allow group admins to customize which rating criteria appear on their group's review form.

## Overview

By default, all groups use the site-wide review criteria you configure. With group-level criteria enabled, each group admin can:

- Use site-wide criteria (inherit mode)
- Choose which site-wide criteria to enable
- Add custom criteria specific to their group
- Track archived criteria for historical reviews

## Enabling the Feature

### Step 1: Access Review Settings

1. Go to **WB Plugins → Group Reviews → General** in your WordPress admin
2. Locate the **Group-Level Criteria** option

### Step 2: Enable Group-Level Criteria

1. Toggle the **Group-Level Criteria** switch to **ON**
2. Click **Save Settings**

Once enabled, group admins will see a new **Review Criteria** tab in their group admin panel.

## How It Works

### Default Behavior (Feature Disabled)

All groups use your site-wide criteria settings. Changes to global criteria affect all groups immediately.

### When Enabled

Each group can operate in two modes:

| Mode | Description | Updates |
|------|-------------|---------|
| **Inherit** | Uses site-wide criteria exactly as configured | Automatic when you change global criteria |
| **Customize** | Group admin selects which criteria to use | Manual - group admin controls what appears |

### What Group Admins Can Do

When a group admin chooses **Customize**:

1. **Enable/Disable Global Criteria** - Toggle individual site-wide criteria on or off for their group
2. **Add Custom Criteria** - Create rating criteria unique to their group (max 50 characters)
3. **Archive Custom Criteria** - Remove criteria while preserving historical review data

### What You Control

As site admin, you control:

- Whether the feature is available at all
- The list of site-wide criteria available to all groups
- Which groups can have reviews (via exclude groups setting)

## Managing Global Criteria

### Adding New Global Criteria

1. Go to **WB Plugins → Group Reviews → Criteria**
2. Add your new criterion
3. Save changes

**Impact on groups:**
- Groups in **Inherit mode**: New criterion appears immediately
- Groups in **Customize mode**: New criterion available for them to enable

### Deactivating Global Criteria

1. Go to **WB Plugins → Group Reviews → Criteria**
2. Deactivate the criterion
3. Save changes

**Impact on groups:**
- Groups in **Inherit mode**: Criterion removed from review form
- Groups in **Customize mode**: Criterion disabled and archived
- Historical reviews: Criterion ratings preserved and still displayed

## Archived Criteria Tracking

When you deactivate a global criterion or a group admin removes a custom one, the system archives it rather than deleting it. This ensures:

- Existing reviews display all original ratings
- Rating averages remain accurate
- Review history is preserved

Archived criteria are tracked with:
- Archive timestamp
- Reason for archival (global deletion, group admin action, etc.)

## Common Scenarios

### Scenario 1: You change site-wide criteria

**Groups in Inherit mode:** Changes apply immediately
**Groups in Customize mode:** Not affected - they continue using their selected criteria

### Scenario 2: You deactivate a global criterion

**All groups:** Criterion is archived and removed from review forms
**Existing reviews:** Continue to display the criterion's rating

### Scenario 3: Group admin adds custom criterion, then removes it

**If reviews exist:** Criterion is archived and still appears on those reviews
**If no reviews:** Criterion can be permanently deleted

## Best Practices

### When to Enable Group-Level Criteria

Enable this feature when:
- Groups have different focus areas requiring different rating criteria
- Group admins are trusted to manage their review experience
- You want to give groups more autonomy

Keep it disabled when:
- You want consistent rating criteria across all groups
- Simplified management is more important than customization
- Groups are primarily managed by site admins

### Communicating with Group Admins

When you enable this feature, inform your group admins:

1. Where to find the setting: **Group → Manage → Review Criteria**
2. The two modes and when to use each
3. That custom criteria can't be deleted if reviews use them
4. To keep criteria names clear and concise (50 character limit)

### Managing Criteria Strategically

**Start broad:** Create comprehensive site-wide criteria that work for most groups

**Let groups specialize:** Allow them to disable irrelevant criteria and add specific ones

**Avoid frequent changes:** Changing global criteria affects all groups in Inherit mode

## Troubleshooting

### Group admin can't see Review Criteria tab

**Check:**
1. Is the group excluded from reviews? (General Settings → Exclude Groups)
2. Is Enable Group-Level Criteria turned on?
3. Is the user actually a group admin or moderator?

### Custom criterion won't delete

**Reason:** Reviews exist with ratings for that criterion

**Solution:** The criterion is archived instead, preserving review data. This is intentional.

### Changes to global criteria not appearing in a group

**Reason:** The group is in Customize mode

**Solution:** The group admin must manually enable new global criteria or switch back to Inherit mode

## Related Settings

**Exclude Groups from Reviews**
Located below Enable Group-Level Criteria. Groups excluded from reviews won't have the Review Criteria tab.

**Site-Wide Criteria Settings**
Configure your default criteria at: **WB Plugins → Group Reviews → Criteria**

## Technical Notes

**Database Storage:** Group criteria settings are stored in group meta using the key `bgr_group_criteria_settings`

**Performance:** No impact on frontend display. Criteria are cached per group.

**Multisite:** Each site manages its own group-level criteria settings independently.

## Need Help?

- Documentation: See the Group Admin Guide for end-user instructions
- Developer Docs: See API reference for hooks and filters
- Support: Contact Wbcom Designs support
