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

    /**
     * Не считаем ключи (dbSize): в тот же Redis пишут сессии, а allkeys-lru может вытеснять ключи.
     * Вместо этого смотрим, что запись и чтение кеша порождают команды записи и попадания в Redis.
     */
    #[Test]
    public function cacheEntryLandsInRedis(): void
    {
        $redis = new Redis();
        $redis->connect((string) getenv('REDIS_HOST'), (int) getenv('REDIS_PORT'), 2.0);
        $id = 'lab-bootstrap-test-' . bin2hex(random_bytes(6));
        $dir = '/lab/tests';

        try {
            $writesBefore = $this->writeCommands($redis);
            $cache = Cache::createInstance();
            self::assertFalse($cache->initCache(60, $id, $dir));
            $cache->startDataCache();
            $cache->endDataCache(['value' => $id]);
            self::assertGreaterThan($writesBefore, $this->writeCommands($redis), 'Запись кеша не дошла до Redis');

            $hitsBefore = (int) $redis->info('stats')['keyspace_hits'];
            $reader = Cache::createInstance();
            self::assertTrue($reader->initCache(60, $id, $dir));
            self::assertSame(['value' => $id], $reader->getVars());
            self::assertGreaterThan($hitsBefore, (int) $redis->info('stats')['keyspace_hits'], 'Чтение кеша не из Redis');
        } finally {
            Cache::createInstance()->clean($id, $dir);
        }
    }

    private function writeCommands(Redis $redis): int
    {
        $total = 0;
        foreach ($redis->info('commandstats') as $command => $stats) {
            if (preg_match('/^cmdstat_(set|setex|psetex|hset|hmset|mset|sadd|expire)$/', (string) $command) === 1
                && preg_match('/calls=(\d+)/', (string) $stats, $m) === 1) {
                $total += (int) $m[1];
            }
        }

        return $total;
    }
}
