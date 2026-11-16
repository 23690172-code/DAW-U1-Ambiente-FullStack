<?php
declare(strict_types=1);

require_once __DIR__.'/../../config/app.php';
require_once __DIR__.'/../../config/database.php';
require_once __DIR__.'/../../helpers/response.php';
require_once __DIR__.'/../../helpers/request.php';
require_once __DIR__.'/../../helpers/auth.php';
require_once __DIR__.'/../../helpers/solicitudes.php';

$user = requireRole(['CLIENTE']);
$data = readJson();
$servicioId = (int)($data['servicio_id'] ?? 0);
$observaciones = trim((string)($data['observaciones'] ?? ''));

if ($servicioId <= 0) {
    jsonResponse(422, ['ok'=>false, 'message'=>'Servicio no válido']);
}

try {
    $pdo = getPDO();
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare(
        'SELECT id,nombre,precio FROM servicios 
        WHERE id = :id AND activo = 1 LIMIT 1'
    );
    $stmt->execute([':id'=>$servicioId]);
    $servicio = $stmt->fetch();
    
    if (!$servicio) rollbackJson($pdo,404,'El servicio no está disponible');
    
    $check = $pdo->prepare(
        'SELECT id FROM solicitudes_servicio 
        WHERE usuario_id=:usuario AND servicio_id=:servicio 
        AND estado IN ("SOLICITADA","AUTORIZADA","PROGRAMADA","EN_PROCESO") 
        LIMIT 1'
    );
    $check->execute([':usuario'=>$user['id'], ':servicio'=>$servicioId]);
    
    if ($check->fetch()) rollbackJson($pdo,409,'Ya existe una solicitud activa para este servicio');
    
    $insert = $pdo->prepare(
        'INSERT INTO solicitudes_servicio 
        (usuario_id,servicio_id,precio_contratado,estado,observaciones_cliente) 
        VALUES (:usuario,:servicio,:precio,"SOLICITADA",:observaciones)'
    );
    $insert->execute([
        ':usuario'=>$user['id'], ':servicio'=>$servicioId,
        ':precio'=>$servicio['precio'], ':observaciones'=>$observaciones ?: null
    ]);
    
    $id = (int)$pdo->lastInsertId();
    
    registrarHistorial($pdo,$id,(int)$user['id'],null,'SOLICITADA','Solicitud registrada por el cliente');
    
    $pdo->commit();
    
    jsonResponse(201,['ok'=>true,'message'=>'Solicitud registrada correctamente','data'=>['id'=>$id]]);
    
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    jsonResponse(500,['ok'=>false,'message'=>'No fue posible registrar la solicitud']);
}