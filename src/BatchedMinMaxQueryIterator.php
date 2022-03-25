<?php

declare(strict_types=1);

namespace Drom\Iterable\Dbal;

use InvalidArgumentException;
use IteratorAggregate;
use Traversable;

use function is_int;
use function min;

/**
 * Прозрачно для клиента разбивает запрос $query по интервалу (minId, maxId), на много маленьких с шагом $batchSize:
 * (minId, minId + batchSize - 1), (minId + batchSize, minId + 2*batchSize - 1), ... (minId + N*batchSize, maxId)
 *
 * Может быть использован для упрощения кода обхода результатов "жирных" коллекций, которые выжирают память
 *
 * @template DbalType
 * @template TItem
 * @implements IteratorAggregate<TItem>
 *
 * @phpstan-type Params array{minId: int, maxId: int}&array<string, mixed>
 * @phpstan-type Types array{minId: DbalType, maxId: DbalType}&array<string, DbalType>
 *
 * @see \Test\Drom\Iterable\Dbal\BatchedMinMaxQueryIteratorTest
 */
class BatchedMinMaxQueryIterator implements IteratorAggregate
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
        if (empty($params['minId']) || !is_int($params['minId'])) {
            throw new InvalidArgumentException('invalid minId');
        }

        if (empty($params['maxId']) || !is_int($params['maxId'])) {
            throw new InvalidArgumentException('invalid maxId');
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
        $minId = $this->params['minId'];
        $maxId = $this->params['maxId'];

        do {
            yield from ($this->connectionMethod)(
                $this->query,
                [
                    'minId' => $minId,
                    'maxId' => min(($minId += $this->batchSize) - 1, $maxId),
                ] + $this->params,
                $this->types,
            );
        } while ($minId <= $maxId);
    }
}
