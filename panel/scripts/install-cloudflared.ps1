# Cloudflared yuklab olish (PHP curl/fopen kerak emas)
$ErrorActionPreference = 'Stop'

$root = Resolve-Path (Join-Path $PSScriptRoot '..\..')
$serverDir = Join-Path $root '.server'
$dest = Join-Path $serverDir 'cloudflared.exe'
$url = 'https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-windows-amd64.exe'

Write-Host 'Webphisher Uzbekistan — Cloudflared o''rnatish' -ForegroundColor Cyan
Write-Host "Manzil: $dest"

if (-not (Test-Path $serverDir)) {
    New-Item -ItemType Directory -Path $serverDir -Force | Out-Null
}

if (Test-Path $dest) {
    Write-Host 'cloudflared.exe allaqachon mavjud.' -ForegroundColor Yellow
    exit 0
}

Write-Host 'Yuklanmoqda (bir necha MB)...' -ForegroundColor Gray
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
Invoke-WebRequest -Uri $url -OutFile $dest -UseBasicParsing

if ((Test-Path $dest) -and ((Get-Item $dest).Length -gt 1000000)) {
    Write-Host 'Tayyor! Panelda Cloudflared tunnel ni ishlatishingiz mumkin.' -ForegroundColor Green
} else {
    Write-Error 'Yuklab olish muvaffaqiyatsiz. Internet yoki GitHub bloklangan bo''lishi mumkin.'
}
