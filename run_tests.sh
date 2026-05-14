#!/bin/bash
# MTasking — Script de pruebas para Linux/macOS

set -e

echo ""
echo "      MTasking — Ejecutar Pruebas Unitarias       "
echo ""

# ── Verificar PHP ──────────────────────────────────────────────────────────────
if ! command -v php &>/dev/null; then
    echo "    PHP no encontrado. Instálalo con:"
    echo "    Ubuntu/Debian: sudo apt install php8.2-cli php8.2-sqlite3 php8.2-xml"
    echo "    macOS:         brew install php"
    exit 1
fi
echo " $(php --version | head -1)"

# ── Verificar PDO SQLite ───────────────────────────────────────────────────────
if ! php -r "exit(extension_loaded('pdo_sqlite') ? 0 : 1);" 2>/dev/null; then
    echo "   Extensión pdo_sqlite no disponible."
    echo "    Ubuntu/Debian: sudo apt install php8.2-sqlite3"
    exit 1
fi
echo "  Extensión pdo_sqlite activa"

# ── Instalar Composer y dependencias ──────────────────────────────────────────
if [ ! -f "vendor/autoload.php" ]; then
    echo ""
    echo "  Instalando dependencias (PHPUnit)..."

    if [ ! -f "composer.phar" ]; then
        echo "    Descargando Composer..."
        php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
        php composer-setup.php --quiet
        rm composer-setup.php
    fi

    php composer.phar install --no-interaction --prefer-dist
else
    echo "  Dependencias ya instaladas (vendor/ existe)"
fi

# ── Ejecutar PHPUnit ──────────────────────────────────────────────────────────
echo ""
echo "  Ejecutando suite de pruebas..."
echo ""

php vendor/bin/phpunit --colors=always --testdox
EXIT_CODE=$?

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
if [ $EXIT_CODE -eq 0 ]; then
    echo "  TODOS LOS TESTS PASARON"
else
    echo "  HAY TESTS FALLIDOS (código: $EXIT_CODE)"
fi
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

exit $EXIT_CODE
