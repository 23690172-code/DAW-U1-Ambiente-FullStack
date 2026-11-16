<?php
declare(strict_types=1);

require_once __DIR__ . '/../../helpers/response.php';

session_start();

if (isset($_SESSION['user'])) {
    jsonResponse(200, ['ok' => true, 'data' => ['user' => $_SESSION['user']]]);
} else {
    jsonResponse(401, ['ok' => false, 'message' => 'No autenticado']);
}