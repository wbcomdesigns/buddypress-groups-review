# Group-Level Criteria API Reference

Developer documentation for the group-level review criteria system introduced in v3.7.0.

## Overview

The group-level criteria system allows groups to override site-wide review criteria. It includes:

- Class-based architecture (`BGR_Group_Criteria`)
- Helper functions for common operations
- AJAX handlers for admin interface
- REST API endpoints
- WordPress hooks for extensibility

## Core Class

### BGR_Group_Criteria

Singleton class managing group criteria settings.

**File:** `includes/class-bgr-group-criteria.php`

**Access:**
```php
$criteria = BGR_Group_Criteria::get_instance();
// Or use helper
$criteria = bgr_group_criteria();
```

## Key Functions

### Getting Criteria

#### `bgr_get_effective_criteria( $group_id )`

Get active criteria for a group, respecting inheritance and overrides.

**Parameters:**
- `$group_id` (int) Group ID

**Returns:** (array) List of active criteria names

**Example:**
```php
$group_id = bp_get_current_group_id();
$criteria = bgr_get_effective_criteria( $group_id );
// Returns: ['Quality', 'Relevance', 'Custom Criterion']
```

**Since:** 3.7.0

---

#### `bgr_is_using_custom_criteria( $group_id )`

Check if group is in customize mode.

**Parameters:**
- `$group_id` (int) Group ID

**Returns:** (bool) True if using custom criteria

**Example:**
```php
if ( bgr_is_using_custom_criteria( $group_id ) ) {
    echo 'This group has custom criteria';
}
```

**Since:** 3.7.0

---

#### `bgr_get_criteria_mode( $group_id )`

Get the criteria mode for a group.

**Parameters:**
- `$group_id` (int) Group ID

**Returns:** (string) 'inherit' or 'override'

**Example:**
```php
$mode = bgr_get_criteria_mode( $group_id );
if ( 'inherit' === $mode ) {
    // Using site-wide criteria
}
```

**Since:** 3.7.0

## Class Methods

### Settings Management

#### `get_group_settings( $group_id )`

Get all criteria settings for a group.

**Returns:** (array)
```php
[
    'mode' => 'inherit|override',
    'enabled_global_criteria' => ['Quality', 'Relevance'],
    'custom_criteria' => [
        ['name' => 'Custom', 'created' => 1234567890, 'active' => true]
    ],
    'archived_criteria' => [
        'Old Criterion' => ['archived_at' => 1234567890, 'reason' => 'disabled_by_group_admin']
    ]
]
```

**Since:** 3.7.0

---

#### `save_group_settings( $group_id, $settings )`

Save criteria settings for a group.

**Parameters:**
- `$group_id` (int) Group ID
- `$settings` (array) Settings array (auto-sanitized)

**Returns:** (bool) Success status

**Example:**
```php
$criteria = bgr_group_criteria();
$settings = $criteria->get_group_settings( $group_id );
$settings['mode'] = 'override';
$criteria->save_group_settings( $group_id, $settings );
```

**Since:** 3.7.0

---

#### `set_criteria_mode( $group_id, $mode )`

Set inherit or override mode.

**Parameters:**
- `$group_id` (int) Group ID
- `$mode` (string) 'inherit' or 'override'

**Returns:** (bool) Success status

**Example:**
```php
bgr_group_criteria()->set_criteria_mode( $group_id, 'override' );
```

**Since:** 3.7.0

### Custom Criteria

#### `add_custom_criteria( $group_id, $name )`

Add custom criterion to a group.

**Parameters:**
- `$group_id` (int) Group ID
- `$name` (string) Criterion name (max 50 chars)

**Returns:** (bool) Success status (false if duplicate exists)

**Example:**
```php
$result = bgr_group_criteria()->add_custom_criteria( 123, 'Code Quality' );
if ( ! $result ) {
    // Criterion already exists
}
```

**Since:** 3.7.0

---

#### `archive_custom_criteria( $group_id, $name, $reason )`

Archive (soft delete) a custom criterion.

**Parameters:**
- `$group_id` (int) Group ID
- `$name` (string) Criterion name
- `$reason` (string) Archive reason (default: 'disabled_by_group_admin')

**Returns:** (bool) Success status

**Example:**
```php
bgr_group_criteria()->archive_custom_criteria( 123, 'Old Criterion', 'no_longer_relevant' );
```

**Since:** 3.7.0

---

#### `delete_custom_criteria( $group_id, $name )`

Delete custom criterion. Auto-archives if reviews exist.

**Parameters:**
- `$group_id` (int) Group ID
- `$name` (string) Criterion name

**Returns:** (bool) Success status

**Example:**
```php
// Hard delete if no reviews, soft delete (archive) if reviews exist
bgr_group_criteria()->delete_custom_criteria( 123, 'Unused Criterion' );
```

**Since:** 3.7.0

### Global Criteria

#### `toggle_global_criteria( $group_id, $name, $enabled )`

Enable/disable a global criterion for a group.

**Parameters:**
- `$group_id` (int) Group ID
- `$name` (string) Criterion name
- `$enabled` (bool) Enable or disable

**Returns:** (bool) Success status

**Example:**
```php
// Disable "Relevance" for this group
bgr_group_criteria()->toggle_global_criteria( 123, 'Relevance', false );
```

**Since:** 3.7.0

---

#### `get_global_active_criteria()`

Get all active site-wide criteria.

**Returns:** (array) List of active global criteria names

**Example:**
```php
$global = bgr_group_criteria()->get_global_active_criteria();
// Returns: ['Quality', 'Relevance', 'Engagement']
```

**Since:** 3.7.0

---

#### `get_global_all_criteria()`

Get all defined global criteria (active and inactive).

**Returns:** (array) List of all global criteria names

**Since:** 3.7.0

### Data Analysis

#### `calculate_group_averages( $group_id )`

Calculate average ratings per criterion for a group.

**Parameters:**
- `$group_id` (int) Group ID

**Returns:** (array) Criteria averages
```php
[
    'Quality' => ['average' => 4.5, 'count' => 10],
    'Custom' => ['average' => 3.8, 'count' => 8]
]
```

**Example:**
```php
$averages = bgr_group_criteria()->calculate_group_averages( 123 );
foreach ( $averages as $name => $data ) {
    echo "$name: {$data['average']} ({$data['count']} reviews)";
}
```

**Since:** 3.7.0

---

#### `calculate_overall_average( $group_id )`

Calculate overall average rating across all criteria.

**Parameters:**
- `$group_id` (int) Group ID

**Returns:** (float) Overall average rating

**Example:**
```php
$overall = bgr_group_criteria()->calculate_overall_average( 123 );
echo "Overall rating: $overall / 5";
```

**Since:** 3.7.0

---

#### `criterion_has_reviews( $group_id, $criteria_name )`

Check if any reviews use a specific criterion.

**Parameters:**
- `$group_id` (int) Group ID
- `$criteria_name` (string) Criterion name

**Returns:** (bool) True if reviews exist

**Example:**
```php
if ( bgr_group_criteria()->criterion_has_reviews( 123, 'Old Criterion' ) ) {
    // Can't hard delete - must archive
}
```

**Since:** 3.7.0

## AJAX Handlers

All AJAX actions require nonce verification and capability checks.

**Nonce:** `bgr_group_criteria_nonce`

### `bgr_save_group_criteria_settings`

Save group criteria settings (mode and enabled global criteria).

**POST Data:**
- `nonce` (string) Nonce value
- `group_id` (int) Group ID
- `mode` (string) 'inherit' or 'override'
- `enabled_global_criteria` (array) List of enabled global criteria names

**Response:**
```json
{
  "success": true,
  "data": {
    "message": "Criteria settings saved successfully.",
    "criteria": ["Quality", "Custom"]
  }
}
```

**Since:** 3.7.0

---

### `bgr_add_custom_criteria`

Add custom criterion via AJAX.

**POST Data:**
- `nonce` (string)
- `group_id` (int)
- `criteria_name` (string) Max 50 chars

**Response:**
```json
{
  "success": true,
  "data": {
    "message": "Custom criterion added successfully.",
    "criteria": ["Quality", "New Custom"]
  }
}
```

**Since:** 3.7.0

---

### `bgr_archive_custom_criteria`

Archive custom criterion via AJAX.

**POST Data:**
- `nonce` (string)
- `group_id` (int)
- `criteria_name` (string)

**Since:** 3.7.0

---

### `bgr_toggle_global_criteria`

Toggle global criterion on/off for group.

**POST Data:**
- `nonce` (string)
- `group_id` (int)
- `criteria_name` (string)
- `enabled` (string) 'true' or 'false'

**Since:** 3.7.0

## REST API

**Base:** `/wp-json/bgr/v1/`

### GET `/groups/{id}/criteria`

Get group criteria information.

**Permission:** Logged-in users only. Logged-out requests get `401`. For a public group any logged-in user can read it. For a private or hidden group you must be a group member or have `manage_options`. An unknown group ID is also refused.

**Response:**
```json
{
  "group_id": 123,
  "mode": "override",
  "settings": {...},
  "effective": ["Quality", "Custom"],
  "averages": {...}
}
```

**Example:**
```javascript
// Send the REST nonce so WordPress knows who you are (wp_create_nonce( 'wp_rest' )).
fetch('/wp-json/bgr/v1/groups/123/criteria', {
  credentials: 'same-origin',
  headers: { 'X-WP-Nonce': wpApiSettings.nonce }
})
  .then(res => res.json())
  .then(data => console.log(data.effective));
```

**Since:** 3.7.0

---

### POST `/groups/{id}/criteria`

Update group criteria settings.

**Permission:** Group admin or site admin

**Body:**
```json
{
  "mode": "override",
  "settings": {
    "enabled_global_criteria": ["Quality"]
  }
}
```

**Response:**
```json
{
  "success": true,
  "group_id": 123,
  "effective": ["Quality"]
}
```

**Since:** 3.7.0

## WordPress Hooks

### Actions

#### `bgr_global_criteria_deleted`

Fires when a global criterion is deleted by site admin.

**Parameters:**
- `$criteria_name` (string) The deleted criterion name

**Example:**
```php
add_action( 'bgr_global_criteria_deleted', function( $criteria_name ) {
    error_log( "Global criterion deleted: $criteria_name" );
}, 10, 1 );
```

**Since:** 3.7.0

---

#### `bgr_global_criteria_archived`

Fires when a global criterion is archived by site admin.

**Parameters:**
- `$criteria_name` (string) The archived criterion name

**Example:**
```php
add_action( 'bgr_global_criteria_archived', function( $criteria_name ) {
    // Notify group admins using this criterion
}, 10, 1 );
```

**Since:** 3.7.0

### Filters

The group criteria system has no filters of its own. The 50-character limit on custom criteria names is fixed in `includes/bgr-group-criteria-ajax.php` and cannot be changed with a hook.

## Database Schema

### Group Meta

**Key:** `bgr_group_criteria_settings`

**Structure:**
```php
[
    'mode' => 'inherit', // or 'override'
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
            'reason' => 'global_criteria_deleted'
        ]
    ]
]
```

**Stored in:** `{$wpdb->prefix}bp_groups_groupmeta`

## Capabilities

Check if user can manage group criteria:

```php
function bgr_user_can_manage_group_criteria( $group_id );
```

**Returns true if:**
- User is site admin (`manage_options`)
- User is group admin (`groups_is_user_admin()`)
- User is group creator

**Since:** 3.7.0

## Integration Examples

### Display Group's Active Criteria

```php
function display_group_criteria( $group_id ) {
    $criteria = bgr_get_effective_criteria( $group_id );

    echo '<h3>Rate this group:</h3><ul>';
    foreach ( $criteria as $name ) {
        echo '<li>' . esc_html( $name ) . '</li>';
    }
    echo '</ul>';
}
```

### Add Custom Criterion Programmatically

```php
function setup_book_club_criteria( $group_id ) {
    $criteria = bgr_group_criteria();

    // Switch to override mode
    $criteria->set_criteria_mode( $group_id, 'override' );

    // Add book-specific criteria
    $criteria->add_custom_criteria( $group_id, 'Book Selection' );
    $criteria->add_custom_criteria( $group_id, 'Discussion Quality' );

    // Enable only relevant global criteria
    $criteria->toggle_global_criteria( $group_id, 'Quality', true );
    $criteria->toggle_global_criteria( $group_id, 'Engagement', true );
    $criteria->toggle_global_criteria( $group_id, 'Relevance', false );
}
```

### Display Ratings with Historical Criteria

```php
function display_review_ratings( $review_id ) {
    $criteria = bgr_group_criteria();
    $ratings = get_post_meta( $review_id, 'review_star_rating', true );
    $group_id = get_post_meta( $review_id, 'linked_group', true );

    if ( ! is_array( $ratings ) ) {
        return;
    }

    foreach ( $ratings as $name => $value ) {
        $is_archived = $criteria->is_criteria_archived( $group_id, $name );

        echo '<div class="rating-item' . ( $is_archived ? ' archived' : '' ) . '">';
        echo esc_html( $name ) . ': ' . esc_html( $value ) . '/5';

        if ( $is_archived ) {
            echo ' <span class="archived-badge">(No longer active)</span>';
        }

        echo '</div>';
    }
}
```

## Performance Notes

**Caching:** Group settings are stored in group meta. BuddyPress handles caching.

**Queries:** The system uses direct group meta queries. No additional database tables.

**Impact:** Minimal. Criteria are loaded once per page load when displaying reviews.

## Migration Notes

**Upgrading from earlier versions:**
- Existing groups default to 'inherit' mode
- No data migration needed
- Reviews display correctly with previous criteria

**Downgrading:**
- Group meta is preserved but ignored
- Groups fall back to global criteria
- No data loss occurs

## Constants

### Class Constants

```php
BGR_Group_Criteria::META_KEY        // 'bgr_group_criteria_settings'
BGR_Group_Criteria::MODE_INHERIT    // 'inherit'
BGR_Group_Criteria::MODE_OVERRIDE   // 'override'
```

## Support

**File locations:**
- Core class: `includes/class-bgr-group-criteria.php`
- Helper functions: `includes/bgr-group-criteria-functions.php`
- AJAX handlers: `includes/bgr-group-criteria-ajax.php`
- Group extension: `includes/bgr-grp-extn.php`
- Admin template: `includes/templates/bgr-group-criteria-settings.php`

**Debugging:**

Enable WordPress debug mode to see criteria-related errors:
```php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
```
