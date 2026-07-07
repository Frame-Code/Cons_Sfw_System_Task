<?php

require_once __DIR__ . '/../DatabaseTestCase.php';
require_once __DIR__ . '/../../backend/models/Task.php';

class TaskControllerTest extends DatabaseTestCase
{
    public function testCrearTarea(): void
    {
        $userId = $this->crearUsuarioDePrueba();
        $projectId = $this->crearProyectoDePrueba($userId);

        $taskId = Task::create(
            'Tarea Test',
            'Descripcion Test',
            'Pendiente',
            $projectId,
            $userId,
            'Alta',
            '2026-12-31'
        );

        $this->assertIsInt($taskId);
        $this->assertGreaterThan(0, $taskId);
    }

    public function testBuscarTareaPorId(): void
    {
        $userId = $this->crearUsuarioDePrueba();
        $projectId = $this->crearProyectoDePrueba($userId);

        $taskId = Task::create(
            'Tarea Test',
            'Descripcion Test',
            'Pendiente',
            $projectId,
            $userId
        );

        $task = Task::findById($taskId);

        $this->assertNotNull($task);
        $this->assertEquals('Tarea Test', $task['titulo']);
    }

    public function testListarTareasPorProyecto(): void
    {
        $userId = $this->crearUsuarioDePrueba();
        $projectId = $this->crearProyectoDePrueba($userId);

        Task::create('Tarea 1', 'Desc', 'Pendiente', $projectId, $userId);
        Task::create('Tarea 2', 'Desc', 'Pendiente', $projectId, $userId);

        $tasks = Task::getAllByProject($projectId);

        $this->assertCount(2, $tasks);
    }
}