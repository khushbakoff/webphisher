<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

spl_autoload_register(static function (string $class): void {
    $file = __DIR__ . '/lib/' . $class . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});

Store::init();
