//check for change in userlogin status
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
        console.log('No user currently logged in using facebook');
    }else{
        console.log('Definetly no user currently logged in using facebook');
    }
}

function checkLoginState() { // Called when a person clicks the Login Button.
    FB.getLoginStatus(function(response) { // See the 'onlogin' handler
        statusChangeCallback(response);
    });
}

window.fbAsyncInit = function() {
    FB.init({
        appId      : '".$fb_app_id."',
        cookie     : true, // Enable cookies to allow the server to access the session.
        xfbml      : true, // Parse social plugins on this webpage.
        version    : 'v16.0' // Use this Graph API version for this call.
    });

    FB.getLoginStatus(function(response) { // Called after the JS SDK has been initialized.
        statusChangeCallback(response); // Returns the login status.
    });

    // FB.login(function(response) {
    //     // handle the response
    //     console.log('User logged in to facebook !!!')
    //     console.log(response);

    // }, {scope: 'name,email', return_scopes: true});
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