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
        <?php if (function_exists('sea_is_private_learning_view') && sea_is_private_learning_view()) : ?>
            <div class="header-actions">
                <?php if (is_user_logged_in()) : ?>
                    <a class="button secondary" href="<?php echo esc_url(sea_logout_url()); ?>"><?php esc_html_e('Đăng xuất', 'sketch-english-academy'); ?></a>
                <?php else : ?>
                    <a class="button secondary" href="<?php echo esc_url(home_url('/dang-nhap/')); ?>"><?php esc_html_e('Đăng nhập', 'sketch-english-academy'); ?></a>
                <?php endif; ?>
            </div>
        <?php else : ?>
            <nav class="main-nav" aria-label="<?php esc_attr_e('Menu chính', 'sketch-english-academy'); ?>">
                <?php
                wp_nav_menu([
                    'theme_location' => 'primary',
                    'container' => false,
                    'fallback_cb' => 'sea_default_menu',
                ]);
                ?>
            </nav>
            <div class="header-actions">
                <a class="button secondary" href="<?php echo esc_url(home_url('/dang-nhap/')); ?>"><?php esc_html_e('Đăng nhập', 'sketch-english-academy'); ?></a>
                <a class="button" href="<?php echo esc_url(home_url('/#tu-van')); ?>"><?php esc_html_e('Đăng ký tư vấn', 'sketch-english-academy'); ?></a>
            </div>
        <?php endif; ?>
    </div>
</header>
