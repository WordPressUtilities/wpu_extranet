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
        if (!isset($field['type'])) {
            $field['type'] = 'text';
        }

        if (!isset($field['attributes'])) {
            $field['attributes'] = '';
        }

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
  Display field
---------------------------------------------------------- */

function wpu_extranet__display_field($field_id, $field) {
    $html = '';
    $defaults = array(
        'label' => $field_id,
        'required' => false,
        'readonly' => false,
        'value' => '',
        'options' => array(),
        'attributes' => '',
        'type' => 'text',
        'before_content' => '',
        'after_content' => '',
        'grid_start' => false,
        'grid_end' => false
    );
    $field = array_merge($defaults, $field);
    if (!isset($field['options']) || !is_array($field['options'])) {
        $field['options'] = array();
    }
    if ($field['readonly']) {
        $field['attributes'] .= ' readonly="readonly"';
    }
    if ($field['required']) {
        $field['attributes'] .= ' required="required"';
    }

    $field = apply_filters('wpu_extranet__display_field__field', $field, $field_id);
    $settings = wpu_extranet_get_skin_settings();
    if ($field['grid_start']) {
        $html .= '<li><ul class="' . $settings['form_grid_classname'] . '">';
    }
    $html .= '<li class="' . $settings['form_box_classname'] . '">';
    $html .= $field['before_content'];
    $label = '<label for="' . $field_id . '">' . $field['label'] . ' :</label>';
    $is_radio_check = in_array($field['type'], array('radio', 'checkbox'));
    switch ($field['type']) {
    case 'multi-checkbox':
        $field['value'] = explode(';', $field['value']);
        if (!is_array($field['value'])) {
            $field['value'] = array();
        }
        $html .= $label;
        $html .= '<ul>';
        foreach ($field['options'] as $option_id => $option) {
            $html .= '<li>';
            $html .= '<input ' . $field['attributes'] . ' type="checkbox" name="' . $field_id . '[]" value="' . $option_id . '" id="' . $field_id . '_' . $option_id . '" ' . (in_array($option_id, $field['value']) ? 'checked="checked"' : '') . ' />';
            $html .= '<label for="' . $field_id . '_' . $option_id . '">' . esc_html($option) . '</label>';
            $html .= '</li>';
        }
        $html .= '</ul>';
        break;

    case 'textarea':
        $html .= $label;
        $html .= '<textarea ' . $field['attributes'] . ' name="' . $field_id . '" id="' . $field_id . '" class="input" autocapitalize="off">' . esc_textarea($field['value']) . '</textarea>';
        break;
    default:
        $value = $field['value'];
        $checked = '';
        if ($field['type'] == 'checkbox') {
            $value = '1';
            $checked = $field['value'] == '1' ? 'checked="checked"' : '';
        }
        $html .= $is_radio_check ? '' : $label;
        $html .= '<input ' . $field['attributes'] . ' type="' . $field['type'] . '" name="' . $field_id . '" ' . $checked . ' value="' . esc_attr($value) . '" id="' . $field_id . '" class="input" size="20" autocapitalize="off" />';
        $html .= $is_radio_check ? $label : '';
    }

    $html .= $field['after_content'];
    $html .= '</li>';
    if ($field['grid_end']) {
        $html .= '</ul></li>';
    }
    return $html;
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
        }
    }

    return $avatar_url;
}, 1, 3);

/* ----------------------------------------------------------
  Update fields
---------------------------------------------------------- */

function wpu_extranet__user__save_fields($fields, $args = array()) {
    if (empty($_POST) || !is_array($fields)) {
        return false;
    }
    $defaults = array(
        'form_id' => 'editmetas',
        'user_id' => get_current_user_id(),
        'callback_before_fields' => false,
        'callback_after_fields' => false
    );
    $args = wp_parse_args($args, $defaults);
    $errors = array();
    $fields = apply_filters('wpu_extranet__user__save_fields', $fields, $args);

    if ($args['callback_before_fields']) {
        $errors = call_user_func($args['callback_before_fields'], $errors, $args);
    }

    /* @TODO nonce */
    foreach ($fields as $field_id => $field) {
        $check_field_id = $field_id;
        $value = false;
        if (isset($_POST[$field_id])) {
            $value = $_POST[$field_id];
        }
        if (isset($field['readonly']) && $field['readonly']) {
            continue;
        }
        if ($field['type'] == 'checkbox') {
            $value = isset($_POST[$field_id]) ? $_POST[$field_id] : '0';
        }
        if ($field['type'] == 'multi-checkbox') {
            $value = array();
            foreach ($field['options'] as $option_id => $option) {
                if (isset($_POST[$field_id]) && in_array($option_id, $_POST[$field_id])) {
                    $value[] = $option_id;
                }
            }
            $value = implode(';', $value);
        }
        if (isset($field['required']) && $field['required'] && empty($value)) {
            $errors[] = sprintf(__('The field %s is required.', 'wpu_extranet'), $field['label']);
            continue;
        }

        if ($value === false) {
            continue;
        }

        $value = sanitize_text_field($value);
        if (isset($field['minlength']) && strlen($value) < $field['minlength']) {
            $errors[] = sprintf(__('The field %s must be at least %s characters.', 'wpu_extranet'), $field['label'], $field['minlength']);
            continue;
        }
        if (isset($field['maxlength']) && strlen($value) > $field['maxlength']) {
            $errors[] = sprintf(__('The field %s must be at most %s characters.', 'wpu_extranet'), $field['label'], $field['maxlength']);
            continue;
        }
        if (isset($field['type']) && $field['type'] == 'email' && !is_email($value)) {
            $errors[] = sprintf(__('The field %s must be a valid email.', 'wpu_extranet'), $field['label']);
            continue;
        }

        if (!empty($errors)) {
            break;
        }
        update_user_meta($args['user_id'], $field_id, $value);
    }

    if ($args['callback_after_fields']) {
        $errors = call_user_func($args['callback_after_fields'], $errors, $args);
    }

    $return_type = 'error';
    if (empty($errors)) {
        $return_type = 'success';
        $errors[] = __('Fields successfully updated!', 'wpu_extranet');
    }
    return wpuextranet_get_html_errors($errors, array(
        'form_id' => $args['form_id'],
        'type' => $return_type
    ));
}
