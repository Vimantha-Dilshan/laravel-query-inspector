<?php

declare(strict_types=1);

namespace Inspector\QueryMonitor\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Inspector\QueryMonitor\Contracts\QueryStorageInterface;

class DuplicateQueryController extends Controller
{
    public function __construct(
        private readonly QueryStorageInterface $storage
    ) {}

    public function index(): View
    {
        $queries = $this->storage->duplicates();

        return view('query-monitor::duplicate-queries.index', [
            'queries' => $queries,
        ]);
    }
}
