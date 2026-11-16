<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/request.php';
require_once __DIR__ . '/../../helpers/auth.php';

$currentUser = requireRole(['ADMIN_SISTEMA']);
if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') jsonResponse(405, ['ok' => false, 'message' => 'Método no permitido']);

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id <= 0) jsonResponse(400, ['ok' => false, 'message' => 'ID no válido']);

$data = readJson();
if (!isset($data['activo']) || !in_array($data['activo'], [0, 1], true)) {
    jsonResponse(422, ['ok' => false, 'message' => 'Estado no válido']);
}
$activo = (int)$data['activo'];

try {
    $pdo = getPDO();
    $stmtUser = $pdo->prepare('SELECT id, rol, activo FROM usuarios WHERE id = :id');
    $stmtUser->execute([':id' => $id]);
    $user = $stmtUser->fetch();

    if (!$user) jsonResponse(404, ['ok' => false, 'message' => 'Usuario no existe']);
    if ((int)$currentUser['id'] === (int)$id && $activo === 0) jsonResponse(409, ['ok' => false, 'message' => 'No puede inactivar su cuenta']);
    
    if ($user['rol'] === 'ADMIN_SISTEMA' && $activo === 0) {
        $stmtAdmins = $pdo->prepare('SELECT COUNT(*) AS total FROM usuarios WHERE rol = "ADMIN_SISTEMA" AND activo = 1 AND id <> :id');
        $stmtAdmins->execute([':id' => $id]);
        if ((int)$stmtAdmins->fetch()['total'] === 0) {
            jsonResponse(409, ['ok' => false, 'message' => 'No puede inactivar al último administrador']);
        }
    }

    $stmt = $pdo->prepare('UPDATE usuarios SET activo = :activo WHERE id = :id');
    $stmt->execute([':activo' => $activo, ':id' => $id]);
    
    jsonResponse(200, ['ok' => true, 'message' => 'Estado actualizado']);
} catch (PDOException $e) {
    jsonResponse(500, ['ok' => false, 'message' => 'Error al cambiar estado']);
}