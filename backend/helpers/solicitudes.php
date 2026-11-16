<?php
declare(strict_types=1);

function obtenerSolicitudBloqueada(PDO $pdo, int $id): array|false {
    $stmt = $pdo->prepare(
        'SELECT * FROM solicitudes_servicio WHERE id = :id LIMIT 1 FOR UPDATE'
    );
    $stmt->execute([':id' => $id]);
    return $stmt->fetch();
}

function registrarHistorial(
    PDO $pdo, 
    int $solicitudId, 
    int $usuarioId,
    ?string $estadoAnterior, 
    string $estadoNuevo,
    ?string $comentario = null
): void {
    $stmt = $pdo->prepare(
        'INSERT INTO historial_solicitud
        (solicitud_id, usuario_id, estado_anterior, estado_nuevo, comentario)
        VALUES (:solicitud, :usuario, :anterior, :nuevo, :comentario)'
    );
    
    $stmt->execute([
        ':solicitud'  => $solicitudId,
        ':usuario'    => $usuarioId,
        ':anterior'   => $estadoAnterior,
        ':nuevo'      => $estadoNuevo,
        ':comentario' => $comentario
    ]);
}

function rollbackJson(PDO $pdo, int $status, string $message): never {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    jsonResponse($status, ['ok' => false, 'message' => $message]);
}