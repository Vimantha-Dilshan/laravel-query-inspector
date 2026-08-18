# Changelog

All notable changes to `laravel-query-inspector` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 2024-01-01

### Added

- Initial release of Laravel Query Performance Monitor
- Real-time query monitoring via `QueryExecuted` event listener
- **Slow Query Detection** — flag queries exceeding a configurable millisecond threshold
- **Duplicate Query Detection** — identify identical SQL executed more than once per request
- **N+1 Query Detection** — surface repeated query patterns within a single request lifecycle
- `QueryAnalyzer` service for in-process performance analysis
- `DatabaseStorage` implementation with full CRUD support
- `QueryStorageInterface` contract for custom storage backends
- `QueryMonitorLog` Eloquent model with query scopes
- Web dashboard built with Blade, Tailwind CSS, and Alpine.js
  - Overview page with aggregate statistics
  - All Queries listing with filters (route, type, date range)
  - Query detail page with optimization recommendations
  - Slow Queries page
  - Duplicate Queries page
  - N+1 Issues page
- `QueryMonitor` facade for the developer-facing API
- Three Artisan commands:
  - `query-monitor:install` — publish config and migrations
  - `query-monitor:clear` — remove old records
  - `query-monitor:report` — print a performance summary table
- `QueryRecorded` event dispatched after each captured query
- Configurable middleware, dashboard path, and access gate
- Comprehensive Pest test suite (unit + feature)
- GitHub Actions CI workflow (PHP 8.2/8.3 × Laravel 10/11/12)
- Full PSR-12 compliance with Laravel Pint

[Unreleased]: https://github.com/inspector/laravel-query-inspector/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/inspector/laravel-query-inspector/releases/tag/v1.0.0
