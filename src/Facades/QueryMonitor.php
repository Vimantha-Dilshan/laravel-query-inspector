<?php

declare(strict_types=1);

namespace Inspector\QueryMonitor\Facades;

use Illuminate\Support\Facades\Facade;
use Inspector\QueryMonitor\Services\QueryAnalyzer;
use Inspector\QueryMonitor\Services\QueryMonitorService;
use Inspector\QueryMonitor\Support\QueryData;

/**
 * @method static bool isEnabled()
 * @method static static ignoreRoute(string $route)
 * @method static static stopMonitoring()
 * @method static static resumeMonitoring()
 * @method static list<QueryData> getRequestQueries()
 * @method static array<string, array{count: int, query: QueryData}> getDuplicateQueries()
 * @method static array<string, array{count: int, query: QueryData}> getPotentialNPlusOneIssues()
 * @method static QueryAnalyzer analyze()
 *
 * @see QueryMonitorService
 */
class QueryMonitor extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'query-monitor';
    }
}
