<?php

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
    if (!isset($field['value'])) {
        $field['value'] = '';
    }
    if (!isset($field['attributes'])) {
        $field['attributes'] = '';
    }
    if (!isset($field['type'])) {
        $field['type'] = 'text';
    }
    $settings = wpu_extranet_get_skin_settings();
    $html .= '<li class="' . $settings['form_box_classname'] . '">';
    $html .= '<label for="' . $field_id . '">' . $field['label'] . ' :</label>';
    $html .= '<input ' . $field['attributes'] . ' type="' . $field['type'] . '" name="' . $field_id . '" value="' . esc_attr($field['value']) . '" id="' . $field_id . '" class="input" size="20" autocapitalize="off" />';
    $html .= '</li>';
    return $html;
}

/* ----------------------------------------------------------
  Log user by id
---------------------------------------------------------- */

function wpu_extranet_log_user($user){
    if(is_numeric($user)){
        $user = get_user_by('id', $user);
    }
    wp_set_auth_cookie($user->ID);
    wp_set_current_user($user->ID);
    do_action('wp_login', $user->user_login, $user);
}
