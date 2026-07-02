<?php

use PHPUnit\Framework\TestCase;

/**
 * DatabaseTestCase
 * Clase base para todos los tests que necesitan acceso a base de datos.
 */
abstract class DatabaseTestCase extends TestCase
{
    protected PDO $pdo;

    /**
     * Se ejecuta antes de CADA test.
     * Crea una base de datos SQLite en memoria con el esquema completo
     * e inyecta la instancia en $GLOBALS['TEST_PDO'] para que getDB() la use.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = createTestDatabase();
        $GLOBALS['TEST_PDO'] = $this->pdo;
    }

    /**
     * Se ejecuta después de CADA test.
     * Limpia la referencia global para que no haya contaminación entre tests.
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        unset($GLOBALS['TEST_PDO']);
    }

    /**
     * Inserta un usuario de prueba y retorna su ID.
     * Método auxiliar para preparar precondiciones rápidamente.
     */
    protected function crearUsuarioDePrueba(
        string $nombre   = 'Usuario Test',
        string $email    = 'test@example.com',
        string $password = 'password123'
    ): int {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->pdo->prepare(
            "INSERT INTO users (nombre, email, password) VALUES (?, ?, ?)"
        );
        $stmt->execute([$nombre, $email, $hash]);
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Inserta un proyecto de prueba y retorna su ID.
     */
    protected function crearProyectoDePrueba(
        int    $userId      = 1,
        string $nombre      = 'Proyecto Test',
        string $descripcion = 'Descripción de prueba'
    ): int {
        $stmt = $this->pdo->prepare(
            "INSERT INTO projects (nombre, descripcion, user_id) VALUES (?, ?, ?)"
        );
        $stmt->execute([$nombre, $descripcion, $userId]);
        return (int) $this->pdo->lastInsertId();
    }
}
