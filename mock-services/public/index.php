<?php

declare(strict_types=1);

/**
 * Фронт-контроллер mock-services: эмуляторы внешних систем (платёжка, доставка, SMS, ERP, маркетплейс).
 * Эмуляторы добавляются на этапах 7, 8, 10, 12, 13. Сейчас — только healthcheck.
 */

$path = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
$path = is_string($path) && $path !== '' ? $path : '/';

header('Content-Type: application/json; charset=utf-8');

$respond = static function (int $status, array $body): never {
    http_response_code($status);
    echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    exit;
};

match ($path) {
    '/health' => $respond(200, ['status' => 'ok', 'services' => []]),
    default => $respond(404, ['error' => ['code' => 'not_found', 'message' => "No mock for {$path}"]]),
};
