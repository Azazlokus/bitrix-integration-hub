# Bitrix Integration Lab

Учебный интернет-магазин на 1С-Битрикс (БУС), в котором собраны типовые интеграции: 1С (CommerceML), Битрикс24, платёжка, доставка, RabbitMQ, ERP, Meilisearch, фиды, уведомления, SSO, мост с Laravel.
Полное ТЗ: @bitrix-integration-lab-SPEC.md — источник истины по этапам и критериям приёмки.
Текущий прогресс: @docs/progress.md

Общение с пользователем, комментарии в документации и ADR — на русском. Идентификаторы, имена классов и сообщения коммитов — на английском.

## Как работать

- **Строго поэтапно**, порядок: 1 → 2 → 3 → 4 → 5 → 6 → 9 → 7 → 8 → 10 → 11 → 12 → 13 → 16 → 17 → 15 → 14. Для этапа используй скилл `/stage <N>`.
- Этап закрыт только когда: код + тесты + проверены критерии приёмки + обновлён `docs/progress.md` + коммит(ы).
- Перед началом этапа — короткий план и вопросы, если в ТЗ есть развилка (например Guzzle vs `HttpClient`). Решения такого уровня оформляй ADR (скилл `/adr`).
- **Всегда проверяй, нельзя ли сделать лучше.** Экосистема меняется быстро: перед добавлением зависимости, образа Docker или GitHub Action проверь актуальную версию (packagist / Docker Hub / GitHub releases), changelog и наличие более удачной альтернативы. Если ТЗ предлагает устаревший подход — скажи об этом и предложи замену, но не меняй молча. Скилл `/check-updates` — периодическая ревизия всего стека.
- Не выдумывай API Битрикса. Если не уверен в сигнатуре D7-класса или событии — проверь в исходниках ядра (`bitrix/modules/...`, когда ядро установлено) или в документации, и скажи, что проверил.

## Жёсткие правила (из ТЗ, раздел 0)

- **Ядро не трогаем.** Свой код — только в `local/` (`modules`, `components`, `templates`, `php_interface`, `routes`). `bitrix/` и `upload/` — в `.gitignore`, редактировать их запрещено (хук это блокирует). Исключение: `bitrix/.settings_extra.php` генерируется скриптом из репо, руками не правится.
- **D7 везде, где возможно**: неймспейсы, ORM `DataManager`, `Engine\Controller`, `EventManager`, `Config\Option`, `DI\ServiceLocator`. Старое API — только при отсутствии аналога и с комментарием «почему».
- PHP `declare(strict_types=1);`, PSR-12, composer-автозагрузка (`local/composer.json`).
- Модуль `local/modules/lab.integration`, namespace `Lab\Integration`, слои `Domain` / `Application` / `Infrastructure` / `Presentation` (подробно — `.claude/rules/architecture.md`).
- Секреты — только `.env` + `Config\Option`. В коде, тестах, фикстурах, логах — никаких реальных токенов/ключей.
- Любой вызов внешней системы: таймаут, ретраи с backoff, идемпотентность, запись в журнал интеграций.
- Схема БД и инфоблоков — только миграциями. Никаких невоспроизводимых правок в админке.

## Команды

Make-цели появляются по мере этапов (этап 1 создаёт основные). Сейчас:

```bash
make hooks        # подключить git-хуки (.githooks): conventional commits, php -l, gitleaks
make help         # список целей
```

После этапа 1: `make up | down | shell | logs | test | migrate | worker | tunnel | demo`.
Все PHP-команды выполняются **внутри контейнера** (`make shell` / `docker compose exec php ...`), а не на хосте (на хосте PHP 8.4 — другая версия, чем в контейнере).

## Git и коммиты

- **Conventional Commits** обязательны: `type(scope): описание` в повелительном наклонении, на английском, ≤ 72 символов в заголовке.
  - type: `feat fix refactor perf test docs build ci chore revert style`
  - scope (по этапам): `infra module catalog 1c api b24 payment delivery queue erp search feeds notify sso laravel observability tests ci docs deps claude`
  - Ломающие изменения: `feat(api)!: ...` + `BREAKING CHANGE:` в теле.
- Один логический шаг — один коммит. Не смешивать рефакторинг и фичу.
- Ветки: `main` защищена; работа в `feat/<stage>-<short>`, `fix/...`, `chore/...`. PR сливается squash'ем, заголовок PR — тоже conventional (проверяется CI).
- Перед коммитом: `git status` + `git diff --staged` — убедись, что не утекают `.env`, логи, дампы БД, файлы ядра.
- Не пушить и не создавать PR без явной просьбы пользователя.

## DevSecOps

Пайплайны в `.github/workflows/`, описание — `docs/devsecops.md`. Кратко: CI (lint, PHPStan, PHPUnit, composer audit), security (gitleaks, Semgrep, Trivy, dependency review), OpenSSF Scorecard, проверка conventional commits, release-please, Dependabot.
При изменении workflow: все actions закреплены **по полному SHA** с комментарием версии, `permissions` минимальные на уровне job, `persist-credentials: false`. Прогоняй `actionlint` и `zizmor`, если доступны.

## Структура (целевая)

```
local/modules/lab.integration/   # модуль: lib/{Domain,Application,Infrastructure,Presentation}, install/, options.php
local/php_interface/             # init.php, include/sale_payment/
local/routes/api.php             # роутинг D7 для REST API
local/components, local/templates
mock-services/                   # эмуляторы внешних систем (платёжка, доставка, SMS, ERP, 1С)
apps/laravel-hub/                # этап 15
docker/, docker-compose.yml, Makefile, .env.example
tests/                           # Unit (без Битрикса), Integration (с бутстрапом), Contract
docs/                            # architecture.md, progress.md, adr/, openapi.yaml, <integration>.md
tools/                           # CLI-утилиты, генераторы, скрипты настройки
```

## Открытые решения (обсудить на этапе 1)

- Версия PHP: ТЗ требует 8.2+, но у PHP 8.2 активная поддержка закончилась, security-фиксы — до конца 2026. Предпочтительно 8.3/8.4 — проверить совместимость с актуальной версией БУС.
- HTTP-клиент: `Bitrix\Main\Web\HttpClient` vs Guzzle (PSR-18) — ADR на этапе 2.
- Механизм миграций: `sprint.migration` vs свой — ADR на этапе 1/3.
