#!/usr/bin/env bash
# PreToolUse (Edit|Write|MultiEdit|NotebookEdit): запрещает правку ядра Битрикса и файлов с секретами.
set -euo pipefail

file=$(jq -r '.tool_input.file_path // .tool_input.notebook_path // empty')
[[ -z "$file" ]] && exit 0

rel="${file#"${CLAUDE_PROJECT_DIR:-$PWD}"/}"

case "$rel" in
  bitrix/.settings_extra.php|www/bitrix/.settings_extra.php)
    echo "bitrix/.settings_extra.php генерируется скриптом из репо — правь генератор, а не файл." >&2; exit 2 ;;
  bitrix/*|upload/*|www/bitrix/*|www/upload/*)
    echo "Запрещено: '$rel' — ядро/пользовательские файлы Битрикса. Весь свой код — в local/." >&2; exit 2 ;;
  .env.example|*/.env.example)
    exit 0 ;;
  .env|.env.*|*/.env|*/.env.*|*.pem|*.key|auth.json|*/auth.json)
    echo "Запрещено: '$rel' содержит секреты. Добавь переменную в .env.example и попроси пользователя заполнить .env." >&2; exit 2 ;;
esac
exit 0
