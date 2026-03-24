<header class="bg-white border-b border-gray-200">
    <div class="w-full px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <div class="flex items-center gap-4 min-w-0">
            <a href="{{ auth()->check() && auth()->user()->role === 'admin' ? route('index') : url('/') }}"
               class="font-semibold text-gray-800 whitespace-nowrap">
                NovaFix
            </a>

            @auth
                @if(auth()->user()->role === 'admin')
                    <nav class="hidden md:flex items-center gap-3 text-sm text-gray-700 min-w-0">
                        <a href="{{ route('index') }}" class="hover:text-gray-900">Diagnosis</a>
                        <a href="{{ route('manage.devices') }}" class="hover:text-gray-900">Devices</a>
                        <a href="{{ route('manage.brands') }}" class="hover:text-gray-900">Brands</a>
                        <a href="{{ route('manage.models') }}" class="hover:text-gray-900">Models</a>
                        <a href="{{ route('manage.problems') }}" class="hover:text-gray-900">Problems</a>
                        <a href="{{ route('manage.answers') }}" class="hover:text-gray-900">Answers</a>
                    </nav>
                @endif
            @endauth
        </div>

        <div class="flex items-center gap-3">
            @auth
                <div class="hidden sm:block text-sm text-gray-600 truncate max-w-[220px]">
                    {{ auth()->user()->name }}
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm px-3 py-1.5 rounded-md bg-gray-900 text-white hover:bg-gray-800">
                        Logout
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="text-sm px-3 py-1.5 rounded-md bg-gray-900 text-white hover:bg-gray-800">
                    Login
                </a>
            @endauth
        </div>
    </div>
</header>

