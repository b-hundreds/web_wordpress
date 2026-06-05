<?php
/**
 * Fallback template.
 *
 * @package Sketch_English_Academy
 */

get_header();
?>
<main class="archive-main">
    <div class="container">
        <div class="section-head"><div><span class="eyebrow"><?php esc_html_e('Bài viết', 'sketch-english-academy'); ?></span><h1><?php bloginfo('name'); ?></h1></div></div>
        <div class="grid two">
            <?php while (have_posts()) : the_post(); ?>
                <article class="card">
                    <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                    <p><?php echo sea_excerpt(26); ?></p>
                    <a class="button secondary" href="<?php the_permalink(); ?>"><?php esc_html_e('Đọc tiếp', 'sketch-english-academy'); ?></a>
                </article>
            <?php endwhile; ?>
        </div>
    </div>
</main>
<?php get_footer(); ?>
