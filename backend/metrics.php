<?php
// backend/metrics.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Para evitar problemas de CORS

// 1. Configuración directa de conexión para XAMPP
$host = 'localhost';
$dbname = 'mtasking';
$username = 'root'; // Usuario por defecto de XAMPP
$password = '';     // Sin contraseña por defecto en XAMPP

try {
    // Crear la conexión PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. Obtener el ID del proyecto de la URL
    $proyecto_id = $_GET['proyecto_id'] ?? null;

    if (!$proyecto_id) {
        echo json_encode(['error' => 'Falta el ID del proyecto']);
        exit;
    }

    // 3. Consultar las estadísticas
    $query = "
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN estado = 'Pendiente' THEN 1 ELSE 0 END) as pendientes,
            SUM(CASE WHEN estado = 'En progreso' THEN 1 ELSE 0 END) as en_progreso,
            SUM(CASE WHEN estado = 'Terminado' THEN 1 ELSE 0 END) as terminados
        FROM tasks 
        WHERE proyecto_id = :proyecto_id
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute(['proyecto_id' => $proyecto_id]);
    $metrics = $stmt->fetch(PDO::FETCH_ASSOC);

    // Asegurarse de que sean números enteros
    $total = (int)$metrics['total'];
    $pendientes = (int)$metrics['pendientes'];
    $en_progreso = (int)$metrics['en_progreso'];
    $terminados = (int)$metrics['terminados'];

    // Calcular porcentaje
    $porcentaje = $total > 0 ? round(($terminados / $total) * 100) : 0;

    // 4. Devolver los datos al frontend
    echo json_encode([
        'total' => $total,
        'pendientes' => $pendientes,
        'en_progreso' => $en_progreso,
        'terminados' => $terminados,
        'porcentaje_completitud' => $porcentaje
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Error de conexión BD: ' . $e->getMessage()]);
}
?>