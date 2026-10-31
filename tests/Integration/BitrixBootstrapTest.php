<?php

declare(strict_types=1);

namespace Lab\Integration\Tests\Integration;

use Bitrix\Main\Application;
use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Data\Cache;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Redis;

/**
 * Приёмка этапа 1: ядро подключается из тестов, а кеш Битрикса реально пишется в Redis.
 */
final class BitrixBootstrapTest extends TestCase
{
    protected function setUp(): void
    {
        if (!defined('LAB_TESTS_BITRIX_LOADED')) {
            self::markTestSkipped('Ядро Битрикса не подключено (запусти make test)');
        }
    }

    #[Test]
    public function kernelIsBootstrapped(): void
    {
        self::assertInstanceOf(Application::class, Application::getInstance());
        self::assertTrue(Application::getConnection()->isConnected());
    }

    #[Test]
    public function cacheIsConfiguredForRedis(): void
    {
        $cache = Configuration::getValue('cache');

        self::assertIsArray($cache, 'Нет секции cache — запусти make settings');
        self::assertSame('\\Bitrix\\Main\\Data\\CacheEngineRedis', $cache['type']['class_name'] ?? null);
    }

    #[Test]
    public function cacheEntryLandsInRedis(): void
    {
        $redis = new Redis();
        $redis->connect((string) getenv('REDIS_HOST'), (int) getenv('REDIS_PORT'), 2.0);
        $before = $redis->dbSize();

        $cache = Cache::createInstance();
        $id = 'lab-bootstrap-test-' . bin2hex(random_bytes(6));
        self::assertFalse($cache->initCache(60, $id, '/lab/tests'));
        $cache->startDataCache();
        $cache->endDataCache(['value' => $id]);

        self::assertGreaterThan($before, $redis->dbSize(), 'Кеш не попал в Redis');

        $reader = Cache::createInstance();
        self::assertTrue($reader->initCache(60, $id, '/lab/tests'));
        self::assertSame(['value' => $id], $reader->getVars());

        $reader->clean($id, '/lab/tests');
    }
}
