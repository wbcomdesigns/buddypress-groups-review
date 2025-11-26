# BuddyBoss Compatibility

## Notification System Compatibility

### Issue Fixed
Review notifications were not clickable in BuddyBoss because of a bug in the notification callback function.

### Root Cause
1. Early `return` statement inside the switch block (line 381)
2. Function returned before switch completed
3. BuddyBoss received no return value, so notifications had no link

### Solution
The notification callback function is now fully compatible with both BuddyPress and BuddyBoss:

#### Function Signature
```php
function bp_group_review_format_notifications( 
    $action, 
    $item_id, 
    $secondary_item_id, 
    $user_id, 
    $format = 'string', 
    $id = 0, 
    $screen = 'web'  // 7th parameter for BuddyBoss
)
```

#### BuddyPress Compatibility
- **Calls with:** 6 parameters (no $screen)
- **Format:** 'array' or 'string'
- **Returns:** 
  - String: `<a href='...'>...</a>`
  - Array: `['text' => '...', 'link' => '...']`
- **Uses:** `bp-notifications/bp-notifications-functions.php` lines 228, 247

#### BuddyBoss Compatibility  
- **Calls with:** 7 parameters (includes $screen)
- **Format:** 'array' or 'string'
- **Returns:** Same as BuddyPress
- **Uses:** `buddyboss-platform/bp-notifications/bp-notifications-functions.php` lines 1499, 1534

### Testing
Both platforms expect the same array structure:
```php
array(
    'text' => 'Notification text',  // Required
    'link' => 'https://...',         // Required
    'title' => 'Title',              // Optional (BuddyBoss only)
    'image' => 'https://...'         // Optional (BuddyBoss only)
)
```

### Files Modified
- `includes/bgr-notifications.php` - Fixed callback return logic
- `includes/bgr-schema.php` - Fixed group URL generation
