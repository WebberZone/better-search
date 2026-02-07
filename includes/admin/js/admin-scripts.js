jQuery(document).ready(
	function ($) {
		$('button[name="bsearch_cache_clear"]').on('click', function () {
			if (confirm(bsearch_admin_data.strings.confirm_message)) {
				var $button = $(this);
				$button.prop('disabled', true).append(' <span class="spinner is-active"></span>');
				clearCache($button);
			}
		});

		// Function to clear the cache.
		function clearCache($button) {
			$.post(ajaxurl, {
				action: 'bsearch_clear_cache',
				security: bsearch_admin_data.security
			}, function (response) {
				if (response.success) {
					alert(response.data.message);
				} else {
					alert(bsearch_admin_data.strings.fail_message);
				}
			}).fail(function (jqXHR, textStatus) {
				alert(bsearch_admin_data.strings.request_fail_message + textStatus);
			}).always(function () {
				$button.prop('disabled', false).find('.spinner').remove();
			});
		}


		// Collation fix AJAX handler
		$(document).on('click', '.bsearch-run-collation-fix', function (e) {
			e.preventDefault();
			if (!window.confirm('Are you sure? Please backup your database before proceeding!')) return;

			var $button = $(this);
			var originalText = $button.text();

			$button.prop('disabled', true)
				.text('Running...')
				.append(' <span class="spinner is-active" style="float: none; margin: 0 0 0 5px;"></span>');

			$.post(ajaxurl, {
				action: 'bsearch_run_collation_fix',
				blog_id: $button.data('blog-id'),
				collation: $button.data('collation'),
				security: bsearch_admin_data.security
			}, function (response) {
				if (response.success) {
					alert(response.data || 'Collation updated successfully.');
					$button.text('Fixed!').removeClass('button-danger').addClass('button-secondary');
				} else {
					alert(response.data || 'Failed to update collation.');
				}
			}).fail(function (jqXHR, textStatus) {
				alert('Request failed: ' + textStatus);
			}).always(function () {
				$button.find('.spinner').remove();
				if ($button.text() !== 'Fixed!') {
					$button.prop('disabled', false).text(originalText);
				}
			});
		});

		$(
			function () {
				$("#post-body-content").tabs(
					{
						create: function (event, ui) {
							$(ui.tab.find("a")).addClass("nav-tab-active");
						},
						activate: function (event, ui) {
							$(ui.oldTab.find("a")).removeClass("nav-tab-active");
							$(ui.newTab.find("a")).addClass("nav-tab-active");
						}
					}
				);
			}
		);

		// Datepicker.
		$(
			function () {
				var dateFormat = 'dd M yy',
					from = $("#datepicker-from")
						.datepicker(
							{
								changeMonth: true,
								changeYear: true,
								maxDate: 0,
								dateFormat: dateFormat
							}
						)
						.on(
							"change",
							function () {
								to.datepicker("option", "minDate", getDate(this));
							}
						),
					to = $("#datepicker-to")
						.datepicker(
							{
								changeMonth: true,
								changeYear: true,
								maxDate: 0,
								dateFormat: dateFormat
							}
						)
						.on(
							"change",
							function () {
								from.datepicker("option", "maxDate", getDate(this));
							}
						);

				function getDate(element) {
					var date;
					try {
						date = $.datepicker.parseDate(dateFormat, element.value);
					} catch (error) {
						date = null;
					}

					return date;
				}
			}
		);
	}
);

/**
 * Copy text to clipboard
 * 
 * @param {string} elementId - ID of the element containing text to copy
 * @returns {void}
 */
function bsearchCopyToClipboard(elementId) {
	const element = document.getElementById(elementId);
	if (!element) return;

	const button = element.parentElement.querySelector('.bsearch-copy-button');
	if (!button) return;

	navigator.clipboard.writeText(element.textContent).then(() => {
		const icon = button.querySelector('.dashicons');
		icon.classList.remove('dashicons-clipboard');
		icon.classList.add('dashicons-yes');
		button.classList.add('copied');
		button.title = better_search_admin.copied;

		setTimeout(() => {
			icon.classList.remove('dashicons-yes');
			icon.classList.add('dashicons-clipboard');
			button.classList.remove('copied');
			button.title = better_search_admin.copyToClipboard;
		}, 2000);
	}).catch(() => {
		const icon = button.querySelector('.dashicons');
		icon.classList.remove('dashicons-clipboard');
		icon.classList.add('dashicons-warning');
		button.classList.add('error');
		button.title = better_search_admin.copyError;

		setTimeout(() => {
			icon.classList.remove('dashicons-warning');
			icon.classList.add('dashicons-clipboard');
			button.classList.remove('error');
			button.title = better_search_admin.copyToClipboard;
		}, 2000);
	});
}

/**
 * Add copy button to code blocks
 * 
 * @param {string} elementId - ID of the element to add copy button to
 * @returns {void}
 */
function bsearchAddCopyButton(elementId) {
	const element = document.getElementById(elementId);
	if (!element) return;

	const button = document.createElement('button');
	button.type = 'button';
	button.className = 'bsearch-copy-button';
	button.setAttribute('aria-label', better_search_admin.copyToClipboard);
	button.title = better_search_admin.copyToClipboard;
	button.onclick = () => bsearchCopyToClipboard(elementId);

	const screenReaderText = document.createElement('span');
	screenReaderText.className = 'screen-reader-text';
	screenReaderText.textContent = better_search_admin.copyToClipboard;

	const icon = document.createElement('span');
	icon.className = 'dashicons dashicons-clipboard';
	icon.setAttribute('aria-hidden', 'true');

	button.appendChild(screenReaderText);
	button.appendChild(icon);

	const wrapper = element.parentElement;
	if (wrapper && wrapper.classList.contains('bsearch-code-wrapper')) {
		wrapper.appendChild(button);
	}
}
