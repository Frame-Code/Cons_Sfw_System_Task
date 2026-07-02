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
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        $titulo        = trim($data['titulo'] ?? '');
        $descripcion   = trim($data['descripcion'] ?? '');
        $estado        = trim($data['estado'] ?? 'Pendiente');
        $proyectoId    = (int) ($data['proyecto_id'] ?? 0);
        $responsableId = !empty($data['responsable_id']) ? (int) $data['responsable_id'] : null;
        $prioridad     = trim($data['prioridad'] ?? 'Media');
        $fechaLimite   = !empty($data['fecha_limite']) ? trim($data['fecha_limite']) : null;

        if (!$titulo || !$proyectoId) {
            http_response_code(400);
            echo json_encode(['error' => 'Título y proyecto son obligatorios.']);
            return;
        }

        $estadosValidos = ['Pendiente', 'En progreso', 'Terminado'];
        if (!in_array($estado, $estadosValidos)) {
            $estado = 'Pendiente';
        }

        $prioridadesValidas = ['Alta', 'Media', 'Baja'];
        if (!in_array($prioridad, $prioridadesValidas)) {
            $prioridad = 'Media';
        }

        // Validar formato fecha (YYYY-MM-DD)
        if ($fechaLimite !== null && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaLimite)) {
            $fechaLimite = null;
        }

        $id = Task::create($titulo, $descripcion, $estado, $proyectoId, $responsableId, $prioridad, $fechaLimite);
        http_response_code(201);
        echo json_encode(['message' => 'Tarea creada.', 'id' => $id]);
    }

    public static function listByProject(int $proyectoId): void {
        self::requireAuth();

        // Filtros opcionales via query string: ?estado=Pendiente&prioridad=Alta
        $estado    = isset($_GET['estado'])    && $_GET['estado']    !== '' ? trim($_GET['estado'])    : null;
        $prioridad = isset($_GET['prioridad']) && $_GET['prioridad'] !== '' ? trim($_GET['prioridad']) : null;

        // validar valores para evitar inyecciones via query params
        $estadosValidos    = ['Pendiente', 'En progreso', 'Terminado'];
        $prioridadesValidas = ['Alta', 'Media', 'Baja'];
        if ($estado    !== null && !in_array($estado, $estadosValidos))      $estado    = null;
        if ($prioridad !== null && !in_array($prioridad, $prioridadesValidas)) $prioridad = null;

        $tasks = Task::getAllByProject($proyectoId, $estado, $prioridad);
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
        $data   = json_decode(file_get_contents('php://input'), true) ?? [];
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

    // NUEVO MÉTODO DE EDICIÓN COMPLETA 
    public static function update(int $id): void {
        self::requireAuth();
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        $titulo        = trim($data['titulo'] ?? '');
        $descripcion   = trim($data['descripcion'] ?? '');
        $estado        = trim($data['estado'] ?? 'Pendiente');
        $prioridad     = trim($data['prioridad'] ?? 'Media');
        $fechaLimite   = !empty($data['fecha_limite']) ? trim($data['fecha_limite']) : null;
        $responsableId = !empty($data['responsable_id']) ? (int) $data['responsable_id'] : null;

        if (!$titulo) {
            http_response_code(400);
            echo json_encode(['error' => 'El título es obligatorio.']);
            return;
        }

        $estadosValidos = ['Pendiente', 'En progreso', 'Terminado'];
        if (!in_array($estado, $estadosValidos)) {
            $estado = 'Pendiente';
        }

        $prioridadesValidas = ['Alta', 'Media', 'Baja'];
        if (!in_array($prioridad, $prioridadesValidas)) {
            $prioridad = 'Media';
        }

        if ($fechaLimite !== null && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaLimite)) {
            $fechaLimite = null;
        }

        Task::update($id, $titulo, $descripcion, $estado, $prioridad, $fechaLimite, $responsableId);
        echo json_encode(['message' => 'Tarea actualizada.']);
    }
}