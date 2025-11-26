# BuddyPress Group Reviews - Developer Guide

## Table of Contents

1. [Introduction](#introduction)
2. [Plugin Architecture](#plugin-architecture)
3. [Action Hooks Reference](#action-hooks-reference)
4. [Filter Hooks Reference](#filter-hooks-reference)
5. [Function Reference](#function-reference)
6. [REST API Integration](#rest-api-integration)
7. [Database Structure](#database-structure)
8. [Custom Post Type](#custom-post-type)
9. [Extending the Plugin](#extending-the-plugin)
10. [Code Examples](#code-examples)
11. [Best Practices](#best-practices)

---

## Introduction

This developer guide provides technical documentation for extending and customizing the BuddyPress Group Reviews plugin. It includes all available hooks, filters, functions, and integration points.

**Plugin Version:** 3.5.0
**Minimum PHP:** 7.4
**Minimum WordPress:** 4.0
**Requires BuddyPress:** 5.0+

**Key Features for Developers:**
- Extensive hook system for customization
- BuddyPress REST API integration
- Schema.org structured data output
- WCAG 2.1 AA accessibility compliant
- Custom post type architecture
- Widget API integration
- GamiPress integration support

---

## Plugin Architecture

### Directory Structure

```
buddypress-groups-review/
├── admin/
│   ├── bgr-admin.php                    # Admin settings and capabilities
│   ├── bgr-admin-feedback.php           # Feedback system
│   ├── bgr-welcome-page.php            # Welcome screen
│   └── wbcom/                          # Wbcom framework integration
│
├── assets/
│   ├── css/                            # Stylesheets
│   ├── js/                             # JavaScript files
│   │   ├── bgr-accessibility.js        # WCAG accessibility features
│   │   └── ...
│   └── images/                         # Plugin images
│
├── includes/
│   ├── bgr-globals.php                 # Global variables and helper functions
│   ├── bgr-filters.php                 # Core BuddyPress integration
│   ├── bgr-ajax.php                    # AJAX handlers
│   ├── bgr-activity.php                # BuddyPress activity integration
│   ├── bgr-notifications.php           # BuddyPress notifications
│   ├── bgr-shortcodes.php              # Shortcode handlers
│   ├── bgr-functions.php               # Utility functions
│   ├── bgr-scripts.php                 # Script/style enqueuing
│   ├── bgr-dynamic-css.php             # Dynamic CSS generation
│   ├── bgr-rating-display.php          # Rating display logic
│   ├── bgr-schema.php                  # Schema.org JSON-LD output
│   ├── bgr-bp-rest-integration.php     # BuddyPress REST API integration
│   ├── bgr-grp-extn.php                # BP Group Extension class
│   ├── class-bgr-multi-support.php     # Multisite support
│   ├── templates/                      # Template files
│   └── widgets/                        # Widget classes
│
├── languages/                          # Translation files
├── docs/                               # Documentation
├── uninstall.php                       # Clean uninstall handler
└── review-bp-grps.php                  # Main plugin file
```

### Class Structure

**Main Classes:**
- `BGR_Custom_Hooks` - Core hooks and filters registration
- `Group_Reviews_Management_Extn` - BuddyPress Group Extension
- `BGR_Activity` - Activity stream integration
- `BGR_Notifications` - Notification system
- `BGR_Shortcodes` - Shortcode functionality
- `BGR_Schema` - Schema.org structured data
- `BGR_BP_REST_Integration` - BuddyPress REST API extension
- `BGR_Multi_Support` - Multisite network support

### Global Variable

The plugin uses a global `$bgr` variable for storing settings:

```php
global $bgr;

$bgr = array(
    'review_label'           => 'Reviews',          // Review tab label
    'manage_review_label'    => 'Manage Reviews',   // Admin tab label
    'review_rating_fields'   => array(),            // Available criteria
    'active_rating_fields'   => array(),            // Enabled criteria
    'reviews_per_page'       => 10,                 // Pagination limit
    'multi_reviews'          => 'no',               // Allow multiple reviews
    'auto_approve_reviews'   => 'yes',              // Auto-approve setting
    'exclude_groups'         => array(),            // Excluded group IDs
    'allow_email'            => 'yes',              // Email notifications
    'allow_notification'     => 'yes',              // BP notifications
    'allow_activity'         => 'yes',              // Activity stream posts
    'rating_color'           => '#f90',             // Star color
);
```

---

## Action Hooks Reference

### Review Lifecycle Hooks

#### `bgr_group_after_review_submit`

Fires after a review has been successfully submitted.

**Parameters:**
- `int $author_id` - User ID of review author
- `int $group_id` - BuddyPress group ID
- `int $review_id` - Review post ID

**Example:**
```php
add_action( 'bgr_group_after_review_submit', 'my_review_submitted_action', 10, 3 );

function my_review_submitted_action( $author_id, $group_id, $review_id ) {
    // Send custom notification
    // Award points
    // Log to analytics
    error_log( "User {$author_id} reviewed group {$group_id}" );
}
```

**Use Cases:**
- Send custom notifications to group admins
- Award points in a gamification system
- Trigger email campaigns
- Log analytics data
- Sync with third-party services

---

#### `bgr_group_accept_review`

Fires when a group admin approves a review.

**Parameters:**
- `int $review_id` - Review post ID being approved

**Example:**
```php
add_action( 'bgr_group_accept_review', 'my_review_approved_action' );

function my_review_approved_action( $review_id ) {
    $author_id = get_post_field( 'post_author', $review_id );
    $group_id  = get_post_meta( $review_id, 'linked_group', true );

    // Send notification to author
    // Update group statistics
    // Trigger webhooks
}
```

**Location:** `includes/bgr-ajax.php:557`

---

#### `bgr_group_deny_review`

Fires when a group admin denies/rejects a review.

**Parameters:**
- `int $review_id` - Review post ID being denied

**Example:**
```php
add_action( 'bgr_group_deny_review', 'my_review_denied_action' );

function my_review_denied_action( $review_id ) {
    $author_id = get_post_field( 'post_author', $review_id );

    // Send notification to author explaining denial
    // Log moderation action
    // Update moderation statistics
}
```

**Location:** `includes/bgr-ajax.php:645`

---

#### `bgr_group_review_after_review_insert`

Fires immediately after a review post is inserted into database.

**Parameters:** None

**Example:**
```php
add_action( 'bgr_group_review_after_review_insert', 'my_post_insert_action' );

function my_post_insert_action() {
    // Clear caches
    // Update counters
    // Sync external systems
}
```

**Location:** `includes/bgr-ajax.php:404`

---

#### `bgr_group_add_review`

Fires when adding a review (email notification context).

**Parameters:**
- `int $group_id` - BuddyPress group ID
- `int $admin_id` - Group admin user ID receiving notification

**Example:**
```php
add_action( 'bgr_group_add_review', 'my_review_notification', 10, 2 );

function my_review_notification( $group_id, $admin_id ) {
    // Send SMS to admin
    // Post to Slack channel
    // Create mobile push notification
}
```

**Location:** `includes/bgr-ajax.php:465`

---

### Display Hooks

#### `bgr_display_ratings`

Displays star ratings for a specific review.

**Parameters:**
- `int $review_id` - Review post ID

**Example:**
```php
// Display ratings in custom template
do_action( 'bgr_display_ratings', $review_id );

// Add custom content after ratings
add_action( 'bgr_display_ratings', 'my_custom_rating_info', 20 );

function my_custom_rating_info( $review_id ) {
    $ratings = get_post_meta( $review_id, 'review_star_rating', true );
    $average = array_sum( $ratings ) / count( $ratings );

    if ( $average >= 4.5 ) {
        echo '<span class="badge-excellent">Excellent!</span>';
    }
}
```

**Location:** Multiple template files

---

#### `bgr_display_group_average_ratings`

Displays average star ratings for a group.

**Parameters:**
- `float $avg_rating` - Average rating value (0-5)

**Example:**
```php
// Customize average rating display
add_action( 'bgr_display_group_average_ratings', 'my_custom_avg_display', 5 );

function my_custom_avg_display( $avg_rating ) {
    // Add custom HTML around rating stars
    echo '<div class="my-rating-wrapper">';
    // Plugin will output stars here
}

add_action( 'bgr_display_group_average_ratings', 'my_custom_avg_display_after', 15 );

function my_custom_avg_display_after( $avg_rating ) {
    echo '</div>';

    // Add badge for highly rated groups
    if ( $avg_rating >= 4.8 ) {
        echo '<img src="/badge-top-rated.png" alt="Top Rated">';
    }
}
```

**Location:** `includes/bgr-filters.php:282`, `includes/bgr-filters.php:385`

---

#### `bgr_display_widget_average_ratings`

Displays average ratings in widget context.

**Parameters:**
- `float $avg_rating` - Average rating value (0-5)

**Example:**
```php
add_action( 'bgr_display_widget_average_ratings', 'my_widget_rating_addon' );

function my_widget_rating_addon( $avg_rating ) {
    // Add review count next to stars in widget
    $review_count = get_reviews_count_for_group( $group_id );
    echo sprintf( '<span class="review-count">(%d reviews)</span>', $review_count );
}
```

**Location:** `includes/widgets/bgr-review.php:265`

---

### GamiPress Integration Hooks

#### `gamipress_bp_group_review`

Triggers GamiPress achievements and points when user reviews a group.

**Parameters:**
- `int $user_id` - User ID who submitted the review

**Example:**
```php
// This is automatically triggered if GamiPress is active
// Define custom triggers in GamiPress settings

// Example: Award extra points for detailed reviews
add_action( 'bgr_group_after_review_submit', 'award_detailed_review_bonus', 10, 3 );

function award_detailed_review_bonus( $author_id, $group_id, $review_id ) {
    $review_content = get_post_field( 'post_content', $review_id );

    if ( str_word_count( $review_content ) > 100 ) {
        // Award bonus points via GamiPress
        gamipress_award_points_to_user( $author_id, 50, 'points', array(
            'reason' => 'Detailed group review'
        ) );
    }
}
```

**Location:** `includes/bgr-ajax.php:196`, `includes/bgr-ajax.php:401`, `includes/bgr-ajax.php:565`

---

### BuddyPress Activity Hooks

#### `bp_register_activity_actions`

Registers custom activity types for group reviews.

**Example:**
```php
// Plugin automatically registers 'bgr_group_posts_activity_action' type
// You can register additional custom activity types:

add_action( 'bp_register_activity_actions', 'my_custom_review_activity' );

function my_custom_review_activity() {
    bp_activity_set_action(
        buddypress()->members->id,
        'my_featured_review',
        __( 'Featured Review', 'my-textdomain' ),
        'my_format_featured_review_action',
        __( 'Featured Reviews', 'my-textdomain' ),
        array( 'groups' )
    );
}
```

---

### Admin/Plugin Integration Hooks

#### `wbcom_add_plugin_license_code`

Adds license input fields for premium version.

**Example:**
```php
add_action( 'wbcom_add_plugin_license_code', 'my_custom_license_field' );

function my_custom_license_field() {
    // Add custom license validation
    // Integrate with your own licensing system
}
```

**Location:** `admin/wbcom/templates/wbcom-license-page.php:34`

---

#### `wbcom_add_header_menu`

Adds custom menu items to plugin admin header.

**Example:**
```php
add_action( 'wbcom_add_header_menu', 'my_custom_admin_menu' );

function my_custom_admin_menu() {
    echo '<li><a href="my-page">Custom Reports</a></li>';
}
```

**Location:** `admin/wbcom/wbcom-admin-settings.php:342`

---

## Filter Hooks Reference

### Tab & Label Filters

#### `bp_group_review_tab_name`

Filters the review tab name displayed in group navigation.

**Parameters:**
- `string $tab_name` - Default tab name (from settings or "Reviews")

**Returns:** `string` - Modified tab name

**Example:**
```php
add_filter( 'bp_group_review_tab_name', 'my_custom_tab_name' );

function my_custom_tab_name( $tab_name ) {
    // Rename tab based on group type
    $group_id = bp_get_current_group_id();
    $group_type = groups_get_groupmeta( $group_id, 'group_type' );

    if ( $group_type === 'business' ) {
        return 'Client Testimonials';
    }

    return $tab_name;
}
```

**Location:** `includes/bgr-globals.php:136`

---

#### `bp_group_review_add_review_tab_name`

Filters the "Add Review" button/label text.

**Parameters:**
- `string $label` - Default label (from settings or "Review")

**Returns:** `string` - Modified label

**Example:**
```php
add_filter( 'bp_group_review_add_review_tab_name', 'my_custom_add_label' );

function my_custom_add_label( $label ) {
    if ( is_user_logged_in() && bp_is_group_single() ) {
        return 'Share Your Experience';
    }
    return $label;
}
```

**Location:** `includes/bgr-globals.php:144`

---

#### `bp_group_review_tab_slug`

Filters the URL slug for review tab.

**Parameters:**
- `string $slug` - Default slug (sanitized version of tab name)

**Returns:** `string` - Modified slug

**Example:**
```php
add_filter( 'bp_group_review_tab_slug', 'my_custom_tab_slug' );

function my_custom_tab_slug( $slug ) {
    return 'testimonials'; // Change URL from /groups/slug/reviews to /groups/slug/testimonials
}
```

**Location:** `includes/bgr-globals.php:153`

---

### Navigation Count Filters

#### `bp_group_review_nav_has_count`

Determines if review tab should display count badge.

**Parameters:**
- `bool $has_count` - Whether nav has count
- `object $nav_item` - Navigation item object
- `string $component` - Current component

**Returns:** `bool`

**Example:**
```php
add_filter( 'bp_group_review_nav_has_count', 'my_hide_review_count', 10, 3 );

function my_hide_review_count( $has_count, $nav_item, $component ) {
    // Hide count for private groups
    if ( bp_get_group_status() === 'private' ) {
        return false;
    }
    return $has_count;
}
```

**Location:** `includes/bgr-filters.php:97`

---

#### `buddypress_groups_review_nav_count`

Filters the actual count number displayed in navigation.

**Parameters:**
- `int $count` - Number of published reviews
- `object $nav_item` - Navigation item object
- `string $component` - Current component

**Returns:** `int`

**Example:**
```php
add_filter( 'buddypress_groups_review_nav_count', 'my_modify_count', 10, 3 );

function my_modify_count( $count, $nav_item, $component ) {
    // Show count + pending reviews for group admins
    if ( groups_is_user_admin( bp_loggedin_user_id(), bp_get_current_group_id() ) ) {
        $pending = get_pending_reviews_count( bp_get_current_group_id() );
        return $count + $pending;
    }
    return $count;
}
```

**Location:** `includes/bgr-filters.php:134`

---

### Display & Content Filters

#### `bgr_filter_group_header_rating_details`

Filters the rating details HTML displayed in group header.

**Parameters:**
- `string $content` - Default HTML content
- `float $avg_rating` - Average rating (0-5)
- `string $review_label` - Review label text
- `int $total_reviews` - Total number of reviews
- `int $group_id` - BuddyPress group ID

**Returns:** `string` - Modified HTML

**Example:**
```php
add_filter( 'bgr_filter_group_header_rating_details', 'my_custom_rating_html', 10, 5 );

function my_custom_rating_html( $content, $avg_rating, $review_label, $total_reviews, $group_id ) {
    // Custom HTML with icon and tooltip
    $html = sprintf(
        '<div class="custom-rating" data-tooltip="Based on %d %s">
            <i class="star-icon"></i>
            <strong>%.1f</strong> / 5
            <span class="review-count">(%d)</span>
        </div>',
        $total_reviews,
        $review_label,
        $avg_rating,
        $total_reviews
    );

    return $html;
}
```

**Location:** `includes/bgr-filters.php:394`

---

#### `bp_core_template_plugin`

Filters the template file path for review screens.

**Parameters:**
- `string $template` - Template file name (without .php)

**Returns:** `string`

**Example:**
```php
add_filter( 'bp_core_template_plugin', 'my_custom_review_template' );

function my_custom_review_template( $template ) {
    if ( bp_is_group() && bp_is_current_action( 'reviews' ) ) {
        // Use custom template from theme
        return 'custom-templates/group-reviews';
    }
    return $template;
}
```

**Location:** `includes/bgr-filters.php:582`, `includes/bgr-filters.php:584`

---

### Activity Stream Filters

#### `bgr_activity_action`

Filters the activity action text when review is posted.

**Parameters:**
- `string $action_text` - Default action string (e.g., "%1$s posted a review in the group %2$s")

**Returns:** `string`

**Example:**
```php
add_filter( 'bgr_activity_action', 'my_custom_activity_text' );

function my_custom_activity_text( $action_text ) {
    // More engaging activity text
    return '%1$s shared their experience with %2$s';
}
```

**Location:** `includes/bgr-activity.php:153`, `includes/bgr-activity.php:218`

---

#### `bp_group_review_group_create_posts_activity_action`

Filters the formatted activity action for review posts.

**Parameters:**
- `string $action` - Formatted action HTML with user/group links

**Returns:** `string`

**Example:**
```php
add_filter( 'bp_group_review_group_create_posts_activity_action', 'my_activity_action' );

function my_activity_action( $action ) {
    // Add emoji or icon
    return '⭐ ' . $action;
}
```

**Location:** `includes/bgr-activity.php:117`

---

### Review Form Filters

#### `bgr_review_form_group_status`

Filters which group statuses are included in review form group selector.

**Parameters:**
- `array $statuses` - Array of group statuses

**Returns:** `array`

**Default:** `array( 'public', 'hidden' )`

**Example:**
```php
add_filter( 'bgr_review_form_group_status', 'my_reviewable_groups' );

function my_reviewable_groups( $statuses ) {
    // Also allow reviewing private groups
    $statuses[] = 'private';
    return $statuses;
}
```

**Location:** `includes/bgr-shortcodes.php:167`

---

### Notification Filters

#### `bp_group_review_add_review_notification_format`

Filters the notification text when new review is submitted.

**Parameters:**
- `string $return` - Notification HTML or text
- `int $group_id` - BuddyPress group ID
- `string $format` - Format type ('string' or 'html')

**Returns:** `string`

**Example:**
```php
add_filter( 'bp_group_review_add_review_notification_format', 'my_review_notification', 10, 3 );

function my_review_notification( $return, $group_id, $format ) {
    if ( $format === 'string' ) {
        $group_name = bp_get_group_name( groups_get_group( $group_id ) );
        return "New review on {$group_name} - check it out!";
    }
    return $return;
}
```

**Location:** `includes/bgr-notifications.php:263`

---

#### `bp_group_review_accept_review_notification_format`

Filters notification when review is approved.

**Parameters:**
- `string $return` - Notification HTML or text
- `int $group_id` - BuddyPress group ID
- `string $format` - Format type

**Returns:** `string`

**Example:**
```php
add_filter( 'bp_group_review_accept_review_notification_format', 'my_approved_notification', 10, 3 );

function my_approved_notification( $return, $group_id, $format ) {
    if ( $format === 'string' ) {
        return "Your review was approved! Thanks for sharing.";
    }
    return $return;
}
```

**Location:** `includes/bgr-notifications.php:302`

---

#### `bp_group_review_deny_review_notification_format`

Filters notification when review is denied.

**Parameters:**
- `string $return` - Notification HTML or text
- `int $group_id` - BuddyPress group ID
- `string $format` - Format type

**Returns:** `string`

**Example:**
```php
add_filter( 'bp_group_review_deny_review_notification_format', 'my_denied_notification', 10, 3 );

function my_denied_notification( $return, $group_id, $format ) {
    if ( $format === 'string' ) {
        return "Your review didn't meet our guidelines. Please review our policy.";
    }
    return $return;
}
```

**Location:** `includes/bgr-notifications.php:342`

---

### Widget Filters

#### `bgr_widget_title`

Filters widget title.

**Parameters:**
- `string $title` - Widget title

**Returns:** `string`

**Example:**
```php
add_filter( 'bgr_widget_title', 'my_widget_title' );

function my_widget_title( $title ) {
    return '<span class="widget-icon">⭐</span> ' . $title;
}
```

**Location:** `includes/widgets/bgr-review.php:141`

---

#### `bgr_listing_cat`

Filters widget listing category/filter.

**Parameters:**
- `string $category` - Listing category

**Returns:** `string`

**Location:** `includes/widgets/bgr-review.php:142`

---

#### `bgr_post_num`

Filters number of posts shown in widget.

**Parameters:**
- `int $number` - Number of reviews to display

**Returns:** `int`

**Location:** `includes/widgets/bgr-review.php:143`, `includes/widgets/group-rating.php:119`

---

#### `bgr_thumbnail`

Filters whether to show thumbnails in widget.

**Parameters:**
- `bool $show_thumbnail` - Whether to show thumbnails

**Returns:** `bool`

**Location:** `includes/widgets/bgr-review.php:144`

---

### Capability Filters

#### `bgr_add_roles_capability`

Filters which roles receive review management capabilities.

**Parameters:**
- `array $roles` - Array of role slugs

**Returns:** `array`

**Default:** `array( 'administrator' )`

**Example:**
```php
add_filter( 'bgr_add_roles_capability', 'my_review_capable_roles' );

function my_review_capable_roles( $roles ) {
    // Allow editors to manage reviews
    $roles[] = 'editor';
    $roles[] = 'shop_manager';
    return $roles;
}
```

**Capabilities Added:**
- `edit_review`
- `read_review`
- `delete_review`
- `edit_reviews`
- `edit_others_reviews`
- `publish_reviews`
- `read_private_reviews`
- `delete_reviews`
- `delete_private_reviews`
- `delete_published_reviews`
- `delete_others_reviews`
- `edit_private_reviews`
- `edit_published_reviews`

**Location:** `admin/bgr-admin.php:290`

---

## Function Reference

### Helper Functions

#### `bp_group_review_tab_name()`

Gets the review tab label from settings.

**Returns:** `string` - Review tab name

**Example:**
```php
$tab_name = bp_group_review_tab_name();
echo 'Visit the ' . $tab_name . ' tab';
```

---

#### `bp_group_review_add_review_tab_name()`

Gets the "Add Review" label from settings.

**Returns:** `string` - Add review label

**Example:**
```php
$label = bp_group_review_add_review_tab_name();
echo '<button>' . $label . '</button>';
```

---

#### `bp_group_review_tab_slug()`

Gets the URL slug for review tab.

**Returns:** `string` - Sanitized slug

**Example:**
```php
$slug = bp_group_review_tab_slug();
$url = bp_get_group_url() . $slug;
```

---

### Template Functions

#### `bp_group_review_remove_add_review_tab()`

Hides the "Add Review" tab for excluded groups via CSS.

**Usage:** Automatically hooked to `wp_head`

**Location:** `includes/bgr-functions.php:9`

---

## REST API Integration

### BuddyPress REST API Extension

The plugin extends the native BuddyPress REST API at `/wp-json/buddypress/v1/`.

#### Review Data in Group Responses

All group endpoints automatically include review statistics:

```http
GET /wp-json/buddypress/v1/groups/123
```

**Response:**
```json
{
  "id": 123,
  "name": "WordPress Developers",
  "reviews": {
    "average_rating": 4.7,
    "total_reviews": 23,
    "ratings": {
      "Quality": 4.8,
      "Engagement": 4.5,
      "Helpfulness": 4.9
    },
    "can_review": true
  }
}
```

#### Review Endpoints

**List Reviews:**
```http
GET /wp-json/buddypress/v1/groups/{group_id}/reviews
```

**Create Review:**
```http
POST /wp-json/buddypress/v1/groups/{group_id}/reviews
Content-Type: application/json

{
  "subject": "Great group!",
  "description": "Very helpful community.",
  "ratings": {
    "Quality": 5,
    "Engagement": 4
  }
}
```

**Get Single Review:**
```http
GET /wp-json/buddypress/v1/groups/{group_id}/reviews/{review_id}
```

**Update Review:**
```http
PUT /wp-json/buddypress/v1/groups/{group_id}/reviews/{review_id}
```

**Delete Review:**
```http
DELETE /wp-json/buddypress/v1/groups/{group_id}/reviews/{review_id}
```

**Full Documentation:** See `docs/BUDDYPRESS-REST-API.md`

---

## Database Structure

### Custom Post Type: `review`

Reviews are stored as WordPress posts with `post_type = 'review'`.

**Post Fields:**
- `post_title` - Review subject/title
- `post_content` - Review description/text
- `post_author` - User ID of reviewer
- `post_status` - `publish` (approved) or `draft` (pending)
- `post_date` - Review submission date

### Custom Taxonomy: `review_category`

Used to categorize reviews (always "Group" for group reviews).

**Term:** `Group`

### Post Meta

**Review Metadata:**

| Meta Key | Type | Description |
|----------|------|-------------|
| `linked_group` | `int` | BuddyPress group ID |
| `review_star_rating` | `array` | Star ratings by criteria |

**Example `review_star_rating` Structure:**
```php
array(
    'Quality'      => 5,
    'Engagement'   => 4,
    'Helpfulness'  => 5,
    'Organization' => 4
)
```

### Options

**Plugin Settings:**

| Option Name | Description |
|-------------|-------------|
| `bgr_admin_general_settings` | General settings array |
| `bgr_admin_criteria_settings` | Review criteria configuration |
| `bgr_admin_display_settings` | Display customization options |
| `bp_group_review_email_settings` | Email notification templates |

---

## Custom Post Type

### Registration

**Post Type:** `review`

**Capabilities:**
```php
'capability_type' => 'review',
'capabilities'    => array(
    'edit_post'          => 'edit_review',
    'read_post'          => 'read_review',
    'delete_post'        => 'delete_review',
    'edit_posts'         => 'edit_reviews',
    'edit_others_posts'  => 'edit_others_reviews',
    'publish_posts'      => 'publish_reviews',
    'read_private_posts' => 'read_private_reviews',
)
```

**Supports:**
- `title`
- `editor`
- `author`

**Taxonomy:** `review_category` (hierarchical)

---

## Extending the Plugin

### Custom Review Criteria

Add/modify review criteria programmatically:

```php
add_filter( 'bgr_admin_criteria_settings', 'add_custom_criteria' );

function add_custom_criteria( $criteria_settings ) {
    // Add new criterion
    $criteria_settings['review_rating_fields'][] = 'Innovation';
    $criteria_settings['active_rating_fields'][] = 'Innovation';

    return $criteria_settings;
}
```

### Custom Review Validation

Validate review content before submission:

```php
add_action( 'bgr_group_after_review_submit', 'validate_review_content', 5, 3 );

function validate_review_content( $author_id, $group_id, $review_id ) {
    $content = get_post_field( 'post_content', $review_id );

    // Check for spam/inappropriate content
    if ( contains_spam( $content ) ) {
        // Mark as spam and notify admin
        wp_update_post( array(
            'ID'          => $review_id,
            'post_status' => 'draft'
        ) );

        add_post_meta( $review_id, 'flagged_spam', true );
    }
}
```

### Custom Email Templates

Customize email notifications:

```php
add_filter( 'bp_group_review_email_subject', 'custom_email_subject', 10, 2 );

function custom_email_subject( $subject, $group_id ) {
    $group_name = bp_get_group_name( groups_get_group( $group_id ) );
    return "⭐ New Review for {$group_name}";
}

add_filter( 'bp_group_review_email_message', 'custom_email_body', 10, 4 );

function custom_email_body( $message, $group_id, $user_id, $review_id ) {
    // Custom HTML email template
    $review_link = get_review_permalink( $review_id );

    ob_start();
    include 'path/to/custom-email-template.php';
    return ob_get_clean();
}
```

### Schema.org Customization

Modify structured data output:

```php
add_filter( 'bgr_schema_output', 'modify_schema_data', 10, 2 );

function modify_schema_data( $schema, $group_id ) {
    // Add additional schema properties
    $schema['@type'] = 'Organization';
    $schema['logo'] = get_group_logo_url( $group_id );
    $schema['contactPoint'] = array(
        '@type' => 'ContactPoint',
        'email' => get_group_email( $group_id )
    );

    return $schema;
}
```

### Custom Widget Output

Modify widget display:

```php
add_filter( 'bgr_widget_review_html', 'custom_widget_review', 10, 2 );

function custom_widget_review( $html, $review ) {
    // Custom review card design
    $custom_html = sprintf(
        '<div class="custom-review-card">
            <div class="review-author">%s</div>
            <div class="review-rating">%s</div>
            <div class="review-excerpt">%s</div>
        </div>',
        get_the_author_meta( 'display_name', $review->post_author ),
        get_review_stars( $review->ID ),
        wp_trim_words( $review->post_content, 20 )
    );

    return $custom_html;
}
```

---

## Code Examples

### Example 1: Award Badges for High Reviews

```php
/**
 * Award badge to groups with 4.5+ average rating
 */
add_action( 'bgr_group_after_review_submit', 'award_excellence_badge', 10, 3 );

function award_excellence_badge( $author_id, $group_id, $review_id ) {
    // Calculate new average
    $avg_rating = calculate_group_average_rating( $group_id );

    if ( $avg_rating >= 4.5 ) {
        // Award badge
        groups_update_groupmeta( $group_id, 'excellence_badge', true );
        groups_update_groupmeta( $group_id, 'badge_earned_date', current_time( 'mysql' ) );

        // Notify group admins
        $admins = groups_get_group_admins( $group_id );
        foreach ( $admins as $admin ) {
            bp_notifications_add_notification( array(
                'user_id'           => $admin->user_id,
                'item_id'           => $group_id,
                'component_name'    => 'groups',
                'component_action'  => 'excellence_badge_awarded',
                'date_notified'     => bp_core_current_time(),
                'is_new'            => 1,
            ) );
        }
    }
}

function calculate_group_average_rating( $group_id ) {
    global $bgr;

    $args = array(
        'post_type'      => 'review',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'meta_query'     => array(
            array(
                'key'     => 'linked_group',
                'value'   => $group_id,
                'compare' => '='
            )
        )
    );

    $reviews = get_posts( $args );
    $rating_fields = $bgr['review_rating_fields'];

    $total_rating = 0;
    $count = 0;

    foreach ( $reviews as $review ) {
        $ratings = get_post_meta( $review->ID, 'review_star_rating', true );

        if ( ! empty( $ratings ) ) {
            foreach ( $ratings as $field => $value ) {
                if ( in_array( $field, $rating_fields ) ) {
                    $total_rating += $value;
                    $count++;
                }
            }
        }
    }

    return $count > 0 ? round( $total_rating / $count, 2 ) : 0;
}
```

### Example 2: Restrict Reviews to Group Members

```php
/**
 * Only allow group members to review
 */
add_action( 'bgr_group_after_review_submit', 'restrict_to_members', 1, 3 );

function restrict_to_members( $author_id, $group_id, $review_id ) {
    // Check if user is group member
    if ( ! groups_is_user_member( $author_id, $group_id ) ) {
        // Delete the review
        wp_delete_post( $review_id, true );

        // Add error notice
        bp_core_add_message(
            'You must be a member of this group to leave a review.',
            'error'
        );

        // Prevent further execution
        remove_all_actions( 'bgr_group_after_review_submit' );
    }
}
```

### Example 3: Sync Reviews to External CRM

```php
/**
 * Send review data to external CRM
 */
add_action( 'bgr_group_after_review_submit', 'sync_to_crm', 10, 3 );

function sync_to_crm( $author_id, $group_id, $review_id ) {
    // Get review data
    $review = get_post( $review_id );
    $ratings = get_post_meta( $review_id, 'review_star_rating', true );
    $user = get_userdata( $author_id );
    $group = groups_get_group( $group_id );

    // Prepare API payload
    $payload = array(
        'customer_email'  => $user->user_email,
        'customer_name'   => $user->display_name,
        'product_id'      => $group_id,
        'product_name'    => $group->name,
        'review_text'     => $review->post_content,
        'rating'          => calculate_average_from_ratings( $ratings ),
        'review_date'     => $review->post_date,
        'ratings_detail'  => $ratings,
    );

    // Send to CRM
    wp_remote_post( 'https://crm.example.com/api/reviews', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . get_option( 'crm_api_key' ),
            'Content-Type'  => 'application/json',
        ),
        'body'    => wp_json_encode( $payload ),
        'timeout' => 15,
    ) );
}
```

### Example 4: Display Top Rated Groups Shortcode

```php
/**
 * Custom shortcode to display top-rated groups
 * Usage: [top_rated_groups limit="5"]
 */
add_shortcode( 'top_rated_groups', 'top_rated_groups_shortcode' );

function top_rated_groups_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'limit' => 5,
    ), $atts );

    // Get all groups
    $all_groups = groups_get_groups( array(
        'per_page' => 100,
        'orderby'  => 'date_created',
    ) );

    // Calculate ratings for each group
    $rated_groups = array();

    foreach ( $all_groups['groups'] as $group ) {
        $avg_rating = calculate_group_average_rating( $group->id );

        if ( $avg_rating > 0 ) {
            $rated_groups[] = array(
                'group'  => $group,
                'rating' => $avg_rating,
            );
        }
    }

    // Sort by rating
    usort( $rated_groups, function( $a, $b ) {
        return $b['rating'] <=> $a['rating'];
    } );

    // Limit results
    $rated_groups = array_slice( $rated_groups, 0, $atts['limit'] );

    // Build output
    ob_start();
    ?>
    <div class="top-rated-groups">
        <h3>Top Rated Groups</h3>
        <ul class="group-list">
            <?php foreach ( $rated_groups as $item ) : ?>
                <li class="group-item">
                    <a href="<?php echo bp_get_group_url( $item['group'] ); ?>">
                        <strong><?php echo esc_html( $item['group']->name ); ?></strong>
                        <span class="rating">⭐ <?php echo number_format( $item['rating'], 1 ); ?>/5</span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php
    return ob_get_clean();
}
```

### Example 5: Auto-Feature Highly Rated Groups

```php
/**
 * Automatically feature groups with 4.8+ rating
 */
add_action( 'bgr_group_after_review_submit', 'auto_feature_top_groups', 15, 3 );

function auto_feature_top_groups( $author_id, $group_id, $review_id ) {
    $avg_rating = calculate_group_average_rating( $group_id );
    $review_count = get_group_review_count( $group_id );

    // Feature if rating is 4.8+ and has at least 10 reviews
    if ( $avg_rating >= 4.8 && $review_count >= 10 ) {
        // Add to featured groups
        $featured_groups = get_option( 'bp_featured_group_ids', array() );

        if ( ! in_array( $group_id, $featured_groups ) ) {
            $featured_groups[] = $group_id;
            update_option( 'bp_featured_group_ids', $featured_groups );

            // Notify group admins
            $message = sprintf(
                'Congratulations! Your group has been featured due to excellent reviews (%.1f/5 from %d reviews).',
                $avg_rating,
                $review_count
            );

            bp_groups_send_admin_notice( $group_id, 'Group Featured', $message );
        }
    }
}

function get_group_review_count( $group_id ) {
    $args = array(
        'post_type'      => 'review',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'meta_query'     => array(
            array(
                'key'     => 'linked_group',
                'value'   => $group_id,
                'compare' => '='
            )
        ),
        'fields'         => 'ids',
    );

    $reviews = get_posts( $args );
    return count( $reviews );
}
```

---

## Best Practices

### 1. Always Check User Permissions

```php
// Good
add_action( 'bgr_group_after_review_submit', 'my_custom_action', 10, 3 );

function my_custom_action( $author_id, $group_id, $review_id ) {
    // Verify permissions before taking action
    if ( ! current_user_can( 'edit_review', $review_id ) ) {
        return;
    }

    // Your code here
}
```

### 2. Sanitize and Validate Data

```php
// Good
add_filter( 'bgr_filter_group_header_rating_details', 'my_custom_rating', 10, 5 );

function my_custom_rating( $content, $avg_rating, $review_label, $total_reviews, $group_id ) {
    // Sanitize outputs
    $avg_rating     = floatval( $avg_rating );
    $total_reviews  = absint( $total_reviews );
    $group_id       = absint( $group_id );
    $review_label   = sanitize_text_field( $review_label );

    // Build safe HTML
    return sprintf(
        '<div class="rating">%s: %.1f/5 (%d %s)</div>',
        esc_html__( 'Rating', 'textdomain' ),
        $avg_rating,
        $total_reviews,
        esc_html( $review_label )
    );
}
```

### 3. Use Proper Hook Priorities

```php
// Validate EARLY (priority 5)
add_action( 'bgr_group_after_review_submit', 'validate_review', 5, 3 );

// Process normally (priority 10 - default)
add_action( 'bgr_group_after_review_submit', 'process_review', 10, 3 );

// Send notifications LATE (priority 20)
add_action( 'bgr_group_after_review_submit', 'send_review_notifications', 20, 3 );
```

### 4. Cache Expensive Calculations

```php
function calculate_group_average_rating( $group_id ) {
    // Check cache first
    $cache_key = 'bgr_avg_rating_' . $group_id;
    $cached = wp_cache_get( $cache_key );

    if ( false !== $cached ) {
        return $cached;
    }

    // Calculate rating (expensive operation)
    $avg_rating = perform_rating_calculation( $group_id );

    // Cache for 1 hour
    wp_cache_set( $cache_key, $avg_rating, '', 3600 );

    return $avg_rating;
}

// Clear cache when new review is added
add_action( 'bgr_group_after_review_submit', 'clear_rating_cache', 10, 3 );

function clear_rating_cache( $author_id, $group_id, $review_id ) {
    $cache_key = 'bgr_avg_rating_' . $group_id;
    wp_cache_delete( $cache_key );
}
```

### 5. Internationalize All Strings

```php
// Good
add_filter( 'bgr_widget_title', 'my_widget_title' );

function my_widget_title( $title ) {
    return sprintf(
        '<span class="icon">⭐</span> %s',
        esc_html__( 'Top Reviews', 'my-textdomain' )
    );
}
```

### 6. Handle Errors Gracefully

```php
add_action( 'bgr_group_after_review_submit', 'sync_to_api', 10, 3 );

function sync_to_api( $author_id, $group_id, $review_id ) {
    $response = wp_remote_post( 'https://api.example.com/reviews', array(
        'body' => get_review_data( $review_id ),
    ) );

    if ( is_wp_error( $response ) ) {
        // Log error instead of breaking the flow
        error_log( 'BGR API Sync Error: ' . $response->get_error_message() );

        // Queue for retry
        add_post_meta( $review_id, '_sync_failed', true );

        return;
    }

    // Success - clear any retry flags
    delete_post_meta( $review_id, '_sync_failed' );
}
```

### 7. Use Transients for Rate Limiting

```php
add_action( 'bgr_group_after_review_submit', 'rate_limited_action', 10, 3 );

function rate_limited_action( $author_id, $group_id, $review_id ) {
    $transient_key = 'bgr_user_review_' . $author_id;

    // Check if user just submitted review (within 1 minute)
    if ( get_transient( $transient_key ) ) {
        // Too fast - possible spam
        wp_update_post( array(
            'ID'          => $review_id,
            'post_status' => 'draft', // Force moderation
        ) );
        return;
    }

    // Set transient for 1 minute
    set_transient( $transient_key, true, 60 );
}
```

### 8. Document Your Custom Code

```php
/**
 * Send review notification to Slack
 *
 * Sends a formatted message to the #reviews Slack channel
 * when a new review is submitted. Includes reviewer name,
 * group name, rating, and link to review.
 *
 * @since 1.0.0
 *
 * @param int $author_id  User ID of the reviewer
 * @param int $group_id   BuddyPress group ID
 * @param int $review_id  Review post ID
 */
function send_review_to_slack( $author_id, $group_id, $review_id ) {
    // Implementation
}
add_action( 'bgr_group_after_review_submit', 'send_review_to_slack', 10, 3 );
```

---

## Troubleshooting

### Common Issues

**1. Hooks Not Firing**

Check that:
- BuddyPress Groups component is active
- Your code is loaded after the plugin (use `plugins_loaded` or later hooks)
- Group is not excluded from reviews

**2. Filters Not Applied**

Ensure:
- Filter priority is correct (lower numbers run earlier)
- Function returns a value (filters must return, not echo)
- Filter parameters match expected types

**3. REST API Not Working**

Verify:
- Pretty permalinks are enabled
- BuddyPress REST API is active (built into BP 5.0+)
- User has proper authentication for POST/PUT/DELETE

### Debug Mode

Enable WordPress debug mode to see errors:

```php
// In wp-config.php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
```

Check logs at: `wp-content/debug.log`

---

## Support & Resources

- **Plugin Support:** https://wbcomdesigns.com/contact/
- **GitHub Issues:** https://github.com/wbcomdesigns/buddypress-groups-review/issues
- **WordPress.org:** https://wordpress.org/support/plugin/review-buddypress-groups/
- **Documentation:** `/docs` folder in plugin directory
- **BuddyPress Codex:** https://codex.buddypress.org/
- **REST API Guide:** `docs/BUDDYPRESS-REST-API.md`

---

## Changelog

See `docs/CHANGELOG-3.5.0.md` for version history and release notes.

---

**License:** GPL-2.0+
**Author:** Wbcom Designs
**Website:** https://wbcomdesigns.com/
