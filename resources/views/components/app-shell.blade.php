@props([
    'title' => null,
])

<div class="min-h-screen bg-gray-50 text-gray-900">
    {{-- Mobile top bar --}}
    <header class="sticky top-0 z-30 border-b border-gray-200 bg-white lg:hidden">
        <div class="flex h-14 items-center justify-between px-4">
            <button type="button"
                class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50"
                data-drawer-open="app-mobile-drawer"
                aria-label="Open menu">
                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <div class="flex items-center gap-2">
                <a href="{{ auth()->check() && auth()->user()->role === 'admin' ? route('index') : url('/') }}"
                    class="font-semibold text-gray-800">
                    NovaFix
                </a>
            </div>

            <div class="flex items-center gap-2">
                @auth
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="rounded-lg bg-gray-900 px-3 py-2 text-xs font-semibold text-white hover:bg-gray-800">
                            Logout
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}"
                        class="rounded-lg bg-gray-900 px-3 py-2 text-xs font-semibold text-white hover:bg-gray-800">
                        Login
                    </a>
                @endauth
            </div>
        </div>
    </header>

    {{-- Mobile drawer --}}
    <div id="app-mobile-drawer-overlay" class="fixed inset-0 z-40 hidden bg-black/40 lg:hidden" data-drawer-overlay="app-mobile-drawer"></div>
    <aside id="app-mobile-drawer"
        class="fixed inset-y-0 left-0 z-50 w-72 -translate-x-full transform border-r border-gray-200 bg-white shadow-lg transition-transform lg:hidden"
        data-drawer="app-mobile-drawer">
        <div class="flex h-14 items-center justify-between px-4 border-b border-gray-100">
            <div class="font-semibold text-gray-800">Menu</div>
            <button type="button"
                class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50"
                data-drawer-close="app-mobile-drawer"
                aria-label="Close menu">
                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="p-3">
            <div class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500 px-3">Navigation</div>
            <div class="space-y-1">
                <x-nav-links />
            </div>
        </div>
    </aside>

    <div class="flex">
        {{-- Desktop sidebar --}}
        <aside class="hidden lg:sticky lg:top-0 lg:z-20 lg:block lg:h-screen lg:w-64 lg:shrink-0 lg:border-r lg:border-gray-200 lg:bg-white">
            <div class="h-16 flex items-center px-6 border-b border-gray-100">
                <a href="{{ auth()->check() && auth()->user()->role === 'admin' ? route('index') : url('/') }}"
                    class="font-semibold text-gray-800">
                    NovaFix
                </a>
            </div>

            <div class="p-4">
                @auth
                    <div class="mb-4 rounded-lg border border-gray-200 bg-gray-50 p-3">
                        <div class="text-xs font-semibold text-gray-600">Signed in</div>
                        <div class="mt-1 text-sm font-semibold text-gray-900 truncate">{{ auth()->user()->name }}</div>
                        <div class="mt-0.5 inline-flex w-fit rounded-full bg-indigo-50 px-2 py-0.5 text-[11px] font-semibold text-indigo-700">
                            {{ strtoupper(auth()->user()->role ?? 'user') }}
                        </div>
                        <div class="text-xs text-gray-600 truncate">{{ auth()->user()->email }}</div>
                    </div>
                @endauth

                <div class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500 px-3">Navigation</div>
                <nav class="space-y-1">
                    <x-nav-links />
                </nav>

                @auth
                    <form method="POST" action="{{ route('logout') }}" class="mt-6">
                        @csrf
                        <button type="submit" class="w-full rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                            Logout
                        </button>
                    </form>
                @endauth
            </div>
        </aside>

        {{-- Main content --}}
        <main class="w-full min-w-0 px-4 py-5 sm:px-6 lg:px-8 lg:py-8">
            {{ $slot }}
        </main>
    </div>
</div>
