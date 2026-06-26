<?php

class Comment {

    // ---- Guardar un nuevo comentario en la base de datos ----
    public static function create(string $contenido, int $taskId, int $userId) {
        $db = getDB();

        try {
            // 1. Validar antes de hacer el insert que el taskId exista
            $stmtCheck = $db->prepare("SELECT id FROM tasks WHERE id = ?");
            $stmtCheck->execute([$taskId]);
            if (!$stmtCheck->fetch()) {
                return false; // La tarea no existe
            }

            // 2. Insertar usando un bloque seguro. Quitamos NOW() y dejamos que use la hora por defecto o asignamos CURRENT_TIMESTAMP
            $stmt = $db->prepare("INSERT INTO comments (contenido, task_id, user_id, created_at) VALUES (?, ?, ?, CURRENT_TIMESTAMP)");
            $success = $stmt->execute([$contenido, $taskId, $userId]);

            return $success ? $db->lastInsertId() : false;

        } catch (PDOException $e) {
            // Captura de errores por si algo sale mal al insertar
            return false;
        }
    }

    // ---- Obtener comentarios de una tarea ----
    public static function getByTaskId(int $taskId) {
        $db = getDB();
        
        $stmt = $db->prepare("
            SELECT c.*, u.nombre AS autor_nombre 
            FROM comments c
            JOIN users u ON c.user_id = u.id
            WHERE c.task_id = ?
            ORDER BY c.created_at DESC
        ");
        
        $stmt->execute([$taskId]);
        // Usamos PDO::FETCH_ASSOC para limpiar el mapeo de relaciones de información extraña
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}