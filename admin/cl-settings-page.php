<?php

/**
 * Admin settings page
 */

add_action( 'admin_menu', 'complete_login_settings_link' );
function complete_login_settings_link(){
    add_options_page( 'Complete Login Settings', 'Complete Login', 'manage_options', 'complete-login-settings-page', 'complete_login_HTML' );
}

add_action( 'admin_init', 'cl_settings' );
function cl_settings(){
    //getting the first menu
    $temp_nav_arr = get_registered_nav_menus();
    $firstKey = array_key_first($temp_nav_arr);
    $default_menu = "default-menu";
    if ( !is_null($firstKey) ) {
        $default_menu = $temp_nav_arr[$firstKey];
    }

    //add options of all the required api and client keys (if not already present) for the login buttons to work
    if ( ! get_option( 'cl_google_client_id' ) ) { add_option( 'cl_google_client_id', '---' ); }
    if ( ! get_option( 'cl_facebook_app_id' ) ) { add_option( 'cl_facebook_app_id', '---' ); }
    if ( ! get_option( 'cl_linkedin_client_id' ) ) { add_option( 'cl_linkedin_client_id', '---' ); }
    if ( ! get_option( 'cl_linkedin_client_secret' ) ) { add_option( 'cl_linkedin_client_secret', '---' ); }
    if ( ! get_option( 'cl_choose_nav' ) ) { add_option( 'cl_choose_nav', $default_menu ); }

    //display location section
    add_settings_section( 'cl_user_auth_display', 'Display settings', null, 'complete-login-settings-page' );
    add_settings_field( 'cl_choose_nav', 'Menu to display auth options', 'menuHTML', 'complete-login-settings-page', 'cl_user_auth_display' );

    //google settings section
    add_settings_section( 'cl_google_section', 'Google Settings', null, 'complete-login-settings-page' );
    add_settings_field( 'cl_google_client_id', 'OAuth 2.0 Client ID', 'googleHTML', 'complete-login-settings-page', 'cl_google_section' );

    //facebook settings section
    add_settings_section( 'cl_facebook_section', 'Facebook Settings', null, 'complete-login-settings-page' );
    add_settings_field( 'cl_facebook_app_id', 'App ID', 'facebookHTML', 'complete-login-settings-page', 'cl_facebook_section' );

    //linkedin settings section
    add_settings_section( 'cl_linkedin_section', 'Linkedin Settings', null, 'complete-login-settings-page' );
    add_settings_field( 'cl_linkedin_client_id', 'Client ID', 'linkedinClientID_HTML', 'complete-login-settings-page', 'cl_linkedin_section' );
    add_settings_field( 'cl_linkedin_client_secret', 'Client Secret', 'linkedinClientSecret_HTML', 'complete-login-settings-page', 'cl_linkedin_section' );

    //register the sections
    register_setting( 'complete_login_plugin', 'cl_choose_nav' );
    register_setting( 'complete_login_plugin', 'cl_google_client_id', ['sanitize_callback' => 'sanitize_text_field', 'default' => '---'] );
    register_setting( 'complete_login_plugin', 'cl_facebook_app_id', ['sanitize_callback' => 'sanitize_text_field', 'default' => '---'] );
    register_setting( 'complete_login_plugin', 'cl_linkedin_client_id', ['sanitize_callback' => 'sanitize_text_field', 'default' => '---'] );
    register_setting( 'complete_login_plugin', 'cl_linkedin_client_secret', ['sanitize_callback' => 'sanitize_text_field', 'default' => '---'] );
    
}

function menuHTML(){
    $current_selected_menu = get_option( 'cl_choose_nav' );
    $menus = get_registered_nav_menus();
    echo '<select name="cl_choose_nav">';
        foreach($menus as $menu => $value){
            if($current_selected_menu == $menu){ 
                echo '<option value="'.$menu.'" selected>' . $value .'</option>';
            }else{
                echo '<option value="'.$menu.'">' . $value .'</option>';
            }
        }
    echo '</select>';
}

function googleHTML(){ 
    $google_client_id = get_option( 'cl_google_client_id', '---' );
    echo '<input type="text" placeholder="Your app\'s OAuth 2.0 Client ID" name="cl_google_client_id" value="'.$google_client_id.'">';
}

function facebookHTML(){
    $facebook_app_id = get_option( 'cl_facebook_app_id', '---' );
    echo '<input type="text" placeholder="Your wep app ID" name="cl_facebook_app_id" value="'.$facebook_app_id.'">';
}

function linkedinClientID_HTML(){
    $linkedin_client_id = get_option( 'cl_linkedin_client_id', '---' );
    echo '<input type="text" placeholder="Enter client ID" name="cl_linkedin_client_id" value="'.$linkedin_client_id.'">';
}

function linkedinClientSecret_HTML(){
    $linkedin_client_secret = get_option( 'cl_linkedin_client_secret', '---' );
    echo '<input type="text" placeholder="Enter client ID" name="cl_linkedin_client_secret" value="'.$linkedin_client_secret.'">';
}

function complete_login_HTML() { ?>
    <div>
        <h1>Complete Login Settings</h1>
        <form action="options.php" method="POST">
            <?php 
                settings_fields( 'complete_login_plugin' );
                do_settings_sections( 'complete-login-settings-page' );
                submit_button();
            ?>
        </form>
    </div>
<?php }