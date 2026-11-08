# Bitrix Integration Lab

Учебный, но приближенный к коммерческому проект «ПрофСпорт»: основной бэкенд компании на **Laravel 13** (`apps/laravel-core`: PIM, цены, остатки, OMS, B2B) и интернет-магазин на 1С-Битрикс (БУС) как витрина. Цель — показать каждый вид интеграции на паре «Битрикс ↔ Laravel» плюс сторонние сервисы (эквайринг, доставка, SMS, маркетплейс, Битрикс24).
**Источник истины по этапам и приёмке:** @docs/roadmap.md (ADR 0003). Исходное ТЗ @bitrix-integration-lab-SPEC.md — его раздел 0 (правила) и критерии приёмки, на которые ссылается roadmap.
Текущий прогресс: @docs/progress.md

Владелец хорошо знает Laravel: объясняя интеграцию или механизм Битрикса, давай аналогию из Laravel и говори, чем Битрикс отличается.

Общение с пользователем, комментарии в документации и ADR — на русском. Идентификаторы, имена классов и сообщения коммитов — на английском.

## Как работать

- **Строго поэтапно**, по номерам этапов `docs/roadmap.md`. Для этапа используй скилл `/stage <N>`.
- Каждый вид интеграции оформляется `docs/integrations/<код>.md` по шаблону `docs/integrations/_template.md` и получает `make demo-<код>`.
- Этап закрыт только когда: код + тесты + проверены критерии приёмки + обновлён `docs/progress.md` + коммит(ы).
- Перед началом этапа — короткий план и вопросы, если в ТЗ есть развилка (например Guzzle vs `HttpClient`). Решения такого уровня оформляй ADR (скилл `/adr`).
- **Всегда проверяй, нельзя ли сделать лучше.** Экосистема меняется быстро: перед добавлением зависимости, образа Docker или GitHub Action проверь актуальную версию (packagist / Docker Hub / GitHub releases), changelog и наличие более удачной альтернативы. Если ТЗ предлагает устаревший подход — скажи об этом и предложи замену, но не меняй молча. Скилл `/check-updates` — периодическая ревизия всего стека.
- Не выдумывай API Битрикса. Если не уверен в сигнатуре D7-класса или событии — проверь в исходниках ядра (`bitrix/modules/...`, когда ядро установлено) или в документации, и скажи, что проверил.

## Жёсткие правила (из ТЗ, раздел 0)

- **Ядро не трогаем.** Свой код — только в `local/` (`modules`, `components`, `templates`, `php_interface`, `routes`). `bitrix/` и `upload/` — в `.gitignore`, редактировать их запрещено (хук это блокирует). Исключение: `bitrix/.settings_extra.php` генерируется скриптом из репо, руками не правится.
- **D7 везде, где возможно**: неймспейсы, ORM `DataManager`, `Engine\Controller`, `EventManager`, `Config\Option`, `DI\ServiceLocator`. Старое API — только при отсутствии аналога и с комментарием «почему».
- PHP `declare(strict_types=1);`, PSR-12, composer-автозагрузка (`local/composer.json`).
- Модуль `local/modules/lab.integration`, namespace `Lab\Integration`, слои `Domain` / `Application` / `Infrastructure` / `Presentation` (подробно — `.claude/rules/architecture.md`).
- Laravel Core — правила в `.claude/rules/laravel.md`. Межсистемные контракты — только в `contracts/`, с версией.
- Секреты — только `.env` + `Config\Option`. В коде, тестах, фикстурах, логах — никаких реальных токенов/ключей.
- Любой вызов внешней системы: таймаут, ретраи с backoff, идемпотентность, запись в журнал интеграций.
- Схема БД и инфоблоков — только миграциями. Никаких невоспроизводимых правок в админке.

## Команды

```bash
make help         # список целей
make up / down    # стек (создаёт .env из .env.example при первом запуске)
make test         # все тесты с бутстрапом Битрикса (нужен запущенный стек и установленное ядро)
make test-unit    # unit-тесты без Битрикса и без запущенного стека
make lint / fix / stan   # PER-CS 3.0 / автофикс / PHPStan level 8
make settings     # сгенерировать bitrix/.settings_extra.php (Redis)
make composer c="require vendor/pkg"
```

Все PHP-команды — **внутри контейнера** (Makefile это делает), не на хосте: версия PHP и расширения должны совпадать с образом.
Пути в контейнере: репозиторий — `/app`, DOCUMENT_ROOT — `/app/www`, `local/` смонтирован в `/app/www/local`.

## Git и коммиты

- **Conventional Commits** обязательны: `type(scope): описание` в повелительном наклонении, на английском, ≤ 72 символов в заголовке.
  - type: `feat fix refactor perf test docs build ci chore revert style`
  - scope: `infra module core contracts catalog 1c queue webhooks sync api b24 payment delivery erp batch search feeds notify sso laravel observability tests ci docs deps adr claude security release` (`core` — Laravel Core, `1c` — обмен CommerceML)
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
www/                             # DOCUMENT_ROOT: ядро (bitrix/, upload/) в .gitignore, публичные страницы — исключениями
local/modules/lab.integration/   # модуль: lib/{Domain,Application,Infrastructure,Presentation}, install/, options.php
local/php_interface/             # init.php, include/sale_payment/
local/routes/api.php             # роутинг D7 для REST API
local/components, local/templates
mock-services/                   # только сторонние сервисы: эквайринг, доставка, SMS, маркетплейс
apps/laravel-core/               # Laravel Core: PIM, OMS, B2B (этап 3)
contracts/                       # межсистемные контракты: OpenAPI, JSON-схемы сообщений
docker/, docker-compose.yml, Makefile, .env.example
tests/                           # Unit (без Битрикса), Integration (с бутстрапом), Contract
docs/                            # roadmap.md, progress.md, architecture.md, adr/, integrations/<код>.md
tools/                           # CLI-утилиты, генераторы, скрипты настройки
```

## Решения и открытые вопросы

- PHP 8.4, MySQL 8.4 LTS, раскладка вебрута — ADR 0002. Совместимость БУС с PHP 8.4 официально не подтверждена — при странных ошибках ядра сначала проверь `site_checker`.
- Стиль кода — PER-CS 3.0 (преемник PSR-12).
- Laravel Core — основной бэкенд, Битрикс — витрина, Б24 интегрирует сайт — ADR 0003. Laravel Core на PostgreSQL 18.
- Открыто: HTTP-клиент `Bitrix\Main\Web\HttpClient` vs Guzzle (PSR-18) — ADR на этапе 2; механизм миграций `sprint.migration` vs свой — ADR на этапе 4 (каталог).
