<?php

declare(strict_types=1);

use Inspector\QueryMonitor\Services\QueryAnalyzer;
use Inspector\QueryMonitor\Support\QueryData;

function makeAnalyzerQueryData(float $executionTime, bool $isSlow = false): QueryData
{
    return new QueryData(
        sql: 'select * from `users`',
        bindings: [],
        executionTime: $executionTime,
        connection: 'testing',
        queryType: 'SELECT',
        route: 'home',
        controller: null,
        requestUrl: 'http://localhost',
        httpMethod: 'GET',
        userId: null,
        environment: 'testing',
        requestId: 'req_analyzer',
        isSlow: $isSlow,
    );
}

it('reports the total number of queries', function (): void {
    $queries  = [
        makeAnalyzerQueryData(10.0),
        makeAnalyzerQueryData(20.0),
        makeAnalyzerQueryData(600.0, true),
    ];

    $analyzer = new QueryAnalyzer($queries, [], [], 500.0);

    expect($analyzer->totalQueries())->toBe(3);
});

it('reports the count of slow queries', function (): void {
    $queries = [
        makeAnalyzerQueryData(10.0, false),
        makeAnalyzerQueryData(600.0, true),
        makeAnalyzerQueryData(800.0, true),
    ];

    $analyzer = new QueryAnalyzer($queries, [], [], 500.0);

    expect($analyzer->slowQueryCount())->toBe(2);
});

it('calculates the average execution time correctly', function (): void {
    $queries = [
        makeAnalyzerQueryData(100.0),
        makeAnalyzerQueryData(200.0),
        makeAnalyzerQueryData(300.0),
    ];

    $analyzer = new QueryAnalyzer($queries, [], [], 500.0);

    expect($analyzer->averageExecutionTime())->toBe(200.0);
});

it('returns 0 average when there are no queries', function (): void {
    $analyzer = new QueryAnalyzer([], [], [], 500.0);

    expect($analyzer->averageExecutionTime())->toBe(0.0);
    expect($analyzer->totalQueries())->toBe(0);
});

it('calculates total execution time correctly', function (): void {
    $queries = [
        makeAnalyzerQueryData(50.0),
        makeAnalyzerQueryData(75.5),
        makeAnalyzerQueryData(124.5),
    ];

    $analyzer = new QueryAnalyzer($queries, [], [], 500.0);

    expect($analyzer->totalExecutionTime())->toBe(250.0);
});

it('reports no performance issues when everything is healthy', function (): void {
    $queries  = [makeAnalyzerQueryData(10.0)];
    $analyzer = new QueryAnalyzer($queries, [], [], 500.0);

    expect($analyzer->hasPerformanceIssues())->toBeFalse();
});

it('reports performance issues when there are slow queries', function (): void {
    $queries  = [makeAnalyzerQueryData(600.0, true)];
    $analyzer = new QueryAnalyzer($queries, [], [], 500.0);

    expect($analyzer->hasPerformanceIssues())->toBeTrue();
});

it('includes all fields in the summary', function (): void {
    $queries  = [makeAnalyzerQueryData(100.0)];
    $analyzer = new QueryAnalyzer($queries, [], [], 500.0);
    $summary  = $analyzer->getSummary();

    expect($summary)->toHaveKeys([
        'total_queries',
        'slow_queries',
        'duplicate_queries',
        'n_plus_one_issues',
        'avg_execution_time_ms',
        'total_execution_time_ms',
        'slow_threshold_ms',
        'has_issues',
    ]);
});

it('reports duplicate count from the provided duplicates array', function (): void {
    $duplicates = [
        'key1' => ['count' => 3, 'query' => makeAnalyzerQueryData(5.0)],
        'key2' => ['count' => 5, 'query' => makeAnalyzerQueryData(5.0)],
    ];

    $analyzer = new QueryAnalyzer([], $duplicates, [], 500.0);

    expect($analyzer->duplicateCount())->toBe(2);
});
