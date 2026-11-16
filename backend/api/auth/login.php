<?php
declare(strict_types=1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/request.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$data = readJson();
$usuario = trim((string)($data['usuario'] ?? ''));
$password = trim((string)($data['password'] ?? ''));

if ($usuario === '' || $password === '') {
    jsonResponse(422, ['ok' => false, 'message' => 'Usuario y contraseña son obligatorios']);
}

try {
    $pdo = getPDO();
    
    $stmt = $pdo->prepare('SELECT id, usuario, nombre, email, password_hash, rol FROM usuarios WHERE usuario = :usuario OR email = :email LIMIT 1');
    
    $stmt->execute([
        ':usuario' => $usuario,
        ':email'   => $usuario
    ]);
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password_hash'])) {
        jsonResponse(401, ['ok' => false, 'message' => 'Credenciales incorrectas']);
    }

    $_SESSION['user'] = [
        'id' => $user['id'],
        'usuario' => $user['usuario'],
        'nombre' => $user['nombre'],
        'email' => $user['email'],
        'rol' => $user['rol']
    ];

    jsonResponse(200, [
        'ok' => true,
        'message' => 'Inicio de sesión exitoso',
        'user' => $_SESSION['user'] 
    ]);

} catch (Throwable $e) {
    jsonResponse(500, [
        'ok' => false, 
        'message' => 'Error en el servidor',
        'debug' => $e->getMessage()
    ]);
}