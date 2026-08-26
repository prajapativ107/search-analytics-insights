(function () {
	'use strict';

	const states = new WeakMap();

	function getState(wrapper) {
		if (!states.has(wrapper)) {
			states.set(
				wrapper,
				{
					timer: null,
					controller: null,
					activeIndex: -1,
				}
			);
		}

		return states.get(wrapper);
	}

	function getStrings(wrapper) {
		const i18n = window.vplensAjaxSearchI18n || {};

		return {
			loading: wrapper.getAttribute('data-loading-text') || i18n.loading || 'Searching...',
			empty: wrapper.getAttribute('data-empty-text') || i18n.empty || 'No results found.',
			minimum: wrapper.getAttribute('data-min-chars-text') || i18n.minimum || 'Type at least 2 characters to search.',
			error: wrapper.getAttribute('data-error-text') || i18n.error || 'Unable to search right now.',
		};
	}

	function setStatus(wrapper, message, isLoading) {
		const status = wrapper.querySelector('.vplens-ajax-search-status');

		if (!status) {
			return;
		}

		const showLoader = '0' !== wrapper.getAttribute('data-wc-show-loader');
		status.textContent = message;
		status.classList.toggle('is-loading', Boolean(isLoading && showLoader));
		status.style.display = isLoading || message ? 'block' : 'none';
	}

	function setResultsVisibility(wrapper, isVisible) {
		const resultsWrap = wrapper.querySelector('.vplens-ajax-search-results-wrap');

		if (resultsWrap) {
			resultsWrap.style.display = isVisible ? 'block' : 'none';
		}

		const wcFocusOverlay = '1' === wrapper.getAttribute('data-wc-focus-overlay');
		if (wcFocusOverlay) {
			let overlay = document.querySelector('.vplens-search-overlay');
			if (isVisible) {
				if (!overlay) {
					overlay = document.createElement('div');
					overlay.className = 'vplens-search-overlay';
					document.body.appendChild(overlay);
				}
				overlay.classList.add('is-active');
			} else if (overlay) {
				overlay.classList.remove('is-active');
			}
		}
	}

	function hideResultsUI(wrapper) {
		setStatus(wrapper, '', false);
		setResultsVisibility(wrapper, false);
	}

	function clearResults(wrapper) {
		const results = wrapper.querySelector('.vplens-ajax-search-results');
		const input = wrapper.querySelector('.vplens-ajax-search-input');

		if (results) {
			results.innerHTML = '';
		}

		if (input) {
			input.setAttribute('aria-expanded', 'false');
			input.removeAttribute('aria-activedescendant');
		}

		getState(wrapper).activeIndex = -1;
	}

	function renderResults(wrapper, items) {
		const results = wrapper.querySelector('.vplens-ajax-search-results');
		const input = wrapper.querySelector('.vplens-ajax-search-input');
		const showFeaturedImages = '1' === wrapper.getAttribute('data-show-featured-images');
		const showPostTypeLabel = '1' === wrapper.getAttribute('data-show-post-type-label');
		const state = getState(wrapper);

		const wcEnabled = '1' === wrapper.getAttribute('data-wc-enabled');
		const wcShowPrice = '1' === wrapper.getAttribute('data-wc-show-price');
		const wcShowStock = '1' === wrapper.getAttribute('data-wc-show-stock');
		const wcShowSku = '1' === wrapper.getAttribute('data-wc-show-sku');
		const wcShowSale = '1' === wrapper.getAttribute('data-wc-show-sale');
		const wcShowFeatured = '1' === wrapper.getAttribute('data-wc-show-featured');
		const wcShowViewAll = '1' === wrapper.getAttribute('data-wc-show-view-all');
		const wcViewAllLabel = wrapper.getAttribute('data-wc-view-all-label') || 'See all results';
		const searchId = wrapper.getAttribute('data-last-search-id') || '';

		if (!results || !input) {
			return;
		}

		results.innerHTML = '';
		state.activeIndex = -1;

		if (!items.length) {
			input.setAttribute('aria-expanded', 'false');
			clearResults(wrapper);
			setResultsVisibility(wrapper, false);
			return;
		}

		items.forEach(
			function (item, index) {
				if (item.post_type === 'product' && wcEnabled) {
					const li = document.createElement('li');
					const link = document.createElement('a');
					const imageWrap = document.createElement('span');
					const detailsWrap = document.createElement('span');
					const title = document.createElement('span');

					li.className = 'vplens-ajax-search-result vplens-ajax-search-result--product';
					li.setAttribute('role', 'option');
					li.id = 'vplens-ajax-search-option-' + index;

					link.className = 'vplens-ajax-search-link';
					link.href = item.url || '#';
					link.tabIndex = -1;
					link.setAttribute('data-post-id', item.id);
					link.setAttribute('data-search-id', searchId);

					imageWrap.className = 'vplens-ajax-search-image-wrap';
					if (showFeaturedImages && item.featured_image) {
						const image = document.createElement('img');
						image.className = 'vplens-ajax-search-image';
						image.src = item.featured_image;
						image.alt = '';
						image.loading = 'lazy';
						image.decoding = 'async';
						image.setAttribute('aria-hidden', 'true');
						imageWrap.appendChild(image);
						link.classList.add('has-featured-image');
					}

					detailsWrap.className = 'vplens-ajax-search-details';

					title.className = 'vplens-ajax-search-title';
					title.textContent = item.title || '';
					detailsWrap.appendChild(title);

					const badgesWrap = document.createElement('span');
					badgesWrap.className = 'vplens-product-badges';
					if (wcShowSale && item.is_on_sale) {
						const badge = document.createElement('span');
						badge.className = 'vplens-badge vplens-badge--sale';
						badge.textContent = 'Sale';
						badgesWrap.appendChild(badge);
					}
					if (wcShowFeatured && item.is_featured) {
						const badge = document.createElement('span');
						badge.className = 'vplens-badge vplens-badge--featured';
						badge.textContent = 'Featured';
						badgesWrap.appendChild(badge);
					}
					if (badgesWrap.childNodes.length) {
						detailsWrap.appendChild(badgesWrap);
					}

					const metaRow = document.createElement('span');
					metaRow.className = 'vplens-product-meta-row';

					if (wcShowSku && item.sku) {
						const sku = document.createElement('span');
						sku.className = 'vplens-product-sku';
						sku.textContent = 'SKU: ' + item.sku;
						metaRow.appendChild(sku);
					}

					if (item.category) {
						const cat = document.createElement('span');
						cat.className = 'vplens-product-cat';
						cat.textContent = item.category;
						metaRow.appendChild(cat);
					}

					if (metaRow.childNodes.length) {
						detailsWrap.appendChild(metaRow);
					}

					const priceStockRow = document.createElement('span');
					priceStockRow.className = 'vplens-product-price-stock-row';

					if (wcShowPrice && item.price_html) {
						const price = document.createElement('span');
						price.className = 'vplens-product-price';
						price.innerHTML = item.price_html;
						priceStockRow.appendChild(price);
					}

					if (wcShowStock && item.stock_status) {
						const stock = document.createElement('span');
						stock.className = 'vplens-stock vplens-stock--' + item.stock_status;
						stock.textContent = item.stock_status === 'instock' ? 'In stock' : 'Out of stock';
						priceStockRow.appendChild(stock);
					}

					if (priceStockRow.childNodes.length) {
						detailsWrap.appendChild(priceStockRow);
					}

					if (imageWrap.childNodes.length) {
						link.appendChild(imageWrap);
					}
					link.appendChild(detailsWrap);
					li.appendChild(link);
					results.appendChild(li);
				} else {
					const li = document.createElement('li');
					const link = document.createElement('a');
					const imageWrap = document.createElement('span');
					const title = document.createElement('span');
					const meta = document.createElement('span');

					li.className = 'vplens-ajax-search-result';
					li.setAttribute('role', 'option');
					li.id = 'vplens-ajax-search-option-' + index;

					link.className = 'vplens-ajax-search-link';
					link.href = item.url || '#';
					link.tabIndex = -1;
					link.setAttribute('data-post-id', item.id);
					link.setAttribute('data-search-id', searchId);

					imageWrap.className = 'vplens-ajax-search-image-wrap';

					if (showFeaturedImages && item.featured_image) {
						const image = document.createElement('img');
						image.className = 'vplens-ajax-search-image';
						image.src = item.featured_image;
						image.alt = '';
						image.loading = 'lazy';
						image.decoding = 'async';
						image.setAttribute('aria-hidden', 'true');
						imageWrap.appendChild(image);
						link.classList.add('has-featured-image');
					}

					title.className = 'vplens-ajax-search-title';
					title.textContent = item.title || '';

					if (imageWrap.childNodes.length) {
						link.appendChild(imageWrap);
					}
					link.appendChild(title);

					if (showPostTypeLabel && item.post_type_label) {
						meta.className = 'vplens-ajax-search-meta';
						meta.textContent = item.post_type_label;
						link.appendChild(meta);
					}

					li.appendChild(link);
					results.appendChild(li);
				}
			}
		);

		if (wcEnabled && wcShowViewAll && items.length > 0) {
			const viewAllLi = document.createElement('li');
			const viewAllLink = document.createElement('a');

			viewAllLi.className = 'vplens-ajax-search-result vplens-ajax-search-result--view-all';
			viewAllLink.className = 'vplens-ajax-search-link vplens-ajax-search-link--view-all';
			viewAllLink.textContent = wcViewAllLabel;
			viewAllLink.href = wrapper.querySelector('.vplens-ajax-search-form').action + '?s=' + encodeURIComponent(input.value.trim()) + '&post_type=product';
			
			viewAllLi.appendChild(viewAllLink);
			results.appendChild(viewAllLi);
		}

		input.setAttribute('aria-expanded', 'true');
		setResultsVisibility(wrapper, true);
	}

	function updateActiveItem(wrapper, direction) {
		const state = getState(wrapper);
		const items = wrapper.querySelectorAll('.vplens-ajax-search-result');

		if (!items.length) {
			return;
		}

		state.activeIndex += direction;

		if (state.activeIndex < 0) {
			state.activeIndex = items.length - 1;
		}

		if (state.activeIndex >= items.length) {
			state.activeIndex = 0;
		}

		items.forEach(
			function (item, index) {
				const link = item.querySelector('a');
				item.classList.toggle('is-active', index === state.activeIndex);

				if (index === state.activeIndex && link) {
					link.focus({ preventScroll: true });
					const input = wrapper.querySelector('.vplens-ajax-search-input');

					if (input) {
						input.setAttribute('aria-activedescendant', item.id);
					}
				}
			}
		);
	}

	function fetchResults(wrapper, term) {
		const state = getState(wrapper);
		const ajaxUrl = wrapper.getAttribute('data-ajax-url');
		const nonce = wrapper.getAttribute('data-nonce');
		const action = wrapper.getAttribute('data-action');
		const limit = wrapper.getAttribute('data-limit') || '10';
		const strings = getStrings(wrapper);

		if (!ajaxUrl || !nonce || !action) {
			return;
		}

		if (state.controller) {
			state.controller.abort();
		}

		state.controller = new AbortController();
		setStatus(wrapper, strings.loading, true);

		const params = new URLSearchParams();
		params.append('action', action);
		params.append('nonce', nonce);
		params.append('term', term);
		params.append('limit', limit);

		const pageTitle = window.vplens_data && window.vplens_data.page_title ? window.vplens_data.page_title : document.title;
		const pageUrl   = window.vplens_data && window.vplens_data.page_url ? window.vplens_data.page_url : window.location.href;
		const pageType  = window.vplens_data && window.vplens_data.page_type ? window.vplens_data.page_type : (window.VPLens && window.VPLens.getPageType ? window.VPLens.getPageType() : 'Other');

		params.append('page_title', pageTitle);
		params.append('page_url', pageUrl);
		params.append('referrer', document.referrer);
		params.append('page_type', pageType);

		fetch(
			ajaxUrl,
			{
				method: 'POST',
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
				},
				body: params.toString(),
				signal: state.controller.signal,
			}
		)
			.then(
				function (response) {
					return response.json();
				}
			)
			.then(
				function (data) {
					if (data.data && data.data.search_id) {
						wrapper.setAttribute('data-last-search-id', data.data.search_id);
					} else {
						wrapper.removeAttribute('data-last-search-id');
					}

					const items = data.data && Array.isArray(data.data.items) ? data.data.items : [];
					if (!items.length) {
						clearResults(wrapper);
						setResultsVisibility(wrapper, false);
						setStatus(wrapper, strings.empty, false);
						return;
					}

					renderResults(wrapper, items);
					setStatus(wrapper, '', false);
				}
			)
			.catch(
				function () {
					if (state.controller.signal.aborted) {
						return;
					}

					clearResults(wrapper);
					setStatus(wrapper, strings.error, false);
					setResultsVisibility(wrapper, false);
				}
			)
			.finally(
				function () {
					state.controller = null;
					const input = wrapper.querySelector('.vplens-ajax-search-input');

					if (input) {
						input.removeAttribute('aria-busy');
					}
				}
			);
	}

	function handleInput(wrapper, event) {
		const input = event.target;
		const term = input.value.trim();
		const state = getState(wrapper);
		const minChars = parseInt(wrapper.getAttribute('data-min-chars') || '2', 10);
		const debounce = parseInt(wrapper.getAttribute('data-debounce') || '300', 10);
		const strings = getStrings(wrapper);

		if (state.timer) {
			window.clearTimeout(state.timer);
		}

		if (term.length < minChars) {
			clearResults(wrapper);
			hideResultsUI(wrapper);
			return;
		}

		input.setAttribute('aria-busy', 'true');
		state.timer = window.setTimeout(
			function () {
				fetchResults(wrapper, term);
			},
			debounce
		);
	}

	function initWrapper(wrapper) {
		const input = wrapper.querySelector('.vplens-ajax-search-input');
		const form = wrapper.querySelector('.vplens-ajax-search-form');

		if (!input || !form) {
			return;
		}

		hideResultsUI(wrapper);

		input.addEventListener('input', handleInput.bind(null, wrapper));
		input.addEventListener(
			'keydown',
			function (event) {
				if ('ArrowDown' === event.key) {
					event.preventDefault();
					updateActiveItem(wrapper, 1);
					return;
				}

				if ('ArrowUp' === event.key) {
					event.preventDefault();
					updateActiveItem(wrapper, -1);
					return;
				}

				if ('Escape' === event.key) {
					clearResults(wrapper);
					hideResultsUI(wrapper);
				}
			}
		);

		function trackClick(searchId, postId) {
			const ajaxUrl = wrapper.getAttribute('data-ajax-url');
			const nonce = wrapper.getAttribute('data-nonce');
			const clickAction = wrapper.getAttribute('data-click-action') || 'vplens_click';

			if (!ajaxUrl || !nonce || !searchId || !postId) {
				return;
			}

			const params = new URLSearchParams();
			params.append('action', clickAction);
			params.append('nonce', nonce);
			params.append('search_id', searchId);
			params.append('post_id', postId);

			if (navigator.sendBeacon) {
				navigator.sendBeacon(ajaxUrl, params);
			} else {
				fetch(
					ajaxUrl,
					{
						method: 'POST',
						credentials: 'same-origin',
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
						},
						body: params.toString(),
						keepalive: true
					}
				).catch(function () {});
			}
		}

		form.addEventListener(
			'submit',
			function (event) {
				event.preventDefault();

				const state = getState(wrapper);
				const items = wrapper.querySelectorAll('.vplens-ajax-search-result');
				const activeResult = state.activeIndex >= 0 ? items[state.activeIndex] : null;
				const link = activeResult ? activeResult.querySelector('a') : null;

				if (link && link.href) {
					const postId = link.getAttribute('data-post-id');
					const searchId = link.getAttribute('data-search-id');
					if (postId && searchId) {
						trackClick(searchId, postId);
					}
					window.location.href = link.href;
					return;
				}

				input.dispatchEvent(new Event('input', { bubbles: true }));
			}
		);

		wrapper.addEventListener(
			'click',
			function (event) {
				const link = event.target.closest('.vplens-ajax-search-link');

				if (link) {
					const postId = link.getAttribute('data-post-id');
					const searchId = link.getAttribute('data-search-id');
					if (postId && searchId) {
						trackClick(searchId, postId);
					}
					clearResults(wrapper);
					hideResultsUI(wrapper);
				}
			}
		);

		document.addEventListener(
			'click',
			function (event) {
				if (!wrapper.contains(event.target) || event.target.classList.contains('vplens-search-overlay')) {
					clearResults(wrapper);
					hideResultsUI(wrapper);
				}
			}
		);
	}

	function init() {
		document.querySelectorAll('.vplens-ajax-search').forEach(initWrapper);
	}

	if ('loading' === document.readyState) {
		document.addEventListener('DOMContentLoaded', init);
		return;
	}

	init();
})();