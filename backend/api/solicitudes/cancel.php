<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/request.php'; 
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/solicitudes.php';

$user = requireRole(['CLIENTE', 'ADMIN_SISTEMA', 'ADMIN_SERVICIOS']);

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    jsonResponse(422, ['ok' => false, 'message' => 'ID de solicitud no válido']);
}

$data = readJson();
$comentario = trim((string)($data['observaciones'] ?? ''));

try {
    $pdo = getPDO();
    $pdo->beginTransaction();
    $solicitud = obtenerSolicitudBloqueada($pdo, $id);

    if (!$solicitud) {
        rollbackJson($pdo, 404, 'Solicitud no encontrada');
    }

    if ($user['rol'] === 'CLIENTE') {
        $estadosPermitidos = ['SOLICITADA', 'AUTORIZADA'];
    } else {
        $estadosPermitidos = ['SOLICITADA', 'AUTORIZADA', 'PROGRAMADA'];
    }

    if ($user['rol'] === 'CLIENTE' && (int)$solicitud['usuario_id'] !== (int)$user['id']) {
        rollbackJson($pdo, 403, 'No puede cancelar esta solicitud');
    }

    if (!in_array($solicitud['estado'], $estadosPermitidos, true)) {
        rollbackJson($pdo, 409, 'La solicitud ya no puede cancelarse');
    }

    $stmt = $pdo->prepare(
        'UPDATE solicitudes_servicio 
         SET estado = "CANCELADA",
             observaciones_admin = COALESCE(:comentario, observaciones_admin)
         WHERE id = :id'
    );
    $stmt->execute([
        ':comentario' => $comentario !== '' ? $comentario : null,
        ':id'         => $id
    ]);

    $estadoAnterior = $solicitud['estado'];
    $comentarioHistorial = $comentario !== '' ? $comentario : 'Solicitud cancelada';
    
    registrarHistorial(
        $pdo, 
        $id, 
        (int)$user['id'], 
        $estadoAnterior, 
        'CANCELADA', 
        $comentarioHistorial
    );

    $pdo->commit();

    jsonResponse(200, [
        'ok' => true, 
        'message' => 'Solicitud cancelada correctamente'
    ]);

} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    jsonResponse(500, [
        'ok' => false, 
        'message' => 'No fue posible cancelar la solicitud'
    ]);
}
