# BuddyPress Group Reviews - User Guide

## Welcome! 👋

This guide will help you understand and use the BuddyPress Group Reviews plugin. Everything is explained in simple language - no coding knowledge required!

---

## Table of Contents

1. [What Does This Plugin Do?](#what-does-this-plugin-do)
2. [Getting Started](#getting-started)
3. [Plugin Settings Explained](#plugin-settings-explained)
4. [How Members Use Reviews](#how-members-use-reviews)
5. [Managing Reviews as Group Admin](#managing-reviews-as-group-admin)
6. [Widgets](#widgets)
7. [Email Notifications](#email-notifications)
8. [Frequently Asked Questions](#frequently-asked-questions)
9. [Troubleshooting](#troubleshooting)

---

## What Does This Plugin Do?

### Simple Explanation
This plugin lets your BuddyPress community members **rate and review groups**, just like people review products on Amazon or restaurants on Yelp.

### Why is This Useful?

**For Community Members:**
- Discover which groups are most helpful
- Share their experience with groups
- See what others think before joining

**For Group Admins:**
- Get feedback from members
- Understand what people like/dislike
- Build trust and credibility

**For Site Owners:**
- Increase member engagement
- Help members find quality groups
- Make your community more active

### Real-World Example
Imagine you run a community about photography:
- Members can review the "Portrait Photography" group
- They might rate it 5 stars for "Helpfulness" and 4 stars for "Activity"
- New members can see these ratings and know it's a good group to join!

---

## Getting Started

### Requirements
- WordPress installed
- BuddyPress plugin active
- Groups component enabled in BuddyPress

### First Time Setup

1. **Install and Activate**
   - Upload the plugin to WordPress
   - Activate it from Plugins page
   - You'll be redirected to settings automatically!

2. **Configure Basic Settings**
   - Go to: **WB Plugins → Group Reviews**
   - Start with the "General" tab
   - Don't worry, we'll explain everything below!

---

## Plugin Settings Explained

### General Settings Tab

#### 1. **Allow Multiple Reviews**
- **What it does:** Lets members submit more than one review for the same group
- **When to enable:**
  - ✅ If groups change over time and you want updated feedback
  - ✅ For active communities where circumstances change
- **When to disable:**
  - ❌ If you only want one opinion per member
  - ❌ To prevent spam or duplicate reviews

**Example:** If enabled, John can review the "WordPress Help" group in January, then again in June if things have improved.

---

#### 2. **Auto Approve Reviews**
- **What it does:** Reviews appear immediately without waiting for approval
- **When to enable:**
  - ✅ For trusted communities
  - ✅ When you want instant feedback visible
  - ✅ To reduce admin workload
- **When to disable:**
  - ❌ If you want to moderate content first
  - ❌ To prevent spam or inappropriate reviews
  - ❌ For sensitive communities

**Example:** With auto-approve ON, Sarah's review appears instantly. With it OFF, group admins must approve it first.

---

#### 3. **Reviews Per Page**
- **What it does:** How many reviews to show at once before pagination kicks in
- **Recommended:** 10-20 reviews
- **Why it matters:** Too many reviews on one page slows loading time

**Example:** Setting "15" means members see 15 reviews, then click "Next Page" for more.

---

#### 4. **Allow Email Notifications**
- **What it does:** Group admins receive emails when someone reviews their group
- **When to enable:**
  - ✅ Admins want to know about new feedback
  - ✅ For important groups that need monitoring
- **When to disable:**
  - ❌ If email notifications become too frequent
  - ❌ Admins prefer to check manually

---

#### 5. **Allow BuddyPress Notifications**
- **What it does:** Shows notifications in BuddyPress notification bell
- **Difference from email:** Shows up in the site, not inbox
- **Best practice:** Enable this - it's less intrusive than email

---

#### 6. **Allow Activity Stream**
- **What it does:** Posts reviews to BuddyPress activity feed
- **When to enable:**
  - ✅ To increase visibility of reviews
  - ✅ To encourage more participation
  - ✅ For active communities
- **When to disable:**
  - ❌ If activity feed gets too cluttered
  - ❌ For private groups only

**Example:** When enabled, members see "John reviewed Photography Group" in their activity feed.

---

#### 7. **Exclude Groups from Reviews**
- **What it does:** Prevents certain groups from being reviewed
- **Use cases:**
  - Test groups
  - Admin-only groups
  - Groups that shouldn't be judged (like support groups)

**How to use:** Start typing group name, select from dropdown, click save.

---

### Criteria Settings Tab

#### What are Criteria?
**Criteria are the different aspects members rate.** Instead of just one overall star rating, you can have multiple categories.

#### Default Criteria Examples:
- **Quality** - How good is the content?
- **Engagement** - Are members active and helpful?
- **Helpfulness** - Does the group solve problems?
- **Organization** - Is the group well-managed?

#### Managing Criteria

**Add New Criteria:**
1. Type the name in the "Add Criteria" field
2. Click "Add" button
3. Click "Save Settings"

**Remove Criteria:**
1. Click the X next to the criteria name
2. Click "Save Settings"

**Enable/Disable Criteria:**
- Toggle the checkbox next to each criteria
- Disabled criteria won't show in review form
- Old reviews keep their ratings

**Best Practices:**
- Use 3-5 criteria (not too many!)
- Make them clear and specific
- Use words everyone understands
- Match your community's needs

**Examples by Community Type:**

**Learning Community:**
- Teaching Quality
- Content Clarity
- Member Support
- Resource Availability

**Professional Network:**
- Networking Opportunities
- Industry Relevance
- Professional Development
- Event Quality

**Hobby Community:**
- Fun Factor
- Member Friendliness
- Activity Level
- Resource Sharing

---

### Display Settings Tab

#### 1. **Reviews Tab Label**
- **What it does:** Changes the name of the "Reviews" tab in groups
- **Default:** "Reviews"
- **Why change it:** To match your site's language/style
- **Examples:**
  - "Feedback"
  - "Ratings"
  - "Member Reviews"
  - "Testimonials"

---

#### 2. **Manage Reviews Tab Label**
- **What it does:** Changes the name of the admin tab for managing reviews
- **Default:** "Manage Reviews"
- **When members see it:** Only group admins see this tab
- **Examples:**
  - "Review Management"
  - "Pending Reviews"
  - "Moderate Feedback"

---

#### 3. **Rating Star Color**
- **What it does:** Changes the color of rating stars
- **Default:** Orange/Gold
- **Why change it:** To match your site's design
- **How to use:** Click the color box, pick a color, save

**Design Tips:**
- Use colors that stand out
- Keep good contrast with background
- Stay consistent with your brand
- Test on mobile devices

---

### Emails Settings Tab

#### 1. **Email Subject**
- **What it does:** Subject line of notification emails
- **Default:** "A new review posted"
- **Available placeholders:** You can use special codes that get replaced:
  - `%review_label%` - The word you use for reviews
  - `%group_name%` - Name of the group being reviewed

**Example:**
- Template: "New Review for %group_name%"
- Result: "New Review for WordPress Developers"

---

#### 2. **Email Message**
- **What it does:** Body text of notification emails
- **Available placeholders:**
  - `%review_label%` - Your review label
  - `%group_name%` - Group name
  - `%user_name%` - Reviewer's name
  - `%review_link%` - Link to view the review

**Example Template:**
```
Hello!

A new review has been submitted for your group %group_name%.

Reviewer: %user_name%

Click here to view it: %review_link%
```

**Tips:**
- Keep it friendly and conversational
- Include the link so admins can view easily
- Don't make it too long
- Test it by submitting a review yourself

---

## How Members Use Reviews

### Viewing Reviews

**Where to Find Reviews:**
1. Go to any BuddyPress group
2. Look for the "Reviews" tab (or whatever you named it)
3. Click to see all published reviews

**What Members See:**
- Reviewer's name and photo
- Review date
- Star ratings for each criteria
- Written review text
- Average rating at the top

---

### Submitting a Review

**Step-by-Step for Members:**

1. **Find a Group to Review**
   - Browse groups
   - Can't review groups where you're an admin!

2. **Click Add Review**
   - Look for "Add Review" button in the group
   - Or go to the Reviews tab

3. **Fill Out the Form**
   - **Subject:** Short title for your review
   - **Description:** Detailed feedback
   - **Ratings:** Click stars to rate (1-5 stars per criteria)

4. **Submit**
   - Click "Submit Review"
   - See confirmation message

5. **What Happens Next?**
   - **Auto-approve ON:** Review appears immediately
   - **Auto-approve OFF:** Group admin must approve first

---

### Review Best Practices for Members

**Good Reviews Are:**
- ✅ Honest and fair
- ✅ Specific with examples
- ✅ Balanced (mention good and bad)
- ✅ Respectful and constructive

**Bad Reviews Are:**
- ❌ Personal attacks
- ❌ Vague ("It's okay")
- ❌ Irrelevant complaints
- ❌ Spam or promotional

**Example of Good Review:**
> **Subject:** Great for Beginners!
>
> **Review:** I joined this WordPress group as a complete beginner three months ago. The members are incredibly patient and helpful. They have weekly video tutorials and respond to questions quickly. My only suggestion would be more advanced tutorials for intermediate users. Overall, highly recommended for anyone starting with WordPress!
>
> **Ratings:**
> - Quality: ⭐⭐⭐⭐⭐
> - Helpfulness: ⭐⭐⭐⭐⭐
> - Content: ⭐⭐⭐⭐

---

## Managing Reviews as Group Admin

### The Manage Reviews Tab

**Where to Find It:**
1. Go to your group (where you're admin)
2. Click "Manage" menu
3. Look for "Manage Reviews" option

**What You See:**
- Pending reviews (if auto-approve is OFF)
- Published reviews
- Options to approve, deny, or delete

---

### Approving Reviews

**When Auto-Approve is OFF:**

1. Go to Manage Reviews tab
2. See list of pending reviews
3. Read each review carefully
4. Click "Approve" or "Deny"

**Should You Approve?**
✅ **Approve if:**
- Review is honest and fair
- Follows community guidelines
- Even if it's negative (if constructive)

❌ **Deny if:**
- Contains spam or promotional content
- Uses offensive language
- Is completely off-topic
- Violates site rules

**Important:** Don't deny reviews just because they're negative! Honest criticism helps improve your group.

---

### Deleting Reviews

**When to Delete:**
- Review violates guidelines (even after approval)
- Contains personal information
- Is spam that got through
- Member requests removal

**How to Delete:**
1. Go to Manage Reviews
2. Find the review
3. Click "Delete" button
4. Confirm deletion

**Note:** Deletion is permanent! Consider carefully.

---

## Widgets

### Group Reviews Widget

**What it does:** Shows recent reviews in your sidebar or footer.

**Setting it Up:**

1. Go to **Appearance → Widgets**
2. Find "Group Reviews Widget"
3. Drag it to a widget area (like sidebar)
4. Configure options:
   - **Title:** Widget heading (e.g., "Recent Reviews")
   - **Number of Reviews:** How many to show (5-10 recommended)
   - **Filter:** Show highest rated, lowest rated, or recent

**Best Places to Use:**
- Homepage sidebar
- Group directory pages
- Member profiles

---

### Group Rating Widget

**What it does:** Shows groups with their average ratings.

**Setting it Up:**
1. Add widget to sidebar
2. Choose how many groups to display
3. Select filter (highest rated, most reviewed, etc.)

**Use Cases:**
- "Top Rated Groups" section
- Showcase popular groups
- Help members discover quality groups

---

## Email Notifications

### For Group Admins

**What Triggers Emails:**
- New review submitted (if notifications enabled)
- Review needs approval (if auto-approve is off)

**Email Contains:**
- Reviewer's name
- Group name
- Link to view/manage the review

**Controlling Email Frequency:**
- Enable/disable in Email Settings
- Consider disabling if you get too many
- BuddyPress notifications are less intrusive alternative

---

### For Reviewers

**Members receive notifications when:**
- Their review is approved (if auto-approve was off)
- They get a reply (future feature)

---

## Frequently Asked Questions

### General Questions

**Q: Who can submit reviews?**
A: Any logged-in member can review groups where they're not an admin.

**Q: Can I review my own group?**
A: No, group admins cannot review their own groups to prevent bias.

**Q: Can members edit their reviews?**
A: Not by default. They must delete and resubmit (if multiple reviews are allowed).

**Q: Are reviews anonymous?**
A: No, reviews show the member's name and profile picture.

**Q: Can I require members to join a group before reviewing?**
A: This is not built-in, but it's good practice to only review groups you've experienced.

---

### Settings Questions

**Q: What happens if I change criteria?**
A: Old reviews keep their ratings, but new reviews use the new criteria.

**Q: Can different groups have different criteria?**
A: No, criteria are site-wide for consistency.

**Q: What happens to reviews if I exclude a group?**
A: Existing reviews remain, but the "Add Review" button disappears.

**Q: Can I bulk delete reviews?**
A: No, reviews must be deleted individually from the group's Manage tab.

---

### Technical Questions

**Q: Will reviews slow down my site?**
A: No, the plugin is optimized for performance with proper caching.

**Q: Are reviews searchable?**
A: Yes, they're stored as WordPress posts so they appear in search results.

**Q: Do reviews work with mobile?**
A: Yes, the plugin is fully responsive and mobile-friendly.

**Q: Is it compatible with my theme?**
A: Yes, it works with any BuddyPress-compatible theme.

**Q: Does it work with BuddyBoss?**
A: Yes! Fully compatible with BuddyBoss Platform.

---

## Troubleshooting

### Reviews Not Showing

**Problem:** Can't see reviews on group page.

**Solutions:**
1. Check if "Reviews" tab exists in group navigation
2. Verify BuddyPress Groups component is active
3. Check if the group is excluded from reviews
4. Clear browser cache
5. Check if any reviews are published

---

### Can't Submit Review

**Problem:** "Add Review" button missing or not working.

**Solutions:**
1. **Check if you're logged in** - Must be a member
2. **Check if you're a group admin** - Can't review your own group
3. **Check if group is excluded** - Some groups don't accept reviews
4. **Check multiple review settings** - You may have already reviewed
5. **Try different browser** - Clear cache and cookies

---

### Email Notifications Not Working

**Problem:** Not receiving email notifications.

**Solutions:**
1. **Check Email Settings tab** - Is "Allow Email" enabled?
2. **Check spam folder** - Emails might be filtered
3. **Verify WordPress email** - Test with other WP emails
4. **Check notification preferences** - In your BuddyPress profile
5. **Contact host** - Some hosts block emails

---

### Stars Not Displaying Properly

**Problem:** Star ratings look broken or wrong color.

**Solutions:**
1. **Clear cache** - Browser and WordPress cache
2. **Check star color** - In Display Settings
3. **Check theme conflicts** - Try default WordPress theme
4. **Disable other plugins** - Test for conflicts
5. **Check FontAwesome** - Make sure it's loading

---

### Reviews Stuck in Pending

**Problem:** Reviews not appearing even after approval.

**Solutions:**
1. **Check auto-approve** - Maybe it's turned off
2. **Verify approval** - Go to Manage Reviews tab
3. **Check review status** - In WordPress backend (Posts → Reviews)
4. **Clear cache** - Site cache might be showing old data

---

## Need More Help?

### Support Resources

**Official Support:**
- Website: [wbcomdesigns.com/contact](https://wbcomdesigns.com/contact)
- Documentation: Check the other guides in `/docs` folder
- Video Tutorials: [vimeo.com/556662460](https://vimeo.com/556662460)

**Community Support:**
- WordPress.org Forums: [wordpress.org/support/plugin/buddypress-groups-review](https://wordpress.org/support/plugin/review-buddypress-groups/)
- GitHub Issues: For bug reports and feature requests

**Before Asking for Help:**
1. Check this guide first
2. Search the forums
3. Try troubleshooting steps
4. Have your plugin version ready
5. Note your WordPress and BuddyPress versions

---

## Tips for Success

### For Site Administrators

1. **Start with Good Settings**
   - Enable auto-approve for trusted communities
   - Use 3-5 clear criteria
   - Set reasonable reviews per page

2. **Moderate Fairly**
   - Don't delete negative reviews if they're honest
   - Respond to feedback constructively
   - Update group descriptions based on reviews

3. **Promote Reviews**
   - Encourage members to leave reviews
   - Feature highly-rated groups
   - Use widgets to showcase reviews

4. **Monitor Quality**
   - Check reviews regularly
   - Remove spam quickly
   - Adjust criteria based on usage

---

### For Group Admins

1. **Welcome Feedback**
   - Don't take negative reviews personally
   - Use them to improve your group
   - Thank reviewers for honest feedback

2. **Stay Active**
   - Good reviews come from active groups
   - Respond to member needs
   - Keep content fresh

3. **Ask for Reviews**
   - Politely remind members to review
   - Make it easy to find review button
   - Show appreciation for reviews

---

### For Members

1. **Be Thorough**
   - Spend time writing quality reviews
   - Rate all criteria fairly
   - Give specific examples

2. **Be Timely**
   - Review while experience is fresh
   - Update reviews if things change (if allowed)

3. **Be Respectful**
   - Criticism is okay, but be constructive
   - Focus on the group, not individuals
   - Follow community guidelines

---

## Conclusion

Congratulations! You now know how to use the BuddyPress Group Reviews plugin effectively.

**Remember:**
- Reviews help build a better community
- Honest feedback improves groups
- Quality reviews help others make decisions

**Have questions?** Check the Developer Guide or contact support!

---

**Plugin Version:** 3.5.0
**Last Updated:** 2025
**License:** GPL-2.0+
