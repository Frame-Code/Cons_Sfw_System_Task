<?php

require_once __DIR__ . '/../config/database.php';

/*Responsable: De la Vega Sanchez Damian Alejandro*
Campo: Flujo de Recuperacion de Contraseña*/

class PasswordReset {

    //Guarda un token nuevo para el email
    public static function crear(string $email, string $token, string $expiraEn): bool {
        $db = getDB();

        //Elimina token anterior del mismo email
        $db->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);

        //Inserta el nuevo
        $stmt = $db->prepare("
            INSERT INTO password_resets (email, token, expires_at)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$email, $token, $expiraEn]);
        return $stmt->rowCount() > 0;
    }

    //Busca un token valido
    public static function buscarToken(string $token): ?array {
        $db   = getDB();
        $ahora = date('Y-m-d H:i:s');
        $stmt = $db->prepare("
            SELECT * FROM password_resets
            WHERE token = ? AND expires_at > ?
            LIMIT 1
        ");
        $stmt->execute([$token, $ahora]);
        $fila = $stmt->fetch();
        return $fila ?: null;
    }

    //Elimina el token tras su uso
    public static function eliminarToken(string $token): void {
        $db = getDB();
        $db->prepare("DELETE FROM password_resets WHERE token = ?")->execute([$token]);
    }
}