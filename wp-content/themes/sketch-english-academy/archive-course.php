<?php
/**
 * Course archive template.
 *
 * @package Sketch_English_Academy
 */

get_header();
?>
<main class="archive-main">
    <div class="container">
        <div class="section-head">
            <div><span class="eyebrow"><?php esc_html_e('Danh mục khóa học', 'sketch-english-academy'); ?></span><h1><?php post_type_archive_title(); ?></h1></div>
            <p><?php esc_html_e('Khám phá các lộ trình tiếng Anh giao tiếp, IELTS và TOEIC phù hợp với từng mục tiêu học tập.', 'sketch-english-academy'); ?></p>
        </div>
        <div class="grid three">
            <?php
            if (have_posts()) :
                while (have_posts()) :
                    the_post();
                    sea_course_card();
                endwhile;
            else :
                echo '<p>' . esc_html__('Chưa có khóa học.', 'sketch-english-academy') . '</p>';
            endif;
            ?>
        </div>
        <?php the_posts_pagination(); ?>
    </div>
</main>
<?php get_footer(); ?>
