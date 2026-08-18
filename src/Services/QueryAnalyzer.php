<?php

declare(strict_types=1);

namespace Inspector\QueryMonitor\Services;

use Inspector\QueryMonitor\Support\QueryData;

class QueryAnalyzer
{
    /**
     * @param  list<QueryData>  $queries
     * @param  array<string, array{count: int, query: QueryData}>  $duplicates
     * @param  array<string, array{count: int, query: QueryData}>  $nPlusOneIssues
     */
    public function __construct(
        private readonly array $queries,
        private readonly array $duplicates,
        private readonly array $nPlusOneIssues,
        private readonly float $slowThreshold,
    ) {}

    public function totalQueries(): int
    {
        return count($this->queries);
    }

    /** @return list<QueryData> */
    public function slowQueries(): array
    {
        return array_values(array_filter(
            $this->queries,
            fn(QueryData $query): bool => $query->isSlow
        ));
    }

    public function slowQueryCount(): int
    {
        return count($this->slowQueries());
    }

    public function duplicateCount(): int
    {
        return count($this->duplicates);
    }

    public function nPlusOneCount(): int
    {
        return count($this->nPlusOneIssues);
    }

    public function averageExecutionTime(): float
    {
        if (empty($this->queries)) {
            return 0.0;
        }

        $total = array_sum(
            array_map(fn(QueryData $query): float => $query->executionTime, $this->queries)
        );

        return round($total / count($this->queries), 2);
    }

    public function totalExecutionTime(): float
    {
        return round(
            array_sum(array_map(fn(QueryData $query): float => $query->executionTime, $this->queries)),
            2
        );
    }

    public function hasPerformanceIssues(): bool
    {
        return $this->slowQueryCount() > 0
            || $this->duplicateCount() > 0
            || $this->nPlusOneCount() > 0;
    }

    public function getSummary(): array
    {
        return [
            'total_queries' => $this->totalQueries(),
            'slow_queries' => $this->slowQueryCount(),
            'duplicate_queries' => $this->duplicateCount(),
            'n_plus_one_issues' => $this->nPlusOneCount(),
            'avg_execution_time_ms' => $this->averageExecutionTime(),
            'total_execution_time_ms' => $this->totalExecutionTime(),
            'slow_threshold_ms' => $this->slowThreshold,
            'has_issues' => $this->hasPerformanceIssues(),
        ];
    }

    /** @return array<string, array{count: int, query: QueryData}> */
    public function getDuplicates(): array
    {
        return $this->duplicates;
    }

    /** @return array<string, array{count: int, query: QueryData}> */
    public function getNPlusOneIssues(): array
    {
        return $this->nPlusOneIssues;
    }
}
