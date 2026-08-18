<?php

declare(strict_types=1);

namespace Inspector\QueryMonitor\Storage;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Inspector\QueryMonitor\Contracts\QueryStorageInterface;
use Inspector\QueryMonitor\Models\QueryMonitorLog;
use Inspector\QueryMonitor\Support\QueryData;

class DatabaseStorage implements QueryStorageInterface
{
    public function store(QueryData $queryData): void
    {
        try {
            QueryMonitorLog::create($queryData->toArray());
        } catch (\Throwable) {
            // Silently fail if the migration has not been run yet.
            // This prevents crashing the application during early setup.
        }
    }

    public function clear(int $olderThanDays = 0): int
    {
        $query = QueryMonitorLog::query();

        if ($olderThanDays > 0) {
            $query->where('created_at', '<', now()->subDays($olderThanDays));
        }

        $count = $query->count();
        $query->delete();

        return $count;
    }

    public function statistics(): array
    {
        return [
            'total_queries' => QueryMonitorLog::count(),
            'slow_queries' => QueryMonitorLog::where('is_slow', true)->count(),
            'avg_execution_time' => round((float) (QueryMonitorLog::avg('execution_time') ?? 0.0), 2),
            'max_execution_time' => round((float) (QueryMonitorLog::max('execution_time') ?? 0.0), 2),
            'queries_today' => QueryMonitorLog::whereDate('created_at', today())->count(),
            'query_type_breakdown' => QueryMonitorLog::selectRaw('query_type, COUNT(*) as total')
                ->groupBy('query_type')
                ->orderByDesc('total')
                ->pluck('total', 'query_type')
                ->toArray(),
        ];
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = QueryMonitorLog::query()->latest();

        if (! empty($filters['route'])) {
            $query->where('route', $filters['route']);
        }

        if (! empty($filters['type'])) {
            $query->where('query_type', strtoupper($filters['type']));
        }

        if (! empty($filters['user_id'])) {
            $query->where('user_id', (int) $filters['user_id']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function slowQueries(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = QueryMonitorLog::query()
            ->where('is_slow', true)
            ->orderByDesc('execution_time');

        if (! empty($filters['route'])) {
            $query->where('route', $filters['route']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function duplicates(int $perPage = 15): LengthAwarePaginator
    {
        return QueryMonitorLog::selectRaw(
            'sql, COUNT(*) as occurrence_count, MAX(execution_time) as max_execution_time, ' .
                'route, http_method, MAX(created_at) as last_seen'
        )
            ->groupBy('sql', 'route', 'http_method')
            ->havingRaw('COUNT(*) > 1')
            ->orderByDesc('occurrence_count')
            ->paginate($perPage);
    }

    public function potentialNPlusOne(int $threshold = 10, int $perPage = 15): LengthAwarePaginator
    {
        return QueryMonitorLog::selectRaw(
            'request_id, sql, COUNT(*) as occurrence_count, ' .
                'route, http_method, MIN(created_at) as created_at'
        )
            ->whereNotNull('request_id')
            ->groupBy('request_id', 'sql', 'route', 'http_method')
            ->havingRaw('COUNT(*) >= ?', [$threshold])
            ->orderByDesc('occurrence_count')
            ->paginate($perPage);
    }

    public function find(int $id): ?QueryMonitorLog
    {
        return QueryMonitorLog::find($id);
    }
}
