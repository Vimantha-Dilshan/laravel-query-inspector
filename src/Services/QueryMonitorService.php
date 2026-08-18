<?php

declare(strict_types=1);

namespace Inspector\QueryMonitor\Services;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Event;
use Inspector\QueryMonitor\Contracts\QueryStorageInterface;
use Inspector\QueryMonitor\Detectors\DuplicateQueryDetector;
use Inspector\QueryMonitor\Detectors\NPlusOneDetector;
use Inspector\QueryMonitor\Detectors\SlowQueryDetector;
use Inspector\QueryMonitor\Events\QueryRecorded;
use Inspector\QueryMonitor\Support\QueryData;

class QueryMonitorService
{
    private bool $monitoring = false;

    /**
     * Guard flag: prevents the storage queries from being monitored recursively.
     */
    private bool $storing = false;

    /** @var list<QueryData> */
    private array $requestQueries = [];

    private readonly string $requestId;

    private readonly DuplicateQueryDetector $duplicateDetector;

    private readonly NPlusOneDetector $nPlusOneDetector;

    private readonly SlowQueryDetector $slowQueryDetector;

    /** @var list<string> */
    private array $ignoredRoutes;

    /** @var list<string> */
    private array $ignoredQueryPatterns;

    public function __construct(
        private readonly QueryStorageInterface $storage,
        private readonly array $config
    ) {
        $this->requestId = uniqid('req_', true);

        $this->duplicateDetector = new DuplicateQueryDetector();

        $this->nPlusOneDetector = new NPlusOneDetector(
            threshold: (int) ($this->config['n_plus_one_threshold'] ?? 10)
        );

        $this->slowQueryDetector = new SlowQueryDetector(
            thresholdMs: (float) ($this->config['slow_query_threshold'] ?? 500)
        );

        $this->ignoredRoutes = (array) ($this->config['ignored_routes'] ?? []);
        $this->ignoredQueryPatterns = (array) ($this->config['ignored_queries'] ?? []);
    }

    /**
     * Attach the query listener to the Laravel event system.
     * Called once from the service provider boot phase.
     */
    public function boot(): void
    {
        $this->monitoring = true;

        Event::listen(QueryExecuted::class, $this->handleQueryExecuted(...));
    }

    private function handleQueryExecuted(QueryExecuted $event): void
    {
        if (! $this->monitoring || $this->storing) {
            return;
        }

        if ($this->shouldIgnoreQuery($event->sql)) {
            return;
        }

        if ($this->shouldIgnoreCurrentRoute()) {
            return;
        }

        $queryData = $this->buildQueryData($event);

        $this->requestQueries[] = $queryData;
        $this->duplicateDetector->track($queryData);
        $this->nPlusOneDetector->track($queryData);

        $this->persistQuery($queryData);

        Event::dispatch(new QueryRecorded($queryData));
    }

    private function buildQueryData(QueryExecuted $event): QueryData
    {
        $request = request();
        $route = $request->route();

        return new QueryData(
            sql: $event->sql,
            bindings: array_values($event->bindings),
            executionTime: (float) $event->time,
            connection: $event->connectionName,
            queryType: $this->detectQueryType($event->sql),
            route: $route?->getName(),
            controller: $this->resolveControllerAction($route),
            requestUrl: $request->fullUrl(),
            httpMethod: $request->method(),
            userId: $this->resolveUserId(),
            environment: app()->environment(),
            requestId: $this->requestId,
            isSlow: $this->slowQueryDetector->detect((float) $event->time),
            metadata: $this->buildMetadata(),
        );
    }

    private function persistQuery(QueryData $queryData): void
    {
        $this->storing = true;

        try {
            $this->storage->store($queryData);
        } finally {
            $this->storing = false;
        }
    }

    private function detectQueryType(string $sql): string
    {
        $normalized = strtoupper(ltrim($sql));

        return match (true) {
            str_starts_with($normalized, 'SELECT') => 'SELECT',
            str_starts_with($normalized, 'INSERT') => 'INSERT',
            str_starts_with($normalized, 'UPDATE') => 'UPDATE',
            str_starts_with($normalized, 'DELETE') => 'DELETE',
            str_starts_with($normalized, 'CREATE') => 'CREATE',
            str_starts_with($normalized, 'DROP') => 'DROP',
            str_starts_with($normalized, 'ALTER') => 'ALTER',
            default => 'OTHER',
        };
    }

    private function resolveControllerAction(mixed $route): ?string
    {
        if ($route === null) {
            return null;
        }

        $action = $route->getActionName();

        return ($action !== null && $action !== 'Closure') ? $action : null;
    }

    private function resolveUserId(): ?int
    {
        try {
            $id = auth()->id();

            return $id !== null ? (int) $id : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function buildMetadata(): array
    {
        $metadata = [];

        if ($this->config['capture_memory_usage'] ?? false) {
            $metadata['memory_usage'] = memory_get_usage(true);
            $metadata['memory_peak'] = memory_get_peak_usage(true);
        }

        return $metadata;
    }

    private function shouldIgnoreQuery(string $sql): bool
    {
        // Always ignore queries targeting the monitor table itself.
        if (str_contains(strtolower($sql), 'query_monitor_logs')) {
            return true;
        }

        foreach ($this->ignoredQueryPatterns as $pattern) {
            if (str_contains(strtolower($sql), strtolower($pattern))) {
                return true;
            }
        }

        return false;
    }

    private function shouldIgnoreCurrentRoute(): bool
    {
        $routeName = request()->route()?->getName();

        if ($routeName === null) {
            return false;
        }

        return in_array($routeName, $this->ignoredRoutes, true);
    }

    // ─── Public API ─────────────────────────────────────────────────────────

    /** @return list<QueryData> */
    public function getRequestQueries(): array
    {
        return $this->requestQueries;
    }

    /** @return array<string, array{count: int, query: QueryData}> */
    public function getDuplicateQueries(): array
    {
        return $this->duplicateDetector->getDuplicates();
    }

    /** @return array<string, array{count: int, query: QueryData}> */
    public function getPotentialNPlusOneIssues(): array
    {
        return $this->nPlusOneDetector->getPotentialIssues();
    }

    public function isEnabled(): bool
    {
        return $this->config['enabled'] ?? true;
    }

    public function ignoreRoute(string $route): static
    {
        $this->ignoredRoutes[] = $route;

        return $this;
    }

    public function stopMonitoring(): static
    {
        $this->monitoring = false;

        return $this;
    }

    public function resumeMonitoring(): static
    {
        $this->monitoring = true;

        return $this;
    }

    public function analyze(): QueryAnalyzer
    {
        return new QueryAnalyzer(
            queries: $this->requestQueries,
            duplicates: $this->duplicateDetector->getDuplicates(),
            nPlusOneIssues: $this->nPlusOneDetector->getPotentialIssues(),
            slowThreshold: $this->slowQueryDetector->getThreshold(),
        );
    }
}
