<?php

require_once __DIR__ . '/../DatabaseTestCase.php';
require_once __DIR__ . '/../../backend/models/Comment.php';
require_once __DIR__ . '/../../backend/models/Task.php';

class CommentControllerTest extends DatabaseTestCase
{
    public function testCrearComentario(): void
    {
        $userId = $this->crearUsuarioDePrueba();
        $projectId = $this->crearProyectoDePrueba($userId);

        $taskId = Task::create(
            'Tarea Test',
            'Descripcion',
            'Pendiente',
            $projectId,
            $userId
        );

        $commentId = Comment::create(
            'Comentario de prueba',
            $taskId,
            $userId
        );

        $this->assertNotFalse($commentId);
    }

    public function testListarComentariosPorTarea(): void
    {
        $userId = $this->crearUsuarioDePrueba();
        $projectId = $this->crearProyectoDePrueba($userId);

        $taskId = Task::create(
            'Tarea Test',
            'Descripcion',
            'Pendiente',
            $projectId,
            $userId
        );

        Comment::create('Comentario 1', $taskId, $userId);
        Comment::create('Comentario 2', $taskId, $userId);

        $comments = Comment::getByTaskId($taskId);

        $this->assertCount(2, $comments);
    }
}