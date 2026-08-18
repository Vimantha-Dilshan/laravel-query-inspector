<?php

declare(strict_types=1);

namespace Inspector\QueryMonitor\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Inspector\QueryMonitor\Support\QueryData;

class QueryRecorded
{
    use Dispatchable;

    public function __construct(
        public readonly QueryData $queryData
    ) {}
}
