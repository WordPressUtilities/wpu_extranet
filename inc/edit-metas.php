<?php

/* ----------------------------------------------------------
  Metas
---------------------------------------------------------- */

function wpu_extranet__user_register_fields() {
    return apply_filters('wpu_extranet__user_register_fields', array(
        'first_name' => array(
            'label' => __('First name', 'wpu_extranet')
        ),
        'last_name' => array(
            'label' => __('Last name', 'wpu_extranet')
        )
    ));
}

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
        $html_return = '<p class="form-password-success">' . __('Profile successfully updated!', 'wpu_extranet') . '</p>';
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
    $user = wp_get_current_user();

    $html = '';

    $html .= '<h3>' . __('Infos', 'wpu_extranet') . '</h3>';
    $html .= '<form action="' . get_permalink() . '" method="post">';
    $html .= '<ul class="cssc-form">';
    $html .= $args['before_fields'];
    $html .= '<li class="box">';
    $html .= '<label for="username">' . __('Username :', 'wpu_extranet') . '</label>';
    $html .= '<input readonly type="text" name="username" value="' . esc_attr($user->display_name) . '" id="username" class="input" value="" size="20" autocapitalize="off" />';
    $html .= '</li>';
    foreach ($extra_fields as $field_id => $field):
        $html .= '<li class="box">';
        $html .= '<label for="' . $field_id . '">' . $field['label'] . ' :</label>';
        $html .= '<input type="text" name="' . $field_id . '" value="' . esc_attr(get_user_meta(get_current_user_id(), $field_id, 1)) . '" id="' . $field_id . '" class="input" value="" size="20" autocapitalize="off" />';
        $html .= '</li>';
    endforeach;
    $html .= '<li class="box box-submit">';
    $html .= '<button class="wpu_extranet-button" type="submit"><span>' . __('Edit my infos', 'wpu_extranet') . '</span></button>';
    $html .= '</li>';
    $html .= '</ul>';
    $html .= '</form>';
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
