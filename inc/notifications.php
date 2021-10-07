<?php

/* ----------------------------------------------------------
  Disable password change mail
---------------------------------------------------------- */

if (!function_exists('wp_password_change_notification')) {
    function wp_password_change_notification($user) {
        return;
    }
}

/* ----------------------------------------------------------
  Disable password reset success mail
---------------------------------------------------------- */

add_filter('send_password_change_email', '__return_false');

/* ----------------------------------------------------------
  Disable new account email
---------------------------------------------------------- */

if (!function_exists('wp_new_user_notification')) {
    function wp_new_user_notification($user) {
        return;
    }
}
