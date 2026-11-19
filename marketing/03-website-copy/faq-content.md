# FAQ Content - BuddyPress Group Reviews

---

## Getting Started

### What is BuddyPress Group Reviews?
BuddyPress Group Reviews is a WordPress plugin that allows members of your BuddyPress community to rate and review groups. Members can submit text reviews along with star ratings based on multiple criteria you define. Reviews help other members find quality groups and give group admins feedback on how to improve.

### What are the requirements to use this plugin?
- WordPress 6.5 or higher
- PHP 8.0 or higher
- BuddyPress 12.0 or higher with the Groups component active
- Also compatible with BuddyBoss Platform

### Does this plugin work with BuddyBoss?
Yes! BuddyPress Group Reviews is fully compatible with BuddyBoss Platform. The plugin integrates seamlessly with BuddyBoss notifications, activity streams, and group functionality.

### How do I install the plugin?
1. Download from WordPress.org or upload the plugin ZIP file
2. Go to Plugins > Add New in your WordPress admin
3. Click "Upload Plugin" and select the ZIP file, or search for "BuddyPress Group Reviews"
4. Click "Install Now" then "Activate"
5. Navigate to the plugin settings to configure your review criteria

### Where do members submit reviews?
Members can submit reviews from the "Reviews" tab on individual group pages. If you're using shortcodes, reviews can also be submitted from any page where you've placed the review form shortcode.

---

## Features & Functionality

### Can I customize the rating criteria?
Yes! You can create unlimited custom rating criteria from the plugin settings. Add criteria like "Communication," "Helpfulness," "Activity Level," or anything relevant to your community. You can also enable or disable individual criteria and reorder them using drag-and-drop.

### Can group admins moderate reviews?
Yes. By default, reviews go through a moderation queue where group admins can approve or deny them before publishing. You can also enable auto-approval if you prefer reviews to publish immediately without moderation.

### Do reviews appear in the activity stream?
Yes, when enabled. You can configure reviews to appear in the BuddyPress activity stream so members see new reviews in their feeds. For private groups, review activity only appears within the group's activity stream.

### Can I display group ratings in sidebars?
Yes! The plugin includes two widgets:
- **Reviews Widget** - Display recent reviews and ratings
- **Group Rating Widget** - Display star ratings for groups

Both can be placed in any widget area and configured to show specific groups or filter by rating.

### What shortcodes are available?
The main shortcode is `[bgr_review_form]` which displays the review submission form. You can pass a group_id parameter to specify which group to review: `[bgr_review_form group_id="123"]`

### Can members submit multiple reviews for the same group?
This is configurable in settings. You can enable multiple reviews per user per group, or restrict to one review per member. When disabled, members will see a notice that they've already reviewed the group.

---

## Reviews & Ratings

### Can group admins delete bad reviews?
Group admins can deny reviews during moderation, but once a review is published, it remains visible. This ensures authentic feedback while preventing spam and inappropriate content during the approval process.

### How is the overall group rating calculated?
The overall rating is the average of all approved reviews. If you have multiple rating criteria, each criterion is first averaged across all reviews, then the criteria averages are combined for the final overall rating.

### Can visitors (non-members) see reviews?
Yes! As of version 3.4.0, logged-out visitors can view reviews and ratings. This is great for SEO and for showing potential members the quality of your groups before they join.

### Are ratings visible on the group directory page?
Yes, group ratings are displayed on the group directory page so members can see average ratings when browsing groups.

### Does the plugin add Google rating schema?
Yes! The plugin automatically adds structured data (schema.org AggregateRating) to group pages, which can result in star ratings appearing in Google search results.

---

## Notifications & Emails

### How do notifications work?
When a member submits a review, the group admin receives a BuddyPress notification (if the Notifications component is active). This notification links directly to the review for easy moderation.

### Can I customize notification emails?
Yes! The plugin includes customizable email templates for:
- New review submissions (to group admins)
- Review approval (to the reviewer)
- Review denial (to the reviewer)

Each email has customizable subject lines and message content with placeholder variables like [group-name], [user-name], and [review-link].

### Can I disable email notifications?
Yes, each email type can be enabled or disabled independently from the Emails settings tab.

---

## Customization & Display

### Can I change the "Reviews" tab label?
Yes! In the Display settings, you can customize both the singular ("Review") and plural ("Reviews") labels. These labels are reflected throughout the plugin, including tab names and buttons.

### Can I change the star rating color?
Yes, use the color picker in Display settings to match the star color to your site's branding. The default is gold (#FFC400).

### How many reviews show per page?
You can configure the number of reviews displayed per page in General settings. Standard WordPress pagination handles longer lists.

### Can I exclude certain groups from having reviews?
Yes! In General settings, you can select specific groups to exclude from review functionality. Excluded groups won't display the Reviews tab and won't appear in review widgets.

---

## Technical & Development

### Does the plugin have a REST API?
Yes! Full REST API support is available for integration with mobile apps and headless frontends. Standard WordPress REST API authentication applies.

### Is the plugin multisite compatible?
Yes, the plugin works with WordPress Multisite installations and can be network-activated or activated per site.

### Does it work with GamiPress?
Yes! The plugin integrates with GamiPress to trigger events when members submit reviews. You can reward members with points or badges for leaving reviews.

### What themes are supported?
The plugin is tested and styled for:
- BuddyBoss Theme
- BuddyX / BuddyX Pro
- Reign
- Youzify
- Standard BuddyPress-compatible themes

### Does the plugin slow down my site?
No. The plugin is optimized for performance with efficient database queries, conditional asset loading (CSS/JS only loads where needed), and follows WordPress best practices.

### What happens when I uninstall the plugin?
By default, your review data is preserved if you deactivate the plugin. As of version 3.5.0, there's a clean uninstall option that removes all plugin data including database tables, options, and user meta—useful for GDPR compliance or fresh installations.

---

## Troubleshooting

### Why don't I see the Reviews tab on groups?
Check these items:
1. Ensure the plugin is activated
2. Verify the BuddyPress Groups component is active
3. Check that the group isn't in the excluded groups list
4. Clear any caching plugins

### Why can't I add a review to my own group?
By design, group admins cannot review their own groups. This prevents self-promotional reviews. You can only review groups where you are not an administrator.

### Notifications aren't working. What should I check?
1. Ensure the BuddyPress Notifications component is active
2. Check "Enable BuddyPress Notifications" is turned on in plugin settings
3. For email notifications, verify your WordPress mail configuration

### Reviews aren't appearing after I submit them.
If auto-approval is disabled (default), reviews need group admin approval before appearing publicly. Check the group's Manage > Reviews section for pending reviews.

### The plugin conflicts with my theme. What can I do?
Try these steps:
1. Update to the latest plugin version
2. Update your theme to the latest version
3. Check for CSS conflicts using browser developer tools
4. Contact Wbcom Designs support with details

---

## Support & Updates

### Where do I get support?
Visit [wbcomdesigns.com/support](https://wbcomdesigns.com/support/) for support requests. Include your WordPress version, BuddyPress version, theme name, and steps to reproduce any issues.

### Is there a Pro version?
The plugin on WordPress.org includes all core features. For enterprise needs or custom development, contact Wbcom Designs.

### How often is the plugin updated?
The plugin is actively maintained with regular updates for WordPress and BuddyPress compatibility, new features, and bug fixes.

### Is the plugin GDPR compliant?
The plugin stores review content and ratings, which may contain personal data. You should include information about reviews in your privacy policy. The clean uninstall feature (v3.5.0+) helps with data deletion requests.

---

## Still Have Questions?

Contact us at [wbcomdesigns.com/contact](https://wbcomdesigns.com/contact/) and we'll be happy to help!
