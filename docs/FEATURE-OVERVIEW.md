# Group-Level Review Criteria - Feature Overview

**BuddyPress Group Reviews v3.7.0**

## What's New

Groups can now customize review criteria instead of using only site-wide settings.

## Key Benefits

**For Sites:**
- Better reviews - criteria match each group's purpose
- More flexibility without losing control
- Happy group admins with more autonomy

**For Groups:**
- Relevant rating categories
- Add group-specific criteria
- Simple or detailed - your choice

**For Reviewers:**
- More meaningful ratings
- Criteria that make sense for the group

## How It Works

### Site Admin (You Control)

**Step 1:** Enable the feature
- Go to **BuddyPress → Group Reviews → General Settings**
- Toggle **Enable Group-Level Criteria** to ON
- Save

**Step 2:** Manage global criteria
- Add/remove criteria at **Criteria Settings**
- Your criteria are available to all groups
- Changes affect groups in Inherit mode automatically

### Group Admins (They Customize)

**Step 1:** Choose a mode
- **Inherit** - Use your global criteria (automatic, zero maintenance)
- **Customize** - Pick which criteria to use and add custom ones

**Step 2:** Configure criteria (if Customize mode)
- Enable/disable global criteria
- Add custom criteria unique to their group
- Preview what appears on review form

## Two Modes Explained

### Inherit Mode (Default)
✓ Uses site-wide criteria exactly
✓ Updates automatically when you change global criteria
✓ Zero work for group admin
✓ Consistent across groups

**Best for:** General groups, new groups, standardization

### Customize Mode
✓ Group controls which criteria appear
✓ Can disable irrelevant global criteria
✓ Can add custom group-specific criteria
✓ Flexible for specialized groups

**Best for:** Book clubs, tech groups, special interest groups

## Real Examples

### Book Club Group
**Global:** Quality, Relevance, Engagement
**Group Uses:** Quality, Engagement, Book Selection (custom), Discussion Depth (custom)

**Why:** "Relevance" doesn't fit. Book-specific criteria added.

### Tech Support Group
**Global:** Quality, Relevance, Engagement
**Group Uses:** Quality, Relevance, Response Time (custom), Solution Quality (custom)

**Why:** Added support-specific metrics.

### General Discussion Group
**Global:** Quality, Relevance, Engagement
**Group Uses:** Quality, Relevance, Engagement

**Why:** Global criteria work perfectly. Stays in Inherit mode.

## What Happens to Old Reviews?

Nothing changes. All existing reviews display exactly as before.

When criteria are archived:
- **New reviews:** Don't show that criterion
- **Old reviews:** Still display all original ratings

This preserves review history and data integrity.

## Site Admin Quick Start

```
1. BuddyPress → Group Reviews → General Settings
2. Toggle "Enable Group-Level Criteria" → ON
3. Save Settings
4. (Optional) Inform group admins of new feature
```

Done! Groups can now customize if they want to.

## Group Admin Quick Start

```
1. Group → Manage → Review Criteria
2. Choose mode:
   - Inherit: Select and save. Done!
   - Customize: Select, pick criteria, add custom ones, save
3. Preview shows what reviewers will see
```

## For Developers

### Get Active Criteria
```php
$criteria = bgr_get_effective_criteria( $group_id );
// Returns: ['Quality', 'Custom Criterion', ...]
```

### Check Mode
```php
$mode = bgr_get_criteria_mode( $group_id );
// Returns: 'inherit' or 'override'
```

### Add Custom Criterion
```php
bgr_group_criteria()->add_custom_criteria( $group_id, 'Code Quality' );
```

### REST API
```
GET  /wp-json/bgr/v1/groups/{id}/criteria
POST /wp-json/bgr/v1/groups/{id}/criteria
```

**Full API:** See [developer-api-group-criteria.md](developer-api-group-criteria.md)

## Common Questions

**Q: Is this required?**
A: No. It's optional. Enable only if you want groups to customize.

**Q: What if I don't enable it?**
A: Everything works exactly as before. All groups use global criteria.

**Q: Can group admins change my global criteria?**
A: No. They can only enable/disable them for their group, not edit them.

**Q: What if a group goes crazy with custom criteria?**
A: They can't affect other groups. Each group's settings are isolated.

**Q: Can I see which groups are customizing?**
A: Yes, through the database or REST API. UI coming in future version.

**Q: Does this slow down the site?**
A: No measurable impact. Settings are cached in group meta.

**Q: Can groups switch between modes?**
A: Yes, anytime. Settings are preserved when switching.

**Q: What happens when I delete a global criterion?**
A: It's archived in all groups using it. Reviews preserve the data.

## Best Practices

### When to Enable This Feature

**Enable when:**
- You have diverse group types (book clubs, tech groups, etc.)
- Group admins are trusted and engaged
- Flexibility is more valuable than strict consistency

**Don't enable when:**
- All groups are similar
- You want strict standardization
- Simplicity is more important than customization

### Communicating with Group Admins

When enabling, tell them:
1. Where to find it: Group → Manage → Review Criteria
2. Two modes and when to use each
3. Custom criteria limit: Keep it under 10 total
4. They can switch modes anytime

### Managing Global Criteria

**Strategy:**
1. Start with broad criteria (Quality, Engagement, etc.)
2. Let groups specialize with custom criteria
3. Avoid frequent global changes (affects Inherit mode groups)
4. Add new global criteria rarely

## Troubleshooting

**Group admin can't see Review Criteria tab:**
- Is the feature enabled by site admin?
- Is the user actually a group admin?
- Is the group excluded from reviews?

**Custom criterion won't delete:**
- Reviews exist with that criterion
- It's archived instead (preserves data)
- This is expected behavior

**Changes not showing:**
- Did they click Save?
- Try refreshing the page
- Check browser console for errors

## Files Created

```
docs/
├── INDEX.md                                  # Complete index
├── README.md                                 # Main overview
├── admin-guide-group-level-criteria.md      # Site admin (1,800 words)
├── group-admin-guide-review-criteria.md     # Group admin (2,000 words)
├── developer-api-group-criteria.md          # Developers (3,500 words)
├── quick-reference-group-criteria.md        # Quick lookup (1,500 words)
├── visual-guide-group-criteria.md           # Diagrams (2,000 words)
├── CHANGELOG-v3.7.0.md                      # Release notes (3,000 words)
└── FEATURE-OVERVIEW.md                      # This file
```

## Get Full Documentation

**Complete guides:**
- Site Admins: [admin-guide-group-level-criteria.md](admin-guide-group-level-criteria.md)
- Group Admins: [group-admin-guide-review-criteria.md](group-admin-guide-review-criteria.md)
- Developers: [developer-api-group-criteria.md](developer-api-group-criteria.md)

**Quick help:**
- [quick-reference-group-criteria.md](quick-reference-group-criteria.md)

**Visual explanations:**
- [visual-guide-group-criteria.md](visual-guide-group-criteria.md)

**Start here:**
- [INDEX.md](INDEX.md) - Find the right documentation for your role

## Support

**Documentation:** See guides linked above
**Plugin Support:** Wbcom Designs support portal
**Website:** https://wbcomdesigns.com/

## Version Info

**Plugin:** BuddyPress Group Reviews 3.7.0
**Feature:** Group-Level Criteria (new in 3.7.0)
**Released:** January 2026

## License

GPL v2 or later

---

**Next Steps:**
- Site Admin? Read [admin-guide-group-level-criteria.md](admin-guide-group-level-criteria.md)
- Group Admin? Read [group-admin-guide-review-criteria.md](group-admin-guide-review-criteria.md)
- Developer? Read [developer-api-group-criteria.md](developer-api-group-criteria.md)
