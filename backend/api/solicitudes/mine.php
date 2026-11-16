<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/auth.php';

$user = requireRole(['CLIENTE']);

try {
    $pdo = getPDO();
    
    $stmt = $pdo->prepare(
        'SELECT 
            ss.id, 
            ss.servicio_id, 
            s.nombre AS servicio,
            ss.precio_contratado, 
            ss.fecha_solicitud,
            ss.fecha_programada, 
            ss.fecha_inicio, 
            ss.fecha_fin,
            ss.estado, 
            ss.observaciones_cliente, 
            ss.observaciones_admin
         FROM solicitudes_servicio ss
         INNER JOIN servicios s ON s.id = ss.servicio_id
         WHERE ss.usuario_id = :usuario
         ORDER BY ss.id DESC'
    );
    
    $stmt->execute([':usuario' => $user['id']]);
    
    $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    jsonResponse(200, [
        'ok' => true, 
        'data' => $solicitudes
    ]);

} catch (Throwable $e) {
    jsonResponse(500, [
        'ok' => false, 
        'message' => 'No fue posible consultar las solicitudes'
    ]);
}