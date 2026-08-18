@extends('query-monitor::layouts.app')

@section('title', 'All Queries')
@section('heading', 'All Queries')
@section('subheading', 'Browse and filter every recorded database query')

@section('content')
    <div class="space-y-5" x-data="{ showFilters: {{ !empty(array_filter($filters)) ? 'true' : 'false' }} }">

        {{-- Filter panel --}}
        <div class="bg-white rounded-lg shadow">
            <button @click="showFilters = !showFilters" class="w-full px-6 py-4 flex items-center justify-between text-left">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Filters</h3>
                <svg :class="showFilters ? 'rotate-180' : ''" class="w-4 h-4 text-gray-400 transform transition-transform"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div x-show="showFilters" x-collapse>
                <form method="GET" action="{{ route('query-monitor.queries.index') }}"
                    class="px-6 pb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Route Name</label>
                        <input type="text" name="route" value="{{ $filters['route'] ?? '' }}"
                            placeholder="e.g. users.index"
                            class="block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Query Type</label>
                        <select name="type"
                            class="block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">All Types</option>
                            @foreach (['SELECT', 'INSERT', 'UPDATE', 'DELETE', 'OTHER'] as $type)
                                <option value="{{ $type }}"
                                    {{ ($filters['type'] ?? '') === $type ? 'selected' : '' }}>
                                    {{ $type }}
                                </option>
                            @endforeach
                        </select>
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
                    <div class="sm:col-span-2 lg:col-span-4 flex items-center space-x-3">
                        <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            Apply Filters
                        </button>
                        <a href="{{ route('query-monitor.queries.index') }}"
                            class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50">
                            Clear
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Results table --}}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">
                    Results
                </h3>
                <span class="text-sm text-gray-500">{{ number_format($queries->total()) }} total</span>
            </div>

            @if ($queries->isEmpty())
                <div class="py-16 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                            d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                    </svg>
                    <p class="mt-4 text-sm text-gray-500">No queries match the current filters.</p>
                    <a href="{{ route('query-monitor.queries.index') }}"
                        class="mt-2 inline-block text-sm text-indigo-600 hover:text-indigo-500">
                        Clear filters
                    </a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    SQL</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Type</th>
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
                                @php
                                    $timeMs = (float) $query->execution_time;
                                    $timeColor =
                                        $timeMs < 100
                                            ? 'text-green-600'
                                            : ($timeMs < 500
                                                ? 'text-yellow-600'
                                                : 'text-red-600');
                                    $typePalette = [
                                        'SELECT' => 'bg-blue-50 text-blue-700',
                                        'INSERT' => 'bg-green-50 text-green-700',
                                        'UPDATE' => 'bg-yellow-50 text-yellow-700',
                                        'DELETE' => 'bg-red-50 text-red-700',
                                    ];
                                    $typeBadge = $typePalette[$query->query_type] ?? 'bg-gray-100 text-gray-600';
                                    $methodPalette = [
                                        'GET' => 'text-blue-600',
                                        'POST' => 'text-green-600',
                                        'PUT' => 'text-yellow-600',
                                        'DELETE' => 'text-red-600',
                                        'PATCH' => 'text-orange-600',
                                    ];
                                    $methodColor = $methodPalette[$query->http_method ?? ''] ?? 'text-gray-500';
                                @endphp
                                <tr
                                    class="{{ $query->is_slow ? 'bg-red-50 hover:bg-red-100' : 'hover:bg-gray-50' }} transition-colors">
                                    <td class="px-6 py-3 max-w-xs">
                                        <span class="font-mono text-xs text-gray-800 block truncate"
                                            title="{{ $query->sql }}">
                                            {{ Str::limit($query->sql, 90) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap">
                                        <span class="font-semibold {{ $timeColor }}">{{ round($timeMs, 2) }}ms</span>
                                        @if ($query->is_slow)
                                            <span
                                                class="ml-1 inline-flex items-center px-1 py-0.5 rounded text-xs font-bold bg-red-100 text-red-700">SLOW</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $typeBadge }}">
                                            {{ $query->query_type ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-gray-600 max-w-xs truncate">
                                        {{ $query->route ?? '—' }}
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap">
                                        @if ($query->http_method)
                                            <span
                                                class="text-xs font-bold {{ $methodColor }}">{{ $query->http_method }}</span>
                                        @else
                                            <span class="text-gray-300">—</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-gray-400 text-xs">
                                        {{ $query->created_at->diffForHumans() }}
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-right">
                                        <a href="{{ route('query-monitor.queries.show', $query->id) }}"
                                            class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">
                                            View →
                                        </a>
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
