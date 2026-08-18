<?php

declare(strict_types=1);

namespace Inspector\QueryMonitor\Detectors;

use Inspector\QueryMonitor\Support\QueryData;

class NPlusOneDetector
{
    /** @var array<string, array{count: int, query: QueryData}> */
    private array $queryPatterns = [];

    public function __construct(
        private readonly int $threshold
    ) {}

    /**
     * Register a query for N+1 pattern analysis.
     */
    public function track(QueryData $queryData): void
    {
        $pattern = $this->normalizeQuery($queryData->sql);

        if (! isset($this->queryPatterns[$pattern])) {
            $this->queryPatterns[$pattern] = [
                'count' => 0,
                'query' => $queryData,
            ];
        }

        $this->queryPatterns[$pattern]['count']++;
    }

    /**
     * Return patterns that appear >= threshold times, indicating a likely N+1.
     *
     * @return array<string, array{count: int, query: QueryData}>
     */
    public function getPotentialIssues(): array
    {
        return array_filter(
            $this->queryPatterns,
            fn(array $item): bool => $item['count'] >= $this->threshold
        );
    }

    public function hasIssues(): bool
    {
        return count($this->getPotentialIssues()) > 0;
    }

    public function getThreshold(): int
    {
        return $this->threshold;
    }

    public function reset(): void
    {
        $this->queryPatterns = [];
    }

    /**
     * Normalize SQL by stripping literal values, leaving only the structural
     * shape. Two queries with different IDs but the same shape become the same
     * pattern, which lets us count repeated relationship loads.
     */
    private function normalizeQuery(string $sql): string
    {
        // Replace numeric literals
        $normalized = preg_replace('/\b\d+\b/', '?', $sql) ?? $sql;
        // Replace single-quoted string literals
        $normalized = preg_replace("/'[^']*'/", '?', $normalized) ?? $normalized;
        // Collapse consecutive whitespace
        $normalized = preg_replace('/\s+/', ' ', trim($normalized)) ?? $normalized;

        return strtolower($normalized);
    }
}
