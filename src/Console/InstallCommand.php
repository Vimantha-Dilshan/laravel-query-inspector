<?php

declare(strict_types=1);

namespace Inspector\QueryMonitor\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'query-monitor:install
                            {--force : Overwrite any existing published files}
                            {--migrate : Run database migrations after publishing}';

    protected $description = 'Install the Laravel Query Performance Monitor package';

    public function handle(): int
    {
        $this->components->info('Installing Laravel Query Performance Monitor...');
        $this->newLine();

        $this->publishConfiguration();
        $this->publishMigrations();

        if ($this->option('migrate')) {
            $this->newLine();
            $this->runMigrations();
        } else {
            $this->newLine();
            $this->components->twoColumnDetail(
                'Next step',
                'Run <fg=yellow;options=bold>php artisan migrate</> to create the monitoring tables'
            );
        }

        $this->newLine();
        $this->components->info('Laravel Query Performance Monitor installed successfully!');

        return self::SUCCESS;
    }

    private function publishConfiguration(): void
    {
        $this->components->task('Publishing configuration', function (): void {
            $this->callSilently('vendor:publish', [
                '--tag' => 'query-monitor-config',
                '--force' => $this->option('force'),
            ]);
        });
    }

    private function publishMigrations(): void
    {
        $this->components->task('Publishing migrations', function (): void {
            $this->callSilently('vendor:publish', [
                '--tag' => 'query-monitor-migrations',
                '--force' => $this->option('force'),
            ]);
        });
    }

    private function runMigrations(): void
    {
        $this->components->task('Running migrations', function (): void {
            $this->callSilently('migrate');
        });
    }
}
