<?php
declare(strict_types=1);

final class UrlShortener
{
    public static function shorten(string $url): ?string
    {
        if (!HttpClient::hasCurl() && !ini_get('allow_url_fopen')) {
            return null;
        }

        $host = preg_replace('#^https?://#', '', $url);
        $isgd = 'https://is.gd/create.php?format=simple&url=' . urlencode('https://' . $host);
        $shortcode = 'https://api.shrtco.de/v2/shorten?url=' . urlencode('https://' . $host);
        $tinyurl = 'https://tinyurl.com/api-create.php?url=' . urlencode('https://' . $host);

        if (HttpClient::headOk($isgd)) {
            return self::normalizeUrl(HttpClient::get($isgd));
        }
        if (HttpClient::headOk($shortcode)) {
            $raw = HttpClient::get($shortcode);
            if ($raw && preg_match('/"short_link2":"([^"]+)"/', str_replace('\\/', '/', $raw), $m)) {
                return $m[1];
            }
        }
        return self::normalizeUrl(HttpClient::get($tinyurl));
    }

    public static function buildMasked(string $mask, ?string $shortUrl): ?string
    {
        if (!$shortUrl) {
            return null;
        }
        $processed = preg_replace('#^https?://#', '', $shortUrl);
        return $mask . '@' . $processed;
    }

    private static function normalizeUrl(?string $body): ?string
    {
        if (!is_string($body) || trim($body) === '') {
            return null;
        }
        $body = trim($body);
        if (!str_starts_with($body, 'http')) {
            return 'https://' . ltrim($body, '/');
        }
        return $body;
    }
}
