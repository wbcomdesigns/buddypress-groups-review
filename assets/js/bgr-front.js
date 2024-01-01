jQuery(document).ready(function (e) {
  var review_label = bgr_front_js_object.review_label;
  if ('yes' == bgr_front_js_object.check_group_admin) {
    if ('yes' == bgr_front_js_object.auto_approve_reviews) {
      jQuery('#' + review_label + '-management').hide();
    }
  } else {
    jQuery('#' + review_label + '-management').hide();
  }
  if ('true' == bgr_front_js_object.exclude_groups) {
    jQuery('#nav-add-feedback').hide();
  }
  jQuery("#bgr_review_success_msg_modal").hide();
  jQuery("#request-review-list #bp-group-edit-add-review-submit").hide();

  // Open Add Review Form
  jQuery(document).on("click", "#add-review", function () {
    jQuery("#bgr-add-review-modal").css("display", "block");
  });

  jQuery(document).on("click", "#groupbutton-85 a", function (e) {
    var show_content = jQuery(this).attr("show");
    localStorage.setItem("bgr_show_form", show_content);
  });

  // Slide the add review form
  jQuery(document).on("click", "#add-review-no-popup", function () {
    jQuery(".bgr-group-review-no-popup-add-block").slideToggle();
  });

  // Accept review
  jQuery(document).on("click", ".accept-button", function () {
    var accept_review_id = jQuery(this).next().val();
    var accept_gid = jQuery(this).attr("data-group-type");
    jQuery.post(
      ajaxurl,
      {
        action: "bgr_accept_review",
        nonce: bgr_front_js_object.wbcom_nonce,
        accept_review_id: accept_review_id,
        group_id: accept_gid,
      },
      function (response) {
        location.reload();
      }
    );
  });

  // Deny review
  jQuery(document).on("click", ".deny-button", function () {
    var deny_review_id = jQuery(this).next().val();
    var deny_gid = jQuery(this).attr("data-group-type");
    jQuery.post(
      ajaxurl,
      {
        action: "bgr_deny_review",
        nonce: bgr_front_js_object.wbcom_nonce,
        deny_review_id: deny_review_id,
        group_id: deny_gid,
      },
      function (response) {
        location.reload();
      }
    );
  });

  // Delete review
  jQuery(document).on("click", ".remove-review-button", function () {
    var remove_review_id = jQuery(this).next().val();
    jQuery.post(
      ajaxurl,
      {
        action: "bgr_remove_review",
        nonce: bgr_front_js_object.wbcom_nonce,
        remove_review_id: remove_review_id,
      },
      function (response) {
        location.reload();
      }
    );
  });

  // Remove error mark on change of input field
  jQuery("#form-group-id").on("change", function () {
    jQuery(this).siblings(".bgr-error-fields").hide();
  });

  jQuery('textarea[name*= "review-desc"]').on("keydown", function () {
    jQuery(this).siblings(".bgr-error-fields").hide();
  });

  jQuery(".bgr-stars").on("click", function () {
    jQuery(this).parent().next(".bgr-error-fields").hide();
  });

  // Show & hide full description
  jQuery(document).on("click", ".expand-review-des", function () {
    var display = jQuery(this)
      .parent()
      .children(".review-full-description")
      .css("display");
    if (display === "block") {
      jQuery(".review-full-description").slideUp(500);
      jQuery(this).parent().children(".review-excerpt").slideDown(500);
      jQuery(this).text(bgr_front_js_object.view_more_text);
    } else {
      jQuery(".review-full-description").hide();
      jQuery(".review-excerpt").show();
      jQuery(".review-full-description").next().text("View More..");
      jQuery(this).parent().children(".review-excerpt").slideUp(500);
      jQuery(this).parent().children(".review-full-description").slideDown(500);
      jQuery(this).text(bgr_front_js_object.view_less_text);
    }
  });

  // Submit Group Review
  jQuery("#bgr-add-review-form").submit(function (e) {
    var loc;
    e.preventDefault();
    var rating_exist = [];
    var bgr_groupid = jQuery("#form-group-id", this).val();
    var bgr_review_desc = jQuery('textarea[name*= "review-desc"]', this).val();
    var rating_field_counter = jQuery(this)
      .children()
      .find("#rating_field_counter")
      .val();
    var empty_rate = 0;

    jQuery(".bgr_mrating", this).each(function () {
      var rate_val = jQuery(this).val();
      if (rate_val > 0) {
        empty_rate = empty_rate + 1;
      } else {
        jQuery(this).parent().next(".bgr-error-fields").show();
      }
      rating_exist.push(rate_val);
    });
    if (rating_field_counter > 0) {
      if (bgr_groupid == "") {
        jQuery("#form-group-id", this).siblings(".bgr-error-fields").show();
      } else {
        if (jQuery.inArray("0", rating_exist) == -1) {
          jQuery.post(
            ajaxurl,
            {
              action: "bgr_submit_review",
              nonce: bgr_front_js_object.wbcom_nonce,
              data: jQuery(this).serialize(),
            },
            function (response) {
              jQuery(".bgr-bp-success p")
                .html(response)
                .css("display", "block");
              jQuery(".bgr-bp-success").fadeIn();
              jQuery(".bgr-group-review-no-popup-add-block").hide();
              jQuery("#bgr-add-review-modal").hide();
              jQuery("#bgr-message").html(response);
              jQuery("#bgr-message").css("display", "block");
              // sessionStorage.reloadAfterPageLoad = true;
              var date = new Date();
              date.setTime(date.getTime() + 20 * 1000);
              jQuery.cookie("res_content", response, {
                expires: date,
              });
              // window.location.reload();
            }
          );
        }
      }
    } else {
      if (bgr_groupid == "" || bgr_review_desc == "") {
        if (bgr_groupid == "") {
          jQuery("#form-group-id", this).siblings(".bgr-error-fields ").show();
        }
        if (bgr_review_desc == "") {
          jQuery('textarea[name*= "review-desc"]', this)
            .siblings(".bgr-error-fields ")
            .show();
        }
      } else {
        jQuery.post(
          ajaxurl,
          {
            action: "bgr_submit_review",
            nonce: wbcom_plugin_installer_params.wbcom_nonce,
            data: jQuery(this).serialize(),
          },
          function (response) {
            jQuery(".bgr-bp-success p").html(response).css("display", "block");
            jQuery(".bgr-bp-success").fadeIn();
            jQuery(".bgr-group-review-no-popup-add-block").hide();
            jQuery("#bgr-add-review-modal").hide();
            jQuery("#bgr-message").html(response);
            jQuery("#bgr-message").css("display", "block");
            // sessionStorage.reloadAfterPageLoad = true;
            var date = new Date();
            date.setTime(date.getTime() + 20 * 1000);
            jQuery.cookie("res_content", response, {
              expires: date,
            });
            // window.location.reload();
          }
        );
      }
    }
  });

  // Ratings Widget filter.
  jQuery(document).on(
    "click",
    "#bp-group-rating-list-options > a",
    function (event) {
      event.preventDefault();
      var jQ = jQuery(this);
      jQ.siblings("a").removeClass("selected");
      var filter = jQ.attr("attr-val");
      var limit = jQ.parent().siblings(".group-rating-limit").val();
      console.log(limit);
      jQuery.post(
        ajaxurl,
        {
          action: "bgr_filter_ratings",
          filter: filter,
          limit: limit,
        },
        function (response) {
          var obj = JSON.parse(response);
          jQ.addClass("selected");
          jQ.parent().siblings("#bp-group-rating").html(obj.html);
        }
      );
    }
  );

  // Reviews tab filter.
  jQuery("#bp-group-reviews-filter-by").change(function () {
    var jQ = "";
    jQuery("#bp-group-reviews-filter-by option:selected").each(function () {
      var jQ = jQuery(this);
      var filter = jQ.val();
      jQuery.post(
        ajaxurl,
        {
          action: "bgr_reviews_filter",
          filter: filter,
        },
        function (response) {
          jQuery("#request-review-list").html(response);
        }
      );
    });
  });
});

/*----------------------------------------
 * Display message after review submit
 *-----------------------------------------*/
jQuery(function () {
  if (jQuery.cookie("res_content")) {
    jQuery(".error").hide();
    // jQuery('.bgr-bp-success p').text(jQuery.cookie('res_content'));
    // jQuery('.bgr-bp-success').show();
    jQuery.cookie("res_content", "", -1);
  }
});

jQuery(function () {
  var show_last_form;
  show_last_form = localStorage.getItem("bgr_show_form");
  if (show_last_form != null) {
    if (show_last_form == "form") {
      jQuery(".bgr-group-review-no-popup-add-block").show();
      localStorage.setItem("bgr_show_form", null);
    }
  }
});