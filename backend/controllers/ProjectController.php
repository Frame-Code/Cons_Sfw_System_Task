<?php

require_once __DIR__ . '/../models/Project.php';

class ProjectController {

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
        $userId = self::requireAuth();
        $data = json_decode(file_get_contents('php://input'), true);

        $nombre      = trim($data['nombre'] ?? '');
        $descripcion = trim($data['descripcion'] ?? '');

        if (!$nombre) {
            http_response_code(400);
            echo json_encode(['error' => 'El nombre del proyecto es obligatorio.']);
            return;
        }

        $id = Project::create($nombre, $descripcion, $userId);
        http_response_code(201);
        echo json_encode(['message' => 'Proyecto creado.', 'id' => $id]);
    }

    public static function list(): void {
        $userId = self::requireAuth();
        $projects = Project::getAllByUser($userId);
        echo json_encode(['projects' => $projects]);
    }

    public static function update(int $id): void {
        $userId = self::requireAuth();
        $data   = json_decode(file_get_contents('php://input'), true);

        $nombre      = trim($data['nombre'] ?? '');
        $descripcion = trim($data['descripcion'] ?? '');

        if (!$nombre) {
            http_response_code(400);
            echo json_encode(['error' => 'El nombre es obligatorio.']);
            return;
        }

        // Verificar que el proyecto pertenece al usuario
        $project = Project::findById($id);
        if (!$project || (int)$project['user_id'] !== $userId) {
            http_response_code(403);
            echo json_encode(['error' => 'No tienes permiso para editar este proyecto.']);
            return;
        }

        Project::update($id, $nombre, $descripcion);
        echo json_encode(['message' => 'Proyecto actualizado.']);
    }

    public static function detail(int $id): void {
        self::requireAuth();
        $project = Project::findById($id);

        if (!$project) {
            http_response_code(404);
            echo json_encode(['error' => 'Proyecto no encontrado.']);
            return;
        }

        echo json_encode(['project' => $project]);
    }
}
