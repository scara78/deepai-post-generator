/**
 * DeepAI Post Generator — Admin JavaScript
 *
 * @package DeepAI_Post_Generator
 * @since   1.0.0
 */

(function ($) {
	'use strict';

	var DeepAIPG = {

		/**
		 * Stored generated content.
		 */
		generatedContent: '',

		/**
		 * Initialize all event handlers.
		 */
		init: function () {
			this.bindGenerateButton();
			this.bindRegenerateButton();
			this.bindSaveButtons();
			this.bindApiTestButtons();
			this.bindModelLoader();
			this.bindToggleKeyVisibility();
			this.bindModalControls();
			this.bindFeaturedImageButton();
		},

		// =====================================================================
		// Content Generation
		// =====================================================================

		bindGenerateButton: function () {
			$('#deepai-generate-btn').on('click', function () {
				var topic = $('#deepai-topic').val().trim();
				if (!topic) {
					DeepAIPG.showToast(deepaiPG.strings.error, 'error');
					$('#deepai-topic').focus();
					return;
				}

				var keywordsRaw = $('#deepai-keywords').val().trim();
				var keywords = keywordsRaw
					? keywordsRaw.split(',').map(function (k) { return k.trim(); }).filter(Boolean)
					: [];

				var data = {
					topic:       topic,
					keywords:    keywords,
					tone:        $('#deepai-tone').val(),
					word_count:  parseInt($('#deepai-word-count').val(), 10) || 1500,
					language:    $('#deepai-language').val(),
					prompt_type: $('#deepai-prompt-type').val(),
					model:       $('#deepai-model').val().trim()
				};

				DeepAIPG.setLoading(true, deepaiPG.strings.generating);

				DeepAIPG.apiRequest('generate', data, 'POST')
					.done(function (resp) {
						if (resp.success && resp.data) {
							DeepAIPG.generatedContent = resp.data.content;
							DeepAIPG.displayContent(resp.data.content);
							DeepAIPG.updateTokenCount(resp.data.tokens_used);
							DeepAIPG.showToast(deepaiPG.strings.success, 'success');
						}
					})
					.fail(function (xhr) {
						var msg = DeepAIPG.getErrorMessage(xhr);
						DeepAIPG.showToast(msg, 'error');
					})
					.always(function () {
						DeepAIPG.setLoading(false);
					});
			});
		},

		bindRegenerateButton: function () {
			$('#deepai-regenerate-btn').on('click', function () {
				$('#deepai-regen-modal').show();
			});

			$('#deepai-confirm-regen').on('click', function () {
				var instructions = $('#deepai-regen-instructions').val().trim();
				if (!instructions) {
					$('#deepai-regen-instructions').focus();
					return;
				}

				$('#deepai-regen-modal').hide();
				DeepAIPG.setLoading(true, deepaiPG.strings.regenerating);

				var data = {
					content:      DeepAIPG.generatedContent,
					instructions: instructions,
					model:        $('#deepai-model').val().trim()
				};

				DeepAIPG.apiRequest('regenerate', data, 'POST')
					.done(function (resp) {
						if (resp.success && resp.data) {
							DeepAIPG.generatedContent = resp.data.content;
							DeepAIPG.displayContent(resp.data.content);
							DeepAIPG.updateTokenCount(resp.data.tokens_used);
							DeepAIPG.showToast(deepaiPG.strings.success, 'success');
						}
					})
					.fail(function (xhr) {
						DeepAIPG.showToast(DeepAIPG.getErrorMessage(xhr), 'error');
					})
					.always(function () {
						DeepAIPG.setLoading(false);
						$('#deepai-regen-instructions').val('');
					});
			});
		},

		// =====================================================================
		// Save / Publish
		// =====================================================================

		bindSaveButtons: function () {
			$('#deepai-save-draft').on('click', function () {
				DeepAIPG.savePost('draft');
			});

			$('#deepai-publish').on('click', function () {
				DeepAIPG.savePost('publish');
			});
		},

		savePost: function (status) {
			if (!DeepAIPG.generatedContent) {
				DeepAIPG.showToast(deepaiPG.strings.noContent, 'error');
				return;
			}

			var $btn = status === 'draft' ? $('#deepai-save-draft') : $('#deepai-publish');
			$btn.prop('disabled', true).text(deepaiPG.strings.saving);

			var tagsRaw = $('#deepai-tags').val().trim();
			var tags = tagsRaw
				? tagsRaw.split(',').map(function (t) { return t.trim(); }).filter(Boolean)
				: [];

			var categories = [];
			$('#deepai-category option:selected').each(function () {
				categories.push(parseInt($(this).val(), 10));
			});

			var data = {
				title:      $('#deepai-topic').val().trim(),
				content:    DeepAIPG.generatedContent,
				status:     status,
				categories: categories,
				tags:       tags
			};

			DeepAIPG.apiRequest('save-post', data, 'POST')
				.done(function (resp) {
					if (resp.success && resp.data) {
						DeepAIPG.showToast(deepaiPG.strings.saved, 'success');
						if (resp.data.edit_url) {
							window.open(resp.data.edit_url, '_blank');
						}
					}
				})
				.fail(function (xhr) {
					DeepAIPG.showToast(DeepAIPG.getErrorMessage(xhr), 'error');
				})
				.always(function () {
					$btn.prop('disabled', false);
					$btn.html(
						status === 'draft'
							? '<span class="dashicons dashicons-media-document"></span> ' + 'Save as Draft'
							: '<span class="dashicons dashicons-yes-alt"></span> ' + 'Publish'
					);
				});
		},

		// =====================================================================
		// Featured Image
		// =====================================================================

		bindFeaturedImageButton: function () {
			$('#deepai-generate-featured-btn').on('click', function () {
				var topic = $('#deepai-topic').val().trim();
				if (!topic || !DeepAIPG.generatedContent) {
					DeepAIPG.showToast(deepaiPG.strings.noContent, 'error');
					return;
				}

				var $btn = $(this);
				$btn.prop('disabled', true);
				DeepAIPG.showToast(deepaiPG.strings.imageGen, 'info');

				var data = {
					prompt: 'Create a professional, high-quality blog featured image for: ' + topic
				};

				DeepAIPG.apiRequest('generate-image', data, 'POST')
					.done(function (resp) {
						if (resp.success && resp.data) {
							DeepAIPG.showToast(deepaiPG.strings.imageDone, 'success');
						}
					})
					.fail(function (xhr) {
						DeepAIPG.showToast(DeepAIPG.getErrorMessage(xhr), 'error');
					})
					.always(function () {
						$btn.prop('disabled', false);
					});
			});
		},

		// =====================================================================
		// API Test
		// =====================================================================

		bindApiTestButtons: function () {
			$('.deepai-test-api').on('click', function () {
				var $btn     = $(this);
				var provider = $btn.data('provider');
				var $field   = $btn.closest('.deepai-api-key-field');
				var apiKey   = $field.find('input').val().trim();

				if (!apiKey) {
					DeepAIPG.showToast('Please enter an API key first.', 'error');
					return;
				}

				$btn.prop('disabled', true).text('Testing...');

				DeepAIPG.apiRequest('test-api', { provider: provider, api_key: apiKey }, 'POST')
					.done(function () {
						DeepAIPG.showToast(deepaiPG.strings.testSuccess, 'success');
					})
					.fail(function (xhr) {
						DeepAIPG.showToast(deepaiPG.strings.testFail + ' ' + DeepAIPG.getErrorMessage(xhr), 'error');
					})
					.always(function () {
						$btn.prop('disabled', false).text('Test Connection');
					});
			});
		},

		// =====================================================================
		// Model Loader
		// =====================================================================

		bindModelLoader: function () {
			$('#deepai-load-models').on('click', function () {
				var $btn  = $(this);
				var $list = $('#deepai-models-list');

				$btn.prop('disabled', true).text('Loading...');

				DeepAIPG.apiRequest('models', {}, 'GET')
					.done(function (resp) {
						if (resp.success && resp.data) {
							var html = '';
							resp.data.forEach(function (model) {
								html += '<div class="deepai-model-option" data-model-id="' +
									DeepAIPG.escapeHtml(model.id) + '">' +
									DeepAIPG.escapeHtml(model.name || model.id) + '</div>';
							});
							$list.html(html).show();

							$list.find('.deepai-model-option').on('click', function () {
								$('#deepai-default-model, #deepai-model').val($(this).data('model-id'));
								$list.hide();
							});
						}
					})
					.fail(function (xhr) {
						DeepAIPG.showToast(DeepAIPG.getErrorMessage(xhr), 'error');
					})
					.always(function () {
						$btn.prop('disabled', false).text('Load Available Models');
					});
			});
		},

		// =====================================================================
		// Toggle Key Visibility
		// =====================================================================

		bindToggleKeyVisibility: function () {
			$('.deepai-toggle-key').on('click', function () {
				var $target = $('#' + $(this).data('target'));
				var type = $target.attr('type') === 'password' ? 'text' : 'password';
				$target.attr('type', type);
				$(this).find('.dashicons')
					.toggleClass('dashicons-visibility dashicons-hidden');
			});
		},

		// =====================================================================
		// Modal Controls
		// =====================================================================

		bindModalControls: function () {
			$('.deepai-modal-close, .deepai-modal-cancel, .deepai-modal-overlay').on('click', function () {
				$(this).closest('.deepai-modal').hide();
			});
		},

		// =====================================================================
		// Helpers
		// =====================================================================

		/**
		 * Make a REST API request.
		 */
		apiRequest: function (endpoint, data, method) {
			return $.ajax({
				url: deepaiPG.restUrl + endpoint,
				method: method,
				data: method === 'GET' ? data : JSON.stringify(data),
				contentType: 'application/json',
				dataType: 'json',
				beforeSend: function (xhr) {
					xhr.setRequestHeader('X-WP-Nonce', deepaiPG.nonce);
				}
			});
		},

		/**
		 * Display generated content.
		 */
		displayContent: function (content) {
			$('#deepai-empty-state').hide();
			$('#deepai-content-preview').show();
			$('#deepai-content-actions').show();

			// Safely render HTML content.
			$('#deepai-generated-content').html(content);
		},

		/**
		 * Set loading state.
		 */
		setLoading: function (loading, text) {
			if (loading) {
				$('#deepai-empty-state').hide();
				$('#deepai-content-preview').hide();
				$('#deepai-content-actions').hide();
				$('#deepai-loading-text').text(text || deepaiPG.strings.generating);
				$('#deepai-loading').show();
				$('#deepai-generate-btn').prop('disabled', true);
			} else {
				$('#deepai-loading').hide();
				$('#deepai-generate-btn').prop('disabled', false);
			}
		},

		/**
		 * Update token count badge.
		 */
		updateTokenCount: function (tokens) {
			if (tokens > 0) {
				$('#deepai-token-count').text(tokens.toLocaleString() + ' tokens').show();
			}
		},

		/**
		 * Show a toast notification.
		 */
		showToast: function (message, type) {
			type = type || 'info';
			var $toast = $('<div class="deepai-toast deepai-toast-' + type + '">' +
				'<span>' + DeepAIPG.escapeHtml(message) + '</span></div>');
			$('#deepai-toast-container').append($toast);

			$toast.on('click', function () { $(this).remove(); });

			setTimeout(function () {
				$toast.fadeOut(300, function () { $(this).remove(); });
			}, 5000);
		},

		/**
		 * Get error message from XHR response.
		 */
		getErrorMessage: function (xhr) {
			if (xhr.responseJSON && xhr.responseJSON.message) {
				return xhr.responseJSON.message;
			}
			return deepaiPG.strings.error;
		},

		/**
		 * Escape HTML for safe insertion.
		 */
		escapeHtml: function (text) {
			var div = document.createElement('div');
			div.appendChild(document.createTextNode(text));
			return div.innerHTML;
		}
	};

	// Initialize on DOM ready.
	$(document).ready(function () {
		DeepAIPG.init();
	});

})(jQuery);
