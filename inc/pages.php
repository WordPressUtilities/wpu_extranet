<?php

/* ----------------------------------------------------------
  Pages settings
---------------------------------------------------------- */

/* Basic links
-------------------------- */

function wpu_extranet__get_login_page() {
    return apply_filters('wpu_extranet__get_login_page', site_url('#panel-login'));
}

function wpu_extranet__get_dashboard_page() {
    return apply_filters('wpu_extranet__get_dashboard_page', get_permalink(get_option('extranet_dashboard__page_id')));
}

function wpu_extranet__get_lostpassword_page() {
    return apply_filters('wpu_extranet__get_lostpassword_page', get_permalink(get_option('extranet_lostpassword__page_id')));
}

function wpu_extranet__get_register_page() {
    return apply_filters('wpu_extranet__get_register_page', get_permalink(get_option('extranet_register__page_id')));
}

/* Mark a page as extranet
-------------------------- */

add_filter('wputh_post_metas_boxes', function ($boxes) {
    $boxes['box_page_extranet'] = array(
        'name' => 'Extranet',
        'post_type' => array('page')
    );
    return $boxes;
});

add_filter('wputh_post_metas_fields', function ($fields) {
    $fields['is_extranet_page'] = array(
        'box' => 'box_page_extranet',
        'name' => 'Extranet page',
        'type' => 'checkbox'
    );
    return $fields;
});

/* ----------------------------------------------------------
  Templates
---------------------------------------------------------- */

/* Menu
-------------------------- */

function wpu_extranet_get_menu($args = array()) {
    if (!is_array($args)) {
        $args = array();
    }

    if (!isset($args['has_logout'])) {
        $args['has_logout'] = true;
    }

    $html = '';

    $cache_id = 'wpu_extranet_get_menu_posts';
    $cache_duration = 30 * 86400;

    // GET CACHED VALUE
    $posts_extranet = wp_cache_get($cache_id);
    if ($posts_extranet === false) {
        // COMPUTE RESULT
        $posts_extranet = get_posts(array(
            'post_type' => 'page',
            'posts_per_page' => -1,
            'orderby' => 'menu_order',
            'meta_query' => array(array(
                'key' => 'is_extranet_page',
                'compare' => '1',
                'value' => '1'
            ))
        ));
        // CACHE RESULT
        wp_cache_set($cache_id, $posts_extranet, '', $cache_duration);
    }

    $html .= '<ul class="extranet-menu">';
    foreach ($posts_extranet as $p) {
        $html .= '<li><a ' . ($p->ID == get_the_ID() ? 'class="active"' : '') . ' href="' . get_permalink($p) . '">' . get_the_title($p) . '</a></li>';
    }
    if ($args['has_logout']) {
        $html .= '<li><a href="' . wp_logout_url(site_url()) . '">' . __('Log out', 'wpu_extranet') . '</a></li>';
    }
    $html .= '</ul>';

    return $html;
}

add_action('save_post', function () {
    wp_cache_delete('wpu_extranet_get_menu_posts');
});
