<?php

declare(strict_types=1);

namespace Inspector\QueryMonitor\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class QueryMonitorLog extends Model
{
    protected $table = 'query_monitor_logs';

    protected $fillable = [
        'sql',
        'bindings',
        'execution_time',
        'connection',
        'query_type',
        'route',
        'controller',
        'request_url',
        'http_method',
        'user_id',
        'environment',
        'request_id',
        'is_slow',
        'metadata',
    ];

    protected $casts = [
        'bindings' => 'array',
        'metadata' => 'array',
        'is_slow' => 'boolean',
        'execution_time' => 'float',
        'user_id' => 'integer',
    ];

    public function getConnectionName(): ?string
    {
        $configured = config('query-monitor.database_connection');

        return $configured ?: parent::getConnectionName();
    }

    public function scopeSlow(Builder $query): Builder
    {
        return $query->where('is_slow', true);
    }

    public function scopeForRoute(Builder $query, string $route): Builder
    {
        return $query->where('route', $route);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function getFormattedExecutionTimeAttribute(): string
    {
        return round($this->execution_time, 2) . 'ms';
    }
}
