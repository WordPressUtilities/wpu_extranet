<?php
defined('ABSPATH') || die;

/* ----------------------------------------------------------
  Password
---------------------------------------------------------- */

/* Form action
-------------------------- */

function wpu_extranet_change_password__action() {
    if (empty($_POST)) {
        return '';
    }
    if (!is_user_logged_in()) {
        return '';
    }
    if (!isset($_POST['current_password'], $_POST['new_password'], $_POST['confirm_new_password'])) {
        return '';
    }

    if (!isset($_POST['wpuextranet_changepassword']) || !wp_verify_nonce($_POST['wpuextranet_changepassword'], 'wpuextranet_changepassword_action')) {
        return '';
    }

    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_new_password = trim($_POST['confirm_new_password']);

    $user_id = get_current_user_id();
    $current_user = get_user_by('id', $user_id);
    if (!$current_user) {
        return;
    }

    /* Check errors */
    $errors = array();

    /* Empty fields */
    if (empty($current_password) || empty($new_password) || empty($confirm_new_password)) {
        $errors[] = __('All fields are required.', 'wpu_extranet');
    }

    /* Invalid password */
    if (!wp_check_password($current_password, $current_user->data->user_pass, $current_user->ID)) {
        $errors[] = __('Current password is incorrect.', 'wpu_extranet');
    }

    /* New passwords do not match */
    if ($new_password != $confirm_new_password) {
        $errors[] = __('Passwords do not match.', 'wpu_extranet');
    }

    /* Short password */
    if (strlen($new_password) < 6) {
        $errors[] = __('Password is too short, minimum of 6 characters.', 'wpu_extranet');
    }

    $html_return = '';
    if (empty($errors)) {
        // Change password
        wp_set_password($new_password, $current_user->ID);
        // Log-in again.
        wpu_extranet_log_user($current_user);

        $html_return .= '<p class="extranet-message extranet-message--success form-password-success">' . __('Password successfully changed!', 'wpu_extranet') . '</p>';
    } else {
        $html_return .= '<ul class="extranet-message extranet-message--error form-password-error">';
        foreach ($errors as $error) {
            $html_return .= '<li><strong class="error">' . __('Error:', 'wpu_extranet') . '</strong> ' . $error . '</li>';
        }
        $html_return .= '</ul>';
    }

    return $html_return;
}

/* HTML Form
-------------------------- */

function wpu_extranet_change_password__form($args = array()) {
    if (!is_array($args)) {
        $args = array();
    }
    if (!isset($args['before_fields'])) {
        $args['before_fields'] = '';
    }
    $html = '';

    $settings = wpu_extranet_get_skin_settings();

    $html .= '<div class="' . $settings['form_wrapper_classname'] . ' form-changepassword-wrapper">';
    $html .= '<h3>' . __('Change password', 'wpu_extranet') . '</h3>';
    $html .= '<form name="changepasswordform" id="changepasswordform" action="' . get_permalink() . '#changepasswordform" method="post">';
    $html .= $args['before_fields'];
    $html .= '<ul class="' . $settings['form_items_classname'] . '">';
    $html .= wpu_extranet__display_field('current_password', array(
        'type' => 'password',
        'attributes' => 'minlength="6" autocomplete="off" required="required"',
        'label' => __('Enter your current password', 'wpu_extranet')
    ));
    $html .= wpu_extranet__display_field('new_password', array(
        'type' => 'password',
        'attributes' => 'minlength="6" autocomplete="off" required="required"',
        'label' => __('New password', 'wpu_extranet')
    ));
    $html .= wpu_extranet__display_field('confirm_new_password', array(
        'type' => 'password',
        'attributes' => 'minlength="6" autocomplete="off" required="required"',
        'label' => __('Confirm new password', 'wpu_extranet')
    ));
    $html .= '<li class="' . $settings['form_box_submit_classname'] . '">';
    $html .= wp_nonce_field('wpuextranet_changepassword_action', 'wpuextranet_changepassword', true, false);
    $html .= '<button class="' . $settings['form_submit_button_classname'] . '" type="submit"><span>' . __('Change password', 'wpu_extranet') . '</span></button>';
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
$html_return_password = wpu_extranet_change_password__action();
get_header();
echo '<h1>' . get_the_title() . '</h1>';
echo wpu_extranet_change_password__form(array(
    'before_fields' => $html_return_password
));
get_footer();
*/
