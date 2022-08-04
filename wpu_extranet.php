<?php

/*
Plugin Name: WPU Extranet
Description: Simple toolbox to create an extranet or a customer account
Version: 0.6.0
Author: Darklg
Author URI: https://darklg.me/
License: MIT License
License URI: http://opensource.org/licenses/MIT
Plugin URI: https://github.com/WordPressUtilities/wpu_extranet
*/

/* ----------------------------------------------------------
  Settings
---------------------------------------------------------- */

include dirname(__FILE__) . '/inc/notifications.php';
include dirname(__FILE__) . '/inc/pages.php';
include dirname(__FILE__) . '/inc/permissions.php';
include dirname(__FILE__) . '/inc/user.php';

/* ----------------------------------------------------------
  Modules
---------------------------------------------------------- */

include dirname(__FILE__) . '/inc/modules/change-password.php';
include dirname(__FILE__) . '/inc/modules/edit-metas.php';
include dirname(__FILE__) . '/inc/modules/lost-password.php';
include dirname(__FILE__) . '/inc/modules/register.php';

/* ----------------------------------------------------------
  Translation
---------------------------------------------------------- */

add_action('plugins_loaded', 'wpu_extranet_plugins_loaded', 10);
function wpu_extranet_plugins_loaded() {
    load_muplugin_textdomain('wpu_extranet', dirname(plugin_basename(__FILE__)) . '/lang/');
}

/* ----------------------------------------------------------
  Skin
---------------------------------------------------------- */

function wpu_extranet_get_skin_settings() {
    $base_settings = array(
        'form_wrapper_classname' => 'form-extranet',
        'form_items_classname' => 'cssc-form cssc-form--extranet',
        'form_box_classname' => 'box box--extranet',
        'form_box_submit_classname' => 'box box--extranet box--submit',
        'form_submit_button_classname' => 'cssc-button cssc-button--extranet'
    );
    $settings = apply_filters('wpu_extranet_get_skin_settings', $base_settings);
    if (!is_array($settings)) {
        $settings = array();
    }
    foreach ($base_settings as $key => $val) {
        if (!isset($settings[$key])) {
            $settings[$key] = $val;
        }
    }
    return $settings;
}
