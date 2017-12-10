<?php
include("efaqkg.php");

function isValidFAQKey(){
	$email = get_option('easy_faqs_registered_name');
	$webaddress = get_option('easy_faqs_registered_url');
	$key = get_option('easy_faqs_registered_key');
	
	$keygen = new EFAQKG();
	$computedKey = $keygen->computeKey($webaddress, $email);
	$computedKeyEJ = $keygen->computeKeyEJ($email);

	if ($key == $computedKey || $key == $computedKeyEJ) {
		return true;
	} else {
		$plugin = "easy-faqs-pro/easy-faqs-pro.php";
		
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		
		if(is_plugin_active($plugin)){
			return true;
		}
		else {
			return false;
		}
	}
}

function isValidMSFAQKey(){
	$plugin = "easy-faqs-pro/easy-faqs-pro.php";
	
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	
	if(is_plugin_active($plugin)){
		return true;
	}
	else {
		return false;
	}
}
?>