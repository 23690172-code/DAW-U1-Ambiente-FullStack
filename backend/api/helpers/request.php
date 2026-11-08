<?php
declare(strict_types=1);

function readJson(): array {
    try {
        return json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $e) {
        jsonResponse(400, ['ok' => false, 'message' => 'JSON no válido']);
    }
}