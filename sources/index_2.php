<?php
session_start();
include_once('commun/MyTools.php');

if (utyGetGet('mirror', false))
{
	$mirror = utyGetGet('mirror', 0);
	if ($mirror == '1')
		$_SESSION['mirror'] = '1';
	else
		$_SESSION['mirror'] = '0';
}
/**
 * Front to the WordPress application. This file doesn't do anything, but loads
 * wp-blog-header.php which does and tells WordPress to load the theme.
 *
 * @package WordPress
 */

/**
 * Tells WordPress to load the WordPress theme and output it.
 *
 * @var bool
 */
define('WP_USE_THEMES', true);

/** Loads the WordPress Environment and Template */
require('./wordpress/wp-blog-header.php');
