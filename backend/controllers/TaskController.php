<?php

require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/../models/User.php';

class TaskController {

    private static function requireAuth(): int {
        session_start();
        if (empty($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'No autenticado.']);
            exit;
        }
        return (int) $_SESSION['user_id'];
    }

    public static function create(): void {
        self::requireAuth();
        $data = json_decode(file_get_contents('php://input'), true);

        $titulo        = trim($data['titulo'] ?? '');
        $descripcion   = trim($data['descripcion'] ?? '');
        $estado        = trim($data['estado'] ?? 'Pendiente');
        $proyectoId    = (int) ($data['proyecto_id'] ?? 0);
        $responsableId = !empty($data['responsable_id']) ? (int) $data['responsable_id'] : null;

        if (!$titulo || !$proyectoId) {
            http_response_code(400);
            echo json_encode(['error' => 'Título y proyecto son obligatorios.']);
            return;
        }

        $estadosValidos = ['Pendiente', 'En progreso', 'Terminado'];
        if (!in_array($estado, $estadosValidos)) {
            $estado = 'Pendiente';
        }

        $id = Task::create($titulo, $descripcion, $estado, $proyectoId, $responsableId);
        http_response_code(201);
        echo json_encode(['message' => 'Tarea creada.', 'id' => $id]);
    }

    public static function listByProject(int $proyectoId): void {
        self::requireAuth();
        $tasks = Task::getAllByProject($proyectoId);
        echo json_encode(['tasks' => $tasks]);
    }

    public static function detail(int $id): void {
        self::requireAuth();
        $task = Task::findById($id);

        if (!$task) {
            http_response_code(404);
            echo json_encode(['error' => 'Tarea no encontrada.']);
            return;
        }

        echo json_encode(['task' => $task]);
    }

    public static function updateStatus(int $id): void {
        self::requireAuth();
        $data   = json_decode(file_get_contents('php://input'), true);
        $estado = trim($data['estado'] ?? '');

        $estadosValidos = ['Pendiente', 'En progreso', 'Terminado'];
        if (!in_array($estado, $estadosValidos)) {
            http_response_code(400);
            echo json_encode(['error' => 'Estado inválido.']);
            return;
        }

        Task::updateStatus($id, $estado);
        echo json_encode(['message' => 'Estado actualizado.']);
    }

    public static function delete(int $id): void {
        self::requireAuth();
        Task::delete($id);
        echo json_encode(['message' => 'Tarea eliminada.']);
    }

    public static function users(): void {
        self::requireAuth();
        $users = User::getAll();
        echo json_encode(['users' => $users]);
    }
}
