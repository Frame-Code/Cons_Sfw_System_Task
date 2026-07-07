<?php

require_once dirname(__DIR__) . '/models/Comment.php';
require_once dirname(__DIR__) . '/exceptions/AppException.php';

class CommentController
{
    public static function listByTask($taskId): void
    {
        header('Content-Type: application/json');

        try {
            $comments = Comment::getByTaskId($taskId);

            echo json_encode([
                "comments" => $comments
            ]);

        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Error al listar comentarios.'
            ]);
        }
    }

    public static function create(int $taskId): void
    {
        header('Content-Type: application/json');

        try {

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            if (!isset($_SESSION['user_id'])) {
                throw new AuthException(
                    'Debes iniciar sesión para acceder a este recurso.'
                );
            }

            if (!$taskId) {
                throw new AppException(
                    'No se pudo identificar el ID de la tarea.',
                    400
                );
            }

            $json = file_get_contents('php://input');
            $data = json_decode($json, true);

            $contenido = isset($data['contenido'])
                ? trim($data['contenido'])
                : '';

            $userId = $_SESSION['user_id'];

            if (empty($contenido)) {
                throw new AppException(
                    'El comentario no puede estar vacío.',
                    400
                );
            }

            $idG = Comment::create(
                $contenido,
                $taskId,
                $userId
            );

            if (!$idG) {
                throw new AppException(
                    'No se pudo guardar el comentario.',
                    500
                );
            }

            http_response_code(201);

            echo json_encode([
                "message" => "Comentario agregado con éxito.",
                "id" => $idG
            ]);

        } catch (AppException $e) {
            http_response_code($e->getHttpCode());
            echo $e->toJson();

        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Error interno del servidor.'
            ]);
        }
    }
}