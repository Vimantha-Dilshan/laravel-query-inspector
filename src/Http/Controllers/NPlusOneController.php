<?php

declare(strict_types=1);

namespace Inspector\QueryMonitor\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Inspector\QueryMonitor\Contracts\QueryStorageInterface;

class NPlusOneController extends Controller
{
    public function __construct(
        private readonly QueryStorageInterface $storage
    ) {}

    public function index(): View
    {
        $threshold = (int) config('query-monitor.n_plus_one_threshold', 10);
        $issues = $this->storage->potentialNPlusOne($threshold);

        return view('query-monitor::n-plus-one.index', [
            'issues' => $issues,
            'threshold' => $threshold,
        ]);
    }
}
