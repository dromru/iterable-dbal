# iterable-dbal

[![Latest Stable Version](https://img.shields.io/packagist/v/dromru/iterable-dbal.svg?style=flat-square)](https://packagist.org/packages/dromru/iterable-dbal)
[![Tests](https://github.com/dromru/iterable-dbal/workflows/Tests/badge.svg)](https://github.com/dromru/iterable-dbal/actions)
[![Coverage Status](https://coveralls.io/repos/github/dromru/iterable-dbal/badge.svg?branch=master)](https://coveralls.io/github/dromru/iterable-dbal?branch=master)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.4-8892BF.svg?style=flat-square)](https://php.net/)

## Проблема

Зачастую код получения данных пачками из БД перемешивается с логикой обработки этих данных. Такой код часто дублируется
и усложняет поддержку.

## Решение

`iterable-dbal` - пакет, который предоставляет набор классов для разбиения пакетных sql-запросов на раздельные.
Приспособлен для использования в связке с [doctrine/dbal](https://github.com/doctrine/dbal).

P.S. Реализован с использованием генераторов.

## Примеры использования

Допустим необходимо итерировать по существующим записям в БД с идентификаторами в промежутке от 1 до 1 000 000 000.
Когда весь 1 000 000 000 из БД в память не влезает (жирный запрос), можно
использовать `\Drom\IterableUtils\Dbal\BatchedMinMaxQueryIterator` для того чтобы выполнить N облегчённых запросов - эта
логика скрыта внутри итератора.

```php
/**
 * @var \Doctrine\DBAL\Connection $connection
 */
$iterator = new \Drom\IterableUtils\Dbal\BatchedMinMaxQueryIterator(
    $batchSize = 1000,
    \Closure::fromCallable([$connection, 'fetchFirstColumn']),
    <<<'SQL'
    SELECT id
    FROM `test`.`sample_table`
    WHERE field1 IN (:param1)
    WHERE id BETWEEN :minId AND :maxId
    SQL,
    [
        'minId' => 1,
        'maxId' => 1_000_000_000,
        'field1' => [1, 2, 3],
    ],
    [
        'minId' => \Doctrine\DBAL\ParameterType::INTEGER,
        'maxId' => \Doctrine\DBAL\ParameterType::INTEGER,
        'field1' => \Doctrine\DBAL\Connection::PARAM_INT_ARRAY,
    ]
);

foreach ($iterator as $id) {
    echo PHP_EOL . $id;
}
```

Если требуется итерировать по существующей коллекции идентификаторов, но необходимо получать набор связанных данных, то можно
использовать `\Drom\IterableUtils\Dbal\BatchedIdsQueryIterator` для того чтобы выполнить N облегчённых запросов.

```php
$ids = [1, 2, /* ... */, 1_000_000_000];

/**
 * @var \Doctrine\DBAL\Connection $connection
 */
$iterator = new \Drom\IterableUtils\Dbal\BatchedIdsQueryIterator(
    $batchSize = 1000,
    \Closure::fromCallable([$connection, 'fetchAllAssociative']),
    <<<'SQL'
    SELECT *
    FROM `test`.`sample_table`
    WHERE field1 = :param1
    WHERE id in (:ids)
    SQL,
    [
        'ids' => $ids,
        'param1' => 1,
    ],
    [
        'ids' => \Doctrine\DBAL\Connection::PARAM_INT_ARRAY,
        'param1' => \Doctrine\DBAL\ParameterType::INTEGER,
    ]
);

foreach ($iterator as $item) {
    echo PHP_EOL;
    print_r($item);
    echo PHP_EOL;
}
```
