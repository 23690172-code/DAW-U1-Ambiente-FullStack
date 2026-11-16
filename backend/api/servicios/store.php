<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/request.php';
require_once __DIR__ . '/../../helpers/auth.php';

requireRole(['ADMIN_SISTEMA', 'ADMIN_SERVICIOS']);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(405, ['ok' => false, 'message' => 'Método no permitido']);

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
    $stmtExiste = $pdo->prepare('SELECT id FROM servicios WHERE nombre = :nombre LIMIT 1');
    $stmtExiste->execute([':nombre' => $nombre]);
    if ($stmtExiste->fetch()) jsonResponse(409, ['ok' => false, 'message' => 'Ya existe un servicio con ese nombre']);

    $stmt = $pdo->prepare('INSERT INTO servicios (nombre, descripcion, precio, duracion_minutos, activo) VALUES (:nombre, :descripcion, :precio, :duracion_minutos, :activo)');
    $stmt->execute([
        ':nombre' => $nombre, ':descripcion' => $descripcion, 
        ':precio' => round((float)$precioRaw, 2), 
        ':duracion_minutos' => (int)$duracionRaw, ':activo' => $activo
    ]);
    
    $id = (int)$pdo->lastInsertId();
    $stmtRes = $pdo->prepare('SELECT * FROM servicios WHERE id = :id');
    $stmtRes->execute([':id' => $id]);
    
    jsonResponse(201, ['ok' => true, 'message' => 'Servicio registrado', 'data' => $stmtRes->fetch()]);
} catch (PDOException $e) {
    jsonResponse(500, ['ok' => false, 'message' => 'Error interno']);
}