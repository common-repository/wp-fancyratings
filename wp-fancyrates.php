<?php
/*
Plugin Name: WP Fancyratings
Plugin URI: http://fatesinger.com/868
Description: Adds an AJAX rating system for your WordPress blog's post/page.
Version: 1.0.0
Author: Bigfa
Author URI: http://fatesinger.com
*/

define('FR_VERSION', '1.0.0');
define('FR_URL', plugins_url('', __FILE__));
define('FR_PATH', dirname( __FILE__ ));
define('FR_ADMIN_URL', admin_url());

/**
 * 加载函数
 */
require FR_PATH . '/functions.php';


