<?php
defined('ABSPATH') || die;

/*
Plugin Name: WPU Extranet
Description: Simple toolbox to create an extranet or a customer account
Version: 0.16.0
Author: Darklg
Author URI: https://darklg.me/
Text Domain: wpu_extranet
Domain Path: /lang
Requires at least: 6.2
Requires PHP: 8.0
Network: Optional
License: MIT License
License URI: https://opensource.org/licenses/MIT
Plugin URI: https://github.com/WordPressUtilities/wpu_extranet
Update URI: https://github.com/WordPressUtilities/wpu_extranet
*/

define('WPU_EXTRANET_VERSION', '0.16.0');

/* ----------------------------------------------------------
  Settings
---------------------------------------------------------- */

require_once __DIR__ . '/inc/_settings.php';
require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/notifications.php';
require_once __DIR__ . '/inc/pages.php';
require_once __DIR__ . '/inc/permissions.php';
require_once __DIR__ . '/inc/user.php';
require_once __DIR__ . '/inc/form.php';

/* ----------------------------------------------------------
  Modules
---------------------------------------------------------- */

require_once __DIR__ . '/inc/modules/change-email.php';
require_once __DIR__ . '/inc/modules/change-password.php';
require_once __DIR__ . '/inc/modules/delete-account.php';
require_once __DIR__ . '/inc/modules/edit-metas.php';
require_once __DIR__ . '/inc/modules/lost-password.php';
require_once __DIR__ . '/inc/modules/register.php';

/* ----------------------------------------------------------
  Translation
---------------------------------------------------------- */

add_action('plugins_loaded', 'wpu_extranet_plugins_loaded', 10);
function wpu_extranet_plugins_loaded() {
    $lang_dir = dirname(plugin_basename(__FILE__)) . '/lang/';
    if (!load_plugin_textdomain('wpu_extranet', false, $lang_dir)) {
        load_muplugin_textdomain('wpu_extranet', $lang_dir);
    }
    $plugin_description = __('Simple toolbox to create an extranet or a customer account', 'wpuactionlogs');
}

/* ----------------------------------------------------------
  Skin
---------------------------------------------------------- */

function wpu_extranet_get_skin_settings() {
    $base_settings = array(
        'form_wrapper_classname' => 'form-extranet',
        'form_items_classname' => 'cssc-form cssc-form--extranet',
        'form_box_classname' => 'box box--extranet',
        'form_grid_classname' => 'grid-extranet',
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
