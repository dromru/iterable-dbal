<?php

namespace PHPSTORM_META {
    registerArgumentsSet(
        'dromru_iterable_dbal_batch_size',
        1_000,
        5_000,
        10_000,
        50_000
    );

    registerArgumentsSet(
        'dromru_iterable_dbal_connection_method',
        [$connection, 'fetchFirstColumn'],
        [$connection, 'fetchAllAssociative'],
        [$connection, 'fetchAllAssociativeIndexed'],
        [$connection, 'fetchAllKeyValue'],
        [$connection, 'fetchAllNumeric']
    );

    expectedArguments(
        \Drom\IterableUtils\Dbal\BatchedIdsQueryIterator::__construct(),
        0,
        argumentsSet('dromru_iterable_dbal_batch_size')
    );

    expectedArguments(
        \Drom\IterableUtils\Dbal\BatchedIdsQueryIterator::__construct(),
        1,
        argumentsSet('dromru_iterable_dbal_connection_method')
    );

    expectedArguments(
        \Drom\IterableUtils\Dbal\BatchedIdsQueryIterator::__construct(),
        2,
        <<<'SQL'
        SELECT *
        FROM table_name
        WHERE id IN (:ids)
        SQL
    );

    expectedArguments(
        \Drom\IterableUtils\Dbal\BatchedIdsQueryIterator::__construct(),
        3,
        [
            'ids' => $ids,
        ]
    );

    expectedArguments(
        \Drom\IterableUtils\Dbal\BatchedIdsQueryIterator::__construct(),
        4,
        [
            'ids' => \Doctrine\DBAL\Connection::PARAM_INT_ARRAY,
        ]
    );

    expectedArguments(
        \Drom\IterableUtils\Dbal\BatchedMinMaxQueryIterator::__construct,
        0,
        argumentsSet('dromru_iterable_dbal_batch_size')
    );

    expectedArguments(
        \Drom\IterableUtils\Dbal\BatchedMinMaxQueryIterator::__construct,
        1,
        argumentsSet('dromru_iterable_dbal_connection_method')
    );

    expectedArguments(
        \Drom\IterableUtils\Dbal\BatchedMinMaxQueryIterator::__construct(),
        2,
        <<<'SQL'
        SELECT *
        FROM table_name
        WHERE id BETWEEN :minId AND :maxId
        SQL
    );

    expectedArguments(
        \Drom\IterableUtils\Dbal\BatchedMinMaxQueryIterator::__construct(),
        3,
        [
            'minId' => $minId,
            'maxId' => $maxId,
        ]
    );

    expectedArguments(
        \Drom\IterableUtils\Dbal\BatchedMinMaxQueryIterator::__construct(),
        4,
        [
            'minId' => \Doctrine\DBAL\ParameterType::INTEGER,
            'maxId' => \Doctrine\DBAL\ParameterType::INTEGER,
        ]
    );
}
