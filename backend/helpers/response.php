<?php
declare(strict_types=1);

function jsonResponse(
    int $status,
    array $data
): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}