<?php

declare(strict_types=1);

namespace Drom\Iterable\Dbal;

use InvalidArgumentException;
use IteratorAggregate;
use Traversable;

use function array_chunk;
use function is_array;

/**
 * Прозрачно для клиента разбивает запрос $query по идентификаторам (ids), на много маленьких с шагом $batchSize:
 * (ids1), (ids2), ... , (idsN)
 *
 * Может быть использован для упрощения кода обхода результатов "жирных" коллекций, которые выжирают память
 *
 * @template DbalType
 * @template TItem
 * @implements IteratorAggregate<TItem>
 *
 * @phpstan-type Params array{ids: array<int>|array<string>}&array<string, mixed>
 * @phpstan-type Types array{ids: DbalType}&array<string, DbalType>
 *
 * @see \Test\Drom\Iterable\Dbal\BatchedIdsQueryIteratorTest
 */
class BatchedIdsQueryIterator implements IteratorAggregate
{
    private int $batchSize;
    /**
     * @var callable(string, Params, Types): iterable<TItem>
     */
    private $connectionMethod;
    private string $query;
    /**
     * @var Params
     */
    private array $params;
    /**
     * @var Types
     */
    private array $types;

    /**
     * @param callable(string, Params, Types): iterable<TItem> $connectionMethod Connection method
     * @param Params $params Query parameters
     * @param Types $types Parameter types
     */
    public function __construct(
        int $batchSize,
        callable $connectionMethod,
        string $query,
        array $params,
        array $types
    ) {
        if (empty($params['ids']) || !is_array($params['ids'])) {
            throw new InvalidArgumentException('invalid ids');
        }

        $this->batchSize = $batchSize;
        $this->connectionMethod = $connectionMethod;
        $this->query = $query;
        $this->params = $params;
        $this->types = $types;
    }

    /**
     * @return Traversable<TItem>
     */
    public function getIterator(): Traversable
    {
        foreach (array_chunk($this->params['ids'], $this->batchSize) as $chunk) {
            yield from ($this->connectionMethod)(
                $this->query,
                ['ids' => $chunk] + $this->params,
                $this->types,
            );
        }
    }
}
