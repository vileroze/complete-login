<?php


/**
 * Runs on initialization
 */

// register style on initialization
add_action( 'init', 'complete_login_init' );
function complete_login_init(){

    //register styles
	wp_register_style( 'complete-login-style', plugins_url( '/assets/css/cl-frontend.css', __FILE__ ), false, '1.0.0', 'all');

    //register scripts
    wp_register_script( 'complete-login-script', plugins_url( '/assets/js/cl-frontend.js', __FILE__ ), false, '1.0.0', true );

    // use the style above
    add_action('wp_enqueue_scripts', 'article_protector_enqueue_scripts');
    function article_protector_enqueue_scripts(){
        wp_enqueue_style( 'complete-login-style' );
        wp_enqueue_script( 'complete-login-script' );
    }

}