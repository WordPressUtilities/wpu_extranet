<?php
defined('ABSPATH') || die;

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

    $fields = wpu_extranet__user_register_fields();
    $fields = apply_filters('wpu_extranet_update_metas__form_fields', $fields);

    return wpu_extranet__save_fields($fields, array(
        'form_id' => 'editmetas',
        'callback_after_fields' => function ($errors) {
            wpu_extranet_update_metas__action__avatar(get_current_user_id());
            return $errors;
        }
    ));
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

    if (!isset($_FILES["wpuextranet_avatar"]) || empty($_FILES["wpuextranet_avatar"]['tmp_name'])) {
        return false;
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

    $fields = array();
    $fields['wpuextranet_avatar'] = wpu_extranet_get_avatar_field();

    /* Default fields */
    $fields['username'] = array(
        'label' => __('Username', 'wpu_extranet'),
        'attributes' => 'readonly',
        'value' => $user->display_name
    );

    $fields['email'] = array(
        'label' => __('Email', 'wpu_extranet'),
        'attributes' => 'readonly',
        'value' => $user->user_email
    );

    /* Custom fields */
    foreach ($extra_fields as $field_id => $field):
        if (!$field['in_editmetas_form']) {
            continue;
        }
        $field['value'] = get_user_meta(get_current_user_id(), $field_id, 1);
        $fields[$field_id] = $field;
    endforeach;

    $fields = apply_filters('wpu_extranet_update_metas__form_fields', $fields);

    return wpu_extranet_get_form_html('editmetas', $fields, array(
        'before_fields' => $args['before_fields'],
        'after_fields' => '',
        'form_action' => get_permalink(),
        'form_submit' => __('Edit my infos', 'wpu_extranet'),
        'form_title' => __('Infos', 'wpu_extranet')
    ));
}

function wpu_extranet_get_avatar_field() {

/* Avatar */
    $avatar_script = <<<EOT
<script>
document.addEventListener("DOMContentLoaded", function() {
    var _img = document.getElementById('wpu-extranet-avatar-image');
    var _msg = document.getElementById('wpu-extranet-avatar-message');

    /* Default status */
    _img.setAttribute('data-src', _img.src);
    _msg.setAttribute('data-display', _msg.style.display);

    /* Event change */
    document.querySelector('[name="wpuextranet_avatar"]').addEventListener('change', function(e) {
        if (!this.files || !this.files[0]) {
            _img.setAttribute('src', _img.getAttribute('data-src'));
            _msg.style.display = _msg.getAttribute('data-display');
            return;
        }
        var reader = new FileReader();
        reader.onload = function(e) {
            _img.setAttribute('src', e.target.result);
            _msg.style.display = 'none';
        };
        reader.readAsDataURL(this.files[0]);
    }, false);
});
</script>
EOT;
    $avatar_img = '<img id="wpu-extranet-avatar-image" src="' . esc_url(get_avatar_url(get_current_user_id())) . '" alt="" />';
    $avatar_message = sprintf(__('The current avatar is generated by %s.', 'wpu_extranet'), '<a target="_blank" href="https://gravatar.com/" rel="noopener">Gravatar</a>');
    $avatar_id = get_user_meta(get_current_user_id(), 'wpuextranet_avatar_id', true);
    if ($avatar_id) {
        $avatar_message = '<input type="checkbox" id="wpuextranet_delete_avatar" name="delete_avatar" value="1" /><label for="wpuextranet_delete_avatar">' . __('Delete this avatar', 'wpu_extranet') . '</label>';
    }
    return array(
        'label' => __('Avatar', 'wpu_extranet'),
        'before_content' => $avatar_script . '<div class="avatar-grid"><div>' . $avatar_img . '</div><div>',
        'after_content' => '<small id="wpu-extranet-avatar-message">' . $avatar_message . '</small></div></div>',
        'attributes' => 'accept="image/png, image/jpg, image/jpeg"',
        'type' => 'file',
        'value' => 0
    );
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
