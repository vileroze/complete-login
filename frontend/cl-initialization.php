<?php


/**
 * Runs on initialization
 */

// register style on initialization
add_action( 'init', 'complete_login_init' );
function complete_login_init(){

    //custom style
	wp_register_style( 'complete-login-style', plugins_url( '/assets/css/cl-frontend.css', __FILE__ ), false, '1.0.0', 'all');

    //custom script
    wp_register_script( 'complete-login-script', plugins_url( '/assets/js/cl-frontend.js', __FILE__ ), false, '1.0.0', true );
    //facebook button
    wp_register_script( 'complete-login-fb-btn', plugins_url( '/assets/js/facebook-btn.js', __FILE__ ), false, '1.0.0', false ); 
    //google library
    wp_register_script( 'complete-login-google-lib', 'https://accounts.google.com/gsi/client', null, '1.0.0', false ); 
    
    //emqueue scripts
    add_action('wp_enqueue_scripts', 'complete_login_enqueue_scripts');
    function complete_login_enqueue_scripts(){
        wp_enqueue_style( 'complete-login-style' );
        wp_enqueue_script( 'complete-login-script' );
        wp_enqueue_script( 'complete-login-fb-btn' );
        wp_enqueue_script( 'complete-login-google-lib' );
        
    }

    //display user registration form if user not logged in
    if ( ! is_user_logged_in() ) {
        
        function complete_login_third_party_login(){
            
            //OAuth flow for linkedin's signin button
            require_once __DIR__ . '/cl-linkedin.php';
        }
        add_action( 'wp_head', 'complete_login_third_party_login' );

        
        /**
         * Add signin and signup button to menu.
         */
        add_filter('wp_nav_menu_items','complete_login_auth_options', 10, 2);
        function complete_login_auth_options( $items, $args ) 
        {
            if( $args->theme_location == 'menu-1' ) // only if primary menu
            {
                $items_array = array();
                while ( false !== ( $item_pos = strpos ( $items, '<li', 3 ) ) )
                {
                    $items_array[] = substr($items, 0, $item_pos);
                    $items = substr($items, $item_pos);
                }
                $items_array[] = $items;
                array_splice($items_array, sizeof($items_array), 0, '<li><button id="myBtn">SIGNIN</button>'.complete_login_third_party_login_providers().'<button id="myBtn1">SIGNUP</button>'.complete_login_user_registration().'</li>'); // insert login options

                $items = implode('', $items_array);
            }
            return $items;
        }
    }else{
        /**
         * Add logout button to menu.
         */
        add_filter('wp_nav_menu_items','complete_login_logout_option', 10, 2);
        function complete_login_logout_option( $items, $args ) 
        {
            if( $args->theme_location == 'menu-1' ) // only if primary menu
            {
                $items_array = array();
                while ( false !== ( $item_pos = strpos ( $items, '<li', 3 ) ) )
                {
                    $items_array[] = substr($items, 0, $item_pos);
                    $items = substr($items, $item_pos);
                }
                $items_array[] = $items;
                array_splice($items_array, sizeof($items_array), 0, '<li><a class="logout-btn" href='.wp_logout_url( home_url() ).'>LOGOUT</a></li>'); // insert login options

                $items = implode('', $items_array);
            }
            return $items;
        }
    }
}









/**
 * It creates a new user with the username, password and email address provided by the user.
 */
function complete_login_user_registration(){
    ob_start();
    ?>
    <!-- The Modal -->
    <div id="myModal1" class="modal">
        <!-- Modal content -->
        <div class="modal-content">
            <span class="close1">&times;</span>
            <h5>SIGN UP</h5>
            <form action="" method="POST">
                <div class="user-registration">
                    <div class="inline-input">
                        <label for="username"><b>Name</b></label>
                        <input type="text" placeholder="Enter your name" name="user_name">
                    </div>

                    <div class="inline-input">
                        <label for="useremail"><b>Email</b></label>
                        <input type="email" placeholder="Enter your email" name="user_email">
                    </div>
                </div>

                <div class="user-registration">
                    <div class="inline-input">
                        <label for="first_password"><b>Password</b></label>
                        <input type="password" placeholder="Enter password" name="first_password">    
                    </div>
                    
                    <div class="inline-input">
                        <label for="confirm_password"><b>Confirm Password</b></label>
                        <input type="password" placeholder="Confirm password" name="confirm_password">
                    </div>

                    <input type="submit" name="user_registration" value="SIGNUP">
                </div>
            </form>
        </div>
    </div>
    <?php

    if( isset($_POST['user_registration']) ){

        $signup_user_name = $_POST['user_name'];
        $signup_user_email = $_POST['user_email'];
        $first_password = $_POST['first_password'];
        $confirm_password = $_POST['confirm_password'];

        if( ! empty( $signup_user_name ) && ! empty( $signup_user_email ) ){

            if( ( $first_password == $confirm_password ) == true ){

                //create new user
                $new_user_id = wp_create_user( $signup_user_name, $confirm_password, $signup_user_email );

                if ( is_wp_error( $new_user_id ) ) {
                    $wp_err =  $new_user_id->get_error_message();
                    return '<p class="err"> <span>&times;</span>&nbsp;&nbsp;&nbsp;'.$wp_err.'</p>';
                } else {

                    //login
                    $user = get_user_by( 'ID', $new_user_id );
                    $user_id = $user->ID;

                    wp_set_current_user($user_id, $user->user_login);
                    wp_set_auth_cookie($user_id);
                    do_action('wp_login', $user->user_login ,$user);

                    if ( is_user_logged_in() ) {
                        global $wp;
                        wp_safe_redirect( home_url( $wp->request ) );
                    }
                }
            }else{
                echo '<p class="err"> <span>&times;</span>&nbsp;&nbsp;&nbsp;Passwords do not match !! </p>';
            }
        }else{
            echo '<p class="err"> <span>&times;</span>&nbsp;&nbsp;&nbsp;All the fields must be filled !! </p>';
        }
    }

    return ob_get_clean();
}
add_filter('template_redirect', 'complete_login_user_registration');





function complete_login_custom_signin( $creds ) {

	$user = wp_signon( $creds, false );

    if ( is_wp_error( $user ) ) {
        $wp_err =  $user->get_error_message();
        return '<p class="err"> <span>&times;</span>&nbsp;&nbsp;&nbsp;'.$wp_err.'</p>';
    }else{
        //redirect back to current page
        global $wp;
        wp_safe_redirect( home_url( $wp->request ) );
    }
}
add_filter( 'template_redirect', 'complete_login_custom_signin');

// Run before the headers and cookies are sent.
// add_action( 'after_setup_theme', 'complete_login_custom_signin', 10, 2 );


/**
 * create user signin form
 */

function complete_login_user_signin(){
    ob_start(); ?>
        
    <h5>SIGN IN</h5>
    <form action="" method="POST">
        <div class="user-registration">
            <div class="inline-input">
                <label for="username"><b>User name</b></label>
                <input type="text" placeholder="Enter your name" name="user_name">
            </div>

            <div class="inline-input">
                <label for="signin_password"><b>Confirm Password</b></label>
                <input type="password" placeholder="Confirm password" name="signin_password">
            </div>

            <input type="submit" name="user_signin" value="SIGNIN">
        </div>
    </form>

    <!-- reset password url -->
    <a class="reset-pass" href="<?php echo esc_url( wp_lostpassword_url( get_home_url() ) ); ?>" alt="<?php esc_attr_e( 'Forgot your password?', 'hitmag' ); ?>">
        <?php esc_html_e( 'Forgot your password?', 'hitmag' ); ?>
    </a>
    <?php

    if( isset($_POST['user_signin']) ){


        $signin_user_name = $_POST['user_name'];
        $signin_password = $_POST['signin_password'];

        if( ! empty( $signin_user_name ) && ! empty( $signin_password ) ){
            //signin user
            $creds = [ 'user_login' => $signin_user_name, 'user_password' => $signin_password, 'remember' => true, ];
            echo complete_login_custom_signin( $creds );
        }else{
            echo '<p class="err"> <span>&times;</span>&nbsp;&nbsp;&nbsp;All the fields must be filled !! </p>';
        }
    }

    return ob_get_clean();
}
add_filter('template_redirect', 'complete_login_user_signin');










































function check_google_signin(){
    if(isset($_COOKIE['google_user_data']) && !empty($_COOKIE['google_user_data'])) { 
        $google_acc_arr = explode (",", $_COOKIE['google_user_data']); 
        $google_email = $google_acc_arr[2];

        $google_full_name = explode (" ", $google_acc_arr[1]);
        $google_first_name = $google_full_name[0];

        if ( email_exists( $google_email ) ) {

            //login
            $user = get_user_by( 'email', $google_email );
            $user_id = $user->ID;

            wp_set_current_user( $user_id, $user->user_login );
            wp_set_auth_cookie( $user_id );
            do_action( 'wp_login', $user->user_login ,$user );

            if ( is_user_logged_in() ) {
                global $wp;
                wp_safe_redirect( home_url( $wp->request ) );
            }

            // // Redirect URL
            // if ( !is_wp_error( $user ) )
            // {
            //     clean_user_cache($user->ID);
            //     $curr_uid = $user->ID;
            //     wp_set_current_user( $curr_uid, $user->user_login );
            //     update_user_caches( $user );

            //     if ( is_user_logged_in() ) {
            //         global $wp;
            //         wp_safe_redirect( home_url( $wp->request ) );
            //     }
            // }

            // echo "That E-mail is registered to user number " . email_exists( $google_email );

        } else {
            // $new_password = wp_generate_password( 8, false, false );
            // $new_user = wp_create_user( $google_first_name, $new_password, $google_email );

            // if ( is_wp_error( $new_user ) ) {
            //     $wp_err =  $new_user->get_error_message();
            //     echo '<p class="err"> <span>&times;</span>&nbsp;&nbsp;&nbsp;'.$wp_err.'</p>';
            // }else{
            // }

            // echo "That E-mail doesn't belong to any registered users on this site";
        }
    }
}
// add_filter('template_redirect', 'check_google_signin');




/**
 * Shows modal with different login options
 */

 function complete_login_third_party_login_providers(){ 

    //check if auth id / client id / client secret provided
    $google_auth_id = get_option( 'cl_google_client_id' );
    $google_btn_configured = !empty($google_auth_id) && $google_auth_id != "" && $google_auth_id != "---";

    //check if facebook app id provided
    $fbook_app_id = get_option( 'cl_facebook_app_id' );
    $fbook_btn_configured = !empty($fbook_app_id) && $fbook_app_id != "" && $fbook_app_id != "---";

    //check if client id or client secret provided
    $linkedin_client_id = get_option( 'cl_linkedin_client_id' );
    $linkedin_client_secret = get_option( 'cl_linkedin_client_secret' );
    $linkedin_btn_configured = !empty($linkedin_client_id) && $linkedin_client_id != "" && $linkedin_client_id != "---" && !empty($linkedin_client_secret) && $linkedin_client_secret != "" && $linkedin_client_secret != "---" ;
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

                <?php check_google_signin(); }else{ ?>
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











































    <script>

        /** 
         * Checking if the user is signed in to google. If the user is signed in, it hides the sign in button and
         * shows the sign out button. 
        */
        var getCookies = function(){
            var pairs = document.cookie.split(';');
            var cookies = {};
            for (var i=0; i<pairs.length; i++){
                var pair = pairs[i].split('=');
                cookies[(pair[0]+'').trim()] = unescape(pair.slice(1).join('='));
            }
            return cookies;
        }

        var myCookies = getCookies();
        console.log(myCookies.g_cookie);

        if(myCookies.g_cookie === 'signedin'){ // if user signed in
            //hide the signin button
            document.getElementById('google_login').classList.add('hide');

            //show signout button
            document.getElementById('google-logout').classList.remove('hide');
        }


        /**
         * The function takes the response from the Google Sign-In API and decodes the JWT response to
         * get the user's ID, name, image URL, and email address.
         */
        function handleCredentialResponse(response) {
            
            //set current user info in cookie
            const responsePayload = decodeJwtResponse(response.credential);
            console.log('Google ID: ' + responsePayload.sub); // Do not send to your backend! Use an ID token instead.
            console.log('Google Name: ' + responsePayload.name);
            console.log('Google Image URL: ' + responsePayload.picture);
            console.log('Google Email: ' + responsePayload.email); // This is null if the 'email' scope is not present.

            let google_id = responsePayload.sub;
            let google_name = responsePayload.name;
            let google_email = responsePayload.email;
            let google_img_url = responsePayload.picture;

            //set cookie after signin
            document.cookie = 'g_cookie=signedin';
            document.cookie = 'google_user_data='+google_id+','+google_name+','+google_email+','+google_img_url;

            location.reload();

        }


        /**
         * The JWT is a string separated by periods. The payload is decoded and parsed into a
         * JSON object.
         * 
         * @return The JWT token is being returned.
         */
        function decodeJwtResponse(data){
            var tokens = data.split(".");
            return JSON.parse(atob(tokens[1]));
        }


        /**
         * Revoking the consent of the user. 
         */

        const googleSignoutBtn = document.getElementById('google-logout');
        
        googleSignoutBtn.onclick = () => {
            //set cookie to empty after signout
            document.cookie = 'g_cookie=';
            document.cookie = 'google_user_data=';
            google.accounts.id.revoke('neplese931@gmail.com', done => {
                console.log('consent revoked');
            });
        }

    </script>
<?php }
// add_filter('template_redirect', 'complete_login_third_party_login_providers');