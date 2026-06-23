<?php

require_once __DIR__ . '/../models/Comment.php';

class CommentController {

    /**
     * Valida que el usuario esté logueado y retorna su ID
     */
    private static function requireAuth(): int {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'No autenticado.']);
            exit;
        }
        return (int) $_SESSION['user_id'];
    }

    /**
     * Crear un nuevo comentario para una tarea
     * POST /backend/index.php (o como manejen la ruta)
     */
    public static function create(): void {
        $userId = self::requireAuth();
        $data = json_decode(file_get_contents('php://input'), true);

        $contenido = trim($data['contenido'] ?? '');
        // Ahora lo toma de lo que inyectamos en la ruta de la API
        $taskId    = (int) ($_POST['task_id'] ?? 0); 

        if (!$contenido || !$taskId) {
            http_response_code(400);
            echo json_encode(['error' => 'El contenido del comentario es obligatorio.']);
            return;
        }

        $id = Comment::create($contenido, $taskId, $userId);
        
        http_response_code(201);
        echo json_encode([
            'message' => 'Comentario agregado con éxito.', 
            'id' => $id
        ]);
    }

    /**
     * Listar todos los comentarios de una tarea en específico
     * GET /backend/index.php?task_id={id}
     */
    public static function listByTask(int $taskId): void {
        self::requireAuth();

        if (!$taskId) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de tarea inválido o faltante.']);
            return;
        }

        $comments = Comment::getAllByTask($taskId);
        echo json_encode(['comments' => $comments]);
    }
}