<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../exceptions/AppException.php';

class AuthController
{
    // -- Registro -------------------------------------------------------------

    public static function register(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            $nombre   = trim($data['nombre']   ?? '');
            $email    = trim($data['email']    ?? '');
            $password = trim($data['password'] ?? '');

            // Validaciones basicas (lanzamos AppException con 400)
            if (!$nombre || !$email || !$password) {
                throw new AppException('Todos los campos son obligatorios.', 400);
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new AppException('El formato del email no es valido.', 400);
            }

            if (strlen($password) < 6) {
                throw new AppException('La contrasena debe tener al menos 6 caracteres.', 400);
            }

            // Email duplicado -> excepcion personalizada 409
            if (User::findByEmail($email)) {
                throw new EmailDuplicadoException($email);
            }

            $id = User::create($nombre, $email, $password);

            http_response_code(201);
            echo json_encode(['message' => 'Usuario registrado correctamente.', 'id' => $id]);

        } catch (AppException $e) {
            http_response_code($e->getHttpCode());
            echo $e->toJson();
        }
    }


    // -- Login ----------------------------------------------------------------

    public static function login(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            $email    = trim($data['email']    ?? '');
            $password = trim($data['password'] ?? '');

            if (!$email || !$password) {
                throw new AppException('Email y contrasena son obligatorios.', 400);
            }

            $user = User::findByEmail($email);

            // Credenciales incorrectas -> excepcion personalizada 401
            if (!$user || !User::verifyPassword($password, $user['password'])) {
                throw new CredencialesInvalidasException();
            }

            session_start();
            $_SESSION['user_id']     = $user['id'];
            $_SESSION['user_nombre'] = $user['nombre'];

            echo json_encode([
                'message' => 'Login exitoso.',
                'user'    => [
                    'id'     => $user['id'],
                    'nombre' => $user['nombre'],
                    'email'  => $user['email'],
                ],
            ]);

        } catch (AppException $e) {
            http_response_code($e->getHttpCode());
            echo $e->toJson();
        }
    }


    // -- Logout ---------------------------------------------------------------

    public static function logout(): void
    {
        session_start();
        session_destroy();
        echo json_encode(['message' => 'Sesion cerrada.']);
    }


    // -- Perfil propio --------------------------------------------------------

    public static function me(): void
    {
        try {
            session_start();

            if (empty($_SESSION['user_id'])) {
                throw new AuthException('Debes iniciar sesion para acceder a este recurso.');
            }

            $user = User::findById((int) $_SESSION['user_id']);

            if (!$user) {
                throw new RecursoNoEncontradoException('Usuario', $_SESSION['user_id']);
            }

            echo json_encode(['user' => $user]);

        } catch (AppException $e) {
            http_response_code($e->getHttpCode());
            echo $e->toJson();
        }
    }

/*Responsable del bloque: De la Vega Sanchez Damian Alejandro*
Campo: Flujo de Recuperacion de Contraseña*/

// -- Solicitar recuperación de contraseña ------------------------------------

public static function solicitarReset(): void
{
    try {
        $data  = json_decode(file_get_contents('php://input'), true);
        $email = trim($data['email'] ?? '');

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new AppException('Email inválido.', 400);
        }

        // Respuesta generica siempre
        $respuesta = ['message' => 'Si el email está registrado, recibirá un enlace.'];

        $user = User::findByEmail($email);
        if (!$user) {
            echo json_encode($respuesta);
            return;
        }

        // Generar token único y expiración de 1 hora
        $token     = bin2hex(random_bytes(32));
        $expiraEn  = date('Y-m-d H:i:s', strtotime('+1 hour'));

        require_once __DIR__ . '/../models/PasswordReset.php';
        require_once __DIR__ . '/../services/MailService.php';
        PasswordReset::crear($email, $token, $expiraEn);

        if (!MailService::enviarResetPassword($email, $token)) {
            error_log("MTasking: no se pudo enviar el correo de recuperación a $email");
        }

        echo json_encode($respuesta);

    } catch (AppException $e) {
        http_response_code($e->getHttpCode());
        echo $e->toJson();
    }
}


// -- Restablecer contraseña con token ----------------------------------------

public static function restablecerPassword(): void
{
    try {
        $data     = json_decode(file_get_contents('php://input'), true);
        $token    = trim($data['token']    ?? '');
        $password = trim($data['password'] ?? '');

        if (!$token || !$password) {
            throw new AppException('Datos incompletos.', 400);
        }

        if (strlen($password) < 6) {
            throw new AppException('La contraseña debe tener al menos 6 caracteres.', 400);
        }

        require_once __DIR__ . '/../models/PasswordReset.php';
        $registro = PasswordReset::buscarToken($token);

        if (!$registro) {
            throw new AppException('Token inválido o expirado.', 400);
        }

        // Actualizar contraseña del usuario
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $db   = getDB();
        $db->prepare("UPDATE users SET password = ? WHERE email = ?")
           ->execute([$hash, $registro['email']]);

        // Eliminar el token usado
        PasswordReset::eliminarToken($token);

        echo json_encode(['message' => 'Contraseña actualizada correctamente.']);

    } catch (AppException $e) {
        http_response_code($e->getHttpCode());
        echo $e->toJson();
    }
}

}