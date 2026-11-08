---
paths:
  - "apps/laravel-core/**"
  - "contracts/**"
---

# Laravel Core

- Laravel 13, PHP 8.4, `declare(strict_types=1)`, Pint (preset laravel), Larastan ≥ 6, Pest.
- Laravel Core — **основной бэкенд** «ПрофСпорта» (PIM, цены, остатки, OMS, B2B), а не мок. Код — как в коммерческом проекте: валидация через FormRequest, политики, транзакции, события домена, state machine статусов заказа.
- Интеграция с Битриксом — только через явные контракты из `contracts/` (OpenAPI, JSON-схемы сообщений) с версией. Формат сообщений очереди — нейтральный JSON, не сериализованные Laravel Jobs: консьюмер на стороне Битрикса не должен знать про Laravel.
- Исходящие вызовы в Битрикс: HTTP Client с `timeout`, `retry` с backoff, ключ идемпотентности; входящие вебхуки и API — подпись или OAuth2 (Passport, client credentials).
- Все команды — внутри контейнера `core` (`make core c="artisan ..."`, цель появится на этапе 3), не на хосте.
- При описании интеграции объясняй через аналогии с Laravel, но не переносить идиомы Laravel в код Битрикса (там — D7, см. `architecture.md`).
