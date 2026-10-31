# Прогресс

Порядок этапов: 1 → 2 → 3 → 4 → 5 → 6 → 9 → 7 → 8 → 10 → 11 → 12 → 13 → 16 → 17 → 15 → 14.

| # | Этап | Статус | Примечания |
|---|---|---|---|
| 0 | Подготовка репозитория, Claude Code, DevSecOps | ✅ готово | 2026-10-31 |
| 1 | Инфраструктура (docker-compose) | 🟡 код готов, ждёт установки БУС | 2026-11-01, ветка `feat/stage-1-infra` |
| 2 | Каркас модуля и журнал интеграций | ⏳ следующий | |
| 3 | Каталог и данные | — | |
| 4 | Обмен с 1С (CommerceML) | — | |
| 5 | REST API сайта | — | |
| 6 | Битрикс24: CRM | — | |
| 9 | Очереди (RabbitMQ) | — | |
| 7 | Платёжная система | — | |
| 8 | Служба доставки | — | |
| 10 | Внешняя ERP | — | |
| 11 | Meilisearch | — | |
| 12 | Фиды и маркетплейсы | — | |
| 13 | Уведомления | — | |
| 16 | Наблюдаемость | — | |
| 17 | Тестирование и CI | — | частично заложено на этапе 0 |
| 15 | Мост с Laravel | — | |
| 14 | SSO (Keycloak) | — | опционально |

## Журнал

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
- ✅ `make up` поднимает всё — проверено (изолированный smoke-проект): 8 сервисов healthy.
- ✅ Сайт открывается: PHP 8.4.27 через nginx отдаёт 200; служебные пути (`local/vendor`, `composer.json`, `php_interface`, `.git`) — 403; туннельный порт — 404 вне allowlist; почта доходит до Mailpit.
- ⏳ Кеш через Redis и `make test` с бутстрапом — тест написан, но **проверить можно только после ручной установки БУС** (README → «Установка Битрикса»). Без ядра: unit 6/6 зелёные, интеграционные корректно пропускаются.

Отклонения от ТЗ: PHP 8.4 вместо 8.2, MySQL 8.4, PER-CS 3.0 вместо PSR-12 (надмножество) — ADR 0002. `migrate`/`demo` — заглушки до этапов 3+.

Долги: после установки сверить формат секций `cache`/`session` с `bitrix/modules/main/lib/data/cacheengineredis.php`, прогнать site_checker на PHP 8.4, проверить кодировку соединения; пароль Redis (`requirepass`) — когда подтвердим поддержку в конфиге кеша D7; digest-пиннинг образов.
