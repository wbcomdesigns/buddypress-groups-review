# Installation and Requirements

## Requirements

| Requirement | Minimum |
|---|---|
| WordPress | 4.0 or later (tested up to 6.9) |
| PHP | 7.4 or later |
| BuddyPress | Required, with the **Groups** component active |

The plugin only runs its group features when the BuddyPress Groups component is active. If Groups is disabled, the group tabs and extensions are not registered.

## Install

1. Upload the plugin folder (`review-bp-grps`) to `/wp-content/plugins/`, or install the zip from the Plugins screen.
2. Activate **Wbcom Designs - BuddyPress Group Reviews** through the Plugins menu.
3. Confirm BuddyPress is active with the Groups component enabled (Settings, BuddyPress, Components).

On activation the plugin registers a `review` custom post type and its `review_category` taxonomy, and seeds default criteria (Quality, Relevance, Engagement), default labels, and a default star colour. See [How Reviews Are Stored](../developer-guide/data-storage.md).

## Where the settings live

After activation, go to **WB Plugins, Group Reviews** in the WordPress admin (page slug `group-review-settings`). See [Site Admin Settings](../usage/settings.md) for each tab.

## Multisite

The plugin supports multisite networks. Activate it per site or network-wide as your setup requires.

## Uninstall

Deleting the plugin from the Plugins screen runs a clean uninstall that removes the plugin's data.
