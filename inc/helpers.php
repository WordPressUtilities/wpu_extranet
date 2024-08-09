<?php
defined('ABSPATH') || die;

/* ----------------------------------------------------------
  Forms
---------------------------------------------------------- */

function wpu_extranet_get_form_html($form_id, $fields = array(), $args = array()) {
    $defaults = array(
        'before_fields' => '',
        'after_fields' => '',
        'hidden_fields' => array(),
        'has_honey_pot' => false,
        'load_user_values' => false,
        'form_action' => '',
        'form_title' => '',
        'form_submit' => __('Submit', 'wpu_extranet')
    );
    $args = array_merge($defaults, $args);
    if (!is_array($args['hidden_fields'])) {
        $args['hidden_fields'] = array();
    }
    $settings = wpu_extranet_get_skin_settings();
    $html = '';

    $html .= '<div class="' . $settings['form_wrapper_classname'] . ' form-' . $form_id . '-wrapper">';
    if ($args['form_title']) {
        $html .= '<h3>' . $args['form_title'] . '</h3>';
    }
    $html .= '<form name="' . $form_id . '" id="' . $form_id . '" action="' . $args['form_action'] . '" method="post">';
    $html .= $args['before_fields'];
    $html .= '<ul class="' . $settings['form_items_classname'] . '">';
    foreach ($fields as $field_id => $field) {
        if($args['load_user_values']) {
            $field['value'] = get_user_meta(get_current_user_id(), $field_id, true);
        }
        $html .= wpu_extranet__display_field($field_id, $field);
    }
    $html .= $args['after_fields'];

    if (!empty($fields)) {
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

/* ----------------------------------------------------------
  Error messages
---------------------------------------------------------- */

function wpuextranet_get_html_errors($errors = array(), $args = array()) {
    /* Do not */
    if (empty($errors)) {
        return '';
    }
    $defaults = array(
        'type' => 'error',
        'form_id' => ''
    );
    $args = array_merge($defaults, $args);
    $classname = 'extranet-message extranet-message--' . $args['type'] . ' form-' . $args['form_id'] . '-' . $args['type'] . '';
    $html_return = '';
    /* Display errors */
    if (count($errors) > 1) {
        $html_return .= '<ul class="' . $classname . '">';
        foreach ($errors as $error) {
            $html_return .= '<li>';
            if ($args['type'] == 'error') {
                $html_return .= '<strong class="error">' . __('Error:', 'wpu_extranet') . '</strong> ';
            }
            $html_return .= $error;
            $html_return .= '</li>';
        }
        $html_return .= '</ul>';
    } else {
        $html_return .= '<p class="' . $classname . '">';
        $html_return .= implode('<br />', $errors);
        $html_return .= '</p>';
    }

    return $html_return;
}
