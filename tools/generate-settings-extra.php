<?php

declare(strict_types=1);

/**
 * Генерирует $DOCUMENT_ROOT/bitrix/.settings_extra.php из переменных окружения.
 * Запуск: make settings (внутри php-контейнера).
 */

use Lab\Tools\SettingsExtraBuilder;

require __DIR__ . '/../local/vendor/autoload.php';

$documentRoot = getenv('DOCUMENT_ROOT');
$documentRoot = is_string($documentRoot) && $documentRoot !== '' ? $documentRoot : '/app/www';
$bitrixDir = $documentRoot . '/bitrix';

if (!is_file($bitrixDir . '/.settings.php')) {
    fwrite(STDERR, "Битрикс не установлен: нет {$bitrixDir}/.settings.php. Сначала пройди мастер установки (см. README).\n");
    exit(1);
}

$names = ['REDIS_HOST', 'REDIS_PORT', 'BX_CACHE_SID'];
$env = array_combine($names, array_map(static fn(string $name): string|false => getenv($name), $names));

$builder = new SettingsExtraBuilder();

try {
    $content = $builder->render($builder->build($env));
} catch (InvalidArgumentException $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}

$target = $bitrixDir . '/.settings_extra.php';
$tmp = $target . '.tmp';
if (file_put_contents($tmp, $content, LOCK_EX) === false || !rename($tmp, $target)) {
    fwrite(STDERR, "Не удалось записать {$target}\n");
    exit(1);
}

echo "Записан {$target}\n";
