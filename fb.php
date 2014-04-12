<?php

require_once 'config.php';
require_once 'vendor/facebook-php-sdk/facebook.php';

/**
 * Returns Facebook user if currently logged in, NULL otherwise.
 * @return object
 */
function getFacebookUser() {
    $facebook = new Facebook(array(
        'appId' => FB_APP_ID,
        'secret' => FB_APP_SECRET,
    ));

    $userId = $facebook->getUser();

    if ($userId) {
        // We have a user ID, so probably a logged in user.
        // If not, we'll get an exception, which we handle below.
        try {
            $userProfile = $facebook->api('/me', 'GET');
            return array(
                'name' => $userProfile['name'],
                'email' => $userProfile['email']);
        } catch (FacebookApiException $e) {
            // If the user is logged out, you can have a 
            // user ID even though the access token is invalid.
            // In this case, we'll get an exception, so we'll
            // just ask the user to login again here.
            error_log($e->getType());
            error_log($e->getMessage());
        }
    }

    return NULL;
}
