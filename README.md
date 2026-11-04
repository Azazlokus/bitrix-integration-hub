# Bitrix Integration Lab

Учебный интернет-магазин на 1С-Битрикс (БУС), в котором собраны типовые интеграции коммерческой разработки: 1С (CommerceML), Битрикс24 (вебхуки, OAuth-приложение, встройки, роботы), своя платёжка и доставка, RabbitMQ + Transactional Outbox, внешняя ERP, Meilisearch, фиды, SMS/Telegram, SSO (Keycloak), мост с Laravel.

> 🚧 В разработке. Статус этапов — [docs/progress.md](docs/progress.md). ТЗ — [bitrix-integration-lab-SPEC.md](bitrix-integration-lab-SPEC.md).

## Требования

Docker 25+ с Compose v2, GNU Make, Linux/WSL2 (репозиторий — в файловой системе Linux, не в `/mnt/c`).

## Быстрый старт

```bash
make hooks      # git-хуки: Conventional Commits, php -l, gitleaks
make env        # создаст .env из .env.example — замени в нём все change-me
make up         # соберёт образы и поднимет стек
make install    # composer install (dev-инструменты, автозагрузка)
```

Заменить пароли нужно **до первого** `make up`: MySQL и RabbitMQ запоминают их при инициализации томов.

### Установка Битрикса (вручную, один раз)

1. `make bitrix-download` — скачает `bitrixsetup.php` в `www/`.
2. Открой http://127.0.0.1:8080/bitrixsetup.php → редакция **«Бизнес»** (триал 30 дней), демо-магазин можно не ставить.
3. На шаге «База данных»: сервер `mysql`, база/пользователь/пароль — `MYSQL_DATABASE` / `MYSQL_USER` / `MYSQL_PASSWORD` из `.env`, «Существующая база».
4. Проверь кодировку: база создана в `utf8mb4` (`bitrix.cnf`); если «Проверка системы» ругается на несовпадение кодировки соединения — в `www/bitrix/php_interface/after_connect_d7.php` должно быть `SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'`.
5. После завершения мастера: `make settings` — кеш и сессии Битрикса переключатся на Redis (`bitrix/.settings_extra.php` генерируется скриптом, руками не правится).
6. Проверка: `make test` (бутстрап ядра + тест «кеш пишется в Redis»), «Проверка системы» в админке (`/bitrix/admin/site_checker.php`).
7. `make bitrix-cleanup` — удалит `www/bitrixsetup.php` и `www/restore.php`.

### Портал Битрикс24 (понадобится на этапе 6)

1. Зарегистрируй бесплатный облачный портал на bitrix24.ru.
2. «Разработчикам → Другое → Входящий вебхук»: права `crm`, `catalog`, `bizproc`. URL вебхука — в `.env` (переменная появится на этапе 6).
3. «Разработчикам → Другое → Локальное приложение»: адрес обработчика — URL из `make tunnel`.

## Сервисы

| Сервис | Адрес на хосте |
|---|---|
| Сайт | http://127.0.0.1:8080 |
| Mailpit (почта) | http://127.0.0.1:8025 |
| RabbitMQ UI | http://127.0.0.1:15672 |
| Meilisearch | http://127.0.0.1:7700 |
| Mock-сервисы | http://127.0.0.1:8090/health |
| MySQL | 127.0.0.1:3306 |
| Keycloak (`docker compose --profile sso up -d`) | http://127.0.0.1:8180 |

## Команды

`make help` — полный список. Основные: `up`, `down`, `shell`, `logs s=<сервис>`, `test`, `test-unit`, `lint`, `fix`, `stan`, `settings`, `worker`, `tunnel`. `migrate` и `demo` появятся на этапах 3+.

## Документация

- [docs/progress.md](docs/progress.md) — что сделано
- [docs/devsecops.md](docs/devsecops.md) — CI/CD и безопасность
- [docs/adr/](docs/adr/) — архитектурные решения
- [CLAUDE.md](CLAUDE.md) — правила работы с проектом (для Claude Code и людей)

## Коммиты

[Conventional Commits](https://www.conventionalcommits.org/): `feat(b24): add deal creation`. Scope — по этапам, список в `tools/check-commit-msg.sh`.
