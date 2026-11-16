<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/request.php';
require_once __DIR__ . '/../../helpers/auth.php';

$currentUser = requireRole(['ADMIN_SISTEMA']);

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    jsonResponse(405, ['ok' => false, 'message' => 'Método no permitido']);
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id <= 0) {
    jsonResponse(400, ['ok' => false, 'message' => 'ID no válido']);
}

$data = readJson();
$usuario = trim($data['usuario'] ?? '');
$nombre = trim($data['nombre'] ?? '');
$email = trim($data['email'] ?? '');
$password = (string)($data['password'] ?? '');
$rol = $data['rol'] ?? 'CLIENTE';
$activo = (int)($data['activo'] ?? 1);
$rolesValidos = ['ADMIN_SISTEMA', 'ADMIN_SERVICIOS', 'CLIENTE'];
$errors = [];

if ($usuario === '') $errors['usuario'] = 'El usuario es obligatorio';
if ($nombre === '') $errors['nombre'] = 'El nombre es obligatorio';
if (strlen($nombre) > 120) $errors['nombre'] = 'Nombre muy largo';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Correo no válido';
if (!in_array($rol, $rolesValidos, true)) $errors['rol'] = 'Rol no válido';
if (!in_array($activo, [0, 1], true)) $errors['activo'] = 'Estado no válido';
if ($password !== '' && strlen($password) < 8) $errors['password'] = 'Mínimo 8 caracteres';

if (!empty($errors)) {
    jsonResponse(422, ['ok' => false, 'message' => 'Existen datos incorrectos', 'errors' => $errors]);
}

try {
    $pdo = getPDO();
    $stmtDuplicado = $pdo->prepare('SELECT id FROM usuarios WHERE (usuario = :usuario OR email = :email) AND id <> :id LIMIT 1');
    $stmtDuplicado->execute([':usuario' => $usuario, ':email' => $email, ':id' => $id]);
    if ($stmtDuplicado->fetch()) {
        jsonResponse(409, ['ok' => false, 'message' => 'Usuario o correo ya en uso']);
    }

    if ((int)$currentUser['id'] === (int)$id && $activo === 0) {
        jsonResponse(409, ['ok' => false, 'message' => 'No puede inactivar su propia cuenta']);
    }

    if ($password !== '') {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE usuarios SET usuario=:usuario, nombre=:nombre, email=:email, password_hash=:password_hash, rol=:rol, activo=:activo WHERE id=:id');
        $stmt->execute([':usuario'=>$usuario, ':nombre'=>$nombre, ':email'=>$email, ':password_hash'=>$hash, ':rol'=>$rol, ':activo'=>$activo, ':id'=>$id]);
    } else {
        $stmt = $pdo->prepare('UPDATE usuarios SET usuario=:usuario, nombre=:nombre, email=:email, rol=:rol, activo=:activo WHERE id=:id');
        $stmt->execute([':usuario'=>$usuario, ':nombre'=>$nombre, ':email'=>$email, ':rol'=>$rol, ':activo'=>$activo, ':id'=>$id]);
    }

    $stmtRes = $pdo->prepare('SELECT id, usuario, nombre, email, rol, activo, updated_at FROM usuarios WHERE id = :id');
    $stmtRes->execute([':id' => $id]);
    jsonResponse(200, ['ok' => true, 'message' => 'Usuario actualizado', 'data' => $stmtRes->fetch()]);
} catch (PDOException $e) {
    jsonResponse(500, ['ok' => false, 'message' => 'Error al actualizar usuario']);
}