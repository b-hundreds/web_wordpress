<?php
/**
 * Page template.
 *
 * @package Sketch_English_Academy
 */

get_header();
?>
<?php if (is_page(['dang-nhap', 'bang-dieu-khien-admin', 'bang-dieu-khien-giao-vien', 'lop-hoc-cua-toi'])) : ?>
<main>
    <div class="container">
        <?php while (have_posts()) : the_post(); ?>
            <?php the_content(); ?>
        <?php endwhile; ?>
    </div>
</main>
<?php get_footer(); return; endif; ?>
<main class="page-main">
    <div class="container entry-content">
        <?php while (have_posts()) : the_post(); ?>
            <h1><?php the_title(); ?></h1>
            <?php the_content(); ?>
        <?php endwhile; ?>
    </div>
</main>
<?php get_footer(); ?>
