<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
session_start();

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode([
        'ok' => false,
        'message' => 'Acceso no autorizado'
    ]);
    exit;
}

$servicios = [
    [
        'id' => 1,
        'nombre' => 'Desarrollo Web',
        'descripcion' => 'Desarrollo de aplicaciones web.',
        'precio' => 8500
    ],
    [
        'id' => 2,
        'nombre' => 'Soporte Técnico',
        'descripcion' => 'Soporte de infraestructura.',
        'precio' => 3500
    ],
    [
        'id' => 3,
        'nombre' => 'Consultoría',
        'descripcion' => 'Consultoría para proyectos tecnológicos.',
        'precio' => 5000
    ]
];

echo json_encode($servicios);