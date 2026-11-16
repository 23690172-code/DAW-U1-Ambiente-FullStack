<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/request.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/solicitudes.php';

$user = requireRole(['ADMIN_SISTEMA', 'ADMIN_SERVICIOS']);

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    jsonResponse(422, ['ok' => false, 'message' => 'ID de solicitud no válido']);
}

try {
    $pdo = getPDO();
    $pdo->beginTransaction();

    $solicitud = obtenerSolicitudBloqueada($pdo, $id);

    if (!$solicitud) {
        rollbackJson($pdo, 404, 'Solicitud no encontrada');
    }

    if ($solicitud['estado'] !== 'SOLICITADA') {
        rollbackJson($pdo, 409, 'La solicitud ya no puede autorizarse');
    }

    $stmt = $pdo->prepare(
        'UPDATE solicitudes_servicio 
         SET estado = "AUTORIZADA", administrador_id = :admin 
         WHERE id = :id'
    );
    $stmt->execute([
        ':admin' => $user['id'], 
        ':id'    => $id
    ]);

    registrarHistorial(
        $pdo, 
        $id, 
        (int)$user['id'], 
        'SOLICITADA', 
        'AUTORIZADA', 
        'Solicitud autorizada'
    );

    $pdo->commit();

    jsonResponse(200, [
        'ok' => true, 
        'message' => 'Solicitud autorizada correctamente'
    ]);

} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    jsonResponse(500, [
        'ok' => false, 
        'message' => 'No fue posible autorizar la solicitud'
    ]);
}
