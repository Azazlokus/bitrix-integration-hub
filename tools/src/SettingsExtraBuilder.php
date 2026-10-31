<?php

declare(strict_types=1);

namespace Lab\Tools;

use InvalidArgumentException;

/**
 * Строит содержимое bitrix/.settings_extra.php: кеш и сессии Битрикса в Redis.
 *
 * Формат секций — по документации D7 («Настройка кеширования», «Хранение сессий»).
 * .settings_extra.php перекрывает одноимённые ключи .settings.php, который создаёт мастер установки,
 * поэтому файл можно перегенерировать в любой момент без потери настроек БД.
 */
final class SettingsExtraBuilder
{
    /**
     * @param array<string, string|false> $env значения переменных окружения (как из getenv())
     *
     * @return array<string, array{value: array<string, mixed>, readonly?: bool}>
     */
    public function build(array $env): array
    {
        $host = $this->required($env, 'REDIS_HOST');
        $port = $this->port($this->required($env, 'REDIS_PORT'));
        $sid = $this->required($env, 'BX_CACHE_SID');

        return [
            'cache' => [
                'value' => [
                    'type' => [
                        'class_name' => '\\Bitrix\\Main\\Data\\CacheEngineRedis',
                        'extension' => 'redis',
                    ],
                    'redis' => [
                        'host' => $host,
                        'port' => (string) $port,
                    ],
                    'sid' => $sid,
                ],
                'readonly' => true,
            ],
            'session' => [
                'value' => [
                    'mode' => 'default',
                    'handlers' => [
                        'general' => [
                            'type' => 'redis',
                            'host' => $host,
                            'port' => (string) $port,
                        ],
                    ],
                ],
                'readonly' => true,
            ],
        ];
    }

    /**
     * @param array<string, mixed> $settings
     */
    public function render(array $settings): string
    {
        return "<?php\n\n"
            . "// Сгенерировано tools/generate-settings-extra.php — не редактировать вручную.\n"
            . 'return ' . var_export($settings, true) . ";\n";
    }

    /**
     * @param array<string, string|false> $env
     */
    private function required(array $env, string $name): string
    {
        $value = $env[$name] ?? false;
        if ($value === false || trim($value) === '') {
            throw new InvalidArgumentException("Environment variable {$name} is required");
        }

        return trim($value);
    }

    private function port(string $value): int
    {
        $port = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 65535]]);
        if ($port === false) {
            throw new InvalidArgumentException("Invalid port: {$value}");
        }

        return $port;
    }
}
