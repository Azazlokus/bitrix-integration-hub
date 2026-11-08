# Прогресс

Этапы — по [roadmap.md](roadmap.md) (с 2026-11-08, ADR 0003: Laravel Core — основной бэкенд, Битрикс — витрина).

| # | Этап | Статус | Примечания |
|---|---|---|---|
| 0 | Подготовка репозитория, Claude Code, DevSecOps | ✅ готово | 2026-10-31 |
| 1 | Инфраструктура (docker-compose) | 🟡 код готов, ждёт ручной установки БУС | ветка `feat/stage-1-infra` |
| 2 | Каркас модуля: журнал, outbox, HTTP-клиент | ⏳ следующий | |
| 3 | Laravel Core (PIM, OMS, B2B, Filament, Passport) | — | |
| 4 | Каталог Битрикса миграциями | — | |
| 5 | `commerceml` — Laravel в роли «1С» | — | |
| 6 | `queue` — outbox + RabbitMQ | — | |
| 7 | `webhooks` — подписанные вебхуки | — | |
| 8 | `rest-pull` — остатки из Core API | — | |
| 9 | `sync-call` — B2B-цена и резерв с фолбэком | — | |
| 10 | `bitrix-api` — REST API сайта + B2B-кабинет | — | |
| 11 | Эквайринг и доставка | — | |
| 12 | `batch-csv` — ночная сверка | — | |
| 13 | `search` — Scout → Meilisearch → витрина | — | |
| 14 | Битрикс24 | — | |
| 15 | Фиды и маркетплейс | — | |
| 16 | Уведомления | — | |
| 17 | `anti-patterns` — прямая БД, prolog в artisan | — | |
| 18 | Наблюдаемость | — | |
| 19 | Тестирование и CI обеих систем | — | частично заложено на этапе 0 |
| 20 | `sso` — Keycloak (опционально) | — | |

## Журнал

### Перестройка roadmap (2026-11-08)

Laravel Core — основной бэкенд «ПрофСпорта», Битрикс — витрина, сторонние сервисы — в mock-services, Б24 интегрирует сайт, PostgreSQL 18 для Core. Каждый вид интеграции — отдельный этап с `docs/integrations/<код>.md` и демо. Решение — ADR 0003, план — `roadmap.md`.

### Этап 0 — подготовка (2026-10-31)

- `CLAUDE.md`, `.claude/` (rules, skills `stage`/`new-integration`/`adr`/`check-updates`, агенты `bitrix-reviewer`/`security-reviewer`, хуки защиты ядра и `php -l`).
- Git: Conventional Commits (локальный хук + проверка в CI), `.gitignore` для ядра/секретов, `.gitattributes`, `.editorconfig`.
- GitHub: CI, Security (gitleaks, Semgrep, Trivy, dependency review), Scorecard, release-please, Dependabot, `tools/github-setup.sh`. Подробно — [devsecops.md](devsecops.md).

**Долги этапа 0:** ✅ закрыты на этапе 1 (PHP 8.4, Dependabot composer/docker, hadolint).

### Этап 1 — инфраструктура (2026-11-01)

Сделано:
- `docker-compose.yml`: nginx 1.30, php-fpm 8.4 (от UID хоста), MySQL 8.4 LTS, Redis 8.10, RabbitMQ 4.4, Meilisearch 1.56, Mailpit, mock-services, worker (профиль `worker`), Keycloak (профиль `sso`), cloudflared (профиль `tunnel`). Healthcheck у всех, `up --wait`.
- Вебрут `www/` + монтирование `local/` (ADR 0002). Сети `edge`/`app`/`data`; в PHP передаются только нужные переменные; туннель смотрит на отдельный nginx-сервер `:8081` с allowlist путей.
- Генератор `bitrix/.settings_extra.php` (кеш и сессии в Redis) — `make settings`, unit-тесты.
- PHPUnit 13 с бутстрапом ядра (`LAB_TESTS_BITRIX=1`), интеграционный тест «кеш реально пишется в Redis». PHPStan level 8 + strict-rules, PER-CS 3.0.
- Makefile: `up down shell logs test test-unit migrate worker tunnel lint fix stan settings bitrix-download bitrix-cleanup mysql`.
- Ревью `bitrix-reviewer` и `security-reviewer` — найденное исправлено (двойная автозагрузка composer, секреты в env FPM, туннель на весь сайт, `/bitrix/cache/{css,js}`, HTTPS за туннелем, httpoxy и др.).

Приёмка:
- ✅ `make up` поднимает всё: проверено настоящим `make env` + `make up` в отдельном worktree с другими портами, 8 сервисов healthy. `make install`, `test-unit`, `lint`, `stan` — зелёные.
- ✅ Сайт открывается: PHP 8.4.27 через nginx отдаёт 200; служебные пути (`local/vendor`, `composer.json`, `php_interface`, `.git`) — 403; туннельный порт — 404 вне allowlist; почта доходит до Mailpit.
- ⏳ Кеш через Redis и `make test` с бутстрапом — тест написан, но **проверить можно только после ручной установки БУС** (README → «Установка Битрикса»). Без ядра: unit 6/6 зелёные, интеграционные корректно пропускаются.

Отклонения от ТЗ: PHP 8.4 вместо 8.2, MySQL 8.4, PER-CS 3.0 вместо PSR-12 (надмножество) — ADR 0002. `migrate`/`demo` — заглушки до этапов 3+.

Долги: после установки сверить формат секций `cache`/`session` с `bitrix/modules/main/lib/data/cacheengineredis.php`, прогнать site_checker на PHP 8.4, проверить кодировку соединения; пароль Redis (`requirepass`) — когда подтвердим поддержку в конфиге кеша D7; digest-пиннинг образов.
