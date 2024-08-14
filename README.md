# WPU Extranet

[![PHP workflow](https://github.com/WordPressUtilities/wpu_extranet/actions/workflows/php.yml/badge.svg 'PHP workflow')](https://github.com/WordPressUtilities/wpu_extranet/actions)

Simple toolbox to create an extranet or a customer account.

## Todo

- [x] Hooks to skin forms
- [x] Translate fields
- [x] Instructions to install
- [ ] remove pages from sitemap


## Install

### Config

Use WPUTheme to handle pages :

```php
add_filter('wputh_pages_site', function ($pages_site) {
    $pages_site['extranet_register__page_id'] = array(
        'post_title' => 'Register',
        'page_template' => 'extranet-register.php',
        'wpu_post_metas' => array(
        ),
        'disable_items' => array()
    );
    $pages_site['extranet_lostpassword__page_id'] = array(
        'post_title' => 'Lost Password',
        'page_template' => 'extranet-lostpassword.php',
        'wpu_post_metas' => array(
        ),
        'disable_items' => array()
    );
    $pages_site['extranet_dashboard__page_id'] = array(
        'post_title' => 'Dashboard',
        'page_template' => 'extranet-dashboard.php',
        'wpu_post_metas' => array(
            'is_extranet_page' => 1
        ),
        'disable_items' => array()
    );
    return $pages_site;
});
```

Or use hooks to handle page links :

```php
add_filter('wpu_extranet__get_login_page', function ($link) {
    return site_url('login-page');
});
add_filter('wpu_extranet__get_dashboard_page', function ($link) {
    return site_url('dashboard-page');
});
add_filter('wpu_extranet__get_lostpassword_page', function ($link) {
    return site_url('lostpassword-page');
});
add_filter('wpu_extranet__get_register_page', function ($link) {
    return site_url('register-page');
});
```

### Templates

#### Register

```php
<?php
/* Template Name: Extranet - Register */
$html_return_register = wpu_extranet_register__action();
get_header();
?>
<div class="centered-container section cc-content-register">
    <div class="content-register section">
        <h1 class="content-register__title"><?php the_title();?></h1>
        <div class="content-register__content">
            <?php
echo wpu_extranet_register__form(array(
    'before_fields' => $html_return_register
));
?>
        </div>
    </div>
</div>
<?php
get_footer();
```

#### Lost Password

```php
<?php
/* Template Name: Extranet - Lost Password */
$html_return_register = wpu_extranet_lostpassword__action();
get_header();
?>
<div class="centered-container section cc-content-register">
    <div class="content-register section">
        <h1 class="content-register__title"><?php the_title();?></h1>
        <div class="content-register__content">
            <?php
echo wpu_extranet_lostpassword__form(array(
    'before_fields' => $html_return_register
));
?>
        </div>
    </div>
</div>
<?php
get_footer();

```

#### Custom User Form

```php
<?php
$form_infos = array(
    'form_id' => 'editmetasnewsletter'
);
$form_fields = array(
    'newsletter_ok' => array(
        'type' => 'checkbox',
        'label' => __('Subscribe to the newsletter', 'wpu_extranet')
    )
);
$html_return_form = wpu_extranet__save_fields($form_fields, $form_infos);
$form_settings = array(
    'before_fields' => $html_return_form,
    'form_action' => get_permalink() . '#' . $form_infos['form_id'],
    'form_title' => __('Newsletter', 'wpu_extranet'),
    'form_submit' => __('Save', 'wpu_extranet'),
    'load_user_values' => true
)
get_header();
?>
<div class="centered-container section cc-content-register">
    <div class="content-register section">
        <h1 class="content-register__title"><?php the_title();?></h1>
        <div class="content-register__content">
<?php
echo wpu_extranet_get_form_html($form_infos['form_id'], $form_fields, $form_settings);
?>
        </div>
    </div>
</div>
<?php
get_footer();

```
