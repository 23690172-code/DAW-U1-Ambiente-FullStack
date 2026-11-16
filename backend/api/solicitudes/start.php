<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
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

    if ($solicitud['estado'] !== 'PROGRAMADA') {
        rollbackJson($pdo, 409, 'La solicitud debe estar programada para poder iniciarse');
    }

    $fechaInicio = date('Y-m-d H:i:s');

    $stmt = $pdo->prepare(
        'UPDATE solicitudes_servicio 
         SET estado = "EN_PROCESO", 
             fecha_inicio = :fecha 
         WHERE id = :id'
    );
    $stmt->execute([
        ':fecha' => $fechaInicio,
        ':id'    => $id
    ]);

    registrarHistorial(
        $pdo, 
        $id, 
        (int)$user['id'], 
        'PROGRAMADA', 
        'EN_PROCESO', 
        'El administrador ha iniciado la atención del servicio'
    );

    $pdo->commit();

    jsonResponse(200, [
        'ok' => true, 
        'message' => 'Atención del servicio iniciada correctamente'
    ]);

} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    jsonResponse(500, [
        'ok' => false, 
        'message' => 'No fue posible iniciar el servicio'
    ]);
}
