<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />


        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.jsx'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-sky-50">
            <div class="mx-auto w-full px-4 sm:px-6 lg:px-8 py-10">
                <div class="mx-auto max-w-5xl">
                    <div class="grid gap-6 lg:grid-cols-2">
                        {{-- Brand panel --}}
                        <div class="hidden lg:flex rounded-2xl border border-indigo-100 bg-white/60 backdrop-blur p-8 shadow-sm">
                            <div class="flex flex-col justify-between w-full">
                                <div>
                                    <a href="/" class="inline-flex items-center gap-3" wire:navigate>
                                        <div class="ml-10">
                                            <div class="text-xl font-semibold text-gray-900">NovaFix Support</div>
                                            <div class="text-sm text-gray-600">Service Center Diagnosis System</div>
                                        </div>
                                    </a>

                                    <div class="mt-8">
                                        <div class="text-sm font-semibold text-gray-900">What you can do</div>
                                        <ul class="mt-3 space-y-2 text-sm text-gray-700">
                                            <li class="flex gap-2"><span class="mt-0.5 h-2 w-2 rounded-full bg-indigo-500"></span>Quick YES/NO fault isolation</li>
                                            <li class="flex gap-2"><span class="mt-0.5 h-2 w-2 rounded-full bg-indigo-500"></span>Reusable diagnosis flows per problem</li>
                                            <li class="flex gap-2"><span class="mt-0.5 h-2 w-2 rounded-full bg-indigo-500"></span>Admin tools to manage devices & models</li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="mt-10 text-xs text-gray-500">
                                    Secure login required for service engineers and admins.
                                </div>
                            </div>
                        </div>

                        {{-- Form card --}}
                        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                            <div class="p-6 sm:p-8">
                                <div class="lg:hidden mb-6">
                                    <a href="/" class="inline-flex items-center gap-3" wire:navigate>
                                        <x-application-logo class="h-9 w-9 fill-current text-indigo-700" />
                                        <div class="text-lg font-semibold text-gray-900">NovaFix Support</div>
                                    </a>
                                </div>

                                {{ $slot }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
