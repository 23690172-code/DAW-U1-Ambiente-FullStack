<?php
declare(strict_types=1);

function getPDO(): PDO {
    static $pdo = null;
    
    if ($pdo === null) {
        $host = getenv('DB_HOST') ?: 'db';
        $db   = getenv('DB_NAME') ?: 'appdb';
        $user = getenv('DB_USER') ?: 'appuser';
        $pass = getenv('DB_PASSWORD') ?: 'appsecret';
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
            
            $offset = (new DateTime())->format('P'); 
            $pdo->exec("SET time_zone = '$offset'");
            
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'message' => 'Error de conexión a la base de datos']);
            exit;
        }
    }
    
    return $pdo;
}