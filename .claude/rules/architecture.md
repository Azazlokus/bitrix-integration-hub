---
paths:
  - "local/modules/lab.integration/**"
  - "local/php_interface/**"
  - "local/routes/**"
---

# Архитектура модуля lab.integration

## Слои (зависимости только сверху вниз: Presentation → Application → Domain ← Infrastructure)

| Слой | Что лежит | Чего нельзя |
|---|---|---|
| `Domain` | сущности, value objects, порты-интерфейсы (`OrderExporter`, `PaymentGateway`, `DeliveryCalculator`, `StockProvider`), доменные исключения | любые `Bitrix\*`, HTTP, БД, `Option`, глобальные функции |
| `Application` | use cases (`SendOrderToCrm`, `ApplyPaymentStatus`, `SyncStocks`), DTO команд/результатов | прямые вызовы внешних систем, ORM Битрикса — только через порты |
| `Infrastructure` | адаптеры: ORM/инфоблоки, клиенты Б24/1С/ERP/платёжки/доставки, RabbitMQ, HTTP-клиент, журнал | бизнес-решения |
| `Presentation` | обработчики событий, D7-контроллеры, агенты, CLI | любая логика кроме: распарсить вход → вызвать use case → отдать ответ |

## Интеграции

- Каждая внешняя система = порт в `Domain` + адаптер в `Infrastructure` + mock-адаптер. Выбор реализации — через `.settings.php` → `services` (`Bitrix\Main\DI\ServiceLocator`), не через `if` в коде.
- Асинхронная схема по умолчанию: событие → outbox (в той же транзакции) → релей → RabbitMQ → консьюмер → адаптер → журнал.
- Синхронно — только расчёт доставки и создание платежа: явный таймаут + фолбэк.
- Каждый исходящий вызов: таймаут, ретраи с экспоненциальным backoff + jitter, ключ идемпотентности, запись в `lab_integration_log`.
- Входящие вебхуки: проверка подписи/токена (constant-time сравнение `hash_equals`), идемпотентность по внешнему ID события, ответ быстро, обработка — через очередь.
- Логи: Monolog, отдельный канал на интеграцию, в `local/logs/`. Маскировать токены, телефоны, email в payload.

## Битрикс

- D7 в приоритете. Старое API (`CIBlockElement`, `CSaleOrder`, ...) — только с комментарием `// Legacy API: <почему нет D7-аналога>`.
- Никаких `GetList` в цикле — батчи, `fetchAll`/`fetchCollection`, `whereIn`.
- Регистрация событий/агентов — в `install/index.php` модуля, удаление — симметрично в `DoUninstall`.
- Для каждого значимого решения — ADR в `docs/adr/`.
