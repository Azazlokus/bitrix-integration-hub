#!/usr/bin/env bash
# Настройка безопасности GitHub-репозитория. Идемпотентен — можно запускать повторно.
# Использование: tools/github-setup.sh <owner/repo>
# Требует: gh auth login (scope repo, admin:repo_hook), репозиторий уже создан.
set -euo pipefail

REPO="${1:?usage: $0 <owner/repo>}"
api() { gh api -H "Accept: application/vnd.github+json" -H "X-GitHub-Api-Version: 2022-11-28" "$@"; }

echo "→ Общие настройки: только squash-merge, удаление веток, заголовок squash = заголовок PR"
api -X PATCH "repos/$REPO" \
  -F allow_squash_merge=true -F allow_merge_commit=false -F allow_rebase_merge=false \
  -F delete_branch_on_merge=true -F allow_auto_merge=true \
  -f squash_merge_commit_title=PR_TITLE -f squash_merge_commit_message=PR_BODY \
  -F has_wiki=false >/dev/null

echo "→ Dependabot alerts + security updates"
api -X PUT "repos/$REPO/vulnerability-alerts" >/dev/null
api -X PUT "repos/$REPO/automated-security-fixes" >/dev/null

echo "→ Secret scanning + push protection (бесплатно для публичных репозиториев)"
api -X PATCH "repos/$REPO" --input - >/dev/null <<'JSON' || echo "  ⚠ недоступно (приватный репозиторий без GHAS?)"
{"security_and_analysis": {
  "secret_scanning": {"status": "enabled"},
  "secret_scanning_push_protection": {"status": "enabled"}
}}
JSON

echo "→ Private vulnerability reporting"
api -X PUT "repos/$REPO/private-vulnerability-reporting" >/dev/null || true

echo "→ Actions: токен по умолчанию read-only, release-please может создавать PR"
api -X PUT "repos/$REPO/actions/permissions/workflow" \
  -f default_workflow_permissions=read -F can_approve_pull_request_reviews=true >/dev/null

echo "→ Actions: разрешены только закреплённые по SHA actions"
api -X PUT "repos/$REPO/actions/permissions" -F enabled=true -f allowed_actions=all -F sha_pinning_required=true >/dev/null \
  || echo "  ⚠ sha_pinning_required не поддерживается API — пропущено"

echo "→ Ruleset для main"
ruleset_id=$(api "repos/$REPO/rulesets" --jq '.[] | select(.name=="main-protection") | .id' || true)
method=POST; path="repos/$REPO/rulesets"
if [[ -n "$ruleset_id" ]]; then method=PUT; path="repos/$REPO/rulesets/$ruleset_id"; fi

api -X "$method" "$path" --input - >/dev/null <<'JSON'
{
  "name": "main-protection",
  "target": "branch",
  "enforcement": "active",
  "conditions": {"ref_name": {"include": ["~DEFAULT_BRANCH"], "exclude": []}},
  "bypass_actors": [{"actor_id": 5, "actor_type": "RepositoryRole", "bypass_mode": "pull_request"}],
  "rules": [
    {"type": "deletion"},
    {"type": "non_fast_forward"},
    {"type": "required_linear_history"},
    {"type": "pull_request", "parameters": {
      "required_approving_review_count": 0,
      "dismiss_stale_reviews_on_push": true,
      "require_code_owner_review": false,
      "require_last_push_approval": false,
      "required_review_thread_resolution": true,
      "allowed_merge_methods": ["squash"]
    }},
    {"type": "required_status_checks", "parameters": {
      "strict_required_status_checks_policy": true,
      "required_status_checks": [
        {"context": "PHP (lint, static analysis, unit tests)"},
        {"context": "Workflows & scripts lint"},
        {"context": "Dockerfile lint (hadolint)"},
        {"context": "docker compose config"},
        {"context": "Secrets (gitleaks)"},
        {"context": "Dependencies, IaC & secrets (Trivy)"},
        {"context": "PR title"},
        {"context": "Commit messages"}
      ]
    }}
  ]
}
JSON

echo "✓ Готово. Проверь: https://github.com/$REPO/settings/security_analysis"
