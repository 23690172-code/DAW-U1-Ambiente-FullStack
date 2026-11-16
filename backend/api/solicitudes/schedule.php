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
$fechaTexto = trim((string)($data['fecha_programada'] ?? ''));
$comentario = trim((string)($data['observaciones'] ?? ''));

try {
    $fecha = new DateTimeImmutable($fechaTexto);
} catch (Exception $e) {
    jsonResponse(422, ['ok' => false, 'message' => 'Fecha de programación incorrecta']);
}

if ($fecha <= new DateTimeImmutable()) {
    jsonResponse(422, ['ok' => false, 'message' => 'La fecha debe ser posterior a la fecha actual']);
}

$fechaMySQL = $fecha->format('Y-m-d H:i:s');

try {
    $pdo = getPDO();
    $pdo->beginTransaction();

    $solicitud = obtenerSolicitudBloqueada($pdo, $id);

    if (!$solicitud) {
        rollbackJson($pdo, 404, 'Solicitud no encontrada');
    }

    if ($solicitud['estado'] !== 'AUTORIZADA') {
        rollbackJson($pdo, 409, 'Solo se pueden programar solicitudes autorizadas');
    }

    $stmt = $pdo->prepare(
        'UPDATE solicitudes_servicio 
         SET estado = "PROGRAMADA", 
             fecha_programada = :fecha,
             administrador_id = :admin, 
             observaciones_admin = :comentario
         WHERE id = :id'
    );
    $stmt->execute([
        ':fecha'      => $fechaMySQL,
        ':admin'      => $user['id'],
        ':comentario' => $comentario !== '' ? $comentario : null,
        ':id'         => $id
    ]);

    $comentarioHistorial = $comentario !== '' ? $comentario : 'Servicio programado';
    registrarHistorial(
        $pdo, 
        $id, 
        (int)$user['id'], 
        'AUTORIZADA', 
        'PROGRAMADA', 
        $comentarioHistorial
    );

    $pdo->commit();

    jsonResponse(200, [
        'ok' => true, 
        'message' => 'Solicitud programada correctamente'
    ]);

} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    jsonResponse(500, [
        'ok' => false, 
        'message' => 'No fue posible programar la solicitud'
    ]);
}
