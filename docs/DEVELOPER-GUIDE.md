# BuddyPress Group Reviews - Developer Guide

## Table of Contents

1. [Introduction](#introduction)
2. [Plugin Architecture](#plugin-architecture)
3. [Action Hooks Reference](#action-hooks-reference)
4. [Filter Hooks Reference](#filter-hooks-reference)
5. [Function Reference](#function-reference)
6. [Uninstall & Data Cleanup](#uninstall--data-cleanup)
7. [REST API Integration](#rest-api-integration)
8. [Database Structure](#database-structure)
9. [Custom Post Type](#custom-post-type)
10. [Extending the Plugin](#extending-the-plugin)
11. [Code Examples](#code-examples)
12. [Best Practices](#best-practices)

---

## Introduction

This developer guide provides technical documentation for extending and customizing the BuddyPress Group Reviews plugin. It includes all available hooks, filters, functions, and integration points.

**Plugin Version:** 3.9.2
**Minimum PHP:** 8.0
**Minimum WordPress:** 6.5
**Requires:** BuddyPress or BuddyBoss Platform with the Groups component active

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

Fires after a review has been successfully submitted, from the group form (AJAX) or the REST create route (`POST buddypress/v1/groups/{id}/reviews`). The plugin's own tagging, GamiPress award, admin notification, admin email and activity side effects run on this hook (`bgr_handle_review_submitted()`, priority 10; activity at 15).

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

Fires when a pending review is approved from any path: group Manage tab, wp-admin row or bulk action, post editor, or REST update. Fired by `bgr_handle_review_decision()` on the pending -> publish status change, regardless of the notification setting.

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

**Location:** `bgr_handle_review_decision()` in `includes/bgr-functions.php`

---

#### `bgr_group_deny_review`

Fires when a pending review is denied from any path (pending -> trash). Removing an already published review does not fire it.

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

**Location:** `bgr_handle_review_decision()` in `includes/bgr-functions.php`

---

#### `bgr_group_review_after_review_insert`

Fires once after a group review is created, from the group form (AJAX) or REST, after its group and ratings are saved.

**Parameters** (since 3.9.1; earlier versions passed none):
- `int $review_id` - Review post ID
- `int $group_id` - BuddyPress group ID
- `int $author_id` - Reviewer user ID

**Example:**
```php
add_action( 'bgr_group_review_after_review_insert', 'my_post_insert_action', 10, 3 );

function my_post_insert_action( $review_id, $group_id, $author_id ) {
    // Clear caches
    // Update counters
    // Sync external systems
}
```

**Location:** `bgr_handle_review_submitted()` in `includes/bgr-functions.php`

---

#### `bgr_group_add_review`

Fires once per group admin when a review is submitted and BuddyPress Notifications is on.

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

**Location:** `bgr_handle_review_submitted()` in `includes/bgr-functions.php`

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
    // Print the numeric average next to the stars.
    echo '<span class="my-avg">' . esc_html( number_format_i18n( (float) $avg_rating, 1 ) ) . '</span>';
}
```

The hook has no group ID. To get a review count, call `bgr_get_group_review_stats( $group_id )` from a place where you know the group.

**Location:** `includes/widgets/bgr-review.php` and `includes/widgets/group-rating.php`

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

### Admin Bulk Action Hooks

#### `handle_bulk_actions-edit-review`

WordPress filter for handling custom bulk actions on reviews.

**Parameters:**
- `string $redirect_to` - The redirect URL after action
- `string $doaction` - The action being taken (`approve_reviews` or `deny_reviews`)
- `array $post_ids` - Array of review post IDs

**Returns:** `string` - Modified redirect URL

**Example:**
```php
add_filter( 'handle_bulk_actions-edit-review', 'my_custom_bulk_handler', 10, 3 );

function my_custom_bulk_handler( $redirect_to, $doaction, $post_ids ) {
    if ( 'my_custom_action' !== $doaction ) {
        return $redirect_to;
    }

    foreach ( $post_ids as $post_id ) {
        // Process each review
        do_something_with_review( $post_id );
    }

    return add_query_arg( 'my_action_done', count( $post_ids ), $redirect_to );
}
```

**Location:** `includes/bgr-filters.php:398`

**Built-in Actions:**
- `approve_reviews` - Publishes draft reviews and triggers `bgr_group_accept_review`
- `deny_reviews` - Moves reviews to trash; triggers `bgr_group_deny_review` (and the denial email) only for reviews that were still pending

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

### Admin Settings Filters

The settings screen (WB Plugins → Group Reviews) was rebuilt in 3.9.0. The bundled wrapper and its license and header-menu actions were removed in that release. Use these filters instead.

#### `bgr_admin_capability`

Filters the capability required to open the WB Plugins hub and the Group Reviews page. Default `manage_options`.

```php
add_filter( 'bgr_admin_capability', function ( $cap ) {
    return 'manage_options';
} );
```

#### `bgr_admin_tabs`

Filters the settings tabs (array keyed by tab slug). The Discover tab is added after this filter and cannot be removed here.

```php
add_filter( 'bgr_admin_tabs', function ( array $tabs ) {
    return $tabs; // Inspect the array to see each tab's keys before editing.
} );
```

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

**Returns:** `bool`

**Example:**
```php
add_filter( 'bp_group_review_nav_has_count', 'my_hide_review_count' );

function my_hide_review_count( $has_count ) {
    // Hide the count badge for private groups.
    if ( 'private' === bp_get_group_status() ) {
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

**Returns:** `int`

**Example:**
```php
add_filter( 'buddypress_groups_review_nav_count', 'my_modify_count' );

function my_modify_count( $count ) {
    // Cap the badge at 99.
    return min( 99, (int) $count );
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
- `string $action_text` - Default action format passed to `sprintf()` with the user link (`%1$s`) and group link (`%2$s`), e.g. "%1$s posted a review in the group %2$s". Keep both placeholders.

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

**Privacy Note:**
Activity posts for reviews in private or hidden groups are automatically hidden from the sitewide activity feed. They only appear within the group's own activity stream. This is handled by the `hide_sitewide` parameter in `bp_activity_add()`.

**Location:** `includes/bgr-activity.php:168`, `includes/bgr-activity.php:240`

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

**Location:** `includes/widgets/bgr-review.php` (the rating widget uses core-style `post_num` instead). The value is clamped between 1 and the widget's maximum.

---

#### `bgr_thumbnail`

Filters whether to show thumbnails in widget.

**Parameters:**
- `bool $show_thumbnail` - Whether to show thumbnails

**Returns:** `bool`

**Location:** `includes/widgets/bgr-review.php:144`

---

### Query Limit Filters

Big sites can tune these safety bounds.

| Filter | Default | Arguments | Purpose |
|--------|---------|-----------|---------|
| `bgr_review_stats_query_limit` | `10000` | `$limit, $group_id` | Max reviews aggregated per group (result is cached for an hour) |
| `bgr_filter_ratings_max_scan` | `500` | `$limit, $group_id` | Max reviews scanned when the widget filters by rating |
| `bgr_schema_reviews_limit` | `10` | `$limit, $group_id` | Reviews written into the Schema.org markup |
| `bgr_exclude_groups_select_limit` | `200` | `$limit` | Groups listed in the Exclude Groups selector |

```php
add_filter( 'bgr_schema_reviews_limit', function ( $limit, $group_id ) {
    return 5;
}, 10, 2 );
```

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

### Admin Functions

#### CSV Export

The plugin provides built-in CSV export functionality for reviews.

**Export URL:**
```
/wp-admin/admin.php?action=bgr_export_reviews&_wpnonce={nonce}
```

**Generate Export URL Programmatically:**
```php
$export_url = wp_nonce_url(
    add_query_arg(
        array(
            'action'    => 'bgr_export_reviews',
            'post_type' => 'review',
        ),
        admin_url( 'admin.php' )
    ),
    'bgr_export_reviews'
);
```

**CSV Columns:**
- Review ID, Date, Status, Reviewer, Reviewer Email
- Group Name, Group ID, Review Title, Review Content, Average Rating
- One rating column per criterion key found in the exported reviews' `review_star_rating` meta: the global criteria first, then group-level criteria in first-seen order (criteria since removed from a group still export). Legacy single-rating reviews (integer key) add no column.
- Rows are written to `php://temp` in batches of 200 and copied out after the header, because the criterion columns are only known once every review has been read.

**Location:** `includes/bgr-filters.php:550` (`BGR_Custom_Hooks::bp_group_review_handle_csv_export()`)

**Spreadsheet formula safety (OWASP CSV injection):** since 3.9.2, `BGR_Custom_Hooks::csv_safe()` prefixes a single quote to any text cell that starts with `=`, `+`, `-`, `@`, a tab or a carriage return. It covers reviewer name and email, group name, review title and content, the criterion column headers and the criterion values. Numeric cells (ID, group ID, average, ratings) are not changed. The method is private, so you cannot call it. If you write your own export of review data, apply the same rule.

**Access control:** the handler runs on `admin_init`. It checks `manage_options` and the `bgr_export_reviews` nonce, streams the file, then calls `exit`.

**Customizing Export:** the export has no action or filter, and it calls `exit` after streaming, so a hook on `admin_init` cannot change the file. To produce a different CSV, register your own admin action with its own capability check and nonce, and read reviews in batches (`posts_per_page` of 200 with `paged`, `meta_key` `linked_group`).

---

## Compatibility with BuddyPress Member Reviews

Both plugins register reviews in the shared `review` post type and `review_category` taxonomy. Group Reviews mark a post with the `linked_group` meta, Member Reviews with `linked_bp_member`. Always scope a query on `review` with the meta key you mean, and use `bupr_is_member_review( $post )` (Member Reviews 3.8.3+) to test a post. The CSV export reads only posts with `linked_group`. Full details: [member-reviews-compatibility.md](member-reviews-compatibility.md).

## Uninstall & Data Cleanup

### Clean Uninstall

By default, the plugin preserves all data when deactivated or deleted. To enable complete data removal:

**Step 1:** Add constant to `wp-config.php`:
```php
define( 'BGR_REMOVE_DATA_ON_UNINSTALL', true );
```

**Step 2:** Delete the plugin through WordPress admin.

**What Gets Removed:**
- Group review posts (reviews with `linked_group` meta) and their meta data. BuddyPress Member Reviews posts share the `review` post type and are kept.
- Plugin options:
  - `bgr_admin_general_settings`
  - `bgr_admin_criteria_settings`
  - `bgr_admin_display_settings`
  - `bp_group_review_email_settings`
- Per-group criteria (`bgr_group_criteria_settings` group meta)
- Cached ratings and submit locks (`bgr_review_*` transients)
- Review capabilities from roles

**Multisite Support:**
On multisite installations, data is removed from all network sites.

**Location:** `uninstall.php`

**Custom cleanup:** there is no uninstall hook. `uninstall.php` runs on its own without loading other plugins, so `plugins_loaded` callbacks do not run. Remove any data your own code attached (for example extra review meta) from your own plugin's uninstall routine.

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

There is no filter for the site-wide criteria list. Manage it on the **Criteria** tab. To add criteria for one group in code, use the group criteria API (see [developer-api-group-criteria.md](developer-api-group-criteria.md)):

```php
$criteria = bgr_group_criteria();
$criteria->set_criteria_mode( $group_id, 'override' );
$criteria->add_custom_criteria( $group_id, 'Innovation' );
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

The subject and message of each email are saved options, not filters. Edit them on **WB Plugins → Group Reviews → Emails**, using the `[group-name]`, `[user-name]`, `[review-link]`, `[site-name]` and (submission email only) `[admin-name]` placeholders. The subject accepts `[group-name]` and `[site-name]`.

To change the email programmatically, filter the saved option:

```php
add_filter( 'option_bp_group_review_email_settings', function ( $settings ) {
    $settings = (array) $settings;
    $settings['review_email_subject'] = 'New review for [group-name]';
    return $settings;
} );
```

The option keys are `review_email_subject`, `review_email_message`, `review_accept_email_subject`, `review_accept_email_message`, `review_deny_email_subject` and `review_deny_email_message`.

### Schema.org Customization

The JSON-LD block is printed on `wp_head` (priority 5) with no filter. The only control is `bgr_schema_reviews_limit`, which sets how many reviews it lists (default 10). See [Query Limit Filters](#query-limit-filters).

### Custom Widget Output

The widgets have no HTML filter. You can change the title with `bgr_widget_title`, the list type with `bgr_listing_cat`, the item count with `bgr_post_num` and thumbnails with `bgr_thumbnail`. See [Widget Filters](#widget-filters).

---

## Code Examples

### Example 1: Award Badges for High Reviews

```php
/**
 * Award badge to groups with 4.5+ average rating
 */
add_action( 'bgr_group_after_review_submit', 'award_excellence_badge', 10, 3 );

function award_excellence_badge( $author_id, $group_id, $review_id ) {
    // Cached, bounded aggregate maintained by the plugin (no review loop needed).
    $stats      = bgr_get_group_review_stats( $group_id );
    $avg_rating = $stats['average'];

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
```

`bgr_get_group_review_stats( $group_id )` runs one bounded query (capped by `bgr_review_stats_query_limit`, default 10000) and caches the result for an hour. It returns `average`, `total_reviews`, per-criterion `ratings` and a per-review `index`. Do not load every review of a group with an unbounded query: it holds them all in memory and breaks on large sites.

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

The plugin already ships a leaderboard shortcode. Use it instead of looping over groups yourself, which costs one rating lookup per group:

```php
echo do_shortcode( '[bgr_display_top_groups title="Top Rated Groups" total_groups="5" type="top rated"]' );
```

Attributes: `title`, `total_groups` (default 10), `type` (`top rated`, `most reviewed` or `members`), `show_avatar` and `show_rating`.

### Example 5: Auto-Feature Highly Rated Groups

```php
/**
 * Automatically feature groups with 4.8+ rating
 */
add_action( 'bgr_group_after_review_submit', 'auto_feature_top_groups', 15, 3 );

function auto_feature_top_groups( $author_id, $group_id, $review_id ) {
    $stats        = bgr_get_group_review_stats( $group_id );
    $avg_rating   = $stats['average'];
    $review_count = $stats['total_reviews'];

    // Feature if rating is 4.8+ and has at least 10 reviews
    if ( $avg_rating >= 4.8 && $review_count >= 10 ) {
        // Add to featured groups
        $featured_groups = get_option( 'bp_featured_group_ids', array() );

        if ( ! in_array( $group_id, $featured_groups ) ) {
            $featured_groups[] = $group_id;
            update_option( 'bp_featured_group_ids', $featured_groups );
        }
    }
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

See the Changelog section of `readme.txt` for version history and release notes.

---

**License:** GPL-2.0+
**Author:** Wbcom Designs
**Website:** https://wbcomdesigns.com/
