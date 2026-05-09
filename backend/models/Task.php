<?php

require_once __DIR__ . '/../config/database.php';

class Task {

    public static function create(string $titulo, string $descripcion, string $estado, int $proyectoId, ?int $responsableId): int {
        $db = getDB();
        $stmt = $db->prepare("
            INSERT INTO tasks (titulo, descripcion, estado, proyecto_id, responsable_id)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$titulo, $descripcion, $estado, $proyectoId, $responsableId]);
        return (int) $db->lastInsertId();
    }

    public static function getAllByProject(int $proyectoId): array {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT t.*, u.nombre AS responsable_nombre
            FROM tasks t
            LEFT JOIN users u ON t.responsable_id = u.id
            WHERE t.proyecto_id = ?
            ORDER BY t.created_at DESC
        ");
        $stmt->execute([$proyectoId]);
        return $stmt->fetchAll();
    }

    public static function updateStatus(int $id, string $estado): bool {
        $db = getDB();
        $stmt = $db->prepare("UPDATE tasks SET estado = ? WHERE id = ?");
        $stmt->execute([$estado, $id]);
        return $stmt->rowCount() > 0;
    }

    public static function delete(int $id): bool {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM tasks WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    public static function findById(int $id): ?array {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT t.*, u.nombre AS responsable_nombre, p.nombre AS proyecto_nombre
            FROM tasks t
            LEFT JOIN users u ON t.responsable_id = u.id
            JOIN projects p ON t.proyecto_id = p.id
            WHERE t.id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $task = $stmt->fetch();
        return $task ?: null;
    }
}
