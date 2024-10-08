<?php
defined('ABSPATH') || die;

/* ----------------------------------------------------------
  Email
---------------------------------------------------------- */

/* Form action
-------------------------- */

function wpu_extranet_change_email__action() {
    if (empty($_POST)) {
        return '';
    }
    if (!is_user_logged_in()) {
        return '';
    }
    if (!isset($_POST['current_email'], $_POST['new_email'], $_POST['confirm_new_email'])) {
        return '';
    }

    if (!isset($_POST['wpuextranet_changeemailform']) || !wp_verify_nonce($_POST['wpuextranet_changeemailform'], 'wpuextranet_changeemailform_action')) {
        return '';
    }

    $current_email = trim($_POST['current_email']);
    $new_email = trim($_POST['new_email']);
    $confirm_new_email = trim($_POST['confirm_new_email']);

    $user_id = get_current_user_id();
    $current_user = get_user_by('id', $user_id);
    if (!$current_user) {
        return;
    }

    /* Check errors */
    $errors = array();

    /* Empty fields */
    if (empty($current_email) || empty($new_email) || empty($confirm_new_email)) {
        $errors[] = __('All fields are required.', 'wpu_extranet');
    }

    /* Invalid email */
    if (!is_email($current_email) || !is_email($new_email) || !is_email($confirm_new_email)) {
        $errors[] = __('Invalid email.', 'wpu_extranet');
    }

    /* Invalid current email */
    if ($current_email == $new_email) {
        $errors[] = __('New email is the same as the current email.', 'wpu_extranet');
    }

    /* New emails do not match */
    if ($new_email != $confirm_new_email) {
        $errors[] = __('Emails do not match.', 'wpu_extranet');
    }

    /* E-mail is already in use */
    if (email_exists($new_email)) {
        $errors[] = __('Email is already in use.', 'wpu_extranet');
    }

    $errors = apply_filters('wpu_extranet__email__validation', $errors, $new_email);
    $errors = apply_filters('wpu_extranet_change_email__action_errors', $errors, $current_email, $new_email, $confirm_new_email);

    $return_type = 'error';
    if (empty($errors)) {
        // Change email
        wp_update_user(array(
            'ID' => $user_id,
            'user_email' => $new_email
        ));
        $return_type = 'success';
        $errors[] = __('Email successfully updated!', 'wpu_extranet');
    }

    return wpuextranet_get_html_errors($errors, array(
        'form_id' => 'change_email',
        'type' => $return_type
    ));
}

/* HTML Form
-------------------------- */

function wpu_extranet_change_email__form($args = array()) {
    if (!is_array($args)) {
        $args = array();
    }
    if (!isset($args['before_fields'])) {
        $args['before_fields'] = '';
    }

    $fields = array();
    $userdata = get_userdata(get_current_user_id());
    $fields['current_email'] = array(
        'type' => 'email',
        'attributes' => 'readonly minlength="6" autocomplete="off" required="required"',
        'label' => __('Your current email', 'wpu_extranet'),
        'value' => $userdata->user_email
    );
    $fields['new_email'] = array(
        'type' => 'email',
        'attributes' => 'minlength="6" autocomplete="off" required="required"',
        'label' => __('New email', 'wpu_extranet')
    );
    $fields['confirm_new_email'] = array(
        'type' => 'email',
        'attributes' => 'minlength="6" autocomplete="off" required="required"',
        'label' => __('Confirm new email', 'wpu_extranet')
    );

    return wpu_extranet_get_form_html('changeemailform', $fields, array(
        'before_fields' => $args['before_fields'],
        'form_action' => get_permalink() . '#changeemailform',
        'form_title' => __('Change email', 'wpu_extranet'),
        'form_submit' => __('Change email', 'wpu_extranet')
    ));

    return $html;
}

/* ----------------------------------------------------------
  Example code
---------------------------------------------------------- */

/*
$html_return_email = wpu_extranet_change_email__action();
get_header();
echo '<h1>' . get_the_title() . '</h1>';
echo wpu_extranet_change_email__form(array(
    'before_fields' => $html_return_email
));
get_footer();
*/
