<?php

require_once __DIR__ . '/../config/database.php';

class Project {

    public static function create(string $nombre, string $descripcion, int $userId): int {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO projects (nombre, descripcion, user_id) VALUES (?, ?, ?)");
        $stmt->execute([$nombre, $descripcion, $userId]);
        return (int) $db->lastInsertId();
    }

    public static function getAllByUser(int $userId): array {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT p.*, u.nombre AS creador
            FROM projects p
            JOIN users u ON p.user_id = u.id
            WHERE p.user_id = ?
            ORDER BY p.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function update(int $id, string $nombre, string $descripcion): bool {
        $db = getDB();
        $stmt = $db->prepare("UPDATE projects SET nombre = ?, descripcion = ? WHERE id = ?");
        $stmt->execute([$nombre, $descripcion, $id]);
        return $stmt->rowCount() >= 0;
    }

    public static function findById(int $id): ?array {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT p.*, u.nombre AS creador
            FROM projects p
            JOIN users u ON p.user_id = u.id
            WHERE p.id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $project = $stmt->fetch();
        return $project ?: null;
    }
}
