<?php

declare(strict_types=1);

namespace Lab\Integration\Tests\Unit\Tools;

use InvalidArgumentException;
use Lab\Tools\SettingsExtraBuilder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SettingsExtraBuilderTest extends TestCase
{
    private const array ENV = ['REDIS_HOST' => 'redis', 'REDIS_PORT' => '6379', 'BX_CACHE_SID' => 'lab'];

    #[Test]
    public function buildsRedisCacheAndSessionSections(): void
    {
        $settings = new SettingsExtraBuilder()->build(self::ENV);

        self::assertSame('\\Bitrix\\Main\\Data\\CacheEngineRedis', $settings['cache']['value']['type']['class_name']);
        self::assertSame(['host' => 'redis', 'port' => '6379'], $settings['cache']['value']['redis']);
        self::assertSame('lab', $settings['cache']['value']['sid']);
        self::assertSame('redis', $settings['session']['value']['handlers']['general']['type']);
        self::assertSame('default', $settings['session']['value']['mode']);
    }

    #[Test]
    public function renderedFileReturnsSameArray(): void
    {
        $builder = new SettingsExtraBuilder();
        $settings = $builder->build(self::ENV);

        $file = tempnam(sys_get_temp_dir(), 'settings_extra');
        self::assertNotFalse($file);
        file_put_contents($file, $builder->render($settings));

        try {
            self::assertSame($settings, require $file);
        } finally {
            unlink($file);
        }
    }

    /**
     * @param array<string, string|false> $env
     */
    #[Test]
    #[DataProvider('invalidEnvProvider')]
    public function rejectsInvalidEnvironment(array $env, string $message): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        new SettingsExtraBuilder()->build($env);
    }

    /**
     * @return iterable<string, array{array<string, string|false>, string}>
     */
    public static function invalidEnvProvider(): iterable
    {
        yield 'missing host' => [['REDIS_HOST' => false] + self::ENV, 'REDIS_HOST'];
        yield 'blank sid' => [['BX_CACHE_SID' => '  '] + self::ENV, 'BX_CACHE_SID'];
        yield 'port not a number' => [['REDIS_PORT' => 'abc'] + self::ENV, 'Invalid port'];
        yield 'port out of range' => [['REDIS_PORT' => '70000'] + self::ENV, 'Invalid port'];
    }
}
