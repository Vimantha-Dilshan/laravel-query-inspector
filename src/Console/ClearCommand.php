<?php

declare(strict_types=1);

namespace Inspector\QueryMonitor\Console;

use Illuminate\Console\Command;
use Inspector\QueryMonitor\Contracts\QueryStorageInterface;

class ClearCommand extends Command
{
    protected $signature = 'query-monitor:clear
                            {--days= : Delete records older than N days (overrides config)}
                            {--force : Skip the confirmation prompt}';

    protected $description = 'Remove query monitor logs from storage';

    public function handle(QueryStorageInterface $storage): int
    {
        $days = $this->option('days') !== null
            ? (int) $this->option('days')
            : (int) config('query-monitor.retention_days', 0);

        $confirmMessage = $days > 0
            ? "This will permanently delete query monitor records older than {$days} day(s)."
            : 'This will permanently delete ALL query monitor records.';

        if (! $this->option('force') && ! $this->components->confirm($confirmMessage)) {
            $this->components->info('Operation cancelled.');

            return self::SUCCESS;
        }

        $this->components->task('Clearing query monitor logs', function () use ($storage, $days, &$deleted): void {
            $deleted = $storage->clear($days);
        });

        $this->newLine();
        $this->components->info("Deleted {$deleted} record(s) successfully.");

        return self::SUCCESS;
    }
}
