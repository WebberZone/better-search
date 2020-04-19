// Function to clear the cache.
function clearCache() {
	/**** since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php ****/
	jQuery.post(ajaxurl, {
		action: 'bsearch_clear_cache'
	}, function (response, textStatus, jqXHR) {
		alert(response.message);
	}, 'json');
}

jQuery(document).ready(function($) {
	// Prompt the user when they leave the page without saving the form.
	formmodified=0;

	function confirmFormChange() {
		formmodified=1;
	}

	function confirmExit() {
		if ( formmodified == 1 ) {
			return true;
		}
	}

	function formNotModified() {
		formmodified = 0;
	}

	$('form *').change( confirmFormChange );

	window.onbeforeunload = confirmExit;

	$( "input[name='submit']" ).click(formNotModified);
	$( "input[id='search-submit']" ).click(formNotModified);
	$( "input[id='doaction']" ).click(formNotModified);
	$( "input[id='doaction2']" ).click(formNotModified);
	$( "input[name='filter_action']" ).click(formNotModified);

	$( function() {
		$( "#post-body-content" ).tabs({
			create: function( event, ui ) {
				$( ui.tab.find("a") ).addClass( "nav-tab-active" );
			},
			activate: function( event, ui ) {
				$( ui.oldTab.find("a") ).removeClass( "nav-tab-active" );
				$( ui.newTab.find("a") ).addClass( "nav-tab-active" );
			}
		});
	});

	// Datepicker.
	$( function() {
		var dateFormat = 'dd M yy',
		from = $( "#datepicker-from" )
			.datepicker({
				changeMonth: true,
				maxDate: 0,
				dateFormat: dateFormat
			})
			.on( "change", function() {
				to.datepicker( "option", "minDate", getDate( this ) );
			}),
		to = $( "#datepicker-to" )
			.datepicker({
				changeMonth: true,
				maxDate: 0,
				dateFormat: dateFormat
			})
			.on( "change", function() {
				from.datepicker( "option", "maxDate", getDate( this ) );
			});

		function getDate( element ) {
			var date;
			try {
				date = $.datepicker.parseDate( dateFormat, element.value );
			} catch( error ) {
				date = null;
			}

			return date;
		}
	} );

	// Initialise CodeMirror.
	$( ".codemirror_html" ).each( function( index, element ) {
		if( $( element ).length && typeof wp.codeEditor === 'object' ) {
			var editorSettings = wp.codeEditor.defaultSettings ? _.clone( wp.codeEditor.defaultSettings ) : {};
			editorSettings.codemirror = _.extend(
				{},
				editorSettings.codemirror,
				{
				}
			);
			var editor = wp.codeEditor.initialize( $( element ), editorSettings );
			editor.codemirror.on( 'change', confirmFormChange );
		}
	});

	$( ".codemirror_js" ).each( function( index, element ) {
		if( $( element ).length && typeof wp.codeEditor === 'object' ) {
			var editorSettings = wp.codeEditor.defaultSettings ? _.clone( wp.codeEditor.defaultSettings ) : {};
			editorSettings.codemirror = _.extend(
				{},
				editorSettings.codemirror,
				{
					mode: 'javascript',
				}
			);
			var editor = wp.codeEditor.initialize( $( element ), editorSettings );
			editor.codemirror.on( 'change', confirmFormChange );
		}
	});

	$( ".codemirror_css" ).each( function( index, element ) {
		if( $( element ).length && typeof wp.codeEditor === 'object' ) {
			var editorSettings = wp.codeEditor.defaultSettings ? _.clone( wp.codeEditor.defaultSettings ) : {};
			editorSettings.codemirror = _.extend(
				{},
				editorSettings.codemirror,
				{
					mode: 'css',
				}
			);
			var editor = wp.codeEditor.initialize( $( element ), editorSettings );
			editor.codemirror.on( 'change', confirmFormChange );
		}
	});

	// Initialise ColorPicker
	$( '.color-field' ).each( function ( i, element ) {
		$( element ).wpColorPicker();
	});

});
