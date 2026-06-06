<?php
/**
 * Front page template.
 *
 * @package Sketch_English_Academy
 */

get_header();
$hero_image = get_template_directory_uri() . '/assets/images/hero-pencil-english.png';
?>
<?php if (is_user_logged_in()) : ?>
<main>
    <div class="container">
        <?php
        if (current_user_can('manage_options')) {
            echo do_shortcode('[sea_admin_dashboard]');
        } elseif (sea_is_teacher()) {
            echo do_shortcode('[sea_teacher_dashboard]');
        } else {
            echo do_shortcode('[sea_student_dashboard]');
        }
        ?>
    </div>
</main>
<?php get_footer(); return; endif; ?>
<main>
    <section class="hero">
        <div class="container hero-grid">
            <div>
                <span class="eyebrow"><?php esc_html_e('English Academy Demo', 'sketch-english-academy'); ?></span>
                <h1><?php echo esc_html(get_theme_mod('sea_hero_title', 'Học tiếng Anh bằng lộ trình rõ ràng, nhìn thấy tiến bộ mỗi tuần')); ?></h1>
                <p class="lead"><?php echo esc_html(get_theme_mod('sea_hero_text', 'Khóa học giao tiếp, IELTS và TOEIC được thiết kế theo trình độ đầu vào, có kiểm tra năng lực, lộ trình rõ ràng và giáo viên theo sát từng tuần.')); ?></p>
                <div class="hero-actions">
                    <a class="button" href="#tu-van"><?php esc_html_e('Đặt lịch test đầu vào', 'sketch-english-academy'); ?></a>
                    <a class="button secondary" href="#khoa-hoc"><?php esc_html_e('Xem khóa học', 'sketch-english-academy'); ?></a>
                </div>
            </div>
            <div class="hero-art">
                <img src="<?php echo esc_url($hero_image); ?>" alt="<?php esc_attr_e('Minh họa bút chì về lớp học tiếng Anh', 'sketch-english-academy'); ?>">
                <div class="sketch-note"><?php esc_html_e('Không gian học tập nhẹ nhàng, truyền cảm hứng mỗi ngày.', 'sketch-english-academy'); ?></div>
            </div>
        </div>
    </section>

    <section class="section alt" id="diem-manh">
        <div class="container">
            <div class="section-head">
                <div><span class="eyebrow"><?php esc_html_e('Điểm mạnh', 'sketch-english-academy'); ?></span><h2><?php esc_html_e('Học đúng trình độ, bám sát mục tiêu', 'sketch-english-academy'); ?></h2></div>
                <p><?php esc_html_e('Từ kiểm tra đầu vào đến báo cáo tiến bộ, mỗi học viên đều có lộ trình học rõ ràng và nội dung phù hợp với mục tiêu cá nhân.', 'sketch-english-academy'); ?></p>
            </div>
            <div class="grid three">
                <article class="card"><div class="feature-icon">A1</div><h3><?php esc_html_e('Lộ trình theo trình độ', 'sketch-english-academy'); ?></h3><p><?php esc_html_e('Phân cấp khóa học rõ ràng cho mất gốc, giao tiếp, IELTS, TOEIC và người đi làm.', 'sketch-english-academy'); ?></p></article>
                <article class="card"><div class="feature-icon">1:1</div><h3><?php esc_html_e('Theo sát từng học viên', 'sketch-english-academy'); ?></h3><p><?php esc_html_e('Giáo viên phản hồi bài tập hằng tuần, sửa phát âm và điều chỉnh mục tiêu theo tốc độ tiến bộ.', 'sketch-english-academy'); ?></p></article>
                <article class="card"><div class="feature-icon">✓</div><h3><?php esc_html_e('Tư vấn trước khi xếp lớp', 'sketch-english-academy'); ?></h3><p><?php esc_html_e('Học viên được kiểm tra năng lực và nhận đề xuất khóa học phù hợp trước khi đăng ký.', 'sketch-english-academy'); ?></p></article>
            </div>
        </div>
    </section>

    <section class="section" id="khoa-hoc">
        <div class="container">
            <div class="section-head">
                <div><span class="eyebrow"><?php esc_html_e('Khóa học nổi bật', 'sketch-english-academy'); ?></span><h2><?php esc_html_e('Chọn mục tiêu, vào đúng lớp', 'sketch-english-academy'); ?></h2></div>
                <a class="button secondary" href="<?php echo esc_url(get_post_type_archive_link('course')); ?>"><?php esc_html_e('Tất cả khóa học', 'sketch-english-academy'); ?></a>
            </div>
            <div class="grid three">
                <?php
                $courses = new WP_Query(['post_type' => 'course', 'posts_per_page' => 3]);
                if ($courses->have_posts()) :
                    while ($courses->have_posts()) :
                        $courses->the_post();
                        sea_course_card();
                    endwhile;
                    wp_reset_postdata();
                endif;
                ?>
            </div>
        </div>
    </section>

    <section class="section alt" id="lo-trinh">
        <div class="container">
            <div class="section-head">
                <div><span class="eyebrow"><?php esc_html_e('Lộ trình', 'sketch-english-academy'); ?></span><h2><?php esc_html_e('Từ đầu vào đến kết quả đầu ra', 'sketch-english-academy'); ?></h2></div>
            </div>
            <div class="grid three steps">
                <article class="card step"><h3><?php esc_html_e('Test đầu vào', 'sketch-english-academy'); ?></h3><p><?php esc_html_e('Đánh giá phát âm, grammar, từ vựng và mục tiêu để xếp lớp phù hợp.', 'sketch-english-academy'); ?></p></article>
                <article class="card step"><h3><?php esc_html_e('Học theo sprint', 'sketch-english-academy'); ?></h3><p><?php esc_html_e('Mỗi tuần có mục tiêu nhỏ, bài tập và phản hồi giúp học viên thấy tiến bộ.', 'sketch-english-academy'); ?></p></article>
                <article class="card step"><h3><?php esc_html_e('Báo cáo tiến bộ', 'sketch-english-academy'); ?></h3><p><?php esc_html_e('Kết quả học tập được tổng hợp để phụ huynh hoặc học viên nắm rõ lộ trình tiếp theo.', 'sketch-english-academy'); ?></p></article>
            </div>
        </div>
    </section>

    <section class="section" id="cam-nhan">
        <div class="container">
            <div class="section-head"><div><span class="eyebrow"><?php esc_html_e('Cảm nhận', 'sketch-english-academy'); ?></span><h2><?php esc_html_e('Học viên nói gì sau khóa học', 'sketch-english-academy'); ?></h2></div></div>
            <div class="grid three">
                <?php
                $quotes = new WP_Query(['post_type' => 'testimonial', 'posts_per_page' => 3]);
                while ($quotes->have_posts()) :
                    $quotes->the_post();
                    ?>
                    <blockquote class="quote"><p><?php echo esc_html(wp_strip_all_tags(get_the_content())); ?></p><strong><?php the_title(); ?></strong><span><?php esc_html_e('Học viên', 'sketch-english-academy'); ?></span></blockquote>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        </div>
    </section>

    <section class="section alt" id="tu-van">
        <div class="container consult-section">
            <div class="consult-intro">
                <span class="eyebrow"><?php esc_html_e('Đăng ký', 'sketch-english-academy'); ?></span>
                <h2><?php esc_html_e('Nhận tư vấn lộ trình miễn phí', 'sketch-english-academy'); ?></h2>
                <p class="lead"><?php esc_html_e('Xem lớp học thử hiện có, chọn ngày phù hợp và để lại thông tin. Đội ngũ tư vấn sẽ xác nhận lịch, kiểm tra trình độ đầu vào và gợi ý lộ trình học.', 'sketch-english-academy'); ?></p>
            </div>
            <?php echo do_shortcode('[sea_lead_form]'); ?>
        </div>
    </section>

    <section class="section" id="faq">
        <div class="container">
            <div class="section-head"><div><span class="eyebrow">FAQ</span><h2><?php esc_html_e('Câu hỏi thường gặp', 'sketch-english-academy'); ?></h2></div></div>
            <?php
            $faqs = new WP_Query(['post_type' => 'faq', 'posts_per_page' => 8]);
            while ($faqs->have_posts()) :
                $faqs->the_post();
                ?>
                <details class="faq-item"><summary><?php the_title(); ?></summary><div><?php the_content(); ?></div></details>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
    </section>
</main>
<?php get_footer(); ?>
