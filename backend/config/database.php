<?php

// MTasking - Configuración de base de datos

define('DB_HOST', 'localhost');
define('DB_NAME', 'mtasking');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

function getDB(): PDO {
    // Modo TESTING: retorna la instancia SQLite inyectada por el bootstrap de pruebas
    if (defined('TESTING') && TESTING === true) {
        if (!isset($GLOBALS['TEST_PDO']) || !($GLOBALS['TEST_PDO'] instanceof PDO)) {
            throw new \RuntimeException(
                'TEST_PDO no está inicializado. Asegúrate de que DatabaseTestCase::setUp() se ejecutó.'
            );
        }
        return $GLOBALS['TEST_PDO'];
    }

    static $pdo = null;

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error de conexión a la base de datos.']);
            exit;
        }
    }

    return $pdo;
}
