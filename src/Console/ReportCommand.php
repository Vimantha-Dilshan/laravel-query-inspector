<?php

declare(strict_types=1);

namespace Inspector\QueryMonitor\Console;

use Illuminate\Console\Command;
use Inspector\QueryMonitor\Contracts\QueryStorageInterface;

class ReportCommand extends Command
{
    protected $signature = 'query-monitor:report
                            {--format=table : Output format: table or json}';

    protected $description = 'Display a database query performance report';

    public function handle(QueryStorageInterface $storage): int
    {
        $stats = $storage->statistics();
        $format = (string) $this->option('format');

        if ($format === 'json') {
            $this->line((string) json_encode($stats, JSON_PRETTY_PRINT));

            return self::SUCCESS;
        }

        $this->renderReport($stats);

        return self::SUCCESS;
    }

    private function renderReport(array $stats): void
    {
        $this->newLine();
        $this->line('<fg=cyan;options=bold> ┌─────────────────────────────────────────────┐</>');
        $this->line('<fg=cyan;options=bold> │   Laravel Query Performance Report          │</>');
        $this->line('<fg=cyan;options=bold> └─────────────────────────────────────────────┘</>');
        $this->newLine();

        $slowCount = (int) ($stats['slow_queries'] ?? 0);
        $slowLabel = $slowCount > 0
            ? "<fg=red;options=bold>{$slowCount}</>"
            : "<fg=green>0</>";

        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Queries', '<fg=white>' . number_format((int) ($stats['total_queries'] ?? 0)) . '</>'],
                ['Queries Today', '<fg=white>' . number_format((int) ($stats['queries_today'] ?? 0)) . '</>'],
                ['Slow Queries', $slowLabel],
                ['Avg Execution Time', '<fg=yellow>' . (string) ($stats['avg_execution_time'] ?? 0) . 'ms</>'],
                ['Max Execution Time', '<fg=red>' . (string) ($stats['max_execution_time'] ?? 0) . 'ms</>'],
            ]
        );

        $breakdown = (array) ($stats['query_type_breakdown'] ?? []);

        if (! empty($breakdown)) {
            $this->newLine();
            $this->components->twoColumnDetail('<fg=gray>Query Type Breakdown</>');

            $rows = array_map(
                fn(string $type, int $count): array => [$type, number_format($count)],
                array_keys($breakdown),
                $breakdown
            );

            $this->table(['Type', 'Count'], $rows);
        }

        $this->newLine();
    }
}
