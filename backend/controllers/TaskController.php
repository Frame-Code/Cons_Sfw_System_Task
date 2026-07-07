<?php

require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../exceptions/AppException.php';

class TaskController
{
    private static function requireAuth(): int
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['user_id'])) {
            throw new AuthException(
                'Debes iniciar sesión para acceder a este recurso.'
            );
        }

        return (int) $_SESSION['user_id'];
    }

    public static function create(): void
    {
        try {
            self::requireAuth();
            $data = json_decode(file_get_contents('php://input'), true);

            $titulo        = trim($data['titulo'] ?? '');
            $descripcion   = trim($data['descripcion'] ?? '');
            $estado        = trim($data['estado'] ?? 'Pendiente');
            $proyectoId    = (int) ($data['proyecto_id'] ?? 0);
            $responsableId = !empty($data['responsable_id']) ? (int)$data['responsable_id'] : null;
            $prioridad     = trim($data['prioridad'] ?? 'Media');
            $fechaLimite   = !empty($data['fecha_limite']) ? trim($data['fecha_limite']) : null;

            if (!$titulo || !$proyectoId) {
                throw new AppException(
                    'Título y proyecto son obligatorios.',
                    400
                );
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

            $id = Task::create(
                $titulo,
                $descripcion,
                $estado,
                $proyectoId,
                $responsableId,
                $prioridad,
                $fechaLimite
            );

            http_response_code(201);
            echo json_encode([
                'message' => 'Tarea creada.',
                'id' => $id
            ]);

        } catch (AppException $e) {
            http_response_code($e->getHttpCode());
            echo $e->toJson();
        }
    }

    public static function listByProject(int $proyectoId): void
    {
        try {
            self::requireAuth();

            $estado    = isset($_GET['estado'])    && $_GET['estado']    !== '' ? trim($_GET['estado'])    : null;
            $prioridad = isset($_GET['prioridad']) && $_GET['prioridad'] !== '' ? trim($_GET['prioridad']) : null;

            $estadosValidos     = ['Pendiente', 'En progreso', 'Terminado'];
            $prioridadesValidas = ['Alta', 'Media', 'Baja'];

            if ($estado    !== null && !in_array($estado, $estadosValidos))       $estado    = null;
            if ($prioridad !== null && !in_array($prioridad, $prioridadesValidas)) $prioridad = null;

            $tasks = Task::getAllByProject($proyectoId, $estado, $prioridad);

            echo json_encode([
                'tasks' => $tasks
            ]);

        } catch (AppException $e) {
            http_response_code($e->getHttpCode());
            echo $e->toJson();
        }
    }

    public static function detail(int $id): void
    {
        try {
            self::requireAuth();

            $task = Task::findById($id);

            if (!$task) {
                throw new RecursoNoEncontradoException('Tarea', $id);
            }

            echo json_encode([
                'task' => $task
            ]);

        } catch (AppException $e) {
            http_response_code($e->getHttpCode());
            echo $e->toJson();
        }
    }

    public static function updateStatus(int $id): void
    {
        try {
            self::requireAuth();

            $data = json_decode(file_get_contents('php://input'), true);
            $estado = trim($data['estado'] ?? '');

            $estadosValidos = ['Pendiente', 'En progreso', 'Terminado'];

            if (!in_array($estado, $estadosValidos)) {
                throw new AppException('Estado inválido.', 400);
            }

            Task::updateStatus($id, $estado);

            echo json_encode([
                'message' => 'Estado actualizado.'
            ]);

        } catch (AppException $e) {
            http_response_code($e->getHttpCode());
            echo $e->toJson();
        }
    }

    public static function delete(int $id): void
    {
        try {
            self::requireAuth();

            Task::delete($id);

            echo json_encode([
                'message' => 'Tarea eliminada.'
            ]);

        } catch (AppException $e) {
            http_response_code($e->getHttpCode());
            echo $e->toJson();
        }
    }

    public static function users(): void
    {
        try {
            self::requireAuth();

            $users = User::getAll();

            echo json_encode([
                'users' => $users
            ]);

        } catch (AppException $e) {
            http_response_code($e->getHttpCode());
            echo $e->toJson();
        }
    }
}