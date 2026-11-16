<?php
declare(strict_types=1);

require_once __DIR__ . '/../../helpers/response.php';

session_start();
session_destroy();

jsonResponse(200, ['ok' => true, 'message' => 'Sesión cerrada']);