var easy_faqs_preview_window;

jQuery(function () {	
	easy_faqs_preview_window = jQuery('#easy_faqs_theme_preview');	
	if (easy_faqs_preview_window.length > 0)
	{
		var faqs_preview = easy_faqs_preview_window.find('.easy-faqs-wrapper');
		var preview_window_content = easy_faqs_preview_window.find('#easy_faqs_theme_preview_content');
				
		// initialize the color picker
		easy_faqs_setup_preview_window_color_picker();
		
		// update the initial state of the preview window
		easy_faqs_update_preview_window();
		
		// update the preview window if the theme selection changes
		jQuery('#faqs_style').bind('change', function () {			
			easy_faqs_update_preview_window();
		});
		
	}
});

function easy_faqs_setup_preview_window_color_picker()
{
	var preview_window_content = easy_faqs_preview_window.find('#easy_faqs_theme_preview_content');
	
	// set the preview window's background color when the user clicks on one of the palette options in the color picker
	jQuery('#easy_faqs_theme_preview_color_picker .iris-palette').bind('click', function (event) {
		var that = this;
		setTimeout(function ()
		{
			var palette_color = jQuery(that).css('backgroundColor');
			preview_window_content.css('backgroundColor', palette_color);
		}, 1);
	});
	
	// set the preview window's background color whenever the color picker emits a change event
	jQuery('#easy_faqs_theme_preview_color_picker').bind('wp-color-picker-value-changed', function (event, new_color)
	{	
		preview_window_content.css('backgroundColor', new_color);
	});
	
	
	// set inital bg color
	preview_window_content.css( 'backgroundColor', jQuery('#easy_faqs_preview_window_background').val() );
}

function easy_faqs_update_preview_window()
{
	var faqs_preview = easy_faqs_preview_window.find('.easy-faqs-wrapper');
	var theme_select = jQuery('#faqs_style');
	var selected = jQuery('option:selected', theme_select);
	var optgroup = selected.parent().attr('label');
	var pro_warning = jQuery('#easy_faqs_themes_pro_warning');
	
	// remove old theme classes
	faqs_preview.removeClass (function (index, css) {
		return (css.match (/(^|\s)easy-faqs-theme-\S+/g) || []).join(' ');
	});	
	
	// add new theme class
	var new_theme_val = theme_select.val();
	if (new_theme_val !== 'no_style')
	{
		// add root class
		var theme_root =  new_theme_val.indexOf("-") > 0 ? new_theme_val.slice(0, new_theme_val.indexOf("-")) : '';
		if (theme_root.length > 0) {
			var new_theme_root_class = theme_root.length > 0 ? 'easy-faqs-theme-' + theme_root : '';
			faqs_preview.addClass(new_theme_root_class);
		}

		// add color class
		var new_theme_color_class = 'easy-faqs-theme-' + new_theme_val;
		faqs_preview.addClass(new_theme_color_class);
	}	
	
	// update pro status
	if (optgroup == 'Basic Themes' || optgroup == 'Office Theme' || pro_warning.length == 0) {
		// free theme selected
		pro_warning.css('display', 'none');
		jQuery('#easy_faqs_theme_preview_submit_button input').removeAttr('disabled');
	} else if (pro_warning.length > 0) {
		// pro theme selected
		pro_warning.css('display', 'block');
		jQuery('#easy_faqs_theme_preview_submit_button input').attr('disabled', 'disabled');
	}
	
	
	// show/hide preview window
	if (theme_select.val() !== 'no_style')
	{
		easy_faqs_preview_window.show();
	} else {
		// hide preview if no_style is selected
		easy_faqs_preview_window.hide();
	}
	
}