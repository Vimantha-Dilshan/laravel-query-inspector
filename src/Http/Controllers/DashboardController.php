<?php

declare(strict_types=1);

namespace Inspector\QueryMonitor\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Inspector\QueryMonitor\Contracts\QueryStorageInterface;

class DashboardController extends Controller
{
    public function __construct(
        private readonly QueryStorageInterface $storage
    ) {}

    public function index(): View
    {
        $statistics = $this->storage->statistics();

        return view('query-monitor::dashboard', [
            'statistics' => $statistics,
        ]);
    }
}
