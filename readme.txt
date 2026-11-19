=== Wbcom Designs - BuddyPress Group Reviews ===
Contributors: wbcomdesigns, vapvarun
Tags: buddypress, groups, reviews, ratings, community
Requires at least: 6.5
Tested up to: 7.1
Stable tag: 3.9.2
Requires PHP: 8.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: bp-group-reviews
Domain Path: /languages

Let members review and rate BuddyPress groups across your own criteria, with moderation, a top-groups leaderboard, and SEO-ready rating markup.

== Description ==

**BuddyPress Group Reviews** adds a reputation layer to your groups. Members write a review and rate the group across as many criteria as you define. Group admins and site admins approve or deny each review before it is published, and the resulting average rating appears on the group page and across the group directory so the best groups are easy to find.

It needs BuddyPress (or BuddyBoss Platform) with the Groups component active. Ratings publish as Schema.org markup, so search engines can show your group scores in results.

= What you get =

**Reviews and multi-criteria ratings**
* Members post a text review and rate the group on every criterion you define.
* Add, reorder and enable or disable rating criteria from the Criteria tab.
* Optionally let members leave more than one review of the same group ("Enable Multiple Reviews").
* Reviews can be submitted even when rating criteria are turned off.
* Group admins cannot review their own group.
* Set how many reviews show per page.

**Group-level review criteria**
* Turn on "Group-Level Criteria" and each group admin gets a Review Criteria tab to tailor criteria for their own group.
* A group can use your global criteria, pick a subset, or add its own custom criteria.
* Criteria that you deactivate globally are archived rather than lost.

**Moderation you control**
* Every review goes to a Manage tab on the group for approval or denial, with auto-approval available.
* Bulk Approve and Bulk Deny to clear a queue quickly.
* Export every review and its ratings to CSV.
* Exclude any group from reviews entirely; the review form and tabs disappear for that group.

**Discovery and SEO**
* A top-groups leaderboard via the [bgr_display_top_groups] shortcode, ranked by top rated, most reviewed, or member count.
* Group ratings on the group directory page.
* Schema.org JSON-LD AggregateRating plus individual Review entries so search engines can read your ratings.
* Two widgets: "BP Group Review Widget" and "BP Displayed Group Rating Widget" for sidebars and footers.

**Community integration**
* Reviews post to the group activity stream (optional).
* BuddyPress notifications and email notifications to group admins on a new review.
* GamiPress integration: reward members with points and badges for reviewing.
* Multisite support.
* Rename the Review and Reviews labels and pick your star rating color.

**Developer-friendly**
* REST API: group reviews (list, create, read, update, delete) under the buddypress/v1 namespace, and group criteria (read, update) under bgr/v1.
* Action and filter hooks throughout, translation ready with a POT file, and RTL support.

= Shortcodes =

**[add_group_review_form]** - Renders the review submission form. No attributes. Use it inside a group context so the form knows which group it belongs to.

**[bgr_display_top_groups]** - Renders a leaderboard of groups that have at least one published review.

* `title` - heading text above the list. Default empty.
* `total_groups` - how many groups to list. Default 10.
* `type` - `top rated` (highest average rating), `most reviewed` (highest review count), or `members` (highest member count). Default `top rated`.
* `show_avatar` - `true` or `false`. Default `true`.
* `show_rating` - `true` or `false`. Default `true`.

Example: `[bgr_display_top_groups title="Most Reviewed" total_groups="10" type="most reviewed"]`

= Perfect For =

* Community sites where members need to find the right group to join
* Directories of clubs, teams, chapters or classes
* Membership sites that surface their most active and best-run groups
* Any BuddyPress or BuddyBoss community that wants trust signals on groups

= Premium Support =

Our dedicated support team is ready to help you get the most out of this plugin. Whether you need help with setup, customization, or troubleshooting, we're here to assist.

= Documentation =

* **[Group Reviews documentation](https://docs.wbcomdesigns.com/bp-family/buddypress-group-reviews/)** - Setup walkthrough, settings reference, and usage guides

= Translations =

* English (default)
* Ready for translation in your language with included POT file
* RTL language support included

= Links =

* [Plugin Homepage](https://wbcomdesigns.com/downloads/buddypress-group-reviews/)
* [Documentation](https://docs.wbcomdesigns.com/bp-family/buddypress-group-reviews/)
* [Support](https://wbcomdesigns.com/support/)
* [Request Features](https://wbcomdesigns.com/contact/)

= Compatibility =

* WordPress 6.5 and higher
* PHP 8.0 and higher
* BuddyPress or BuddyBoss Platform with the Groups component active (required)
* Multisite supported
* Styled for the BuddyX and Reign themes, including their dark mode
* Youzify: the average rating and review button appear in the Youzify group header

= What's New in 3.9.2 =

Star colour, CSV export and theme layout fixes. Group Reviews no longer recolours BuddyPress Member Reviews stars, the CSV export can no longer carry spreadsheet formulas, and the rating row, criteria options and form colours are corrected on Reign and BuddyX. See the changelog for the full list.

== More from Wbcom Designs ==

Group Reviews helps members pick the right group. These Wbcom Designs products fit the same kind of site:

* **[BuddyX](https://buddyxtheme.com/features/)** - A free community theme on WordPress.org. Group Reviews takes its colors from BuddyX and follows its dark mode toggle.
* **[Reign](https://reigntheme.com/best-wordpress-community-theme/)** - A community theme for BuddyPress and BuddyBoss sites. Review cards, stars and the leaderboard use Reign's own color settings.
* **[Listora](https://listora.org/features/reviews-and-trust/)** - A directory plugin for places, businesses and services. Members review listings and owners reply, in the free version.
* **[BuddyNext](https://buddynext.com/features/)** - Our standalone community plugin with a feed, spaces, profiles, messaging and moderation, for new sites that do not need BuddyPress.

== Installation ==

= Automatic Installation (Recommended) =

1. Log in to your WordPress admin dashboard
2. Navigate to Plugins > Add New
3. Search for "BuddyPress Group Reviews"
4. Click "Install Now" on the Wbcom Designs - BuddyPress Group Reviews plugin
5. After installation, click "Activate"
6. Go to WB Plugins > Group Reviews to configure the plugin

= Manual Installation =

1. Download the plugin ZIP file
2. Upload the entire "buddypress-groups-review" folder to the /wp-content/plugins/ directory, or use Plugins > Add New > Upload Plugin
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Configure settings at WB Plugins > Group Reviews

= Post-Installation Setup =

After activating the plugin, we recommend:

1. Visit **WB Plugins > Group Reviews > General** to set auto approval, multiple reviews, reviews per page, and any groups to exclude
2. Add your rating criteria under the **Criteria** tab
3. Rename the review labels and pick your star color under the **Display** tab
4. Review the notification templates under the **Emails** tab
5. Open any group and check the Reviews tab
6. See the [documentation](https://docs.wbcomdesigns.com/bp-family/buddypress-group-reviews/) for detailed instructions

= Requirements =

* WordPress 6.5 or higher
* PHP 8.0 or higher
* BuddyPress or BuddyBoss Platform with the Groups component active

== Frequently Asked Questions ==

= Is BuddyPress required for this plugin? =

Yes. This plugin extends BuddyPress groups, so BuddyPress (or BuddyBoss Platform) must be active with the Groups component enabled. If it is not, the plugin deactivates itself and explains why.

= How can we submit a review to a group using this plugin? =

Visit the "/groups" section of your site and open a single group. Members see a "Reviews" tab there that lets them add a group review.

= How do we allow users to give multiple reviews on the same group? =

Enable **Enable Multiple Reviews** under WB Plugins > Group Reviews > General. It is off by default, so each member can review a group once.

= What will I do if I don't want to provide review functionality to any group? =

Use the **Exclude Groups** setting in the General tab. Selected groups lose review functionality entirely: the review form and the review tabs no longer appear for them.

= How do you hide some rating criteria from the Review form? =

You can hide criteria by disabling them in the 'Criteria' tab.

= Can group admins set their own rating criteria? =

Yes. Turn on **Group-Level Criteria** in the General tab. Each group admin then gets a "Review Criteria" tab in their group where they can use your global criteria, select a subset, or add custom criteria for that group only.

= How do you change the label of the 'Reviews' tab or the 'Manage Reviews' tab? =

This plugin provides settings to change labels; you can see them under the plugin's 'Display' tab. The group tabs are "Reviews" and a separate "Manage Reviews" tab, both of which follow your custom label.

= Why is the 'Add Review' option not displayed in a group I created? =

Group admins can't review their own group. You can only add a review to groups where you are not the admin.

= How do you display groups' average reviews according to reviews or ratings in the sidebar or footer? =

The plugin ships two widgets, "BP Group Review Widget" and "BP Displayed Group Rating Widget", which display average ratings and reviews for BuddyPress groups.

= How can I use the review form on any other page? =

Add the [add_group_review_form] shortcode to the page. It takes no attributes. Use it in a group context so the form knows which group the review belongs to.

= How do I show a list of the best groups? =

Use the [bgr_display_top_groups] shortcode on any page. For example, [bgr_display_top_groups title="Top Groups" total_groups="10" type="top rated"] lists your ten highest-rated groups. Set type to "most reviewed" or "members" to rank differently. See the Shortcodes section above for every attribute.

= Does the plugin remove its data when I delete it? =

No, not by default. Deleting the plugin leaves your reviews and settings intact so you can reinstall without losing anything. If you want a clean uninstall, add `define( 'BGR_REMOVE_DATA_ON_UNINSTALL', true );` to your wp-config.php before deleting the plugin.

= Does this plugin work with multisite? =

Yes, the plugin supports multisite networks.

= Is the plugin translation ready? =

Yes. The plugin is fully translation ready with an included POT file, and it includes RTL language support.

= Does it work with BuddyNext? =

No. BuddyNext is a standalone community plugin that runs instead of BuddyPress, and its spaces are not BuddyPress groups, so Group Reviews has nothing to attach to there. Group Reviews is for sites running BuddyPress or BuddyBoss Platform.

= Will reviews match my theme and its dark mode? =

Reviews use your theme's own link and button colors. On BuddyX and Reign the review cards, stars and leaderboard also take their colors from the theme, and dark mode follows the theme's toggle rather than the visitor's system setting. With Youzify active, the group's average rating and the review button appear in the Youzify group header.

= Can members earn points or badges for writing reviews? =

Yes, with GamiPress. Group Reviews adds a "Give Group Review" event to GamiPress, so you can award points, achievements or ranks when a member reviews a group.

= Can members review places or businesses instead of groups? =

Not with this plugin; it reviews BuddyPress groups only. For reviews of listings such as places, businesses or services, use [Listora](https://listora.org/features/reviews-and-trust/), where members review listings and owners can reply.

= Where do I get support? =

We'd like you to use [Wbcom Designs](https://wbcomdesigns.com/contact/) for all support questions. We love helping. Using Wbcom Designs allows us to assist you better and quicker.

* [Documentation](https://docs.wbcomdesigns.com/bp-family/buddypress-group-reviews/) - Setup and settings guide for Group Reviews
* [Support](https://wbcomdesigns.com/support/) - Get help from our team
* [Contact us](https://wbcomdesigns.com/contact/) - Report bugs and request features

== Screenshots ==
1. Group Reviews tab with multi-criteria star ratings and the aggregate group score.
2. Group Reviews tab rendered in BuddyX dark mode.
3. Group Reviews on a mobile viewport with stacked criteria.
4. Admin General settings - review submission and moderation options.
5. Admin Criteria manager - add, reorder, and enable rating criteria.

== Changelog ==

= 3.9.2 - November 2026 =

Star colour, CSV export and theme layout fixes.

* Fix      - Group Reviews no longer recolours BuddyPress Member Reviews stars in the activity stream, so each plugin keeps its own star colour.
* Fix      - The rating row in the group directory and group header no longer adds extra height or shrinks the stars on Reign and BuddyX.
* Fix      - The rating line under the stars in the group header is readable on the cover image in Reign's cover layouts, and the review count in the Top Rated Groups shortcode now meets text contrast.
* Fix      - The group Review Criteria mode options show each radio beside its label, with the description underneath and no grey band.
* Fix      - Required-field markers and review form error messages now meet text contrast in light and dark mode.
* Fix      - The note in the Current Active Criteria box is now readable against its tinted background.
* Fix      - The Group Review and Group Rating widgets show 50px avatars with 8px corners on group pages, 15px text, review counts that read 1 Review or 3 Reviews, and the same empty message.
* Fix      - Group Reviews styles no longer restyle pop-ups, close buttons, group header buttons or pagination from the theme or other plugins.
* Fix      - Removing a group criterion checks existing reviews in pages of 200 instead of loading every review of the group at once.
* Fix      - A blank, zero or negative Reviews Per Page value no longer hides every review in the group tab or makes the Manage list unbounded; the setting is kept between 1 and 100.
* Fix      - The REST list of group reviews now caps its page size at 100 and treats page 0 as page 1.
* Fix      - Excluded-group CSS, the rating colour fallback and the rating markup now use WordPress enqueue and script-tag helpers instead of raw inline tags.
* Fix      - Saving Reviews Per Page now puts the stored value back in the field, so a typed 500 shows as 100 straight away.
* Fix      - Accept, Deny and Delete on the group Manage tab show the server message in a notice when refused instead of reloading the page.
* Fix      - Groups excluded from reviews no longer print rating markup; one shared check now decides exclusion for the tab, form, AJAX, REST and markup.
* Fix      - The group header Add Review button no longer overlaps the rating row and Sort select on BuddyBoss and Reign at phone width.
* Fix      - PHP warnings from the activity hook when a non-review post, such as a BuddyPress email, is published before init.
* Security - The review CSV export now neutralises cells that start with =, +, -, @, tab or return, so a review cannot run as a spreadsheet formula.
* Dev      - Replaced the deprecated jQuery .bind() call on the admin criteria screen with .on().
* Dev      - Uninstall no longer flushes the whole object cache, and bin/local-ci.sh and bin/build-release.sh are added.
* Dev      - Documentation moved into docs/website in the standard format.

= 3.9.1 - October 2026 =

Privacy, moderation email and theme fixes. Recommended for every site with private or hidden groups.

* Improve  - Pagination, buttons and links now use the active theme's own button and link colors, in light and dark mode.
* Improve  - The Top Rated Groups widget reads cached group ratings instead of loading every review, and its ratings now match the group header.
* Improve  - The review CSV export now runs in batches, so large sites can export without running out of memory.
* Improve  - The criteria Remove and Add controls use standard WordPress buttons, each Remove labelled with its criterion for screen readers.
* Improve  - Reviews created through the REST API now get the same notifications, emails, activity and hooks as reviews submitted in the group.
* Fix      - Submitting a review after the rating criteria changed could fail with an error or save stars against the wrong criteria.
* Fix      - Approving or denying a review from wp-admin, bulk actions or the post editor now emails the reviewer.
* Fix      - The approval email now links the reviewer to the group's reviews instead of wp-admin.
* Fix      - Turning BuddyPress Notifications off now stops every review notification.
* Fix      - Group reviews now appear as an option in the activity stream Show filter.
* Fix      - The Submit button no longer wraps onto two lines when the review label is renamed or translated.
* Fix      - Uninstalling Group Reviews no longer deletes BuddyPress Member Reviews, which share the same review type.
* Fix      - Uninstall now removes all of the plugin's own data, including per-group criteria, cached ratings and saved options.
* Fix      - The Documentation links open the current documentation page directly.
* Fix      - Review acceptance and denial notification emails could not be turned off; the enable toggles now persist an explicit off state.
* Fix      - The acceptance and denial email toggles now hide their subject and message fields when switched off, matching the submission toggle.
* Fix      - Removed the stray filled background bar behind each rating criterion in the Add Review form.
* Fix      - Reworked the reviews pagination into a single consistent, theme-aware style with a clearly marked active page.
* Fix      - Corrected the feedback slug so plugin activation data is stored under the plugin's own key.
* Fix      - Corrected a double-escaped ampersand in the Help and Support heading.
* Fix      - Fixed missing spaces before placeholders and a mistranslation in the French strings, and corrected the Portuguese (Brazil) New Post string.
* Fix      - The plugin name is no longer translatable, so no locale can rename the product. French rendered it as "BuddyPress Group Evaluations" in the settings heading, the browser title and the feedback prompt.
* Security - Reviews and ratings of private and hidden groups are no longer shown to non-members through the REST API, the ratings filter, the group page or the widgets.
* Security - Group and member names are now escaped in review notification emails.
* Security - Removed logged-out AJAX registrations for the accept, deny, remove and submit review endpoints, which require an authenticated user.
* Dev      - The bgr_group_review_after_review_insert action now passes the review, group and reviewer IDs and fires for REST-created reviews too.
* Dev      - Added bgr_can_view_group_reviews() so integrations can apply the same review visibility rule.
* Dev      - Replaced HSL colors in the admin accordion with hex, and removed the unused .bgr-switch/.bgr-slider toggle CSS.
* Dev      - Corrected two PHPStan findings in the translation tooling and the script-textdomain filter, so static analysis runs clean at the configured level.
* Dev      - Tracked composer.json and the contract-audit baseline, which the blanket *.json ignore rule had been excluding, so the static analysis and contract gates are reproducible.

= 3.9.0 - July 2026 =

UI pass: BuddyX/Reign dark-mode token mapping, accessibility focus rings, and CSS cleanup.

* New      - Added German, Spanish, Italian and Portuguese (Brazil) translations, and completed the existing French one.
* New      - Centralized theme integration (BGR_Theme_Integration) that detects the active theme and adds a bgr-theme-{slug} body class with a filterable token map.
* Improve  - Mapped review, rating and leaderboard colors to BuddyX --bx-color-* tokens with Reign and dark-literal fallbacks so dark mode renders correctly on BuddyX, BuddyX Pro and Reign.
* Improve  - Unified the two admin stylesheets onto one shared --bgr-admin-* token vocabulary and tokenized the legacy admin colors.
* Improve  - Rebuilt the admin settings screen with the modern Wbcom card-panel UI (sidebar navigation). Settings and saved data are unchanged.
* Improve  - Replaced browser alert/confirm dialogs in the group criteria manager with accessible toast and confirm dialogs.
* Improve  - Larger, accessible tap targets on the review submission button.
* Improve  - Converted the star rating layout from floats to inline-flow so it mirrors correctly in RTL.
* Fix      - JavaScript translations could never load in production: the translation file lookup used the minified script path, which never matches the file the build produces.
* Fix      - The screen-reader labels on the star rating, the review actions and the skip link had no translatable source.
* Fix      - The default review notification emails were hardcoded in English and could not be translated.
* Fix      - Several translator notes sat in a separate PHP block from their string, so they never reached translators.
* Fix      - Added visible :focus-visible outlines to review, rating, leaderboard and admin controls (WCAG 2.4.7).
* Fix      - Dark mode now activates only on the explicit theme toggle (data-bx-mode="dark") and never from the OS preference.
* Fix      - Removed the deprecated bundled admin wrapper.
* Dev      - Extracted the 125-line inline style block from the group criteria template into an enqueued, tokenized stylesheet with min and RTL builds.
* Dev      - Consolidated the duplicated rating-color CSS into a single shared helper.

= 3.8.1 =
* Fixed: Star rating UI display issue with half-star icons
* Fixed: Reviews count not displaying correctly on group tab

= 3.8.0 =
* New: Group leaderboard shortcode [bgr_display_top_groups]
* Improved: Leaderboard shortcode listing UI
* Improved: CSS variables with fallback values for better theme compatibility
* Updated: Review tab label customization
* Updated: Documentation links
* Fixed: WordPress Coding Standards compliance across all PHP files
* Fixed: Added direct file access protection to all PHP files
* Fixed: Plugin Check compatibility improvements
* Removed: Distribution zip from repository

= 3.7.0 =
* New: Group-Level Review Criteria - Group admins can now customize review criteria for their specific groups
* New: Site admin toggle to enable/disable group-level criteria customization
* New: Groups can use global criteria, select specific criteria, or add custom criteria
* New: Archived criteria tracking when global criteria are deactivated
* Fixed: jQuery deprecation warnings for .submit(), .mouseenter(), .mouseleave() methods
* Fixed: Added missing CSS file for group criteria admin panel
* Improved: Better separation of global vs group-specific review criteria

= 3.6.0 =
* New: Bulk Approve and Bulk Deny actions for managing multiple reviews at once
* New: CSV Export functionality - Export all reviews with ratings to CSV file
* Security: Protected email notifications from header injection attacks
* Security: REST API review creation now requires group membership
* Fixed: Rating star color setting now properly applies to frontend stars
* Fixed: Exclude groups setting now works correctly
* Fixed: "Add Review" button properly aligns with "Join Group" button in group headers
* Fixed: Duplicate reviews prevented when users double-click submit
* Fixed: Reviews can now be submitted when rating criteria is disabled
* Improved: Faster page loads with cached review counts

= 3.5.1 =
* Fixed: PHP warning when accessing group slug property on certain server configurations
* Fixed: BuddyBoss notification links now properly clickable - notifications take you to the correct review page

= 3.5.0 =
* New: Mobile app and headless CMS support - Reviews now work with custom mobile apps
* New: Better search engine visibility with improved SEO markup
* New: Enhanced accessibility for users with disabilities (keyboard navigation and screen reader support)
* New: Opt-in clean uninstall - define BGR_REMOVE_DATA_ON_UNINSTALL in wp-config.php to remove all plugin data when you delete the plugin. Deleting the plugin removes nothing unless you set it.
* Improved: Faster page loading with optimized CSS and JavaScript
* Improved: Better compatibility with popular themes (Reign, BuddyX)
* Improved: More consistent and clearer interface text throughout the plugin
* Fixed: Notifications now work properly with BuddyBoss - clickable links and correct redirects
* Fixed: Review notifications now take members to the correct page instead of showing "access denied"
* Fixed: Email notification settings now save correctly
* Fixed: Manage Reviews tab now properly hidden for excluded groups
* Fixed: Tab icons display correctly when you customize tab labels
* Fixed: GamiPress integration now triggers events correctly
* Fixed: Various errors and warnings that could appear on some server configurations

= 3.4.0 =
* New: Visitors can now view reviews without logging in
* New: Better compatibility with Youzify theme
* Improved: Faster performance with optimized code
* Improved: Cleaner admin interface with better visibility
* Improved: Better translation support for multiple languages
* Fixed: Reviews now approve correctly
* Fixed: Email notifications display properly with correct line breaks
* Fixed: Member avatars display correctly in reviews
* Fixed: Private group reviews no longer appear in public activity feed
* Fixed: Review tab appears with correct name on new installations
* Fixed: Pagination works properly in Manage Reviews section
* Fixed: Email settings now save correctly
* Fixed: Various compatibility issues with Youzify theme

= 3.3.0 =
* New: Multisite network support - Use the plugin across multiple sites
* New: Icons for "Add Review" button and review listings in Youzify theme
* Improved: Better security with enhanced data validation
* Improved: Mobile-friendly interface for adding reviews and viewing ratings
* Fixed: Compatibility issues with BuddyBoss platform
* Fixed: Review pagination now works correctly
* Fixed: Review counts display properly on group tabs
* Fixed: Excluded groups setting now works as expected
* Fixed: Errors when accepting reviews
* Fixed: Email notifications send correctly when reviews are submitted
* Fixed: Review tab displays correctly in BuddyBoss
* Fixed: Various interface improvements for Youzify theme compatibility

= 3.2.2 =
* New: Submit reviews without rating criteria - More flexible review options
* Improved: Cleaner review listings with simplified interface
* Improved: Better visual design throughout the plugin
* Fixed: "Add Review" button now displays correctly
* Fixed: Reviews display properly on group directory pages
* Fixed: Pagination works smoothly
* Fixed: Review criteria options save correctly in settings
* Fixed: Plugin only activates when BuddyPress Groups component is active

= 3.1.2 =
* Fixed: Exclude groups from reviews option now works correctly

= 3.1.1 =
* Fixed: Compatibility with BuddyPress 12.0
* Fixed: Review widget errors
* Fixed: Various PHP warnings
* Updated: Compatible with WordPress 6.5.0

= 3.1.0 =
* Fixed: Group admin button displays correctly
* Fixed: Sidebar layout in add review section
* Fixed: Various PHP warnings

= 3.0.0 =
* New: Full compatibility with BuddyPress 12.0
* Improved: Code quality and performance enhancements

= 2.9.0 =
* Fix: (#132) Fixed two group review button
* Fix: (#131) Fix Console error
* Fix: (#128) Fixed gamipress integration issue
* Fix: (#129) Fixed exclude group option issue
* Fix: (#130) Fixed logout-user-issue

= 2.8.7 =
* Fix: (#126) UI issue in pagination
* Fix: (#122) Fixed warnings
* Fix: Added missing review image

= 2.8.6 =
* Fix: Backend ui managed
* Fix: (#121) Fixed when click add review no forms shows to add a review

= 2.8.5 =
* Fix: Updated admin wrapper

= 2.8.4 =
* Fix: Fixed security issue and added capability checks
* Fix: (#120) Hide add review tab for the logged-out user

= 2.8.3 =
* Fix: Fixed double activity is generating when auto approval is disabled
* Fix: (#85) Added group activity for group review
* Fix: Fixed notice issue on group review
* Fix: (#115) Fixed give a notice when user is not permitted to post multiple
* Fix: BB Theme with platform with UI managed
* Fix: Fixed phpcs error

= 2.8.2 =
* Fix: PHPCS Fixes

= 2.8.1 =
* Fix: Install Free plugin from WordPress Wrapper
* Fix: (#114) Fixed string issue in add review tab
* Fix: fixed button display issue in manage tab when label is changed
* Fix: (#113) Fixed BP notification link issue when auto approve is disable
* Fix: (#111) Fixed tab redirection issue when bp rewrites is active
* Fix: (#109) Removed review title A string
* Fix: (#111) Fixed manage review tab redirect issue with BP Rewrites plugin
* Fix: (#110)Fixed review title display issue on group manage review tab
* Fix: (#107)Fixed getting error when we use shortcode into page
* Fix: (#109)Fixed Admin Review title issue
* Fix: Added hookable position after review submit

= 2.8.0 =
* Fix: Added common space between rating stars
* Fix: (#91) Fixed group avatar changed on rating filter of widget
* Fix: Update success message color
* Fix: #102 - Typo error
* Fix: #101 - Reviews CPT Title "Review" is not Changed according
* Fix: #98 - Group Review Email Subject and Email Content Placeholder
* Fix: #100 - debug code output displaying after submitting the review
* Fix: undefine index warning
* Fix: #91 - widget: bp group review widget
* Fix: Update admin url if need to approve feedback
* Fix: #84 - Review mail string change
* Fix: #80 - reviews are redirecting us to 404 page (reviews tab)
* Fix: (#95) Fixed plugin create admin menu if BuddyPress not activate
* Fix: (#77) Fixed replace plural string with singular
* Fix: admin options button UI
* Fix: (#75) Fixed php notice is no group select in bacend admin seating
* Fix: (#87) Fixed grammatically error
* Fix: (#95) Fixed redirect to error page after activate

= 2.7.0 =
* Fix: Update plugin backend UI

= 2.6.0 =
* Fix: (#73) Fixed fatal error on widget on click view
* Fix: (#63) Remove one message after submitting review
* Fix: (#67) Fixed notification redirect to 404
* Fix: (#69) Fixed add review label not changing and redirect to 404
* Fix: (#68) Fixed unable to publish group review form by shortcode
* Fix: (#62) Fixed add review button on group header redirect to 404
* Fix: (#57) Fixed change the slug according to label

= 2.3.0 =
* Fix: (#33) Fixed BuddyPress as activated still shows notices
* Fix: (#40) Fix Show the label for no. of review at review tab
* Fix: (#39) Fixed tab filter with chrome
* Fix: (#41) Fix BuddyPress Notifications are not being triggered on posting
* Fix: (#40) Bug Fix of review count for review admin menu
* Fix: Add admin review notice
* Fix: String changes

= 2.2.0 =
* Fix: Translation Fixes
* Fix: Admin notice fixed
* Enhancement: Added reviews tab filters.
* Enhancement: Added group rating widget.
* Enhancement: Group widget and add review

= 2.1.0 =
* Fix - (#26) - Delete icon in exclude groups selectize
* Fix - (#27)-The whole list is not visible.

= 2.0.1 =
* Fix - Compatibility with BuddyPress 4.3.0. #25

= 2.0.0 =
* Fix - Compatibility with BuddyPress 4.1.0. #20
* Enhancement - Improved Backend UI where you can manage all wbcom plugin's settings at one place. #22
* Enhancement - Used latest fontawesome library. #23

= 1.0.5 =
* Fix - Blank description issue for bbpress notifications.
* Enhancement - Compatibility with BuddyPress 3.2.0

= 1.0.4 =
* Fix - updated hooks to fix 404 errors on Group Review buttons

= 1.0.3 =
* Enhancement : Added rating section at group directory
* Enhancement - Added Google Rating Snippet meta tags
* Enhancement - Simplified rating view on single group page
* Fix - Removed JS tab for add review and added real sub nav to add reviews

= 1.0.2 =
* Fix : Notice fixes
* Fix : Remove "Add" button for admins
* Fix : Incorrect link in e-mail
* Fix : Incorrect link in notification
* Fix : Change message when auto approve review setting is enabled.
* Fix : Changed review form validations.
* Fix : Fixed UI issues in reviews page, single review page.
* Enhancement : Add Review button on group header.
* Enhancement : Add few new extra settings
* Enhancement : Added functionality of multiple review.
* Enhancement : Added ratings in group directory.
* Enhancement : Added filter for change group header rating additional details.

= 1.0.1 =
* Layout Improvement

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 3.9.2 =
Security hardening for the review CSV export, plus star colour and theme layout fixes. Recommended update.

= 3.9.1 =
Privacy fix: reviews and ratings of private and hidden groups are no longer visible to non-members. Also fixes moderation emails and uninstall. Recommended update.

= 3.9.0 =
UI and accessibility pass. Dark mode now renders correctly on BuddyX and Reign and follows the theme toggle rather than the OS setting. Settings and saved reviews are unchanged.
