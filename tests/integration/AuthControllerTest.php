<?php

require_once __DIR__ . '/../DatabaseTestCase.php';
require_once __DIR__ . '/../../backend/controllers/AuthController.php';

class AuthControllerTest extends DatabaseTestCase
{
    public function testRegistroUsuario(): void
    {
        $id = User::create(
            'Usuario Test',
            'usuario@test.com',
            '123456'
        );

        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);
    }

    public function testBuscarUsuarioPorEmail(): void
    {
        $this->crearUsuarioDePrueba(
            'Denisse',
            'denisse@test.com'
        );

        $user = User::findByEmail('denisse@test.com');

        $this->assertNotNull($user);
        $this->assertEquals('Denisse', $user['nombre']);
    }

    public function testPasswordHash(): void
    {
        $this->crearUsuarioDePrueba(
            'User',
            'user@test.com',
            '123456'
        );

        $user = User::findByEmail('user@test.com');

        $this->assertTrue(
            password_verify(
                '123456',
                $user['password']
            )
        );
    }
}