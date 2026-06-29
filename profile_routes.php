<?php

/**
 * NOTA DE INTEGRACIÓN:
 * Estas líneas se agregan al archivo de rutas principal del proyecto
 * (routes.php / web.php, el mismo que usan Sebastián para /comments,
 * Joel para /tasks/{id}/history y Diego para /metrics).
 *
 * Ajusta la sintaxis exacta del router (este ejemplo usa una firma
 * genérica $router->get()/post() con middleware 'auth', muy común en
 * routers caseros de PHP; cámbiala si el proyecto usa otra convención).
 */

// --- Perfil de usuario (requiere sesión iniciada) -------------------------
$router->get('/profile', [ProfileController::class, 'show'])->middleware('auth');
$router->post('/profile/name', [ProfileController::class, 'updateName'])->middleware('auth');
$router->post('/profile/password', [ProfileController::class, 'updatePassword'])->middleware('auth');
