<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getPDO();

    $usuario     = 'alumno';
    $nombre      = 'Alumno Demo';
    $password    = 'alumno123';
    $rol         = 'Alumno';

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $consulta = $pdo->prepare('SELECT id FROM usuarios WHERE usuario = :usuario LIMIT 1');
    $consulta->execute([':usuario' => $usuario]);
    $existente = $consulta->fetch();

    if ($existente) {
        $sql = $pdo->prepare('UPDATE usuarios 
                              SET nombre = :nombre, 
                                  password_hash = :password_hash, 
                                  rol = :rol, 
                                  activo = 1 
                              WHERE usuario = :usuario');
        $sql->execute([
            ':nombre'        => $nombre,
            ':password_hash' => $passwordHash,
            ':rol'           => $rol,
            ':usuario'       => $usuario,
        ]);
        echo "Usuario 'alumno' actualizado correctamente.\n";
    } else {
        $sql = $pdo->prepare('INSERT INTO usuarios (usuario, nombre, password_hash, rol, activo) 
                              VALUES (:usuario, :nombre, :password_hash, :rol, 1)');
        $sql->execute([
            ':usuario'       => $usuario,
            ':nombre'        => $nombre,
            ':password_hash' => $passwordHash,
            ':rol'           => $rol,
        ]);
        echo "Usuario 'alumno' creado correctamente.\n";
    }

    echo "Hash generado: " . $passwordHash . "\n";

} catch (Throwable $e) {
    echo "Error al crear el usuario: " . $e->getMessage() . "\n";
}