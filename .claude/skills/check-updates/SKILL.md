---
name: check-updates
description: Ревизия актуальности стека проекта — composer/npm-зависимости, Docker-образы, GitHub Actions, версия PHP и БУС, инструменты качества. Использовать периодически, перед началом этапа или когда пользователь спрашивает «что устарело/что можно улучшить».
---

# Ревизия актуальности стека

Собери таблицу «текущее → последнее стабильное → что изменилось → рекомендация». Ничего не обновляй без согласия пользователя.

1. **Composer**: `composer outdated --direct --format=json` и `composer audit` (в контейнере). Мажорные обновления — читать UPGRADE/CHANGELOG.
2. **GitHub Actions**: для каждого `uses:` в `.github/workflows/*.yml` — `gh api repos/<owner>/<repo>/releases/latest --jq .tag_name`, затем SHA: `gh api repos/<owner>/<repo>/commits/<tag> --jq .sha`. Проверь, не архивирован ли репозиторий и нет ли security-advisory.
3. **Docker-образы**: теги в `docker-compose.yml`/Dockerfile vs Docker Hub (`curl -s "https://hub.docker.com/v2/repositories/library/<image>/tags?page_size=50"`). EOL-статус: https://endoflife.date/api/<product>.json (php, mysql, redis, rabbitmq, nginx, node, laravel).
4. **PHP**: https://www.php.net/releases/active.php + совместимость с текущей версией БУС.
5. **Инструменты**: PHPStan, PHP-CS-Fixer, PHPUnit, Rector, Semgrep, Trivy, gitleaks — новые правила/уровни, которые стоит включить.
6. **Подходы**: не появилось ли в D7/Б24 REST нового API, заменяющего наш обходной путь (например, новые методы `catalog.*`, изменения в OAuth Б24).
7. Итог — таблица + приоритизированный список «обновить сейчас / запланировать / не трогать» с причинами. Предложи коммиты вида `chore(deps): bump ...` / `ci(deps): bump ...`.
