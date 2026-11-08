<?php

header('Content-Type: application/json; charset=utf-8');

try {

    $host = getenv('DB_HOST');
    $database = getenv('DB_NAME');
    $user = getenv('DB_USER');
    $password = getenv('DB_PASSWORD');

    $pdo = new PDO(
        "mysql:host=$host;dbname=$database;charset=utf8mb4",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    );

    $pdo->query("SELECT 1");

    echo json_encode([
        'ok' => true,
        'backend' => 'PHP funcionando',
        'database' => 'MySQL conectado'
    ]);

} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        'ok' => false,
        'backend' => 'PHP funcionando',
        'database' => 'Error de conexión'
    ]);
}