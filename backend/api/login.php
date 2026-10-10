<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'ok' => false,
        'message' => 'Metodo no permitido'
    ]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode([
        'ok' => false,
        'message' => 'Datos no validos'
    ]);
    exit;
}

$usuario = trim((string)($data['usuario'] ?? ''));
$contrasena = (string)($data['contrasena'] ?? '');

// Datos unicamente para la practica
$usuarioDemo = 'admin';
$contrasenaDemo = '123456';

if ($usuario === $usuarioDemo && $contrasena === $contrasenaDemo) {
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'usuario' => 'admin',
        'nombre' => 'Administrador Demo',
        'rol' => 'Administrador'
    ];

    echo json_encode([
        'ok' => true,
        'user' => $_SESSION['user']
    ]);
    exit;
}

http_response_code(401);
echo json_encode([
    'ok' => false,
    'message' => 'Usuario o contraseña incorrectos'
]);