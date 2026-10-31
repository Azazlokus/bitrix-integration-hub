# Прогресс

Порядок этапов: 1 → 2 → 3 → 4 → 5 → 6 → 9 → 7 → 8 → 10 → 11 → 12 → 13 → 16 → 17 → 15 → 14.

| # | Этап | Статус | Примечания |
|---|---|---|---|
| 0 | Подготовка репозитория, Claude Code, DevSecOps | ✅ готово | 2026-10-31 |
| 1 | Инфраструктура (docker-compose) | ⏳ следующий | |
| 2 | Каркас модуля и журнал интеграций | — | |
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

**Долги / решить на этапе 1:** версия PHP (8.2 → 8.3/8.4?), Dependabot для composer/docker, hadolint для Dockerfile.
