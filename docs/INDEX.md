# BuddyPress Group Reviews v3.7.0 Documentation Index

Complete documentation for Group-Level Review Criteria feature.

## Documentation Files

### 📚 Overview & Getting Started

**[README.md](README.md)**
- Feature overview
- Quick links to all documentation
- Installation and basic configuration
- Common use cases

### 👨‍💼 For Site Administrators

**[Admin Guide: Group-Level Criteria](admin-guide-group-level-criteria.md)**
- How to enable the feature
- Managing global criteria
- Understanding inherit vs customize modes
- Archived criteria tracking
- Best practices
- Troubleshooting

**Word count:** ~1,800 words | **Read time:** 8 minutes

### 👥 For Group Administrators

**[Group Admin Guide: Review Criteria](group-admin-guide-review-criteria.md)**
- Accessing criteria settings
- Choosing your mode
- Working with global criteria
- Adding custom criteria
- Archiving criteria
- Practical examples
- Troubleshooting

**Word count:** ~2,000 words | **Read time:** 9 minutes

### 💻 For Developers

**[Developer API: Group Criteria](developer-api-group-criteria.md)**
- Core class documentation
- Key functions and methods
- AJAX handlers
- REST API endpoints
- WordPress hooks
- Database schema
- Integration examples
- Code snippets

**Word count:** ~3,500 words | **Read time:** 15 minutes

### 🔍 Quick References

**[Quick Reference Guide](quick-reference-group-criteria.md)**
- Checklists for common tasks
- Function quick reference
- AJAX endpoints
- REST API summary
- Common patterns
- File locations
- Troubleshooting quick fixes

**Word count:** ~1,500 words | **Read time:** 5 minutes

**[Visual Guide](visual-guide-group-criteria.md)**
- Diagrams and flowcharts
- Data flow illustrations
- Use case examples
- Settings flow
- Decision trees
- Common workflows

**Word count:** ~2,000 words | **Read time:** 10 minutes

### 📋 Release Information

**[Changelog v3.7.0](CHANGELOG-v3.7.0.md)**
- Complete feature details
- Technical changes
- New files and classes
- Database changes
- Backward compatibility
- Security improvements
- Testing checklist
- Known limitations

**Word count:** ~3,000 words | **Read time:** 12 minutes

## Documentation by Role

### I'm a Site Administrator

**Start here:**
1. [README.md](README.md) - Understand the feature
2. [Admin Guide](admin-guide-group-level-criteria.md) - Enable and configure
3. [Quick Reference](quick-reference-group-criteria.md) - Keep handy for quick lookups

**Advanced:**
- [Visual Guide](visual-guide-group-criteria.md) - Understand data flow
- [Changelog](CHANGELOG-v3.7.0.md) - Technical details

### I'm a Group Administrator

**Start here:**
1. [README.md](README.md) - Feature overview
2. [Group Admin Guide](group-admin-guide-review-criteria.md) - Complete walkthrough
3. [Visual Guide](visual-guide-group-criteria.md) - See examples and workflows

**Quick help:**
- [Quick Reference](quick-reference-group-criteria.md) - Common tasks

### I'm a Developer

**Start here:**
1. [Developer API](developer-api-group-criteria.md) - Complete API reference
2. [Quick Reference](quick-reference-group-criteria.md) - Function lookups

**Understanding the system:**
- [Visual Guide](visual-guide-group-criteria.md) - Data flow and structure
- [Changelog](CHANGELOG-v3.7.0.md) - Technical implementation details

### I'm New to the Plugin

**Start here:**
1. [README.md](README.md) - Overview and installation
2. Choose your role above and follow that path

## Documentation by Task

### Setting Up the Feature

1. **[Admin Guide → Enabling the Feature](admin-guide-group-level-criteria.md#enabling-the-feature)**
2. **[Quick Reference → Site Admin Checklist](quick-reference-group-criteria.md#site-admin-checklist)**

### Customizing Group Criteria

1. **[Group Admin Guide → Choose Your Mode](group-admin-guide-review-criteria.md#choosing-your-mode)**
2. **[Visual Guide → Decision Tree](visual-guide-group-criteria.md#decision-tree-for-group-admins)**
3. **[Quick Reference → Group Admin Checklist](quick-reference-group-criteria.md#group-admin-checklist)**

### Integrating with Code

1. **[Developer API → Key Functions](developer-api-group-criteria.md#key-functions)**
2. **[Developer API → Integration Examples](developer-api-group-criteria.md#integration-examples)**
3. **[Quick Reference → Function Reference](quick-reference-group-criteria.md#function-quick-reference)**

### Troubleshooting Issues

1. **[Admin Guide → Troubleshooting](admin-guide-group-level-criteria.md#troubleshooting)**
2. **[Group Admin Guide → Troubleshooting](group-admin-guide-review-criteria.md#troubleshooting)**
3. **[Quick Reference → Quick Fixes](quick-reference-group-criteria.md#troubleshooting-quick-fixes)**
4. **[Visual Guide → Troubleshooting Flowchart](visual-guide-group-criteria.md#troubleshooting-flowchart)**

### Understanding Data Flow

1. **[Visual Guide → Data Flow](visual-guide-group-criteria.md#data-flow)**
2. **[Visual Guide → Database Structure](visual-guide-group-criteria.md#database-structure)**
3. **[Developer API → Database Schema](developer-api-group-criteria.md#database-schema)**

## Key Concepts

### Modes

**Inherit Mode**
- Uses site-wide criteria exactly
- Automatic updates
- Zero maintenance

**Customize Mode**
- Select which global criteria to use
- Add custom criteria
- Manual management

See: [Visual Guide → Two Modes Explained](visual-guide-group-criteria.md#two-modes-explained)

### Criteria Types

**Global Criteria**
- Defined by site admin
- Available to all groups
- Can be enabled/disabled per group in Customize mode

**Custom Criteria**
- Created by group admin
- Specific to one group
- Can be archived when no longer needed

See: [Group Admin Guide → Working with Criteria](group-admin-guide-review-criteria.md#working-with-site-wide-criteria)

### Archiving

**What it means:**
Soft delete - criteria removed from new reviews but preserved in old ones

**When it happens:**
- Site admin deactivates global criterion
- Group admin removes custom criterion (when reviews exist)

**Why it matters:**
Preserves review data integrity

See: [Visual Guide → Archiving System](visual-guide-group-criteria.md#archiving-system)

## Common Questions

**Q: Do I need to enable this feature?**

No. It's optional. Without enabling it, all groups use global criteria (current behavior).

**Q: Will this affect existing reviews?**

No. Existing reviews display exactly as before.

**Q: Can group admins override my global criteria?**

They can disable them for their group (in Customize mode) but can't change the global settings.

**Q: What happens to reviews when criteria are archived?**

They continue to display all their original ratings. Only new reviews are affected.

**Q: Can I customize criteria programmatically?**

Yes. See [Developer API → Integration Examples](developer-api-group-criteria.md#integration-examples).

## File Structure

```
docs/
├── INDEX.md (this file)                      # Documentation index
├── README.md                                  # Overview
├── admin-guide-group-level-criteria.md       # Site admin guide
├── group-admin-guide-review-criteria.md      # Group admin guide
├── developer-api-group-criteria.md           # Developer reference
├── quick-reference-group-criteria.md         # Quick lookups
├── visual-guide-group-criteria.md            # Diagrams & examples
└── CHANGELOG-v3.7.0.md                       # Release notes
```

## Feature Components

### Code Files

```
includes/
├── class-bgr-group-criteria.php              # Core class
├── bgr-group-criteria-functions.php          # Helper functions
├── bgr-group-criteria-ajax.php               # AJAX handlers
├── bgr-grp-extn.php                          # Group extension
└── templates/
    └── bgr-group-criteria-settings.php       # Admin template

admin/
└── review-admin-options-page.php             # Settings page
```

See: [Changelog → New Files](CHANGELOG-v3.7.0.md#new-files)

### Database

**Group Meta:** `bgr_group_criteria_settings`

**Global Setting:** `bgr_admin_general_settings['enable_group_criteria']`

See: [Developer API → Database Schema](developer-api-group-criteria.md#database-schema)

## API Endpoints

### REST API

**GET** `/wp-json/bgr/v1/groups/{id}/criteria`
- Get group criteria settings

**POST** `/wp-json/bgr/v1/groups/{id}/criteria`
- Update group criteria

See: [Developer API → REST API](developer-api-group-criteria.md#rest-api)

### AJAX Actions

- `bgr_save_group_criteria_settings`
- `bgr_add_custom_criteria`
- `bgr_archive_custom_criteria`
- `bgr_toggle_global_criteria`
- `bgr_get_group_criteria`

See: [Developer API → AJAX Handlers](developer-api-group-criteria.md#ajax-handlers)

## WordPress Hooks

### Actions

```php
do_action( 'bgr_global_criteria_deleted', $criteria_name );
do_action( 'bgr_global_criteria_archived', $criteria_name );
```

### Filters

```php
apply_filters( 'bgr_group_criteria_max_length', 50 );
```

See: [Developer API → WordPress Hooks](developer-api-group-criteria.md#wordpress-hooks)

## Version Information

**Plugin Version:** 3.7.0
**Feature Version:** 3.7.0 (initial release)
**Documentation Version:** 1.0
**Last Updated:** January 2026

## Requirements

- WordPress 5.0+
- BuddyPress 5.0+ (Groups component active)
- PHP 7.4+

## Support

**Documentation Issues:**
If you find errors or need clarification in this documentation, please contact Wbcom Designs support.

**Feature Support:**
For help using the feature, see the Troubleshooting sections in the relevant guide.

**Developer Support:**
For API questions, see [Developer API](developer-api-group-criteria.md) or contact support.

## Contributing

Suggestions for documentation improvements are welcome. Contact Wbcom Designs with:
- Which document needs improvement
- What information is missing or unclear
- Your suggested changes

## License

This documentation is released under GPL v2 or later, consistent with the plugin license.

## Credits

**Documentation Team:** Wbcom Designs Technical Writing
**Development Team:** Wbcom Designs Development
**Plugin Author:** Wbcom Designs

## Additional Resources

**External Links:**
- [BuddyPress Codex](https://codex.buddypress.org/)
- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [Wbcom Designs](https://wbcomdesigns.com/)

**Related Plugins:**
- Other BuddyPress plugins by Wbcom Designs
- BuddyPress core documentation

## Quick Navigation

### By Audience
- [For Site Admins](#im-a-site-administrator)
- [For Group Admins](#im-a-group-administrator)
- [For Developers](#im-a-developer)

### By Topic
- [Setup & Configuration](#setting-up-the-feature)
- [Customization](#customizing-group-criteria)
- [Development](#integrating-with-code)
- [Troubleshooting](#troubleshooting-issues)

### By Document Type
- [Guides](#-overview--getting-started)
- [References](#-quick-references)
- [Technical](#-release-information)

---

**Need help finding something?** Use your browser's find function (Ctrl/Cmd + F) to search this index.
