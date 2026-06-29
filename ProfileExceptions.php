<?php

/**
 * NOTA DE INTEGRACIÓN:
 * Denisse está refactorizando los controladores para usar la jerarquía de
 * AppException que ya existe en el proyecto (tomando AuthController como
 * referencia). Estas dos excepciones siguen ese mismo patrón para que el
 * módulo de Perfil sea consistente con el resto de la aplicación.
 *
 * Si en el proyecto real estas clases ya existen (por ejemplo porque
 * AuthController ya lanza una excepción de "credenciales inválidas"),
 * NO las dupliques: borra este archivo y usa las existentes, ajustando
 * los nombres en ProfileController.php.
 */

namespace App\Exceptions;

/**
 * Se lanza cuando los datos enviados por el usuario no son válidos
 * (campos vacíos, contraseña muy corta, confirmación no coincide, etc.)
 * Debe traducirse a una respuesta HTTP 422.
 */
class ValidationException extends AppException
{
    protected $statusCode = 422;
}

/**
 * Se lanza cuando el usuario no tiene permiso para realizar la acción,
 * por ejemplo cuando la "contraseña actual" no coincide con la almacenada.
 * Debe traducirse a una respuesta HTTP 401/403.
 */
class UnauthorizedException extends AppException
{
    protected $statusCode = 401;
}
