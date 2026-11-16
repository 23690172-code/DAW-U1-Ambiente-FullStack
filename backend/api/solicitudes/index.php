<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/auth.php';

$user = requireRole(['ADMIN_SISTEMA', 'ADMIN_SERVICIOS']);

try {
    $pdo = getPDO();

    $stmt = $pdo->query('
        SELECT ss.id, u.nombre AS cliente, u.email AS cliente_email,
               s.nombre AS servicio, ss.precio_contratado, 
               ss.fecha_solicitud, ss.fecha_programada, ss.fecha_inicio, ss.fecha_fin, 
               ss.estado, ss.observaciones_cliente, ss.observaciones_admin,
               admin.nombre AS administrador
        FROM solicitudes_servicio ss
        INNER JOIN usuarios u ON u.id = ss.usuario_id
        INNER JOIN servicios s ON s.id = ss.servicio_id
        LEFT JOIN usuarios admin ON admin.id = ss.administrador_id
        ORDER BY ss.id DESC
    ');
    
    $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    jsonResponse(200, ['ok' => true, 'data' => $solicitudes]);

} catch (Throwable $e) {
    jsonResponse(500, [
        'ok' => false, 
        'message' => 'No fue posible consultar las solicitudes'
    ]);
}
