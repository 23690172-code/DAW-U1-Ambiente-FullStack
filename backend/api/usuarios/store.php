<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/request.php';
require_once __DIR__ . '/../helpers/auth.php';

requireRole(['ADMIN_SISTEMA']);

$data = readJson();

$usuario  = trim($data['usuario'] ?? '');
$nombre   = trim($data['nombre'] ?? '');
$email    = trim($data['email'] ?? '');
$password = (string)($data['password'] ?? '');
$rol      = $data['rol'] ?? 'CLIENTE';
$activo   = (int)($data['activo'] ?? 1);

$roles = ['ADMIN_SISTEMA', 'ADMIN_SERVICIOS', 'CLIENTE'];
$errors = [];

if ($usuario === '') {
    $errors['usuario'] = 'El usuario es obligatorio';
}
if ($nombre === '') {
    $errors['nombre'] = 'El nombre es obligatorio';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Correo electrónico no válido';
}
if (strlen($password) < 8) {
    $errors['password'] = 'La contraseña debe tener al menos 8 caracteres';
}
if (!in_array($rol, $roles, true)) {
    $errors['rol'] = 'Rol no válido';
}

if (!empty($errors)) {
    jsonResponse(422, [
        'ok' => false,
        'message' => 'Existen datos incorrectos',
        'errors' => $errors
    ]);
}

try {
    $pdo = getPDO();

    $check = $pdo->prepare('SELECT id FROM usuarios WHERE usuario = :usuario OR email = :email LIMIT 1');
    $check->execute([':usuario' => $usuario, ':email' => $email]);

    if ($check->fetch()) {
        jsonResponse(409, ['ok' => false, 'message' => 'Usuario o correo ya registrado']);
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare('INSERT INTO usuarios (usuario, nombre, email, password_hash, rol, activo) VALUES (:usuario, :nombre, :email, :password_hash, :rol, :activo)');
    $stmt->execute([
        ':usuario'       => $usuario,
        ':nombre'        => $nombre,
        ':email'         => $email,
        ':password_hash' => $hash,
        ':rol'           => $rol,
        ':activo'        => $activo
    ]);

    jsonResponse(201, [
        'ok' => true,
        'message' => 'Usuario registrado',
        'data' => ['id' => (int)$pdo->lastInsertId()]
    ]);
} catch (PDOException $e) {
    jsonResponse(500, ['ok' => false, 'message' => 'Error al registrar usuario']);
}