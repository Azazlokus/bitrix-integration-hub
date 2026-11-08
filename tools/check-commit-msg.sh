#!/usr/bin/env bash
# Проверка заголовка коммита на Conventional Commits.
# Использование: tools/check-commit-msg.sh <файл-с-сообщением>   (git commit-msg hook)
#                tools/check-commit-msg.sh --subject "<заголовок>" (CI)
set -euo pipefail

TYPES='feat|fix|refactor|perf|test|docs|build|ci|chore|revert|style'
SCOPES='infra|module|core|contracts|catalog|1c|queue|webhooks|sync|api|b24|payment|delivery|erp|batch|search|feeds|notify|sso|laravel|observability|tests|ci|docs|deps|adr|claude|security|release'
MAX_LEN=72

if [[ "${1:-}" == "--subject" ]]; then
  subject="${2:-}"
else
  subject=$(grep -v '^#' "${1:?message file required}" | sed '/^[[:space:]]*$/d' | head -n1)
fi

# Автоматические коммиты git пропускаем
if [[ "$subject" =~ ^(Merge|Revert\ \"|fixup!|squash!|amend!) ]]; then
  exit 0
fi

pattern="^(${TYPES})(\((${SCOPES})(,(${SCOPES}))*\))?!?: [^ ].*[^.]$"

fail() {
  cat >&2 <<EOF
✗ Коммит не соответствует Conventional Commits: $1
  Заголовок: "$subject"

  Формат: type(scope): description
    type:  ${TYPES//|/, }
    scope: ${SCOPES//|/, } (необязателен, можно несколько через запятую)
    ! после scope — ломающее изменение
  Пример:  feat(b24): add deal creation with product rows
EOF
  exit 1
}

[[ -n "$subject" ]] || fail "пустой заголовок"
(( ${#subject} <= MAX_LEN )) || fail "длина ${#subject} > ${MAX_LEN}"
[[ "$subject" =~ $pattern ]] || fail "неверный type/scope или формат (без точки в конце, пробел после ':')"
exit 0
