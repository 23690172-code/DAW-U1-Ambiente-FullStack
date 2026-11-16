<?php
declare(strict_types=1);

function readJson(): array {
    try {
        $input = file_get_contents('php://input');
        if (empty($input)) {
            return [];
        }
        return json_decode($input, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $e) {
        jsonResponse(400, ['ok' => false, 'message' => 'JSON no válido']);
    }
}