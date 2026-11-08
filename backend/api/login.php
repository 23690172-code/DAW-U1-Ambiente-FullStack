<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Método no permitido']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Datos no válidos']);
    exit;
}

$usuario = trim((string)($data['usuario'] ?? ""));
$contrasena = (string)($data['contrasena'] ?? "");

// --- CONEXIÓN A LA BASE DE DATOS ---
$host = getenv('DB_HOST') ?: 'db';
$database = getenv('DB_NAME') ?: 'appdb';
$user = getenv('DB_USER') ?: 'appuser';
$password = getenv('DB_PASSWORD') ?: 'appsecret';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Buscamos al usuario en la base de datos
    $stmt = $pdo->prepare('SELECT id, usuario, nombre, password_hash, rol, activo FROM usuarios WHERE usuario = :usuario LIMIT 1');
    $stmt->execute([':usuario' => $usuario]);
    $userDB = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verificamos que exista y esté activo
    if ($userDB && (int)$userDB['activo'] === 1) {
        // Verificamos la contraseña
        if (password_verify($contrasena, $userDB['password_hash'])) {
            session_regenerate_id(true);
            
            $_SESSION['user'] = [
                'id' => $userDB['id'],
                'usuario' => $userDB['usuario'],
                'nombre' => $userDB['nombre'],
                'rol' => $userDB['rol']
            ];

            echo json_encode([
                'ok' => true,
                'user' => $_SESSION['user']
            ]);
            exit;
        } else {
            http_response_code(401);
            echo json_encode(['ok' => false, 'message' => 'La contraseña es incorrecta']);
            exit;
        }
    } else {
        http_response_code(401);
        echo json_encode(['ok' => false, 'message' => 'Usuario no encontrado o inactivo']);
        exit;
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Error de BD: ' . $e->getMessage()]);
    exit;
}