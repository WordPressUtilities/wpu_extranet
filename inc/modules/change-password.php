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

    if (!isset($_POST['wpuextranet_changepasswordform']) || !wp_verify_nonce($_POST['wpuextranet_changepasswordform'], 'wpuextranet_changepasswordform_action')) {
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

    $return_type = 'error';
    if (empty($errors)) {
        // Change password
        wp_set_password($new_password, $current_user->ID);
        // Log-in again.
        wpu_extranet_log_user($current_user);
        $return_type = 'success';
        $errors[] = __('Password successfully changed!', 'wpu_extranet');
    }

    return wpuextranet_get_html_errors($errors, array(
        'form_id' => 'change_password',
        'type' => $return_type
    ));
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

    $fields = array();
    $fields['current_password'] = array(
        'type' => 'password',
        'attributes' => 'minlength="6" autocomplete="off" required="required"',
        'label' => __('Enter your current password', 'wpu_extranet')
    );
    $fields['new_password'] = array(
        'type' => 'password',
        'attributes' => 'minlength="6" autocomplete="off" required="required"',
        'label' => __('New password', 'wpu_extranet')
    );
    $fields['confirm_new_password'] = array(
        'type' => 'password',
        'attributes' => 'minlength="6" autocomplete="off" required="required"',
        'label' => __('Confirm new password', 'wpu_extranet')
    );

    return wpu_extranet_get_form_html('changepasswordform', $fields, array(
        'before_fields' => $args['before_fields'],
        'form_action' => get_permalink() . '#changepasswordform',
        'form_title' => __('Change password', 'wpu_extranet'),
        'form_submit' => __('Change password', 'wpu_extranet')
    ));
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
