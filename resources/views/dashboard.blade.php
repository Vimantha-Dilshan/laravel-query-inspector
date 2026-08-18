@extends('query-monitor::layouts.app')

@section('title', 'Dashboard')
@section('heading', 'Performance Dashboard')
@section('subheading', 'Real-time overview of your database query health')

@section('content')
    <div class="space-y-6">

        {{-- ── Stats cards ──────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">

            {{-- Total Queries --}}
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="p-5 flex items-center">
                    <div class="flex-shrink-0 w-11 h-11 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                        </svg>
                    </div>
                    <div class="ml-4 flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-500 truncate">Total Queries</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($statistics['total_queries']) }}</p>
                    </div>
                </div>
                <div class="bg-gray-50 px-5 py-2 text-xs text-gray-500">
                    {{ number_format($statistics['queries_today']) }} recorded today
                </div>
            </div>

            {{-- Slow Queries --}}
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="p-5 flex items-center">
                    <div
                        class="flex-shrink-0 w-11 h-11 {{ $statistics['slow_queries'] > 0 ? 'bg-red-100' : 'bg-gray-100' }} rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 {{ $statistics['slow_queries'] > 0 ? 'text-red-600' : 'text-gray-400' }}"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4 flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-500 truncate">Slow Queries</p>
                        <p
                            class="text-2xl font-bold {{ $statistics['slow_queries'] > 0 ? 'text-red-600' : 'text-gray-900' }}">
                            {{ number_format($statistics['slow_queries']) }}
                        </p>
                    </div>
                </div>
                <div class="bg-gray-50 px-5 py-2 text-xs text-gray-500">
                    Threshold: {{ config('query-monitor.slow_query_threshold', 500) }}ms
                </div>
            </div>

            {{-- Average Time --}}
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="p-5 flex items-center">
                    <div class="flex-shrink-0 w-11 h-11 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div class="ml-4 flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-500 truncate">Avg Execution Time</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $statistics['avg_execution_time'] }}<span
                                class="text-sm font-normal text-gray-500">ms</span></p>
                    </div>
                </div>
                <div class="bg-gray-50 px-5 py-2 text-xs text-gray-500">
                    Peak: {{ $statistics['max_execution_time'] }}ms
                </div>
            </div>

            {{-- Queries Today --}}
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="p-5 flex items-center">
                    <div class="flex-shrink-0 w-11 h-11 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="ml-4 flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-500 truncate">Today</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($statistics['queries_today']) }}</p>
                    </div>
                </div>
                <div class="bg-gray-50 px-5 py-2 text-xs text-gray-500">
                    {{ now()->format('D, d M Y') }}
                </div>
            </div>
        </div>

        {{-- ── Quick navigation panels ───────────────────────────────── --}}
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
            <a href="{{ route('query-monitor.slow-queries.index') }}"
                class="group bg-white rounded-lg shadow p-6 hover:shadow-md transition-shadow border-l-4 border-red-500 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 group-hover:text-red-600 transition-colors">Slow
                        Queries</h3>
                    <p class="mt-1 text-sm text-gray-500">Queries exceeding
                        {{ config('query-monitor.slow_query_threshold', 500) }}ms threshold</p>
                    <p class="mt-3 text-sm text-indigo-600 font-medium">View all →</p>
                </div>
                <div class="ml-4 text-4xl font-extrabold text-red-500">
                    {{ number_format($statistics['slow_queries']) }}
                </div>
            </a>

            <a href="{{ route('query-monitor.duplicate-queries.index') }}"
                class="group bg-white rounded-lg shadow p-6 hover:shadow-md transition-shadow border-l-4 border-yellow-500 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 group-hover:text-yellow-600 transition-colors">
                        Duplicate Queries</h3>
                    <p class="mt-1 text-sm text-gray-500">Same query executed multiple times per request</p>
                    <p class="mt-3 text-sm text-indigo-600 font-medium">View all →</p>
                </div>
                <div class="ml-4">
                    <svg class="w-12 h-12 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                </div>
            </a>

            <a href="{{ route('query-monitor.n-plus-one.index') }}"
                class="group bg-white rounded-lg shadow p-6 hover:shadow-md transition-shadow border-l-4 border-orange-500 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 group-hover:text-orange-600 transition-colors">N+1
                        Issues</h3>
                    <p class="mt-1 text-sm text-gray-500">Query patterns repeated ≥
                        {{ config('query-monitor.n_plus_one_threshold', 10) }}x in one request</p>
                    <p class="mt-3 text-sm text-indigo-600 font-medium">View all →</p>
                </div>
                <div class="ml-4">
                    <svg class="w-12 h-12 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
            </a>
        </div>

        {{-- ── Query type breakdown ──────────────────────────────────── --}}
        @if (!empty($statistics['query_type_breakdown']))
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-base font-semibold text-gray-900">Query Type Breakdown</h3>
                    <p class="text-sm text-gray-500 mt-0.5">Distribution of SQL statement types across all recorded queries
                    </p>
                </div>
                <div class="p-6">
                    @php
                        $typeColors = [
                            'SELECT' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'bar' => 'bg-blue-400'],
                            'INSERT' => ['bg' => 'bg-green-50', 'text' => 'text-green-700', 'bar' => 'bg-green-400'],
                            'UPDATE' => ['bg' => 'bg-yellow-50', 'text' => 'text-yellow-700', 'bar' => 'bg-yellow-400'],
                            'DELETE' => ['bg' => 'bg-red-50', 'text' => 'text-red-700', 'bar' => 'bg-red-400'],
                            'CREATE' => ['bg' => 'bg-purple-50', 'text' => 'text-purple-700', 'bar' => 'bg-purple-400'],
                            'OTHER' => ['bg' => 'bg-gray-50', 'text' => 'text-gray-700', 'bar' => 'bg-gray-400'],
                        ];
                        $total = array_sum($statistics['query_type_breakdown']);
                    @endphp
                    <div class="space-y-3">
                        @foreach ($statistics['query_type_breakdown'] as $type => $count)
                            @php
                                $colors = $typeColors[$type] ?? $typeColors['OTHER'];
                                $pct = $total > 0 ? round(($count / $total) * 100, 1) : 0;
                            @endphp
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $colors['bg'] }} {{ $colors['text'] }}">
                                        {{ $type }}
                                    </span>
                                    <span class="text-sm text-gray-600">{{ number_format($count) }} <span
                                            class="text-gray-400">({{ $pct }}%)</span></span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2">
                                    <div class="{{ $colors['bar'] }} h-2 rounded-full transition-all duration-500"
                                        style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- ── Empty state --}}
        @if ($statistics['total_queries'] === 0)
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <svg class="mx-auto h-14 w-14 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                        d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">No queries recorded yet</h3>
                <p class="mt-2 text-sm text-gray-500">Start making requests to your application and queries will appear
                    here.</p>
            </div>
        @endif

    </div>
@endsection
