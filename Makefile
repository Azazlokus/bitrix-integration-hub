.DEFAULT_GOAL := help
SHELL := /usr/bin/env bash

DC      := docker compose
PHP     := $(DC) exec php
PHP_RUN := $(DC) run --rm --no-deps -e LAB_TESTS_BITRIX=0 php
COMPOSER_DIR := /app/local
BITRIXSETUP_URL := https://www.1c-bitrix.ru/download/scripts/bitrixsetup.php

.PHONY: help hooks env up down restart build ps shell logs composer install lint fix stan test test-unit \
        settings migrate worker tunnel demo bitrix-download bitrix-cleanup mysql

help: ## Показать список команд
	@grep -hE '^[a-zA-Z_-]+:.*?## ' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-16s\033[0m %s\n", $$1, $$2}'

hooks: ## Подключить git-хуки проекта (conventional commits, php -l, gitleaks)
	git config core.hooksPath .githooks
	chmod +x .githooks/* tools/*.sh .claude/hooks/*.sh
	@echo "git-хуки подключены"

env: ## Создать .env из .env.example (если ещё нет) с UID/GID текущего пользователя
	@if [[ ! -f .env ]]; then \
		sed -e "s/^UID=.*/UID=$$(id -u)/" -e "s/^GID=.*/GID=$$(id -g)/" .env.example > .env.tmp && mv .env.tmp .env && \
		echo ".env создан — замени значения change-me"; \
	fi
	@mkdir -p www/local local/logs

# --- Окружение ---

up: env ## Поднять все сервисы
	$(DC) up -d --build --wait
	@port() { grep -E "^$$1=" .env | cut -d= -f2; }; \
	echo "Сайт: http://127.0.0.1:$$(port HTTP_PORT)  Mailpit: :$$(port MAILPIT_UI_PORT)  RabbitMQ: :$$(port RABBITMQ_UI_PORT)  Meilisearch: :$$(port MEILI_PORT)"

down: ## Остановить сервисы (данные сохраняются)
	$(DC) --profile '*' down

restart: down up ## Перезапустить

build: env ## Пересобрать образы
	$(DC) build --pull

ps: ## Статус сервисов
	$(DC) ps

shell: ## Shell в php-контейнере
	$(PHP) bash

logs: ## Логи (make logs s=php)
	$(DC) logs -f --tail=200 $(s)

mysql: ## Консоль MySQL под пользователем Битрикса
	$(DC) exec mysql sh -c 'MYSQL_PWD="$$MYSQL_PASSWORD" mysql -u"$$MYSQL_USER" "$$MYSQL_DATABASE"'

# --- Код и качество ---

composer: env ## Composer (make composer c="require monolog/monolog")
	$(PHP_RUN) composer --working-dir=$(COMPOSER_DIR) $(c)

install: env ## composer install
	$(PHP_RUN) composer install --working-dir=$(COMPOSER_DIR) --no-interaction

lint: env ## Проверка стиля (PHP-CS-Fixer, dry run)
	$(PHP_RUN) composer --working-dir=$(COMPOSER_DIR) lint

fix: env ## Автоисправление стиля
	$(PHP_RUN) composer --working-dir=$(COMPOSER_DIR) fix

stan: env ## Статический анализ (PHPStan)
	$(PHP_RUN) composer --working-dir=$(COMPOSER_DIR) stan

test: ## Все тесты с бутстрапом Битрикса
	$(PHP) env LAB_TESTS_BITRIX=1 local/vendor/bin/phpunit $(args)

test-unit: env ## Только unit-тесты (без Битрикса и без запущенного окружения)
	$(PHP_RUN) local/vendor/bin/phpunit --testsuite Unit $(args)

# --- Битрикс ---

bitrix-download: env ## Скачать bitrixsetup.php в ./www (дальше — мастер установки в браузере)
	curl -fSL --proto '=https' --proto-redir '=https' --tlsv1.2 -o www/bitrixsetup.php $(BITRIXSETUP_URL)
	@sha256sum www/bitrixsetup.php
	@echo "Открой http://127.0.0.1:$$(grep -E '^HTTP_PORT=' .env | cut -d= -f2)/bitrixsetup.php — после установки удали www/bitrixsetup.php (make bitrix-cleanup)"

bitrix-cleanup: ## Удалить установщик и скрипт восстановления из вебрута
	rm -f www/bitrixsetup.php www/restore.php

settings: ## Сгенерировать bitrix/.settings_extra.php (кеш и сессии в Redis)
	$(PHP) php tools/generate-settings-extra.php

migrate: ## Применить миграции (этап 3: sprint.migration)
	@echo "Миграции появятся на этапе 3" >&2; exit 1

demo: ## Наполнить данными и прогнать сквозной сценарий
	@echo "Демо-сценарий появится по мере этапов (заказ → оплата → доставка → CRM → 1С)" >&2; exit 1

# --- Интеграции ---

worker: ## Запустить консьюмеры очередей (контейнер worker)
	$(DC) --profile worker up -d worker
	$(DC) logs -f worker

tunnel: ## Публичный https-туннель для входящих вебхуков (только allowlist путей, см. docker/nginx/webhooks.conf)
	$(DC) --profile tunnel up -d tunnel
	@echo "Ждём URL туннеля..."; \
	for i in $$(seq 1 30); do \
		url=$$($(DC) logs tunnel 2>&1 | grep -oE 'https://[a-z0-9-]+\.trycloudflare\.com' | tail -1); \
		[[ -n "$$url" ]] && { echo "Туннель: $$url"; exit 0; }; sleep 1; \
	done; echo "URL не появился — make logs s=tunnel" >&2; exit 1
