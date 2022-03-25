<?php

declare(strict_types=1);

namespace Test\Drom\Iterable\Dbal;

use function array_shift;
use function func_get_args;

/**
 * @template TItem
 * @template TParameter
 * @template TParameterType
 */
class SpyConnection
{
    private array $queries = [];
    private array $results;

    /**
     * @param array<TItem[]> $results
     */
    public function __construct(array $results)
    {
        $this->results = $results;
    }

    /**
     * @param string $query
     * @param array<string, TParameter> $params
     * @param array<string, TParameterType> $types
     *
     * @return array<TItem>
     */
    public function fetchFirstColumn(string $query, array $params = [], array $types = []): array
    {
        $this->queries[] = func_get_args();

        return array_shift($this->results);
    }

    public function getQueries(): array
    {
        return $this->queries;
    }
}
