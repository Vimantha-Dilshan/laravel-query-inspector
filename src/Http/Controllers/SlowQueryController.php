<?php

declare(strict_types=1);

namespace Inspector\QueryMonitor\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Inspector\QueryMonitor\Contracts\QueryStorageInterface;

class SlowQueryController extends Controller
{
    public function __construct(
        private readonly QueryStorageInterface $storage
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['route', 'date_from', 'date_to']);
        $queries = $this->storage->slowQueries($filters);
        $threshold = (int) config('query-monitor.slow_query_threshold', 500);

        return view('query-monitor::slow-queries.index', [
            'queries' => $queries,
            'filters' => $filters,
            'threshold' => $threshold,
        ]);
    }
}
