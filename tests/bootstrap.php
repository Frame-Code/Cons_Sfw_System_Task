<?php

/**
 * MTasking — Pruebas
 * Este archivo se carga automáticamente por PHPUnit antes de ejecutar cualquier test.
 * Define el modo TESTING y proporciona la función createTestDatabase() que inicializa.
 */

// Activa el modo testing. getDB() retornará $GLOBALS['TEST_PDO'] en lugar de conectar a MySQL.
define('TESTING', true);

// Carga el Composer (PHPUnit y dependencias)
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Crea y retorna una instancia PDO con SQLite en memoria e inicializa el esquema.
 * Cada test llama a esta función en setUp() para obtener una base de datos limpia.
 */
function createTestDatabase(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    // Habilitar foreign keys en SQLite
    $pdo->exec('PRAGMA foreign_keys = ON;');

    //Esquema adaptado de database/schema.sql
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id          INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre      TEXT    NOT NULL,
            email       TEXT    NOT NULL UNIQUE,
            password    TEXT    NOT NULL,
            created_at  TEXT    DEFAULT (datetime('now'))
        );

        CREATE TABLE IF NOT EXISTS projects (
            id          INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre      TEXT    NOT NULL,
            descripcion TEXT,
            user_id     INTEGER NOT NULL,
            created_at  TEXT    DEFAULT (datetime('now')),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS tasks (
            id             INTEGER PRIMARY KEY AUTOINCREMENT,
            titulo         TEXT    NOT NULL,
            descripcion    TEXT,
            estado         TEXT    NOT NULL DEFAULT 'Pendiente',
            proyecto_id    INTEGER NOT NULL,
            responsable_id INTEGER,
            created_at     TEXT    DEFAULT (datetime('now')),
            FOREIGN KEY (proyecto_id)    REFERENCES projects(id) ON DELETE CASCADE,
            FOREIGN KEY (responsable_id) REFERENCES users(id)    ON DELETE SET NULL
        );
    ");

    return $pdo;
}
