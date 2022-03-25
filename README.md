# iterable-dbal

Классы для разбиения больших sql-запросов коллекций на пачки.

P.S. Реализован с использованием генераторов.

## Примеры использования

```php
/**
 * @var \Doctrine\DBAL\Connection $connection
 */
$iterator = new \Drom\Iterable\Dbal\BatchedMinMaxQueryIterator(
    1000,
    \Closure::fromCallable([$connection, 'fetchFirstColumn']),
    <<<'SQL'
    SELECT id
    FROM `test`.`sample_table`
    WHERE field1 IN (:param1)
    WHERE id BETWEEN :minId AND :maxId
    SQL,
    [
        'minId' => 1,
        'maxId' => 15,
        'field1' => [1, 2, 3],
    ],
    [
        'minId' => \Doctrine\DBAL\ParameterType::INTEGER,
        'maxId' => \Doctrine\DBAL\ParameterType::INTEGER,
        'field1' => \Doctrine\DBAL\Connection::PARAM_INT_ARRAY,
    ]
);

foreach ($iterator as $item) {
    print_r($item);
}
```

```php
/**
 * @var \Doctrine\DBAL\Connection $connection
 */
$iterator = new \Drom\Iterable\Dbal\BatchedIdsQueryIterator(
    1000,
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
    print_r($item);
}
```

## Полезняки

```bash
composer run fix
composer run lint
composer run test

composer run phpstan
composer run phpunit
composer run phpmd
composer run phpcs
composer run php-cs-fixer
composer run phpcbf
```
