<?php
declare(strict_types=1);

function requireAuth(): array {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (!isset($_SESSION['user'])) {
        jsonResponse(401, ['ok' => false, 'message' => 'Usuario no autenticado']);
    }
    return $_SESSION['user'];
}

function requireRole(array $allowedRoles): array {
    $user = requireAuth();
    if (!in_array($user['rol'], $allowedRoles, true)) {
        jsonResponse(403, ['ok' => false, 'message' => 'No cuenta con permisos']);
    }
    return $user;
}