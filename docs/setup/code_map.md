## Текущий порядок расчёта

Точка оркестрации — `AssessmentService::assess()`:

1. `ApplicationValidator::validate($payload)`
   - нормализует и проверяет VIN, год, пробег, оценочную стоимость, сумму и срок;
   - при ошибках выбрасывает `ValidationException`, дальнейшего расчёта решения **нет**.
2. `LtvCalculator::calculate($requestedAmount, $marketValue)`
   - считает `round(requested_amount / market_value * 100, 2)`.
3. `DecisionEngine::decide($ltv)`
   - возвращает решение только по LTV.
4. `VehicleAge::inYears($year)`
   - рассчитывает возраст для поля ответа `vehicle_age`.
5. `approved_limit` равен запрошенной сумме только при `approve`; при `review` и `reject` — `0`.

### Участвующие файлы

| Файл | Роль |
|---|---|
| `backend/src/Domain/AssessmentService.php` | Вызывает валидацию → расчёт LTV → расчёт решения. |
| `backend/src/Domain/ApplicationValidator.php` | Валидирует и нормализует входные поля, включая `mileage`. |
| `backend/src/Domain/LtvCalculator.php` | Считает LTV. |
| `backend/src/Domain/DecisionEngine.php` | Выбирает `approve` / `review` / `reject` по LTV. |
| `backend/src/Domain/VehicleAge.php` | Считает возраст автомобиля из года выпуска. |
| `backend/src/Domain/VinValidator.php` | Проверяет формат VIN во время валидации. |
| `backend/src/Domain/ValidationException.php` | Исключение при невалидной заявке. |
| `backend/config/rules.php` | Хранит лимиты для валидации и пороги LTV. |

### Фактические пороги решения

В `DecisionEngine::decide()`:

```php
if ($ltv < $approveMax) {
    return self::APPROVE;
}
if ($ltv <= $reviewMax) {
    return self::REVIEW;
}
return self::REJECT;
```

При значениях из `rules.php`:

- LTV `< 60.0` → `approve`;
- LTV `>= 60.0` и `<= 85.0` → `review`;
- LTV `> 85.0` → `reject`.

В комментарии `rules.php` написано, что `LTV <= approve_max` даёт `approve`, но код использует строгое `<`. Поэтому при LTV ровно `60.0` фактический результат — `review`.

`ltv_by_age` в `rules.php` сейчас **не используется** при расчёте решения или лимита: это прямо указано в комментарии конфига и `AssessmentService`.

## Правило «пробег более 400 000 км → review»

Логичное место для правила — `DecisionEngine::decide()`, потому что этот метод централизованно выбирает статус решения.

Сейчас сигнатура:

```php
public function decide(float $ltv): string
```

Для нового правила ей потребуется также пробег, например вторым аргументом. В `AssessmentService::assess()` уже есть нормализованное значение:

```php
$input['mileage']
```

поэтому вызов `decide()` должен будет передавать и его.

Если правило должно безусловно переводить заявку в `review`, проверка должна располагаться в `DecisionEngine::decide()` **до проверок LTV**. Но в формулировке нет приоритета при конфликте правил: например, для LTV `> 85%` и пробега `> 400 000` не сказано, должен итог быть `reject` или `review`. В коде такого правила и приоритета **нет**.

### Что уже есть для реализации

Есть:

- входное поле `payload['mileage']`;
- нормализованное значение `$input['mileage']`;
- проверка допустимого диапазона в `ApplicationValidator`;
- секция `vehicle` в `rules.php`, куда может быть добавлен порог `400000`;
- место вызова решения: `AssessmentService::assess()`.

Не хватает:

- порога `400000` в конфигурации;
- параметра пробега у `DecisionEngine::decide()`;
- логики влияния пробега на решение;
- определённого приоритета правила пробега относительно LTV-отказа.

## Что уже проверяется о пробеге

В `ApplicationValidator::validate()`:

```php
$mileage = (int) ($payload['mileage'] ?? -1);
if ($mileage < 0 || $mileage > $this->rules['vehicle']['max_mileage_km']) {
    $errors['mileage'] = sprintf(
        'Пробег от 0 до %d км',
        $this->rules['vehicle']['max_mileage_km']
    );
}
```

Текущая проверка:

- пробег должен быть от `0` до `500000` км включительно;
- предел `500000` задан как `vehicle.max_mileage_km` в `rules.php`;
- при пробеге свыше `500000` заявка не получает `review`: она не проходит валидацию, выбрасывается `ValidationException`, решение не рассчитывается;
- при пробеге от `0` до `500000` пробег сейчас **не влияет** ни на LTV, ни на `approve` / `review` / `reject`.
