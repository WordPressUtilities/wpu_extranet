<?php

/* ----------------------------------------------------------
  Honeypot
---------------------------------------------------------- */

function wpu_extranet_register_get_honeypot_id() {
    return 'check_' . md5(AUTH_SALT . get_bloginfo('name'));
}

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
    /* Not submitting or displaying an error message */
    if (!isset($_POST['wpuextranet_register']) && !isset($_GET['register']) && !isset($_GET['registererror'])) {
        return '';
    }

    /* Invalid nonce */
    if (isset($_POST['wpuextranet_register']) && !wp_verify_nonce($_POST['wpuextranet_register'], 'wpuextranet_register_action')) {
        return '';
    }

    /* Checked honeypot */
    $honeypot_id = wpu_extranet_register_get_honeypot_id();
    if (isset($_POST[$honeypot_id]) && $_POST[$honeypot_id] == 1) {
        return '';
    }

    $register_success_user_id = false;
    if (!empty($_POST) && isset($_POST['user_login'], $_POST['user_email'], $_POST['user_password'])) {
        $user_login = sanitize_text_field($_POST['user_login']);
        $user_email = sanitize_email($_POST['user_email']);
        $user_id = register_new_user($user_login, $user_email);

        // Registration was a success
        // Log user and redirect to the dashboard
        if (is_numeric($user_id)) {
            wp_set_password($_POST['user_password'], $user_id);
            wpu_extranet_log_user($user_id);
            wp_redirect(add_query_arg('registersuccess', '1', wpu_extranet__get_dashboard_page()));
            die;
        }
    }

    $html_return = '';
    if (isset($_GET['register']) && $_GET['register'] == 'success') {
        $html_return .= '<p class="extranet-message extranet-message--success form-register-success">' . __('Registration confirmation will be emailed to you.', 'wpu_extranet') . '</p>';
    }

    if (isset($_GET['registererror'])) {
        $html_return .= '<p class="extranet-message extranet-message--error form-register-error"><strong class="error">' . __('Error:', 'wpu_extranet') . '</strong> ';
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
    $html .= '<form name="registerform" id="registerform" action="' . esc_url(get_permalink()) . '" method="post">';
    $html .= $args['before_fields'];
    $html .= '<ul class="' . $settings['form_items_classname'] . '">';
    foreach ($extra_fields as $field_id => $field):
        if (!$field['in_registration_form']) {
            continue;
        }

        $html .= wpu_extranet__display_field($field_id, $field);
    endforeach;
    $html .= wpu_extranet__display_field('user_login', array(
        'label' => __('Username', 'wpu_extranet'),
        'attributes' => 'autocomplete="off" pattern="[A-Za-z0-9_]+" required="required"'
    ));
    $html .= wpu_extranet__display_field('user_email', array(
        'type' => 'email',
        'attributes' => 'required="required"',
        'label' => __('Email', 'wpu_extranet')
    ));
    $html .= wpu_extranet__display_field('user_password', array(
        'type' => 'password',
        'attributes' => 'minlength="6" autocomplete="off" required="required"',
        'label' => __('Password', 'wpu_extranet')
    ));
    do_action('register_form');
    $honeypot_id = wpu_extranet_register_get_honeypot_id();
    $html .= '<li class="' . $settings['form_box_submit_classname'] . '">';
    $html .= '<label for="' . $honeypot_id . '" aria-hidden="true" class="visually-hidden"><input type="radio" name="' . $honeypot_id . '" id="' . $honeypot_id . '" style="display:none" value="1"></label>';
    $html .= '<input type="hidden" name="wpu_extranet" value="register" />';
    $html .= '<input type="hidden" name="redirect_to" value="' . esc_attr(add_query_arg('register', 'success', get_permalink())) . '" />';
    $html .= wp_nonce_field('wpuextranet_register_action', 'wpuextranet_register', true, false);
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
