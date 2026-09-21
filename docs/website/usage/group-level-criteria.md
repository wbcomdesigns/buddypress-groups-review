# Group-Level Review Criteria

By default all groups share the same global criteria set by the site admin. Group-level criteria let each group admin tailor the criteria for their own group.

## Turning the feature on (site admin)

Go to **WB Plugins, Group Reviews, General** and enable **Enable Group-Level Criteria**. Until this is on, the per-group **Review Criteria** tab is not registered and every group uses the global criteria.

## The per-group Review Criteria tab

When the feature is enabled, each group gains a **Review Criteria** admin tab. From it a group admin can:

- **Use global criteria** - fall back to the site-wide criteria (the default mode).
- **Enable specific global criteria** - show only a chosen subset of the global criteria.
- **Add custom criteria** - define criteria unique to this group.

The group's choice is applied to the review form and to its rating calculations. Settings on this tab are saved over AJAX.

## What the review form uses

For a given group, the form shows the group's effective criteria: its custom or selected criteria when it uses custom criteria, otherwise the global set.

## When global criteria change

If the site admin deactivates or deletes a global criterion that a group had enabled, the plugin tracks it as an **archived criterion** for that group so past reviews and the group's configuration stay consistent.

## Where it is stored

Per-group criteria are stored in BuddyPress group meta under the key `bgr_group_criteria_settings` (with `enabled_global_criteria`, `custom_criteria`, and `archived_criteria`). See [How Reviews Are Stored](../developer-guide/data-storage.md).
