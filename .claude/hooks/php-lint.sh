#!/usr/bin/env bash
# PostToolUse (Edit|Write|MultiEdit): синтаксическая проверка PHP и автоформат PHP-CS-Fixer, если он установлен.
set -euo pipefail

file=$(jq -r '.tool_input.file_path // empty')
[[ "$file" == *.php && -f "$file" ]] || exit 0

if command -v php >/dev/null 2>&1; then
  if ! out=$(php -l "$file" 2>&1); then
    echo "$out" >&2
    exit 2
  fi
fi

root="${CLAUDE_PROJECT_DIR:-$PWD}"
fixer="$root/local/vendor/bin/php-cs-fixer"
if [[ -x "$fixer" && -f "$root/.php-cs-fixer.dist.php" ]]; then
  "$fixer" fix --quiet --config="$root/.php-cs-fixer.dist.php" "$file" >/dev/null 2>&1 || true
fi
exit 0
