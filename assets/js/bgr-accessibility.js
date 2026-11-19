/**
 * Accessibility improvements for BuddyPress Group Reviews.
 *
 * @package BuddyPress_Group_Review
 * @since 3.5.0
 */

(function ($) {
  'use strict';

  /**
   * i18n dictionary localized from PHP (see BGRScriptsStyles::
   * bp_group_review_custom_variables). The literals below are fallbacks
   * only — PHP is the translation home for every string here.
   */
  var i18n = window.bgr_a11y_i18n || {};

  /**
   * Read a translated string, falling back to the English literal.
   *
   * @param {string} key      Key in the localized i18n object.
   * @param {string} fallback English fallback.
   * @return {string} Translated string.
   */
  function t(key, fallback) {
    return i18n[key] || fallback;
  }

  /**
   * Minimal sprintf for %1$s / %2$s placeholders, so translators keep
   * control of word order instead of us concatenating fragments.
   *
   * @param {string} format Format string containing %1$s style tokens.
   * @param {...string} args Replacement values.
   * @return {string} Formatted string.
   */
  function format(fmt) {
    var args = Array.prototype.slice.call(arguments, 1);
    return String(fmt).replace(/%(\d+)\$s/g, function (match, index) {
      var value = args[index - 1];
      return 'undefined' !== typeof value ? value : match;
    });
  }

  /**
   * Initialize accessibility features.
   */
  function initAccessibility() {
    // Add ARIA labels to star ratings
    addStarRatingAccessibility();

    // Improve form accessibility
    improveFormAccessibility();

    // Add keyboard navigation for modals
    addModalKeyboardSupport();

    // Improve button accessibility
    improveButtonAccessibility();

    // Add skip links for screen readers
    addSkipLinks();
  }

  /**
   * Add ARIA labels and keyboard support to star ratings.
   */
  function addStarRatingAccessibility() {
    // Each .bgr-stars span is one star; its row (.multi-review) holds the criteria name.
    $('.bgr-stars').each(function () {
      var $label = $(this).closest('.multi-review').children().first().clone();
      $label.children().remove();
      var criteriaName = $.trim($label.text()) || t('defaultCriteria', 'Rating');

      $(this)
        .attr({
          'role': 'button',
          'tabindex': '0',
          'aria-label': format(
            t('rateStars', 'Rate %1$s out of 5 stars for %2$s'),
            $(this).data('attr'),
            criteriaName
          ),
        })
        .on('keydown', function (e) {
          if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            $(this).trigger('click');
          }
        });
    });

    $('.bgr-criteria-row').attr('role', 'group');
  }

  /**
   * Improve form accessibility.
   */
  function improveFormAccessibility() {
    // Ensure all form fields have proper labels
    $('input[name="review-subject"]').attr({
      'aria-label': t('reviewSubject', 'Review subject'),
      'aria-required': 'true'
    });

    $('textarea[name="review-desc"]').attr({
      'aria-label': t('reviewDesc', 'Review description'),
      'aria-required': 'true'
    });

    // Add ARIA live region for error messages
    if (!$('#bgr-error-announcer').length) {
      $('body').append('<div id="bgr-error-announcer" class="screen-reader-text" aria-live="assertive" aria-atomic="true"></div>');
    }

    // Announce errors to screen readers
    $('.bgr-error-fields').each(function () {
      if ($(this).is(':visible')) {
        var errorText = $(this).text();
        $('#bgr-error-announcer').text(errorText);
      }
    });

    // Add aria-invalid to fields with errors
    $('.bgr-error-fields').each(function () {
      $(this).prev('input, textarea, select').attr('aria-invalid', 'true');
    });

    // Remove aria-invalid when error is cleared
    $('input, textarea, select').on('input change', function () {
      if ($(this).next('.bgr-error-fields').is(':hidden')) {
        $(this).removeAttr('aria-invalid');
      }
    });
  }

  /**
   * Add keyboard navigation support for modals.
   */
  function addModalKeyboardSupport() {
    var $modal = $('#bgr-add-review-modal');

    if ($modal.length) {
      // Trap focus within modal when open
      $modal.on('keydown', function (e) {
        if (e.key === 'Tab') {
          var focusableElements = $modal.find('a, button, input, textarea, select, [tabindex]:not([tabindex="-1"])');
          var firstElement = focusableElements.first();
          var lastElement = focusableElements.last();

          if (e.shiftKey) {
            if (document.activeElement === firstElement[0]) {
              e.preventDefault();
              lastElement.focus();
            }
          } else {
            if (document.activeElement === lastElement[0]) {
              e.preventDefault();
              firstElement.focus();
            }
          }
        }

        // Close modal on Escape
        if (e.key === 'Escape') {
          closeModal();
        }
      });

      // Add ARIA attributes to modal
      $modal.attr({
        'role': 'dialog',
        'aria-modal': 'true',
        'aria-labelledby': 'bgr-modal-title'
      });

      // Ensure modal title has ID
      $modal.find('h2, h3').first().attr('id', 'bgr-modal-title');
    }
  }

  /**
   * Close modal and return focus.
   */
  function closeModal() {
    var $modal = $('#bgr-add-review-modal');
    $modal.css('display', 'none');
    // Return focus to trigger button
    $('#add-review').focus();
  }

  /**
   * Improve button accessibility.
   */
  function improveButtonAccessibility() {
    // Add descriptive ARIA labels to action buttons
    $('.accept-button').attr('aria-label', t('acceptReview', 'Accept this review'));
    $('.deny-button').attr('aria-label', t('denyReview', 'Deny this review'));
    $('.remove-review-button').attr('aria-label', t('deleteReview', 'Delete this review'));

    // Ensure buttons are keyboard accessible
    $('.button, .generic-button a').each(function () {
      if (!$(this).is('button') && !$(this).is('input[type="submit"]')) {
        $(this).attr({
          'role': 'button',
          'tabindex': '0'
        });

        // Add keyboard support for link buttons
        $(this).on('keypress', function (e) {
          if (e.which === 13 || e.which === 32) {
            e.preventDefault();
            $(this).trigger('click');
          }
        });
      }
    });
  }

  /**
   * Add skip links for screen readers.
   */
  function addSkipLinks() {
    if (!$('#bgr-skip-to-reviews').length && $('#group-reviews-list').length) {
      var skipLink = $('<a>', {
        'id': 'bgr-skip-to-reviews',
        'href': '#group-reviews-list',
        'class': 'skip-link screen-reader-text',
        'text': t('skipToReviews', 'Skip to reviews')
      });

      skipLink.on('focus', function () {
        $(this).removeClass('screen-reader-text');
      }).on('blur', function () {
        $(this).addClass('screen-reader-text');
      });

      $('body').prepend(skipLink);
    }
  }

  /**
   * Add focus visible styles for keyboard navigation.
   */
  function addFocusStyles() {
    // Add CSS for focus-visible (keyboard navigation)
    var focusStyles = `
      <style id="bgr-a11y-focus">
        .bgr-stars:focus-visible,
        .button:focus,
        .generic-button a:focus,
        input:focus,
        textarea:focus,
        select:focus {
          outline: 2px solid #0073aa;
          outline-offset: 2px;
        }

        .skip-link:focus {
          position: absolute;
          top: 10px;
          left: 10px;
          background: #fff;
          color: #0073aa;
          padding: 10px;
          z-index: 100000;
          text-decoration: underline;
        }

        .screen-reader-text {
          border: 0;
          clip: rect(1px, 1px, 1px, 1px);
          clip-path: inset(50%);
          height: 1px;
          margin: -1px;
          overflow: hidden;
          padding: 0;
          position: absolute;
          width: 1px;
          word-wrap: normal !important;
        }
      </style>
    `;

    if (!$('#bgr-a11y-focus').length) {
      $('head').append(focusStyles);
    }
  }

  /**
   * Improve color contrast for WCAG AA compliance.
   */
  function checkColorContrast() {
    // This is a placeholder for color contrast checking
    // In production, you might want to use a library like axe-core
    // or implement custom contrast ratio calculations
  }

  // Initialize when document is ready
  $(document).ready(function () {
    initAccessibility();
    addFocusStyles();
  });

  // Re-initialize after AJAX calls
  $(document).ajaxComplete(function () {
    addStarRatingAccessibility();
    improveFormAccessibility();
    improveButtonAccessibility();
  });

})(jQuery);
