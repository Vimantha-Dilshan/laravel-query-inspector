<?php

declare(strict_types=1);

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Inspector\QueryMonitor\Models\QueryMonitorLog;
use Inspector\QueryMonitor\Services\QueryMonitorService;

it('detects a potential N+1 pattern when a query runs repeatedly', function (): void {
    config()->set('query-monitor.n_plus_one_threshold', 5);

    $service = app(QueryMonitorService::class);

    // Simulate loading orders for each of 10 customers (classic N+1)
    for ($i = 1; $i <= 10; $i++) {
        Event::dispatch(new QueryExecuted(
            sql: 'select * from `orders` where `orders`.`customer_id` = ? and `orders`.`customer_id` is not null',
            bindings: [$i],
            time: 2.0,
            connection: DB::connection(),
        ));
    }

    $issues = $service->getPotentialNPlusOneIssues();

    expect($issues)->not->toBeEmpty();
});

it('does not flag a query that runs below the threshold as an N+1', function (): void {
    config()->set('query-monitor.n_plus_one_threshold', 50);

    $service = app(QueryMonitorService::class);

    for ($i = 1; $i <= 5; $i++) {
        Event::dispatch(new QueryExecuted(
            sql: 'select * from `orders` where `orders`.`customer_id` = ?',
            bindings: [$i],
            time: 1.0,
            connection: DB::connection(),
        ));
    }

    $issues = $service->getPotentialNPlusOneIssues();

    expect($issues)->toBeEmpty();
});

it('potentialNPlusOne() storage method returns patterns at or above threshold', function (): void {
    $requestId = 'req_nplusone_test';
    $threshold = 5;

    $base = [
        'sql' => 'select * from `posts` where `user_id` = 1',
        'bindings' => [],
        'execution_time' => 2,
        'connection' => 'testing',
        'query_type' => 'SELECT',
        'route' => 'blog.index',
        'controller' => null,
        'request_url' => 'http://localhost/blog',
        'http_method' => 'GET',
        'user_id' => null,
        'environment' => 'testing',
        'request_id' => $requestId,
        'is_slow' => false,
        'metadata' => null,
    ];

    // Insert $threshold + 2 rows with the same request_id and SQL
    for ($i = 0; $i < $threshold + 2; $i++) {
        QueryMonitorLog::create($base);
    }

    $storage = app(\Inspector\QueryMonitor\Contracts\QueryStorageInterface::class);
    $results = $storage->potentialNPlusOne($threshold);

    expect($results->total())->toBeGreaterThan(0);
});
