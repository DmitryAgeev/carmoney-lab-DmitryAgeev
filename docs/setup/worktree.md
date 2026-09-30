# Git Worktree

```
C:/Users/dimas/VSCodeProjects/carmoney-lab-DmitryAgeev                                     c82d1af [d1/1.2.1-1.2.3-DmitryAgeev]
C:/Users/dimas/VSCodeProjects/carmoney-lab-DmitryAgeev/.kilo/worktrees/grizzled-eucalyptus c82d1af (detached HEAD)
C:/Users/dimas/VSCodeProjects/carmoney-lab-DmitryAgeev/.kilo/worktrees/pond-penalty        c82d1af [pond-penalty]
```

ApplicationValidatorTest.php — проверяет валидацию заявки: нормализацию VIN, будущий год, минимальную сумму и сбор всех ошибок.

AssessmentServiceTest.php — проверяет итоговую оценку заявки для решений approve/review/reject, LTV, лимита и возраста авто.

DecisionEngineTest.php — проверяет выбор решения по LTV и граничные значения порогов.

LtvCalculatorTest.php — проверяет расчёт LTV и исключения при нулевой стоимости или неположительной сумме.

VinValidatorTest.php — проверяет формат VIN: длину, регистр, запрещённые символы, спецсимволы и пустую строку.

Два агента в одной рабочей директории могут одновременно менять одни и те же файлы,
создавая конфликтующие правки. Отдельные worktree изолируют состояние файлов и git-веток.