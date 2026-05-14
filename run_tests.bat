@echo off
chcp 65001 >nul 2>&1
title MTasking — Suite de Pruebas

echo.
echo         MTasking — Ejecutar Pruebas Unitarias      
echo.

:: ── Verificar que PHP está instalado ─────────────────────────────────────────
php --version >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo  [ERROR] PHP no encontrado en el PATH.
    echo  Descarga PHP en: https://windows.php.net/download
    echo  Asegurate de agregar PHP al PATH del sistema.
    pause
    exit /b 1
)

echo  [OK] PHP encontrado:
php -r "echo '        PHP ' . PHP_VERSION . PHP_EOL;"

:: ── Verificar extensión SQLite ───────────────────────────────────────────────
php -r "if(!extension_loaded('pdo_sqlite')){echo '[ERROR] Extensión pdo_sqlite no habilitada.';exit(1);}" 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo.
    echo  Habilita pdo_sqlite en php.ini descomentando:
    echo    extension=pdo_sqlite
    pause
    exit /b 1
)
echo  [OK] Extensión pdo_sqlite activa

:: ── Instalar/actualizar dependencias con Composer ────────────────────────────
echo.
echo  Instalando dependencias (PHPUnit)...

if not exist "vendor\autoload.php" (
    if not exist "composer.phar" (
        echo  Descargando Composer...
        php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
        php composer-setup.php --quiet
        del composer-setup.php
    )
    php composer.phar install --no-interaction --prefer-dist
) else (
    echo  [OK] Dependencias ya instaladas (vendor/ existe)
)

:: ── Verificar que PHPUnit existe ──────────────────────────────────────────────
if not exist "vendor\bin\phpunit" (
    echo  [ERROR] PHPUnit no encontrado en vendor/bin/phpunit
    echo  Ejecuta: php composer.phar install
    pause
    exit /b 1
)

echo  [OK] PHPUnit listo
echo.
echo  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
echo  Ejecutando suite de pruebas...
echo  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
echo.

php vendor\bin\phpunit --colors=always --testdox 2>&1

set EXIT_CODE=%ERRORLEVEL%

echo.
echo  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

if %EXIT_CODE% EQU 0 (
    echo   RESULTADO: TODOS LOS TESTS PASARON ✓
) else (
    echo   RESULTADO: HAY TESTS FALLIDOS ✗  ^(código: %EXIT_CODE%^)
)

echo  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
echo.
pause
exit /b %EXIT_CODE%
