<?php

/**
 * STEP: 1
 * get the 'code' from url
 */

$first_code = empty($_GET['code']) ? '' : $_GET['code'];
$li_client_id = get_option( 'cl_linkedin_client_id' );
$li_client_secret = get_option( 'cl_linkedin_client_secret' );

if ( $first_code != '' ) {//&& !empty($li_client_id) && $li_client_id != "" && $li_client_id != "---" && !empty($li_client_secret) && $li_client_secret != "" && $li_client_secret != "---"

    $first_code = trim($first_code," ");
    $user_name = '';
    $user_email = '';


    /**
     * STEP: 2
     * pass it into the below url to get access token
     */

    $url = "https://www.linkedin.com/oauth/v2/accessToken?code=".$first_code."&grant_type=authorization_code&client_id=".$li_client_id."&client_secret=".$li_client_secret."&redirect_uri=https://scary-novel.localsite.io/";
    $json = file_get_contents($url);
    $json_data = json_decode($json, true);
    $linkedin_access_token = $json_data["access_token"];


    /**
     * STEP: 3
     * pass the above access token below as a param to get user info
     * gets both name and email separately
     */

    //gets basic user info (excluding email)
    $url_basic = 'https://api.linkedin.com/v2/me';

    $ch_user_info = curl_init($url_basic);                                                                      
    curl_setopt($ch_user_info, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch_user_info, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer '. $linkedin_access_token,
        'X-Restli-Protocol-Version: 2.0.0',
        'Accept: application/json',
        'Content-Type: application/json'
      ]);
      curl_setopt($ch_user_info, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch_user_info, CURLOPT_VERBOSE, 1);
      curl_setopt($ch_user_info, CURLOPT_HEADER, 1);

    $basic_response = curl_exec($ch_user_info);
    $basic_headerSize = curl_getinfo($ch_user_info, CURLINFO_HEADER_SIZE);
    $basic_body = substr($basic_response, $basic_headerSize);
    $basic_response_body = json_decode($basic_body,true);
 

    //gets email address of user
    $url_for_email = 'https://api.linkedin.com/v2/emailAddress?q=members&projection=(elements*(handle~))';

    $ch_user_email = curl_init();
    curl_setopt($ch_user_email, CURLOPT_URL, $url_for_email);
    curl_setopt($ch_user_email, CURLOPT_HTTPHEADER, [
      'Authorization: Bearer '. $linkedin_access_token,
      'X-Restli-Protocol-Version: 2.0.0',
      'Accept: application/json',
      'Content-Type: application/json'
    ]);
    curl_setopt($ch_user_email, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch_user_email, CURLOPT_VERBOSE, 1);
    curl_setopt($ch_user_email, CURLOPT_HEADER, 1);

    $email_response = curl_exec($ch_user_email);
    $email_headerSize = curl_getinfo($ch_user_email, CURLINFO_HEADER_SIZE);
    $email_body = substr($email_response, $email_headerSize);
    $email_response_body = json_decode($email_body,true);


    //print all user info (name and email)
    $user_email = (($email_response_body['elements'][0])['handle~'])['emailAddress'];
    $user_name = $basic_response_body["localizedFirstName"] . ' ' .$basic_response_body["localizedLastName"];
    ?>
        <script>
            console.log("=========== Linkedin User Info ===========");
            console.log("Linkedin name =  <?php echo $user_name; ?>");
            console.log("Linkedin email =  <?php echo $user_email; ?>");
        </script>
    <?php

}else{
    echo '<script> console.log("Linkedin button not pressed"); </script>';
}

