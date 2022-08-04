<?php

/* ----------------------------------------------------------
  Metas
---------------------------------------------------------- */

add_action('user_register', function ($user_id) {
    $fields = wpu_extranet__user_register_fields();
    foreach ($fields as $id => $field) {
        if (!empty($_POST[$id])) {
            update_user_meta($user_id, $id, trim(sanitize_text_field($_POST[$id])));
        }
    }
});

/* Form action
-------------------------- */

function wpu_extranet_update_metas__action() {
    if (empty($_POST)) {
        return '';
    }
    if (!is_user_logged_in()) {
        return '';
    }

    if (!isset($_POST['wpuextranet_editmetas']) || !wp_verify_nonce($_POST['wpuextranet_editmetas'], 'wpuextranet_editmetas_action')) {
        return '';
    }

    $user_id = get_current_user_id();

    $fields = wpu_extranet__user_register_fields();

    /* Check errors */
    $errors = array();
    $has_update = false;
    foreach ($fields as $id => $field) {
        if (isset($_POST[$id])) {
            $has_update = true;
            update_user_meta($user_id, $id, trim(sanitize_text_field($_POST[$id])));
        }
    }

    $upload_avatar = wpu_extranet_update_metas__action__avatar($user_id);

    $html_return = '';
    if ($has_update || $upload_avatar) {
        $html_return = '<p class="extranet-message extranet-message--success form-password-success">' . __('Profile successfully updated!', 'wpu_extranet') . '</p>';
    }

    return $html_return;
}

function wpu_extranet_update_metas__action__avatar($user_id) {
    $avatar_id = get_user_meta($user_id, 'wpuextranet_avatar_id', true);
    if (isset($_POST['delete_avatar'])) {
        if (is_numeric($avatar_id)) {
            /* Delete meta */
            delete_user_meta($user_id, 'wpuextranet_avatar_id');
            /* Delete avatar image */
            wp_delete_attachment($avatar_id, true);
        }
        return true;
    }

    /* Check attachment types */
    $allowed_extensions = array("image/png", "image/jpg", "image/jpeg");
    if (!in_array(mime_content_type($_FILES["wpuextranet_avatar"]['tmp_name']), $allowed_extensions)) {
        return false;
    }

    // These files need to be included as dependencies when on the front end.
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';

    $attachment_id = media_handle_upload('wpuextranet_avatar', 0);
    if (!is_wp_error($attachment_id)) {
        /* Delete old avatar image */
        if ($avatar_id) {
            wp_delete_attachment($avatar_id, true);
        }
        /* Change meta */
        update_user_meta($user_id, 'wpuextranet_avatar_id', $attachment_id);
        return true;
    }

    return false;

}

/* Form HTML
-------------------------- */

function wpu_extranet_update_metas__form($args = array()) {
    if (!is_array($args)) {
        $args = array();
    }
    if (!isset($args['before_fields'])) {
        $args['before_fields'] = '';
    }

    $extra_fields = wpu_extranet__user_register_fields();
    $settings = wpu_extranet_get_skin_settings();

    $user = wp_get_current_user();

    $html = '';
    $html .= '<div class="' . $settings['form_wrapper_classname'] . ' form-editmetas-wrapper">';
    $html .= '<h3>' . __('Infos', 'wpu_extranet') . '</h3>';
    $html .= '<form name="editmetasform" id="editmetasform" enctype="multipart/form-data" action="' . get_permalink() . '" method="post">';
    $html .= '<ul class="' . $settings['form_items_classname'] . '">';
    $html .= $args['before_fields'];

    /* Avatar */
    $avatar_img = '<img src="' . esc_url(get_avatar_url(get_current_user_id())) . '" alt="" />';
    $avatar_message = sprintf(__('The current avatar is generated by %s.', 'wpu_extranet'), '<a target="_blank" href="https://gravatar.com/" rel="noopener">Gravatar</a>');
    $avatar_id = get_user_meta(get_current_user_id(), 'wpuextranet_avatar_id', true);
    if ($avatar_id) {
        $avatar_message = '<input type="checkbox" id="wpuextranet_delete_avatar" name="delete_avatar" value="1" /><label for="wpuextranet_delete_avatar">' . __('Delete this avatar', 'wpu_extranet') . '</label>';
    }
    $html .= wpu_extranet__display_field('wpuextranet_avatar', array(
        'label' => __('Avatar', 'wpu_extranet'),
        'before_content' => '<div class="avatar-grid"><div>' . $avatar_img . '</div><div>',
        'after_content' => '<small>' . $avatar_message . '</small></div></div>',
        'attributes' => 'accept="image/png, image/jpg, image/jpeg"',
        'type' => 'file',
        'value' => $user->user_email
    ));

    /* Default fields */
    $html .= wpu_extranet__display_field('username', array(
        'label' => __('Username', 'wpu_extranet'),
        'attributes' => 'readonly',
        'value' => $user->display_name
    ));
    $html .= wpu_extranet__display_field('email', array(
        'label' => __('Email', 'wpu_extranet'),
        'attributes' => 'readonly',
        'value' => $user->user_email
    ));

    /* Custom fields */
    foreach ($extra_fields as $field_id => $field):
        if (!$field['in_editmetas_form']) {
            continue;
        }
        $field['value'] = get_user_meta(get_current_user_id(), $field_id, 1);
        $html .= wpu_extranet__display_field($field_id, $field);
    endforeach;

    $html .= '<li class="' . $settings['form_box_submit_classname'] . '">';
    $html .= wp_nonce_field('wpuextranet_editmetas_action', 'wpuextranet_editmetas', true, false);
    $html .= '<button class="' . $settings['form_submit_button_classname'] . '" type="submit"><span>' . __('Edit my infos', 'wpu_extranet') . '</span></button>';
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
$html_return_metas = wpu_extranet_update_metas__action();
get_header();
echo '<h1>' . get_the_title() . '</h1>';
echo wpu_extranet_update_metas__form(array(
    'before_fields' => $html_return_metas
));
get_footer();
*/
