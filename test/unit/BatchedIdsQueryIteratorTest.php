<?php

declare(strict_types=1);

namespace Test\Drom\IterableUtils\Dbal;

use Closure;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Drom\IterableUtils\Dbal\BatchedIdsQueryIterator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Drom\IterableUtils\Dbal\BatchedIdsQueryIterator
 * @codeCoverageIgnore
 */
class BatchedIdsQueryIteratorTest extends TestCase
{
    /**
     * @test
     */
    public function expectedBatchedQueries(): void
    {
        $connection = new SpyConnection(
            [
                ['1', '2', '3'],
                ['4', '11', '12'],
                ['13', '14', '15']
            ]
        );

        $iterator = new BatchedIdsQueryIterator(
            3,
            Closure::fromCallable([$connection, 'fetchFirstColumn']),
            $query = <<<'SQL'
            SELECT id
            FROM `test`.`sample_table`
            WHERE field1 = :param1
            WHERE id in (:ids)
            SQL,
            [
                'ids' => $ids = ['1', '2', '3', '4', '11', '12', '13', '14', '15'],
                'param1' => 1,
            ],
            $types = [
                'ids' => Connection::PARAM_INT_ARRAY,
                'param1' => ParameterType::INTEGER,
            ],
        );

        self::assertSame($ids, [...$iterator]);
        self::assertSame(
            [
                [$query, ['ids' => ['1', '2', '3'], 'param1' => 1], $types],
                [$query, ['ids' => ['4', '11', '12'], 'param1' => 1], $types],
                [$query, ['ids' => ['13', '14', '15'], 'param1' => 1], $types],
            ],
            $connection->getQueries()
        );
    }
}
