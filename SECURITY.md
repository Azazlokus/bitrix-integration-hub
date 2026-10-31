# Security Policy

Это учебный проект, не предназначенный для продакшена.

## Сообщить об уязвимости

Не создавайте публичный issue. Используйте **GitHub → Security → Report a vulnerability** (private vulnerability reporting).

## Что делается автоматически

См. [docs/devsecops.md](docs/devsecops.md): поиск секретов (gitleaks), SAST (Semgrep), сканирование зависимостей и IaC (Trivy, Dependency Review, `composer audit`), безопасность GitHub Actions (zizmor, SHA-пиннинг), OpenSSF Scorecard, Dependabot.
