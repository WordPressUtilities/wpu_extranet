<?php

/* ----------------------------------------------------------
  Lost password
---------------------------------------------------------- */

/* Form action
-------------------------- */

function wpu_extranet_lostpassword__action() {
    if (is_user_logged_in()) {
        wp_redirect(wpu_extranet__get_dashboard_page());
        die;
    }

    $html_return = '';
    if (isset($_GET['lostpassword']) && $_GET['lostpassword'] == 'success') {
        $html_return .= '<p class="form-lostpassword-success">' . __('Check your email for the confirmation link.', 'wpu_extranet') . '</p>';
    }

    return $html_return;
}

/* Form HTML
-------------------------- */

function wpu_extranet_lostpassword__form($args = array()) {
    if (!is_array($args)) {
        $args = array();
    }
    if (!isset($args['before_fields'])) {
        $args['before_fields'] = '';
    }

    $extra_fields = wpu_extranet__user_register_fields();
    $settings = wpu_extranet_get_skin_settings();

    $html = '';
    $html .= '<div class="' . $settings['form_wrapper_classname'] . ' form-lostpassword-wrapper">';
    $html .= '<form name="lostpasswordform" id="lostpasswordform" action="' . esc_url(network_site_url('wp-login.php?action=lostpassword', 'login_post')) . '" method="post">';
    $html .= $args['before_fields'];
    $html .= '<ul class="' . $settings['form_items_classname'] . '">';
    $html .= '<li class="' . $settings['form_box_classname'] . '">';
    $html .= '<label for="user_login" >' . __('Username or Email Address') . '</label>';
    $html .= '<input required type="text" name="user_login" id="user_login" class="input" value="" size="20" autocapitalize="off" /></label>';
    $html .= '</li>';
    $html .= '<li class=""' . $settings['form_box_submit_classname'] . '">';
    do_action('lostpassword_form');
    $html .= '<input type="hidden" name="redirect_to" value="' . esc_attr(add_query_arg('success', '1', get_permalink())) . '" />';
    $html .= '<button class="' . $settings['form_submit_button_classname'] . '" type="submit"><span>' . __('Get New Password') . '</span></button>';
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
$html_return_lostpassword = wpu_extranet_lostpassword__action();
get_header();
echo '<h1>' . get_the_title() . '</h1>';
echo wpu_extranet_lostpassword__form(array(
    'before_fields' => $html_return_lostpassword
));
get_footer();
*/
