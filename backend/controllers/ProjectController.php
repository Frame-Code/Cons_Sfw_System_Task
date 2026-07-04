<?php

require_once __DIR__ . '/../models/Project.php';
require_once __DIR__ . '/../exceptions/AppException.php';

class ProjectController
{

    private static function requireAuth(): int
    {
        session_start();

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

            $userId = self::requireAuth();

            $data = json_decode(file_get_contents('php://input'), true);

            $nombre = trim($data['nombre'] ?? '');
            $descripcion = trim($data['descripcion'] ?? '');

            if (!$nombre) {
                throw new AppException(
                    'El nombre del proyecto es obligatorio.',
                    400
                );
            }

            $id = Project::create(
                $nombre,
                $descripcion,
                $userId
            );

            http_response_code(201);

            echo json_encode([
                'message' => 'Proyecto creado.',
                'id' => $id
            ]);

        } catch (AppException $e) {

            http_response_code($e->getHttpCode());

            echo $e->toJson();

        }
    }

    public static function list(): void
    {
        try {

            $userId = self::requireAuth();

            $projects = Project::getAllByUser($userId);

            echo json_encode([
                'projects' => $projects
            ]);

        } catch (AppException $e) {

            http_response_code($e->getHttpCode());

            echo $e->toJson();

        }
    }

    public static function update(int $id): void
    {
        try {

            $userId = self::requireAuth();

            $data = json_decode(file_get_contents('php://input'), true);

            $nombre = trim($data['nombre'] ?? '');
            $descripcion = trim($data['descripcion'] ?? '');

            if (!$nombre) {
                throw new AppException(
                    'El nombre es obligatorio.',
                    400
                );
            }

            $project = Project::findById($id);

            if (!$project) {
                throw new RecursoNoEncontradoException(
                    'Proyecto',
                    $id
                );
            }

            if ((int)$project['user_id'] !== $userId) {
                throw new AppException(
                    'No tienes permiso para editar este proyecto.',
                    403
                );
            }

            Project::update(
                $id,
                $nombre,
                $descripcion
            );

            echo json_encode([
                'message' => 'Proyecto actualizado.'
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

            $project = Project::findById($id);

            if (!$project) {
                throw new RecursoNoEncontradoException(
                    'Proyecto',
                    $id
                );
            }

            echo json_encode([
                'project' => $project
            ]);

        } catch (AppException $e) {

            http_response_code($e->getHttpCode());

            echo $e->toJson();

        }
    }

}
