<?php

declare(strict_types=1);

namespace Inspector\QueryMonitor\Detectors;

use Inspector\QueryMonitor\Support\QueryData;

class DuplicateQueryDetector
{
    /** @var array<string, array{count: int, query: QueryData}> */
    private array $executedQueries = [];

    /**
     * Register a query for duplicate tracking.
     */
    public function track(QueryData $queryData): void
    {
        $key = $this->buildKey($queryData);

        if (! isset($this->executedQueries[$key])) {
            $this->executedQueries[$key] = [
                'count' => 0,
                'query' => $queryData,
            ];
        }

        $this->executedQueries[$key]['count']++;
    }

    /**
     * Return only entries that have been executed more than once.
     *
     * @return array<string, array{count: int, query: QueryData}>
     */
    public function getDuplicates(): array
    {
        return array_filter(
            $this->executedQueries,
            static fn(array $item): bool => $item['count'] > 1
        );
    }

    public function hasDuplicates(): bool
    {
        return count($this->getDuplicates()) > 0;
    }

    public function reset(): void
    {
        $this->executedQueries = [];
    }

    private function buildKey(QueryData $queryData): string
    {
        return md5($queryData->sql . serialize($queryData->bindings));
    }
}
