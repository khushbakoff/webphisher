# PHP.ini: curl va allow_url_fopen yoqish
$ErrorActionPreference = 'Stop'

$php = Get-Command php -ErrorAction SilentlyContinue
if (-not $php) {
    $candidates = @(
        'C:\tools\php85\php.exe',
        'C:\php\php.exe',
        'C:\xampp\php\php.exe'
    )
    foreach ($c in $candidates) {
        if (Test-Path $c) { $php = Get-Item $c; break }
    }
}

if (-not $php) {
    Write-Host 'php.exe topilmadi. PATH ga PHP qo''shing yoki to''liq yo\'lni kiriting.' -ForegroundColor Red
    exit 1
}

$phpExe = $php.Source
$phpDir = Split-Path $phpExe -Parent
Write-Host "PHP: $phpExe" -ForegroundColor Cyan

$iniLine = & $phpExe --ini 2>&1 | Select-String 'Loaded Configuration File'
if (-not $iniLine) {
    Write-Host 'php.ini topilmadi. php.ini-development ni php.ini qilib ko''chiring:' -ForegroundColor Yellow
    Write-Host "  $phpDir\php.ini-development -> $phpDir\php.ini"
    exit 1
}

$iniPath = ($iniLine -replace '.*>\s*', '').Trim()
Write-Host "php.ini: $iniPath" -ForegroundColor Cyan

$content = Get-Content $iniPath -Raw
$changed = $false

if ($content -match 'allow_url_fopen\s*=\s*Off') {
    $content = $content -replace 'allow_url_fopen\s*=\s*Off', 'allow_url_fopen = On'
    $changed = $true
    Write-Host 'allow_url_fopen = On qilindi' -ForegroundColor Green
}

$extDir = Join-Path $phpDir 'ext'
if ($content -match ';extension_dir\s*=\s*"ext"' -or $content -match 'extension_dir\s*=\s*"ext"') {
    $absExt = $extDir -replace '\\', '/'
    $content = $content -replace 'extension_dir\s*=\s*"ext"', "extension_dir = `"$absExt`""
    $changed = $true
}

$curlDll = Join-Path $extDir 'php_curl.dll'
if (Test-Path $curlDll) {
    if ($content -match ';extension=curl') {
        $content = $content -replace ';extension=curl', 'extension=curl'
        $changed = $true
        Write-Host 'extension=curl yoqildi' -ForegroundColor Green
    }
} else {
    Write-Host "Ogohlantirish: php_curl.dll topilmadi: $curlDll" -ForegroundColor Yellow
    Write-Host 'PHP to''liq ZIP (Thread Safe) paketini qayta yuklang.' -ForegroundColor Yellow
}

$sqliteDll = Join-Path $extDir 'php_pdo_sqlite.dll'
if ((Test-Path $sqliteDll) -and ($content -match ';extension=pdo_sqlite')) {
    $content = $content -replace ';extension=pdo_sqlite', 'extension=pdo_sqlite'
    $content = $content -replace ';extension=sqlite3', 'extension=sqlite3'
    $changed = $true
    Write-Host 'pdo_sqlite yoqildi' -ForegroundColor Green
}

if ($changed) {
    Set-Content -Path $iniPath -Value $content -NoNewline
    Write-Host 'php.ini yangilandi. Panelni qayta ishga tushiring.' -ForegroundColor Green
} else {
    Write-Host 'O''zgartirish kerak emas yoki allaqachon sozlangan.' -ForegroundColor Gray
}

Write-Host ''
& $phpExe -r "echo 'curl='.(function_exists('curl_init')?'OK':'NO').' fopen='.(ini_get('allow_url_fopen')?'On':'Off').PHP_EOL;"
