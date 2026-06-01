<?php
declare(strict_types=1);

final class BinaryInstaller
{
    public static function cloudflaredPath(): string
    {
        $name = ProcessHelper::isWindows() ? 'cloudflared.exe' : 'cloudflared';
        return SERVER_DIR . DIRECTORY_SEPARATOR . $name;
    }

    public static function loclxPath(): string
    {
        $name = ProcessHelper::isWindows() ? 'loclx.exe' : 'loclx';
        return SERVER_DIR . DIRECTORY_SEPARATOR . $name;
    }

    public static function ensureCloudflared(): array
    {
        $path = self::cloudflaredPath();
        if (is_file($path)) {
            return ['ok' => true, 'path' => $path];
        }
        if (!is_dir(SERVER_DIR)) {
            mkdir(SERVER_DIR, 0755, true);
        }

        if (!HttpClient::hasCurl() && !ini_get('allow_url_fopen')) {
            return [
                'ok' => false,
                'error' => 'Yuklab olish uchun php.ini da extension=curl yoki allow_url_fopen=On kerak.',
            ];
        }

        $arch = ProcessHelper::arch();
        if (ProcessHelper::isWindows()) {
            $url = 'https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-windows-amd64.exe';
        } elseif ($arch === 'arm64') {
            $url = 'https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-arm64';
        } elseif ($arch === 'arm') {
            $url = 'https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-arm';
        } elseif ($arch === 'amd64') {
            $url = 'https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64';
        } else {
            $url = 'https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-386';
        }

        $result = HttpClient::download($url, $path, 300);
        if (!$result['ok']) {
            if (ProcessHelper::isWindows()) {
                $result['error'] .= ' Windows: panel\\install-cloudflared.bat faylini ishga tushiring (PHP kerak emas).';
            }
            return $result;
        }
        if (!ProcessHelper::isWindows()) {
            @chmod($path, 0755);
        }
        return ['ok' => true, 'path' => $path];
    }

    public static function ensureLoclx(): array
    {
        $path = self::loclxPath();
        if (is_file($path)) {
            return ['ok' => true, 'path' => $path];
        }
        if (!is_dir(SERVER_DIR)) {
            mkdir(SERVER_DIR, 0755, true);
        }

        $arch = ProcessHelper::arch();
        if (ProcessHelper::isWindows()) {
            return ['ok' => false, 'error' => 'LocalXpose binary is not available on Windows in this panel. Use Cloudflared or Localhost.'];
        }

        if (!HttpClient::hasCurl() && !ini_get('allow_url_fopen')) {
            return [
                'ok' => false,
                'error' => 'Yuklab olish uchun php.ini da extension=curl yoki allow_url_fopen=On kerak.',
            ];
        }

        $map = [
            'arm' => 'loclx-linux-arm.zip',
            'arm64' => 'loclx-linux-arm64.zip',
            'amd64' => 'loclx-linux-amd64.zip',
            '386' => 'loclx-linux-386.zip',
        ];
        $file = $map[$arch] ?? $map['amd64'];
        $url = 'https://api.localxpose.io/api/v2/downloads/' . $file;
        $zipPath = SERVER_DIR . DIRECTORY_SEPARATOR . 'loclx.zip';
        $dl = HttpClient::download($url, $zipPath, 300);
        if (!$dl['ok']) {
            return $dl;
        }
        if (!class_exists('ZipArchive')) {
            @unlink($zipPath);
            return ['ok' => false, 'error' => 'zip extension kerak (extension=zip php.ini da)'];
        }
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            @unlink($zipPath);
            return ['ok' => false, 'error' => 'Failed to extract LocalXpose'];
        }
        $zip->extractTo(SERVER_DIR);
        $zip->close();
        @unlink($zipPath);
        $bin = SERVER_DIR . DIRECTORY_SEPARATOR . 'loclx';
        if (is_file($bin)) {
            @chmod($bin, 0755);
            return ['ok' => true, 'path' => $bin];
        }
        return ['ok' => false, 'error' => 'LocalXpose binary missing after extract'];
    }
}
