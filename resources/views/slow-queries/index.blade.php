@extends('query-monitor::layouts.app')

@section('title', 'Slow Queries')
@section('heading', 'Slow Queries')
@section('subheading', 'Queries that exceeded the ' . $threshold . 'ms threshold')

@section('content')
    <div class="space-y-5" x-data="{ showFilters: false }">

        {{-- Threshold notice --}}
        <div class="bg-red-50 border border-red-200 rounded-lg px-5 py-4 flex items-start space-x-3">
            <svg class="flex-shrink-0 w-5 h-5 text-red-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                <p class="text-sm font-medium text-red-800">Slow Query Threshold: {{ $threshold }}ms</p>
                <p class="text-sm text-red-600 mt-0.5">
                    The queries below each exceeded {{ $threshold }}ms. Consider adding indexes, restructuring queries,
                    or enabling caching.
                    Adjust the threshold in <code class="bg-red-100 px-1 rounded">config/query-monitor.php</code>.
                </p>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white rounded-lg shadow">
            <button @click="showFilters = !showFilters"
                class="w-full px-6 py-4 flex items-center justify-between text-left">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Filters</h3>
                <svg :class="showFilters ? 'rotate-180' : ''" class="w-4 h-4 text-gray-400 transform transition-transform"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div x-show="showFilters" x-collapse>
                <form method="GET" action="{{ route('query-monitor.slow-queries.index') }}"
                    class="px-6 pb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Route Name</label>
                        <input type="text" name="route" value="{{ $filters['route'] ?? '' }}"
                            placeholder="e.g. products.index"
                            class="block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Date From</label>
                        <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"
                            class="block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Date To</label>
                        <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"
                            class="block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div class="sm:col-span-3 flex space-x-3">
                        <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                            Apply
                        </button>
                        <a href="{{ route('query-monitor.slow-queries.index') }}"
                            class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50">
                            Clear
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Results --}}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Results</h3>
                <span class="text-sm text-gray-500">{{ number_format($queries->total()) }} slow queries</span>
            </div>

            @if ($queries->isEmpty())
                <div class="py-16 text-center">
                    <svg class="mx-auto h-12 w-12 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="mt-4 text-sm font-medium text-green-600">No slow queries detected!</p>
                    <p class="text-sm text-gray-500 mt-1">All recorded queries completed within the {{ $threshold }}ms
                        threshold.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    SQL</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Execution Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Route</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Method</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    When</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Detail</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach ($queries as $query)
                                <tr class="hover:bg-red-50 transition-colors">
                                    <td class="px-6 py-3 max-w-xs">
                                        <span class="font-mono text-xs text-gray-800 block truncate"
                                            title="{{ $query->sql }}">
                                            {{ Str::limit($query->sql, 85) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap">
                                        <span
                                            class="font-bold text-red-600 text-sm">{{ round($query->execution_time, 2) }}ms</span>
                                        <span
                                            class="ml-1 text-xs text-gray-400">+{{ round($query->execution_time - $threshold, 0) }}ms
                                            over limit</span>
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-gray-600">{{ $query->route ?? '—' }}</td>
                                    <td class="px-6 py-3 whitespace-nowrap">
                                        @if ($query->http_method)
                                            <span class="text-xs font-bold text-blue-600">{{ $query->http_method }}</span>
                                        @else
                                            <span class="text-gray-300">—</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-gray-400 text-xs">
                                        {{ $query->created_at->diffForHumans() }}</td>
                                    <td class="px-6 py-3 whitespace-nowrap text-right">
                                        <a href="{{ route('query-monitor.queries.show', $query->id) }}"
                                            class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">View →</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $queries->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
