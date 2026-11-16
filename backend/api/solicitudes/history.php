<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/solicitudes.php';

$user = requireRole(['CLIENTE', 'ADMIN_SISTEMA', 'ADMIN_SERVICIOS']);

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    jsonResponse(422, ['ok' => false, 'message' => 'ID de solicitud no válido']);
}

try {
    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT id, usuario_id FROM solicitudes_servicio WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $solicitud = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$solicitud) {
        jsonResponse(404, ['ok' => false, 'message' => 'Solicitud no encontrada']);
    }

    if ($user['rol'] === 'CLIENTE' && (int)$solicitud['usuario_id'] !== (int)$user['id']) {
        jsonResponse(403, ['ok' => false, 'message' => 'No tiene permiso para ver este historial']);
    }

    $stmt = $pdo->prepare(
        'SELECT 
            h.id, 
            h.estado_anterior, 
            h.estado_nuevo,
            h.comentario, 
            h.fecha, 
            u.nombre AS usuario
         FROM historial_solicitud h
         INNER JOIN usuarios u ON u.id = h.usuario_id
         WHERE h.solicitud_id = :id
         ORDER BY h.id ASC'
    );
    
    $stmt->execute([':id' => $id]);
    $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);

    jsonResponse(200, [
        'ok' => true,
        'data' => $historial
    ]);

} catch (Throwable $e) {
    jsonResponse(500, [
        'ok' => false, 
        'message' => 'No fue posible consultar el historial'
    ]);
}