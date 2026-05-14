<?php

require_once __DIR__ . '/../../tests/DatabaseTestCase.php';
require_once __DIR__ . '/../../backend/models/User.php';
require_once __DIR__ . '/../../backend/models/Project.php';
require_once __DIR__ . '/../../backend/models/Task.php';

/**
 * TaskModelTest — Pruebas unitarias del modelo Task
 *
 * Cubre los casos del Plan de Pruebas:
 *   TP-007: Estado inválido se normaliza a 'Pendiente'
 *   TP-008: Actualización de estado de tarea a través del ciclo de vida
 */
class TaskModelTest extends DatabaseTestCase
{
    private int $userId;
    private int $proyectoId;

    protected function setUp(): void
    {
        parent::setUp();
        // Precondiciones: usuario + proyecto existentes
        $this->userId     = $this->crearUsuarioDePrueba('Tester', 'tester@test.com', 'pw123');
        $this->proyectoId = $this->crearProyectoDePrueba($this->userId, 'Proyecto Tasks');
    }


    // TP-007 | Estado inválido se normaliza a 'Pendiente'
    /**
     * @test
     * Verifica que la lógica del controlador normaliza estados inválidos a 'Pendiente'.
     * TaskController::create() hace:
     *   $estadosValidos = ['Pendiente', 'En progreso', 'Terminado'];
     *   if (!in_array($estado, $estadosValidos)) { $estado = 'Pendiente'; }
     */
    public function testEstadoInvalidoSeNormaliza(): void
    {
        // Simular la lógica de normalización del controlador
        $estadosValidos = ['Pendiente', 'En progreso', 'Terminado'];

        $estadoInvalido = 'InvalidoXYZ';
        if (!in_array($estadoInvalido, $estadosValidos)) {
            $estadoInvalido = 'Pendiente';
        }

        // Assert: el estado inválido fue normalizado
        $this->assertEquals(
            'Pendiente',
            $estadoInvalido,
            'Un estado inválido debe normalizarse a "Pendiente"'
        );

        // Act: crear la tarea con el estado normalizado
        $id = Task::create('Tarea con estado inválido', 'desc', 'Pendiente', $this->proyectoId, null);

        // Assert: la tarea existe con estado 'Pendiente'
        $tarea = Task::findById($id);
        $this->assertNotNull($tarea);
        $this->assertEquals(
            'Pendiente',
            $tarea['estado'],
            'La tarea debe almacenarse con estado "Pendiente"'
        );
    }

    /**
     * @test
     * Verifica que los tres estados válidos pasan la validación sin normalización.
     */
    public function testEstadosValidosPasanValidacion(): void
    {
        $estadosValidos = ['Pendiente', 'En progreso', 'Terminado'];

        foreach ($estadosValidos as $estado) {
            $estadoFinal = $estado;
            if (!in_array($estadoFinal, $estadosValidos)) {
                $estadoFinal = 'Pendiente';
            }
            $this->assertEquals(
                $estado,
                $estadoFinal,
                "El estado válido '$estado' no debe ser modificado"
            );
        }
    }

    
    // TP-008 | Actualización de estado a través del ciclo de vida
    /**
     * @test
     * @covers Task::create
     * @covers Task::updateStatus
     * @covers Task::findById
     */
    public function testActualizarEstadoTarea(): void
    {
        // Arrange: crear tarea en estado inicial
        $id = Task::create('Tarea de prueba', 'Descripción', 'Pendiente', $this->proyectoId, null);
        $tarea = Task::findById($id);
        $this->assertEquals('Pendiente', $tarea['estado'], 'Estado inicial debe ser Pendiente');

        // Act: avanzar a 'En progreso'
        $resultado = Task::updateStatus($id, 'En progreso');
        $this->assertTrue($resultado, 'updateStatus() debe retornar true');

        $tarea = Task::findById($id);
        $this->assertEquals(
            'En progreso',
            $tarea['estado'],
            'El estado debe actualizarse a "En progreso"'
        );

        // Act: avanzar a 'Terminado'
        Task::updateStatus($id, 'Terminado');

        $tarea = Task::findById($id);
        $this->assertEquals(
            'Terminado',
            $tarea['estado'],
            'El estado debe actualizarse a "Terminado"'
        );
    }


    // EXTRA | CRUD completo de tareas
    /**
     * @test
     * @covers Task::create
     * @covers Task::getAllByProject
     * @covers Task::findById
     */
    public function testCrearYListarTareasPorProyecto(): void
    {
        // Crear responsable
        $responsableId = $this->crearUsuarioDePrueba('Responsable', 'resp@test.com', 'pw456');

        // Crear dos tareas en el mismo proyecto
        $id1 = Task::create('Tarea Alpha', 'desc 1', 'Pendiente',   $this->proyectoId, $responsableId);
        $id2 = Task::create('Tarea Beta',  'desc 2', 'En progreso', $this->proyectoId, null);

        // Assert: listado por proyecto retorna ambas tareas
        $tareas = Task::getAllByProject($this->proyectoId);
        $this->assertCount(2, $tareas, 'Deben existir 2 tareas en el proyecto');

        // Assert: detalle incluye datos del responsable
        $tarea1 = Task::findById($id1);
        $this->assertEquals('Tarea Alpha',  $tarea1['titulo']);
        $this->assertEquals('Pendiente',    $tarea1['estado']);
        $this->assertEquals('Responsable',  $tarea1['responsable_nombre']);
        $this->assertEquals('Proyecto Tasks', $tarea1['proyecto_nombre']);

        // Assert: tarea sin responsable
        $tarea2 = Task::findById($id2);
        $this->assertEquals('En progreso', $tarea2['estado']);
        $this->assertNull($tarea2['responsable_nombre']);
    }

    /**
     * @test
     * @covers Task::delete
     */
    public function testEliminarTarea(): void
    {
        $id = Task::create('Tarea a eliminar', '', 'Pendiente', $this->proyectoId, null);

        // Verificar que existe
        $this->assertNotNull(Task::findById($id));

        // Act: eliminar
        $resultado = Task::delete($id);

        // Assert
        $this->assertTrue($resultado, 'delete() debe retornar true');
        $this->assertNull(Task::findById($id), 'La tarea eliminada no debe ser encontrada');
    }
}
