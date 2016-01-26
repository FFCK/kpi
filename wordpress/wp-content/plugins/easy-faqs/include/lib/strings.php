<?php
	//notification email strings
	define("NEW_FAQ_SUBMISSION_SUBJECT", __("New Easy FAQ Submission on ", $this->textdomain));
	define("NEW_FAQ_SUBMISSION_BODY", __("You have received a new submission with Easy FAQs on your site, ", $this->textdomain) . get_bloginfo('name') . ".  " . __("Login and see what they had to say!", $this->textdomain));
	
	//faq submission form
	define("FAQ_FORM_ERROR_NAME", __(get_option('easy_faqs_name_error_message', "Please enter your name."), $this->textdomain));
	define("FAQ_FORM_ERROR_QUESTION", __(get_option('easy_faqs_question_error_message', "Please enter your question."), $this->textdomain));
	define("FAQ_FORM_ERROR_CAPTCHA", __(get_option('easy_faqs_captcha_error_message', "Captcha did not match."), $this->textdomain));
	define("FAQ_FORM_ERROR_SUBMISSION", __(get_option('easy_faqs_general_error_message', "There was an error with your submission.  Please check the fields and try again."), $this->textdomain));
	define("FAQ_FORM_NAME", __(get_option('easy_faqs_name_label', 'Your Name'), $this->textdomain));
	define("FAQ_FORM_NAME_DESCRIPTION", __(get_option('easy_faqs_name_description', 'Please enter your name.'), $this->textdomain));
	define("FAQ_FORM_QUESTION", __(get_option('easy_faqs_question_label', 'Your Question'), $this->textdomain));
	define("FAQ_FORM_QUESTION_DESCRIPTION", __(get_option('easy_faqs_question_description', 'Please enter your Question.'), $this->textdomain));
	define("FAQ_SUBMIT_QUESTION_BUTTON", __(get_option('easy_faqs_submit_button_label', 'Ask Question'), $this->textdomain));
	
	//plugin list links
	define("FAQ_SUPPORT_TEXT", __("Get Support", $this->textdomain));
	define("FAQ_UPGRADE_TEXT", __("Upgrade to Pro", $this->textdomain));
	define("FAQ_SETTINGS_TEXT", __("Settings", $this->textdomain));
	
	//quick links
	define("FAQ_QUICK_LINKS_LABEL", __("Quick Links", $this->textdomain));