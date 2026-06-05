<?php
/**
 * Header template.
 *
 * @package Sketch_English_Academy
 */
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="<?php echo esc_url(home_url('/')); ?>">
            <span class="brand-mark">EN</span>
            <span><?php bloginfo('name'); ?><small><?php bloginfo('description'); ?></small></span>
        </a>
        <nav class="main-nav" aria-label="<?php esc_attr_e('Menu chính', 'sketch-english-academy'); ?>">
            <?php
            wp_nav_menu([
                'theme_location' => 'primary',
                'container' => false,
                'fallback_cb' => 'sea_default_menu',
            ]);
            ?>
        </nav>
        <a class="button" href="<?php echo esc_url(home_url('/#tu-van')); ?>"><?php esc_html_e('Đăng ký tư vấn', 'sketch-english-academy'); ?></a>
    </div>
</header>
