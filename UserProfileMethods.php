<?php

/**
 * NOTA DE INTEGRACIÓN:
 * Este archivo asume que ya existe una clase User (probablemente en
 * src/Models/User.php) con, al menos, un método para autenticar login
 * (algo como User::findByEmail($email)) y una conexión PDO obtenida vía
 * una clase Database (el mismo patrón que usa Damián para password_resets
 * y Sebastián/Joel para sus tablas).
 *
 * Si la clase User ya existe, copia ÚNICAMENTE los métodos nuevos
 * (findById, updateName, updatePassword, verifyCurrentPassword) dentro
 * de la clase existente, en vez de pegar este archivo completo.
 */

namespace App\Models;

use App\Database;
use PDO;

class User
{
    /**
     * Busca un usuario por su id. Necesario para precargar el formulario
     * de perfil con el nombre y el email actuales.
     */
    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare(
            'SELECT id, name, email, created_at FROM users WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    /**
     * Actualiza únicamente el nombre del usuario.
     */
    public static function updateName(int $id, string $name): bool
    {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare(
            'UPDATE users SET name = :name, updated_at = :updated_at WHERE id = :id'
        );

        return $stmt->execute([
            'name'       => $name,
            'updated_at' => date('Y-m-d H:i:s'),
            'id'         => $id,
        ]);
    }

    /**
     * Verifica que la contraseña en texto plano corresponda al hash
     * almacenado para ese usuario. Se usa antes de permitir el cambio
     * de contraseña.
     */
    public static function verifyCurrentPassword(int $id, string $plainPassword): bool
    {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare('SELECT password FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return false;
        }

        return password_verify($plainPassword, $row['password']);
    }

    /**
     * Actualiza la contraseña del usuario. Recibe la contraseña en texto
     * plano y se encarga de hashearla con password_hash (mismo algoritmo
     * que debería estar usando AuthController en el registro/login).
     */
    public static function updatePassword(int $id, string $newPlainPassword): bool
    {
        $pdo = Database::getConnection();

        $hashed = password_hash($newPlainPassword, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare(
            'UPDATE users SET password = :password, updated_at = :updated_at WHERE id = :id'
        );

        return $stmt->execute([
            'password'   => $hashed,
            'updated_at' => date('Y-m-d H:i:s'),
            'id'         => $id,
        ]);
    }
}
