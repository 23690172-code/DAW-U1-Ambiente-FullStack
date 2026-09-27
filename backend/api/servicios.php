<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
$servicios = [
    [
        'id' => 1,
        'nombre' => 'Desarrollo Web',
        'descripcion' => 'Desarrollo de aplicaciones web empresariales.',
        'precio' => 8500
    ],
    [
        'id' => 2,
        'nombre' => 'Soporte Técnico',
        'descripcion' => 'Servicio de soporte para infraestructura tecnológica.',
        'precio' => 3500
    ],
    [
        'id' => 3,
        'nombre' => 'Consultoría',
        'descripcion' => 'Análisis y asesoría para proyectos tecnológicos.',
        'precio' => 5000
    ]
];
echo json_encode($servicios, JSON_UNESCAPED_UNICODE);