<?php
header('Content-Type: text/plain; charset=utf-8');
echo "PHP " . PHP_VERSION . "\n";
echo "php.ini: " . (php_ini_loaded_file() ?: 'topilmadi') . "\n\n";

echo "pdo_sqlite: " . (extension_loaded('pdo_sqlite') ? "OK" : "YO'Q (panel JSON rejimida ishlaydi)") . "\n";
echo "curl: " . (extension_loaded('curl') ? "OK" : "YO'Q") . "\n";
echo "allow_url_fopen: " . (ini_get('allow_url_fopen') ? "On" : "Off") . "\n";
echo "zip: " . (extension_loaded('zip') ? "OK" : "YO'Q") . "\n\n";

if (!extension_loaded('curl') && !ini_get('allow_url_fopen')) {
    echo "DIQQAT: Cloudflared yuklab olish uchun quyidagilardan birini yoqing:\n";
    echo "  1) php.ini -> extension=curl  (ext/php_curl.dll)\n";
    echo "  2) php.ini -> allow_url_fopen = On\n";
}
