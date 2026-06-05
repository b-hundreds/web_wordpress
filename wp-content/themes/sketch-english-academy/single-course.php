<?php
/**
 * Single course template.
 *
 * @package Sketch_English_Academy
 */

get_header();
the_post();
$meta = sea_course_meta(get_the_ID());
?>
<main class="single-main">
    <div class="container content-wrap">
        <article class="entry-content">
            <span class="eyebrow"><?php esc_html_e('Chi tiết khóa học', 'sketch-english-academy'); ?></span>
            <h1><?php the_title(); ?></h1>
            <p class="lead"><?php echo sea_excerpt(30); ?></p>
            <?php if (has_post_thumbnail()) : the_post_thumbnail('large'); endif; ?>
            <?php the_content(); ?>
        </article>
        <aside class="sidebar-box">
            <h2><?php esc_html_e('Thông tin lớp', 'sketch-english-academy'); ?></h2>
            <div class="meta-row">
                <?php foreach ($meta as $value) : ?>
                    <?php if ($value) : ?><span class="pill"><?php echo esc_html($value); ?></span><?php endif; ?>
                <?php endforeach; ?>
            </div>
            <?php if (!empty($meta['price'])) : ?><p class="price"><?php echo esc_html($meta['price']); ?></p><?php endif; ?>
            <a class="button" href="<?php echo esc_url(home_url('/#tu-van')); ?>"><?php esc_html_e('Đăng ký tư vấn', 'sketch-english-academy'); ?></a>
        </aside>
    </div>
</main>
<?php get_footer(); ?>
