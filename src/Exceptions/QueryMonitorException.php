<?php

declare(strict_types=1);

namespace Inspector\QueryMonitor\Exceptions;

use RuntimeException;

class QueryMonitorException extends RuntimeException
{
    public static function unsupportedStorageDriver(string $driver): self
    {
        return new self(
            "Unsupported query monitor storage driver: [{$driver}]. " .
                "Currently supported drivers are: database."
        );
    }

    public static function migrationNotRun(): self
    {
        return new self(
            'The query_monitor_logs table does not exist. ' .
                'Please run: php artisan migrate'
        );
    }
}
