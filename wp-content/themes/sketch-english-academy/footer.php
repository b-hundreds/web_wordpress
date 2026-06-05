<?php
/**
 * Footer template.
 *
 * @package Sketch_English_Academy
 */
?>
<footer class="site-footer">
    <div class="container footer-grid">
        <div>
            <h2><?php bloginfo('name'); ?></h2>
            <p><?php bloginfo('description'); ?></p>
            <p><?php esc_html_e('Hotline:', 'sketch-english-academy'); ?> <?php echo esc_html(get_theme_mod('sea_phone', '0900 123 456')); ?></p>
        </div>
        <div>
            <h3><?php esc_html_e('Khóa học', 'sketch-english-academy'); ?></h3>
            <ul class="footer-list">
                <li><a href="<?php echo esc_url(get_post_type_archive_link('course')); ?>"><?php esc_html_e('Tất cả khóa học', 'sketch-english-academy'); ?></a></li>
                <li><a href="<?php echo esc_url(home_url('/#tu-van')); ?>"><?php esc_html_e('Kiểm tra đầu vào', 'sketch-english-academy'); ?></a></li>
            </ul>
        </div>
        <div>
            <h3><?php esc_html_e('Hỗ trợ học viên', 'sketch-english-academy'); ?></h3>
            <ul class="footer-list">
                <li><?php esc_html_e('Test trình độ miễn phí', 'sketch-english-academy'); ?></li>
                <li><?php esc_html_e('Tư vấn lộ trình cá nhân', 'sketch-english-academy'); ?></li>
                <li><?php esc_html_e('Theo dõi tiến bộ hằng tuần', 'sketch-english-academy'); ?></li>
            </ul>
        </div>
    </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
