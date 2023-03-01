<?php

//STEP: 1
//get the 'code' from url
$first_code = empty($_GET['code']) ? '' : $_GET['code'];
if ($first_code != '') {

    $first_code = trim($first_code," ");
    $user_firstname = '';
    $user_lastname = '';
    $user_email = '';

    ?>
        <script>
            //print code
            // console.log("CODE== <?php //echo $first_code; ?>");
        </script>
    <?php

    //STEP: 2
    //pass it into the below url to get access token
    $url = "https://www.linkedin.com/oauth/v2/accessToken?code=".$first_code."&grant_type=authorization_code&client_id=86q3eul364itg6&client_secret=Tev6wxO3NnTVnLAH&redirect_uri=https://scary-novel.localsite.io/";
    $json = file_get_contents($url);
    $json_data = json_decode($json, true);
    $linkedin_access_token = $json_data["access_token"];

    //print the access token
    ?>
    <script>
        // console.log("ACCESS TOKEN== <?php //echo $linkedin_access_token; ?>");
    </script>
    <?php

    //STEP: 3

    //pass the above access token below as a param to get user info

    //gets basic user info (excluding email)
    $url_basic = 'https://api.linkedin.com/v2/me';

    $additional_headers = [                                                                        
        'Authorization: Bearer '.$linkedin_access_token.'',
    ];

    $ch_user_info = curl_init($url_basic);                                                                      
    curl_setopt($ch_user_info, CURLOPT_CUSTOMREQUEST, "GET");                                                                     
    curl_setopt($ch_user_info, CURLOPT_HTTPHEADER, $additional_headers); 

    $server_output = curl_exec ($ch_user_info); //gets basic profile

    ?>
        <script>
            
            var user_info = JSON.stringify(<?php echo $server_output; ?>); 
            // console.info("==USER INFO== " + user_info); 
            console.log((<?php print_r($server_output); ?>));
        </script>
    <?php

    //get email address of user
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

    $response = curl_exec($ch_user_email);
    $headerSize = curl_getinfo($ch_user_email, CURLINFO_HEADER_SIZE);
    $body = substr($response, $headerSize);
    $response_body = json_decode($body,true);

    //print the email address
    ?>
    <script>
        console.log("==EMAIL ADDR==");
        console.log((<?php print_r($response_body); ?>));
        console.log(
            <?php 
                print_r((($response_body['elements'][0])['handle~'])['emailAddress']); 
            ?>);
    </script>
    <?php

    
}else{

    ?>
        <script>console.log("======= <?php echo 'code aako chaina'; ?>"); </script>
    <?php
}

