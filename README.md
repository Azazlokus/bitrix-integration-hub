# Bitrix Integration Lab

Учебный интернет-магазин на 1С-Битрикс (БУС), в котором собраны типовые интеграции коммерческой разработки: 1С (CommerceML), Битрикс24 (вебхуки, OAuth-приложение, встройки, роботы), своя платёжка и доставка, RabbitMQ + Transactional Outbox, внешняя ERP, Meilisearch, фиды, SMS/Telegram, SSO (Keycloak), мост с Laravel.

> 🚧 В разработке. Статус этапов — [docs/progress.md](docs/progress.md). ТЗ — [bitrix-integration-lab-SPEC.md](bitrix-integration-lab-SPEC.md).

## Быстрый старт

Появится на этапе 1 (`make up` + `make demo`). Сейчас:

```bash
make hooks   # git-хуки: Conventional Commits, php -l, gitleaks
```

## Документация

- [docs/progress.md](docs/progress.md) — что сделано
- [docs/devsecops.md](docs/devsecops.md) — CI/CD и безопасность
- [docs/adr/](docs/adr/) — архитектурные решения
- [CLAUDE.md](CLAUDE.md) — правила работы с проектом (для Claude Code и людей)

## Коммиты

[Conventional Commits](https://www.conventionalcommits.org/): `feat(b24): add deal creation`. Scope — по этапам, список в `tools/check-commit-msg.sh`.
