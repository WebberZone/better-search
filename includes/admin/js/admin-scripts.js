jQuery(document).ready(
	function ($) {
		$('button[name="bsearch_cache_clear"]').on('click', function () {
			if (confirm(bsearch_admin_data.confirm_message)) {
				var $button = $(this);
				$button.prop('disabled', true).append(' <span class="spinner is-active"></span>');
				clearCache($button);
			}
		});

		// Function to clear the cache.
		function clearCache($button) {
			$.post(bsearch_admin_data.ajax_url, {
				action: 'bsearch_clear_cache',
				security: bsearch_admin_data.security
			}, function (response) {
				if (response.success) {
					alert(response.data.message);
				} else {
					alert(bsearch_admin_data.fail_message);
				}
			}).fail(function (jqXHR, textStatus) {
				alert(bsearch_admin_data.request_fail_message + textStatus);
			}).always(function () {
				$button.prop('disabled', false).find('.spinner').remove();
			});
		}

		// Prompt the user when they leave the page without saving the form.
		var formmodified = 0;

		function confirmFormChange() {
			formmodified = 1;
		}

		function confirmExit() {
			if (formmodified == 1) {
				return true;
			}
		}

		function formNotModified() {
			formmodified = 0;
		}

		$('form *').change(confirmFormChange);

		// Collation fix AJAX handler
		$(document).on('click', '.bsearch-run-collation-fix', function (e) {
			e.preventDefault();
			if (!window.confirm('Are you sure? Please backup your database before proceeding!')) return;

			var $button = $(this);
			var originalText = $button.text();

			$button.prop('disabled', true)
				.text('Running...')
				.append(' <span class="spinner is-active" style="float: none; margin: 0 0 0 5px;"></span>');

			$.post(bsearch_admin_data.ajax_url, {
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

		window.onbeforeunload = confirmExit;

		$("input[name='submit']").click(formNotModified);
		$("input[id='search-submit']").click(formNotModified);
		$("input[id='doaction']").click(formNotModified);
		$("input[id='doaction2']").click(formNotModified);
		$("input[name='filter_action']").click(formNotModified);

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
