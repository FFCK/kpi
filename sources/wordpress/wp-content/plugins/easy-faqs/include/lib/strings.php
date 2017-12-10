<?php
	//load plugin text domain
	$plugin_dir = basename( dirname(__FILE__), 2 );
	$easy_faqs_textdomain = 'easy-faqs';
	load_plugin_textdomain( $easy_faqs_textdomain, false, $plugin_dir );
	
	$easy_faqs_strings = array();
	
	//notification email strings
	$easy_faqs_strings['NEW_FAQ_SUBMISSION_SUBJECT'] = __( "New Easy FAQ Submission on ", $easy_faqs_textdomain );
	$easy_faqs_strings['NEW_FAQ_SUBMISSION_BODY'] = __( "You have received a new submission with Easy FAQs on your site, ", $easy_faqs_textdomain) . get_bloginfo('name') . ".  " . __("Login and see what they had to say!", $easy_faqs_textdomain );
	
	//faq submission form
	$easy_faqs_strings['FAQ_FORM_ERROR_NAME'] = __( get_option('easy_faqs_name_error_message', "Please enter your name."), $easy_faqs_textdomain );
	$easy_faqs_strings['FAQ_FORM_ERROR_QUESTION'] = __( get_option('easy_faqs_question_error_message', "Please enter your question."), $easy_faqs_textdomain );
	$easy_faqs_strings['FAQ_FORM_ERROR_CAPTCHA'] = __( get_option('easy_faqs_captcha_error_message', "Captcha did not match."), $easy_faqs_textdomain );
	$easy_faqs_strings['FAQ_FORM_ERROR_SUBMISSION'] = __( get_option('easy_faqs_general_error_message', "There was an error with your submission.  Please check the fields and try again."), $easy_faqs_textdomain );
	$easy_faqs_strings['FAQ_FORM_NAME'] = __( get_option('easy_faqs_name_label', 'Your Name'), $easy_faqs_textdomain );
	$easy_faqs_strings['FAQ_FORM_NAME_DESCRIPTION'] = __( get_option('easy_faqs_name_description', 'Please enter your name.'), $easy_faqs_textdomain );
	$easy_faqs_strings['FAQ_FORM_QUESTION'] = __( get_option('easy_faqs_question_label', 'Your Question'), $easy_faqs_textdomain );
	$easy_faqs_strings['FAQ_FORM_QUESTION_DESCRIPTION'] = __( get_option('easy_faqs_question_description', 'Please enter your Question.'), $easy_faqs_textdomain );
	$easy_faqs_strings['FAQ_SUBMIT_QUESTION_BUTTON'] = __( get_option('easy_faqs_submit_button_label', 'Ask Question'), $easy_faqs_textdomain );
	
	//plugin list links
	$easy_faqs_strings['FAQ_SUPPORT_TEXT'] = __( "Get Support", $easy_faqs_textdomain );
	$easy_faqs_strings['FAQ_UPGRADE_TEXT'] = __( "Upgrade to Pro", $easy_faqs_textdomain );
	$easy_faqs_strings['FAQ_SETTINGS_TEXT'] = __( "Settings", $easy_faqs_textdomain );
	
	//quick links
	$easy_faqs_strings['FAQ_QUICK_LINKS_LABEL'] = __( "Quick Links", $easy_faqs_textdomain );
	
	$easy_faqs_strings['MUST_HAVE_VALID_KEY'] = __( "You must have a valid key to perform this action.", $easy_faqs_textdomain );
	$easy_faqs_strings['QUESTION_FROM'] = __( "Question from: ", $easy_faqs_textdomain );	
	
	return apply_filters('easy_faqs_strings', $easy_faqs_strings);