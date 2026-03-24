<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />

        <title>{{ config('app.name', 'NovaFix Support') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.jsx'])
        @inertiaHead
    </head>
    <body class="min-h-screen bg-gray-50 text-gray-900">
        <header class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="/" class="font-semibold text-gray-800">NovaFix</a>
                    @auth
                        @if(auth()->user()->role === 'admin')
                            <a href="{{ route('index') }}" class="text-sm text-gray-700 hover:text-gray-900">Admin Diagnosis</a>
                            <a href="{{ route('manage.devices') }}" class="text-sm text-gray-700 hover:text-gray-900">Devices</a>
                            <a href="{{ route('manage.brands') }}" class="text-sm text-gray-700 hover:text-gray-900">Brands</a>
                            <a href="{{ route('manage.models') }}" class="text-sm text-gray-700 hover:text-gray-900">Models</a>
                            <a href="{{ route('manage.problems') }}" class="text-sm text-gray-700 hover:text-gray-900">Problems</a>
                            <a href="{{ route('manage.answers') }}" class="text-sm text-gray-700 hover:text-gray-900">Answers</a>
                        @endif
                    @endauth
                </div>

                <div class="flex items-center gap-3">
                    @auth
                        <span class="text-sm text-gray-600">{{ auth()->user()->name }}</span>
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

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            @inertia
        </main>
    </body>
</html>

