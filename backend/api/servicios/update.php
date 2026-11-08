<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/request.php';
require_once __DIR__ . '/../helpers/auth.php';
/*

|--------------------------------------------------------------------------
| 1. Verificar permisos
|--------------------------------------------------------------------------
*/
requireRole(['ADMIN_SISTEMA','ADMIN_SERVICIOS']);
/*
|--------------------------------------------------------------------------
| 2. Verificar método HTTP
|--------------------------------------------------------------------------
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
|--------------------------------------------------------------------------
| 3. Obtener ID
|--------------------------------------------------------------------------
|
| Ejemplo:
|
| PUT /api/servicios/update.php?id=5
*/
$id = filter_input( INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
jsonResponse(
400,
[
'ok' => false,
'message' => 'ID de servicio no válido'
]
);
}
/*
|--------------------------------------------------------------------------
| 4. Leer JSON
|--------------------------------------------------------------------------
*/
$data = readJson();

/*
|--------------------------------------------------------------------------
| 5. Recuperar valores
|--------------------------------------------------------------------------
*/
$nombre = trim((string)( $data['nombre'] ?? ''));
$descripcion = trim((string)($data['descripcion'] ?? ''));
$precioRaw = $data['precio'] ?? null;
$duracionRaw = $data['duracion_minutos'] ?? null;
$activoRaw = $data['activo'] ?? 1;
/*
|--------------------------------------------------------------------------
| 6. Validaciones
|--------------------------------------------------------------------------
*/
$errors = [];
/*
|--------------------------------------------------------------------------
| Nombre
|--------------------------------------------------------------------------
*/
if ($nombre === '') {
$errors['nombre'] = 'El nombre del servicio es obligatorio';
}
elseif (mb_strlen($nombre) > 120) {
$errors['nombre'] = 'El nombre no puede superar 120 caracteres';
}
/*
|--------------------------------------------------------------------------
| Descripción
|--------------------------------------------------------------------------
*/
if ($descripcion === '') {
$errors['descripcion'] = 'La descripción es obligatoria';
}
/*
|--------------------------------------------------------------------------
| Precio
|--------------------------------------------------------------------------
*/
if ($precioRaw === null || $precioRaw === '') {
$errors['precio'] = 'El precio es obligatorio';
}
elseif (!is_numeric($precioRaw)) {
$errors['precio'] = 'El precio debe ser numérico';

}
elseif ((float)$precioRaw < 0) {
$errors['precio'] = 'El precio no puede ser negativo';
}
/*
|--------------------------------------------------------------------------
| Duración
|--------------------------------------------------------------------------
*/
if ($duracionRaw === null || $duracionRaw === '') {
$errors['duracion_minutos'] = 'La duración es obligatoria';
}
elseif (filter_var($duracionRaw, FILTER_VALIDATE_INT) === false) {
$errors['duracion_minutos'] = 'La duración debe expresarse en minutos enteros';
}
elseif ((int)$duracionRaw <= 0) {
$errors['duracion_minutos'] = 'La duración debe ser mayor que cero';
}
/*
|--------------------------------------------------------------------------
| Estado
|--------------------------------------------------------------------------
*/
if (is_bool($activoRaw)) {
$activo = $activoRaw ? 1 : 0;
}
elseif (
in_array(
$activoRaw,
[0, 1, '0', '1'],
true
)
) {
$activo = (int)$activoRaw;
}
else {
$activo = -1;
$errors['activo'] = 'El estado debe ser activo o inactivo';
}
/*
|--------------------------------------------------------------------------
| 7. Detener proceso si existen errores
|--------------------------------------------------------------------------
*/
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
|--------------------------------------------------------------------------
| 8. Normalizar datos
|--------------------------------------------------------------------------
*/
$precio = round((float)$precioRaw, 2 );
$duracionMinutos = (int)$duracionRaw;
/*
|--------------------------------------------------------------------------
| 9. Operaciones con base de datos
|--------------------------------------------------------------------------
*/
try {
$pdo = getPDO();
/*
|--------------------------------------------------------------------------
| 10. Verificar que el servicio exista
|--------------------------------------------------------------------------
*/
$stmtActual = $pdo->prepare(' SELECT id, nombre, descripcion, precio,
duracion_minutos, activo FROM servicios WHERE id = :id LIMIT 1 ' );
$stmtActual->execute([':id' => $id]);
$servicioActual = $stmtActual->fetch();
if (!$servicioActual) {
jsonResponse(
404,
[
'ok' => false,
'message' => 'El servicio no existe'
]
);
}
/*
|--------------------------------------------------------------------------
| 11. Verificar nombre duplicado
|--------------------------------------------------------------------------
|
| Excluimos el propio registro.
*/

$stmtDuplicado = $pdo->prepare(' SELECT id FROM servicios WHERE nombre =
:nombre AND id <> :id LIMIT 1 ');
$stmtDuplicado->execute([
':nombre' => $nombre,
':id' => $id
]);
if ($stmtDuplicado->fetch() ) {
jsonResponse(
409,
[
'ok' => false,
'message' => 'Ya existe otro servicio con ese nombre'
]
);
}

/*
|--------------------------------------------------------------------------
| 12. Preparar UPDATE
|--------------------------------------------------------------------------
*/
$stmt = $pdo->prepare('UPDATE servicios SET nombre = :nombre, descripcion =
:descripcion, precio = :precio, duracion_minutos = :duracion_minutos,
activo = :activo WHERE id = :id' );
/*
|--------------------------------------------------------------------------
| 13. Ejecutar UPDATE
|--------------------------------------------------------------------------
*/
$stmt->execute([
':nombre' => $nombre,
':descripcion' => $descripcion,
':precio' => $precio,
':duracion_minutos' => $duracionMinutos,
':activo' => $activo,
':id' => $id
]);
/*
|--------------------------------------------------------------------------
| 14. Recuperar registro actualizado
|--------------------------------------------------------------------------
*/
$stmtResultado =
$pdo->prepare( ' SELECT id, nombre, descripcion, precio, duracion_minutos, activo,
created_at, updated_at FROM servicios WHERE id = :id LIMIT 1' );

$stmtResultado->execute([
':id' => $id
]);

$servicioActualizado = $stmtResultado->fetch();

/*
|--------------------------------------------------------------------------
| 15. Respuesta JSON
|--------------------------------------------------------------------------
*/
jsonResponse(
200,
[
'ok' => true,
'message' => 'Servicio actualizado correctamente',
'data' => $servicioActualizado
]
);
} catch (PDOException $e) {
jsonResponse(
500,
[
'ok' => false,
'message' => 'Error interno al actualizar el servicio'
]
);
}