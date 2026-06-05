<?php
/**
 * Local demo configuration for Sketch English Academy.
 */

define('DB_NAME', 'wordpress');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_HOST', 'localhost');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');

define('DB_ENGINE', 'sqlite');
define('DB_DIR', __DIR__ . '/wp-content/database/');
define('DB_FILE', 'demo.sqlite');

define('AUTH_KEY',         'local-demo-auth-key-2026-06-05');
define('SECURE_AUTH_KEY',  'local-demo-secure-auth-key-2026-06-05');
define('LOGGED_IN_KEY',    'local-demo-logged-in-key-2026-06-05');
define('NONCE_KEY',        'local-demo-nonce-key-2026-06-05');
define('AUTH_SALT',        'local-demo-auth-salt-2026-06-05');
define('SECURE_AUTH_SALT', 'local-demo-secure-auth-salt-2026-06-05');
define('LOGGED_IN_SALT',   'local-demo-logged-in-salt-2026-06-05');
define('NONCE_SALT',       'local-demo-nonce-salt-2026-06-05');

$table_prefix = 'wp_';

define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
define('SCRIPT_DEBUG', true);
define('WP_HOME', 'http://127.0.0.1:8080');
define('WP_SITEURL', 'http://127.0.0.1:8080');

if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

require_once ABSPATH . 'wp-settings.php';
