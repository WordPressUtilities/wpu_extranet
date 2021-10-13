<?php

/* Failed login in front : redirect to the home page
-------------------------- */

add_action('wp_login_failed', function ($username) {
    $referer = wp_get_referer();
    if (!strstr($referrer, 'wp-login') && !strstr($referrer, 'wp-admin')) {
        wp_redirect(add_query_arg('login', 'failed', wpu_extranet__get_login_page()));
        exit;
    }
});

/* Redirect to extranet after login if the target page is in wp-admin
-------------------------- */

add_filter('login_redirect', function ($url, $request, $user) {
    if (strpos($url, 'wp-admin') !== false && $user && is_object($user) && is_a($user, 'WP_User') && !$user->has_cap('edit_posts')) {
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

/* Force login back URL
-------------------------- */

add_filter('login_url', function ($url) {
    if ($GLOBALS['pagenow'] != 'wp-login.php' || !isset($_GET['action'])) {
        return $url;
    }
    if ($_GET['action'] == 'resetpass' || $_GET['action'] == 'rp') {
        return wpu_extranet__get_login_page();
    }
    return $url;
}, 10, 1);

/* Change login back URL
-------------------------- */

add_filter('login_site_html_link', function ($content) {
    $content = sprintf(
        '<a href="%s">%s</a>',
        esc_url(wpu_extranet__get_login_page()),
        sprintf(
            /* translators: %s: Site title. */
            _x('&larr; Go to %s', 'site'),
            get_bloginfo('title', 'display')
        )
    );
    return $content;
}, 10, 1);
