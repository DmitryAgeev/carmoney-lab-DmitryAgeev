# Проверка code-review skill

## Со скиллом

Запрос:
> Посмотри backend/src/Repository/ApplicationRepository.php перед мержем.

Ответ:
Найдены проблемы

Critical backend/src/Repository/ApplicationRepository.php:92
status подставляется в SQL конкатенацией строк. Запрос GET /api/applications?status=decided'%20OR%201=1%20--%20 обходит фильтр и возвращает все заявки. Нужен prepared statement с параметром :status.
High backend/src/Repository/ApplicationRepository.php:24-58
Сохранение заявки выполняется тремя отдельными запросами без транзакции. Ошибка при вставке автомобиля или решения оставит в БД частично сохранённую заявку, которая будет выглядеть как decided. Нужны beginTransaction(), commit() и rollBack() при исключении.
Medium backend/src/Repository/ApplicationRepository.php:100-107
listApplications() создаёт два дополнительных запроса на каждую заявку: при 50 записях это 101 запрос. Следует получать машину и решение одним запросом через LEFT JOIN, как уже сделано в find().
Нерабочего кода, API-ключей и данных авторизации не обнаружено. Стиль в целом соответствует проекту.

Скилл вызван: да

## Без скилла

Запрос:
> Посмотри backend/src/Repository/ApplicationRepository.php перед мержем.

Ответ:
Найдено перед merge

P1: SQL-инъекция через status
backend/src/Repository/ApplicationRepository.php:92 конкатенирует параметр запроса в SQL. status приходит из GET /api/applications?status=... без валидации (ApplicationController.php:84-87). Значение вроде ' OR 1=1 -- обходит фильтр, а некорректные кавычки дают ошибку БД. Нужен prepared statement с :status и параметром.
P1: частично сохранённая заявка при ошибке
backend/src/Repository/ApplicationRepository.php:24-58 создаёт заявку, автомобиль и решение тремя запросами без транзакции. При ошибке второго или третьего execute() останется applications без связанных данных, хотя статус уже decided. PDO настроен на исключения, поэтому это реальный сценарий. Нужны beginTransaction(), commit() и rollBack().
P2: N+1-запросы в списке заявок
backend/src/Repository/ApplicationRepository.php:97-107 делает до 101 SQL-запроса на один вызов: один для заявок и два на каждую из 50 записей. Для операторского списка это лишняя нагрузка и задержка. Автомобиль и решение следует получить одним запросом через LEFT JOIN, как уже сделано в find().
make test проходит: 34 теста, 42 проверки. Тестов на фильтр status, атомарность save() и производительность списка нет.

## Вывод

Со скиллом агент отдельно проверил ошибки, качество, стиль, нерабочий код и утечки ключей/данных авторизации, а также не менял файлы. Без скилла ответ был свободного формата и не использовал этот чек-лист.