<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;

/**
 * NOTA DE INTEGRACIÓN:
 * Esqueleto de pruebas de integración para el módulo de Perfil, pensado
 * para encajar con la suite que Denisse está armando con PHPUnit +
 * SQLite. Ajusta el bootstrap/conexión a la misma base de pruebas
 * (Database::setTestConnection() o como se llame en el proyecto) y la
 * forma de simular sesión/autenticación que ya exista para los demás
 * controladores.
 */
class ProfileControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // TODO: inicializar conexión SQLite de pruebas y migrar tabla `users`,
        // igual que se haga en el resto de tests (AuthControllerTest, etc).
        // TODO: crear un usuario de prueba e iniciar sesión simulada.
    }

    public function testUsuarioAutenticadoPuedeVerSuPerfil(): void
    {
        $this->markTestIncomplete('Pendiente: conectar con el bootstrap real de tests del proyecto.');
    }

    public function testActualizaNombreCorrectamente(): void
    {
        $this->markTestIncomplete('Pendiente: conectar con el bootstrap real de tests del proyecto.');
        // Esperado: POST /profile/name con name="Nuevo Nombre" -> users.name actualizado.
    }

    public function testRechazaNombreVacio(): void
    {
        $this->markTestIncomplete('Pendiente: conectar con el bootstrap real de tests del proyecto.');
        // Esperado: POST /profile/name con name="" -> ValidationException / 422.
    }

    public function testCambiaContrasenaConContrasenaActualCorrecta(): void
    {
        $this->markTestIncomplete('Pendiente: conectar con el bootstrap real de tests del proyecto.');
        // Esperado: POST /profile/password con current_password correcta y
        // new_password/confirm_password válidas -> password_verify() pasa con el nuevo valor.
    }

    public function testRechazaCambioDeContrasenaSiActualEsIncorrecta(): void
    {
        $this->markTestIncomplete('Pendiente: conectar con el bootstrap real de tests del proyecto.');
        // Esperado: current_password incorrecta -> UnauthorizedException / 401.
    }

    public function testRechazaSiConfirmacionNoCoincide(): void
    {
        $this->markTestIncomplete('Pendiente: conectar con el bootstrap real de tests del proyecto.');
        // Esperado: new_password !== confirm_password -> ValidationException / 422.
    }

    public function testRechazaAccesoSiNoHaySesion(): void
    {
        $this->markTestIncomplete('Pendiente: conectar con el bootstrap real de tests del proyecto.');
        // Esperado: sin usuario autenticado -> UnauthorizedException / 401 en las 3 rutas.
    }
}
