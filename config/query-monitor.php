<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Enable Query Monitor
    |--------------------------------------------------------------------------
    |
    | This option controls whether query monitoring is active. You may wish
    | to disable this in production environments or for specific use cases.
    |
    */

    'enabled' => env('QUERY_MONITOR_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Slow Query Threshold
    |--------------------------------------------------------------------------
    |
    | Queries exceeding this threshold (in milliseconds) will be flagged as
    | slow queries and highlighted in the dashboard with recommendations.
    |
    */

    'slow_query_threshold' => env('QUERY_MONITOR_SLOW_THRESHOLD', 500),

    /*
    |--------------------------------------------------------------------------
    | N+1 Detection Threshold
    |--------------------------------------------------------------------------
    |
    | The minimum number of times a similar query pattern must appear within
    | a single request to be flagged as a potential N+1 problem.
    |
    */

    'n_plus_one_threshold' => env('QUERY_MONITOR_N_PLUS_ONE_THRESHOLD', 10),

    /*
    |--------------------------------------------------------------------------
    | Storage Driver
    |--------------------------------------------------------------------------
    |
    | This option defines which storage driver is used for persisting query
    | data. Currently "database" is supported. Future drivers may be added.
    |
    | Supported: "database"
    |
    */

    'storage' => env('QUERY_MONITOR_STORAGE', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Database Connection
    |--------------------------------------------------------------------------
    |
    | The database connection to use for storing query monitor logs. Defaults
    | to your application's default connection. Set this to a separate
    | connection if you want to isolate monitoring data.
    |
    */

    'database_connection' => env('QUERY_MONITOR_DB_CONNECTION', null),

    /*
    |--------------------------------------------------------------------------
    | Retention Days
    |--------------------------------------------------------------------------
    |
    | The number of days to retain query monitor records. Records older than
    | this value will be deleted when running "query-monitor:clear".
    | Set to 0 to retain records indefinitely.
    |
    */

    'retention_days' => env('QUERY_MONITOR_RETENTION_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Monitor Console Commands
    |--------------------------------------------------------------------------
    |
    | Whether to monitor queries executed during Artisan console commands.
    | Disabled by default to reduce noise and avoid monitoring migrations.
    |
    */

    'monitor_console_commands' => env('QUERY_MONITOR_CONSOLE', false),

    /*
    |--------------------------------------------------------------------------
    | Capture Memory Usage
    |--------------------------------------------------------------------------
    |
    | When enabled, memory usage and peak memory are captured at the time each
    | query executes. This adds a small overhead but provides richer insights.
    |
    */

    'capture_memory_usage' => env('QUERY_MONITOR_MEMORY', false),

    /*
    |--------------------------------------------------------------------------
    | Ignored Routes
    |--------------------------------------------------------------------------
    |
    | Queries executed during requests to these named routes will not be
    | monitored. Useful for health checks and webhook endpoints.
    |
    */

    'ignored_routes' => [
        // 'health.check',
        // 'telescope.requests.index',
    ],

    /*
    |--------------------------------------------------------------------------
    | Ignored Query Patterns
    |--------------------------------------------------------------------------
    |
    | Any SQL query containing these substrings (case-insensitive) will be
    | silently skipped. Useful for filtering out migration schema queries.
    |
    */

    'ignored_queries' => [
        // 'migrations',
        // 'information_schema',
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the built-in web dashboard for browsing captured query data.
    |
    */

    'dashboard' => [

        /*
         * Toggle the dashboard on or off. When disabled, no web routes are
         * registered.
         */
        'enabled' => env('QUERY_MONITOR_DASHBOARD_ENABLED', true),

        /*
         * The URI prefix under which the dashboard is served.
         * e.g. http://your-app.test/query-monitor
         */
        'path' => env('QUERY_MONITOR_DASHBOARD_PATH', 'query-monitor'),

        /*
         * Middleware applied to every dashboard route.
         */
        'middleware' => ['web'],

        /*
         * A gate (ability name) used to restrict dashboard access in
         * non-local environments. Set to null to allow all authenticated users,
         * or define a gate in AuthServiceProvider.
         */
        'gate' => env('QUERY_MONITOR_GATE', null),

    ],

];
