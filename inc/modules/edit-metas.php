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

    $html_return = '';
    if ($has_update) {
        $html_return = '<p class="extranet-message extranet-message--success form-password-success">' . __('Profile successfully updated!', 'wpu_extranet') . '</p>';
    }

    return $html_return;
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
    $html .= '<form name="editmetasform" id="editmetasform" action="' . get_permalink() . '" method="post">';
    $html .= '<ul class="' . $settings['form_items_classname'] . '">';
    $html .= $args['before_fields'];
    $html .= wpu_extranet__display_field('username', array(
        'label' => __('Username', 'wpu_extranet'),
        'attributes' => 'readonly',
        'value' => $user->display_name
    ));
    foreach ($extra_fields as $field_id => $field):
        if (!$field['in_editmetas_form']) {
            continue;
        }
        $field['value'] = get_user_meta(get_current_user_id(), $field_id, 1);
        $html .= wpu_extranet__display_field($field_id, $field);
    endforeach;
    $html .= '<li class="' . $settings['form_box_submit_classname'] . '">';
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
