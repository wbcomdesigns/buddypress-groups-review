jQuery.noConflict();
jQuery(document).ready(function () {
  // Save Criteria Settings
  jQuery("#bgr-settings-updated").hide();
  jQuery("#bgr-exclude-group-review").selectize({
    placeholder: "Select groups",
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
          action: "bgr_save_admin_criteria_settings",
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
    if (jQuery("#bgr-allow-popup").is(":checked")) {
      allow_popup = "yes";
    } else {
      allow_popup = "no";
    }
    var bgr_multi_reviews = jQuery("#bgr-multi-reviews").is(":checked")
      ? "yes"
      : "no";
    var bgr_auto_approve_reviews = jQuery("#bgr-auto-approve-reviews").is(
      ":checked"
    )
      ? "yes"
      : "no";
    var reviews_per_page = jQuery("#reviews_per_page").val();
    var review_email_subject = jQuery("#review_email_subject").val();
    var review_email_message = jQuery("#review_email_message").val();
    var bgr_allow_email = jQuery("#bgr-email").is(":checked") ? "yes" : "no";
    var bgr_allow_notification = jQuery("#bgr-notification").is(":checked")
      ? "yes"
      : "no";
    var bgr_allow_activity = jQuery("#bgr-activity").is(":checked")
      ? "yes"
      : "no";

    jQuery.post(
      ajaxurl,
      {
        action: "bgr_save_admin_general_settings",
        nonce: bgr_admin_js.wbcom_nonce,
        multi_reviews: bgr_multi_reviews,
        bgr_auto_approve_reviews: bgr_auto_approve_reviews,
        reviews_per_page: reviews_per_page,
        allow_email: bgr_allow_email,
        allow_notification: bgr_allow_notification,
        allow_activity: bgr_allow_activity,
        exclude_groups: exclude_groups,
        review_email_subject: review_email_subject,
        review_email_message: review_email_message,
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
        action: "bgr_save_admin_display_settings",
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
  jQuery("#bgr-field-add").bind("click", function () {
    var div = jQuery("<div/>");
    div.html(BGRGetBGRDynamicTextBox(""));
    jQuery("#bgr-textbox-container")
      .append(div)
      .css("class", "rating-review-div ui-sortable-handle");
  });

  /*** Remove criteria field ****/
  jQuery("body").on("click", ".remove", function () {
    jQuery(this).closest("div").remove();
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

  jQuery(document).on("change", "#bgr-email", function () {
    if (jQuery(this).prop("checked") == true) {
      jQuery(".review-email-section").show();
    } else {
      jQuery(".review-email-section").hide();
    }
  });

  jQuery(document).on("click", ".bgr-approve-review", function () {
    var review_id = jQuery(this).data("rid");
    jQuery(this).html("Approving..");
    jQuery.post(
      ajaxurl,
      {
        action: "bgr_admin_approve_review",
        review_id: review_id,
        nonce: bgr_admin_js.wbcom_nonce,
      },
      function (response) {
        if (response == "review-approved-successfully") {
          window.location.href = window.location.href;
        } else {
          console.log("Review not approved!");
        }
      }
    );
  });
});

/*** Function for add new criteria text field ***/
function BGRGetBGRDynamicTextBox(value) {
  return (
    '<span>&equiv;</span><input name = "BGRDynamicTextBox" class="draggable" type="text" value = "' +
    value +
    '" />&nbsp;' +
    '<input type="button" value="Remove" class="remove button button-secondary bgr-remove" /><label class="wb-switch"><input type="checkbox" class="bgr-criteria-state" name="bgr-criteria-state" data-attr="" checked="checked" ><div class="wb-slider wb-round"></div></label>'
  );
}
