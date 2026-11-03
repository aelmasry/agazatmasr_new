<?php
/*
Plugin Name: Agazat Code
Plugin URI: https://mohannad.online
Description: Add custom codes to head, after body, or before closing body tag.
Version: 1.0
Author: Mohannad
Author URI: https://mohannad.online
License: GPL2
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('AGAZAT_CODE_VERSION', '1.0');
define('AGAZAT_CODE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AGAZAT_CODE_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include admin and frontend files
require_once AGAZAT_CODE_PLUGIN_DIR . 'includes/admin.php';
require_once AGAZAT_CODE_PLUGIN_DIR . 'includes/frontend.php';

// Activation and deactivation hooks
register_activation_hook(__FILE__, 'agazat_code_activate');
register_deactivation_hook(__FILE__, 'agazat_code_deactivate');

function agazat_code_activate() {
    // Add default options if they don't exist
    if (!get_option('agazat_code_head')) {
        update_option('agazat_code_head', '');
    }
    if (!get_option('agazat_code_after_body')) {
        update_option('agazat_code_after_body', '');
    }
    if (!get_option('agazat_code_before_body_close')) {
        update_option('agazat_code_before_body_close', '');
    }
}

function agazat_code_deactivate() {
    // Clean up options if needed
    // delete_option('agazat_code_head');
    // delete_option('agazat_code_after_body');
    // delete_option('agazat_code_before_body_close');
}