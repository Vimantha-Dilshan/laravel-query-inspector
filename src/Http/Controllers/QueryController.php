<?php

declare(strict_types=1);

namespace Inspector\QueryMonitor\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Inspector\QueryMonitor\Contracts\QueryStorageInterface;

class QueryController extends Controller
{
    public function __construct(
        private readonly QueryStorageInterface $storage
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['route', 'type', 'user_id', 'date_from', 'date_to']);
        $queries = $this->storage->paginate($filters);

        return view('query-monitor::queries.index', [
            'queries' => $queries,
            'filters' => $filters,
        ]);
    }

    public function show(int $id): View
    {
        $query = $this->storage->find($id);

        abort_if($query === null, 404, 'Query record not found.');

        return view('query-monitor::queries.show', [
            'query' => $query,
        ]);
    }
}
