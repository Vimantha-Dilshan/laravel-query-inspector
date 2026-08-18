<?php

declare(strict_types=1);

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Inspector\QueryMonitor\Models\QueryMonitorLog;

it('marks a query as slow when its execution time meets the threshold', function (): void {
    // Use a threshold of 1ms so any test query is considered slow
    config()->set('query-monitor.slow_query_threshold', 1);

    // Dispatch a QueryExecuted event with a time above the threshold
    Event::dispatch(new QueryExecuted(
        sql: 'select * from `test_users` where `id` = ?',
        bindings: [1],
        time: 500.0, // 500ms — definitely slow
        connection: DB::connection(),
    ));

    $log = QueryMonitorLog::latest()->first();

    expect($log)->not->toBeNull();
    expect($log->is_slow)->toBeTrue();
});

it('does not mark a query as slow when it falls below the threshold', function (): void {
    config()->set('query-monitor.slow_query_threshold', 9999);

    Event::dispatch(new QueryExecuted(
        sql: 'select * from `test_users`',
        bindings: [],
        time: 5.0, // 5ms — far below threshold
        connection: DB::connection(),
    ));

    $log = QueryMonitorLog::latest()->first();

    expect($log)->not->toBeNull();
    expect($log->is_slow)->toBeFalse();
});

it('stores the slow flag correctly in the database', function (): void {
    config()->set('query-monitor.slow_query_threshold', 1);

    Event::dispatch(new QueryExecuted(
        sql: 'select * from `test_users` where `email` = ?',
        bindings: ['test@example.com'],
        time: 1200.0,
        connection: DB::connection(),
    ));

    expect(QueryMonitorLog::where('is_slow', true)->count())->toBeGreaterThan(0);
});
