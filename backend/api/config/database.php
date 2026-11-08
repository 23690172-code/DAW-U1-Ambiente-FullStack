<?php
declare(strict_types=1);

function getPDO(): PDO {
    // Usamos getenv porque en el compose.yaml de la Práctica 1 se configuraron
    // estas variables de entorno para el backend.
    $host = getenv('DB_HOST') ?: 'db';
    $database = getenv('DB_NAME') ?: 'appdb';
    $user = getenv('DB_USER') ?: 'appuser';
    $password = getenv('DB_PASSWORD') ?: 'appsecret';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$database;charset=$charset";
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        return new PDO($dsn, $user, $password, $options);
    } catch (PDOException $e) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok' => false, 
            'message' => 'Error de conexión a la base de datos: ' . $e->getMessage()
        ]);
        exit;
    }
}