<?php

declare(strict_types=1);

// Composer-автозагрузка собственного кода и зависимостей (local/composer.json).
$autoload = __DIR__ . '/../vendor/autoload.php';
if (is_file($autoload)) {
    require_once $autoload;
}
