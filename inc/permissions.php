<?php

/* Failed login in front : redirect to the home page
-------------------------- */

add_action('wp_login_failed', function ($username) {
    if (!strstr($referrer, 'wp-login') && !strstr($referrer, 'wp-admin')) {
        wp_redirect(add_query_arg('login', 'failed', wpu_extranet__get_login_page()));
        exit;
    }
});

/* Redirect to extranet after login
-------------------------- */

add_filter('login_redirect', function ($url, $request, $user) {
    if ($user && is_object($user) && is_a($user, 'WP_User') && !$user->has_cap('edit_posts')) {
        return wpu_extranet__get_dashboard_page();
    }
    return $url;
}, 10, 3);

/* Redirect to login if invalid access to a private page
-------------------------- */

add_action('wp', function () {
    if (is_user_logged_in()) {
        return;
    }
    $queried_object = get_queried_object();
    if (!is_object($queried_object) || !isset($queried_object->post_status)) {
        return;
    }
    $is_extranet_page = get_post_meta(get_the_ID(), 'is_extranet_page', 1) == '1';
    if (!$is_extranet_page) {
        return;
    }
    wp_redirect(wpu_extranet__get_login_page());
});

/* Disable admin bar
-------------------------- */

add_action('set_current_user', function () {
    if (!current_user_can('edit_posts')) {
        show_admin_bar(false);
    }
});
