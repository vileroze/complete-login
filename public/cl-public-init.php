<?php
/**
 * Runs on initialization
 */
add_action( 'init', 'complete_login_init' );
function complete_login_init(){

    complete_login_add_custom_styles_and_scripts();
    
    //OAuth flow for linkedin's signin button
    if ( ! is_user_logged_in() ) {
        function complete_login_third_party_login(){
            require_once __DIR__ . '/cl-linkedin.php';
        }
        add_action( 'wp_head', 'complete_login_third_party_login' );
    }

    complete_login_add_auth_menu_items();

}


function complete_login_add_custom_styles_and_scripts(){
    //custom style
	wp_register_style( 'complete-login-custom-style', plugins_url( '/public/assets/css/cl-public.css', CL_PLUGIN_BASENAME ), false, '1.0.0', 'all');

    //custom
    wp_register_script( 'complete-login-custom-script', plugins_url( '/public/assets/js/cl-public.js', CL_PLUGIN_BASENAME ), false, '1.0.0', false );

    //modals
    wp_register_script( 'complete-login-modal-script', plugins_url( '/public/assets/js/cl-modal.js', CL_PLUGIN_BASENAME ), false, '1.0.0', true );

    //facebook button
    wp_register_script( 'complete-login-fb-btn', plugins_url( '/public/facebook/js/facebook-user-cred.js', CL_PLUGIN_BASENAME ), false, '1.0.0', false ); 

    //google library
    wp_register_script( 'complete-login-google-lib', 'https://accounts.google.com/gsi/client', null, '1.0.0', false ); 
    
    //enqueue scripts
    add_action('wp_enqueue_scripts', 'complete_login_enqueue_scripts');
    function complete_login_enqueue_scripts(){
        wp_enqueue_style( 'complete-login-custom-style' );
        wp_enqueue_script( 'complete-login-custom-script' );
        wp_enqueue_script( 'complete-login-modal-script' );
        wp_enqueue_script( 'complete-login-fb-btn' );
        wp_enqueue_script( 'complete-login-google-lib' );
    }

}


function complete_login_add_auth_menu_items(){
    add_filter( 'wp_nav_menu_items','complete_login_auth_options', 10, 2 );
    function complete_login_auth_options( $items, $args ) 
    {
        $chosen_menu = get_option( 'cl_choose_nav' );
        
        if( $args->theme_location == $chosen_menu ) //display the buttons on user chosen menu
        {
            $loggedout_nav_item = '<li><button id="myBtn">SIGNIN</button>'.complete_login_third_party_login_providers().'<button id="myBtn1">SIGNUP</button>'.complete_login_user_registration().'</li>';
            $loggedin_nav_item = '<li><a id="logout-btn" class="logout-btn" href='. wp_logout_url( home_url() ).'>LOGOUT</a></li>';

            $items_array = array();
            while ( false !== ( $item_pos = strpos ( $items, '<li', 3 ) ) )
            {
                $items_array[] = substr($items, 0, $item_pos);
                $items = substr($items, $item_pos);
            }
            $items_array[] = $items;
            array_splice($items_array, sizeof($items_array), 0, is_user_logged_in() ? $loggedin_nav_item : $loggedout_nav_item );
            $items = implode('', $items_array);
        }
        return $items;
    }
}


//!===================================================================================================
//!=====================================UNDER CONSTRUCTION============================================
//!===================================================================================================
/**
 * It destroys the current session, logs the user out, and redirects them to the home page.
 * !works ONLY for normal signin
 */
function complete_login_custom_signout1(){
    unset($_COOKIE['tpl_user_data']);
    return wp_logout_url( home_url() );
}


function complete_login_custom_signout(){
  
    if( $_COOKIE['tpl_user_data'] != "" ){

        //gets third party login providers name
        $tpl_user_arr = explode ( ",", $_COOKIE['tpl_user_data'] ); 
        $tpl_provider_name = $tpl_user_arr[sizeof($tpl_user_arr)-1];
        

        if ( $tpl_provider_name == "google" ){
            echo revoke_google_consent();
            add_action( 'wp_footer','revoke_google_consent',9999 );
        }else if( $tpl_provider_name == "facebook" ){

        }

        setcookie( "tpl_user_data", "" );
        setcookie( "g_cookie", "" );
    }
}
add_action('wp_logout', 'complete_login_custom_signout');

//!===================================================================================================
//!=====================================UNDER CONSTRUCTION============================================
//!===================================================================================================



function check_third_party_signin(){
    if( isset($_COOKIE['tpl_user_data']) && $_COOKIE['tpl_user_data'] != "" ) { 
        $tpl_acc_arr = explode ( ",", $_COOKIE['tpl_user_data'] ); 
        $tpl_email = $tpl_acc_arr[2];

        $tpl_full_name = explode (" ", $tpl_acc_arr[1]);
        $tpl_first_name = $tpl_full_name[0];

        if ( email_exists( $tpl_email ) ) { //login the existing user
            
            //login
            $user = get_user_by( 'email', $tpl_email );
            $user_id = $user->ID;
            echo "userID=>".$user_id." userEmail=>".$tpl_email."<br>";

            wp_set_current_user( $user_id, $user->user_login );
            wp_set_auth_cookie( $user_id );
            do_action( 'wp_login', $user->user_login, $user );

            return @header( home_url() );

        } else { 

            //create new user and login the user
            $new_password = wp_generate_password( 8, false, false );
            $user = wp_create_user( $tpl_first_name, $new_password, $tpl_email );

            if ( is_wp_error( $user ) ) {
                $wp_err =  $user->get_error_message();
                echo '<p class="err"> <span>&times;</span>&nbsp;&nbsp;&nbsp;'.$wp_err.'</p>';
            }else{
                wp_set_current_user( $user->ID, $user->user_login );
                wp_set_auth_cookie( $user->ID, true );
                do_action( 'wp_login', $user->user_login, $user );
            }
        }
    }
}


/**
 * Shows modal with different login options
 */

function complete_login_third_party_login_providers(){

    //check if auth id / client id / client secret provided
    $google_auth_id = get_option( 'cl_google_client_id' );
    $google_btn_configured = !empty($google_auth_id) && $google_auth_id != "" && $google_auth_id != "---";
    $google_user_cred_src = plugins_url( '/public/google/js/google-user-cred.js', CL_PLUGIN_BASENAME );

    //check if facebook app id provided
    $fbook_app_id = get_option( 'cl_facebook_app_id' );
    $fbook_btn_configured = !empty($fbook_app_id) && $fbook_app_id != "" && $fbook_app_id != "---";

    //check if client id or client secret provided
    $linkedin_client_id = get_option( 'cl_linkedin_client_id' );
    $linkedin_client_secret = get_option( 'cl_linkedin_client_secret' );
    $linkedin_btn_configured = !empty($linkedin_client_id) && $linkedin_client_id != "" && $linkedin_client_id != "---" && !empty($linkedin_client_secret) && $linkedin_client_secret != "" && $linkedin_client_secret != "---" ;
    
    check_third_party_signin();
    ?>

    <!-- The Modal -->
    <div id="myModal" class="modal">
        <!-- Modal content -->
        <div class="modal-content">
            <span class="close">&times;</span>

            <!-- user signup form -->   
            <?php echo complete_login_user_signin(); ?>

            <h5>OR SIGNIN WITH:</h5>

            <div class="third-party-login">

                <?php if ( $google_btn_configured ){ ?>

                    <div class="user-login">
                        <!-- Google signin button -->
                        <div id="g_id_onload" data-client_id="975954367849-kpnpua9cia8pk9n882o9jgnm8cctpehd.apps.googleusercontent.com" data-context="signin" data-ux_mode="popup" data-callback="handleCredentialResponse" data-auto_prompt="false"></div>
                        <div  id="google_login" class="g_id_signin" data-type="standard" data-shape="rectangular" data-theme="filled_blue" data-text="signin_with" data-size="large" data-locale="en-US" data-logo_alignment="left" data-width="225"></div>

                        <!-- Google logout button -->
                        <div id="google-logout" class="hide">
                            <a href="">Logout from Google?</a> 
                        </div>
                    </div>

                <?php }else{ ?>
                    <div class="user-login">
                        <p class="err">⚠️ Google button didn't load properly, make sure that you add your auth ID from the admin settings !!!</p>
                    </div>
                <?php } ?>


                <?php if ( $fbook_btn_configured ){ ?>

                    <div class="user-login">
                        <!-- Facebook signin button -->
                        <div id="fb-root"></div>
                        <script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_GB/sdk.js#xfbml=1&version=v16.0&appId=1336979370426534&autoLogAppEvents=1" nonce="OJDXbaIR"> </script>
                        <div id="fb-login" class="fb-login-button" onlogin="checkLoginState();" data-size="large" data-button-type="" data-layout="" data-auto-logout-link="false" data-use-continue-as="false"></div>

                        <!-- Facebook logout button -->
                        <div id="fb-logout" class="hide">
                            <a href="" onclick="fbSignOut();">Logout from Facebook?</a>
                        </div>
                    </div>

                <?php }else{ ?>
                    <div class="user-login">
                        <p class="err">⚠️ Facebook button didn't load properly, make sure that you add your app ID from the admin settings !!!</p>
                    </div>
                <?php } ?>


                <?php if ( $linkedin_btn_configured ){ ?>

                    <div class="user-login">
                        <!-- Linkedin signin button -->
                        <a href="https://www.linkedin.com/oauth/v2/authorization?response_type=code&client_id=86q3eul364itg6&redirect_uri=https://scary-novel.localsite.io/&scope=r_liteprofile%20r_emailaddress" class="linkedin-signin">
                            <div>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" data-supported-dps="24x24" fill="currentColor" class="mercado-match" width="30" height="30" focusable="false">
                                    <path d="M20.5 2h-17A1.5 1.5 0 002 3.5v17A1.5 1.5 0 003.5 22h17a1.5 1.5 0 001.5-1.5v-17A1.5 1.5 0 0020.5 2zM8 19H5v-9h3zM6.5 8.25A1.75 1.75 0 118.3 6.5a1.78 1.78 0 01-1.8 1.75zM19 19h-3v-4.74c0-1.42-.6-1.93-1.38-1.93A1.74 1.74 0 0013 14.19a.66.66 0 000 .14V19h-3v-9h2.9v1.3a3.11 3.11 0 012.7-1.4c1.55 0 3.36.86 3.36 3.66z"></path>
                                </svg>
                                <p>Log in With Linkedin</p>
                            </div>
                        </a>
                    </div>

                <?php }else{ ?>
                    <div class="user-login">
                        <p class="err">⚠️ Linkedin button didn't load properly, make sure that you add your client ID and client secret from the admin settings !!!</p>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
    
    <!-- gets google user credentials -->
    <script type="text/javascript" src="<?php echo $google_user_cred_src; ?>"></script>

<?php }


function revoke_google_consent() {
    return "<script>
            //set cookie to empty after signout
            google.accounts.id.revoke('neplese931@gmail.com', done => {
                console.log('consent revoked');
            });
        </script>";
}


/**
 * Contains functions that handles normal sigin and signup of users 
 * signin requires username and password
 * signup requires name, email and password
 */
require_once __DIR__ . '/cl-default-entry.php';