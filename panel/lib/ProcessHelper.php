<?php
declare(strict_types=1);

final class ProcessHelper
{
    public static function isWindows(): bool
    {
        return DIRECTORY_SEPARATOR === '\\';
    }

    public static function phpBinary(): string
    {
        return PHP_BINARY ?: 'php';
    }

    public static function writePid(string $file, int $pid): void
    {
        file_put_contents($file, (string) $pid);
    }

    public static function readPid(string $file): ?int
    {
        if (!is_file($file)) {
            return null;
        }
        $pid = (int) trim((string) file_get_contents($file));
        return $pid > 0 ? $pid : null;
    }

    public static function isRunning(?int $pid): bool
    {
        if ($pid === null || $pid <= 0) {
            return false;
        }
        if (self::isWindows()) {
            $out = [];
            exec('tasklist /FI "PID eq ' . $pid . '" 2>NUL', $out);
            $text = implode("\n", $out);
            return str_contains($text, (string) $pid);
        }
        return function_exists('posix_kill') && @posix_kill($pid, 0);
    }

    public static function kill(?int $pid): void
    {
        if ($pid === null || $pid <= 0) {
            return;
        }
        if (self::isWindows()) {
            exec('taskkill /PID ' . $pid . ' /F 2>NUL');
        } elseif (function_exists('posix_kill')) {
            @posix_kill($pid, SIGTERM);
            usleep(200000);
            if (self::isRunning($pid)) {
                @posix_kill($pid, SIGKILL);
            }
        }
    }

    public static function killByName(array $names): void
    {
        foreach ($names as $name) {
            if (self::isWindows()) {
                exec('taskkill /IM ' . $name . ' /F 2>NUL');
            } else {
                exec('pkill -f ' . escapeshellarg($name) . ' 2>/dev/null');
            }
        }
    }

    public static function findPidOnPort(int $port): ?int
    {
        if (self::isWindows()) {
            $lines = [];
            exec('netstat -ano | findstr :' . (int) $port, $lines);
            foreach ($lines as $line) {
                if (stripos($line, 'LISTENING') === false) {
                    continue;
                }
                $parts = preg_split('/\s+/', trim($line));
                $pid = (int) ($parts[count($parts) - 1] ?? 0);
                if ($pid > 0) {
                    return $pid;
                }
            }
            return null;
        }

        $out = trim((string) shell_exec('lsof -ti tcp:' . (int) $port . ' -sTCP:LISTEN 2>/dev/null | head -1'));
        $pid = (int) $out;
        return $pid > 0 ? $pid : null;
    }

    /** @return array{pid: int|null, output: string} */
    public static function startBackground(string $command, ?string $cwd = null, ?string $logFile = null): array
    {
        $cwd = $cwd ?? ZPHISHER_ROOT;
        if (self::isWindows()) {
            if ($logFile) {
                $redir = ' >> ' . escapeshellarg($logFile) . ' 2>&1';
            } else {
                $redir = ' > NUL 2>&1';
            }
            $full = 'cd /d ' . escapeshellarg($cwd) . ' && start /B "" ' . $command . $redir;
            pclose(popen($full, 'r'));
            return ['pid' => null, 'output' => 'started'];
        }

        if ($logFile) {
            $full = 'cd ' . escapeshellarg($cwd) . ' && nohup ' . $command . ' >> ' . escapeshellarg($logFile) . ' 2>&1 & echo $!';
        } else {
            $full = 'cd ' . escapeshellarg($cwd) . ' && nohup ' . $command . ' > /dev/null 2>&1 & echo $!';
        }
        $pid = (int) trim((string) shell_exec($full));
        return ['pid' => $pid > 0 ? $pid : null, 'output' => 'started'];
    }

    public static function run(string $command, ?string $cwd = null, int $timeout = 30): string
    {
        $cwd = $cwd ?? ZPHISHER_ROOT;
        $descriptor = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $proc = proc_open($command, $descriptor, $pipes, $cwd);
        if (!is_resource($proc)) {
            return '';
        }
        fclose($pipes[0]);
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);
        $out = '';
        $start = time();
        while (true) {
            $out .= stream_get_contents($pipes[1]) ?: '';
            $out .= stream_get_contents($pipes[2]) ?: '';
            $status = proc_get_status($proc);
            if (!$status['running']) {
                break;
            }
            if (time() - $start > $timeout) {
                proc_terminate($proc);
                break;
            }
            usleep(100000);
        }
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($proc);
        return $out;
    }

    public static function arch(): string
    {
        $arch = php_uname('m');
        if (str_contains($arch, 'aarch64') || str_contains($arch, 'arm64')) {
            return 'arm64';
        }
        if (str_contains($arch, 'arm')) {
            return 'arm';
        }
        if (str_contains($arch, '64')) {
            return 'amd64';
        }
        return '386';
    }
}
