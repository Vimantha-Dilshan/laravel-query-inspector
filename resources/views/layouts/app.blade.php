<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title', 'Dashboard') — Query Monitor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }

        pre {
            tab-size: 2;
        }
    </style>
</head>

<body class="h-full">

    <div class="min-h-full" x-data="{ mobileMenuOpen: false }">

        {{-- ── Sidebar (desktop) ───────────────────────────────────── --}}
        <div class="hidden lg:fixed lg:inset-y-0 lg:flex lg:w-64 lg:flex-col">
            <div class="flex flex-col flex-grow bg-gray-900 overflow-y-auto">

                {{-- Brand --}}
                <div class="flex items-center flex-shrink-0 px-5 py-5 border-b border-gray-700">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0 w-9 h-9 bg-indigo-500 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-white font-semibold text-sm leading-tight">Query Monitor</p>
                            <p class="text-gray-400 text-xs">Performance Inspector</p>
                        </div>
                    </div>
                </div>

                {{-- Navigation --}}
                <nav class="flex-1 mt-4 px-3 space-y-1">
                    @php
                        $navItems = [
                            [
                                'route' => 'query-monitor.dashboard',
                                'label' => 'Dashboard',
                                'icon' =>
                                    'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
                            ],
                            [
                                'route' => 'query-monitor.queries.index',
                                'label' => 'All Queries',
                                'icon' => 'M4 6h16M4 10h16M4 14h16M4 18h16',
                            ],
                            [
                                'route' => 'query-monitor.slow-queries.index',
                                'label' => 'Slow Queries',
                                'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                                'badge' => 'slow',
                            ],
                            [
                                'route' => 'query-monitor.duplicate-queries.index',
                                'label' => 'Duplicates',
                                'icon' =>
                                    'M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z',
                            ],
                            [
                                'route' => 'query-monitor.n-plus-one.index',
                                'label' => 'N+1 Issues',
                                'icon' =>
                                    'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
                            ],
                        ];
                    @endphp

                    @foreach ($navItems as $item)
                        @php $active = request()->routeIs($item['route']); @endphp
                        <a href="{{ route($item['route']) }}"
                            class="{{ $active ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}
                              group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-all duration-150">
                            <svg class="{{ $active ? 'text-indigo-400' : 'text-gray-400 group-hover:text-gray-300' }} mr-3 flex-shrink-0 h-5 w-5"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="{{ $item['icon'] }}" />
                            </svg>
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>

                {{-- Footer --}}
                <div class="px-4 py-4 border-t border-gray-700">
                    <p class="text-xs text-gray-500">Laravel Query Inspector</p>
                    <p class="text-xs text-gray-600">{{ config('app.env') }} environment</p>
                </div>
            </div>
        </div>

        {{-- ── Main content ─────────────────────────────────────────── --}}
        <div class="lg:pl-64 flex flex-col min-h-screen">

            {{-- Top header --}}
            <div class="sticky top-0 z-10 flex-shrink-0 flex h-16 bg-white border-b border-gray-200 shadow-sm">
                {{-- Mobile menu button --}}
                <button type="button" @click="mobileMenuOpen = true"
                    class="px-4 text-gray-500 lg:hidden focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <div class="flex-1 px-6 flex justify-between items-center">
                    <div>
                        <h1 class="text-lg font-semibold text-gray-900">@yield('heading', 'Dashboard')</h1>
                        <p class="text-sm text-gray-500">@yield('subheading', 'Query Performance Overview')</p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <span
                            class="hidden sm:inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5 animate-pulse"></span>
                            Monitoring Active
                        </span>
                        <span class="text-xs text-gray-400 bg-gray-100 px-2 py-1 rounded font-mono">
                            {{ config('app.env') }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Page content --}}
            <main class="flex-1 py-6 px-4 sm:px-6 lg:px-8">
                @yield('content')
            </main>

            <footer class="py-4 px-6 text-center text-xs text-gray-400 border-t border-gray-200">
                Laravel Query Inspector — helping you write faster applications
            </footer>
        </div>

        {{-- ── Mobile off-canvas menu ───────────────────────────────── --}}
        <div x-show="mobileMenuOpen" x-cloak class="relative z-40 lg:hidden">
            <div class="fixed inset-0 bg-gray-600 bg-opacity-75" @click="mobileMenuOpen = false"></div>
            <div class="fixed inset-y-0 left-0 flex flex-col w-64 bg-gray-900">
                <div class="flex items-center justify-between px-5 py-5 border-b border-gray-700">
                    <p class="text-white font-semibold text-sm">Query Monitor</p>
                    <button @click="mobileMenuOpen = false" class="text-gray-400 hover:text-white">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <nav class="flex-1 mt-4 px-3 space-y-1">
                    @foreach ($navItems as $item)
                        @php $active = request()->routeIs($item['route']); @endphp
                        <a href="{{ route($item['route']) }}" @click="mobileMenuOpen = false"
                            class="{{ $active ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}
                              group flex items-center px-3 py-2 text-sm font-medium rounded-md">
                            <svg class="{{ $active ? 'text-indigo-400' : 'text-gray-400' }} mr-3 flex-shrink-0 h-5 w-5"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="{{ $item['icon'] }}" />
                            </svg>
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>
            </div>
        </div>

    </div>
</body>

</html>
