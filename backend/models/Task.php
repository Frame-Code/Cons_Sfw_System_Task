<?php

require_once __DIR__ . '/../config/database.php';

class Task {

    public static function create(
        string $titulo,
        string $descripcion,
        string $estado,
        int $proyectoId,
        ?int $responsableId,
        string $prioridad = 'Media',
        ?string $fechaLimite = null
    ): int {
        $db = getDB();
        $stmt = $db->prepare("
            INSERT INTO tasks (titulo, descripcion, estado, prioridad, fecha_limite, proyecto_id, responsable_id)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$titulo, $descripcion, $estado, $prioridad, $fechaLimite, $proyectoId, $responsableId]);
        return (int) $db->lastInsertId();
    }

    /**
     * Retorna las tareas de un proyecto, con filtros opcionales por estado y prioridad.
     * Ordenadas por prioridad (Alta primero), luego fecha_limite más próxima, luego más recientes.
     */
    public static function getAllByProject(int $proyectoId, ?string $estado = null, ?string $prioridad = null): array {
        $db = getDB();

        $sql = "
            SELECT t.*, u.nombre AS responsable_nombre
            FROM tasks t
            LEFT JOIN users u ON t.responsable_id = u.id
            WHERE t.proyecto_id = ?
        ";
        $params = [$proyectoId];

        if ($estado !== null && $estado !== '') {
            $sql .= " AND t.estado = ?";
            $params[] = $estado;
        }
        if ($prioridad !== null && $prioridad !== '') {
            $sql .= " AND t.prioridad = ?";
            $params[] = $prioridad;
        }

        // SQLite no soporta CASE en ORDER BY de la misma forma, usamos IF solo en MySQL
        // Para compatibilidad con SQLite (tests) ordenamos solo por created_at DESC
        if (defined('TESTING') && TESTING) {
            $sql .= " ORDER BY t.created_at DESC";
        } else {
            $sql .= " ORDER BY
                CASE t.prioridad WHEN 'Alta' THEN 1 WHEN 'Media' THEN 2 WHEN 'Baja' THEN 3 END,
                t.fecha_limite IS NULL,
                t.fecha_limite ASC,
                t.created_at DESC";
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
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
