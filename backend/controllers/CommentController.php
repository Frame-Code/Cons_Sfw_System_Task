<?php

require_once __DIR__ . '/../models/Comment.php';

class CommentController {

    /**
     * Crear un nuevo comentario para una tarea
     */
    public static function create($taskId): void {
        // Verificar sesión activa (401 Unauthorized)
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401); 
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

        // Crear comentario asociándolo al taskId de la ruta
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
     */
    public static function getByTask($taskId): void {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(["error" => "Sesión no iniciada."]);
            return;
        }

        // Llamamos al nombre correcto de la función en el modelo
        $comments = Comment::getByTaskId((int)$taskId);
        
        http_response_code(200);
        echo json_encode(['comments' => $comments]);
    }
}