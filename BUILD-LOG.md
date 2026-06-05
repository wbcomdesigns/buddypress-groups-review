# BUILD-LOG — BuddyPress Group Reviews wrapper migration

**Plugin:** buddypress-groups-review (Wbcom Designs - BuddyPress Group Reviews)
**Branch:** `3.9.0`
**Main file:** `review-bp-grps.php`
**Date:** 2026-06-05
**Scope:** OLD Wbcom tab wrapper → new card-panel admin wrapper + approved safe fixes. UX-only migration: no option keys, AJAX actions, REST routes, nonces, or meta keys renamed.

---

## Settings persistence — detected, NOT changed (Playbook 0.2)

This plugin uses a **mixed** save model, preserved verbatim:

| Tab | Mechanism | Option key |
|-----|-----------|-----------|
| General | custom AJAX `bp_group_review_save_admin_general_settings` (full-array rebuild) | `bgr_admin_general_settings` |
| Criteria | custom AJAX `bp_group_review_save_admin_criteria_settings` | `bgr_admin_criteria_settings` |
| Display | custom AJAX `bp_group_review_save_admin_display_settings` | `bgr_admin_display_settings` |
| Emails | Settings API `options.php` + `register_setting` + `bp_group_review_sanitize_email_settings` | `bp_group_review_email_settings` |

**Each option key is owned by exactly one tab**, so the multi-tab sentinel-guarded merge (Playbook 7.1) does **not** apply here — there is no cross-tab key wipe risk. The new views keep the **exact element IDs / field names** the existing JS + Settings API read, so saving is byte-identical to the old UI. The new controller re-registers `register_setting('bp_group_review_email_settings')` with the same sanitize callback.

All four options are still deleted in `uninstall.php` (untouched — verified 4 `delete_option` calls present).

---

## Files DELETED

- `admin/wbcom/` (entire dir): `wbcom-admin-settings.php`, `assets/css/*`, `assets/js/*`, `assets/imgs/`, `templates/wbcom-{plugins,support,license}-page.php`.
- `admin/review-admin-options-page.php` — legacy tab dispatcher + `bp_group_review_{general,criteria,display,emails}_setting()` renderers (superseded by `includes/admin/views/settings-*.php`).
- `admin/bgr-welcome-page.php` — legacy welcome tab (superseded by `includes/admin/views/welcome.php`).

The `Wbcom_Admin_Settings` class (and its `wbcom_addons_cards` AJAX handler + `[wbcom_admin_setting_header]` shortcode) lived only in the deleted wrapper file, so they are gone with it.

## Files MOVED

- `admin/wbcom/assets/imgs/bgr.png` → `admin/assets/imgs/bgr.png` (git rename R100). Referenced by the retained `admin/bgr-admin-feedback.php` rating-prompt notice; reference updated.

## Files CREATED

- `includes/admin/class-bgr-admin-panel.php` — controller `BGR_Admin_Panel` (menu under `wbcomplugins`, 999-priority hub takeover, screen-scoped enqueue, notice suppression, `register_settings` for emails, `render_hub` + `render_page`).
- `includes/admin/views/shell.php` — page header + sidebar nav + body slot (`wp-header-end` marker; no form wrapper — each tab owns its submit).
- `includes/admin/views/hub.php` — WB Plugins hub landing (wrapper-helper-slug filtered).
- `includes/admin/views/welcome.php` — Welcome tab (about + resource cards).
- `includes/admin/views/settings-general.php` — General tab (preserves IDs `#bgr-multi-reviews`, `#bgr-auto-approve-reviews`, `#reviews_per_page`, `#bgr-notification`, `#bgr-activity`, `#bgr-enable-group-criteria`, `#bgr-exclude-group-review`, `#bgr-save-admin-general-settings`, `.bgr-admin-general-settings-spinner`).
- `includes/admin/views/settings-criteria.php` — Criteria tab (preserves `#bgr-textbox-container`, `BGRDynamicTextBox`, `.bgr-criteria-state`, `#bgr-field-add`, `#bgr-save-admin-criteria-settings`, sortable target).
- `includes/admin/views/settings-display.php` — Display tab (preserves `#bgrReviewLabel`, `#bgrManageReviewLabel`, `#bgr-rating-color` + `.bgr-review-color` color-picker hook, `#bgr-save-admin-display-settings`).
- `includes/admin/views/settings-emails.php` — Emails tab (options.php form; field names `bp_group_review_email_settings[...]` + `settings_fields()` group preserved; default-seeding mirrors legacy).
- `assets/css/admin.css` — `--bgr-admin-*` token system, card panel, sidebar nav, `.wb-switch` toggle restyle, hub grid, toast/confirm, 2 media blocks.
- `assets/js/admin.js` — `window.bgrToast()` + `window.bgrConfirm()` (Promise) helpers.
- `public/js/toast.js`, `public/js/confirm.js` — front-end toast/confirm helpers for the group-criteria manager.

## Files MODIFIED

- `review-bp-grps.php` — version header `3.8.1`→`3.9.0`; `BGR_PLUGIN_VERSION` `3.8.1`→`3.9.0`; removed `admin/wbcom/wbcom-admin-settings.php` from the include array.
- `admin/bgr-admin.php` — `BGR_Admin` reduced to its non-wrapper responsibilities (review CPT, `review_category` taxonomy, role capabilities, pending-review count badge). Removed the wrapper menu registration (`bp_group_review_add_submenu_page_admin_settings`), the tab-based `bp_group_review_admin_options_page` render, and `wbcom_hide_all_admin_notices_from_setting_page` (the new panel owns menu + notice suppression). Now requires + boots `BGR_Admin_Panel`. Class docblock rewritten to reflect post-migration scope.
- `admin/bgr-admin-feedback.php` — `bgr.png` path updated to `admin/assets/imgs/`.
- `includes/bgr-scripts.php` — group-criteria-admin enqueue now depends on the new `bgr-toast` + `bgr-confirm` scripts and ensures `bgr-reviews-css` (which now carries the toast/confirm CSS) is present on the group admin screen. The retained admin-screen enqueue (bgr-admin.js, color picker, selectize, sortable) is unchanged and still matches via the `'group-review-settings' === $page` clause.
- `gruntfile.js` — removed the three `admin/wbcom/...` build targets (cssmin:wbcom, uglify:wbcom, the wbcom rtlcss entry) and three orphan `admin/wbcom` glob lines in the dist copy task. Parses clean (`node -c`).
- `readme.txt` — `Stable tag` `3.8.1`→`3.9.0` + `= 3.9.0 =` changelog entry.

---

## Safe fixes applied

1. **8 native `alert()`/`confirm()` in `assets/js/bgr-group-criteria-admin.js`** (lines 118, 123, 148, 152, 160, 182, 186 + the surrounding error paths) → replaced with `bgrToast()` (validation/error) and `bgrConfirm()` (archive confirm, Promise-based). New front-end helpers `public/js/toast.js` + `public/js/confirm.js` enqueued as deps; styles bundled into `assets/css/bgr-reviews.css`.
2. **Native `confirm()` in `admin/assets/js/bgr-admin.js:206`** (deny-review) → `window.bgrConfirm()` modal (admin helper from `assets/js/admin.js`), with a graceful `Promise.resolve(true)` fallback.
3. **`wbcom_addons_cards` capability hardening** — the handler only existed inside the deleted `admin/wbcom/wbcom-admin-settings.php` (echo-only, no DB write). It is **removed entirely** with the wrapper, which satisfies the intent of the safe fix more cleanly than adding a cap to dead code. No `wbcom_addons_cards` registration remains anywhere in the tree.
4. **<40px tap target** at `assets/css/bgr-reviews.css:383` (youzify submit-review button: `padding:8px` + `line-height:15px` ≈ 31px) → `min-height:40px`, `padding:11px 12px`, `line-height:18px`, flex-centered. Meets the 40px tap-target contract.
5. **Version drift** — header + `BGR_PLUGIN_VERSION` + readme `Stable tag` all bumped `3.8.1` → `3.9.0` (matches branch); changelog stub added.

---

## Verification

- `php -l` — **clean** on every created/modified PHP file (controller, 6 views, `admin/bgr-admin.php`, `admin/bgr-admin-feedback.php`, `includes/bgr-scripts.php`, `review-bp-grps.php`, `uninstall.php`).
- WPCS (`wpcs` MCP) — `includes/admin/` directory: **0 errors, 0 warnings**. `review-bp-grps.php`: clean. `includes/bgr-scripts.php`: the only findings are **5 pre-existing** loose-comparison warnings (lines 181, 265) that are present in `HEAD` and untouched by this migration — no new violations introduced.
- `node -c gruntfile.js` — parses clean after build-config cleanup.
- Option-key preservation — old vs new proof: all four `update_option` / `register_setting` keys (`bgr_admin_general_settings`, `bgr_admin_criteria_settings`, `bgr_admin_display_settings`, `bp_group_review_email_settings`) unchanged; element IDs / field names the JS + Settings API depend on all confirmed present in the new views.
- Isolated load test (`wp eval`, no activation) — controller instantiates, `get_tabs()` returns welcome/general/criteria/display/emails, slug + option group correct.
- Render test (`wp eval`, no activation) — all 5 tab views render through `shell.php` and the hub renders, with zero PHP errors and the `.bgr-admin` shell present.
- No commit / push / activation / DB writes performed.

---

## Out of scope — TODOs (deferred, logged per brief)

1. **Unbounded query (perf bug):** `BGR_AJAX::bp_group_review_filter_ratings` in `includes/bgr-ajax.php` runs `get_posts( posts_per_page => -1 )` over the `review` CPT with no pagination — will not scale past a few hundred reviews. Also `bp_group_review_general_setting`/the General view call `groups_get_groups( per_page => -1 )` to populate the exclude-groups `<select>`, which is unbounded on sites with thousands of groups. Both are pre-existing and OUT of scope for this UX migration. **TODO:** add LIMIT/pagination (and consider an AJAX-searched group selector) in a dedicated performance release.
2. **3 nopriv AJAX endpoints** (`bp_group_review_filter_ratings`, `bp_group_submit_review`, `bp_group_remove_review` + accept/deny) are intentional public/member-context endpoints — left as-is (manifest marks the nonce-no-cap flags as false positives).
3. **REST surface** (4 `register_rest_route` → 7 endpoints under `buddypress/v1`) — left completely untouched.
4. **Legacy `admin/assets/css/bgr-admin.css` + selectize vendor assets** are retained because the General-tab group `<select>` (selectize), color picker, and `.wb-switch` rely on the legacy admin JS/CSS still enqueued for this screen. They are reachable, not dead. Not relocated; a future cleanup pass could fold the still-used selectize styles into `admin.css`.
5. **Emails-tab toggle JS mismatch (pre-existing):** `bgr-admin.js:177` binds a change handler to `#bgr-email` to show/hide `.review-email-section`, but the checkbox id is `#bgr-allow-email`. This latent mismatch predates the migration; markup id preserved verbatim for parity. **TODO:** reconcile the handler selector in a bug-fix pass.

## Needs human eyes

- Browser smoke on an install where the plugin is **active** (it is inactive on wbcom-free per the no-activation rule): click through all five tabs, save each (General/Criteria/Display via AJAX, Emails via options.php), confirm the deny-review and group-criteria confirm modals fire, and verify the WB Plugins hub renders with this plugin as a card alongside any un-migrated peers.
