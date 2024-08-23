<?php
defined('ABSPATH') || die;

/* ----------------------------------------------------------
  Settings for default avatar
---------------------------------------------------------- */

/* Boxes */
add_filter('wpu_options_boxes', function ($boxes) {
    $boxes['wpu_extranet_default'] = array(
        'name' => '[WPU Extranet] Settings'
    );
    return $boxes;
}, 10, 1);

/* Fields */
add_filter('wpu_options_fields', function ($options) {
    $options['wpu_extranet_default_avatar'] = array(
        'help' => __('Image must be accessible from the Internet (no local env, no preproduction env)', 'wpu_extranet'),
        'label' => __('Default avatar', 'wpu_extranet'),
        'box' => 'wpu_extranet_default',
        'type' => 'media'
    );
    return $options;
}, 10, 1);
