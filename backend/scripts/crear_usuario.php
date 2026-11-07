<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getPDO();

    $usuario  = 'admin';
    $nombre   = 'Administrador Demo';
    $password = '123456';
    $rol      = 'Administrador';

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // ¿Ya existe?
    $consulta = $pdo->prepare('SELECT id FROM usuarios WHERE usuario = :usuario LIMIT 1');
    $consulta->execute([':usuario' => $usuario]);
    $existente = $consulta->fetch();

    if ($existente) {
        $sql = $pdo->prepare(
            'UPDATE usuarios
             SET nombre = :nombre, password_hash = :password_hash, rol = :rol, activo = 1
             WHERE usuario = :usuario'
        );
    } else {
        $sql = $pdo->prepare(
            'INSERT INTO usuarios (usuario, nombre, password_hash, rol, activo)
             VALUES (:usuario, :nombre, :password_hash, :rol, 1)'
        );
    }

    $sql->execute([
        ':usuario'       => $usuario,
        ':nombre'        => $nombre,
        ':password_hash' => $passwordHash,
        ':rol'           => $rol,
    ]);

    echo "Usuario creado/actualizado correctamente.\n";
} catch (Throwable $e) {
    echo "Error al crear el usuario: " . $e->getMessage() . "\n";
}