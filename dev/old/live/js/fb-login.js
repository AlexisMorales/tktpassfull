// Load the SDK asynchronously
(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s);
  js.id = id;
  js.src = "//connect.facebook.net/en_US/sdk.js";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));

window.fbAsyncInit = function() {
  FB.init({
    appId: '1616269921948808',
    oauth   : true,
    status  : true, // check login status
    cookie: true, // enable cookies to allow the server to access the session
    xfbml: false, // parse social plugins on this page
    version: 'v2.5', // use version 2.2
    redirect_uri: window.location.href
  });

  // Now that we've initialized the JavaScript SDK, we call 
  // FB.getLoginStatus().  This function gets the state of the
  // person visiting this page and can return one of three states to
  // the callback you provide.  They can be:
  //
  // 1. Logged into your app ('connected')
  // 2. Logged into Facebook, but not your app ('not_authorized')
  // 3. Not logged into Facebook and can't tell if they are logged into
  //    your app or not.
  //
  // These three cases are handled in the callback function.

  FB.getLoginStatus(statusChangeCallback);

};

// This is called with the results from from FB.getLoginStatus().
function statusChangeCallback(response) {
  console.log('statusChangeCallback');
  console.log(response);
  // The response object is returned with a status field that lets the
  // app know the current login status of the person.
  // Full docs on the response object can be found in the documentation
  // for FB.getLoginStatus().
  if (response.status === 'connected') {
    // Logged into your app and Facebook.=
    loggedIn();
  } else {
      $('#login,#mob-login').html('Sign Up / Log In');
  }
}

// Ran after login is successful.
// See statusChangeCallback() for when this call is made.
function loggedIn() {
  console.log('Welcome!  Fetching your information.... ');
  FB.api('/me/permissions', function(response) {
    var allRequired = true;
    response.data.forEach(function(e,i,a){
        if(e.status == "declined") allRequired = false;
    });
    if(allRequired){
      FB.api('/me?fields=id,name,first_name,last_name,email,birthday,gender,picture', function(response) {
        $.ajax({
          url: '/api/users',
          method: 'POST',
          data: {
            fb_id: response.id,
            email: response.email
          },
          success: function(d){console.log(d)}
        });
        for(var field in response) {
           $('#account').data(field,response[field]);//$('#account').attr('data-'+field,response[field]);
        }
        $('#loginModal .alert-danger').slideUp();
        $('#loginModal').modal('hide');
        $('#login').parent().hide();
        $('#account').parent().show();
        $('#account').html('<img src="'+response.picture.data.url+'" class="account-pic"> Hi '+response.first_name+' <i class="fa fa-angle-down"></i>');
        $('#mob-account').html('<img src="'+response.picture.data.url+'" class="account-pic"> Hi '+response.first_name);
        if(/account/.test(document.location.href)) $('#account').addClass('active');
        $('.mobile-navbar').addClass('logged-in');
      });
    } else {
      $('#loginModal .alert-danger').slideDown();
      $('#fb-login').off('click').on('click',function(){fb_login(true);});
    }
  });
}

function fb_login(rerequest){
    $('#login,#mob-login').html('Logging in..');
    var opts = {
      scope: 'public_profile,user_friends,email,user_about_me,user_birthday,user_events,rsvp_event'
    };
    if(rerequest) opts.auth_type = 'rerequest';
    FB.login(statusChangeCallback, opts);
}

(function(window,document,$,undefined){
    $('#fb-login').on('click',function(){fb_login();});
})(this,this.document,this.jQuery)