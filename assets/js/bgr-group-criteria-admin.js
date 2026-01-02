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

			self.setStatus('Saving...', '');

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
						self.setStatus(response.data.message || 'Error saving settings.', 'error');
					}
				},
				error: function() {
					self.setStatus('Network error. Please try again.', 'error');
				}
			});
		},

		addCustomCriteria: function() {
			var self = this;
			var name = $('#bgr-new-criteria-name').val().trim();

			if (!name) {
				alert('Please enter a criteria name.');
				return;
			}

			if (name.length > 50) {
				alert('Criteria name must be 50 characters or less.');
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
							'<button type="button" class="bgr-archive-custom-criteria button-link" data-name="' + self.escapeHtml(name) + '">Archive</button>' +
							'</div>';
						$('#bgr-custom-criteria-list').append(html);
						$('#bgr-new-criteria-name').val('');
						self.updatePreviewFromResponse(response.data.criteria);
						self.setStatus(response.data.message, 'success');
					} else {
						alert(response.data.message || 'Error adding criteria.');
					}
				},
				error: function() {
					alert('Network error. Please try again.');
				}
			});
		},

		archiveCustomCriteria: function(name) {
			var self = this;

			if (!confirm('Are you sure you want to archive "' + name + '"? Historical reviews will be preserved.')) {
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
						alert(response.data.message || 'Error archiving criteria.');
					}
				},
				error: function() {
					alert('Network error. Please try again.');
				}
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
				$list.append('<li><em>No criteria selected</em></li>');
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
