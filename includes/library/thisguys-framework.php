<?php
/*
Plugin Name: ThisGuys Framework
Plugin URI: http://wp.thisguys.com/plugins/framework
Description: This plugin is actually a framework, offering many classes and methodologies to speed up development, including an autoloader and data-access classes.
Version: 1.0
Author: ThisGuys Development
Author URI: http://thisguys.com
License: All Rights Reserved
*/

// Make sure we are called by Wordpress, not a user agent
if( !defined('ABSPATH') || !function_exists('wp_get_theme')  )
{
	echo "ERROR - Direct Access";
	exit();
}

$GLOBALS['type'] = 'WP';

require_once('base.php');