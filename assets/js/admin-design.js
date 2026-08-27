(function ($) {
	'use strict';

	$(document).ready(function () {
		const previewRoot = document.getElementById('vplens-preview-root');
		if (!previewRoot) {
			return; // Not on the design page
		}


		// Dynamic input change mapping to CSS Custom Properties
		const varMap = {
			'input[bg_color]': '--vplens-input-bg',
			'input[text_color]': '--vplens-input-text',
			'input[placeholder_color]': '--vplens-input-placeholder',
			'input[border_color]': '--vplens-input-border-color',
			'input[border_width]': '--vplens-input-border-width',
			'input[border_radius]': '--vplens-input-border-radius',
			'input[height]': '--vplens-input-height',
			'input[font_size]': '--vplens-input-font-size',
			'input[padding]': '--vplens-input-padding',
			'button[bg_color]': '--vplens-button-bg',
			'button[hover_bg_color]': '--vplens-button-hover-bg',
			'button[text_color]': '--vplens-button-text',
			'button[width]': '--vplens-button-width',
			'button[height]': '--vplens-button-height',
			'button[border_radius]': '--vplens-button-border-radius',
			'button[font_size]': '--vplens-button-font-size',
			'icon[color]': '--vplens-icon-color',
			'icon[size]': '--vplens-icon-size',
			'layout[width]': '--vplens-layout-width',
			'layout[gap]': '--vplens-layout-gap',
			'dropdown[bg_color]': '--vplens-dropdown-bg',
			'dropdown[border_color]': '--vplens-dropdown-border-color',
			'dropdown[border_radius]': '--vplens-dropdown-border-radius',
			'dropdown[max_height]': '--vplens-dropdown-max-height',
			'dropdown[result_spacing]': '--vplens-dropdown-result-spacing',
			'dropdown[result_padding]': '--vplens-dropdown-result-padding',
			'product[image_size]': '--vplens-product-image-size',
			'product[title_color]': '--vplens-product-title-color',
			'product[title_font_size]': '--vplens-product-title-font-size',
			'product[price_color]': '--vplens-product-price-color',
			'product[sku_color]': '--vplens-product-sku-color',
			'product[category_color]': '--vplens-product-category-color',
			'product[stock_color]': '--vplens-product-stock-color',
			'hover[bg_color]': '--vplens-hover-bg',
			'hover[text_color]': '--vplens-hover-text',
			'badges[sale_bg]': '--vplens-badge-sale-bg',
			'badges[sale_text]': '--vplens-badge-sale-text',
			'badges[featured_bg]': '--vplens-badge-featured-bg',
			'badges[featured_text]': '--vplens-badge-featured-text',
			'no_results[text_color]': '--vplens-no-results-color',
			'no_results[font_size]': '--vplens-no-results-font-size',
			'view_all[text_color]': '--vplens-view-all-text',
			'view_all[bg_color]': '--vplens-view-all-bg',
			'view_all[hover_color]': '--vplens-view-all-hover'
		};

		const defaultSettings = {
			'input[bg_color]': '#ffffff',
			'input[text_color]': '#102033',
			'input[placeholder_color]': '#5f6b7a',
			'input[border_color]': '#c8d3df',
			'input[border_width]': 1,
			'input[border_radius]': 20,
			'input[height]': 52,
			'input[font_size]': 16,
			'input[padding]': 16,
			'button[bg_color]': '#0b7285',
			'button[hover_bg_color]': '#0f9bb0',
			'button[text_color]': '#ffffff',
			'button[text]': 'Search',
			'button[width]': 'auto',
			'button[height]': 52,
			'button[border_radius]': 12,
			'button[font_size]': 16,
			'button[show_icon]': true,
			'icon[show]': true,
			'icon[color]': '#5f6b7a',
			'icon[size]': 18,
			'icon[position]': 'right',
			'layout[type]': 'inline',
			'layout[width]': 600,
			'layout[gap]': 12,
			'layout[align]': 'center',
			'dropdown[bg_color]': '#ffffff',
			'dropdown[border_color]': '#d9e1ea',
			'dropdown[border_radius]': 16,
			'dropdown[shadow]': 'medium',
			'dropdown[max_height]': 400,
			'dropdown[result_spacing]': 8,
			'dropdown[result_padding]': 12,
			'product[image_size]': 48,
			'product[title_color]': '#102033',
			'product[title_font_size]': 15,
			'product[price_color]': '#102033',
			'product[sku_color]': '#5f6b7a',
			'product[category_color]': '#868e96',
			'product[stock_color]': '#12b886',
			'hover[bg_color]': '#f8fafc',
			'hover[text_color]': '#0b7285',
			'badges[sale_bg]': '#e64980',
			'badges[sale_text]': '#ffffff',
			'badges[featured_bg]': '#0b7285',
			'badges[featured_text]': '#ffffff',
			'no_results[message]': 'No results found.',
			'no_results[text_color]': '#5f6b7a',
			'no_results[font_size]': 15,
			'view_all[text]': 'See all results',
			'view_all[text_color]': '#0b7285',
			'view_all[bg_color]': '#f8fafc',
			'view_all[hover_color]': '#0f9bb0',
			'view_all[align]': 'center'
		};

		function updatePreviewVars() {
			for (const [key, cssVar] of Object.entries(varMap)) {
				const nameKey = key.replace('[', '][');
				const input = document.querySelector(`[name="vplens_settings[design][${nameKey}"]`);
				if (!input) {
					continue;
				}

				let val = input.value;

				// Append px unit to numeric properties
				const needsUnit = cssVar.endsWith('-width') ||
								  cssVar.endsWith('-radius') ||
								  cssVar.endsWith('-height') ||
								  cssVar.endsWith('-size') ||
								  cssVar.endsWith('-padding') ||
								  cssVar.endsWith('-spacing') ||
								  cssVar.endsWith('-gap');

				if (needsUnit && !isNaN(val) && val.trim() !== '') {
					val = val + 'px';
				}

				if (cssVar === '--vplens-dropdown-shadow') {
					if (val === 'low') {
						val = '0 2px 8px rgba(0, 0, 0, 0.08)';
					} else if (val === 'medium') {
						val = '0 8px 30px rgba(0, 0, 0, 0.12)';
					} else if (val === 'high') {
						val = '0 15px 50px rgba(0, 0, 0, 0.18)';
					} else {
						val = 'none';
					}
				}

				previewRoot.style.setProperty(cssVar, val);
			}

			// Update Text content elements
			const btnTextInput = document.querySelector('[name="vplens_settings[design][button][text]"]');
			if (btnTextInput) {
				$('.vplens-ajax-search-button .vplens-btn-text').text(btnTextInput.value);
			}

			const noResultsInput = document.querySelector('[name="vplens_settings[design][no_results][message]"]');
			if (noResultsInput) {
				$('.vplens-ajax-search-status').text(noResultsInput.value);
			}

			const viewAllInput = document.querySelector('[name="vplens_settings[design][view_all][text]"]');
			if (viewAllInput) {
				$('.vplens-ajax-search-all-link').text(viewAllInput.value);
			}

			// Alignment of view all button
			const viewAllAlign = document.querySelector('[name="vplens_settings[design][view_all][align]"]');
			if (viewAllAlign) {
				$('.vplens-ajax-search-all-results').css('text-align', viewAllAlign.value);
			}

			// Toggle input icon visibility
			const showIconCheckbox = document.querySelector('[name="vplens_settings[design][icon][show]"]');
			const iconPosition = document.querySelector('[name="vplens_settings[design][icon][position]"]');
			const searchIcon = $('#vplens-preview-root .vplens-preview-search-icon');
			const inputField = $('#vplens-preview-root .vplens-ajax-search-input');

			if (showIconCheckbox && showIconCheckbox.checked) {
				searchIcon.show();
				inputField.parent().addClass('vplens-ajax-search-fields--has-icon');
				if (iconPosition && iconPosition.value === 'left') {
					searchIcon.css({ left: '16px', right: 'auto' });
					if (inputField[0]) {
						inputField[0].style.setProperty('padding-left', '44px', 'important');
						inputField[0].style.setProperty('padding-right', 'var(--vplens-input-padding)', 'important');
					}
				} else {
					searchIcon.css({ right: '16px', left: 'auto' });
					if (inputField[0]) {
						inputField[0].style.setProperty('padding-right', '44px', 'important');
						inputField[0].style.setProperty('padding-left', 'var(--vplens-input-padding)', 'important');
					}
				}
			} else {
				searchIcon.hide();
				inputField.parent().removeClass('vplens-ajax-search-fields--has-icon');
				if (inputField[0]) {
					inputField[0].style.setProperty('padding-left', 'var(--vplens-input-padding)', 'important');
					inputField[0].style.setProperty('padding-right', 'var(--vplens-input-padding)', 'important');
				}
			}

			// Toggle button icon and text visibility
			const showBtnIcon = document.querySelector('[name="vplens_settings[design][button][show_icon]"]');
			const searchBtn = $('#vplens-preview-root .vplens-ajax-search-button');
			
			if (showBtnIcon && showBtnIcon.checked) {
				searchBtn.addClass('vplens-btn-has-icon');
			} else {
				searchBtn.removeClass('vplens-btn-has-icon');
			}

			// Layout type toggle
			const layoutType = document.querySelector('[name="vplens_settings[design][layout][type]"]');
			const layoutWrap = $('#vplens-preview-root .vplens-layout-preview');
			if (layoutType) {
				layoutWrap.removeClass('vplens-layout-inline vplens-layout-full_width vplens-layout-compact');
				layoutWrap.addClass('vplens-layout-' + layoutType.value);

				if (layoutType.value === 'compact') {
					searchBtn.addClass('vplens-btn-icon-only');
				} else {
					searchBtn.removeClass('vplens-btn-icon-only');
				}
			}

			// Alignment of form
			const formAlign = document.querySelector('[name="vplens_settings[design][layout][align]"]');
			if (formAlign) {
				const alignment = formAlign.value;
				let marginRule = '0 auto';
				if (alignment === 'left') {
					marginRule = '0 auto 0 0';
				} else if (alignment === 'right') {
					marginRule = '0 0 0 auto';
				}
				$('.vplens-ajax-search').css('margin', marginRule);
			}
		}

		// Initialize WP Color Picker on design section inputs
		$('.vplens-color-picker').wpColorPicker({
			change: function (event, ui) {
				const input = event.target;
				setTimeout(function() {
					$(input).val(ui.color.toString()).trigger('change');
				}, 10);
			},
			clear: function (event) {
				const container = $(event.target).closest('.wp-picker-container');
				const input = container.find('.vplens-color-picker');
				setTimeout(function() {
					input.val('').trigger('change');
				}, 10);
			}
		});

		// Monitor other input changes
		$('.vplens-customizer-sidebar').on('input change', 'input, select', function () {
			updatePreviewVars();
		});

		// Preview state toggles
		$('.vplens-state-tab').on('click', function () {
			$('.vplens-state-tab').removeClass('active');
			$(this).addClass('active');

			const state = $(this).data('state');
			const resultsWrap = $('.vplens-ajax-search-results-wrap');
			const resultsList = $('.vplens-ajax-search-results');
			const statusMsg = $('.vplens-ajax-search-status');
			const footerAll = $('.vplens-ajax-search-all-results');

			resultsWrap.show();

			if (state === 'form') {
				resultsWrap.hide();
			} else if (state === 'results') {
				resultsList.show();
				footerAll.show();
				statusMsg.hide().removeClass('is-loading');
			} else if (state === 'no_results') {
				resultsList.hide();
				footerAll.hide();
				const noResultsInput = document.querySelector('[name="vplens_settings[design][no_results][message]"]');
				const fallbackMsg = (typeof vplensDesignI18n !== 'undefined' && vplensDesignI18n.no_results) ? vplensDesignI18n.no_results : 'No results found.';
				statusMsg.text(noResultsInput ? noResultsInput.value : fallbackMsg);
				statusMsg.show().removeClass('is-loading');
			} else if (state === 'loading') {
				resultsList.hide();
				footerAll.hide();
				statusMsg.show().addClass('is-loading');
				const searchingMsg = (typeof vplensDesignI18n !== 'undefined' && vplensDesignI18n.searching) ? vplensDesignI18n.searching : 'Searching...';
				statusMsg.text(searchingMsg);
			}
		});

		// Desktop/Mobile device preview toggles
		$('.vplens-device-btn').on('click', function () {
			$('.vplens-device-btn').removeClass('active');
			$(this).addClass('active');

			const device = $(this).data('device');
			const sandbox = $('.vplens-preview-sandbox-wrap');

			sandbox.removeClass('device-desktop device-mobile');
			sandbox.addClass('device-' + device);
		});

		// Reset Design Button Handler
		$('#vplens-reset-design-btn').on('click', function (e) {
			e.preventDefault();
			const confirmMsg = (typeof vplensDesignI18n !== 'undefined' && vplensDesignI18n.reset_confirm) ? vplensDesignI18n.reset_confirm : 'Are you sure you want to reset all design settings back to defaults?';
			if (!confirm(confirmMsg)) {
				return;
			}

			// Restore input values
			for (const [key, value] of Object.entries(defaultSettings)) {
				const nameKey = key.replace('[', '][');
				const input = document.querySelector(`[name="vplens_settings[design][${nameKey}"]`);
				if (!input) {
					continue;
				}

				if (input.type === 'checkbox') {
					input.checked = Boolean(value);
				} else {
					input.value = value;
					// Trigger Color Picker if enqueued
					if ($(input).hasClass('vplens-color-picker')) {
						$(input).wpColorPicker('color', value);
					}
				}
			}

			updatePreviewVars();
		});

		// Initialize values on first load
		updatePreviewVars();
	});

})(jQuery);
