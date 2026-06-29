<?php

namespace App\Controllers;

use App\Models\User;
use App\Exceptions\ValidationException;
use App\Exceptions\UnauthorizedException;
use App\Auth; // helper de sesión usado por el resto del proyecto (AuthController)

/**
 * NOTA DE INTEGRACIÓN:
 * - Se asume que existe un helper App\Auth con un método estático
 *   Auth::user() que devuelve el array/objeto del usuario autenticado
 *   (id, name, email) tomado de la sesión, igual que lo usa AuthController
 *   para proteger rutas. Ajusta el nombre si en el proyecto se llama distinto
 *   (por ejemplo $_SESSION['user_id'] directamente).
 * - Se asume una clase base Controller con un método render($view, $data)
 *   que incluye la vista correspondiente. Si el proyecto usa otra firma
 *   (p. ej. view()), renombra la llamada en show().
 * - Igual que pide la tarea de Denisse, este controlador NO hace try/catch
 *   de errores "a mano": lanza ValidationException / UnauthorizedException
 *   y deja que el manejador global de excepciones (o el front controller)
 *   las traduzca a la respuesta HTTP correspondiente, tal como debería
 *   hacerlo AuthController.
 */
class ProfileController extends Controller
{
    /**
     * GET /profile
     * Muestra el formulario de edición de perfil con los datos actuales.
     */
    public function show()
    {
        $authUser = Auth::user();

        if (!$authUser) {
            throw new UnauthorizedException('Debes iniciar sesión para ver tu perfil.');
        }

        $user = User::findById($authUser['id']);

        return $this->render('profile/show', [
            'user' => $user,
        ]);
    }

    /**
     * POST /profile/name
     * Actualiza el nombre del usuario autenticado.
     */
    public function updateName()
    {
        $authUser = Auth::user();

        if (!$authUser) {
            throw new UnauthorizedException('Debes iniciar sesión para realizar esta acción.');
        }

        $name = trim($_POST['name'] ?? '');

        if ($name === '') {
            throw new ValidationException('El nombre no puede estar vacío.');
        }

        if (mb_strlen($name) > 100) {
            throw new ValidationException('El nombre no puede tener más de 100 caracteres.');
        }

        User::updateName($authUser['id'], $name);

        // Si la sesión guarda el nombre en caché, se refresca aquí también.
        if (method_exists(Auth::class, 'refresh')) {
            Auth::refresh($authUser['id']);
        }

        return $this->redirectWithMessage('/profile', 'success', 'Tu nombre se actualizó correctamente.');
    }

    /**
     * POST /profile/password
     * Cambia la contraseña del usuario autenticado, verificando primero
     * que la contraseña actual sea correcta.
     */
    public function updatePassword()
    {
        $authUser = Auth::user();

        if (!$authUser) {
            throw new UnauthorizedException('Debes iniciar sesión para realizar esta acción.');
        }

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword     = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            throw new ValidationException('Todos los campos de contraseña son obligatorios.');
        }

        if (mb_strlen($newPassword) < 8) {
            throw new ValidationException('La nueva contraseña debe tener al menos 8 caracteres.');
        }

        if ($newPassword !== $confirmPassword) {
            throw new ValidationException('La confirmación de la contraseña no coincide.');
        }

        // 1) Verificar que la contraseña actual sea correcta antes de cambiar nada.
        if (!User::verifyCurrentPassword($authUser['id'], $currentPassword)) {
            throw new UnauthorizedException('La contraseña actual es incorrecta.');
        }

        // 2) Evitar que la "nueva" contraseña sea exactamente igual a la actual.
        if ($currentPassword === $newPassword) {
            throw new ValidationException('La nueva contraseña debe ser diferente a la actual.');
        }

        // 3) Actualizar.
        User::updatePassword($authUser['id'], $newPassword);

        return $this->redirectWithMessage('/profile', 'success', 'Tu contraseña se actualizó correctamente.');
    }
}
