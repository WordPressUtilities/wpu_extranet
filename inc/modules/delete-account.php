<?php
defined('ABSPATH') || die;

/* ----------------------------------------------------------
  Delete account
---------------------------------------------------------- */

/* Test
-------------------------- */

function wpu_extranet_user_can_delete_account($errors, $user_id) {
    if (current_user_can('edit_users', $user_id)) {
        $errors[] = __('You can not delete this type of account.', 'wpu_extranet');
    }

    $errors = apply_filters('wpu_extranet_user_can_delete_account', $errors, $user_id);

    return $errors;
}

/* Form action
-------------------------- */

function wpu_extranet_delete_account__action() {
    if (empty($_POST)) {
        return '';
    }
    if (!is_user_logged_in()) {
        return '';
    }
    if (!isset($_POST['current_password'])) {
        return '';
    }

    if (!isset($_POST['wpuextranet_delete_account']) || !wp_verify_nonce($_POST['wpuextranet_delete_account'], 'wpuextranet_delete_account_action')) {
        return '';
    }

    $password = trim($_POST['current_password']);

    $user_id = get_current_user_id();
    $current_user = get_user_by('id', $user_id);
    if (!$current_user) {
        return;
    }

    /* Check errors */
    $errors = array();

    /* Empty fields */
    if (empty($password)) {
        $errors[] = __('All fields are required.', 'wpu_extranet');
    }

    /* Invalid password */
    if (!wp_check_password($password, $current_user->data->user_pass, $current_user->ID)) {
        $errors[] = __('Invalid password.', 'wpu_extranet');
    }

    /* User can edit other users : prevent deletion */
    $errors = wpu_extranet_user_can_delete_account($errors, $user_id);

    if (empty($errors)) {
        /* Delete user and redirect to home */
        require_once ABSPATH . 'wp-admin/includes/user.php';
        wp_delete_user($user_id);
        wp_redirect(home_url() . '#');
        die;
    }

    /* Display errors */
    return wpuextranet_get_html_errors($errors, array(
        'form_id' => 'delete_account',
        'type' => 'error'
    ));

}

/* HTML Form
-------------------------- */

function wpu_extranet_delete_account__form($args = array()) {
    if (!is_array($args)) {
        $args = array();
    }
    if (!isset($args['before_fields'])) {
        $args['before_fields'] = '';
    }
    $html = '';

    $user_id = get_current_user_id();
    $userdata = get_userdata($user_id);

    $errors = wpu_extranet_user_can_delete_account(array(), $user_id);
    $user_can_delete_account = empty($errors);
    if (!empty($errors)) {
        $args['before_fields'] .= wpuextranet_get_html_errors($errors, array(
            'form_id' => 'delete_account',
            'type' => 'error'
        ));
    }

    $settings = wpu_extranet_get_skin_settings();
    $html .= '<div class="' . $settings['form_wrapper_classname'] . ' form-delete_account-wrapper">';
    $html .= '<h3>' . __('Delete your account', 'wpu_extranet') . '</h3>';
    $html .= '<form name="delete_accountform" id="delete_accountform" action="' . get_permalink() . '#delete_accountform" method="post">';
    $html .= $args['before_fields'];
    if ($user_can_delete_account) {
        $html .= '<ul class="' . $settings['form_items_classname'] . '">';
        $html .= wpu_extranet__display_field('current_password', array(
            'type' => 'password',
            'attributes' => 'minlength="6" autocomplete="off" required="required"',
            'label' => __('Enter your current password', 'wpu_extranet')
        ));
        $html .= '<li class="' . $settings['form_box_submit_classname'] . '">';
        $html .= wp_nonce_field('wpuextranet_delete_account_action', 'wpuextranet_delete_account', true, false);
        $html .= '<button class="' . $settings['form_submit_button_classname'] . '" type="submit"><span>' . __('Delete your account', 'wpu_extranet') . '</span></button>';
    }
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
$html_return_delete_account = wpu_extranet_delete_account__action();
get_header();
echo '<h1>' . get_the_title() . '</h1>';
echo wpu_extranet_delete_account__form(array(
    'before_fields' => $html_return_delete_account
));
get_footer();
*/
