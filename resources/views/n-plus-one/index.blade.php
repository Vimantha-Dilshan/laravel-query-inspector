@extends('query-monitor::layouts.app')

@section('title', 'N+1 Issues')
@section('heading', 'Potential N+1 Issues')
@section('subheading', 'Query patterns repeated ≥ ' . $threshold . ' times within a single request')

@section('content')
    <div class="space-y-5">

        {{-- Info banner --}}
        <div class="bg-orange-50 border border-orange-200 rounded-lg px-5 py-5">
            <div class="flex items-start space-x-3">
                <svg class="flex-shrink-0 w-5 h-5 text-orange-600 mt-0.5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div>
                    <p class="text-sm font-semibold text-orange-800">What is an N+1 query problem?</p>
                    <p class="text-sm text-orange-700 mt-1 leading-relaxed">
                        An N+1 problem occurs when your code executes 1 query to load a collection, then N additional
                        queries (one per item) to load related data — for example loading orders for each customer in a
                        loop.
                        The fix is almost always eager loading with <code class="bg-orange-100 px-1 rounded">with()</code>.
                    </p>
                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div class="bg-red-100 rounded p-3">
                            <p class="font-semibold text-red-800 mb-1">❌ Problem</p>
                            <pre class="text-red-700 font-mono text-xs">$customers = Customer::all();
foreach ($customers as $c) {
    echo $c->orders; // N extra queries
}</pre>
                        </div>
                        <div class="bg-green-100 rounded p-3">
                            <p class="font-semibold text-green-800 mb-1">✅ Solution</p>
                            <pre class="text-green-700 font-mono text-xs">$customers = Customer::with('orders')
    ->get(); // single JOIN query</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Results --}}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Detected Patterns</h3>
                <span class="text-sm text-gray-500">{{ number_format($issues->total()) }} patterns (threshold:
                    ≥{{ $threshold }}×)</span>
            </div>

            @if ($issues->isEmpty())
                <div class="py-16 text-center">
                    <svg class="mx-auto h-12 w-12 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="mt-4 text-sm font-medium text-green-600">No N+1 issues detected!</p>
                    <p class="text-sm text-gray-500 mt-1">No query pattern exceeded the {{ $threshold }}× threshold in a
                        single request.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    SQL Pattern</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Count in Request</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Route</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Method</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Request ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    First Seen</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach ($issues as $issue)
                                @php
                                    $count = (int) ($issue->occurrence_count ?? 0);
                                    $severity =
                                        $count >= 100
                                            ? 'bg-red-100 text-red-700'
                                            : ($count >= 50
                                                ? 'bg-orange-100 text-orange-700'
                                                : 'bg-yellow-50 text-yellow-700');
                                @endphp
                                <tr class="hover:bg-orange-50 transition-colors">
                                    <td class="px-6 py-3 max-w-sm">
                                        <span class="font-mono text-xs text-gray-800 block truncate"
                                            title="{{ $issue->sql }}">
                                            {{ Str::limit($issue->sql, 85) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $severity }}">
                                            {{ number_format($count) }}× queries
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-gray-600">{{ $issue->route ?? '—' }}</td>
                                    <td class="px-6 py-3 whitespace-nowrap">
                                        @if ($issue->http_method)
                                            <span class="text-xs font-bold text-blue-600">{{ $issue->http_method }}</span>
                                        @else
                                            <span class="text-gray-300">—</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap">
                                        <span class="font-mono text-xs text-gray-400" title="{{ $issue->request_id }}">
                                            {{ Str::limit($issue->request_id ?? '—', 20) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-gray-400 text-xs">
                                        @if ($issue->created_at)
                                            {{ \Carbon\Carbon::parse($issue->created_at)->diffForHumans() }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $issues->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
