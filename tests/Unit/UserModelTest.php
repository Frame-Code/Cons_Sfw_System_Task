<?php

require_once __DIR__ . '/../../tests/DatabaseTestCase.php';
require_once __DIR__ . '/../../backend/models/User.php';

/**
 * UserModelTest — Pruebas unitarias del modelo User
 *
 * Cubre los casos del Plan de Pruebas:
 *   TP-001: Registro de usuario con datos válidos
 *   TP-002: Registro con email duplicado
 *   TP-003: Login con credenciales correctas
 *   TP-004: Login con contraseña incorrecta
 */
class UserModelTest extends DatabaseTestCase
{
    // TP-001 | Registro de usuario con datos válidos
    /**
     * @test
     * @covers User::create
     * @covers User::findByEmail
     * @covers User::verifyPassword
     */
    public function testCrearUsuarioYVerificarPassword(): void
    {
        // Arrange
        $nombre   = 'Juan Pérez';
        $email    = 'juan@test.com';
        $password = 'secret123';

        // Act: crear el usuario
        $id = User::create($nombre, $email, $password);

        // Assert: se obtiene un ID válido
        $this->assertIsInt($id, 'create() debe retornar un entero');
        $this->assertGreaterThan(0, $id, 'El ID debe ser mayor a 0');

        // Assert: el usuario puede ser encontrado por email
        $user = User::findByEmail($email);
        $this->assertNotNull($user, 'findByEmail() debe retornar el usuario creado');
        $this->assertEquals($nombre, $user['nombre']);
        $this->assertEquals($email,  $user['email']);

        // Assert: la contraseña se almacenó con hash (NO en texto plano)
        $this->assertNotEquals(
            $password,
            $user['password'],
            'La contraseña NO debe guardarse en texto plano'
        );

        // Assert: bcrypt verifica correctamente
        $this->assertTrue(
            User::verifyPassword($password, $user['password']),
            'verifyPassword() debe retornar true con la contraseña correcta'
        );
    }


    // TP-002 | Registro con email duplicado
    /**
     * @test
     * @covers User::create
     */
    public function testEmailDuplicadoLanzaExcepcion(): void
    {
        // Arrange: primer usuario
        User::create('Usuario Uno', 'duplicado@test.com', 'pass111');

        // Assert: el segundo intento con el mismo email lanza excepción PDO
        $this->expectException(\PDOException::class);

        // Act: intentar crear otro usuario con el mismo email (UNIQUE constraint)
        User::create('Usuario Dos', 'duplicado@test.com', 'pass222');
    }


    // TP-003 | Login con credenciales correctas
    /**
     * @test
     * @covers User::findByEmail
     * @covers User::verifyPassword
     */
    public function testLoginCredencialesCorrectas(): void
    {
        // Arrange: crear usuario
        $email    = 'login@test.com';
        $password = 'pass1234';
        User::create('Login User', $email, $password);

        // Act
        $user   = User::findByEmail($email);
        $result = User::verifyPassword($password, $user['password']);

        // Assert
        $this->assertNotNull($user, 'El usuario debe existir en la DB');
        $this->assertTrue($result, 'verifyPassword() debe retornar true con password correcta');
        $this->assertEquals('Login User', $user['nombre']);
        $this->assertEquals($email, $user['email']);
    }

    
    // TP-004 | Login con contraseña incorrecta
    /**
     * @test
     * @covers User::verifyPassword
     */
    public function testLoginContrasenaIncorrecta(): void
    {
        // Arrange
        $email           = 'login2@test.com';
        $passwordCorrecta = 'correcta99';
        User::create('Login User 2', $email, $passwordCorrecta);

        // Act
        $user   = User::findByEmail($email);
        $result = User::verifyPassword('incorrecta', $user['password']);

        // Assert
        $this->assertFalse(
            $result,
            'verifyPassword() debe retornar false cuando la contraseña no coincide'
        );
    }

    
    // EXTRA | findById y getAll
    /**
     * @test
     * @covers User::findById
     * @covers User::getAll
     */
    public function testFindByIdYGetAll(): void
    {
        $id1 = User::create('Ana García',   'ana@test.com',   'pw123');
        $id2 = User::create('Pedro López',  'pedro@test.com', 'pw456');

        // findById retorna el usuario correcto
        $user = User::findById($id1);
        $this->assertNotNull($user);
        $this->assertEquals('Ana García', $user['nombre']);
        $this->assertArrayNotHasKey('password', $user, 'findById NO debe exponer password');

        // getAll retorna todos los usuarios
        $all = User::getAll();
        $this->assertCount(2, $all);
    }
}
