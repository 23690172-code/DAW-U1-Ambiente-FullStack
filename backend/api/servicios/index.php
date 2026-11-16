<?php
declare(strict_types=1);
require_once __DIR__.'/../../config/database.php';
require_once __DIR__.'/../../helpers/response.php';
require_once __DIR__.'/../../helpers/auth.php';

$user = requireAuth(); 

try {
    $pdo = getPDO();
    
    if ($user['rol'] === 'CLIENTE') {
        $stmt = $pdo->query('SELECT id, nombre, descripcion, precio, duracion_minutos FROM servicios WHERE activo = 1 ORDER BY nombre ASC');
    } else {
        $stmt = $pdo->query('SELECT id, nombre, descripcion, precio, duracion_minutos, activo FROM servicios ORDER BY nombre ASC');
    }
    
    $servicios = $stmt->fetchAll();
    jsonResponse(200, ['ok' => true, 'data' => $servicios]);
} catch (Throwable $e) {
    jsonResponse(500, ['ok' => false, 'message' => 'Error al consultar servicios']);
}