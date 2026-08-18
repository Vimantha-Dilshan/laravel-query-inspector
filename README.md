# Laravel Query Inspector – Database Performance Monitor for Laravel

<!--
keywords:
laravel query monitor
laravel slow query detection
laravel n+1 detection
laravel duplicate query detection
laravel database performance
laravel query profiler
laravel query debugger
laravel performance monitoring
-->

⭐ If you find Laravel Query Inspector useful, please consider starring the repository.

![Packagist Version](https://img.shields.io/packagist/v/inspector/laravel-query-inspector)
![Packagist Downloads](https://img.shields.io/packagist/dt/inspector/laravel-query-inspector)
[![Tests](https://github.com/inspector/laravel-query-inspector/actions/workflows/ci.yml/badge.svg)](https://github.com/inspector/laravel-query-inspector/actions)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

**Laravel Query Inspector** is a zero-configuration database performance monitor for Laravel.

It listens to every SQL query your application executes and automatically identifies the three most common causes of database slowness: **slow queries**, **N+1 query problems**, and **duplicate queries** — surfacing them through a clean web dashboard with actionable fix recommendations.

Laravel Query Inspector is designed for teams that want to ship faster applications without instrumenting every controller by hand:

- local development profiling
- staging environment pre-release checks
- production performance auditing
- code review support for database-heavy features
- onboarding new engineers to a complex codebase

---

## Why Laravel Query Inspector?

Most Laravel developers reach for `DB::listen()` or `dd()` to debug queries. That approach is manual, ephemeral, and gives you no aggregate view. Other packages log everything but tell you nothing — you still have to figure out what is wrong.

Laravel Query Inspector takes a different approach. It captures every query with full context (route, controller, user, request ID) and runs it through three dedicated detectors. If something looks wrong, it tells you exactly what and suggests a fix.

| Feature                        | Query Inspector | Manual DB::listen | Generic Log Packages |
|--------------------------------|-----------------|-------------------|----------------------|
| Automatic slow query flagging  | ✓               | ✗                 | ✗                    |
| N+1 pattern detection          | ✓               | ✗                 | ✗                    |
| Duplicate query detection      | ✓               | ✗                 | ✗                    |
| Per-request analysis           | ✓               | ✗                 | ✗                    |
| Actionable fix recommendations | ✓               | ✗                 | ✗                    |
| Web dashboard                  | ✓               | ✗                 | ✗                    |
| Facade API                     | ✓               | ✗                 | ✗                    |
| Configurable thresholds        | ✓               | ✗                 | partial              |
| Route / query ignore lists     | ✓               | ✗                 | ✗                    |
| Pluggable storage drivers      | ✓               | ✗                 | ✗                    |
| Zero code changes required     | ✓               | ✗                 | ✓                    |

---

## Requirements

- PHP `^8.2`
- Laravel `^10.0`, `^11.0`, or `^12.0`
- A supported database: MySQL, PostgreSQL, or SQLite

---

## Installation

```bash
composer require inspector/laravel-query-inspector
php artisan query-monitor:install --migrate
```

`query-monitor:install` publishes the configuration file and migrations and runs `php artisan migrate`. Open `http://your-app.test/query-monitor` to see the dashboard immediately — no further setup required.

---

## Detecting your first slow query

Every query is captured automatically from the moment the package boots. To verify detection is working, temporarily lower the threshold in your local `.env`:

```dotenv
QUERY_MONITOR_SLOW_THRESHOLD=50
```

Any query taking longer than 50ms will appear on the **Slow Queries** dashboard page, flagged with a red badge, exact execution time, and optimization suggestions.

---

## Slow query detection

Queries exceeding the configured millisecond threshold are flagged automatically. The threshold is tunable per environment:

```php
// config/query-monitor.php
'slow_query_threshold' => env('QUERY_MONITOR_SLOW_THRESHOLD', 500),
```

The detail page for each slow query shows the full SQL, bound parameters, execution time, the route and controller that triggered it, and a set of concrete optimization suggestions — check indexes, avoid `SELECT *`, run `EXPLAIN`.

---

## N+1 query detection

The N+1 problem is the most common cause of database performance regressions in Laravel. It occurs when your code loads a collection and then queries a relationship inside a loop:

```php
// ❌ Executes 1 + N queries — one per customer
$customers = Customer::all();

foreach ($customers as $customer) {
    echo $customer->orders->count();
}
```

```php
// ✅ Executes 2 queries regardless of collection size
$customers = Customer::with('orders')->get();

foreach ($customers as $customer) {
    echo $customer->orders->count();
}
```

Query Inspector normalizes each SQL statement by stripping literal values — so `WHERE id = 1` and `WHERE id = 2` collapse to the same structural pattern — and counts how many times each pattern fires within a single request. When a pattern reaches the configured threshold, it is flagged as a probable N+1.

```php
// config/query-monitor.php
'n_plus_one_threshold' => env('QUERY_MONITOR_N_PLUS_ONE_THRESHOLD', 10),
```

The N+1 dashboard page shows the query pattern, how many times it ran, the route that triggered it, and the request ID so you can correlate multiple entries from the same request lifecycle.

---

## Duplicate query detection

Duplicate queries are identical SQL statements (same text, same bindings) executed more than once within the same request. They are almost always avoidable:

```php
// ❌ Three database round-trips for the same row
$user = User::find(1); // called in middleware
$user = User::find(1); // called in a controller
$user = User::find(1); // called in a service
```

```php
// ✅ Query once, share the result
$user = User::find(1);
app()->instance('current-user', $user);
```

Query Inspector uses a hash map keyed on `md5(sql + serialized_bindings)` to track every query in the current request. Anything appearing more than once is a duplicate. The dashboard groups duplicates by SQL and shows occurrence counts, originating routes, and common fix patterns: caching with `Cache::remember()`, eager loading with `with()`, and model reuse.

---

## Facade API

Query Inspector exposes a `QueryMonitor` facade for programmatic access in your application code:

```php
use Inspector\QueryMonitor\Facades\QueryMonitor;

// Check whether monitoring is currently active
QueryMonitor::isEnabled();

// Exclude a named route from monitoring
QueryMonitor::ignoreRoute('health.check');

// Pause and resume monitoring around a section of code
QueryMonitor::stopMonitoring();
sensitiveOperation();
QueryMonitor::resumeMonitoring();

// Retrieve all queries captured in the current request
$queries = QueryMonitor::getRequestQueries(); // list<QueryData>

// Get duplicate query groups detected in the current request
$duplicates = QueryMonitor::getDuplicateQueries();

// Get potential N+1 patterns detected in the current request
$issues = QueryMonitor::getPotentialNPlusOneIssues();
```

### Per-request analysis

Run a full in-memory analysis of everything captured so far in the current request:

```php
$analyzer = QueryMonitor::analyze();

$analyzer->totalQueries();          // int
$analyzer->slowQueryCount();        // int
$analyzer->duplicateCount();        // int
$analyzer->nPlusOneCount();         // int
$analyzer->averageExecutionTime();  // float (ms)
$analyzer->totalExecutionTime();    // float (ms)
$analyzer->hasPerformanceIssues();  // bool

$summary = $analyzer->getSummary();
// [
//   'total_queries'           => 48,
//   'slow_queries'            => 2,
//   'duplicate_queries'       => 5,
//   'n_plus_one_issues'       => 1,
//   'avg_execution_time_ms'   => 12.4,
//   'total_execution_time_ms' => 595.2,
//   'slow_threshold_ms'       => 500.0,
//   'has_issues'              => true,
// ]
```

---

## Listening to query events

Every captured query dispatches a `QueryRecorded` event. Hook into it to push data to Slack, your observability platform, or any custom sink:

```php
use Inspector\QueryMonitor\Events\QueryRecorded;

Event::listen(QueryRecorded::class, function (QueryRecorded $event): void {
    $data = $event->queryData;

    if ($data->isSlow) {
        Log::channel('slack')->warning('Slow query detected', [
            'route'          => $data->route,
            'execution_time' => $data->executionTime,
            'sql'            => $data->sql,
        ]);
    }
});
```

The `QueryData` DTO exposes: `sql`, `bindings`, `executionTime`, `connection`, `queryType`, `route`, `controller`, `requestUrl`, `httpMethod`, `userId`, `environment`, `requestId`, `isSlow`, and `metadata`.

---

## Web dashboard

Navigate to `/query-monitor` (path is configurable) to access the built-in dashboard.

| Page              | Route                              | Description                                               |
|-------------------|------------------------------------|-----------------------------------------------------------|
| Overview          | `/query-monitor`                   | Aggregate stats, query-type breakdown, quick links        |
| All Queries       | `/query-monitor/queries`           | Filterable table of every recorded query                  |
| Query Detail      | `/query-monitor/queries/{id}`      | Full SQL, bindings, metadata, and optimization tips       |
| Slow Queries      | `/query-monitor/slow-queries`      | Queries ranked by execution time with threshold shown     |
| Duplicate Queries | `/query-monitor/duplicate-queries` | Grouped by SQL with occurrence counts and fix patterns    |
| N+1 Issues        | `/query-monitor/n-plus-one`        | Repeated query patterns per request with side-by-side fix |

### Access control

In **local** environments the dashboard is open to everyone. In all other environments access is controlled by a Laravel gate:

```php
// app/Providers/AuthServiceProvider.php
Gate::define('viewQueryMonitor', function (User $user): bool {
    return $user->hasRole('developer');
});
```

```dotenv
QUERY_MONITOR_GATE=viewQueryMonitor
```

---

## Artisan commands

| Command                   | Purpose                                                                 |
|---------------------------|-------------------------------------------------------------------------|
| `query-monitor:install`   | Publish config and migrations (`--force`, `--migrate`)                  |
| `query-monitor:clear`     | Delete log records (`--days=N` for retention window, `--force` to skip prompt) |
| `query-monitor:report`    | Print an aggregate performance summary table (`--format=json`)          |

Example report output:

```
 ┌─────────────────────────────────────────────┐
 │   Laravel Query Performance Report          │
 └─────────────────────────────────────────────┘

 ┌────────────────────────┬────────────────┐
 │ Metric                 │ Value          │
 ├────────────────────────┼────────────────┤
 │ Total Queries          │ 10,542         │
 │ Queries Today          │ 1,204          │
 │ Slow Queries           │ 230            │
 │ Avg Execution Time     │ 35ms           │
 │ Max Execution Time     │ 1,812ms        │
 └────────────────────────┴────────────────┘
```

---

## Configuration

After installation, the configuration file lives at `config/query-monitor.php`. Every option is backed by an environment variable:

```php
return [
    // Enable or disable the entire monitoring engine
    'enabled' => env('QUERY_MONITOR_ENABLED', true),

    // Queries slower than this (ms) are flagged as slow
    'slow_query_threshold' => env('QUERY_MONITOR_SLOW_THRESHOLD', 500),

    // Queries that repeat this many times in one request trigger an N+1 alert
    'n_plus_one_threshold' => env('QUERY_MONITOR_N_PLUS_ONE_THRESHOLD', 10),

    // Storage driver — "database" ships in core; extend via QueryStorageInterface
    'storage' => env('QUERY_MONITOR_STORAGE', 'database'),

    // Separate DB connection for the monitor table (useful in high-traffic apps)
    'database_connection' => env('QUERY_MONITOR_DB_CONNECTION', null),

    // Records older than this are removed by query-monitor:clear
    'retention_days' => env('QUERY_MONITOR_RETENTION_DAYS', 30),

    // Monitor queries run by Artisan commands (disabled by default)
    'monitor_console_commands' => env('QUERY_MONITOR_CONSOLE', false),

    // Capture PHP memory usage alongside each query
    'capture_memory_usage' => env('QUERY_MONITOR_MEMORY', false),

    // Named routes whose queries should be silently skipped
    'ignored_routes' => [
        // 'health.check',
    ],

    // SQL substrings to skip (case-insensitive)
    'ignored_queries' => [],

    'dashboard' => [
        'enabled'    => env('QUERY_MONITOR_DASHBOARD_ENABLED', true),
        'path'       => env('QUERY_MONITOR_DASHBOARD_PATH', 'query-monitor'),
        'middleware' => ['web'],
        'gate'       => env('QUERY_MONITOR_GATE', null),
    ],
];
```

---

## Extending — custom storage drivers

Implement `QueryStorageInterface` to send query data anywhere:

```php
use Inspector\QueryMonitor\Contracts\QueryStorageInterface;
use Inspector\QueryMonitor\Support\QueryData;

class RedisQueryStorage implements QueryStorageInterface
{
    public function store(QueryData $queryData): void
    {
        Redis::lpush('query_monitor', json_encode($queryData->toArray()));
        Redis::ltrim('query_monitor', 0, 9999);
    }

    // implement clear(), statistics(), paginate(), slowQueries(),
    // duplicates(), potentialNPlusOne(), find()
}
```

Register the binding in a service provider:

```php
$this->app->bind(QueryStorageInterface::class, RedisQueryStorage::class);
```

---

## Features at a glance

- **Automatic query capture** via Laravel's `QueryExecuted` event — zero code changes required
- **Slow query detection** with configurable thresholds and per-query optimization tips
- **N+1 detection** through SQL normalization and per-request pattern frequency counting
- **Duplicate query detection** via hash-map keyed on `sql + bindings`
- **`QueryData` immutable DTO** — 14 fields per query including route, controller, user, and request ID
- **`QueryAnalyzer`** — in-process per-request aggregate analysis with `getSummary()`
- **`QueryRecorded` event** dispatched after every captured query — hook in your own alerting
- **`QueryMonitor` facade** — `analyze()`, `ignoreRoute()`, `stopMonitoring()`, `resumeMonitoring()`
- **Web dashboard** built with Blade, Tailwind CSS CDN, and Alpine.js — no build step
- **`DatabaseStorage` driver** with filtered pagination, duplicate grouping, and N+1 aggregation via SQL
- **`QueryStorageInterface` contract** for custom drivers (Redis, Elasticsearch, external APIs)
- **Recursive monitoring guard** — storage queries never appear in the monitor log
- **Route and query ignore lists** to suppress noise from health checks and internal tooling
- **Configurable dashboard access gate** for non-local environments
- **Comprehensive Pest test suite** — unit and feature tests running against SQLite in-memory

---

## Design principles

- **Zero configuration.** Works out of the box with sensible defaults. Tune thresholds when you are ready.
- **Non-intrusive.** The listener attaches once at boot. No decorators, no middleware wrapping, no trait requirements on your models.
- **Honest detection.** Three detectors, each solving one clearly defined problem with no opaque scoring.
- **Actionable output.** Every flagged query links to a detail page with a concrete fix suggestion.
- **Safe by default.** Storage queries are guarded from re-entering the listener. Exceptions in the storage layer never crash the application.

---

## Roadmap

Planned for upcoming releases:

- Redis storage driver
- Laravel Telescope integration
- `EXPLAIN` plan capture and display alongside stored SQL
- Slack / webhook alerting for slow queries in production
- Time-series chart on the dashboard overview page
- Filament admin panel integration

---

## Contributing

Contributions are welcome. Please read [CONTRIBUTING](CONTRIBUTING.md) before submitting pull requests.

---

## Security

If you discover a security vulnerability, please report it responsibly. See [SECURITY](SECURITY.md) for details.

> **Note:** The dashboard exposes raw query data including bound parameters. Always restrict dashboard access in production using the `query-monitor.dashboard.gate` configuration.

---

## License

Laravel Query Inspector is open-source software licensed under the [MIT](LICENSE) license.

---

## Credits

Laravel Query Inspector was built to give every Laravel developer a fast, clear answer to the question: *"Why is my application slow?"*

If you find it useful, consider starring the repository ⭐
