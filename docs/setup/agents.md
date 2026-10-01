# Agents

## planner

Основной агент для подготовки плана изменений.
Может читать проект и писать только в `docs/plan/`.
Изменение кода и выполнение bash-команд запрещены.

## scout

Субагент-разведчик.
Только ищет по коду и возвращает найденные места с путями и строками.
Редактирование файлов и bash запрещены.

## Результат scout

Пробег читается в ключевых местах:

backend/src/Domain/ApplicationValidator.php:43 — payload['mileage'].
backend/src/Repository/ApplicationRepository.php:45 — нормализованный $input['mileage'].
backend/src/Repository/ApplicationRepository.php:68 — SELECT v.mileage_km из БД.
frontend/app.js:13–14 — чтение поля формы и преобразование в Number.
backend/src/Http/ApplicationController.php:25, 28, 56, 59 — чтение тела HTTP-запросов, содержащего пробег.