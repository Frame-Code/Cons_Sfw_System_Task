<?php

require_once __DIR__ . '/../DatabaseTestCase.php';
require_once __DIR__ . '/../../backend/models/Project.php';

class ProjectControllerTest extends DatabaseTestCase
{
    public function testCrearProyecto(): void
    {
        $userId = $this->crearUsuarioDePrueba();

        $projectId = Project::create(
            'Proyecto Test',
            'Descripcion Test',
            $userId
        );

        $this->assertIsInt($projectId);
        $this->assertGreaterThan(0, $projectId);
    }

    public function testBuscarProyectoPorId(): void
    {
        $userId = $this->crearUsuarioDePrueba();

        $projectId = $this->crearProyectoDePrueba($userId);

        $project = Project::findById($projectId);

        $this->assertNotNull($project);
        $this->assertEquals('Proyecto Test', $project['nombre']);
    }

    public function testListarProyectosPorUsuario(): void
    {
        $userId = $this->crearUsuarioDePrueba();

        $this->crearProyectoDePrueba($userId);
        $this->crearProyectoDePrueba($userId, 'Proyecto 2');

        $projects = Project::getAllByUser($userId);

        $this->assertCount(2, $projects);
    }
}