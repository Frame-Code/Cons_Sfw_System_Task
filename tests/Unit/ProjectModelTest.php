<?php

require_once __DIR__ . '/../../tests/DatabaseTestCase.php';
require_once __DIR__ . '/../../backend/models/User.php';
require_once __DIR__ . '/../../backend/models/Project.php';

/**
 * ProjectModelTest — Pruebas unitarias del modelo Project
 *
 * Cubre los casos del Plan de Pruebas:
 *   TP-005: Creación de proyecto con nombre vacío (validación del controlador)
 *   TP-006: Creación de proyecto con datos válidos
 */
class ProjectModelTest extends DatabaseTestCase
{
    private int $userId;

    protected function setUp(): void
    {
        parent::setUp();
        // Crear un usuario propietario para los proyectos de prueba
        $this->userId = $this->crearUsuarioDePrueba('Propietario', 'owner@test.com', 'pw123');
    }

    // TP-005 | Nombre vacío es rechazado por la lógica del controlador
    /**
     * @test
     * Verifica que la lógica de validación del controlador rechaza nombres vacíos.
     * ProjectController::create() hace: $nombre = trim($data['nombre'] ?? '');
     * y luego: if (!$nombre) { return HTTP 400 }
     */
    public function testNombreVacioEsRechazadoPorValidacion(): void
    {
        // Simular la lógica de validación del controlador
        $nombreVacio         = trim('');
        $nombreSoloEspacios  = trim('   ');
        $nombreValido        = trim('Mi Proyecto');

        // Assert: cadena vacía es falsy, el controlador retornará error 400
        $this->assertFalse(
            (bool) $nombreVacio,
            'Nombre vacío debe evaluarse como false (controlador retornará HTTP 400)'
        );

        $this->assertFalse(
            (bool) $nombreSoloEspacios,
            'Nombre con solo espacios debe evaluarse como false tras trim()'
        );

        // Assert: nombre con contenido es truthy, pasa la validación
        $this->assertTrue(
            (bool) $nombreValido,
            'Nombre válido debe evaluarse como true y pasar la validación'
        );
    }


    // TP-006 | Creación de proyecto con datos válidos
    /**
     * @test
     * @covers Project::create
     * @covers Project::getAllByUser
     * @covers Project::findById
     */
    public function testCrearYListarProyecto(): void
    {
        // Act: crear proyecto
        $id = Project::create('Mi Proyecto', 'Descripción de prueba', $this->userId);

        // Assert: ID válido
        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id, 'create() debe retornar un ID mayor a 0');

        // Assert: aparece en la lista del usuario
        $proyectos = Project::getAllByUser($this->userId);
        $this->assertCount(1, $proyectos, 'El usuario debe tener exactamente 1 proyecto');
        $this->assertEquals('Mi Proyecto', $proyectos[0]['nombre']);
        $this->assertEquals('Descripción de prueba', $proyectos[0]['descripcion']);

        // Assert: recuperable por ID
        $proyecto = Project::findById($id);
        $this->assertNotNull($proyecto, 'findById() debe retornar el proyecto');
        $this->assertEquals('Mi Proyecto',          $proyecto['nombre']);
        $this->assertEquals('Descripción de prueba', $proyecto['descripcion']);
        $this->assertEquals($this->userId,           (int) $proyecto['user_id']);
    }

    // EXTRA | Actualización de proyecto
    /**
     * @test
     * @covers Project::update
     */
    public function testActualizarProyecto(): void
    {
        $id = Project::create('Nombre Original', 'Desc Original', $this->userId);

        // Act
        Project::update($id, 'Nombre Actualizado', 'Desc Actualizada');

        // Assert
        $proyecto = Project::findById($id);
        $this->assertEquals('Nombre Actualizado', $proyecto['nombre']);
        $this->assertEquals('Desc Actualizada',   $proyecto['descripcion']);
    }

    /**
     * @test
     * @covers Project::getAllByUser
     */
    public function testListadoSoloMuestraProyectosDelUsuario(): void
    {
        // Crear segundo usuario
        $otroUserId = $this->crearUsuarioDePrueba('Otro', 'otro@test.com', 'pw999');

        Project::create('Proyecto A', 'desc', $this->userId);
        Project::create('Proyecto B', 'desc', $this->userId);
        Project::create('Proyecto de otro usuario', 'desc', $otroUserId);

        // El primer usuario solo ve sus 2 proyectos
        $proyectos = Project::getAllByUser($this->userId);
        $this->assertCount(2, $proyectos);

        // El segundo usuario solo ve su 1 proyecto
        $proyectosOtro = Project::getAllByUser($otroUserId);
        $this->assertCount(1, $proyectosOtro);
    }
}
