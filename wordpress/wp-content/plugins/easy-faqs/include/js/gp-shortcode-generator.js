EasyFAQs_ShortcodeGenerator = function ($)
{
	root = this; 

	this.main = function () {
		wrapper = jQuery('#gold_plugins_shortcode_generator');
		if (wrapper.length > 0)
		{
			// wire-up the "Build My Shortcode" button
			var button = wrapper.find('#sc_generate');
			button.on('click', root.build_shortcode);

			// make the output box auto-highlight
			root.enable_shortcode_highlighting();
			
			// make sure all disabled inputs are really disabled
			root.update_disabled_inputs();
		}
	};

	// disables all inputs inside table rows that have the class "disabled"
	this.update_disabled_inputs = function()
	{
		jQuery('#gold_plugins_shortcode_generator tr.disabled input').attr('disabled', 'disabled');
	}

	// highlight the shortcode inside it's textbox
	this.highlight_shortcode = function()
	{
		jQuery('#sc_gen_output').select();
	}
	
	// highlight the shortcode when the textbox gains focus
	this.enable_shortcode_highlighting = function()
	{
		jQuery('#sc_gen_output').bind('click', function ()
		{
			root.highlight_shortcode();
		});
	}

	// retrives the value from the input specified by selector,
	// and optionally runs it through the filter function
	// note: this is a generic, reusable function
	this.get_value_from_input = function(selector, default_value, filter)
	{
		var trg = jQuery(selector);
		var val = '';

		if ( trg.is(':checkbox') ) {
			val = ( jQuery(selector).is(':checked') ? jQuery(selector).val() : '' );
		} else {
			val = jQuery(selector).val();
		}
		
		val = (val ? val : default_value);
		
		if (filter == 'int') {
			var temp_val  = parseInt(val + '' , 10 );
			if (isNaN(temp_val)) {
				return default_value;
			} else {
				return temp_val;
			}
		}
		else if (filter == 'convert_to_milliseconds') {
			var temp_val  = parseInt(val + '' , 10 );
			if (isNaN(temp_val)) {
				return default_value;
			} else {
				return temp_val * 1000;
			}
		}
		else if (filter == 'yes_or_no_to_0_or_1') {
			if (val == 'yes') {
				return 1;
			} else if (val == 'no' || val == '') {
				return 0;			
			} else {
				return default_value;
			}
		}
		else {
			return val;
		}
	}
	
	// converts a $key and its $value into text output, per our business rules
	this.add_attribute = function($key, $val, $orderby, $show_quick_links, $read_more_url)
	{
		if ($key == 'use_excerpt') {
			return ($val == 1) ? " use_excerpt='1'" : '';
		}
		else if ($key == 'show_thumbs') {
			return ($val == 1) ? " show_thumbs='1'" : '';
		}
		else if ($key == 'count') {
			return ($val > 1) ? " count='" + $val + "'" : '';
		}
		else if ($key == 'category') {
			return ($val != 'all') ? " category='" + $val + "'" : '';
		}
		else if ($key == 'orderby') {
			return ($val != '') ? " orderby='" + $val + "'" : '';
		}
		else if ($key == 'order') {
			if ($orderby !=='random' && $orderby !=='rand') {
				return " order='" + $val + "'";
			} else {
				return '';
			}
		}
		else if ($key == 'quick_links_cols') {
			if ($show_quick_links !== 0 ) {
				return " colcount='" + $val + "'";
			} else {
				return '';
			}
		}
		else if ($key == 'quick_links') {
			if ($show_quick_links !== 0 ) {
				return " quicklinks='" + $val + "'";
			} else {
				return '';
			}
		}
		else if ($key == 'read_more_url') {
			if ($read_more_url.length > 0) {
				return " read_more_link='" + $val + "'";
			} else {
				return '';
			}
		}
		else if ($key == 'read_more_text') {
			if ($read_more_url.length > 0) {
				return " read_more_link_text='" + $val + "'";
			} else {
				return '';
			}
		}
		else if ($key == 'theme') {
			if ($val !== 'no_style') {
				return " theme='" + $val + "'";
			} else {
				return '';
			}
		}
		else if ($key == 'accordion_style') {
			if ($val !== 'normal') {
				return " style='" + $val + "'";
			} else {
				return '';
			}
		}
		else {
			return " " + $key + "='" + $val + "'";
		}
	}

	// given our inputs, generate the shortcode
	// note: this function is almost entirely business logic, and thus not reusable
	this.build_shortcode = function()
	{
		// collect variables
		var $atts = [];
		var $str = '';
		$is_pro = root.get_value_from_input('#is_pro', 0, 'int');
		$atts['count'] = root.get_value_from_input('#sc_gen_count', 0, 'int');
		$atts['orderby'] = root.get_value_from_input('#sc_gen_order_by', 'title');
		$atts['order'] = root.get_value_from_input('#sc_gen_order_dir', 'ASC');
		$atts['category'] = root.get_value_from_input('#sc_gen_category', 'all');
		$atts['show_thumbs'] = root.get_value_from_input('#sc_gen_show_thumbs', 0, 'yes_or_no_to_0_or_1');
		$atts['read_more_text'] = root.get_value_from_input('#sc_gen_read_more_text', '');
		$atts['read_more_url'] = root.get_value_from_input('#sc_gen_read_more_url', '');
		$atts['theme'] = root.get_value_from_input('#sc_gen_theme', '');
		
		// pro only features
		if ($is_pro) {
			$atts['quick_links'] = root.get_value_from_input('#sc_gen_quick_links', 0, 'yes_or_no_to_0_or_1');
			$atts['quick_links_cols'] = root.get_value_from_input('#sc_gen_quick_links_cols', 2, 'int');
			$atts['accordion_style'] = root.get_value_from_input("input[name='sc_gen_style']:checked", 'normal');
		} else {
			$atts['quick_links'] = 0;
			$atts['quick_links_cols'] = 2;
			$atts['accordion_style'] = 'normal';
		}
		
		
		
		// begin with "[faqs"
		$str = '[faqs';
		$use_random = false;
		
		// next add each attribute according to the user supplied values
		var $a;
		for ($key in $atts) {
			$str += root.add_attribute($key, $atts[$key], $atts['orderby'], $atts['quick_links'], $atts['read_more_url']);
		}
		
		// finally, close and display the shortcode
		$str += ']';
		jQuery('#sc_gen_output').val($str);
		jQuery('#sc_gen_output_wrapper').show();
		
		root.highlight_shortcode();
		
	}
	
	// kick things off upon construction
	root.main();
	
} // end EasyFAQs_ShortcodeGenerator class

/*
* Initialize the shortcode generator upon Query's document.ready event
*/
jQuery(function () {
	EasyFAQs_ShortcodeGenerator(jQuery);
});