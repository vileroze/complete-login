<?php

/**
 * It creates a new user, logs them in, and redirects them to the current page.
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

    if( isset( $_POST['user_registration'] ) ){

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
                    do_action( 'wp_login', $user->user_login ,$user );

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




/**
 * It creates a form that allows users to sign in.
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
            $user = wp_signon( $creds, false );

            if ( is_wp_error( $user ) ) {
                $wp_err =  $user->get_error_message();
                return '<p class="err"> <span>&times;</span>&nbsp;&nbsp;&nbsp;'.$wp_err.'</p>';
            }else{
                //redirect back to current page
                global $wp;
                wp_safe_redirect( home_url( $wp->request ) );
            }
            
        }else{
            echo '<p class="err"> <span>&times;</span>&nbsp;&nbsp;&nbsp;All the fields must be filled !! </p>';
        }
    }

    return ob_get_clean();
}
add_filter('template_redirect', 'complete_login_user_signin');


