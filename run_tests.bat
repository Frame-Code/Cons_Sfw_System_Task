@echo off
setlocal enabledelayedexpansion
title MTasking - Suite de Pruebas

echo.
echo  ================================================
echo   MTasking - Ejecutar Pruebas Unitarias
echo  ================================================
echo.

:: -- 1. Verificar PHP --
php --version >nul 2>&1
if !ERRORLEVEL! NEQ 0 goto err_php

for /f "tokens=*" %%v in ('php -r "echo PHP_VERSION;"') do echo  [OK] PHP %%v

:: -- 2. Verificar pdo_sqlite --
php -r "exit(extension_loaded('pdo_sqlite') ? 0 : 1);" >nul 2>&1
if !ERRORLEVEL! NEQ 0 goto err_sqlite
echo  [OK] Extension pdo_sqlite activa

:: -- 3. Descargar PHPUnit phar si no existe --
if exist "phpunit.phar" goto phar_ok

echo.
echo  Descargando PHPUnit 10 (phar)...
curl -L https://phar.phpunit.de/phpunit-10.phar -o phpunit.phar
if !ERRORLEVEL! NEQ 0 goto err_curl
echo  [OK] PHPUnit descargado

:phar_ok
echo  [OK] PHPUnit listo

:: -- 4. Ejecutar tests --
echo.
echo  ================================================
echo   Ejecutando suite de pruebas...
echo  ================================================
echo.

php phpunit.phar --colors=always --testdox
set RESULT=!ERRORLEVEL!

echo.
echo  ================================================
if !RESULT! EQU 0 (
    echo   RESULTADO: TODOS LOS TESTS PASARON
) else (
    echo   RESULTADO: HAY TESTS FALLIDOS ^(codigo: !RESULT!^)
)
echo  ================================================
echo.
pause
exit /b !RESULT!

:: -- Mensajes de error --
:err_php
echo  [ERROR] PHP no encontrado en el PATH.
echo  Descarga PHP en: https://windows.php.net/download
echo  Agrega PHP al PATH del sistema.
goto fin_error

:err_sqlite
echo  [ERROR] Extension pdo_sqlite no disponible.
echo  Abre php.ini y descomenta: extension=pdo_sqlite
goto fin_error

:err_curl
echo  [ERROR] No se pudo descargar PHPUnit con curl.
echo  Descarga manualmente phpunit-10.phar desde:
echo    https://phar.phpunit.de/phpunit-10.phar
echo  Renambralo a phpunit.phar y colocalo en esta carpeta.
goto fin_error

:fin_error
echo.
pause
exit /b 1
