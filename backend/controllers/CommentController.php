<?php
require_once dirname(__DIR__) . '/models/Comment.php';

class CommentController {
    /**
     * Listar comentarios en formato JSON limpio
     */
    public static function listByTask($taskId): void {
        header('Content-Type: application/json');
        try {
            $comments = Comment::getByTaskId($taskId);
            echo json_encode(["comments" => $comments]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error al listar comentarios.", "detalles" => $e->getMessage()]);
        }
    }

   /**
     * Guardar un nuevo comentario enviado desde el frontend
     */
    public static function create(): void {
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 1. Validar que el usuario tenga sesión activa
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(["error" => "Sesión no iniciada."]);
            return;
        }

        // 2. Extraer dinámicamente el taskId desde la URL (Ej: /tasks/1/comments)
        $requestUri = $_SERVER['REQUEST_URI'];
        $scriptName = $_SERVER['SCRIPT_NAME']; 
        $path = str_replace($scriptName, '', $requestUri);
        $path = trim($path, '/');
        $segments = explode('/', $path);

        // Buscamos el valor numérico que va después de la palabra 'tasks'
        $taskId = null;
        foreach ($segments as $key => $segment) {
            if ($segment === 'tasks' && isset($segments[$key + 1])) {
                $taskId = (int)$segments[$key + 1];
                break;
            }
        }

        if (!$taskId) {
            http_response_code(400);
            echo json_encode(["error" => "No se pudo identificar el ID de la tarea."]);
            return;
        }

        // 3. Leer el contenido JSON enviado desde el frontend
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $contenido = isset($data['contenido']) ? trim($data['contenido']) : '';
        $userId = $_SESSION['user_id'];

        if (empty($contenido)) {
            http_response_code(400);
            echo json_encode(["error" => "El comentario no puede estar vacío."]);
            return;
        }

        try {
            // Pasamos los 3 argumentos en el orden exacto que requiere el modelo
            $idG = Comment::create($contenido, $taskId, $userId);
            if ($idG) {
                http_response_code(201);
                echo json_encode(["message" => "Comentario agregado con éxito.", "id" => $idG]);
            } else {
                http_response_code(500);
                echo json_encode(["error" => "No se pudo guardar el comentario en la base de datos."]);
            }
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error interno del servidor.", "detalles" => $e->getMessage()]);
        }
    } 
}   