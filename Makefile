.DEFAULT_GOAL := help
SHELL := /usr/bin/env bash

.PHONY: help hooks

help: ## Показать список команд
	@grep -hE '^[a-zA-Z_-]+:.*?## ' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-14s\033[0m %s\n", $$1, $$2}'

hooks: ## Подключить git-хуки проекта (conventional commits, php -l, gitleaks)
	git config core.hooksPath .githooks
	chmod +x .githooks/* tools/*.sh .claude/hooks/*.sh
	@echo "git-хуки подключены"
