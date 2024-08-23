<?php
defined('ABSPATH') || die;

/* ----------------------------------------------------------
  User fields
---------------------------------------------------------- */

function wpu_extranet__user_register_fields() {
    $fields_raw = apply_filters('wpu_extranet__user_register_fields', array(
        'first_name' => array(
            'label' => __('First name', 'wpu_extranet')
        ),
        'last_name' => array(
            'label' => __('Last name', 'wpu_extranet')
        )
    ));
    $fields_wpu = apply_filters('wpu_usermetas_fields', array());
    foreach ($fields_wpu as $key => $field) {
        if (!isset($field['wpuextranet_front']) || !$field['wpuextranet_front']) {
            continue;
        }
        if (!isset($field['label']) && isset($field['name'])) {
            $field['label'] = $field['name'];
        }
        $fields_raw[$key] = $field;
    }
    $fields = array();
    foreach ($fields_raw as $key => $field) {
        $field = wpu_extranet__correct_field($field, $key);
        if (!isset($field['in_registration_form'])) {
            $field['in_registration_form'] = true;
        }

        if (!isset($field['in_editmetas_form'])) {
            $field['in_editmetas_form'] = true;
        }

        $fields[$key] = $field;
    }
    return $fields;
}
/* ----------------------------------------------------------
  Log user by id
---------------------------------------------------------- */

function wpu_extranet_log_user($user) {
    if (is_numeric($user)) {
        $user = get_user_by('id', $user);
    }
    wp_set_auth_cookie($user->ID);
    wp_set_current_user($user->ID);
    do_action('wp_login', $user->user_login, $user);
}

/* ----------------------------------------------------------
  Custom avatar
---------------------------------------------------------- */

/* Based on https://developer.wordpress.org/reference/hooks/get_avatar/#comment-4570 */
add_filter('get_avatar_url', function ($avatar_url, $id_or_email, $args) {
    $user = false;

    if (is_numeric($id_or_email)) {
        $id = (int) $id_or_email;
        $user = get_user_by('id', $id);
    } elseif (is_object($id_or_email)) {
        if (!empty($id_or_email->user_id)) {
            $id = (int) $id_or_email->user_id;
            $user = get_user_by('id', $id);
        }
    } else {
        $user = get_user_by('email', $id_or_email);
    }

    if ($user && is_object($user)) {
        $avatar_id = get_user_meta($user->data->ID, 'wpuextranet_avatar_id', true);
        if ($avatar_id) {
            return wp_get_attachment_image_url($avatar_id, 'thumbnail');
        } else {
            $wpu_extranet_default_avatar = get_option('wpu_extranet_default_avatar');
            if ($wpu_extranet_default_avatar) {
                return add_query_arg('d', wp_get_attachment_image_url($wpu_extranet_default_avatar, 'thumbnail'), $avatar_url);;
            }
        }
    }

    return $avatar_url;
}, 1, 3);
