<?php

declare(strict_types=1);

namespace Inspector\QueryMonitor\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Inspector\QueryMonitor\QueryMonitorServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [QueryMonitorServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        // Package defaults for tests
        config()->set('query-monitor.enabled', true);
        config()->set('query-monitor.storage', 'database');
        config()->set('query-monitor.slow_query_threshold', 500);
        config()->set('query-monitor.n_plus_one_threshold', 10);
        config()->set('query-monitor.dashboard.enabled', true);
        config()->set('query-monitor.monitor_console_commands', true);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Lightweight helper table used across feature tests
        Schema::create('test_users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamps();
        });
    }
}
