<?php
/**
 * Sketch English Academy theme functions.
 *
 * @package Sketch_English_Academy
 */

if (!defined('ABSPATH')) {
    exit;
}

define('SEA_VERSION', '1.0.0');

function sea_setup(): void
{
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);
    add_theme_support('custom-logo', ['height' => 80, 'width' => 240, 'flex-height' => true, 'flex-width' => true]);

    register_nav_menus([
        'primary' => __('Menu chính', 'sketch-english-academy'),
        'footer' => __('Menu chân trang', 'sketch-english-academy'),
    ]);
}
add_action('after_setup_theme', 'sea_setup');

function sea_assets(): void
{
    wp_enqueue_style('sea-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@500;650;750;850;950&display=swap', [], null);
    wp_enqueue_style('sea-style', get_stylesheet_uri(), [], SEA_VERSION);
    wp_enqueue_script('sea-site', get_template_directory_uri() . '/assets/js/site.js', [], SEA_VERSION, true);
}
add_action('wp_enqueue_scripts', 'sea_assets');

function sea_default_menu(): void
{
    echo '<ul><li><a href="' . esc_url(home_url('/#khoa-hoc')) . '">Khóa học</a></li><li><a href="' . esc_url(home_url('/#lo-trinh')) . '">Lộ trình</a></li><li><a href="' . esc_url(home_url('/#faq')) . '">FAQ</a></li></ul>';
}

function sea_register_content_types(): void
{
    register_post_type('course', [
        'labels' => [
            'name' => __('Khóa học', 'sketch-english-academy'),
            'singular_name' => __('Khóa học', 'sketch-english-academy'),
            'add_new_item' => __('Thêm khóa học', 'sketch-english-academy'),
            'edit_item' => __('Sửa khóa học', 'sketch-english-academy'),
        ],
        'public' => true,
        'menu_icon' => 'dashicons-welcome-learn-more',
        'has_archive' => true,
        'rewrite' => ['slug' => 'khoa-hoc'],
        'supports' => ['title', 'editor', 'excerpt', 'thumbnail'],
        'show_in_rest' => true,
    ]);

    register_taxonomy('course_level', ['course'], [
        'labels' => ['name' => __('Trình độ', 'sketch-english-academy')],
        'public' => true,
        'hierarchical' => true,
        'rewrite' => ['slug' => 'trinh-do'],
        'show_in_rest' => true,
    ]);

    register_post_type('testimonial', [
        'labels' => ['name' => __('Cảm nhận', 'sketch-english-academy')],
        'public' => true,
        'menu_icon' => 'dashicons-format-quote',
        'supports' => ['title', 'editor', 'excerpt'],
        'show_in_rest' => true,
    ]);

    register_post_type('faq', [
        'labels' => ['name' => __('FAQ', 'sketch-english-academy')],
        'public' => true,
        'menu_icon' => 'dashicons-editor-help',
        'supports' => ['title', 'editor'],
        'show_in_rest' => true,
    ]);

    register_post_type('lead', [
        'labels' => ['name' => __('Lead tư vấn', 'sketch-english-academy')],
        'public' => false,
        'show_ui' => true,
        'menu_icon' => 'dashicons-email-alt2',
        'supports' => ['title'],
        'capability_type' => 'post',
    ]);
}
add_action('init', 'sea_register_content_types');

function sea_add_meta_boxes(): void
{
    add_meta_box('sea_course_details', __('Thông tin khóa học', 'sketch-english-academy'), 'sea_course_details_box', 'course', 'normal', 'high');
    add_meta_box('sea_lead_details', __('Thông tin đăng ký', 'sketch-english-academy'), 'sea_lead_details_box', 'lead', 'normal', 'high');
}
add_action('add_meta_boxes', 'sea_add_meta_boxes');

function sea_course_details_box(WP_Post $post): void
{
    wp_nonce_field('sea_save_course', 'sea_course_nonce');
    $fields = [
        'duration' => __('Thời lượng', 'sketch-english-academy'),
        'schedule' => __('Lịch học', 'sketch-english-academy'),
        'price' => __('Học phí', 'sketch-english-academy'),
        'outcome' => __('Cam kết đầu ra', 'sketch-english-academy'),
    ];

    foreach ($fields as $key => $label) {
        $value = get_post_meta($post->ID, "_sea_{$key}", true);
        echo '<p><label for="sea_' . esc_attr($key) . '"><strong>' . esc_html($label) . '</strong></label>';
        echo '<input class="widefat" id="sea_' . esc_attr($key) . '" name="sea_' . esc_attr($key) . '" value="' . esc_attr($value) . '"></p>';
    }
}

function sea_lead_details_box(WP_Post $post): void
{
    $fields = ['name', 'phone', 'email', 'course', 'message'];
    echo '<table class="widefat striped"><tbody>';
    foreach ($fields as $field) {
        echo '<tr><th>' . esc_html(ucfirst($field)) . '</th><td>' . esc_html(get_post_meta($post->ID, "_sea_{$field}", true)) . '</td></tr>';
    }
    echo '</tbody></table>';
}

function sea_save_course_meta(int $post_id): void
{
    if (!isset($_POST['sea_course_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['sea_course_nonce'])), 'sea_save_course')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    foreach (['duration', 'schedule', 'price', 'outcome'] as $key) {
        if (isset($_POST["sea_{$key}"])) {
            update_post_meta($post_id, "_sea_{$key}", sanitize_text_field(wp_unslash($_POST["sea_{$key}"])));
        }
    }
}
add_action('save_post_course', 'sea_save_course_meta');

function sea_handle_lead_form(): void
{
    if (!isset($_POST['sea_lead_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['sea_lead_nonce'])), 'sea_submit_lead')) {
        return;
    }

    $name = isset($_POST['sea_name']) ? sanitize_text_field(wp_unslash($_POST['sea_name'])) : '';
    $phone = isset($_POST['sea_phone']) ? sanitize_text_field(wp_unslash($_POST['sea_phone'])) : '';
    $email = isset($_POST['sea_email']) ? sanitize_email(wp_unslash($_POST['sea_email'])) : '';
    $course = isset($_POST['sea_course']) ? sanitize_text_field(wp_unslash($_POST['sea_course'])) : '';
    $message = isset($_POST['sea_message']) ? sanitize_textarea_field(wp_unslash($_POST['sea_message'])) : '';

    if (!$name || !$phone) {
        wp_safe_redirect(add_query_arg('sea_status', 'missing', wp_get_referer() ?: home_url('/')));
        exit;
    }

    $lead_id = wp_insert_post([
        'post_type' => 'lead',
        'post_status' => 'publish',
        'post_title' => sprintf('%s - %s', $name, current_time('d/m/Y H:i')),
    ]);

    if ($lead_id && !is_wp_error($lead_id)) {
        foreach (compact('name', 'phone', 'email', 'course', 'message') as $key => $value) {
            update_post_meta($lead_id, "_sea_{$key}", $value);
        }

        $admin_email = get_option('admin_email');
        wp_mail($admin_email, 'Lead tư vấn mới từ website', "Tên: {$name}\nĐiện thoại: {$phone}\nEmail: {$email}\nKhóa học: {$course}\nLời nhắn: {$message}");
        wp_safe_redirect(add_query_arg('sea_status', 'success', wp_get_referer() ?: home_url('/')));
        exit;
    }

    wp_safe_redirect(add_query_arg('sea_status', 'error', wp_get_referer() ?: home_url('/')));
    exit;
}
add_action('admin_post_nopriv_sea_lead', 'sea_handle_lead_form');
add_action('admin_post_sea_lead', 'sea_handle_lead_form');

function sea_lead_form_shortcode(): string
{
    $status = isset($_GET['sea_status']) ? sanitize_key($_GET['sea_status']) : '';
    $courses = get_posts(['post_type' => 'course', 'numberposts' => 20, 'orderby' => 'menu_order title', 'order' => 'ASC']);

    ob_start();
    ?>
    <form class="lead-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <?php if ('success' === $status) : ?>
            <p class="form-message success"><?php esc_html_e('Đã nhận thông tin. Tư vấn viên sẽ liên hệ trong thời gian sớm nhất.', 'sketch-english-academy'); ?></p>
        <?php elseif ($status) : ?>
            <p class="form-message error"><?php esc_html_e('Vui lòng kiểm tra lại thông tin bắt buộc.', 'sketch-english-academy'); ?></p>
        <?php endif; ?>
        <input type="hidden" name="action" value="sea_lead">
        <?php wp_nonce_field('sea_submit_lead', 'sea_lead_nonce'); ?>
        <div class="form-grid">
            <p class="form-field"><label for="sea_name"><?php esc_html_e('Họ tên', 'sketch-english-academy'); ?></label><input id="sea_name" name="sea_name" required></p>
            <p class="form-field"><label for="sea_phone"><?php esc_html_e('Số điện thoại', 'sketch-english-academy'); ?></label><input id="sea_phone" name="sea_phone" required></p>
            <p class="form-field"><label for="sea_email"><?php esc_html_e('Email', 'sketch-english-academy'); ?></label><input id="sea_email" type="email" name="sea_email"></p>
            <p class="form-field"><label for="sea_course"><?php esc_html_e('Khóa quan tâm', 'sketch-english-academy'); ?></label><select id="sea_course" name="sea_course"><option value=""><?php esc_html_e('Chọn khóa học', 'sketch-english-academy'); ?></option><?php foreach ($courses as $course) : ?><option><?php echo esc_html(get_the_title($course)); ?></option><?php endforeach; ?></select></p>
            <p class="form-field full"><label for="sea_message"><?php esc_html_e('Mục tiêu học tập', 'sketch-english-academy'); ?></label><textarea id="sea_message" name="sea_message"></textarea></p>
        </div>
        <button type="submit"><?php esc_html_e('Nhận tư vấn miễn phí', 'sketch-english-academy'); ?></button>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('sea_lead_form', 'sea_lead_form_shortcode');

function sea_excerpt(int $words = 22): string
{
    return esc_html(wp_trim_words(get_the_excerpt() ?: get_the_content(), $words));
}

function sea_course_meta(int $post_id): array
{
    return [
        'duration' => get_post_meta($post_id, '_sea_duration', true),
        'schedule' => get_post_meta($post_id, '_sea_schedule', true),
        'price' => get_post_meta($post_id, '_sea_price', true),
        'outcome' => get_post_meta($post_id, '_sea_outcome', true),
    ];
}

function sea_course_card(?WP_Post $post = null): void
{
    $post = $post ?: get_post();
    $meta = sea_course_meta($post->ID);
    ?>
    <article class="course-card">
        <a class="course-media" href="<?php echo esc_url(get_permalink($post)); ?>" aria-label="<?php echo esc_attr(get_the_title($post)); ?>">
            <?php if (has_post_thumbnail($post)) : ?>
                <?php echo get_the_post_thumbnail($post, 'medium_large'); ?>
            <?php else : ?>
                <span>EN</span>
            <?php endif; ?>
        </a>
        <div class="course-body">
            <h3><a href="<?php echo esc_url(get_permalink($post)); ?>"><?php echo esc_html(get_the_title($post)); ?></a></h3>
            <p><?php echo sea_excerpt(21); ?></p>
            <div class="meta-row">
                <?php foreach (['duration', 'schedule', 'outcome'] as $key) : ?>
                    <?php if (!empty($meta[$key])) : ?><span class="pill"><?php echo esc_html($meta[$key]); ?></span><?php endif; ?>
                <?php endforeach; ?>
            </div>
            <?php if (!empty($meta['price'])) : ?><div class="price"><?php echo esc_html($meta['price']); ?></div><?php endif; ?>
            <div class="card-actions"><a class="button secondary" href="<?php echo esc_url(get_permalink($post)); ?>"><?php esc_html_e('Xem chi tiet', 'sketch-english-academy'); ?></a></div>
        </div>
    </article>
    <?php
}

function sea_output_seo(): void
{
    if (is_admin()) {
        return;
    }

    $description = get_bloginfo('description') ?: 'Khóa học tiếng Anh giao tiếp, IELTS, TOEIC và tiếng Anh cho người đi làm.';
    if (is_singular()) {
        $description = wp_trim_words(wp_strip_all_tags(get_the_excerpt() ?: get_the_content(null, false, get_queried_object_id())), 28);
    }

    echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr(wp_get_document_title()) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
    echo '<meta property="og:type" content="' . (is_singular() ? 'article' : 'website') . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url(home_url(add_query_arg([], $GLOBALS['wp']->request ?? ''))) . '">' . "\n";
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
}
add_action('wp_head', 'sea_output_seo', 1);

function sea_schema(): void
{
    if (is_admin()) {
        return;
    }

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'EducationalOrganization',
        'name' => get_bloginfo('name'),
        'url' => home_url('/'),
        'description' => get_bloginfo('description'),
        'areaServed' => 'Vietnam',
        'sameAs' => [],
    ];

    if (is_singular('course')) {
        $meta = sea_course_meta(get_the_ID());
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Course',
            'name' => get_the_title(),
            'description' => wp_trim_words(wp_strip_all_tags(get_the_content()), 35),
            'provider' => ['@type' => 'EducationalOrganization', 'name' => get_bloginfo('name'), 'url' => home_url('/')],
            'offers' => ['@type' => 'Offer', 'priceCurrency' => 'VND', 'price' => preg_replace('/[^0-9]/', '', (string) $meta['price']), 'availability' => 'https://schema.org/InStock'],
        ];
    }

    if (is_front_page()) {
        $faqs = get_posts(['post_type' => 'faq', 'numberposts' => 8]);
        if ($faqs) {
            $schema = [
                $schema,
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'FAQPage',
                    'mainEntity' => array_map(static function ($faq) {
                        return [
                            '@type' => 'Question',
                            'name' => get_the_title($faq),
                            'acceptedAnswer' => ['@type' => 'Answer', 'text' => wp_strip_all_tags($faq->post_content)],
                        ];
                    }, $faqs),
                ],
            ];
        }
    }

    echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
}
add_action('wp_head', 'sea_schema', 30);

function sea_customize_register(WP_Customize_Manager $wp_customize): void
{
    $wp_customize->add_section('sea_home', ['title' => __('Noi dung trang chu', 'sketch-english-academy'), 'priority' => 30]);
    $fields = [
        'sea_hero_title' => ['label' => 'Tiêu đề hero', 'default' => 'Học tiếng Anh bằng lộ trình rõ ràng, nhìn thấy tiến bộ mỗi tuần'],
        'sea_hero_text' => ['label' => 'Mô tả hero', 'default' => 'Khóa học giao tiếp, IELTS và TOEIC được thiết kế theo trình độ đầu vào, có kiểm tra năng lực, lộ trình rõ ràng và giáo viên theo sát từng tuần.'],
        'sea_phone' => ['label' => 'Hotline', 'default' => '0900 123 456'],
    ];

    foreach ($fields as $id => $field) {
        $wp_customize->add_setting($id, ['default' => $field['default'], 'sanitize_callback' => 'sanitize_text_field']);
        $wp_customize->add_control($id, ['section' => 'sea_home', 'label' => $field['label'], 'type' => 'text']);
    }
}
add_action('customize_register', 'sea_customize_register');

function sea_seed_demo_content(): void
{
    if (get_option('sea_demo_seeded')) {
        return;
    }

    $courses = [
        ['Giao tiếp Mất Gốc', 'Lấy lại phát âm, từ vựng và phản xạ hỏi đáp trong 12 tuần.', '12 tuần', 'Tối 2-4-6', '3.900.000đ', 'Nói được 20 chủ đề cơ bản'],
        ['IELTS Foundation 4.0-5.5', 'Xây nền grammar, từ vựng học thuật và kỹ năng làm bài IELTS.', '16 tuần', 'Cuối tuần', '6.900.000đ', 'Tăng 1.0 band'],
        ['TOEIC Cấp Tốc 650+', 'Tập trung chiến thuật nghe đọc và bộ đề sát format cho người đi làm.', '10 tuần', 'Tối 3-5', '4.800.000đ', 'Đạt 650+'],
    ];

    foreach ($courses as $course) {
        $post_id = wp_insert_post([
            'post_type' => 'course',
            'post_status' => 'publish',
            'post_title' => $course[0],
            'post_excerpt' => $course[1],
            'post_content' => "<h2>Bạn sẽ học được gì?</h2><ul><li>Lộ trình cá nhân hóa theo mục tiêu đầu ra.</li><li>Bài tập hằng tuần và chấm điểm chi tiết.</li><li>Lớp sĩ số nhỏ, giáo viên theo sát tiến bộ.</li></ul><h2>Phù hợp với ai?</h2><p>Khóa học dành cho học viên cần một lộ trình rõ ràng, có người đồng hành và cần kết quả có thể đo lường.</p>",
        ]);

        if ($post_id && !is_wp_error($post_id)) {
            update_post_meta($post_id, '_sea_duration', $course[2]);
            update_post_meta($post_id, '_sea_schedule', $course[3]);
            update_post_meta($post_id, '_sea_price', $course[4]);
            update_post_meta($post_id, '_sea_outcome', $course[5]);
        }
    }

    $testimonials = [
        ['Minh Anh', 'Sau 3 tháng mình từ mất gốc đã tự tin đặt câu hỏi và trao đổi với khách nước ngoài.'],
        ['Quốc Huy', 'Lộ trình TOEIC ngắn gọn, bài tập đúng điểm yếu nên điểm tăng nhanh hơn mình nghĩ.'],
        ['Thu Trang', 'Giáo viên sửa phát âm rất kỹ, website đăng ký và xem khóa học cũng rất dễ hiểu.'],
    ];

    foreach ($testimonials as $item) {
        wp_insert_post(['post_type' => 'testimonial', 'post_status' => 'publish', 'post_title' => $item[0], 'post_content' => $item[1]]);
    }

    $faqs = [
        ['Có kiểm tra đầu vào không?', 'Có. Học viên được test đầu vào và nhận tư vấn lộ trình trước khi xếp lớp.'],
        ['Lớp học có bao nhiêu học viên?', 'Mỗi lớp giới hạn 8-12 học viên để giáo viên theo sát phản xạ, phát âm và bài tập.'],
        ['Có được tư vấn lộ trình trước khi học không?', 'Có. Sau bài kiểm tra đầu vào, tư vấn viên sẽ gợi ý khóa học và lịch học phù hợp với mục tiêu của bạn.'],
    ];

    foreach ($faqs as $faq) {
        wp_insert_post(['post_type' => 'faq', 'post_status' => 'publish', 'post_title' => $faq[0], 'post_content' => $faq[1]]);
    }

    update_option('sea_demo_seeded', 1);
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'sea_seed_demo_content');

function sea_flush_rewrites(): void
{
    sea_register_content_types();
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'sea_flush_rewrites');
