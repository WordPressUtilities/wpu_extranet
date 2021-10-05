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
    $fields = array();
    foreach ($fields_raw as $key => $field) {
        if (!isset($field['type'])) {
            $field['type'] = 'text';
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
