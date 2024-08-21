<?php
defined('ABSPATH') || die;

/* ----------------------------------------------------------
  Build a form
---------------------------------------------------------- */

/* Main form
-------------------------- */

function wpu_extranet_get_form_html($form_id, $fields = array(), $args = array()) {
    $defaults = array(
        'before_fields' => '',
        'after_fields' => '',
        'hidden_fields' => array(),
        'has_honey_pot' => false,
        'load_user_values' => false,
        'allow_no_fields' => false,
        'form_action' => '',
        'form_title' => '',
        'form_submit' => __('Submit', 'wpu_extranet')
    );
    $args = array_merge($defaults, $args);
    if (!is_array($args['hidden_fields'])) {
        $args['hidden_fields'] = array();
    }

    $form_attributes = '';
    foreach ($fields as $field_id => $field) {
        if (isset($field['type']) && $field['type'] == 'file') {
            $form_attributes .= ' enctype="multipart/form-data"';
        }
    }

    $settings = wpu_extranet_get_skin_settings();
    $html = '';

    $html .= '<div class="' . $settings['form_wrapper_classname'] . ' form-' . $form_id . '-wrapper">';
    if ($args['form_title']) {
        $html .= '<h3>' . $args['form_title'] . '</h3>';
    }
    $html .= '<form name="' . $form_id . '" id="' . $form_id . '" action="' . $args['form_action'] . '" method="post" ' . $form_attributes . '>';
    $html .= $args['before_fields'];
    $html .= '<ul class="' . $settings['form_items_classname'] . '">';
    foreach ($fields as $field_id => $field) {
        if ($args['load_user_values']) {
            $field['value'] = get_user_meta(get_current_user_id(), $field_id, true);
        }
        $html .= wpu_extranet__display_field($field_id, $field);
    }
    $html .= $args['after_fields'];

    if (!empty($fields) || $args['allow_no_fields']) {
        $html .= '<li class="' . $settings['form_box_submit_classname'] . '">';
        if ($args['has_honey_pot']) {
            $honeypot_id = wpu_extranet_register_get_honeypot_id();
            $html .= '<label for="' . $honeypot_id . '" aria-hidden="true" class="visually-hidden"><input type="radio" name="' . $honeypot_id . '" id="' . $honeypot_id . '" style="display:none" value="1"></label>';
        }
        $html .= wp_nonce_field('wpuextranet_' . $form_id . '_action', 'wpuextranet_' . $form_id, true, false);
        foreach ($args['hidden_fields'] as $id => $value) {
            $html .= '<input type="hidden" name="' . $id . '" value="' . esc_attr($value) . '" />';
        }
        $html .= '<button class="' . $settings['form_submit_button_classname'] . '" type="submit"><span>' . $args['form_submit'] . '</span></button>';
        $html .= '</li>';
    }
    $html .= '</ul>';
    $html .= '</form>';
    $html .= '</div>';

    return $html;

}

/* Ensure field is correct
-------------------------- */

function wpu_extranet__correct_field($field, $field_id) {
    if (!is_array($field)) {
        $field = array();
    }
    $defaults = array(
        'label' => $field_id,
        'required' => false,
        'multiple' => false,
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
    return $field;
}

/* Display field
-------------------------- */

function wpu_extranet__display_field($field_id, $field) {
    $html = '';

    $field = wpu_extranet__correct_field($field, $field_id);

    if ($field['readonly']) {
        $field['attributes'] .= ' readonly="readonly"';
    }
    if ($field['required']) {
        $field['attributes'] .= ' required="required"';
    }
    $field_display_id = 'f' . uniqid() . '_' . $field_id;

    $field = apply_filters('wpu_extranet__display_field__field', $field, $field_id);
    $settings = wpu_extranet_get_skin_settings();
    if ($field['grid_start']) {
        $html .= '<li><ul class="' . $settings['form_grid_classname'] . '">';
    }
    $html .= '<li data-fieldid="' . esc_attr($field_id) . '" data-fieldtype="' . esc_attr($field['type']) . '" class="' . $settings['form_box_classname'] . '">';
    $html .= $field['before_content'];

    $label_content = $field['label'];
    if ($field['required']) {
        $label_content .= ' <span class="required">*</span>';
    }
    if ($field['type'] != 'checkbox') {
        $label_content .= ' :';
    }
    $label = '<label for="' . $field_display_id . '">' . $label_content . '</label>';
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
            $html .= '<input ' . $field['attributes'] . ' type="checkbox" name="' . $field_id . '[]" value="' . $option_id . '" id="' . $field_display_id . '_' . $option_id . '" ' . (in_array($option_id, $field['value']) ? 'checked="checked"' : '') . ' />';
            $html .= '<label for="' . $field_id . '_' . $option_id . '">' . esc_html($option) . '</label>';
            $html .= '</li>';
        }
        $html .= '</ul>';
        break;
    case 'select':
        $html .= $label;
        $html .= '<select  ' . $field['attributes'] . ' ' . ($field['multiple'] ? 'multiple' : '') . ' name="' . $field_id . ($field['multiple'] ? '[]' : '') . '" id="' . $field_display_id . '" >';
        $selected_options = array($field['value']);
        if ($field['multiple']) {
            $selected_options = explode(';', $field['value']);
        }
        foreach ($field['options'] as $option_id => $option) {
            $html .= '<option value="' . $option_id . '" ' . (in_array($option_id, $selected_options) ? 'selected="selected"' : '') . '>' . esc_html($option) . '</option>';
        }
        $html .= '</select>';
        break;
    case 'textarea':
        $html .= $label;
        $html .= '<textarea ' . $field['attributes'] . ' name="' . $field_id . '" id="' . $field_display_id . '" class="input" autocapitalize="off">' . esc_textarea($field['value']) . '</textarea>';
        break;
    default:
        $value = $field['value'];
        $checked = '';
        if ($field['type'] == 'checkbox') {
            $value = '1';
            $checked = $field['value'] == '1' ? 'checked="checked"' : '';
        }
        $html .= $is_radio_check ? '' : $label;
        $html .= '<input ' . $field['attributes'] . ' type="' . $field['type'] . '" name="' . $field_id . '" ' . $checked . ' value="' . esc_attr($value) . '" id="' . $field_display_id . '" class="input" size="20" autocapitalize="off" />';
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
  Submit
---------------------------------------------------------- */

/* Update fields
-------------------------- */

function wpu_extranet__save_fields($fields, $args = array()) {
    if (empty($_POST) || !is_array($fields)) {
        return false;
    }

    $defaults = array(
        'form_id' => 'editmetas',
        'user_id' => get_current_user_id(),
        'callback_before_fields' => false,
        'callback_after_fields' => false
    );
    $args = array_merge($defaults, $args);

    if (!isset($_POST['wpuextranet_' . $args['form_id']]) || !wp_verify_nonce($_POST['wpuextranet_' . $args['form_id']], 'wpuextranet_' . $args['form_id'] . '_action')) {
        return false;
    }

    $errors = array();
    $fields = apply_filters('wpu_extranet__save_fields', $fields, $args);

    if ($args['callback_before_fields']) {
        $errors = call_user_func($args['callback_before_fields'], $errors, $args);
    }

    foreach ($fields as $field_id => $field) {
        $check_field_id = $field_id;
        $field = wpu_extranet__correct_field($field, $field_id);
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
        if ($field['type'] == 'multi-checkbox' || ($field['type'] == 'select' && $field['multiple'])) {
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

        /* Conditions */
        if (isset($field['minlength']) && strlen($value) < $field['minlength']) {
            $errors[] = sprintf(__('The field %s must be at least %s characters.', 'wpu_extranet'), $field['label'], $field['minlength']);
            continue;
        }
        if (isset($field['maxlength']) && strlen($value) > $field['maxlength']) {
            $errors[] = sprintf(__('The field %s must be at most %s characters.', 'wpu_extranet'), $field['label'], $field['maxlength']);
            continue;
        }

        /* Types */
        if ($field['type'] == 'email' && !is_email($value)) {
            $errors[] = sprintf(__('The field %s must be a valid email.', 'wpu_extranet'), $field['label']);
            continue;
        }
        if ($field['type'] == 'url' && !filter_var($value, FILTER_VALIDATE_URL)) {
            $errors[] = sprintf(__('The field %s must be a valid URL.', 'wpu_extranet'), $field['label']);
            continue;
        }
        if ($field['type'] == 'select' && !$field['multiple'] && !isset($field['options'][$value])) {
            $errors[] = sprintf(__('The field %s must be a valid option.', 'wpu_extranet'), $field['label']);
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
