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
| Solamente el Administrador del Sistema puede activar o inactivar usuarios. 
*/ 
$currentUser = requireRole(['ADMIN_SISTEMA']); 

/* 
|---
| 2. Verificar método HTTP
|---
| Se utilizará PATCH porque solamente se modifica parcialmente 
| el recurso usuario: su campo "activo". 
*/ 
if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') { 
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
| 3. Obtener ID del usuario
|---
| Ejemplo:
| PATCH /api/usuarios/status.php?id=5
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
| 4. Leer información JSON
|---
| Ejemplo recibido:
| {
|   "activo": 0
| }
*/ 
$data = readJson(); 

/* 
|---
| 5. Validar campo "activo"
|---
*/ 
if (!array_key_exists('activo', $data)) { 
    jsonResponse( 
        422, 
        [ 
            'ok' => false, 
            'message' => 'Debe indicar el estado del usuario', 
            'errors' => ['activo' => 'El campo activo es obligatorio'] 
        ]
    ); 
} 

/* 
| 1 = Activo
| 0 = Inactivo
*/ 
$activo = (int)$data['activo']; 

if (!in_array($activo, [0, 1], true)) {
    jsonResponse( 
        422, 
        [ 
            'ok' => false, 
            'message' => 'Estado de usuario no válido', 
            'errors' => ['activo' => 'El estado debe ser 0 o 1'] 
        ]
    ); 
}

/* 
| 7. Operaciones con base de datos
*/
try {
    $pdo = getPDO();

    /*
    | 8. Verificar que el usuario exista (necesario para las siguientes validaciones)
    */
    $stmtUsuario = $pdo->prepare('SELECT id, usuario, nombre, email, rol, activo, created_at, updated_at FROM usuarios WHERE id = :id LIMIT 1');
    $stmtUsuario->execute([':id' => $id]);
    $usuario = $stmtUsuario->fetch();

    if (!$usuario) {
        jsonResponse(
            404,
            [
                'ok' => false,
                'message' => 'El usuario no existe'
            ]
        );
    }

    /*
    | 8. Evitar que el administrador se inactive a sí mismo
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
    | 9. Evitar operación innecesaria
    */
    if ((int)$usuario['activo'] === $activo) {
        jsonResponse(
            200,
            [
                'ok' => true,
                'message' => $activo === 1 
                    ? 'El usuario ya se encuentra activo' 
                    : 'El usuario ya se encuentra inactivo',
                'data' => [
                    'id' => (int)$usuario['id'],
                    'activo' => $activo
                ]
            ]
        );
    }

    /*
    | 10. Protección adicional: no dejar el sistema sin administradores activos
    | Si se intenta inactivar una cuenta ADMIN_SISTEMA,
    | verificamos que exista al menos otro administrador activo.
    */
    if ($usuario['rol'] === 'ADMIN_SISTEMA' && $activo === 0) {
        $stmtAdmins = $pdo->prepare('SELECT COUNT(*) AS total FROM usuarios WHERE rol = :rol AND activo = 1 AND id <> :id');
        $stmtAdmins->execute([
            ':rol' => 'ADMIN_SISTEMA',
            ':id' => $id
        ]);
        $resultado = $stmtAdmins->fetch();
        $totalAdmins = (int)$resultado['total'];

        if ($totalAdmins === 0) {
            jsonResponse(
                409,
                [
                    'ok' => false,
                    'message' => 'No es posible inactivar al último administrador activo del sistema'
                ]
            );
        }
    }

    /*
    | 11. Actualizar estado mediante PDO
    */
    $stmt = $pdo->prepare('UPDATE usuarios SET activo = :activo WHERE id = :id');
    $stmt->execute([
        ':activo' => $activo,
        ':id' => $id
    ]);

    /*
    | 12. Consultar información actualizada
    */
    $stmtResultado = $pdo->prepare('SELECT id, usuario, nombre, email, rol, activo, created_at, updated_at FROM usuarios WHERE id = :id LIMIT 1');
    $stmtResultado->execute([':id' => $id]);
    $usuarioActualizado = $stmtResultado->fetch();

    /*
    | 13. Respuesta JSON
    */
    jsonResponse(
        200,
        [
            'ok' => true,
            'message' => $activo === 1 
                ? 'Usuario activado correctamente' 
                : 'Usuario inactivado correctamente',
            'data' => $usuarioActualizado
        ]
    );

} catch (PDOException $e) {
    /*
    | No enviar detalles internos de PDO al frontend.
    */
    jsonResponse(
        500,
        [
            'ok' => false,
            'message' => 'Error interno al modificar el estado del usuario'
        ]
    );
}