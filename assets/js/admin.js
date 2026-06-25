(function () {
	'use strict';

	window.addEventListener(
		'DOMContentLoaded',
		function () {
			const wrapper = document.querySelector( '.search-analytics-insights-wrap' );

			if ( ! wrapper) {
				return;
			}

			const buttons                   = wrapper.querySelectorAll( '.search-analytics-insights-copy-shortcode' );
			const copiedLabel               = wrapper.getAttribute( 'data-copied-label' ) || 'Copied';
			const loadAllPublicPostTypes    = wrapper.querySelector( '#search-analytics-insights-search_sources-load_all_public_post_types' );
			const searchablePostTypeOptions = wrapper.querySelectorAll( '.search-analytics-insights-post-type-option input[type="checkbox"]' );

			if (loadAllPublicPostTypes && searchablePostTypeOptions.length) {
				const syncSearchablePostTypes = function () {
					const isDisabled = loadAllPublicPostTypes.checked;

					searchablePostTypeOptions.forEach(
						function (checkbox) {
							checkbox.disabled = isDisabled;
							const option      = checkbox.closest( '.search-analytics-insights-post-type-option' );

							if (option) {
								option.classList.toggle( 'is-disabled', isDisabled );
							}
						}
					);
				};

				loadAllPublicPostTypes.addEventListener( 'change', syncSearchablePostTypes );
				syncSearchablePostTypes();
			}

			// Bind details row click toggler logic using event delegation.
			wrapper.addEventListener(
				'click',
				function (e) {
					const toggleBtn = e.target.closest( '.sai-toggle-details' );
					if ( ! toggleBtn) {
						return;
					}

					e.preventDefault();
					const targetId = toggleBtn.getAttribute( 'data-target' );
					const targetRow = document.getElementById( targetId );

					if (targetRow) {
						if (targetRow.style.display === 'none') {
							targetRow.style.display = '';
							toggleBtn.textContent = toggleBtn.getAttribute( 'data-hide-label' ) || 'Hide';
						} else {
							targetRow.style.display = 'none';
							toggleBtn.textContent = toggleBtn.getAttribute( 'data-show-label' ) || 'Details';
						}
					}
				}
			);

			if ( ! buttons.length) {
				return;
			}

			buttons.forEach(
				function (button) {
					button.addEventListener(
						'click',
						function () {
							const shortcode = button.getAttribute( 'data-copy-shortcode' );

							if ( ! shortcode) {
								return;
							}

							const originalLabel  = button.textContent;
							const setCopiedLabel = function () {
								button.textContent = copiedLabel;
								window.setTimeout(
									function () {
										button.textContent = originalLabel;
									},
									1500
								);
							};

							if (navigator.clipboard && navigator.clipboard.writeText) {
								navigator.clipboard.writeText( shortcode ).then( setCopiedLabel );
								return;
							}

							const textarea = document.createElement( 'textarea' );
							textarea.value = shortcode;
							textarea.setAttribute( 'readonly', 'readonly' );
							textarea.style.position = 'absolute';
							textarea.style.left     = '-9999px';
							document.body.appendChild( textarea );
							textarea.select();

							try {
								document.execCommand( 'copy' );
								setCopiedLabel();
							} finally {
								document.body.removeChild( textarea );
							}
						}
					);
				}
			);
		}
	);

})();
