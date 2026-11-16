<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/request.php';
require_once __DIR__ . '/../../helpers/auth.php';

requireRole(['ADMIN_SISTEMA', 'ADMIN_SERVICIOS']);
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') jsonResponse(405, ['ok' => false, 'message' => 'Método no permitido']);

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id <= 0) jsonResponse(400, ['ok' => false, 'message' => 'ID no válido']);

$data = readJson();
$nombre = trim((string)($data['nombre'] ?? ''));
$descripcion = trim((string)($data['descripcion'] ?? ''));
$precioRaw = $data['precio'] ?? null;
$duracionRaw = $data['duracion_minutos'] ?? null;
$activoRaw = $data['activo'] ?? 1;
$errors = [];

if ($nombre === '') $errors['nombre'] = 'Obligatorio';
if ($descripcion === '') $errors['descripcion'] = 'Obligatorio';
if (!is_numeric($precioRaw) || $precioRaw < 0) $errors['precio'] = 'Precio inválido';
if (!filter_var($duracionRaw, FILTER_VALIDATE_INT) || $duracionRaw <= 0) $errors['duracion_minutos'] = 'Duración inválida';

$activo = in_array($activoRaw, [0, 1, '0', '1', true, false], true) ? (int)$activoRaw : -1;
if ($activo === -1) $errors['activo'] = 'Estado inválido';

if (!empty($errors)) jsonResponse(422, ['ok' => false, 'message' => 'Datos incorrectos', 'errors' => $errors]);

try {
    $pdo = getPDO();
    $stmtDuplicado = $pdo->prepare('SELECT id FROM servicios WHERE nombre = :nombre AND id <> :id LIMIT 1');
    $stmtDuplicado->execute([':nombre' => $nombre, ':id' => $id]);
    if ($stmtDuplicado->fetch()) jsonResponse(409, ['ok' => false, 'message' => 'Ya existe otro servicio con ese nombre']);

    $stmt = $pdo->prepare('UPDATE servicios SET nombre=:nombre, descripcion=:descripcion, precio=:precio, duracion_minutos=:duracion, activo=:activo WHERE id=:id');
    $stmt->execute([
        ':nombre' => $nombre, ':descripcion' => $descripcion, 
        ':precio' => round((float)$precioRaw, 2), 
        ':duracion' => (int)$duracionRaw, ':activo' => $activo, ':id' => $id
    ]);
    
    $stmtRes = $pdo->prepare('SELECT * FROM servicios WHERE id = :id');
    $stmtRes->execute([':id' => $id]);
    
    jsonResponse(200, ['ok' => true, 'message' => 'Servicio actualizado', 'data' => $stmtRes->fetch()]);
} catch (PDOException $e) {
    jsonResponse(500, ['ok' => false, 'message' => 'Error interno']);
}