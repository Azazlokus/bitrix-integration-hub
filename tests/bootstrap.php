<?php

declare(strict_types=1);

/**
 * Бутстрап PHPUnit.
 *
 * LAB_TESTS_BITRIX=1 (так запускает `make test` в контейнере) — подключается ядро Битрикса для интеграционных тестов.
 * Без переменной (CI, `--testsuite Unit`) — только composer-автозагрузка: unit-тесты не зависят от Битрикса.
 */

require __DIR__ . '/../local/vendor/autoload.php';

// init.php не должен подключать автозагрузку повторно (см. комментарий там)
define('LAB_COMPOSER_AUTOLOADED', true);

if (getenv('LAB_TESTS_BITRIX') !== '1') {
    return;
}

$documentRoot = getenv('DOCUMENT_ROOT');
$documentRoot = is_string($documentRoot) && $documentRoot !== '' ? $documentRoot : '/app/www';
$prolog = $documentRoot . '/bitrix/modules/main/include/prolog_before.php';

if (!is_file($prolog)) {
    fwrite(STDERR, "LAB_TESTS_BITRIX=1, но ядро не найдено: {$prolog}\nУстанови Битрикс (README) или запусти: make test-unit\n");
    exit(1);
}

$_SERVER['DOCUMENT_ROOT'] = $documentRoot;
$_SERVER['HTTP_HOST'] ??= 'localhost';
$_SERVER['SERVER_NAME'] ??= 'localhost';

define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
define('NO_AGENT_CHECK', true);
define('NO_AGENT_STATISTIC', true);
define('STOP_STATISTICS', true);
define('BX_NO_ACCELERATOR_RESET', true);
define('BX_SECURITY_SESSION_VIRTUAL', true); // CLI: сессия в памяти, не в Redis
define('LAB_TESTS_BITRIX_LOADED', true);

// PHPUnit подключает бутстрап внутри метода, а ядро рассчитывает на глобальную область видимости —
// объявляем глобальными переменные, которые создают dbconn.php и prolog.
global $DB, $DBType, $DBSQLServerType, $DBHost, $DBLogin, $DBPassword, $DBName, $DBDebug, $DBDebugToFile,
$APPLICATION, $USER, $MESS, $USER_FIELD_MANAGER, $CACHE_MANAGER, $stackCacheManager, $BX_STATE;

require $prolog;
