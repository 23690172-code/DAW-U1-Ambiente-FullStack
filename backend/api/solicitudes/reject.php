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

$data = readJson();
$comentario = trim((string)($data['observaciones'] ?? ''));

if ($comentario === '') {
    jsonResponse(422, ['ok' => false, 'message' => 'Debe indicar el motivo del rechazo']);
}

try {
    $pdo = getPDO();
    $pdo->beginTransaction();

    $solicitud = obtenerSolicitudBloqueada($pdo, $id);

    if (!$solicitud) {
        rollbackJson($pdo, 404, 'Solicitud no encontrada');
    }

    if ($solicitud['estado'] !== 'SOLICITADA') {
        rollbackJson($pdo, 409, 'La solicitud ya no puede rechazarse');
    }

    $stmt = $pdo->prepare(
        'UPDATE solicitudes_servicio 
         SET estado = "RECHAZADA", 
             administrador_id = :admin, 
             observaciones_admin = :comentario 
         WHERE id = :id'
    );
    $stmt->execute([
        ':admin'      => $user['id'], 
        ':comentario' => $comentario, 
        ':id'         => $id
    ]);

    registrarHistorial(
        $pdo, 
        $id, 
        (int)$user['id'], 
        'SOLICITADA', 
        'RECHAZADA', 
        $comentario
    );

    $pdo->commit();

    jsonResponse(200, [
        'ok' => true, 
        'message' => 'Solicitud rechazada correctamente'
    ]);

} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    jsonResponse(500, [
        'ok' => false, 
        'message' => 'No fue posible rechazar la solicitud'
    ]);
}
