# DevSecOps

## Слои защиты

| Где | Что | Инструмент |
|---|---|---|
| Claude Code | запрет правки `bitrix/`, `upload/`, `.env`; запрет чтения секретов; `php -l` после правки | `.claude/settings.json`, `.claude/hooks/` |
| Локально (git) | Conventional Commits, запрет коммита ядра/секретов, `php -l`, gitleaks | `.githooks/` (`make hooks`) |
| PR | заголовок и все коммиты — Conventional; dependency review (CVE + лицензии) | `conventional-commits.yml`, `security.yml` |
| CI | composer validate/audit, PHP-CS-Fixer, PHPStan, PHPUnit; actionlint, zizmor, shellcheck | `ci.yml` |
| Security | секреты (gitleaks, полная история), SAST (Semgrep: php, secrets, dockerfile, actions), уязвимости/IaC/секреты (Trivy) → вкладка Security (SARIF); еженедельный пересмотр | `security.yml` |
| Supply chain | все actions по SHA, `permissions: {}` + минимальные права на job, `persist-credentials: false`, harden-runner, Dependabot с cooldown 7 дней, OpenSSF Scorecard | `dependabot.yml`, `scorecard.yml` |
| Релизы | CHANGELOG и semver-теги из Conventional Commits | `release-please.yml` |
| Репозиторий | ruleset на `main` (PR + зелёные проверки + линейная история + только squash), secret scanning + push protection, private vulnerability reporting | `tools/github-setup.sh` |

> CodeQL не используется: PHP он не поддерживает. Для PHP — Semgrep + PHPStan (с этапа 1; позже можно добавить taint-анализ Psalm).

## Публикация на GitHub (когда будем готовы)

```bash
gh repo create <owner>/bitrix-integration-hub --public --source=. --remote=origin   # или --private
git push -u origin main
tools/github-setup.sh <owner>/bitrix-integration-hub
```

Публичный репозиторий — бесплатно получает secret scanning, push protection, загрузку SARIF в Security и публикацию Scorecard. В приватном без GitHub Advanced Security шаги `upload-sarif` упадут — тогда их нужно отключить (результаты останутся в логах).

## Обновление actions

Dependabot предлагает PR раз в неделю (`ci(deps): ...`). Вручную:

```bash
tag=$(gh api repos/<owner>/<repo>/releases/latest --jq .tag_name)
gh api repos/<owner>/<repo>/commits/$tag --jq .sha
```

Или скилл Claude Code `/check-updates`.
