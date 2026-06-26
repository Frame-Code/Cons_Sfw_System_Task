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
    public function create($taskId) {
    // Verificar sesión activa
    if (!isset($_SESSION['user_id'])) {
        http_response_code(410); 
        echo json_encode(["error" => "Sesión no iniciada."]);
        return;
    }

    // Leer el JSON del body completo
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    $contenido = isset($data['contenido']) ? trim($data['contenido']) : '';
    $userId = $_SESSION['user_id'];

    if (empty($contenido)) {
        http_response_code(400);
        echo json_encode(["error" => "El contenido del comentario no puede estar vacío."]);
        return;
    }

    // Pasamos el $taskId directo que viene de la ruta inyectada
    $idG = Comment::create($contenido, (int)$taskId, $userId);

    if ($idG) {
        http_response_code(201);
        echo json_encode(["message" => "Comentario agregado con éxito.", "id" => $idG]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "No se pudo guardar el comentario. Verifique que la tarea exista."]);
    }
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