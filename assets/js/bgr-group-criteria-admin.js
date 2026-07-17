/**
 * Group Criteria Admin JavaScript
 *
 * Handles the group-level criteria settings in the group admin panel.
 *
 * @since 3.7.0
 * @package BuddyPress_Group_Review
 */

(function($) {
	'use strict';

	// wp.i18n helpers (handle depends on wp-i18n; guarded for safety).
	var hasI18n = window.wp && window.wp.i18n;
	var __ = hasI18n ? window.wp.i18n.__ : function (text) { return text; };
	var sprintf = hasI18n ? window.wp.i18n.sprintf : function (format, value) { return format.replace('%s', value); };

	var BGR_Group_Criteria = {
		groupId: 0,
		nonce: '',

		init: function() {
			this.groupId = $('#bgr-group-id').val();
			this.nonce = $('#bgr-criteria-nonce').val();

			if (!this.groupId) {
				return;
			}

			this.bindEvents();
		},

		bindEvents: function() {
			var self = this;

			// Mode change
			$('input[name="bgr_criteria_mode"]').on('change', function() {
				self.handleModeChange($(this).val());
			});

			// Save settings
			$('#bgr-save-criteria-settings').on('click', function(e) {
				e.preventDefault();
				self.saveSettings();
			});

			// Add custom criteria
			$('#bgr-add-custom-criteria').on('click', function(e) {
				e.preventDefault();
				self.addCustomCriteria();
			});

			// Enter key on custom criteria input
			$('#bgr-new-criteria-name').on('keypress', function(e) {
				if (e.which === 13) {
					e.preventDefault();
					self.addCustomCriteria();
				}
			});

			// Archive custom criteria
			$(document).on('click', '.bgr-archive-custom-criteria', function(e) {
				e.preventDefault();
				var name = $(this).data('name');
				self.archiveCustomCriteria(name);
			});

			// Toggle global criteria checkbox
			$('input[name="bgr_enabled_global[]"]').on('change', function() {
				self.updatePreview();
			});
		},

		handleModeChange: function(mode) {
			if (mode === 'override') {
				$('#bgr-override-settings').slideDown();
			} else {
				$('#bgr-override-settings').slideUp();
			}
			this.updatePreview();
		},

		saveSettings: function() {
			var self = this;
			var mode = $('input[name="bgr_criteria_mode"]:checked').val();
			var enabledGlobal = [];

			$('input[name="bgr_enabled_global[]"]:checked').each(function() {
				enabledGlobal.push($(this).val());
			});

			self.setStatus(__('Saving...', 'bp-group-reviews'), '');

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'bgr_save_group_criteria_settings',
					nonce: self.nonce,
					group_id: self.groupId,
					mode: mode,
					enabled_global_criteria: enabledGlobal
				},
				success: function(response) {
					if (response.success) {
						self.setStatus(response.data.message, 'success');
						self.updatePreviewFromResponse(response.data.criteria);
					} else {
						self.setStatus(response.data.message || __('Error saving settings.', 'bp-group-reviews'), 'error');
					}
				},
				error: function() {
					self.setStatus(__('Network error. Please try again.', 'bp-group-reviews'), 'error');
				}
			});
		},

		// Toast helper — uses bgrToast() when available, else falls back
		// to the inline status line. No native alert() (dev-rule 10/12).
		notify: function(message, type) {
			if (typeof window.bgrToast === 'function') {
				window.bgrToast(message, type || 'info');
			} else {
				this.setStatus(message, 'error' === type ? 'error' : '');
			}
		},

		addCustomCriteria: function() {
			var self = this;
			var name = $('#bgr-new-criteria-name').val().trim();

			if (!name) {
				self.notify(__('Please enter a criteria name.', 'bp-group-reviews'), 'warning');
				return;
			}

			if (name.length > 50) {
				self.notify(__('Criteria name must be 50 characters or less.', 'bp-group-reviews'), 'warning');
				return;
			}

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'bgr_add_custom_criteria',
					nonce: self.nonce,
					group_id: self.groupId,
					criteria_name: name
				},
				success: function(response) {
					if (response.success) {
						// Add to the list
						var html = '<div class="bgr-custom-criteria-item" data-name="' + self.escapeHtml(name) + '">' +
							'<span class="bgr-criteria-name">' + self.escapeHtml(name) + '</span>' +
							'<button type="button" class="bgr-archive-custom-criteria button-link" data-name="' + self.escapeHtml(name) + '">' + __('Archive', 'bp-group-reviews') + '</button>' +
							'</div>';
						$('#bgr-custom-criteria-list').append(html);
						$('#bgr-new-criteria-name').val('');
						self.updatePreviewFromResponse(response.data.criteria);
						self.setStatus(response.data.message, 'success');
					} else {
						self.notify((response.data && response.data.message) || __('Error adding criteria.', 'bp-group-reviews'), 'error');
					}
				},
				error: function() {
					self.notify(__('Network error. Please try again.', 'bp-group-reviews'), 'error');
				}
			});
		},

		archiveCustomCriteria: function(name) {
			var self = this;

			var ask = typeof window.bgrConfirm === 'function'
				? window.bgrConfirm({
					title: __('Archive criteria?', 'bp-group-reviews'),
					/* translators: %s: criteria name. */
					message: sprintf(__('Are you sure you want to archive "%s"? Historical reviews will be preserved.', 'bp-group-reviews'), name),
					confirm: __('Archive', 'bp-group-reviews'),
					dangerous: true
				})
				: Promise.resolve(true);

			ask.then(function(ok) {
				if (!ok) {
					return;
				}
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'bgr_archive_custom_criteria',
						nonce: self.nonce,
						group_id: self.groupId,
						criteria_name: name
					},
					success: function(response) {
						if (response.success) {
							// Remove from the list
							$('.bgr-custom-criteria-item[data-name="' + name + '"]').fadeOut(function() {
								$(this).remove();
							});
							self.updatePreviewFromResponse(response.data.criteria);
							self.setStatus(response.data.message, 'success');
						} else {
							self.notify((response.data && response.data.message) || __('Error archiving criteria.', 'bp-group-reviews'), 'error');
						}
					},
					error: function() {
						self.notify(__('Network error. Please try again.', 'bp-group-reviews'), 'error');
					}
				});
			});
		},

		updatePreview: function() {
			var mode = $('input[name="bgr_criteria_mode"]:checked').val();
			var criteria = [];

			if (mode === 'inherit') {
				// Show global active criteria (we'll need to refresh from server)
				this.refreshPreview();
			} else {
				// Collect enabled global criteria
				$('input[name="bgr_enabled_global[]"]:checked').each(function() {
					criteria.push($(this).val());
				});

				// Collect active custom criteria
				$('.bgr-custom-criteria-item').each(function() {
					criteria.push($(this).data('name'));
				});

				this.updatePreviewList(criteria);
			}
		},

		refreshPreview: function() {
			var self = this;

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'bgr_get_group_criteria',
					nonce: self.nonce,
					group_id: self.groupId
				},
				success: function(response) {
					if (response.success) {
						self.updatePreviewFromResponse(response.data.effective);
					}
				}
			});
		},

		updatePreviewFromResponse: function(criteria) {
			this.updatePreviewList(criteria);
		},

		updatePreviewList: function(criteria) {
			var $list = $('#bgr-active-criteria-preview');
			$list.empty();

			if (criteria && criteria.length > 0) {
				for (var i = 0; i < criteria.length; i++) {
					$list.append('<li>' + this.escapeHtml(criteria[i]) + '</li>');
				}
			} else {
				$list.append('<li><em>' + __('No criteria selected', 'bp-group-reviews') + '</em></li>');
			}
		},

		setStatus: function(message, type) {
			var $status = $('#bgr-save-status');
			$status.removeClass('success error').addClass(type).text(message);

			if (type === 'success') {
				setTimeout(function() {
					$status.fadeOut(function() {
						$(this).text('').show();
					});
				}, 3000);
			}
		},

		escapeHtml: function(text) {
			var div = document.createElement('div');
			div.appendChild(document.createTextNode(text));
			return div.innerHTML;
		}
	};

	$(document).ready(function() {
		BGR_Group_Criteria.init();
	});

})(jQuery);
