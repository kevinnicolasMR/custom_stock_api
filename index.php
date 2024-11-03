<?php
/*
Plugin Name: WP Stock Multimedia
Description: Sistema de stock multimedia privado con integración a Google Drive.
Version: 1.0
Author: Kevin Nicolas Medina Robles
*/

if (!defined('ABSPATH')) exit; 

define('WP_STOCK_MULTIMEDIA_PATH', plugin_dir_path(__FILE__));
define('WP_STOCK_MULTIMEDIA_URL', plugin_dir_url(__FILE__));

require_once WP_STOCK_MULTIMEDIA_PATH . 'inc/api-connection.php';  
require_once WP_STOCK_MULTIMEDIA_PATH . 'inc/general-plugin-creation.php';  
require_once WP_STOCK_MULTIMEDIA_PATH . 'inc/admin-page.php';  



register_deactivation_hook(__FILE__, 'wp_stock_multimedia_deactivate');

