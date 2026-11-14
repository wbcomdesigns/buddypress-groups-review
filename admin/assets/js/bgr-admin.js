jQuery.noConflict();

// wp.i18n helper (handle depends on wp-i18n; guarded for safety).
//
// This MUST be named `__`. `wp i18n make-pot` extracts by function name, so an
// aliased call - this file previously used `bgrAdminI18n( 'Text', 'domain' )` -
// is invisible to the extractor: the string never reaches the .pot and can
// never be translated. Do not rename it back.
var __ =
  window.wp && window.wp.i18n
    ? window.wp.i18n.__
    : function (text) {
        return text;
      };

jQuery(document).ready(function () {
  // Save Criteria Settings
  jQuery("#bgr-settings-updated").hide();

  // Handle dismissible notices
  jQuery(document).on('click', '.notice-dismiss', function() {
    jQuery(this).closest('.notice').fadeOut();
  });
  jQuery("#bgr-exclude-group-review").selectize({
    placeholder: __("Select groups", "bp-group-reviews"),
    plugins: ["remove_button"],
  });

  jQuery(document).on(
    "click",
    "#bgr-save-admin-criteria-settings",
    function () {
      jQuery(this).addClass("bgr-btn-ajax");
      jQuery(".bgr-admin-criteria-settings-spinner").show();
      var field_values = [];
      var criteria = [];
      jQuery("input[name=BGRDynamicTextBox]").each(function () {
        if (jQuery(this).val().trim() != "") {
          var criteria_val = jQuery(this).val();
          field_values.push(criteria_val);
          jQuery(this)
            .siblings(".wb-switch")
            .children(".bgr-criteria-state")
            .attr("data-attr", criteria_val);
        }
      });
      jQuery(".bgr-criteria-state").each(function () {
        if (jQuery(this).val().trim() != "" && jQuery(this).is(":checked")) {
          var criteriaAttr = jQuery(this).attr("data-attr");
          criteria.push(criteriaAttr);
        }
      });
      jQuery.post(
        ajaxurl,
        {
          action: "bp_group_review_save_admin_criteria_settings",
          nonce: bgr_admin_js.wbcom_nonce,
          field_values: field_values,
          active_criterias: criteria,
        },
        function (response) {
          if (response === "admin-criteria-settings-saved") {
            location.reload();
            jQuery("#bgr-settings-updated").show();
            jQuery("#bgr-save-admin-criteria-settings").removeClass(
              "bgr-btn-ajax"
            );
            jQuery(".bgr-admin-criteria-settings-spinner").hide();
          }
        }
      );
    }
  );

  // Save General Settings
  jQuery(document).on("click", "#bgr-save-admin-general-settings", function () {
    jQuery(this).addClass("bgr-btn-ajax");
    jQuery(".bgr-admin-general-settings-spinner").show();
    var exclude_groups = jQuery("#bgr-exclude-group-review").val();
    var bgr_multi_reviews = jQuery("#bgr-multi-reviews").is(":checked") ? "yes" : "no";
    var bgr_auto_approve_reviews = jQuery("#bgr-auto-approve-reviews").is( ":checked" ) ? "yes" : "no";
    var reviews_per_page = jQuery("#reviews_per_page").val();
    var bgr_allow_notification = jQuery("#bgr-notification").is(":checked") ? "yes" : "no";
    var bgr_allow_activity = jQuery("#bgr-activity").is(":checked") ? "yes" : "no";
    var bgr_enable_group_criteria = jQuery("#bgr-enable-group-criteria").is(":checked") ? "yes" : "no";

    jQuery.post(
      ajaxurl,
      {
        action: "bp_group_review_save_admin_general_settings",
        nonce: bgr_admin_js.wbcom_nonce,
        multi_reviews: bgr_multi_reviews,
        bgr_auto_approve_reviews: bgr_auto_approve_reviews,
        reviews_per_page: reviews_per_page,
        allow_notification: bgr_allow_notification,
        allow_activity: bgr_allow_activity,
        exclude_groups: exclude_groups,
        enable_group_criteria: bgr_enable_group_criteria
      },
      function (response) {
        if (response === "admin-general-settings-saved") {
          jQuery("#bgr-settings-updated").show();
          jQuery("#bgr-save-admin-general-settings").removeClass(
            "bgr-btn-ajax"
          );
          jQuery(".bgr-admin-general-settings-spinner").hide();
        }
      }
    );
  });

  // Save Display Settings
  jQuery(document).on("click", "#bgr-save-admin-display-settings", function () {
    jQuery(this).addClass("bgr-btn-ajax");
    jQuery(".bgr-admin-display-settings-spinner").show();
    var review_label = jQuery("#bgrReviewLabel").val();
    var manage_review_label = jQuery("#bgrManageReviewLabel").val();
    var bgr_rating_color = jQuery("#bgr-rating-color").val();
    jQuery.post(
      ajaxurl,
      {
        action: "bp_group_review_save_admin_display_settings",
        nonce: bgr_admin_js.wbcom_nonce,
        review_label: review_label,
        manage_review_label: manage_review_label,
        bgr_rating_color: bgr_rating_color,
      },
      function (response) {
        if (response === "admin-display-settings-saved") {
          jQuery("#bgr-settings-updated").show();
          jQuery("#bgr-save-admin-display-settings").removeClass(
            "bgr-btn-ajax"
          );
          jQuery(".bgr-admin-display-settings-spinner").hide();
        }
      }
    );
  });

  /*** Add new criteria field ***/
  jQuery("#bgr-field-add").on("click", function () {
    var div = jQuery("<div class='rating-review-div' />");
    div.html(BGRGetBGRDynamicTextBox(""));
    jQuery("#bgr-textbox-container")
      .append(div)
      .addClass("ui-sortable-handle");
  });

  /*** Remove criteria field ****/
  jQuery("#bgr-textbox-container").on("click", ".bgr-remove", function () {
    jQuery(this).closest(".rating-review-div").remove();
  });

  /*** Keep each Remove button's accessible name in step with its criterion ***/
  jQuery("#bgr-textbox-container").on("input", "input.draggable", function () {
    var remove = jQuery(this).siblings(".bgr-remove");
    if (this.value) {
      /* translators: %s: criterion name. */
      remove.attr("aria-label", wp.i18n.sprintf(__("Remove %s", "bp-group-reviews"), this.value));
    } else {
      remove.removeAttr("aria-label");
    }
  });

  /*** FAQ(s) accordion js ***/
  jQuery(document).on("click", ".bgr-faq-accordion", function () {
    var display = jQuery(this).next().css("display");
    if (display === "block") {
      jQuery(".bgr-faq-panel").slideUp(500);
      jQuery(".bgr-faq-accordion").removeClass("bgr-faq-accordion-active");
    } else {
      jQuery(".bgr-faq-panel").hide();
      jQuery(".bgr-faq-accordion").removeClass("bgr-faq-accordion-active");
      jQuery(this).next().slideDown(500);
      jQuery(this).addClass("bgr-faq-accordion-active");
    }
  });

  /*** Make Draggable admin criteria input fields ***/
  jQuery("#bgr-textbox-container").sortable();
  jQuery("#bgr-textbox-container").disableSelection();

  // Support tab accordian
  var acc = document.getElementsByClassName("bgr-accordion");
  var i;
  for (i = 0; i < acc.length; i++) {
    acc[i].onclick = function () {
      this.classList.toggle("active");
      var panel = this.nextElementSibling;
      if (panel.style.maxHeight) {
        panel.style.maxHeight = null;
      } else {
        panel.style.maxHeight = panel.scrollHeight + "px";
      }
    };
  }

  jQuery(document).on("click", ".bgr-accordion", function () {
    return false;
  });

  // Each email toggle shows/hides its own subject + message rows. Bind all
  // three and sync the initial state on load so a saved-off toggle starts
  // collapsed instead of only reacting to the next change event.
  var bgrEmailToggles = {
    "#bgr-allow-email": ".review-email-section",
    "#bgr-accept-enable": ".review-accept-email-section",
    "#bgr-deny-email": ".review-deny-email-section",
  };

  function bgrSyncEmailSection(toggle, section) {
    if (jQuery(toggle).prop("checked") === true) {
      jQuery(section).show();
    } else {
      jQuery(section).hide();
    }
  }

  jQuery.each(bgrEmailToggles, function (toggle, section) {
    if (!jQuery(toggle).length) {
      return;
    }
    bgrSyncEmailSection(toggle, section);
    jQuery(document).on("change", toggle, function () {
      bgrSyncEmailSection(toggle, section);
    });
  });

  jQuery(document).on("click", ".bgr-approve-review", function () {
    var review_id = jQuery(this).data("rid");
    jQuery(this).html(__("Approving..", "bp-group-reviews"));
    jQuery.post(
      ajaxurl,
      {
        action: "bp_group_review_admin_approve_review",
        review_id: review_id,
        nonce: bgr_admin_js.wbcom_nonce,
      },
      function (response) {
        if (response === "review-approved-successfully") {
          window.location.href = window.location.href;
        }
      }
    );
  });

  // Deny review from row action.
  jQuery(document).on("click", ".bgr-deny-review", function () {
    var $btn = jQuery(this);
    var review_id = $btn.data("rid");
    var denyMsg =
      (window.bgrAdminPanel &&
        window.bgrAdminPanel.i18n &&
        window.bgrAdminPanel.i18n.denyReview) ||
      __(
        "Are you sure you want to deny this review? It will be moved to trash.",
        "bp-group-reviews"
      );

    // Promise-based confirm modal (no native confirm()). Falls back to
    // immediate continue if the helper is somehow unavailable.
    var ask =
      typeof window.bgrConfirm === "function"
        ? window.bgrConfirm({ message: denyMsg, tone: "danger" })
        : Promise.resolve(true);

    ask.then(function (ok) {
      if (!ok) {
        return;
      }
      $btn.html(__("Denying..", "bp-group-reviews"));
      jQuery.post(
        ajaxurl,
        {
          action: "bp_group_review_admin_deny_review",
          review_id: review_id,
          nonce: bgr_admin_js.wbcom_nonce,
        },
        function (response) {
          if (response === "review-denied-successfully") {
            window.location.href = window.location.href;
          }
        }
      );
    });
  });
});

/*** Function for add new criteria text field ***/
function BGRGetBGRDynamicTextBox(value) {
  return (
    '<span class="move-icons">&equiv;</span><input name="BGRDynamicTextBox" class="draggable" type="text" value="' +
    value +
    '" />' +
    '<button type="button" class="button-link button-link-delete bgr-remove">' +
    __("Remove", "bp-group-reviews") +
    '</button><label class="wb-switch"><input type="checkbox" class="bgr-criteria-state" name="bgr-criteria-state" data-attr="" checked="checked" ><div class="wb-slider wb-round"></div></label>'
  );
}
