<?php
defined('ABSPATH') || die;

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
