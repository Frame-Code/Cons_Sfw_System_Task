<?php

/**
 * MTasking — Excepciones personalizadas
 *
 * Jerarquía:
 *   AppException                  (base del sistema)
 *   ├── AuthException             (errores de autenticación)
 *   │   ├── EmailDuplicadoException
 *   │   └── CredencialesInvalidasException
 *   └── RecursoNoEncontradoException (entidad inexistente)
 */


// ── Excepción base ────────────────────────────────────────────────────────────

class AppException extends RuntimeException
{
    protected int $httpCode;

    public function __construct(string $mensaje, int $httpCode = 500, ?\Throwable $anterior = null)
    {
        parent::__construct($mensaje, $httpCode, $anterior);
        $this->httpCode = $httpCode;
    }

    /** Código HTTP sugerido para la respuesta al cliente */
    public function getHttpCode(): int
    {
        return $this->httpCode;
    }

    /** Convierte la excepción en respuesta JSON estándar */
    public function toJson(): string
    {
        return json_encode(['error' => $this->getMessage()]);
    }
}


// ── Excepciones de autenticación ──────────────────────────────────────────────

class AuthException extends AppException
{
    public function __construct(string $mensaje, ?\Throwable $anterior = null)
    {
        parent::__construct($mensaje, 401, $anterior);
    }
}

class EmailDuplicadoException extends AuthException
{
    public function __construct(string $email)
    {
        parent::__construct("Ya existe una cuenta registrada con el email: $email");
        // Sobreescribimos el código HTTP: conflicto, no fallo de autenticación
        $this->code     = 409;
        $this->httpCode = 409;
    }
}

class CredencialesInvalidasException extends AuthException
{
    public function __construct()
    {
        parent::__construct('Email o contraseña incorrectos.');
    }
}


// ── Excepción de recurso no encontrado ────────────────────────────────────────

class RecursoNoEncontradoException extends AppException
{
    public function __construct(string $recurso, int|string $id)
    {
        parent::__construct("$recurso con id=$id no encontrado.", 404);
    }
}
