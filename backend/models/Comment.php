<?php

require_once __DIR__ . '/../config/database.php';

class Comment {

    /**
     * Guardar un nuevo comentario en la base de datos
     */
    public static function create(string $contenido, int $taskId, int $userId): int {
        $db = getDB();
        $stmt = $db->prepare("
            INSERT INTO comments (contenido, task_id, user_id, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$contenido, $taskId, $userId]);
        return (int) $db->lastInsertId();
    }

    /**
     * Obtener los comentarios de una tarea específica (más recientes primero)
     * Hace un JOIN con la tabla de usuarios para traer el nombre del autor
     */
    public static function getAllByTask(int $taskId): array {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT c.*, u.nombre AS autor_nombre
            FROM comments c
            JOIN users u ON c.user_id = u.id
            WHERE c.task_id = ?
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$taskId]);
        return $stmt->fetchAll();
    }
}