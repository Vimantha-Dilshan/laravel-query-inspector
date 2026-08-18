@extends('query-monitor::layouts.app')

@section('title', 'Duplicate Queries')
@section('heading', 'Duplicate Queries')
@section('subheading', 'SQL statements executed more than once — often cacheable or avoidable')

@section('content')
    <div class="space-y-5">

        {{-- Info banner --}}
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg px-5 py-4 flex items-start space-x-3">
            <svg class="flex-shrink-0 w-5 h-5 text-yellow-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
            <div>
                <p class="text-sm font-medium text-yellow-800">What are duplicate queries?</p>
                <p class="text-sm text-yellow-700 mt-0.5">
                    These are identical SQL statements executed multiple times. Common fixes include result caching
                    (<code class="bg-yellow-100 px-1 rounded">Cache::remember()</code>), eager loading, or
                    refactoring repository calls to avoid redundancy.
                </p>
            </div>
        </div>

        {{-- Results --}}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Duplicate Query Groups</h3>
                <span class="text-sm text-gray-500">{{ number_format($queries->total()) }} groups</span>
            </div>

            @if ($queries->isEmpty())
                <div class="py-16 text-center">
                    <svg class="mx-auto h-12 w-12 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="mt-4 text-sm font-medium text-green-600">No duplicate queries found!</p>
                    <p class="text-sm text-gray-500 mt-1">All recorded queries appear to be unique.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    SQL</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Occurrences</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Max Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Route</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Last Seen</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach ($queries as $query)
                                @php
                                    $count = (int) ($query->occurrence_count ?? 0);
                                    $severity =
                                        $count >= 50
                                            ? 'bg-red-100 text-red-700'
                                            : ($count >= 10
                                                ? 'bg-yellow-100 text-yellow-700'
                                                : 'bg-orange-50 text-orange-700');
                                @endphp
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-3 max-w-sm">
                                        <span class="font-mono text-xs text-gray-800 block truncate"
                                            title="{{ $query->sql }}">
                                            {{ Str::limit($query->sql, 90) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $severity }}">
                                            {{ number_format($count) }}×
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-600">
                                        {{ round((float) ($query->max_execution_time ?? 0), 2) }}ms
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-gray-600">
                                        {{ $query->route ?? '—' }}
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-gray-400 text-xs">
                                        @if ($query->last_seen)
                                            {{ \Carbon\Carbon::parse($query->last_seen)->diffForHumans() }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Recommendation --}}
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                    <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider mb-2">Common Fixes</p>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs text-gray-600">
                        <div class="flex items-start space-x-2">
                            <span class="text-green-500 font-bold mt-0.5">①</span>
                            <span>Use <code class="bg-gray-200 px-1 rounded">Cache::remember()</code> to cache query results
                                for repeated lookups.</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <span class="text-blue-500 font-bold mt-0.5">②</span>
                            <span>Use <code class="bg-gray-200 px-1 rounded">with()</code> eager loading to reduce repeated
                                relationship queries.</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <span class="text-purple-500 font-bold mt-0.5">③</span>
                            <span>Pass pre-loaded models between services instead of re-querying within the same
                                request.</span>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $queries->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
