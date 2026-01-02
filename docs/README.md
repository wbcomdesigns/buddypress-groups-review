# BuddyPress Group Reviews Documentation

Complete documentation for BuddyPress Group Reviews plugin v3.7.0 by Wbcom Designs.

## Quick Links

- [Site Admin Guide: Group-Level Criteria](admin-guide-group-level-criteria.md)
- [Group Admin Guide: Review Criteria](group-admin-guide-review-criteria.md)
- [Developer API: Group Criteria](developer-api-group-criteria.md)

## What's New in v3.7.0

### Group-Level Review Criteria

Version 3.7.0 introduces group-level review criteria customization. Group admins can now:

- Choose to use site-wide criteria or customize for their group
- Enable/disable individual global criteria
- Add custom rating criteria specific to their group
- Track archived criteria for historical reviews

**Site Admin:** See [Admin Guide](admin-guide-group-level-criteria.md) for setup instructions

**Group Admin:** See [Group Admin Guide](group-admin-guide-review-criteria.md) to customize your group's criteria

**Developer:** See [API Reference](developer-api-group-criteria.md) for integration details

## Features Overview

### Core Features

- **Group Reviews** - Members can write and rate BuddyPress groups
- **Multi-Criteria Ratings** - Rate groups on multiple customizable criteria
- **Review Moderation** - Group admins approve/deny reviews before publication
- **Rating Display** - Show average ratings and individual reviews
- **Activity Integration** - Reviews appear in BuddyPress activity streams
- **Notifications** - Notify group admins of new reviews

### v3.7.0 Features

- **Group-Level Criteria** - Per-group customization of rating criteria
- **Inherit or Override** - Groups choose global or custom criteria
- **Custom Criteria** - Add group-specific rating categories
- **Archived Criteria** - Preserve historical review data when criteria change
- **REST API** - Programmatic access to group criteria settings

## Documentation Structure

### For Site Administrators

**[Admin Guide: Group-Level Criteria](admin-guide-group-level-criteria.md)**

Learn how to:
- Enable group-level criteria feature
- Manage global criteria
- Understand how groups inherit or override settings
- Handle archived criteria
- Troubleshoot common issues

### For Group Administrators

**[Group Admin Guide: Review Criteria](group-admin-guide-review-criteria.md)**

Learn how to:
- Access criteria settings in your group
- Choose between inherit and customize modes
- Enable/disable global criteria
- Add custom criteria
- Archive criteria you no longer need

### For Developers

**[Developer API: Group Criteria](developer-api-group-criteria.md)**

Reference for:
- Core class methods (`BGR_Group_Criteria`)
- Helper functions
- AJAX handlers
- REST API endpoints
- WordPress hooks and filters
- Database schema
- Integration examples

## Installation

### Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- BuddyPress 5.0 or higher with Groups component active

### Install Steps

1. Upload plugin folder to `/wp-content/plugins/`
2. Activate via **Plugins** menu in WordPress admin
3. Configure at **BuddyPress → Group Reviews**
4. Enable Groups component in BuddyPress settings if not already active

## Basic Configuration

### Initial Setup

1. **General Settings**
   - Go to **BuddyPress → Group Reviews → General Settings**
   - Configure reviews per page
   - Set review moderation options
   - Enable/disable group-level criteria

2. **Criteria Settings**
   - Go to **BuddyPress → Group Reviews → Criteria Settings**
   - Add rating criteria (Quality, Relevance, etc.)
   - Activate criteria for use across site

3. **Display Settings**
   - Configure review labels and tab names
   - Set rating display options
   - Customize review form fields

### Enable Group-Level Criteria

1. Go to **BuddyPress → Group Reviews → General Settings**
2. Toggle **Enable Group-Level Criteria** to ON
3. Save settings
4. Group admins can now customize criteria at **Group → Manage → Review Criteria**

## Common Use Cases

### Scenario 1: Site-Wide Consistent Criteria

**Goal:** All groups use same rating criteria

**Setup:**
- Configure global criteria in Criteria Settings
- Leave "Enable Group-Level Criteria" OFF
- All groups automatically use your criteria

### Scenario 2: Let Groups Customize

**Goal:** Allow groups to tailor ratings to their needs

**Setup:**
- Configure comprehensive global criteria
- Turn ON "Enable Group-Level Criteria"
- Groups can enable/disable or add custom criteria

### Scenario 3: Specialized Group Types

**Goal:** Book clubs rate differently than tech groups

**Setup:**
- Enable group-level criteria
- Book club groups add custom criteria: "Book Selection", "Discussion Quality"
- Tech groups add: "Code Quality", "Technical Depth"
- Both can also use global criteria like "Engagement"

## Support

### Getting Help

- **Documentation:** See guides in this docs folder
- **Support Portal:** [Wbcom Designs Support](https://wbcomdesigns.com/support/)
- **Plugin Page:** [BuddyPress Group Reviews](https://wbcomdesigns.com/downloads/buddypress-group-reviews/)

### Reporting Issues

Include in your support request:
- WordPress version
- BuddyPress version
- Plugin version
- PHP version
- Description of the issue
- Steps to reproduce
- Screenshots (if applicable)

### Feature Requests

We welcome feedback! Submit feature requests through our support portal.

## Changelog

### Version 3.7.0

**Added:**
- Group-level review criteria customization
- Inherit or override mode for groups
- Custom group-specific criteria
- Archived criteria tracking
- REST API for group criteria
- New AJAX handlers for criteria management
- Group admin settings tab for criteria

**Improved:**
- Review data integrity with archived criteria
- Flexibility for group administrators
- Developer API with comprehensive functions

## License

GPL v2 or later

## Credits

Developed by [Wbcom Designs](https://wbcomdesigns.com/)

## Additional Resources

- [BuddyPress Documentation](https://codex.buddypress.org/)
- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [Wbcom Designs Plugins](https://wbcomdesigns.com/downloads/category/buddypress-plugins/)
