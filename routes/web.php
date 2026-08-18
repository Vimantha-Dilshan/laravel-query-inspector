<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Inspector\QueryMonitor\Http\Controllers\DashboardController;
use Inspector\QueryMonitor\Http\Controllers\DuplicateQueryController;
use Inspector\QueryMonitor\Http\Controllers\NPlusOneController;
use Inspector\QueryMonitor\Http\Controllers\QueryController;
use Inspector\QueryMonitor\Http\Controllers\SlowQueryController;
use Inspector\QueryMonitor\Http\Middleware\DashboardAuthentication;

Route::group([
    'prefix' => config('query-monitor.dashboard.path', 'query-monitor'),
    'middleware' => array_merge(
        (array) config('query-monitor.dashboard.middleware', ['web']),
        [DashboardAuthentication::class]
    ),
    'as' => 'query-monitor.',
], static function (): void {
    Route::get('/', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/queries', [QueryController::class, 'index'])
        ->name('queries.index');

    Route::get('/queries/{id}', [QueryController::class, 'show'])
        ->where('id', '[0-9]+')
        ->name('queries.show');

    Route::get('/slow-queries', [SlowQueryController::class, 'index'])
        ->name('slow-queries.index');

    Route::get('/duplicate-queries', [DuplicateQueryController::class, 'index'])
        ->name('duplicate-queries.index');

    Route::get('/n-plus-one', [NPlusOneController::class, 'index'])
        ->name('n-plus-one.index');
});
