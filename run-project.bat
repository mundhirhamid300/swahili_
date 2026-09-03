@echo off
setlocal

set "PHP84_DIR=C:\Users\Raommy\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.4_Microsoft.Winget.Source_8wekyb3d8bbwe"
set "COMPOSER_DIR=C:\ProgramData\ComposerSetup\bin"
set "NODE_DIR=C:\Program Files\nodejs"
set "GIT_DIR=C:\Program Files\Git\cmd"

if not exist "%PHP84_DIR%\php.exe" (
    echo ERROR: PHP 8.4 was not found at:
    echo %PHP84_DIR%\php.exe
    pause
    exit /b 1
)

set "PATH=%PHP84_DIR%;%COMPOSER_DIR%;%NODE_DIR%;%GIT_DIR%;C:\Windows\System32;C:\Windows;%PATH%"
set "COMSPEC=C:\Windows\System32\cmd.exe"

cd /d "%~dp0"

echo Using:
php -v
echo.

php artisan optimize:clear
if errorlevel 1 (
    echo.
    echo ERROR: Laravel cache clearing failed.
    pause
    exit /b 1
)

echo.
echo Swahili LMS: http://127.0.0.1:8001
echo Press Ctrl+C to stop the server.
echo.

php artisan serve --host=127.0.0.1 --port=8001

endlocal
