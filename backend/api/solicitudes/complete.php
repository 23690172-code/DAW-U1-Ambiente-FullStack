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

try {
    $pdo = getPDO();
    $pdo->beginTransaction();
    $solicitud = obtenerSolicitudBloqueada($pdo, $id);

    if (!$solicitud) {
        rollbackJson($pdo, 404, 'Solicitud no encontrada');
    }

    if ($solicitud['estado'] !== 'EN_PROCESO') {
        rollbackJson($pdo, 409, 'Solo se pueden completar solicitudes en proceso');
    }

    $fechaFin = date('Y-m-d H:i:s');

    $stmt = $pdo->prepare(
        'UPDATE solicitudes_servicio 
         SET estado = "COMPLETADA", 
             fecha_fin = :fecha,
             observaciones_admin = COALESCE(:comentario, observaciones_admin)
         WHERE id = :id'
    );
    $stmt->execute([
        ':fecha'      => $fechaFin,
        ':comentario' => $comentario !== '' ? $comentario : null,
        ':id'         => $id
    ]);

    $comentarioHistorial = $comentario !== '' ? $comentario : 'Servicio completado satisfactoriamente';
    registrarHistorial(
        $pdo, 
        $id, 
        (int)$user['id'], 
        'EN_PROCESO', 
        'COMPLETADA', 
        $comentarioHistorial
    );

    $pdo->commit();

    jsonResponse(200, [
        'ok' => true, 
        'message' => 'Servicio completado correctamente'
    ]);

} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    jsonResponse(500, [
        'ok' => false, 
        'message' => 'No fue posible completar el servicio'
    ]);
}
