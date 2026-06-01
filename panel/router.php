<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

if (str_starts_with($uri, '/api')) {
    require __DIR__ . '/api/router.php';
    return true;
}

$file = __DIR__ . $uri;
if ($uri !== '/' && is_file($file) && !str_contains($uri, '..')) {
    return false;
}

if (str_starts_with($uri, '/assets/')) {
    return false;
}

require __DIR__ . '/views/app.php';
return true;
