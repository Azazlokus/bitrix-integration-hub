---
paths:
  - "docker/**"
  - "docker-compose*.yml"
  - "compose*.yaml"
  - "**/Dockerfile*"
  - "Makefile"
  - ".github/**"
---

# Инфраструктура и CI

## Docker
- Образы — с конкретной версией (`php:8.3-fpm-bookworm`, `mysql:8.4`), не `latest`. Перед добавлением проверь актуальный тег.
- Контейнеры приложений — не от root, где возможно; healthcheck у каждого сервиса; `depends_on: condition: service_healthy`.
- Порты наружу публикуются только на `127.0.0.1`.
- Секреты — через `.env` (в `.gitignore`), `.env.example` поддерживается в актуальном состоянии при каждом новом параметре.
- Dockerfile: многоэтапная сборка, `--no-install-recommends`, очистка apt-кеша. Проверка — Trivy misconfig (CI); hadolint добавить в CI на этапе 1.

## GitHub Actions
- Каждый `uses:` закреплён полным SHA + комментарий `# vX.Y.Z`. Новую версию узнавать через `gh api repos/<owner>/<repo>/releases/latest`.
- `permissions: {}` на уровне workflow, нужные права — на уровне job.
- `actions/checkout` всегда с `persist-credentials: false`.
- Никаких `${{ github.event.* }}` прямо в `run:` — только через `env:` (защита от script injection).
- Не использовать `pull_request_target` с checkout кода PR.
- После правок: `actionlint` и `zizmor .github/workflows` (если установлены).

## Makefile
- У каждой цели комментарий `## описание` (для `make help`). Команды PHP — через `docker compose exec`.
