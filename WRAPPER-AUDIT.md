# WRAPPER-AUDIT — buddypress-groups-review (branch 3.9.0)

**Date:** 2026-06-05
**Auditor:** AutoVAP read-only audit (no code modified)
**Wrapper type:** NEW card-panel (`includes/admin/views/shell.php` present)
**Controller:** `includes/admin/class-bgr-admin-panel.php` (`BGR_Admin_Panel`)

---

## 1. Controller Contract

| Method | Present? | Notes |
|--------|----------|-------|
| `get_tabs()` | YES — static, returns welcome/general/criteria/display/emails with icon+group keys | |
| `register()` | YES — hooks `admin_menu`, `admin_menu @999` (hub takeover), `admin_init`, `admin_enqueue_scripts`, `in_admin_header` (suppress_foreign_notices) | |
| `add_menu()` | YES — `manage_options`-capped; creates `wbcomplugins` top-level if absent; submenu `group-review-settings` | No old `wbcom-designs` top-level remains |
| `takeover_hub_landing()` | YES — priority-999 admin_menu hook; `remove_all_actions('toplevel_page_wbcomplugins')` then re-adds own render | |
| `register_settings()` | YES — fires on `admin_init`; registers `bp_group_review_email_settings` with `bp_group_review_sanitize_email_settings` | |
| `enqueue_assets()` | YES — screen-scoped via `is_our_screen()`; enqueues `assets/css/admin.css` + `assets/js/admin.js` with `wp_localize_script` i18n payload | `$hook_suffix` is intentionally discarded (uses `get_current_screen()`) |
| `is_our_screen()` | YES — private; regex-matches `_page_group-review-settings$` OR `toplevel_page_wbcomplugins` | |
| `suppress_foreign_notices()` | YES — `in_admin_header` priority 1; removes `admin_notices` + `all_admin_notices` on matched screen | |
| `render_hub()` | YES — includes `includes/admin/views/hub.php` | |
| `render_page()` | YES — reads `?tab=`, validates against `get_tabs()`, resolves view file via `$view_map`, includes `shell.php` | |

**Missing from contract:** `sanitize_settings()` is not a unified method; instead each AJAX-saved tab has its own sanitization inside the AJAX handler (General/Criteria/Display) and the Emails tab uses the global `bp_group_review_sanitize_email_settings()`. This is architecturally correct for this plugin's mixed save model and is explicitly documented. No contract gap.

**Additional note:** `admin/bgr-admin.php` correctly bootstraps the controller via `require_once BGR_PLUGIN_PATH . 'includes/admin/class-bgr-admin-panel.php'` and `new BGR_Admin_Panel()->register()`.

---

## 2. Path-Constant Check: PASS

`BGR_PLUGIN_PATH` and `BGR_PLUGIN_URL` are defined in `review-bp-grps.php` line 58–59:

```php
define( 'BGR_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'BGR_PLUGIN_URL',  plugin_dir_url( __FILE__ ) );
```

`__FILE__` is the plugin root file. Every view include and asset URL in `BGR_Admin_Panel` uses these constants (`BGR_PLUGIN_PATH . 'includes/admin/views/...'`, `BGR_PLUGIN_URL . 'assets/css/admin.css'`). No path is derived from a constant defined inside `includes/`. All asset URLs resolve to the plugin root. **PASS.**

---

## 3. Tab ↔ View Map

| Tab slug | View file | View exists? | Tab in `get_tabs()`? |
|----------|-----------|-------------|----------------------|
| welcome  | `includes/admin/views/welcome.php` | YES | YES |
| general  | `includes/admin/views/settings-general.php` | YES | YES |
| criteria | `includes/admin/views/settings-criteria.php` | YES | YES |
| display  | `includes/admin/views/settings-display.php` | YES | YES |
| emails   | `includes/admin/views/settings-emails.php` | YES | YES |
| hub      | `includes/admin/views/hub.php` | YES | not a tab (hub landing) |

**Dead nav links:** None.
**Orphan views:** `hub.php` has no sidebar tab, but it is the hub landing page (not a settings tab) — correct.
**Map: COMPLETE.**

---

## 4. Option Wiring — End-to-End

### 4a. `bgr_admin_general_settings`

| Check | Result |
|-------|--------|
| **Registered** | Custom AJAX `bp_group_review_save_admin_general_settings` (no `register_setting`; AJAX owns the full array rebuild). Verified correct. |
| **Rendered** | `settings-general.php`: fields `#bgr-multi-reviews`, `#bgr-auto-approve-reviews`, `#reviews_per_page`, `#bgr-notification`, `#bgr-activity`, `#bgr-enable-group-criteria`, `#bgr-exclude-group-review`. All IDs present and match exactly what `bgr-admin.js:62–71` reads. |
| **Saved + Sanitized** | `bgr-ajax.php:326–354`: `sanitize_text_field` on all scalar values; `array_map('absint',...)` on `exclude_groups`; `update_option('bgr_admin_general_settings', ...)`. Nonce `admin-ajax-nonce` verified; `manage_options` cap checked. |
| **Read-back** | `bgr-globals.php:23–46` reads `get_option('bgr_admin_general_settings')` → populates `$bgr`. `settings-general.php:43–56` reads from `$bgr`. Round-trip complete. |
| **Verdict** | PASS |

### 4b. `bgr_admin_criteria_settings`

| Check | Result |
|-------|--------|
| **Registered** | Custom AJAX `bp_group_review_save_admin_criteria_settings`. |
| **Rendered** | `settings-criteria.php`: `#bgr-textbox-container`, `BGRDynamicTextBox` inputs, `.bgr-criteria-state` toggles, `#bgr-field-add`, `#bgr-save-admin-criteria-settings`. All IDs/classes match `bgr-admin.js` selectors. |
| **Saved + Sanitized** | `bgr-ajax.php:261–317`: `array_map('sanitize_text_field',...)` + `array_unique` on both `add_review_rating_fields` and `active_rating_fields`. Nonce + `manage_options` checked. `update_option('bgr_admin_criteria_settings', ...)`. |
| **Read-back** | `bgr-globals.php:79–90` → `$bgr['review_rating_fields']` / `$bgr['active_rating_fields']`. `settings-criteria.php:23–24` reads these. Round-trip complete. |
| **Verdict** | PASS |

### 4c. `bgr_admin_display_settings`

| Check | Result |
|-------|--------|
| **Registered** | Custom AJAX `bp_group_review_save_admin_display_settings`. |
| **Rendered** | `settings-display.php`: `#bgrReviewLabel`, `#bgrManageReviewLabel`, `#bgr-rating-color` (class `bgr-review-color`). All IDs match `bgr-admin.js:99–116`. |
| **Saved + Sanitized** | `bgr-ajax.php:363–383`: `sanitize_text_field` on all three values; `update_option('bgr_admin_display_settings', ['review_label', 'manage_review_label', 'bgr_rating_color'])`. Key naming: AJAX saves `bgr_rating_color`; globals reads `bgr_admin_display_settings['bgr_rating_color']` → `$bgr['rating_color']`; view reads `$bgr['rating_color']`. The intermediate key name difference (`bgr_rating_color` in DB array vs `rating_color` in `$bgr`) is intentional and consistent. |
| **Read-back** | `bgr-globals.php:94–108` → `$bgr['rating_color']`. `settings-display.php:24` reads it. Round-trip complete. |
| **Verdict** | PASS |

### 4d. `bp_group_review_email_settings`

| Check | Result |
|-------|--------|
| **Registered** | `BGR_Admin_Panel::register_settings()` on `admin_init` — `register_setting('bp_group_review_email_settings', 'bp_group_review_email_settings', ['sanitize_callback' => 'bp_group_review_sanitize_email_settings'])`. Also registered redundantly in `review-bp-grps.php:269–278` via `admin_menu` hook (legacy; identical group/name/callback — WordPress treats duplicate calls as idempotent, no data-loss risk). |
| **Rendered** | `settings-emails.php`: `<form action="options.php">` with `settings_fields('bp_group_review_email_settings')`. Fields: `bp_group_review_email_settings[bgr_allow_email]` (id `bgr-allow-email`), `bp_group_review_email_settings[bgr_accept_enable]` (id `bgr-accept-enable`), `bp_group_review_email_settings[bgr_deny_email]` (id `bgr-deny-email`), `[review_email_subject]`, `[review_email_message]`, `[review_accept_email_subject]`, `[review_accept_email_message]`, `[review_deny_email_subject]`, `[review_deny_email_message]`. All names match what the sanitizer and globals read. |
| **Saved + Sanitized** | `bp_group_review_sanitize_email_settings()` in `review-bp-grps.php:248–263`: `sanitize_key` on keys, `sanitize_text_field` on scalar values (and arrays). Sanitize callback wired to `register_setting`. |
| **Read-back** | `bgr-globals.php:55–75` reads `get_option('bp_group_review_email_settings')` → fields used throughout notification/email dispatch logic. `settings-emails.php:24–48` reads and pre-populates defaults. Round-trip complete. |
| **Verdict** | PASS with NOTE: legacy double `register_setting` registration (redundant, harmless, but should be cleaned up in a separate pass) |

---

## 5. Multi-Tab Data-Loss Guard

**Verdict: N/A — not needed.**

Each option key is owned by exactly one tab, with no cross-tab overlap:
- `bgr_admin_general_settings` → General tab only
- `bgr_admin_criteria_settings` → Criteria tab only
- `bgr_admin_display_settings` → Display tab only
- `bp_group_review_email_settings` → Emails tab only

The Playbook 7.1 sentinel-guarded merge is explicitly not required here. Confirmed in `BUILD-LOG.md` and by direct code inspection. **No data-loss risk.**

---

## 6. Known Latent Bug: Email Toggle ID Mismatch — CONFIRMED PRESENT

**Location:** `admin/assets/js/bgr-admin.js:177`
**Finding:** The toggle handler binds to `#bgr-email`:
```javascript
jQuery(document).on("change", "#bgr-email", function () { ... });
```
The actual checkbox ID in `includes/admin/views/settings-emails.php:63` is `id="bgr-allow-email"`.

**Effect:** The `.review-email-section` rows (email subject/body) never show/hide in response to the toggle checkbox because the jQuery selector never matches any element. The setting still saves correctly via the `name` attribute (`bp_group_review_email_settings[bgr_allow_email]`) — only the conditional visibility behavior is broken.

**Classification:** Medium bug (broken UX, not a data-loss or security issue). Pre-existing latent bug, explicitly flagged in `BUILD-LOG.md` section "Out of scope — Emails-tab toggle JS mismatch". **MUST FIX before release.**

---

## 7. REST Routes — VERIFIED UNTOUCHED

4 `register_rest_route` calls confirmed in the codebase:
- `includes/bgr-bp-rest-integration.php:232` (GET+POST `/groups/{id}/reviews`)
- `includes/bgr-bp-rest-integration.php:291` (GET+PUT/PATCH+DELETE `/groups/{id}/reviews/{review_id}`)
- `includes/bgr-group-criteria-ajax.php:304` (GET `/groups/{id}/criteria`)
- `includes/bgr-group-criteria-ajax.php:337` (PUT/PATCH `/groups/{id}/criteria`)

All 4 `register_rest_route` blocks → 7 logical endpoints under `buddypress/v1`. No changes to REST routes, permission callbacks, or handlers. **REST surface PASS.**

---

## 8. alert()/confirm() Replacement — VERIFIED COMPLETE

**`assets/js/bgr-group-criteria-admin.js`:** No `alert()` or native `confirm()` calls remain. All 8 violations listed in manifest replaced:
- Validation errors (`alert`) → `self.notify(message, 'warning')` which calls `window.bgrToast()`
- Archive confirm → `window.bgrConfirm({...})` (Promise-based)

**`admin/assets/js/bgr-admin.js:206` (deny review):** Native `confirm()` replaced with:
```javascript
var ask = typeof window.bgrConfirm === 'function'
  ? window.bgrConfirm({ message: denyMsg, tone: 'danger' })
  : Promise.resolve(true);
ask.then(function(ok) { ... });
```
Correct Promise-based implementation with `Promise.resolve(true)` fallback.

**New helpers:** `assets/js/admin.js` exports `window.bgrToast` and `window.bgrConfirm`. `public/js/toast.js` and `public/js/confirm.js` provide front-end equivalents for group criteria UI. All three enqueued with correct dependencies. **PASS.**

---

## 9. Tap-Target Fix — VERIFIED

`assets/css/bgr-reviews.css:383–396` (youzify submit button):
```css
body[class*="youzify"] button.btn.btn-default.bgr-submit-review,
body[class*="youzify"] .bgr-col-2.bgr-generic-button .bgr-col-12.generic-button a,
body[class*="youzify"] .generic-button a.button {
    min-height: 40px;
    padding: 11px 12px;
    line-height: 18px;
    box-sizing: border-box;
    display: inline-flex;
    ...
}
```
`padding: 11px × 2 + line-height: 18px = 40px + `min-height: 40px` enforced. Meets the 40px WCAG 2.5.5 / Wbcom tap-target contract. **PASS.**

---

## 10. Version Consistency — VERIFIED

| Location | Value |
|----------|-------|
| Plugin header (`review-bp-grps.php:19`) | `3.9.0` |
| `BGR_PLUGIN_VERSION` constant (`review-bp-grps.php:57`) | `3.9.0` |
| `readme.txt` Stable tag (line 8) | `3.9.0` |
| `audit/manifest.json` version_note | "dev branch is 3.9.0" (manifest predates bump; note is stale but manifest itself is not authoritative for release) |

All three authoritative locations are consistent at `3.9.0`. **PASS.**

---

## 11. Legacy Wrapper Removal — VERIFIED COMPLETE

| Check | Result |
|-------|--------|
| `admin/wbcom/` directory | DELETED — not present in filesystem |
| `admin/review-admin-options-page.php` | DELETED |
| `admin/bgr-welcome-page.php` | DELETED |
| `[wbcom_admin_setting_header]` shortcode registration | GONE with `wbcom-admin-settings.php` |
| `wbcom_addons_cards` AJAX handler | GONE with `wbcom-admin-settings.php` |
| `wbcom-plugins-page`, `wbcom-support-page`, `wbcom-license-page` submenu entries | Referenced only in `hub.php` as the **filter-out list** (these slugs are excluded from the hub card grid) — correct usage |
| `in_admin_header` notice suppression replacing `wbcom_hide_all_admin_notices_from_setting_page` | Implemented in `BGR_Admin_Panel::suppress_foreign_notices()` |

**No dangling functional references to deleted wrapper files.** The two grep hits are comments in docblocks, which are acceptable. **PASS.**

---

## 12. Uninstall Cleanup — VERIFIED

`uninstall.php:74–77`:
```php
delete_option( 'bgr_admin_general_settings' );
delete_option( 'bgr_admin_criteria_settings' );
delete_option( 'bgr_admin_display_settings' );
delete_option( 'bp_group_review_email_settings' );
```
All 4 plugin options deleted. `WP_UNINSTALL_PLUGIN` guard present at line 22. `BGR_REMOVE_DATA_ON_UNINSTALL` constant guard also present (opt-in data removal). **PASS.**

---

## 13. Hygiene: Screen-Scoped Enqueue

`BGR_Admin_Panel::enqueue_assets()` uses `get_current_screen()` + `is_our_screen()` — assets load only on `*_page_group-review-settings` or `toplevel_page_wbcomplugins`. **PASS.**

`BGRScriptsStyles::bp_group_review_admin_custom_variables()` in `bgr-scripts.php:265` has a **stale condition string** `'wb-plugins_page_group-review-settings' == $screen->base`. The correct screen base is `wbcomplugins_page_group-review-settings` (parent slug `wbcomplugins`, not `wb-plugins`). This condition is dead (never matches). The assets still load because the fallback `'group-review-settings' == $page` and `'toplevel_page_wbcomplugins' == $screen->base` cover the two real cases. **Low severity cosmetic bug — dead condition, no runtime impact.**

---

## 14. Out of Scope — Confirmed Flagged

**Unbounded query:** `bgr-ajax.php` `bp_group_review_filter_ratings` uses `get_posts(posts_per_page => -1)`. `settings-general.php:50–56` uses `groups_get_groups(per_page => -1)` for the Exclude Groups selector. Both are pre-existing, out of scope for this wrapper migration, and documented in `BUILD-LOG.md`. Acknowledged and flagged for a dedicated performance release.

---

## 15. Double `register_setting` Registration

`review-bp-grps.php:269–279` registers `bp_group_review_email_settings` on `admin_menu`. `BGR_Admin_Panel::register_settings()` registers the same option on `admin_init`. WordPress's `register_setting` is additive/idempotent for identical group+name — the sanitize callback from the later call (admin_init) wins. Both use the same callback. No data-loss; redundant legacy call should be removed in a cleanup pass.

---

## Severity-Ranked Findings

| # | Severity | Finding | File:line | Suggested fix journey |
|---|----------|---------|-----------|----------------------|
| 1 | **Medium** | Email toggle handler binds `#bgr-email` but checkbox id is `#bgr-allow-email` — `.review-email-section` show/hide is permanently broken | `admin/assets/js/bgr-admin.js:177` | `bug-fix`: change `#bgr-email` → `#bgr-allow-email` |
| 2 | **Low** | Dead screen-base condition in `bgr-scripts.php:265` — `'wb-plugins_page_group-review-settings'` never matches; was the legacy parent slug `wb-plugins`. Assets load via fallback conditions, so no runtime impact | `includes/bgr-scripts.php:265` | `cleanup`: remove or update the stale string |
| 3 | **Low** | Double `register_setting` for `bp_group_review_email_settings` — once on `admin_menu` in root file, once on `admin_init` in controller. Harmless but redundant | `review-bp-grps.php:269–279` | `cleanup`: remove the legacy `admin_menu` registration from root file |
| 4 | **Info** | Unbounded `get_posts(posts_per_page => -1)` in filter-ratings AJAX handler and `groups_get_groups(per_page => -1)` in General tab view — pre-existing, out of scope | `includes/bgr-ajax.php` / `settings-general.php:50` | Dedicated performance release |

---

## Overall Verdict

**SHIP-CLEAN with one must-fix before release.**

The card-panel wrapper migration is structurally correct and complete:
- All four options wire end-to-end with no data-loss risk.
- All 8 native `alert()`/`confirm()` calls replaced.
- 40px tap-target rule met.
- Version 3.9.0 consistent across all 3 authoritative locations.
- REST surface untouched.
- Legacy wrapper fully deleted with no dangling functional references.
- Uninstall deletes all 4 options.
- Path constants resolve to plugin root.
- Tab ↔ view map complete.

**Must-fix before release (item #1):** Change `#bgr-email` → `#bgr-allow-email` in `admin/assets/js/bgr-admin.js:177`. This is a pre-existing bug that was knowingly preserved for migration parity, but it means the "show/hide email subject+body rows" behavior on the Emails tab does not work at all in the new wrapper (nor did it in the old one). It is a visible UX defect on the first settings tab a new user is likely to click.

**Fix-before-ship list:** `admin/assets/js/bgr-admin.js:177` (and its companion `.min.js`).
**Defer to cleanup pass:** `includes/bgr-scripts.php:265` (dead condition), `review-bp-grps.php:269–279` (legacy double `register_setting`).
**Defer to performance release:** Unbounded queries.
