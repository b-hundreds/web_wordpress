<?php
/**
 * Footer template.
 *
 * @package Sketch_English_Academy
 */
?>
<?php if (function_exists('sea_is_private_learning_view') && sea_is_private_learning_view()) : ?>
<footer class="site-footer">
    <div class="container">
        <p><?php bloginfo('name'); ?> · <?php esc_html_e('Khu vực học tập nội bộ', 'sketch-english-academy'); ?></p>
    </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
<?php return; endif; ?>
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
            <h3><?php esc_html_e('Địa chỉ', 'sketch-english-academy'); ?></h3>
            <ul class="footer-list">
                <li><a href="https://www.google.com/maps/search/?api=1&query=71%2C%20Ph%C6%B0%C6%A1ng%20Canh%2C%20Xu%C3%A2n%20Ph%C6%B0%C6%A1ng%2C%20H%C3%A0%20N%E1%BB%99i" target="_blank" rel="noopener noreferrer">71, Phương Canh, Xuân Phương, Hà Nội</a></li>
            </ul>
        </div>
    </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
