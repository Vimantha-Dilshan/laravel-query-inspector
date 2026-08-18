<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Inspector\QueryMonitor\Models\QueryMonitorLog;

it('shows the dashboard when enabled', function (): void {
    $this->withoutMiddleware()
        ->get(route('query-monitor.dashboard'))
        ->assertOk()
        ->assertViewIs('query-monitor::dashboard');
});

it('shows the all-queries page', function (): void {
    $this->withoutMiddleware()
        ->get(route('query-monitor.queries.index'))
        ->assertOk()
        ->assertViewIs('query-monitor::queries.index');
});

it('shows the slow queries page', function (): void {
    $this->withoutMiddleware()
        ->get(route('query-monitor.slow-queries.index'))
        ->assertOk()
        ->assertViewIs('query-monitor::slow-queries.index');
});

it('shows the duplicate queries page', function (): void {
    $this->withoutMiddleware()
        ->get(route('query-monitor.duplicate-queries.index'))
        ->assertOk()
        ->assertViewIs('query-monitor::duplicate-queries.index');
});

it('shows the n-plus-one page', function (): void {
    $this->withoutMiddleware()
        ->get(route('query-monitor.n-plus-one.index'))
        ->assertOk()
        ->assertViewIs('query-monitor::n-plus-one.index');
});

it('shows a query detail page for an existing record', function (): void {
    $log = QueryMonitorLog::create([
        'sql'          => 'select * from `test_users`',
        'bindings'     => [],
        'execution_time' => 12.5,
        'connection'   => 'testing',
        'query_type'   => 'SELECT',
        'route'        => 'home',
        'controller'   => null,
        'request_url'  => 'http://localhost',
        'http_method'  => 'GET',
        'user_id'      => null,
        'environment'  => 'testing',
        'request_id'   => 'req_test',
        'is_slow'      => false,
        'metadata'     => null,
    ]);

    $this->withoutMiddleware()
        ->get(route('query-monitor.queries.show', $log->id))
        ->assertOk()
        ->assertViewIs('query-monitor::queries.show');
});

it('returns 404 for a non-existent query detail page', function (): void {
    $this->withoutMiddleware()
        ->get(route('query-monitor.queries.show', 99999))
        ->assertNotFound();
});

it('returns 404 for all routes when dashboard is disabled', function (): void {
    config()->set('query-monitor.dashboard.enabled', false);

    // When the dashboard is disabled the routes are not registered;
    // the request hits no matching route and returns 404.
    $this->withoutMiddleware()
        ->get('/query-monitor')
        ->assertNotFound();
});
