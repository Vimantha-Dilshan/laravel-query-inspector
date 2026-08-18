@extends('query-monitor::layouts.app')

@section('title', 'Query #' . $query->id)
@section('heading', 'Query Detail')
@section('subheading', '#' . $query->id . ' · ' . ($query->query_type ?? 'SQL') . ' · ' . round($query->execution_time,
    2) . 'ms')

@section('content')
    @php
        $timeMs = (float) $query->execution_time;
        $timeColor = $timeMs < 100 ? 'text-green-600' : ($timeMs < 500 ? 'text-yellow-600' : 'text-red-600');
        $typePalette = [
            'SELECT' => 'bg-blue-100 text-blue-800',
            'INSERT' => 'bg-green-100 text-green-800',
            'UPDATE' => 'bg-yellow-100 text-yellow-800',
            'DELETE' => 'bg-red-100 text-red-800',
        ];
        $typeBadge = $typePalette[$query->query_type] ?? 'bg-gray-100 text-gray-700';
    @endphp
    <div class="space-y-6">

        {{-- Back --}}
        <a href="{{ route('query-monitor.queries.index') }}"
            class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-500 font-medium">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to All Queries
        </a>

        {{-- SQL card --}}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
                <h3 class="text-base font-semibold text-gray-900">SQL Statement</h3>
                <div class="flex items-center space-x-2">
                    @if ($query->is_slow)
                        <span
                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700">
                            ⚠ SLOW QUERY
                        </span>
                    @endif
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $typeBadge }}">
                        {{ $query->query_type ?? '—' }}
                    </span>
                </div>
            </div>
            <div class="p-6">
                <pre
                    class="bg-gray-900 text-green-300 p-5 rounded-lg text-xs font-mono overflow-x-auto leading-relaxed whitespace-pre-wrap break-all">{{ $query->sql }}</pre>

                @if (!empty($query->bindings))
                    <div class="mt-5">
                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Bound Parameters</h4>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($query->bindings as $i => $binding)
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded bg-gray-100 text-xs font-mono text-gray-700">
                                    <span class="text-gray-400 mr-1">[${{ $i }}]</span>
                                    {{ is_null($binding) ? 'NULL' : (is_bool($binding) ? ($binding ? 'true' : 'false') : $binding) }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Metadata grid --}}
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">

            {{-- Performance --}}
            <div class="bg-white rounded-lg shadow p-5">
                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">Performance</h4>
                <dl class="space-y-3">
                    <div class="flex justify-between items-center">
                        <dt class="text-sm text-gray-600">Execution Time</dt>
                        <dd class="text-sm font-bold {{ $timeColor }}">{{ round($timeMs, 4) }}ms</dd>
                    </div>
                    <div class="flex justify-between items-center">
                        <dt class="text-sm text-gray-600">Status</dt>
                        <dd class="text-sm font-semibold {{ $query->is_slow ? 'text-red-600' : 'text-green-600' }}">
                            {{ $query->is_slow ? 'SLOW' : 'NORMAL' }}
                        </dd>
                    </div>
                    <div class="flex justify-between items-center">
                        <dt class="text-sm text-gray-600">Connection</dt>
                        <dd class="text-sm font-mono text-gray-800">{{ $query->connection ?? 'default' }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Request context --}}
            <div class="bg-white rounded-lg shadow p-5">
                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">Request Context</h4>
                <dl class="space-y-3">
                    <div class="flex justify-between items-center">
                        <dt class="text-sm text-gray-600">HTTP Method</dt>
                        <dd class="text-sm font-bold text-blue-600">{{ $query->http_method ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between items-center">
                        <dt class="text-sm text-gray-600">Route</dt>
                        <dd class="text-sm font-mono text-gray-800">{{ $query->route ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between items-center">
                        <dt class="text-sm text-gray-600">User ID</dt>
                        <dd class="text-sm text-gray-800">{{ $query->user_id ?? 'Guest' }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Environment --}}
            <div class="bg-white rounded-lg shadow p-5">
                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">Environment</h4>
                <dl class="space-y-3">
                    <div class="flex justify-between items-center">
                        <dt class="text-sm text-gray-600">App Env</dt>
                        <dd class="text-sm font-mono text-gray-800">{{ $query->environment ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between items-center">
                        <dt class="text-sm text-gray-600">Recorded At</dt>
                        <dd class="text-sm text-gray-800">{{ $query->created_at->format('Y-m-d H:i:s') }}</dd>
                    </div>
                    <div class="flex justify-between items-center">
                        <dt class="text-sm text-gray-600">Request ID</dt>
                        <dd class="text-xs font-mono text-gray-500 truncate max-w-[120px]"
                            title="{{ $query->request_id }}">
                            {{ $query->request_id ?? '—' }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Controller + URL --}}
        @if ($query->controller || $query->request_url)
            <div class="bg-white rounded-lg shadow p-5 space-y-4">
                @if ($query->controller)
                    <div>
                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Controller Action</h4>
                        <p class="text-sm font-mono text-gray-800 break-all">{{ $query->controller }}</p>
                    </div>
                @endif
                @if ($query->request_url)
                    <div>
                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Request URL</h4>
                        <p class="text-sm font-mono text-indigo-700 break-all">{{ $query->request_url }}</p>
                    </div>
                @endif
            </div>
        @endif

        {{-- Metadata --}}
        @if (!empty($query->metadata))
            <div class="bg-white rounded-lg shadow p-5">
                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Additional Metadata</h4>
                <pre class="text-xs text-gray-700 bg-gray-50 p-4 rounded-lg overflow-x-auto">{{ json_encode($query->metadata, JSON_PRETTY_PRINT) }}</pre>
            </div>
        @endif

        {{-- Optimization tips --}}
        @if ($query->is_slow)
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-5">
                <h4 class="text-sm font-semibold text-amber-800 flex items-center gap-1.5 mb-3">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    Optimization Recommendations
                </h4>
                <ul class="space-y-1.5 text-sm text-amber-700">
                    @if ($query->query_type === 'SELECT')
                        <li class="flex items-start gap-2"><span class="text-amber-500 mt-0.5">•</span> Verify that indexes
                            exist on all columns used in WHERE, JOIN, and ORDER BY clauses.</li>
                        <li class="flex items-start gap-2"><span class="text-amber-500 mt-0.5">•</span> Consider a composite
                            index when filtering on multiple columns together.</li>
                        <li class="flex items-start gap-2"><span class="text-amber-500 mt-0.5">•</span> Avoid <code
                                class="bg-amber-100 px-1 rounded">SELECT *</code> — select only the columns your code
                            actually uses.</li>
                        <li class="flex items-start gap-2"><span class="text-amber-500 mt-0.5">•</span> Run <code
                                class="bg-amber-100 px-1 rounded">EXPLAIN</code> on this query to inspect the execution
                            plan.</li>
                    @else
                        <li class="flex items-start gap-2"><span class="text-amber-500 mt-0.5">•</span> Ensure WHERE-clause
                            columns are indexed for this write operation.</li>
                        <li class="flex items-start gap-2"><span class="text-amber-500 mt-0.5">•</span> Consider batching
                            multiple writes into a single statement where possible.</li>
                    @endif
                    <li class="flex items-start gap-2"><span class="text-amber-500 mt-0.5">•</span> If this query is called
                        repeatedly, evaluate query result caching.</li>
                </ul>
            </div>
        @endif

    </div>
@endsection
