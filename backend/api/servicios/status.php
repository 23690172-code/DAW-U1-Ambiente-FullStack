<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/request.php';
require_once __DIR__ . '/../../helpers/auth.php';

requireRole(['ADMIN_SISTEMA', 'ADMIN_SERVICIOS']);
if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') jsonResponse(405, ['ok' => false, 'message' => 'Método no permitido']);

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id <= 0) jsonResponse(400, ['ok' => false, 'message' => 'ID no válido']);

$data = readJson();
if (!isset($data['activo']) || !in_array($data['activo'], [0, 1, '0', '1', true, false], true)) {
    jsonResponse(422, ['ok' => false, 'message' => 'Estado no válido']);
}
$activo = (int)$data['activo'];

try {
    $pdo = getPDO();
    $stmt = $pdo->prepare('UPDATE servicios SET activo = :activo WHERE id = :id');
    $stmt->execute([':activo' => $activo, ':id' => $id]);
    jsonResponse(200, ['ok' => true, 'message' => 'Estado actualizado']);
} catch (PDOException $e) {
    jsonResponse(500, ['ok' => false, 'message' => 'Error al cambiar estado']);
}