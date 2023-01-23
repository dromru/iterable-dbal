<?php

declare(strict_types=1);

namespace Test\Drom\IterableUtils\Dbal;

use Closure;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Drom\IterableUtils\Dbal\BatchedMinMaxQueryIterator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Drom\IterableUtils\Dbal\BatchedMinMaxQueryIterator
 * @codeCoverageIgnore
 */
class BatchedMinMaxQueryIteratorTest extends TestCase
{
    /**
     * @test
     */
    public function expectedPackQueries(): void
    {
        $connection = new SpyConnection(
            [
                ['1', '2', '3'],
                ['4', /* '5', '6' */],
                [/* '7', '8', '9' */],
                [/* '10', */ '11', '12'],
                ['13', '14']
            ]
        );

        $iterator = new BatchedMinMaxQueryIterator(
            3,
            Closure::fromCallable([$connection, 'fetchFirstColumn']),
            $query = <<<'SQL'
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
            $types = [
                'minId' => ParameterType::INTEGER,
                'maxId' => ParameterType::INTEGER,
                'field1' => Connection::PARAM_INT_ARRAY,
            ],
        );

        self::assertSame(['1', '2', '3', '4', '11', '12', '13', '14'], [...$iterator]);
        self::assertSame(
            [
                [$query, ['minId' => 1, 'maxId' => 3, 'field1' => [1, 2, 3]], $types],
                [$query, ['minId' => 4, 'maxId' => 6, 'field1' => [1, 2, 3]], $types],
                [$query, ['minId' => 7, 'maxId' => 9, 'field1' => [1, 2, 3]], $types],
                [$query, ['minId' => 10, 'maxId' => 12, 'field1' => [1, 2, 3]], $types],
                [$query, ['minId' => 13, 'maxId' => 15, 'field1' => [1, 2, 3]], $types],
            ],
            $connection->getQueries()
        );
    }
}
