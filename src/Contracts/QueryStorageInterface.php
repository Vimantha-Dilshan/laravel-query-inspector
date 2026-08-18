<?php

declare(strict_types=1);

namespace Inspector\QueryMonitor\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Inspector\QueryMonitor\Support\QueryData;

interface QueryStorageInterface
{
    /**
     * Persist a captured query record.
     */
    public function store(QueryData $queryData): void;

    /**
     * Delete stored query records.
     *
     * @param  int  $olderThanDays  Delete records older than N days. 0 = delete all.
     * @return int Number of records deleted.
     */
    public function clear(int $olderThanDays = 0): int;

    /**
     * Return aggregate performance statistics.
     */
    public function statistics(): array;

    /**
     * Return a paginated list of all queries, optionally filtered.
     *
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Return a paginated list of slow queries.
     *
     * @param  array<string, mixed>  $filters
     */
    public function slowQueries(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Return queries that appear more than once (duplicates).
     */
    public function duplicates(int $perPage = 15): LengthAwarePaginator;

    /**
     * Return query patterns that appear >= $threshold times in the same request.
     */
    public function potentialNPlusOne(int $threshold = 10, int $perPage = 15): LengthAwarePaginator;

    /**
     * Find a single query record by ID.
     */
    public function find(int $id): ?object;
}
