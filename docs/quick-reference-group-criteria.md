# Quick Reference: Group-Level Criteria

Fast lookup guide for group-level review criteria feature (v3.7.0).

## Site Admin Checklist

### Enabling the Feature

- [ ] Go to **BuddyPress → Group Reviews → General Settings**
- [ ] Toggle **Enable Group-Level Criteria** to ON
- [ ] Click **Save Settings**
- [ ] Inform group admins of the new feature

### Managing Global Criteria

**Location:** BuddyPress → Group Reviews → Criteria Settings

**Add Criterion:**
1. Enter name in "Add New Criterion" field
2. Click Add
3. Activate it

**Deactivate Criterion:**
1. Find criterion in list
2. Toggle it off
3. Save changes
4. All groups are updated automatically

**Impact:**
- Groups in Inherit mode: Changes apply immediately
- Groups in Customize mode: Not affected

## Group Admin Checklist

### Access Settings

- [ ] Go to your group page
- [ ] Click **Manage**
- [ ] Select **Review Criteria** from sidebar

### Choose Your Mode

**Use Site-Wide Criteria (Inherit):**
- Select the radio button
- Save changes
- Done - no further config needed

**Customize Criteria:**
- Select "Customize criteria for this group"
- Check/uncheck global criteria
- Add custom criteria if needed
- Save changes

### Add Custom Criterion

1. Type name (max 50 chars)
2. Click **Add Criteria**
3. Verify it appears in list
4. Save changes

### Archive Custom Criterion

1. Find it in Custom Criteria list
2. Click **Archive**
3. Confirm action
4. Save changes

## Function Quick Reference

### Helper Functions

```php
// Get active criteria for a group
$criteria = bgr_get_effective_criteria( $group_id );

// Check if using custom mode
$is_custom = bgr_is_using_custom_criteria( $group_id );

// Get mode (inherit or override)
$mode = bgr_get_criteria_mode( $group_id );

// Get the main instance
$manager = bgr_group_criteria();
```

### Common Operations

```php
$criteria = bgr_group_criteria();

// Switch to customize mode
$criteria->set_criteria_mode( $group_id, 'override' );

// Add custom criterion
$criteria->add_custom_criteria( $group_id, 'Code Quality' );

// Enable global criterion
$criteria->toggle_global_criteria( $group_id, 'Quality', true );

// Disable global criterion
$criteria->toggle_global_criteria( $group_id, 'Relevance', false );

// Archive custom criterion
$criteria->archive_custom_criteria( $group_id, 'Old Criterion' );

// Get averages
$averages = $criteria->calculate_group_averages( $group_id );

// Check if criterion has reviews
$has_reviews = $criteria->criterion_has_reviews( $group_id, 'Quality' );
```

## AJAX Endpoints

**Nonce:** `bgr_group_criteria_nonce`

**Actions:**
- `bgr_save_group_criteria_settings` - Save mode and enabled criteria
- `bgr_add_custom_criteria` - Add custom criterion
- `bgr_archive_custom_criteria` - Archive custom criterion
- `bgr_delete_custom_criteria` - Delete custom criterion
- `bgr_toggle_global_criteria` - Enable/disable global criterion
- `bgr_get_group_criteria` - Get all settings

**All require:**
- Valid nonce
- Group admin or site admin capability
- POST request

## REST API Endpoints

**Base:** `/wp-json/bgr/v1/`

**GET** `/groups/{id}/criteria`
- Public access
- Returns group criteria info

**POST** `/groups/{id}/criteria`
- Requires group admin permission
- Updates criteria settings

## WordPress Hooks

### Actions

```php
// Fires when global criterion is deleted
do_action( 'bgr_global_criteria_deleted', $criteria_name );

// Fires when global criterion is archived
do_action( 'bgr_global_criteria_archived', $criteria_name );
```

### Filters

```php
// Change max length for custom criteria names (default: 50)
apply_filters( 'bgr_group_criteria_max_length', 50 );
```

## Database

**Group Meta Key:** `bgr_group_criteria_settings`

**Structure:**
```php
[
    'mode' => 'inherit|override',
    'enabled_global_criteria' => ['Quality', 'Relevance'],
    'custom_criteria' => [
        ['name' => 'Custom', 'created' => timestamp, 'active' => true]
    ],
    'archived_criteria' => [
        'Name' => ['archived_at' => timestamp, 'reason' => 'reason']
    ]
]
```

## Permission Check

```php
// Check if user can manage group criteria
if ( bgr_user_can_manage_group_criteria( $group_id ) ) {
    // User is site admin, group admin, or group creator
}
```

## Common Patterns

### Display Review Form Criteria

```php
$group_id = bp_get_current_group_id();
$criteria = bgr_get_effective_criteria( $group_id );

foreach ( $criteria as $name ) {
    echo '<label>' . esc_html( $name ) . '</label>';
    echo '<select name="rating_' . sanitize_key( $name ) . '">';
    for ( $i = 1; $i <= 5; $i++ ) {
        echo '<option value="' . $i . '">' . $i . '</option>';
    }
    echo '</select>';
}
```

### Show Group's Rating Averages

```php
$averages = bgr_group_criteria()->calculate_group_averages( $group_id );

foreach ( $averages as $name => $data ) {
    echo '<div>';
    echo '<strong>' . esc_html( $name ) . ':</strong> ';
    echo esc_html( $data['average'] ) . '/5 ';
    echo '(' . esc_html( $data['count'] ) . ' reviews)';
    echo '</div>';
}
```

### Display Review with Historical Criteria

```php
$criteria_manager = bgr_group_criteria();
$ratings = get_post_meta( $review_id, 'review_star_rating', true );
$group_id = get_post_meta( $review_id, 'linked_group', true );

foreach ( $ratings as $name => $value ) {
    $is_archived = $criteria_manager->is_criteria_archived( $group_id, $name );

    echo '<div class="rating">';
    echo esc_html( $name ) . ': ' . esc_html( $value ) . '/5';

    if ( $is_archived ) {
        echo ' <em>(archived)</em>';
    }

    echo '</div>';
}
```

## Troubleshooting Quick Fixes

### Group admin can't see Review Criteria tab

**Check:**
1. Is feature enabled? (BuddyPress → Group Reviews → General Settings)
2. Is group excluded from reviews?
3. Is user actually a group admin?

### Custom criterion won't delete

**Reason:** Reviews exist with this criterion

**Solution:** It's archived automatically - expected behavior

### Changes not applying

**Check:**
1. Did you click Save?
2. Did you get success message?
3. Try switching modes and back
4. Clear browser cache

### Inherit mode not showing site changes

**Check:**
- Group must be in Inherit mode (not Customize)
- Verify at Group → Manage → Review Criteria

## File Locations

```
includes/
├── class-bgr-group-criteria.php          # Core class
├── bgr-group-criteria-functions.php      # Helper functions
├── bgr-group-criteria-ajax.php           # AJAX handlers
├── bgr-grp-extn.php                      # Group extension registration
└── templates/
    └── bgr-group-criteria-settings.php   # Admin template
```

## Constants

```php
// Class constants
BGR_Group_Criteria::META_KEY        // 'bgr_group_criteria_settings'
BGR_Group_Criteria::MODE_INHERIT    // 'inherit'
BGR_Group_Criteria::MODE_OVERRIDE   // 'override'
```

## Support Links

- Full Admin Guide: [admin-guide-group-level-criteria.md](admin-guide-group-level-criteria.md)
- Group Admin Guide: [group-admin-guide-review-criteria.md](group-admin-guide-review-criteria.md)
- Developer API: [developer-api-group-criteria.md](developer-api-group-criteria.md)
- Main Documentation: [README.md](README.md)
