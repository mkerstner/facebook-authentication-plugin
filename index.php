<?php
require_once 'config.php';
require_once 'fb.php';

// set this to your user's browser locale. You might want to determine this 
// settings automatically ;)
$locale = 'en_US';

$fbUser = getFacebookUser();
?>

<html>

    <head>
        <script src="//code.jquery.com/jquery-2.1.0.min.js"></script>
    </head>

    <title>Facebook Authentication Plugin</title>

    <body>

        <?php if ($fbUser) : ?>
            <h1>Welcome <?php echo $fbUser['name'] . ' (' . $fbUser['email']; ?>)!</h1>
            <p>Feel free to <a href="#" onclick="fbLogout();">logout</a> again (or via Facebook directly) :)</p>
        <?php endif; ?>

        <!-- // We need this container for the Facebook JavaScript SDK -->
        <div id="fb-root"></div>

        <!-- // The following incorporates additional functionality for Facebook JavaScript SDK -->
        <script>

            /**
             * Handle FB login.
             * @param {Object} response
             * @see https://developers.facebook.com/docs/javascript/reference
             */
            function fbHandleLogin(response) {

                console.debug('Hey there! We are quickly processing your login '
                        + 'request based on your FB authentication status...');

                if (response.authResponse) {
                    /**
                     * we are simply refreshing to page for now so that we can 
                     * test the Facebook PHP SDK as well.
                     * Feel free to update this event handler to your needs.
                     */
                    window.location.reload();
                } else {
                    console.debug('Uups, it seems like our user did not finish '
                            + 'the login process');
                }
            }

            /**
             * Handle FB register action.
             * @param {Object} response
             * https://developers.facebook.com/docs/javascript/reference
             */
            function fbHandleRegister(response) {

                console.debug('Hey there! We are quickly processing your '
                        + 'register request based on your FB authentication status...');

                if (response.authResponse) {
                    /**
                     * we are simply refreshing to page for now so that we can 
                     * test the Facebook PHP SDK as well.
                     * Feel free to update this event handler to your needs.
                     */
                    window.location.reload();
                } else {
                    console.debug('Uups, it seems like our user did not finish '
                            + 'the register process');
                }
            }

            /**
             * Handles FB session status.
             * @param {Object} response
             * @see https://developers.facebook.com/docs/javascript/reference
             */
            function fbHandleSession(response) {

                console.debug('Hey there! We are quickly checking your Facebook '
                        + 'login status for further actions...');

                if (response.authResponse) {
                    if (response.status === 'connected') {
                        /**
                         * our user is already logged in to FB and connected to 
                         * our app - great!
                         * Let's handle the connected state.
                         */
                        fbHandleConnected(response);
                    } else if (response.status === 'not_authorized') {
                        /**
                         * our user is logged in to FB but not to our app. 
                         * We don't bother the user with login pop-ups or other
                         * notifications at the moment. So let's silently
                         * ignore this state. 
                         * Feel free to add status messages as needed here.
                         */
                        console.debug('Welcome logged in FB user, '
                                + 'you might want to authenticate our app to '
                                + 'access all features?');
                    } else {
                        /**
                         * our user is not logged in to FB so we can't check for
                         * possible app permissions we've set. 
                         * We don't bother the user with login pop-ups or other
                         * notifications at the moment. So let's silently
                         * ignore this state. 
                         * Feel free to add status messages as needed here.
                         */
                        console.debug('Welcome user, '
                                + 'you might want to login to FB and '
                                + 'authenticate our app to access all features?');
                    }
                } else {
                    console.debug('... welcome STRANGER!');
                }
            }

            /**
             * Fetches user information.
             * Requires user to be logged in to FB and connection to app, i.e.
             * user has accepted scope permissions.
             * @param {Object} response
             * @see https://developers.facebook.com/docs/javascript/reference
             */
            function fbHandleConnected(response) {
                console.log('Welcome! Fetching your information based ' +
                        'on scope permissions previously requested.... ');

                if (response.authResponse && response.status === 'connected') {
                    /**
                     * Let's request some user data to display. Feel free to adjust
                     * this event handler to your needs ;)
                     */
                    FB.api('/me', function(rsp) {
                        console.log('Good to see you: ' + rsp.name
                                + ' (' + rsp.email + ')');
                    });
                } else {
                    console.debug('Sorry, user is not logged in to FB and/or '
                            + 'connected to our app');
                }
            }

            /**
             * Logs out user and reloads page on success.
             */
            function fbLogout() {
                FB.logout(function(response) {
                    window.location.reload();
                });
            }

            /**
             * Asynchronous Facebook JavaScript SDK inclusion.
             * Takes care of connecting event handlers and initial setup.
             * @returns {undefined}
             */
            window.fbAsyncInit = function() {
                FB.init({
                    appId: '<?php echo FB_APP_ID; ?>',
                    status: true,
                    cookie: true, // enable cookies to allow the server to access the session
                    xfbml: true  // parse XFBML
                });

                /**
                 * connect event handler to user's FB authentication status
                 * @type type
                 */
                FB.getLoginStatus(fbHandleSession);

                /**
                 * here you can set to request additional permissions from your
                 * user. Keep in mind that the more permissions you request the
                 * higher is the chance that users will deny access to their 
                 * profile
                 * @type Object
                 */
                var fbScope = {scope: 'email, user_location'};

                jQuery('document').ready(function() {
                    /**
                     * connect custom FB login button
                     */
                    if (jQuery('#CustomFacebookLoginButton')) {
                        jQuery('#CustomFacebookLoginButton').bind('click', function() {
                            FB.login(fbHandleLogin, fbScope);
                        });
                    }
                    /**
                     * connect custom FB register button
                     */
                    if (jQuery('#CustomFacebookRegisterButton')) {
                        jQuery('#CustomFacebookRegisterButton').bind('click', function() {
                            FB.login(fbHandleRegister, fbScope);
                        });
                    }
                });
                /**
                 * Here we subscribe to the auth.authResponseChange JavaScript 
                 * event. This event is fired for any authentication related 
                 * change, such as login, logout or session refresh. This means 
                 * that whenever someone who was previously logged out tries to 
                 * log in again, the correct case below will be handled. 
                 */
                FB.Event.subscribe('auth.authResponseChange', function(response) {
                    fbHandleSession(response);
                });
            };

            // Load the SDK asynchronously
            (function(d) {
                var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
                if (d.getElementById(id)) {
                    return;
                }
                js = d.createElement('script');
                js.id = id;
                js.async = true;
                js.src = "//connect.facebook.net/<?php echo $locale; ?>/all.js";
                ref.parentNode.insertBefore(js, ref);
            }
            (document));
        </script>

        <p>You can include to default Facebook Login Button that will trigger 
            <b>FB.login()</b> (i.e. without parameters) when clicked.</p>

    <fb:login-button show-faces="false" width="200" max-rows="1"></fb:login-button>

    <p>In case you want to pass <b>additional parameters</b> you can also use custom
        icons, as shown below:</p>

    Custom LOGIN button: <img src="images/custom-fb-login.png" id="CustomFacebookLoginButton" height="25"/>

    <br/><br/>

    Custom REGISTER button: <img src="images/custom-fb-register.png" id="CustomFacebookRegisterButton" height="25"/>
</body>
</html>