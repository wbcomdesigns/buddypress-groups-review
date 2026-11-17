# Visual Guide: Group-Level Review Criteria

Diagrams and examples explaining how group-level criteria work.

## Feature Overview

```
┌─────────────────────────────────────────────────────────────┐
│                      SITE ADMIN                             │
│  Configures Global Criteria: Quality, Relevance, Engagement │
│  Enables Group-Level Criteria Feature: ON/OFF               │
└────────────────────┬────────────────────────────────────────┘
                     │
        ┌────────────┴────────────┐
        │                         │
        ▼                         ▼
┌───────────────┐         ┌───────────────┐
│   Group A     │         │   Group B     │
│ (Inherit Mode)│         │(Customize Mode)│
└───────────────┘         └───────────────┘
        │                         │
        ▼                         ▼
   Uses exactly             Selects which
   global criteria          criteria to use
                           + adds custom ones
```

## Two Modes Explained

### Inherit Mode (Default)

```
Site Admin Sets:           Group Sees:
┌─────────────┐           ┌─────────────┐
│  Quality    │   ──────> │  Quality    │
│  Relevance  │   ──────> │  Relevance  │
│  Engagement │   ──────> │  Engagement │
└─────────────┘           └─────────────┘

Changes to global = immediate update to group
```

**Key Points:**
- Zero configuration for group admin
- Always in sync with site settings
- Best for general-purpose groups

### Customize Mode

```
Site Admin Provides:      Group Admin Selects:
┌─────────────┐           ┌─────────────┐
│  Quality    │   ────┐   │  Quality    │ ✓ Enabled
│  Relevance  │   ────X   │             │ ✗ Disabled
│  Engagement │   ────┐   │  Engagement │ ✓ Enabled
└─────────────┘       │   └─────────────┘
                      │   ┌─────────────┐
                      └──>│ Book Rating │ Custom
                          │ Plot Depth  │ Custom
                          └─────────────┘

Group controls what appears
```

**Key Points:**
- Full control over criteria
- Can add group-specific criteria
- Manual updates when global changes

## Data Flow

### Review Submission

```
1. Reviewer visits group
   │
   ▼
2. System loads group's effective criteria
   │
   ├─ Inherit mode? → Use global criteria
   │
   └─ Customize mode? → Use group's selected criteria
   │
   ▼
3. Review form displays criteria
   │
   ▼
4. Reviewer rates each criterion
   │
   ▼
5. Review saved with ratings data
   │
   ▼
6. Review remembers which criteria it used
```

### Review Display

```
Review Record:
┌────────────────────────────────┐
│ Review ID: 123                 │
│ Group ID: 45                   │
│ Ratings:                       │
│   Quality: 5                   │
│   Book Rating: 4 (custom)      │
│   Old Criterion: 3 (archived)  │
└────────────────────────────────┘
        │
        ▼
Display shows ALL ratings,
even if criterion is now archived
```

## Archiving System

### When Criteria Are Archived

```
Active Criterion               Archived Criterion
┌──────────────┐              ┌──────────────┐
│ Name: Quality│              │ Name: Quality│
│ Active: true │   ────>      │ Active: false│
│              │   Archive    │ Archived: now│
└──────────────┘              │ Reason: noted│
                              └──────────────┘

New Reviews:                  Old Reviews:
Won't see this criterion      Still show ratings
```

### Archive Triggers

```
Trigger 1: Site Admin Deactivates Global Criterion
┌─────────────┐
│ Site Admin  │ Deactivates "Relevance"
└──────┬──────┘
       │
       ▼
All groups using "Relevance" → Archived
       │
       ├─ Inherit mode groups: Auto-archived
       │
       └─ Customize mode groups: Auto-archived if enabled

Trigger 2: Group Admin Removes Custom Criterion
┌─────────────┐
│ Group Admin │ Archives "Book Rating"
└──────┬──────┘
       │
       ▼
Check: Are there reviews with this criterion?
       │
       ├─ Yes → Soft delete (archive)
       │
       └─ No  → Hard delete (permanent)
```

## Use Case Examples

### Example 1: Book Club

```
Before Customization:          After Customization:
┌─────────────┐               ┌─────────────┐
│  Quality    │               │  Quality    │ ✓ From global
│  Relevance  │               │  Engagement │ ✓ From global
│  Engagement │               │ Book Choice │ ✓ Custom
└─────────────┘               │ Plot Depth  │ ✓ Custom
                              │ Pacing      │ ✓ Custom
                              └─────────────┘

Review Form Shows:
Rate this group on:
★★★★★ Quality
★★★★★ Engagement
★★★★★ Book Choice
★★★★★ Plot Depth
★★★★★ Pacing
```

### Example 2: Tech Support Group

```
Before Customization:          After Customization:
┌─────────────┐               ┌─────────────┐
│  Quality    │               │  Quality    │ ✓ From global
│  Relevance  │               │  Relevance  │ ✓ From global
│  Engagement │               │Response Time│ ✓ Custom
└─────────────┘               │Solution Help│ ✓ Custom
                              └─────────────┘

Review Form Shows:
Rate this group on:
★★★★★ Quality
★★★★★ Relevance
★★★★★ Response Time
★★★★★ Solution Helpfulness
```

### Example 3: General Group (Inherit Mode)

```
Site Global Criteria:         Group Uses:
┌─────────────┐               ┌─────────────┐
│  Quality    │   ═════════>  │  Quality    │
│  Relevance  │   ═════════>  │  Relevance  │
│  Engagement │   ═════════>  │  Engagement │
└─────────────┘               └─────────────┘

No group admin action needed
Always stays in sync
```

## Settings Flow

### Site Admin Enables Feature

```
Step 1: Admin Panel
┌──────────────────────────────┐
│ WB Plugins > Group Reviews   │
│ > General                    │
│                              │
│ [✓] Group-Level Criteria     │
│                              │
│                              │
│ [Save Settings]              │
└──────────────────────────────┘
        │
        ▼
Step 2: Groups Get Access
┌──────────────────────────────┐
│ Group > Manage               │
│                              │
│ New Tab Appears:             │
│ • Review Criteria            │
└──────────────────────────────┘
```

### Group Admin Customizes

```
Step 1: Access Settings
┌──────────────────────────────┐
│ Group > Manage >             │
│ Review Criteria              │
└──────────────────────────────┘
        │
        ▼
Step 2: Choose Mode
┌──────────────────────────────┐
│ ○ Use site-wide criteria     │
│ ● Customize for this group   │
└──────────────────────────────┘
        │
        ▼
Step 3: Select Criteria
┌──────────────────────────────┐
│ Site-Wide:                   │
│ [✓] Quality                  │
│ [ ] Relevance                │
│ [✓] Engagement               │
│                              │
│ Custom:                      │
│ • Book Rating                │
│ • Plot Depth                 │
│ [Add new...]                 │
└──────────────────────────────┘
        │
        ▼
Step 4: Save & Preview
┌──────────────────────────────┐
│ Active Criteria:             │
│ • Quality                    │
│ • Engagement                 │
│ • Book Rating                │
│ • Plot Depth                 │
└──────────────────────────────┘
```

## Rating Calculations

### With Mixed Criteria Over Time

```
Review History:

Review 1 (Jan 2024):           Review 2 (Feb 2024):
Criteria at time:              Criteria at time:
┌─────────────┐               ┌─────────────┐
│ Quality: 5  │               │ Quality: 4  │
│ Relevance: 4│               │ Custom: 5   │
└─────────────┘               └─────────────┘

Review 3 (Mar 2024):
Criteria at time:
┌─────────────┐
│ Quality: 3  │
│ Custom: 4   │
└─────────────┘

Average Calculations:
┌────────────────────────────┐
│ Quality:                   │
│   Sum: 5+4+3 = 12          │
│   Count: 3                 │
│   Average: 4.0             │
│                            │
│ Relevance: (archived)      │
│   Sum: 4                   │
│   Count: 1                 │
│   Average: 4.0             │
│                            │
│ Custom:                    │
│   Sum: 5+4 = 9             │
│   Count: 2                 │
│   Average: 4.5             │
└────────────────────────────┘

All reviews counted accurately
```

## Database Structure

### Group Meta Storage

```
wp_bp_groups_groupmeta
┌────────┬──────────┬──────────────────────────┬────────┐
│group_id│ meta_key │      meta_value          │meta_id │
├────────┼──────────┼──────────────────────────┼────────┤
│   45   │bgr_group_│ {                        │  123   │
│        │criteria_ │   "mode": "override",    │        │
│        │settings  │   "enabled_global_       │        │
│        │          │    criteria": [          │        │
│        │          │     "Quality",           │        │
│        │          │     "Engagement"         │        │
│        │          │   ],                     │        │
│        │          │   "custom_criteria": [   │        │
│        │          │     {                    │        │
│        │          │       "name": "Book",    │        │
│        │          │       "created": 123456, │        │
│        │          │       "active": true     │        │
│        │          │     }                    │        │
│        │          │   ],                     │        │
│        │          │   "archived_criteria": { │        │
│        │          │     "Old": {             │        │
│        │          │       "archived_at": 789,│        │
│        │          │       "reason": "admin"  │        │
│        │          │     }                    │        │
│        │          │   }                      │        │
│        │          │ }                        │        │
└────────┴──────────┴──────────────────────────┴────────┘
```

## Permission Hierarchy

```
                    ┌──────────────┐
                    │  Site Admin  │
                    │ (Full Access)│
                    └───────┬──────┘
                            │
                Can enable/disable feature
                Can manage global criteria
                Can override group settings
                            │
                ┌───────────┴───────────┐
                │                       │
        ┌───────▼──────┐        ┌──────▼──────┐
        │ Group Admin  │        │Group Creator│
        │(Group Access)│        │(Group Access)│
        └──────────────┘        └─────────────┘
                │                       │
                └───────────┬───────────┘
                            │
                Can set mode for their group
                Can enable/disable global criteria
                Can add/archive custom criteria
                            │
                    ┌───────▼──────┐
                    │Group Members │
                    │  (No Access) │
                    └──────────────┘
                            │
                    Can only write reviews
                    using active criteria
```

## Impact of Global Changes

### Site Admin Adds New Global Criterion

```
Before:                        After:
┌─────────────────────┐       ┌─────────────────────┐
│ Global: Quality     │       │ Global: Quality     │
│         Relevance   │  ADD  │         Relevance   │
│                     │  ───> │         Engagement  │ NEW
└─────────────────────┘       └─────────────────────┘
         │                             │
         ▼                             ▼
┌─────────────────────┐       ┌─────────────────────┐
│ Group A (Inherit)   │       │ Group A (Inherit)   │
│ • Quality           │       │ • Quality           │
│ • Relevance         │       │ • Relevance         │
│                     │       │ • Engagement        │ ← Auto-added
└─────────────────────┘       └─────────────────────┘
         │                             │
         ▼                             ▼
┌─────────────────────┐       ┌─────────────────────┐
│ Group B (Customize) │       │ Group B (Customize) │
│ • Quality           │       │ • Quality           │
│ • Custom1           │       │ • Custom1           │
│                     │       │                     │
│                     │       │ Engagement now      │
│                     │       │ available to enable │ ← Manual
└─────────────────────┘       └─────────────────────┘
```

### Site Admin Deactivates Global Criterion

```
Before:                        After:
┌─────────────────────┐       ┌─────────────────────┐
│ Global: Quality     │       │ Global: Quality     │
│         Relevance   │ DEACT │                     │ Relevance
│         Engagement  │  ───> │         Engagement  │ deactivated
└─────────────────────┘       └─────────────────────┘
         │                             │
         ▼                             ▼
┌─────────────────────┐       ┌─────────────────────┐
│ Group A (Inherit)   │       │ Group A (Inherit)   │
│ • Quality           │       │ • Quality           │
│ • Relevance         │       │                     │ ← Auto-removed
│ • Engagement        │       │ • Engagement        │
└─────────────────────┘       └─────────────────────┘
                                      │
                              Relevance archived
                                      │
         ▼                             ▼
┌─────────────────────┐       ┌─────────────────────┐
│ Group B (Customize) │       │ Group B (Customize) │
│ • Quality           │       │ • Quality           │
│ • Relevance         │       │                     │ ← Auto-archived
│ • Custom1           │       │ • Custom1           │
└─────────────────────┘       └─────────────────────┘
```

## Decision Tree for Group Admins

```
                    Start Here
                        │
        ┌───────────────┴───────────────┐
        │                               │
Does your group need              Group fits well
special criteria?                 with global criteria?
        │                               │
        └──Yes                    Yes───┘
                │                 │
                ▼                 ▼
        Choose CUSTOMIZE    Choose INHERIT
                │                 │
                ▼                 │
        Pick which global         └──> Done!
        criteria to use           Zero maintenance
                │
                ▼
        Add custom criteria
        if needed
                │
                ▼
        Save settings
                │
                ▼
        Preview active
        criteria
                │
                ▼
        Done!
        Check occasionally
        if site adds new
        global criteria
```

## Common Workflows

### Workflow 1: Setting Up New Specialized Group

```
1. Create group
   │
   ▼
2. Go to Manage > Review Criteria
   │
   ▼
3. Select "Customize criteria for this group"
   │
   ▼
4. Keep relevant global criteria enabled
   │
   ▼
5. Add group-specific custom criteria
   │
   ▼
6. Save settings
   │
   ▼
7. Test by submitting a review
   │
   ▼
8. Verify criteria appear correctly
```

### Workflow 2: Simplifying Group Criteria

```
1. Too many criteria showing?
   │
   ▼
2. Go to Manage > Review Criteria
   │
   ▼
3. If in Customize mode:
   │
   ├─ Disable unnecessary global criteria
   │
   └─ Archive unnecessary custom criteria
   │
   ▼
4. Save settings
   │
   ▼
5. Preview shows fewer criteria
   │
   ▼
6. New reviews use simplified set
```

### Workflow 3: Switching from Custom Back to Global

```
1. Decide to use site standards
   │
   ▼
2. Go to Manage > Review Criteria
   │
   ▼
3. Select "Use site-wide criteria"
   │
   ▼
4. Save settings
   │
   ▼
5. Custom settings preserved but not used
   │
   ▼
6. Group now uses global criteria
   │
   ▼
7. Can switch back later if needed
   (custom settings will restore)
```

## Troubleshooting Flowchart

```
Problem: Review Criteria Tab Not Visible
              │
              ▼
    Is user a group admin?
              │
      ┌───────┴───────┐
      No              Yes
      │               │
      │               ▼
      │     Is feature enabled by site admin?
      │               │
      │       ┌───────┴───────┐
      │       No              Yes
      │       │               │
      │       │               ▼
      │       │     Is group excluded from reviews?
      │       │               │
      │       │       ┌───────┴───────┐
      │       │       No              Yes
      │       │       │               │
      ▼       ▼       ▼               ▼
   Not     Contact   Tab         Group can't
   allowed site     should       have reviews
           admin    appear

Problem: Can't Delete Custom Criterion
              │
              ▼
    Are there reviews using this criterion?
              │
      ┌───────┴───────┐
      Yes             No
      │               │
      ▼               ▼
  Criterion     Criterion
  archived      deleted
  (data kept)   (permanently)
      │               │
      └───────┬───────┘
              │
              ▼
    This is expected behavior
    for data integrity
```

## Related Documentation

- [Admin Guide](admin-guide-group-level-criteria.md) - Site admin setup
- [Group Admin Guide](group-admin-guide-review-criteria.md) - Group configuration
- [Developer API](developer-api-group-criteria.md) - Technical reference
- [Quick Reference](quick-reference-group-criteria.md) - Fast lookups
