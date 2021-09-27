<?php

/* ----------------------------------------------------------
  Registration
---------------------------------------------------------- */

add_filter('registration_errors', function ($errors, $login, $email) {
    if (!is_wp_error($errors) || empty($errors->errors) || empty($_POST) || !isset($_POST['wpu_extranet']) || $_POST['wpu_extranet'] != 'register') {
        return $errors;
    }
    $error = 0;
    if (isset($errors->errors['username_exists'])) {
        $error = 1;
    }
    if (isset($errors->errors['email_exists'])) {
        $error = 2;
    }
    if (isset($errors->errors['invalid_username'])) {
        $error = 3;
    }
    wp_redirect(add_query_arg('registererror', $error, wpu_extranet__get_register_page()));
    die;
}, 10, 3);

/* Re-enable registration
-------------------------- */

add_action('plugins_loaded', function () {
    if (apply_filters('wpu_extranet__user_register_enabled', true)) {
        remove_filter('pre_option_users_can_register', 'wputh_admin_option_users_can_register');
    }
});

/* Form action
-------------------------- */

function wpu_extranet_register__action() {
    if (!get_option('users_can_register')) {
        wp_redirect(home_url());
        die;
    }
    if (is_user_logged_in()) {
        wp_redirect(wpu_extranet__get_dashboard_page());
        die;
    }

    $html_return = '';
    if (isset($_GET['register']) && $_GET['register'] == 'success') {
        $html_return .= '<p class="form-register-success">' . __('Registration confirmation will be emailed to you.', 'wpu_extranet') . '</p>';
    }

    if (isset($_GET['registererror'])) {
        $html_return .= '<p class="form-register-error">';
        switch ($_GET['registererror']) {
        case '1':
            $html_return .= __('This username already exists.', 'wpu_extranet');
            break;
        case '2':
            $html_return .= __('This email is already registered.', 'wpu_extranet');
            break;
        case '3':
            $html_return .= __('This username contains invalid characters.', 'wpu_extranet');
            break;
        default:
            $html_return .= __('Registration failed.', 'wpu_extranet');
        }
        $html_return .= '</p>';
    }

    return $html_return;
}

/* Form HTML
-------------------------- */

function wpu_extranet_register__form($args = array()) {
    if (!is_array($args)) {
        $args = array();
    }
    if (!isset($args['before_fields'])) {
        $args['before_fields'] = '';
    }

    $extra_fields = wpu_extranet__user_register_fields();

    $settings = wpu_extranet_get_skin_settings();

    $html = '';
    $html .= '<div class="' . $settings['form_wrapper_classname'] . ' form-register-wrapper">';
    $html .= '<form name="registerform" id="registerform" action="' . esc_url(site_url('wp-login.php?action=register', 'login_post')) . '" method="post" novalidate="novalidate">';
    $html .= $args['before_fields'];
    $html .= '<ul class="' . $settings['form_items_classname'] . '">';
    foreach ($extra_fields as $field_id => $field):
        $html .= '<li class="' . $settings['form_box_classname'] . '">';
        $html .= '<label for="' . $field_id . '">' . $field['label'] . '</label>';
        $html .= '<input type="text" name="' . $field_id . '" id="' . $field_id . '" class="input" value="" size="20" autocapitalize="off" />';
        $html .= '</li>';
    endforeach;
    $html .= '<li class="' . $settings['form_box_classname'] . '">';
    $html .= '<label for="user_login">' . __('Username', 'wpu_extranet') . '</label>';
    $html .= '<input type="text" name="user_login" id="user_login" class="input" value="" size="20" autocapitalize="off" />';
    $html .= '</li>';
    $html .= '<li class="' . $settings['form_box_classname'] . '">';
    $html .= '<label for="user_email">' . __('Email', 'wpu_extranet') . '</label>';
    $html .= '<input type="email" name="user_email" id="user_email" class="input" value="" size="25" />';
    $html .= '</li>';
    do_action('register_form');
    $html .= '<li class=""' . $settings['form_box_submit_classname'] . '">';
    $html .= '<input type="hidden" name="wpu_extranet" value="register" />';
    $html .= '<input type="hidden" name="redirect_to" value="' . esc_attr(add_query_arg('register', 'success', get_permalink())) . '" />';
    $html .= '<button class="' . $settings['form_submit_button_classname'] . '" type="submit" name="wp-submit" id="wp-submit"><span>' . __('Register', 'wpu_extranet') . '</span></button>';
    $html .= '</li>';
    $html .= '</ul>';
    $html .= '</form>';
    $html .= '</div>';
    return $html;
}

/* ----------------------------------------------------------
  Example code
---------------------------------------------------------- */

/*
$html_return_register = wpu_extranet_register__action();
get_header();
echo '<h1>' . get_the_title() . '</h1>';
echo wpu_extranet_register__form(array(
    'before_fields' => $html_return_register
));
get_footer();
*/
