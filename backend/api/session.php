<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
session_start();

if (isset($_SESSION['user'])) {
    echo json_encode([
        'authenticated' => true,
        'user' => $_SESSION['user']
    ]);
    exit;
}

echo json_encode([
    'authenticated' => false,
    'user' => null
]);