<?php

declare(strict_types=1);

namespace Inspector\QueryMonitor;

use Illuminate\Support\ServiceProvider;
use Inspector\QueryMonitor\Console\ClearCommand;
use Inspector\QueryMonitor\Console\InstallCommand;
use Inspector\QueryMonitor\Console\ReportCommand;
use Inspector\QueryMonitor\Contracts\QueryStorageInterface;
use Inspector\QueryMonitor\Exceptions\QueryMonitorException;
use Inspector\QueryMonitor\Services\QueryMonitorService;
use Inspector\QueryMonitor\Storage\DatabaseStorage;

class QueryMonitorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/query-monitor.php',
            'query-monitor'
        );

        $this->app->bind(QueryStorageInterface::class, function ($app) {
            $driver = $app['config']->get('query-monitor.storage', 'database');

            return match ($driver) {
                'database' => $app->make(DatabaseStorage::class),
                default => throw QueryMonitorException::unsupportedStorageDriver($driver),
            };
        });

        $this->app->singleton(QueryMonitorService::class, function ($app) {
            return new QueryMonitorService(
                storage: $app->make(QueryStorageInterface::class),
                config: (array) $app['config']->get('query-monitor', []),
            );
        });

        $this->app->alias(QueryMonitorService::class, 'query-monitor');
    }

    public function boot(): void
    {
        $this->registerPublishing();
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'query-monitor');
        $this->registerRoutes();
        $this->registerCommands();
        $this->bootMonitoring();
    }

    private function registerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../config/query-monitor.php' => config_path('query-monitor.php'),
        ], ['query-monitor', 'query-monitor-config']);

        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], ['query-monitor', 'query-monitor-migrations']);

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/query-monitor'),
        ], ['query-monitor', 'query-monitor-views']);
    }

    private function registerRoutes(): void
    {
        if (! config('query-monitor.dashboard.enabled', true)) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
    }

    private function registerCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            InstallCommand::class,
            ClearCommand::class,
            ReportCommand::class,
        ]);
    }

    private function bootMonitoring(): void
    {
        if (! config('query-monitor.enabled', true)) {
            return;
        }

        if ($this->app->runningInConsole() && ! config('query-monitor.monitor_console_commands', false)) {
            return;
        }

        /** @var QueryMonitorService $service */
        $service = $this->app->make(QueryMonitorService::class);
        $service->boot();
    }
}
