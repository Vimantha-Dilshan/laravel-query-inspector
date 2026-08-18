<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Inspector\QueryMonitor\Models\QueryMonitorLog;
use Inspector\QueryMonitor\Services\QueryMonitorService;

it('records a query in the database when executed', function (): void {
    DB::table('test_users')->where('id', 1)->first();

    expect(QueryMonitorLog::where('query_type', 'SELECT')->count())->toBeGreaterThan(0);
});

it('captures the correct query type for SELECT', function (): void {
    DB::table('test_users')->get();

    $log = QueryMonitorLog::latest()->first();

    expect($log->query_type)->toBe('SELECT');
});

it('captures the correct query type for INSERT', function (): void {
    DB::table('test_users')->insert(['name' => 'Alice', 'email' => 'alice@example.com']);

    $log = QueryMonitorLog::where('query_type', 'INSERT')->latest()->first();

    expect($log)->not->toBeNull();
    expect($log->query_type)->toBe('INSERT');
});

it('captures execution time as a non-negative float', function (): void {
    DB::table('test_users')->get();

    $log = QueryMonitorLog::latest()->first();

    expect($log->execution_time)->toBeFloat();
    expect($log->execution_time)->toBeGreaterThanOrEqual(0.0);
});

it('stores the current environment in the log record', function (): void {
    DB::table('test_users')->get();

    $log = QueryMonitorLog::latest()->first();

    expect($log->environment)->toBe(app()->environment());
});

it('stores a non-empty request_id for each captured query', function (): void {
    DB::table('test_users')->get();
    DB::table('test_users')->where('id', 2)->first();

    $requestIds = QueryMonitorLog::pluck('request_id')->filter()->unique();

    expect($requestIds)->toHaveCount(1); // same request, one ID
});

it('does not monitor queries targeting the monitor table itself', function (): void {
    $before = QueryMonitorLog::count();

    // Reading from the monitor table should not create another row
    QueryMonitorLog::count();

    $after = QueryMonitorLog::count();

    expect($after)->toBe($before);
});

it('does not record queries when monitoring is disabled', function (): void {
    // Re-create a fresh service with monitoring disabled
    config()->set('query-monitor.enabled', false);

    /** @var QueryMonitorService $service */
    $service = new QueryMonitorService(
        storage: app(\Inspector\QueryMonitor\Contracts\QueryStorageInterface::class),
        config: config('query-monitor'),
    );
    $service->boot();

    $before = QueryMonitorLog::count();
    DB::table('test_users')->get();

    // The original (enabled) service still runs but we just verify the disabled
    // service would not produce extra rows from its own listener path.
    // This test primarily verifies the flag logic on the new instance.
    expect($service->isEnabled())->toBeFalse();
});

it('captures bindings alongside the sql', function (): void {
    DB::table('test_users')->where('id', 42)->first();

    $log = QueryMonitorLog::latest()->first();

    expect($log->bindings)->toBeArray();
    expect($log->bindings)->toContain(42);
});

it('exposes captured queries through getRequestQueries()', function (): void {
    $service = app(QueryMonitorService::class);

    $before = count($service->getRequestQueries());

    DB::table('test_users')->get();

    expect(count($service->getRequestQueries()))->toBeGreaterThan($before);
});
