<?php
declare(strict_types=1);

final class HttpClient
{
    public static function hasCurl(): bool
    {
        return function_exists('curl_init');
    }

    /** @return resource */
    private static function streamContext(int $timeout = 30)
    {
        return stream_context_create([
            'http' => [
                'timeout' => $timeout,
                'follow_location' => 1,
                'max_redirects' => 5,
                'user_agent' => 'Webphisher-Uzbekistan-Panel/1.0',
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);
    }

    public static function download(string $url, string $dest, int $timeout = 300): array
    {
        if (!is_dir(dirname($dest))) {
            mkdir(dirname($dest), 0755, true);
        }

        if (self::hasCurl()) {
            return self::downloadCurl($url, $dest, $timeout);
        }

        return self::downloadStream($url, $dest, $timeout);
    }

    public static function get(string $url, int $timeout = 15): ?string
    {
        if (self::hasCurl()) {
            $ch = curl_init($url);
            if ($ch === false) {
                return null;
            }
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_USERAGENT => 'Webphisher-Uzbekistan-Panel/1.0',
            ]);
            $body = curl_exec($ch);
            curl_close($ch);
            return is_string($body) ? $body : null;
        }

        $body = @file_get_contents($url, false, self::streamContext($timeout));
        return is_string($body) ? $body : null;
    }

    public static function headOk(string $url, int $timeout = 5): bool
    {
        if (self::hasCurl()) {
            $ch = curl_init($url);
            if ($ch === false) {
                return false;
            }
            curl_setopt_array($ch, [
                CURLOPT_NOBODY => true,
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_FOLLOWLOCATION => true,
            ]);
            curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            return $code >= 200 && $code < 400;
        }

        $headers = @get_headers($url, true, self::streamContext($timeout));
        if (!$headers) {
            return false;
        }
        $status = is_array($headers[0] ?? null) ? $headers[0][0] : ($headers[0] ?? '');
        return (bool) preg_match('/\s2\d\d\s/', (string) $status) || (bool) preg_match('/\s3\d\d\s/', (string) $status);
    }

    private static function downloadCurl(string $url, string $dest, int $timeout): array
    {
        $ch = curl_init($url);
        if ($ch === false) {
            return ['ok' => false, 'error' => 'curl init failed'];
        }
        $fp = fopen($dest, 'wb');
        if ($fp === false) {
            curl_close($ch);
            return ['ok' => false, 'error' => 'Cannot write to ' . $dest];
        }
        curl_setopt_array($ch, [
            CURLOPT_FILE => $fp,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'Webphisher-Uzbekistan-Panel/1.0',
        ]);
        $ok = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        fclose($fp);
        if (!$ok) {
            @unlink($dest);
            return ['ok' => false, 'error' => $err ?: 'Download failed'];
        }
        return ['ok' => true, 'path' => $dest];
    }

    private static function downloadStream(string $url, string $dest, int $timeout): array
    {
        $in = @fopen($url, 'rb', false, self::streamContext($timeout));
        if ($in === false) {
            return [
                'ok' => false,
                'error' => 'Yuklab bo\'lmadi. allow_url_fopen yoqing yoki php.ini da extension=curl ni yoqing.',
            ];
        }
        $out = @fopen($dest, 'wb');
        if ($out === false) {
            fclose($in);
            return ['ok' => false, 'error' => 'Cannot write to ' . $dest];
        }
        $copied = stream_copy_to_stream($in, $out);
        fclose($in);
        fclose($out);
        if ($copied === false || $copied === 0) {
            @unlink($dest);
            return ['ok' => false, 'error' => 'Download failed (empty file or network error)'];
        }
        return ['ok' => true, 'path' => $dest];
    }
}
