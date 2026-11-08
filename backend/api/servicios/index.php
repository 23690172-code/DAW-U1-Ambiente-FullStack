<?php 
declare(strict_types=1); 

require_once __DIR__ . '/../config/database.php'; 
require_once __DIR__ . '/../helpers/response.php'; 
require_once __DIR__ . '/../helpers/auth.php'; 

/* 
| 1. Verificar autenticación
| Cualquier usuario autenticado puede consultar servicios.
| La información disponible dependerá de su rol. 
*/ 
$user = requireAuth(); 

/* 
|---
| 2. Verificar método HTTP
|--- 
*/ 
if ($_SERVER['REQUEST_METHOD'] !== 'GET') { 
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
| 3. Obtener filtro opcional de estado
|---
| Ejemplos:
| GET /api/servicios/
| GET /api/servicios/?activo=1
| GET /api/servicios/?activo=0
| 
| Nota:
| El CLIENTE siempre verá únicamente servicios activos,
| independientemente del parámetro enviado.
*/ 
$filtroActivo = null; 

if (isset($_GET['activo']) && $_GET['activo'] !== "") { 
    $valorActivo = filter_var($_GET['activo'], FILTER_VALIDATE_INT); 
    if (!in_array($valorActivo, [0, 1], true)) { 
        jsonResponse( 
            400, 
            [ 
                'ok' => false, 
                'message' => 'El filtro activo debe ser 0 o 1' 
            ]
        ); 
    } 
    $filtroActivo = $valorActivo; 
} 

/* 
|---
| 4. Consultar base de datos
|---
*/ 
try { 
    $pdo = getPDO(); 
    
    /* 
    |---
    | 5. Construir consulta según el rol
    |---
    */ 
    $sql = 'SELECT id, nombre, descripcion, precio, duracion_minutos, activo, created_at, updated_at FROM servicios'; 
    $params = []; 

    /* 
    |---
    | Si el usuario es CLIENTE:
    | únicamente puede consultar servicios activos.
    |---
    */ 
    if ($user['rol'] === 'CLIENTE') { 
        $sql .= ' WHERE activo = :activo '; 
        $params[':activo'] = 1; 
    } 
    /* 
    |---
    | Si es administrador:
    | puede consultar todos o aplicar filtro activo/inactivo.
    |---
    */ 
    elseif (in_array($user['rol'], ['ADMIN_SISTEMA', 'ADMIN_SERVICIOS'], true)) { 
        if ($filtroActivo !== null) { 
            $sql .= ' WHERE activo = :activo '; 
            $params[':activo'] = $filtroActivo; 
        } 
    } 
    /* 
    | Si el rol almacenado no es reconocido
    */ 
    else { 
        jsonResponse( 
            403, 
            [ 
                'ok' => false, 
                'message' => 'El rol del usuario no tiene permiso para consultar servicios' 
            ]
        ); 
    } 

    /* 
    | Ordenar resultados
    */ 
    $sql .= ' ORDER BY nombre ASC '; 

    /* 
    | 6. Preparar consulta
    */ 
    $stmt = $pdo->prepare($sql); 

    /* 
    | 7. Ejecutar consulta
    */ 
    $stmt->execute($params); 

    /* 
    | 8. Recuperar información
    */ 
    $servicios = $stmt->fetchAll(); 

    /* 
    | 9. Respuesta JSON
    */ 
    jsonResponse( 
        200, 
        [ 
            'ok' => true, 
            'data' => $servicios, 
            'meta' => [ 
                'total' => count($servicios), 
                'rol' => $user['rol'], 
                'filtroActivo' => $user['rol'] === 'CLIENTE' ? 1 : $filtroActivo 
            ] 
        ]
    ); 

} catch (PDOException $e) { 
    /* 
    |---
    | 10. Manejo de errores
    |---
    | No enviar $e->getMessage() al frontend.
    */ 
    jsonResponse( 
        500, 
        [ 
            'ok' => false, 
            'message' => 'Error interno al consultar los servicios' 
        ]
    ); 
}