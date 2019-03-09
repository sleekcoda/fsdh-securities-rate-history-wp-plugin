<?php
/**
*   Plugin Name:    RC Rate History
*   Plugin URI:     http://rightclickng.com
*   Author:         Toheeb Ogunleye
*   Author URI:     http://rightclickng.com
*   Description:    This plugin will help manage dialy <strong>rate history</strong> from <i>wordpress</i> with ease
*   Version:        0.01
*   License:        GPLv2 or later
*   Text Domain:    rightclickng
*/

if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}
//define('RCRHDIR')

require('class-rc-rate-history.php');

new RCRateHistory();