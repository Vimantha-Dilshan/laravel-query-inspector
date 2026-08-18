<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Inspector\QueryMonitor\Models\QueryMonitorLog;
use Inspector\QueryMonitor\Services\QueryMonitorService;

it('detects duplicate queries within the same request', function (): void {
    $service = app(QueryMonitorService::class);

    // Execute the same query twice
    DB::table('test_users')->where('id', 1)->first();
    DB::table('test_users')->where('id', 1)->first();

    $duplicates = $service->getDuplicateQueries();

    expect($duplicates)->not->toBeEmpty();
});

it('counts duplicate occurrences correctly', function (): void {
    $service = app(QueryMonitorService::class);

    $before = count($service->getDuplicateQueries());

    DB::table('test_users')->where('id', 99)->first();
    DB::table('test_users')->where('id', 99)->first();
    DB::table('test_users')->where('id', 99)->first();

    $duplicates = $service->getDuplicateQueries();

    // At least one group should have count >= 3
    $hasTriplet = collect($duplicates)->contains(
        fn(array $item): bool => $item['count'] >= 3
    );

    expect($hasTriplet)->toBeTrue();
});

it('does not flag unique queries as duplicates', function (): void {
    $service = app(QueryMonitorService::class);

    // Each query uses a unique binding
    DB::table('test_users')->where('id', 111)->first();
    DB::table('test_users')->where('id', 222)->first();
    DB::table('test_users')->where('id', 333)->first();

    $duplicates = $service->getDuplicateQueries();

    // None of the above should be duplicates of each other
    foreach ($duplicates as $item) {
        expect($item['count'])->toBeLessThan(2);
    }
});

it('database storage returns duplicate groups correctly', function (): void {
    $storage = app(\Inspector\QueryMonitor\Contracts\QueryStorageInterface::class);

    // Seed two identical query logs manually
    $shared = [
        'sql' => 'select * from `test_users` where `id` = 1',
        'bindings' => [],
        'execution_time' => 10,
        'connection' => 'testing',
        'query_type' => 'SELECT',
        'route' => 'home',
        'controller' => null,
        'request_url' => 'http://localhost',
        'http_method' => 'GET',
        'user_id' => null,
        'environment' => 'testing',
        'request_id' => 'req_abc123',
        'is_slow' => false,
        'metadata' => null,
    ];

    QueryMonitorLog::create($shared);
    QueryMonitorLog::create($shared);

    $duplicates = $storage->duplicates();

    expect($duplicates->total())->toBeGreaterThan(0);
});
