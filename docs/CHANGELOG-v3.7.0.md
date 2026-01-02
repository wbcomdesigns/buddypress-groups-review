# Changelog: Version 3.7.0

Release Date: [Current Date]

## Major Features

### Group-Level Review Criteria

Version 3.7.0 introduces the ability for group administrators to customize review criteria on a per-group basis.

#### For Site Administrators

**New Setting: Enable Group-Level Criteria**

Location: BuddyPress → Group Reviews → General Settings

When enabled, group administrators can customize which rating criteria appear on their group's review form.

**Benefits:**
- More flexibility for specialized groups
- Better ratings relevant to each group's purpose
- Maintained control through global criteria management

**How It Works:**

Site admins create and manage global criteria as before. Groups can then:
- Use global criteria as-is (Inherit mode)
- Choose which global criteria to enable (Customize mode)
- Add custom criteria specific to their group

#### For Group Administrators

**New Settings Page: Review Criteria**

Location: Group → Manage → Review Criteria (when feature is enabled by site admin)

**Features:**
- **Mode Selection:** Choose between using site-wide criteria or customizing
- **Global Criteria Control:** Enable/disable individual site-wide criteria
- **Custom Criteria:** Add rating categories unique to your group
- **Live Preview:** See exactly which criteria will appear on review forms

**Example Use Cases:**

Book Club Group:
- Disable: "Relevance" (not applicable)
- Keep: "Quality", "Engagement"
- Add Custom: "Book Selection", "Discussion Depth"

Tech Support Group:
- Disable: "Engagement"
- Keep: "Quality", "Relevance"
- Add Custom: "Response Time", "Solution Quality"

## Technical Changes

### New Files

```
includes/
├── class-bgr-group-criteria.php          # Core criteria management class
├── bgr-group-criteria-functions.php      # Helper functions
├── bgr-group-criteria-ajax.php           # AJAX handlers
└── templates/
    └── bgr-group-criteria-settings.php   # Group admin template
```

### Updated Files

```
includes/
├── bgr-grp-extn.php                      # Added Group_Reviews_Criteria_Extn class
├── bgr-globals.php                       # Added enable_group_criteria setting
└── bgr-ajax.php                          # Added enable_group_criteria to save handler

admin/
└── review-admin-options-page.php         # Added Enable Group-Level Criteria toggle
```

### New Classes

**BGR_Group_Criteria**

Singleton class managing all group-level criteria operations.

Key methods:
- `get_effective_criteria( $group_id )` - Get active criteria for a group
- `set_criteria_mode( $group_id, $mode )` - Switch between inherit/override
- `add_custom_criteria( $group_id, $name )` - Add group-specific criterion
- `archive_custom_criteria( $group_id, $name )` - Soft delete criterion
- `calculate_group_averages( $group_id )` - Get rating averages per criterion

**Group_Reviews_Criteria_Extn**

BuddyPress Group Extension providing admin interface.

### New Functions

**Helper Functions:**
- `bgr_group_criteria()` - Get BGR_Group_Criteria instance
- `bgr_get_effective_criteria( $group_id )` - Get active criteria
- `bgr_is_using_custom_criteria( $group_id )` - Check if in customize mode
- `bgr_get_criteria_mode( $group_id )` - Get current mode

**Permission Check:**
- `bgr_user_can_manage_group_criteria( $group_id )` - Capability check

### New AJAX Actions

- `wp_ajax_bgr_save_group_criteria_settings` - Save criteria settings
- `wp_ajax_bgr_add_custom_criteria` - Add custom criterion
- `wp_ajax_bgr_archive_custom_criteria` - Archive criterion
- `wp_ajax_bgr_delete_custom_criteria` - Delete criterion
- `wp_ajax_bgr_toggle_global_criteria` - Toggle global criterion
- `wp_ajax_bgr_get_group_criteria` - Get criteria data

### New REST API Endpoints

**GET** `/wp-json/bgr/v1/groups/{id}/criteria`
- Public endpoint
- Returns group criteria settings and effective criteria

**POST** `/wp-json/bgr/v1/groups/{id}/criteria`
- Requires group admin permission
- Updates group criteria settings

### New WordPress Hooks

**Actions:**
- `bgr_global_criteria_deleted` - Fires when site admin deletes global criterion
- `bgr_global_criteria_archived` - Fires when site admin archives global criterion

**Filters:**
- `bgr_group_criteria_max_length` - Filter max length for custom criteria names (default: 50)

### Database Changes

**New Group Meta:**

Key: `bgr_group_criteria_settings`

Structure:
```php
[
    'mode' => 'inherit|override',
    'enabled_global_criteria' => ['Quality', 'Relevance'],
    'custom_criteria' => [
        [
            'name' => 'Custom Criterion',
            'created' => 1234567890,
            'active' => true
        ]
    ],
    'archived_criteria' => [
        'Old Criterion' => [
            'archived_at' => 1234567890,
            'reason' => 'disabled_by_group_admin'
        ]
    ]
]
```

Stored in: `{$wpdb->prefix}bp_groups_groupmeta`

**New Global Setting:**

Key: `bgr_admin_general_settings['enable_group_criteria']`

Values: 'yes' | 'no'

## Feature Details

### Inherit vs Customize Modes

**Inherit Mode (Default):**
- Group uses exactly what site admin configured
- Automatic updates when global criteria change
- Zero maintenance for group admin
- Consistent across all groups

**Customize Mode:**
- Group admin controls which criteria appear
- Can enable/disable global criteria
- Can add custom group-specific criteria
- Manual updates required

### Archived Criteria

When criteria are removed (by site admin or group admin), they're archived rather than deleted.

**Why Archive?**

Preserves data integrity for existing reviews. Reviews submitted before archival still display all their original ratings.

**Archive Triggers:**
- Site admin deactivates global criterion
- Group admin disables custom criterion
- Custom criterion deleted when reviews exist

**Archive Tracking:**
- Timestamp of archival
- Reason for archival
- Original criterion data

### Data Integrity

**Review Display:**

Each review remembers which criteria it was rated with. If a criterion is later archived, the review still displays that rating with an "archived" indicator.

**Rating Calculations:**

Averages are calculated only from published reviews. Archived criteria are included in calculations for reviews that used them.

**Historical Data:**

All rating data is preserved permanently, ensuring accurate historical reporting.

## User Experience Improvements

### For Site Admins

**Centralized Control:**
- One place to manage global criteria
- Clear visibility into which groups customize
- Impact analysis when changing global criteria

**Flexibility Without Chaos:**
- Groups can customize within defined boundaries
- Site admin retains ultimate control
- Archived criteria prevent data loss

### For Group Admins

**Simplified Interface:**
- Clear mode selection
- Visual preview of active criteria
- Inline help text
- Real-time validation

**Smart Defaults:**
- New groups default to Inherit mode
- Switching to Customize pre-selects current global criteria
- Prevents accidentally disabling all criteria

**Safe Operations:**
- Can't delete criteria with existing reviews
- Archive system preserves data
- Changes apply immediately to new reviews

### For Reviewers

**Relevant Criteria:**
- See only criteria applicable to the group
- Better review experience
- More meaningful ratings

**Consistent Experience:**
- Review form works identically
- Same star rating system
- Familiar interface

## Backward Compatibility

### Existing Reviews

All existing reviews continue to display correctly. No data migration needed.

### Existing Groups

All existing groups default to Inherit mode, maintaining current behavior until a group admin actively chooses to customize.

### API Compatibility

All existing API functions continue to work. New functions are additions, not replacements.

### Theme Compatibility

No theme changes required. The feature integrates seamlessly with existing review display.

## Performance

**Database Queries:**

Minimal impact. Settings are stored in group meta with standard BuddyPress caching.

**Page Load:**

No additional queries on review display. Criteria are loaded once per page.

**AJAX Operations:**

All admin operations use AJAX to prevent page reloads and improve user experience.

## Security

**Capability Checks:**

All operations verify user has permission:
- Site admin (`manage_options`)
- Group admin (`groups_is_user_admin`)
- Group creator

**Nonce Verification:**

All AJAX requests require valid nonce: `bgr_group_criteria_nonce`

**Input Sanitization:**

All user input is sanitized:
- Criterion names: `sanitize_text_field()`
- Group IDs: `absint()`
- Arrays: Individually sanitized

**Output Escaping:**

All output properly escaped:
- HTML: `esc_html()`
- Attributes: `esc_attr()`
- URLs: `esc_url()`

## Testing

### Manual Testing Checklist

**Site Admin:**
- [ ] Enable/disable group-level criteria feature
- [ ] Add new global criterion
- [ ] Deactivate global criterion
- [ ] Verify archived tracking

**Group Admin:**
- [ ] Switch between Inherit and Customize modes
- [ ] Enable/disable global criteria
- [ ] Add custom criterion
- [ ] Archive custom criterion
- [ ] Verify preview updates

**Review Display:**
- [ ] Submit review with current criteria
- [ ] Archive criterion
- [ ] Verify old review still shows archived criterion
- [ ] Verify new review doesn't show archived criterion

### Browser Compatibility

Tested on:
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

### WordPress Compatibility

Tested with:
- WordPress 6.0+
- BuddyPress 10.0+
- PHP 7.4, 8.0, 8.1, 8.2

## Known Limitations

### Custom Criteria Limit

No hard limit on number of custom criteria per group, but UI becomes unwieldy beyond 10-15 total criteria.

**Recommendation:** Keep total criteria (global + custom) under 10 for best user experience.

### Criterion Name Length

Maximum 50 characters for custom criteria names.

**Rationale:** Ensures consistent display across all devices and themes.

**Override:** Use filter `bgr_group_criteria_max_length` if needed.

### Archived Criteria

Can't be permanently deleted if reviews exist.

**Rationale:** Preserves review data integrity.

**Workaround:** None needed - archived criteria don't appear on new reviews.

## Migration Path

### From Earlier Versions

**Automatic:**
- All groups default to Inherit mode
- Existing reviews display correctly
- No admin action required

**Optional:**
- Site admin can enable group-level criteria
- Groups can then customize as needed

### To Future Versions

All data structures designed for forward compatibility:
- Settings stored in standard group meta
- Extensible array structure
- No hardcoded limitations

## Documentation

### New Documentation Files

- `docs/admin-guide-group-level-criteria.md` - Site admin setup guide
- `docs/group-admin-guide-review-criteria.md` - Group admin user guide
- `docs/developer-api-group-criteria.md` - Developer API reference
- `docs/quick-reference-group-criteria.md` - Quick lookup guide
- `docs/CHANGELOG-v3.7.0.md` - This file

### Updated Files

- `docs/README.md` - Overview and quick links

## Support

### Common Questions

**Q: Does this affect existing reviews?**
A: No. Existing reviews display exactly as before.

**Q: Can group admins delete site-wide criteria?**
A: No. They can only disable them for their group.

**Q: What happens when I deactivate a global criterion?**
A: It's archived. Groups in Inherit mode stop showing it. Groups in Customize mode keep it if previously enabled.

**Q: Can groups create unlimited custom criteria?**
A: Technically yes, but we recommend staying under 10 total criteria.

**Q: Do I need to update my theme?**
A: No. The feature works with existing themes.

## Future Enhancements

### Planned

- Bulk criteria management for site admins
- Import/export criteria sets
- Criteria templates for common group types
- Analytics dashboard for criteria usage

### Under Consideration

- Criterion icons/images
- Weighted criteria (some count more than others)
- Conditional criteria (show based on group attributes)
- Multi-language criteria names

## Credits

**Development:** Wbcom Designs Development Team
**Testing:** Wbcom Designs QA Team
**Documentation:** Wbcom Designs Technical Writing Team

## License

GPL v2 or later, consistent with plugin license.

## Release Notes

This release represents a major feature addition while maintaining 100% backward compatibility. Existing installations can upgrade without any changes to behavior unless the site admin actively enables the new group-level criteria feature.

The architecture is designed for extensibility, with clear separation of concerns and comprehensive hooks for developers to extend functionality.
