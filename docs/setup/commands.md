# Проверка команд

## 2.4.1 /review-diff

Вызов:
`/review-diff`

Результат:
Команда запустила ревью текущих изменений перед мержем, использовала правила `code-review`, результат выдала в чат, файлы не изменяла.

Сравнение с ручным прогоном из 2.2:
Результат не хуже ручного вызова той же рутины.

## 2.4.2 Параметр команды

1. `/review-diff backend/src/Repository/ApplicationRepository.php`
   → проведено ревью ApplicationRepository.php.

2. `/review-diff backend/src/Domain/DecisionEngine.php`
   → проведено ревью DecisionEngine.php.

3. `/review-diff`
   → агент запросил файл или ветку и не стал выбирать объект самостоятельно.

Вывод: параметр реально определяет объект код-ревью.