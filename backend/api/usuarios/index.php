<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/auth.php';

requireRole(['ADMIN_SISTEMA']);

try {
    $pdo = getPDO();
    $stmt = $pdo->query('SELECT id, usuario, nombre, email, rol, activo, created_at, updated_at FROM usuarios ORDER BY id DESC');
    jsonResponse(200, ['ok' => true, 'data' => $stmt->fetchAll()]);
} catch (PDOException $e) {
    jsonResponse(500, ['ok' => false, 'message' => 'Error al consultar usuarios']);
}