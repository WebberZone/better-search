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
