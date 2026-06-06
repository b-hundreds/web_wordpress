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
define('SEA_GOOGLE_CLIENT_ID', '533272251113-q429n27vru870og6d701d5mv5vvrvpf4.apps.googleusercontent.com');
define('SEA_RECAPTCHA_SITE_KEY', '6Ld9XA8tAAAAALkqJzCk3fhEAabpK_mW_U9ZfTjY');
define('SEA_RECAPTCHA_SECRET_KEY', '6Ld9XA8tAAAAACZgb5t7b-qO5q6mHF49595keJBG');

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

    if (is_page('dang-nhap')) {
        wp_enqueue_script('sea-google-identity', 'https://accounts.google.com/gsi/client', [], null, true);
        wp_enqueue_script('sea-recaptcha', 'https://www.google.com/recaptcha/api.js', [], null, true);
    }
}
add_action('wp_enqueue_scripts', 'sea_assets');

function sea_google_identity_script_tag(string $tag, string $handle): string
{
    if ('sea-google-identity' !== $handle) {
        return $tag;
    }

    return str_replace(' src=', ' async defer src=', $tag);
}
add_filter('script_loader_tag', 'sea_google_identity_script_tag', 10, 2);

add_filter('show_admin_bar', '__return_false');

function sea_default_menu(): void
{
    echo '<ul><li><a href="' . esc_url(home_url('/#khoa-hoc')) . '">Khóa học</a></li><li><a href="' . esc_url(home_url('/#lo-trinh')) . '">Lộ trình</a></li><li><a href="' . esc_url(home_url('/#cam-nhan')) . '">Cảm nhận</a></li><li><a href="' . esc_url(home_url('/#faq')) . '">FAQ</a></li></ul>';
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
    $trial_class = isset($_POST['sea_trial_class']) ? sanitize_text_field(wp_unslash($_POST['sea_trial_class'])) : '';
    $trial_date = isset($_POST['sea_trial_date']) ? sanitize_text_field(wp_unslash($_POST['sea_trial_date'])) : '';
    $message = isset($_POST['sea_message']) ? sanitize_textarea_field(wp_unslash($_POST['sea_message'])) : '';

    $phone_digits = preg_replace('/\D+/', '', $phone);
    $is_valid_phone = (bool) preg_match('/^(0[0-9]{9}|84[0-9]{9})$/', $phone_digits);
    $is_valid_email = $email && is_email($email);

    if (!$name || !$phone || !$email || !$course || !$trial_class || !$trial_date || !$is_valid_phone || !$is_valid_email || !sea_is_valid_trial_choice($trial_class, $trial_date)) {
        $error = 'missing';
        if ($phone && !$is_valid_phone) {
            $error = 'phone';
        } elseif ($email && !$is_valid_email) {
            $error = 'email';
        } elseif ($trial_class && $trial_date && !sea_is_valid_trial_choice($trial_class, $trial_date)) {
            $error = 'trial';
        }

        wp_safe_redirect(add_query_arg('sea_status', $error, wp_get_referer() ?: home_url('/')));
        exit;
    }

    $lead_id = wp_insert_post([
        'post_type' => 'lead',
        'post_status' => 'publish',
        'post_title' => sprintf('%s - %s', $name, current_time('d/m/Y H:i')),
    ]);

    if ($lead_id && !is_wp_error($lead_id)) {
        foreach (compact('name', 'phone', 'email', 'course', 'trial_class', 'trial_date', 'message') as $key => $value) {
            update_post_meta($lead_id, "_sea_{$key}", $value);
        }

        $admin_email = get_option('admin_email');
        wp_mail($admin_email, 'Lead tư vấn mới từ website', "Tên: {$name}\nĐiện thoại: {$phone}\nEmail: {$email}\nKhóa học: {$course}\nLớp học thử: {$trial_class}\nNgày học thử: {$trial_date}\nLời nhắn: {$message}");
        wp_safe_redirect(add_query_arg('sea_status', 'success', wp_get_referer() ?: home_url('/')));
        exit;
    }

    wp_safe_redirect(add_query_arg('sea_status', 'error', wp_get_referer() ?: home_url('/')));
    exit;
}
add_action('admin_post_nopriv_sea_lead', 'sea_handle_lead_form');
add_action('admin_post_sea_lead', 'sea_handle_lead_form');

function sea_trial_calendar_events(?int $timestamp = null): array
{
    $timestamp = $timestamp ?: current_time('timestamp');
    $learning_data = sea_demo_learning_data();
    $classes = $learning_data['classes'];
    $today = wp_date('Y-m-d', $timestamp);
    $month_start = strtotime(wp_date('Y-m-01', $timestamp));
    $days_in_month = (int) wp_date('t', $month_start);
    $month_end = strtotime('+' . ($days_in_month - 1) . ' days', $month_start);
    $rules = [
        'ielts-foundation' => ['trial_weekday' => 6, 'real_weekday' => 4],
        'toeic-650' => ['trial_weekday' => 1, 'real_weekday' => 3],
        'giao-tiep-mat-goc' => ['trial_weekday' => 3, 'real_weekday' => 7],
    ];

    $events = [];
    foreach ($rules as $class_key => $rule) {
        if (!isset($classes[$class_key])) {
            continue;
        }

        for ($day_offset = 0; $day_offset < $days_in_month; $day_offset++) {
            $day_ts = strtotime('+' . $day_offset . ' days', $month_start);
            if ($day_ts > $month_end) {
                break;
            }

            $date = wp_date('Y-m-d', $day_ts);
            $weekday = (int) wp_date('N', $day_ts);

            if ($weekday === $rule['trial_weekday'] && $date >= $today) {
                $events[] = [
                    'date' => $date,
                    'class_key' => $class_key,
                    'type' => 'trial',
                    'status' => $date === $today ? 'Đang diễn ra' : 'Sắp diễn ra',
                ];
            }

            if ($weekday === $rule['real_weekday']) {
                $events[] = [
                    'date' => $date,
                    'class_key' => $class_key,
                    'type' => 'real',
                    'status' => $date === $today ? 'Đang diễn ra' : ($date > $today ? 'Sắp diễn ra' : 'Đã diễn ra'),
                ];
            }
        }
    }

    usort($events, static fn($a, $b) => strcmp($a['date'] . $a['class_key'] . $a['type'], $b['date'] . $b['class_key'] . $b['type']));
    return $events;
}

function sea_trial_options(array $events): array
{
    $learning_data = sea_demo_learning_data();
    $classes = $learning_data['classes'];
    $options = ['by_class' => [], 'by_date' => [], 'dates' => [], 'class_course' => []];
    foreach ($classes as $class_key => $class) {
        $options['class_course'][$class_key] = $class['name'];
    }

    foreach ($events as $event) {
        if ('trial' !== $event['type']) {
            continue;
        }

        $options['by_class'][$event['class_key']][] = $event['date'];
        $options['by_date'][$event['date']][] = $event['class_key'];
        $options['dates'][] = $event['date'];
    }

    $options['dates'] = array_values(array_unique($options['dates']));
    foreach (['by_class', 'by_date'] as $group) {
        foreach ($options[$group] as $key => $values) {
            $options[$group][$key] = array_values(array_unique($values));
        }
    }

    return $options;
}

function sea_is_valid_trial_choice(string $class_key, string $date): bool
{
    $options = sea_trial_options(sea_trial_calendar_events());
    return isset($options['by_class'][$class_key]) && in_array($date, $options['by_class'][$class_key], true);
}

function sea_lead_form_shortcode(): string
{
    $status = isset($_GET['sea_status']) ? sanitize_key($_GET['sea_status']) : '';
    $courses = get_posts(['post_type' => 'course', 'numberposts' => 20, 'orderby' => 'menu_order title', 'order' => 'ASC']);
    $learning_data = sea_demo_learning_data();
    $trial_classes = $learning_data['classes'];
    $now = current_time('timestamp');
    $today = wp_date('Y-m-d', $now);
    $month_start = strtotime(wp_date('Y-m-01', $now));
    $days_in_month = (int) wp_date('t', $month_start);
    $first_weekday = (int) wp_date('N', $month_start);
    $trial_events = sea_trial_calendar_events($now);
    $trial_options = sea_trial_options($trial_events);
    $events_by_date = [];
    foreach ($trial_events as $event) {
        if (isset($trial_classes[$event['class_key']])) {
            $events_by_date[$event['date']][] = $event;
        }
    }
    $trial_dates = $trial_options['dates'];

    ob_start();
    ?>
    <form class="lead-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <?php if ('success' === $status) : ?>
            <p class="form-message success"><?php esc_html_e('Đã nhận thông tin. Tư vấn viên sẽ liên hệ trong thời gian sớm nhất.', 'sketch-english-academy'); ?></p>
        <?php elseif ($status) : ?>
            <p class="form-message error">
                <?php
                $messages = [
                    'phone' => 'Số điện thoại chưa đúng định dạng.',
                    'email' => 'Email chưa đúng định dạng.',
                    'trial' => 'Lớp học thử và ngày học thử không khớp với lịch hiện có.',
                    'missing' => 'Vui lòng điền đủ thông tin bắt buộc.',
                ];
                echo esc_html($messages[$status] ?? 'Vui lòng kiểm tra lại thông tin.');
                ?>
            </p>
        <?php endif; ?>
        <input type="hidden" name="action" value="sea_lead">
        <?php wp_nonce_field('sea_submit_lead', 'sea_lead_nonce'); ?>
        <script type="application/json" id="sea-trial-options"><?php echo wp_json_encode($trial_options, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
        <div class="lead-form-layout">
        <div class="trial-calendar">
            <div class="trial-calendar-head">
                <div>
                    <h3><?php echo esc_html('Lịch lớp tháng ' . wp_date('m/Y', $now)); ?></h3>
                </div>
            </div>
            <div class="calendar-legend">
                <span><i class="legend-dot trial"></i><?php esc_html_e('Học thử', 'sketch-english-academy'); ?></span>
                <span><i class="legend-dot real"></i><?php esc_html_e('Học thật', 'sketch-english-academy'); ?></span>
                <span><i class="legend-dot current"></i><?php esc_html_e('Đang diễn ra', 'sketch-english-academy'); ?></span>
            </div>
            <div class="calendar-grid">
                <?php foreach (['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'] as $weekday) : ?>
                    <div class="calendar-weekday"><?php echo esc_html($weekday); ?></div>
                <?php endforeach; ?>
                <?php for ($blank = 1; $blank < $first_weekday; $blank++) : ?>
                    <div class="calendar-day empty"></div>
                <?php endfor; ?>
                <?php for ($day = 1; $day <= $days_in_month; $day++) : ?>
                    <?php
                    $date = wp_date('Y-m-d', strtotime('+' . ($day - 1) . ' days', $month_start));
                    $day_events = $events_by_date[$date] ?? [];
                    ?>
                    <div class="calendar-day <?php echo $date === $today ? 'today' : ''; ?>">
                        <span class="calendar-date"><?php echo esc_html($day); ?></span>
                        <?php foreach ($day_events as $event) : ?>
                            <?php
                            $class = $trial_classes[$event['class_key']];
                            $capacity = (int) ($class['capacity'] ?? 12);
                            $current_size = count($class['students']);
                            $event_label = ('trial' === $event['type'] ? 'Học thử: ' : 'Học thật: ') . $class['name'];
                            $event_classes = 'calendar-event ' . ('trial' === $event['type'] ? 'trial' : 'real') . ('Đang diễn ra' === $event['status'] ? ' current' : '');
                            ?>
                            <?php if ('trial' === $event['type']) : ?>
                                <button class="<?php echo esc_attr($event_classes); ?>" type="button" data-trial-class="<?php echo esc_attr($event['class_key']); ?>" data-trial-date="<?php echo esc_attr($date); ?>" title="<?php echo esc_attr($class['name'] . ' - ' . $event['status']); ?>">
                                    <strong><?php echo esc_html($event_label); ?></strong>
                                    <span><?php echo esc_html($event['status']); ?> · <?php echo esc_html($current_size); ?>/<?php echo esc_html($capacity); ?> học viên</span>
                                </button>
                            <?php else : ?>
                                <div class="<?php echo esc_attr($event_classes); ?>" title="<?php echo esc_attr($class['name'] . ' - ' . $class['room']); ?>">
                                    <strong><?php echo esc_html($event_label); ?></strong>
                                    <span><?php echo esc_html($event['status']); ?></span>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
        <div class="lead-fields">
        <div class="form-grid">
            <p class="form-field"><label for="sea_name"><?php esc_html_e('Họ tên', 'sketch-english-academy'); ?> <span class="required-mark">*</span></label><input id="sea_name" name="sea_name" required></p>
            <p class="form-field"><label for="sea_phone"><?php esc_html_e('Số điện thoại', 'sketch-english-academy'); ?> <span class="required-mark">*</span></label><input id="sea_phone" name="sea_phone" inputmode="tel" pattern="(0|\+?84)[0-9 .-]{8,13}" required></p>
            <p class="form-field"><label for="sea_email"><?php esc_html_e('Email', 'sketch-english-academy'); ?> <span class="required-mark">*</span></label><input id="sea_email" type="email" name="sea_email" required></p>
            <p class="form-field"><label for="sea_course"><?php esc_html_e('Khóa quan tâm', 'sketch-english-academy'); ?> <span class="required-mark">*</span></label><select id="sea_course" name="sea_course" required><option value=""><?php esc_html_e('Chọn khóa học', 'sketch-english-academy'); ?></option><?php foreach ($courses as $course) : ?><option value="<?php echo esc_attr(get_the_title($course)); ?>"><?php echo esc_html(get_the_title($course)); ?></option><?php endforeach; ?></select></p>
            <p class="form-field"><label for="sea_trial_class"><?php esc_html_e('Lớp học thử', 'sketch-english-academy'); ?> <span class="required-mark">*</span></label><select id="sea_trial_class" name="sea_trial_class" required><option value=""><?php esc_html_e('Chọn lớp học thử', 'sketch-english-academy'); ?></option><?php foreach ($trial_classes as $class_key => $class) : ?><?php $capacity = (int) ($class['capacity'] ?? 12); ?><option value="<?php echo esc_attr($class_key); ?>" data-course-title="<?php echo esc_attr($class['name']); ?>"><?php echo esc_html($class['name'] . ' - ' . count($class['students']) . '/' . $capacity . ' học viên'); ?></option><?php endforeach; ?></select></p>
            <p class="form-field"><label for="sea_trial_date"><?php esc_html_e('Ngày muốn học thử', 'sketch-english-academy'); ?> <span class="required-mark">*</span></label><select id="sea_trial_date" name="sea_trial_date" required><option value=""><?php esc_html_e('Chọn ngày học thử', 'sketch-english-academy'); ?></option><?php foreach ($trial_dates as $date) : ?><option value="<?php echo esc_attr($date); ?>"><?php echo esc_html(wp_date('d/m/Y', strtotime($date))); ?></option><?php endforeach; ?></select></p>
            <p class="form-field full"><label for="sea_message"><?php esc_html_e('Mục tiêu học tập', 'sketch-english-academy'); ?></label><textarea id="sea_message" name="sea_message"></textarea></p>
        </div>
        <button type="submit"><?php esc_html_e('Nhận tư vấn miễn phí', 'sketch-english-academy'); ?></button>
        </div>
        </div>
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

function sea_add_demo_roles(): void
{
    add_role('sea_teacher', 'Giáo viên', [
        'read' => true,
        'upload_files' => true,
        'sea_grade_students' => true,
        'sea_manage_classes' => true,
    ]);

    add_role('sea_student', 'Học viên', [
        'read' => true,
        'sea_view_lessons' => true,
    ]);

    $admin = get_role('administrator');
    if ($admin) {
        $admin->add_cap('sea_grade_students');
        $admin->add_cap('sea_manage_classes');
        $admin->add_cap('sea_view_lessons');
    }
}
add_action('after_switch_theme', 'sea_add_demo_roles');
add_action('init', 'sea_add_demo_roles');

function sea_demo_learning_data(): array
{
    $defaults = [
        'class' => [
            'name' => 'IELTS Foundation 4.0-5.5',
            'teacher' => 'Cô Mai Anh',
            'schedule' => 'Thứ 3 - Thứ 5, 19:30',
            'room' => 'Phòng Speaking Lab 2',
            'progress' => 42,
        ],
        'students' => [
            ['name' => 'Minh Anh', 'email' => 'minhanh@example.local', 'attendance' => '9/10', 'progress' => 46],
            ['name' => 'Quốc Huy', 'email' => 'quochuy@example.local', 'attendance' => '8/10', 'progress' => 38],
            ['name' => 'Thu Trang', 'email' => 'thutrang@example.local', 'attendance' => '10/10', 'progress' => 55],
        ],
        'lessons' => [
            ['title' => 'Phát âm nguyên âm dài và ngắn', 'status' => 'Đã học', 'duration' => '45 phút'],
            ['title' => 'Từ vựng chủ đề Work & Study', 'status' => 'Đang học', 'duration' => '60 phút'],
            ['title' => 'Writing Task 1: mô tả biểu đồ', 'status' => 'Sắp mở', 'duration' => '75 phút'],
        ],
        'submissions' => [
            'minhanh' => [
                'student' => 'Minh Anh',
                'assignment' => 'Speaking: Describe your study routine',
                'submitted' => '05/06/2026 20:15',
                'grade' => '8.0',
                'comment' => 'Phát âm rõ hơn, cần nối âm tự nhiên hơn ở câu dài.',
                'status' => 'Đã chấm',
            ],
            'quochuy' => [
                'student' => 'Quốc Huy',
                'assignment' => 'Vocabulary Quiz: Work & Study',
                'submitted' => '05/06/2026 19:42',
                'grade' => '7.0',
                'comment' => 'Nắm từ vựng tốt, cần ôn lại collocation với make/do.',
                'status' => 'Đã chấm',
            ],
            'thutrang' => [
                'student' => 'Thu Trang',
                'assignment' => 'Writing Task 1: Line chart intro',
                'submitted' => '05/06/2026 21:05',
                'grade' => '',
                'comment' => '',
                'status' => 'Chờ chấm',
            ],
        ],
    ];

    $data = get_option('sea_demo_learning_data', []);
    $data = wp_parse_args(is_array($data) ? $data : [], $defaults);

    if (empty($data['classes'])) {
        $data['classes'] = sea_demo_classes_from_legacy_data($data);
    }

    return $data;
}

function sea_save_demo_learning_data(array $data): void
{
    update_option('sea_demo_learning_data', $data, false);
}

function sea_demo_classes_from_legacy_data(array $data): array
{
    return [
        'ielts-foundation' => [
            'name' => $data['class']['name'],
            'teacher' => $data['class']['teacher'],
            'schedule' => $data['class']['schedule'],
            'room' => $data['class']['room'],
            'progress' => $data['class']['progress'],
            'capacity' => 12,
            'level' => 'Foundation',
            'status' => 'Đang học',
            'next_session' => 'Thứ 5, 19:30',
            'students' => $data['students'],
            'lessons' => $data['lessons'],
            'submissions' => $data['submissions'],
        ],
        'toeic-650' => [
            'name' => 'TOEIC Cấp Tốc 650+',
            'teacher' => 'Cô Mai Anh',
            'schedule' => 'Thứ 2 - Thứ 4, 18:00',
            'room' => 'Phòng Listening Lab 1',
            'progress' => 68,
            'capacity' => 16,
            'level' => 'Intermediate',
            'status' => 'Đang học',
            'next_session' => 'Thứ 4, 18:00',
            'students' => [
                ['name' => 'Hoàng Nam', 'email' => 'hoangnam@example.local', 'attendance' => '11/12', 'progress' => 72],
                ['name' => 'Bảo Trân', 'email' => 'baotran@example.local', 'attendance' => '10/12', 'progress' => 64],
                ['name' => 'Gia Bảo', 'email' => 'giabao@example.local', 'attendance' => '9/12', 'progress' => 58],
                ['name' => 'Hà My', 'email' => 'hamy@example.local', 'attendance' => '12/12', 'progress' => 76],
            ],
            'lessons' => [
                ['title' => 'Part 3: hội thoại nơi công sở', 'status' => 'Đang học', 'duration' => '60 phút'],
                ['title' => 'Part 5: dạng từ và thì động từ', 'status' => 'Đã học', 'duration' => '45 phút'],
                ['title' => 'Mini test 60 câu', 'status' => 'Sắp mở', 'duration' => '75 phút'],
            ],
            'submissions' => [
                'hoangnam' => ['student' => 'Hoàng Nam', 'assignment' => 'Mini test Part 5', 'submitted' => '05/06/2026 18:30', 'grade' => '8.0', 'comment' => 'Tốc độ làm bài tốt, cần kiểm tra kỹ câu từ loại.', 'status' => 'Đã chấm'],
                'baotran' => ['student' => 'Bảo Trân', 'assignment' => 'Listening Part 3 transcript', 'submitted' => '05/06/2026 19:10', 'grade' => '', 'comment' => '', 'status' => 'Chờ chấm'],
            ],
        ],
        'giao-tiep-mat-goc' => [
            'name' => 'Giao tiếp Mất Gốc',
            'teacher' => 'Cô Mai Anh',
            'schedule' => 'Thứ 3 - Thứ 6, 20:00',
            'room' => 'Phòng Speaking Lab 3',
            'progress' => 25,
            'capacity' => 10,
            'level' => 'Beginner',
            'status' => 'Mới khai giảng',
            'next_session' => 'Thứ 6, 20:00',
            'students' => [
                ['name' => 'Lan Chi', 'email' => 'lanchi@example.local', 'attendance' => '4/4', 'progress' => 28],
                ['name' => 'Tuấn Kiệt', 'email' => 'tuankiet@example.local', 'attendance' => '3/4', 'progress' => 22],
                ['name' => 'Ngọc Mai', 'email' => 'ngocmai@example.local', 'attendance' => '4/4', 'progress' => 31],
            ],
            'lessons' => [
                ['title' => 'Chào hỏi và giới thiệu bản thân', 'status' => 'Đã học', 'duration' => '45 phút'],
                ['title' => 'Phát âm âm cuối /s/ và /t/', 'status' => 'Đang học', 'duration' => '60 phút'],
                ['title' => 'Role-play: hỏi đường', 'status' => 'Sắp mở', 'duration' => '45 phút'],
            ],
            'submissions' => [
                'lanchi' => ['student' => 'Lan Chi', 'assignment' => 'Recording: self introduction', 'submitted' => '05/06/2026 21:00', 'grade' => '7.5', 'comment' => 'Giọng rõ, cần luyện nối âm ở câu dài.', 'status' => 'Đã chấm'],
                'tuankiet' => ['student' => 'Tuấn Kiệt', 'assignment' => 'Pronunciation drill: final sounds', 'submitted' => '05/06/2026 21:20', 'grade' => '', 'comment' => '', 'status' => 'Chờ chấm'],
            ],
        ],
    ];
}

function sea_seed_role_demo(): void
{
    sea_add_demo_roles();
    update_option('timezone_string', 'Asia/Bangkok');

    $users = [
        'giaovien' => ['password' => 'teacher12345', 'email' => 'giaovien@example.local', 'role' => 'sea_teacher', 'name' => 'Cô Mai Anh'],
        'hocvien' => ['password' => 'student12345', 'email' => 'hocvien@example.local', 'role' => 'sea_student', 'name' => 'Minh Anh'],
    ];

    foreach ($users as $login => $user) {
        $user_id = username_exists($login);
        if (!$user_id) {
            $user_id = wp_create_user($login, $user['password'], $user['email']);
        } else {
            wp_set_password($user['password'], $user_id);
        }

        if (!is_wp_error($user_id)) {
            wp_update_user([
                'ID' => $user_id,
                'display_name' => $user['name'],
                'first_name' => $user['name'],
                'role' => $user['role'],
            ]);
        }
    }

    if (!get_option('sea_demo_learning_data')) {
        sea_save_demo_learning_data(sea_demo_learning_data());
    }

    $pages = [
        'dang-nhap' => ['Đăng nhập', '[sea_role_login]'],
        'bang-dieu-khien-admin' => ['Bảng điều khiển admin', '[sea_admin_dashboard]'],
        'bang-dieu-khien-giao-vien' => ['Bảng điều khiển giáo viên', '[sea_teacher_dashboard]'],
        'lop-hoc-cua-toi' => ['Lớp học của tôi', '[sea_student_dashboard]'],
    ];

    foreach ($pages as $slug => $page) {
        $existing = get_page_by_path($slug);
        $args = [
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_title' => $page[0],
            'post_name' => $slug,
            'post_content' => $page[1],
        ];

        if ($existing) {
            $args['ID'] = $existing->ID;
            wp_update_post($args);
        } else {
            wp_insert_post($args);
        }
    }

    flush_rewrite_rules();
}
add_action('after_switch_theme', 'sea_seed_role_demo');

function sea_role_entry_url(?WP_User $user = null): string
{
    $user = $user ?: wp_get_current_user();
    $roles = $user instanceof WP_User ? (array) $user->roles : [];

    if (in_array('administrator', $roles, true)) {
        return home_url('/bang-dieu-khien-admin/');
    }

    if (in_array('sea_student', $roles, true)) {
        return home_url('/lop-hoc-cua-toi/');
    }

    if (in_array('sea_teacher', $roles, true)) {
        return home_url('/bang-dieu-khien-giao-vien/');
    }

    return home_url('/dang-nhap/');
}

function sea_is_learning_dashboard_page(): bool
{
    return is_page(['bang-dieu-khien-admin', 'bang-dieu-khien-giao-vien', 'lop-hoc-cua-toi']);
}

function sea_is_private_learning_view(): bool
{
    return is_user_logged_in() && (sea_is_learning_dashboard_page() || is_front_page());
}

function sea_logout_url(): string
{
    return wp_logout_url(home_url('/dang-nhap/'));
}

function sea_set_login_test_cookie(): void
{
    if (is_page('dang-nhap') && !is_user_logged_in()) {
        setcookie(TEST_COOKIE, 'WP Cookie check', 0, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
    }
}
add_action('template_redirect', 'sea_set_login_test_cookie');

function sea_role_login_redirect(string $redirect_to, string $requested_redirect_to, $user): string
{
    if ($user instanceof WP_User) {
        $requested_path = $requested_redirect_to ? (string) wp_parse_url($requested_redirect_to, PHP_URL_PATH) : '';
        $admin_path = (string) wp_parse_url(admin_url(), PHP_URL_PATH);

        if (user_can($user, 'manage_options') && $requested_path && 0 === strpos($requested_path, $admin_path)) {
            return $requested_redirect_to;
        }

        return sea_role_entry_url($user);
    }

    return $redirect_to;
}
add_filter('login_redirect', 'sea_role_login_redirect', 10, 3);

function sea_validate_role_login_robot($user, string $username, string $password)
{
    if (!isset($_POST['sea_role_login'])) {
        return $user;
    }

    $recaptcha = sea_verify_recaptcha_response();
    if (is_wp_error($recaptcha)) {
        return $recaptcha;
    }

    return $user;
}
add_filter('authenticate', 'sea_validate_role_login_robot', 20, 3);

function sea_verify_recaptcha_response()
{
    $token = isset($_POST['g-recaptcha-response']) ? sanitize_textarea_field(wp_unslash($_POST['g-recaptcha-response'])) : '';
    if (!$token) {
        return new WP_Error('sea_recaptcha_missing', __('Vui lòng xác minh reCAPTCHA trước khi gửi.', 'sketch-english-academy'));
    }

    $remote_ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
    $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
        'timeout' => 10,
        'body' => [
            'secret' => SEA_RECAPTCHA_SECRET_KEY,
            'response' => $token,
            'remoteip' => $remote_ip,
        ],
    ]);

    if (is_wp_error($response)) {
        return new WP_Error('sea_recaptcha_unavailable', __('Không thể kết nối reCAPTCHA. Vui lòng thử lại.', 'sketch-english-academy'));
    }

    $payload = json_decode(wp_remote_retrieve_body($response), true);
    if (!is_array($payload) || empty($payload['success'])) {
        return new WP_Error('sea_recaptcha_failed', __('reCAPTCHA chưa hợp lệ. Vui lòng thử lại.', 'sketch-english-academy'));
    }

    return true;
}

function sea_role_login_robot_fields(string $content): string
{
    if (empty($GLOBALS['sea_render_role_login_form'])) {
        return $content;
    }

    return $content . '<input type="hidden" name="sea_role_login" value="1"><div class="recaptcha-wrap"><div class="g-recaptcha" data-sitekey="' . esc_attr(SEA_RECAPTCHA_SITE_KEY) . '"></div></div>';
}
add_filter('login_form_middle', 'sea_role_login_robot_fields');

function sea_google_login_url(): string
{
    return admin_url('admin-post.php?action=sea_google_login');
}

function sea_verify_google_id_token(string $credential)
{
    $response = wp_remote_get(add_query_arg('id_token', $credential, 'https://oauth2.googleapis.com/tokeninfo'), [
        'timeout' => 10,
    ]);

    if (is_wp_error($response)) {
        return $response;
    }

    if (200 !== wp_remote_retrieve_response_code($response)) {
        return new WP_Error('sea_google_token_invalid', __('Google token không hợp lệ.', 'sketch-english-academy'));
    }

    $payload = json_decode(wp_remote_retrieve_body($response), true);
    if (!is_array($payload)) {
        return new WP_Error('sea_google_token_invalid_json', __('Không đọc được phản hồi xác thực từ Google.', 'sketch-english-academy'));
    }

    $issuer = $payload['iss'] ?? '';
    $audience = $payload['aud'] ?? '';
    $email = sanitize_email($payload['email'] ?? '');
    $email_verified = filter_var($payload['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN);
    $subject = sanitize_text_field($payload['sub'] ?? '');

    if (!in_array($issuer, ['accounts.google.com', 'https://accounts.google.com'], true) || SEA_GOOGLE_CLIENT_ID !== $audience || !$subject || !$email || !$email_verified) {
        return new WP_Error('sea_google_token_rejected', __('Tài khoản Google chưa được xác minh hoặc không đúng Client ID.', 'sketch-english-academy'));
    }

    return [
        'sub' => $subject,
        'email' => $email,
        'name' => sanitize_text_field($payload['name'] ?? ''),
        'given_name' => sanitize_text_field($payload['given_name'] ?? ''),
        'picture' => esc_url_raw($payload['picture'] ?? ''),
    ];
}

function sea_verify_google_access_token(string $access_token)
{
    $token_response = wp_remote_get(add_query_arg('access_token', $access_token, 'https://oauth2.googleapis.com/tokeninfo'), [
        'timeout' => 10,
    ]);

    if (is_wp_error($token_response)) {
        return $token_response;
    }

    if (200 !== wp_remote_retrieve_response_code($token_response)) {
        return new WP_Error('sea_google_access_token_invalid', __('Google access token không hợp lệ.', 'sketch-english-academy'));
    }

    $token_payload = json_decode(wp_remote_retrieve_body($token_response), true);
    if (!is_array($token_payload) || SEA_GOOGLE_CLIENT_ID !== ($token_payload['aud'] ?? '')) {
        return new WP_Error('sea_google_access_token_rejected', __('Google token không đúng Client ID.', 'sketch-english-academy'));
    }

    $userinfo_response = wp_remote_get('https://www.googleapis.com/oauth2/v3/userinfo', [
        'timeout' => 10,
        'headers' => [
            'Authorization' => 'Bearer ' . $access_token,
        ],
    ]);

    if (is_wp_error($userinfo_response)) {
        return $userinfo_response;
    }

    if (200 !== wp_remote_retrieve_response_code($userinfo_response)) {
        return new WP_Error('sea_google_userinfo_invalid', __('Không lấy được thông tin tài khoản Google.', 'sketch-english-academy'));
    }

    $profile = json_decode(wp_remote_retrieve_body($userinfo_response), true);
    if (!is_array($profile)) {
        return new WP_Error('sea_google_userinfo_json', __('Không đọc được thông tin tài khoản Google.', 'sketch-english-academy'));
    }

    $email = sanitize_email($profile['email'] ?? '');
    $email_verified = filter_var($profile['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN);
    $subject = sanitize_text_field($profile['sub'] ?? '');

    if (!$subject || !$email || !$email_verified) {
        return new WP_Error('sea_google_userinfo_rejected', __('Tài khoản Google chưa xác minh email.', 'sketch-english-academy'));
    }

    return [
        'sub' => $subject,
        'email' => $email,
        'name' => sanitize_text_field($profile['name'] ?? ''),
        'given_name' => sanitize_text_field($profile['given_name'] ?? ''),
        'picture' => esc_url_raw($profile['picture'] ?? ''),
    ];
}

function sea_find_user_by_google_sub(string $google_sub): ?WP_User
{
    $users = get_users([
        'number' => 1,
        'meta_key' => '_sea_google_sub',
        'meta_value' => $google_sub,
        'fields' => 'all',
    ]);

    return $users ? $users[0] : null;
}

function sea_get_or_create_google_user(array $profile)
{
    $user = sea_find_user_by_google_sub($profile['sub']);
    if ($user instanceof WP_User) {
        return $user;
    }

    $user = get_user_by('email', $profile['email']);
    if (!$user) {
        $email_parts = explode('@', $profile['email']);
        $base_login = sanitize_user($email_parts[0] ?? '', true) ?: 'google_user';
        $login = $base_login;
        $suffix = 1;
        while (username_exists($login)) {
            $login = $base_login . $suffix;
            $suffix++;
        }

        $user_id = wp_insert_user([
            'user_login' => $login,
            'user_email' => $profile['email'],
            'user_pass' => wp_generate_password(32, true, true),
            'display_name' => $profile['name'] ?: $profile['email'],
            'first_name' => $profile['given_name'],
            'role' => 'sea_student',
        ]);

        if (is_wp_error($user_id)) {
            return $user_id;
        }

        $user = get_user_by('id', $user_id);
    }

    update_user_meta($user->ID, '_sea_google_sub', $profile['sub']);
    update_user_meta($user->ID, '_sea_google_picture', $profile['picture']);

    if (!in_array('administrator', (array) $user->roles, true) && !in_array('sea_teacher', (array) $user->roles, true) && !in_array('sea_student', (array) $user->roles, true)) {
        $user->set_role('sea_student');
    }

    return $user;
}

function sea_handle_google_login(): void
{
    $redirect = home_url('/dang-nhap/');
    $cookie_csrf = isset($_COOKIE['g_csrf_token']) ? sanitize_text_field(wp_unslash($_COOKIE['g_csrf_token'])) : '';
    $post_csrf = isset($_POST['g_csrf_token']) ? sanitize_text_field(wp_unslash($_POST['g_csrf_token'])) : '';
    $credential = isset($_POST['credential']) ? sanitize_textarea_field(wp_unslash($_POST['credential'])) : '';
    $access_token = isset($_POST['access_token']) ? sanitize_textarea_field(wp_unslash($_POST['access_token'])) : '';
    $nonce = isset($_POST['sea_google_nonce']) ? sanitize_text_field(wp_unslash($_POST['sea_google_nonce'])) : '';
    $has_valid_google_csrf = $cookie_csrf && $post_csrf && hash_equals($cookie_csrf, $post_csrf);
    $has_valid_wp_nonce = $nonce && wp_verify_nonce($nonce, 'sea_google_login');

    if ((!$has_valid_google_csrf && !$has_valid_wp_nonce) || (!$credential && !$access_token)) {
        wp_safe_redirect(add_query_arg('google_login', 'csrf', $redirect));
        exit;
    }

    $profile = $credential ? sea_verify_google_id_token($credential) : sea_verify_google_access_token($access_token);
    if (is_wp_error($profile)) {
        wp_safe_redirect(add_query_arg('google_login', 'invalid', $redirect));
        exit;
    }

    $user = sea_get_or_create_google_user($profile);
    if (is_wp_error($user) || !$user instanceof WP_User) {
        wp_safe_redirect(add_query_arg('google_login', 'user', $redirect));
        exit;
    }

    wp_set_current_user($user->ID);
    wp_set_auth_cookie($user->ID, true, is_ssl());
    do_action('wp_login', $user->user_login, $user);

    wp_safe_redirect(sea_role_entry_url($user));
    exit;
}
add_action('admin_post_nopriv_sea_google_login', 'sea_handle_google_login');
add_action('admin_post_sea_google_login', 'sea_handle_google_login');

function sea_handle_student_registration(): void
{
    $redirect = home_url('/dang-nhap/');
    if (!isset($_POST['sea_register_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['sea_register_nonce'])), 'sea_register_student')) {
        wp_safe_redirect(add_query_arg('register_status', 'csrf', $redirect));
        exit;
    }

    $name = isset($_POST['sea_register_name']) ? sanitize_text_field(wp_unslash($_POST['sea_register_name'])) : '';
    $email = isset($_POST['sea_register_email']) ? sanitize_email(wp_unslash($_POST['sea_register_email'])) : '';
    $phone = isset($_POST['sea_register_phone']) ? sanitize_text_field(wp_unslash($_POST['sea_register_phone'])) : '';
    $password = isset($_POST['sea_register_password']) ? (string) wp_unslash($_POST['sea_register_password']) : '';
    $confirm = isset($_POST['sea_register_confirm']) ? (string) wp_unslash($_POST['sea_register_confirm']) : '';
    $phone_digits = preg_replace('/\D+/', '', $phone);
    $recaptcha = sea_verify_recaptcha_response();

    if (is_wp_error($recaptcha)) {
        wp_safe_redirect(add_query_arg('register_status', 'recaptcha', $redirect));
        exit;
    }

    if (!$name || !$email || !is_email($email) || !$phone || !preg_match('/^(0[0-9]{9}|84[0-9]{9})$/', $phone_digits) || strlen($password) < 8 || $password !== $confirm) {
        wp_safe_redirect(add_query_arg('register_status', 'invalid', $redirect));
        exit;
    }

    if (email_exists($email)) {
        wp_safe_redirect(add_query_arg('register_status', 'exists', $redirect));
        exit;
    }

    $email_parts = explode('@', $email);
    $base_login = sanitize_user($email_parts[0] ?? '', true) ?: 'hocvien';
    $login = $base_login;
    $suffix = 1;
    while (username_exists($login)) {
        $login = $base_login . $suffix;
        $suffix++;
    }

    $user_id = wp_insert_user([
        'user_login' => $login,
        'user_email' => $email,
        'user_pass' => $password,
        'display_name' => $name,
        'role' => 'sea_student',
    ]);

    if (is_wp_error($user_id)) {
        wp_safe_redirect(add_query_arg('register_status', 'error', $redirect));
        exit;
    }

    update_user_meta($user_id, '_sea_phone', $phone);
    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id, true, is_ssl());
    do_action('wp_login', $login, get_user_by('id', $user_id));

    wp_safe_redirect(sea_role_entry_url(get_user_by('id', $user_id)));
    exit;
}
add_action('admin_post_nopriv_sea_register_student', 'sea_handle_student_registration');

function sea_is_teacher(): bool
{
    $user = wp_get_current_user();
    return in_array('sea_teacher', (array) $user->roles, true);
}

function sea_is_student(): bool
{
    $user = wp_get_current_user();
    return in_array('sea_student', (array) $user->roles, true);
}

function sea_demo_login_box(string $role): string
{
    $is_teacher = 'teacher' === $role;
    $title = 'admin' === $role ? 'Đăng nhập admin' : ($is_teacher ? 'Đăng nhập giáo viên' : 'Đăng nhập học viên');

    ob_start();
    ?>
    <div class="role-panel">
        <h2><?php echo esc_html($title); ?></h2>
        <p><?php esc_html_e('Vui lòng đăng nhập bằng tài khoản đã được cấp để truy cập khu vực học tập.', 'sketch-english-academy'); ?></p>
        <?php
        wp_login_form([
            'redirect' => esc_url(get_permalink()),
            'label_username' => 'Tên đăng nhập',
            'label_password' => 'Mật khẩu',
            'label_log_in' => 'Đăng nhập',
            'remember' => true,
        ]);
        ?>
    </div>
    <?php
    return ob_get_clean();
}

function sea_role_login_shortcode(): string
{
    $google_status = isset($_GET['google_login']) ? sanitize_key($_GET['google_login']) : '';
    $register_status = isset($_GET['register_status']) ? sanitize_key($_GET['register_status']) : '';

    if (is_user_logged_in()) {
        $user = wp_get_current_user();

        ob_start();
        ?>
        <div class="role-panel">
            <span class="eyebrow">Đăng nhập</span>
            <h1>Bạn đã đăng nhập</h1>
            <p>Website đã nhận diện tài khoản <strong><?php echo esc_html($user->display_name ?: $user->user_login); ?></strong>. Bấm nút bên dưới để vào giao diện đúng với vai trò hiện tại.</p>
            <div class="hero-actions">
                <a class="button" href="<?php echo esc_url(sea_role_entry_url($user)); ?>">Vào dashboard</a>
                <a class="button secondary" href="<?php echo esc_url(sea_logout_url()); ?>">Đăng xuất</a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    ob_start();
    ?>
    <section class="role-dashboard">
        <div class="login-layout" data-auth-root data-initial-view="<?php echo $register_status ? 'register' : 'login'; ?>">
            <div class="role-panel auth-panel">
                <form id="sea-google-login-form" action="<?php echo esc_url(sea_google_login_url()); ?>" method="post" hidden>
                    <input type="hidden" name="sea_google_nonce" value="<?php echo esc_attr(wp_create_nonce('sea_google_login')); ?>">
                    <input type="hidden" id="sea_google_access_token" name="access_token" value="">
                    <input type="hidden" id="sea_google_credential" name="credential" value="">
                </form>
                <div id="g_id_onload"
                    data-client_id="<?php echo esc_attr(SEA_GOOGLE_CLIENT_ID); ?>"
                    data-callback="seaHandleGoogleCredential"
                    data-auto_prompt="false">
                </div>

                <div class="auth-view login-panel" data-auth-panel="login">
                    <h2>Đăng nhập</h2>
                    <?php if ($google_status) : ?>
                        <p class="form-message error">
                            <?php
                            $messages = [
                                'csrf' => 'Phiên đăng nhập Google hết hạn. Vui lòng thử lại.',
                                'invalid' => 'Google chưa xác minh được tài khoản này hoặc Client ID chưa khớp.',
                                'user' => 'Không thể tạo tài khoản từ Google. Vui lòng thử lại sau.',
                            ];
                            echo esc_html($messages[$google_status] ?? 'Không thể đăng nhập bằng Google.');
                            ?>
                        </p>
                    <?php endif; ?>
                    <?php
                    $GLOBALS['sea_render_role_login_form'] = true;
                    wp_login_form([
                        'redirect' => esc_url(sea_role_entry_url()),
                        'label_username' => 'Tên đăng nhập',
                        'label_password' => 'Mật khẩu',
                        'label_remember' => 'Ghi nhớ đăng nhập',
                        'label_log_in' => 'Đăng nhập',
                        'remember' => true,
                    ]);
                    unset($GLOBALS['sea_render_role_login_form']);
                    ?>
                    <div class="auth-actions">
                        <button class="button google-login-button" type="button" data-google-login data-google-client-id="<?php echo esc_attr(SEA_GOOGLE_CLIENT_ID); ?>">
                            <span class="google-mark">G</span>
                            <span>Đăng nhập bằng Google</span>
                        </button>
                        <button class="button secondary register-jump" type="button" data-auth-switch="register">Đăng ký tài khoản học viên</button>
                    </div>
                </div>

                <div class="auth-view register-panel" data-auth-panel="register" hidden>
                    <span class="eyebrow">Đăng ký</span>
                    <h2>Tạo tài khoản học viên</h2>
                    <?php if ($register_status) : ?>
                        <p class="form-message error">
                            <?php
                            $messages = [
                                'csrf' => 'Phiên đăng ký hết hạn. Vui lòng thử lại.',
                            'invalid' => 'Vui lòng kiểm tra họ tên, email, số điện thoại và mật khẩu.',
                            'exists' => 'Email này đã có tài khoản. Hãy đăng nhập hoặc dùng Google.',
                            'recaptcha' => 'Vui lòng xác minh reCAPTCHA trước khi đăng ký.',
                            'error' => 'Không thể tạo tài khoản lúc này.',
                        ];
                            echo esc_html($messages[$register_status] ?? 'Không thể đăng ký tài khoản.');
                            ?>
                        </p>
                    <?php endif; ?>
                    <form class="student-register-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                        <input type="hidden" name="action" value="sea_register_student">
                        <?php wp_nonce_field('sea_register_student', 'sea_register_nonce'); ?>
                        <div class="form-grid">
                            <p class="form-field"><label for="sea_register_name">Họ tên <span class="required-mark">*</span></label><input id="sea_register_name" name="sea_register_name" required></p>
                            <p class="form-field"><label for="sea_register_phone">Số điện thoại <span class="required-mark">*</span></label><input id="sea_register_phone" name="sea_register_phone" inputmode="tel" required></p>
                            <p class="form-field full"><label for="sea_register_email">Email <span class="required-mark">*</span></label><input id="sea_register_email" type="email" name="sea_register_email" required></p>
                            <p class="form-field"><label for="sea_register_password">Mật khẩu <span class="required-mark">*</span></label><input id="sea_register_password" type="password" name="sea_register_password" minlength="8" required></p>
                            <p class="form-field"><label for="sea_register_confirm">Nhập lại mật khẩu <span class="required-mark">*</span></label><input id="sea_register_confirm" type="password" name="sea_register_confirm" minlength="8" required></p>
                        </div>
                        <div class="recaptcha-wrap"><div class="g-recaptcha" data-sitekey="<?php echo esc_attr(SEA_RECAPTCHA_SITE_KEY); ?>"></div></div>
                        <div class="auth-actions">
                            <button type="submit">Đăng ký tài khoản</button>
                            <button class="button secondary" type="button" data-auth-switch="login">Quay lại đăng nhập</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode('sea_role_login', 'sea_role_login_shortcode');

function sea_admin_dashboard_shortcode(): string
{
    if (!is_user_logged_in()) {
        return sea_demo_login_box('admin');
    }

    if (!current_user_can('manage_options')) {
        return '<div class="role-panel"><h2>Không đúng vai trò</h2><p>Tài khoản hiện tại không có quyền quản trị hệ thống.</p><a class="button" href="' . esc_url(sea_logout_url()) . '">Đăng xuất</a></div>';
    }

    $data = sea_demo_learning_data();
    $classes = $data['classes'];

    if (isset($_POST['sea_assign_lead_nonce'], $_POST['lead_id'], $_POST['class_key']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['sea_assign_lead_nonce'])), 'sea_assign_lead')) {
        $lead_id = absint($_POST['lead_id']);
        $class_key = sanitize_key(wp_unslash($_POST['class_key']));

        if ($lead_id && isset($data['classes'][$class_key])) {
            $lead_name = get_post_meta($lead_id, '_sea_name', true) ?: get_the_title($lead_id);
            $lead_email = get_post_meta($lead_id, '_sea_email', true);
            $lead_phone = get_post_meta($lead_id, '_sea_phone', true);
            $capacity = (int) ($data['classes'][$class_key]['capacity'] ?? 12);
            $current_size = count($data['classes'][$class_key]['students']);

            if ($current_size < $capacity) {
                $exists = false;
                foreach ($data['classes'][$class_key]['students'] as $student) {
                    if (($lead_email && $student['email'] === $lead_email) || $student['name'] === $lead_name) {
                        $exists = true;
                        break;
                    }
                }

                if (!$exists) {
                    $data['classes'][$class_key]['students'][] = [
                        'name' => $lead_name,
                        'email' => $lead_email ?: $lead_phone,
                        'attendance' => '0/0',
                        'progress' => 0,
                    ];
                    sea_save_demo_learning_data($data);
                    update_post_meta($lead_id, '_sea_status', 'Đã xếp lớp');
                    update_post_meta($lead_id, '_sea_assigned_class', $class_key);
                }
            }
        }

        $classes = $data['classes'];
    }

    $student_count = array_sum(array_map(static fn($class) => count($class['students']), $classes));
    $pending_count = array_sum(array_map(static fn($class) => count(array_filter($class['submissions'], static fn($submission) => 'Chờ chấm' === $submission['status'])), $classes));
    $leads = get_posts(['post_type' => 'lead', 'numberposts' => 20, 'orderby' => 'date', 'order' => 'DESC']);
    $lead_count = count($leads);
    $new_lead_count = count(array_filter($leads, static fn($lead) => 'Đã xếp lớp' !== get_post_meta($lead->ID, '_sea_status', true)));
    $course_count = wp_count_posts('course')->publish ?? 0;

    ob_start();
    ?>
    <section class="role-dashboard">
        <div class="role-hero">
            <div>
                <span class="eyebrow">Admin</span>
                <h1>Bảng điều khiển quản trị</h1>
                <p class="lead">Theo dõi toàn bộ vận hành trung tâm: lớp học, học viên, lead tư vấn, khóa học và tiến độ chấm bài.</p>
            </div>
            <div class="role-actions">
                <a class="button secondary" href="<?php echo esc_url(sea_logout_url()); ?>">Đăng xuất</a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card"><strong><?php echo esc_html(count($classes)); ?></strong><span>Lớp đang vận hành</span></div>
            <div class="stat-card"><strong><?php echo esc_html($student_count); ?></strong><span>Học viên trong lớp</span></div>
            <div class="stat-card"><strong><?php echo esc_html($new_lead_count); ?></strong><span>Đăng ký chờ xử lý</span></div>
            <div class="stat-card"><strong><?php echo esc_html($pending_count); ?></strong><span>Bài chờ chấm</span></div>
        </div>

        <div class="dashboard-grid">
            <div class="role-panel">
                <div class="panel-heading">
                    <div><h2>Tổng quan lớp học</h2><p>Lịch học, sĩ số và sức chứa của từng lớp.</p></div>
                    <span class="pill"><?php echo esc_html($course_count); ?> khóa public</span>
                </div>
                <table class="demo-table">
                    <thead><tr><th>Lớp</th><th>Lịch học</th><th>Giáo viên</th><th>Sĩ số</th><th>Tiến độ</th></tr></thead>
                    <tbody>
                        <?php foreach ($classes as $class_key => $class) : ?>
                            <?php $class_pending = count(array_filter($class['submissions'], static fn($submission) => 'Chờ chấm' === $submission['status'])); ?>
                            <?php $capacity = (int) ($class['capacity'] ?? 12); ?>
                            <tr>
                                <td><strong><?php echo esc_html($class['name']); ?></strong><br><small><?php echo esc_html($class['room']); ?> · <?php echo esc_html($class_pending); ?> bài chờ chấm</small></td>
                                <td><?php echo esc_html($class['schedule']); ?><br><small>Buổi tới: <?php echo esc_html($class['next_session']); ?></small></td>
                                <td><?php echo esc_html($class['teacher']); ?></td>
                                <td><strong><?php echo esc_html(count($class['students'])); ?>/<?php echo esc_html($capacity); ?></strong></td>
                                <td><div class="mini-progress"><span style="width: <?php echo esc_attr($class['progress']); ?>%"></span></div><?php echo esc_html($class['progress']); ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="role-panel">
                <div class="panel-heading">
                    <div><h2>Việc quản trị cần theo dõi</h2><p>Các đầu việc thường dùng khi demo vai trò admin.</p></div>
                </div>
                <div class="lesson-list">
                    <article class="lesson-item"><div><strong>Duyệt đăng ký mới</strong><span>Kiểm tra thông tin từ form public và xếp học viên vào lớp phù hợp.</span></div><span class="pill"><?php echo esc_html($new_lead_count); ?> chờ xử lý</span></article>
                    <article class="lesson-item"><div><strong>Theo dõi bài chờ chấm</strong><span>Nhắc giáo viên phản hồi bài nộp đúng hạn.</span></div><span class="pill"><?php echo esc_html($pending_count); ?> bài</span></article>
                    <article class="lesson-item"><div><strong>Quản lý nội dung khóa học</strong><span>Cập nhật học phí, lịch học và cam kết đầu ra.</span></div><span class="pill"><?php echo esc_html($course_count); ?> khóa</span></article>
                </div>
            </div>
        </div>

        <div class="role-panel admin-wide-panel">
            <div class="panel-heading">
                <div><h2>Đăng ký từ website public</h2><p>Lead từ form tư vấn sẽ xuất hiện ở đây. Admin chọn lớp để chuyển lead thành học viên trong sĩ số lớp.</p></div>
                <span class="pill"><?php echo esc_html($lead_count); ?> tổng đăng ký</span>
            </div>
            <table class="demo-table">
                <thead><tr><th>Người đăng ký</th><th>Liên hệ</th><th>Học thử</th><th>Mục tiêu</th><th>Trạng thái</th><th>Xếp lớp</th></tr></thead>
                <tbody>
                    <?php if ($leads) : ?>
                        <?php foreach ($leads as $lead) : ?>
                            <?php
                            $status = get_post_meta($lead->ID, '_sea_status', true) ?: 'Chờ tư vấn';
                            $assigned_class = get_post_meta($lead->ID, '_sea_assigned_class', true);
                            $trial_class = get_post_meta($lead->ID, '_sea_trial_class', true);
                            $trial_date = get_post_meta($lead->ID, '_sea_trial_date', true);
                            $preferred_class = $trial_class && isset($classes[$trial_class]) ? $trial_class : '';
                            ?>
                            <tr>
                                <td><strong><?php echo esc_html(get_post_meta($lead->ID, '_sea_name', true) ?: get_the_title($lead)); ?></strong><br><small><?php echo esc_html(get_the_date('d/m/Y H:i', $lead)); ?></small></td>
                                <td><?php echo esc_html(get_post_meta($lead->ID, '_sea_phone', true)); ?><br><small><?php echo esc_html(get_post_meta($lead->ID, '_sea_email', true)); ?></small></td>
                                <td><?php echo esc_html(get_post_meta($lead->ID, '_sea_course', true) ?: 'Chưa chọn'); ?><br><small><?php echo esc_html($preferred_class ? $classes[$preferred_class]['name'] : 'Chưa chọn lớp học thử'); ?><?php echo $trial_date ? ' · ' . esc_html(wp_date('d/m/Y', strtotime($trial_date))) : ''; ?></small></td>
                                <td><?php echo esc_html(wp_trim_words(get_post_meta($lead->ID, '_sea_message', true), 12)); ?></td>
                                <td><span class="pill"><?php echo esc_html($status); ?></span><?php if ($assigned_class && isset($classes[$assigned_class])) : ?><br><small><?php echo esc_html($classes[$assigned_class]['name']); ?></small><?php endif; ?></td>
                                <td>
                                    <?php if ('Đã xếp lớp' === $status) : ?>
                                        <span class="pill">Hoàn tất</span>
                                    <?php else : ?>
                                        <form class="inline-assign-form" method="post">
                                            <?php wp_nonce_field('sea_assign_lead', 'sea_assign_lead_nonce'); ?>
                                            <input type="hidden" name="lead_id" value="<?php echo esc_attr($lead->ID); ?>">
                                            <select name="class_key">
                                                <?php foreach ($classes as $class_key => $class) : ?>
                                                    <?php $capacity = (int) ($class['capacity'] ?? 12); ?>
                                                    <option value="<?php echo esc_attr($class_key); ?>" <?php selected($preferred_class, $class_key); ?>><?php echo esc_html($class['name'] . ' (' . count($class['students']) . '/' . $capacity . ')'); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit">Xếp lớp</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr><td colspan="6">Chưa có đăng ký nào từ form public.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode('sea_admin_dashboard', 'sea_admin_dashboard_shortcode');

function sea_teacher_dashboard_shortcode(): string
{
    if (!is_user_logged_in()) {
        return sea_demo_login_box('teacher');
    }

    if (!sea_is_teacher()) {
        return '<div class="role-panel"><h2>Không đúng vai trò</h2><p>Tài khoản hiện tại không có quyền chấm điểm. Hãy đăng xuất rồi đăng nhập bằng tài khoản giáo viên.</p><a class="button" href="' . esc_url(sea_logout_url()) . '">Đăng xuất</a></div>';
    }

    $data = sea_demo_learning_data();
    $classes = $data['classes'];
    $selected_class = isset($_GET['class']) ? sanitize_key(wp_unslash($_GET['class'])) : 'ielts-foundation';
    if (!isset($classes[$selected_class])) {
        $selected_class = array_key_first($classes);
    }
    $class = $classes[$selected_class];

    if (isset($_POST['sea_grade_nonce'], $_POST['student_key']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['sea_grade_nonce'])), 'sea_grade_submission')) {
        $key = sanitize_key(wp_unslash($_POST['student_key']));
        if (isset($data['classes'][$selected_class]['submissions'][$key])) {
            $data['classes'][$selected_class]['submissions'][$key]['grade'] = sanitize_text_field(wp_unslash($_POST['grade'] ?? ''));
            $data['classes'][$selected_class]['submissions'][$key]['comment'] = sanitize_textarea_field(wp_unslash($_POST['comment'] ?? ''));
            $data['classes'][$selected_class]['submissions'][$key]['status'] = 'Đã chấm';
            sea_save_demo_learning_data($data);
            $classes = $data['classes'];
            $class = $classes[$selected_class];
        }
    }

    $pending_count = count(array_filter($class['submissions'], static fn($submission) => 'Chờ chấm' === $submission['status']));
    $graded = array_filter($class['submissions'], static fn($submission) => '' !== $submission['grade']);
    $average = $graded ? round(array_sum(array_map(static fn($submission) => (float) $submission['grade'], $graded)) / count($graded), 1) : 0;

    ob_start();
    ?>
    <section class="role-dashboard">
        <div class="role-hero">
            <div>
                <span class="eyebrow">Giáo viên</span>
                <h1>Quản lý lớp học</h1>
                <p class="lead">Xem toàn bộ lớp đang phụ trách, theo dõi tiến độ và chấm điểm bài nộp theo từng lớp.</p>
            </div>
            <div class="role-actions">
                <a class="button secondary" href="<?php echo esc_url(sea_logout_url()); ?>">Đăng xuất</a>
            </div>
        </div>

        <div class="role-panel class-switcher">
            <div class="panel-heading">
                <div><h2>Các lớp đang phụ trách</h2><p>Chọn một lớp để xem danh sách học viên, bài nộp và kế hoạch buổi tới.</p></div>
            </div>
            <div class="class-grid">
                <?php foreach ($classes as $class_key => $class_item) : ?>
                    <?php
                    $class_pending = count(array_filter($class_item['submissions'], static fn($submission) => 'Chờ chấm' === $submission['status']));
                    $is_active = $class_key === $selected_class;
                    ?>
                    <a class="class-card <?php echo $is_active ? 'active' : ''; ?>" href="<?php echo esc_url(add_query_arg('class', $class_key, get_permalink())); ?>">
                        <span class="pill"><?php echo esc_html($class_item['status']); ?></span>
                        <strong><?php echo esc_html($class_item['name']); ?></strong>
                        <small><?php echo esc_html($class_item['schedule']); ?> · <?php echo esc_html($class_item['room']); ?></small>
                        <div class="class-card-meta">
                            <span><?php echo esc_html(count($class_item['students'])); ?> học viên</span>
                            <span><?php echo esc_html($class_pending); ?> bài chờ chấm</span>
                            <span><?php echo esc_html($class_item['progress']); ?>%</span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="selected-class-head">
            <div>
                <span class="eyebrow">Lớp đang xem</span>
                <h2><?php echo esc_html($class['name']); ?></h2>
                <p><?php echo esc_html($class['schedule']); ?> · <?php echo esc_html($class['room']); ?> · Buổi tới: <?php echo esc_html($class['next_session']); ?></p>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card"><strong><?php echo esc_html(count($class['students'])); ?></strong><span>Học viên đang học</span></div>
            <div class="stat-card"><strong><?php echo esc_html($pending_count); ?></strong><span>Bài chờ chấm</span></div>
            <div class="stat-card"><strong><?php echo esc_html($average); ?></strong><span>Điểm trung bình</span></div>
            <div class="stat-card"><strong><?php echo esc_html($class['progress']); ?>%</strong><span>Tiến độ giáo trình</span></div>
        </div>

        <div class="dashboard-grid">
            <div class="role-panel">
                <div class="panel-heading">
                    <div><h2>Danh sách lớp</h2><p>Theo dõi chuyên cần và tiến độ từng học viên.</p></div>
                    <span class="pill">Cập nhật hôm nay</span>
                </div>
                <table class="demo-table">
                    <thead><tr><th>Học viên</th><th>Email</th><th>Chuyên cần</th><th>Tiến độ</th><th>Trạng thái</th></tr></thead>
                    <tbody>
                        <?php foreach ($class['students'] as $student) : ?>
                            <tr>
                                <td><strong><?php echo esc_html($student['name']); ?></strong></td>
                                <td><?php echo esc_html($student['email']); ?></td>
                                <td><?php echo esc_html($student['attendance']); ?></td>
                                <td>
                                    <div class="mini-progress"><span style="width: <?php echo esc_attr($student['progress']); ?>%"></span></div>
                                    <?php echo esc_html($student['progress']); ?>%
                                </td>
                                <td><span class="pill">Đang học</span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="role-panel-sub">
                    <h3>Kế hoạch buổi tới</h3>
                    <ul class="task-list">
                        <li>Kiểm tra 10 phút từ vựng Work & Study.</li>
                        <li>Luyện speaking theo cặp, ghi âm và phản hồi tại lớp.</li>
                        <li>Giao bài Writing Task 1 phần overview.</li>
                    </ul>
                </div>
            </div>

            <div class="role-panel">
                <div class="panel-heading">
                    <div><h2>Chấm điểm bài nộp</h2><p>Nhập điểm và nhận xét, học viên sẽ thấy kết quả trong lớp học của mình.</p></div>
                </div>
                <?php foreach ($class['submissions'] as $key => $submission) : ?>
                    <form class="grade-form" method="post">
                        <?php wp_nonce_field('sea_grade_submission', 'sea_grade_nonce'); ?>
                        <input type="hidden" name="student_key" value="<?php echo esc_attr($key); ?>">
                        <div class="submission-head">
                            <strong><?php echo esc_html($submission['student']); ?></strong>
                            <span class="pill"><?php echo esc_html($submission['status']); ?></span>
                        </div>
                        <p><?php echo esc_html($submission['assignment']); ?><br><small>Nộp lúc <?php echo esc_html($submission['submitted']); ?></small></p>
                        <label>Điểm</label>
                        <input name="grade" value="<?php echo esc_attr($submission['grade']); ?>" placeholder="VD: 8.0">
                        <label>Nhận xét</label>
                        <textarea name="comment"><?php echo esc_textarea($submission['comment']); ?></textarea>
                        <button type="submit">Lưu điểm</button>
                    </form>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode('sea_teacher_dashboard', 'sea_teacher_dashboard_shortcode');

function sea_student_dashboard_shortcode(): string
{
    if (!is_user_logged_in()) {
        return sea_demo_login_box('student');
    }

    if (!sea_is_student()) {
        return '<div class="role-panel"><h2>Không đúng vai trò</h2><p>Tài khoản hiện tại không phải học viên. Hãy đăng xuất rồi đăng nhập bằng tài khoản học viên.</p><a class="button" href="' . esc_url(sea_logout_url()) . '">Đăng xuất</a></div>';
    }

    $data = sea_demo_learning_data();
    $submission = $data['submissions']['minhanh'];

    ob_start();
    ?>
    <section class="role-dashboard">
        <div class="role-hero">
            <div>
                <span class="eyebrow">Học viên</span>
                <h1>Lớp học của tôi</h1>
                <p class="lead"><?php echo esc_html($data['class']['name']); ?> · <?php echo esc_html($data['class']['schedule']); ?> · <?php echo esc_html($data['class']['room']); ?></p>
            </div>
            <div class="role-actions">
                <a class="button secondary" href="<?php echo esc_url(sea_logout_url()); ?>">Đăng xuất</a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card"><strong>Thứ 5</strong><span>Buổi học tiếp theo</span></div>
            <div class="stat-card"><strong><?php echo esc_html($data['class']['progress']); ?>%</strong><span>Tiến độ lớp</span></div>
            <div class="stat-card"><strong><?php echo esc_html($submission['grade'] ?: '...'); ?></strong><span>Điểm bài gần nhất</span></div>
            <div class="stat-card"><strong>9/10</strong><span>Chuyên cần cá nhân</span></div>
        </div>

        <div class="dashboard-grid">
            <div class="role-panel">
                <div class="panel-heading">
                    <div><h2>Bài học trong tuần</h2><p>Theo dõi nội dung đã học và phần cần chuẩn bị trước buổi tới.</p></div>
                </div>
                <div class="lesson-list">
                    <?php foreach ($data['lessons'] as $lesson) : ?>
                        <article class="lesson-item">
                            <div><strong><?php echo esc_html($lesson['title']); ?></strong><span><?php echo esc_html($lesson['duration']); ?></span></div>
                            <span class="pill"><?php echo esc_html($lesson['status']); ?></span>
                        </article>
                    <?php endforeach; ?>
                </div>

                <div class="role-panel-sub">
                    <h3>Lộ trình cá nhân</h3>
                    <div class="learning-roadmap">
                        <span class="roadmap-step done">A1</span>
                        <span class="roadmap-step active">A2</span>
                        <span class="roadmap-step">B1</span>
                        <span class="roadmap-step">IELTS 5.5</span>
                    </div>
                </div>
            </div>

            <div class="role-panel">
                <div class="panel-heading">
                    <div><h2>Điểm và nhận xét</h2><p>Phản hồi mới nhất từ giáo viên cho bài nộp của bạn.</p></div>
                </div>
                <div class="grade-result">
                    <span class="grade-number"><?php echo esc_html($submission['grade'] ?: '...'); ?></span>
                    <div>
                        <strong><?php echo esc_html($submission['assignment']); ?></strong>
                        <p><?php echo esc_html($submission['comment'] ?: 'Giáo viên chưa chấm bài này.'); ?></p>
                    </div>
                </div>
                <h3>Việc cần làm</h3>
                <ul class="task-list">
                    <li>Hoàn thành 20 flashcard chủ đề Work & Study.</li>
                    <li>Ghi âm lại bài speaking sau khi đọc nhận xét.</li>
                    <li>Chuẩn bị outline cho Writing Task 1.</li>
                </ul>
            </div>
        </div>
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode('sea_student_dashboard', 'sea_student_dashboard_shortcode');
