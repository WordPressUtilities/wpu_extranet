<?php

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
        $errors[] = __('All fields are required', 'wpu_extranet');
    }

    /* Invalid password */
    if (!wp_check_password($current_password, $current_user->data->user_pass, $current_user->ID)) {
        $errors[] = __('Current password is incorrect', 'wpu_extranet');
    }

    /* New passwords do not match */
    if ($new_password != $confirm_new_password) {
        $errors[] = __('Passwords do not match', 'wpu_extranet');
    }

    /* Short password */
    if (strlen($new_password) < 6) {
        $errors[] = __('Password is too short, minimum of 6 characters', 'wpu_extranet');
    }

    $html_return = '';
    if (empty($errors)) {
        wp_set_password($new_password, $current_user->ID);
        $html_return .= '<p class="form-password-success">' . __('Password successfully changed!', 'wpu_extranet') . '</p>';
    } else {
        $html_return .= '<ul class="form-password-error">';
        foreach ($errors as $error) {
            $html_return .= '<li><strong>Error: </strong>' . $error . '</li>';
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

    $html .= '<h3>' . __('Change password', 'wpu_extranet') . '</h3>';
    $html .= '<form action="' . get_permalink() . '" method="post">';
    $html .= $args['before_fields'];
    $html .= '<ul class="cssc-form">';
    $html .= '<li class="box">';
    $html .= '<label for="current_password">' . __('Enter your current password :', 'wpu_extranet') . '</label>';
    $html .= '<input minlength="6" id="current_password" type="password" name="current_password" title="current_password" placeholder="" required/>';
    $html .= '</li>';
    $html .= '<li class="box">';
    $html .= '<label for="new_password">' . __('New password :', 'wpu_extranet') . '</label>';
    $html .= '<input minlength="6" id="new_password" type="password" name="new_password" title="new_password" placeholder="" required/>';
    $html .= '</li>';
    $html .= '<li class="box">';
    $html .= '<label for="confirm_new_password">' . __('Confirm new password :', 'wpu_extranet') . '</label>';
    $html .= '<input minlength="6" id="confirm_new_password" type="password" name="confirm_new_password" title="confirm_new_password" placeholder="" required/>';
    $html .= '</li>';
    $html .= '<li class="box box-submit">';
    $html .= '<button class="wpu_extranet-button" type="submit"><span>' . __('Change password', 'wpu_extranet') . '</span></button>';
    $html .= '</li>';
    $html .= '</ul>';
    $html .= '</form>';

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
