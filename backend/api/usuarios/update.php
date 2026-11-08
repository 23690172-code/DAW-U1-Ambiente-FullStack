<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/request.php';
require_once __DIR__ . '/../helpers/auth.php';

/* 
|---
| 1. Verificar permisos
|---
| Solo el Administrador del Sistema puede modificar usuarios.
*/
$currentUser = requireRole([
    'ADMIN_SISTEMA'
]);

/* 
|---
| 2. Verificar método HTTP
|---
*/
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    jsonResponse(
        405,
        [
            'ok' => false,
            'message' => 'Método no permitido'
        ]
    );
}

/* 
|---
| 3. Obtener y validar ID
|---
| Ejemplo:
| PUT /api/usuarios/update.php?id=5
*/
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id <= 0) {
    jsonResponse(
        400,
        [
            'ok' => false,
            'message' => 'ID de usuario no válido'
        ]
    );
}

/* 
|---
| 4. Leer JSON recibido
|---
*/
$data = readJson();

/* 
|---
| 5. Recuperar campos
|---
*/
$usuario  = trim((string)($data['usuario'] ?? ""));
$nombre   = trim((string)($data['nombre'] ?? ""));
$email    = trim((string)($data['email'] ?? ""));
$password = (string)($data['password'] ?? "");
$rol      = (string)($data['rol'] ?? "");
$activo   = (int)($data['activo'] ?? 1);

$rolesValidos = [
    'ADMIN_SISTEMA',
    'ADMIN_SERVICIOS',
    'CLIENTE'
];

$errors = [];

if ($usuario === "") {
    $errors['usuario'] = 'El usuario es obligatorio';
}
if (strlen($usuario) > 50) {
    $errors['usuario'] = 'El usuario no puede superar 50 caracteres';
}
if ($nombre === "") {
    $errors['nombre'] = 'El nombre es obligatorio';
}
if (strlen($nombre) > 120) {
    $errors['nombre'] = 'El nombre no puede superar 120 caracteres';
}
if ($email === "") {
    $errors['email'] = 'El correo electrónico es obligatorio';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'El correo electrónico no es válido';
}
if (!in_array($rol, $rolesValidos, true)) {
    $errors['rol'] = 'El rol seleccionado no es válido';
}
if (!in_array($activo, [0, 1], true)) {
    $errors['activo'] = 'El estado debe ser activo o inactivo';
}

/* 
| La contraseña es opcional al editar.
| Si viene vacía: conservar contraseña existente.
| Si contiene información: validar y generar un nuevo hash.
*/
if ($password !== "" && strlen($password) < 8) {
    $errors['password'] = 'La contraseña debe contener al menos 8 caracteres';
}

if (!empty($errors)) {
    jsonResponse(
        422,
        [
            'ok' => false,
            'message' => 'Existen datos incorrectos',
            'errors' => $errors
        ]
    );
}

/* 
| 8. Trabajar con la base de datos
*/
try {
    $pdo = getPDO();

    /* 
    | 9. Comprobar que el usuario existe
    */
    $stmtActual = $pdo->prepare('SELECT id, usuario, nombre, email, rol, activo, password_hash FROM usuarios WHERE id = :id LIMIT 1');
    $stmtActual->execute([':id' => $id]);
    $usuarioActual = $stmtActual->fetch();
    
    if (!$usuarioActual) {
        jsonResponse(
            404,
            [
                'ok' => false,
                'message' => 'El usuario no existe'
            ]
        );
    }

    /* 
    | 10. Comprobar usuario o email duplicado
    | Debemos excluir al usuario que estamos editando.
    */
    $stmtDuplicado = $pdo->prepare('SELECT id FROM usuarios WHERE ( usuario = :usuario OR email = :email ) AND id <> :id LIMIT 1');
    $stmtDuplicado->execute([
        ':usuario' => $usuario,
        ':email' => $email,
        ':id' => $id
    ]);

    if ($stmtDuplicado->fetch()) {
        jsonResponse(
            409,
            [
                'ok' => false,
                'message' => 'El usuario o correo electrónico ya se encuentra registrado'
            ]
        );
    }

    /* 
    | 11. Evitar que el administrador se inactive a sí mismo
    */
    if ((int)$currentUser['id'] === (int)$id && $activo === 0) {
        jsonResponse(
            409,
            [
                'ok' => false,
                'message' => 'No puede inactivar su propia cuenta'
            ]
        );
    }

    /* 
    | 12. Actualizar usuario
    | Existen dos posibilidades:
    | A) password vacío -> no modificar password_hash.
    | B) password tiene valor -> generar nuevo hash.
    */
    if ($password !== "") {
        /* Actualización CON cambio de contraseña */
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE usuarios SET usuario = :usuario, nombre = :nombre, email = :email, password_hash = :password_hash, rol = :rol, activo = :activo WHERE id = :id');
        $stmt->execute([
            ':usuario' => $usuario,
            ':nombre' => $nombre,
            ':email' => $email,
            ':password_hash' => $passwordHash,
            ':rol' => $rol,
            ':activo' => $activo,
            ':id' => $id
        ]);
    } else {
        /* Actualización SIN cambiar contraseña */
        $stmt = $pdo->prepare('UPDATE usuarios SET usuario = :usuario, nombre = :nombre, email = :email, rol = :rol, activo = :activo WHERE id = :id');
        $stmt->execute([
            ':usuario' => $usuario,
            ':nombre' => $nombre,
            ':email' => $email,
            ':rol' => $rol,
            ':activo' => $activo,
            ':id' => $id
        ]);
    }

    /* 
    | 13. Recuperar usuario actualizado
    */
    $stmtResultado = $pdo->prepare('SELECT id, usuario, nombre, email, rol, activo, created_at, updated_at FROM usuarios WHERE id = :id LIMIT 1');
    $stmtResultado->execute([':id' => $id]);
    $usuarioActualizado = $stmtResultado->fetch();

    /* 
    | 14. Respuesta JSON
    */
    jsonResponse(
        200,
        [
            'ok' => true,
            'message' => 'Usuario actualizado correctamente',
            'data' => $usuarioActualizado
        ]
    );

} catch (PDOException $e) {
    /* 
    | Nunca enviar $e->getMessage() al frontend en producción.
    */
    jsonResponse(
        500,
        [
            'ok' => false,
            'message' => 'Error interno al actualizar el usuario'
        ]
    );
}