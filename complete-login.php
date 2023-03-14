<?php

/**
 * @wordpress-plugin
 * Plugin Name:         Complete Login
 * Description:         Provides login gateway to third party apps like Facebook, Google and Linkedin
 * Version:             1.0
 * Author:              Vileroze
 * Author URI:          http://codepixelzmedia.com/
 * License:             GPL-2.0+
 * License URI:         http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:         complete-login
 */



// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'CL_VERSION', '1.0.0' );

if ( ! defined('CL_PATH_FILE') ) {
    define( 'CL_PATH_FILE', __FILE__ );
}

if ( ! defined('CL_PATH') ) {
    define( 'CL_PATH', dirname(CL_PATH_FILE) );
}

if ( ! defined('CL_PLUGIN_BASENAME') ) {
    define( 'CL_PLUGIN_BASENAME', plugin_basename(CL_PATH_FILE) );
}


/**
 * Activation hook
 */
function complete_login_activate() { 
	// Clear the permalinks after the post type has been registered.
	flush_rewrite_rules(); 
}
register_activation_hook( __FILE__, 'complete_login_activate' );


/**
 * Deactivation hook.
 */
function complete_login_deactivate() {
	// Clear the permalinks to remove our post type's rules from the database.
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'complete_login_deactivate' );

/**
 * Check if version matches requirements
 * Tested on:
 * PHP - 8.1.9
 * WP Engine - 6.2
 */

if (!version_compare(PHP_VERSION, '7.0', '>=')) {
    add_action('admin_notices', 'cl_fail_php_version');
} elseif (!version_compare(get_bloginfo('version'), '4.6', '>=')) {
    add_action('admin_notices', 'cl_fail_wp_version');
} else {
    // require_once(CL_PATH . '/complete-login-init.php');

    require_once __DIR__ . '/public/cl-public-init.php';
    require_once __DIR__ . '/admin/cl-settings-page.php';
}


function cl_fail_php_version() {
    /* translators: %2$s: PHP version */
    $message      = sprintf(esc_html__('%1$s requires PHP version %2$s+, plugin is currently NOT ACTIVE.', 'complete-login'), 'Complete Login', '7.0');
    $html_message = sprintf('<div class="error">%s</div>', wpautop($message));
    echo wp_kses_post($html_message);
}

function cl_fail_wp_version() {
    /* translators: %2$s: WordPress version */
    $message      = sprintf(esc_html__('%1$s requires WordPress version %2$s+. Because you are using an earlier version, the plugin is currently NOT ACTIVE.', 'complete-login'), 'Nextend Social Login', '4.6');
    $html_message = sprintf('<div class="error">%s</div>', wpautop($message));
    echo wp_kses_post($html_message);
}


