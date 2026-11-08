<?php

declare(strict_types=1);

// Composer-автозагрузка собственного кода и зависимостей (local/composer.json).
// CLI (PHPUnit, воркеры) грузит её раньше по пути /app/local/vendor, а ядро приходит сюда через /app/www/local:
// для require_once это разные файлы (bind-mount, не симлинк) — повторная загрузка дала бы фатальную ошибку.
if (!defined('LAB_COMPOSER_AUTOLOADED')) {
    $autoload = __DIR__ . '/../vendor/autoload.php';
    if (is_file($autoload)) {
        require_once $autoload;
        define('LAB_COMPOSER_AUTOLOADED', true);
    }
}
