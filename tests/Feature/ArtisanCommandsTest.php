<?php

declare(strict_types=1);

use Inspector\QueryMonitor\Models\QueryMonitorLog;

it('install command exits successfully', function (): void {
    $this->artisan('query-monitor:install')
        ->assertExitCode(0);
});

it('clear command removes all records when --force flag is provided', function (): void {
    QueryMonitorLog::create([
        'sql'          => 'select 1',
        'bindings'     => [],
        'execution_time' => 1,
        'connection'   => 'testing',
        'query_type'   => 'SELECT',
        'route'        => null,
        'controller'   => null,
        'request_url'  => 'http://localhost',
        'http_method'  => 'GET',
        'user_id'      => null,
        'environment'  => 'testing',
        'request_id'   => 'req_1',
        'is_slow'      => false,
        'metadata'     => null,
    ]);

    expect(QueryMonitorLog::count())->toBe(1);

    $this->artisan('query-monitor:clear --force')
        ->assertExitCode(0);

    expect(QueryMonitorLog::count())->toBe(0);
});

it('clear command with --days only removes old records', function (): void {
    // Seed one "old" and one "fresh" record
    QueryMonitorLog::create([
        'sql' => 'select 1',
        'bindings' => [],
        'execution_time' => 1,
        'connection' => 'testing',
        'query_type' => 'SELECT',
        'route' => null,
        'controller' => null,
        'request_url' => 'http://localhost',
        'http_method' => 'GET',
        'user_id' => null,
        'environment' => 'testing',
        'request_id' => 'req_old',
        'is_slow' => false,
        'metadata' => null,
        'created_at' => now()->subDays(60),
        'updated_at' => now()->subDays(60),
    ]);

    QueryMonitorLog::create([
        'sql' => 'select 2',
        'bindings' => [],
        'execution_time' => 1,
        'connection' => 'testing',
        'query_type' => 'SELECT',
        'route' => null,
        'controller' => null,
        'request_url' => 'http://localhost',
        'http_method' => 'GET',
        'user_id' => null,
        'environment' => 'testing',
        'request_id' => 'req_new',
        'is_slow' => false,
        'metadata' => null,
    ]);

    $this->artisan('query-monitor:clear --days=30 --force')
        ->assertExitCode(0);

    // Only the recent record should remain
    expect(QueryMonitorLog::count())->toBe(1);
    expect(QueryMonitorLog::first()->request_id)->toBe('req_new');
});

it('report command exits successfully and outputs table format', function (): void {
    $this->artisan('query-monitor:report')
        ->assertExitCode(0);
});

it('report command outputs json when --format=json is passed', function (): void {
    $this->artisan('query-monitor:report --format=json')
        ->assertExitCode(0)
        ->expectsOutputToContain('total_queries');
});
