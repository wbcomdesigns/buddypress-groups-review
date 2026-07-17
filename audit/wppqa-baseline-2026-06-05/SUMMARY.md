# wppqa Baseline — BuddyPress Group Reviews

- Date: 2026-06-05
- Branch: 3.9.0
- Plugin version (header/constant): 3.8.1
- Tool: wp-plugin-qa MCP

## Per-check results

| Check | Passed | Failed | Skipped |
|---|---|---|---|
| `wppqa_check_plugin_dev_rules` | 0 | 11 (errors) + 3 warnings | 0 |
| `wppqa_check_rest_js_contract` | 2 | 0 | 0 |
| `wppqa_check_wiring_completeness` | 0 | 0 | 1 (no `templates/` setting reads to scan) |

Release-readiness gate: **NOT release-ready** (`failed > 0` on dev-rules).

## plugin_dev_rules — top findings (real)

1. **alert()/confirm() banned (Rule 10)** — 8 occurrences. REAL.
   - `assets/js/bgr-group-criteria-admin.js:118,123,148,152,182,186` — `alert()`
   - `assets/js/bgr-group-criteria-admin.js:160` — `confirm()`
   - `admin/assets/js/bgr-admin.js:206` — `confirm()`
   - Fix: replace with toast (`window.{prefix}Toast`) + modal confirm. Out of scope for onboarding (no code changes).

2. **Tap target < 40px (Rule 4, low)** — 3 occurrences (same rule, source + min + rtl of one button).
   - `assets/css/bgr-reviews.css:383` (+ `min/` + `rtl/`). REAL but low severity.

## plugin_dev_rules — nonce-no-cap (pre-triaged FALSE POSITIVES)

The checker does not recognize `wp_ajax_nopriv_*` registration, so it flags public endpoints as missing a cap check. All 3 verified false positives:

- `includes/bgr-ajax.php:66` — `bp_group_review_filter_ratings`: public (nopriv) read-only widget render. No privileged action.
- `includes/bgr-ajax.php:394` — `bp_group_submit_review`: nopriv-registered but enforces `is_user_logged_in()` + `groups_is_user_member()` immediately after the nonce check.
- `admin/wbcom/wbcom-admin-settings.php:42` — `wbcom_addons_cards`: admin-area AJAX that only echoes back sanitized input; no state change. OLD-wrapper code.

## rest_js_contract

Clean (2 passed, 0 failed). No envelope mismatches detected between REST shapes and JS property access.

## wiring_completeness

Skipped — the checker only scans `templates/` for setting reads; this plugin reads its 4 option arrays through the global `$bgr` in `includes/` service code, so nothing to verify here (not a defect).
