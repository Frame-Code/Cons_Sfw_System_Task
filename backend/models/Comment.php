<?php
// Cambiamos la ruta para que apunte al archivo correcto de configuración
require_once dirname(__DIR__) . '/config/Database.php';

class Comment {
    /**
     * Obtener todos los comentarios de una tarea con el nombre del autor
     */
    public static function getByTaskId($taskId) {
        // CORREGIDO: Usamos la función de tu líder getDB() en lugar de la clase inexistente
        $db = getDB();
        
        $stmt = $db->prepare("
            SELECT c.*, u.nombre AS autor_nombre 
            FROM comments c
            JOIN users u ON c.user_id = u.id
            WHERE c.task_id = ?
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([(int)$taskId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Insertar un nuevo comentario en la base de datos
     */
    public static function create($contenido, $taskId, $userId) {
        // CORREGIDO: Usamos la función de tu líder getDB() en lugar de la clase inexistente
        $db = getDB();
        
        $stmt = $db->prepare("
            INSERT INTO comments (contenido, task_id, user_id, created_at) 
            VALUES (?, ?, ?, NOW())
        ");
        $success = $stmt->execute([$contenido, (int)$taskId, (int)$userId]);
        return $success ? $db->lastInsertId() : false;
    }
}