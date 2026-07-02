<?php

require_once __DIR__ . '/../config/database.php';

class User {

    public static function findByEmail(string $email): ?array {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function findById(int $id): ?array {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, nombre, email, created_at FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function create(string $nombre, string $email, string $password): int {
        $db = getDB();
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $db->prepare("INSERT INTO users (nombre, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$nombre, $email, $hash]);
        return (int) $db->lastInsertId();
    }

    public static function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }

    public static function getAll(): array {
        $db = getDB();
        $stmt = $db->query("SELECT id, nombre, email FROM users ORDER BY nombre");
        return $stmt->fetchAll();
    }
}
