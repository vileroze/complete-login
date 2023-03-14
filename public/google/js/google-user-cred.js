console.log(myCookies.tpl_user_data);

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
    document.cookie = 'tpl_user_data='+google_id+','+google_name+','+google_email+','+google_img_url+','+'google';

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
var googleSignoutBtn = document.getElementById('google-logout');

googleSignoutBtn.onclick = () => {
    //set cookie to empty after signout
    document.cookie = 'g_cookie=';
    document.cookie = 'tpl_user_data=';
    google.accounts.id.revoke('neplese931@gmail.com', done => {
        console.log('consent revoked');
    });
}