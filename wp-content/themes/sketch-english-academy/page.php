<?php
/**
 * Page template.
 *
 * @package Sketch_English_Academy
 */

get_header();
?>
<main class="page-main">
    <div class="container entry-content">
        <?php while (have_posts()) : the_post(); ?>
            <h1><?php the_title(); ?></h1>
            <?php the_content(); ?>
        <?php endwhile; ?>
    </div>
</main>
<?php get_footer(); ?>
