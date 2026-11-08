<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/request.php';
require_once __DIR__ . '/../helpers/auth.php';

// 1. Verificar permisos
requireRole(['ADMIN_SISTEMA', 'ADMIN_SERVICIOS']);

// 2. Leer JSON
$data = readJson();

$nombre = trim((string)($data['nombre'] ?? ''));
$descripcion = trim((string)($data['descripcion'] ?? ''));
$precioRaw = $data['precio'] ?? null;
$duracionRaw = $data['duracion_minutos'] ?? null;
$activoRaw = $data['activo'] ?? 1;

// 3. Validaciones
$errors = [];

if ($nombre === '') {
    $errors['nombre'] = 'El nombre del servicio es obligatorio';
} elseif (mb_strlen($nombre) > 120) {
    $errors['nombre'] = 'El nombre no puede superar 120 caracteres';
}

if ($descripcion === '') {
    $errors['descripcion'] = 'La descripción es obligatoria';
}

if ($precioRaw === null || $precioRaw === '') {
    $errors['precio'] = 'El precio es obligatorio';
} elseif (!is_numeric($precioRaw)) {
    $errors['precio'] = 'El precio debe ser un valor numérico';
} elseif ((float)$precioRaw < 0) {
    $errors['precio'] = 'El precio no puede ser negativo';
}

if ($duracionRaw === null || $duracionRaw === '') {
    $errors['duracion_minutos'] = 'La duración es obligatoria';
} elseif (filter_var($duracionRaw, FILTER_VALIDATE_INT) === false) {
    $errors['duracion_minutos'] = 'La duración debe expresarse en minutos enteros';
} elseif ((int)$duracionRaw <= 0) {
    $errors['duracion_minutos'] = 'La duración debe ser mayor que cero';
}

// Validar estado
if (is_bool($activoRaw)) {
    $activo = $activoRaw ? 1 : 0;
} elseif (in_array($activoRaw, [0, 1, '0', '1'], true)) {
    $activo = (int)$activoRaw;
} else {
    $activo = -1;
    $errors['activo'] = 'El estado debe ser activo o inactivo';
}

if (!empty($errors)) {
    jsonResponse(422, [
        'ok' => false,
        'message' => 'Existen datos incorrectos',
        'errors' => $errors
    ]);
}

// 4. Normalizar valores
$precio = round((float)$precioRaw, 2);
$duracionMinutos = (int)$duracionRaw;

// 5. Operaciones con base de datos
try {
    $pdo = getPDO();

    // Comprobar si ya existe un servicio con el mismo nombre
    $stmtExiste = $pdo->prepare('SELECT id FROM servicios WHERE nombre = :nombre LIMIT 1');
    $stmtExiste->execute([':nombre' => $nombre]);
    
    if ($stmtExiste->fetch()) {
        jsonResponse(409, ['ok' => false, 'message' => 'Ya existe un servicio con ese nombre']);
    }

    // Insertar nuevo servicio
    $stmt = $pdo->prepare('INSERT INTO servicios (nombre, descripcion, precio, duracion_minutos, activo) VALUES (:nombre, :descripcion, :precio, :duracion_minutos, :activo)');
    $stmt->execute([
        ':nombre' => $nombre,
        ':descripcion' => $descripcion,
        ':precio' => $precio,
        ':duracion_minutos' => $duracionMinutos,
        ':activo' => $activo
    ]);

    $nuevoId = (int)$pdo->lastInsertId();

    // Recuperar el servicio recién creado para devolverlo
    $stmtServicio = $pdo->prepare('SELECT id, nombre, descripcion, precio, duracion_minutos, activo, created_at, updated_at FROM servicios WHERE id = :id LIMIT 1');
    $stmtServicio->execute([':id' => $nuevoId]);
    $servicio = $stmtServicio->fetch();

    jsonResponse(201, [
        'ok' => true,
        'message' => 'Servicio registrado correctamente',
        'data' => $servicio
    ]);

} catch (PDOException $e) {
    jsonResponse(500, ['ok' => false, 'message' => 'Error interno al registrar el servicio']);
}