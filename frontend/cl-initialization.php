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
    add_action('wp_enqueue_scripts', 'complete_login_enqueue_scripts');
    function complete_login_enqueue_scripts(){
        wp_enqueue_style( 'complete-login-style' );
        wp_enqueue_script( 'complete-login-script' );
    }


    //display user registration form if user not logged in
    if ( ! is_user_logged_in() ) {

        function complete_login_third_party_login(){

            //intializing google library
            echo '<meta name="google-signin-client_id" content="975954367849-kpnpua9cia8pk9n882o9jgnm8cctpehd.apps.googleusercontent.com">';
            echo '<script src="https://accounts.google.com/gsi/client" async defer></script>';

            //intializing facebook js sdk
            echo "<script>
                    //check for change in login status
                    function statusChangeCallback(response) {  // Called with the results from FB.getLoginStatus().
                        if (response.status === 'connected') {   // Logged into your webpage and Facebook.
                            FB.api('/me?fields=name,email', function(response) {
                                
                                console.log('You are currently logged in using facebook');
                                console.log(response); 
                                console.log('FB id: ' + response.id);
                                console.log('FB name: ' + response.name);
                                console.log('FB email: ' + response.email);

                                //show singout button
                                document.getElementById('fb-logout').classList.remove('hide');

                                //hide signin button
                                document.getElementById('fb-login').classList.add('hide');
                            });
                        } else if (response.status === 'unknown') {  // Not logged into your webpage or we are unable to tell.
                            // alert('No user currently logged in');
                            console.log('No no user currently logged in using facebook');
                        }else{
                            console.log('Definetly no user currently logged in using facebook');
                        }
                    }

                    function checkLoginState() { // Called when a person is finished with the Login Button.
                        FB.getLoginStatus(function(response) { // See the 'onlogin' handler
                            statusChangeCallback(response);
                        });
                    }

                    window.fbAsyncInit = function() {
                        FB.init({
                            appId      : '1336979370426534',
                            cookie     : true, // Enable cookies to allow the server to access the session.
                            xfbml      : true, // Parse social plugins on this webpage.
                            version    : 'v16.0' // Use this Graph API version for this call.
                        });

                        FB.Canvas.setSize();
                        FB.Canvas.setAutoGrow(7);

                        FB.getLoginStatus(function(response) { // Called after the JS SDK has been initialized.
                            statusChangeCallback(response); // Returns the login status.
                        });

                        FB.login(function(response) {
                            // handle the response
                            console.log('User logged in to facebook !!!')
                            console.log(response);

                        }, {scope: 'name,email', return_scopes: true});
                    };

                    (function(d, s, id){
                        var js, fjs = d.getElementsByTagName(s)[0];
                        if (d.getElementById(id)) {return;}
                        js = d.createElement(s); js.id = id;
                        js.src = 'https://connect.facebook.net/en_US/sdk.js';
                        fjs.parentNode.insertBefore(js, fjs);
                    }(document, 'script', 'facebook-jssdk'));

                    //logout function
                    function fbSignOut(){
                        FB.logout(function(response) {
                            // Person is now logged out
                            console.log('User logged out from facebook !!!');
                        });
                    }

                </script>";        
        }
        add_action( 'wp_head', 'complete_login_third_party_login' );

        function complete_login_fb(){
            echo '<script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js"></script>';
        }
        add_action( 'wp_footer', 'complete_login_fb' );


        //adding nav item to display login options
        add_filter('wp_nav_menu_items','complete_login_custom_menu_items', 10, 2);
        function complete_login_custom_menu_items( $items, $args ) 
        {
            if( $args->theme_location == 'menu-1' ) // only for primary menu
            {
                $items_array = array();
                while ( false !== ( $item_pos = strpos ( $items, '<li', 3 ) ) )
                {
                    $items_array[] = substr($items, 0, $item_pos);
                    $items = substr($items, $item_pos);
                }
                $items_array[] = $items;
                array_splice($items_array, sizeof($items_array), 0, '<li><button id="myBtn">LOGIN OPTIONS</button>'.complete_login_third_party_login_providers().'</li>'); // insert custom item after 2nd one

                $items = implode('', $items_array);
            }
            return $items;
        }
    }
}



/**
 * Shows modal with different login options
 */

 function complete_login_third_party_login_providers(){

    ?>

    <!-- The Modal -->
    <div id="myModal" class="modal">
        <!-- Modal content -->
        <div class="modal-content">
            <span class="close">&times;</span>
            <h5>LOGIN</h5>

            <div class="user-login">

                <!-- Google signin button -->
                <div id="g_id_onload"
                    data-client_id="975954367849-kpnpua9cia8pk9n882o9jgnm8cctpehd.apps.googleusercontent.com"
                    data-context="signin"
                    data-ux_mode="popup"
                    data-callback="handleCredentialResponse"
                    data-auto_prompt="false">
                </div>

                <div class="g_id_signin"
                    data-type="standard"
                    data-shape="rectangular"
                    data-theme="filled_blue"
                    data-text="login_with"
                    data-size="large"
                    data-locale="en-US"
                    data-logo_alignment="left">
                </div>

                <!-- Google logout button -->
                <div id="google-logout" class="">
                    <a href="" onclick="googleSignOut();">Logout from Google?</a>
                </div>
            </div>

            <div class="user-login">

                <!-- Facebook signin button -->
                <div id="fb-root"></div>

                <script 
                    async 
                    defer 
                    crossorigin="anonymous" 
                    src="https://connect.facebook.net/en_GB/sdk.js#xfbml=1&version=v16.0&appId=1336979370426534&autoLogAppEvents=1" nonce="OJDXbaIR">  
                </script>
                
                <div 
                    id="fb-login"
                    class="fb-login-button" 
                    onlogin="checkLoginState();"
                    data-size="medium" 
                    data-button-type="" 
                    data-layout=""
                    data-auto-logout-link="false" 
                    data-use-continue-as="false">
                </div>

                <!-- Facebook logout button -->
                <div id="fb-logout" class="hide">
                    <a href="" onclick="fbSignOut();">Logout from Facebook?</a>
                </div>
            </div>
        </div>
    </div>


    <script>

        /**
         * The function takes the response from the Google Sign-In API and decodes the JWT response to
         * get the user's ID, name, image URL, and email address.
         */
        function handleCredentialResponse(response) {
            const responsePayload = decodeJwtResponse(response.credential);
            console.log('Google ID: ' + responsePayload.sub); // Do not send to your backend! Use an ID token instead.
            console.log('Google Name: ' + responsePayload.name);
            console.log('Google Image URL: ' + responsePayload.picture);
            console.log('Google Email: ' + responsePayload.email); // This is null if the 'email' scope is not present.
        }

        /**
         * The JWT is a string separated by periods. The second part of the string is the payload. The
         * payload is base64 encoded. The payload is JSON. The payload is decoded and parsed into a
         * JSON object.
         * 
         * @return The JWT token is being returned.
         */
        function decodeJwtResponse(data){
            var tokens = data.split(".");
            return JSON.parse(atob(tokens[1]));
        }


        /**
         * The signOut() method of the GoogleAuth object signs the user out of the application and
         * revokes all of the scopes that the user granted.
         */
        function googleSignOut() {
            var auth2 = gapi.auth2.getAuthInstance();
            auth2.signOut().then(function () {
            console.log('User signed out from google.');
            });
        }

    </script>
<?php }