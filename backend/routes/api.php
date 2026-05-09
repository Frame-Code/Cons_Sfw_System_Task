<?php

require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/ProjectController.php';
require_once __DIR__ . '/../controllers/TaskController.php';

$method = $_SERVER['REQUEST_METHOD'];

// Usar PATH_INFO si está disponible (más fiable), si no, recortar REQUEST_URI
if (!empty($_SERVER['PATH_INFO'])) {
    $uri = $_SERVER['PATH_INFO'];
} else {
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    // Eliminar todo lo que haya antes de index.php (cualquier cantidad de segmentos)
    $uri = preg_replace('#^.*/index\.php#', '', $uri);
}

$uri = rtrim($uri, '/') ?: '/';

// ========================
//  RUTAS DE AUTENTICACIÓN
// ========================
if ($uri === '/auth/register' && $method === 'POST') {
    AuthController::register();

} elseif ($uri === '/auth/login' && $method === 'POST') {
    AuthController::login();

} elseif ($uri === '/auth/logout' && $method === 'POST') {
    AuthController::logout();

} elseif ($uri === '/auth/me' && $method === 'GET') {
    AuthController::me();

// ========================
//  RUTAS DE PROYECTOS
// ========================
} elseif ($uri === '/projects' && $method === 'POST') {
    ProjectController::create();

} elseif ($uri === '/projects' && $method === 'GET') {
    ProjectController::list();

} elseif (preg_match('#^/projects/(\d+)$#', $uri, $m) && $method === 'GET') {
    ProjectController::detail((int) $m[1]);

} elseif (preg_match('#^/projects/(\d+)$#', $uri, $m) && $method === 'PUT') {
    ProjectController::update((int) $m[1]);

// ========================
//  RUTAS DE TAREAS
// ========================
} elseif ($uri === '/tasks' && $method === 'POST') {
    TaskController::create();

} elseif (preg_match('#^/projects/(\d+)/tasks$#', $uri, $m) && $method === 'GET') {
    TaskController::listByProject((int) $m[1]);

} elseif (preg_match('#^/tasks/(\d+)$#', $uri, $m) && $method === 'GET') {
    TaskController::detail((int) $m[1]);

} elseif (preg_match('#^/tasks/(\d+)/status$#', $uri, $m) && $method === 'PUT') {
    TaskController::updateStatus((int) $m[1]);

} elseif (preg_match('#^/tasks/(\d+)$#', $uri, $m) && $method === 'DELETE') {
    TaskController::delete((int) $m[1]);

} elseif ($uri === '/users' && $method === 'GET') {
    TaskController::users();

// ========================
//  RUTA NO ENCONTRADA
// ========================
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Ruta no encontrada.']);
}
