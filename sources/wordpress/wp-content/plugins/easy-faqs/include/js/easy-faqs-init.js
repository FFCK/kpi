var EasyFAQs_scroll_if_anchor = function (href, scroll_offset) {

    // If our Href points to a valid, non-empty anchor, and is on the same page (e.g. #foo)
    // Legacy jQuery and IE7 may have issues: http://stackoverflow.com/q/1593174
    var $target = jQuery(href);

    // Older browsers without pushState might flicker here, as they momentarily
    // jump to the wrong position (IE < 10)
    if($target.length) {
        jQuery('html, body').animate({ scrollTop: $target.offset().top - scroll_offset });
        if(history && "pushState" in history) {
            history.pushState({}, document.title, window.location.pathname + href);
            return false;
        }
    }
};  


var initEasyFAQs = function ()
{
	var options = {
		header: '.easy-faq-title',
		//animate: "bounceslide",
		collapsible: true,
		heightStyle: "content",
	};
	jQuery( ".easy-faqs-accordion" ).accordion(options);
	var options = {
		header: '.easy-faq-title',
		active: false,
		collapsible: true,
		heightStyle: "content",
	};
	jQuery( ".easy-faqs-accordion-collapsed" ).accordion(options);
	
	//quicklinks
	var quicklinks = jQuery(".faq-questions");
	var scroll_offset = quicklinks.data('scroll_offset');
	if (!scroll_offset) {
		scroll_offset = 0;
	}
	
	// wire up quicklinks to jump to their anchors
	quicklinks.on('click', 'a', function() {
		// if an FAQ is already being shown, collapse it first
		jQuery('.easy-faqs-wrapper .ui-accordion-content-active').css('display', 'none').removeClass('ui-accordion-content-active');
		
		// scroll to the anchor, accounting for offests		
		var faq_header = jQuery("#easy-faq-" + jQuery(this).parent("li").attr("id") + " h3");
		var href = jQuery(this).attr('href');
		setTimeout(function () {
			EasyFAQs_scroll_if_anchor(href, scroll_offset);
		}, 1);
		
		// then, only if the FAQ is not already expanded, expend it now
		if (!faq_header.hasClass('ui-state-active')) {
			faq_header.trigger("click");
		}
		
		return false;
	});
	
	// if the URL's hash matches an FAQ, expand it and scroll to it
	var hash = window.location.hash;
	if (typeof(hash) == 'string' && hash.length > 0 && hash != '#' && hash.indexOf('easy-faq-') >= 0) {
		var starting_faq = jQuery(hash);
		if (starting_faq.length > 0 && starting_faq.hasClass('easy-faq')){
			var starting_header = starting_faq.find('h3.easy-faq-title');
			if (!starting_header.hasClass('ui-state-active')) {
				starting_header.trigger("click");
				EasyFAQs_scroll_if_anchor(hash, scroll_offset);
			}
		}
	}
	
}

jQuery(initEasyFAQs);