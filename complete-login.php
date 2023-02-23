<?php

/* 
    Plugin Name: Complete Login
    Description: Makes API calls to help users login to 
    Version: 1.0
    Author: Vileroze
    Author URI: https://youtube.com
*/


/**
 * Activation hook
 */
function article_protector_activate() { 
	// Clear the permalinks after the post type has been registered.
	flush_rewrite_rules(); 
}
register_activation_hook( __FILE__, 'complete_login_activate' );


/**
 * Deactivation hook.
 */
function article_protector_deactivate() {
	// Clear the permalinks to remove our post type's rules from the database.
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'complete_login_deactivate' );


require_once __DIR__ . '/public/cl-initialization.php';

